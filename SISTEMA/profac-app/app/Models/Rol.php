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
        'estado_id'
    ];

    /**
     * Relación con usuarios
     */
    public function usuarios()
    {
        return $this->hasMany(usuario::class, 'rol_id');
    }

    /**
     * Relación con estado
     */
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
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
     * Obtener los menús disponibles para este rol
     */
    public function getMenusConSubmenus()
    {
        return Menu::getMenusParaRol($this->id);
    }
}
