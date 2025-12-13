<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelMotivoNotaCredito extends Model
{
    use HasFactory;

    protected $table = 'motivo_nota_credito';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'descripcion','users_id'];
}
