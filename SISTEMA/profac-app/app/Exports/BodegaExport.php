<?php

namespace App\Exports;

use App\Models\modelBodega;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class BodegaExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    
    
    
    public function collection()
    {

        return DB::table('recibido_bodega')
                ->join('producto', 'recibido_bodega.producto_id', '=', 'producto.id')
                ->join('seccion', 'recibido_bodega.seccion_id', '=', 'seccion.id')
                ->join('segmento', 'seccion.segmento_id', '=', 'segmento.id')
                ->join('bodega', 'segmento.bodega_id', '=', 'bodega.id')        
                ->join('users', 'recibido_bodega.recibido_por', '=', 'users.id')            
                ->select('producto.id', 'producto.nombre as producto','bodega.nombre as bodega','seccion.descripcion', 'segmento.descripcion as segmento','recibido_bodega.cantidad_disponible', 'recibido_bodega.created_at', 'users.name')  
                ->where('recibido_bodega.estado_recibido', '=', 4)
                ->get();

    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre Producto',
            'Bodega',
            'Seccion',
            'Segmento',
            'Cantidad Disponible',
            'Fecha Recibido',
            'Recibido Por',
        ];
    }

}
