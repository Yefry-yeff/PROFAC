<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAnuladaToDistribucionesEntregaFacturasEnum extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE distribuciones_entrega_facturas MODIFY COLUMN estado_entrega ENUM('sin_entrega','parcial','entregado','anulada') NOT NULL DEFAULT 'sin_entrega'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE distribuciones_entrega_facturas MODIFY COLUMN estado_entrega ENUM('sin_entrega','parcial','entregado') NOT NULL DEFAULT 'sin_entrega'");
    }
}
