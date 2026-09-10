<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClienteActoresAsignados
{
    public const ROL_ASESOR_COMERCIAL = 2;
    public const ROL_TELE_ASESOR = 3;
    public const ROL_GESTOR_ENTREGA = 16;

    public static function usuarios(int $clienteId, int $rolId): Collection
    {
        return DB::table('cliente_usuario as cu')
            ->join('users as u', 'u.id', '=', 'cu.usuario_id')
            ->where('cu.cliente_id', $clienteId)
            ->where('cu.rol_id', $rolId)
            ->orderBy('u.name')
            ->distinct()
            ->get(['u.id', 'u.name as text']);
    }

    public static function validar(int $clienteId, int $usuarioId, int $rolId, string $campo): void
    {
        $asignado = DB::table('cliente_usuario as cu')
            ->join('users as u', 'u.id', '=', 'cu.usuario_id')
            ->where('cu.cliente_id', $clienteId)
            ->where('cu.usuario_id', $usuarioId)
            ->where('cu.rol_id', $rolId)
            ->exists();

        if (!$asignado) {
            $tipo = $rolId === self::ROL_TELE_ASESOR ? 'tele asesor' : 'asesor comercial';
            throw ValidationException::withMessages([
                $campo => "El {$tipo} seleccionado no está asignado a este cliente en la cartera.",
            ]);
        }
    }
}
