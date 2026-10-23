<?php

namespace App\Exports;

use App\Support\FiltrosCompras;
use App\Support\BoletasCompras;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ComprasMesExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithColumnWidths,
    WithColumnFormatting, WithCustomValueBinder, WithStrictNullComparison, WithCustomStartCell,
    WithDrawings, WithStyles, WithTitle
{
    
    protected array $filtros;

    public function __construct(string|array $mes)
    {
        if (is_string($mes)) {
            validator(['mes' => $mes], ['mes' => 'required|date_format:Y-m'])->validate();
            $fecha = \Carbon\Carbon::createFromFormat('!Y-m', $mes);
            $mes = ['desde' => $fecha->copy()->startOfMonth()->toDateString(),
                'hasta' => $fecha->copy()->endOfMonth()->toDateString()];
        }
        $this->filtros = FiltrosCompras::validar($mes);
    }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection(): \Illuminate\Support\Enumerable
    {
        $importesCompras = DB::table('compra_has_producto as detalle')
            ->join('producto', 'producto.id', '=', 'detalle.producto_id')
            ->select('detalle.compra_id')
            ->selectRaw('SUM(CASE WHEN producto.isv = 0 THEN detalle.sub_total_producto ELSE 0 END) AS importe_exento')
            ->selectRaw('SUM(CASE WHEN producto.isv = 15 THEN detalle.sub_total_producto ELSE 0 END) AS base_15')
            ->groupBy('detalle.compra_id');
        $importesGastos = DB::table('gasto_detalles')->select('gasto_id')
            ->selectRaw('SUM(CASE WHEN isv_porcentaje = 0 THEN subtotal ELSE 0 END) AS importe_exento')
            ->selectRaw('SUM(CASE WHEN isv_porcentaje = 15 THEN subtotal ELSE 0 END) AS base_15')
            ->groupBy('gasto_id');
        $compras = FiltrosCompras::aplicar(DB::table('compra')
            ->join('proveedores', 'proveedores.id', '=', 'compra.proveedores_id')
            ->leftJoinSub($importesCompras, 'importes', 'importes.compra_id', '=', 'compra.id')
            ->select('proveedores.rtn', 'proveedores.nombre', 'compra.codigo_cai as cai',
                'compra.numero_factura', 'compra.fecha_emision', 'importes.importe_exento', 'importes.base_15')
            ->selectRaw("'compra' AS origen")
            ->where('compra.estado_compra_id', 1), $this->filtros, 'compra', 'proveedores_id')
            ->orderBy('compra.id')->get();
        $gastos = FiltrosCompras::aplicar(
            DB::table('gastos')
                ->join('proveedores', 'proveedores.id', '=', 'gastos.proveedor_id')
                ->leftJoinSub($importesGastos, 'importes', 'importes.gasto_id', '=', 'gastos.id')
                ->select('proveedores.rtn', 'proveedores.nombre', 'gastos.cai',
                    'gastos.numero_factura', 'gastos.fecha_emision', 'importes.importe_exento', 'importes.base_15')
                ->selectRaw("'gasto' AS origen")
                ->where('incluir_reporte_compras', true)->where('gastos.estado', 'activo'),
            $this->filtros, 'gastos', 'proveedor_id')
            ->orderBy('gastos.id')->get();
        $boletas = BoletasCompras::documentos(BoletasCompras::aplicarCompras(
            DB::table('boleta_compra as b'), $this->filtros))->get();
        return $compras->concat($gastos)->concat($boletas)->map(fn ($documento) => $this->documento($documento));
    }

    protected function documento(object $documento): array
    {
        $exento = round((float) $documento->importe_exento, 2);
        $base15 = round((float) $documento->base_15, 2);
        $fecha = \Carbon\Carbon::parse($documento->fecha_emision)->format('d/m/Y');
        return [
            'rtn' => $documento->rtn,
            'clase_documento' => $documento->origen === 'boleta' ? 'OC-Otros comprobantes de pago' : 'FA-factura',
            'cai' => $documento->cai,
            'numero_factura' => $documento->numero_factura,
            'proveedor' => $documento->nombre,
            'fecha_emision' => $fecha,
            'fecha_contable' => $fecha,
            'importe_exento' => $exento,
            'base_15' => $base15,
            'monto_al_costo' => $documento->origen !== 'gasto' ? round($exento + $base15, 2) : null,
            'monto_al_gasto' => $documento->origen === 'gasto' ? round($exento + $base15, 2) : null,
        ];
    }

    public function headings(): array
    {
        return [
            '200-RTN',
            '600-Clase de documento',
            '7-CAI',
            '8-Nº documento (establecimiento - punto de emisión - tipo documento - correlativo)',
            '71-Nº Nombre del proveedor',
            '900-Fecha emisión',
            '100-Fecha contable',
            '110-Importe exento',
            '1511-Importe base 15%',
            '270-Monto al costo',
            '280-Monto al gasto',
        ];
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        if ($value !== null && ($cell->getRow() <= 5 || !in_array($cell->getColumn(), ['H', 'I', 'J', 'K'], true))) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return ['H' => '#,##0.00', 'I' => '#,##0.00', 'J' => '#,##0.00', 'K' => '#,##0.00'];
    }

    public function title(): string
    {
        return 'Compras';
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 22, 'B' => 24, 'C' => 38, 'D' => 40, 'E' => 38, 'F' => 22, 'G' => 22,
            'H' => 22, 'I' => 24, 'J' => 22, 'K' => 22,
        ];
    }

    public function drawings(): BaseDrawing|array
    {
        $logo = new Drawing();
        $logo->setName('Logo Valencia');
        $logo->setPath(public_path('img/membrete/Logo3.png'));
        $logo->setHeight(58);
        $logo->setCoordinates('A1');
        $logo->setOffsetX(4)->setOffsetY(4);
        return $logo;
    }

    public function styles(Worksheet $sheet): ?array
    {
        $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));
        $sheet->mergeCells("A1:{$lastColumn}1")->mergeCells("A2:{$lastColumn}2")
            ->mergeCells("A3:{$lastColumn}3")->mergeCells("A4:{$lastColumn}4");
        $sheet->setCellValue('A1', 'DISTRIBUCIONES VALENCIA   |   RTN: 08011986138652');
        $sheet->setCellValue('A2', 'REPORTE DE ' . strtoupper($this->title()));
        $sheet->setCellValue('A3', 'Generado: ' . now()->format('d/m/Y H:i')
            . '   |   Descargado por: ' . auth()->user()->name);
        $sheet->setCellValue('A4', 'Emision: ' . $this->filtros['desde'] . ' al ' . $this->filtros['hasta']
            . '   |   Documento: ' . ($this->filtros['factura'] ?: 'Todos')
            . '   |   Proveedor: ' . (empty($this->filtros['proveedor']) ? 'Todos'
                : DB::table('proveedores')->where('id', $this->filtros['proveedor'])->value('nombre')));
        $sheet->getRowDimension(1)->setRowHeight(62);
        $sheet->getRowDimension(2)->setRowHeight(24);
        $sheet->getRowDimension(3)->setRowHeight(18);
        $sheet->getRowDimension(4)->setRowHeight(30);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('1F3864');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('E07000');
        $sheet->getStyle('A2:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        $sheet->getStyle('A3:A4')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('555555');
        $sheet->getStyle("A5:{$lastColumn}5")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E07000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(60);
        $lastRow = $sheet->getHighestRow();
        if ($lastRow >= 6) {
            $sheet->getStyle("H6:K{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("A6:{$lastColumn}{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            for ($row = 6; $row <= $lastRow; $row++) {
                $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($row % 2 === 0 ? 'FFF4E8' : 'FFFFFF');
                $sheet->getRowDimension($row)->setRowHeight(36);
            }
            $sheet->getStyle("J6:K{$lastRow}")->getFont()->setBold(true)->getColor()->setRGB('126B5B');
        }
        $sheet->getStyle("A5:{$lastColumn}{$lastRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E6C49F');
        $sheet->setAutoFilter("A5:{$lastColumn}{$lastRow}");
        $sheet->freezePane('E6');
        $sheet->setShowGridlines(false);
        $sheet->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0)
            ->setRowsToRepeatAtTopByStartAndEnd(1, 5);
        return [];
    }
}
