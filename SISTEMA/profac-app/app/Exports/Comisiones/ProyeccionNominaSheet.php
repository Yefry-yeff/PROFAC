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
    protected float  $basePoliticaAnterior;
    protected float  $comisionPoliticaAnterior;
    protected array  $mesesCobrados;
    protected float  $totalCobrado;
    protected string $generadoPor;

    public function __construct(string $empleado, string $periodoLabel, int $totalFacturas, float $baseComisionable, float $comisionAsesor, float $comisionTeleasesor, float $comisionGestor, float $basePoliticaAnterior, float $comisionPoliticaAnterior, array $mesesCobrados, float $totalCobrado, string $generadoPor)
    {
        $this->empleado                 = $empleado;
        $this->periodoLabel             = $periodoLabel;
        $this->totalFacturas            = $totalFacturas;
        $this->baseComisionable         = $baseComisionable;
        $this->comisionAsesor           = $comisionAsesor;
        $this->comisionTeleasesor       = $comisionTeleasesor;
        $this->comisionGestor           = $comisionGestor;
        $this->basePoliticaAnterior     = $basePoliticaAnterior;
        $this->comisionPoliticaAnterior = $comisionPoliticaAnterior;
        $this->mesesCobrados            = $mesesCobrados;
        $this->totalCobrado             = $totalCobrado;
        $this->generadoPor              = $generadoPor;
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
        $rows[] = ['NOMBRE: ' . $empleadoUp, '', '', '', ''];                                            // R7
        $rows[] = ['PERIODO: ' . $periodoUpper, '', '', '', ''];                                         // R8
        $rows[] = ['FACTURAS PROYECTADAS ' . $periodoUpper . ' COMISIONABLES', '', '', '', ''];          // R9
        $rows[] = ['BASE COMISIONABLE ESCALA ' . $periodoUpper, 'L', $this->baseComisionable, '', ''];  // R10
        $rows[] = ['Comisión Asesor Comercial', 'L', $this->comisionAsesor, '', ''];                    // R11
        $rows[] = ['Comisión Teleasesor', 'L', $this->comisionTeleasesor, '', ''];                      // R12
        $rows[] = ['Comisión Gestor de Entrega', 'L', $this->comisionGestor, '', ''];                   // R13
        $rows[] = ['TOTAL ESCALA', 'L', 0.0, '', ''];                                                  // R14 =C11+C12+C13
        $rows[] = ['BASE COMISIONABLE POLÍTICA ANTERIOR ' . $periodoUpper, 'L', $this->basePoliticaAnterior, '', ''];  // R15
        $rows[] = ['Comisión Política Anterior', 'L', $this->comisionPoliticaAnterior, '', ''];         // R16
        $rows[] = ['TOTAL COMISIÓN (ESCALA + POLÍTICA ANTERIOR)', 'L', 0.0, '', ''];                    // R17 =C14+C16
        $rows[] = ['DEDUCCION RETENCION EN LA FUENTE', 'L', '', '', ''];                                // R18 vacío, usuario llena
        $rows[] = ['TOTAL PROYECTADO NETO', 'L', 0.0, '', ''];                                         // R19 =C17-C18
        $rows[] = ['Generado por: ' . $this->generadoPor, '', 'Fecha generacion: ' . $ahora, '', ''];  // R20
        $rows[] = ['FACTURAS POR MES EN ' . $periodoUpper, '', '', '', ''];                             // R21
        $rows[] = ['Mes cobrado', 'Cantidad de facturas', 'Total cobrado (L.)', '', ''];                // R22

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
                // R10–R19: col C:E merged
                foreach (range(10, 19) as $r) { $sheet->mergeCells("C{$r}:E{$r}"); }
                $sheet->mergeCells('A20:B20');
                $sheet->mergeCells('C20:E20');
                $sheet->mergeCells('A21:E21');
                $sheet->mergeCells('C22:E22');
                for ($r = 23; $r <= $lastRow; $r++) { $sheet->mergeCells("C{$r}:E{$r}"); }

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

                // Bloque naranja R7-R20
                $sheet->getStyle('A7:E20')->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E97824']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '9A3412']]],
                ]);
                // Encabezados nombre/periodo más oscuros
                $sheet->getStyle('A7:E9')->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CC6218']]]);
                $sheet->getStyle('A7:E8')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle('A9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // R10-R13: base escala + comisiones unitarias
                $sheet->getStyle('A10:A13')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('B10:B13')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C10:E13')->getNumberFormat()->setFormatCode('"L "#,##0.00');
                $sheet->getStyle('C10:C13')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // R14: TOTAL ESCALA = C11+C12+C13
                $sheet->getStyle('A14:E14')->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '0F172A']],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FED7AA']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '9A3412']]],
                ]);
                $sheet->getStyle('A14')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('B14')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C14:E14')->getNumberFormat()->setFormatCode('"L "#,##0.00');
                $sheet->getStyle('C14')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->setCellValue('C14', '=C11+C12+C13');

                // R15: Base Política Anterior — fondo distinto
                $sheet->getStyle('A15:E15')->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B85A10']]]);
                $sheet->getStyle('B15')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C15:E15')->getNumberFormat()->setFormatCode('"L "#,##0.00');
                $sheet->getStyle('C15')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // R16: Comisión Política Anterior
                $sheet->getStyle('B16')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C16:E16')->getNumberFormat()->setFormatCode('"L "#,##0.00');
                $sheet->getStyle('C16')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // R17: TOTAL COMISIÓN = C14+C16
                $sheet->getStyle('A17:E17')->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '0F172A']],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FDE68A']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '9A3412']]],
                ]);
                $sheet->getStyle('A17')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('B17')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C17:E17')->getNumberFormat()->setFormatCode('"L "#,##0.00');
                $sheet->getStyle('C17')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->setCellValue('C17', '=C14+C16');

                // R18: DEDUCCION — celda vacía editable
                $sheet->getStyle('C18:E18')->applyFromArray([
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFBEB']],
                    'font'    => ['bold' => false, 'size' => 11, 'color' => ['rgb' => '1E293B']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'F59E0B']]],
                ]);
                $sheet->getStyle('C18:E18')->getNumberFormat()->setFormatCode('"L "#,##0.00');
                $sheet->setCellValue('C18', '');

                // R19: TOTAL PROYECTADO NETO = C17-C18
                $sheet->getStyle('A19:E19')->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '0F172A']],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '9A3412']]],
                ]);
                $sheet->getStyle('A19')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('B19')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C19:E19')->getNumberFormat()->setFormatCode('"L "#,##0.00');
                $sheet->getStyle('C19')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->setCellValue('C19', '=C17-C18');

                // R20: generado por
                $sheet->getStyle('A20:E20')->applyFromArray([
                    'font'    => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '64748B']],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);
                // R21: encabezado meses
                $sheet->getStyle('A21:E21')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                // R22: cabecera tabla
                $sheet->getStyle('A22:E22')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '0F172A']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                // Filas de datos meses
                for ($r = 23; $r <= $lastRow - 1; $r++) {
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
