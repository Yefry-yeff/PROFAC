<?php

namespace App\Support;

use Illuminate\Http\Request;

class FiltrosNotasCredito
{
    public static function fechasYSecuencias(Request $request, array &$bindings): string
    {
        $secuenciaFactura = trim((string) $request->input('numero_secuencia_cai_factura', ''));
        $secuenciaNota = trim((string) $request->input('numero_secuencia_cai', ''));
        $sql = '';

        if ($secuenciaFactura !== '') {
            $sql .= ' AND (CAST(fa.numero_secuencia_cai AS CHAR) LIKE ? OR fa.cai LIKE ?)';
            $bindings[] = '%' . $secuenciaFactura . '%';
            $bindings[] = '%' . $secuenciaFactura . '%';
        } else {
            $sql .= ' AND A.fecha BETWEEN ? AND ?';
            $bindings[] = $request->fechaInicio;
            $bindings[] = $request->fechaFinal;
        }

        if ($secuenciaNota !== '') {
            $sql .= ' AND CAST(A.numero_secuencia_cai AS CHAR) LIKE ?';
            $bindings[] = '%' . $secuenciaNota . '%';
        }

        return $sql;
    }
}
