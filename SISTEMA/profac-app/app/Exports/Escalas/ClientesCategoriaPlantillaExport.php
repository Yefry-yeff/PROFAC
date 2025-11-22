<?php
namespace App\Exports\Escalas;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;

use App\Models\ModelCliente;
use Maatwebsite\Excel\Concerns\{FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ClientesCategoriaPlantillaExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    public function query()
{
    return \DB::table('cliente as c')
        ->leftJoin('cliente_categoria_escala as cat', 'cat.id', '=', 'c.cliente_categoria_escala_id')
        ->select(
            'c.id',
            'c.nombre',
            'c.rtn',
            'c.correo',
            'c.cliente_categoria_escala_id',
            \DB::raw('COALESCE(cat.nombre_categoria, "") as categoria_nombre')
        )
        ->where('c.estado_cliente_id','=', 1)
        ->orderBy('c.id');
}

public function headings(): array
{
    return ['id','nombre','rtn','correo','categoria_actual_id','categoria_actual_nombre','nueva_categoria_id'];
}

public function map($c): array
{
    return [
        $c->id,
        $c->nombre,
        $c->rtn,
        $c->correo,
        $c->cliente_categoria_escala_id,        // lo que ya tiene
        $c->categoria_nombre,                   // solo informativo
        ''                                      // usuario rellena nueva
    ];
}

public function registerEvents(): array
{
    return [
        AfterSheet::class => function(AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            
            // Obtener el número total de filas
            $highestRow = $sheet->getHighestRow();
            
            // Formatear la columna C (RTN) como texto
            $sheet->getStyle('C2:C' . $highestRow)
                ->getNumberFormat()
                ->setFormatCode('@'); // @ es el código para formato de texto
            
            // Desproteger toda la hoja primero
            $sheet->getProtection()->setSheet(false);
            
            // Columnas a bloquear (A-F): id, nombre, rtn, correo, categoria_actual_id, categoria_actual_nombre
            $columnasProtegidas = ['A', 'B', 'C', 'D', 'E', 'F'];
            
            // Aplicar estilo bloqueado a las columnas protegidas (desde fila 2 hasta el final)
            foreach ($columnasProtegidas as $columna) {
                $rango = $columna . '2:' . $columna . $highestRow;
                $sheet->getStyle($rango)->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
                
                // Aplicar color de fondo gris claro para indicar que están bloqueadas
                $sheet->getStyle($rango)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');
            }
            
            // Desbloquear la columna editable (G): nueva_categoria_id
            $sheet->getStyle('G2:G' . $highestRow)->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
            
            // Proteger también los encabezados (fila 1 completa)
            $sheet->getStyle('A1:G1')->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
            
            // Activar la protección de la hoja
            $sheet->getProtection()->setPassword('');
            $sheet->getProtection()->setSheet(true);
            $sheet->getProtection()->setSort(false);
            $sheet->getProtection()->setInsertRows(false);
            $sheet->getProtection()->setDeleteRows(false);
            $sheet->getProtection()->setInsertColumns(false);
            $sheet->getProtection()->setDeleteColumns(false);
            $sheet->getProtection()->setFormatCells(false);
        },
    ];
}
}
