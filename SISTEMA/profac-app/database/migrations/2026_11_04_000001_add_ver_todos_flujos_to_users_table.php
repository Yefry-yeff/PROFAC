<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'ver_todos_flujos')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('ver_todos_flujos')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'ver_todos_flujos')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('ver_todos_flujos');
            });
        }
    }
};
