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
        return self::getMenusParaRoles([$rolId]);
    }

    /**
     * Obtener menús con submenus para VARIOS roles a la vez (union de
     * permisos). Usado por MenuHelper para soportar multi-rol: un usuario
     * ve el submenu si CUALQUIERA de sus roles (principal o adicional)
     * tiene acceso a el.
     */
    public static function getMenusParaRoles(array $rolIds)
    {
        return self::activos()
            ->with(['submenus' => function ($query) use ($rolIds) {
                $query->whereHas('roles', function ($q) use ($rolIds) {
                    $q->whereIn('rol_id', $rolIds);
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
