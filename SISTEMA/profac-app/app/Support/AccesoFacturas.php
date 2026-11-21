<?php

namespace App\Support;

final class AccesoFacturas
{
    private const USUARIO_HISTORICO = 'yefryyo@gmail.com';

    public static function permiteHistorialCompleto(?object $usuario): bool
    {
        return $usuario !== null
            && strcasecmp(trim((string) ($usuario->email ?? '')), self::USUARIO_HISTORICO) === 0;
    }
}
