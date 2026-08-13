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

        return [
            'id' => (int) $expo->id,
            'nombre' => $expo->nombre,
            'bodegas' => DB::table('expo_bodega')->where('expo_id', $expo->id)->pluck('bodega_id')->map(fn ($id) => (int) $id)->all(),
            'escalas' => DB::table('expo_escala')->where('expo_id', $expo->id)->pluck('escala_id')->map(fn ($id) => (int) $id)->all(),
            'escalas_detalle' => DB::table('expo_escala as ee')
                ->join('categoria_precios as cp', 'cp.id', '=', 'ee.escala_id')
                ->where('ee.expo_id', $expo->id)
                ->orderBy('cp.nombre')
                ->get(['cp.id', 'cp.nombre'])
                ->map(fn ($escala) => [
                    'id' => (int) $escala->id,
                    'nombre' => $escala->nombre,
                ])->all(),
            'descuentos' => DB::table('expo_descuento')
                ->where('expo_id', $expo->id)
                ->orderBy('venta_minima')
                ->get(['venta_minima', 'porcentaje_descuento'])
                ->map(fn ($regla) => [
                    'venta_minima' => (float) $regla->venta_minima,
                    'porcentaje_descuento' => (float) $regla->porcentaje_descuento,
                ])->all(),
            'descuentos_marca' => DB::table('expo_descuento_marca as edm')
                ->join('marca as m', 'm.id', '=', 'edm.marca_id')
                ->where('edm.expo_id', $expo->id)
                ->orderBy('edm.orden')
                ->get(['edm.marca_id', 'm.nombre as marca', 'edm.venta_minima', 'edm.porcentaje_descuento', 'edm.orden'])
                ->map(fn ($regla) => [
                    'marca_id' => (int) $regla->marca_id,
                    'marca' => $regla->marca,
                    'venta_minima' => (float) $regla->venta_minima,
                    'porcentaje_descuento' => (float) $regla->porcentaje_descuento,
                    'orden' => (int) $regla->orden,
                ])->all(),
        ];
    }

    public static function detalleActivaParaUsuario(?int $expoId, ?int $usuarioId): ?array
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

        return $autorizado ? $detalle : null;
    }

    public static function tipoVentaId(): ?int
    {
        $id = DB::table('tipo_venta')
            ->whereRaw('LOWER(TRIM(descripcion)) = ?', ['expo'])
            ->value('id');

        return $id ? (int) $id : null;
    }
}