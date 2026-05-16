<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteDocumentosTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cliente_documentos')) { return; }
        Schema::create('cliente_documentos', function (Blueprint $table) {
            $table->id();
            $table->integer('cliente_id');
            $table->string('tipo_documento', 60);
            $table->string('nombre_original', 255);
            $table->string('ruta_archivo', 500);
            $table->integer('users_id');
            $table->timestamps();

            $table->index('cliente_id');
            $table->unique(['cliente_id', 'tipo_documento']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente_documentos');
    }
}
