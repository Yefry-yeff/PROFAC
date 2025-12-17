<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    use HasFactory;

    protected $table = 'login_history';

    protected $fillable = [
        'user_id',
        'nombre',
        'terminal',
        'ip_address',
        'fecha_ingreso',
    ];

    protected $casts = [
        'fecha_ingreso' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
