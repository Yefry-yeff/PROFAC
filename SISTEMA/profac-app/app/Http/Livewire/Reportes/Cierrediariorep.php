<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class CierreDiariorep extends Component
{
    public function render()
    {
        return view('livewire.reportes.cierrediariorep');
    }

    public function consulta($fecha)
    {
        try {
            $consulta = DB::select("
                SELECT
                    bc.fechaCierre AS 'FECHA DE CIERRE',
                    cc.nombre_userCierre AS 'REGISTRADO POR',
                    cc.estadoDescripcion AS 'ESTADO DE CAJA',
                    f.id,
                    f.cai,
                    f.numero_factura AS 'FACTURA',
                    cc.cliente AS 'CLIENTE',
                    cc.vendedor AS 'VENDEDOR',
                    cc.subtotal AS 'SUBTOTAL FACTURADO',
                    cc.imp_venta AS 'ISV FACTURADO',
                    cc.total AS 'TOTAL FACTURADO',
                    cc.tipoFactura  AS 'CALIDAD DE FACTURA',
                    cc.tipo AS 'TIPO DE CLIENTE',
                    CASE
                        WHEN ac.id_tipo_pago_cobro IS NULL THEN 'N/A'
                        ELSE tpc.descripcion
                    END AS 'PAGO POR',
                    b.nombre AS 'BANCO',
                    ac.monto_abonado AS 'ABONO',
                    ac.fecha_pago AS 'FECHA DE PAGO'
                FROM cierrecaja cc
                INNER JOIN bitacoracierre bc ON bc.id = cc.bitacoraCierre_id
                INNER JOIN factura f ON f.cai = cc.factura AND cc.cliente = f.nombre_cliente
                INNER JOIN abonos_creditos ac ON ac.factura_id = f.id
                INNER JOIN banco b ON b.id = ac.banco_id
                LEFT JOIN tipo_pago_cobro tpc ON tpc.id = ac.id_tipo_pago_cobro
                WHERE bc.fechaCierre = ?", [$fecha]);

            return Datatables::of($consulta)
                ->rawColumns([])
                ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar el reporte solicitado.',
                'errorTh' => $e,
            ], 402);
        }
    }
}
