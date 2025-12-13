<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelTranslado extends Model
{
    use HasFactory;
    protected $table = 'translado';
    protected $primaryKey = 'id';
    protected $fillable = ['id'];
}
