<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const NOMBRE = 'EXPO - DISPONIBLE AGRUPADO';

    public function up(): void
    {
        if (!DB::table('bodega')->where('id', 0)->exists()) {
            $modoAnterior = (string) DB::selectOne('SELECT @@SESSION.sql_mode AS modo')->modo;
            $modos = array_filter(explode(',', $modoAnterior));
            if (!in_array('NO_AUTO_VALUE_ON_ZERO', $modos, true)) {
                $modos[] = 'NO_AUTO_VALUE_ON_ZERO';
            }

            DB::statement('SET SESSION sql_mode = ?', [implode(',', $modos)]);
            try {
                DB::table('bodega')->insert([
                    'id' => 0,
                    'nombre' => self::NOMBRE,
                    'direccion' => 'Ubicacion virtual para ofertas Expo',
                    'estado_id' => DB::table('estado')->orderBy('id')->value('id'),
                    'municipio_id' => DB::table('municipio')->orderBy('id')->value('id'),
                    'encargado_bodega' => DB::table('users')->orderBy('id')->value('id'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } finally {
                DB::statement('SET SESSION sql_mode = ?', [$modoAnterior]);
            }
        }

        $segmentoId = DB::table('segmento')
            ->where('bodega_id', 0)
            ->where('descripcion', self::NOMBRE)
            ->value('id');
        if (!$segmentoId) {
            $segmentoId = DB::table('segmento')->insertGetId([
                'descripcion' => self::NOMBRE,
                'bodega_id' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!DB::table('seccion')->where('segmento_id', $segmentoId)->where('descripcion', self::NOMBRE)->exists()) {
            DB::table('seccion')->insert([
                'descripcion' => self::NOMBRE,
                'numeracion' => 0,
                'estado_id' => DB::table('estado')->orderBy('id')->value('id'),
                'segmento_id' => $segmentoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $segmentos = DB::table('segmento')->where('bodega_id', 0)->where('descripcion', self::NOMBRE)->pluck('id');
        DB::table('seccion')->whereIn('segmento_id', $segmentos)->where('descripcion', self::NOMBRE)->delete();
        DB::table('segmento')->whereIn('id', $segmentos)->delete();
        DB::table('bodega')->where('id', 0)->where('nombre', self::NOMBRE)->delete();
    }
};