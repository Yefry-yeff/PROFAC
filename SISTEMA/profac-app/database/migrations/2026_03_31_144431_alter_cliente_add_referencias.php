<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cliente', function (Blueprint $table) {
            if (!Schema::hasColumn('cliente', 'ref_referencias')) {
                $table->text('ref_referencias')->nullable();
            }
            if (!Schema::hasColumn('cliente', 'ref_tiempo_relacion')) {
                $table->string('ref_tiempo_relacion', 100)->nullable();
            }
            if (!Schema::hasColumn('cliente', 'ref_tiempo_credito')) {
                $table->string('ref_tiempo_credito', 100)->nullable();
            }
            if (!Schema::hasColumn('cliente', 'ref_limite_credito')) {
                $table->decimal('ref_limite_credito', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('cliente', 'ref_observaciones')) {
                $table->text('ref_observaciones')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('cliente', function (Blueprint $table) {
            $table->dropColumn(['ref_referencias','ref_tiempo_relacion','ref_tiempo_credito','ref_limite_credito','ref_observaciones']);
        });
    }
};
