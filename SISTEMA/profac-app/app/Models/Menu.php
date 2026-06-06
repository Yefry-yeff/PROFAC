<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menu';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'icon',
        'nombre_menu',
        'orden',
        'estado_id'
    ];

    /**
     * Relación con submenus
     */
    public function submenus()
    {
        return $this->hasMany(SubMenu::class, 'menu_id')->orderBy('orden');
    }

    /**
     * Relación con estado
     */
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    /**
     * Scope para menus activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado_id', 1)->orderBy('orden');
    }

    /**
     * Obtener menús con submenus para un rol específico
     */
    public static function getMenusParaRol($rolId)
    {
        return self::activos()
            ->with(['submenus' => function ($query) use ($rolId) {
                $query->whereHas('roles', function ($q) use ($rolId) {
                    $q->where('rol_id', $rolId);
                })
                ->where('estado_id', 1)
                ->orderBy('orden');
            }])
            ->get()
            ->filter(function ($menu) {
                return $menu->submenus->count() > 0;
            });
    }
}
