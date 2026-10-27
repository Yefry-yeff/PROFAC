<?php

namespace Tests\Feature;

use App\Livewire\Proveedores;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProveedoresTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '\..\..\bootstrap\app.php';
        $app->make(Kernel::class)->bootstrap();
        $app['config']->set('database.connections.proveedores_testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]);
        $app['config']->set('database.default', 'proveedores_testing');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('proveedores_testing', DB::connection()->getName());
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        foreach (['pais', 'departamento', 'municipio', 'categoria', 'tipo_personalidad', 'retenciones'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id();
                $table->string('nombre');
                if ($name === 'departamento') {
                    $table->integer('pais_id');
                }
                if ($name === 'municipio') {
                    $table->integer('departamento_id');
                }
            });
            $row = ['id' => 1, 'nombre' => 'Prueba'];
            if ($name === 'departamento') {
                $row['pais_id'] = 1;
            }
            if ($name === 'municipio') {
                $row['departamento_id'] = 1;
            }
            DB::table($name)->insert($row);
        }
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable();
            foreach (['nombre', 'direccion', 'contacto', 'telefono_1', 'telefono_2', 'correo_1', 'correo_2', 'rtn'] as $field) {
                $table->string($field);
            }
            foreach (['registrado_por', 'estado_id', 'municipio_id', 'tipo_personalidad_id', 'categoria_id'] as $field) {
                $table->integer($field);
            }
            $table->timestamps();
        });
        Schema::create('retenciones_has_proveedores', function (Blueprint $table) {
            $table->integer('proveedores_id');
            $table->integer('retenciones_id');
            $table->timestamps();
        });
        DB::table('users')->insert(['id' => 1, 'name' => 'Prueba']);
        $this->actingAs(User::findOrFail(1));
    }

    private function datos(string $rtn = '08011999000001'): array
    {
        return [
            'rtn_prov' => $rtn, 'nombre_prov' => 'Proveedor de prueba',
            'categoria_prov' => 1, 'giro_neg_prov' => 1, 'telefono_prov' => '99999999',
            'pais_prov' => 1, 'depto_prov' => 1, 'municipio_prov' => 1, 'direccion_prov' => 'Direccion de prueba',
        ];
    }

    private function crear(array $datos)
    {
        return (new Proveedores)->proveerdoresModelInsert(new Request($datos));
    }

    public function test_codigo_se_genera_con_el_id_autoincremental_y_opcionales_pueden_omitirse(): void
    {
        foreach (['08011999000001', '08011999000002'] as $rtn) {
            $response = $this->crear($this->datos($rtn) + ['codigo_prov' => 'IGNORAR']);
            $this->assertSame(200, $response->getStatusCode(), $response->getContent());
            $id = $response->getData(true)['proveedor']['id'];
            $row = DB::table('proveedores')->find($id);
            $this->assertSame((string) $id, $row->codigo);
            $this->assertSame('', $row->correo_1);
            $this->assertSame('', $row->telefono_2);
            $this->assertSame('', $row->contacto);
        }
        $this->assertSame(2, DB::table('proveedores')->max('id'));
        $this->assertSame(0, DB::table('retenciones_has_proveedores')->count());
    }

    public function test_rechaza_rtn_repetido_incluso_con_espacios_y_proveedor_inactivo(): void
    {
        $this->crear($this->datos());
        DB::table('proveedores')->update(['estado_id' => 2]);
        $response = $this->crear($this->datos(' 08011999000001 '));
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('Ya existe un proveedor con este RTN.', $response->getData(true)['errors']['rtn_prov'][0]);
        $this->assertSame(1, DB::table('proveedores')->count());
    }

    public function test_edicion_conserva_codigo_y_datos_que_ya_no_estan_en_el_formulario(): void
    {
        $this->crear($this->datos() + ['contacto_prov' => 'Contacto previo', 'correo_prov_2' => 'otro@example.test']);
        $datos = ['idProveedor' => 1, 'editar_codigo_prov' => 'NO CAMBIAR'];
        foreach ($this->datos() as $key => $value) {
            $datos['editar_' . $key] = $value;
        }
        $response = (new Proveedores)->editarProveedor(new Request($datos));
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $row = DB::table('proveedores')->find(1);
        $this->assertSame('1', $row->codigo);
        $this->assertSame('Contacto previo', $row->contacto);
        $this->assertSame('otro@example.test', $row->correo_2);

        $this->crear($this->datos('08011999000002'));
        $datos['editar_rtn_prov'] = '08011999000002';
        $response = (new Proveedores)->editarProveedor(new Request($datos));
        $this->assertSame(422, $response->getStatusCode());
        $this->assertArrayHasKey('editar_rtn_prov', $response->getData(true)['errors']);
        $this->assertSame('08011999000001', DB::table('proveedores')->find(1)->rtn);
    }

    public function test_valida_obligatorios_correo_y_jerarquia_de_direccion(): void
    {
        $response = $this->crear([]);
        $this->assertSame(422, $response->getStatusCode());
        foreach (['rtn_prov', 'nombre_prov', 'categoria_prov', 'giro_neg_prov', 'telefono_prov', 'municipio_prov', 'direccion_prov'] as $campo) {
            $this->assertArrayHasKey($campo, $response->getData(true)['errors']);
        }
        $datos = $this->datos();
        $datos['correo_prov'] = 'no-es-correo';
        $datos['pais_prov'] = '';
        $response = $this->crear($datos);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertArrayHasKey('correo_prov', $response->getData(true)['errors']);
        $this->assertArrayHasKey('pais_prov', $response->getData(true)['errors']);

        DB::table('departamento')->insert(['id' => 2, 'nombre' => 'Otro departamento', 'pais_id' => 1]);
        $datos = $this->datos();
        $datos['depto_prov'] = 2;
        $response = $this->crear($datos);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertArrayHasKey('municipio_prov', $response->getData(true)['errors']);
        $this->assertSame(0, DB::table('proveedores')->count());
    }

    public function test_conserva_duplicados_historicos_sin_permitir_otros_nuevos(): void
    {
        $this->crear($this->datos());
        $legacy = (array) DB::table('proveedores')->find(1);
        unset($legacy['id']);
        $legacy['codigo'] = 'LEGADO';
        DB::table('proveedores')->insert($legacy);

        $datos = ['idProveedor' => 2];
        foreach ($this->datos() as $key => $value) {
            $datos['editar_' . $key] = $value;
        }
        $datos['editar_nombre_prov'] = 'Nombre actualizado';
        $response = (new Proveedores)->editarProveedor(new Request($datos));
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame('LEGADO', DB::table('proveedores')->find(2)->codigo);
        $this->assertSame(2, DB::table('proveedores')->where('rtn', '08011999000001')->count());
        $this->assertSame(422, $this->crear($this->datos())->getStatusCode());
    }

    public function test_formulario_tiene_tres_secciones_y_solo_los_campos_solicitados(): void
    {
        $html = view('livewire.partials.proveedor-form', [
            'editar' => false,
            'categorias' => DB::table('categoria')->get(),
            'tipoPersonalidad' => DB::table('tipo_personalidad')->get(),
            'paises' => DB::table('pais')->get(),
            'retenciones' => DB::table('retenciones')->get(),
        ])->render();
        $this->assertSame(3, substr_count($html, '<fieldset'));
        foreach (array_keys($this->datos()) as $campo) {
            $this->assertStringContainsString('name="' . $campo . '"', $html);
        }
        foreach (['codigo_prov', 'contacto_prov', 'correo_prov_2'] as $campo) {
            $this->assertStringNotContainsString('name="' . $campo . '"', $html);
        }
        foreach (['retencion_prov', 'rtn_sin_registro', 'telefono_1_sin_registro', 'telefono_2_sin_registro'] as $campo) {
            $this->assertStringContainsString('name="' . $campo . '"', $html);
        }
    }

    public function test_sin_registro_permite_varios_rtn_na_y_guarda_telefonos_vacios(): void
    {
        $datos = $this->datos();
        unset($datos['rtn_prov'], $datos['telefono_prov']);
        $datos += ['rtn_sin_registro' => '1', 'telefono_1_sin_registro' => '1', 'telefono_2_sin_registro' => '1'];
        for ($i = 0; $i < 2; $i++) {
            $response = $this->crear($datos);
            $this->assertSame(200, $response->getStatusCode(), $response->getContent());
            $row = DB::table('proveedores')->find($response->getData(true)['proveedor']['id']);
            $this->assertSame('N/A', $row->rtn);
            $this->assertSame('', $row->telefono_1);
            $this->assertSame('', $row->telefono_2);
        }
        $this->assertSame(2, DB::table('proveedores')->where('rtn', 'N/A')->count());
    }

    public function test_rtn_alfanumerico_y_formato_de_telefonos_se_validan_en_servidor(): void
    {
        $datos = $this->datos('AB0801C123');
        $datos['telefono_prov_2'] = '22223333';
        $response = $this->crear($datos);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $row = DB::table('proveedores')->find(1);
        $this->assertSame('AB0801C123', $row->rtn);
        $this->assertSame('9999-9999', $row->telefono_1);
        $this->assertSame('2222-3333', $row->telefono_2);
        $this->assertSame(422, $this->crear($datos)->getStatusCode());

        foreach (['N/A', 'AB-123', 'AB 123', '<ABC>'] as $rtn) {
            $response = $this->crear($this->datos($rtn));
            $this->assertSame(422, $response->getStatusCode(), $rtn);
            $this->assertArrayHasKey('rtn_prov', $response->getData(true)['errors']);
        }
        foreach (['123', '123456789', '1234-ABCD', '123-45678'] as $telefono) {
            $datos = $this->datos('OTRO123');
            $datos['telefono_prov'] = $telefono;
            $datos['telefono_prov_2'] = $telefono;
            $response = $this->crear($datos);
            $this->assertSame(422, $response->getStatusCode());
            $this->assertArrayHasKey('telefono_prov', $response->getData(true)['errors']);
            $this->assertArrayHasKey('telefono_prov_2', $response->getData(true)['errors']);
        }
        $datos = $this->datos('OTRO123');
        $datos['telefono_prov'] = '';
        $this->assertSame(422, $this->crear($datos)->getStatusCode());
    }

    public function test_edicion_sin_registro_y_retencion_se_guardan_sin_afectar_otros_proveedores(): void
    {
        DB::table('retenciones')->insert(['id' => 2, 'nombre' => 'Retencion 1%']);
        $this->crear($this->datos() + ['retencion_prov' => 2]);
        $this->crear($this->datos('SEGUNDO2') + ['retencion_prov' => 2]);
        $this->assertSame(2, DB::table('retenciones_has_proveedores')->count());
        $datos = ['idProveedor' => 1];
        foreach ($this->datos() as $key => $value) {
            $datos['editar_' . $key] = $value;
        }
        $datos += [
            'editar_rtn_sin_registro' => '1', 'editar_telefono_1_sin_registro' => '1',
            'editar_telefono_2_sin_registro' => '1', 'editar_retencion_prov' => 1,
        ];
        $response = (new Proveedores)->editarProveedor(new Request($datos));
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $row = DB::table('proveedores')->find(1);
        $this->assertSame('N/A', $row->rtn);
        $this->assertSame('', $row->telefono_1);
        $this->assertSame('', $row->telefono_2);
        $this->assertSame(0, DB::table('retenciones_has_proveedores')->where('proveedores_id', 1)->count());
        $this->assertSame(1, DB::table('retenciones_has_proveedores')->where('proveedores_id', 2)->count());

        $datos['editar_retencion_prov'] = 2;
        $response = (new Proveedores)->editarProveedor(new Request($datos));
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame(2, DB::table('retenciones_has_proveedores')->where('proveedores_id', 1)->value('retenciones_id'));
        $response = (new Proveedores)->editarProveedor(new Request($datos));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(1, DB::table('retenciones_has_proveedores')->where('proveedores_id', 1)->count());

        $datos['editar_retencion_prov'] = 9999;
        $response = (new Proveedores)->editarProveedor(new Request($datos));
        $this->assertSame(422, $response->getStatusCode());
        $this->assertArrayHasKey('editar_retencion_prov', $response->getData(true)['errors']);
        $this->assertSame(2, DB::table('retenciones_has_proveedores')->count());

        unset($datos['editar_rtn_sin_registro'], $datos['editar_telefono_1_sin_registro'], $datos['editar_telefono_2_sin_registro']);
        $datos['editar_retencion_prov'] = 2;
        $datos['editar_rtn_prov'] = 'ALFA1234';
        $datos['editar_telefono_prov'] = '9876-5432';
        $datos['editar_telefono_prov_2'] = '22223333';
        $response = (new Proveedores)->editarProveedor(new Request($datos));
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $row = DB::table('proveedores')->find(1);
        $this->assertSame('ALFA1234', $row->rtn);
        $this->assertSame('9876-5432', $row->telefono_1);
        $this->assertSame('2222-3333', $row->telefono_2);
    }
}
