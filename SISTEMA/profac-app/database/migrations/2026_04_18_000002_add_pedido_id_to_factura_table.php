<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPedidoIdToFacturaTable extends Migration
{
    public function up()
    {
        Schema::table('factura', function (Blueprint $table) {
            $table->unsignedBigInteger('pedido_id')->nullable()->after('comprovante_entrega_id');
        });
    }

    public function down()
    {
        Schema::table('factura', function (Blueprint $table) {
            $table->dropColumn('pedido_id');
        });
    }
}
