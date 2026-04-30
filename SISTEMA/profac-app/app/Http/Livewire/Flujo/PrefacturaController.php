<?php

namespace App\Http\Livewire\Flujo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Luecano\NumeroALetras\NumeroALetras;
use PDF;

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
}
