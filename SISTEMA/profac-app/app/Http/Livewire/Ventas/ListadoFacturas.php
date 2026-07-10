<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;
use Illuminate\Http\Request;
use App\Exports\FacturasUnificadasExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use Illuminate\Database\QueryException;
use Throwable;
use DataTables;
use App\Models\ModelCliente;
use App\Models\ModelFactura;
use App\Models\ModelLogEstadoFactura;
use App\Models\ModelRecibirBodega;
use App\Models\ModelLogTranslados;



class ListadoFacturas extends Component
{
    const ADMIN_ROLES = [1, 3, 5, 16];

    public function render()
    {
        return view('livewire.ventas.listado-facturas-unificado', ['tipoVenta' => 'corporativo', 'nombreTipo' => 'Clientes B', 'esVendedor' => false]); // Vista unificada
    }

    public function listarFacturas(){

        try {
            $filtroCai        = trim(request()->input('filtroCai', ''));
            $filtroCliente    = trim(request()->input('filtroCliente', ''));
            $filtroVendedor   = trim(request()->input('filtroVendedor', ''));
            $filtroFacturador = trim(request()->input('filtroFacturador', ''));
            $filtroDesde      = trim(request()->input('filtroDesde', ''));
            $filtroHasta      = trim(request()->input('filtroHasta', ''));
            $whereFilters = '';
            $bindings     = [];
            if ($filtroCai)        { $whereFilters .= " AND A.cai LIKE ? ";                                              $bindings[] = "%{$filtroCai}%"; }
            if ($filtroCliente)    { $whereFilters .= " AND factura.nombre_cliente LIKE ? ";                             $bindings[] = "%{$filtroCliente}%"; }
            if ($filtroVendedor)   { $whereFilters .= " AND users.name LIKE ? ";                                         $bindings[] = "%{$filtroVendedor}%"; }
            if ($filtroFacturador) { $whereFilters .= " AND (SELECT name FROM users WHERE id = factura.users_id) LIKE ? "; $bindings[] = "%{$filtroFacturador}%"; }
            if ($filtroDesde && $filtroHasta) { $whereFilters .= " AND DATE(factura.created_at) BETWEEN ? AND ? "; $bindings[] = $filtroDesde; $bindings[] = $filtroHasta; }
            elseif ($filtroDesde)  { $whereFilters .= " AND DATE(factura.created_at) >= ? "; $bindings[] = $filtroDesde; }
            elseif ($filtroHasta)  { $whereFilters .= " AND DATE(factura.created_at) <= ? "; $bindings[] = $filtroHasta; }

            if(Auth::user()->rol_id  == '1' || Auth::user()->rol_id  == '5' || Auth::user()->rol_id == '3' || Auth::user()->rol_id == '16'){

            $listaFacturas = DB::SELECT("
            select
            factura.id as id,
            @i := @i + 1 as contador,
            numero_factura,
            factura.cai as correlativo,
            A.cai as cai,
            fecha_emision,
            factura.nombre_cliente as nombre,
            tipo_pago_venta.descripcion,
            FORMAT(COALESCE(factura.sub_total_grabado,0),2) as gravado,
            FORMAT(COALESCE(factura.sub_total_excento,0),2) as exento,
            FORMAT(CASE WHEN factura.tipo_venta_id = 3 THEN COALESCE(factura.sub_total,0) ELSE 0 END,2) as exonerado,
            fecha_vencimiento,
            FORMAT(sub_total,2) as sub_total,
            FORMAT(isv,2) as isv,
            FORMAT(total,2) as total,
            factura.credito,
            users.name as vendedor,
            (select name from users where id = factura.users_id) as facturador,
            (select if(sum(monto) is null,0,sum(monto)) from pago_venta where estado_venta_id = 1   and factura_id = factura.id ) as monto_pagado,
            factura.estado_venta_id,
            factura.created_at as fecha_registro

        from factura
            inner join cliente
            on factura.cliente_id = cliente.id
            inner join tipo_pago_venta
            on factura.tipo_pago_id = tipo_pago_venta.id
            inner join users
            on factura.vendedor = users.id
            inner join cai A
            on factura.cai_id= A.id
            cross join (select @i := 0) r
        where ( YEAR(factura.created_at) >= (YEAR(NOW())-2) )and factura.estado_factura_id=1 and factura.estado_venta_id<>2 and (factura.tipo_venta_id = 1) {$whereFilters}
        order by factura.created_at desc
            ", $bindings);

            }else{

                $listaFacturas = DB::SELECT("
                select
                factura.id as id,
                @i := @i + 1 as contador,
                numero_factura,
                factura.cai as correlativo,
                A.cai as cai,
                fecha_emision,
                factura.nombre_cliente as nombre,
                tipo_pago_venta.descripcion,
                FORMAT(COALESCE(factura.sub_total_grabado,0),2) as gravado,
                FORMAT(COALESCE(factura.sub_total_excento,0),2) as exento,
                FORMAT(CASE WHEN factura.tipo_venta_id = 3 THEN COALESCE(factura.sub_total,0) ELSE 0 END,2) as exonerado,
                fecha_vencimiento,
                FORMAT(sub_total,2) as sub_total,
                FORMAT(isv,2) as isv,
                FORMAT(total,2) as total,
                factura.credito,
                users.name as creado_por,
                (select if(sum(monto) is null,0,sum(monto)) from pago_venta where estado_venta_id = 1   and factura_id = factura.id ) as monto_pagado,
                factura.estado_venta_id,
                factura.created_at as fecha_registro

            from factura
                inner join cliente
                on factura.cliente_id = cliente.id
                inner join tipo_pago_venta
                on factura.tipo_pago_id = tipo_pago_venta.id
                inner join users
                on factura.vendedor = users.id
                inner join cai A
                on factura.cai_id= A.id
                cross join (select @i := 0) r
            where ( YEAR(factura.created_at) >= (YEAR(NOW())-2) )and factura.estado_factura_id=1 and factura.estado_venta_id<>2 and (factura.tipo_venta_id = 1) and   factura.vendedor = ".Auth::user()->id."{$whereFilters} order by factura.created_at desc", $bindings);
            }




            $puedeAnular = in_array((int) Auth::user()->rol_id, self::ADMIN_ROLES, true);

            return Datatables::of($listaFacturas)
            ->addColumn('opciones', function ($listaFacturas) use ($puedeAnular) {

                $opcionAnular = $puedeAnular
                    ? '<li><a class="dropdown-item" onclick="anularVentaConfirmar('.$listaFacturas->id.')" > <i class="fa-solid fa-ban text-danger"></i> Anular Factura </a></li>'
                    : '';


                    return

                    '<div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                            más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; top: 33px; left: 0px; will-change: top, left;">

                            <li>
                                <a class="dropdown-item" href="/detalle/venta/'.$listaFacturas->id.'" > <i class="fa-solid fa-arrows-to-eye text-info"></i> Detalle de venta </a>
                            </li>

                            <li>
                            <a class="dropdown-item" target="_blank"  href="/factura/cooporativo/'.$listaFacturas->id.'"> <i class="fa-solid fa-print text-info"></i> Imprimir Factura Original</a>
                            </li>

                            <li>
                            <a class="dropdown-item" target="_blank"  href="/factura/cooporativoCopia/'.$listaFacturas->id.'"> <i class="fa-solid fa-print text-info"></i> Imprimir Factura Copia </a>
                            </li>


                            <li>
                            <a class="dropdown-item" target="_blank"  href="/facturaCoor/actaRec/'.$listaFacturas->id.'"> <i class="fa-solid fa-print text-info"></i> Imprimir Acta de Recepción </a>
                            </li>


                            <li>
                                <a class="dropdown-item" href="/crear/vale/'.$listaFacturas->id.'" > <i class="fa-solid fa-calendar-days text-success"></i> Agendar Entrega </a>
                            </li>

                            '.$opcionAnular.'


                        </ul>
                    </div>';

            })
            ->addColumn('estado_cobro', function ($listaFacturas) {




                $revision = DB::SELECTONE("
                    SELECT IF(COUNT(*), aplicacion_pagos.saldo, -1) AS 'cerrado'
                    from aplicacion_pagos
                    where aplicacion_pagos.estado = 1 and aplicacion_pagos.factura_id =
                    ".$listaFacturas->id);


                    if( $revision->cerrado == 0){

                        return
                        '
                        <p class="text-center" ><span class="badge badge-primary p-2" style="font-size:0.75rem">Cerrada</span></p>
                        ';

                    }else{
                        return
                        '
                        <p class="text-center"><span class="badge badge-danger p-2" style="font-size:0.75rem">Pendiente</span></p>
                        ';
                    }
           })
            ->rawColumns(['opciones','estado_cobro'])
            ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar las compras.',
                'errorTh' => $e,
            ], 402);

        }

    }

    public function exportarExcelUnificado()
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        try {
            $tipo          = trim(request()->input('tipo', 'todos'));
            $filtroCai     = trim(request()->input('filtroCai', ''));
            $filtroCliente = trim(request()->input('filtroCliente', ''));
            $filtroVendedor= trim(request()->input('filtroVendedor', ''));
            $filtroDesde   = trim(request()->input('filtroDesde', ''));
            $filtroHasta   = trim(request()->input('filtroHasta', ''));
            $downloadToken = (string) request()->input('download_token', '');

            $tipoVentaMap  = ['corporativo' => [1], 'estatal' => [2], 'exonerado' => [3], 'todos' => [1, 2, 3]];
            $tipoVentaIds  = $tipoVentaMap[$tipo] ?? [1, 2, 3];

            $tipoLabels    = ['corporativo' => 'Clientes B', 'estatal' => 'Clientes A', 'exonerado' => 'Exoneradas', 'todos' => 'Todas'];
            $tipoLabel     = $tipoLabels[$tipo] ?? 'Todas';

            $adminRoles = [1, 3, 5, 16];
            $esAdmin    = in_array((int) Auth::user()->rol_id, $adminRoles);

            $query = DB::table('factura as f')
                ->join('cliente as c',          'c.id',   '=', 'f.cliente_id')
                ->join('tipo_pago_venta as tpv', 'tpv.id', '=', 'f.tipo_pago_id')
                ->join('users as u',             'u.id',   '=', 'f.vendedor')
                ->join('cai as A',               'A.id',   '=', 'f.cai_id')
                ->leftJoin('tipo_venta as tv',   'tv.id',  '=', 'f.tipo_venta_id')
                ->select([
                    'f.id',
                    'f.tipo_venta_id',
                    DB::raw('tv.descripcion                                                     AS tipo_label'),
                    DB::raw('f.cai                                                              AS cai'),
                    'f.fecha_emision',
                    DB::raw('f.nombre_cliente                                                   AS nombre'),
                    DB::raw('tpv.descripcion                                                    AS descripcion'),
                    DB::raw('FORMAT(COALESCE(f.sub_total_grabado,0),2)                          AS gravado'),
                    DB::raw('FORMAT(COALESCE(f.sub_total_excento,0),2)                          AS exento'),
                    DB::raw('FORMAT(CASE WHEN f.tipo_venta_id=3 THEN COALESCE(f.sub_total,0) ELSE 0 END,2) AS exonerado'),
                    DB::raw('FORMAT(f.sub_total,2)                                              AS sub_total'),
                    DB::raw('FORMAT(f.isv,2)                                                    AS isv'),
                    DB::raw('FORMAT(f.total,2)                                                  AS total'),
                    'f.credito',
                    DB::raw('u.name                                                             AS vendedor'),
                    DB::raw('COALESCE((SELECT ap.saldo FROM aplicacion_pagos ap WHERE ap.estado=1 AND ap.factura_id=f.id LIMIT 1), -1) AS saldo_cobro'),
                ])
                ->where('f.fecha_emision', '>=', DB::raw("DATE_SUB(CURDATE(), INTERVAL 2 YEAR)"))
                ->where('f.estado_venta_id',  '<>', 2)
                ->whereIn('f.tipo_venta_id', $tipoVentaIds);

            if (!$esAdmin) { $query->where('f.vendedor', Auth::id()); }
            if ($filtroCai)     { $query->where('f.cai',           'LIKE', "%{$filtroCai}%"); }
            if ($filtroCliente) { $query->where('f.nombre_cliente','LIKE', "%{$filtroCliente}%"); }
            if ($filtroVendedor){ $query->where('u.name',          'LIKE', "%{$filtroVendedor}%"); }
            if ($filtroDesde && $filtroHasta) {
                $query->whereBetween('f.fecha_emision', [$filtroDesde, $filtroHasta]);
            } elseif ($filtroDesde) {
                $query->where('f.fecha_emision', '>=', $filtroDesde);
            } elseif ($filtroHasta) {
                $query->where('f.fecha_emision', '<=', $filtroHasta);
            }

            $data = $query->orderBy('f.created_at', 'desc')->get()->map(function ($row) {
                $row->estado_cobro_raw = $row->saldo_cobro == 0 ? 'Cerrada' : 'Pendiente';
                return (array) $row;
            })->all();

            $fileName = 'Facturas_' . $tipo . '_' . now()->format('Y-m-d') . '.xlsx';
            $response = Excel::download(
                new FacturasUnificadasExport($data, $tipoLabel, $filtroDesde, $filtroHasta),
                $fileName
            );

            if ($downloadToken !== '') {
                setcookie('fu_excel_download_token', $downloadToken, time() + 300, '/', '', false, false);
            }

            return $response;

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al generar el Excel.', 'errorTh' => $e->getMessage()], 500);
        }
    }

    public function listarTodasFacturas()
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        try {
            $filtroCai        = trim(request()->input('filtroCai', ''));
            $filtroCliente    = trim(request()->input('filtroCliente', ''));
            $filtroVendedor   = trim(request()->input('filtroVendedor', ''));
            $filtroDesde      = trim(request()->input('filtroDesde', ''));
            $filtroHasta      = trim(request()->input('filtroHasta', ''));

            $adminRoles = [1, 3, 5, 16];
            $esAdmin    = in_array((int) Auth::user()->rol_id, $adminRoles);

            $query = DB::table('factura as f')
                ->join('cliente as c',          'c.id',   '=', 'f.cliente_id')
                ->join('tipo_pago_venta as tpv', 'tpv.id', '=', 'f.tipo_pago_id')
                ->join('users as u',             'u.id',   '=', 'f.vendedor')
                ->join('cai as A',               'A.id',   '=', 'f.cai_id')
                ->leftJoin('tipo_venta as tv',   'tv.id',  '=', 'f.tipo_venta_id')
                ->select([
                    'f.id',
                    'f.tipo_venta_id',
                    DB::raw('tv.descripcion                                                          AS tipo_label'),
                    DB::raw('f.cai                                                                   AS cai'),
                    'f.fecha_emision',
                    DB::raw('f.nombre_cliente                                                        AS nombre'),
                    DB::raw('tpv.descripcion                                                         AS descripcion'),
                    DB::raw('FORMAT(COALESCE(f.sub_total_grabado,0),2)                               AS gravado'),
                    DB::raw('FORMAT(COALESCE(f.sub_total_excento,0),2)                               AS exento'),
                    DB::raw('FORMAT(CASE WHEN f.tipo_venta_id=3 THEN COALESCE(f.sub_total,0) ELSE 0 END,2) AS exonerado'),
                    DB::raw('FORMAT(f.sub_total,2)                                                   AS sub_total'),
                    DB::raw('FORMAT(f.isv,2)                                                         AS isv'),
                    DB::raw('FORMAT(f.total,2)                                                       AS total'),
                    'f.credito',
                    DB::raw('u.name                                                                  AS vendedor'),
                    DB::raw('(SELECT name FROM users u2 WHERE u2.id = f.users_id LIMIT 1)            AS facturador'),
                    // Pre-calculado: evita 1 query por fila en addColumn
                    DB::raw('COALESCE((SELECT ap.saldo FROM aplicacion_pagos ap WHERE ap.estado=1 AND ap.factura_id=f.id LIMIT 1), -1) AS saldo_cobro'),
                    'f.estado_venta_id',
                    DB::raw('f.created_at                                                            AS fecha_registro'),
                ])
                ->where('f.fecha_emision', '>=', DB::raw("DATE_SUB(CURDATE(), INTERVAL 2 YEAR)"))
                ->where('f.estado_venta_id',  '<>', 2)
                ->whereIn('f.tipo_venta_id', [1, 2, 3]);

            if (!$esAdmin) {
                $query->where('f.vendedor', Auth::id());
            }

            if ($filtroCai)     { $query->where('f.cai',           'LIKE', "%{$filtroCai}%"); }
            if ($filtroCliente) { $query->where('f.nombre_cliente', 'LIKE', "%{$filtroCliente}%"); }
            if ($filtroVendedor){ $query->where('u.name',           'LIKE', "%{$filtroVendedor}%"); }
            if ($filtroDesde && $filtroHasta) {
                $query->whereBetween('f.fecha_emision', [$filtroDesde, $filtroHasta]);
            } elseif ($filtroDesde) {
                $query->where('f.fecha_emision', '>=', $filtroDesde);
            } elseif ($filtroHasta) {
                $query->where('f.fecha_emision', '<=', $filtroHasta);
            }

            return Datatables::of($query)
                ->filterColumn('cai', function ($q, $keyword) {
                    $q->where('f.cai', 'LIKE', "%{$keyword}%");
                })
                ->filterColumn('nombre', function ($q, $keyword) {
                    $q->where('f.nombre_cliente', 'LIKE', "%{$keyword}%");
                })
                ->filterColumn('descripcion', function ($q, $keyword) {
                    $q->where('tpv.descripcion', 'LIKE', "%{$keyword}%");
                })
                ->filterColumn('vendedor', function ($q, $keyword) {
                    $q->where('u.name', 'LIKE', "%{$keyword}%");
                })
                ->addColumn('opciones', function ($row) use ($esAdmin) {
                    $opcionAnular = $esAdmin
                        ? '<li><a class="dropdown-item" onclick="anularVentaConfirmar('.$row->id.')"><i class="fa-solid fa-ban text-danger"></i> Anular Factura</a></li>'
                        : '';

                    return '<div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle">Ver más</button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/detalle/venta/'.$row->id.'"><i class="fa-solid fa-arrows-to-eye text-info"></i> Detalle de venta</a></li>
                            <li><a class="dropdown-item" target="_blank" href="/factura/cooporativo/'.$row->id.'"><i class="fa-solid fa-print text-info"></i> Imprimir Factura Original</a></li>
                            <li><a class="dropdown-item" target="_blank" href="/factura/cooporativoCopia/'.$row->id.'"><i class="fa-solid fa-print text-info"></i> Imprimir Factura Copia</a></li>
                            <li><a class="dropdown-item" target="_blank" href="/facturaCoor/actaRec/'.$row->id.'"><i class="fa-solid fa-print text-info"></i> Imprimir Acta de Recepción</a></li>
                            <li><a class="dropdown-item" href="/crear/vale/'.$row->id.'"><i class="fa-solid fa-calendar-days text-success"></i> Agendar Entrega</a></li>
                            '.$opcionAnular.'
                        </ul>
                    </div>';
                })
                ->addColumn('estado_cobro', function ($row) {
                    return $row->saldo_cobro == 0
                        ? '<p class="text-center"><span class="badge badge-primary p-2" style="font-size:.75rem">Cerrada</span></p>'
                        : '<p class="text-center"><span class="badge badge-danger p-2" style="font-size:.75rem">Pendiente</span></p>';
                })
                ->rawColumns(['opciones', 'estado_cobro'])
                ->make(true);

        } catch (QueryException $e) {
            return response()->json(['message' => 'Error al listar las facturas.', 'errorTh' => $e->getMessage()], 402);
        }
    }

    public function anularVentaRegistro(Request $request){
        if (!in_array((int) Auth::user()->rol_id, self::ADMIN_ROLES, true)) {
            return response()->json([
                "text" => "No autorizado para anular facturas.",
                "icon" => "warning",
                "title" => "Acceso denegado",
            ], 403);
        }

        $arrayLog = [];
        try {
        DB::beginTransaction();


         $numeroPagos = DB::SELECTONE("select count(id) as 'numero_pagos' from pago_venta where estado_venta_id = 1 and factura_id = ".$request->idFactura);

         if($numeroPagos->numero_pagos != 0 ){
            DB::rollBack();
            return response()->json([
                "text" =>"<p  class='text-left'>Esta factura no puede ser anulada, dado que cuenta con pagos registrados, si desea anular dicha factura debe eliminar todo registro de pago.</p>",
                "icon" => "warning",
                "title" => "Advertencia!",
            ],200);
         }


         $estadoVenta = DB::SELECTONE("select estado_venta_id from factura where id =".$request->idFactura );

         if($estadoVenta->estado_venta_id == 2 ){
                DB::rollBack();
            return response()->json([
                "text" =>"<p  class='text-left'>Esta factura no puede ser anulada, dado que ha sido anulada anteriormente.</p>",
                "icon" => "warning",
                "title" => "Advertencia!",
            ],200);
         }

            $periodoConciliado = DB::selectOne(
                "SELECT COUNT(*) as total
                 FROM facturas_comision fc
                 INNER JOIN comision_periodo cp
                     ON cp.periodo = DATE_FORMAT(fc.fecha_cierre_factura, '%Y-%m-01')
                    AND cp.estado = 1
                 WHERE fc.factura_id = ?
                    AND fc.estado_id = 1",
                [$request->idFactura]
            );

            if ((int)($periodoConciliado->total ?? 0) > 0) {
                DB::rollBack();
                return response()->json([
                     "text" =>"<p  class='text-left'>No se puede anular la factura porque su comisión pertenece a un período conciliado.</p>",
                     "icon" => "warning",
                     "title" => "Periodo conciliado",
                ],200);
            }




         $compra = ModelFactura::find($request->idFactura);
         $compra->estado_venta_id = 2;
         $compra->save();


         $cliente = ModelCliente::find($compra->cliente_id);
         $cliente->credito = $cliente->credito + $compra->total;
         $cliente->save();



         $logEstado = new ModelLogEstadoFactura;
         $logEstado->factura_id = $request->idFactura;
         $logEstado->estado_venta_id_anterior = $estadoVenta->estado_venta_id;
         $logEstado->users_id = Auth::user()->id;
         $logEstado->motivo = $request->motivo;
         $logEstado->save();

         $lotes = DB::SELECT("select lote,cantidad_s,numero_unidades_resta_inventario,unidad_medida_venta_id from venta_has_producto where factura_id = ".$request->idFactura);

         foreach ($lotes as $lote) {
                $recibidoBodega = ModelRecibirBodega::find($lote->lote);
                $recibidoBodega->cantidad_disponible = $recibidoBodega->cantidad_disponible + $lote->numero_unidades_resta_inventario;
                $recibidoBodega->save();

                array_push($arrayLog,[
                    'origen'=>$lote->lote,
                    'destino'=>$lote->lote,
                    'factura_id'=>$request->idFactura,
                    'cantidad'=>$lote->numero_unidades_resta_inventario,
                    "unidad_medida_venta_id"=>$lote->unidad_medida_venta_id,
                    "users_id"=> Auth::user()->id,
                    "descripcion"=>"Factura Anulada",
                    "created_at"=>now(),
                    "updated_at"=>now(),
                ]);

            };

            ModelLogTranslados::insert($arrayLog);


            DB::SELECT(
                "
                    UPDATE aplicacion_pagos
                    SET aplicacion_pagos.estado = 2
                    WHERE aplicacion_pagos.factura_id = ".$request->idFactura);

            // Revertir comisiones activas de la factura anulada
            $fcRows = DB::select(
                "SELECT fc.id, fc.rol_id, fc.tipo_comision, fc.monto_rol,
                        DATE_FORMAT(fc.fecha_cierre_factura, '%Y-%m-01') as mes_comision,
                        CASE fc.tipo_comision
                            WHEN 1 THEN f.users_id
                            WHEN 2 THEN f.users_id
                            WHEN 3 THEN f.vendedor
                            WHEN 4 THEN f.gestor_entrega
                            ELSE NULL
                        END as user_id
                 FROM facturas_comision fc
                 INNER JOIN factura f ON f.id = fc.factura_id
                 WHERE fc.factura_id = ?
                   AND fc.estado_id = 1",
                [$request->idFactura]
            );

            $fcIds = [];
            foreach ($fcRows as $fcRow) {
                $fcIds[] = (int) $fcRow->id;

                if (!empty($fcRow->user_id) && !empty($fcRow->rol_id) && !empty($fcRow->mes_comision)) {
                    DB::table('comision_empleado')
                        ->where('users_comision', (int) $fcRow->user_id)
                        ->where('rol_id', (int) $fcRow->rol_id)
                        ->where('mes_comision', (string) $fcRow->mes_comision)
                        ->where('estado_id', 1)
                        ->update([
                            'comision_acumulada' => DB::raw('GREATEST(0, comision_acumulada - ' . (float) $fcRow->monto_rol . ')'),
                            'fecha_ult_modificacion' => now(),
                            'updated_at' => now(),
                        ]);
                }
            }

            if (!empty($fcIds)) {
                DB::table('facturas_comision')
                    ->whereIn('id', $fcIds)
                    ->update([
                        'estado_id' => 2,
                        'monto_rol' => 0,
                        'retencion_mora_monto' => 0,
                        'retencion_mora_dias' => 0,
                        'updated_at' => now(),
                    ]);

                DB::table('producto_comision')
                    ->whereIn('facturas_comision_id', $fcIds)
                    ->update([
                        'estado_id' => 2,
                        'updated_at' => now(),
                    ]);
            }



         DB::commit();
        return response()->json([
            "text" =>"Factura anulada con exito",
            "icon" => "success",
            "title" => "Exito",
        ],200);
        } catch (QueryException $e) {

        DB::rollback();
        return response()->json([
            'message' => 'Ha ocurrido un error',
            'error' => $e
        ], 402);
        }

     }
}
