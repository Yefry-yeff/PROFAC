<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelFactura extends Model
{
    use HasFactory;
    protected $table = 'factura';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'cai',
        'rtn',
        'sub_total',
        'sub_total_grabado',
        'sub_total_excento',
        'isv',
        'total',
        'fecha_emision',
        'fecha_vencimiento',
        'credito',
        'tipo_pago_id',
        'cai_id',
        'estado_venta_id',
        'cliente_id',
        'vendedor',
        'monto_comision',
        'tipo_venta_id',
        'estado_factura_id',
        'estado_editar',
        'codigo_exoneracion_id',
        'comprovante_entrega_id',
        'comentario',
    ];
}
