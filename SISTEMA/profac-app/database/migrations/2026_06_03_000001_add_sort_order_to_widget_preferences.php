<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('dashboard_widget_preferences', function (Blueprint $table) {
            $table->unsignedSmallInteger('sort_order')->nullable()->default(null)->after('visible');
        });
    }

    public function down()
    {
        Schema::table('dashboard_widget_preferences', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
