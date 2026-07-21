<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZoneGroupAuditTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('zone_group_audit')) { return; }
        Schema::create('zone_group_audit', function (Blueprint $table) {
            $table->id();
            $table->integer('zone_group_id')->nullable();
            $table->string('action', 50); // CREATE, UPDATE, DELETE
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->integer('user_id')->nullable();
            $table->timestamps();

            $table->index('zone_group_id');
            $table->index(['action', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('zone_group_audit');
    }
}
