<?php

namespace App\Exports\Comisiones;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ProyeccionNominaSheet implements FromArray, WithTitle, WithEvents, WithStrictNullComparison
{
    protected string $empleado;
    protected string $periodoLabel;
    protected int    $totalFacturas;
    protected float  $baseComisionable;
    protected float  $comisionAsesor;
    protected float  $comisionTeleasesor;
    protected float  $comisionGestor;
    protected array  $mesesCobrados;
    protected float  $totalCobrado;
    protected string $generadoPor;

    public function __construct(string $empleado, string $periodoLabel, int $totalFacturas, float $baseComisionable, float $comisionAsesor, float $comisionTeleasesor, float $comisionGestor, array $mesesCobrados, float $totalCobrado, string $generadoPor)
    {
        $this->empleado           = $empleado;
        $this->periodoLabel       = $periodoLabel;
        $this->totalFacturas      = $totalFacturas;
        $this->baseComisionable   = $baseComisionable;
        $this->comisionAsesor     = $comisionAsesor;
        $this->comisionTeleasesor = $comisionTeleasesor;
        $this->comisionGestor     = $comisionGestor;
        $this->mesesCobrados      = $mesesCobrados;
        $this->totalCobrado       = $totalCobrado;
        $this->generadoPor        = $generadoPor;
    }

    public function title(): string
    {
        $clean = str_replace(['\\', '/', '*', '?', ':', '[', ']'], '', $this->empleado);
        $clean = trim($clean);
        if ($clean === '') { $clean = 'Proyeccion'; }
        return mb_strlen($clean) > 31 ? mb_substr($clean, 0, 31) : $clean;
    }

    public function array(): array
    {
        $periodoUpper = mb_strtoupper($this->periodoLabel, 'UTF-8');
        $empleadoUp   = mb_strtoupper($this->empleado, 'UTF-8');
        $ahora        = now()->format('d/m/Y H:i');

        $rows   = [];
        $rows[] = ['DISTRIBUCIONES VALENCIA', '', '', '', ''];
        $rows[] = ['TEGUCIGALPA, M.D.C.', '', '', '', ''];
        $rows[] = ['Col. Godoy una cuadra arriba de fuerza aerea', '', '', '', ''];
        $rows[] = ['Tels.: (504)2234-9877 / 22349914', '', '', '', ''];
        $rows[] = ['E-mail: lisbeth.ortiz@distribucionesvalencia.hn / seyli.torres@distribucionesvalencia.hn  R.T.N. 08011986138652', '', '', '', ''];
        $rows[] = ['', '', '', '', ''];
        $rows[] = ['NOMBRE: ' . $empleadoUp, '', '', '', ''];
        $rows[] = ['PERIODO: ' . $periodoUpper, '', '', '', ''];
        $rows[] = ['FACTURAS PROYECTADAS ' . $periodoUpper . ' COMISIONABLES', '', '', '', ''];
        $rows[] = ['BASE COMISIONABLE ' . $periodoUpper, 'L', $this->baseComisionable, '', ''];
        $rows[] = ['Comisión Asesor Comercial', 'L', $this->comisionAsesor, '', ''];
        $rows[] = ['Comisión Teleasesor', 'L', $this->comisionTeleasesor, '', ''];
        $rows[] = ['Comisión Gestor de Entrega', 'L', $this->comisionGestor, '', ''];
        $rows[] = ['DEDUCCION RETENCION EN LA FUENTE', 'L', '', '', ''];  // C14 — el usuario ingresa el monto
        $rows[] = ['TOTAL PROYECTADO', 'L', 0.0, '', ''];  // C15 — fórmula =C11+C12+C13-C14 (se pone en AfterSheet)
        $rows[] = ['Generado por: ' . $this->generadoPor, '', 'Fecha generacion: ' . $ahora, '', ''];
        $rows[] = ['FACTURAS POR MES EN ' . $periodoUpper, '', '', '', ''];
        $rows[] = ['Mes cobrado', 'Cantidad de facturas', 'Total cobrado (L.)', '', ''];

        foreach ($this->mesesCobrados as $mes) {
            $mesLabel = (string) ($mes['mes_label'] ?? '');
            $mesTxt   = $mesLabel !== '' ? ('Facturas de ' . $mesLabel . ' cobradas') : 'Facturas cobradas';
            $rows[]   = [$mesTxt, (int) ($mes['cantidad'] ?? 0), (float) ($mes['total'] ?? 0), '', ''];
        }

        $rows[] = ['TOTAL COBROS', $this->totalFacturas, $this->totalCobrado, '', ''];
        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $logoPath = public_path('img/LOGO_VALENCIA.jpg');

                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');
                $sheet->mergeCells('A3:E3');
                $sheet->mergeCells('A4:E4');
                $sheet->mergeCells('A5:E5');
                $sheet->mergeCells('A7:E7');
                $sheet->mergeCells('A8:E8');
                $sheet->mergeCells('A9:E9');
                $sheet->mergeCells('C10:E10');
                $sheet->mergeCells('C11:E11');
                $sheet->mergeCells('C12:E12');
                $sheet->mergeCells('C13:E13');
                $sheet->mergeCells('C14:E14');
                $sheet->mergeCells('C15:E15');
                $sheet->mergeCells('A16:B16');
                $sheet->mergeCells('C16:E16');
                $sheet->mergeCells('A17:E17');
                $sheet->mergeCells('C18:E18');
                for ($r = 19; $r <= $lastRow; $r++) { $sheet->mergeCells("C{$r}:E{$r}"); }

                $sheet->getStyle('A1:E5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '0F172A']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'E2E8F0']]],
                ]);
                $sheet->getStyle('A3:E5')->getFont()->setSize(10);
                $sheet->getStyle('A1:E2')->getFont()->setSize(14);
                $sheet->getStyle('A5:E5')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
                $sheet->getStyle('A5:E5')->getBorders()->getBottom()->getColor()->setRGB('CBD5E1');

                // Bloque naranja filas 7-15
                $sheet->getStyle('A7:E15')->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E97824']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '9A3412']]],
                ]);
                $sheet->getStyle('A7:E9')->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CC6218']]]);
                $sheet->getStyle('A7:E8')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle('A9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                // Filas 10-14: label izq, L centro, monto der
                $sheet->getStyle('A10:A14')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('B10:B14')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C10:E13')->getNumberFormat()->setFormatCode('"L "#,##0.00');
                $sheet->getStyle('C10:C14')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Fila 14: DEDUCCION — celda C14 vacía y editable (fondo blanco)
                $sheet->getStyle('C14:E14')->applyFromArray([
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFBEB']],
                    'font'    => ['bold' => false, 'size' => 11, 'color' => ['rgb' => '1E293B']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'F59E0B']]],
                ]);
                $sheet->getStyle('C14:E14')->getNumberFormat()->setFormatCode('"L "#,##0.00');
                $sheet->setCellValue('C14', '');  // vacío para que el usuario ingrese

                // Fila 15: TOTAL PROYECTADO con fórmula =C11+C12+C13-C14
                $sheet->getStyle('A15:E15')->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '0F172A']],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '9A3412']]],
                ]);
                $sheet->getStyle('A15')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('B15')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C15:E15')->getNumberFormat()->setFormatCode('"L "#,##0.00');
                $sheet->getStyle('C15')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->setCellValue('C15', '=C11+C12+C13-C14');  // fórmula automática

                // Fila 16: generado por
                $sheet->getStyle('A16:E16')->applyFromArray([
                    'font'    => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '64748B']],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);
                // Fila 17: encabezado meses
                $sheet->getStyle('A17:E17')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                // Fila 18: cabecera tabla
                $sheet->getStyle('A18:E18')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '0F172A']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                // Filas de datos
                for ($r = 19; $r <= $lastRow - 1; $r++) {
                    $bg = ($r % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:E{$r}")->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'E2E8F0']]]]);
                    $sheet->getStyle("C{$r}:E{$r}")->getNumberFormat()->setFormatCode('"L "#,##0.00');
                    $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
                // Fila TOTAL COBROS
                $sheet->getStyle("A{$lastRow}:E{$lastRow}")->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1E293B']]],
                ]);
                $sheet->getStyle("C{$lastRow}:E{$lastRow}")->getNumberFormat()->setFormatCode('"L "#,##0.00');
                $sheet->getStyle("C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                // Logo arriba-izquierda
                if (file_exists($logoPath)) {
                    $drawing = new Drawing();
                    $drawing->setName('Logo');
                    $drawing->setDescription('Valencia');
                    $drawing->setPath($logoPath);
                    $drawing->setHeight(110);
                    $drawing->setCoordinates('A1');
                    $drawing->setWorksheet($sheet);
                }
                // Anchos y alturas
                foreach (['A' => 46, 'B' => 22, 'C' => 28, 'D' => 14, 'E' => 14] as $col => $w) {
                    $sheet->getColumnDimension($col)->setWidth($w);
                }
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(26);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(19);
                $sheet->getRowDimension(5)->setRowHeight(19);
                $sheet->getRowDimension(7)->setRowHeight(28);
                $sheet->getRowDimension(8)->setRowHeight(24);
                $sheet->getRowDimension(9)->setRowHeight(20);
                $sheet->getRowDimension(15)->setRowHeight(26);
                $sheet->getRowDimension(17)->setRowHeight(24);
                $sheet->getRowDimension(18)->setRowHeight(22);
                $sheet->freezePane('A19');

                $sheet->getStyle('A1:E5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '0F172A']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'E2E8F0']]],
                ]);

                $sheet->getStyle('A3:E5')->getFont()->setSize(10);
                $sheet->getStyle('A1:E2')->getFont()->setSize(14);
                $sheet->getStyle('A5:E5')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
                $sheet->getStyle('A5:E5')->getBorders()->getBottom()->getColor()->setRGB('CBD5E1');

                // Fila TOTAL COBROS
                $sheet->getStyle("A{$lastRow}:E{$lastRow}")->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1E293B']]],
                ]);
                $sheet->getStyle("C{$lastRow}:E{$lastRow}")->getNumberFormat()->setFormatCode('"L "#,##0.00');
                $sheet->getStyle("C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                // Logo arriba-izquierda
                if (file_exists($logoPath)) {
                    $drawing = new Drawing();
                    $drawing->setName('Logo');
                    $drawing->setDescription('Valencia');
                    $drawing->setPath($logoPath);
                    $drawing->setHeight(110);
                    $drawing->setCoordinates('A1');
                    $drawing->setWorksheet($sheet);
                }
                // Anchos y alturas
                foreach (['A' => 46, 'B' => 22, 'C' => 28, 'D' => 14, 'E' => 14] as $col => $w) {
                    $sheet->getColumnDimension($col)->setWidth($w);
                }
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(26);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(19);
                $sheet->getRowDimension(5)->setRowHeight(19);
                $sheet->getRowDimension(7)->setRowHeight(28);
                $sheet->getRowDimension(8)->setRowHeight(24);
                $sheet->getRowDimension(9)->setRowHeight(20);
                $sheet->getRowDimension(13)->setRowHeight(26);
                $sheet->getRowDimension(15)->setRowHeight(24);
                $sheet->getRowDimension(16)->setRowHeight(22);
                $sheet->freezePane('A17');
            },
        ];
    }
}
