<?php

namespace App\Services\Expo;

use App\Models\ModelCodigoAutorizacion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SaldoLineasOferta
{
    private const TOLERANCIA = 0.0001;

    public function pendientes(int $cotizacionId): Collection
    {
        $facturado = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->where('f.estado_venta_id', 1)
            ->whereNotNull('vhp.cotizacion_has_producto_id')
            ->groupBy('vhp.cotizacion_has_producto_id')
            ->selectRaw('vhp.cotizacion_has_producto_id, SUM(COALESCE(vhp.cantidad_oferta_aplicada, vhp.cantidad_s)) as cantidad_facturada');

        return DB::table('cotizacion_has_producto as chp')
            ->leftJoinSub($facturado, 'facturado', function ($join) {
                $join->on('facturado.cotizacion_has_producto_id', '=', 'chp.id');
            })
            ->where('chp.cotizacion_id', $cotizacionId)
            ->select([
                'chp.*',
                DB::raw('COALESCE(facturado.cantidad_facturada, 0) as cantidad_facturada'),
                DB::raw('GREATEST(chp.cantidad - COALESCE(facturado.cantidad_facturada, 0), 0) as cantidad_pendiente'),
            ])
            ->orderBy('chp.indice')
            ->get();
    }

    /**
     * @param array<int, string|int> $indices
     * @return array<string, int> ID de línea por índice del carrito.
     */
    public function validarSolicitudFactura(Request $request, array $indices): array
    {
        $cotizacionId = (int) $request->input('cotizacion_id', 0);
        $expoSolicitada = (int) $request->input('expo_id', 0) > 0;
        $esOfertaExpo = $cotizacionId > 0 && DB::table('expo_cotizacion')
            ->where('cotizacion_id', $cotizacionId)
            ->exists();

        if ($expoSolicitada && !$esOfertaExpo) {
            throw ValidationException::withMessages([
                'cotizacion_id' => 'La factura Expo debe estar vinculada a una oferta Expo válida.',
            ]);
        }

        if (!$esOfertaExpo) {
            return [];
        }

        $lineasPorIndice = [];
        $cantidades = [];
        $productos = [];

        foreach ($indices as $indice) {
            $lineaId = (int) $request->input('cotizacionLineaId' . $indice, 0);
            $cantidad = (float) $request->input('cantidad' . $indice, 0);

            if ($lineaId <= 0) {
                continue;
            }

            $lineasPorIndice[(string) $indice] = $lineaId;
            $cantidades[$lineaId] = ($cantidades[$lineaId] ?? 0) + $cantidad;
            $productos[$lineaId] = (int) $request->input('idProducto' . $indice, 0);
        }

        if (empty($cantidades)) {
            throw ValidationException::withMessages([
                'productos' => 'La factura Expo debe incluir al menos una línea de la oferta vinculada.',
            ]);
        }

        $lineas = $this->validarYBloquear($cotizacionId, $cantidades);

        foreach ($productos as $lineaId => $productoId) {
            if ((int) $lineas[$lineaId]->producto_id !== $productoId) {
                throw ValidationException::withMessages([
                    'productos' => "El producto enviado no corresponde a la línea Expo {$lineaId}.",
                ]);
            }
        }

        $permitePreciosMenores = DB::table('tipo_factura')
            ->where('id', (int) $request->input('tipo_factura_id', 0))
            ->value('multiples_precios');

        if ((bool) $permitePreciosMenores) {
            $autorizacion = ModelCodigoAutorizacion::where('id', (int) $request->input('codigo_autorizacion', 0))
                ->where('users_id', Auth::id())
                ->where('estado_id', 1)
                ->first();

            if (!$autorizacion || !$autorizacion->esValido(
                $request->input('flujo_id') ? (int) $request->input('flujo_id') : null,
                'facturacion'
            )) {
                throw ValidationException::withMessages([
                    'autorizacion_sr' => 'La Factura SR requiere un código de autorización válido y vigente.',
                ]);
            }
        }

        if (!(bool) $permitePreciosMenores) {
            $productosBajoPacto = [];
            $productosSobrePacto = [];
            foreach ($lineasPorIndice as $indice => $lineaId) {
                $precioPactado = (float) ($lineas[$lineaId]->precio_unidad ?? 0);
                $precioFactura = (float) $request->input('precio' . $indice, 0);
                if ($precioPactado > 0 && $precioFactura < $precioPactado - self::TOLERANCIA) {
                    $productosBajoPacto[] = (string) ($lineas[$lineaId]->nombre_producto ?? "Producto #{$lineaId}")
                        . ': pactado L. ' . number_format($precioPactado, 2)
                        . ', ingresado L. ' . number_format($precioFactura, 2);
                } elseif ($precioPactado > 0 && $precioFactura > $precioPactado + self::TOLERANCIA) {
                    $productosSobrePacto[] = (string) ($lineas[$lineaId]->nombre_producto ?? "Producto #{$lineaId}")
                        . ': pactado L. ' . number_format($precioPactado, 2)
                        . ', ingresado L. ' . number_format($precioFactura, 2);
                }
            }

            if (!empty($productosBajoPacto)) {
                throw ValidationException::withMessages([
                    'precio_expo_menor' => 'El precio de la factura es menor que el pactado en la Oferta Expo ganadora. '
                        . implode('; ', $productosBajoPacto)
                        . '. Para usar un precio menor debe seleccionar Factura SR.',
                ]);
            }

            if (!empty($productosSobrePacto)) {
                throw ValidationException::withMessages([
                    'precio_expo_distinto' => 'La factura Expo normal debe conservar exactamente el precio pactado en la oferta ganadora. '
                        . implode('; ', $productosSobrePacto) . '.',
                ]);
            }
        }

        $cantidadesAplicadas = [];
        $restantePorLinea = $lineas->mapWithKeys(
            fn ($linea) => [(int) $linea->id => (float) $linea->cantidad_aplicada]
        )->all();
        foreach ($lineasPorIndice as $indice => $lineaId) {
            $cantidadAplicada = min(
                (float) $request->input('cantidad' . $indice, 0),
                (float) ($restantePorLinea[$lineaId] ?? 0)
            );
            $cantidadesAplicadas['cantidadOfertaAplicada' . $indice] = $cantidadAplicada;
            $restantePorLinea[$lineaId] = max(0, ($restantePorLinea[$lineaId] ?? 0) - $cantidadAplicada);
        }
        $request->merge($cantidadesAplicadas);

        return $lineasPorIndice;
    }

    /**
     * @param array<int, float|int|string> $cantidades Cantidad solicitada por ID de línea.
     */
    public function validarYBloquear(int $cotizacionId, array $cantidades): Collection
    {
        if (!DB::transactionLevel()) {
            throw new \LogicException('La validación de cantidades Expo requiere una transacción activa.');
        }

        $expoCotizacion = DB::table('expo_cotizacion as ec')
            ->join('expo as e', 'e.id', '=', 'ec.expo_id')
            ->where('ec.cotizacion_id', $cotizacionId)
            ->lockForUpdate()
            ->first(['ec.id', 'ec.estado', 'e.estado as expo_estado']);

        if (!$expoCotizacion) {
            throw ValidationException::withMessages([
                'cotizacion_id' => 'La oferta Expo vinculada no existe.',
            ]);
        }

        if ($expoCotizacion->expo_estado !== 'Activo') {
            throw ValidationException::withMessages([
                'cotizacion_id' => 'La Expo finalizó y ya no permite generar nuevas facturas.',
            ]);
        }

        if (!in_array($expoCotizacion->estado, ['PENDIENTE_FACTURACION', 'FACTURACION_PARCIAL'], true)) {
            throw ValidationException::withMessages([
                'cotizacion_id' => "La oferta Expo no admite nuevas facturas en estado {$expoCotizacion->estado}.",
            ]);
        }

        $cantidades = collect($cantidades)
            ->mapWithKeys(fn($cantidad, $lineaId) => [(int) $lineaId => (float) $cantidad]);

        if ($cantidades->isEmpty() || $cantidades->contains(fn($cantidad) => $cantidad <= 0)) {
            throw ValidationException::withMessages([
                'productos' => 'Cada línea Expo debe tener una cantidad mayor que cero.',
            ]);
        }

        $lineas = DB::table('cotizacion_has_producto')
            ->where('cotizacion_id', $cotizacionId)
            ->whereIn('id', $cantidades->keys())
            ->lockForUpdate()
            ->get(['id', 'producto_id', 'nombre_producto', 'cantidad', 'precio_unidad'])
            ->keyBy('id');

        if ($lineas->count() !== $cantidades->count()) {
            throw ValidationException::withMessages([
                'productos' => 'Una o más líneas no pertenecen a la Oferta Expo seleccionada.',
            ]);
        }

        $facturado = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->where('f.estado_venta_id', 1)
            ->whereIn('vhp.cotizacion_has_producto_id', $cantidades->keys())
            ->groupBy('vhp.cotizacion_has_producto_id')
            ->selectRaw('vhp.cotizacion_has_producto_id, SUM(COALESCE(vhp.cantidad_oferta_aplicada, vhp.cantidad_s)) as cantidad_facturada')
            ->pluck('cantidad_facturada', 'vhp.cotizacion_has_producto_id');

        foreach ($cantidades as $lineaId => $solicitada) {
            $ofertada = (float) $lineas[$lineaId]->cantidad;
            $consumida = (float) ($facturado[$lineaId] ?? 0);
            $pendiente = max(0.0, $ofertada - $consumida);

            $lineas[$lineaId]->cantidad_facturada = $consumida;
            $lineas[$lineaId]->cantidad_pendiente = $pendiente;
            $lineas[$lineaId]->cantidad_solicitada = $solicitada;
            $lineas[$lineaId]->cantidad_aplicada = min($solicitada, $pendiente);
        }

        return $lineas;
    }
}