<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class ExpoStock
{
    public const BODEGA_VIRTUAL_ID = 0;
    public const NOMBRE_VIRTUAL = 'EXPO - DISPONIBLE AGRUPADO';

    public static function ubicacionVirtual(): array
    {
        $ubicacion = DB::table('seccion as s')
            ->join('segmento as sg', 'sg.id', '=', 's.segmento_id')
            ->join('bodega as b', 'b.id', '=', 'sg.bodega_id')
            ->where('b.id', self::BODEGA_VIRTUAL_ID)
            ->where('b.nombre', self::NOMBRE_VIRTUAL)
            ->where('sg.descripcion', self::NOMBRE_VIRTUAL)
            ->where('s.descripcion', self::NOMBRE_VIRTUAL)
            ->first(['s.id as seccion_id']);

        if (!$ubicacion) {
            throw new \RuntimeException('No existe la bodega virtual de Expo. Ejecute las migraciones.');
        }

        return [
            'bodega_id' => self::BODEGA_VIRTUAL_ID,
            'seccion_id' => (int) $ubicacion->seccion_id,
            'nombre_bodega' => self::NOMBRE_VIRTUAL,
        ];
    }

    public static function disponible(int $productoId, array $bodegaIds): float
    {
        return self::resumen($productoId, $bodegaIds)['disponible'];
    }

    public static function resumen(int $productoId, array $bodegaIds): array
    {
        $bodegaIds = array_values(array_unique(array_filter(array_map('intval', $bodegaIds), fn ($id) => $id > 0)));
        if ($productoId <= 0 || !$bodegaIds) {
            return ['existencia' => 0.0, 'reservado' => 0.0, 'disponible' => 0.0];
        }

        $existencia = (float) DB::table('recibido_bodega as rb')
            ->join('seccion as s', 's.id', '=', 'rb.seccion_id')
            ->join('segmento as sg', 'sg.id', '=', 's.segmento_id')
            ->where('rb.producto_id', $productoId)
            ->whereIn('sg.bodega_id', $bodegaIds)
            ->sum('rb.cantidad_disponible');

        $reservado = (float) DB::table('prefactura_has_producto as php')
            ->join('prefactura as pf', 'pf.id', '=', 'php.prefactura_id')
            ->join('seccion as s', 's.id', '=', 'php.seccion_id')
            ->join('segmento as sg', 'sg.id', '=', 's.segmento_id')
            ->where('pf.estado', 'activo')
            ->whereRaw("TIMESTAMPADD(DAY, COALESCE((SELECT cp.dias_validez FROM configuracion_prefactura cp ORDER BY cp.id DESC LIMIT 1), 7), COALESCE(pf.created_at, CONCAT(COALESCE(pf.fecha_emision, CURDATE()), ' 00:00:00'))) > NOW()")
            ->where('php.producto_id', $productoId)
            ->where('php.resta_inventario', 1)
            ->whereIn('sg.bodega_id', $bodegaIds)
            ->sum('php.cantidad');

        return [
            'existencia' => $existencia,
            'reservado' => $reservado,
            'disponible' => max(0.0, $existencia - $reservado),
        ];
    }

    public static function opcion(int $productoId, array $bodegaIds): ?array
    {
        $disponible = self::disponible($productoId, $bodegaIds);
        $ubicacion = self::ubicacionVirtual();

        return [
            'id' => $ubicacion['seccion_id'],
            'idBodega' => $ubicacion['bodega_id'],
            'bodegaSeccion' => $ubicacion['nombre_bodega'],
            'text' => $ubicacion['nombre_bodega'] . ' - cantidad ' . floor($disponible),
            'disponible' => $disponible,
            'esSinExistencia' => 0,
        ];
    }
}