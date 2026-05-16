<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddAnuladaToEstadoEntregaEnum extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE distribuciones_entrega_facturas MODIFY COLUMN estado_entrega ENUM('sin_entrega','parcial','entregado','anulada') NOT NULL DEFAULT 'sin_entrega'");
    }

    public function down()
    {
        // Revert any 'anulada' rows to 'sin_entrega' before removing the enum value
        DB::statement("UPDATE distribuciones_entrega_facturas SET estado_entrega = 'sin_entrega' WHERE estado_entrega = 'anulada'");
        DB::statement("ALTER TABLE distribuciones_entrega_facturas MODIFY COLUMN estado_entrega ENUM('sin_entrega','parcial','entregado') NOT NULL DEFAULT 'sin_entrega'");
    }
}
