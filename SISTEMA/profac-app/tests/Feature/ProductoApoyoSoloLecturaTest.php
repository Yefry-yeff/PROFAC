<?php

namespace Tests\Feature;

use App\Livewire\Inventario\DetalleProducto;
use App\Livewire\Inventario\Producto;
use App\Livewire\Inventario\ProductoApoyo;
use App\Models\SubMenu;
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
    private const OPCIONES = [
        10 => SubMenu::OPCION_PRODUCTOS_VER_DETALLE,
        11 => SubMenu::OPCION_PRODUCTOS_EDITAR,
        12 => SubMenu::OPCION_PRODUCTOS_SUBIR_FOTO,
        13 => SubMenu::OPCION_PRODUCTOS_CREAR,
        14 => SubMenu::OPCION_PRODUCTOS_CARGA_MASIVA,
        15 => SubMenu::OPCION_PRODUCTOS_EXPORTAR,
        16 => SubMenu::OPCION_PRODUCTOS_INACTIVAR,
        17 => SubMenu::OPCION_PRODUCTOS_UNIDADES_VENTA,
        18 => SubMenu::OPCION_APOYO_SUBIR_FOTO,
    ];

    private function otorgar(string ...$urls): void
    {
        foreach ($urls as $url) {
            DB::table('usuario_submenu')->insert([
                'usuario_id' => 1, 'sub_menu_id' => array_search($url, self::OPCIONES, true), 'permitido' => true,
            ]);
        }
    }

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
        foreach (self::OPCIONES as $id => $url) {
            DB::table('sub_menu')->insert(['id' => $id, 'url' => $url]);
        }
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
        $this->otorgar(SubMenu::OPCION_PRODUCTOS_EDITAR, SubMenu::OPCION_PRODUCTOS_INACTIVAR);
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

    public function test_catalog_access_alone_does_not_grant_any_option(): void
    {
        DB::table('usuario_submenu')->where('sub_menu_id', 2)->update(['permitido' => true]);
        $producto = new Producto();
        $acciones = [
            fn () => $producto->editarProducto(Request::create('/producto/editar', 'POST', ['id_producto_edit' => 1])),
            fn () => $producto->inactivarProducto(Request::create('/producto/inactivar', 'POST', ['id' => 1, 'estado' => 2])),
            fn () => $producto->crearProducto(Request::create('/producto/registrar', 'POST')),
            fn () => $producto->export(Request::create('/producto/excel')),
            fn () => $producto->guardarFoto(Request::create('/ruta/imagen/edit', 'POST')),
            fn () => $producto->descargarPlantillaCargaMasiva(app(\App\Services\Inventario\ProductoCargaMasivaService::class)),
            fn () => (new DetalleProducto())->editarUnidadesVenta(Request::create('/detalle/unidades/editar', 'POST')),
        ];
        foreach ($acciones as $indice => $accion) {
            try {
                $accion();
                $this->fail("La accion {$indice} debe denegarse sin su opcion.");
            } catch (HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
        }

        $detalle = new DetalleProducto();
        $detalle->mount(1);
        $this->assertFalse($detalle->soloLectura);
        $this->assertFalse($detalle->puedeEditar);
        $this->assertFalse($detalle->puedeSubirFoto);
        $this->assertFalse($detalle->puedeEditarUnidades);
    }

    public function test_detail_flags_follow_each_option_independently(): void
    {
        DB::table('usuario_submenu')->where('sub_menu_id', 2)->update(['permitido' => true]);
        $this->otorgar(SubMenu::OPCION_PRODUCTOS_SUBIR_FOTO, SubMenu::OPCION_PRODUCTOS_UNIDADES_VENTA);

        $detalle = new DetalleProducto();
        $detalle->mount(1);
        $this->assertFalse($detalle->puedeEditar);
        $this->assertTrue($detalle->puedeSubirFoto);
        $this->assertTrue($detalle->puedeEditarUnidades);
    }

    public function test_options_are_inherited_from_roles_and_user_exceptions_revoke_them(): void
    {
        DB::table('usuario_submenu')->where('sub_menu_id', 2)->update(['permitido' => true]);
        DB::table('rol_submenu')->insert(['rol_id' => 2, 'sub_menu_id' => 11]);
        $this->assertTrue(User::find(1)->tienePermiso(SubMenu::OPCION_PRODUCTOS_EDITAR));

        DB::table('usuario_submenu')->insert(['usuario_id' => 1, 'sub_menu_id' => 11, 'permitido' => false]);
        $this->assertFalse(User::find(1)->tienePermiso(SubMenu::OPCION_PRODUCTOS_EDITAR));
    }

    public function test_support_catalog_only_allows_uploading_photos_with_its_option(): void
    {
        $detalle = new DetalleProducto();
        $detalle->mount(1, true);
        $this->assertFalse($detalle->puedeSubirFoto);

        $this->otorgar(SubMenu::OPCION_APOYO_SUBIR_FOTO);
        $detalle = new DetalleProducto();
        $detalle->mount(1, true);
        $this->assertTrue($detalle->soloLectura);
        $this->assertTrue($detalle->puedeSubirFoto);
        $this->assertFalse($detalle->puedeEditar);
        $this->assertFalse($detalle->puedeEditarUnidades);

        $response = (new Producto())->guardarFoto(Request::create('/ruta/imagen/edit', 'POST'));
        $this->assertSame(200, $response->getStatusCode());
    }
}
