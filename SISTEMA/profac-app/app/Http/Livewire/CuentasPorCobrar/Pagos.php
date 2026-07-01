<?php

namespace App\Http\Livewire\CuentasPorCobrar;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
use App\Models\AplicacionPagos\Modelfactura_retencion_seguimiento;
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
    private const RETENCION_FUTURA_PENDIENTE = 'pendiente';
    private const RETENCION_FUTURA_APLICADA = 'aplicada';
    private const RETENCION_FUTURA_DESCARTADA = 'descartada';

    public function render()
    {
        return view('livewire.cuentas-por-cobrar.pagos');
    }

    private function marcarSeguimientoRetencionFutura(int $facturaId, int $aplicacionPagoId, ?string $observacion = null): void
    {
        $clienteId = (int) DB::table('factura')->where('id', $facturaId)->value('cliente_id');

        $seguimiento = Modelfactura_retencion_seguimiento::query()
            ->where('factura_id', $facturaId)
            ->first();

        if ($seguimiento && $seguimiento->estado === self::RETENCION_FUTURA_APLICADA) {
            return;
        }

        $payload = [
            'aplicacion_pagos_id'    => $aplicacionPagoId,
            'cliente_id'             => $clienteId ?: null,
            'estado'                 => self::RETENCION_FUTURA_PENDIENTE,
            'observacion_marcado'    => $observacion,
            'observacion_resolucion' => null,
            'usr_marcado'            => Auth::id(),
            'usr_resolvio'           => null,
            'fecha_marcado'          => now(),
            'fecha_resolucion'       => null,
            'numero_retencion'       => null,
            'archivo_retencion'      => null,
        ];

        if ($seguimiento) {
            $seguimiento->fill($payload)->save();
            return;
        }

        Modelfactura_retencion_seguimiento::create(array_merge($payload, [
            'factura_id' => $facturaId,
        ]));
    }

    private function resolverSeguimientoRetencionFutura(
        int $facturaId,
        int $aplicacionPagoId,
        string $estado,
        ?string $observacion = null,
        ?string $numeroRetencion = null,
        ?string $archivoRetencion = null
    ): void {
        $clienteId = (int) DB::table('factura')->where('id', $facturaId)->value('cliente_id');

        $seguimiento = Modelfactura_retencion_seguimiento::query()
            ->firstOrNew(['factura_id' => $facturaId]);

        if (!$seguimiento->exists) {
            $seguimiento->usr_marcado = Auth::id();
            $seguimiento->fecha_marcado = now();
        }

        $seguimiento->aplicacion_pagos_id = $aplicacionPagoId;
        $seguimiento->cliente_id = $clienteId ?: null;
        $seguimiento->estado = $estado;
        $seguimiento->observacion_resolucion = $observacion;
        $seguimiento->usr_resolvio = Auth::id();
        $seguimiento->fecha_resolucion = now();
        $seguimiento->numero_retencion = $numeroRetencion;
        $seguimiento->archivo_retencion = $archivoRetencion;
        $seguimiento->save();
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
                (select fecha_vencimiento
                from factura
                where id = factura_id) as  'fechaVencimiento',
                total_factura_cargo as     'cargo',
                total_notas_credito as     'notasCredito',
                total_nodas_debito as      'notasDebito',
                credito_abonos as          'abonosCargo',
                movimiento_suma as         'movSuma',
                movimiento_resta as        'movResta',
                retencion_isv_factura as   'isv',
                saldo as                   'saldo',

                -- Interés a hoy usando scalar subquery (compatible con todas las versiones MySQL)
                IF(
                    (SELECT f2.estado_venta_id FROM factura f2 WHERE f2.id = aplicacion_pagos.factura_id) = 1
                    AND aplicacion_pagos.saldo > 0
                    AND DATEDIFF(CURDATE(), (SELECT f3.fecha_vencimiento FROM factura f3 WHERE f3.id = aplicacion_pagos.factura_id)) > 0
                    AND (SELECT COUNT(*) FROM configuracion_intereses WHERE estado = 1) > 0,
                    ROUND(
                        aplicacion_pagos.saldo
                        * ((SELECT ci.tasa_mensual FROM configuracion_intereses ci WHERE ci.estado = 1 ORDER BY ci.fecha_vigencia DESC LIMIT 1) / 100.0)
                        * (DATEDIFF(CURDATE(), (SELECT f4.fecha_vencimiento FROM factura f4 WHERE f4.id = aplicacion_pagos.factura_id)) / 30.0),
                        2
                    ),
                    0.00
                ) AS 'interesMora',

                -- Total máximo a pagar = saldo + interés
                aplicacion_pagos.saldo + IF(
                    (SELECT f2.estado_venta_id FROM factura f2 WHERE f2.id = aplicacion_pagos.factura_id) = 1
                    AND aplicacion_pagos.saldo > 0
                    AND DATEDIFF(CURDATE(), (SELECT f3.fecha_vencimiento FROM factura f3 WHERE f3.id = aplicacion_pagos.factura_id)) > 0
                    AND (SELECT COUNT(*) FROM configuracion_intereses WHERE estado = 1) > 0,
                    ROUND(
                        aplicacion_pagos.saldo
                        * ((SELECT ci.tasa_mensual FROM configuracion_intereses ci WHERE ci.estado = 1 ORDER BY ci.fecha_vigencia DESC LIMIT 1) / 100.0)
                        * (DATEDIFF(CURDATE(), (SELECT f4.fecha_vencimiento FROM factura f4 WHERE f4.id = aplicacion_pagos.factura_id)) / 30.0),
                        2
                    ),
                    0.00
                ) AS 'totalPagar',

                estado_retencion_isv as    'estadoRetencion',
                retencion_aplicada as      'retencion_aplicada',
                COALESCE((select frs.estado from factura_retencion_seguimiento frs where frs.factura_id = aplicacion_pagos.factura_id limit 1), 'sin_marcar') as 'seguimientoRetencionEstado',
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

                            $seguimientoEstado = (string) ($cuenta->seguimientoRetencionEstado ?? 'sin_marcar');
                            $seguimientoLabel = $seguimientoEstado === self::RETENCION_FUTURA_PENDIENTE
                                ? '<a class="ap-ctx-item ap-ctx-dimmed"><span class="ap-ctx-icon ci-orange"><i class="fa fa-flag"></i></span>Marcada para retención futura</a>'
                                : ($seguimientoEstado === self::RETENCION_FUTURA_APLICADA
                                    ? '<a class="ap-ctx-item ap-ctx-dimmed"><span class="ap-ctx-icon ci-green"><i class="fa fa-check-circle"></i></span>Seguimiento resuelto: aplicada</a>'
                                    : ($seguimientoEstado === self::RETENCION_FUTURA_DESCARTADA
                                        ? '<a class="ap-ctx-item ap-ctx-dimmed"><span class="ap-ctx-icon ci-gray"><i class="fa fa-times-circle"></i></span>Seguimiento resuelto: no aplica</a>'
                                        : ''));

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
                                        '.$seguimientoLabel.'
                                        '.$retencionItem.'
                                        <a class="ap-ctx-item" onclick="modalNotaCredito('.$cuenta->codigoPago.',\''.$cuenta->codigoFactura.'\','.$cuenta->idFactura.','.$cuenta->tieneNC.')">
                                            <span class="ap-ctx-icon ci-green"><i class="fa fa-arrow-down"></i></span>Nota de crédito</a>
                                        <a class="ap-ctx-item" onclick="modalNotaDebito('.$cuenta->codigoPago.',\''.$cuenta->codigoFactura.'\','.$cuenta->idFactura.','.$cuenta->tieneND.')">
                                            <span class="ap-ctx-icon ci-orange"><i class="fa fa-arrow-up"></i></span>Nota de débito</a>
                                        <a class="ap-ctx-item" onclick="modalOtrosMovimientos('.$cuenta->codigoPago.',\''.$cuenta->codigoFactura.'\','.$cuenta->idFactura.','.$cuenta->saldo.')">
                                            <span class="ap-ctx-icon ci-gray"><i class="fa fa-refresh"></i></span>Otros movimientos</a>
                                        <div class="ap-ctx-divider"></div>
                                        <a class="ap-ctx-item ap-ctx-highlight" onclick="modalAbonos('.$cuenta->codigoPago.',\''.$cuenta->codigoFactura.'\','.$cuenta->idFactura.','.$cuenta->saldo.',\''.$seguimientoEstado.'\',\''.$cuenta->fechaVencimiento.'\','.$cuenta->totalPagar.')">
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
            DATE_FORMAT(ac.fecha_pago, '%Y-%m-%d') as 'fechaPago',
            DATE_FORMAT(ac.created_at, '%Y-%m-%d %H:%i:%s') as 'fechaRegistro',
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

                        $request->validate([
                            'codAplicPago'        => 'required|integer',
                            'idFacturaRetencion'  => 'required|integer',
                            'comentario_retencion'=> 'required|string|max:500',
                            'selectTiporetencion' => 'required|in:1,2',
                            'montoRetencion'      => 'required',
                            'numero_retencion'    => 'nullable|string|max:100',
                            'doc_retencion'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                        ]);

                        $comentarioRetencion = str_replace("'", "''", (string) $request->comentario_retencion);


                         $cuentas2 = DB::select("

                        CALL sp_aplicacion_pagos(
                            '4',
                            '0',
                            '".Auth::user()->id."',
                            '".$request->idFacturaRetencion."',
                            '".$comentarioRetencion."',
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

                        $archivoRetencion = null;
                        $file = $request->file('doc_retencion');
                        if ($file !== null) {
                            $folderPath = public_path('documentos_retenciones_cxc');
                            if (!File::exists($folderPath)) {
                                File::makeDirectory($folderPath, 0755, true);
                            }

                            $fileName = 'retencion_'.time().'_'.$request->codAplicPago.'.'.$file->getClientOriginalExtension();
                            $file->move($folderPath, $fileName);
                            $archivoRetencion = '/documentos_retenciones_cxc/'.$fileName;
                        }

                        $updateAplicacionPago = [
                            'updated_at' => now(),
                        ];

                        if (Schema::hasColumn('aplicacion_pagos', 'numero_retencion')) {
                            $updateAplicacionPago['numero_retencion'] = $request->numero_retencion;
                        }

                        if (Schema::hasColumn('aplicacion_pagos', 'archivo_retencion')) {
                            $updateAplicacionPago['archivo_retencion'] = $archivoRetencion;
                        }

                        DB::table('aplicacion_pagos')
                            ->where('id', $request->codAplicPago)
                            ->update($updateAplicacionPago);

                        // Si la retencion aplicada deja saldo en 0, cerrar y comisionar
                        // usando la fecha del ultimo abono registrado en la factura.
                        $apPostRetencion = DB::table('aplicacion_pagos')
                            ->where('id', (int) $request->codAplicPago)
                            ->select('factura_id', 'saldo', 'estado_cerrado')
                            ->first();

                        $retencionAplica = ((int) $request->selectTiporetencion === 2);
                        $saldoPost = (float) ($apPostRetencion->saldo ?? 0);
                        $cierraPorRetencion = false;
                        $fechaPagoComision = null;
                        $fuenteFechaComision = null;

                        if ($retencionAplica && $apPostRetencion && $saldoPost <= 0.0001) {
                            $cierraPorRetencion = true;
                            DB::table('aplicacion_pagos')
                                ->where('id', (int) $request->codAplicPago)
                                ->update(['saldo' => 0, 'updated_at' => now()]);

                            if ((int) ($apPostRetencion->estado_cerrado ?? 0) !== 2) {
                                $cierreRetencion = DB::select(" 
                                    CALL sp_aplicacion_pagos(
                                        '9',
                                        '0',
                                        '".Auth::user()->id."',
                                        '0',
                                        'CIERRE AUTOMATICO POR RETENCION',
                                        '".$request->codAplicPago."',
                                        '0',
                                        '0',
                                        @estado,
                                        @msjResultado);");

                                if (($cierreRetencion[0]->estado ?? -1) == -1) {
                                    return response()->json([
                                        "text" => "Ha ocurrido un error al cerrar automaticamente la factura por retencion.",
                                        "icon" => "error",
                                        "title"=>"Error!"
                                    ],402);
                                }

                                $apTrasCierre = DB::table('aplicacion_pagos')
                                    ->where('id', (int) $request->codAplicPago)
                                    ->select('estado_cerrado')
                                    ->first();

                                if (!$apTrasCierre || (int) $apTrasCierre->estado_cerrado !== 2) {
                                    DB::table('aplicacion_pagos')
                                        ->where('id', (int) $request->codAplicPago)
                                        ->update([
                                            'estado_cerrado'       => 2,
                                            'usr_cerro'            => Auth::id(),
                                            'fecha_cierre_factura' => now(),
                                            'ultimo_usr_actualizo' => Auth::id(),
                                            'updated_at'           => now(),
                                        ]);
                                }
                            }

                            $ultimoAbono = DB::table('abonos_creditos')
                                ->where('aplicacion_pagos_id', (int) $request->codAplicPago)
                                ->where('factura_id', (int) $request->idFacturaRetencion)
                                ->where('estado_abono', 1)
                                ->orderByRaw('CASE WHEN fecha_pago IS NULL THEN 1 ELSE 0 END ASC')
                                ->orderBy('fecha_pago', 'desc')
                                ->orderBy('id', 'desc')
                                ->select('fecha_pago', 'created_at')
                                ->first();

                            if ($ultimoAbono) {
                                $fechaBase = $ultimoAbono->fecha_pago ?: $ultimoAbono->created_at;
                                if (!empty($fechaBase)) {
                                    $fechaPagoComision = Carbon::parse($fechaBase)->toDateString();
                                    $fuenteFechaComision = !empty($ultimoAbono->fecha_pago)
                                        ? 'ultimo_abono_fecha_pago'
                                        : 'ultimo_abono_created_at';
                                }
                            } else {
                                $fuenteFechaComision = 'sin_abonos_previos';
                            }

                            $generador = app(GeneradorFacturasComision::class);
                            $arrayfacturas_comision = $generador->generar(
                                (int) $request->idFacturaRetencion,
                                (int) $request->codAplicPago,
                                $fechaPagoComision
                            );

                            if (!empty($arrayfacturas_comision)) {
                                $arrayfacturas_comision = app(AplicadorRetencionesMora::class)
                                    ->aplicar($arrayfacturas_comision, (int) $request->idFacturaRetencion, $fechaPagoComision);
                                $procesador = app(ProcesadorComisiones::class);
                                foreach ($arrayfacturas_comision as $factura) {
                                    $procesador->procesar($factura);
                                }
                            }
                        }

                        $this->resolverSeguimientoRetencionFutura(
                            (int) $request->idFacturaRetencion,
                            (int) $request->codAplicPago,
                            ((int) $request->selectTiporetencion === 2)
                                ? self::RETENCION_FUTURA_APLICADA
                                : self::RETENCION_FUTURA_DESCARTADA,
                            (string) $request->comentario_retencion,
                            $request->numero_retencion,
                            $archivoRetencion
                        );

                        return response()->json([
                            "icon" => "success",
                            "text" => "Retención gestionada correctamente.",
                            "title"=>"Exito!",
                            "trazabilidad" => [
                                "cierra_por_retencion" => $cierraPorRetencion,
                                "fecha_comision_usada" => $fechaPagoComision,
                                "fuente_fecha_comision" => $fuenteFechaComision,
                            ]
                        ],200);

            }catch (QueryException $e) {
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error: ".$e,
                "title"=>"Error!",
                "error" => $e
            ],402);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                "icon"  => "warning",
                "title" => "Datos incompletos",
                "text"  => collect($e->errors())->flatten()->first() ?? 'Revise los datos de retención.'
            ], 422);
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

    private function obtenerCategoriaPrecioMasBaja(int $clienteCategoriaEscalaId): ?int
    {
        if ($clienteCategoriaEscalaId <= 0) {
            return null;
        }

        $categoriaId = DB::table('categoria_precios')
            ->where('cliente_categoria_escala_id', $clienteCategoriaEscalaId)
            ->where('estado_id', 1)
            ->orderByRaw('CASE WHEN porc_precio_a IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('porc_precio_a', 'asc')
            ->orderBy('id', 'asc')
            ->value('id');

        return $categoriaId ? (int) $categoriaId : null;
    }

    /**
     * Previsualiza qué roles recibirían comisión si este pago cierra la factura.
     * No modifica ningún dato — solo lectura.
     */
    public function previewComisionesFactura(Request $request)
    {
        $facturaId        = (int) $request->input('factura_id');
        $montoAbono       = (float) $request->input('monto_abono', 0);
        $aplicacionPagoId = (int) $request->input('aplicacion_pagos_id');

        $respBase = [
            'cerrara'             => false,
            'ya_comisionada'      => false,
            'targets'             => [],
            'sr_forzado'          => false,
            'sr_tipo_factura'     => null,
            'sr_categoria_baja'   => null,
            'sr_productos'        => [],
            'sr_porcentajes'      => [],
        ];

        // Si la factura ya tiene comisiones activas, no habrá nuevas comisiones.
        // Si solo tiene comisiones inactivas (revertidas), sí puede recalcular.
        if (DB::table('facturas_comision')->where('factura_id', $facturaId)->where('estado_id', 1)->exists()) {
            $respBase['ya_comisionada'] = true;
            return response()->json($respBase);
        }

        // Verificar si el monto abonado cierra la factura (saldo queda en 0)
        $saldo = (float) DB::table('aplicacion_pagos')->where('id', $aplicacionPagoId)->value('saldo');
        if ($saldo <= 0 || $montoAbono < $saldo) {
            return response()->json($respBase);
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
                    ug.name     AS gestor_nombre,
                    tf.codigo   AS tipo_factura_codigo,
                    tf.nombre   AS tipo_factura_nombre,
                    cl.cliente_categoria_escala_id
             FROM factura f
             INNER JOIN users uf ON uf.id = f.users_id
             INNER JOIN users uv ON uv.id = f.vendedor
             LEFT JOIN users ug ON ug.id = f.gestor_entrega
             LEFT JOIN tipo_factura tf ON tf.id = f.tipo_factura_id
             LEFT JOIN cliente cl ON cl.id = f.cliente_id
             WHERE f.id = ?",
            [$facturaId]
        );

        if (!$fila) {
            return response()->json($respBase);
        }

        $tipoFacturaCodigo = (string) ($fila->tipo_factura_codigo ?? '');
        $clienteCategoriaEscalaId = (int) ($fila->cliente_categoria_escala_id ?? 0);
        $esFacturaSr = in_array($tipoFacturaCodigo, [
            'sin_restriccion_gobierno',
            'sin_restriccion_precio',
        ], true);

        $categoriaBajaId = null;
        $categoriaBaja = null;
        if ($esFacturaSr) {
            $categoriaBajaId = $this->obtenerCategoriaPrecioMasBaja((int) ($fila->cliente_categoria_escala_id ?? 0));
            if ($categoriaBajaId) {
                $cat = DB::table('categoria_precios')
                    ->where('id', $categoriaBajaId)
                    ->select('id', 'nombre', 'porc_precio_a')
                    ->first();
                if ($cat) {
                    $categoriaBaja = [
                        'id' => (int) $cat->id,
                        'nombre' => (string) $cat->nombre,
                        'porc_precio_a' => $cat->porc_precio_a !== null ? (float) $cat->porc_precio_a : null,
                    ];
                }
            }
        }

        $roles          = DB::table('rol')->pluck('nombre', 'id');
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
                'tiene_escala' => false,
                'porcentaje_comision' => null,
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
                    'tiene_escala' => false,
                    'porcentaje_comision' => null,
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
                'tiene_escala' => false,
                'porcentaje_comision' => null,
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
                'tiene_escala' => false,
                'porcentaje_comision' => null,
            ];
        }

        $categoriasProductoFactura = DB::table('venta_has_producto as vp')
            ->join('precios_producto_carga as ppc', 'ppc.id', '=', 'vp.precios_producto_carga_id')
            ->where('vp.factura_id', $facturaId)
            ->pluck('ppc.categoria_precios_id')
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($esFacturaSr && $categoriaBajaId) {
            $categoriasProductoFactura = [$categoriaBajaId];
        }

        foreach ($targets as &$t) {
            $qEscala = DB::table('comision_escala')
                ->where('estado_id', 1)
                ->where('rol_id', (int) $t['rol_id'])
                ->where('cliente_categoria_escala_id', $clienteCategoriaEscalaId);

            if (!empty($categoriasProductoFactura)) {
                $qEscala->whereIn('categoria_precios_id', $categoriasProductoFactura);
            }

            $escalas = $qEscala
                ->orderBy('categoria_precios_id')
                ->orderBy('id', 'desc')
                ->get(['categoria_precios_id', 'porcentaje_comision']);

            $t['tiene_escala'] = $escalas->isNotEmpty();
            $t['porcentaje_comision'] = $escalas->isNotEmpty()
                ? (float) $escalas->first()->porcentaje_comision
                : null;
        }
        unset($t);

        // Filtrar: solo roles con escala activa configurada (los desactivados ya fueron excluidos arriba)
        $targets = array_values(array_filter($targets, function ($t) {
            return $t['tiene_escala'] === true;
        }));

        $productosSr = [];
        if ($esFacturaSr) {
            $productos = DB::table('venta_has_producto as vp')
                ->leftJoin('producto as p', 'p.id', '=', 'vp.producto_id')
                ->leftJoin('precios_producto_carga as ppc', 'ppc.id', '=', 'vp.precios_producto_carga_id')
                ->leftJoin('categoria_precios as cp', 'cp.id', '=', 'ppc.categoria_precios_id')
                ->where('vp.factura_id', $facturaId)
                ->selectRaw('p.nombre as producto, cp.id as categoria_usada_id, cp.nombre as categoria_usada, vp.cantidad, vp.precio_unidad, vp.precioSeleccionado, COALESCE(NULLIF(vp.precioSeleccionado, 0), vp.precio_unidad) as precio_para_comision')
                ->get();

            foreach ($productos as $prod) {
                $productosSr[] = [
                    'producto' => (string) ($prod->producto ?? 'Producto sin nombre'),
                    'categoria_usada_id' => $prod->categoria_usada_id ? (int) $prod->categoria_usada_id : null,
                    'categoria_usada' => (string) ($prod->categoria_usada ?? 'Sin categoría'),
                    'cantidad' => (float) ($prod->cantidad ?? 0),
                    'precio_unidad' => (float) ($prod->precio_unidad ?? 0),
                    'precio_seleccionado' => (float) ($prod->precioSeleccionado ?? 0),
                    'precio_para_comision' => (float) ($prod->precio_para_comision ?? $prod->precio_unidad ?? 0),
                ];
            }
        }

        $porcentajesSr = array_map(function ($t) {
            return [
                'capacidad' => (string) $t['capacidad'],
                'rol_id' => (int) $t['rol_id'],
                'rol_nombre' => (string) $t['rol_nombre'],
                'porcentaje_comision' => $t['porcentaje_comision'] !== null ? (float) $t['porcentaje_comision'] : null,
            ];
        }, $targets);

        return response()->json([
            'cerrara'        => true,
            'ya_comisionada' => false,
            'sin_config'     => count($targets) === 0,
            'targets'        => $targets,
            'sr_forzado'     => $esFacturaSr,
            'sr_tipo_factura' => $esFacturaSr
                ? [
                    'codigo' => $tipoFacturaCodigo,
                    'nombre' => (string) ($fila->tipo_factura_nombre ?? ''),
                ]
                : null,
            'sr_categoria_baja' => $categoriaBaja,
            'sr_productos'      => $productosSr,
            'sr_porcentajes'    => $porcentajesSr,
        ]);
    }

    public function guardarCreditos( Request $request){

       try {
            $cm = "'";
            $name = '';
            $path = '';
          $comentarioAbono = str_replace("'", "''", (string) $request->comentarioAbono);

            $saldoActual = DB::selectone('select saldo from aplicacion_pagos where id = '.$request->codAplicPagoAbono);

            // ── Lógica de pagos parciales: interés primero, luego capital ──────────
            $montoTotal    = (float) $request->montoAbono;
            $montoInteres  = (float) ($request->interesMontoHidden ?? 0);
            $cobrarInteres = (string) ($request->cobrarInteresFlag ?? '1') === '1';

            // El máximo permitido es saldo + interés (cuando se cobra interés) o solo el saldo
            $maxPermitido = ($cobrarInteres && $montoInteres > 0)
                ? round((float) $saldoActual->saldo + $montoInteres, 2)
                : (float) $saldoActual->saldo;

            if ($montoTotal > $maxPermitido + 0.005) {
                return response()->json([
                    "icon"  => "warning",
                    "text"  => "No se puede registrar un monto mayor al total a cancelar (capital + interés).",
                    "title" => "Advertencia!"
                ], 400);
            }

            // Monto que realmente se aplica al capital
            $montoAplicadoCapital = $montoTotal;

            if ($cobrarInteres && $montoInteres > 0) {
                // El interés se persiste por separado — aquí calculamos cuánto va al capital
                if ($montoTotal <= $montoInteres) {
                    // El pago no alcanza a cubrir siquiera el interés: todo va a interés
                    $montoAplicadoCapital = 0;
                } else {
                    // Sobrante después de pagar el interés → va al capital
                    $montoAplicadoCapital = round($montoTotal - $montoInteres, 2);
                }
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

           // El SP recibe el monto que se aplica al capital (no el total del pago)
           $cuentas2 = DB::select("

           CALL sp_aplicacion_pagos(
               '8',
               '0',
               '".Auth::user()->id."',
               '".$request->idFacturaAbono."',
               '".$comentarioAbono."',
               '".$request->codAplicPagoAbono."',
               '0',
               '".$montoAplicadoCapital."',
               @estado, @msjResultado);");

           $cliente = DB::SELECTONE("select cliente_id from factura where id=".$request->idFacturaAbono);
           $creditoCli = DB::SELECTONE("select credito_inicial, credito, cliente_categoria_escala_id from cliente where id=".$cliente->cliente_id);

           if ($creditoCli->credito_inicial !=0) {
            // Para el crédito del cliente se usa el monto aplicado al capital
            $homologoCredito = $creditoCli->credito + $montoAplicadoCapital;

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

                $generador = app(GeneradorFacturasComision::class);
                if ($request->filled('periodo_comision_asignado')) {
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
                    $fechaMoraCalculo = $request->fecha_pago
                        ? \Carbon\Carbon::parse($request->fecha_pago)->toDateString()
                        : $fechaPagoComision;
                    $arrayfacturas_comision = app(AplicadorRetencionesMora::class)
                        ->aplicar($arrayfacturas_comision, (int) $request->idFacturaAbono, $fechaMoraCalculo);
                    $procesador = app(ProcesadorComisiones::class);
                    foreach ($arrayfacturas_comision as $factura) {
                        $procesador->procesar($factura);
                    }
                }

           }

           if ($request->boolean('requiereRetencionFutura')) {
               $this->marcarSeguimientoRetencionFutura(
                   (int) $request->idFacturaAbono,
                   (int) $request->codAplicPagoAbono,
                   $request->comentarioAbono
               );
           }

           // ── Persistir decisión de interés ──────────────────────────────────
           $montoInteresRequest = (float) ($request->interesMontoHidden ?? 0);
           if ($montoInteresRequest > 0 && !empty($request->interesConfiguracionId)) {
               $this->_persistirInteresInterno($request, $cobrarInteres, $montoAplicadoCapital);
           }

           return response()->json([
               'icon'  => 'success',
               'title' => '¡Éxito!',
               'text'  => 'El pago fue registrado correctamente.'
                        . ($cobrarInteres && $montoInteresRequest > 0
                            ? ' Interés de L. ' . number_format($montoInteresRequest, 2) . ' incluido.'
                            : ''),
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

    // ─── Persistir interés internamente durante guardarCreditos ──────────────
    private function _persistirInteresInterno($request, bool $cobrarInteres, float $montoAplicadoCapital): void
    {
        $facturaId    = (int) $request->idFacturaAbono;
        $configId     = (int) $request->interesConfiguracionId;
        $montoInteres = (float) $request->interesMontoHidden;

        if (!$configId || $montoInteres <= 0) return;

        // Evitar duplicados: si ya hay un interés cobrado activo, no persistir
        $yaCobrado = DB::table('factura_interes')
            ->where('factura_id', $facturaId)
            ->where('cobrado', 1)
            ->where('anulado', 0)
            ->exists();

        if ($cobrarInteres && $yaCobrado) return;

        try {
            \App\Models\FacturaInteres::create([
                'factura_id'               => $facturaId,
                'configuracion_interes_id' => $configId,
                'fecha_inicio'             => $request->interesVencimiento,
                'fecha_fin'                => $request->fecha_pago ?? now()->toDateString(),
                'capital_base'             => $request->interesCapitalHidden,
                'porcentaje_aplicado'      => $request->interesPorcentaje,
                'dias_vencidos'            => (int) $request->interesDiasHidden,
                'monto_interes'            => $montoInteres,
                'estado'                   => 1,
                'cobrado'                  => $cobrarInteres,
                'fecha_cobro'              => $cobrarInteres ? ($request->fecha_pago ?? now()->toDateString()) : null,
                'usuario_cobro'            => $cobrarInteres ? Auth::id() : null,
                'usr_no_cobro'             => !$cobrarInteres ? Auth::id() : null,
                'fecha_no_cobro'           => !$cobrarInteres ? now() : null,
                'motivo_no_cobro'          => !$cobrarInteres ? ($request->motivoNoCobrar ?? null) : null,
            ]);
        } catch (\Throwable $e) {
            // Silencioso — no bloquear el flujo principal del pago
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
                    $precio_venta = (float) ($producto->precioSeleccionado ?? 0) > 0
                        ? (float) $producto->precioSeleccionado
                        : (float) $producto->precio_unidad;
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
            $row->interes   = $row->interes ?? 0;
            return $row;
        }, $estadoCuenta);

        // Filtrar para mostrar únicamente movimientos con saldo > 0
        $estadoCuenta = array_filter($estadoCuenta, function ($row) {
            $saldo = $row->saldo ?? $row->Saldo ?? 0;
            return ((float) $saldo) > 0;
        });
        $estadoCuenta = array_values($estadoCuenta);

        // Recalcular acumulado = saldo + interés tras el filtrado
        $runningTotal = 0;
        foreach ($estadoCuenta as $row) {
            $runningTotal   += (float) ($row->saldo ?? 0) + (float) ($row->interes ?? 0);
            $row->acumulado  = $runningTotal;
        }

        if (empty($estadoCuenta)) {
            $nombreCliente  = DB::table('cliente')->where('id', (int) $idClientepdf)->value('nombre') ?? 'Cliente #'.$idClientepdf;
            $sinMovimientos = true;
        } else {
            $nombreCliente  = $estadoCuenta[0]->cliente;
            $sinMovimientos = false;
        }

        $pdf = PDF::loadView('/pdf/estadocuentaAplicacion', compact('estadoCuenta', 'nombreCliente', 'sinMovimientos'))
                  ->setPaper('letter')
                  ->setPaper("A4", "landscape");

        return $pdf->stream("ESTADO_CUENTA.pdf");
    }

    // ─── Consultar interés de una factura (idempotente — no persiste) ─────────
    public function consultarInteres($facturaId)
    {
        $facturaId = (int) $facturaId;

        // Usar la fecha del pago efectivo; si no se provee, usar la fecha de hoy.
        // Se evita pasar NULL al SP para no causar fallos silenciosos en PDO/MySQL.
        $fechaCalculo = request('fecha_pago')
            ? date('Y-m-d', strtotime(request('fecha_pago')))
            : date('Y-m-d');

        $resultado = DB::select("CALL sp_calcular_intereses_factura(?, ?)", [
            $facturaId,
            $fechaCalculo,
        ]);

        if (empty($resultado)) {
            return response()->json(['aplica' => false, 'monto_interes' => 0], 200);
        }

        return response()->json($resultado[0], 200);
    }

    // ─── Persistir interés al confirmar el cobro ──────────────────────────────
    public function persistirInteres(Request $request)
    {
        $request->validate([
            'factura_id'           => 'required|integer|exists:factura,id',
            'configuracion_id'     => 'required|integer|exists:configuracion_intereses,id',
            'capital_base'         => 'required|numeric|min:0',
            'porcentaje_aplicado'  => 'required|numeric|min:0',
            'dias_vencidos'        => 'required|integer|min:0',
            'monto_interes'        => 'required|numeric|min:0',
            'fecha_vencimiento'    => 'required|date',
        ]);

        $facturaId = (int) $request->factura_id;

        // Protección anti-duplicado: si ya existe un interés cobrado para este período, rechazar
        $yaCobrado = DB::table('factura_interes')
            ->where('factura_id', $facturaId)
            ->where('cobrado', 1)
            ->where('anulado', 0)
            ->exists();

        if ($yaCobrado) {
            return response()->json([
                'icon'  => 'warning',
                'title' => 'Interés ya registrado',
                'text'  => 'Esta factura ya tiene un interés cobrado registrado. No se puede duplicar.',
            ], 409);
        }

        DB::beginTransaction();
        try {
            $interes = \App\Models\FacturaInteres::create([
                'factura_id'              => $facturaId,
                'configuracion_interes_id' => $request->configuracion_id,
                'fecha_inicio'            => $request->fecha_vencimiento,
                'fecha_fin'               => now()->toDateString(),
                'capital_base'            => $request->capital_base,
                'porcentaje_aplicado'     => $request->porcentaje_aplicado,
                'dias_vencidos'           => $request->dias_vencidos,
                'monto_interes'           => $request->monto_interes,
                'estado'                  => 1,
                'cobrado'                 => true,
                'fecha_cobro'             => now()->toDateString(),
                'usuario_cobro'           => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'icon'      => 'success',
                'title'     => 'Interés registrado',
                'text'      => 'El interés por mora fue registrado correctamente.',
                'interes_id' => $interes->id,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'No se pudo registrar el interés: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─── Registrar decisión de no cobrar interés ──────────────────────────────
    public function registrarNoCobrarInteres(Request $request)
    {
        $request->validate([
            'factura_id'           => 'required|integer|exists:factura,id',
            'configuracion_id'     => 'required|integer|exists:configuracion_intereses,id',
            'capital_base'         => 'required|numeric|min:0',
            'porcentaje_aplicado'  => 'required|numeric|min:0',
            'dias_vencidos'        => 'required|integer|min:0',
            'monto_interes'        => 'required|numeric|min:0',
            'fecha_vencimiento'    => 'required|date',
            'motivo'               => 'nullable|string|max:500',
        ]);

        $facturaId = (int) $request->factura_id;

        DB::beginTransaction();
        try {
            // Registrar la decisión como interés no cobrado para auditoría
            \App\Models\FacturaInteres::create([
                'factura_id'              => $facturaId,
                'configuracion_interes_id' => $request->configuracion_id,
                'fecha_inicio'            => $request->fecha_vencimiento,
                'fecha_fin'               => now()->toDateString(),
                'capital_base'            => $request->capital_base,
                'porcentaje_aplicado'     => $request->porcentaje_aplicado,
                'dias_vencidos'           => $request->dias_vencidos,
                'monto_interes'           => $request->monto_interes,
                'estado'                  => 1,
                'cobrado'                 => false,
                'usr_no_cobro'            => Auth::id(),
                'fecha_no_cobro'          => now(),
                'motivo_no_cobro'         => $request->motivo,
            ]);

            DB::commit();

            return response()->json([
                'icon'  => 'success',
                'title' => 'Registrado',
                'text'  => 'Decisión de no cobrar interés registrada para auditoría.',
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'Error al registrar la decisión: ' . $e->getMessage(),
            ], 500);
        }
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
                             AND fc.aplicacion_pagos_id = ?
                             AND fc.estado_id  = 1",
                        [$abono->factura_id, $abono->aplicacion_pagos_id]
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
                                 WHERE fc.factura_id = ?
                                     AND fc.aplicacion_pagos_id = ?
                                     AND fc.estado_id = 1",
                                [$factura_id, $apId]
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
                    ->update([
                        'estado_id' => 2,
                        'monto_rol' => 0,
                        'retencion_mora_monto' => 0,
                        'retencion_mora_dias' => 0,
                        'updated_at' => now(),
                    ]);

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

    public function listarHistoricoRetenciones($id)
    {
        try {
            $consulta = DB::table('factura_retencion_seguimiento as frs')
                ->join('factura as f', 'f.id', '=', 'frs.factura_id')
                ->join('cliente as c', 'c.id', '=', 'frs.cliente_id')
                ->leftJoin('users as um', 'um.id', '=', 'frs.usr_marcado')
                ->leftJoin('users as ur', 'ur.id', '=', 'frs.usr_resolvio')
                ->where('frs.cliente_id', $id)
                ->orderByDesc('frs.fecha_marcado')
                ->selectRaw("frs.id as codigoSeguimiento, frs.aplicacion_pagos_id as codigoPago, frs.factura_id as idFactura, f.cai as correlativo, c.nombre as cliente, frs.estado, DATE_FORMAT(frs.fecha_marcado, '%Y-%m-%d %H:%i:%s') as fechaMarcado, DATE_FORMAT(frs.fecha_resolucion, '%Y-%m-%d %H:%i:%s') as fechaResolucion, frs.observacion_marcado, frs.observacion_resolucion, frs.numero_retencion as numeroRetencion, frs.archivo_retencion as archivoRetencion, um.name as usuarioMarcado, ur.name as usuarioResolvio")
                ->get();

            return Datatables::of($consulta)
                ->addColumn('estadoEtiqueta', function ($consulta) {
                    if ($consulta->estado === self::RETENCION_FUTURA_PENDIENTE) {
                        return '<span class="badge badge-warning">PENDIENTE</span>';
                    }

                    if ($consulta->estado === self::RETENCION_FUTURA_APLICADA) {
                        return '<span class="badge badge-success">APLICADA</span>';
                    }

                    if ($consulta->estado === self::RETENCION_FUTURA_DESCARTADA) {
                        return '<span class="badge badge-secondary">NO APLICA</span>';
                    }

                    return '<span class="badge badge-light">SIN ESTADO</span>';
                })
                ->addColumn('archivoEtiqueta', function ($consulta) {
                    if (empty($consulta->archivoRetencion)) {
                        return '<span class="badge badge-light">Sin archivo</span>';
                    }

                    return '<a class="badge badge-info" href="' . e($consulta->archivoRetencion) . '" target="_blank" rel="noopener">Ver archivo</a>';
                })
                ->rawColumns(['estadoEtiqueta', 'archivoEtiqueta'])
                ->make(true);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar el histórico de retenciones.',
                'errorTh' => $e,
            ], 402);
        }
    }




    }
