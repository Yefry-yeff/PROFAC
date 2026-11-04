<?php

namespace App\Exports\Reportes;

use App\Exports\Reportes\Expo\EstiloExpo;
use App\Exports\Reportes\Expo\EtiquetasExpo;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel del detalle de una oferta de Expo: datos generales, resumen,
 * productos ofertados, facturas relacionadas y productos facturados.
 */
class ReporteExpoOfertaExport implements FromArray, WithTitle, WithEvents
{
    use EstiloExpo;

    private const COLUMNAS_PRODUCTOS = 17;

    public function __construct(private array $detalle, private array $info = [])
    {
    }

    public function array(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Oferta ' . $this->detalle['oferta']['id'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => fn (AfterSheet $event) => $this->dibujar($event->sheet->getDelegate()),
        ];
    }

    private function dibujar(Worksheet $s): void
    {
        $o = $this->detalle['oferta'];
        $r = $this->detalle['resumen'];
        $n = self::COLUMNAS_PRODUCTOS;
        $titulo = 'Reporte Expo · Oferta #' . $o['id'] . ' · ' . $o['cliente'];
        $fila = $this->escribirTitulo($s, $titulo, $this->info, $n);

        $this->escribirBarra($s, $fila++, 'DATOS DE LA OFERTA', 8);
        $fila = $this->cuadricula($s, $fila, [
            ['Oferta #', $o['id'], 'id'], ['Flujo', $o['flujo_id'], 'id'], ['Expo', $o['expo']], ['Estado', EtiquetasExpo::estado($o['estado'])],
            ['Cliente', $o['cliente']], ['RTN', $o['rtn']], ['Asesor', $o['asesor']], ['Teleasesor', $o['teleasesor']],
            ['Fecha', $o['fecha'], 'fecha'], ['Hora', $o['hora']], ['Tipo de venta', $o['tipo_venta']], ['Condición de pago', $o['condicion_pago']],
            ['Sección', $o['nombre_seccion'] ? trim(($o['numero_seccion'] ? 'Sección ' . $o['numero_seccion'] . ' · ' : '') . $o['nombre_seccion']) : 'Oferta principal'],
            ['Oferta origen', $o['oferta_origen_id'], 'id'], ['Facturación', EtiquetasExpo::estado($r['estado_facturacion'] ?? null)], ['Creado por', $o['creado_por'] ?? null],
        ]);
        if (!empty($o['nota'])) {
            $this->escribirNota($s, $fila++, 'Nota: ' . $o['nota'], 8);
        }

        $fila++;
        $this->escribirBarra($s, $fila++, 'RESUMEN (lempiras)', 8);
        $fila = $this->cuadricula($s, $fila, [
            ['Subtotal original', $r['subtotal_original'], 'moneda'], ['Descuento', $r['descuento'], 'moneda'],
            ['Subtotal final', $r['subtotal_final'], 'moneda'], ['ISV', $r['isv'], 'moneda'],
            ['Total', $r['total'], 'moneda'], ['Costo', $r['costo'], 'moneda'],
            ['Utilidad', $r['utilidad'], 'moneda'], ['Margen %', $r['margen_pct'], 'pct'],
            ['Facturado sin ISV', $r['total_facturado'] ?? 0, 'moneda'], ['Pendiente por facturar', $r['diferencia_pendiente'] ?? 0, 'moneda'],
            ['% Avance', ($r['subtotal_final'] ?? 0) > 0 ? ($r['total_facturado'] ?? 0) * 100 / $r['subtotal_final'] : 0, 'pct'],
        ]);

        $fila++;
        $this->escribirBarra($s, $fila++, 'PRODUCTOS OFERTADOS', $n);
        $encabezadoProductos = $fila;
        $fila = $this->escribirTabla($s, $fila, [
            ['titulo' => 'Código', 'ancho' => 16],
            ['titulo' => 'Producto', 'ancho' => 42],
            ['titulo' => 'Marca'],
            ['titulo' => 'Categoría'],
            ['titulo' => 'Escala', 'ancho' => 14],
            ['titulo' => 'Cantidad', 'formato' => 'cantidad', 'total' => 'suma'],
            ['titulo' => 'Precio base', 'formato' => 'moneda'],
            ['titulo' => 'Precio antes de descuento', 'formato' => 'moneda'],
            ['titulo' => 'Descuento', 'formato' => 'moneda', 'total' => 'suma'],
            ['titulo' => 'Descuento %', 'formato' => 'pct', 'ancho' => 12],
            ['titulo' => 'Precio final', 'formato' => 'moneda'],
            ['titulo' => 'Subtotal', 'clave' => 'subtotal', 'formato' => 'moneda', 'total' => 'suma'],
            ['titulo' => 'ISV', 'formato' => 'moneda', 'total' => 'suma'],
            ['titulo' => 'Total', 'formato' => 'moneda', 'total' => 'suma'],
            ['titulo' => 'Costo', 'formato' => 'moneda', 'total' => 'suma'],
            ['titulo' => 'Utilidad', 'clave' => 'utilidad', 'formato' => 'moneda', 'total' => 'suma'],
            ['titulo' => 'Margen %', 'formato' => 'pct', 'total' => ['ratio', 'utilidad', 'subtotal'], 'ancho' => 12],
        ], array_map(fn ($p) => [
            $p['codigo'], $p['producto'], $p['marca'], $p['categoria'], $p['escala'], $p['cantidad'],
            $p['precio_base'], $p['precio_antes_descuento'], $p['descuento'], $p['descuento_pct'], $p['precio_final'],
            $p['subtotal_final'], $p['isv'], $p['total'], $p['costo_total'], $p['utilidad'], $p['margen_pct'],
        ], $this->detalle['productos']));

        $facturas = $this->detalle['facturas'];
        $fila += 2;
        $this->escribirBarra($s, $fila++, 'FACTURAS RELACIONADAS (' . count($facturas) . ')', 14);
        $fila = $this->escribirTabla($s, $fila, [
            ['titulo' => 'Factura'],
            ['titulo' => 'Fecha', 'formato' => 'fecha'],
            ['titulo' => 'Estado'],
            ['titulo' => 'Asesor'],
            ['titulo' => 'Teleasesor'],
            ['titulo' => 'Subtotal factura', 'formato' => 'moneda', 'total' => 'suma'],
            ['titulo' => 'ISV factura', 'formato' => 'moneda', 'total' => 'suma'],
            ['titulo' => 'Total factura', 'formato' => 'moneda', 'total' => 'suma'],
            ['titulo' => 'Subtotal de esta oferta', 'clave' => 'subtotal', 'formato' => 'moneda', 'total' => 'suma'],
            ['titulo' => 'Costo', 'formato' => 'moneda', 'total' => 'suma'],
            ['titulo' => 'Utilidad', 'clave' => 'utilidad', 'formato' => 'moneda', 'total' => 'suma'],
            ['titulo' => 'Margen %', 'formato' => 'pct', 'total' => ['ratio', 'utilidad', 'subtotal']],
            ['titulo' => 'Cantidad aplicada', 'formato' => 'cantidad', 'total' => 'suma'],
            ['titulo' => 'Productos', 'formato' => 'entero', 'total' => 'suma'],
        ], array_map(fn ($f) => [
            $f['numero'], $f['fecha'], EtiquetasExpo::estado($f['estado']), $f['asesor'], $f['teleasesor'],
            $f['subtotal'], $f['isv'], $f['total'], $f['subtotal_relacionado'], $f['costo'], $f['utilidad'],
            $f['margen_pct'], $f['cantidad_aplicada'], count($f['productos']),
        ], $facturas), ['vacio' => 'Esta oferta no tiene facturas directas. Si tiene secciones, sus facturas están en el Excel de cada sección.']);

        $lineas = [];
        foreach ($facturas as $f) {
            foreach ($f['productos'] as $p) {
                $lineas[] = [
                    $f['numero'], $p['codigo'], $p['producto'], $p['marca'], $p['cantidad'], $p['precio'],
                    $p['descuento'], $p['subtotal'], $p['isv'], $p['total'], $p['costo'], $p['utilidad'], $p['margen_pct'],
                ];
            }
        }
        if ($lineas) {
            $fila += 2;
            $this->escribirBarra($s, $fila++, 'PRODUCTOS FACTURADOS', 13);
            $this->escribirTabla($s, $fila, [
                ['titulo' => 'Factura'],
                ['titulo' => 'Código'],
                ['titulo' => 'Producto'],
                ['titulo' => 'Marca'],
                ['titulo' => 'Cantidad', 'formato' => 'cantidad', 'total' => 'suma'],
                ['titulo' => 'Precio', 'formato' => 'moneda'],
                ['titulo' => 'Descuento', 'formato' => 'moneda', 'total' => 'suma'],
                ['titulo' => 'Subtotal', 'clave' => 'subtotal', 'formato' => 'moneda', 'total' => 'suma'],
                ['titulo' => 'ISV', 'formato' => 'moneda', 'total' => 'suma'],
                ['titulo' => 'Total', 'formato' => 'moneda', 'total' => 'suma'],
                ['titulo' => 'Costo', 'formato' => 'moneda', 'total' => 'suma'],
                ['titulo' => 'Utilidad', 'clave' => 'utilidad', 'formato' => 'moneda', 'total' => 'suma'],
                ['titulo' => 'Margen %', 'formato' => 'pct', 'total' => ['ratio', 'utilidad', 'subtotal']],
            ], $lineas);
        }

        $this->configurarImpresion($s, $encabezadoProductos);
        $s->setSelectedCell('A1');
    }

    /** Pares etiqueta/valor en filas de 4 pares (columnas A-H). Devuelve la siguiente fila libre. */
    private function cuadricula(Worksheet $s, int $fila, array $pares): int
    {
        $inicio = $fila;
        foreach (array_chunk($pares, 4) as $grupo) {
            foreach ($grupo as $i => $par) {
                $colEtiqueta = $i * 2 + 1;
                $etiqueta = Coordinate::stringFromColumnIndex($colEtiqueta) . $fila;
                $s->setCellValue($etiqueta, $par[0]);
                $s->getStyle($etiqueta)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '334155']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                ]);
                $formato = $par[2] ?? 'texto';
                $this->escribirValor($s, $colEtiqueta + 1, $fila, $par[1], $formato);
                $s->getStyle(Coordinate::stringFromColumnIndex($colEtiqueta + 1) . $fila)->getAlignment()
                    ->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP)
                    ->setHorizontal($formato === 'texto' ? Alignment::HORIZONTAL_LEFT : Alignment::HORIZONTAL_RIGHT);
            }
            $fila++;
        }
        $this->bordes($s, "A{$inicio}:H" . ($fila - 1));

        return $fila;
    }
}
