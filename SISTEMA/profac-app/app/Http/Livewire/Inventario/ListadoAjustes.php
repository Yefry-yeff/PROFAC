<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use Auth;
use DataTables;
use Validator;
use Illuminate\Support\Facades\File;
use PDF;
use Luecano\NumeroALetras\NumeroALetras;

class ListadoAjustes extends Component
{
    public function render()
    {
        $fechaActual = date('n');
        $resta = $fechaActual - 2;
        $anio = date("Y");

        if($resta<=0){
            $resta=12;
            $anio = $anio - 1;
        }

        if($resta<10){
            $resta = '0'.$resta;
        }

        $fechaInicio = $anio.'-'.$resta.'-01';
       
        return view('livewire.inventario.listado-ajustes',compact('fechaInicio'));
    }

    public function listarAjustes(Request $request){
   
        try{
         
        $listado = DB::SELECT("
        select
        ajuste.id as codigo,
        comentario,
        tipo_ajuste.nombre as motivo,
        numero_ajuste,
        fecha,
        name,
        ajuste.created_at,
        ajuste.anulado
        from ajuste
        inner join users
        on users.id = ajuste.users_id
        inner join tipo_ajuste
        on ajuste.tipo_ajuste_id = tipo_ajuste.id
        where fecha BETWEEN '".$request->fechaInicio."' and '".$request->fechaFinal."'"
        );

        

        return Datatables::of($listado)
        ->addColumn('estado', function ($ajuste) {
            if ($ajuste->anulado == 1) {
                return '<span class="badge-anulado"><i class="fa fa-ban mr-1"></i>Anulado</span>';
            }
            return '<span class="badge-activo"><i class="fa fa-check-circle mr-1"></i>Activo</span>';
        })
        ->addColumn('opciones', function ($ajuste) {
            $btnImprimir = '<a href="/ajustes/imprimir/ajuste/'.$ajuste->codigo.'" target="_blank" class="dropdown-item"><i class="fa fa-file-invoice mr-1 text-warning"></i> Imprimir</a>';

            if ($ajuste->anulado == 1) {
                $btnAnular = '<a class="dropdown-item text-muted disabled" style="pointer-events:none;opacity:.5"><i class="fa fa-ban mr-1"></i> Anulado</a>';
            } else {
                $btnAnular = '<a class="dropdown-item text-danger" onclick="confirmarAnularAjuste('.$ajuste->codigo.',\''.addslashes($ajuste->numero_ajuste).'\')"><i class="fa fa-ban mr-1"></i> Anular</a>';
            }

            return '
            <div class="aj-dropdown">
                <button class="btn-aj-menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-ellipsis-v mr-1"></i> Acciones
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    '.$btnImprimir.'
                    <div class="dropdown-divider"></div>
                    '.$btnAnular.'
                </div>
            </div>';
        })

        ->rawColumns(['estado', 'opciones',])
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

    public function anularAjuste(Request $request)
    {
        try {
            $idAjuste = $request->idAjuste;
            $motivo   = trim($request->motivo ?? '');

            // Verificar que el ajuste exista y no esté ya anulado
            $ajuste = DB::SELECTONE("select id, numero_ajuste, anulado from ajuste where id = " . intval($idAjuste));

            if (!$ajuste) {
                return response()->json([
                    'icon'  => 'warning',
                    'text'  => 'El ajuste no existe.',
                    'title' => 'Advertencia!',
                ], 200);
            }

            if ($ajuste->anulado == 1) {
                return response()->json([
                    'icon'  => 'warning',
                    'text'  => 'Este ajuste ya fue anulado anteriormente.',
                    'title' => 'Acción no permitida!',
                ], 200);
            }

            // Obtener productos del ajuste con información de bodega
            $productos = DB::SELECT("
                select
                    ahp.recibido_bodega_id,
                    ahp.producto_id,
                    ahp.tipo_aritmetica,
                    ahp.cantidad_total,
                    ahp.unidad_medida_venta_id,
                    p.nombre as nombre_producto,
                    b.id as bodega_id,
                    b.nombre as bodega_nombre,
                    sg.id as segmento_id,
                    sg.descripcion as segmento_nombre,
                    sc.id as seccion_id,
                    sc.descripcion as seccion_nombre
                from ajuste_has_producto ahp
                inner join recibido_bodega rb on rb.id = ahp.recibido_bodega_id
                inner join seccion sc on sc.id = rb.seccion_id
                inner join segmento sg on sg.id = sc.segmento_id
                inner join bodega b on b.id = sg.bodega_id
                inner join producto p on p.id = ahp.producto_id
                where ahp.ajuste_id = " . intval($idAjuste)
            );

            if (empty($productos)) {
                return response()->json([
                    'icon'  => 'warning',
                    'text'  => 'No se encontraron productos asociados a este ajuste.',
                    'title' => 'Advertencia!',
                ], 200);
            }

            DB::beginTransaction();

            $usuario = Auth::user();
            $now = now();

            foreach ($productos as $prod) {
                $lote = DB::SELECTONE("select cantidad_disponible from recibido_bodega where id = " . intval($prod->recibido_bodega_id));

                // Operacion inversa: si fue suma (1) → restar; si fue resta (2) → sumar
                if ($prod->tipo_aritmetica == 1) {
                    $nuevaCantidad = $lote->cantidad_disponible - $prod->cantidad_total;
                    $descripcionAnulacionCardex = 'Anulación de ajuste - Ajuste de tipo suma de unidades (-)';
                    $descripcionAnulacionLog = 'Anulación ajuste suma (-)';
                } else {
                    $nuevaCantidad = $lote->cantidad_disponible + $prod->cantidad_total;
                    $descripcionAnulacionCardex = 'Anulación de ajuste - Ajuste de tipo resta de unidades (+)';
                    $descripcionAnulacionLog = 'Anulación ajuste resta (+)';
                }

                // Actualizar stock en recibido_bodega
                DB::statement(
                    "UPDATE recibido_bodega SET cantidad_disponible = ? WHERE id = ?",
                    [$nuevaCantidad, $prod->recibido_bodega_id]
                );

                // Insertar registro en cardex
                DB::table('cardex')->insert([
                    'fecha_creacion'      => $now,
                    'producto'            => $prod->nombre_producto,
                    'id_producto'         => $prod->producto_id,
                    'ajuste'              => $ajuste->id,
                    'ajuste_cod'          => $ajuste->numero_ajuste,
                    'descripcion'         => $descripcionAnulacionCardex,
                    'id_Bodega_origen'    => $prod->bodega_id,
                    'Bodega_origen_nombre'=> $prod->bodega_nombre,
                    'id_segmento_origen'  => $prod->segmento_id,
                    'segmento_origen_nombre' => $prod->segmento_nombre,
                    'id_seccion_origen'   => $prod->seccion_id,
                    'seccion_origen_nombre' => $prod->seccion_nombre,
                    'cantidad'            => $prod->cantidad_total,
                    'usuario'             => $usuario->name,
                ]);

                // Insertar en el log real de movimientos de inventario
                DB::table('log_translado')->insert([
                    'origen'               => $prod->recibido_bodega_id,
                    'cantidad'             => $prod->cantidad_total,
                    'unidad_medida_venta_id' => $prod->unidad_medida_venta_id,
                    'users_id'             => $usuario->id,
                    'descripcion'          => $descripcionAnulacionLog,
                    'ajuste_id'            => $ajuste->id,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ]);
            }

            // Marcar ajuste como anulado
            DB::statement(
                "UPDATE ajuste SET anulado = 1, anulado_por = ?, anulado_at = ?, motivo_anulacion = ? WHERE id = ?",
                [$usuario->id, $now, $motivo ?: null, $idAjuste]
            );

            DB::commit();

            return response()->json([
                'icon'  => 'success',
                'text'  => 'El ajuste <b>' . $ajuste->numero_ajuste . '</b> fue anulado exitosamente.',
                'title' => 'Éxito!',
            ], 200);

        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'icon'    => 'error',
                'text'    => 'Ha ocurrido un error al anular el ajuste.',
                'title'   => 'Error!',
                'message' => 'Ha ocurrido un error',
                'error'   => $e,
            ], 402);
        }
    }
}

