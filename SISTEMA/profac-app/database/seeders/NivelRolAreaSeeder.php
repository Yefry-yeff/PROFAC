<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NivelRolAreaSeeder extends Seeder
{
    public function run(): void
    {
        // ── Niveles jerárquicos ──────────────────────────────────────────
        // orden menor = mayor jerarquía. Escalable: se puede agregar N niveles.
        $niveles = [
            ['nombre' => 'Gerente General',     'descripcion' => 'Máxima autoridad de la empresa',          'orden' => 1, 'estado_id' => 1],
            ['nombre' => 'Jefe de Departamento', 'descripcion' => 'Responsable de un área o departamento',  'orden' => 2, 'estado_id' => 1],
            ['nombre' => 'Supervisor',            'descripcion' => 'Supervisión de equipos operativos',       'orden' => 3, 'estado_id' => 1],
            ['nombre' => 'Colaborador',           'descripcion' => 'Empleado operativo de área',              'orden' => 4, 'estado_id' => 1],
        ];

        foreach ($niveles as $nivel) {
            DB::table('nivel_rol')->updateOrInsert(
                ['nombre' => $nivel['nombre']],
                array_merge($nivel, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // ── Áreas / Departamentos ────────────────────────────────────────
        // Escalable: se agrega un área sin tocar código.
        $areas = [
            ['nombre' => 'Gerencia',         'descripcion' => 'Dirección general de la empresa',           'estado_id' => 1],
            ['nombre' => 'Ventas',            'descripcion' => 'Gestión comercial y facturación',           'estado_id' => 1],
            ['nombre' => 'Administración',    'descripcion' => 'Finanzas, contabilidad y recursos humanos', 'estado_id' => 1],
            ['nombre' => 'Logística',         'descripcion' => 'Entregas, despacho y transporte',           'estado_id' => 1],
            ['nombre' => 'Operaciones',       'descripcion' => 'Bodega, inventario y compras',              'estado_id' => 1],
            ['nombre' => 'Tecnología',        'descripcion' => 'Sistemas e infraestructura TI',             'estado_id' => 1],
            ['nombre' => 'Atención al Cliente','descripcion' => 'Servicio postventa y soporte',             'estado_id' => 1],
        ];

        foreach ($areas as $area) {
            DB::table('area')->updateOrInsert(
                ['nombre' => $area['nombre']],
                array_merge($area, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
