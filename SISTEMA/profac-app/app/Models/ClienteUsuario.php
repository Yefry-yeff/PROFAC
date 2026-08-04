<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteUsuario extends Model
{
    protected $table = 'cliente_usuario';

    protected $fillable = [
        'cliente_id',
        'usuario_id',
        'rol_id',
        'fecha_asignacion',
        'asignado_por',
    ];

    public $timestamps = true;

    public function cliente()
    {
        return $this->belongsTo(ModelCliente::class, 'cliente_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
