<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use App\Models\Departamento;
use App\Models\Municipio;

class FacturaTratamientoEntrega extends Model
{
    protected $table = 'factura_tratamiento_entrega';

    protected $fillable = [
        'factura_id',
        'department_id',
        'municipality_id',
        'direccion_entrega',
        'gestor_entrega_id',
        'usr_registro',
        'usr_actualizo',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'department_id');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'municipality_id');
    }
}
