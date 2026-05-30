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
            $fechaBaseExpr = "COALESCE(c.fecha_creacion, c.created_at, c.updated_at)";

            if ($colFechaGestion) {
                $fechaOrdenExpr = "c.$colFechaOrden";
            } else {
                $caseFechaPartes = [];

                if ($colAjuste) {
                    $caseFechaPartes[] = "WHEN c.$colAjuste IS NOT NULL THEN COALESCE(a.created_at, a.updated_at, a.fecha, $fechaBaseExpr)";
                }

                if ($colCompra) {
                    $caseFechaPartes[] = "WHEN c.$colCompra IS NOT NULL THEN COALESCE(co.created_at, co.updated_at, co.fecha_recepcion, co.fecha_emision, $fechaBaseExpr)";
                }

                if ($colComprobante) {
                    $caseFechaPartes[] = "WHEN c.$colComprobante IS NOT NULL THEN COALESCE(ce.created_at, ce.updated_at, ce.fecha_emision, $fechaBaseExpr)";
                }

                if ($colVale1) {
                    $caseFechaPartes[] = "WHEN c.$colVale1 IS NOT NULL THEN COALESCE(v1.created_at, v1.updated_at, $fechaBaseExpr)";
                }

                if ($colVale2) {
                    $caseFechaPartes[] = "WHEN c.$colVale2 IS NOT NULL THEN COALESCE(v2.created_at, v2.updated_at, $fechaBaseExpr)";
                }

                if ($colNotaCredito) {
                    $caseFechaPartes[] = "WHEN c.$colNotaCredito IS NOT NULL THEN COALESCE(nc.created_at, nc.updated_at, nc.fecha, $fechaBaseExpr)";
                }

                if (!empty($caseFechaPartes)) {
                    $fechaOrdenExpr = 'CASE ' . implode(' ', $caseFechaPartes) . " ELSE $fechaBaseExpr END";
                } else {
                    $fechaOrdenExpr = $fechaBaseExpr;
                }
            }

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
            if ($colCompra) {
                $query->leftJoin('compra as co', 'co.id', '=', "c.$colCompra");
                $query->leftJoin('users as cu', 'cu.id', '=', 'co.users_id');
            }
            if ($colComprobante) {
                $query->leftJoin('comprovante_entrega as ce', 'ce.id', '=', "c.$colComprobante");
                $query->leftJoin('users as ceu', 'ceu.id', '=', 'ce.users_id');
            }
            if ($colVale1) {
                $query->leftJoin('vale as v1', 'v1.id', '=', "c.$colVale1");
                $query->leftJoin('users as v1u', 'v1u.id', '=', 'v1.users_id');
            }
            if ($colVale2) {
                $query->leftJoin('vale as v2', 'v2.id', '=', "c.$colVale2");
                $query->leftJoin('users as v2u', 'v2u.id', '=', 'v2.users_id');
            }
            if ($colNotaCredito) {
                $query->leftJoin('nota_credito as nc', 'nc.id', '=', "c.$colNotaCredito");
                $query->leftJoin('users as ncu', 'ncu.id', '=', 'nc.users_id');
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

            $usuarioExpr = 'NULL';
            if ($colUsuario) {
                $baseUsuarioExpr = "COALESCE(u.name, CAST(c.$colUsuario AS CHAR))";
                $usuarioCasePartes = [];

                if ($colCompra) {
                    $usuarioCasePartes[] = "WHEN c.$colCompra IS NOT NULL THEN COALESCE(cu.name, $baseUsuarioExpr)";
                }

                if ($colComprobante) {
                    $usuarioCasePartes[] = "WHEN c.$colComprobante IS NOT NULL THEN COALESCE(ceu.name, $baseUsuarioExpr)";
                }

                if ($colVale1) {
                    $usuarioCasePartes[] = "WHEN c.$colVale1 IS NOT NULL THEN COALESCE(v1u.name, $baseUsuarioExpr)";
                }

                if ($colVale2) {
                    $usuarioCasePartes[] = "WHEN c.$colVale2 IS NOT NULL THEN COALESCE(v2u.name, $baseUsuarioExpr)";
                }

                if ($colNotaCredito) {
                    $usuarioCasePartes[] = "WHEN c.$colNotaCredito IS NOT NULL THEN COALESCE(ncu.name, $baseUsuarioExpr)";
                }

                $usuarioExpr = !empty($usuarioCasePartes)
                    ? 'CASE ' . implode(' ', $usuarioCasePartes) . " ELSE $baseUsuarioExpr END"
                    : $baseUsuarioExpr;
            } elseif ($colUsuarioNombre) {
                $baseUsuarioExpr = "c.$colUsuarioNombre";
                $usuarioCasePartes = [];

                if ($colCompra) {
                    $usuarioCasePartes[] = "WHEN c.$colCompra IS NOT NULL THEN COALESCE(cu.name, $baseUsuarioExpr)";
                }

                if ($colComprobante) {
                    $usuarioCasePartes[] = "WHEN c.$colComprobante IS NOT NULL THEN COALESCE(ceu.name, $baseUsuarioExpr)";
                }

                if ($colVale1) {
                    $usuarioCasePartes[] = "WHEN c.$colVale1 IS NOT NULL THEN COALESCE(v1u.name, $baseUsuarioExpr)";
                }

                if ($colVale2) {
                    $usuarioCasePartes[] = "WHEN c.$colVale2 IS NOT NULL THEN COALESCE(v2u.name, $baseUsuarioExpr)";
                }

                if ($colNotaCredito) {
                    $usuarioCasePartes[] = "WHEN c.$colNotaCredito IS NOT NULL THEN COALESCE(ncu.name, $baseUsuarioExpr)";
                }

                $usuarioExpr = !empty($usuarioCasePartes)
                    ? 'CASE ' . implode(' ', $usuarioCasePartes) . " ELSE $baseUsuarioExpr END"
                    : $baseUsuarioExpr;
            } elseif ($colCompra) {
                $usuarioExpr = 'cu.name';
            } elseif ($colComprobante) {
                $usuarioExpr = 'ceu.name';
            } elseif ($colVale1) {
                $usuarioExpr = 'v1u.name';
            } elseif ($colVale2) {
                $usuarioExpr = 'v2u.name';
            } elseif ($colNotaCredito) {
                $usuarioExpr = 'ncu.name';
            }

            $query->select([
                DB::raw("DATE_FORMAT($fechaOrdenExpr, '%Y-%m-%d %H:%i:%s') as fechaIngreso"),
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
                DB::raw("$usuarioExpr as usuario"),
            ]);

            if ($filtroDesde !== '' && $filtroHasta !== '') {
                $query->whereBetween(DB::raw("DATE($fechaOrdenExpr)"), [$filtroDesde, $filtroHasta]);
            } elseif ($filtroDesde !== '') {
                $query->whereDate(DB::raw($fechaOrdenExpr), '>=', $filtroDesde);
            } elseif ($filtroHasta !== '') {
                $query->whereDate(DB::raw($fechaOrdenExpr), '<=', $filtroHasta);
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
                if ($usuarioExpr !== 'NULL') {
                    $usuarioNormalizado = mb_strtolower($filtroUsuario);
                    $query->whereRaw(
                        'LOWER(' . $usuarioExpr . ') LIKE ?',
                        ['%' . $usuarioNormalizado . '%']
                    );
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

            if ($tipoDocumento !== '') {
                switch ($tipoDocumento) {
                    case 'ajuste':
                        if ($colAjuste) {
                            if ($idDocumento !== '') {
                                $query->where("c.$colAjuste", $idDocumento);
                            } else {
                                $query->whereNotNull("c.$colAjuste");
                            }
                        }
                        break;
                    case 'compra':
                        if ($colCompra) {
                            if ($idDocumento !== '') {
                                $query->where("c.$colCompra", $idDocumento);
                            } else {
                                $query->whereNotNull("c.$colCompra");
                            }
                        }
                        break;
                    case 'comprobante':
                        if ($colComprobante) {
                            if ($idDocumento !== '') {
                                $query->where("c.$colComprobante", $idDocumento);
                            } else {
                                $query->whereNotNull("c.$colComprobante");
                            }
                        }
                        break;
                    case 'vale':
                        if ($colVale1 || $colVale2) {
                            $query->where(function ($q) use ($idDocumento, $colVale1, $colVale2) {
                                if ($idDocumento !== '') {
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
                                } else {
                                    if ($colVale1) {
                                        $q->whereNotNull("c.$colVale1");
                                    }
                                    if ($colVale2) {
                                        if ($colVale1) {
                                            $q->orWhereNotNull("c.$colVale2");
                                        } else {
                                            $q->whereNotNull("c.$colVale2");
                                        }
                                    }
                                }
                            });
                        }
                        break;
                    case 'nota_credito':
                        if ($colNotaCredito) {
                            if ($idDocumento !== '') {
                                $query->where("c.$colNotaCredito", $idDocumento);
                            } else {
                                $query->whereNotNull("c.$colNotaCredito");
                            }
                        }
                        break;
                }
            }

            $listaCardex = $query
                ->orderByRaw("$fechaOrdenExpr DESC")
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
