<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddComentarioToLogTransladoTable extends Migration
{
    public function up()
    {
        Schema::table('log_translado', function (Blueprint $table) {
            $table->text('comentario')->nullable()->after('descripcion');
        });
    }

    public function down()
    {
        Schema::table('log_translado', function (Blueprint $table) {
            $table->dropColumn('comentario');
        });
    }
}
