<?php

namespace Tests\Feature;

use App\Livewire\Inventario\DetalleProducto;
use App\Livewire\Inventario\Producto;
use App\Livewire\Inventario\ProductoApoyo;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ProductoApoyoSoloLecturaTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '\..\..\bootstrap\app.php';
        $app->make(Kernel::class)->bootstrap();
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('users', function (Blueprint $t) { $t->id(); $t->string('name'); $t->integer('rol_id')->default(2); });
        Schema::create('rol', fn (Blueprint $t) => $t->id());
        Schema::create('usuario_rol', function (Blueprint $t) { $t->integer('usuario_id'); $t->integer('rol_id'); });
        Schema::create('usuario_submenu', function (Blueprint $t) {
            $t->integer('usuario_id'); $t->integer('sub_menu_id'); $t->boolean('permitido');
        });
        Schema::create('sub_menu', function (Blueprint $t) {
            $t->id(); $t->string('url'); $t->integer('estado_id')->default(1); $t->integer('orden')->default(1);
        });
        Schema::create('rol_submenu', function (Blueprint $t) { $t->integer('rol_id'); $t->integer('sub_menu_id'); });
        Schema::create('producto', function (Blueprint $t) {
            $t->id(); $t->string('nombre'); $t->string('descripcion'); $t->string('codigo_barra')->nullable();
            $t->string('codigo_estatal')->nullable(); $t->integer('sub_categoria_id')->default(1);
            $t->integer('marca_id')->nullable(); $t->integer('estado_producto_id')->default(1);
            $t->integer('unidad_medida_compra_id')->default(1); $t->decimal('unidadad_compra')->default(1);
            $t->integer('tiempo_recuperacion_meses')->nullable(); $t->string('origen')->nullable();
            $t->integer('users_id')->default(1); $t->timestamps();
        });
        Schema::create('sub_categoria', function (Blueprint $t) { $t->id(); $t->integer('categoria_producto_id'); });
        Schema::create('categoria_producto', function (Blueprint $t) { $t->id(); $t->string('descripcion'); });
        Schema::create('marca', fn (Blueprint $t) => $t->id());
        Schema::create('compra', function (Blueprint $t) { $t->id(); $t->integer('estado_compra_id'); });
        Schema::create('recibido_bodega', function (Blueprint $t) {
            $t->id(); $t->integer('compra_id')->nullable(); $t->integer('producto_id'); $t->decimal('cantidad_disponible');
        });
        DB::table('users')->insert(['id' => 1, 'name' => 'Usuario apoyo']);
        DB::table('sub_menu')->insert([
            ['id' => 1, 'url' => 'producto/apoyo/registro'],
            ['id' => 2, 'url' => 'producto/registro'],
        ]);
        DB::table('usuario_submenu')->insert([
            ['usuario_id' => 1, 'sub_menu_id' => 1, 'permitido' => true],
            ['usuario_id' => 1, 'sub_menu_id' => 2, 'permitido' => false],
        ]);
        DB::table('categoria_producto')->insert(['id' => 1, 'descripcion' => 'Categoria']);
        DB::table('sub_categoria')->insert(['id' => 1, 'categoria_producto_id' => 1]);
        DB::table('producto')->insert([
            ['id' => 1, 'nombre' => 'Activo', 'descripcion' => 'Producto activo', 'estado_producto_id' => 1],
            ['id' => 2, 'nombre' => 'Inactivo', 'descripcion' => 'Producto inactivo', 'estado_producto_id' => 2],
        ]);
        $this->actingAs(User::findOrFail(1));
    }

    public function test_support_list_only_links_to_read_only_detail_for_active_and_inactive_products(): void
    {
        $response = (new ProductoApoyo())->listarProductos(Request::create('/apoyo/listar/productos'));
        $this->assertSame(200, $response->getStatusCode());
        $rows = $response->getData()->data;
        $this->assertCount(2, $rows);
        foreach ($rows as $row) {
            $this->assertStringContainsString('/producto/apoyo/detalle/' . $row->codigo, $row->acciones);
            $this->assertStringContainsString('Ver detalle', $row->acciones);
            $this->assertStringNotContainsString('Inactivar', $row->acciones);
            $this->assertStringNotContainsString('> Activar', $row->acciones);
            $this->assertStringNotContainsString('cambiarEstado', $row->acciones);
        }
    }

    public function test_support_detail_stays_read_only_even_if_user_can_also_edit_normal_catalog(): void
    {
        $detalle = new DetalleProducto();
        $detalle->mount(1);
        $this->assertTrue($detalle->soloLectura);
        DB::table('usuario_submenu')->where('sub_menu_id', 2)->update(['permitido' => true]);
        $detalle = new DetalleProducto();
        $detalle->mount(1, true);
        $this->assertTrue($detalle->soloLectura);
        $normal = new DetalleProducto();
        $normal->mount(1);
        $this->assertFalse($normal->soloLectura);
        $route = app('router')->getRoutes()->match(Request::create('/producto/apoyo/detalle/1'));
        $this->assertTrue($route->defaults['soloLectura']);
        $route = app('router')->getRoutes()->match(Request::create('/producto/apoyo/detalle/1/unidades'));
        $this->assertTrue($route->defaults['soloLectura']);
    }

    public function test_support_state_endpoint_never_modifies_product_even_with_normal_catalog_access(): void
    {
        DB::table('usuario_submenu')->where('sub_menu_id', 2)->update(['permitido' => true]);
        foreach ([1 => 2, 2 => 1] as $id => $estado) {
            $response = (new ProductoApoyo())->inactivarProducto(Request::create('/apoyo/inactivar', 'POST', compact('id', 'estado')));
            $this->assertSame(403, $response->getStatusCode());
            $this->assertSame($id === 1 ? 1 : 2, DB::table('producto')->where('id', $id)->value('estado_producto_id'));
        }
    }

    public function test_support_only_user_cannot_bypass_restriction_using_normal_mutation_endpoints(): void
    {
        $actions = [
            [new Producto(), 'editarProducto', ['id_producto_edit' => 1, 'nombre_producto_edit' => 'Alterado']],
            [new Producto(), 'inactivarProducto', ['id' => 1, 'estado' => 2]],
            [new DetalleProducto(), 'editarUnidadesVenta', ['idUniadVenta' => 1]],
        ];
        foreach ($actions as [$component, $method, $data]) {
            try {
                $component->$method(Request::create('/producto', 'POST', $data));
                $this->fail('La accion debe denegarse sin permiso del catalogo normal.');
            } catch (HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
        }
        $this->assertSame('Activo', DB::table('producto')->where('id', 1)->value('nombre'));
        $this->assertSame(1, DB::table('producto')->where('id', 1)->value('estado_producto_id'));
    }

    public function test_normal_catalog_keeps_editing_and_changing_state_when_authorized(): void
    {
        DB::table('usuario_submenu')->where('sub_menu_id', 2)->update(['permitido' => true]);
        $response = (new Producto())->editarProducto(Request::create('/producto/editar', 'POST', [
            'id_producto_edit' => 1, 'nombre_producto_edit' => 'Editado', 'descripcion_producto_edit' => 'Descripcion editada',
            'cod_barra_producto_edit' => '', 'cod_estatal_producto_edit' => '', 'sub_categoria_producto_edit' => 1,
            'unidades_editar' => 1, 'unidad_producto_editar' => 1,
        ]));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Editado', DB::table('producto')->where('id', 1)->value('nombre'));
        $response = (new Producto())->inactivarProducto(Request::create('/producto/inactivar', 'POST', ['id' => 1, 'estado' => 2]));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(2, DB::table('producto')->where('id', 1)->value('estado_producto_id'));
    }
}
