<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardComercial extends Component
{
    // ─── Filtros ────────────────────────────────────────────────────────────
    public int    $filtroMes;
    public int    $filtroAnio;
    public string $fechaInicio  = '';
    public string $fechaFin     = '';
    public        $filtroVendedorId   = '';
    public        $filtroClienteId    = '';
    public string $filtroEstadoCliente = '';

    // Top clientes
    public int    $topLimit       = 10;

    // Productividad
    public string $periodoProductividad = 'mensual';

    // Participación — eje del gráfico pastel
    public string $participacionEje = 'vendedor';

    // Periodo label para mostrar
    public string $periodoLabel = '';

    // Modo oscuro
    public bool $darkMode = false;

    // Rol del usuario (cacheado en mount)
    public int    $rolId   = 0;
    public string $rolNombre = '';

    // Vista seleccionada por el administrador (rol 1)
    public string $vistaAdmin = 'gerencia';

    /** @var array<string,string> Mapa rol_id → vista parcial */
    protected array $viewMap = [
        1  => 'dashboard.gerencia',
        2  => 'dashboard.vendedor',
        3  => 'dashboard.televendedor',
        4  => 'dashboard.cobros',
        5  => 'dashboard.aux-admin',
        6  => 'dashboard.aux-contable',
        7  => 'dashboard.logistica',
        8  => 'dashboard.rrhh',
        9  => 'dashboard.mercadeo',
        10 => 'dashboard.picking',
        11 => 'dashboard.logistica',
        12 => 'dashboard.default',
        13 => 'dashboard.default',
        14 => 'dashboard.televendedor',
        15 => 'dashboard.vendedor',
        16 => 'dashboard.logistica',
        17 => 'dashboard.logistica',
        18 => 'dashboard.auditoria',
        19 => 'dashboard.gerencia',
    ];

    protected static array $MESES = [
        '', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
    ];

    // ─── Mount ──────────────────────────────────────────────────────────────
    public function mount(): void
    {
        $user            = Auth::user();
        $this->rolId     = (int) ($user->rol_id ?? 0);
        $this->rolNombre = optional($user->rol)->nombre ?? 'Sin rol';

        $this->filtroMes  = now()->month;
        $this->filtroAnio = now()->year;
        $this->fechaInicio = now()->startOfMonth()->toDateString();
        $this->fechaFin    = now()->endOfMonth()->toDateString();
        $this->actualizarPeriodoLabel();

        // Si es Asesor Comercial / Televendedor, pre-filtrar por su propio usuario
        if (in_array($this->rolId, [2, 3, 14, 15])) {
            $this->filtroVendedorId = $user->id;
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────
    private function actualizarPeriodoLabel(): void
    {
        $this->periodoLabel = self::$MESES[$this->filtroMes] . ' ' . $this->filtroAnio;
    }

    /** Vistas disponibles para el selector del administrador */
    public static array $adminViewOptions = [
        'gerencia'     => ['label' => 'Gerencia',         'icon' => 'fa-tachometer'],
        'vendedor'     => ['label' => 'Asesor Comercial', 'icon' => 'fa-briefcase'],
        'televendedor' => ['label' => 'Tele Asesor',      'icon' => 'fa-phone'],
        'cobros'       => ['label' => 'Cobros',           'icon' => 'fa-money'],
        'aux-admin'    => ['label' => 'Aux. Admin.',      'icon' => 'fa-cogs'],
        'aux-contable' => ['label' => 'Aux. Contable',    'icon' => 'fa-calculator'],
        'logistica'    => ['label' => 'Logística',        'icon' => 'fa-truck'],
        'rrhh'         => ['label' => 'RRHH',             'icon' => 'fa-users'],
        'mercadeo'     => ['label' => 'Marca',            'icon' => 'fa-cubes'],
        'auditoria'    => ['label' => 'Auditoría',        'icon' => 'fa-search'],
    ];

    /** Devuelve el nombre de la vista a cargar según rol */
    public function getRolView(): string
    {
        if ($this->rolId === 1) {
            $map = [
                'gerencia'     => 'dashboard.gerencia',
                'vendedor'     => 'dashboard.vendedor',
                'televendedor' => 'dashboard.televendedor',
                'cobros'       => 'dashboard.cobros',
                'aux-admin'    => 'dashboard.aux-admin',
                'aux-contable' => 'dashboard.aux-contable',
                'logistica'    => 'dashboard.logistica',
                'rrhh'         => 'dashboard.rrhh',
                'mercadeo'     => 'dashboard.mercadeo',
                'auditoria'    => 'dashboard.auditoria',
            ];
            return $map[$this->vistaAdmin] ?? 'dashboard.gerencia';
        }
        return $this->viewMap[$this->rolId] ?? 'dashboard.default';
    }

    // Actualizar fechas cuando cambia mes/año
    public function updatedFiltroMes(): void
    {
        $this->recalcularFechas();
    }

    public function updatedFiltroAnio(): void
    {
        $this->recalcularFechas();
    }

    private function recalcularFechas(): void
    {
        $base = Carbon::createFromDate($this->filtroAnio, $this->filtroMes, 1);
        $this->fechaInicio = $base->startOfMonth()->toDateString();
        $this->fechaFin    = $base->copy()->endOfMonth()->toDateString();
        $this->actualizarPeriodoLabel();
    }

    // ─── Render ──────────────────────────────────────────────────────────────
    public function render()
    {
        $rolView   = $this->getRolView();
        $vendedores = $this->getVendedores();

        // Datos según rol
        $kpis              = $this->getKpis();
        $topClientes       = $this->getTopClientes();
        $tendencia         = $this->getTendenciaMensual();
        $productividad     = $this->getProductividadVendedores();
        $participacion     = $this->getParticipacionMercado();
        $comparativa       = $this->getComparativaMesAnterior();
        $kpisCobros        = in_array($this->rolId, [1, 4, 5, 6, 18, 19]) ? $this->getKpisCobros() : [];
        $kpisInventario    = in_array($this->rolId, [1, 5, 9, 18]) ? $this->getKpisInventario() : [];
        $evolucion         = $this->getEvolucionHistorica();

        return view('livewire.dashboard.dashboard-comercial', compact(
            'rolView',
            'vendedores',
            'kpis',
            'topClientes',
            'tendencia',
            'productividad',
            'participacion',
            'comparativa',
            'kpisCobros',
            'kpisInventario',
            'evolucion',
        ))->layout('layouts.app', ['title' => 'Dashboard Comercial']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  DATOS COMPARTIDOS
    // ─────────────────────────────────────────────────────────────────────────

    public function getVendedores(): array
    {
        return Cache::remember('dashboard_vendedores', 300, function () {
            return DB::table('users as u')
                ->join('rol as r', 'r.id', '=', 'u.rol_id')
                ->whereIn('u.rol_id', [2, 3, 14, 15])
                ->where('u.estado_id', 1)
                ->orderBy('u.name')
                ->get(['u.id', 'u.name'])
                ->toArray();
        });
    }

    // ─── KPIs principales ───────────────────────────────────────────────────
    public function getKpis(): array
    {
        $fi = $this->fechaInicio;
        $ff = $this->fechaFin;

        $cacheKey = "dashboard_kpis_{$fi}_{$ff}_{$this->filtroVendedorId}_{$this->filtroClienteId}";

        return Cache::remember($cacheKey, 120, function () use ($fi, $ff) {
            $q = DB::table('factura as f')
                ->whereBetween('f.fecha_emision', [$fi, $ff])
                ->where('f.estado_venta_id', 1);

            if ($this->filtroVendedorId) $q->where('f.vendedor', $this->filtroVendedorId);
            if ($this->filtroClienteId)  $q->where('f.cliente_id', $this->filtroClienteId);

            $ventaTotal  = (float) (clone $q)->sum('f.total');
            $numFacturas = (int)   (clone $q)->count();
            $clientesAct = (int)   (clone $q)->distinct()->count('f.cliente_id');
            $ticketProm  = $numFacturas > 0 ? round($ventaTotal / $numFacturas, 2) : 0;

            // Clientes nuevos: primer factura en el período
            $clientesNuevos = (int) DB::table('factura as f')
                ->where('f.estado_venta_id', 1)
                ->whereBetween('f.fecha_emision', [$fi, $ff])
                ->whereNotIn('f.cliente_id', function ($sub) use ($fi) {
                    $sub->select('cliente_id')
                        ->from('factura')
                        ->where('estado_venta_id', 1)
                        ->where('fecha_emision', '<', $fi);
                })
                ->distinct()
                ->count('f.cliente_id');

            // Período anterior para variación
            $diasPeriodo = max(1, Carbon::parse($fi)->diffInDays(Carbon::parse($ff)) + 1);
            $fiPrev = Carbon::parse($fi)->subDays($diasPeriodo)->toDateString();
            $ffPrev = Carbon::parse($fi)->subDay()->toDateString();

            $qPrev = DB::table('factura')
                ->where('estado_venta_id', 1)
                ->whereBetween('fecha_emision', [$fiPrev, $ffPrev]);
            if ($this->filtroVendedorId) $qPrev->where('vendedor', $this->filtroVendedorId);

            $ventaAnterior = (float) (clone $qPrev)->sum('total');
            $variacion = $ventaAnterior > 0
                ? round((($ventaTotal - $ventaAnterior) / $ventaAnterior) * 100, 1)
                : null;

            return [
                'venta_total'    => $ventaTotal,
                'num_facturas'   => $numFacturas,
                'clientes_act'   => $clientesAct,
                'clientes_nuevos'=> $clientesNuevos,
                'ticket_prom'    => $ticketProm,
                'variacion'      => $variacion,
                'venta_anterior' => $ventaAnterior,
            ];
        });
    }

    // ─── Top Clientes ────────────────────────────────────────────────────────
    public function getTopClientes(): array
    {
        $fi    = $this->fechaInicio;
        $ff    = $this->fechaFin;
        $limit = $this->topLimit;
        $vid   = $this->filtroVendedorId;
        $cacheKey = "dash_top_clientes_{$fi}_{$ff}_{$limit}_{$vid}";

        return Cache::remember($cacheKey, 120, function () use ($fi, $ff, $limit) {
            // Mes anterior
            $diasPeriodo = max(1, Carbon::parse($fi)->diffInDays(Carbon::parse($ff)) + 1);
            $fiPrev = Carbon::parse($fi)->subDays($diasPeriodo)->toDateString();
            $ffPrev = Carbon::parse($fi)->subDay()->toDateString();

            $vendWhere  = $this->filtroVendedorId ? "AND f.vendedor = {$this->filtroVendedorId}" : '';
            $totalPeriod = DB::table('factura')
                ->where('estado_venta_id', 1)
                ->whereBetween('fecha_emision', [$fi, $ff])
                ->sum('total') ?: 1;

            $rows = DB::SELECT("
                SELECT
                    cli.id                                           AS codigo,
                    cli.nombre                                       AS nombre,
                    COALESCE(SUM(f.total),0)                         AS total_actual,
                    COUNT(f.id)                                      AS num_facturas,
                    MAX(f.fecha_emision)                             AS ultima_compra,
                    ROUND(COALESCE(SUM(f.total),0) / {$totalPeriod} * 100, 2) AS participacion,
                    COALESCE((
                        SELECT SUM(fp.total)
                        FROM factura fp
                        WHERE fp.cliente_id = cli.id
                          AND fp.estado_venta_id = 1
                          AND fp.fecha_emision BETWEEN '{$fiPrev}' AND '{$ffPrev}'
                          {$vendWhere}
                    ),0)                                             AS total_anterior
                FROM factura f
                INNER JOIN cliente cli ON cli.id = f.cliente_id
                WHERE f.estado_venta_id = 1
                  AND f.fecha_emision BETWEEN '{$fi}' AND '{$ff}'
                  {$vendWhere}
                GROUP BY cli.id, cli.nombre
                ORDER BY total_actual DESC
                LIMIT {$limit}
            ");

            return array_map(function ($r) {
                $variacion = $r->total_anterior > 0
                    ? round((($r->total_actual - $r->total_anterior) / $r->total_anterior) * 100, 1)
                    : null;

                // Calcular días sin compra (comparado con fecha actual)
                $diasSinCompra = $r->ultima_compra
                    ? Carbon::parse($r->ultima_compra)->diffInDays(now())
                    : 9999;

                $requiereAtencion = ($variacion !== null && $variacion <= -20)
                    || $diasSinCompra > 90;

                return [
                    'codigo'            => $r->codigo,
                    'nombre'            => $r->nombre,
                    'total_actual'      => (float) $r->total_actual,
                    'num_facturas'      => (int)   $r->num_facturas,
                    'ultima_compra'     => $r->ultima_compra,
                    'participacion'     => (float) $r->participacion,
                    'total_anterior'    => (float) $r->total_anterior,
                    'variacion'         => $variacion,
                    'requiere_atencion' => $requiereAtencion,
                ];
            }, $rows);
        });
    }

    // ─── Productividad Vendedores ────────────────────────────────────────────
    public function getProductividadVendedores(): array
    {
        $fi    = $this->fechaInicio;
        $ff    = $this->fechaFin;
        $cacheKey = "dash_productividad_{$fi}_{$ff}_{$this->periodoProductividad}";

        return Cache::remember($cacheKey, 120, function () use ($fi, $ff) {
            $rows = DB::SELECT("
                SELECT
                    u.name                          AS vendedor,
                    COALESCE(SUM(f.total),0)        AS venta_total,
                    COUNT(DISTINCT f.id)            AS num_facturas,
                    COUNT(DISTINCT f.cliente_id)    AS clientes_atendidos
                FROM users u
                LEFT JOIN factura f
                    ON f.vendedor = u.id
                    AND f.estado_venta_id = 1
                    AND f.fecha_emision BETWEEN '{$fi}' AND '{$ff}'
                WHERE u.rol_id IN (2,3,14,15)
                  AND u.estado_id = 1
                GROUP BY u.id, u.name
                ORDER BY venta_total DESC
            ");

            return array_map(fn($r) => [
                'vendedor'          => $r->vendedor,
                'venta_total'       => (float) $r->venta_total,
                'num_facturas'      => (int)   $r->num_facturas,
                'clientes_atendidos'=> (int)   $r->clientes_atendidos,
            ], $rows);
        });
    }

    // ─── Tendencia mensual (últimos 12 meses) ───────────────────────────────
    public function getTendenciaMensual(): array
    {
        $vendWhere = $this->filtroVendedorId
            ? "AND f.vendedor = {$this->filtroVendedorId}" : '';

        $cacheKey = "dash_tendencia_12m_{$this->filtroVendedorId}";

        return Cache::remember($cacheKey, 300, function () use ($vendWhere) {
            $rows = DB::SELECT("
                SELECT
                    DATE_FORMAT(f.fecha_emision,'%Y-%m') AS periodo,
                    MONTHNAME(f.fecha_emision)            AS mes_nombre,
                    MONTH(f.fecha_emision)                AS mes_num,
                    YEAR(f.fecha_emision)                 AS anio,
                    ROUND(SUM(f.total),2)                 AS monto,
                    COUNT(DISTINCT f.id)                  AS facturas,
                    COUNT(DISTINCT f.cliente_id)          AS clientes
                FROM factura f
                WHERE f.estado_venta_id = 1
                  AND f.fecha_emision >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                  {$vendWhere}
                GROUP BY periodo, mes_nombre, mes_num, anio
                ORDER BY periodo ASC
            ");

            $meses_es = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
                             'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

            return [
                'labels'   => array_map(fn($r) => $meses_es[$r->mes_num] . " '" . substr($r->anio, -2), $rows),
                'montos'   => array_map(fn($r) => (float) $r->monto,    $rows),
                'facturas' => array_map(fn($r) => (int)   $r->facturas,  $rows),
                'clientes' => array_map(fn($r) => (int)   $r->clientes,  $rows),
            ];
        });
    }

    // ─── Evolución histórica ─────────────────────────────────────────────────
    public function getEvolucionHistorica(): array
    {
        return $this->getTendenciaMensual();
    }

    // ─── Participación de mercado ────────────────────────────────────────────
    public function getParticipacionMercado(): array
    {
        $fi  = $this->fechaInicio;
        $ff  = $this->fechaFin;
        $eje = $this->participacionEje;
        $cacheKey = "dash_participacion_{$fi}_{$ff}_{$eje}";

        return Cache::remember($cacheKey, 120, function () use ($fi, $ff, $eje) {
            switch ($eje) {
                case 'vendedor':
                    $rows = DB::SELECT("
                        SELECT u.name AS etiqueta, ROUND(SUM(f.total),2) AS monto
                        FROM factura f
                        INNER JOIN users u ON u.id = f.vendedor
                        WHERE f.estado_venta_id = 1
                          AND f.fecha_emision BETWEEN '{$fi}' AND '{$ff}'
                        GROUP BY u.id, u.name ORDER BY monto DESC LIMIT 15
                    ");
                    break;

                case 'cliente':
                    $rows = DB::SELECT("
                        SELECT cli.nombre AS etiqueta, ROUND(SUM(f.total),2) AS monto
                        FROM factura f
                        INNER JOIN cliente cli ON cli.id = f.cliente_id
                        WHERE f.estado_venta_id = 1
                          AND f.fecha_emision BETWEEN '{$fi}' AND '{$ff}'
                        GROUP BY cli.id, cli.nombre ORDER BY monto DESC LIMIT 15
                    ");
                    break;

                case 'categoria':
                    $rows = DB::SELECT("
                        SELECT cp.nombre AS etiqueta, ROUND(SUM(vhp.sub_total_s),2) AS monto
                        FROM factura f
                        INNER JOIN venta_has_producto vhp ON vhp.factura_id = f.id
                        INNER JOIN producto p ON p.id = vhp.producto_id
                        INNER JOIN sub_categoria sc ON sc.id = p.sub_categoria_id
                        INNER JOIN categoria_producto cp ON cp.id = sc.categoria_producto_id
                        WHERE f.estado_venta_id = 1
                          AND f.fecha_emision BETWEEN '{$fi}' AND '{$ff}'
                        GROUP BY cp.id, cp.nombre ORDER BY monto DESC LIMIT 15
                    ");
                    break;

                case 'marca':
                    $rows = DB::SELECT("
                        SELECT m.nombre AS etiqueta, ROUND(SUM(vhp.sub_total_s),2) AS monto
                        FROM factura f
                        INNER JOIN venta_has_producto vhp ON vhp.factura_id = f.id
                        INNER JOIN producto p ON p.id = vhp.producto_id
                        INNER JOIN marca m ON m.id = p.marca_id
                        WHERE f.estado_venta_id = 1
                          AND f.fecha_emision BETWEEN '{$fi}' AND '{$ff}'
                        GROUP BY m.id, m.nombre ORDER BY monto DESC LIMIT 15
                    ");
                    break;

                default:
                    $rows = [];
            }

            return [
                'labels' => array_map(fn($r) => $r->etiqueta, $rows),
                'series' => array_map(fn($r) => (float) $r->monto, $rows),
            ];
        });
    }

    // ─── Comparativa vs mes anterior ─────────────────────────────────────────
    public function getComparativaMesAnterior(): array
    {
        $fi   = $this->fechaInicio;
        $ff   = $this->fechaFin;
        $dias = max(1, Carbon::parse($fi)->diffInDays(Carbon::parse($ff)) + 1);
        $fiPrev = Carbon::parse($fi)->subDays($dias)->toDateString();
        $ffPrev = Carbon::parse($fi)->subDay()->toDateString();
        $limit  = $this->topLimit;
        $vid    = $this->filtroVendedorId;
        $cacheKey = "dash_comparativa_{$fi}_{$ff}_{$limit}_{$vid}";

        return Cache::remember($cacheKey, 120, function () use ($fi, $ff, $fiPrev, $ffPrev, $limit, $vid) {
            $vendWhereA    = $vid ? "AND fa.vendedor = {$vid}" : '';
            $vendWhereB    = $vid ? "AND fb.vendedor = {$vid}" : '';
            $vendWhereMain = $vid ? "AND vendedor = {$vid}"    : '';

            $rows = DB::SELECT("
                SELECT
                    cli.nombre AS cliente,
                    COALESCE((SELECT SUM(fa.total) FROM factura fa
                        WHERE fa.cliente_id = cli.id AND fa.estado_venta_id = 1
                          AND fa.fecha_emision BETWEEN '{$fi}' AND '{$ff}' {$vendWhereA}
                    ),0) AS mes_actual,
                    COALESCE((SELECT SUM(fb.total) FROM factura fb
                        WHERE fb.cliente_id = cli.id AND fb.estado_venta_id = 1
                          AND fb.fecha_emision BETWEEN '{$fiPrev}' AND '{$ffPrev}' {$vendWhereB}
                    ),0) AS mes_anterior
                FROM cliente cli
                WHERE cli.id IN (
                    SELECT DISTINCT cliente_id FROM factura
                    WHERE estado_venta_id = 1
                      AND fecha_emision BETWEEN '{$fi}' AND '{$ff}'
                      {$vendWhereMain}
                )
                ORDER BY mes_actual DESC
                LIMIT {$limit}
            ");

            return array_map(function ($r) {
                $variacion = $r->mes_anterior > 0
                    ? round((($r->mes_actual - $r->mes_anterior) / $r->mes_anterior) * 100, 1)
                    : null;
                return [
                    'cliente'      => $r->cliente,
                    'mes_actual'   => (float) $r->mes_actual,
                    'mes_anterior' => (float) $r->mes_anterior,
                    'variacion'    => $variacion,
                ];
            }, $rows);
        });
    }

    // ─── KPIs de Cobros ──────────────────────────────────────────────────────
    public function getKpisCobros(): array
    {
        $fi  = $this->fechaInicio;
        $ff  = $this->fechaFin;
        $vid = $this->filtroVendedorId;
        $cacheKey = "dash_cobros_kpis_{$fi}_{$ff}_{$vid}";

        return Cache::remember($cacheKey, 120, function () use ($fi, $ff, $vid) {

            // ── Cartera: saldo pendiente total ──
            $saldoPendiente = (float) DB::table('aplicacion_pagos')
                ->where('estado', 1)
                ->where('estado_cerrado', '!=', 2)
                ->where('saldo', '>', 0)
                ->sum('saldo');

            // ── Facturas vencidas (fecha_vencimiento < hoy y saldo > 0) ──
            $facturasVencidas = (int) DB::table('aplicacion_pagos as ap')
                ->join('factura as f', 'f.id', '=', 'ap.factura_id')
                ->where('ap.estado', 1)
                ->where('ap.estado_cerrado', '!=', 2)
                ->where('ap.saldo', '>', 0)
                ->whereNotNull('f.fecha_vencimiento')
                ->where('f.fecha_vencimiento', '<', now()->toDateString())
                ->count();

            // ── Facturas pendientes (activas, saldo > 0) ──
            $facturasPendientes = (int) DB::table('aplicacion_pagos')
                ->where('estado', 1)
                ->where('estado_cerrado', '!=', 2)
                ->where('saldo', '>', 0)
                ->count();

            // ── Total recuperado en el período (abonos_creditos) ──
            $totalRecuperado = (float) DB::table('abonos_creditos')
                ->where('estado_abono', 1)
                ->whereBetween('fecha_pago', [$fi, $ff])
                ->sum('monto_abonado');

            // ── Variación recuperado vs período anterior ──
            $fiAnt = Carbon::parse($fi)->subMonth()->toDateString();
            $ffAnt = Carbon::parse($ff)->subMonth()->toDateString();
            $recuperadoAnt = (float) DB::table('abonos_creditos')
                ->where('estado_abono', 1)
                ->whereBetween('fecha_pago', [$fiAnt, $ffAnt])
                ->sum('monto_abonado');
            $varRecuperado = $recuperadoAnt > 0
                ? round((($totalRecuperado - $recuperadoAnt) / $recuperadoAnt) * 100, 1)
                : null;

            // ── Clientes morosos ──
            $clientesMorosos = (int) DB::table('aplicacion_pagos as ap')
                ->join('factura as f', 'f.id', '=', 'ap.factura_id')
                ->where('ap.estado', 1)
                ->where('ap.saldo', '>', 0)
                ->whereNotNull('f.fecha_vencimiento')
                ->where('f.fecha_vencimiento', '<', now()->toDateString())
                ->distinct()
                ->count('f.cliente_id');

            // ── Abonos por día en el período ──
            $cobrosDiariosRaw = DB::table('abonos_creditos')
                ->selectRaw('fecha_pago as dia, ROUND(SUM(monto_abonado),2) as monto, COUNT(*) as cantidad')
                ->where('estado_abono', 1)
                ->whereBetween('fecha_pago', [$fi, $ff])
                ->groupBy('fecha_pago')
                ->orderBy('fecha_pago')
                ->get();
            $cobrosDiarios = [
                'fechas'     => $cobrosDiariosRaw->pluck('dia')->toArray(),
                'montos'     => $cobrosDiariosRaw->pluck('monto')->map(fn($v) => (float)$v)->toArray(),
                'cantidades' => $cobrosDiariosRaw->pluck('cantidad')->map(fn($v) => (int)$v)->toArray(),
            ];

            // ── Tendencia abonos últimos 6 meses ──
            $tendLabels = [];
            $tendMontos = [];
            for ($i = 5; $i >= 0; $i--) {
                $dt = now()->subMonths($i);
                $tendLabels[] = $dt->format('M y');
                $tendMontos[] = (float) DB::table('abonos_creditos')
                    ->where('estado_abono', 1)
                    ->whereYear('fecha_pago', $dt->year)
                    ->whereMonth('fecha_pago', $dt->month)
                    ->sum('monto_abonado');
            }
            $tendenciaCobros = ['labels' => $tendLabels, 'montos' => $tendMontos];

            // ── Antigüedad de saldos ──
            $antRow = DB::selectOne("
                SELECT
                    ROUND(SUM(CASE WHEN DATEDIFF(NOW(),f.fecha_vencimiento) <= 0                  THEN ap.saldo ELSE 0 END),2) AS al_dia,
                    ROUND(SUM(CASE WHEN DATEDIFF(NOW(),f.fecha_vencimiento) BETWEEN  1 AND  30   THEN ap.saldo ELSE 0 END),2) AS d_0_30,
                    ROUND(SUM(CASE WHEN DATEDIFF(NOW(),f.fecha_vencimiento) BETWEEN 31 AND  60   THEN ap.saldo ELSE 0 END),2) AS d_31_60,
                    ROUND(SUM(CASE WHEN DATEDIFF(NOW(),f.fecha_vencimiento) BETWEEN 61 AND  90   THEN ap.saldo ELSE 0 END),2) AS d_61_90,
                    ROUND(SUM(CASE WHEN DATEDIFF(NOW(),f.fecha_vencimiento)  > 90                 THEN ap.saldo ELSE 0 END),2) AS d_90_mas
                FROM aplicacion_pagos ap
                INNER JOIN factura f ON f.id = ap.factura_id
                WHERE ap.estado = 1 AND ap.saldo > 0 AND ap.estado_cerrado != 2
                  AND f.fecha_vencimiento IS NOT NULL
            ");
            $antiguedad = [
                'al_dia'  => (float)($antRow->al_dia  ?? 0),
                '0_30'    => (float)($antRow->d_0_30  ?? 0),
                '31_60'   => (float)($antRow->d_31_60 ?? 0),
                '61_90'   => (float)($antRow->d_61_90 ?? 0),
                '90_mas'  => (float)($antRow->d_90_mas ?? 0),
            ];

            // ── Top deudores ──
            $topDeudoresRaw = DB::select("
                SELECT cli.nombre AS cliente,
                    ROUND(SUM(ap.saldo),2) AS saldo,
                    COUNT(DISTINCT f.id) AS facturas_vencidas,
                    MAX(DATEDIFF(NOW(),f.fecha_vencimiento)) AS dias_vencido
                FROM aplicacion_pagos ap
                INNER JOIN factura f   ON f.id   = ap.factura_id
                INNER JOIN cliente cli ON cli.id = f.cliente_id
                WHERE ap.estado = 1 AND ap.saldo > 0 AND ap.estado_cerrado != 2
                  AND f.fecha_vencimiento IS NOT NULL AND f.fecha_vencimiento < CURDATE()
                GROUP BY cli.id, cli.nombre
                ORDER BY saldo DESC LIMIT 10
            ");
            $topDeudores = array_map(fn($r) => [
                'cliente'           => $r->cliente,
                'saldo'             => (float)$r->saldo,
                'facturas_vencidas' => (int)$r->facturas_vencidas,
                'dias_vencido'      => (int)$r->dias_vencido,
                'ultima_gestion'    => '—',
            ], $topDeudoresRaw);

            // ── Próximas a vencer (7 días) ──
            $proximasVencerRaw = DB::table('aplicacion_pagos as ap')
                ->join('factura as f',   'f.id',   '=', 'ap.factura_id')
                ->join('cliente as cli', 'cli.id', '=', 'f.cliente_id')
                ->select('f.cai as numero','cli.nombre as cliente','ap.saldo as monto','f.fecha_vencimiento')
                ->where('ap.estado', 1)
                ->where('ap.saldo', '>', 0)
                ->where('ap.estado_cerrado', '!=', 2)
                ->whereBetween('f.fecha_vencimiento', [now()->toDateString(), now()->addDays(7)->toDateString()])
                ->orderBy('f.fecha_vencimiento')
                ->limit(15)
                ->get();
            $proximasVencer = $proximasVencerRaw->map(fn($r) => [
                'numero'            => $r->numero,
                'cliente'           => $r->cliente,
                'monto'             => (float)$r->monto,
                'fecha_vencimiento' => $r->fecha_vencimiento,
            ])->toArray();

            // ── Top 10 facturas con mayor saldo pendiente (sin restricción de período emisión) ──
            $topFacturasCobrarRaw = DB::table('aplicacion_pagos as ap')
                ->join('factura as f',   'f.id',   '=', 'ap.factura_id')
                ->join('cliente as cli', 'cli.id', '=', 'f.cliente_id')
                ->select(
                    'f.id as factura_id',
                    'f.cai as numero',
                    'cli.nombre as cliente',
                    'ap.saldo as saldo',
                    'f.total as total_factura',
                    'f.fecha_emision',
                    'f.fecha_vencimiento',
                    DB::raw('GREATEST(0, DATEDIFF(CURDATE(), f.fecha_vencimiento)) as dias_vencido')
                )
                ->where('ap.estado', 1)
                ->where('ap.saldo', '>', 0)
                ->where('ap.estado_cerrado', '!=', 2)
                ->when($vid, fn($q) => $q->where('f.vendedor', $vid))
                ->orderByDesc('ap.saldo')
                ->limit(10)
                ->get();
            $topFacturasCobrar = $topFacturasCobrarRaw->map(fn($r) => [
                'numero'            => $r->numero,
                'cliente'           => $r->cliente,
                'saldo'             => (float)$r->saldo,
                'total_factura'     => (float)$r->total_factura,
                'fecha_emision'     => $r->fecha_emision,
                'fecha_vencimiento' => $r->fecha_vencimiento,
                'dias_vencido'      => (int)$r->dias_vencido,
            ])->toArray();

            return [
                'saldo_pendiente'     => $saldoPendiente,
                'facturas_vencidas'   => $facturasVencidas,
                'facturas_pendientes' => $facturasPendientes,
                'total_recuperado'    => $totalRecuperado,
                'var_recuperado'      => $varRecuperado,
                'clientes_morosos'    => $clientesMorosos,
                'proximas_vencer'     => $proximasVencer,
                'cobros_diarios'      => $cobrosDiarios,
                'tendencia_cobros'    => $tendenciaCobros,
                'antiguedad'          => $antiguedad,
                'top_deudores'        => $topDeudores,
                'top_facturas_cobrar' => $topFacturasCobrar,
            ];
        });
    }

    // ─── Ranking Vendedores (para Supervisor/Gerencia) ───────────────────────
    public function getRankingVendedores(): array
    {
        $fi = $this->fechaInicio;
        $ff = $this->fechaFin;
        $cacheKey = "dash_ranking_vend_{$fi}_{$ff}";

        return Cache::remember($cacheKey, 120, function () use ($fi, $ff) {
            return DB::SELECT("
                SELECT
                    u.name AS vendedor,
                    COALESCE(SUM(f.total),0) AS venta_total,
                    COUNT(DISTINCT f.id) AS facturas,
                    COUNT(DISTINCT f.cliente_id) AS clientes
                FROM users u
                LEFT JOIN factura f
                    ON f.vendedor = u.id
                    AND f.estado_venta_id = 1
                    AND f.fecha_emision BETWEEN '{$fi}' AND '{$ff}'
                WHERE u.rol_id IN (2,3,14,15) AND u.estado_id = 1
                GROUP BY u.id, u.name
                ORDER BY venta_total DESC
            ");
        });
    }

    // ─── KPIs de Inventario ──────────────────────────────────────────────────
    public function getKpisInventario(): array
    {
        return Cache::remember('dash_inventario_kpis', 300, function () {
            $totalProductos = (int) DB::table('producto')->where('estado_producto_id', 1)->count();

            $sinImagen = (int) DB::table('producto as p')
                ->leftJoin('img_producto as ip', 'ip.producto_id', '=', 'p.id')
                ->whereNull('ip.id')
                ->where('p.estado_producto_id', 1)
                ->count();

            $bajosStock = (int) DB::select("
                SELECT COUNT(*) as cnt FROM (
                    SELECT p.id, COALESCE(SUM(c.cantidad),0) as stock
                    FROM producto p LEFT JOIN cardex c ON c.id_producto = p.id
                    WHERE p.estado_producto_id = 1
                    GROUP BY p.id HAVING stock >= 0 AND stock < 10
                ) sub
            ")[0]->cnt;

            $sinMovimiento = (int) DB::select("
                SELECT COUNT(*) as cnt FROM producto p
                WHERE p.estado_producto_id = 1
                AND p.id NOT IN (
                    SELECT DISTINCT vhp.producto_id FROM venta_has_producto vhp
                    INNER JOIN factura f ON f.id = vhp.factura_id
                    WHERE f.fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                )
            ")[0]->cnt;

            $topBajosRaw = DB::select("
                SELECT p.nombre, COALESCE(SUM(c.cantidad),0) as stock
                FROM producto p LEFT JOIN cardex c ON c.id_producto = p.id
                WHERE p.estado_producto_id = 1
                GROUP BY p.id, p.nombre
                HAVING COALESCE(SUM(c.cantidad),0) >= 0 AND COALESCE(SUM(c.cantidad),0) < 10
                ORDER BY stock ASC LIMIT 15
            ");

            $topBajosStock = array_map(fn($r) => [
                'nombre' => $r->nombre,
                'stock'  => (int) $r->stock,
            ], $topBajosRaw);

            return [
                'total_productos'    => $totalProductos,
                'bajos_stock'        => $bajosStock,
                'sin_imagen'         => $sinImagen,
                'sin_movimiento_90'  => $sinMovimiento,
                'top_bajos_stock'    => $topBajosStock,
            ];
        });
    }

    // ─── Exportar CSV ────────────────────────────────────────────────────────
    public function exportarCsv()
    {
        $fi    = $this->fechaInicio;
        $ff    = $this->fechaFin;
        $binds = [$fi, $ff];
        $vendWhere = '';

        if ($this->filtroVendedorId !== '' && $this->filtroVendedorId !== null) {
            $vendWhere = 'AND f.vendedor = ?';
            $binds[]   = (int) $this->filtroVendedorId;
        }

        $rows = DB::select("
            SELECT
                f.cai AS numero_factura,
                DATE_FORMAT(f.fecha_emision,'%d/%m/%Y') AS fecha,
                u.name AS vendedor,
                cli.nombre AS cliente,
                f.total AS total
            FROM factura f
            INNER JOIN users u ON u.id = f.vendedor
            INNER JOIN cliente cli ON cli.id = f.cliente_id
            WHERE f.estado_venta_id = 1
              AND f.fecha_emision BETWEEN ? AND ?
              {$vendWhere}
            ORDER BY f.fecha_emision ASC
        ", $binds);

        $csv  = "Exportado por: " . Auth::user()->name . "\n";
        $csv .= "Período: {$fi} al {$ff}\n";
        $csv .= "Fecha generación: " . now()->format('d/m/Y H:i') . "\n\n";
        $csv .= "Factura,Fecha,Vendedor,Cliente,Total\n";
        foreach ($rows as $r) {
            $csv .= "\"{$r->numero_factura}\",\"{$r->fecha}\",\"{$r->vendedor}\",\"{$r->cliente}\",{$r->total}\n";
        }

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, "dashboard_{$fi}_{$ff}.csv", ['Content-Type' => 'text/csv']);
    }
}
