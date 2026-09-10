<?php

namespace App\Exports;

use App\Exports\Comisiones\ProyeccionEspecial15AuditoriaSheet;
use App\Exports\Comisiones\ProyeccionNominaSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Export de Proyección de Comisiones con regla especial del 15% por cliente.
 *
 * Los clientes configurados en ProyeccionComisiones15SheetExport reciben el
 * 15% fijo; las demás filas conservan la comisión normal por escala.
 *
 * Uso exclusivo: acceso restringido a un usuario puntual (ver controlador).
 *
 * Incluye nómina proyectada, detalle por rol, todas las líneas y auditoría.
 */
class ProyeccionComisiones15Export implements WithMultipleSheets
{
    protected array  $data;
    protected string $empresa;
    protected string $periodo;
    protected string $generadoPor;
    protected ProyeccionNominaSheet $nominaSheet;

    public function __construct(array $data, string $empresa, string $periodo, string $generadoPor, ProyeccionNominaSheet $nominaSheet)
    {
        $this->data        = $data;
        $this->empresa     = $empresa;
        $this->periodo     = $periodo;
        $this->generadoPor = $generadoPor;
        $this->nominaSheet = $nominaSheet;
    }

    public function sheets(): array
    {
        $tabs = [
            ['label' => 'Asesor Comercial',   'capacidad' => 'ASESOR'],
            ['label' => 'Teleasesor',          'capacidad' => 'TELEASESOR'],
            ['label' => 'Gestor de Entregas',  'capacidad' => 'GESTOR_ENTREGA'],
        ];

        $sheets = [$this->nominaSheet];

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

        $sheets[] = new ProyeccionEspecial15AuditoriaSheet(
            $this->data,
            $this->periodo,
            $this->generadoPor
        );

        return $sheets;
    }
}
