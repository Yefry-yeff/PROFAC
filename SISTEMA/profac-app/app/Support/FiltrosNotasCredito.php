<?php

namespace App\Support;

use Illuminate\Http\Request;

class FiltrosNotasCredito
{
    public static function fechasYSecuencias(Request $request, array &$bindings): string
    {
        $secuenciaFactura = trim((string) $request->input('numero_secuencia_cai_factura', ''));
        $busquedaNota = trim((string) $request->input('numero_secuencia_cai', ''));
        $sql = '';

        if ($secuenciaFactura !== '') {
            $sql .= ' AND (CAST(fa.numero_secuencia_cai AS CHAR) LIKE ? OR fa.cai LIKE ?)';
            $bindings[] = '%' . $secuenciaFactura . '%';
            $bindings[] = '%' . $secuenciaFactura . '%';
        }

        if ($busquedaNota !== '') {
            $sql .= ' AND (
                CAST(A.numero_secuencia_cai AS CHAR) LIKE ?
                OR A.numero_nota LIKE ?
                OR A.cai LIKE ?
                OR CAST(A.id AS CHAR) LIKE ?
            )';
            array_push(
                $bindings,
                '%' . $busquedaNota . '%',
                '%' . $busquedaNota . '%',
                '%' . $busquedaNota . '%',
                '%' . $busquedaNota . '%'
            );
        }

        if ($secuenciaFactura === '' && $busquedaNota === '') {
            $sql .= ' AND A.fecha BETWEEN ? AND ?';
            $bindings[] = $request->fechaInicio;
            $bindings[] = $request->fechaFinal;
        }

        return $sql;
    }
}
