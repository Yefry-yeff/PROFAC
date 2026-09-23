<?php

namespace Tests\Feature;

use App\Http\Livewire\Flujo\RevicionInventario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use Tests\TestCase;

class RevisionInventarioBodegaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_sincroniza_bodega_autoseleccionada_aunque_la_linea_no_este_marcada_sin_existencia(): void
    {
        $linea = DB::table('cotizacion_has_producto as chp')
            ->join('recibido_bodega as rb', function ($join) {
                $join->on('rb.producto_id', '=', 'chp.producto_id')
                    ->whereColumn('rb.seccion_id', '!=', 'chp.seccion_id');
            })
            ->join('seccion as s', 's.id', '=', 'rb.seccion_id')
            ->join('segmento as sg', 'sg.id', '=', 's.segmento_id')
            ->join('bodega as b', 'b.id', '=', 'sg.bodega_id')
            ->where('chp.resta_inventario', 1)
            ->where('rb.cantidad_disponible', '>', 0)
            ->select(
                'chp.id',
                'chp.cotizacion_id',
                'chp.producto_id',
                'chp.Bodega_id as bodega_origen_id',
                'chp.seccion_id as seccion_origen_id',
                'rb.seccion_id as seccion_destino_id',
                'sg.bodega_id as bodega_destino_id',
                'b.nombre as bodega_destino_nombre'
            )
            ->first();

        $this->assertNotNull($linea, 'No existe una línea con stock en una bodega alterna para ejecutar la prueba.');

        $componente = new RevicionInventario();
        $componente->cotizacionId = (int) $linea->cotizacion_id;
        $componente->esOfertaExpo = false;
        $componente->productos = [[
            'idx' => 0,
            'cotizacion_has_producto_id' => (int) $linea->id,
            'nombre_producto' => 'Producto de prueba',
            'cantidad' => 1,
            'bodega_id' => (int) $linea->bodega_origen_id,
            'seccion_id' => (int) $linea->seccion_origen_id,
            'sin_existencia' => false,
            'destinos_bodega' => [[
                'value' => $linea->bodega_destino_id . '|' . $linea->seccion_destino_id,
                'bodega_nombre' => (string) $linea->bodega_destino_nombre,
                'stock' => 1,
            ]],
        ]];
        $componente->bodegaExpoSeleccionada = [
            0 => $linea->bodega_destino_id . '|' . $linea->seccion_destino_id,
        ];

        $metodo = new ReflectionMethod($componente, 'sincronizarBodegasNormalesSeleccionadas');
        $metodo->setAccessible(true);

        $this->assertTrue($metodo->invoke($componente));
        $this->assertDatabaseHas('cotizacion_has_producto', [
            'id' => $linea->id,
            'Bodega_id' => $linea->bodega_destino_id,
            'seccion_id' => $linea->seccion_destino_id,
            'nombre_bodega' => $linea->bodega_destino_nombre,
        ]);
    }
}