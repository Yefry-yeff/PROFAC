<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();
            $table->string('title', 120);
            $table->string('icon', 60)->default('fa-bar-chart');
            $table->string('color', 20)->default('#1ab394');
            $table->string('widget_type', 80);
            $table->boolean('enabled')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('config')->nullable();  // extra config like stock_minimo threshold
            $table->timestamps();
        });

        Schema::create('dashboard_widget_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('widget_id')->constrained('dashboard_widgets')->onDelete('cascade');
            $table->unsignedBigInteger('rol_id');
            $table->timestamps();
            $table->unique(['widget_id', 'rol_id']);
        });

        // ── Seed default widgets ─────────────────────────────────────────────
        $now = now();
        $widgets = [
            [
                'key'         => 'usuarios_activos',
                'title'       => 'Usuarios Activos',
                'icon'        => 'fa-users',
                'color'       => '#1ab394',
                'widget_type' => 'stat_usuarios_activos',
                'enabled'     => true,
                'sort_order'  => 10,
                'config'      => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'ventas_mes',
                'title'       => 'Ventas del Mes',
                'icon'        => 'fa-shopping-cart',
                'color'       => '#1c84c6',
                'widget_type' => 'stat_ventas_mes',
                'enabled'     => true,
                'sort_order'  => 20,
                'config'      => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'mejor_vendedor',
                'title'       => 'Mejor Vendedor (mes)',
                'icon'        => 'fa-trophy',
                'color'       => '#f8ac59',
                'widget_type' => 'stat_mejor_vendedor',
                'enabled'     => true,
                'sort_order'  => 30,
                'config'      => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'mejor_cliente',
                'title'       => 'Cliente Top (mes)',
                'icon'        => 'fa-star',
                'color'       => '#ed5565',
                'widget_type' => 'stat_mejor_cliente',
                'enabled'     => true,
                'sort_order'  => 40,
                'config'      => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'ultimas_ventas',
                'title'       => 'Últimas Ventas',
                'icon'        => 'fa-list-alt',
                'color'       => '#23c6c8',
                'widget_type' => 'tabla_ultimas_ventas',
                'enabled'     => true,
                'sort_order'  => 50,
                'config'      => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'grafico_ventas',
                'title'       => 'Gráfico Ventas (6 meses)',
                'icon'        => 'fa-line-chart',
                'color'       => '#6f42c1',
                'widget_type' => 'grafico_ventas_6m',
                'enabled'     => true,
                'sort_order'  => 60,
                'config'      => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'usuarios_roles',
                'title'       => 'Usuarios y Roles',
                'icon'        => 'fa-id-card',
                'color'       => '#2f4050',
                'widget_type' => 'tabla_usuarios_roles',
                'enabled'     => true,
                'sort_order'  => 70,
                'config'      => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'stock_bajo',
                'title'       => 'Productos con Stock Bajo',
                'icon'        => 'fa-exclamation-triangle',
                'color'       => '#e74c3c',
                'widget_type' => 'tabla_stock_bajo',
                'enabled'     => true,
                'sort_order'  => 80,
                'config'      => json_encode(['stock_minimo' => 10, 'limite' => 20]),
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        $ids = [];
        foreach ($widgets as $w) {
            DB::table('dashboard_widgets')->insert($w);
            $ids[$w['key']] = DB::getPdo()->lastInsertId();
        }

        // ── Rol IDs: 1=Administrador, 2=Asesor Comercial, 3=Televendedor
        //    4=Créditos y Cobros, 5=Aux Administrativo, 6=Aux Contable
        //    7=Auditoria y Logistica, 8=Recursos Humanos
        $rolesMap = [
            'usuarios_activos' => [],           // todos (vacío = todos)
            'ventas_mes'       => [1, 2, 3, 4, 5, 6, 7],
            'mejor_vendedor'   => [1, 2, 5, 6],
            'mejor_cliente'    => [1, 2, 4, 5, 6],
            'ultimas_ventas'   => [1, 2, 3, 4, 5, 6],
            'grafico_ventas'   => [1, 2, 5, 6],
            'usuarios_roles'   => [1, 8],
            'stock_bajo'       => [1, 5, 7],
        ];

        foreach ($rolesMap as $key => $roles) {
            foreach ($roles as $rolId) {
                DB::table('dashboard_widget_roles')->insert([
                    'widget_id'  => $ids[$key],
                    'rol_id'     => $rolId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('dashboard_widget_roles');
        Schema::dropIfExists('dashboard_widgets');
    }
};
