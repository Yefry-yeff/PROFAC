<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrefacturaAuditoria extends Model
{
    protected $table    = 'prefactura_auditoria';
    protected $fillable = [
        'tipo_accion',
        'prefactura_id',
        'factura_id',
        'datos_anteriores',
        'datos_nuevos',
        'motivo',
        'usuario_id',
        'autorizador_id',
        'autorizacion_id',
        'ip',
        'session_id',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos'     => 'array',
    ];

    /** Registra una acción auditable. */
    public static function registrar(
        string $tipoAccion,
        ?int $prefacturaId,
        ?int $facturaId,
        ?array $datosAnteriores,
        ?array $datosNuevos,
        ?string $motivo,
        ?int $autorizacionId = null
    ): self {
        $auth   = \Illuminate\Support\Facades\Auth::user();
        $userId = $auth?->id;

        // Obtener users_id del código de autorización para el autorizador
        $autorizadorId = null;
        if ($autorizacionId) {
            $autorizadorId = \Illuminate\Support\Facades\DB::table('codigo_autorizacion')
                ->where('id', $autorizacionId)
                ->value('users_id');
        }

        return self::create([
            'tipo_accion'       => $tipoAccion,
            'prefactura_id'     => $prefacturaId,
            'factura_id'        => $facturaId,
            'datos_anteriores'  => $datosAnteriores,
            'datos_nuevos'      => $datosNuevos,
            'motivo'            => $motivo,
            'usuario_id'        => $userId,
            'autorizador_id'    => $autorizadorId,
            'autorizacion_id'   => $autorizacionId,
            'ip'                => request()->ip(),
            'session_id'        => session()->getId(),
        ]);
    }
}
