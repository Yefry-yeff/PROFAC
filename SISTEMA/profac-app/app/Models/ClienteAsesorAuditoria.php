<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteAsesorAuditoria extends Model
{
    protected $table = 'cliente_asesor_auditoria';

    const UPDATED_AT = null;
    const CREATED_AT = null;

    protected $fillable = [
        'cliente_id',
        'asesor_id',
        'tipo',
        'accion',
        'usuario',
        'comentario',
        'lote_id',
        'fecha',
    ];

    public function cliente()
    {
        return $this->belongsTo(ModelCliente::class, 'cliente_id');
    }

    public function asesor()
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    public function usuarioResponsable()
    {
        return $this->belongsTo(User::class, 'usuario');
    }
}
