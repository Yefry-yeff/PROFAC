<?php

namespace App\Http\Livewire\CuentasPorCobrar;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use DataTables;
use Throwable;
use PDF;
use Carbon\Carbon;

use Illuminate\Support\Facades\File;

use App\Models\ModelCliente;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CuentasPorCobrarExport;
use App\Exports\CuentasPorCobrarInteresExport;
use App\Models\AplicacionPagos\Modelotros_movimientos;
use App\Models\AplicacionPagos\Modelabonos_creditos;
use App\Models\AplicacionPagos\ModelComisionReversionLog;
use App\Models\Comisiones\Escalado\modelproducto_comision;
use App\Models\Comisiones\Escalado\modelfacturas_comision;
use App\Models\Comisiones\Escalado\modelcomision_empleado;
use App\Models\Comisiones\Escalado\modelcomision_escala;
use App\Services\Comisiones\ProcesadorComisiones;
use App\Services\Comisiones\GeneradorFacturasComision;
use App\Services\Comisiones\AplicadorRetencionesMora;




class Pagos extends Component
{
    public function render()
    {
        return view('livewire.cuentas-por-cobrar.pagos');
    }




    public function listarClientes(Request $request){
        try {

         //$clientes = DB::SELECT("select id, nombre as text from cliente where estado_cliente_id = 1");//Clientes Activos
         $clientes = DB::SELECT("select id, concat(id,' - ',nombre) as text from cliente where (id LIKE '%".$request->search."%' or nombre Like '%".$request->search."%') limit 15");//Todos los Clientes

         return response()->json([
            'results'=>$clientes,
        ],200);

        } catch (QueryException $e) {
        return response()->json([
         'message' => 'Ha ocurrido un error',
         'error' => $e
        ],402);
        }
    }


    public function listarCuentasPorCobrar($id){
        try{

            // Sincroniza siempre las facturas activas del cliente en aplicacion_pagos.
            // Esto evita que una comparación por conteos deje facturas fuera del listado.
            $cuentasSync = DB::select(" 

                CALL sp_aplicacion_pagos('3','".$id."', '".Auth::user()->id."', '0','na','0','0','0', @estado, @msjResultado);");

            if ($cuentasSync[0]->estado == -1) {
                return response()->json([
                    "text" => "Ha ocurrido un error al sincronizar las facturas en aplicacion de pagos.",
                    "icon" => "error",
                    "title"=>"Error!"
                ],402);
            }

            $cuentas = DB::select("
                select
                id as                      'codigoPago',
                factura_id as              'idFactura',
                (select cai
                from factura
                where id = factura_id) as  'codigoFactura',
                total_factura_cargo as     'cargo',
                total_notas_credito as     'notasCredito',
                total_nodas_debito as      'notasDebito',
                credito_abonos as          'abonosCargo',
                movimiento_suma as         'movSuma',
                movimiento_resta as        'movResta',
                retencion_isv_factura as   'isv',
                saldo as                   'saldo',
                estado_retencion_isv as    'estadoRetencion',
                retencion_aplicada as      'retencion_aplicada',
                estado as                  'estado',
                estado_cerrado as          'estadoCierre',
                usr_cerro as               'usrCierre',
                created_at as              'fechaRegistro',
                updated_at  as             'ultimoRegistro',
                IF(
                   (
                    select
                       COUNT(*)
                    from nota_credito
                    where nota_credito.factura_id = idFactura
                    ) > 0, 1, 0
                ) as                       'tieneNC',
                IF(
                   (
                    select
                       COUNT(*)
                    from notadebito
                    where notadebito.factura_id = idFactura
                    ) > 0, 1, 0
                ) as                       'tieneND'

                from aplicacion_pagos
                where
                cliente_id = ".$id."
                and
                estado = 1 and estado_cerrado <> 2 and saldo <> 0;"
            );



        return Datatables::of($cuentas)
                ->addColumn('acciones', function ($cuenta) {

                    if (Auth::user()->rol_id == '2') {
                        return '<span class="badge badge-success">Sin Acciones</span>';
                    }else {
                        if ($cuenta->estadoCierre) {
                            return '<span class="badge badge-success">Factura cerrada</span>';
                        }else{

                            //dd($cuenta);
                            $retencionItem = ($cuenta->retencion_aplicada == 0)
                                ? '<a class="ap-ctx-item" onclick="modalRetencion('.$cuenta->codigoPago.','.$cuenta->isv.','.$cuenta->estadoRetencion.',\''.$cuenta->codigoFactura.'\','.$cuenta->idFactura.')">
                                        <span class="ap-ctx-icon ci-yellow"><i class="fa fa-percent"></i></span>Gestionar retención</a>'
                                : '<a class="ap-ctx-item ap-ctx-dimmed">
                                        <span class="ap-ctx-icon ci-green"><i class="fa fa-check"></i></span>Retención gestionada</a>';

                            $btnBase = '
                                <div class="ap-ctx-wrap">
                                    <button class="ap-actions-toggle" onclick="apCtxToggle(this)">
                                        <i class="fa fa-sliders"></i> Acciones
                                    </button>
                                    <div class="ap-ctx-menu" style="display:none;">
                                        <div class="ap-ctx-section">Factura #'.$cuenta->codigoFactura.'</div>
                                        <a class="ap-ctx-item" href="/detalle/venta/'.$cuenta->idFactura.'">
                                            <span class="ap-ctx-icon ci-blue"><i class="fa fa-eye"></i></span>Detalle de venta</a>
                                        <a class="ap-ctx-item" href="/factura/cooporativo/'.$cuenta->idFactura.'" target="_blank">
                                            <span class="ap-ctx-icon ci-red"><i class="fa fa-file-pdf-o"></i></span>Imprimir factura</a>
                                        <div class="ap-ctx-divider"></div>
                                        '.$retencionItem.'
                                        <a class="ap-ctx-item" onclick="modalNotaCredito('.$cuenta->codigoPago.',\''.$cuenta->codigoFactura.'\','.$cuenta->idFactura.','.$cuenta->tieneNC.')">
                                            <span class="ap-ctx-icon ci-green"><i class="fa fa-arrow-down"></i></span>Nota de crédito</a>
                                        <a class="ap-ctx-item" onclick="modalNotaDebito('.$cuenta->codigoPago.',\''.$cuenta->codigoFactura.'\','.$cuenta->idFactura.','.$cuenta->tieneND.')">
                                            <span class="ap-ctx-icon ci-orange"><i class="fa fa-arrow-up"></i></span>Nota de débito</a>
                                        <a class="ap-ctx-item" onclick="modalOtrosMovimientos('.$cuenta->codigoPago.',\''.$cuenta->codigoFactura.'\','.$cuenta->idFactura.','.$cuenta->saldo.')">
                                            <span class="ap-ctx-icon ci-gray"><i class="fa fa-refresh"></i></span>Otros movimientos</a>
                                        <div class="ap-ctx-divider"></div>
                                        <a class="ap-ctx-item ap-ctx-highlight" onclick="modalAbonos('.$cuenta->codigoPago.',\''.$cuenta->codigoFactura.'\','.$cuenta->idFactura.','.$cuenta->saldo.')">
                                            <span class="ap-ctx-icon ci-teal"><i class="fa fa-money"></i></span>Registrar pago</a>
                                    </div>
                                </div>';
                            return $btnBase;
                        }
                    }

                })


                ->rawColumns(['acciones'])
                ->make(true);
        } catch (QueryException $e) {


            return response()->json([
                'message' => 'Ha ocurrido un error al listar las cuentas.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function listarMovimientos($id){
        try{

            $consulta = DB::select("

            select
            ot.id as 'codigoMovimiento',
            ot.aplicacion_pagos_id as 'codigoPago',
            (select cai from factura where id = ot.factura_id) as correlativo,
            FORMAT(ot.monto, 2) as monto,
            ot.tipo_movimiento,
            ot.comentario,
            ot.estado as estadoMov,
            (select name from users where id = ot.usr_registro) as userRegistro,
            ot.created_at as fechaRegistro,
            ot.factura_id
                from otros_movimientos ot
                inner join aplicacion_pagos ap on ap.id = ot.aplicacion_pagos_id
                where
                ap.cliente_id = ".$id."
                and ap.estado = 1
                and ot.estado = 1
                ;"
            );



        return Datatables::of($consulta)
                ->addColumn('acciones', function ($consulta) {


                    return
                        '
                                <span class="badge badge-info">Sin Acciones</span>
                        ';
                })


                ->rawColumns(['acciones'])
                ->make(true);
        } catch (QueryException $e) {


            return response()->json([
                'message' => 'Ha ocurrido un error al listar las cuentas.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function listarAbonos($id){
        try{

            $consulta = DB::select("

            select
            ac.id as 'codigoAbono',
            ac.aplicacion_pagos_id as 'codigoPago',
            (select cai from factura where id = ac.factura_id) as correlativo,
            FORMAT(ac.monto_abonado, 2) as monto,
            ac.monto_abonado as monto_real,
            ac.comentario as 'comentarioabono',
            ac.estado_abono as 'estadoAbono',
            (select name from users where id = ac.usr_registro) as 'userRegistro',
            ac.created_at as 'fechaRegistro',
            ac.factura_id
                from abonos_creditos ac
                inner join aplicacion_pagos ap on ap.id = ac.aplicacion_pagos_id
                where
                ap.cliente_id = ".$id."
                and ap.estado = 1
                and ac.estado_abono = 1
                ;"
            );



        return Datatables::of($consulta)
                ->addColumn('acciones', function ($consulta) {
                    if (Auth::user()->rol_id == '2') {
                        return '<span class="badge badge-info">Sin Acciones</span>';
                    }

                    return '<button class="btn btn-sm btn-outline-danger" onclick="modalAnularAbono(' . $consulta->codigoAbono . ',\'' . $consulta->correlativo . '\')"><i class="fa fa-ban"></i> Anular pago</button>';
                })


                ->rawColumns(['acciones'])
                ->make(true);
        } catch (QueryException $e) {


            return response()->json([
                'message' => 'Ha ocurrido un error al listar las cuentas.',
                'errorTh' => $e,
            ], 402);
        }
    }

    public function listarNotasCredito($idFactura){

        try {
                $notasCredito = DB::select("
                    select
                    id as 'idNotaCredito',
                    cai as 'correlativo'
                    from nota_credito where estado_rebajado = 2 and estado_nota_id = 1 and factura_id =
                ".$idFactura);
            return response()->json([
                'results'=>$notasCredito,
            ],200);

        } catch (QueryException $e) {
           return response()->json([
            'message' => 'Ha ocurrido un error',
            'error' => $e
           ],402);
        }

    }

    public function datosNotasCredito($idNotaCredito){

        try {
                $notaCredito = DB::select("
                    select
                    comentario,
                    total AS total,
                    estado_rebajado
                    from nota_credito where id =
                ".$idNotaCredito);
            return response()->json([
                'result'=>$notaCredito,
            ],200);

        } catch (QueryException $e) {
           return response()->json([
            'message' => 'Ha ocurrido un error',
            'error' => $e
           ],402);
        }

    }

    public function listarNotasDebito($idFactura){



        try {
                    $notasDebito = DB::select("
                    select
                    id as 'idNotaDebito',
                    numeroCai as 'correlativo'
                    from notadebito where estado_sumado = 2 and  estado_id = 1 and factura_id =
                ".$idFactura);
            return response()->json([
                'results'=>$notasDebito,
            ],200);

        } catch (QueryException $e) {
           return response()->json([
            'message' => 'Ha ocurrido un error',
            'error' => $e
           ],402);
        }

    }

    public function datosNotasDebito($idNotaDebito){

        try {
                $notaDebito = DB::select("
                    select
                    motivoDescripcion AS 'comentario',
                    monto_asignado AS 'total',
                    estado_sumado
                    from notadebito where id =
                ".$idNotaDebito);
            return response()->json([
                'result'=>$notaDebito,
            ],200);

        } catch (QueryException $e) {
           return response()->json([
            'message' => 'Ha ocurrido un error',
            'error' => $e
           ],402);
        }

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////!SECTION
    ///////////////////////////////GESTIONES DE RETENCION DE ISV

    public function gestionRetencion( Request $request){

        try {


                         $cuentas2 = DB::select("

                        CALL sp_aplicacion_pagos(
                            '4',
                            '0',
                            '".Auth::user()->id."',
                            '".$request->idFacturaRetencion."',
                            '".$request->comentario_retencion."',
                            '".$request->codAplicPago."',
                            '".$request->selectTiporetencion."',
                            '".$request->montoRetencion."',
                            @estado, @msjResultado);");


                        //dd($cuentas2[0]->estado);

                        if ($cuentas2[0]->estado == -1) {
                            return response()->json([
                                "text" => "Ha ocurrido un error al insertar facturas en aplicacion de pagos.",
                                "icon" => "error",
                                "title"=>"Error!"
                            ],402);
                        }

            }catch (QueryException $e) {
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error: ".$e,
                "title"=>"Error!",
                "error" => $e
            ],402);
        }

    }

   ///////////////////////////////GESTIONES DE notas nde credito

    public function gestionNC( Request $request){

        //dd($request);

        try {


                        $cuentas2 = DB::select("

                        CALL sp_aplicacion_pagos(
                            '5',
                            '".$request->selectNotaCredito."',
                            '".Auth::user()->id."',
                            '".$request->idFacturaNC."',
                            '".$request->comentarioRebaja."',
                            '".$request->codAplicPagonc."',
                            '".$request->selectAplicado."',
                            '".$request->totalNotaCredito."',
                            @estado, @msjResultado);");


                        //dd($cuentas2[0]->estado);

                        if ($cuentas2[0]->estado == -1) {
                            return response()->json([
                                "text" => "Ha ocurrido un error.",
                                "icon" => "error",
                                "title"=>"Error!"
                            ],402);
                        }

            }catch (QueryException $e) {
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error: ".$e,
                "title"=>"Error!",
                "error" => $e
            ],402);
        }

    }



   ///////////////////////////////GESTIONES DE notas nde debito

    public function gestionND( Request $request){

       // dd($request);

        try {


                        $cuentas2 = DB::select("

                        CALL sp_aplicacion_pagos(
                            '6',
                            '".$request->selectNotaDebito."',
                            '".Auth::user()->id."',
                            '".$request->idFacturaND."',
                            '".$request->comentarioSuma."',
                            '".$request->codAplicPagond."',
                            '".$request->selectAplicadond."',
                            '".$request->totalNotaDebito."',
                            @estado, @msjResultado);");


                        //dd($cuentas2[0]->estado);

                        if ($cuentas2[0]->estado == -1) {
                            return response()->json([
                                "text" => "Ha ocurrido un error.",
                                "icon" => "error",
                                "title"=>"Error!"
                            ],402);
                        }

            }catch (QueryException $e) {
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error: ".$e,
                "title"=>"Error!",
                "error" => $e
            ],402);
        }

    }


    ///////////////////////////////GESTIONES DE OTRO MOVIMIENTO

    public function guardarOtroMov( Request $request){


        try {
            $cm = "'";

                        $otrosMovimientos = new Modelotros_movimientos;
                            $otrosMovimientos->aplicacion_pagos_id = $request->codAplicPagoom;
                            $otrosMovimientos->factura_id = $request->idFacturaom;
                            $otrosMovimientos->monto = $request->montoTM;
                            $otrosMovimientos->comentario = $request->motivoMovimiento;
                            $otrosMovimientos->usr_registro = Auth::user()->id;
                            $otrosMovimientos->estado = 1;
                            $otrosMovimientos->tipo_movimiento = $request->selecttipoMovimiento;
                        $otrosMovimientos->save();



                        $cuentas2 = DB::select("

                        CALL sp_aplicacion_pagos(
                            '7',
                            '0',
                            '".Auth::user()->id."',
                            '".$request->idFacturaom."',
                            '".$request->motivoMovimiento."',
                            '".$request->codAplicPagoom."',
                            '".$request->selecttipoMovimiento."',
                            '".$request->montoTM."',
                            @estado, @msjResultado);");


                        if ($request->selecttipoMovimiento=2) {


                            $cliente = DB::SELECTONE("select cliente_id from factura where id=".$request->idFacturaom);
                            $creditoCli = DB::SELECTONE("select credito , cliente_categoria_escala_id from cliente where id=".$cliente->cliente_id);


                            $homologoCredito = $creditoCli->credito + $request->montoAbono;

                            $clienteCredito =  ModelCliente::find($cliente->cliente_id);
                            $clienteCredito->credito = trim($homologoCredito);
                            $clienteCredito->save();

                        }

                        //dd($cuentas2[0]->estado);


                        if ($cuentas2[0]->estado == -1) {
                            return response()->json([
                                "text" => "Ha ocurrido un error en el procedimiento almacenado.",
                                "icon" => "error",
                                "title"=>"Error!"
                            ],402);
                        }

                       $saldoActual2 = DB::selectone("select saldo from aplicacion_pagos where id = ".$request->codAplicPagoom);

                      // dd($request);
                       if($saldoActual2->saldo == 0){
                            //dd("Prueba de que llega aqui esta mierda");
                           $cuentas22 = DB::select("
                               CALL sp_aplicacion_pagos(
                                   '9',
                                   '0',
                                   '".Auth::user()->id."',
                                   '0',
                                   'CIERRE POR SALDO 0',
                                   '".$request->codAplicPagoAbono."',
                                   '0',
                                   '0',
                                   @estado,
                                   @msjResultado);");



                                $generador = app(GeneradorFacturasComision::class);
                                $arrayfacturas_comision = $generador->generar(
                                    (int) $request->idFacturaom,
                                    (int) $request->codAplicPagoom
                                );

                                if (!empty($arrayfacturas_comision)) {
                                    $arrayfacturas_comision = app(AplicadorRetencionesMora::class)
                                        ->aplicar($arrayfacturas_comision, (int) $request->idFacturaom);
                                    $procesador = app(ProcesadorComisiones::class);
                                    foreach ($arrayfacturas_comision as $factura) {
                                        $procesador->procesar($factura);
                                    }
                                }


                           if ($cuentas22[0]->estado == -1) {
                               return response()->json([
                                   "text" => "Ha ocurrido un error en el procedimiento almacenado.",
                                   "icon" => "error",
                                   "title"=>"Error!"
                               ],402);
                           }

                           /* Me sale más facil procesarlo aqui */

                           /* Distribución de facturas comision */

                       }

            }catch (QueryException $e) {
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error: ".$e,
                "title"=>"Error!",
                "error" => $e
            ],402);
        }

    }

    ///////////////////////////////GESTIONES DE creditos y abonos

    /**
     * Previsualiza qué roles recibirían comisión si este pago cierra la factura.
     * No modifica ningún dato — solo lectura.
     */
    public function previewComisionesFactura(Request $request)
    {
        $facturaId        = (int) $request->input('factura_id');
        $montoAbono       = (float) $request->input('monto_abono', 0);
        $aplicacionPagoId = (int) $request->input('aplicacion_pagos_id');

        // Si la factura ya tiene comisiones activas, no habrá nuevas comisiones.
        // Si solo tiene comisiones inactivas (revertidas), sí puede recalcular.
        if (DB::table('facturas_comision')->where('factura_id', $facturaId)->where('estado_id', 1)->exists()) {
            return response()->json(['cerrara' => false, 'ya_comisionada' => true, 'targets' => []]);
        }

        // Verificar si el monto abonado cierra la factura (saldo queda en 0)
        $saldo = (float) DB::table('aplicacion_pagos')->where('id', $aplicacionPagoId)->value('saldo');
        if ($saldo <= 0 || $montoAbono < $saldo) {
            return response()->json(['cerrara' => false, 'ya_comisionada' => false, 'targets' => []]);
        }

        // Obtener facturador, vendedor y gestor de entrega (si existe) con sus roles
        $fila = DB::selectOne(
            "SELECT f.users_id AS facturador_id,
                    uf.rol_id   AS facturador_rol,
                    uf.name     AS facturador_nombre,
                    f.vendedor  AS vendedor_id,
                    uv.rol_id   AS vendedor_rol,
                    uv.name     AS vendedor_nombre,
                    f.gestor_entrega AS gestor_id,
                    ug.rol_id   AS gestor_rol,
                    ug.name     AS gestor_nombre
             FROM factura f
             INNER JOIN users uf ON uf.id = f.users_id
             INNER JOIN users uv ON uv.id = f.vendedor
             LEFT JOIN users ug ON ug.id = f.gestor_entrega
             WHERE f.id = ?",
            [$facturaId]
        );

        if (!$fila) {
            return response()->json(['cerrara' => false, 'ya_comisionada' => false, 'targets' => []]);
        }

        $roles          = DB::table('rol')->pluck('nombre', 'id');
        $rolesConEscala = DB::table('comision_escala')
            ->where('estado_id', 1)
            ->pluck('rol_id')
            ->unique()
            ->flip()
            ->all();

        // Roles desactivados en el panel de control — mismo filtro que el generador.
        // Los roles que NO aparecen en comision_rol_config se asumen habilitados.
        $rolesDesactivados = DB::table('comision_rol_config')
            ->where('calcular', 0)
            ->pluck('rol_id')
            ->flip()
            ->all();

        $targets = [];

        // Capacidad 1 — Facturador con rol fijo ROL_FACTURADOR_ID (3 = Televendedor)
        $rolFijo = GeneradorFacturasComision::ROL_FACTURADOR_ID;
        if (!isset($rolesDesactivados[$rolFijo])) {
            $targets[] = [
                'capacidad'    => 'Facturador',
                'tipo'         => 1,
                'empleado'     => $fila->facturador_nombre,
                'rol_id'       => $rolFijo,
                'rol_nombre'   => $roles[$rolFijo] ?? 'Desconocido',
                'tiene_escala' => isset($rolesConEscala[$rolFijo]),
            ];
        }

        // Capacidad 2 — Facturador en su rol real (si difiere del fijo)
        // Se omite si:
        //   a) Su rol real coincide con ROL_FACTURADOR_ID (ya cubierto por entrada 1).
        //   b) Su rol real coincide con ROL_VENDEDOR_ID: la entrada 3 (VENDEDOR) ya
        //      cubre ese rol. No importa si son personas distintas — un rol solo
        //      recibe comisión una vez por factura.
        $facturadorRol = (int) $fila->facturador_rol;

        if ($facturadorRol !== $rolFijo && $facturadorRol !== GeneradorFacturasComision::ROL_VENDEDOR_ID) {
            if (!isset($rolesDesactivados[$facturadorRol])) {
                $targets[] = [
                    'capacidad'    => 'Rol Real',
                    'tipo'         => 2,
                    'empleado'     => $fila->facturador_nombre,
                    'rol_id'       => $facturadorRol,
                    'rol_nombre'   => $roles[$facturadorRol] ?? 'Desconocido',
                    'tiene_escala' => isset($rolesConEscala[$facturadorRol]),
                ];
            }
        }

        // Capacidad 3 — Vendedor con rol fijo ROL_VENDEDOR_ID (2 = Asesor Comercial)
        $rolVendedor = GeneradorFacturasComision::ROL_VENDEDOR_ID;
        if (!isset($rolesDesactivados[$rolVendedor])) {
            $targets[] = [
                'capacidad'    => 'Vendedor',
                'tipo'         => 3,
                'empleado'     => $fila->vendedor_nombre,
                'rol_id'       => $rolVendedor,
                'rol_nombre'   => $roles[$rolVendedor] ?? 'Desconocido',
                'tiene_escala' => isset($rolesConEscala[$rolVendedor]),
            ];
        }

        // Capacidad 4 — Gestor de entrega con rol fijo ROL_GESTOR_ENTREGA_ID (16)
        $gestorId = (int) ($fila->gestor_id ?? 0);
        $rolGestor = GeneradorFacturasComision::ROL_GESTOR_ENTREGA_ID;
        if ($gestorId > 0 && !isset($rolesDesactivados[$rolGestor])) {
            $targets[] = [
                'capacidad'    => 'Gestor de Entrega',
                'tipo'         => 4,
                'empleado'     => $fila->gestor_nombre ?? 'Sin nombre',
                'rol_id'       => $rolGestor,
                'rol_nombre'   => $roles[$rolGestor] ?? 'Desconocido',
                'tiene_escala' => isset($rolesConEscala[$rolGestor]),
            ];
        }

        // Filtrar: solo roles con escala activa configurada (los desactivados ya fueron excluidos arriba)
        $targets = array_values(array_filter($targets, function ($t) {
            return $t['tiene_escala'] === true;
        }));

        return response()->json([
            'cerrara'        => true,
            'ya_comisionada' => false,
            'sin_config'     => count($targets) === 0,
            'targets'        => $targets,
        ]);
    }

    public function guardarCreditos( Request $request){

        //dd($request);

       try {
            $cm = "'";
            $name = '';
            $path = '';
          $comentarioAbono = str_replace("'", "''", (string) $request->comentarioAbono);




            $saldoActual = DB::selectone('select saldo from aplicacion_pagos where id = '.$request->codAplicPagoAbono);

            if($request->montoAbono > $saldoActual->saldo){
                return response()->json([
                    "icon" => "warning",
                    "text"=>"No se puede registrar un monto mayor al saldo actual.",
                    "title"=>"Advertencia!"

                ],400);

            }

                        $file = $request->file('doc_pago');
                        if($file != NULL){

                            $name = 'doc_'. time()."-". '.' . $file->getClientOriginalExtension();
                            $path = public_path() . '/documentos_aplicacion_pagos';
                            $file->move($path, $name);
                        }else{
                            $name = '';
                        }

                       $abonos = new Modelabonos_creditos;
                        $abonos->aplicacion_pagos_id = $request->codAplicPagoAbono;
                        $abonos->factura_id = $request->idFacturaAbono;
                        $abonos->banco_id = $request->selectBanco;
                        $abonos->estado_abono= 1;
                        $abonos->id_tipo_pago_cobro= $request->selectMetodoPago;
                        $abonos->monto_abonado = $request->montoAbono;
                        $abonos->usr_registro = Auth::user()->id;
                        $abonos->comentario = $request->comentarioAbono;
                        $abonos->url_documento = $path;
                        $abonos->fecha_pago = $request->fecha_pago;
                        $abonos->numero_recibo = $request->numero_recibo;

                        // Registro de desvío de período por conciliación
                        if ($request->filled('periodo_comision_original')) {
                            $abonos->periodo_comision_original = $request->periodo_comision_original;
                            $abonos->periodo_comision_asignado = $request->periodo_comision_asignado ?: null;
                            $abonos->desvio_confirmado_por     = Auth::id();
                        }

                       $abonos->save();

                       $cuentas2 = DB::select("

                       CALL sp_aplicacion_pagos(
                           '8',
                           '0',
                           '".Auth::user()->id."',
                           '".$request->idFacturaAbono."',
                           '".$comentarioAbono."',
                           '".$request->codAplicPagoAbono."',
                           '0',
                           '".$request->montoAbono."',
                           @estado, @msjResultado);");


                       //dd($cuentas2[0]->estado);

                       $cliente = DB::SELECTONE("select cliente_id from factura where id=".$request->idFacturaAbono);

                       $creditoCli = DB::SELECTONE("select credito_inicial, credito, cliente_categoria_escala_id from cliente where id=".$cliente->cliente_id);

                       if ($creditoCli->credito_inicial !=0) {
                        $homologoCredito = $creditoCli->credito + $request->montoAbono;

                        $clienteCredito =  ModelCliente::find($cliente->cliente_id);
                        $clienteCredito->credito = trim($homologoCredito);
                        $clienteCredito->save();
                       }


                       if ($cuentas2[0]->estado == -1) {
                           return response()->json([
                               "text" => "Ha ocurrido un error en el procedimiento almacenado.",
                               "icon" => "error",
                               "title"=>"Error!"
                           ],402);
                       }


                       $saldoActual2 = DB::selectone('select saldo from aplicacion_pagos where id = '.$request->codAplicPagoAbono);
                       $saldoDespues = (float) ($saldoActual2->saldo ?? 0);

                       if($saldoDespues <= 0.0001){
                           // Normalizar a 0 exacto para evitar residuos decimales.
                           DB::table('aplicacion_pagos')
                               ->where('id', (int) $request->codAplicPagoAbono)
                               ->update(['saldo' => 0, 'updated_at' => now()]);

                           $cuentas22 = DB::select("

                               CALL sp_aplicacion_pagos(
                                   '9',
                                   '0',
                                   '".Auth::user()->id."',
                                   '0',
                                   'CIERRE AUTOMATICO POR SALDO 0 (ABONO)',
                                   '".$request->codAplicPagoAbono."',
                                   '0',
                                   '0',
                                   @estado,
                                   @msjResultado);");

                           if ($cuentas22[0]->estado == -1) {
                               return response()->json([
                                   "text" => "Ha ocurrido un error al cerrar automaticamente la factura.",
                                   "icon" => "error",
                                   "title"=>"Error!"
                               ],402);
                           }

                           // Blindaje: si por cualquier motivo no cerró en SP, cerrar por fallback.
                           $apPostCierre = DB::table('aplicacion_pagos')
                               ->where('id', (int) $request->codAplicPagoAbono)
                               ->select('estado_cerrado')
                               ->first();

                           if (!$apPostCierre || (int) $apPostCierre->estado_cerrado !== 2) {
                               DB::table('aplicacion_pagos')
                                   ->where('id', (int) $request->codAplicPagoAbono)
                                   ->update([
                                       'estado_cerrado'       => 2,
                                       'usr_cerro'            => Auth::id(),
                                       'fecha_cierre_factura' => now(),
                                       'ultimo_usr_actualizo' => Auth::id(),
                                       'updated_at'           => now(),
                                   ]);
                           }

                                // Registrar comisiones.
                                // Si el mes del pago estaba conciliado, usar el período asignado
                                // (próximo abierto); de lo contrario usar fecha_pago normal.
                                $generador = app(GeneradorFacturasComision::class);
                                if ($request->filled('periodo_comision_asignado')) {
                                    // Usar el primer día del mes asignado como fecha de comisión
                                    $fechaPagoComision = \Carbon\Carbon::parse($request->periodo_comision_asignado)
                                        ->startOfMonth()->toDateString();
                                } else {
                                    $fechaPagoComision = $request->fecha_pago
                                        ? \Carbon\Carbon::parse($request->fecha_pago)->toDateString()
                                        : null;
                                }
                                $arrayfacturas_comision = $generador->generar(
                                    (int) $request->idFacturaAbono,
                                    (int) $request->codAplicPagoAbono,
                                    $fechaPagoComision
                                );

                                if (!empty($arrayfacturas_comision)) {
                                    $arrayfacturas_comision = app(AplicadorRetencionesMora::class)
                                        ->aplicar($arrayfacturas_comision, (int) $request->idFacturaAbono, $fechaPagoComision);
                                    $procesador = app(ProcesadorComisiones::class);
                                    foreach ($arrayfacturas_comision as $factura) {
                                        $procesador->procesar($factura);
                                    }
                                }

                       }

                       return response()->json([
                           'icon'  => 'success',
                           'title' => '¡Éxito!',
                           'text'  => 'El pago fue registrado correctamente.',
                       ]);

           }catch (QueryException $e) {

           return response()->json([
               "icon" => "error",
               "text" => "Ha ocurrido un error: ".$e,
               "title"=>"Error!",
               "error" => $e
           ],402);
       }

    }

    public function gestionComision($cliente_categoria_escala_id,$idFacturaAbono ,$codAplicPagoAbono){
        try{
            $arrayfacturas_comision = [];
            $arrayproducto_comision = [];

            $parametros_comision = DB::SELECT("select * from comision_escala where estado_id = 1 and cliente_categoria_escala_id = ". $cliente_categoria_escala_id);
            $productos_factura = DB::SELECT(" select * from venta_has_producto where factura_id = ".$idFacturaAbono);
                            /* recorriendo los parametros para comisionar de ese cliente, en esta factura */
            $monto_rol_factura = 0;
            foreach ($parametros_comision as $param) {

                // Aquí accedés a cada campo del registro
                $comision_escala_id     = $param->id;
                $rol_id                 = $param->rol_id;
                $porcentaje_comision    = $param->porcentaje_comision;

                foreach ($productos_factura as $producto) {
                    $precio_venta = $producto->precio_unidad;
                    $cantidad = $producto->cantidad;
                    $idproducto =  $producto->producto_id;
                    $precios_producto_carga_id  = $producto->precios_producto_carga_id;
                    $monto_comision = ((($porcentaje_comision/100) * $precio_venta));
                    array_push($arrayproducto_comision, [
                        "cantidad" => $cantidad,
                        "precio_venta" => $precio_venta,
                        "monto_comision" => $monto_comision,
                        "precios_producto_carga_id" => $precios_producto_carga_id,
                        "producto_id" => $idproducto,
                        "rol_id" => $rol_id,
                        "estado_id" => 1,
                        "created_at" => NOW(),
                        "updated_at" => NOW()

                    ]);
                }
                    /*Inserto todos los productos según yo */


                array_push($arrayfacturas_comision, [
                    "fecha_cierre_factura" => NOW(),
                    "monto_rol" => 0,
                    "factura_id" => $idFacturaAbono,
                    "comision_escala_id" => $comision_escala_id,
                    "aplicacion_pagos_id" => $codAplicPagoAbono,
                    "rol_id" => $rol_id,
                    "estado_id" => 1
                ]);



            }
            $totalesPorRol = [];
            foreach ($arrayproducto_comision as $p) {
                // soporta tanto arrays asociativos como objetos stdClass
                $rol = isset($p['rol_id']) ? $p['rol_id'] : (isset($p->rol_id) ? $p->rol_id : null);
                $monto = isset($p['monto_comision']) ? $p['monto_comision'] : (isset($p->monto_comision) ? $p->monto_comision : 0);

                if ($rol === null) continue;

                // forzamos a float por seguridad
                $monto = (float) $monto;

                if (!isset($totalesPorRol[$rol])) $totalesPorRol[$rol] = 0.0;
                $totalesPorRol[$rol] += $monto;
            }

                // 2) Actualizar $arrayfacturas_comision usando los totales por rol
                // (se asume que cada elemento tiene 'rol_id' y queremos setear/actualizar 'monto_rol')
            foreach ($arrayfacturas_comision as &$facturaRol) {

                $rol = $facturaRol['rol_id'];
                $totalRol = 0;

                foreach ($arrayproducto_comision as $prod) {

                    if ($prod['rol_id'] == $rol) {
                        // multiplicar monto * cantidad ANTES de sumar
                        $totalRol += ($prod['monto_comision'] * $prod['cantidad']);
                    }

                }

                // asignar el total calculado
                $facturaRol['monto_rol'] = $totalRol;
            }
            unset($facturaRol);

            modelproducto_comision::insert($arrayproducto_comision);
            modelfacturas_comision::insert($arrayfacturas_comision);

            /*recuperar factura, vendedor y teleoperacior del id factura*/

            $datos_factura = DB::SELECTONE("select user_id as 'teleoperador', vendedor from factura where id = ".$idFacturaAbono);

        }catch (QueryException $e) {

           return response()->json([
               "icon" => "error",
               "text" => "Ha ocurrido un error: ".$e,
               "title"=>"Error!",
               "error" => $e
           ],402);
       }

    }

    public function datosBanco(){
        try {
            $bancos = DB::select("
                select CONCAT(nombre, ' - ', cuenta) as banco, id as idBanco from banco;
            ");
            return response()->json([
                'result'=>$bancos,
            ],200);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al buscar bancos',
                'error' => $e
            ],402);
        }
    }

    public function cerrarFactura(Request $request){
        try {

            $revision = DB::SELECTONE("
            select aplicacion_pagos.saldo as saldo
            from aplicacion_pagos
            where aplicacion_pagos.estado <> 1
            and aplicacion_pagos.id =
            ".$request->codAplicCierre);
            if ( !is_null($revision)) {
                if ($revision->saldo > 0 ) {
                    return response()->json([
                        "text" => "No es posible cerrar la factura, Saldo del estado de cuenta, no es 0.",
                        "icon" => "error",
                        "title"=>"Error!"
                    ],402);
                }
            }


            $cuentas2 = DB::select("

            CALL sp_aplicacion_pagos(
                '9',
                '0',
                '".Auth::user()->id."',
                '0',
                '".$request->comentarioCierre."',
                '".$request->codAplicCierre."',
                '0',
                '0',
                @estado,
                @msjResultado);");


            //dd($cuentas2[0]->estado);

            if ($cuentas2[0]->estado == -1) {
                return response()->json([
                    "text" => "Ha ocurrido un error en el procedimiento almacenado.",
                    "icon" => "error",
                    "title"=>"Error!"
                ],402);
            }

            // Registrar comisiones al cierre manual de factura
            $apCierre = DB::selectone(
                "SELECT ap.factura_id, ap.saldo
                 FROM aplicacion_pagos ap
                 WHERE ap.id = " . (int) $request->codAplicCierre
            );

            if ($apCierre && $apCierre->saldo == 0) {
                $generador = app(GeneradorFacturasComision::class);
                $arrayfacturas_comision = $generador->generar(
                    (int) $apCierre->factura_id,
                    (int) $request->codAplicCierre
                );

                if (!empty($arrayfacturas_comision)) {
                    $arrayfacturas_comision = app(AplicadorRetencionesMora::class)
                        ->aplicar($arrayfacturas_comision, (int) $apCierre->factura_id);
                    $procesador = app(ProcesadorComisiones::class);
                    foreach ($arrayfacturas_comision as $factura) {
                        $procesador->procesar($factura);
                    }
                }
            }

        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error al cerrar la factura.",
                "title"=>"Error!",
                "error" => $e
            ],402);
        }
    }

    public function imprimirEstadoCuenta($idClientepdf){
        $estadoCuenta = DB::select("CALL estadoCuenta_sp('".$idClientepdf."');");

        $estadoCuenta = array_map(function ($row) {
            $row->acumulado = $row->acumulado ?? $row->Acumulado ?? 0;
            return $row;
        }, $estadoCuenta);

        if (empty($estadoCuenta)) {
            // Sin facturas pendientes para este cliente — generar PDF informativo
            $nombreCliente = DB::table('cliente')->where('id', (int) $idClientepdf)->value('nombre') ?? 'Cliente #'.$idClientepdf;
            $estadoCuenta  = [];
            $sinMovimientos = true;
        } else {
            $nombreCliente  = $estadoCuenta[0]->cliente;
            $sinMovimientos = false;
        }

        $pdf = PDF::loadView('/pdf/estadocuentaAplicacion', compact('estadoCuenta', 'nombreCliente', 'sinMovimientos'))->setPaper('letter')->setPaper("A4", "landscape");

        return $pdf->stream("ESTADO_CUENTA.pdf");
    }

    // ══════════════════════════════════════════════════════════════════
    //  ANULACIÓN DE ABONO
    // ══════════════════════════════════════════════════════════════════

    /**
     * Devuelve el impacto que tendría anular un abono (solo lectura).
     * Usado para construir la advertencia antes de confirmar.
     */
    public function impactoAnularAbono(Request $request, $abono_id = null)
    {
        $abonoId = (int) ($abono_id ?? $request->input('abono_id'));

        $abono = DB::selectOne(
            "SELECT ac.id, ac.monto_abonado, ac.factura_id, ac.aplicacion_pagos_id,
                    ac.comentario, ac.estado_abono,
                    (SELECT cai FROM factura WHERE id = ac.factura_id) AS correlativo_factura,
                    ap.estado_cerrado, ap.saldo, ap.credito_abonos,
                    (SELECT nombre FROM cliente WHERE id = ap.cliente_id) AS nombre_cliente,
                    (SELECT credito_inicial FROM cliente WHERE id = ap.cliente_id) AS credito_inicial_cliente
             FROM abonos_creditos ac
             INNER JOIN aplicacion_pagos ap ON ap.id = ac.aplicacion_pagos_id
             WHERE ac.id = ?",
            [$abonoId]
        );

        if (!$abono) {
            return response()->json(['error' => 'Abono no encontrado.'], 404);
        }

        if ($abono->estado_abono != 1) {
            return response()->json(['error' => 'Este abono ya fue anulado.'], 422);
        }

        $estabasCerrada  = ((int) $abono->estado_cerrado === 2);
        $comisiones      = [];

        $rows = DB::select(
            "SELECT fc.id AS facturas_comision_id,
                    fc.rol_id,
                    fc.tipo_comision,
                    fc.monto_rol,
                    fc.aplicacion_pagos_id AS ap_id_comision,
                    u.id   AS usuario_id,
                    u.name AS usuario_nombre,
                    r.nombre AS rol_nombre,
                    ce.id  AS comision_empleado_id,
                    ce.mes_comision,
                    ce.comision_acumulada
             FROM facturas_comision fc
             INNER JOIN users u ON (
                 CASE fc.tipo_comision
                     WHEN 1 THEN u.id = (SELECT users_id FROM factura WHERE id = fc.factura_id)
                     WHEN 2 THEN u.id = (SELECT users_id FROM factura WHERE id = fc.factura_id)
                     WHEN 3 THEN u.id = (SELECT vendedor FROM factura WHERE id = fc.factura_id)
                     WHEN 4 THEN u.id = (SELECT gestor_entrega FROM factura WHERE id = fc.factura_id)
                     ELSE 1=0
                 END
             )
             LEFT JOIN rol r ON r.id = fc.rol_id
             LEFT JOIN comision_empleado ce
                 ON ce.users_comision = u.id
                 AND ce.rol_id        = fc.rol_id
                 AND ce.estado_id     = 1
                 AND ce.mes_comision  = DATE_FORMAT(fc.fecha_cierre_factura, '%Y-%m-01')
             WHERE fc.factura_id = ?
               AND fc.estado_id  = 1",
            [$abono->factura_id]
        );

        foreach ($rows as $row) {
            $comisiones[] = [
                'facturas_comision_id' => $row->facturas_comision_id,
                'usuario_nombre'       => $row->usuario_nombre,
                'rol_nombre'           => $row->rol_nombre ?? 'Sin rol',
                'monto_revertido'      => (float) $row->monto_rol,
                'mes_afectado'         => $row->mes_comision,
                'comision_acumulada'   => (float) $row->comision_acumulada,
            ];
        }

        $saldoResultante = (float) $abono->saldo + (float) $abono->monto_abonado;

        return response()->json([
            'abono_id'             => $abonoId,
            'correlativo_factura'  => $abono->correlativo_factura,
            'factura_id'           => $abono->factura_id,
            'aplicacion_pagos_id'  => $abono->aplicacion_pagos_id,
            'monto_abono'          => (float) $abono->monto_abonado,
            'nombre_cliente'       => $abono->nombre_cliente,
            'factura_estaba_cerrada' => $estabasCerrada,
            'saldo_resultante'     => $saldoResultante,
            'tiene_comisiones'     => !empty($comisiones),
            'comisiones'           => $comisiones,
            'restaura_credito'     => ((float) $abono->credito_inicial_cliente > 0),
        ]);
    }

    /**
     * Ejecuta la anulación completa del abono en una transacción única.
     */
    public function anularAbono(Request $request)
    {
        $abonoId = (int) $request->input('abono_id');
        $motivo  = trim($request->input('motivo', ''));

        if ($motivo === '') {
            return response()->json([
                'icon'  => 'warning',
                'title' => 'Advertencia',
                'text'  => 'Debe ingresar un motivo para anular el pago.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Cargar abono y verificar que pueda anularse
            $abono = DB::selectOne(
                'SELECT ac.*, ap.estado_cerrado, ap.saldo, ap.credito_abonos,
                        ap.cliente_id, f.total AS total_factura
                 FROM abonos_creditos ac
                 INNER JOIN aplicacion_pagos ap ON ap.id = ac.aplicacion_pagos_id
                 INNER JOIN factura f            ON f.id  = ac.factura_id
                 WHERE ac.id = ? FOR UPDATE',
                [$abonoId]
            );

            if (!$abono) {
                DB::rollBack();
                return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Abono no encontrado.'], 404);
            }

            if ((int) $abono->estado_abono !== 1) {
                DB::rollBack();
                return response()->json(['icon' => 'warning', 'title' => 'Advertencia', 'text' => 'Este abono ya fue anulado anteriormente.'], 422);
            }

            $monto           = (float) $abono->monto_abonado;
            $factura_id      = (int)   $abono->factura_id;
            $apId            = (int)   $abono->aplicacion_pagos_id;
            $clienteId       = (int)   $abono->cliente_id;
            $estabasCerrada  = ((int)  $abono->estado_cerrado === 2);

            // 2. Marcar abono como anulado
            DB::table('abonos_creditos')
                ->where('id', $abonoId)
                ->update(['estado_abono' => 0, 'updated_at' => now()]);

            // 3. Revertir saldo en aplicacion_pagos
            DB::table('aplicacion_pagos')
                ->where('id', $apId)
                ->update([
                    'saldo'               => DB::raw('saldo + ' . $monto),
                    'credito_abonos'      => DB::raw('GREATEST(0, credito_abonos - ' . $monto . ')'),
                    'ultimo_usr_actualizo' => Auth::id(),
                    'updated_at'          => now(),
                ]);

            // 4. Si la factura estaba cerrada, reabrirla
            if ($estabasCerrada) {
                DB::table('aplicacion_pagos')
                    ->where('id', $apId)
                    ->update([
                        'estado_cerrado'       => 0,
                        'usr_cerro'            => 0,
                        'fecha_cierre_factura' => null,
                        'updated_at'           => now(),
                    ]);
            }

            // 5. Restaurar crédito del cliente si aplica
            $clienteData = DB::table('cliente')->where('id', $clienteId)
                ->select('credito_inicial', 'credito')->first();

            if ($clienteData && (float) $clienteData->credito_inicial > 0) {
                DB::table('cliente')
                    ->where('id', $clienteId)
                    ->update(['credito' => DB::raw('credito - ' . $monto)]);
            }

            // 6. Revertir comisiones activas de la factura (esté cerrada o no)
            $comisionesRevertidas = [];

            $filas = DB::select(
                "SELECT fc.id AS fc_id, fc.rol_id, fc.tipo_comision, fc.monto_rol,
                        u.id AS user_id, u.name AS user_nombre,
                        ce.id AS ce_id, ce.mes_comision
                 FROM facturas_comision fc
                 INNER JOIN users u ON (
                     CASE fc.tipo_comision
                         WHEN 1 THEN u.id = (SELECT users_id FROM factura WHERE id = fc.factura_id)
                         WHEN 2 THEN u.id = (SELECT users_id FROM factura WHERE id = fc.factura_id)
                         WHEN 3 THEN u.id = (SELECT vendedor FROM factura WHERE id = fc.factura_id)
                         WHEN 4 THEN u.id = (SELECT gestor_entrega FROM factura WHERE id = fc.factura_id)
                         ELSE 1=0
                     END
                 )
                 LEFT JOIN comision_empleado ce
                     ON ce.users_comision = u.id
                     AND ce.rol_id        = fc.rol_id
                     AND ce.estado_id     = 1
                     AND ce.mes_comision  = DATE_FORMAT(fc.fecha_cierre_factura, '%Y-%m-01')
                 WHERE fc.factura_id = ? AND fc.estado_id = 1",
                [$factura_id]
            );

            foreach ($filas as $fila) {
                $montoRol = (float) $fila->monto_rol;

                // Descontar de comision_empleado (nunca bajar de 0)
                if ($fila->ce_id) {
                    DB::table('comision_empleado')
                        ->where('id', $fila->ce_id)
                        ->update([
                            'comision_acumulada'     => DB::raw('GREATEST(0, comision_acumulada - ' . $montoRol . ')'),
                            'fecha_ult_modificacion' => now(),
                            'updated_at'             => now(),
                        ]);
                }

                $comisionesRevertidas[] = [
                    'facturas_comision_id' => $fila->fc_id,
                    'usuario_id'           => $fila->user_id,
                    'usuario_nombre'       => $fila->user_nombre,
                    'rol_id'               => $fila->rol_id,
                    'tipo_comision'        => $fila->tipo_comision,
                    'monto_revertido'      => $montoRol,
                    'mes_afectado'         => $fila->mes_comision,
                    'comision_empleado_id' => $fila->ce_id,
                ];
            }

            // Marcar facturas_comision como revertidas (estado_id = 2 = inactivo)
            if (!empty($comisionesRevertidas)) {
                $fcIds = array_column($comisionesRevertidas, 'facturas_comision_id');
                DB::table('facturas_comision')
                    ->whereIn('id', $fcIds)
                    ->update(['estado_id' => 2, 'updated_at' => now()]);

                // Marcar líneas de producto_comision asociadas
                DB::table('producto_comision')
                    ->whereIn('facturas_comision_id', $fcIds)
                    ->update(['estado_id' => 2, 'updated_at' => now()]);
            }

            // 7. Registrar log de reversión
            ModelComisionReversionLog::create([
                'abono_id'              => $abonoId,
                'factura_id'            => $factura_id,
                'aplicacion_pagos_id'   => $apId,
                'monto_abono_anulado'   => $monto,
                'tenia_comisiones'      => !empty($comisionesRevertidas),
                'comisiones_revertidas' => !empty($comisionesRevertidas) ? $comisionesRevertidas : null,
                'motivo'                => $motivo,
                'factura_reabierta'     => $estabasCerrada,
                'usr_anulo'             => Auth::id(),
            ]);

            DB::commit();

            $msg = 'Pago anulado correctamente.';
            if ($estabasCerrada) {
                $msg .= ' La factura ha sido reabierta.';
                if (!empty($comisionesRevertidas)) {
                    $msg .= ' Las comisiones asociadas fueron revertidas.';
                }
            }

            return response()->json([
                'icon'  => 'success',
                'title' => '¡Anulado!',
                'text'  => $msg,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'Ocurrió un error al anular el pago: ' . $e->getMessage(),
            ], 500);
        }
    }




    }
