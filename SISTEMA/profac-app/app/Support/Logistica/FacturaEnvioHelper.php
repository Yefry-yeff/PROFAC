<?php

namespace App\Support\Logistica;

use App\Models\Logistica\FacturaTratamientoEntrega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Persiste los datos de envío de la factura, heredándolos de la oferta cuando
 * la factura se crea desde una prefactura.
 */
class FacturaEnvioHelper
{
    public static function guardar(Request $request, int $facturaId, $gestorEntregaId = null): void
    {
        if ($facturaId <= 0) {
            return;
        }

        $zonaSolicitada = $request->input('zone_group_id');
        $direccionSolicitada = $request->input('direccion_entrega');
        $hayEnvioExplicito = $request->exists('zone_group_id') || $request->exists('direccion_entrega');

        $registroExistente = FacturaTratamientoEntrega::where('factura_id', $facturaId)->first();
        if (!$hayEnvioExplicito && $registroExistente) {
            return;
        }

        $zoneGroupId = $zonaSolicitada;
        $direccionEntrega = $direccionSolicitada;
        $prefacturaId = (int) $request->input('prefactura_id', 0);

        if ($prefacturaId > 0 && (!$zoneGroupId || !$direccionEntrega)) {
            $envioOferta = DB::table('prefactura as pf')
                ->leftJoin('cotizacion as c', 'c.id', '=', 'pf.cotizacion_id')
                ->where('pf.id', $prefacturaId)
                ->first(['c.zone_group_id', 'c.direccion_entrega']);

            if ($envioOferta) {
                $zoneGroupId = $zoneGroupId ?: $envioOferta->zone_group_id;
                $direccionEntrega = $direccionEntrega ?: $envioOferta->direccion_entrega;
            }
        }

        if (!$zoneGroupId && !$direccionEntrega) {
            return;
        }

        FacturaTratamientoEntrega::updateOrCreate(
            ['factura_id' => $facturaId],
            [
                'zone_group_id' => $zoneGroupId ? (int) $zoneGroupId : null,
                'direccion_entrega' => $direccionEntrega ?: null,
                'gestor_entrega_id' => $gestorEntregaId ?: null,
                'usr_registro' => Auth::id(),
                'usr_actualizo' => Auth::id(),
            ]
        );
    }
}
