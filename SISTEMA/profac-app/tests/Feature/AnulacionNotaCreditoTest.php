<?php

namespace Tests\Feature;

use App\Livewire\NotaCredito\CrearNotaCredito;
use App\Livewire\NotaCredito\ListadoNotaCredito;
use App\Models\User;
use App\Services\NotaCredito\AnuladorNotaCredito;
use DomainException;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AnulacionNotaCreditoTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '\..\..\bootstrap\app.php';
        $app->make(Kernel::class)->bootstrap();
        $app['config']->set('database.connections.anulacion_nc_testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]);
        $app['config']->set('database.default', 'anulacion_nc_testing');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('anulacion_nc_testing', DB::connection()->getName());
        $this->travelTo(\Carbon\Carbon::parse('2026-10-20 10:00:00'));
        $this->crearEsquema();
        $this->sembrar();
        $this->actingAs(User::findOrFail(3));
    }

    private function crearEsquema(): void
    {
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
        });
        Schema::create('factura', function (Blueprint $t) {
            $t->integer('id')->primary();
            $t->string('cai');
            $t->integer('cliente_id');
            $t->integer('estado_venta_id');
            $t->integer('users_id');
            $t->integer('vendedor');
            $t->integer('gestor_entrega')->nullable();
        });
        Schema::create('aplicacion_pagos', function (Blueprint $t) {
            $t->integer('id')->primary();
            $t->integer('cliente_id');
            $t->integer('factura_id');
            $t->decimal('total_notas_credito', 18, 2)->nullable();
            $t->decimal('saldo', 18, 2);
            $t->integer('estado');
            $t->integer('estado_cerrado');
            $t->integer('usr_cerro')->default(0);
            $t->dateTime('fecha_cierre_factura')->nullable();
            $t->integer('ultimo_usr_actualizo')->nullable();
            $t->timestamps();
        });
        Schema::create('nota_credito', function (Blueprint $t) {
            $t->integer('id')->primary();
            $t->string('cai');
            $t->decimal('total', 18, 2);
            $t->decimal('isv', 18, 2)->default(0);
            $t->date('fecha');
            $t->integer('factura_id');
            $t->integer('estado_nota_id');
            $t->integer('estado_rebajado')->default(2);
            $t->integer('user_registra_rebaja')->default(0);
            $t->date('fecha_rebajado')->nullable();
            $t->string('comentario_rebajado')->default('N/A');
            $t->timestamps();
        });
        Schema::create('nota_credito_creditos', function (Blueprint $t) {
            $t->id();
            $t->integer('nota_credito_id')->unique();
            $t->integer('cliente_id');
            $t->decimal('monto_original', 18, 2);
            $t->decimal('monto_aplicado', 18, 2)->default(0);
            $t->decimal('monto_reembolsado', 18, 2)->default(0);
            $t->decimal('saldo_disponible', 18, 2);
            $t->string('estado', 30);
            $t->timestamps();
        });
        Schema::create('nota_credito_movimientos', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('credito_id');
            $t->string('tipo', 30);
            $t->decimal('monto', 18, 2);
            $t->integer('factura_id')->nullable();
            $t->integer('aplicacion_pagos_id')->nullable();
            $t->integer('banco_id')->nullable();
            $t->string('cuenta_reembolso')->nullable();
            $t->integer('tipo_pago_cobro_id')->nullable();
            $t->string('referencia')->nullable();
            $t->string('comprobante')->nullable();
            $t->text('comentario')->nullable();
            $t->unsignedBigInteger('movimiento_origen_id')->nullable();
            $t->unsignedBigInteger('users_id');
            $t->date('fecha_movimiento');
            $t->timestamps();
        });
        Schema::create('nota_credito_asientos', function (Blueprint $t) {
            $t->id();
            $t->integer('nota_credito_id');
            $t->unsignedBigInteger('movimiento_id')->nullable()->unique();
            $t->string('tipo', 30);
            $t->date('fecha');
            $t->string('descripcion');
            $t->unsignedBigInteger('users_id');
            $t->timestamps();
        });
        Schema::create('nota_credito_asiento_detalles', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('asiento_id');
            $t->string('cuenta_codigo');
            $t->string('cuenta_nombre');
            $t->decimal('debe', 18, 2)->default(0);
            $t->decimal('haber', 18, 2)->default(0);
            $t->timestamps();
        });
        Schema::create('nota_credito_has_producto', function (Blueprint $t) {
            $t->integer('nota_credito_id');
            $t->integer('producto_id');
            $t->integer('seccion_id');
            $t->decimal('cantidad', 18, 2);
            $t->integer('unidad_medida_venta_id');
        });
        Schema::create('nota_credito_ajustes_comision', function (Blueprint $t) {
            $t->id();
            $t->integer('nota_credito_id');
            $t->integer('facturas_comision_id');
            $t->integer('factura_id');
            $t->unsignedBigInteger('user_id');
            $t->integer('rol_id');
            $t->date('periodo_original');
            $t->date('periodo_aplicado');
            $t->decimal('monto', 16, 2);
            $t->timestamp('anulado_at')->nullable();
            $t->unsignedBigInteger('anulado_por')->nullable();
            $t->timestamps();
        });
        Schema::create('nota_credito_reversiones', function (Blueprint $t) {
            $t->id();
            $t->integer('nota_credito_id');
            $t->string('alcance', 20);
            $t->unsignedBigInteger('movimiento_id')->nullable();
            $t->text('motivo');
            $t->text('detalle')->nullable();
            $t->unsignedBigInteger('users_id');
            $t->timestamps();
        });
        Schema::create('expo_cotizacion', function (Blueprint $t) {
            $t->id();
            $t->integer('nota_credito_id')->nullable();
        });
        Schema::create('unidad_medida_venta', function (Blueprint $t) {
            $t->integer('id')->primary();
            $t->integer('unidad_venta');
        });
        Schema::create('producto', function (Blueprint $t) {
            $t->integer('id')->primary();
            $t->string('nombre');
        });
        Schema::create('bodega', function (Blueprint $t) {
            $t->integer('id')->primary();
            $t->string('nombre');
        });
        Schema::create('segmento', function (Blueprint $t) {
            $t->integer('id')->primary();
            $t->integer('bodega_id');
            $t->string('descripcion');
        });
        Schema::create('seccion', function (Blueprint $t) {
            $t->integer('id')->primary();
            $t->integer('segmento_id');
            $t->string('descripcion');
        });
        Schema::create('recibido_bodega', function (Blueprint $t) {
            $t->integer('id')->primary();
            $t->integer('producto_id');
            $t->integer('seccion_id');
            $t->decimal('cantidad_disponible', 18, 2);
            $t->timestamp('updated_at')->nullable();
        });
        Schema::create('venta_has_producto', function (Blueprint $t) {
            $t->integer('factura_id');
            $t->integer('producto_id');
            $t->integer('seccion_id');
            $t->integer('lote');
            $t->integer('numero_unidades_resta_inventario');
            $t->integer('unidades_nota_credito_resta_inventario');
            $t->integer('cantidad_nota_credito');
            $t->integer('resta_inventario_total');
            $t->integer('unidad_medida_venta_id');
            $t->decimal('precio_unidad', 18, 2);
            $t->decimal('cantidad', 18, 2);
            $t->decimal('cantidad_para_entregar', 18, 2);
        });
        Schema::create('log_translado', function (Blueprint $t) {
            $t->id();
            $t->integer('origen');
            $t->integer('destino')->nullable();
            $t->decimal('cantidad', 18, 2);
            $t->unsignedBigInteger('users_id');
            $t->string('descripcion');
            $t->text('comentario')->nullable();
            $t->integer('factura_id')->nullable();
            $t->integer('nota_credito_id')->nullable();
            $t->integer('unidad_medida_venta_id')->nullable();
            $t->timestamps();
        });
        Schema::create('facturas_comision', function (Blueprint $t) {
            $t->integer('id')->primary();
            $t->date('fecha_cierre_factura')->nullable();
            $t->decimal('monto_rol', 16, 2);
            $t->decimal('retencion_mora_monto', 16, 2)->default(0);
            $t->integer('retencion_mora_dias')->default(0);
            $t->integer('factura_id');
            $t->integer('aplicacion_pagos_id')->nullable();
            $t->integer('rol_id');
            $t->integer('tipo_comision');
            $t->integer('estado_id');
            $t->timestamps();
        });
        Schema::create('producto_comision', function (Blueprint $t) {
            $t->id();
            $t->integer('facturas_comision_id');
            $t->integer('estado_id');
            $t->timestamps();
        });
        Schema::create('comision_empleado', function (Blueprint $t) {
            $t->id();
            $t->decimal('comision_acumulada', 16, 2);
            $t->dateTime('fecha_ult_modificacion')->nullable();
            $t->date('mes_comision');
            $t->string('nombre_empleado')->nullable();
            $t->integer('users_comision');
            $t->integer('rol_id');
            $t->integer('estado_id');
            $t->timestamps();
        });
        Schema::create('comision_periodo', function (Blueprint $t) {
            $t->id();
            $t->date('periodo');
            $t->integer('estado');
            $t->dateTime('fecha_conciliacion')->nullable();
        });
    }

    private function sembrar(): void
    {
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Facturador'], ['id' => 2, 'name' => 'Vendedor'], ['id' => 3, 'name' => 'Admin'],
        ]);
        DB::table('factura')->insert([
            ['id' => 10, 'cai' => 'F-10', 'cliente_id' => 5, 'estado_venta_id' => 1, 'users_id' => 1, 'vendedor' => 2, 'gestor_entrega' => null],
            ['id' => 11, 'cai' => 'F-11', 'cliente_id' => 5, 'estado_venta_id' => 1, 'users_id' => 1, 'vendedor' => 2, 'gestor_entrega' => null],
            ['id' => 12, 'cai' => 'F-12', 'cliente_id' => 5, 'estado_venta_id' => 2, 'users_id' => 1, 'vendedor' => 2, 'gestor_entrega' => null],
        ]);
        DB::table('unidad_medida_venta')->insert(['id' => 1, 'unidad_venta' => 1]);
        DB::table('producto')->insert(['id' => 1, 'nombre' => 'Producto prueba']);
        DB::table('bodega')->insert([
            ['id' => 1, 'nombre' => 'Bodega de origen'],
            ['id' => 2, 'nombre' => 'Bodega alterna'],
        ]);
        DB::table('segmento')->insert([
            ['id' => 1, 'bodega_id' => 1, 'descripcion' => 'Segmento origen'],
            ['id' => 2, 'bodega_id' => 2, 'descripcion' => 'Segmento alterno'],
        ]);
        DB::table('seccion')->insert([
            ['id' => 1, 'segmento_id' => 1, 'descripcion' => 'Sección origen'],
            ['id' => 2, 'segmento_id' => 2, 'descripcion' => 'Sección alterna'],
        ]);
    }

    /** Nota 1 de 100 aplicada 60 a la factura 10 y 40 a la 11 (ambas cerradas por la nota). */
    private function notaAplicada(array $opciones = []): void
    {
        $now = now();
        DB::table('nota_credito')->insert([
            'id' => 1, 'cai' => 'NC-1', 'total' => 100, 'isv' => 15, 'fecha' => '2026-10-05', 'factura_id' => 10,
            'estado_nota_id' => 1, 'estado_rebajado' => 1, 'created_at' => '2026-10-05 08:00:00', 'updated_at' => $now,
        ]);
        DB::table('nota_credito_creditos')->insert([
            'id' => 1, 'nota_credito_id' => 1, 'cliente_id' => 5, 'monto_original' => 100, 'monto_aplicado' => 100,
            'monto_reembolsado' => 0, 'saldo_disponible' => 0, 'estado' => 'consumido', 'created_at' => $now, 'updated_at' => $now,
        ]);
        foreach ([[1, 10, 60], [2, 11, 40]] as [$ap, $factura, $monto]) {
            DB::table('aplicacion_pagos')->insert([
                'id' => $ap, 'cliente_id' => 5, 'factura_id' => $factura, 'total_notas_credito' => $monto, 'saldo' => 0,
                'estado' => 1, 'estado_cerrado' => 2, 'usr_cerro' => 3, 'fecha_cierre_factura' => '2026-10-10 09:00:00',
                'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('nota_credito_movimientos')->insert([
                'id' => $ap, 'credito_id' => 1, 'tipo' => 'aplicacion', 'monto' => $monto, 'factura_id' => $factura,
                'aplicacion_pagos_id' => $ap, 'users_id' => 3, 'fecha_movimiento' => '2026-10-10',
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $this->asiento($ap + 1, 'aplicacion', $ap, "Aplicación a F-{$factura}", [
                ['CREDITO_CLIENTE', 'Crédito a favor del cliente', $monto, 0],
                ['CUENTAS_POR_COBRAR', 'Cuentas por cobrar clientes', 0, $monto],
            ]);
        }
        $this->asiento(1, 'emision', null, 'Emisión NC-1', [
            ['DEVOLUCIONES_VENTAS', 'Devoluciones', 85, 0],
            ['ISV_DEBITO_FISCAL', 'ISV', 15, 0],
            ['CREDITO_CLIENTE', 'Crédito a favor del cliente', 0, 100],
        ]);
    }

    private function asiento(int $id, string $tipo, ?int $movimientoId, string $descripcion, array $lineas): void
    {
        DB::table('nota_credito_asientos')->insert([
            'id' => $id, 'nota_credito_id' => 1, 'movimiento_id' => $movimientoId, 'tipo' => $tipo,
            'fecha' => '2026-10-10', 'descripcion' => $descripcion, 'users_id' => 3,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach ($lineas as [$codigo, $cuenta, $debe, $haber]) {
            DB::table('nota_credito_asiento_detalles')->insert([
                'asiento_id' => $id, 'cuenta_codigo' => $codigo, 'cuenta_nombre' => $cuenta,
                'debe' => $debe, 'haber' => $haber, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    private function comisionesDelCierre(): void
    {
        foreach ([[1, 3, 2, 2, 10.00], [2, 1, 3, 1, 5.00]] as [$id, $tipo, $rol, $user, $monto]) {
            DB::table('facturas_comision')->insert([
                'id' => $id, 'fecha_cierre_factura' => '2026-10-10', 'monto_rol' => $monto, 'factura_id' => 11,
                'aplicacion_pagos_id' => 2, 'rol_id' => $rol, 'tipo_comision' => $tipo, 'estado_id' => 1,
                'created_at' => '2026-10-10 09:00:00', 'updated_at' => '2026-10-10 09:00:00',
            ]);
            DB::table('producto_comision')->insert(['facturas_comision_id' => $id, 'estado_id' => 1]);
            DB::table('comision_empleado')->insert([
                'comision_acumulada' => $monto === 10.00 ? 50 : 20, 'mes_comision' => '2026-10-01',
                'users_comision' => $user, 'rol_id' => $rol, 'estado_id' => 1,
            ]);
        }
    }

    private function devolucionDeInventario(float $disponible = 50): void
    {
        DB::table('recibido_bodega')->insert(['id' => 1, 'producto_id' => 1, 'seccion_id' => 1, 'cantidad_disponible' => $disponible]);
        DB::table('venta_has_producto')->insert([
            'factura_id' => 10, 'producto_id' => 1, 'seccion_id' => 1, 'lote' => 1, 'numero_unidades_resta_inventario' => 10,
            'unidades_nota_credito_resta_inventario' => 0, 'cantidad_nota_credito' => 0, 'resta_inventario_total' => 0,
            'unidad_medida_venta_id' => 1, 'precio_unidad' => 5, 'cantidad' => 10, 'cantidad_para_entregar' => 0,
        ]);
        DB::table('nota_credito_has_producto')->insert([
            'nota_credito_id' => 1, 'producto_id' => 1, 'seccion_id' => 1, 'cantidad' => 10, 'unidad_medida_venta_id' => 1,
        ]);
        DB::table('log_translado')->insert([
            'origen' => 1, 'destino' => 1, 'cantidad' => 10, 'users_id' => 3, 'descripcion' => 'Devolucion de producto',
            'nota_credito_id' => 1, 'unidad_medida_venta_id' => 1,
        ]);
        DB::table('log_translado')->insert([
            'origen' => 1, 'destino' => null, 'cantidad' => 0, 'users_id' => 3, 'descripcion' => 'Venta de producto', 'factura_id' => 10,
        ]);
    }

    private function servicio(): AnuladorNotaCredito
    {
        return app(AnuladorNotaCredito::class);
    }

    public function test_anular_nota_revierte_aplicaciones_comisiones_ajustes_inventario_y_asientos(): void
    {
        $this->notaAplicada();
        $this->comisionesDelCierre();
        $this->devolucionDeInventario();
        DB::table('nota_credito_ajustes_comision')->insert([
            'nota_credito_id' => 1, 'facturas_comision_id' => 99, 'factura_id' => 10, 'user_id' => 2, 'rol_id' => 2,
            'periodo_original' => '2026-09-01', 'periodo_aplicado' => '2026-10-01', 'monto' => 4,
        ]);

        $resultado = $this->servicio()->anularNota(1, 'Nota emitida por error', 3);

        $this->assertSame(2, DB::table('nota_credito')->where('id', 1)->value('estado_nota_id') + 0);
        $credito = DB::table('nota_credito_creditos')->find(1);
        $this->assertSame('anulado', $credito->estado);
        $this->assertEquals(0, $credito->saldo_disponible + $credito->monto_aplicado + $credito->monto_reembolsado);

        foreach ([1 => 60, 2 => 40] as $ap => $monto) {
            $fila = DB::table('aplicacion_pagos')->find($ap);
            $this->assertEquals($monto, $fila->saldo);
            $this->assertEquals(0, $fila->total_notas_credito);
            $this->assertSame(0, (int) $fila->estado_cerrado);
            $this->assertNull($fila->fecha_cierre_factura);
        }

        $this->assertSame(['aplicacion_anulada', 'aplicacion_anulada'], DB::table('nota_credito_movimientos')->whereIn('id', [1, 2])->pluck('tipo')->all());
        $reversos = DB::table('nota_credito_movimientos')->where('tipo', 'reversion_aplicacion')->orderBy('movimiento_origen_id')->get();
        $this->assertSame([1, 2], $reversos->pluck('movimiento_origen_id')->map(fn ($id) => (int) $id)->all());
        $this->assertSame('Nota emitida por error', $reversos->first()->comentario);

        $this->assertSame([2, 2], DB::table('facturas_comision')->pluck('estado_id')->map(fn ($v) => (int) $v)->all());
        $this->assertEquals(0, DB::table('facturas_comision')->sum('monto_rol'));
        $this->assertSame([2, 2], DB::table('producto_comision')->pluck('estado_id')->map(fn ($v) => (int) $v)->all());
        $this->assertEquals(44, DB::table('comision_empleado')->where('users_comision', 2)->value('comision_acumulada'));
        $this->assertEquals(15, DB::table('comision_empleado')->where('users_comision', 1)->value('comision_acumulada'));
        $this->assertNotNull(DB::table('nota_credito_ajustes_comision')->value('anulado_at'));

        $this->assertEquals(40, DB::table('recibido_bodega')->where('id', 1)->value('cantidad_disponible'));
        $vhp = DB::table('venta_has_producto')->first();
        $this->assertSame([10, 10, 10, 10], [
            (int) $vhp->unidades_nota_credito_resta_inventario, (int) $vhp->cantidad_nota_credito,
            (int) $vhp->resta_inventario_total, (int) $vhp->cantidad_para_entregar,
        ]);
        $this->assertEquals(10, DB::table('log_translado')->where('descripcion', 'Venta de producto')->value('cantidad'));
        $anulacion = DB::table('log_translado')->where('descripcion', 'Nota credito anulado')->first();
        $this->assertNull($anulacion->destino);
        $this->assertSame([1, 10, 1], [(int) $anulacion->origen, (int) $anulacion->factura_id, (int) $anulacion->nota_credito_id]);

        $reversosAsientos = DB::table('nota_credito_asientos')->whereIn('tipo', ['anulacion_emision', 'anulacion_aplicacion'])->get();
        $this->assertCount(3, $reversosAsientos);
        $emision = $reversosAsientos->firstWhere('tipo', 'anulacion_emision');
        $detalles = DB::table('nota_credito_asiento_detalles')->where('asiento_id', $emision->id)->get()->keyBy('cuenta_codigo');
        $this->assertEquals(100, $detalles['CREDITO_CLIENTE']->debe);
        $this->assertEquals(85, $detalles['DEVOLUCIONES_VENTAS']->haber);
        $this->assertEquals(15, $detalles['ISV_DEBITO_FISCAL']->haber);
        $this->assertEquals(
            DB::table('nota_credito_asiento_detalles')->sum('debe'),
            DB::table('nota_credito_asiento_detalles')->sum('haber')
        );

        $this->assertSame('nota', DB::table('nota_credito_reversiones')->value('alcance'));
        $this->assertCount(2, $resultado['aplicaciones']);
        $this->assertSame(3, $resultado['asientos_revertidos']);
    }

    public function test_nota_reembolsada_registra_el_reverso_contable_del_reembolso(): void
    {
        $now = now();
        DB::table('nota_credito')->insert([
            'id' => 1, 'cai' => 'NC-1', 'total' => 100, 'fecha' => '2026-10-05', 'factura_id' => 10,
            'estado_nota_id' => 1, 'estado_rebajado' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('nota_credito_creditos')->insert([
            'id' => 1, 'nota_credito_id' => 1, 'cliente_id' => 5, 'monto_original' => 100, 'monto_reembolsado' => 100,
            'saldo_disponible' => 0, 'estado' => 'consumido', 'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('nota_credito_movimientos')->insert([
            'id' => 1, 'credito_id' => 1, 'tipo' => 'reembolso', 'monto' => 100, 'banco_id' => 7, 'cuenta_reembolso' => 'BAC 123',
            'tipo_pago_cobro_id' => 2, 'users_id' => 3, 'fecha_movimiento' => '2026-10-05', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->asiento(1, 'reembolso', 1, 'Reembolso NC-1', [
            ['CREDITO_CLIENTE', 'Crédito a favor del cliente', 100, 0],
            ['BANCO_CAJA', 'Banco o caja', 0, 100],
        ]);

        $previa = $this->servicio()->previsualizarNota(1);
        $this->assertTrue($previa['puede']);
        $this->assertSame('BAC 123', $previa['reembolsos'][0]['cuenta']);

        $this->servicio()->anularNota(1, 'Reembolso mal emitido', 3);

        $this->assertSame('reembolso_anulado', DB::table('nota_credito_movimientos')->where('id', 1)->value('tipo'));
        $reverso = DB::table('nota_credito_movimientos')->where('tipo', 'reversion_reembolso')->first();
        $this->assertSame([1, 7, 'BAC 123'], [(int) $reverso->movimiento_origen_id, (int) $reverso->banco_id, $reverso->cuenta_reembolso]);
        $asiento = DB::table('nota_credito_asientos')->where('tipo', 'anulacion_reembolso')->first();
        $detalles = DB::table('nota_credito_asiento_detalles')->where('asiento_id', $asiento->id)->get()->keyBy('cuenta_codigo');
        $this->assertEquals(100, $detalles['BANCO_CAJA']->debe);
        $this->assertEquals(100, $detalles['CREDITO_CLIENTE']->haber);
    }

    public function test_revertir_una_aplicacion_deja_la_nota_disponible_y_puede_repetirse_por_aplicacion(): void
    {
        $this->notaAplicada();
        $this->comisionesDelCierre();

        $resultado = $this->servicio()->revertirAplicacion(2, 'Aplicada a la factura equivocada', 3);

        $this->assertSame('parcial', $resultado['estado']);
        $this->assertEquals(40, $resultado['saldo_disponible']);
        $credito = DB::table('nota_credito_creditos')->find(1);
        $this->assertEquals([60, 40, 'parcial'], [$credito->monto_aplicado + 0, $credito->saldo_disponible + 0, $credito->estado]);
        $nota = DB::table('nota_credito')->find(1);
        $this->assertSame([1, 3], [(int) $nota->estado_nota_id, (int) $nota->estado_rebajado]);
        $this->assertEquals(40, DB::table('aplicacion_pagos')->where('id', 2)->value('saldo'));
        $this->assertEquals(0, DB::table('aplicacion_pagos')->where('id', 1)->value('saldo'));
        $this->assertSame([2, 2], DB::table('facturas_comision')->pluck('estado_id')->map(fn ($v) => (int) $v)->all());
        $this->assertSame('aplicacion', DB::table('nota_credito_movimientos')->where('id', 1)->value('tipo'));
        $this->assertSame('aplicacion_anulada', DB::table('nota_credito_movimientos')->where('id', 2)->value('tipo'));
        $this->assertSame('aplicacion', DB::table('nota_credito_asientos')->where('movimiento_id', 1)->value('tipo'));
        $this->assertSame(1, DB::table('nota_credito_asientos')->where('tipo', 'anulacion_aplicacion')->count());

        $repetida = $this->servicio()->previsualizarAplicacion(2);
        $this->assertFalse($repetida['puede']);
        $this->assertSame('Esta aplicación ya fue anulada anteriormente.', $repetida['bloqueos'][0]);

        $segunda = $this->servicio()->revertirAplicacion(1, 'Reaplicar en otra factura', 3);
        $this->assertSame('disponible', $segunda['estado']);
        $nota = DB::table('nota_credito')->find(1);
        $this->assertSame([2, 'N/A', null], [(int) $nota->estado_rebajado, $nota->comentario_rebajado, $nota->fecha_rebajado]);
        $this->assertEquals([100, 0], [DB::table('nota_credito_creditos')->value('saldo_disponible') + 0, DB::table('nota_credito_creditos')->value('monto_aplicado') + 0]);
        $this->assertSame(2, DB::table('nota_credito_reversiones')->where('alcance', 'aplicacion')->count());
    }

    public function test_aplicacion_a_factura_anulada_solo_registra_el_reverso_contable(): void
    {
        $this->notaAplicada();
        DB::table('aplicacion_pagos')->where('id', 2)->update(['estado' => 2, 'factura_id' => 12]);
        DB::table('nota_credito_movimientos')->where('id', 2)->update(['factura_id' => 12]);

        $previa = $this->servicio()->previsualizarAplicacion(2);
        $this->assertFalse($previa['aplicaciones'][0]['vigente']);

        $efecto = $this->servicio()->revertirAplicacion(2, 'Factura anulada previamente', 3)['aplicacion'];

        $this->assertFalse($efecto['cuenta_restaurada']);
        $fila = DB::table('aplicacion_pagos')->find(2);
        $this->assertEquals([0, 40, 2], [$fila->saldo + 0, $fila->total_notas_credito + 0, (int) $fila->estado_cerrado]);
        $this->assertSame('aplicacion_anulada', DB::table('nota_credito_movimientos')->where('id', 2)->value('tipo'));
    }

    public function test_comision_de_periodo_conciliado_se_ajusta_en_el_siguiente_periodo_abierto(): void
    {
        $this->notaAplicada();
        $this->comisionesDelCierre();
        DB::table('comision_periodo')->insert(['periodo' => '2026-10-01', 'estado' => 1, 'fecha_conciliacion' => '2026-10-15 00:00:00']);
        DB::table('facturas_comision')->where('id', 2)->update(['created_at' => '2026-10-16 09:00:00']);

        $this->servicio()->revertirAplicacion(2, 'Revertir cierre conciliado', 3);

        $this->assertEquals(50, DB::table('comision_empleado')->where('users_comision', 2)->where('mes_comision', '2026-10-01')->value('comision_acumulada'));
        $this->assertEquals(-10, DB::table('comision_empleado')->where('users_comision', 2)->where('mes_comision', '2026-11-01')->value('comision_acumulada'));
        $this->assertEquals(20, DB::table('comision_empleado')->where('users_comision', 1)->where('mes_comision', '2026-10-01')->value('comision_acumulada'));
        $this->assertNull(DB::table('comision_empleado')->where('users_comision', 1)->where('mes_comision', '2026-11-01')->first());
    }

    public function test_bloquea_sin_cambios_cuando_el_inventario_devuelto_ya_fue_utilizado(): void
    {
        $this->notaAplicada();
        $this->devolucionDeInventario(4);

        $previa = $this->servicio()->previsualizarNota(1);
        $this->assertFalse($previa['puede']);
        $this->assertFalse($previa['inventario'][0]['suficiente']);
        $this->assertSame('Producto prueba', $previa['inventario'][0]['producto']);
        $this->assertSame(10.0, $previa['inventario'][0]['cantidad']);
        $this->assertSame(4.0, $previa['inventario'][0]['disponible']);
        $this->assertNotEmpty($previa['inventario'][0]['bloqueo']);

        try {
            $this->servicio()->anularNota(1, 'Intento sin inventario', 3);
            $this->fail('Debió bloquearse la anulación.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('No hay inventario suficiente', $e->getMessage());
        }

        $this->assertSame(1, (int) DB::table('nota_credito')->value('estado_nota_id'));
        $this->assertSame(['aplicacion', 'aplicacion'], DB::table('nota_credito_movimientos')->pluck('tipo')->all());
        $this->assertEquals([0, 0], DB::table('aplicacion_pagos')->pluck('saldo')->map(fn ($v) => $v + 0)->all());
        $this->assertSame(0, DB::table('nota_credito_reversiones')->count());
    }

    public function test_anulacion_permite_seleccionar_bodega_alterna_y_muestra_codigo_producto(): void
    {
        $this->notaAplicada();
        $this->devolucionDeInventario(4);
        DB::table('recibido_bodega')->insert([
            'id' => 2, 'producto_id' => 1, 'seccion_id' => 2, 'cantidad_disponible' => 20,
        ]);

        $previa = $this->servicio()->previsualizarNota(1);
        $producto = $previa['inventario'][0];
        $this->assertSame(1, $producto['codigo']);
        $this->assertSame('Bodega de origen', $producto['bodega']);
        $this->assertSame(10.0, $producto['cantidad']);
        $this->assertCount(2, $producto['bodegas']);

        $this->servicio()->anularNota(1, 'Rebajar bodega alterna', 3, [1 => 2]);

        $this->assertEquals(4, DB::table('recibido_bodega')->where('id', 1)->value('cantidad_disponible'));
        $this->assertEquals(10, DB::table('recibido_bodega')->where('id', 2)->value('cantidad_disponible'));
        $this->assertSame(2, (int) DB::table('log_translado')
            ->where('descripcion', 'Nota credito anulado')
            ->value('origen'));
    }

    public function test_reglas_de_bloqueo_motivo_y_nota_inexistente(): void
    {
        $this->notaAplicada();
        DB::table('expo_cotizacion')->insert(['nota_credito_id' => 1]);

        $previa = $this->servicio()->previsualizarNota(1);
        $this->assertFalse($previa['puede']);
        $this->assertStringContainsString('Oferta Expo', $previa['bloqueos'][0]);

        foreach ([[fn () => $this->servicio()->anularNota(1, 'Motivo valido', 3), 'Oferta Expo'],
                  [fn () => $this->servicio()->anularNota(1, '  x ', 3), 'motivo'],
                  [fn () => $this->servicio()->anularNota(999, 'Motivo valido', 3), 'no existe'],
                  [fn () => $this->servicio()->revertirAplicacion(999, 'Motivo valido', 3), 'no existe']] as [$accion, $texto]) {
            try {
                $accion();
                $this->fail('Debió lanzar DomainException.');
            } catch (DomainException $e) {
                $this->assertStringContainsString($texto, $e->getMessage());
            }
        }

        DB::table('expo_cotizacion')->delete();
        $this->servicio()->anularNota(1, 'Anulación válida', 3);
        $this->assertFalse($this->servicio()->previsualizarAplicacion(1)['puede']);
    }

    public function test_nota_legada_sin_registro_de_aplicaciones_se_anula_y_advierte(): void
    {
        $now = now();
        DB::table('nota_credito')->insert([
            'id' => 1, 'cai' => 'NC-1', 'total' => 100, 'fecha' => '2026-10-05', 'factura_id' => 10,
            'estado_nota_id' => 1, 'estado_rebajado' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);

        $previa = $this->servicio()->previsualizarNota(1);
        $this->assertTrue($previa['puede']);
        $this->assertStringContainsString('anterior al esquema', $previa['advertencias'][0]);

        $this->servicio()->anularNota(1, 'Nota anterior al esquema', 3);

        $this->assertSame(2, (int) DB::table('nota_credito')->value('estado_nota_id'));
        $this->assertSame('anulado', DB::table('nota_credito_creditos')->value('estado'));
    }

    public function test_endpoints_devuelven_resumen_errores_y_banderas_de_asientos(): void
    {
        $this->notaAplicada();

        $respuesta = (new CrearNotaCredito)->anularNotaCredito(new Request(['idNotaCredito' => 1]), $this->servicio());
        $this->assertSame(422, $respuesta->getStatusCode());
        $this->assertSame('Ingrese el motivo de la anulación.', $respuesta->getData(true)['text']);

        $asientos = (new ListadoNotaCredito)->asientos(1)->getData(true);
        $aplicaciones = collect($asientos['asientos'])->where('tipo', 'aplicacion');
        $this->assertTrue($asientos['nota_activa']);
        $this->assertSame([true, true], $aplicaciones->pluck('anulable')->values()->all());
        $this->assertSame([1, 2], $aplicaciones->pluck('movimiento_id')->values()->all());

        $respuesta = (new ListadoNotaCredito)->revertirAplicacion(new Request(['idMovimiento' => 1, 'motivo' => 'Se aplicó mal']), $this->servicio());
        $this->assertSame(200, $respuesta->getStatusCode());
        $this->assertSame('Aplicación anulada', $respuesta->getData(true)['title']);

        $respuesta = (new ListadoNotaCredito)->revertirAplicacion(new Request(['idMovimiento' => 1, 'motivo' => 'Se aplicó mal']), $this->servicio());
        $this->assertSame(422, $respuesta->getStatusCode());

        $respuesta = (new CrearNotaCredito)->anularNotaCredito(new Request(['idNotaCredito' => 1, 'motivo' => 'Nota duplicada']), $this->servicio());
        $this->assertSame(200, $respuesta->getStatusCode());
        $this->assertStringContainsString('aplicación(es) revertida(s)', $respuesta->getData(true)['text']);

        $asientos = (new ListadoNotaCredito)->asientos(1)->getData(true);
        $this->assertFalse($asientos['nota_activa']);
        $this->assertSame([false, false], collect($asientos['asientos'])->where('tipo', 'aplicacion')->pluck('anulable')->values()->all());
        $this->assertSame([true], collect($asientos['asientos'])->where('tipo', 'emision')->pluck('anulado')->values()->all());
        $this->assertSame(3, collect($asientos['asientos'])->filter(fn ($a) => str_starts_with($a['tipo'], 'anulacion'))->count());

        $previa = (new ListadoNotaCredito)->previsualizarAnulacion(1, $this->servicio())->getData(true);
        $this->assertFalse($previa['puede']);
    }
}
