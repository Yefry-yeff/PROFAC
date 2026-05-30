<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MigrateCreditoClienteToClienteCredito extends Migration
{
    /**
     * Migra los datos de crédito existentes en la tabla `cliente`
     * (campos: credito_inicial, credito, dias_credito, vendedor, metodo_pago)
     * hacia la nueva tabla `cliente_credito`.
     *
     * Solo procesa clientes que:
     *   - Tengan credito_inicial > 0, ó
     *   - Tengan credito > 0, ó
     *   - Tengan dias_credito > 0
     * y que NO tengan ya un registro en cliente_credito.
     */
    public function up()
    {
        // Usuarios que ya tienen registro en cliente_credito (para no duplicar)
        $yaExisten = DB::table('cliente_credito')
            ->pluck('cliente_id')
            ->toArray();

        // Clientes con datos de crédito en la tabla antigua
        $clientes = DB::table('cliente')
            ->where(function ($q) {
                $q->where('credito_inicial', '>', 0)
                  ->orWhere('credito', '>', 0)
                  ->orWhere('dias_credito', '>', 0);
            })
            ->whereNotIn('id', $yaExisten)
            ->select([
                'id',
                'credito_inicial',
                'credito',
                'dias_credito',
                'vendedor',
                'metodo_pago',
                'users_id',
                'created_at',
            ])
            ->get();

        $ahora = now();
        $insertados = 0;

        foreach ($clientes as $c) {
            $creditoActivo = ($c->credito_inicial > 0 || $c->credito > 0) ? 1 : 0;

            // Validar que users_id exista en la tabla users
            $usersId = DB::table('users')->where('id', $c->users_id)->value('id');
            if (!$usersId) {
                // Asignar al primer usuario administrador como fallback
                $usersId = DB::table('users')->orderBy('id')->value('id') ?? 1;
            }

            DB::table('cliente_credito')->insert([
                'activo'                  => 1,
                'cliente_id'              => $c->id,
                'credito_activo'          => $creditoActivo,
                'credito'                 => $c->credito_inicial ?? 0,
                'dias_credito'            => $c->dias_credito ?? 0,
                'vendedor_id'             => $c->vendedor ?: null,
                'referencias_bancarias'   => null,
                'referencias_comerciales' => null,
                'metodo_pago'             => $c->metodo_pago ?: null,
                'letra_cambio'            => 0,
                'obs_letra_cambio'        => null,
                'aval_solidario'          => 0,
                'obs_aval_solidario'      => null,
                'autorizacion_gerencia'   => null,
                'fecha_vigencia'          => null,
                'users_id'               => $usersId,
                'created_at'              => $c->created_at ?? $ahora,
                'updated_at'              => $ahora,
            ]);

            $insertados++;
        }

        echo "  Migrados: {$insertados} registros de crédito a cliente_credito.\n";
    }

    public function down()
    {
        // Eliminar solo los registros que provienen de la migración
        // (aquellos donde autorizacion_gerencia es NULL y obs_letra_cambio es NULL,
        // es decir, los migrados automáticamente, no los creados manualmente)
        // Para mayor seguridad se limpia toda la tabla si fue migración limpia.
        DB::statement("
            DELETE cc
            FROM cliente_credito cc
            WHERE NOT EXISTS (
                SELECT 1 FROM cliente_credito cc2
                WHERE cc2.cliente_id = cc.cliente_id
                  AND cc2.autorizacion_gerencia IS NOT NULL
                  AND cc2.id != cc.id
            )
            AND cc.autorizacion_gerencia IS NULL
            AND cc.referencias_bancarias IS NULL
            AND cc.referencias_comerciales IS NULL
        ");
    }
}
