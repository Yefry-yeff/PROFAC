<?php

namespace App\Http\Livewire\Usuarios;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardEmpresarial extends Component
{
    // Filtro de mes/año seleccionado
    public string $periodoLabel = '';
    public int    $filtroMes;
    public int    $filtroAnio;

    public function mount(): void
    {
        $this->filtroMes  = now()->month;
        $this->filtroAnio = now()->year;
        $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                       'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $this->periodoLabel = $meses[$this->filtroMes] . ' ' . $this->filtroAnio;
    }

    // ─────────────────────────────────────────────────────────────────────
    //  RENDER
    // ─────────────────────────────────────────────────────────────────────
    public function render()
    {
        $mes  = $this->filtroMes;
        $anio = $this->filtroAnio;

        return view('livewire.usuarios.dashboard-empresarial', [
            // ── Facturación ──────────────────────────────────────────────
            'fact'          => $this->metricasFacturacion($mes, $anio),
            'graficoFact'   => $this->graficoFacturacion(),

            // ── Vendedores ───────────────────────────────────────────────
            'vend'          => $this->metricasVendedores($mes, $anio),
            'graficoVend'   => $this->graficoVendedores(),
            'rankingVend'   => $this->rankingVendedores($mes, $anio),

            // ── Cobros ───────────────────────────────────────────────────
            'cobros'        => $this->metricasCobros($mes, $anio),
            'graficoCobros' => $this->graficoCobros(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  SECCIÓN 1 — FACTURACIÓN
    // ─────────────────────────────────────────────────────────────────────
    private function metricasFacturacion(int $mes, int $anio): array
    {
        $base = DB::table('factura')
            ->where('estado_factura_id', 1)
            ->whereMonth('fecha_emision', $mes)
            ->whereYear('fecha_emision', $anio);

        $total  = (float) (clone $base)->sum('total');
        $count  = (int)   (clone $base)->count();
        $ticket = $count > 0 ? round($total / $count, 2) : 0;

        // Crédito vs Contado (tipo_pago_id: 1=contado, resto=crédito)
        $contado = (int) (clone $base)->where('credito', 0)->count();
        $credito = $count - $contado;

        // Facturas cerradas este mes (aplicacion_pagos.estado_cerrado = 2)
        $cerradasRow = DB::table('aplicacion_pagos as ap')
            ->join('factura as f', 'f.id', '=', 'ap.factura_id')
            ->where('ap.estado_cerrado', 2)
            ->whereMonth('ap.updated_at', $mes)
            ->whereYear('ap.updated_at', $anio)
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(f.total), 0) as monto')
            ->first();

        // Facturas activas con saldo pendiente
        $pendientes = (int) DB::table('aplicacion_pagos')
            ->where('estado', 1)
            ->where('estado_cerrado', '<>', 2)
            ->whereColumn('saldo', '>', DB::raw('0'))
            ->count();

        // Mes anterior para variación %
        $mesAnt  = $mes === 1 ? 12 : $mes - 1;
        $anioAnt = $mes === 1 ? $anio - 1 : $anio;
        $totalAnt = (float) DB::table('factura')
            ->where('estado_factura_id', 1)
            ->whereMonth('fecha_emision', $mesAnt)
            ->whereYear('fecha_emision', $anioAnt)
            ->sum('total');
        $variacion = $totalAnt > 0 ? round((($total - $totalAnt) / $totalAnt) * 100, 1) : null;

        return [
            'total'          => $total,
            'count'          => $count,
            'ticket'         => $ticket,
            'contado'        => $contado,
            'credito'        => $credito,
            'cerradasCount'  => (int) ($cerradasRow->cnt ?? 0),
            'cerradasMonto'  => (float) ($cerradasRow->monto ?? 0),
            'pendientes'     => $pendientes,
            'variacion'      => $variacion,
        ];
    }

    private function graficoFacturacion(): array
    {
        $rows = DB::table('factura')
            ->selectRaw('YEAR(fecha_emision) as anio, MONTH(fecha_emision) as mes,
                         ROUND(SUM(total), 2) as monto, COUNT(*) as cantidad')
            ->where('estado_factura_id', 1)
            ->where('fecha_emision', '>=', now()->subMonths(5)->startOfMonth())
            ->groupByRaw('YEAR(fecha_emision), MONTH(fecha_emision)')
            ->orderByRaw('anio, mes')
            ->get();

        $meses_es = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
                          'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        return [
            'categorias' => $rows->map(fn($r) => $meses_es[$r->mes] . " '" . substr($r->anio, -2))->values()->toArray(),
            'montos'     => $rows->map(fn($r) => (float) $r->monto)->values()->toArray(),
            'cantidades' => $rows->map(fn($r) => (int) $r->cantidad)->values()->toArray(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    //  SECCIÓN 2 — VENDEDORES
    // ─────────────────────────────────────────────────────────────────────
    private function metricasVendedores(int $mes, int $anio): array
    {
        // Cotizaciones
        $cotBase = DB::table('cotizacion')
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $anio);
        $cotCount = (int)   (clone $cotBase)->count();
        $cotTotal = (float) (clone $cotBase)->sum('total');

        // Pedidos creados este mes
        $pedCount = (int) DB::table('pedido')
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $anio)
            ->count();

        // Pedidos facturados (estado = 'facturado') creados este mes
        $pedFacturados = (int) DB::table('pedido')
            ->where('estado', 'facturado')
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $anio)
            ->count();

        // Tasa de conversión: pedidos facturados / cotizaciones
        $tasaConversion = $cotCount > 0
            ? round(($pedFacturados / max($cotCount, 1)) * 100, 1)
            : 0;

        return [
            'cotCount'          => $cotCount,
            'cotTotal'          => $cotTotal,
            'pedCount'          => $pedCount,
            'pedFacturados'     => $pedFacturados,
            'tasaConversion'    => $tasaConversion,
            'facturasConPedido' => $pedFacturados,
        ];
    }

    private function graficoVendedores(): array
    {
        $cotizaciones = DB::table('cotizacion')
            ->selectRaw('YEAR(created_at) as anio, MONTH(created_at) as mes, COUNT(*) as cantidad')
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('anio, mes')
            ->get()
            ->keyBy(fn($r) => $r->anio . '-' . $r->mes);

        $pedidos = DB::table('pedido')
            ->selectRaw('YEAR(created_at) as anio, MONTH(created_at) as mes, COUNT(*) as cantidad')
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('anio, mes')
            ->get()
            ->keyBy(fn($r) => $r->anio . '-' . $r->mes);

        // Construir eje de 6 meses
        $categorias = [];
        $seriesCot  = [];
        $seriesPed  = [];
        $meses_es   = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
                            'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        for ($i = 5; $i >= 0; $i--) {
            $dt   = now()->subMonths($i);
            $key  = $dt->year . '-' . $dt->month;
            $label = $meses_es[$dt->month] . " '" . substr($dt->year, -2);
            $categorias[]  = $label;
            $seriesCot[]   = (int) ($cotizaciones[$key]->cantidad ?? 0);
            $seriesPed[]   = (int) ($pedidos[$key]->cantidad ?? 0);
        }

        return [
            'categorias' => $categorias,
            'cotizaciones' => $seriesCot,
            'pedidos'      => $seriesPed,
        ];
    }

    private function rankingVendedores(int $mes, int $anio): array
    {
        $rows = DB::table('factura as f')
            ->join('users as u', 'u.id', '=', 'f.vendedor')
            ->selectRaw('u.name as nombre, COUNT(*) as facturas, ROUND(SUM(f.total), 2) as monto')
            ->where('f.estado_factura_id', 1)
            ->whereMonth('f.fecha_emision', $mes)
            ->whereYear('f.fecha_emision', $anio)
            ->whereNotNull('f.vendedor')
            ->groupBy('f.vendedor', 'u.name')
            ->orderByDesc('monto')
            ->limit(5)
            ->get();

        return $rows->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────
    //  SECCIÓN 3 — COBROS
    // ─────────────────────────────────────────────────────────────────────
    private function metricasCobros(int $mes, int $anio): array
    {
        // Cartera activa total (todas las facturas con saldo > 0)
        $carteraTotal = (float) DB::table('aplicacion_pagos')
            ->where('estado', 1)
            ->where('saldo', '>', 0)
            ->sum('saldo');

        // Abonos registrados este mes
        $abonosMes = DB::table('abonos_creditos')
            ->where('estado_abono', 1)
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $anio)
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(monto_abonado), 0) as monto')
            ->first();

        // Facturas cerradas este mes
        $cerradasMes = (int) DB::table('aplicacion_pagos')
            ->where('estado_cerrado', 2)
            ->whereMonth('updated_at', $mes)
            ->whereYear('updated_at', $anio)
            ->count();

        // Facturas pendientes (saldo > 0, activas)
        $pendientes = (int) DB::table('aplicacion_pagos')
            ->where('estado', 1)
            ->where('estado_cerrado', '<>', 2)
            ->where('saldo', '>', 0)
            ->count();

        // Facturas en mora: factura con vencimiento < hoy y saldo > 0
        $enMora = (int) DB::table('aplicacion_pagos as ap')
            ->join('factura as f', 'f.id', '=', 'ap.factura_id')
            ->where('ap.estado', 1)
            ->where('ap.estado_cerrado', '<>', 2)
            ->where('ap.saldo', '>', 0)
            ->whereNotNull('f.fecha_vencimiento')
            ->where('f.fecha_vencimiento', '<', now()->toDateString())
            ->count();

        // Abonos mes anterior para variación
        $mesAnt  = $mes === 1 ? 12 : $mes - 1;
        $anioAnt = $mes === 1 ? $anio - 1 : $anio;
        $abonosAnt = (float) DB::table('abonos_creditos')
            ->where('estado_abono', 1)
            ->whereMonth('created_at', $mesAnt)
            ->whereYear('created_at', $anioAnt)
            ->sum('monto_abonado');
        $montoAbonos = (float) ($abonosMes->monto ?? 0);
        $varCobros = $abonosAnt > 0
            ? round((($montoAbonos - $abonosAnt) / $abonosAnt) * 100, 1)
            : null;

        return [
            'carteraTotal'   => $carteraTotal,
            'abonosCount'    => (int) ($abonosMes->cnt ?? 0),
            'abonosMonto'    => $montoAbonos,
            'cerradasMes'    => $cerradasMes,
            'pendientes'     => $pendientes,
            'enMora'         => $enMora,
            'varCobros'      => $varCobros,
        ];
    }

    private function graficoCobros(): array
    {
        $rows = DB::table('abonos_creditos')
            ->selectRaw('YEAR(created_at) as anio, MONTH(created_at) as mes,
                         ROUND(SUM(monto_abonado), 2) as monto, COUNT(*) as cantidad')
            ->where('estado_abono', 1)
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('anio, mes')
            ->get()
            ->keyBy(fn($r) => $r->anio . '-' . $r->mes);

        $categorias = [];
        $montos     = [];
        $cantidades = [];
        $meses_es   = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
                            'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        for ($i = 5; $i >= 0; $i--) {
            $dt    = now()->subMonths($i);
            $key   = $dt->year . '-' . $dt->month;
            $label = $meses_es[$dt->month] . " '" . substr($dt->year, -2);
            $categorias[] = $label;
            $montos[]     = (float) ($rows[$key]->monto ?? 0);
            $cantidades[] = (int)   ($rows[$key]->cantidad ?? 0);
        }

        return [
            'categorias' => $categorias,
            'montos'     => $montos,
            'cantidades' => $cantidades,
        ];
    }
}
