<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteDocumento extends Model
{
    protected $table = 'cliente_documentos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'cliente_id', 'tipo_documento', 'nombre_original', 'ruta_archivo', 'users_id'
    ];

    public static $tipos = [
        'escritura_empresa'       => 'Escritura de la Empresa',
        'dni_representante'       => 'DNI del Representante Legal',
        'rtn'                     => 'RTN',
        'permiso_operacion'       => 'Permiso de Operación',
        'croquis'                 => 'Croquis',
        'contrato_arrendamiento'  => 'Contrato de Arrendamiento',
        'foto_establecimiento'    => 'Fotos de Establecimiento',
    ];
}
