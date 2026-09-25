<?php

namespace App\Support\Logistica;

use App\Models\Logistica\FacturaTratamientoEntrega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Guarda la sección "Envío" (zona + dirección) capturada en el modal
 * "Actores de la Factura" / "Gestor de Entrega", reutilizando la MISMA tabla
 * que usa "Gestión de Facturas" al tratar facturas (factura_tratamiento_entrega),
 * pero con la zona seleccionada directamente por el usuario en vez de
 * derivarla de departamento/municipio.
 */
class FacturaEnvioHelper
{
    public static function guardar(Request $request, int $facturaId, $gestorEntregaId = null): void
    {
        $zoneGroupId = $request->input('zone_group_id');
        if (!$zoneGroupId || $facturaId <= 0) {
            return;
        }

        FacturaTratamientoEntrega::updateOrCreate(
            ['factura_id' => $facturaId],
            [
                'zone_group_id' => (int) $zoneGroupId,
                'direccion_entrega' => $request->input('direccion_entrega') ?: null,
                'gestor_entrega_id' => $gestorEntregaId ?: null,
                'usr_registro' => Auth::id(),
                'usr_actualizo' => Auth::id(),
            ]
        );
    }
}
