<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class ExpoConfig
{
    public static function activa(?int $expoId = null): ?object
    {
        $query = DB::table('expo')
            ->where('estado', 'Activo')
            ->where('fecha_inicio', '<=', now())
            ->where(function ($q) {
                $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', now());
            });

        if ($expoId) {
            $query->where('id', $expoId);
        }

        return $query->orderByDesc('id')->first();
    }

    public static function detalleActiva(?int $expoId = null): ?array
    {
        $expo = self::activa($expoId);
        if (!$expo) {
            return null;
        }

        return self::construirDetalle($expo);
    }

    public static function detalleActivaParaUsuario(?int $expoId, ?int $usuarioId, ?int $clienteId = null): ?array
    {
        if (!$usuarioId) {
            return null;
        }

        $detalle = self::detalleActiva($expoId);
        if (!$detalle) {
            return null;
        }

        $autorizado = DB::table('expo_usuario')
            ->where('expo_id', $detalle['id'])
            ->where('usuario_id', $usuarioId)
            ->exists();

        if (!$autorizado) {
            return null;
        }

        return $clienteId === null ? $detalle : self::aplicarElegibilidadCliente($detalle, $clienteId);
    }

    public static function detalleParaFacturacion(int $expoId, int $cotizacionId, ?int $usuarioId): ?array
    {
        $detalle = self::detalleActivaParaUsuario($expoId, $usuarioId);
        if ($detalle) {
            return $detalle;
        }

        if (!$usuarioId || !DB::table('expo_usuario')->where('expo_id', $expoId)->where('usuario_id', $usuarioId)->exists()) {
            return null;
        }

        $reabierta = DB::table('expo_cotizacion')
            ->where('expo_id', $expoId)
            ->where('cotizacion_id', $cotizacionId)
            ->where('reapertura_autorizada', true)
            ->whereIn('estado', ['PENDIENTE_FACTURACION', 'FACTURACION_PARCIAL'])
            ->exists();
        if (!$reabierta) {
            return null;
        }

        $expo = DB::table('expo')->where('id', $expoId)->first();
        return $expo ? self::construirDetalle($expo) : null;
    }

    public static function detalleParaDuplicacion(int $expoId, int $cotizacionId, int $flujoId, ?int $usuarioId): ?array
    {
        if (!$usuarioId || !DB::table('expo_usuario')
            ->where('expo_id', $expoId)
            ->where('usuario_id', $usuarioId)
            ->exists()) {
            return null;
        }

        $ofertaPerteneceAlFlujo = DB::table('expo_cotizacion as ec')
            ->where('ec.expo_id', $expoId)
            ->where('ec.cotizacion_id', $cotizacionId)
            ->where(function ($query) use ($cotizacionId, $flujoId) {
                $query->where('ec.flujo_id', $flujoId)
                    ->orWhereExists(function ($historico) use ($cotizacionId, $flujoId) {
                        $historico->selectRaw('1')
                            ->from('historico_flujo as hf')
                            ->where('hf.flujo_id', $flujoId)
                            ->where('hf.tipo_tramite_id', 2)
                            ->where('hf.tramite_id', $cotizacionId);
                    });
            })
            ->exists();
        if (!$ofertaPerteneceAlFlujo || self::motivoBloqueoDuplicacion($cotizacionId, $flujoId)) {
            return null;
        }

        $expo = DB::table('expo')->where('id', $expoId)->first();
        if (!$expo) {
            return null;
        }

        // La duplicación debe conservar la autorización de descuento del
        // cliente y escala, igual que la creación de una oferta nueva.
        $clienteId = (int) DB::table('cotizacion')
            ->where('id', $cotizacionId)
            ->value('cliente_id');

        return self::aplicarElegibilidadCliente(
            self::construirDetalle($expo),
            $clienteId
        );
    }

    public static function motivoBloqueoDuplicacion(int $cotizacionId, int $flujoId): ?string
    {
        $secciones = DB::table('expo_oferta_seccion')
            ->where('expo_oferta_seccion.flujo_id', $flujoId)
            ->where('expo_oferta_seccion.cotizacion_origen_id', $cotizacionId);

        if ((clone $secciones)->where('expo_oferta_seccion.estado', 'FACTURADA')->exists()) {
            return 'No es posible duplicar la oferta porque una de sus secciones ya fue facturada.';
        }

        if ((clone $secciones)
            ->join('prefactura as pf', 'pf.id', '=', 'expo_oferta_seccion.prefactura_id')
            ->where('pf.estado', 'activo')
            ->exists()) {
            return 'Debe anular las prefacturas activas de todas las secciones antes de duplicar la oferta.';
        }

        return null;
    }

    public static function detalleParaEditarFactura(int $expoId, int $cotizacionId, int $prefacturaId, int $flujoId, int $autorizacionId, ?int $usuarioId): ?array
    {
        if (!$usuarioId || !DB::table('expo_usuario')
            ->where('expo_id', $expoId)
            ->where('usuario_id', $usuarioId)
            ->exists()) {
            return null;
        }

        $edicionAutorizada = DB::table('prefactura as pf')
            ->join('expo_cotizacion as ec', 'ec.cotizacion_id', '=', 'pf.cotizacion_id')
            ->join('codigo_autorizacion as ca', function ($join) use ($autorizacionId, $flujoId) {
                $join->where('ca.id', $autorizacionId)
                    ->where('ca.flujo_id', $flujoId)
                    ->where('ca.tipo_tramite', 'editar_factura')
                    ->where('ca.estado_codigo_id', 2);
            })
            ->where('pf.id', $prefacturaId)
            ->where('pf.flujo_id', $flujoId)
            ->where('pf.cotizacion_id', $cotizacionId)
            ->whereIn('pf.estado', ['activo', 'convertida'])
            ->where('ec.expo_id', $expoId)
            ->where('ca.users_id', $usuarioId)
            ->exists();
        if (!$edicionAutorizada) {
            return null;
        }

        $expo = DB::table('expo')->where('id', $expoId)->first();
        return $expo ? self::construirDetalle($expo) : null;
    }

    public static function tipoVentaId(): ?int
    {
        $id = DB::table('tipo_venta')
            ->whereRaw('LOWER(TRIM(descripcion)) = ?', ['expo'])
            ->value('id');

        return $id ? (int) $id : null;
    }

    private static function construirDetalle(object $expo): array
    {
        $descuentosClientes = DB::table('expo_asistencia_descuento_escala as eade')
            ->join('expo_asistencia as ea', 'ea.id', '=', 'eade.expo_asistencia_id')
            ->where('ea.expo_id', $expo->id)
            ->get(['ea.cliente_id', 'eade.escala_id', 'eade.descuento_modo', 'eade.descuento_escalon'])
            ->groupBy('cliente_id')
            ->map(fn ($descuentos) => $descuentos->mapWithKeys(fn ($descuento) => [
                (int) $descuento->escala_id => [
                    'descuento_modo' => $descuento->descuento_modo,
                    'descuento_escalon' => $descuento->descuento_escalon !== null
                        ? (int) $descuento->descuento_escalon
                        : null,
                ],
            ])->all())->all();

        return [
            'id' => (int) $expo->id,
            'nombre' => $expo->nombre,
            'bodegas' => DB::table('expo_bodega')->where('expo_id', $expo->id)->pluck('bodega_id')->map(fn ($id) => (int) $id)->all(),
            'escalas' => DB::table('expo_escala')->where('expo_id', $expo->id)->pluck('escala_id')->map(fn ($id) => (int) $id)->all(),
            'escalas_detalle' => DB::table('expo_escala as ee')
                ->join('categoria_precios as cp', 'cp.id', '=', 'ee.escala_id')
                ->where('ee.expo_id', $expo->id)->orderBy('cp.nombre')->get(['cp.id', 'cp.nombre'])
                ->map(fn ($escala) => ['id' => (int) $escala->id, 'nombre' => $escala->nombre])->all(),
            'descuentos' => DB::table('expo_descuento')->where('expo_id', $expo->id)->orderBy('venta_minima')
                ->get(['venta_minima', 'porcentaje_descuento'])
                ->map(fn ($regla) => ['venta_minima' => (float) $regla->venta_minima, 'porcentaje_descuento' => (float) $regla->porcentaje_descuento])->all(),
            'descuentos_marca' => DB::table('expo_descuento_marca as edm')
                ->join('marca as m', 'm.id', '=', 'edm.marca_id')
                ->where('edm.expo_id', $expo->id)->orderBy('edm.orden')
                ->get(['edm.marca_id', 'm.nombre as marca', 'edm.venta_minima', 'edm.porcentaje_descuento', 'edm.requiere_asistencia', 'edm.orden'])
                ->map(fn ($regla) => [
                    'marca_id' => (int) $regla->marca_id,
                    'marca' => $regla->marca,
                    'venta_minima' => (float) $regla->venta_minima,
                    'porcentaje_descuento' => (float) $regla->porcentaje_descuento,
                    'requiere_asistencia' => (bool) $regla->requiere_asistencia,
                    'orden' => (int) $regla->orden,
                ])->all(),
            'tipo_descuento' => 'escala',
            'descuentos_escala' => DB::table('expo_descuento_escala as ede')
                ->join('categoria_precios as cp', 'cp.id', '=', 'ede.escala_id')
                ->where('ede.expo_id', $expo->id)->orderBy('ede.orden')
                ->get(['ede.escala_id', 'cp.nombre as escala', 'ede.venta_minima', 'ede.porcentaje_descuento', 'ede.requiere_asistencia', 'ede.orden'])
                ->map(fn ($regla) => [
                    'escala_id' => (int) $regla->escala_id,
                    'escala' => $regla->escala,
                    'venta_minima' => (float) $regla->venta_minima,
                    'porcentaje_descuento' => (float) $regla->porcentaje_descuento,
                    'requiere_asistencia' => (bool) $regla->requiere_asistencia,
                    'orden' => (int) $regla->orden,
                ])->all(),
            'clientes_asistentes' => DB::table('expo_asistencia')->where('expo_id', $expo->id)
                ->pluck('cliente_id')->map(fn ($id) => (int) $id)->all(),
            'descuentos_clientes' => $descuentosClientes,
        ];
    }

    private static function aplicarElegibilidadCliente(array $detalle, int $clienteId): array
    {
        $asistio = in_array($clienteId, $detalle['clientes_asistentes'] ?? [], true);
        $detalle['cliente_asistio'] = $asistio;
        $detalle['descuentos_forzados'] = $detalle['descuentos_clientes'][$clienteId] ?? [];
        $detalle['descuentos_marca'] = array_values(array_filter(
            $detalle['descuentos_marca'],
            fn (array $regla) => empty($regla['requiere_asistencia']) || $asistio
        ));
        $detalle['descuentos_escala'] = array_values(array_filter(
            $detalle['descuentos_escala'],
            fn (array $regla) => empty($regla['requiere_asistencia']) || $asistio
        ));

        return $detalle;
    }
}