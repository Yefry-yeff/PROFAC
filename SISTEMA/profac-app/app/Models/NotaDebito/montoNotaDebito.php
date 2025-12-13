<?php

namespace App\Models\NotaDebito;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class montoNotaDebito extends Model
{
    use HasFactory;
    protected $table = 'montonotadebito';
    protected $primaryKey = 'id';
    protected $fillable = ['monto', 'descripcion', 'estado_id', 'users_registra_id'];
}
