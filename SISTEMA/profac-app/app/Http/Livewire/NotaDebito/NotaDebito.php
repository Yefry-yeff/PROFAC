<?php

namespace App\Http\Livewire\NotaDebito;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use PDF;
use App\Models\NotaDebito\montoNotaDebito;
use App\Models\NotaDebito\notaDebito as mNotaDebito;
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
use App\Http\Controllers\CAI\Notificaciones;

class NotaDebito extends Component
{
    public function render(){
        $cai_nd_existencia = (object) ['existe' => $this->caiDebitoDisponible() ? 1 : 0];
        return view('livewire.nota-debito.nota-debito-moderno', compact('cai_nd_existencia'));
    }

    public function listarFacturas(Request $request){
        try {
            $notasActivas = DB::table('notadebito')
                ->select('factura_id', DB::raw('COUNT(*) as total'))
                ->where('estado_id', 1)
                ->groupBy('factura_id');

            $listaFacturas = DB::table('factura as f')
                ->join('cliente as c', 'f.cliente_id', '=', 'c.id')
                ->join('tipo_pago_venta as tp', 'f.tipo_pago_id', '=', 'tp.id')
                ->join('users as u', 'f.vendedor', '=', 'u.id')
                ->leftJoinSub($notasActivas, 'nd', 'nd.factura_id', '=', 'f.id')
                ->select([
                    'f.id', 'f.numero_factura', 'f.cai', 'f.fecha_emision',
                    'c.nombre', 'tp.descripcion', 'f.fecha_vencimiento',
                    'f.credito', 'u.name as creado_por', 'f.pendiente_cobro as monto_pagado',
                    'f.estado_venta_id', DB::raw('COALESCE(nd.total, 0) as notas_activas'),
                    DB::raw('FORMAT(f.sub_total, 2) as sub_total'),
                    DB::raw('FORMAT(f.isv, 2) as isv'),
                    DB::raw('FORMAT(f.total, 2) as total'),
                ])
                ->when($request->filled('fecha_desde'), fn ($query) => $query->whereDate('f.fecha_emision', '>=', $request->fecha_desde))
                ->when($request->filled('fecha_hasta'), fn ($query) => $query->whereDate('f.fecha_emision', '<=', $request->fecha_hasta))
                ->when($request->filled('factura'), function ($query) use ($request) {
                    $buscar = trim($request->factura);
                    $query->where(function ($filtro) use ($buscar) {
                        $filtro->where('f.cai', 'like', "%{$buscar}%")
                            ->orWhere('f.numero_factura', 'like', "%{$buscar}%");
                    });
                })
                ->when($request->filled('cliente'), function ($query) use ($request) {
                    $query->where(function ($filtro) use ($request) {
                        $filtro->where('c.nombre', $request->cliente)
                            ->orWhere('f.nombre_cliente', $request->cliente);
                    });
                })
                ->when($request->filled('vendedor'), fn ($query) => $query->where('u.name', $request->vendedor))
                ->when($request->estado_nota === 'asignada', fn ($query) => $query->whereRaw('COALESCE(nd.total, 0) > 0'))
                ->when($request->estado_nota === 'sin_asignar', fn ($query) => $query->whereRaw('COALESCE(nd.total, 0) = 0'))
                ->when($request->estado_cobro === 'pendiente', fn ($query) => $query->where('f.pendiente_cobro', '<>', 0))
                ->when($request->estado_cobro === 'completo', fn ($query) => $query->where('f.pendiente_cobro', 0))
                ->orderByDesc('f.created_at');

            $montoDebito = DB::table('montonotadebito')->where('estado_id', 1)->first(['id', 'monto']);
            $caiDisponible = $this->caiDebitoDisponible();

            return Datatables::of($listaFacturas)
            ->filter(function ($query) use ($request) {
                $buscar = trim((string) $request->input('search.value', ''));
                if ($buscar === '') {
                    return;
                }

                $termino = "%{$buscar}%";
                $query->where(function ($filtro) use ($termino) {
                    $filtro->where('f.cai', 'like', $termino)
                        ->orWhere('f.numero_factura', 'like', $termino)
                        ->orWhere('f.fecha_emision', 'like', $termino)
                        ->orWhere('c.nombre', 'like', $termino)
                        ->orWhere('tp.descripcion', 'like', $termino)
                        ->orWhere('f.fecha_vencimiento', 'like', $termino)
                        ->orWhere('f.sub_total', 'like', $termino)
                        ->orWhere('f.isv', 'like', $termino)
                        ->orWhere('f.total', 'like', $termino)
                        ->orWhere('u.name', 'like', $termino);
                });
            })
            ->addColumn('opciones', function ($factura) use ($montoDebito, $caiDisponible) {
                if ((int) $factura->notas_activas === 0) {
                    if (!$caiDisponible) {
                        return '<button class="btn btn-sm btn-secondary" disabled title="Configure un CAI vigente">CAI no disponible</button>';
                    }

                    if (!$montoDebito) {
                        return '<button class="btn btn-sm btn-secondary" disabled title="Configure un monto activo">Sin monto activo</button>';
                    }

                    return '<button type="button" class="btn btn-sm btn-warning" onclick="llenadoModalDebito('
                        . $factura->id . ', ' . (float) $montoDebito->monto . ', ' . $montoDebito->id
                        . ')"><i class="fa fa-plus-circle mr-1"></i>Asignar</button>';
                }

                return '<a class="btn btn-sm btn-outline-warning" href="/debito/imprimir/' . $factura->id
                    . '" target="_blank"><i class="fa fa-file-pdf-o mr-1"></i>Imprimir</a>';
            })
            ->addColumn('estado_cobro', function ($factura) {
                return (float) $factura->monto_pagado !== 0.0
                    ? '<span class="badge badge-danger p-2">Pendiente</span>'
                    : '<span class="badge badge-success p-2">Completo</span>';
            })
            ->addColumn('estado_ndebito', function ($factura) {
                return (int) $factura->notas_activas === 0
                    ? '<span class="badge badge-warning p-2">Sin asignar</span>'
                    : '<span class="badge badge-success p-2">Asignada</span>';
            })
            ->rawColumns(['opciones','estado_cobro','estado_ndebito'])
            ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar las compras.',
                'errorTh' => $e,
            ], 402);

        }

    }

    public function listarMontos(){
        try {

            $listaMontos = DB::SELECT("
                select
                id,
                monto,
                (select name from users where id = montonotadebito.users_registra_id) as 'user',
                created_at
                from montonotadebito
                where estado_id = 1
            ");

            return Datatables::of($listaMontos)
            ->addColumn('estado_monto', function ($listaMontos) {
                $ESTADOmONTO = DB::SELECTONE("select estado_id from montonotadebito where id = ".$listaMontos->id);
                if( $ESTADOmONTO->estado_id == 1){

                    return
                    '
                    <p class="text-center" ><span class="badge badge-primary p-2" style="font-size:0.75rem">Activo</span></p>
                    ';

                }

           })
            ->rawColumns(['estado_monto'])
            ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar los montos.',
                'errorTh' => $e,
            ], 402);

        }
    }

    public function guardarMonto(Request $request){
        try {
            DB::beginTransaction();

                DB::update('
                update
                montonotadebito
                set estado_id = 2');

                $montoNotaDebito = new montoNotaDebito;
                $montoNotaDebito->monto = $request->monto;
                $montoNotaDebito->descripcion = $request->descripcion;
                $montoNotaDebito->estado_id = 1;
                $montoNotaDebito->users_registra_id = Auth::user()->id;
                $montoNotaDebito->save();

            DB::commit();
            return response()->json([
                "icon" => "success",
                "text" => "Registro de monto de débito con éxito!",
                "title"=>"Exito!"
            ],200);

        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error al registrar el débito.",
                "title"=>"Error!",
                "error" => $e
            ],402);
        }
    }

    public function guardarNotaDebito(Request $request){

       try {
        $estadoCuenta = DB::selectone('select estado_cerrado from aplicacion_pagos where estado = 1 and factura_id = '.$request->factura_id);
        if($estadoCuenta != null){
            if($estadoCuenta->estado_cerrado == 2){
                return response()->json([
                    "icon" => "warning",
                    "text"=>"Esta factura esta cerrada, no se puede crear nota.",
                    "title"=>"Advertencia!"

                ],402);

            }

         }

            // dd($request);

        $facturaClienteId = DB::SELECTONE("select cliente_id from factura where id = ". $request->factura_id);
            //tipo_cliente 1 = B y 2 = A
        $tipoCliente = DB::SELECTONE("select tipo_cliente_id from cliente where id = ". $facturaClienteId->cliente_id);

            //tipo_cliente 1 = B-coorporativo-noDeclara-serie y 2 = A-estatal-Sideclara-numeroActual
        if ($tipoCliente->tipo_cliente_id === 2 ) {
            $estado = 2;

             $cai = DB::SELECTONE("select
                             id,
                             numero_inicial,
                             numero_final,
                             cantidad_otorgada,
                             numero_actual as 'numero_actual',
                             if( DATE(NOW()) > fecha_limite_emision ,'TRUE','FALSE') as fecha_limite_emision,
                             cantidad_no_utilizada
                             from cai
                                      where tipo_documento_fiscal_id = 4 and estado_id = 1
                                          and cantidad_no_utilizada > 0
                                          and DATE(fecha_limite_emision) >= CURDATE()");

         } elseif($tipoCliente->tipo_cliente_id === 1) {

             $estado = 1;

             $cai = DB::SELECTONE("select
                             id,
                             numero_inicial,
                             numero_final,
                             cantidad_otorgada,
                             serie as 'numero_actual',
                             if( DATE(NOW()) > fecha_limite_emision ,'TRUE','FALSE') as fecha_limite_emision,
                             cantidad_no_utilizada
                             from cai
                                      where tipo_documento_fiscal_id = 4 and estado_id = 1
                                          and cantidad_no_utilizada > 0
                                          and DATE(fecha_limite_emision) >= CURDATE()");
         }

            if (!$cai) {
                return response()->json([
                     "title" => "CAI no disponible",
                     "icon" => "warning",
                     "text" => "No existe un CAI vigente con correlativos disponibles para notas de débito.",
                ], 422);
            }

            DB::beginTransaction();

            $caiBloqueado = ModelCAI::whereKey($cai->id)->lockForUpdate()->first();
            DB::table('factura')->where('id', $request->factura_id)->lockForUpdate()->first();

            if (!$caiBloqueado || $caiBloqueado->cantidad_no_utilizada <= 0
                 || date('Y-m-d', strtotime($caiBloqueado->fecha_limite_emision)) < date('Y-m-d')) {
                DB::rollBack();
                return response()->json([
                     "title" => "CAI no disponible",
                     "icon" => "warning",
                     "text" => "El CAI ya no tiene correlativos vigentes disponibles.",
                ], 422);
            }

            $notaActiva = DB::table('notadebito')
                ->where('factura_id', $request->factura_id)
                ->where('estado_id', 1)
                ->exists();

            if ($notaActiva) {
                DB::rollBack();
                return response()->json([
                     'icon' => 'warning',
                     'title' => 'Nota ya asignada',
                     'text' => 'La factura ya tiene una nota de débito activa.',
                ], 422);
            }

            $cai->numero_actual = $tipoCliente->tipo_cliente_id === 2
                ? $caiBloqueado->numero_actual
                : $caiBloqueado->serie;
            $cai->cantidad_no_utilizada = $caiBloqueado->cantidad_no_utilizada;

         if ($cai->numero_actual < $cai->cantidad_otorgada) {
                DB::rollBack();
            return response()->json([
                "title" => "Advertencia",
                "icon" => "warning",
                "text" => "La Nota de débito no puede proceder por alcanzar límite de número CAI.",
            ], 400);
        }

        //dd($num_1);


        $numeroSecuencia = $cai->numero_actual;
        $arrayCai = explode('-', $cai->numero_final);
        $cuartoSegmentoCAI = sprintf("%'.08d", $numeroSecuencia);
        $numeroCAI = $arrayCai[0] . '-' . $arrayCai[1] . '-' . $arrayCai[2] . '-' . $cuartoSegmentoCAI;


        //dd($numeroCAI);
       // $numeroCAI = $arrayCai[0].'-'.$arrayCai[1].'-'.$arrayCai[2].'-'.$cuartoSegmentoCAI;

        $fechaActual = date('Y');
        $correlativo = $fechaActual.'-'.$numeroSecuencia;

        /* GUARDANDO LO DE LA NOTA DE DÉBITO */


        $validarCAI = new Notificaciones();
        $validarCAI->validarAlertaCAI(ltrim($arrayCai[3],"0"),$numeroSecuencia, 5);

        $NotaDebito = new mNotaDebito;
        $NotaDebito->factura_id = $request->factura_id;
        $NotaDebito->montoNotaDebito_id = $request->montoNotaDebito_id;
        $NotaDebito->monto_asignado = $request->monto_;
        $NotaDebito->fechaEmision = $request->fechaEmision;
        $NotaDebito->motivoDescripcion = $request->motivoDescripcion;
        $NotaDebito->cai_ndebito = $cai->id;
        $NotaDebito->numeroCai = $numeroCAI;
        $NotaDebito->correlativoND = $correlativo;
        $NotaDebito->estado_id = 1;
        $NotaDebito->estado_nota_dec = $estado;
        $NotaDebito->users_registra_id = Auth::user()->id;



        $NotaDebito->estado_sumado =2;
        $NotaDebito->user_registra_sumado = 0;
        $NotaDebito->comentario_sumado = 'N/A';
        $NotaDebito->fecha_sumado = NULL;

        $NotaDebito->save();

        /* SE AGREGA LA FUNCION DE SUMAR EL MONTO DE NOTA DE DEBITO A LA FACTURA */
        /*
                $facturaTotal = DB::SELECTONE("select total from factura where id = ". $request->factura_id);

                $totalFacturaMasnDebito = $request->monto_ + $facturaTotal->total;

                DB::table('factura')->where('id',$request->factura_id)->update(array('total'=>$totalFacturaMasnDebito));
        */

        /* ============================================================================ */


        if ($tipoCliente->tipo_cliente_id === 2 ) {
            $caiUpdated =  ModelCAI::find($cai->id);
            $caiUpdated->numero_actual = $caiUpdated->numero_actual + 1;
            $caiUpdated->cantidad_no_utilizada=  $caiUpdated->cantidad_no_utilizada - 1;
            $caiUpdated->save();
        }  elseif($tipoCliente->tipo_cliente_id === 1 )  {
            $caiUpdated =  ModelCAI::find($cai->id);
            $caiUpdated->serie = $caiUpdated->serie + 1;
            $caiUpdated->cantidad_no_utilizada=  $caiUpdated->cantidad_no_utilizada - 1;
            $caiUpdated->save();
        }


        DB::commit();
       return response()->json([
        'icon' => 'success',
        'text' => 'Nota de debito realizada con éxito.',
        'title' => 'Exito!',
       ],200);
    } catch (\Throwable $e) {
        DB::rollback();
        return response()->json([
            'icon' => 'error',
            'text' => 'Ha ocurrido un error al guardar la nota de debito',
            'title' => 'Error',
            'message' => 'Ha ocurrido un error',
            'error' => $e,
           ],402);
       }



    }

    public function listarnotasDebito(Request $request){
        try {
            $listanotaDebito = DB::table('notadebito as nd')
                ->join('factura as f', 'f.id', '=', 'nd.factura_id')
                ->join('cliente as c', 'c.id', '=', 'f.cliente_id')
                ->join('users as u', 'u.id', '=', 'nd.users_registra_id')
                ->select([
                    'nd.id', 'nd.factura_id', 'nd.monto_asignado', 'nd.fechaEmision',
                    'nd.motivoDescripcion', 'nd.numeroCai', 'nd.correlativoND',
                    'nd.estado_id', 'nd.created_at', 'f.cai as factura_cai',
                    'c.nombre as cliente', 'u.name as user',
                ])
                ->when($request->filled('fecha_desde'), fn ($query) => $query->whereDate('nd.fechaEmision', '>=', $request->fecha_desde))
                ->when($request->filled('fecha_hasta'), fn ($query) => $query->whereDate('nd.fechaEmision', '<=', $request->fecha_hasta))
                ->when($request->filled('factura'), function ($query) use ($request) {
                    $buscar = trim($request->factura);
                    $query->where(function ($filtro) use ($buscar) {
                        $filtro->where('f.cai', 'like', "%{$buscar}%")
                            ->orWhere('nd.correlativoND', 'like', "%{$buscar}%")
                            ->orWhere('nd.numeroCai', 'like', "%{$buscar}%");
                    });
                })
                ->when($request->filled('cliente'), function ($query) use ($request) {
                    $query->where(function ($filtro) use ($request) {
                        $filtro->where('c.nombre', $request->cliente)
                            ->orWhere('f.nombre_cliente', $request->cliente);
                    });
                })
                ->when($request->filled('usuario'), fn ($query) => $query->where('u.name', $request->usuario))
                ->when($request->filled('estado'), fn ($query) => $query->where('nd.estado_id', (int) $request->estado))
                ->orderByDesc('nd.id');

            return Datatables::of($listanotaDebito)
            ->addColumn('estado', function ($nota) {
                return (int) $nota->estado_id === 1
                    ? '<span class="badge badge-success p-2">Activo</span>'
                    : '<span class="badge badge-danger p-2">Anulado</span>';
            })
            ->addColumn('file', function ($nota) {
                return '<a class="btn btn-sm btn-outline-warning" target="_blank" href="/debito/imprimir/'
                    . $nota->factura_id . '"><i class="fa fa-file-pdf-o mr-1"></i>Ver</a>';
            })
            ->rawColumns(['estado','file'])
            ->make(true);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al listar las notas de debito.',
                'errorTh' => $e,
            ], 402);

        }
    }

    public function descargarNota($idFactura){

            $notaDebito = DB::SELECTONE("
                select
                    id
                    ,factura_id
                    ,monto_asignado
                    ,fechaEmision
                    ,motivoDescripcion
                    ,cai_ndebito
                    ,numeroCai
                    ,correlativoND
                    ,(select name from users where id = notadebito.users_registra_id) as 'user'
                    ,created_at
                    ,estado_id
                from notadebito
                where notadebito.factura_id = ".$idFactura."
                order by notadebito.id desc
                limit 1"
            );

            $cai = DB::SELECTONE("select
                *
            from cai
            where tipo_documento_fiscal_id = 4 and id = ".$notaDebito->cai_ndebito);

            //dd($cai);
            $cliente = DB::SELECTONE("select
                nombre_cliente,
                cai,
                estado_factura_id,
                numero_factura  ,
                cliente.direccion,
                cliente.telefono_empresa,
                cliente.rtn,
                cliente.correo
                from factura
                join cliente on cliente.id = factura.cliente_id
                where factura.id =".$idFactura);

            $formatter = new NumeroALetras();
            $formatter->apocope = true;
            $numeroLetras = $formatter->toMoney($notaDebito->monto_asignado, 2, 'LEMPIRAS', 'CENTAVOS');

            $montoConCentavos= DB::SELECTONE("
            select
                FORMAT(monto_asignado,2) as total
            from notadebito where id = ".$notaDebito->id);

            $pdf = PDF::loadView('/pdf/nodaDeDebito', compact('numeroLetras','notaDebito', 'cliente', 'cai', 'montoConCentavos'))->setPaper('letter');

            return $pdf->stream("nota_debito_" . $notaDebito->factura_id.".pdf");

    }
    public function descargarNotaCopia($idFactura){

        $notaDebito = DB::SELECTONE("
            select
                id
                ,factura_id
                ,monto_asignado
                ,fechaEmision
                ,motivoDescripcion
                ,cai_ndebito
                ,numeroCai
                ,correlativoND
                ,(select name from users where id = notadebito.users_registra_id) as 'user'
                ,created_at
                ,estado_id
            from notadebito
            where notadebito.factura_id = ".$idFactura."
            order by notadebito.id desc
            limit 1"
        );

        $cai = DB::SELECTONE("select
            *
        from cai
        where tipo_documento_fiscal_id = 4 and estado_id = 1 and id = ".$notaDebito->cai_ndebito);


        $cliente = DB::SELECTONE("select
        nombre_cliente,
        cai,
        estado_factura_id,
        numero_factura  ,
        cliente.direccion,
        cliente.telefono_empresa,
        cliente.rtn,
        cliente.correo
        from factura
        join cliente on cliente.id = factura.cliente_id
        where factura.id =".$idFactura);
        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney($notaDebito->monto_asignado, 2, 'LEMPIRAS', 'CENTAVOS');

        $montoConCentavos= DB::SELECTONE("
        select
            FORMAT(monto_asignado,2) as total
        from notadebito where id = ".$notaDebito->id);

        $pdf = PDF::loadView('/pdf/nodaDeDebito_copia', compact('numeroLetras','notaDebito', 'cliente', 'cai', 'montoConCentavos'))->setPaper('letter');

        return $pdf->stream("nota_debito_" . $notaDebito->factura_id.".pdf");

}

    public function anularNotaDebito($idNota){
        try {
            DB::beginTransaction();

                DB::update('
                update
                notadebito
                set estado_id = 2
                where estado_sumado = 2 id ='.$idNota);

            DB::commit();
            return response()->json([
                "icon" => "success",
                "text" => "Nota Anulada con éxito!",
                "title"=>"Exito!"
            ],200);

        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                "icon" => "error",
                "text" => "Ha ocurrido un error al Anular la nota.",
                "title"=>"Error!",
                "error" => $e
            ],402);
        }
    }

    private function caiDebitoDisponible(): bool
    {
        return DB::table('cai')
            ->where('tipo_documento_fiscal_id', 4)
            ->where('estado_id', 1)
            ->where('cantidad_no_utilizada', '>', 0)
            ->whereDate('fecha_limite_emision', '>=', now()->toDateString())
            ->exists();
    }
}
