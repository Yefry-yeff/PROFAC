<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteDocFisicoTable extends Migration
{
    public function up()
    {
        Schema::create('cliente_doc_fisico', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('cliente_id');
            $table->string('tipo_documento', 60);
            $table->timestamps();

            $table->unique(['cliente_id', 'tipo_documento']);
            $table->foreign('cliente_id')->references('id')->on('cliente')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente_doc_fisico');
    }
}
