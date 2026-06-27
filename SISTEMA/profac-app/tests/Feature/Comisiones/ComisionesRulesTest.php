<?php

namespace Tests\Feature\Comisiones;

use App\Http\Livewire\Comisiones\Escalado\ReportesComisionesGenerales;
use App\Services\Comisiones\AplicadorRetencionesMora;
use App\Services\Comisiones\GeneradorFacturasComision;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ComisionesRulesTest extends TestCase
{
    private string $originalDatabase;
    private string $tempDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDatabase = (string) config('database.connections.mysql.database');
        $this->tempDatabase = 'copilot_comisiones_' . bin2hex(random_bytes(4));

        DB::statement("CREATE DATABASE `{$this->tempDatabase}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        config()->set('database.default', 'mysql');
        config()->set('database.connections.mysql.database', $this->tempDatabase);

        DB::purge('mysql');
        DB::reconnect('mysql');
        DB::setDefaultConnection('mysql');

        $this->createSchema();
        $this->seedBaseCatalogs();
    }

    protected function tearDown(): void
    {
        DB::disconnect('mysql');

        config()->set('database.connections.mysql.database', $this->originalDatabase);
        DB::purge('mysql');
        DB::reconnect('mysql');
        DB::statement("DROP DATABASE IF EXISTS `{$this->tempDatabase}`");

        parent::tearDown();
    }

    public function test_generador_uses_scale_for_the_invoice_client_category(): void
    {
        DB::table('users')->insert([
            ['id' => 1, 'rol_id' => 5, 'name' => 'Facturador'],
            ['id' => 2, 'rol_id' => 2, 'name' => 'Vendedor'],
        ]);

        DB::table('cliente')->insert([
            'id' => 1,
            'nombre' => 'Cliente Prueba',
            'cliente_categoria_escala_id' => 10,
        ]);

        DB::table('tipo_factura')->insert([
            'id' => 1,
            'codigo' => 'normal',
            'nombre' => 'Normal',
        ]);

        DB::table('factura')->insert([
            'id' => 1,
            'users_id' => 1,
            'vendedor' => 2,
            'cliente_id' => 1,
            'tipo_factura_id' => 1,
            'tipo_pago_id' => 1,
            'fecha_emision' => '2026-06-01',
            'fecha_vencimiento' => '2026-06-01',
            'sub_total' => 200,
            'cai' => 'FAC-001',
            'total' => 200,
        ]);

        DB::table('categoria_precios')->insert([
            'id' => 501,
            'cliente_categoria_escala_id' => 10,
            'nombre' => 'Precio F',
            'porc_precio_a' => 0,
            'estado_id' => 1,
        ]);

        DB::table('precios_producto_carga')->insert([
            'id' => 700,
            'categoria_precios_id' => 501,
            'precio_base_venta' => 100,
        ]);

        DB::table('venta_has_producto')->insert([
            'factura_id' => 1,
            'cantidad' => 2,
            'precio_unidad' => 500,
            'precioSeleccionado' => 100,
            'precios_producto_carga_id' => 700,
            'producto_id' => 1,
        ]);

        DB::table('comision_escala')->insert([
            [
                'id' => 1,
                'rol_id' => 2,
                'cliente_categoria_escala_id' => 10,
                'categoria_precios_id' => 501,
                'porcentaje_comision' => 1,
                'estado_id' => 1,
            ],
            [
                'id' => 2,
                'rol_id' => 2,
                'cliente_categoria_escala_id' => 20,
                'categoria_precios_id' => 501,
                'porcentaje_comision' => 9,
                'estado_id' => 1,
            ],
        ]);

        $resultado = app(GeneradorFacturasComision::class)->generar(1, 9001, '2026-06-26');

        $this->assertCount(1, $resultado);
        $this->assertSame(2, $resultado[0]['rol_id']);
        $this->assertEquals(2.0, $resultado[0]['monto_rol']);
        $this->assertEquals(2.0, (float) DB::table('producto_comision')->value('monto_comision'));
    }

    public function test_generador_sr_uses_lowest_price_category_for_client_scale(): void
    {
        DB::table('users')->insert([
            ['id' => 1, 'rol_id' => 5, 'name' => 'Facturador'],
            ['id' => 2, 'rol_id' => 2, 'name' => 'Vendedor'],
        ]);

        DB::table('cliente')->insert([
            'id' => 2,
            'nombre' => 'Cliente SR',
            'cliente_categoria_escala_id' => 10,
        ]);

        DB::table('tipo_factura')->insert([
            'id' => 2,
            'codigo' => 'sin_restriccion_precio',
            'nombre' => 'SR Precio',
        ]);

        DB::table('categoria_precios')->insert([
            [
                'id' => 601,
                'cliente_categoria_escala_id' => 10,
                'nombre' => 'Base',
                'porc_precio_a' => 0,
                'estado_id' => 1,
            ],
            [
                'id' => 602,
                'cliente_categoria_escala_id' => 10,
                'nombre' => 'Alta',
                'porc_precio_a' => 25,
                'estado_id' => 1,
            ],
        ]);

        DB::table('factura')->insert([
            'id' => 2,
            'users_id' => 1,
            'vendedor' => 2,
            'cliente_id' => 2,
            'tipo_factura_id' => 2,
            'tipo_pago_id' => 1,
            'fecha_emision' => '2026-06-01',
            'fecha_vencimiento' => '2026-06-01',
            'sub_total' => 100,
            'cai' => 'FAC-002',
            'total' => 100,
        ]);

        DB::table('precios_producto_carga')->insert([
            'id' => 701,
            'categoria_precios_id' => 602,
            'precio_base_venta' => 100,
        ]);

        DB::table('venta_has_producto')->insert([
            'factura_id' => 2,
            'cantidad' => 1,
            'precio_unidad' => 100,
            'precios_producto_carga_id' => 701,
            'producto_id' => 1,
        ]);

        DB::table('comision_escala')->insert([
            [
                'rol_id' => 2,
                'cliente_categoria_escala_id' => 10,
                'categoria_precios_id' => 601,
                'porcentaje_comision' => 1,
                'estado_id' => 1,
            ],
            [
                'rol_id' => 2,
                'cliente_categoria_escala_id' => 10,
                'categoria_precios_id' => 602,
                'porcentaje_comision' => 5,
                'estado_id' => 1,
            ],
        ]);

        $resultado = app(GeneradorFacturasComision::class)->generar(2, 9002, '2026-06-26');

        $this->assertCount(1, $resultado);
        $this->assertEquals(1.0, $resultado[0]['monto_rol']);
        $this->assertEquals(1.0, (float) DB::table('producto_comision')->orderByDesc('id')->value('monto_comision'));
    }

    public function test_generador_excludes_roles_disabled_for_calculation(): void
    {
        DB::table('users')->insert([
            ['id' => 1, 'rol_id' => 5, 'name' => 'Facturador'],
            ['id' => 2, 'rol_id' => 2, 'name' => 'Vendedor'],
        ]);

        DB::table('cliente')->insert([
            'id' => 4,
            'nombre' => 'Cliente Rol Off',
            'cliente_categoria_escala_id' => 10,
        ]);

        DB::table('tipo_factura')->insert([
            'id' => 3,
            'codigo' => 'normal',
            'nombre' => 'Normal',
        ]);

        DB::table('factura')->insert([
            'id' => 3,
            'users_id' => 1,
            'vendedor' => 2,
            'cliente_id' => 4,
            'tipo_factura_id' => 3,
            'tipo_pago_id' => 1,
            'fecha_emision' => '2026-06-01',
            'fecha_vencimiento' => '2026-06-01',
            'sub_total' => 100,
            'cai' => 'FAC-003',
            'total' => 100,
        ]);

        DB::table('categoria_precios')->insert([
            'id' => 603,
            'cliente_categoria_escala_id' => 10,
            'nombre' => 'Precio G',
            'porc_precio_a' => 0,
            'estado_id' => 1,
        ]);

        DB::table('precios_producto_carga')->insert([
            'id' => 702,
            'categoria_precios_id' => 603,
            'precio_base_venta' => 100,
        ]);

        DB::table('venta_has_producto')->insert([
            'factura_id' => 3,
            'cantidad' => 1,
            'precio_unidad' => 100,
            'precios_producto_carga_id' => 702,
            'producto_id' => 1,
        ]);

        DB::table('comision_escala')->insert([
            'rol_id' => 2,
            'cliente_categoria_escala_id' => 10,
            'categoria_precios_id' => 603,
            'porcentaje_comision' => 2,
            'estado_id' => 1,
        ]);

        DB::table('comision_rol_config')->insert([
            'rol_id' => 2,
            'calcular' => 0,
        ]);

        $resultado = app(GeneradorFacturasComision::class)->generar(3, 9003, '2026-06-26');

        $this->assertSame([], $resultado);
        $this->assertSame(0, DB::table('facturas_comision')->count());
    }

    public function test_aplicador_retenciones_handles_contado_and_is_idempotent(): void
    {
        Auth::partialMock()->shouldReceive('id')->andReturn(777);

        DB::table('factura')->insert([
            'id' => 10,
            'users_id' => 1,
            'vendedor' => 2,
            'cliente_id' => 1,
            'tipo_factura_id' => 1,
            'tipo_pago_id' => 1,
            'fecha_emision' => '2026-06-01',
            'fecha_vencimiento' => '2026-06-01',
            'sub_total' => 500,
            'cai' => 'FAC-010',
            'total' => 500,
        ]);

        DB::table('facturas_comision')->insert([
            'id' => 100,
            'fecha_cierre_factura' => '2026-06-10',
            'monto_rol' => 100,
            'factura_id' => 10,
            'aplicacion_pagos_id' => 5000,
            'rol_id' => 2,
            'tipo_comision' => 3,
            'estado_id' => 1,
            'cantidad_usuariosxrol' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('dias_gracia_comision')->insert([
            'rol_id' => 2,
            'tipo_factura' => 'contado',
            'dias_gracia' => 5,
            'porcentaje_retencion' => 100,
        ]);

        $payload = [[
            'facturas_comision_id' => 100,
            'monto_rol' => 100,
            'rol_id' => 2,
            'target_user_id' => 2,
        ]];

        $resultado = app(AplicadorRetencionesMora::class)->aplicar($payload, 10, '2026-06-10');
        $resultado2 = app(AplicadorRetencionesMora::class)->aplicar($resultado, 10, '2026-06-10');

        $this->assertEquals(0.0, $resultado[0]['monto_rol']);
        $this->assertEquals(0.0, $resultado2[0]['monto_rol']);
        $this->assertEquals(100.0, (float) DB::table('facturas_comision')->where('id', 100)->value('retencion_mora_monto'));
        $this->assertSame(1, DB::table('retencion_mora_log')->where('facturas_comision_id', 100)->count());
    }

    public function test_aplicador_retenciones_handles_credito_by_grace_periods(): void
    {
        Auth::partialMock()->shouldReceive('id')->andReturn(888);

        DB::table('factura')->insert([
            'id' => 11,
            'users_id' => 1,
            'vendedor' => 2,
            'cliente_id' => 1,
            'tipo_factura_id' => 1,
            'tipo_pago_id' => 2,
            'fecha_emision' => '2026-06-01',
            'fecha_vencimiento' => '2026-06-01',
            'sub_total' => 500,
            'cai' => 'FAC-011',
            'total' => 500,
        ]);

        DB::table('facturas_comision')->insert([
            'id' => 101,
            'fecha_cierre_factura' => '2026-06-12',
            'monto_rol' => 100,
            'factura_id' => 11,
            'aplicacion_pagos_id' => 5001,
            'rol_id' => 2,
            'tipo_comision' => 3,
            'estado_id' => 1,
            'cantidad_usuariosxrol' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('dias_gracia_comision')->insert([
            'rol_id' => 2,
            'tipo_factura' => 'credito',
            'dias_gracia' => 5,
            'porcentaje_retencion' => 10,
        ]);

        $payload = [[
            'facturas_comision_id' => 101,
            'monto_rol' => 100,
            'rol_id' => 2,
            'target_user_id' => 2,
        ]];

        $resultado = app(AplicadorRetencionesMora::class)->aplicar($payload, 11, '2026-06-12');

        $this->assertEquals(80.0, $resultado[0]['monto_rol']);
        $this->assertEquals(20.0, (float) DB::table('facturas_comision')->where('id', 101)->value('retencion_mora_monto'));
        $this->assertSame(2, DB::table('retencion_mora_log')->where('facturas_comision_id', 101)->count());
    }

    public function test_detalle_nomina_normalizes_legacy_unit_commissions_to_line_totals(): void
    {
        DB::table('users')->insert([
            ['id' => 1, 'rol_id' => 5, 'name' => 'Facturador'],
            ['id' => 2, 'rol_id' => 2, 'name' => 'Vendedor'],
        ]);

        DB::table('cliente')->insert([
            'id' => 3,
            'nombre' => 'Cliente Reporte',
            'cliente_categoria_escala_id' => 10,
        ]);

        DB::table('factura')->insert([
            'id' => 20,
            'users_id' => 1,
            'vendedor' => 2,
            'cliente_id' => 3,
            'tipo_factura_id' => 1,
            'tipo_pago_id' => 1,
            'fecha_emision' => '2026-06-15',
            'fecha_vencimiento' => '2026-06-15',
            'sub_total' => 2462.20,
            'cai' => '000-001-01-00042973',
            'total' => 2462.20,
        ]);

        DB::table('categoria_precios')->insert([
            ['id' => 801, 'cliente_categoria_escala_id' => 10, 'nombre' => 'F', 'porc_precio_a' => 0, 'estado_id' => 1],
            ['id' => 802, 'cliente_categoria_escala_id' => 10, 'nombre' => 'C', 'porc_precio_a' => 10, 'estado_id' => 1],
        ]);

        DB::table('precios_producto_carga')->insert([
            ['id' => 901, 'categoria_precios_id' => 801, 'precio_base_venta' => 28.75],
            ['id' => 902, 'categoria_precios_id' => 801, 'precio_base_venta' => 100.00],
            ['id' => 903, 'categoria_precios_id' => 802, 'precio_base_venta' => 363.69],
        ]);

        DB::table('producto')->insert([
            ['id' => 1, 'nombre' => 'Acuarela 12 Colores'],
            ['id' => 2, 'nombre' => 'Agenda 80 Hojas'],
            ['id' => 3, 'nombre' => 'Dispensador Jabon'],
        ]);

        DB::table('facturas_comision')->insert([
            'id' => 200,
            'fecha_cierre_factura' => '2026-06-15',
            'monto_rol' => 9.8488,
            'factura_id' => 20,
            'aplicacion_pagos_id' => 9003,
            'rol_id' => 2,
            'tipo_comision' => 3,
            'estado_id' => 1,
            'cantidad_usuariosxrol' => 1,
            'retencion_mora_monto' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('producto_comision')->insert([
            [
                'cantidad' => 5,
                'precio_venta' => 28.75,
                'monto_comision' => 0.1150,
                'precios_producto_carga_id' => 901,
                'factura_id' => 20,
                'producto_id' => 1,
                'rol_id' => 2,
                'estado_id' => 1,
                'facturas_comision_id' => 200,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cantidad' => 5,
                'precio_venta' => 100.00,
                'monto_comision' => 0.4000,
                'precios_producto_carga_id' => 902,
                'factura_id' => 20,
                'producto_id' => 2,
                'rol_id' => 2,
                'estado_id' => 1,
                'facturas_comision_id' => 200,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cantidad' => 5,
                'precio_venta' => 363.69,
                'monto_comision' => 1.4548,
                'precios_producto_carga_id' => 903,
                'factura_id' => 20,
                'producto_id' => 3,
                'rol_id' => 2,
                'estado_id' => 1,
                'facturas_comision_id' => 200,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $request = Request::create('/comision/reporte/nomina/detalle', 'GET', [
            'empleado_id' => 2,
            'mes_clave' => '2026-06',
            'fechaInicio' => '2026-06-01',
            'fechaFin' => '2026-06-30',
        ]);

        $response = app(ReportesComisionesGenerales::class)->detalleNomina($request);
        $payload = $response->getData(true);

        $this->assertCount(1, $payload['data']);
        $detalle = $payload['data'][0]['detalle_productos'];

        $this->assertCount(3, $detalle);
        $this->assertEquals(0.58, $detalle[0]['comision']);
        $this->assertEquals(0.40, $detalle[0]['porcentaje_comision']);
        $this->assertEquals(9.85, round(array_sum(array_column($detalle, 'comision')), 2));
    }

    public function test_detalle_nomina_consolidates_identical_duplicate_product_rows(): void
    {
        DB::table('users')->insert([
            ['id' => 11, 'rol_id' => 5, 'name' => 'Facturador Duplicado'],
            ['id' => 12, 'rol_id' => 2, 'name' => 'Vendedor Duplicado'],
        ]);

        DB::table('cliente')->insert([
            'id' => 30,
            'nombre' => 'Cliente Duplicado',
            'cliente_categoria_escala_id' => 10,
        ]);

        DB::table('factura')->insert([
            'id' => 30,
            'users_id' => 11,
            'vendedor' => 12,
            'cliente_id' => 30,
            'tipo_factura_id' => 1,
            'tipo_pago_id' => 1,
            'fecha_emision' => '2026-06-20',
            'fecha_vencimiento' => '2026-06-20',
            'sub_total' => 100,
            'cai' => 'DUP-001',
            'total' => 100,
        ]);

        DB::table('categoria_precios')->insert([
            'id' => 830,
            'cliente_categoria_escala_id' => 10,
            'nombre' => 'F',
            'porc_precio_a' => 0,
            'estado_id' => 1,
        ]);

        DB::table('precios_producto_carga')->insert([
            'id' => 930,
            'categoria_precios_id' => 830,
            'precio_base_venta' => 50.00,
        ]);

        DB::table('producto')->insert([
            'id' => 8300,
            'nombre' => 'Producto Duplicado',
        ]);

        DB::table('facturas_comision')->insert([
            'id' => 300,
            'fecha_cierre_factura' => '2026-06-20',
            'monto_rol' => 3.00,
            'factura_id' => 30,
            'aplicacion_pagos_id' => 9300,
            'rol_id' => 2,
            'tipo_comision' => 3,
            'estado_id' => 1,
            'cantidad_usuariosxrol' => 1,
            'retencion_mora_monto' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('producto_comision')->insert([
            [
                'cantidad' => 1,
                'precio_venta' => 50.00,
                'monto_comision' => 1.00,
                'precios_producto_carga_id' => 930,
                'factura_id' => 30,
                'producto_id' => 8300,
                'rol_id' => 2,
                'estado_id' => 1,
                'facturas_comision_id' => 300,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cantidad' => 1,
                'precio_venta' => 50.00,
                'monto_comision' => 1.00,
                'precios_producto_carga_id' => 930,
                'factura_id' => 30,
                'producto_id' => 8300,
                'rol_id' => 2,
                'estado_id' => 1,
                'facturas_comision_id' => 300,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cantidad' => 1,
                'precio_venta' => 50.00,
                'monto_comision' => 1.00,
                'precios_producto_carga_id' => 930,
                'factura_id' => 30,
                'producto_id' => 8300,
                'rol_id' => 2,
                'estado_id' => 1,
                'facturas_comision_id' => 300,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $request = Request::create('/comision/reporte/nomina/detalle', 'GET', [
            'empleado_id' => 12,
            'mes_clave' => '2026-06',
            'fechaInicio' => '2026-06-01',
            'fechaFin' => '2026-06-30',
        ]);

        $response = app(ReportesComisionesGenerales::class)->detalleNomina($request);
        $payload = $response->getData(true);

        $this->assertCount(1, $payload['data']);
        $detalle = $payload['data'][0]['detalle_productos'];

        $this->assertCount(1, $detalle);
        $this->assertEquals(3.0, $detalle[0]['cantidad']);
        $this->assertEquals(3.0, $detalle[0]['comision']);
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('rol_id')->nullable();
            $table->string('name');
        });

        Schema::create('rol', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('nombre');
            $table->integer('estado_id')->default(1);
        });

        Schema::create('cliente_categoria_escala', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('nombre_categoria');
        });

        Schema::create('cliente', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('nombre')->nullable();
            $table->integer('cliente_categoria_escala_id')->nullable();
        });

        Schema::create('tipo_factura', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('codigo')->nullable();
            $table->string('nombre')->nullable();
        });

        Schema::create('factura', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('users_id');
            $table->integer('vendedor');
            $table->integer('gestor_entrega')->nullable();
            $table->integer('tipo_factura_id')->nullable();
            $table->integer('cliente_id')->nullable();
            $table->integer('tipo_pago_id')->default(1);
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('sub_total', 12, 4)->default(0);
            $table->decimal('total', 12, 4)->default(0);
            $table->string('cai')->nullable();
        });

        Schema::create('categoria_precios', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('cliente_categoria_escala_id')->nullable();
            $table->string('nombre')->nullable();
            $table->decimal('porc_precio_a', 8, 4)->nullable();
            $table->integer('estado_id')->default(1);
        });

        Schema::create('precios_producto_carga', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('categoria_precios_id');
            $table->decimal('precio_base_venta', 12, 4)->nullable();
        });

        Schema::create('producto', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('nombre')->nullable();
        });

        Schema::create('venta_has_producto', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('factura_id');
            $table->decimal('cantidad', 12, 4);
            $table->decimal('precio_unidad', 12, 4);
            $table->string('idPrecioSeleccionado')->nullable();
            $table->decimal('precioSeleccionado', 12, 4)->nullable();
            $table->integer('precios_producto_carga_id');
            $table->integer('producto_id');
        });

        Schema::create('comision_rol_config', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rol_id');
            $table->integer('calcular')->default(1);
            $table->integer('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('comision_escala', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rol_id');
            $table->integer('cliente_categoria_escala_id')->nullable();
            $table->integer('categoria_precios_id')->nullable();
            $table->decimal('porcentaje_comision', 12, 4);
            $table->integer('estado_id')->default(1);
        });

        Schema::create('facturas_comision', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha_cierre_factura');
            $table->decimal('monto_rol', 12, 4);
            $table->integer('factura_id');
            $table->integer('comision_escala_id')->nullable();
            $table->integer('aplicacion_pagos_id')->nullable();
            $table->integer('rol_id');
            $table->integer('tipo_comision')->nullable();
            $table->integer('estado_id')->default(1);
            $table->integer('cantidad_usuariosxrol')->nullable();
            $table->decimal('retencion_mora_monto', 12, 4)->nullable();
            $table->integer('retencion_mora_dias')->nullable();
            $table->timestamps();
        });

        Schema::create('producto_comision', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('cantidad', 12, 4);
            $table->decimal('precio_venta', 12, 4);
            $table->decimal('monto_comision', 12, 4);
            $table->integer('precios_producto_carga_id')->nullable();
            $table->integer('factura_id')->nullable();
            $table->integer('producto_id')->nullable();
            $table->integer('rol_id')->nullable();
            $table->integer('estado_id')->default(1);
            $table->integer('facturas_comision_id')->nullable();
            $table->timestamps();
        });

        Schema::create('dias_gracia_comision', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rol_id');
            $table->string('tipo_factura');
            $table->integer('dias_gracia');
            $table->decimal('porcentaje_retencion', 12, 4)->nullable();
            $table->string('descripcion')->nullable();
        });

        Schema::create('retencion_mora_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('factura_id');
            $table->integer('facturas_comision_id');
            $table->integer('rol_id');
            $table->integer('user_id')->nullable();
            $table->string('tipo_factura');
            $table->date('fecha_aplicacion');
            $table->integer('dias_transcurridos');
            $table->integer('dias_gracia_configurados');
            $table->decimal('porcentaje_aplicado', 12, 4);
            $table->integer('periodo_numero')->nullable();
            $table->decimal('comision_original', 12, 4);
            $table->decimal('monto_retenido', 12, 4);
            $table->decimal('subtotal_factura', 12, 4);
            $table->integer('usuario_ejecutor')->nullable();
            $table->timestamps();
        });

        Schema::create('comision_reversiones', function (Blueprint $table) {
            $table->increments('id');
            $table->text('comisiones_revertidas')->nullable();
            $table->string('motivo')->nullable();
            $table->timestamps();
        });
    }

    private function seedBaseCatalogs(): void
    {
        DB::table('rol')->insert([
            ['id' => 2, 'nombre' => 'Asesor Comercial', 'estado_id' => 1],
            ['id' => 3, 'nombre' => 'Televendedor', 'estado_id' => 1],
            ['id' => 5, 'nombre' => 'Administrador', 'estado_id' => 1],
            ['id' => 16, 'nombre' => 'Gestor Entrega', 'estado_id' => 1],
        ]);

        DB::table('cliente_categoria_escala')->insert([
            ['id' => 10, 'nombre_categoria' => 'Co-Distribuidor'],
            ['id' => 20, 'nombre_categoria' => 'Minorista'],
        ]);
    }
}