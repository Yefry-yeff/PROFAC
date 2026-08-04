<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Export de Proyección de Comisiones — variante "Fijo 15%".
 *
 * Misma estructura y estilo que ProyeccionComisionesExport, pero recalculando
 * la comisión de cada línea con un porcentaje fijo del 15% sobre la base
 * comisionable, sin importar la escala parametrizada.
 *
 * Uso exclusivo: acceso restringido a un usuario puntual (ver controlador).
 *
 * Pestañas:
 *   1. Asesor Comercial  (capacidad = ASESOR)
 *   2. Teleasesor        (capacidad = TELEASESOR)
 *   3. Gestor de Entregas(capacidad = GESTOR_ENTREGA)
 *   4. Todas             (sin filtro)
 */
class ProyeccionComisiones15Export implements WithMultipleSheets
{
    protected array  $data;
    protected string $empresa;
    protected string $periodo;
    protected string $generadoPor;

    public function __construct(array $data, string $empresa, string $periodo, string $generadoPor)
    {
        $this->data        = $data;
        $this->empresa     = $empresa;
        $this->periodo     = $periodo;
        $this->generadoPor = $generadoPor;
    }

    public function sheets(): array
    {
        $tabs = [
            ['label' => 'Asesor Comercial',   'capacidad' => 'ASESOR'],
            ['label' => 'Teleasesor',          'capacidad' => 'TELEASESOR'],
            ['label' => 'Gestor de Entregas',  'capacidad' => 'GESTOR_ENTREGA'],
        ];

        $sheets = [];

        foreach ($tabs as $tab) {
            $filtered = array_values(array_filter($this->data, function ($row) use ($tab) {
                $r = (array) $row;
                return ($r['capacidad'] ?? '') === $tab['capacidad'];
            }));

            $sheets[] = new ProyeccionComisiones15SheetExport(
                $filtered,
                $tab['label'],
                $this->empresa,
                $this->periodo,
                $this->generadoPor
            );
        }

        // Pestaña "Todas" sin filtro
        $sheets[] = new ProyeccionComisiones15SheetExport(
            $this->data,
            'Todas',
            $this->empresa,
            $this->periodo,
            $this->generadoPor
        );

        return $sheets;
    }
}
