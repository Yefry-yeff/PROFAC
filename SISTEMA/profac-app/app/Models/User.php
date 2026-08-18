<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name', 'email', 'password','rol_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Rol PRINCIPAL asignado al usuario (FK users.rol_id). No se toca por
     * multi-rol: sigue siendo el rol de referencia para todo el codigo legado.
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    /**
     * Roles ADICIONALES del usuario (multi-rol), ademas de su rol principal.
     * Ver App\Models\UsuarioRol.
     */
    public function rolesAdicionales()
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'usuario_id', 'rol_id')
                    ->withTimestamps();
    }

    /**
     * IDs de TODOS los roles del usuario: el principal (rol_id) + los
     * adicionales asignados via usuario_rol. Usar esto (en vez de rol_id
     * directo) para cualquier verificacion de acceso/menu que deba
     * combinar permisos de multiples roles.
     *
     * @return int[]
     */
    public function rolesIds(): array
    {
        $ids = $this->rolesAdicionales()->pluck('rol.id')->toArray();
        if ($this->rol_id) {
            $ids[] = (int) $this->rol_id;
        }
        return array_values(array_unique(array_map('intval', $ids)));
    }

    /**
     * Verifica si el usuario tiene un rol especifico entre TODOS sus roles
     * (principal + adicionales).
     */
    public function tieneRol(int $rolId): bool
    {
        return in_array($rolId, $this->rolesIds(), true);
    }

    /**
     * Verifica si el usuario tiene el rol "Administrador" (id 1) entre
     * cualquiera de sus roles (principal o adicional).
     */
    public function esAdministrador(): bool
    {
        return $this->tieneRol(1);
    }
}
