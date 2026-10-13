<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class FacturasFlujo
{
    public static function tieneVigentes(int $flujoId): bool
    {
        return DB::table('historico_flujo as hf')
            ->join('factura as f', 'f.id', '=', 'hf.tramite_id')
            ->where('hf.flujo_id', $flujoId)
            ->where(function ($query) {
                $query->where('hf.tipo_tramite_id', 3)
                    ->orWhere(function ($legacy) {
                        // Tipo 5 también contiene entregas; solo usarlo en flujos sin factura tipo 3.
                        $legacy->where('hf.tipo_tramite_id', 5)
                            ->whereNotExists(function ($factura) {
                                $factura->selectRaw('1')
                                    ->from('historico_flujo as hf_factura')
                                    ->whereColumn('hf_factura.flujo_id', 'hf.flujo_id')
                                    ->where('hf_factura.tipo_tramite_id', 3);
                            });
                    });
            })
            ->where('f.estado_venta_id', '!=', 2)
            ->exists();
    }
}
