<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TiposFlujoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('tipos_flujo')->updateOrInsert(
            ['nombre' => 'venta'],
            ['estado' => 'activo', 'created_by' => null, 'updated_by' => null, 'created_at' => now(), 'updated_at' => now()]
        );
    }
}
