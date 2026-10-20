<?php

namespace Tests\Feature;

use App\Exports\BoletasComprasExport;
use App\Exports\ComprasMesExport;
use App\Livewire\BoletaCompra\CrearBoletaCompra;
use App\Livewire\BoletaCompra\HistorialBoletaCompra;
use App\Livewire\Inventario\ListarCompras;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;

class BoletasComprasTest extends GastosTest
{
    private function boleta(array $cambios = []): int
    {
        $id = DB::table('boleta_compra')->insertGetId($cambios + [
            'numero_boleta' => '000-001-11-00000001', 'cliente' => 'Proveedor de boleta',
            'direccion' => 'Direccion', 'rtn_dni' => '08011999000001', 'fecha' => '2026-10-17',
            'sub_total' => 999, 'total' => 999, 'estado' => 1, 'users_id' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach ([10.25, 20.50] as $linea => $importe) {
            DB::table('boleta_compra_detalle')->insert([
                'boleta_compra_id' => $id, 'linea' => $linea + 1, 'descripcion' => 'Concepto',
                'precio' => $importe, 'cantidad' => 1, 'importe' => $importe,
            ]);
        }
        return $id;
    }

    private function accion(array $ids, string $accion)
    {
        return (new HistorialBoletaCompra)->cambiarReporteCompras(new Request([
            'seleccionados' => $ids, 'accion' => $accion,
        ]));
    }

    public function test_boletas_ciclo_auditado_con_bloqueos_y_retiro(): void
    {
        $a = $this->boleta();
        $b = $this->boleta(['numero_boleta' => 'OTRA']);
        $this->assertCount(0, (new ComprasMesExport('2026-10'))->collection());
        $this->assertSame(2, $this->accion([$a, $b], 'incluir')->getData()->cantidad);
        $this->assertSame(2, DB::table('boleta_compra_auditoria')->count());
        $evento = DB::table('boleta_compra_auditoria')->first();
        $this->assertSame(1, (int) $evento->usuario_id);
        $this->assertSame('incluir_compras', $evento->accion);
        $this->assertSame(['incluir_reporte_compras' => false], json_decode($evento->antes, true));
        $this->assertSame(['incluir_reporte_compras' => true], json_decode($evento->despues, true));
        $this->assertNotEmpty($evento->created_at);
        $listado = collect((new ListarCompras)->listarCompras(new Request())->getData(true)['data']);
        $this->assertTrue($listado->contains('id', 'B-' . $a));
        $this->assertSame('boleta', $listado->first()['tipo_documento']);
        $this->assertSame(422, (new CrearBoletaCompra)->anularBoletaCompra(new Request(['id' => $a]))->status());
        $this->assertSame(422, (new HistorialBoletaCompra)->actualizarBoletaCompra(new Request([
            'id' => $a, 'cliente' => 'Cambio bloqueado', 'fecha' => '2026-10-18', 'total' => 10,
            'conceptos' => json_encode([['descripcion' => 'Cambio', 'precio' => 10, 'cantidad' => 1, 'importe' => 10]]),
        ]))->status());
        Livewire::test(\App\Livewire\BoletaCompra\EditarBoletaCompra::class, ['id' => $a])->assertForbidden();
        $this->assertSame('Proveedor de boleta', DB::table('boleta_compra')->find($a)->cliente);
        $this->assertSame(2, $this->accion([$a, $b], 'quitar')->getData()->cantidad);
        $this->assertCount(0, (new ComprasMesExport('2026-10'))->collection());
        $this->assertSame(4, DB::table('boleta_compra_auditoria')->count());
        $this->assertSame(200, (new CrearBoletaCompra)->anularBoletaCompra(new Request(['id' => $a]))->status());
        $this->assertSame(5, DB::table('boleta_compra_auditoria')->count());
        try {
            $this->accion([$a, $b], 'incluir');
            $this->fail('No debe incluir boletas anuladas.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('seleccionados', $e->errors());
        }
        $this->assertEquals(0, DB::table('boleta_compra')->find($b)->incluir_reporte_compras);
    }

    public function test_boletas_seleccion_mixta_no_duplica_y_rechaza_ids_invalidos(): void
    {
        $a = $this->boleta();
        $b = $this->boleta();
        $this->accion([$a], 'incluir');
        $this->assertSame(1, $this->accion([$a, $b], 'incluir')->getData()->cantidad);
        try {
            $this->accion([$a, $b], 'incluir');
            $this->fail('No debe registrar cambios repetidos.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('seleccionados', $e->errors());
        }
        foreach ([[$a, 999], [$a, $a], []] as $ids) {
            try {
                $this->accion($ids, 'quitar');
                $this->fail('Seleccion invalida aceptada.');
            } catch (ValidationException $e) {
                $this->assertNotEmpty($e->errors());
            }
        }
        $this->assertSame(2, DB::table('boleta_compra_auditoria')->count());
        $this->assertEquals(1, DB::table('boleta_compra')->find($a)->incluir_reporte_compras);
    }

    public function test_boletas_excel_oc_con_importes_detalle_filtros_y_estado(): void
    {
        DB::table('cai_boleta_compra')->insert(['id' => 1, 'cai' => '000CAI']);
        $a = $this->boleta(['cai_boleta_id' => 1]);
        $this->boleta(['numero_boleta' => 'OTRO', 'fecha' => '2026-09-30']);
        $this->accion([$a], 'incluir');
        $filtros = ['desde' => '2026-10-17', 'hasta' => '2026-10-17', 'factura' => '00000001', 'proveedor' => '1'];
        $export = new ComprasMesExport($filtros);
        $this->assertCount(1, $export->collection());
        $fila = $export->collection()->first();
        $this->assertSame('OC-Otros comprobantes de pago', $fila['clase_documento']);
        $this->assertSame('000CAI', $fila['cai']);
        $this->assertSame('Proveedor de boleta', $fila['proveedor']);
        $this->assertSame([30.75, 0.0, 30.75, null], array_values(array_slice($fila, 7)));
        $this->assertCount(0, $export->collection()->filter(fn ($fila) => $fila['monto_al_gasto'] !== null));
        DB::table('proveedores')->insert(['id' => 2, 'nombre' => 'Otro', 'rtn' => 'otro', 'estado_id' => 1]);
        $this->assertCount(0, (new ComprasMesExport(array_replace($filtros, ['proveedor' => '2'])))->collection());
        $filtrosHistorial = ['fechaInicio' => '2026-10-17', 'fechaFinal' => '2026-10-17', 'buscar' => '00000001'];
        $historial = new BoletasComprasExport($filtrosHistorial);
        $this->assertSame('Pasada al reporte de compras', $historial->collection()->first()['estado']);
        $ruta = tempnam(sys_get_temp_dir(), 'boletas-excel-');
        try {
            file_put_contents($ruta, Excel::raw($historial, \Maatwebsite\Excel\Excel::XLSX));
            $libro = \PhpOffice\PhpSpreadsheet\IOFactory::load($ruta);
            $hoja = $libro->getActiveSheet();
            $this->assertSame('OC-Otros comprobantes de pago', $hoja->getCell('B6')->getValue());
            $this->assertSame('000CAI', $hoja->getCell('C6')->getValue());
            $this->assertSame('000-001-11-00000001', $hoja->getCell('D6')->getValue());
            $this->assertSame('Estado', $hoja->getCell('L5')->getValue());
            $this->assertSame('Pasada al reporte de compras', $hoja->getCell('L6')->getValue());
            $this->assertNull($hoja->getCell('K6')->getValue());
            $this->assertSame('A5:L6', $hoja->getAutoFilter()->getRange());
            $this->assertCount(1, $hoja->getDrawingCollection());
            $this->assertSame('E07000', $hoja->getStyle('L5')->getFill()->getStartColor()->getRGB());
            $libro->disconnectWorksheets();
        } finally {
            unlink($ruta);
        }
        $listado = (new HistorialBoletaCompra)->listadoBoletaCompra(new Request($filtrosHistorial))->getData(true)['data'];
        $this->assertCount(1, $listado);
        $this->assertCount(1, $listado[0]['historial']);
        $this->assertStringNotContainsString('onclick="anularBoleta', $listado[0]['opciones']);
    }

    public function test_boletas_filtros_y_estados_historicos_no_se_incluyen_por_defecto(): void
    {
        $a = $this->boleta(['numero_boleta' => 'BOLETA-A']);
        $b = $this->boleta(['numero_boleta' => 'BOLETA-B', 'estado' => 2]);
        $this->boleta(['numero_boleta' => 'FUERA', 'fecha' => '2026-09-30']);
        $filtros = ['fechaInicio' => '2026-10-17', 'fechaFinal' => '2026-10-17', 'buscar' => 'BOLETA-'];
        $export = new BoletasComprasExport($filtros);
        $this->assertSame(['Registrada', 'Anulada'], $export->collection()->pluck('estado')->all());
        $this->assertCount(0, (new ComprasMesExport('2026-10'))->collection());
        $filas = (new HistorialBoletaCompra)->listadoBoletaCompra(new Request($filtros))->getData(true)['data'];
        $this->assertCount(2, $filas);
        foreach ([
            ['fechaInicio' => 'invalido', 'fechaFinal' => '2026-10-17'],
            ['fechaInicio' => '2026-10-18', 'fechaFinal' => '2026-10-17'],
        ] as $invalido) {
            try {
                (new HistorialBoletaCompra)->listadoBoletaCompra(new Request($invalido));
                $this->fail('Rango invalido aceptado.');
            } catch (ValidationException $e) {
                $this->assertNotEmpty($e->errors());
            }
        }
    }
}
