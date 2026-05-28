<?php

namespace App\Http\Livewire\Cardex;

use Livewire\Component;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Auth;
use Validator;
use DataTables;

class CardexGeneral extends Component
{
    public function render()
    {
        return view('livewire.cardex.cardex-general');
    }

    public function listarCardex(Request $request, $fecha_inicio, $fecha_final){
        try {

            $filtroProducto   = trim($request->input('filtroProducto', ''));
            $filtroCai        = trim($request->input('filtroCai', ''));
            $filtroUsuario    = trim($request->input('filtroUsuario', ''));
            $filtroBodOrigen  = trim($request->input('filtroBodegaOrigen', ''));
            $filtroBodDestino = trim($request->input('filtroBodegaDestino', ''));
            $tipoDocumento    = trim($request->input('tipoDocumento', ''));
            $idDocumento      = trim($request->input('idDocumento', ''));
            $filtroDesde      = trim($request->input('filtroDesde', $fecha_inicio));
            $filtroHasta      = trim($request->input('filtroHasta', $fecha_final));

            $pick = function (array $candidates) {
                foreach ($candidates as $candidate) {
                    if (Schema::hasColumn('cardex', $candidate)) {
                        return $candidate;
                    }
                }
                return null;
            };

            $colProductoId  = $pick(['id_producto', 'producto_id']);
            $colFactura     = $pick(['id_factura', 'factura_id', 'factura']);
            $colAjuste      = $pick(['id_ajuste', 'ajuste_id', 'ajuste']);
            $colCompra      = $pick(['id_compra', 'detalleCompra', 'compra_id']);
            $colComprobante = $pick(['id_comprobante_entrega', 'comprobante_entrega_id', 'comprobante']);
            $colVale1       = $pick(['id_vale_tipo_1', 'vale_tipo_1']);
            $colVale2       = $pick(['id_vale_tipo_2', 'vale_tipo_2']);
            $colNotaCredito = $pick(['id_nota_de_credito', 'id_nota_credito', 'nota_credito_id', 'nota_credito']);
            $colUsuario     = $pick(['usuario', 'user_id', 'users_id']);
            $colUsuarioNombre = $pick(['usuario_nombre', 'nombre_usuario', 'usuario_texto']);
            $colBodOrigen   = $pick(['id_Bodega_origen', 'id_bodega_origen']);
            $colSecOrigen   = $pick(['id_seccion_origen']);
            $colBodDestino  = $pick(['id_bodega_destino', 'id_Bodega_destino']);
            $colSecDestino  = $pick(['id_seccion_destino']);
            $colFechaGestion = $pick(['fecha_gestion', 'fechaGestion']);
            $colFechaOrden = $colFechaGestion ?: 'fecha_creacion';

            $origenExpr = "''";
            if ($colBodOrigen && $colSecOrigen) {
                $origenExpr = "TRIM(CONCAT(COALESCE(bo.nombre, ''), ' ', COALESCE(so.descripcion, '')))";
            } elseif ($colBodOrigen) {
                $origenExpr = "COALESCE(bo.nombre, '')";
            }

            $destinoExpr = "''";
            if ($colBodDestino && $colSecDestino) {
                $destinoExpr = "TRIM(CONCAT(COALESCE(bd.nombre, ''), ' ', COALESCE(sd.descripcion, '')))";
            } elseif ($colBodDestino) {
                $destinoExpr = "COALESCE(bd.nombre, '')";
            }

            $query = DB::table('cardex as c');

            if ($colProductoId) {
                $query->leftJoin('producto as p', 'p.id', '=', "c.$colProductoId");
            }
            if ($colFactura) {
                $query->leftJoin('factura as f', 'f.id', '=', "c.$colFactura");
            }
            if ($colAjuste) {
                $query->leftJoin('ajuste as a', 'a.id', '=', "c.$colAjuste");
            }
            if ($colComprobante) {
                $query->leftJoin('comprovante_entrega as ce', 'ce.id', '=', "c.$colComprobante");
            }
            if ($colVale1) {
                $query->leftJoin('vale as v1', 'v1.id', '=', "c.$colVale1");
            }
            if ($colVale2) {
                $query->leftJoin('vale as v2', 'v2.id', '=', "c.$colVale2");
            }
            if ($colNotaCredito) {
                $query->leftJoin('nota_credito as nc', 'nc.id', '=', "c.$colNotaCredito");
            }
            if ($colUsuario) {
                $query->leftJoin('users as u', 'u.id', '=', "c.$colUsuario");
            }
            if ($colBodOrigen) {
                $query->leftJoin('bodega as bo', 'bo.id', '=', "c.$colBodOrigen");
            }
            if ($colSecOrigen) {
                $query->leftJoin('seccion as so', 'so.id', '=', "c.$colSecOrigen");
            }
            if ($colBodDestino) {
                $query->leftJoin('bodega as bd', 'bd.id', '=', "c.$colBodDestino");
            }
            if ($colSecDestino) {
                $query->leftJoin('seccion as sd', 'sd.id', '=', "c.$colSecDestino");
            }

            $query->select([
                DB::raw("DATE_FORMAT(c.$colFechaOrden, '%Y-%m-%d %H:%i:%s') as fechaIngreso"),
                DB::raw($colProductoId ? 'COALESCE(c.producto, p.nombre) as producto' : 'c.producto as producto'),
                DB::raw($colProductoId ? "c.$colProductoId as codigoProducto" : 'NULL as codigoProducto'),
                DB::raw($colFactura ? "c.$colFactura as factura" : 'NULL as factura'),
                DB::raw($colFactura ? 'f.cai as factura_cod' : 'NULL as factura_cod'),
                DB::raw($colAjuste ? "c.$colAjuste as ajuste" : 'NULL as ajuste'),
                DB::raw($colAjuste ? 'a.numero_ajuste as ajuste_cod' : 'NULL as ajuste_cod'),
                DB::raw($colCompra ? "c.$colCompra as detalleCompra" : 'NULL as detalleCompra'),
                DB::raw($colComprobante ? "c.$colComprobante as comprobante" : 'NULL as comprobante'),
                DB::raw($colComprobante ? 'ce.numero_comprovante as comprobante_cod' : 'NULL as comprobante_cod'),
                DB::raw($colVale1 ? "c.$colVale1 as vale_tipo_1" : 'NULL as vale_tipo_1'),
                DB::raw($colVale1 ? 'v1.numero_vale as vale_tipo_1_cod' : 'NULL as vale_tipo_1_cod'),
                DB::raw($colVale2 ? "c.$colVale2 as vale_tipo_2" : 'NULL as vale_tipo_2'),
                DB::raw($colVale2 ? 'v2.numero_vale as vale_tipo_2_cod' : 'NULL as vale_tipo_2_cod'),
                DB::raw($colNotaCredito ? "c.$colNotaCredito as nota_credito" : 'NULL as nota_credito'),
                DB::raw($colNotaCredito ? 'nc.numero_nota as nota_credito_cod' : 'NULL as nota_credito_cod'),
                DB::raw('c.descripcion as descripcion'),
                DB::raw("$origenExpr as origen"),
                DB::raw("$destinoExpr as destino"),
                DB::raw('c.cantidad as cantidad'),
                DB::raw($colUsuario ? "COALESCE(u.name, CAST(c.$colUsuario AS CHAR)) as usuario" : ($colUsuarioNombre ? "c.$colUsuarioNombre as usuario" : 'NULL as usuario')),
            ]);

            if ($filtroDesde !== '' && $filtroHasta !== '') {
                $query->whereBetween(DB::raw("DATE(c.$colFechaOrden)"), [$filtroDesde, $filtroHasta]);
            } elseif ($filtroDesde !== '') {
                $query->whereDate("c.$colFechaOrden", '>=', $filtroDesde);
            } elseif ($filtroHasta !== '') {
                $query->whereDate("c.$colFechaOrden", '<=', $filtroHasta);
            }

            if ($filtroProducto !== '') {
                $query->where(function ($q) use ($filtroProducto, $colProductoId) {
                    if ($colProductoId) {
                        $q->where("c.$colProductoId", 'like', "%{$filtroProducto}%")
                          ->orWhere('c.producto', 'like', "%{$filtroProducto}%")
                          ->orWhere('p.nombre', 'like', "%{$filtroProducto}%");
                    } else {
                        $q->where('c.producto', 'like', "%{$filtroProducto}%");
                    }
                });
            }

            if ($filtroCai !== '') {
                if ($colFactura) {
                    $query->where('f.cai', 'like', "%{$filtroCai}%");
                }
            }

            if ($filtroUsuario !== '') {
                if ($colUsuario) {
                    $query->where("c.$colUsuario", $filtroUsuario);
                }
            }

            if ($filtroBodOrigen !== '') {
                if ($colBodOrigen) {
                    $query->where("c.$colBodOrigen", $filtroBodOrigen);
                }
            }

            if ($filtroBodDestino !== '') {
                if ($colBodDestino) {
                    $query->where("c.$colBodDestino", $filtroBodDestino);
                }
            }

            if ($idDocumento !== '') {
                switch ($tipoDocumento) {
                    case 'ajuste':
                        if ($colAjuste) {
                            $query->where("c.$colAjuste", $idDocumento);
                        }
                        break;
                    case 'compra':
                        if ($colCompra) {
                            $query->where("c.$colCompra", $idDocumento);
                        }
                        break;
                    case 'comprobante':
                        if ($colComprobante) {
                            $query->where("c.$colComprobante", $idDocumento);
                        }
                        break;
                    case 'vale':
                        if ($colVale1 || $colVale2) {
                            $query->where(function ($q) use ($idDocumento, $colVale1, $colVale2) {
                                if ($colVale1) {
                                    $q->where("c.$colVale1", $idDocumento);
                                }
                                if ($colVale2) {
                                    if ($colVale1) {
                                        $q->orWhere("c.$colVale2", $idDocumento);
                                    } else {
                                        $q->where("c.$colVale2", $idDocumento);
                                    }
                                }
                            });
                        }
                        break;
                    case 'nota_credito':
                        if ($colNotaCredito) {
                            $query->where("c.$colNotaCredito", $idDocumento);
                        }
                        break;
                }
            }

            $listaCardex = $query
                ->orderByDesc("c.$colFechaOrden")
                ->get();

            

            return Datatables::of($listaCardex)
            ->addColumn('doc_factura', function($elemento){
                if($elemento->factura != null){
                    return '<a target="_blank" href="/detalle/venta/'.$elemento->factura.'"><i class="fas fa-receipt"></i> FACTURA # '.$elemento->factura_cod.'</a>';
                }
            })
            ->addColumn('doc_ajuste', function($elemento){
                if($elemento->ajuste != null){
                    return '<a target="_blank" href="/ajustes/imprimir/ajuste/'.$elemento->ajuste.'"><i class="fas fa-receipt"></i> VER DETALLE DE AJUSTE #'.$elemento->ajuste_cod.'</a>';
                }
            })
            ->addColumn('detalleCompra', function($elemento){
                if($elemento->detalleCompra != null){
                    return '<a target="_blank" href="/producto/compras/detalle/'.$elemento->detalleCompra.'"><i class="fas fa-receipt"></i> DETALLE DE COMPRA </a>';
                }
            })

            ->addColumn('comprobante_entrega', function($elemento){
                if($elemento->comprobante != null){
                    return '<a target="_blank" href="/comprobante/imprimir/'.$elemento->comprobante.'"><i class="fas fa-receipt"></i> COMPROBANTE DE ENTREGA #'.$elemento->comprobante_cod.' </a>';
                }
            })

            ->addColumn('vale_tipo_1', function($elemento){
                if($elemento->vale_tipo_1 != null){
                    return '<a target="_blank" href="/imprimir/entrega/'.$elemento->vale_tipo_1.'"><i class="fas fa-receipt"></i> VALE TIPO 1 #'.$elemento->vale_tipo_1_cod.' </a>';
                }
            })

            ->addColumn('vale_tipo_2', function($elemento){
                if($elemento->vale_tipo_2 != null){
                    return '<a target="_blank" href="/vale/imprimir/'.$elemento->vale_tipo_2.'"><i class="fas fa-receipt"></i> VALE TIPO 2 #'.$elemento->vale_tipo_2_cod.' </a>';
                }
            })

            ->addColumn('nota_credito', function($elemento){
                if($elemento->nota_credito != null){
                    return '<a target="_blank" href="/nota/credito/imprimir/'.$elemento->nota_credito.'"><i class="fas fa-receipt"></i> NOTA DE CREDITO #'.$elemento->nota_credito_cod.' </a>';
                }
            })
            ->rawColumns(['doc_factura','doc_ajuste', 'detalleCompra','comprobante_entrega','vale_tipo_1','vale_tipo_2','nota_credito'])
            ->make(true);

        } catch (QueryException $e) {

            return response()->json([
                "message" => "Ha ocurrido un error al listar el cardex solicitado.",
                "error" => $e
            ]);
        }

    }
}
