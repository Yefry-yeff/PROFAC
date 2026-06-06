<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubMenu extends Model
{
    use HasFactory;

    protected $table = 'sub_menu';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'url',
        'nombre',
        'menu_id',
        'orden',
        'estado_id',
        'icono'
    ];

    /**
     * Relación con menu principal
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * Relación con estado
     */
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    /**
     * Relación muchos a muchos con roles
     */
    public function roles()
    {
        return $this->belongsToMany(
            \App\Models\Rol::class,
            'rol_submenu',
            'sub_menu_id',
            'rol_id'
        )->withTimestamps();
    }

    /**
     * Scope para submenus activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado_id', 1)->orderBy('orden');
    }

    /**
     * Verificar si un rol tiene acceso a este submenu
     */
    public function tieneAcceso($rolId)
    {
        return $this->roles()->where('rol_id', $rolId)->exists();
    }
}
