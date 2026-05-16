<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TiposEstatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $estatus = ['pedido', 'Ofertas', 'factura', 'prefactura', 'Entrega Cobro'];
        foreach ($estatus as $est) {
            \DB::table('tipos_estatus')->updateOrInsert(
                ['nombre' => $est],
                ['estado' => 'activo', 'created_by' => null, 'updated_by' => null, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
