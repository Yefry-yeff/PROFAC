<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Vuelca los datos de documentos comerciales desde cotizacion → flujo,
 * usando historico_flujo (tipo_tramite_id = 2) como puente de relación.
 *
 * Para cada cotización vinculada a un flujo vía historico_flujo,
 * si el flujo aún no tiene el campo lleno se copia desde la cotización.
 * Los campos ya cargados en flujo no se sobreescriben (COALESCE).
 *
 * Columnas sincronizadas:
 *   numero_orden_compra, archivo_orden_compra,
 *   numero_forma_f01,    archivo_forma_f01
 *
 * (numero_exoneracion/archivo_exoneracion no existían en cotizacion —
 *  esos campos solo aplican para registros nuevos en flujo.)
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE flujo f
            INNER JOIN (
                SELECT
                    hf.flujo_id,
                    MAX(c.numero_orden_compra)  AS numero_orden_compra,
                    MAX(c.archivo_orden_compra) AS archivo_orden_compra,
                    MAX(c.numero_forma_f01)     AS numero_forma_f01,
                    MAX(c.archivo_forma_f01)    AS archivo_forma_f01
                FROM historico_flujo hf
                INNER JOIN cotizacion c ON c.id = hf.tramite_id
                WHERE hf.tipo_tramite_id = 2
                  AND (
                        c.numero_orden_compra  IS NOT NULL
                     OR c.archivo_orden_compra IS NOT NULL
                     OR c.numero_forma_f01     IS NOT NULL
                     OR c.archivo_forma_f01    IS NOT NULL
                  )
                GROUP BY hf.flujo_id
            ) datos ON datos.flujo_id = f.id
            SET
                f.numero_orden_compra  = COALESCE(f.numero_orden_compra,  datos.numero_orden_compra),
                f.archivo_orden_compra = COALESCE(f.archivo_orden_compra, datos.archivo_orden_compra),
                f.numero_forma_f01     = COALESCE(f.numero_forma_f01,     datos.numero_forma_f01),
                f.archivo_forma_f01    = COALESCE(f.archivo_forma_f01,    datos.archivo_forma_f01)
        ");
    }

    public function down(): void
    {
        // No se puede revertir un volcado de datos de forma segura.
        // Los campos en flujo pueden dejarse tal como están.
    }
};
