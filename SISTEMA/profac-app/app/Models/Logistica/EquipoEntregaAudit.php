<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EquipoEntregaAudit extends Model
{
    protected $table = 'equipos_entrega_audit';

    protected $fillable = [
        'equipo_entrega_id',
        'action',
        'old_data',
        'new_data',
        'user_id',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function equipo()
    {
        return $this->belongsTo(EquipoEntrega::class, 'equipo_entrega_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
