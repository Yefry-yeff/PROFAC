<?php

namespace App\Http\Livewire\Flujo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Luecano\NumeroALetras\NumeroALetras;
use PDF;
use App\Models\PrefacturaAuditoria;
use App\Http\Livewire\Ventas\FacturacionCorporativa;

/**
 * HTTP controller (non-Livewire) that handles prefactura save & print.
 * Registered in web.php as a plain controller.
 */
class PrefacturaController
{
    // ─────────────────────────────────────────────────────────────────────
    // POST /flujo/prefactura/guardar
    // ─────────────────────────────────────────────────────────────────────
    public function guardar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subTotalGeneralGrabado' => 'required',
            'subTotalGeneral'        => 'required',
            'isvGeneral'             => 'required',
            'totalGeneral'           => 'required',
            'numeroInputs'           => 'required',
            'seleccionarCliente'     => 'required',
            'nombre_cliente_ventas'  => 'required',
            'bodega'                 => 'required',
            'seleccionarProducto'    => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'icon'    => 'error',
                'title'   => 'Error',
                'text'    => 'Por favor, verificar que todos los campos estén completados.',
                'errors'  => $validator->errors(),
            ], 401);
        }

        $arrayInputs = explode(',', $request->arregloIdInputs);

        // ── Calcular fecha de vencimiento ──────────────────────────────────
        $config = DB::table('configuracion_prefactura')->first();
        $diasValidez = $config ? (int) $config->dias_validez : 7;
        $fechaEmision    = $request->fecha_emision ?? now()->toDateString();
        $fechaVencimiento = \Carbon\Carbon::parse($fechaEmision)->addDays($diasValidez)->toDateString();

        DB::beginTransaction();
        try {
            // ── Cabecera ────────────────────────────────────────────────────
            $prefacturaId = DB::table('prefactura')->insertGetId([
                'cotizacion_id'       => $request->cotizacion_id ?: null,
                'flujo_id'            => $request->flujo_id      ?: null,
                'cliente_id'          => $request->seleccionarCliente,
                'nombre_cliente'      => $request->nombre_cliente_ventas,
                'RTN'                 => $request->rtn_ventas ?: null,
                'fecha_emision'       => $fechaEmision,
                'fecha_vencimiento'   => $fechaVencimiento,
                'sub_total'           => $request->subTotalGeneral,
                'sub_total_grabado'   => $request->subTotalGeneralGrabado,
                'sub_total_excento'   => $request->subTotalGeneralExcento ?? 0,
                'isv'                 => $request->isvGeneral,
                'total'               => $request->totalGeneral,
                'porc_descuento'      => $request->porDescuento ?? 0,
                'monto_descuento'     => $request->descuentoGeneral ?? 0,
                'tipo_venta_id'       => $request->tipo_venta_id ?: null,
                'vendedor'            => $request->vendedor ?: null,
                'nota'                => $request->nota ?: null,
                'arregloIdInputs'     => $request->arregloIdInputs,
                'numeroInputs'        => $request->numeroInputs,
                'estado'              => 'activo',
                'users_id'            => Auth::id(),
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            // ── Productos ───────────────────────────────────────────────────
            $arrayProductos = [];
            for ($i = 0; $i < count($arrayInputs); $i++) {
                $idx = $arrayInputs[$i];
                $arrayProductos[] = [
                    'prefactura_id'           => $prefacturaId,
                    'producto_id'             => $request->{"idProducto{$idx}"}      ?: null,
                    'indice'                  => $idx,
                    'nombre_producto'         => $request->{"nombre{$idx}"}          ?? '',
                    'nombre_bodega'           => $request->{"bodega{$idx}"}          ?? '',
                    'precio_unidad'           => $request->{"precio{$idx}"}          ?? 0,
                    'cantidad'               => $request->{"cantidad{$idx}"}         ?? 1,
                    'sub_total'              => $request->{"subTotal{$idx}"}         ?? 0,
                    'isv'                    => $request->{"isvProducto{$idx}"}      ?? 0,
                    'total'                  => $request->{"total{$idx}"}            ?? 0,
                    'isv_producto'           => $request->{"isv{$idx}"}              ?? 0,
                    'Bodega_id'              => $request->{"idBodega{$idx}"}         ?: null,
                    'seccion_id'             => $request->{"idSeccion{$idx}"}        ?: null,
                    'unidad_medida_venta_id' => $request->{"idUnidadVenta{$idx}"}    ?: null,
                    'monto_descProducto'     => $request->{"acumuladoDescuento{$idx}"} ?? 0,
                    'idPrecioSeleccionado'   => $request->{"idPrecioSeleccionado{$idx}"} ?? null,
                    'precioSeleccionado'     => $request->{"precios{$idx}"}          ?? null,
                    'precios_producto_carga_id' => $request->{"precios_producto_carga_id{$idx}"} ?: null,
                    'resta_inventario'       => $request->{"restaInventario{$idx}"}  ?? 0,
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ];
            }

            if (!empty($arrayProductos)) {
                DB::table('prefactura_has_producto')->insert($arrayProductos);
            }

            // ── Disminuir inventario ────────────────────────────────────────
            // Only decrease stock for rows where resta_inventario is truthy
            foreach ($arrayProductos as $prod) {
                if ($prod['resta_inventario'] && $prod['producto_id'] && $prod['seccion_id']) {
                    DB::table('inventario')
                        ->where('producto_id', $prod['producto_id'])
                        ->where('seccion_id', $prod['seccion_id'])
                        ->decrement('existencia', $prod['cantidad'] * ($prod['unidad_medida_venta_id'] ? 1 : 1));
                }
            }

            // ── Historico flujo + avanzar flujo a prefactura ───────────────
            $flujoId = $request->flujo_id ?: null;
            if ($flujoId) {
                // tipo_tramite_id para 'prefactura'
                $tramiteId = DB::table('tipos_tramites')->where('nombre', 'prefactura')->value('id');

                DB::table('historico_flujo')->insert([
                    'flujo_id'        => $flujoId,
                    'tipo_tramite_id' => $tramiteId,
                    'tramite_id'      => $prefacturaId,
                    'estado_id'       => 1,
                    'observaciones'   => 'Prefactura #' . $prefacturaId . ' creada desde oferta ganadora',
                    'created_by'      => Auth::id(),
                    'updated_by'      => Auth::id(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                DB::table('flujo')->where('id', $flujoId)->update([
                    'tipo_tramite_id' => $tramiteId,
                    'updated_by'      => Auth::id(),
                    'updated_at'      => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'icon'          => 'success',
                'title'         => '¡Éxito!',
                'text'          => 'Prefactura guardada correctamente.',
                'idPrefactura'  => $prefacturaId,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'Error al guardar: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /prefactura/imprimir/{id}
    // ─────────────────────────────────────────────────────────────────────
    public function imprimir(int $id)
    {
        $datos = DB::selectOne("
            SELECT
                p.cliente_id as clienteId,
                CONCAT(YEAR(NOW()),'-',p.id) as codigo,
                cl.nombre,
                cl.direccion,
                cl.correo,
                cl.telefono_empresa,
                p.fecha_emision,
                TIME(p.created_at) as hora,
                p.fecha_vencimiento,
                cl.rtn,
                u.name as cotizador,
                (SELECT name FROM users WHERE id = p.vendedor) as vendedor,
                p.nota,
                p.id as prefactura_id,
                cfg.dias_validez,
                cfg.descripcion_validez
            FROM prefactura p
            INNER JOIN cliente cl ON cl.id = p.cliente_id
            INNER JOIN users u ON u.id = p.users_id
            CROSS JOIN configuracion_prefactura cfg
            WHERE p.id = ?
        ", [$id]);

        if (!$datos) abort(404);

        $productos = DB::select("
            SELECT
                pr.id as codigo,
                pr.nombre,
                pr.descripcion,
                IF(pr.isv = 0, 'SI', 'NO') as excento,
                FORMAT(php.precio_unidad, 2) as precio,
                FORMAT(php.cantidad, 2) as cantidad,
                FORMAT(php.sub_total, 2) as importe,
                COALESCE(um.nombre, '') as medida
            FROM prefactura_has_producto php
            INNER JOIN producto pr ON pr.id = php.producto_id
            LEFT JOIN unidad_medida_venta umv ON umv.id = php.unidad_medida_venta_id
            LEFT JOIN unidad_medida um ON um.id = umv.unidad_medida_id
            WHERE php.prefactura_id = ?
            ORDER BY php.indice ASC
        ", [$id]);

        $importes = DB::selectOne("
            SELECT
                porc_descuento, total, isv, sub_total,
                sub_total_grabado, sub_total_excento, monto_descuento
            FROM prefactura WHERE id = ?
        ", [$id]);

        $importesConCentavos = DB::selectOne("
            SELECT
                FORMAT(monto_descuento, 2) as monto_descuento,
                FORMAT(total, 2) as total,
                FORMAT(isv, 2) as isv,
                FORMAT(sub_total, 2) as sub_total,
                FORMAT(sub_total_grabado, 2) as sub_total_grabado,
                FORMAT(sub_total_excento, 2) as sub_total_excento
            FROM prefactura WHERE id = ?
        ", [$id]);

        $flagCentavos = (fmod((float) $importes->total, 1) != 0.0);
        $formatter    = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('pdf.prefactura', compact(
            'datos', 'productos', 'importes', 'importesConCentavos',
            'flagCentavos', 'numeroLetras'
        ))->setPaper('letter');

        return $pdf->stream("Prefactura_NO_{$datos->codigo}.pdf");
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /cotizacion/prefacturar-desde-oferta
    // Creates a prefactura directly from a cotizacion (offer).
    // Validates inventory from recibido_bodega, reserves stock,
    // inserts historico_flujo (tipo_tramite_id=4), updates flujo.
    // ─────────────────────────────────────────────────────────────────────
    public function prefacturarDesdeOferta(Request $request)
    {
        $cotizacionId = (int) $request->cotizacion_id;
        $flujoId      = $request->flujo_id ? (int) $request->flujo_id : null;

        if (!$cotizacionId) {
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'cotizacion_id requerido.'], 422);
        }

        $cotizacion = DB::table('cotizacion')->where('id', $cotizacionId)->first();
        if (!$cotizacion) {
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Oferta no encontrada.'], 404);
        }

        // ── Verificar si la revisión de inventario está activa ────────────
        $configRevision = DB::table('configuracion_revision_inventario')->first();
        $revisionActiva = $configRevision && (bool) $configRevision->activo;

        if ($revisionActiva) {
            // Buscar flujo_id si no vino en el request
            if (!$flujoId) {
                $flujoId = DB::table('historico_flujo')
                    ->where('tipo_tramite_id', 2)
                    ->where('tramite_id', $cotizacionId)
                    ->value('flujo_id');
            }

            DB::beginTransaction();
            try {
                // 1. Quitar ganadora anterior
                if ($flujoId) {
                    DB::table('historico_flujo')
                        ->where('flujo_id', $flujoId)
                        ->where('tipo_tramite_id', 2)
                        ->where('observaciones', 'ganadora')
                        ->update(['observaciones' => null, 'updated_at' => now()]);
                }

                // 2. Marcar esta oferta como ganadora
                DB::table('historico_flujo')
                    ->where('tramite_id', $cotizacionId)
                    ->where('tipo_tramite_id', 2)
                    ->when($flujoId, fn($q) => $q->where('flujo_id', $flujoId))
                    ->update(['observaciones' => 'ganadora', 'updated_at' => now()]);

                // 3. Auditoría cotizacion_estado
                DB::table('cotizacion_estado')->insert([
                    'cotizacion_id' => $cotizacionId,
                    'flujo_id'      => $flujoId,
                    'ganadora'      => 1,
                    'comentario'    => 'Marcada como ganadora. Enviada a Revisión de Inventario.',
                    'estado_id'     => 1,
                    'created_by'    => Auth::id(),
                    'updated_by'    => Auth::id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                // 4. Crear historico_flujo tipo=9
                DB::table('historico_flujo')->insert([
                    'flujo_id'        => $flujoId,
                    'tipo_tramite_id' => 9,
                    'tramite_id'      => $cotizacionId,
                    'estado_id'       => 5,
                    'observaciones'   => 'En Revisión de Inventario. Oferta #' . $cotizacionId,
                    'created_by'      => Auth::id(),
                    'updated_by'      => Auth::id(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                // 5. Avanzar flujo al paso 9
                if ($flujoId) {
                    DB::table('flujo')->where('id', $flujoId)->update([
                        'tipo_tramite_id' => 9,
                        'updated_by'      => Auth::id(),
                        'updated_at'      => now(),
                    ]);
                }

                DB::commit();
                return response()->json([
                    'en_revision_inventario' => true,
                    'cotizacionId'           => $cotizacionId,
                    'flujoId'                => $flujoId,
                    'message'                => 'Oferta #' . $cotizacionId . ' enviada a Revisión de Inventario.',
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => $e->getMessage()], 500);
            }
        }

        $productos = DB::table('cotizacion_has_producto')
            ->where('cotizacion_id', $cotizacionId)
            ->get();

        // ── 1. Validar inventario (stock neto = stock_real - reservado en prefacturas activas) ──
        $stockErrors = [];
        foreach ($productos as $prod) {
            if ($prod->resta_inventario && $prod->producto_id && $prod->seccion_id) {
                $rawStock  = (float) DB::table('recibido_bodega')
                    ->where('producto_id', $prod->producto_id)
                    ->where('seccion_id',  $prod->seccion_id)
                    ->where('cantidad_disponible', '>', 0)
                    ->sum('cantidad_disponible');
                $reservado = (float) DB::table('prefactura_has_producto as php')
                    ->join('prefactura as pf', 'pf.id', '=', 'php.prefactura_id')
                    ->where('pf.estado', 'activo')
                    ->where('php.producto_id', $prod->producto_id)
                    ->where('php.seccion_id',  $prod->seccion_id)
                    ->where('php.resta_inventario', 1)
                    ->sum('php.cantidad');
                $disponible = max(0.0, $rawStock - $reservado);

                if ($disponible < $prod->cantidad) {
                    $stockErrors[] = [
                        'producto'   => $prod->nombre_producto,
                        'solicitado' => (int) $prod->cantidad,
                        'disponible' => (int) $disponible,
                    ];
                }
            }
}

        if (!empty($stockErrors)) {
            return response()->json([
                'icon'         => 'error',
                'title'        => 'Stock insuficiente',
                'stock_errors' => $stockErrors,
            ], 422);
        }

        // ── 2. Si no viene flujo_id, buscarlo vía historico_flujo ─────────
        if (!$flujoId) {
            $flujoId = DB::table('historico_flujo')
                ->where('tipo_tramite_id', 2)
                ->where('tramite_id', $cotizacionId)
                ->value('flujo_id');
        }

        // ── 3. Calcular fecha vencimiento ──────────────────────────────────
        $config      = DB::table('configuracion_prefactura')->first();
        $diasValidez = $config ? (int) $config->dias_validez : 7;
        $fechaEmision     = now()->toDateString();
        $fechaVencimiento = \Carbon\Carbon::parse($fechaEmision)->addDays($diasValidez)->toDateString();

        DB::beginTransaction();
        try {
            // ── 4. Crear prefactura ────────────────────────────────────────
            $prefacturaId = DB::table('prefactura')->insertGetId([
                'cotizacion_id'       => $cotizacionId,
                'flujo_id'            => $flujoId,
                'cliente_id'          => $cotizacion->cliente_id,
                'nombre_cliente'      => $cotizacion->nombre_cliente,
                'RTN'                 => $cotizacion->RTN,
                'fecha_emision'       => $fechaEmision,
                'fecha_vencimiento'   => $fechaVencimiento,
                'sub_total'           => $cotizacion->sub_total,
                'sub_total_grabado'   => $cotizacion->sub_total_grabado,
                'sub_total_excento'   => $cotizacion->sub_total_excento,
                'isv'                 => $cotizacion->isv,
                'total'               => $cotizacion->total,
                'porc_descuento'      => $cotizacion->porc_descuento ?? 0,
                'monto_descuento'     => $cotizacion->monto_descuento ?? 0,
                'tipo_venta_id'       => $cotizacion->tipo_venta_id,
                'vendedor'            => $cotizacion->vendedor,
                'nota'                => $cotizacion->nota,
                'arregloIdInputs'     => $cotizacion->arregloIdInputs,
                'numeroInputs'        => $cotizacion->numeroInputs,
                'estado'              => 'activo',
                'users_id'            => Auth::id(),
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            // ── 5. Insertar productos ──────────────────────────────────────
            $prefProds = [];
            foreach ($productos as $prod) {
                $prefProds[] = [
                    'prefactura_id'           => $prefacturaId,
                    'producto_id'             => $prod->producto_id,
                    'indice'                  => $prod->indice,
                    'nombre_producto'         => $prod->nombre_producto,
                    'nombre_bodega'           => $prod->nombre_bodega,
                    'precio_unidad'           => $prod->precio_unidad,
                    'cantidad'                => $prod->cantidad,
                    'sub_total'               => $prod->sub_total,
                    'isv'                     => $prod->isv,
                    'total'                   => $prod->total,
                    'isv_producto'            => $prod->isv_producto,
                    'Bodega_id'               => $prod->bodega_id,
                    'seccion_id'              => $prod->seccion_id,
                    'unidad_medida_venta_id'  => $prod->unidad_medida_venta_id,
                    'monto_descProducto'      => $prod->monto_descProducto ?? 0,
                    'idPrecioSeleccionado'     => $prod->idPrecioSeleccionado ?? null,
                    'precioSeleccionado'       => $prod->precioSeleccionado ?? null,
                    'precios_producto_carga_id' => $prod->precios_producto_carga_id ?? null,
                    'resta_inventario'         => $prod->resta_inventario,
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ];
            }
            if (!empty($prefProds)) {
                DB::table('prefactura_has_producto')->insert($prefProds);
            }

            // ── 6. Reservar inventario en recibido_bodega ──────────────────
            foreach ($prefProds as $pp) {
                if ($pp['resta_inventario'] && $pp['producto_id'] && $pp['seccion_id']) {
                    // Descontar de los registros con más cantidad disponible primero
                    $restante = (float) $pp['cantidad'];
                    $filas = DB::table('recibido_bodega')
                        ->where('producto_id', $pp['producto_id'])
                        ->where('seccion_id',  $pp['seccion_id'])
                        ->where('cantidad_disponible', '>', 0)
                        ->orderByDesc('cantidad_disponible')
                        ->get(['id', 'cantidad_disponible']);

                    foreach ($filas as $fila) {
                        if ($restante <= 0) break;
                        $descontar = min((float) $fila->cantidad_disponible, $restante);
                        DB::table('recibido_bodega')
                            ->where('id', $fila->id)
                            ->decrement('cantidad_disponible', $descontar);
                        $restante -= $descontar;
                    }
                }
            }

            // ── 7. Auditar cotizacion_estado como ganadora ─────────────────
            DB::table('cotizacion_estado')->insert([
                'cotizacion_id' => $cotizacionId,
                'flujo_id'      => $flujoId,
                'ganadora'      => 1,
                'comentario'    => 'Prefacturada directamente desde oferta',
                'estado_id'     => 1,
                'created_by'    => Auth::id(),
                'updated_by'    => Auth::id(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // ── 8. Actualizar observaciones en historico_flujo de la oferta ─
            DB::table('historico_flujo')
                ->where('tipo_tramite_id', 2)
                ->where('tramite_id', $cotizacionId)
                ->update(['observaciones' => 'ganadora', 'updated_at' => now()]);

            // ── 9. Insertar historico_flujo para la prefactura ─────────────
            if ($flujoId) {
                DB::table('historico_flujo')->insert([
                    'flujo_id'        => $flujoId,
                    'tipo_tramite_id' => 4, // prefactura
                    'tramite_id'      => $prefacturaId,
                    'estado_id'       => 1,
                    'observaciones'   => 'Prefactura #' . $prefacturaId . ' creada desde oferta #' . $cotizacionId,
                    'created_by'      => Auth::id(),
                    'updated_by'      => Auth::id(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                DB::table('flujo')->where('id', $flujoId)->update([
                    'tipo_tramite_id' => 4,
                    'updated_by'      => Auth::id(),
                    'updated_at'      => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'icon'        => 'success',
                'title'       => '¡Prefactura generada!',
                'idPrefactura' => $prefacturaId,
                'flujoId'     => $flujoId,
                'diasValidez' => $diasValidez,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'Error al generar prefactura: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /prefactura/{id}/tipos-facturacion
    // Returns the available billing types for the prefactura's client.
    // ─────────────────────────────────────────────────────────────────────
    public function getTiposFacturacion(int $id)
    {
        $prefactura = DB::table('prefactura')->where('id', $id)->first();
        if (!$prefactura) {
            return response()->json(['error' => 'Prefactura no encontrada.'], 404);
        }

        $clienteId     = (int) ($prefactura->cliente_id ?? 0);
        $cliente       = $clienteId ? DB::table('cliente')->where('id', $clienteId)->first() : null;
        $tipoClienteId = $cliente ? (int) $cliente->tipo_cliente_id : 0;

        $tiposVenta = [];
        if ($tipoClienteId === 1) {            // Corporativo (B)
            $tiposVenta[] = 1;
        } elseif (in_array($tipoClienteId, [2, 3])) {  // Estatal (A) / Gobierno
            $tiposVenta[] = 2;
        }
        if ($clienteId && DB::table('codigo_exoneracion')
                ->where('cliente_id', $clienteId)->where('estado_id', 1)->exists()) {
            $tiposVenta[] = 3; // Exonerada
        }
        if (empty($tiposVenta)) {
            $tiposVenta = [3]; // Fallback
        }

        $tipos = DB::table('tipo_factura')
            ->whereIn('tipo_venta_id', $tiposVenta)
            ->where('estado', 1)
            ->orderBy('orden')
            ->get(['id', 'nombre', 'codigo', 'ruta_menu', 'tipo_venta_id'])
            ->toArray();

        return response()->json(['tipos' => $tipos]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /prefactura/{id}/facturar
    // Registers the prefactura → factura transition and returns the same
    // billing destination used by flujo (first active tipo_factura except cotizacion).
    // ─────────────────────────────────────────────────────────────────────
    public function registrarFacturacion(Request $request, int $id)
    {
        $prefactura = DB::table('prefactura')->where('id', $id)->where('estado', 'activo')->first();
        if (!$prefactura) {
            return response()->json(['error' => 'Prefactura no encontrada o inactiva.'], 404);
        }

        $tipoFactura = DB::table('tipo_factura')
            ->where('estado', 1)
            ->where('codigo', '!=', 'cotizacion_clientes_a')
            ->orderBy('orden')
            ->first(['ruta_menu']);

        if (!$tipoFactura) {
            return response()->json(['error' => 'No hay tipos de facturación disponibles.'], 422);
        }

        $flujoId = (int) ($prefactura->flujo_id ?? 0);

        DB::beginTransaction();
        try {
            if ($flujoId) {
                DB::table('historico_flujo')->insert([
                    'flujo_id'        => $flujoId,
                    'tipo_tramite_id' => 3,
                    'tramite_id'      => $id,
                    'estado_id'       => 1,
                    'observaciones'   => 'Facturación iniciada desde prefactura #' . $id,
                    'created_by'      => Auth::id(),
                    'updated_by'      => Auth::id(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                DB::table('flujo')->where('id', $flujoId)->update([
                    'tipo_tramite_id' => 3,
                    'updated_by'      => Auth::id(),
                    'updated_at'      => now(),
                ]);
            }

            DB::commit();

            $url = '/' . ltrim($tipoFactura->ruta_menu, '/')
                . '?from=prefactura&prefactura_id=' . $id
                . ($flujoId ? '&flujoId=' . $flujoId : '');
            return response()->json(['url' => $url]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /flujo/{id}/pedido-id
    // Returns the pedido_id associated with a flujo (if any).
    // ─────────────────────────────────────────────────────────────────────
    public function getPedidoIdByFlujo(int $id)
    {
        $flujo = DB::table('flujo')->where('id', $id)->first();
        if (!$flujo) {
            return response()->json(['pedido_id' => null]);
        }
        $pedidoId = null;
        if (is_numeric($flujo->identificacion)) {
            $pedidoId = DB::table('pedido')->where('id', (int) $flujo->identificacion)->value('id');
        }
        return response()->json(['pedido_id' => $pedidoId]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /prefactura/{id}/facturar-directo
    // Crea la factura directamente desde los datos de la prefactura,
    // sin redirigir al formulario de edición.
    // Body esperado: tipo_pago (1=contado, 2=crédito), autorizacion_id (opcional), motivo (opcional)
    // ─────────────────────────────────────────────────────────────────────
    public function facturarDirectamente(Request $request, int $id)
    {
        $pf = DB::table('prefactura')->where('id', $id)->where('estado', 'activo')->first();
        if (!$pf) {
            return response()->json(['error' => 'Prefactura no encontrada o inactiva.'], 404);
        }

        $tipoPago = (int) ($request->tipo_pago ?? 1);

        // ── Calcular fecha_vencimiento ────────────────────────────────────
        $fechaEmision = now()->toDateString();
        if ($tipoPago === 2 && $pf->cliente_id) {
            $diasCredito = DB::table('cliente')->where('id', $pf->cliente_id)->value('dias_credito') ?? 0;
        } else {
            $diasCredito = 0;
        }
        $fechaVencimiento = \Carbon\Carbon::parse($fechaEmision)->addDays($diasCredito)->toDateString();

        // ── Obtener productos de la prefactura ────────────────────────────
        $productos = DB::table('prefactura_has_producto')
            ->where('prefactura_id', $id)
            ->get();

        if ($productos->isEmpty()) {
            return response()->json(['error' => 'La prefactura no tiene productos.'], 422);
        }

        // ── Construir índices y datos por producto ────────────────────────
        $indicesArr = [];
        $productoData = [];
        foreach ($productos as $prod) {
            $idx     = $prod->indice;
            $unidad  = 1;
            if ($prod->unidad_medida_venta_id) {
                $unidad = DB::table('unidad_medida_venta')
                    ->where('id', $prod->unidad_medida_venta_id)
                    ->value('unidad_venta') ?? 1;
            }
            // restaInventario = cantidad en unidades base a descontar del inventario
            $restaInv = $prod->resta_inventario ? ($prod->cantidad * $unidad) : 0;

            $indicesArr[] = $idx;
            $productoData["idSeccion{$idx}"]               = $prod->seccion_id;
            $productoData["idProducto{$idx}"]              = $prod->producto_id;
            $productoData["restaInventario{$idx}"]         = $restaInv;
            $productoData["nombre{$idx}"]                  = $prod->nombre_producto;
            $productoData["bodega{$idx}"]                  = $prod->nombre_bodega;
            $productoData["precio{$idx}"]                  = $prod->precio_unidad;
            $productoData["cantidad{$idx}"]                = $prod->cantidad;
            $productoData["subTotal{$idx}"]                = $prod->sub_total;
            $productoData["isvProducto{$idx}"]             = $prod->isv;
            $productoData["total{$idx}"]                   = $prod->total;
            $productoData["isv{$idx}"]                     = $prod->isv_producto;
            $productoData["unidad{$idx}"]                  = $unidad;
            $productoData["idPrecioSeleccionado{$idx}"]    = $prod->idPrecioSeleccionado;
            $productoData["precios{$idx}"]                 = $prod->precioSeleccionado;
            $productoData["precios_producto_carga_id{$idx}"] = $prod->precios_producto_carga_id;
            $productoData["idUnidadVenta{$idx}"]           = $prod->unidad_medida_venta_id;
        }

        // ── Obtener pedido_id vinculado al flujo ──────────────────────────
        $pedidoId = null;
        if ($pf->flujo_id) {
            $flujo = DB::table('flujo')->where('id', $pf->flujo_id)->first();
            if ($flujo && is_numeric($flujo->identificacion)) {
                $pedidoId = (int) $flujo->identificacion;
            }
        }

        // ── Construir Request sintético para guardarVenta ─────────────────
        $syntheticData = array_merge([
            'fecha_emision'            => $fechaEmision,
            'fecha_vencimiento'        => $fechaVencimiento,
            'subTotalGeneralGrabado'   => $pf->sub_total_grabado,
            'subTotalGeneral'          => $pf->sub_total,
            'subTotalGeneralExcento'   => $pf->sub_total_excento ?? 0,
            'isvGeneral'               => $pf->isv,
            'totalGeneral'             => $pf->total,
            'numeroInputs'             => $pf->numeroInputs,
            'arregloIdInputs'          => implode(',', $indicesArr),
            'seleccionarCliente'       => $pf->cliente_id,
            'nombre_cliente_ventas'    => $pf->nombre_cliente,
            'rtn_ventas'               => $pf->RTN,
            'tipoPagoVenta'            => $tipoPago,
            'restriccion'              => 0,  // sin restricción de facturas vencidas en flujo directo
            'vendedor'                 => $pf->vendedor,
            'porDescuento'             => $pf->porc_descuento ?? 0,
            'porDescuentoCalculado'    => $pf->monto_descuento ?? 0,
            'nota_comen'               => $pf->nota,
            'codigo_autorizacion'      => null,
            'idComprobante'            => null,
            'ordenCompra'              => null,
            'pedido_id'                => $pedidoId,
            'flujo_id'                 => $pf->flujo_id,
        ], $productoData);

        $syntheticRequest = Request::create(
            '/prefactura/' . $id . '/facturar-directo',
            'POST',
            $syntheticData
        );
        // Propagar autenticación y sesión al request sintético
        $syntheticRequest->setUserResolver(fn() => Auth::user());
        $syntheticRequest->cookies->set(
            config('session.cookie'),
            $request->cookie(config('session.cookie'))
        );

        // ── Llamar guardarVenta del controlador existente ─────────────────
        // Marcar la prefactura como 'procesando' para excluirla del stock reservado
        // durante el check de stock en guardarVenta (evita double-counting).
        DB::table('prefactura')->where('id', $id)->update(['estado' => 'procesando']);

        $corp = app()->make(FacturacionCorporativa::class);
        $corp->arrayProductos = [];
        $corp->arrayLogs      = [];

        $response = $corp->guardarVenta($syntheticRequest);
        $payload  = json_decode($response->getContent(), true);

        if ($response->getStatusCode() >= 400 || ($payload['icon'] ?? '') === 'error') {
            // Revertir estado si falló
            DB::table('prefactura')->where('id', $id)->update(['estado' => 'activo']);
            return response()->json([
                'error'  => $payload['text'] ?? $payload['error'] ?? 'Error al facturar.',
                'detail' => $payload,
            ], 422);
        }

        // Advertencia de stock u otro aviso de negocios (guardarVenta devuelve 200 con icon=warning)
        if (($payload['icon'] ?? '') === 'warning') {
            // Revertir estado si no pasó el check
            DB::table('prefactura')->where('id', $id)->update(['estado' => 'activo']);
            return response()->json([
                'icon'    => 'warning',
                'title'   => '¡Advertencia!',
                'warning' => $payload['text'] ?? 'No fue posible completar la facturación.',
                'detail'  => $payload,
            ], 422);
        }

        $facturaId = (int) ($payload['idFactura'] ?? 0);

        // ── Actualizar prefactura a 'convertida' ──────────────────────────
        DB::table('prefactura')
            ->where('id', $id)
            ->update(['estado' => 'convertida', 'updated_at' => now()]);

        // ── Registrar el flujo (equivalente a confirmarFacturaFlujo) ──────
        $flujoId = (int) ($pf->flujo_id ?? 0);
        if ($flujoId && $facturaId) {
            $TIPO_FACTURA  = 3;
            $TIPO_ENTREGA  = 5;
            $TIPO_COBRO    = 6;
            $TIPO_CONJUNTO = 7;
            $ESTADO_ACTIVO = 1;
            $ESTADO_PEND   = 5;

            DB::table('flujo')->where('id', $flujoId)->update([
                'tipo_tramite_id' => $TIPO_CONJUNTO,
                'updated_by'      => Auth::id(),
                'updated_at'      => now(),
            ]);

            $existeFactura = DB::table('historico_flujo')
                ->where('flujo_id', $flujoId)
                ->where('tipo_tramite_id', $TIPO_FACTURA)
                ->where('estado_id', '!=', 7)
                ->orderByDesc('id')
                ->first(['id', 'tramite_id']);

            if ($existeFactura) {
                DB::table('historico_flujo')->where('id', $existeFactura->id)->update([
                    'tramite_id'    => $facturaId,
                    'observaciones' => 'Factura #' . $facturaId . ' generada directamente desde prefactura #' . $id,
                    'updated_by'    => Auth::id(),
                    'updated_at'    => now(),
                ]);
            } else {
                DB::table('historico_flujo')->insert([
                    'flujo_id'        => $flujoId,
                    'tipo_tramite_id' => $TIPO_FACTURA,
                    'tramite_id'      => $facturaId,
                    'estado_id'       => $ESTADO_ACTIVO,
                    'observaciones'   => 'Factura #' . $facturaId . ' generada directamente desde prefactura #' . $id,
                    'created_by'      => Auth::id(),
                    'updated_by'      => Auth::id(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            // Entrega pendiente
            $entregaExiste = DB::table('historico_flujo')
                ->where('flujo_id', $flujoId)->where('tipo_tramite_id', $TIPO_ENTREGA)
                ->whereNull('tramite_id')->where('estado_id', $ESTADO_PEND)->exists();
            if (!$entregaExiste) {
                DB::table('historico_flujo')->insert([
                    'flujo_id'        => $flujoId,
                    'tipo_tramite_id' => $TIPO_ENTREGA,
                    'tramite_id'      => null,
                    'estado_id'       => $ESTADO_PEND,
                    'observaciones'   => 'Entrega pendiente — Factura #' . $facturaId,
                    'created_by'      => Auth::id(),
                    'updated_by'      => Auth::id(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            // Cobro pendiente
            $aplicacionPagoId = DB::table('aplicacion_pagos')
                ->where('factura_id', $facturaId)->orderByDesc('id')->value('id');
            $cobroPendiente = DB::table('historico_flujo')
                ->where('flujo_id', $flujoId)->where('tipo_tramite_id', $TIPO_COBRO)
                ->where('estado_id', $ESTADO_PEND)->orderByDesc('id')->first(['id', 'tramite_id']);
            if ($cobroPendiente) {
                if (empty($cobroPendiente->tramite_id) && $aplicacionPagoId) {
                    DB::table('historico_flujo')->where('id', $cobroPendiente->id)->update([
                        'tramite_id' => $aplicacionPagoId, 'updated_by' => Auth::id(), 'updated_at' => now(),
                    ]);
                }
            } else {
                DB::table('historico_flujo')->insert([
                    'flujo_id'        => $flujoId,
                    'tipo_tramite_id' => $TIPO_COBRO,
                    'tramite_id'      => $aplicacionPagoId,
                    'estado_id'       => $ESTADO_PEND,
                    'observaciones'   => 'Cobro pendiente — Factura #' . $facturaId,
                    'created_by'      => Auth::id(),
                    'updated_by'      => Auth::id(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        // ── Auditoría ─────────────────────────────────────────────────────
        $autorizacionId = $request->input('autorizacion_id') ? (int) $request->input('autorizacion_id') : null;
        PrefacturaAuditoria::registrar(
            'facturacion_directa',
            $id,
            $facturaId,
            ['prefactura_id' => $id, 'estado' => 'activo', 'total' => $pf->total],
            ['factura_id' => $facturaId, 'tipo_pago' => $tipoPago],
            $request->input('motivo'),
            $autorizacionId
        );

        return response()->json([
            'icon'       => 'success',
            'title'      => '¡Facturado!',
            'text'       => 'Factura #' . $facturaId . ' generada correctamente.',
            'factura_id' => $facturaId,
            'print_url'  => '/factura/cooporativo/' . $facturaId,
        ], 200);
    }
}
