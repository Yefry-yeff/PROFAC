<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubMenu extends Model
{
    use HasFactory;

    protected $table = 'sub_menu';
    protected $primaryKey = 'id';

    public const URL_VENTAS = 'flujo/ventas';
    /** Opción de Ventas: ver los flujos de todos los usuarios. */
    public const OPCION_VER_TODOS_FLUJOS = 'flujo/ventas/opcion/ver-todos-flujos';

    public const URL_CATALOGO_PRODUCTOS = 'producto/registro';
    public const OPCION_PRODUCTOS_VER_DETALLE = 'producto/registro/opcion/ver-detalle';
    public const OPCION_PRODUCTOS_EDITAR = 'producto/registro/opcion/editar';
    public const OPCION_PRODUCTOS_SUBIR_FOTO = 'producto/registro/opcion/subir-foto';
    public const OPCION_PRODUCTOS_CREAR = 'producto/registro/opcion/crear';
    public const OPCION_PRODUCTOS_CARGA_MASIVA = 'producto/registro/opcion/carga-masiva';
    public const OPCION_PRODUCTOS_EXPORTAR = 'producto/registro/opcion/exportar';
    public const OPCION_PRODUCTOS_INACTIVAR = 'producto/registro/opcion/inactivar';
    public const OPCION_PRODUCTOS_UNIDADES_VENTA = 'producto/registro/opcion/unidades-venta';

    public const URL_CATALOGO_APOYO = 'producto/apoyo/registro';
    public const OPCION_APOYO_SUBIR_FOTO = 'producto/apoyo/registro/opcion/subir-foto';

    protected $fillable = [
        'url',
        'nombre',
        'menu_id',
        'padre_id',
        'orden',
        'estado_id',
        'icono'
    ];

    /**
     * Submenú del que es opción (null para submenús de navegación).
     */
    public function padre()
    {
        return $this->belongsTo(self::class, 'padre_id');
    }

    /**
     * Opciones (permisos sin navegación) que cuelgan de este submenú.
     */
    public function opciones()
    {
        return $this->hasMany(self::class, 'padre_id')->orderBy('orden');
    }

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
