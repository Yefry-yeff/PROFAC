<?php

namespace App\Exports;

use App\Models\Gasto;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GastosExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithColumnWidths,
    WithCustomStartCell, WithCustomValueBinder, WithDrawings, WithStrictNullComparison, WithStyles, WithTitle
{
    public function __construct(private array $filtros)
    {
    }

    public function collection(): \Illuminate\Support\Enumerable
    {
        $importes = DB::table('gasto_detalles')->select('gasto_id')
            ->selectRaw('SUM(CASE WHEN isv_porcentaje = 0 THEN subtotal ELSE 0 END) AS importe_exento')
            ->selectRaw('SUM(CASE WHEN isv_porcentaje > 0 THEN subtotal ELSE 0 END) AS importe_gravado')
            ->groupBy('gasto_id');

        return Gasto::reporte()->filtrar($this->filtros)
            ->leftJoinSub($importes, 'importes', 'importes.gasto_id', '=', 'gastos.id')
            ->addSelect('importes.importe_exento', 'importes.importe_gravado')
            ->orderByDesc('gastos.id')->get()->map(fn ($gasto) => [
            'id' => 'G-' . $gasto->id,
            'proveedor' => $gasto->proveedor,
            'cai' => $gasto->cai,
            'numero_factura' => $gasto->numero_factura,
            'fecha_emision' => \Carbon\Carbon::parse($gasto->fecha_emision)->format('d/m/Y'),
            'fecha_recepcion' => \Carbon\Carbon::parse($gasto->fecha_recepcion)->format('d/m/Y'),
            'fecha_vencimiento' => \Carbon\Carbon::parse($gasto->fecha_vencimiento)->format('d/m/Y'),
            'importe_exento' => (float) $gasto->importe_exento,
            'importe_gravado' => (float) $gasto->importe_gravado,
            'isv' => (float) $gasto->isv,
            'total' => (float) $gasto->total,
            'estado' => $gasto->estado_reporte,
            'reporte_compras' => $gasto->incluir_reporte_compras ? 'Si' : 'No',
            'anulado_por' => $gasto->anulado_por_nombre,
            'anulado_at' => $gasto->anulado_at?->format('d/m/Y H:i:s'),
            'motivo_anulacion' => $gasto->motivo_anulacion,
        ]);
    }

    public function headings(): array
    {
        return [
            'ID', 'Nombre del proveedor', 'CAI', 'Numero de documento', 'Fecha de emision',
            'Fecha de recepcion', 'Fecha de vencimiento', 'Importe exento', 'Importe gravado',
            'ISV', 'Total', 'Estado', 'Pasado al reporte de compras',
            'Anulado por', 'Fecha y hora de anulacion', 'Motivo de anulacion',
        ];
    }

    public function title(): string
    {
        return 'Gastos';
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        if ($value !== null && !in_array($cell->getColumn(), ['H', 'I', 'J', 'K'], true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
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

    public function columnWidths(): array
    {
        return [
            'A' => 12, 'B' => 38, 'C' => 35, 'D' => 25, 'E' => 18, 'F' => 18, 'G' => 18,
            'H' => 19, 'I' => 19, 'J' => 19, 'K' => 20, 'L' => 32, 'M' => 25,
            'N' => 28, 'O' => 25, 'P' => 45,
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        $sheet->mergeCells('A1:P1')->mergeCells('A2:P2')->mergeCells('A3:P3')->mergeCells('A4:P4');
        $sheet->setCellValue('A1', 'DISTRIBUCIONES VALENCIA   |   RTN: 08011986138652');
        $sheet->setCellValue('A2', 'REPORTE DE GASTOS');
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
        $sheet->getStyle('A5:P5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E07000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(32);
        $lastRow = $sheet->getHighestRow();
        if ($lastRow >= 6) {
            $sheet->getStyle("H6:K{$lastRow}")->getNumberFormat()->setFormatCode('"L. " #,##0.00');
            $sheet->getStyle("H6:K{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("A6:P{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            for ($row = 6; $row <= $lastRow; $row++) {
                $sheet->getStyle("A{$row}:P{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($row % 2 === 0 ? 'FFF4E8' : 'FFFFFF');
                $sheet->getRowDimension($row)->setRowHeight(36);
            }
            $sheet->getStyle("K6:K{$lastRow}")->getFont()->setBold(true)->getColor()->setRGB('126B5B');
        }
        $sheet->getStyle("A5:P{$lastRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E6C49F');
        $sheet->setAutoFilter("A5:P{$lastRow}");
        $sheet->freezePane('E6');
        $sheet->setShowGridlines(false);
        $sheet->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0)
            ->setRowsToRepeatAtTopByStartAndEnd(1, 5);
        return [];
    }
}
