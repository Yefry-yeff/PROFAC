<?php

namespace App\Exports\Reportes\Expo;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/** Hoja con una tabla filtrable (título, contexto, tabla, totales y notas). */
class HojaTablaExpo implements FromArray, WithTitle, WithEvents
{
    use EstiloExpo;

    public function __construct(
        private string $nombreHoja,
        private string $titulo,
        private array $info,
        private array $columnas,
        private array $filas,
        private array $notas = [],
        private int $columnasFijas = 1,
    ) {
    }

    public function array(): array
    {
        return [];
    }

    public function title(): string
    {
        return mb_substr($this->nombreHoja, 0, 31);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $s = $event->sheet->getDelegate();
                $numColumnas = count($this->columnas);
                $encabezado = $this->escribirTitulo($s, $this->titulo, $this->info, $numColumnas);
                $ultima = $this->escribirTabla($s, $encabezado, $this->columnas, $this->filas, ['autofiltro' => true]);
                $fila = $ultima + 2;
                foreach ($this->notas as $nota) {
                    $this->escribirNota($s, $fila++, $nota, $numColumnas);
                }
                $s->freezePane(chr(ord('A') + $this->columnasFijas) . ($encabezado + 1));
                $this->configurarImpresion($s, $encabezado);
                $s->setSelectedCell('A1');
            },
        ];
    }
}
