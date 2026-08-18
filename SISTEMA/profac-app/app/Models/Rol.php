<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'rol';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'estado_id',
        'nivel_id',
        'area_id',
    ];

    /**
     * Relación con usuarios
     */
    public function usuarios()
    {
        return $this->hasMany(User::class, 'rol_id');
    }

    /**
     * Relación con estado
     */
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    /**
     * Nivel jerárquico del rol (Gerente, Jefe de Depto., Colaborador, etc.)
     */
    public function nivel()
    {
        return $this->belongsTo(NivelRol::class, 'nivel_id');
    }

    /**
     * Área / departamento al que pertenece el rol
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    /**
     * Relación muchos a muchos con submenus
     */
    public function submenus()
    {
        return $this->belongsToMany(
            SubMenu::class,
            'rol_submenu',
            'rol_id',
            'sub_menu_id'
        )->withTimestamps();
    }

    /**
     * Usuarios que tienen este rol como ADICIONAL (multi-rol), ademas de
     * los usuarios que lo tienen como principal (relación usuarios()).
     */
    public function usuariosAdicionales()
    {
        return $this->belongsToMany(User::class, 'usuario_rol', 'rol_id', 'usuario_id')
                    ->withTimestamps();
    }

    /**
     * Obtener los menús disponibles para este rol
     */
    public function getMenusConSubmenus()
    {
        return Menu::getMenusParaRol($this->id);
    }
}
