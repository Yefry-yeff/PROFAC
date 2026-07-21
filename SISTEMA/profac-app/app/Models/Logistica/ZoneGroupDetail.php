<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use App\Models\Departamento;
use App\Models\Municipio;

class ZoneGroupDetail extends Model
{
    protected $table = 'zone_group_details';

    protected $fillable = [
        'zone_group_id',
        'department_id',
        'municipality_id',
        'status',
        'usr_registro',
        'usr_actualizo',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function zona()
    {
        return $this->belongsTo(ZoneGroup::class, 'zone_group_id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'department_id');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'municipality_id');
    }
}
