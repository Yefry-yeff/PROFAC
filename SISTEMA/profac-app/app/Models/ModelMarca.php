<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelMarca extends Model
{
    use HasFactory;
    protected $table = 'marca';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'nombre','users_id'];
}

