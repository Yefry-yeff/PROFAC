<?php

namespace App\Http\Livewire\FlujoDeVenta;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class VidaUtilDeUnFlujo extends Component
{
    public $titulo = 'Analítica Avanzada del Flujo';
    public $fechaDesde;
    public $fechaHasta;
    public $filtroCliente = '';
    public $filtroAsesor = '';
    public $filtroTeleasesor = '';
    public $filtroUsuario = '';
    public $filtroEtapa = '';
    public $filtroEstado = '';
    public $filtroTipoVenta = '';
    public $filtroOferta = '';
    public $filtroPrefactura = '';
    public $filtroFactura = '';
    public $filtroProducto = '';
    public $filtroMarca = '';
    public $filtroCategoria = '';
    public $filtroEquipo = '';
    public $catalogos = [];
    public $kpis = [];
    public $metricasEtapa = [];
    public $cuellosBotella = [];
    public $evolucion = [];
    public $metricasUsuario = [];
    public $flujos = [];
    public $detalleFlujo = null;
    public $calidadDatos = [];
    public $umbralesEditables = [];

    public function mount(): void
    {
        $this->fechaDesde = now()->subDays(30)->format('Y-m-d');
        $this->fechaHasta = now()->format('Y-m-d');
        $this->cargarCatalogos();
        $this->cargarUmbralesEditables();
        $this->cargarAnalitica();
    }

    public function render()
    {
        return view('livewire.flujodeventa.vidautildeunflujo');
    }

    public function aplicarFiltros(): void
    {
        $this->validate([
            'fechaDesde' => 'required|date',
            'fechaHasta' => 'required|date|after_or_equal:fechaDesde',
        ]);
        $this->cargarAnalitica();
        $this->dispatchBrowserEvent('flujo-analytics-updated', $this->datosGraficos());
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'filtroCliente', 'filtroAsesor', 'filtroTeleasesor', 'filtroUsuario',
            'filtroEtapa', 'filtroEstado', 'filtroTipoVenta', 'filtroOferta',
            'filtroPrefactura', 'filtroFactura', 'filtroProducto', 'filtroMarca',
            'filtroCategoria', 'filtroEquipo',
        ]);
        $this->fechaDesde = now()->subDays(30)->format('Y-m-d');
        $this->fechaHasta = now()->format('Y-m-d');
        $this->aplicarFiltros();
    }

    public function guardarUmbrales(): void
    {
        foreach ($this->umbralesEditables as $tipo => $limites) {
            $normal = max(0, (int) ($limites['normal'] ?? 0));
            $advertencia = max(0, (int) ($limites['advertencia'] ?? 0));
            if ($advertencia <= $normal) {
                $this->dispatchBrowserEvent('flujo-analytics-error', [
                    'mensaje' => 'El límite crítico debe ser mayor que el límite normal en todas las etapas.',
                ]);
                return;
            }
            DB::table('flujo_analitica_umbrales')->updateOrInsert(
                ['tipo_tramite_id' => (int) $tipo],
                [
                    'normal_minutos' => $normal,
                    'advertencia_minutos' => $advertencia,
                    'activo' => 1,
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]
            );
        }
        $this->cargarAnalitica();
        $this->dispatchBrowserEvent('flujo-analytics-updated', $this->datosGraficos());
        $this->dispatchBrowserEvent('flujo-thresholds-saved');
    }

    public function verFlujo(int $flujoId): void
    {
        $flujo = DB::table('flujo as f')
            ->leftJoin('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
            ->leftJoin('users as creador', 'creador.id', '=', 'f.created_by')
            ->where('f.id', $flujoId)
            ->first([
                'f.id', 'f.identificacion', 'f.nombre', 'f.cliente_rtn', 'f.estado_id',
                'f.created_at', 'f.updated_at', 'tt.nombre as etapa_actual',
                'creador.name as creado_por',
            ]);
        if (!$flujo) {
            $this->dispatchBrowserEvent('flujo-analytics-error', ['mensaje' => 'El flujo solicitado ya no existe.']);
            return;
        }

        $eventos = $this->consultarEventos(collect([$flujoId]))->get($flujoId, collect());
        $segmentos = $this->construirSegmentos($eventos, (int) $flujo->estado_id === 7);
        $ofertaIds = $eventos->where('tipo_tramite_id', 2)->pluck('tramite_id')->filter()->unique();
        $prefacturaIds = $eventos->where('tipo_tramite_id', 4)->pluck('tramite_id')->filter()->unique();
        $facturaIds = $eventos->where('tipo_tramite_id', 3)->pluck('tramite_id')->filter()->unique();

        $ofertas = $ofertaIds->isEmpty() ? collect() : DB::table('cotizacion as c')
            ->leftJoin('users as u', 'u.id', '=', 'c.users_id')
            ->leftJoin('users as asesor', 'asesor.id', '=', 'c.vendedor')->whereIn('c.id', $ofertaIds)
            ->get([
                'c.id', 'c.total', 'c.porc_descuento', 'c.monto_descuento', 'c.created_at',
                'c.updated_at', 'u.name as usuario', 'asesor.name as asesor',
                DB::raw('(SELECT COUNT(*) FROM cotizacion_has_producto chp WHERE chp.cotizacion_id = c.id) as productos'),
            ]);
        $prefacturas = $prefacturaIds->isEmpty() ? collect() : DB::table('prefactura as p')
            ->leftJoin('users as u', 'u.id', '=', 'p.users_id')->whereIn('p.id', $prefacturaIds)
            ->get(['p.id', 'p.estado', 'p.total', 'p.created_at', 'p.updated_at', 'u.name as usuario']);
        $facturas = $facturaIds->isEmpty() ? collect() : DB::table('factura as fa')
            ->leftJoin('users as u', 'u.id', '=', 'fa.users_id')
            ->leftJoin('aplicacion_pagos as ap', 'ap.factura_id', '=', 'fa.id')
            ->whereIn('fa.id', $facturaIds)
            ->get([
                'fa.id', 'fa.numero_factura', 'fa.total', 'fa.fecha_emision', 'fa.fecha_vencimiento',
                'fa.estado_venta_id', 'fa.created_at', 'u.name as usuario', 'ap.saldo', 'ap.fecha_cierre_factura',
            ]);
        $entregas = $facturaIds->isEmpty() ? collect() : DB::table('distribuciones_entrega_facturas as def')
            ->join('distribuciones_entrega as de', 'de.id', '=', 'def.distribucion_entrega_id')
            ->leftJoin('equipos_entrega as ee', 'ee.id', '=', 'de.equipo_entrega_id')
            ->whereIn('def.factura_id', $facturaIds)
            ->get([
                'def.factura_id', 'def.estado_entrega', 'def.fecha_entrega_real', 'def.observaciones',
                'def.motivo_anulacion', 'de.fecha_programada', 'de.hora_salida', 'de.hora_llegada',
                'ee.nombre_equipo',
            ]);
        $pagos = $facturaIds->isEmpty() ? collect() : DB::table('abonos_creditos as ac')
            ->leftJoin('users as u', 'u.id', '=', 'ac.usr_registro')
            ->whereIn('ac.factura_id', $facturaIds)->orderBy('ac.created_at')
            ->get([
                'ac.factura_id', 'ac.monto_abonado', 'ac.fecha_pago', 'ac.created_at',
                'ac.numero_recibo', 'ac.comentario', 'u.name as usuario',
            ]);
        $facturas = $facturas->map(function ($factura) use ($pagos) {
            $pagosFactura = $pagos->where('factura_id', $factura->id)->sortBy('created_at')->values();
            $primero = $pagosFactura->first();
            $ultimo = $pagosFactura->last();
            $emision = Carbon::parse($factura->created_at ?: $factura->fecha_emision);
            $fechaPrimerPago = $primero ? Carbon::parse($primero->created_at ?: $primero->fecha_pago) : null;
            $fechaPagoTotal = $factura->fecha_cierre_factura
                ? Carbon::parse($factura->fecha_cierre_factura)
                : (((float) ($factura->saldo ?? $factura->total) <= 0 && $ultimo) ? Carbon::parse($ultimo->created_at ?: $ultimo->fecha_pago) : null);
            $vencimiento = $factura->fecha_vencimiento ? Carbon::parse($factura->fecha_vencimiento)->endOfDay() : null;
            if (!$fechaPagoTotal) {
                $clasificacion = 'Pendiente de cobro';
            } elseif ($emision->isSameDay($fechaPagoTotal)) {
                $clasificacion = 'Cobrado inmediatamente';
            } elseif (!$vencimiento || $fechaPagoTotal->lte($vencimiento)) {
                $clasificacion = 'Cobrado dentro del plazo';
            } elseif ($fechaPagoTotal->diffInDays($vencimiento) <= 30) {
                $clasificacion = 'Cobro atrasado';
            } else {
                $clasificacion = 'Cobro crítico';
            }
            $factura->primer_pago = $fechaPrimerPago?->format('d/m/Y H:i:s');
            $factura->ultimo_pago = $ultimo ? Carbon::parse($ultimo->created_at ?: $ultimo->fecha_pago)->format('d/m/Y H:i:s') : null;
            $factura->cantidad_pagos = $pagosFactura->count();
            $factura->dias_primer_pago = $fechaPrimerPago ? $emision->diffInDays($fechaPrimerPago) : null;
            $factura->dias_pago_total = $fechaPagoTotal ? $emision->diffInDays($fechaPagoTotal) : null;
            $factura->clasificacion_cobro = $clasificacion;
            return $factura;
        });
        $codigos = $this->consultarCodigos($flujoId);
        $auditoria = Schema::hasTable('flujo_auditoria_eventos')
            ? DB::table('flujo_auditoria_eventos as fae')
                ->leftJoin('users as u', 'u.id', '=', 'fae.usuario_id')
                ->leftJoin('tipos_tramites as tt', 'tt.id', '=', 'fae.tipo_tramite_id')
                ->where('fae.flujo_id', $flujoId)->orderBy('fae.created_at')
                ->get([
                    'fae.id', 'fae.entidad_tipo', 'fae.entidad_id', 'fae.estado_anterior',
                    'fae.estado_nuevo', 'fae.fecha_hora_entrada', 'fae.fecha_hora_salida',
                    'fae.accion', 'fae.observacion', 'fae.created_at', 'u.name as usuario',
                    'tt.nombre as etapa',
                ])
            : collect();

        $this->detalleFlujo = [
            'flujo' => (array) $flujo,
            'duracion_operativa' => $this->formatearMinutos($this->minutosHastaEtapa($segmentos, 5)),
            'duracion_total' => $this->formatearMinutos($this->minutosTranscurridos($segmentos)),
            'segmentos' => $segmentos,
            'ofertas' => $this->aArray($ofertas),
            'prefacturas' => $this->aArray($prefacturas),
            'facturas' => $this->aArray($facturas),
            'entregas' => $this->aArray($entregas),
            'pagos' => $this->aArray($pagos),
            'codigos' => $this->aArray($codigos),
            'auditoria' => $this->aArray($auditoria),
        ];
        $this->dispatchBrowserEvent('flujo-analytics-detail-ready');
    }

    private function cargarAnalitica(): void
    {
        $flujos = $this->consultaFlujos()->get();
        $ids = $flujos->pluck('id');
        $eventosPorFlujo = $this->consultarEventos($ids);
        $umbrales = $this->obtenerUmbrales();
        $porEtapa = [];
        $porUsuario = [];
        $resumenFlujos = [];
        $evolucion = [];
        $totales = [];
        $hastaFactura = [];
        $hastaEntrega = [];
        $hastaCobro = [];
        $completados = 0;
        $reprocesosTotal = 0;

        foreach ($flujos as $flujo) {
            $eventos = $eventosPorFlujo->get($flujo->id, collect());
            $cerrado = (int) $flujo->estado_id === 7 || (int) $flujo->tipo_tramite_id === 8;
            $segmentos = $this->construirSegmentos($eventos, $cerrado, $umbrales);
            $total = $this->minutosTranscurridos($segmentos);
            $fecha = Carbon::parse($flujo->created_at)->format('Y-m-d');
            $reprocesos = 0;

            foreach ($segmentos as $segmento) {
                $tipo = $segmento['tipo_tramite_id'];
                if (!isset($porEtapa[$tipo])) {
                    $porEtapa[$tipo] = [
                        'tipo_tramite_id' => $tipo, 'etapa' => $segmento['etapa'],
                        'orden' => $segmento['orden'], 'duraciones' => [], 'flujos' => [],
                        'retrasados' => 0, 'reprocesos' => 0, 'devoluciones' => 0,
                    ];
                }
                $porEtapa[$tipo]['duraciones'][] = $segmento['minutos'];
                $porEtapa[$tipo]['flujos'][$flujo->id] = true;
                $porEtapa[$tipo]['retrasados'] += $segmento['semaforo'] === 'critico' ? 1 : 0;
                $porEtapa[$tipo]['devoluciones'] += $segmento['es_devolucion'] ? 1 : 0;
                if ($segmento['visita'] > 1) {
                    $porEtapa[$tipo]['reprocesos']++;
                    $reprocesos++;
                }

                $usuarioId = $segmento['usuario_id'];
                if ($usuarioId) {
                    if (!isset($porUsuario[$usuarioId])) {
                        $porUsuario[$usuarioId] = [
                            'usuario' => $segmento['usuario'], 'areas' => [], 'flujos' => [],
                            'duraciones' => [], 'reprocesos' => 0, 'devoluciones' => 0,
                        ];
                    }
                    $porUsuario[$usuarioId]['areas'][$segmento['etapa']] = true;
                    $porUsuario[$usuarioId]['flujos'][$flujo->id] = true;
                    $porUsuario[$usuarioId]['duraciones'][] = $segmento['minutos'];
                    $porUsuario[$usuarioId]['reprocesos'] += $segmento['visita'] > 1 ? 1 : 0;
                    $porUsuario[$usuarioId]['devoluciones'] += $segmento['es_devolucion'] ? 1 : 0;
                }
            }

            if ($cerrado) {
                $completados++;
                $totales[] = $total;
            }
            foreach ([[3, &$hastaFactura], [5, &$hastaEntrega], [6, &$hastaCobro]] as [$tipo, &$destino]) {
                $minutos = $this->minutosHastaEtapa($segmentos, $tipo);
                if ($minutos !== null) $destino[] = $minutos;
            }
            unset($destino);
            $reprocesosTotal += $reprocesos;
            $evolucion[$fecha] = $evolucion[$fecha] ?? ['total' => 0, 'cantidad' => 0];
            $evolucion[$fecha]['total'] += $total;
            $evolucion[$fecha]['cantidad']++;
            $resumenFlujos[] = [
                'id' => (int) $flujo->id, 'identificacion' => $flujo->identificacion,
                'cliente' => $flujo->nombre ?: 'Sin cliente', 'rtn' => $flujo->cliente_rtn,
                'etapa' => $flujo->etapa_actual ?: 'Sin etapa', 'estado' => $cerrado ? 'Finalizado' : 'Abierto',
                'inicio' => Carbon::parse($flujo->created_at)->format('d/m/Y H:i'),
                'ultima_actividad' => Carbon::parse($flujo->updated_at)->format('d/m/Y H:i'),
                'minutos' => $total, 'duracion' => $this->formatearMinutos($total),
                'reprocesos' => $reprocesos, 'semaforo' => $this->semaforoGeneral($segmentos),
            ];
        }

        $this->metricasEtapa = $this->resumirEtapas($porEtapa, $umbrales);
        $this->cuellosBotella = collect($this->metricasEtapa)->sortByDesc('promedio_minutos')->take(5)->values()->all();
        $this->metricasUsuario = $this->resumirUsuarios($porUsuario);
        $this->evolucion = collect($evolucion)->map(function ($dato, $fecha) {
            return [
                'fecha' => Carbon::parse($fecha)->format('d/m'), 'cantidad' => $dato['cantidad'],
                'promedio_minutos' => (int) round($dato['total'] / max(1, $dato['cantidad'])),
            ];
        })->values()->all();

        $codigos = $ids->isEmpty() || !Schema::hasTable('codigo_autorizacion') ? collect() : DB::table('codigo_autorizacion')
            ->whereIn('flujo_id', $ids)->where('tipo_tramite', 'facturacion')
            ->get(['flujo_id', 'estado_codigo_id', 'created_at', 'updated_at', 'fecha_utilizacion']);
        $esperasCodigo = $codigos->map(function ($codigo) {
            $fin = $codigo->fecha_utilizacion ?: ((int) $codigo->estado_codigo_id === 2 ? $codigo->updated_at : null);
            return $fin ? Carbon::parse($codigo->created_at)->diffInMinutes(Carbon::parse($fin)) : null;
        })->filter(fn ($valor) => $valor !== null)->values()->all();

        usort($resumenFlujos, fn ($a, $b) => $b['minutos'] <=> $a['minutos']);
        $this->flujos = array_slice($resumenFlujos, 0, 100);
        $this->kpis = [
            'total' => $flujos->count(), 'completados' => $completados,
            'abiertos' => $flujos->count() - $completados,
            'promedio_total' => $this->formatearMinutos($this->promedio($totales)),
            'promedio_factura' => $this->formatearMinutos($this->promedio($hastaFactura)),
            'promedio_entrega' => $this->formatearMinutos($this->promedio($hastaEntrega)),
            'promedio_cobro' => $this->formatearMinutos($this->promedio($hastaCobro)),
            'reprocesos' => $reprocesosTotal,
            'devoluciones' => array_sum(array_column($this->metricasEtapa, 'devoluciones')),
            'solicitudes_codigo' => $codigos->count(),
            'flujos_con_codigo' => $codigos->pluck('flujo_id')->unique()->count(),
            'espera_codigo' => $this->formatearMinutos($this->promedio($esperasCodigo)),
            'flujo_rapido' => empty($totales) ? 'Sin datos' : $this->formatearMinutos(min($totales)),
            'flujo_lento' => empty($totales) ? 'Sin datos' : $this->formatearMinutos(max($totales)),
        ];
        $this->calidadDatos = [
            'eventos' => $eventosPorFlujo->flatten(1)->count(),
            'metodo' => 'Tiempos reconstruidos con fecha y hora de created_at/updated_at. Cola y procesamiento se separan únicamente cuando existe un evento de inicio explícito.',
        ];
    }

    private function consultaFlujos()
    {
        $query = DB::table('flujo as f')
            ->leftJoin('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
            ->whereBetween('f.created_at', [Carbon::parse($this->fechaDesde)->startOfDay(), Carbon::parse($this->fechaHasta)->endOfDay()])
            ->select(['f.id', 'f.identificacion', 'f.nombre', 'f.cliente_rtn', 'f.tipo_tramite_id', 'f.estado_id', 'f.created_at', 'f.updated_at', 'tt.nombre as etapa_actual']);
        if ($this->filtroCliente !== '') {
            $texto = '%' . trim($this->filtroCliente) . '%';
            $query->where(fn ($q) => $q->where('f.nombre', 'like', $texto)->orWhere('f.cliente_rtn', 'like', $texto));
        }
        if ($this->filtroEtapa !== '') $query->where('f.tipo_tramite_id', (int) $this->filtroEtapa);
        if ($this->filtroEstado !== '') $query->where('f.estado_id', (int) $this->filtroEstado);
        if ($this->filtroUsuario !== '') {
            $usuario = (int) $this->filtroUsuario;
            $query->whereExists(fn ($sub) => $sub->selectRaw('1')->from('historico_flujo as hfu')->whereColumn('hfu.flujo_id', 'f.id')->where('hfu.created_by', $usuario));
        }
        $this->aplicarFiltrosDocumentales($query);
        return $query->orderByDesc('f.created_at');
    }

    private function aplicarFiltrosDocumentales($query): void
    {
        foreach ([[2, $this->filtroOferta], [4, $this->filtroPrefactura], [3, $this->filtroFactura]] as [$tipo, $valor]) {
            $valor = trim((string) $valor);
            if ($valor === '') continue;
            $query->whereExists(fn ($sub) => $sub->selectRaw('1')->from('historico_flujo as hfd')->whereColumn('hfd.flujo_id', 'f.id')->where('hfd.tipo_tramite_id', $tipo)->where('hfd.tramite_id', (int) $valor));
        }
        $asesor = (int) $this->filtroAsesor;
        $teleasesor = (int) $this->filtroTeleasesor;
        $tipoVenta = (int) $this->filtroTipoVenta;
        if ($asesor || $teleasesor || $tipoVenta) {
            $query->whereExists(function ($sub) use ($asesor, $teleasesor, $tipoVenta) {
                $sub->selectRaw('1')->from('historico_flujo as hfo')->join('cotizacion as co', 'co.id', '=', 'hfo.tramite_id')
                    ->whereColumn('hfo.flujo_id', 'f.id')->where('hfo.tipo_tramite_id', 2);
                if ($asesor) $sub->where('co.vendedor', $asesor);
                if ($teleasesor) $sub->where('co.users_id', $teleasesor);
                if ($tipoVenta) $sub->where('co.tipo_venta_id', $tipoVenta);
            });
        }
        $producto = trim((string) $this->filtroProducto);
        $marca = (int) $this->filtroMarca;
        $categoria = (int) $this->filtroCategoria;
        if ($producto !== '' || $marca || $categoria) {
            $query->whereExists(function ($sub) use ($producto, $marca, $categoria) {
                $sub->selectRaw('1')->from('historico_flujo as hfp')
                    ->join('cotizacion_has_producto as chp', 'chp.cotizacion_id', '=', 'hfp.tramite_id')
                    ->join('producto as prod', 'prod.id', '=', 'chp.producto_id')
                    ->leftJoin('precios_producto_carga as ppc', 'ppc.id', '=', 'chp.precios_producto_carga_id')
                    ->whereColumn('hfp.flujo_id', 'f.id')->where('hfp.tipo_tramite_id', 2);
                if ($producto !== '') {
                    $like = '%' . $producto . '%';
                    $sub->where(fn ($q) => $q->where('prod.id', (int) $producto)->orWhere('prod.nombre', 'like', $like)->orWhere('prod.codigo_barra', 'like', $like));
                }
                if ($marca) $sub->where('ppc.marca_id', $marca);
                if ($categoria) $sub->where('ppc.categoria_producto_id', $categoria);
            });
        }
        if ($this->filtroEquipo !== '') {
            $equipo = (int) $this->filtroEquipo;
            $query->whereExists(function ($sub) use ($equipo) {
                $sub->selectRaw('1')->from('historico_flujo as hfe')
                    ->join('distribuciones_entrega_facturas as def', 'def.factura_id', '=', 'hfe.tramite_id')
                    ->join('distribuciones_entrega as de', 'de.id', '=', 'def.distribucion_entrega_id')
                    ->whereColumn('hfe.flujo_id', 'f.id')->where('hfe.tipo_tramite_id', 3)->where('de.equipo_entrega_id', $equipo);
            });
        }
    }

    private function consultarEventos(Collection $ids): Collection
    {
        if ($ids->isEmpty()) return collect();
        return DB::table('historico_flujo as hf')
            ->leftJoin('tipos_tramites as tt', 'tt.id', '=', 'hf.tipo_tramite_id')
            ->leftJoin('flujo_etapas as fe', 'fe.tipo_tramite_id', '=', 'hf.tipo_tramite_id')
            ->leftJoin('users as u', 'u.id', '=', 'hf.created_by')
            ->whereIn('hf.flujo_id', $ids)->orderBy('hf.flujo_id')->orderBy('hf.created_at')->orderBy('hf.id')
            ->get([
                'hf.id', 'hf.flujo_id', 'hf.tramite_id', 'hf.tipo_tramite_id', 'hf.estado_id', 'hf.observaciones',
                'hf.created_by', 'hf.updated_by', 'hf.created_at', 'hf.updated_at', 'tt.nombre as tramite',
                'fe.nombre_display', 'fe.orden', 'fe.icono', 'u.name as usuario',
            ])->groupBy('flujo_id');
    }

    private function construirSegmentos(Collection $eventos, bool $cerrado, array $umbrales = []): array
    {
        $segmentos = [];
        $visitas = [];
        $eventos = $eventos->values();
        $ultimoInicio = $eventos->isEmpty() ? null : Carbon::parse($eventos->max('created_at'));
        foreach ($eventos as $indice => $evento) {
            $tipo = (int) $evento->tipo_tramite_id;
            $inicio = Carbon::parse($evento->created_at);
            $actualizacion = Carbon::parse($evento->updated_at ?: $evento->created_at);
            $siguiente = isset($eventos[$indice + 1]) ? Carbon::parse($eventos[$indice + 1]->created_at) : null;
            $pendienteParalelo = !$cerrado && (int) $evento->estado_id === 5 && $ultimoInicio && $inicio->eq($ultimoInicio);
            $salida = $actualizacion->gt($inicio)
                ? $actualizacion
                : ($pendienteParalelo
                    ? now()
                    : (($siguiente && $siguiente->gt($inicio)) ? $siguiente : ((!$cerrado && $indice === $eventos->count() - 1) ? now() : $inicio->copy())));
            $minutos = max(0, $inicio->diffInMinutes($salida));
            $visitas[$tipo] = ($visitas[$tipo] ?? 0) + 1;
            $limites = $umbrales[$tipo] ?? $this->umbralPredeterminado($tipo);
            $texto = strtolower((string) $evento->observaciones);
            $segmentos[] = [
                'id' => (int) $evento->id, 'tipo_tramite_id' => $tipo,
                'etapa' => $evento->nombre_display ?: ucfirst((string) $evento->tramite),
                'icono' => $evento->icono ?: 'fa-circle', 'orden' => (int) ($evento->orden ?: 99),
                'entrada' => $inicio->format('d/m/Y H:i:s'),
                'salida' => $salida->gt($inicio) ? $salida->format('d/m/Y H:i:s') : null,
                'entrada_ts' => $inicio->timestamp,
                'salida_ts' => $salida->timestamp,
                'minutos' => $minutos, 'duracion' => $this->formatearMinutos($minutos),
                'usuario_id' => $evento->created_by ? (int) $evento->created_by : null,
                'usuario' => $evento->usuario ?: 'Sin registro', 'estado_id' => (int) $evento->estado_id,
                'estado' => $this->nombreEstado((int) $evento->estado_id), 'observacion' => $evento->observaciones ?: 'Sin observaciones',
                'visita' => $visitas[$tipo],
                'semaforo' => $minutos > $limites['advertencia'] ? 'critico' : ($minutos > $limites['normal'] ? 'advertencia' : 'normal'),
                'es_devolucion' => str_contains($texto, 'devol') || str_contains($texto, 'rechaz') || str_contains($texto, 'regres'),
                'calidad' => $actualizacion->gt($inicio) ? 'registrado' : 'reconstruido',
            ];
        }
        return $segmentos;
    }

    private function resumirEtapas(array $datosEtapas, array $umbrales): array
    {
        $salida = [];
        foreach ($datosEtapas as $tipo => $datos) {
            sort($datos['duraciones']);
            $cantidad = count($datos['duraciones']);
            $normal = ($umbrales[$tipo] ?? $this->umbralPredeterminado($tipo))['normal'];
            $dentro = count(array_filter($datos['duraciones'], fn ($valor) => $valor <= $normal));
            $salida[] = [
                'tipo_tramite_id' => $tipo, 'etapa' => $datos['etapa'], 'orden' => $datos['orden'],
                'procesos' => count($datos['flujos']), 'promedio_minutos' => $this->promedio($datos['duraciones']),
                'promedio' => $this->formatearMinutos($this->promedio($datos['duraciones'])),
                'minimo' => $this->formatearMinutos($cantidad ? min($datos['duraciones']) : 0),
                'maximo' => $this->formatearMinutos($cantidad ? max($datos['duraciones']) : 0),
                'mediana' => $this->formatearMinutos($this->mediana($datos['duraciones'])),
                'retrasados' => $datos['retrasados'], 'cumplimiento' => $cantidad ? round(($dentro / $cantidad) * 100, 1) : 0,
                'devoluciones' => $datos['devoluciones'], 'reprocesos' => $datos['reprocesos'],
            ];
        }
        usort($salida, fn ($a, $b) => $a['orden'] <=> $b['orden']);
        return $salida;
    }

    private function resumirUsuarios(array $datosUsuarios): array
    {
        $salida = [];
        foreach ($datosUsuarios as $datos) {
            sort($datos['duraciones']);
            $salida[] = [
                'usuario' => $datos['usuario'], 'area' => implode(', ', array_slice(array_keys($datos['areas']), 0, 2)),
                'procesos' => count($datos['flujos']), 'promedio' => $this->formatearMinutos($this->promedio($datos['duraciones'])),
                'rapido' => $this->formatearMinutos(empty($datos['duraciones']) ? 0 : min($datos['duraciones'])),
                'lento' => $this->formatearMinutos(empty($datos['duraciones']) ? 0 : max($datos['duraciones'])),
                'reprocesos' => $datos['reprocesos'], 'devoluciones' => $datos['devoluciones'],
                'promedio_minutos' => $this->promedio($datos['duraciones']),
            ];
        }
        usort($salida, fn ($a, $b) => $b['procesos'] <=> $a['procesos']);
        return array_slice($salida, 0, 20);
    }

    private function cargarCatalogos(): void
    {
        $this->catalogos = [
            'etapas' => $this->aArray(DB::table('flujo_etapas')->where('activo', 1)->orderBy('orden')->get(['tipo_tramite_id as id', 'nombre_display as nombre'])),
            'usuarios' => $this->aArray(DB::table('users')->where('estado_id', 1)->orderBy('name')->get(['id', 'name as nombre'])),
            'tipos_venta' => $this->aArray(DB::table('tipo_venta')->orderBy('id')->get(['id', 'descripcion as nombre'])),
            'marcas' => $this->aArray(DB::table('marca')->orderBy('nombre')->get(['id', 'nombre'])),
            'categorias' => $this->aArray(DB::table('categoria_producto')->orderBy('descripcion')->get(['id', 'descripcion as nombre'])),
            'equipos' => $this->aArray(DB::table('equipos_entrega')->where('estado_id', 1)->orderBy('nombre_equipo')->get(['id', 'nombre_equipo as nombre'])),
        ];
    }

    private function obtenerUmbrales(): array
    {
        if (!Schema::hasTable('flujo_analitica_umbrales')) return [];
        return DB::table('flujo_analitica_umbrales')->where('activo', 1)->get()->mapWithKeys(fn ($row) => [(int) $row->tipo_tramite_id => ['normal' => (int) $row->normal_minutos, 'advertencia' => (int) $row->advertencia_minutos]])->all();
    }

    private function cargarUmbralesEditables(): void
    {
        $guardados = $this->obtenerUmbrales();
        foreach ($this->catalogos['etapas'] as $etapa) {
            $tipo = (int) $etapa['id'];
            $limites = $guardados[$tipo] ?? $this->umbralPredeterminado($tipo);
            $this->umbralesEditables[$tipo] = [
                'nombre' => $etapa['nombre'],
                'normal' => $limites['normal'],
                'advertencia' => $limites['advertencia'],
            ];
        }
    }

    private function consultarCodigos(int $flujoId): Collection
    {
        if (!Schema::hasTable('codigo_autorizacion')) return collect();
        return DB::table('codigo_autorizacion as ca')->leftJoin('users as u', 'u.id', '=', 'ca.users_id')
            ->where('ca.flujo_id', $flujoId)->orderBy('ca.created_at')
            ->get(['ca.id', 'ca.tipo_tramite', 'ca.estado_codigo_id', 'ca.created_at', 'ca.fecha_utilizacion', 'ca.updated_at', 'u.name as usuario'])
            ->map(function ($codigo) {
                $fin = $codigo->fecha_utilizacion ?: ((int) $codigo->estado_codigo_id === 2 ? $codigo->updated_at : null);
                $codigo->espera = $fin ? $this->formatearMinutos(Carbon::parse($codigo->created_at)->diffInMinutes(Carbon::parse($fin))) : 'Pendiente';
                return $codigo;
            });
    }

    private function datosGraficos(): array
    {
        return [
            'etapas' => $this->metricasEtapa, 'evolucion' => $this->evolucion,
            'kpis' => $this->kpis,
            'reprocesos' => array_map(fn ($etapa) => ['etapa' => $etapa['etapa'], 'cantidad' => $etapa['reprocesos']], $this->metricasEtapa),
        ];
    }

    private function umbralPredeterminado(int $tipo): array
    {
        return match ($tipo) {
            2, 9, 4 => ['normal' => 30, 'advertencia' => 60],
            10, 3 => ['normal' => 60, 'advertencia' => 120],
            5 => ['normal' => 120, 'advertencia' => 240],
            6 => ['normal' => 1440, 'advertencia' => 4320],
            default => ['normal' => 60, 'advertencia' => 120],
        };
    }

    private function minutosHastaEtapa(array $segmentos, int $tipo): ?int
    {
        if (empty($segmentos)) return null;
        $inicio = min(array_column($segmentos, 'entrada_ts'));
        foreach ($segmentos as $segmento) {
            if ($segmento['tipo_tramite_id'] === $tipo) {
                return max(0, (int) floor(($segmento['entrada_ts'] - $inicio) / 60));
            }
        }
        return null;
    }

    private function minutosTranscurridos(array $segmentos): int
    {
        if (empty($segmentos)) return 0;
        $inicio = min(array_column($segmentos, 'entrada_ts'));
        $fin = max(array_column($segmentos, 'salida_ts'));
        return max(0, (int) floor(($fin - $inicio) / 60));
    }

    private function semaforoGeneral(array $segmentos): string
    {
        if (collect($segmentos)->contains(fn ($segmento) => $segmento['semaforo'] === 'critico')) return 'critico';
        if (collect($segmentos)->contains(fn ($segmento) => $segmento['semaforo'] === 'advertencia')) return 'advertencia';
        return 'normal';
    }

    private function nombreEstado(int $estado): string
    {
        return [1 => 'Activo', 2 => 'Anulado', 3 => 'Devolución', 4 => 'Vencido', 5 => 'Pendiente', 7 => 'Cancelado'][$estado] ?? 'Estado ' . $estado;
    }

    private function promedio(array $valores): int
    {
        return empty($valores) ? 0 : (int) round(array_sum($valores) / count($valores));
    }

    private function mediana(array $valores): int
    {
        $cantidad = count($valores);
        if (!$cantidad) return 0;
        $medio = intdiv($cantidad, 2);
        return $cantidad % 2 ? (int) $valores[$medio] : (int) round(($valores[$medio - 1] + $valores[$medio]) / 2);
    }

    private function formatearMinutos(?int $minutos): string
    {
        if ($minutos === null) return 'Sin datos';
        if ($minutos < 60) return $minutos . ' min';
        if ($minutos < 1440) return intdiv($minutos, 60) . ' h ' . ($minutos % 60) . ' min';
        return intdiv($minutos, 1440) . ' d ' . intdiv($minutos % 1440, 60) . ' h';
    }

    private function aArray(Collection $collection): array
    {
        return $collection->map(fn ($row) => (array) $row)->values()->all();
    }
}
