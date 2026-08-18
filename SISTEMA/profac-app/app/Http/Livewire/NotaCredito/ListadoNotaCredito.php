<?php

namespace App\Http\Livewire\NotaCredito;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\File;
use DataTables;
use Auth;
use Validator;
use PDF;
use Luecano\NumeroALetras\NumeroALetras;

use App\Models\ModelFactura;
use App\Models\ModelCAI;
use App\Models\ModelRecibirBodega;
use App\Models\ModelVentaProducto;
use App\Models\ModelLogTranslados;
use App\Models\ModelParametro;
use App\Models\ModelLista;
use App\Models\ModelCliente;
use App\Models\logCredito;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NotasCreditoExport;

class ListadoNotaCredito extends Component
{
    public function render()
    {
        $fechaActual = date('n');
        $resta = $fechaActual - 2;

        $mesActual =0;
        $AnioActual = date('Y');

        if($resta<=0){
            $mesActual=12;
            $AnioActual = $AnioActual - 1;
        }elseif($resta>0 && $resta<10){
            $mesActual = '0'.$resta;
        }else{
            $mesActual = date('m');
        }


        $fechaInicio = $AnioActual.'-'.$mesActual.'-01';

        $clientes = DB::select("SELECT id, nombre FROM cliente ORDER BY nombre ASC");
        $motivos  = DB::select("SELECT id, descripcion FROM motivo_nota_credito ORDER BY descripcion ASC");
        $usuarios = DB::select("SELECT id, name FROM users WHERE estado_id = 1 ORDER BY name ASC");

        return view('livewire.nota-credito.listado-nota-credito', compact('fechaInicio', 'clientes', 'motivos', 'usuarios'));
    }

    public function listadoNotaCredito(Request $request){
        try{
            $listado = DB::SELECT("
            select
            fa.id as idFactura,
            A.id as codigo,
            A.cai,
            cli.nombre as cliente,
            fa.cai as factura,
            B.descripcion as motivo,
            A.comentario,
            A.sub_total as sub_total,
            A.isv as isv,
            A.total as total,
            COALESCE(cc.monto_aplicado, 0) as monto_aplicado,
            COALESCE(cc.monto_reembolsado, 0) as monto_reembolsado,
            COALESCE(cc.saldo_disponible, 0) as saldo_disponible,
            COALESCE((
                SELECT SUM(ap.saldo) FROM aplicacion_pagos ap
                WHERE ap.cliente_id = cli.id AND ap.estado = 1
                    AND ap.estado_cerrado <> 2 AND ap.saldo > 0.005
            ), 0) as saldo_pendiente_cliente,
            CASE COALESCE(cc.estado, '')
                WHEN 'disponible' THEN 'Disponible'
                WHEN 'parcial' THEN 'Parcialmente utilizada'
                WHEN 'consumido' THEN 'Consumida'
                WHEN 'legado_consumido' THEN 'Consumida (legado)'
                WHEN 'anulado' THEN 'Anulada'
                ELSE 'Sin billetera'
            END as estado_credito,
            A.created_at as fecha_registro,
            name as registrado_por
            from nota_credito A
            inner join motivo_nota_credito B
            on A.motivo_nota_credito_id = B.id
            inner join users
            on A.users_id = users.id
            inner join factura fa on fa.id = A.factura_id
            inner join cliente cli on cli.id = fa.cliente_id
            left join nota_credito_creditos cc on cc.nota_credito_id = A.id
            where

            fa.tipo_venta_id = 2
            and estado_nota_id <>2
            and A.fecha BETWEEN '".$request->fechaInicio."' and '".$request->fechaFinal."'"
            . ($request->cliente_id ? " and cli.id = " . intval($request->cliente_id) : "")
            . ($request->motivo_id  ? " and A.motivo_nota_credito_id = " . intval($request->motivo_id) : "")
            . ($request->user_id    ? " and A.users_id = " . intval($request->user_id) : "")
            );
            //dd($listado);
            /* A.estado_nota_dec = 1 */
            return Datatables::of($listado)
            ->addColumn('opciones', function ($nota) {
                    $gestionar = (float) $nota->saldo_disponible > 0.005
                        ? '<li><a class="dropdown-item" onclick="gestionarCreditoNota('.$nota->codigo.','.$nota->saldo_disponible.','.$nota->saldo_pendiente_cliente.')"><i class="fa fa-random"></i> Gestionar crédito</a></li>'
                        : '';
                    return

                    '<div class="btn-group">
                    <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                        más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
    
                        
                             '.$gestionar.'
                             <li><a class="dropdown-item" onclick="anularNota('.$nota->codigo.','.$nota->idFactura.' )" class="btn btn-sm btn-warning "><i class="fa-solid fa-trash"></i> Anular</a></li>
    
    
                            <li><a class="dropdown-item" href="/nota/credito/imprimir/'.$nota->codigo.'" target="_blank" class="btn btn-sm btn-warning "><i class="fa-solid fa-file-invoice"></i> Imprimir Orginal</a></li>
    
                            <li><a class="dropdown-item" href="/nota/credito/imprimir/copia/'.$nota->codigo.'" target="_blank" class="btn btn-sm btn-warning "><i class="fa-solid fa-file-invoice"></i> Imprimir Copia</a></li>
                            <li><a class="dropdown-item" onclick="verAsientosNota('.$nota->codigo.')"><i class="fa fa-balance-scale"></i> Ver ajustes contables</a></li>
    
                        </ul>
    
    
                    </div>';
            })

            ->rawColumns(['opciones',])
            ->make(true);



           } catch (QueryException $e) {
           return response()->json([
            'icon' => '',
            'text' => '',
            'title' => '',
            'message' => 'Ha ocurrido un error',
            'error' => $e,
           ],402);
           }
    }


    public function kpis(Request $request)
    {
        try {
            $tipoVenta = intval($request->tipo_venta ?? 2);
            $sql = "
                SELECT COUNT(*) as total,
                    COALESCE(SUM(A.sub_total),0) as sub_total,
                    COALESCE(SUM(A.isv),0) as isv,
                    COALESCE(SUM(A.total),0) as total_monto
                FROM nota_credito A
                INNER JOIN factura fa ON fa.id = A.factura_id
                INNER JOIN cliente cli ON cli.id = fa.cliente_id
                WHERE fa.tipo_venta_id = {$tipoVenta}
                AND estado_nota_id <> 2
                AND A.fecha BETWEEN '".$request->fechaInicio."' AND '".$request->fechaFinal."'"
                . ($request->cliente_id ? " AND cli.id = " . intval($request->cliente_id) : "")
                . ($request->motivo_id  ? " AND A.motivo_nota_credito_id = " . intval($request->motivo_id) : "")
                . ($request->user_id    ? " AND A.users_id = " . intval($request->user_id) : "");
            $row = DB::selectOne($sql);
            return response()->json([
                'success'     => true,
                'total'       => $row->total       ?? 0,
                'sub_total'   => $row->sub_total   ?? 0,
                'isv'         => $row->isv         ?? 0,
                'total_monto' => $row->total_monto ?? 0,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false], 500);
        }
    }

    public function asientos($idNotaCredito)
    {
        $asientos = DB::table('nota_credito_asientos as a')
            ->join('nota_credito_asiento_detalles as d', 'd.asiento_id', '=', 'a.id')
            ->leftJoin('users as u', 'u.id', '=', 'a.users_id')
            ->where('a.nota_credito_id', (int) $idNotaCredito)
            ->orderBy('a.fecha')
            ->orderBy('a.id')
            ->orderBy('d.id')
            ->select(
                'a.id', 'a.tipo', 'a.fecha', 'a.descripcion', 'u.name as usuario',
                'd.cuenta_codigo', 'd.cuenta_nombre', 'd.debe', 'd.haber'
            )
            ->get()
            ->groupBy('id')
            ->map(function ($detalles) {
                $primero = $detalles->first();
                return [
                    'tipo' => $primero->tipo,
                    'fecha' => $primero->fecha,
                    'descripcion' => $primero->descripcion,
                    'usuario' => $primero->usuario,
                    'detalles' => $detalles->map(function ($detalle) {
                        return [
                            'codigo' => $detalle->cuenta_codigo,
                            'cuenta' => $detalle->cuenta_nombre,
                            'debe' => (float) $detalle->debe,
                            'haber' => (float) $detalle->haber,
                        ];
                    })->values(),
                ];
            })->values();

        return response()->json(['asientos' => $asientos]);
    }

    public function exportarExcel(Request $request)
    {
        try {
            $listado = DB::SELECT("
                SELECT A.id as codigo, A.cai, cli.nombre as cliente, fa.cai as factura,
                    B.descripcion as motivo, A.comentario, A.sub_total, A.isv, A.total,
                    COALESCE(cc.monto_aplicado, 0) as monto_aplicado,
                    COALESCE(cc.monto_reembolsado, 0) as monto_reembolsado,
                    COALESCE(cc.saldo_disponible, 0) as saldo_disponible,
                    CASE COALESCE(cc.estado, '')
                        WHEN 'disponible' THEN 'Disponible'
                        WHEN 'parcial' THEN 'Parcialmente utilizada'
                        WHEN 'consumido' THEN 'Consumida'
                        WHEN 'legado_consumido' THEN 'Consumida (legado)'
                        WHEN 'anulado' THEN 'Anulada'
                        ELSE 'Sin billetera'
                    END as estado_credito,
                    A.created_at as fecha_registro, users.name as registrado_por
                FROM nota_credito A
                INNER JOIN motivo_nota_credito B ON A.motivo_nota_credito_id = B.id
                INNER JOIN users ON A.users_id = users.id
                INNER JOIN factura fa ON fa.id = A.factura_id
                INNER JOIN cliente cli ON cli.id = fa.cliente_id
                LEFT JOIN nota_credito_creditos cc ON cc.nota_credito_id = A.id
                WHERE fa.tipo_venta_id = 2
                AND estado_nota_id <> 2
                AND A.fecha BETWEEN '".$request->fechaInicio."' AND '".$request->fechaFinal."'"
                . ($request->cliente_id ? " AND cli.id = " . intval($request->cliente_id) : "")
                . ($request->motivo_id  ? " AND A.motivo_nota_credito_id = " . intval($request->motivo_id) : "")
                . ($request->user_id    ? " AND A.users_id = " . intval($request->user_id) : "")
                . " ORDER BY A.fecha DESC"
            );
            $usuario = Auth::check() ? Auth::user()->name : 'Sistema';
            return Excel::download(
                new NotasCreditoExport($listado, $usuario, 'Notas de Crédito — Clientes A'),
                'NotasCreditoA_' . now()->format('Y-m-d') . '.xlsx'
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function imprimirFacturaCoorporativa($idFactura)
    {
            /*CONSULTA PARA LISTAR PRODUCTOS NOTA DE CRÉDITO*/

            /*


        select
            D.id AS codigo,
            D.nombre as descripcion,
            F.nombre as medida,
            H.nombre AS bodega,
            FF.descripcion as seccion,
            FORMAT(C.precio_unidad,2) as precio,
            FORMAT(C.cantidad,2) as cantidad,
            FORMAT(C.sub_total,2) as sub_total
        from factura A
        inner join nota_credito B
        on A.id = B.factura_id
        inner join nota_credito_has_producto C
        on B.id = C.nota_credito_id
        inner join producto D
        on C.producto_id = D.id
        inner join unidad_medida_venta E
        on C.unidad_medida_venta_id = E.id
        inner join unidad_medida F
        on F.id = E.unidad_medida_id
        inner join seccion FF
        on C.seccion_id = FF.id
        inner join segmento G
        on FF.segmento_id = G.id
        inner join bodega H
        on G.bodega_id = H.id
        where B.estado_nota_id=1 and A.id = 1422
        group by  codigo ,descripcion, medida,bodega, seccion, precio, cantidad,sub_total
            */
        $cai = DB::SELECTONE("
        select
        A.cai as numero_factura,
        A.numero_factura as numero,
        A.estado_factura_id as estado_factura,
        A.estado_venta_id,
        B.cai,
        DATE_FORMAT(B.fecha_limite_emision,'%d/%m/%Y' ) as fecha_limite_emision,
        B.numero_inicial,
        B.numero_final,
        C.descripcion,
        DATE_FORMAT(A.fecha_emision,'%d/%m/%Y' ) as  fecha_emision,
        TIME(A.created_at) as hora,
        DATE_FORMAT(A.fecha_vencimiento,'%d/%m/%Y' ) as fecha_vencimiento,
        name,
        D.id as factura

       from factura A
       inner join cai B
       on A.cai_id = B.id
       inner join tipo_pago_venta C
       on A.tipo_pago_id = C.id
       inner join users
       on A.vendedor = users.id
       inner join estado_factura D
       on A.estado_factura_id = D.id
       where A.id = ".$idFactura);

       $cliente = DB::SELECTONE("
       select
        factura.nombre_cliente as nombre,
        cliente.direccion,
        cliente.correo,
        factura.fecha_emision,
        factura.fecha_vencimiento,
        TIME(factura.created_at) as hora,
        cliente.telefono_empresa,
        cliente.rtn
        from factura
        inner join cliente
        on factura.cliente_id = cliente.id
        where factura.id = ".$idFactura);


            $importes = DB::SELECTONE("
            select
            total,
            isv,
            sub_total,
            sub_total_grabado,
            sub_total_excento
            from factura
            where id = " . $idFactura);


            $importesConCentavos = DB::SELECTONE("
            select
            FORMAT(total,2) as total,
            FORMAT(isv,2) as isv,
            FORMAT(sub_total,2) as sub_total,
            FORMAT(sub_total_grabado,2) as sub_total_grabado,
            FORMAT(sub_total_excento,2) as sub_total_excento
            from factura where factura.id = " . $idFactura);




        $productos = DB::SELECT("

                select
                D.id AS codigo,
                D.nombre as descripcion,
                F.nombre as medida,
                H.nombre AS bodega,
                FF.descripcion as seccion,
                FORMAT(C.precio_unidad,2) as precio,
                FORMAT(C.cantidad,2) as cantidad,
                FORMAT(C.sub_total,2) as sub_total
            from factura A
            inner join nota_credito B
            on A.id = B.factura_id
            inner join nota_credito_has_producto C
            on B.id = C.nota_credito_id
            inner join producto D
            on C.producto_id = D.id
            inner join unidad_medida_venta E
            on C.unidad_medida_venta_id = E.id
            inner join unidad_medida F
            on F.id = E.unidad_medida_id
            inner join seccion FF
            on C.seccion_id = FF.id
            inner join segmento G
            on FF.segmento_id = G.id
            inner join bodega H
            on G.bodega_id = H.id
            where B.estado_nota_id=1 and A.id = ".$idFactura."
            group by  codigo ,descripcion, medida,bodega, seccion, precio, cantidad,sub_total
            "
        );

        $ordenCompra = DB::SELECTONE("
        select
        B.numero_orden
        from factura A
        inner join numero_orden_compra B
        on A.numero_orden_compra_id = B.id
        where A.id =".$idFactura);

        if(empty($ordenCompra->numero_orden)){
            $ordenCompra=["numero_orden"=>""];
        }else{
            $ordenCompra=["numero_orden"=>$ordenCompra->numero_orden];
        }


        if( fmod($importes->total, 1) == 0.0 ){
            $flagCentavos = false;

        }else{
            $flagCentavos = true;
        }

        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $pdf = PDF::loadView('/pdf/notaCredito', compact('cai', 'cliente','importes','productos','numeroLetras','importesConCentavos','flagCentavos','ordenCompra'))->setPaper('letter');

        return $pdf->stream("nota_credito" . $cai->numero_factura.".pdf");


    }

    public function imprimirnotaCreditoOriginal($idNota)
    {
        $cai = DB::SELECTONE("
        select
        A.cai nota_credito_cai,
        B.cai factura,
        C.cai,
        CONCAT(DAY(C.fecha_limite_emision),'/',MONTH(C.fecha_limite_emision),'/',YEAR(C.fecha_limite_emision)) fecha_limite_emision,
        C.numero_inicial,
        C.numero_final,
        DATE_FORMAT(A.created_at,'%d/%m/%Y' ) as  fecha_emision,
        TIME(A.created_at) as hora,
        DATE_FORMAT(B.fecha_vencimiento,'%d/%m/%Y' ) as fecha_vencimiento,
        U.name, B.estado_factura_id as estado_factura, B.estado_venta_id, B.numero_factura
        from nota_credito A
        inner join factura B
        on A.factura_id = B.id
        inner join cai C
        on A.cai_id = C.id
        inner join users U on (U.id = A.users_id)
        where A.id =".$idNota
        );



        $cliente = DB::SELECTONE("
        select
         factura.nombre_cliente as nombre,
         cliente.direccion,
         cliente.correo,
         factura.fecha_emision,
         factura.fecha_vencimiento,
         TIME(factura.created_at) as hora,
         cliente.telefono_empresa,
         cliente.rtn
         from factura
         inner join cliente
         on factura.cliente_id = cliente.id
         inner join nota_credito
         on nota_credito.factura_id = factura.id
         where nota_credito.id = ".$idNota
        );




            $importes = DB::SELECTONE("
            select
            total,
            isv,
            sub_total,
            sub_total_grabado,
            sub_total_excento
            from nota_credito
            where id = " . $idNota);


            $importesConCentavos = DB::SELECTONE("
            select
            FORMAT(total,2) as total,
            FORMAT(isv,2) as isv,
            FORMAT(sub_total,2) as sub_total,
            FORMAT(sub_total_grabado,2) as sub_total_grabado,
            FORMAT(sub_total_excento,2) as sub_total_excento
            from nota_credito where nota_credito.id = " . $idNota);


            $productos = DB::SELECT("
            select
            D.id AS codigo,
            D.nombre as descripcion,
            F.nombre as medida,
            H.nombre AS bodega,
            FF.descripcion as seccion,
            FORMAT(C.precio_unidad,2) as precio,
            FORMAT(C.cantidad,2) as cantidad,
            FORMAT(C.sub_total,2) as sub_total,
            C.indice
            from factura A
            inner join nota_credito B
            on A.id = B.factura_id
            inner join nota_credito_has_producto C
            on B.id = C.nota_credito_id
            inner join producto D
            on C.producto_id = D.id
            inner join unidad_medida_venta E
            on C.unidad_medida_venta_id = E.id
            inner join unidad_medida F
            on F.id = E.unidad_medida_id
            inner join seccion FF
            on C.seccion_id = FF.id
            inner join segmento G
            on FF.segmento_id = G.id
            inner join bodega H
            on G.bodega_id = H.id
            where B.estado_nota_id=1 and B.id = ".$idNota."
            group by  codigo ,descripcion, medida,bodega, seccion, precio, cantidad,sub_total,C.indice
            UNION
            
            select
            0 as codigo,
            CONCAT('DESCUENTO - ', IFNULL(M.descripcion, B.comentario)) as descripcion,
            '' as medida,
            '' as bodega,
            '' as seccion,
            FORMAT(B.total,2) as precio,
            FORMAT(1,0) as cantidad,
            FORMAT(B.total,2) as sub_total,
            99999 as indice
            from factura A
            inner join nota_credito B
            on A.id = B.factura_id
            left join motivo_nota_credito M
            on B.motivo_nota_credito_id = M.id
            where B.estado_nota_id=1 and B.id = ".$idNota."
            order by indice asc
            "  
            );


            if( fmod($importes->total, 1) == 0.0 ){
                $flagCentavos = false;

            }else{
                $flagCentavos = true;
            }

            $formatter = new NumeroALetras();
            $formatter->apocope = true;
            $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

            $comentario = DB::SELECTONE("SELECT comentario FROM nota_credito WHERE id = " . $idNota);

            // Parsear comentario: JSON (tipo descuento) contiene descripcion + notas; texto plano = solo notas
            $descripcion = '';
            $notas = $comentario->comentario ?? 'N/A';
            $decoded = json_decode($notas, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['notas'])) {
                $descripcion = $decoded['descripcion'] ?? '';
                $notas = $decoded['notas'] ?? '';
            }

            if ($descripcion) {
                // Nota por descuento: reemplazar descripcion del row DESCUENTO con el comentario_descuento
                foreach ($productos as $producto) {
                    if ($producto->indice == 99999) {
                        $producto->descripcion = $descripcion;
                        break;
                    }
                }
            } else {
                // Nota por producto: eliminar el row DESCUENTO que no corresponde
                $productos = array_values(array_filter($productos, function($p) {
                    return $p->indice != 99999;
                }));
            }

            $movimientosCredito = $this->movimientosCreditoParaImpresion((int) $idNota);
            $pdf = PDF::loadView('/pdf/notaCredito', compact('cai', 'cliente','importes','productos','numeroLetras','importesConCentavos','flagCentavos','comentario','descripcion','notas','movimientosCredito'))->setPaper('letter');

            return $pdf->stream("nota_credito" . $cai->nota_credito_cai.".pdf");






    }

    public function imprimirnotaCreditoCopia($idNota)
    {
        $cai = DB::SELECTONE("
        select
        A.cai nota_credito_cai,
        B.cai factura,
        C.cai,
        CONCAT(DAY(C.fecha_limite_emision),'/',MONTH(C.fecha_limite_emision),'/',YEAR(C.fecha_limite_emision)) fecha_limite_emision,
        C.numero_inicial,
        C.numero_final,
        DATE_FORMAT(A.created_at,'%d/%m/%Y' ) as  fecha_emision,
        TIME(A.created_at) as hora,
        DATE_FORMAT(B.fecha_vencimiento,'%d/%m/%Y' ) as fecha_vencimiento,
        U.name, B.estado_factura_id as estado_factura, B.estado_venta_id, B.numero_factura
        from nota_credito A
        inner join factura B
        on A.factura_id = B.id
        inner join cai C
        on A.cai_id = C.id
        inner join users U on (U.id = A.users_id)
        where A.id =".$idNota
        );



        $cliente = DB::SELECTONE("
        select
         factura.nombre_cliente as nombre,
         cliente.direccion,
         cliente.correo,
         factura.fecha_emision,
         factura.fecha_vencimiento,
         TIME(factura.created_at) as hora,
         cliente.telefono_empresa,
         cliente.rtn
         from factura
         inner join cliente
         on factura.cliente_id = cliente.id
         inner join nota_credito
         on nota_credito.factura_id = factura.id
         where nota_credito.id = ".$idNota
        );




            $importes = DB::SELECTONE("
            select
            total,
            isv,
            sub_total,
            sub_total_grabado,
            sub_total_excento
            from nota_credito
            where id = " . $idNota);


            $importesConCentavos = DB::SELECTONE("
            select
            FORMAT(total,2) as total,
            FORMAT(isv,2) as isv,
            FORMAT(sub_total,2) as sub_total,
            FORMAT(sub_total_grabado,2) as sub_total_grabado,
            FORMAT(sub_total_excento,2) as sub_total_excento
            from nota_credito where nota_credito.id = " . $idNota);


            $productos = DB::SELECT("
            select
            D.id AS codigo,
            D.nombre as descripcion,
            F.nombre as medida,
            H.nombre AS bodega,
            FF.descripcion as seccion,
            FORMAT(C.precio_unidad,2) as precio,
            FORMAT(C.cantidad,2) as cantidad,
            FORMAT(C.sub_total,2) as sub_total,
            C.indice
            from factura A
            inner join nota_credito B
            on A.id = B.factura_id
            inner join nota_credito_has_producto C
            on B.id = C.nota_credito_id
            inner join producto D
            on C.producto_id = D.id
            inner join unidad_medida_venta E
            on C.unidad_medida_venta_id = E.id
            inner join unidad_medida F
            on F.id = E.unidad_medida_id
            inner join seccion FF
            on C.seccion_id = FF.id
            inner join segmento G
            on FF.segmento_id = G.id
            inner join bodega H
            on G.bodega_id = H.id
            where B.estado_nota_id=1 and B.id = ".$idNota."
            group by  codigo ,descripcion, medida,bodega, seccion, precio, cantidad,sub_total,C.indice
            UNION
            
            select
            0 as codigo,
            CONCAT('DESCUENTO - ', IFNULL(M.descripcion, B.comentario)) as descripcion,
            '' as medida,
            '' as bodega,
            '' as seccion,
            FORMAT(B.total,2) as precio,
            FORMAT(1,0) as cantidad,
            FORMAT(B.total,2) as sub_total,
            99999 as indice
            from factura A
            inner join nota_credito B
            on A.id = B.factura_id
            left join motivo_nota_credito M
            on B.motivo_nota_credito_id = M.id
            where B.estado_nota_id=1 and B.id = ".$idNota."
            order by indice asc
            "  
            );


            if( fmod($importes->total, 1) == 0.0 ){
                $flagCentavos = false;

            }else{
                $flagCentavos = true;
            }

            $formatter = new NumeroALetras();
            $formatter->apocope = true;
            $numeroLetras = $formatter->toMoney($importes->total, 2, 'LEMPIRAS', 'CENTAVOS');

            $comentario = DB::SELECTONE("SELECT comentario FROM nota_credito WHERE id = " . $idNota);

            // Parsear comentario: JSON (tipo descuento) contiene descripcion + notas; texto plano = solo notas
            $descripcion = '';
            $notas = $comentario->comentario ?? 'N/A';
            $decoded = json_decode($notas, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['notas'])) {
                $descripcion = $decoded['descripcion'] ?? '';
                $notas = $decoded['notas'] ?? '';
            }

            if ($descripcion) {
                // Nota por descuento: reemplazar descripcion del row DESCUENTO con el comentario_descuento
                foreach ($productos as $producto) {
                    if ($producto->indice == 99999) {
                        $producto->descripcion = $descripcion;
                        break;
                    }
                }
            } else {
                // Nota por producto: eliminar el row DESCUENTO que no corresponde
                $productos = array_values(array_filter($productos, function($p) {
                    return $p->indice != 99999;
                }));
            }

            $movimientosCredito = $this->movimientosCreditoParaImpresion((int) $idNota);
            $pdf = PDF::loadView('/pdf/notaCredito_copia', compact('cai', 'cliente','importes','productos','numeroLetras','importesConCentavos','flagCentavos','comentario','descripcion','notas','movimientosCredito'))->setPaper('letter');

            return $pdf->stream("nota_credito" . $cai->nota_credito_cai.".pdf");






    }

    private function movimientosCreditoParaImpresion(int $idNota): array
    {
        return DB::table('nota_credito_movimientos as m')
            ->join('nota_credito_creditos as c', 'c.id', '=', 'm.credito_id')
            ->leftJoin('factura as f', 'f.id', '=', 'm.factura_id')
            ->leftJoin('tipo_pago_cobro as tpc', 'tpc.id', '=', 'm.tipo_pago_cobro_id')
            ->where('c.nota_credito_id', $idNota)
            ->whereIn('m.tipo', ['aplicacion', 'reembolso'])
            ->orderBy('m.id')
            ->get([
                'm.tipo',
                'm.monto',
                'f.cai as factura',
                'tpc.descripcion as metodo_reembolso',
            ])
            ->all();
    }
}




