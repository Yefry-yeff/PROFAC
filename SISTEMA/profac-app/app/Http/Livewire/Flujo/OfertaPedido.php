<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Auth;
use Validator;
use PDF;
use Luecano\NumeroALetras\NumeroALetras;
use App\Models\ModelOferta;
use App\Models\ModelOfertaProducto;

class OfertaPedido extends Component
{
    public $pedidoId;
    public $pedidoCliente;  // client name pre-loaded from pedido

    public function mount($pedidoId)
    {
        $this->pedidoId = $pedidoId;

        // Load pedido + client info to pre-populate the form
        $pedido = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->select('c.id as cliente_id', 'c.nombre as nombre_cliente', 'c.rtn')
            ->where('p.id', $pedidoId)
            ->first();

        $this->pedidoCliente = $pedido ? (array) $pedido : null;
    }

    public function render()
    {
        $pedidoId      = $this->pedidoId;
        $pedidoCliente = $this->pedidoCliente;
        $tipoCotizacion = 1; // always corporate
        $layout = request()->has('embed') ? 'layouts.embed' : 'layouts.app';
        return view('livewire.flujo.oferta-pedido', compact('pedidoId', 'pedidoCliente', 'tipoCotizacion'))
            ->layout($layout);
    }

    // ── Same client list logic as Cotizacion (reused) ───────────────────────
    public function listarClientes(Request $request)
    {
        try {
            $listaClientes = DB::select("
                SELECT id, nombre AS text
                FROM cliente
                WHERE estado_cliente_id = 1
                  AND (id LIKE ? OR nombre LIKE ?)
                LIMIT 15
            ", ['%'.$request->search.'%', '%'.$request->search.'%']);

            return response()->json(['results' => $listaClientes], 200);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 402);
        }
    }

    // ── Save Oferta ─────────────────────────────────────────────────────────
    public function guardarOferta(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'subTotalGeneralGrabado'        => 'required',
                'subTotalGeneralGrabadoMostrar' => 'required',
                'subTotalGeneral'               => 'required',
                'isvGeneral'                    => 'required',
                'totalGeneral'                  => 'required',
                'numeroInputs'                  => 'required',
                'seleccionarCliente'            => 'required',
                'nombre_cliente_ventas'         => 'required',
                'bodega'                        => 'required',
                'seleccionarProducto'           => 'required',
                'pedido_id'                     => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'icon'    => 'error',
                    'title'   => 'Error de validación',
                    'text'    => 'Por favor, verifica que todos los campos estén completados.',
                    'errors'  => $validator->errors(),
                ], 401);
            }

            // Verificar si ya existe una oferta ganadora para este pedido
            $tieneGanadora = DB::table('oferta')
                ->where('pedido_id', $request->pedido_id)
                ->where('estado', 'ganadora')
                ->exists();

            if ($tieneGanadora) {
                return response()->json([
                    'icon'  => 'warning',
                    'title' => 'Oferta ganadora ya seleccionada',
                    'text'  => 'Este pedido ya tiene una oferta ganadora. No se pueden crear más ofertas.',
                ], 200);
            }

            // Limit: max 10 ofertas per pedido
            $totalOfertas = DB::table('oferta')
                ->where('pedido_id', $request->pedido_id)
                ->count();

            if ($totalOfertas >= 10) {
                return response()->json([
                    'icon'  => 'warning',
                    'title' => 'Límite alcanzado',
                    'text'  => 'Este pedido ya tiene 10 ofertas registradas (máximo permitido).',
                ], 200);
            }

            $arrayInputs = explode(',', $request->arregloIdInputs);
            $arrayProductos = [];

            DB::beginTransaction();

            $oferta = new ModelOferta();
            $oferta->pedido_id         = $request->pedido_id;
            $oferta->nombre_cliente    = $request->nombre_cliente_ventas;
            $oferta->RTN               = $request->rtn_ventas;
            $oferta->fecha_emision     = $request->fecha_emision;
            $oferta->fecha_vencimiento = $request->fecha_emision;
            $oferta->sub_total         = $request->subTotalGeneral;
            $oferta->sub_total_grabado = $request->subTotalGeneralGrabado;
            $oferta->sub_total_excento = $request->subTotalGeneralExcento ?? 0;
            $oferta->isv               = $request->isvGeneral;
            $oferta->total             = $request->totalGeneral;
            $oferta->cliente_id        = $request->seleccionarCliente;
            $oferta->tipo_venta_id     = $request->tipo_venta_id ?? 1;
            $oferta->vendedor          = $request->vendedor ?? Auth::id();
            $oferta->users_id          = Auth::id();
            $oferta->arregloIdInputs   = json_encode($request->arregloIdInputs);
            $oferta->numeroInputs      = $request->numeroInputs;
            $oferta->porc_descuento    = $request->porDescuento ?? 0;
            $oferta->monto_descuento   = $request->descuentoGeneral ?? 0;
            $oferta->nota              = $request->nota;
            $oferta->save();

            for ($i = 0; $i < count($arrayInputs); $i++) {
                $idx = $arrayInputs[$i];

                $arrayProductos[] = [
                    'oferta_id'               => $oferta->id,
                    'producto_id'             => $request->{'idProducto'.$idx},
                    'indice'                  => $idx,
                    'nombre_producto'         => $request->{'nombre'.$idx},
                    'nombre_bodega'           => $request->{'bodega'.$idx},
                    'precio_unidad'           => $request->{'precio'.$idx},
                    'cantidad'                => $request->{'cantidad'.$idx},
                    'sub_total'               => $request->{'subTotal'.$idx},
                    'isv'                     => $request->{'isvProducto'.$idx},
                    'total'                   => $request->{'total'.$idx},
                    'bodega_id'               => $request->{'idBodega'.$idx},
                    'seccion_id'              => $request->{'idSeccion'.$idx},
                    'resta_inventario'        => $request->{'restaInventario'.$idx},
                    'isv_producto'            => $request->{'isv'.$idx},
                    'unidad_medida_venta_id'  => $request->{'idUnidadVenta'.$idx},
                    'monto_descProducto'      => $request->{'acumuladoDescuento'.$idx} ?? 0,
                    'idPrecioSeleccionado'    => $request->{'idPrecioSeleccionado'.$idx},
                    'precioSeleccionado'      => $request->{'precios'.$idx},
                    'precios_producto_carga_id' => $request->{'precios_producto_carga_id'.$idx},
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ];
            }

            ModelOfertaProducto::insert($arrayProductos);

            // ── Registrar/actualizar en sistema de flujo ─────────────────────────
            $hfExistente = DB::table('historico_flujo')
                ->where('tipo_tramite_id', 1) // 'pedido' en tipos_tramites
                ->where('tramite_id', $request->pedido_id)
                ->first();

            if ($hfExistente) {
                // Ya existe flujo del pedido → actualizar estatus a Ofertas (id=2)
                DB::table('flujo')
                    ->where('id', $hfExistente->flujo_id)
                    ->update([
                        'tipo_tramite_id' => 2,
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);
                DB::table('historico_flujo')->insert([
                    'flujo_id'        => $hfExistente->flujo_id,
                    'tipo_tramite_id' => 2, // 'Ofertas' en tipos_tramites
                    'tramite_id'      => $oferta->id,
                    'estado_id'       => DB::table('estado_venta')->where('descripcion', 'activa')->value('id'),
                    'observaciones'   => 'Oferta #'.$oferta->id.' registrada para pedido #'.$request->pedido_id,
                    'created_by'    => Auth::id(),
                    'updated_by'    => Auth::id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            } else {
                // Pedido sin flujo previo → crear flujo directamente en etapa Ofertas
                $flujoId = DB::table('flujo')->insertGetId([
                    'tipo_flujo_id'   => 1,
                    'identificacion'  => (string) $request->pedido_id,
                    'nombre'          => $request->nombre_cliente_ventas,
                    'cliente_rtn'     => $request->rtn_ventas ?? null,
                    'tipo_tramite_id' => 2,
                    'created_by'    => Auth::id(),
                    'updated_by'    => Auth::id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                DB::table('historico_flujo')->insert([
                    'flujo_id'        => $flujoId,
                    'tipo_tramite_id' => 2, // 'Ofertas' en tipos_tramites
                    'tramite_id'      => $oferta->id,
                    'estado_id'       => DB::table('estado_venta')->where('descripcion', 'activa')->value('id'),
                    'observaciones'   => 'Oferta #'.$oferta->id.' registrada (flujo iniciado desde oferta)',
                    'created_by'    => Auth::id(),
                    'updated_by'    => Auth::id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'icon'     => 'success',
                'title'    => '¡Éxito!',
                'text'     => 'Oferta <strong>#'.$oferta->id.'</strong> registrada correctamente.',
                'idOferta' => $oferta->id,
                'pedidoId' => $oferta->pedido_id,
            ], 200);

        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'Ha ocurrido un error al guardar la oferta.',
                'error' => $e->getMessage(),
            ], 402);
        }
    }

    // ── Print Oferta (PDF-style view) ────────────────────────────────────────
    public function imprimirOferta($id)
    {
        $oferta = DB::table('oferta as o')
            ->join('cliente as c', 'c.id', '=', 'o.cliente_id')
            ->join('users as u', 'u.id', '=', 'o.users_id')
            ->select(
                'o.*',
                'c.nombre as cliente_nombre',
                'c.direccion',
                'c.correo',
                'c.telefono_empresa',
                'c.rtn',
                'u.name as cotizador',
                DB::raw('(SELECT name FROM users WHERE id = o.vendedor) as vendedor_nombre')
            )
            ->where('o.id', $id)
            ->first();

        $productos = DB::table('oferta_has_producto as ohp')
            ->join('producto as p', 'p.id', '=', 'ohp.producto_id')
            ->join('unidad_medida_venta as umv', 'umv.id', '=', 'ohp.unidad_medida_venta_id')
            ->join('unidad_medida as um', 'um.id', '=', 'umv.unidad_medida_id')
            ->select(
                'p.id as codigo',
                'p.nombre',
                'p.descripcion',
                DB::raw("IF(p.isv=0,'SI','NO') as excento"),
                DB::raw('FORMAT(ohp.precio_unidad,2) as precio'),
                DB::raw('FORMAT(ohp.cantidad,2) as cantidad'),
                DB::raw('FORMAT(ohp.sub_total,2) as importe'),
                'um.nombre as medida'
            )
            ->where('ohp.oferta_id', $id)
            ->orderBy('ohp.indice')
            ->get();

        $flagCentavos = fmod((float) $oferta->total, 1) != 0.0;
        $formatter = new NumeroALetras();
        $formatter->apocope = true;
        $numeroLetras = $formatter->toMoney((float) $oferta->total, 2, 'LEMPIRAS', 'CENTAVOS');

        $esCancelada = isset($oferta->estado) && $oferta->estado === 'cancelada';

        $pdf = PDF::loadView('flujo.oferta-imprimir', compact('oferta', 'productos', 'flagCentavos', 'numeroLetras', 'esCancelada'))
            ->setPaper('letter');

        return $pdf->stream('Oferta_Pedido'.$oferta->pedido_id.'_No'.$oferta->id.'.pdf');
    }
}
