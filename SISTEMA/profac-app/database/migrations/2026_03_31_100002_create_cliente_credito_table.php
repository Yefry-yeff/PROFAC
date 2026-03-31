<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteCreditoTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cliente_credito')) { return; }
        Schema::create('cliente_credito', function (Blueprint $table) {
            $table->id();
            $table->integer('cliente_id');
            $table->tinyInteger('credito_activo')->default(0)->comment('1=crédito habilitado, 0=deshabilitado');
            $table->decimal('credito', 12, 2)->default(0);
            $table->integer('dias_credito')->default(0);
            $table->integer('vendedor_id')->nullable();
            $table->text('referencias_bancarias')->nullable();
            $table->text('referencias_comerciales')->nullable();
            $table->string('metodo_pago', 100)->nullable();
            $table->tinyInteger('letra_cambio')->default(0);
            $table->tinyInteger('aval_solidario')->default(0);
            $table->text('autorizacion_gerencia')->nullable()->comment('Comentario de gerencia en cada modificación');
            $table->integer('users_id');
            $table->timestamps();

            $table->index('cliente_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente_credito');
    }
}
