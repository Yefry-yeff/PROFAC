<?php

namespace App\Exports;

use App\Support\BoletasCompras;

class BoletasComprasExport extends ComprasMesExport
{
    public function __construct(private array $filtrosBoletas)
    {
        $this->filtros = [
            'desde' => $filtrosBoletas['fechaInicio'], 'hasta' => $filtrosBoletas['fechaFinal'],
            'factura' => $filtrosBoletas['buscar'] ?? '', 'proveedor' => '',
        ];
    }

    public function collection(): \Illuminate\Support\Enumerable
    {
        return BoletasCompras::documentos(BoletasCompras::historial($this->filtrosBoletas))->get()
            ->map(fn ($boleta) => $this->documento($boleta) + ['estado' => BoletasCompras::estado($boleta)]);
    }

    public function headings(): array
    {
        return [...parent::headings(), 'Estado'];
    }

    public function columnWidths(): array
    {
        return parent::columnWidths() + ['L' => 36];
    }

    public function title(): string
    {
        return 'Boletas de compras';
    }
}
