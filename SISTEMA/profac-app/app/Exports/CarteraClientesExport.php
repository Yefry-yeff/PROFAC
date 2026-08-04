<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CarteraClientesExport implements FromArray, WithColumnWidths, WithDrawings, WithEvents, WithStyles, WithTitle
{
    private const LAST_COL = 'J';

    private $clientes;
    private string $titulo;
    private string $alcance;
    private string $filtros;
    private string $usuario;

    public function __construct($clientes, string $titulo, string $alcance, string $filtros, string $usuario)
    {
        $this->clientes = $clientes;
        $this->titulo = $titulo;
        $this->alcance = $alcance;
        $this->filtros = $filtros;
        $this->usuario = $usuario;
    }

    public function title(): string
    {
        return 'Cartera de Clientes';
    }

    public function array(): array
    {
        $filas = [
            ['DISTRIBUCIONES VALENCIA S.A. DE C.V.   |   RTN: 08011986138652', '', '', '', '', '', '', '', '', ''],
            [$this->titulo, '', '', '', '', '', '', '', '', ''],
            ['Generado: ' . now()->format('d/m/Y H:i') . '   |   Descargado por: ' . $this->usuario, '', '', '', '', '', '', '', '', ''],
            ['Alcance: ' . $this->alcance . '   |   Filtros: ' . $this->filtros, '', '', '', '', '', '', '', '', ''],
            ['#', 'CLIENTE', 'RTN', 'TELÉFONO', 'MUNICIPIO', 'DEPARTAMENTO', 'ESTADO', 'ASESORES COMERCIALES', 'TELEASESORES', 'ID CLIENTE'],
        ];

        foreach ($this->clientes as $indice => $cliente) {
            $filas[] = [
                $indice + 1,
                $cliente->nombre,
                $cliente->rtn ?? '',
                $cliente->telefono_empresa ?? '',
                $cliente->municipio_nombre ?? '',
                $cliente->departamento_nombre ?? '',
                $cliente->estado_descripcion ?? '',
                $cliente->asesores_comerciales ?? 'Sin asignar',
                $cliente->teleasesores ?? 'Sin asignar',
                (int) $cliente->id,
            ];
        }

        return $filas;
    }

    public function drawings()
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
            'A' => 7,
            'B' => 38,
            'C' => 20,
            'D' => 18,
            'E' => 24,
            'F' => 24,
            'G' => 16,
            'H' => 36,
            'I' => 36,
            'J' => 13,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:' . self::LAST_COL . '1');
        $sheet->mergeCells('A2:' . self::LAST_COL . '2');
        $sheet->mergeCells('A3:' . self::LAST_COL . '3');
        $sheet->mergeCells('A4:' . self::LAST_COL . '4');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('1F3864');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(62);

        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('E07000');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(22);

        $sheet->getStyle('A3:A4')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('555555');
        $sheet->getStyle('A3:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        $sheet->getRowDimension(3)->setRowHeight(17);
        $sheet->getRowDimension(4)->setRowHeight(30);

        $sheet->getStyle('A5:' . self::LAST_COL . '5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E07000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(30);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $ultimaFila = $sheet->getHighestRow();

                if ($ultimaFila >= 6) {
                    for ($fila = 6; $fila <= $ultimaFila; $fila++) {
                        $color = $fila % 2 === 0 ? 'FFF4E8' : 'FFFFFF';
                        $sheet->getStyle("A{$fila}:" . self::LAST_COL . $fila)->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);
                        $sheet->getStyle("A{$fila}:" . self::LAST_COL . $fila)->getAlignment()
                            ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                        $sheet->getStyle("A{$fila}:A{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("J{$fila}:J{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getRowDimension($fila)->setRowHeight(28);
                    }
                    $sheet->setAutoFilter('A5:' . self::LAST_COL . $ultimaFila);
                }

                $sheet->getStyle('A5:' . self::LAST_COL . max(5, $ultimaFila))->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E6C49F');
                $sheet->getStyle('A5:' . self::LAST_COL . max(5, $ultimaFila))->getBorders()->getOutline()
                    ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('E07000');
                $sheet->freezePane('A6');
                $sheet->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);
                $sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.3)->setRight(0.3);
            },
        ];
    }
}
