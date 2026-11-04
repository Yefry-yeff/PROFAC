<?php

namespace App\Services\Expo;

use App\Models\CreditoRevision;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DevolverSeccionAnulada
{
    public function porFactura(int $facturaId): void
    {
        $secciones = DB::table('expo_oferta_seccion as eos')
            ->where('eos.estado', 'FACTURADA')
            ->where(function ($query) use ($facturaId) {
                $query->whereIn('eos.prefactura_id', function ($auditoria) use ($facturaId) {
                    $auditoria->select('prefactura_id')
                        ->from('prefactura_auditoria')
                        ->where('factura_id', $facturaId)
                        ->where('tipo_accion', 'facturacion_directa');
                })->orWhereIn('eos.cotizacion_id', function ($lineas) use ($facturaId) {
                    $lineas->select('chp.cotizacion_id')
                        ->from('venta_has_producto as vhp')
                        ->join('cotizacion_has_producto as chp', 'chp.id', '=', 'vhp.cotizacion_has_producto_id')
                        ->where('vhp.factura_id', $facturaId);
                });
            })
            ->lockForUpdate()
            ->get();

        foreach ($secciones as $seccion) {
            $this->devolver($seccion, 'factura', $facturaId);
        }
    }

    public function porPrefactura(int $flujoId, int $prefacturaId): bool
    {
        $seccion = DB::table('expo_oferta_seccion')
            ->where('flujo_id', $flujoId)
            ->where('prefactura_id', $prefacturaId)
            ->lockForUpdate()
            ->first();

        if (!$seccion) {
            return false;
        }

        $this->devolver($seccion, 'prefactura', $prefacturaId);

        return true;
    }

    private function devolver(object $seccion, string $documento, int $documentoId): void
    {
        $referencia = $documento . ' Expo #' . $documentoId;
        $flujoId = (int) $seccion->flujo_id;
        $cotizacionId = (int) $seccion->cotizacion_id;

        DB::table('prefactura')->where('id', $seccion->prefactura_id)
            ->update(['estado' => 'inactive', 'updated_at' => now()]);

        DB::table('expo_oferta_seccion')->where('id', $seccion->id)->update([
            'estado' => 'DEVUELTA_SECCION',
            'prefactura_id' => null,
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        DB::table('historico_flujo')
            ->where('flujo_id', $flujoId)
            ->where(function ($query) use ($cotizacionId, $seccion) {
                $query->where(function ($revision) use ($cotizacionId) {
                    $revision->whereIn('tipo_tramite_id', [9, 10])->where('tramite_id', $cotizacionId);
                })->orWhere(function ($prefactura) use ($seccion) {
                    $prefactura->where('tipo_tramite_id', 4)->where('tramite_id', $seccion->prefactura_id);
                });
            })
            ->where('estado_id', '!=', 7)
            ->update([
                'estado_id' => 7,
                'observaciones' => 'Ciclo cerrado por anulación de ' . $referencia . '.',
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

        $revisionCredito = CreditoRevision::paraSeccion($flujoId, $cotizacionId);
        if ($revisionCredito) {
            $estadoAnterior = $revisionCredito->estado;
            $revisionCredito->update([
                'estado' => CreditoRevision::CANCELADO,
                'observaciones' => 'Revisión cancelada por anulación de ' . $referencia . '.',
                'usuario_revision' => Auth::id(),
                'ip_revision' => request()->ip(),
            ]);
            $revisionCredito->registrarHistorial(
                $documento . '_anulada',
                $estadoAnterior,
                CreditoRevision::CANCELADO,
                'La sección debe iniciar nuevamente desde Revisión de Crédito.',
                request()->ip()
            );
        }

        $seccionesPendientes = DB::table('historico_flujo')
            ->where('flujo_id', $flujoId)
            ->where('tipo_tramite_id', 11)
            ->where('tramite_id', $seccion->cotizacion_origen_id)
            ->where('estado_id', 5)
            ->exists();

        if (!$seccionesPendientes) {
            DB::table('historico_flujo')->insert([
                'flujo_id' => $flujoId,
                'tipo_tramite_id' => 11,
                'tramite_id' => $seccion->cotizacion_origen_id,
                'estado_id' => 5,
                'observaciones' => 'Sección #' . $seccion->numero . ' devuelta para edición por anulación de ' . $referencia . '.',
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('flujo')->where('id', $flujoId)->update([
            'tipo_tramite_id' => 11,
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);
    }
}
