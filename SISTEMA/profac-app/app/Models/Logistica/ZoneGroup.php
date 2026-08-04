<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ZoneGroup extends Model
{
    protected $table = 'zone_groups';

    protected $fillable = [
        'name',
        'description',
        'orden',
        'status',
        'usr_registro',
        'usr_actualizo',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function detalles()
    {
        return $this->hasMany(ZoneGroupDetail::class, 'zone_group_id');
    }

    public function detallesActivos()
    {
        return $this->hasMany(ZoneGroupDetail::class, 'zone_group_id')->where('status', 1);
    }

    public function auditorias()
    {
        return $this->hasMany(ZoneGroupAudit::class, 'zone_group_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'usr_registro');
    }

    public function scopeActivos($query)
    {
        return $query->where('status', 1);
    }
}
