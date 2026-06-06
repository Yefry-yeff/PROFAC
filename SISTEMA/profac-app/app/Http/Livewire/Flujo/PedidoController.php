<?php

namespace App\Http\Livewire\Flujo;

use Illuminate\Support\Facades\DB;
use PDF;

/**
 * HTTP controller (non-Livewire) that handles pedido print.
 * Registered in web.php as a plain controller.
 */
class PedidoController
{
    // ─────────────────────────────────────────────────────────────────────
    // GET /flujo/pedido/imprimir/{id}
    // ─────────────────────────────────────────────────────────────────────
    public function imprimir(int $id)
    {
        $datos = DB::selectOne("
            SELECT
                p.cliente_id       AS clienteId,
                CONCAT(YEAR(p.created_at), '-', p.id) AS codigo,
                cl.nombre,
                cl.direccion,
                cl.correo,
                cl.telefono_empresa,
                DATE(p.created_at) AS fecha_emision,
                TIME(p.created_at) AS hora,
                cl.rtn,
                u.name             AS cotizador,
                p.observaciones    AS nota,
                (SELECT f.id FROM flujo f WHERE f.identificacion = CAST(p.id AS CHAR) AND f.tipo_flujo_id = 1 LIMIT 1) AS flujo_id
            FROM pedido p
            INNER JOIN cliente cl ON cl.id = p.cliente_id
            INNER JOIN users u    ON u.id  = p.users_id
            WHERE p.id = ?
        ", [$id]);

        if (!$datos) {
            abort(404, 'Pedido no encontrado.');
        }

        $productos = DB::select("
            SELECT
                nombre_producto,
                FORMAT(cantidad, 2) AS cantidad
            FROM pedido_detalle
            WHERE pedido_id = ?
            ORDER BY id ASC
        ", [$id]);

        $pdf = PDF::loadView('pdf.pedido', compact('datos', 'productos'))
                  ->setPaper('letter');

        return $pdf->stream("Pedido_NO_{$datos->codigo}.pdf");
    }
}
