<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;

    protected $table = 'estado';
    
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'id',
        'descripcion',
    ];

    /**
     * Relación con usuarios
     */
    public function usuarios()
    {
        return $this->hasMany(usuario::class, 'estado_id');
    }
}
