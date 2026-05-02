<?php

namespace App\Services\Comisiones;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Comisiones\Escalado\modelproducto_comision;
use App\Models\Comisiones\Escalado\modelfacturas_comision;
use App\Models\Comisiones\Escalado\modelcomision_empleado;
use App\Models\Comisiones\Escalado\modelcomision_escala;

class GeneradorFacturasComision
{
    public function generar(
        int $facturaId,
        int $aplicacionPagoId,
        int $categoriaClienteId
    ): array {

        // Prevenir doble registro: si ya existen comisiones para esta factura, no procesar
        $yaProcessado = modelfacturas_comision::where('factura_id', $facturaId)->exists();
        if ($yaProcessado) {
            return [];
        }

        // Solo televendedor (rol 3) y asesor comercial (rol 2)
        $parametros = DB::table('comision_escala')
            ->where('estado_id', 1)
            ->where('cliente_categoria_escala_id', $categoriaClienteId)
            ->whereIn('rol_id', [2, 3])
            ->get();

        // Si no hay parámetros configurados para esta categoría de cliente, no hacer nada
        if ($parametros->isEmpty()) {
            return [];
        }

        $productos = DB::table('venta_has_producto')
            ->where('factura_id', $facturaId)
            ->get();

        if ($productos->isEmpty()) {
            return [];
        }

        [$productosComision, $facturasComision] =
            $this->calcularComisiones($parametros, $productos, $facturaId, $aplicacionPagoId);

        if (!empty($productosComision)) {
            modelproducto_comision::insert($productosComision);
        }
        if (!empty($facturasComision)) {
            modelfacturas_comision::insert($facturasComision);
        }

        return $facturasComision;
    }

    /* ================= LÓGICA ================= */

    protected function calcularComisiones($parametros, $productos, $facturaId, $aplicacionPagoId): array
    {
        $productosComision = [];
        $facturasComision = [];
        $totalesPorRol = [];

        foreach ($parametros as $param) {
            $totalesPorRol[$param->rol_id] = 0;

            foreach ($productos as $producto) {

                $montoUnitario = ($param->porcentaje_comision / 100) * $producto->precio_unidad;
                $totalProducto = $montoUnitario * $producto->cantidad;

                $productosComision[] = [
                    'cantidad' => $producto->cantidad,
                    'precio_venta' => $producto->precio_unidad,
                    'monto_comision' => $montoUnitario,
                    'precios_producto_carga_id' => $producto->precios_producto_carga_id,
                    'factura_id' => $facturaId,
                    'producto_id' => $producto->producto_id,
                    'rol_id' => $param->rol_id,
                    'estado_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $totalesPorRol[$param->rol_id] += $totalProducto;
            }

            $facturasComision[] = [
                'fecha_cierre_factura' => now(),
                'monto_rol' => 0, // se setea abajo
                'factura_id' => $facturaId,
                'comision_escala_id' => $param->id,
                'aplicacion_pagos_id' => $aplicacionPagoId,
                'rol_id' => $param->rol_id,
                'estado_id' => 1,
                 'created_at' => now(),
                 'updated_at' => now(),
            ];
        }

        // asignar totales por rol
        foreach ($facturasComision as &$factura) {
            $factura['monto_rol'] = $totalesPorRol[$factura['rol_id']] ?? 0;
        }

        return [$productosComision, $facturasComision];
    }
}
