<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class SaldoCobro
{
    public function pendientes(): Builder
    {
        $cuentas = DB::table('aplicacion_pagos')->where('estado', 1)
            ->selectRaw('MAX(id) as id, factura_id')->groupBy('factura_id');
        $pagos = DB::table('pago_venta')->where('estado_venta_id', 1)
            ->selectRaw('factura_id, SUM(monto) as pagado')->groupBy('factura_id');
        $cargo = 'CASE WHEN f.tipo_venta_id = 3 THEN f.sub_total ELSE f.total END';
        $saldo = "ROUND(CASE WHEN ap.id IS NULL THEN $cargo - COALESCE(pv.pagado, 0)
            ELSE ap.saldo + $cargo - COALESCE(ap.total_factura_cargo, $cargo) END, 2)";

        return DB::table('factura as f')
            ->leftJoinSub($cuentas, 'ultima', 'ultima.factura_id', '=', 'f.id')
            ->leftJoin('aplicacion_pagos as ap', 'ap.id', '=', 'ultima.id')
            ->leftJoinSub($pagos, 'pv', 'pv.factura_id', '=', 'f.id')
            ->where('f.estado_venta_id', 1)
            ->where(fn ($q) => $q->whereNull('ap.estado_cerrado')->orWhere('ap.estado_cerrado', '<>', 2))
            ->whereRaw("$saldo > 0")
            ->select('f.id', 'f.cliente_id', 'f.nombre_cliente', 'f.cai', 'f.numero_secuencia_cai')
            ->selectRaw("$saldo as saldo");
    }

    public function opcionFactura(int $id, bool $pendiente): string
    {
        return $pendiente
            ? '<li><button type="button" class="dropdown-item" onclick="Livewire.dispatch(\'abrirCorreoCobro\', {tipo: \'factura\', id: ' . $id . '})"><i class="fa fa-envelope text-info"></i> Enviar factura por correo</button></li>'
            : '';
    }
}
