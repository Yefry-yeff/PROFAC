<div>
{{-- ══════════════════════════════════════════════════════════════════════
     INTELIGENCIA DE INVENTARIO – RESUMEN EJECUTIVO
     ══════════════════════════════════════════════════════════════════════ --}}
<style>
:root {
    --ii-bg:        #f0f2f8;
    --ii-card:      #ffffff;
    --ii-border:    #e2e8f0;
    --ii-shadow:    0 2px 12px rgba(30,40,80,.08);
    --ii-shadow-lg: 0 8px 32px rgba(30,40,80,.13);
    --ii-green:     #27ae60;
    --ii-yellow:    #f39c12;
    --ii-red:       #e74c3c;
    --ii-blue:      #2980b9;
    --ii-gray:      #95a5a6;
    --ii-dark:      #1a2035;
    --ii-accent:    #f39c12;
    --ii-radius:    14px;
    --ii-radius-sm: 8px;
}
.ii-wrap { background:var(--ii-bg); min-height:100vh; padding:0 0 40px; }
.ii-header {
    background:linear-gradient(135deg,#1a2035 0%,#2d3561 60%,#1a2035 100%);
    padding:28px 32px 24px; color:#fff;
}
.ii-header-top { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:16px; }
.ii-header h1  { font-size:24px; font-weight:800; margin:0; letter-spacing:-.3px; }
.ii-header p   { font-size:13px; color:rgba(255,255,255,.6); margin:4px 0 0; }
.ii-filters    { display:flex; flex-wrap:wrap; gap:8px; align-items:center; margin-top:20px; }
.ii-filters select, .ii-filters input {
    background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2);
    color:#fff; border-radius:var(--ii-radius-sm); padding:6px 12px; font-size:12px;
    outline:none; min-width:130px;
}
.ii-filters select option { background:#1a2035; color:#fff; }
.ii-filters input[type="date"]::-webkit-calendar-picker-indicator { filter:invert(1); }
.ii-btn { display:inline-flex; align-items:center; gap:6px; padding:7px 16px; border-radius:20px;
    font-size:12px; font-weight:600; border:none; cursor:pointer; transition:.2s; white-space:nowrap; }
.ii-btn:hover { transform:translateY(-1px); }
.ii-btn-light  { background:rgba(255,255,255,.15); color:#fff; border:1px solid rgba(255,255,255,.25); }
.ii-btn-light:hover { background:rgba(255,255,255,.25); }
.ii-btn-accent { background:var(--ii-accent); color:#fff; box-shadow:0 4px 14px rgba(243,156,18,.4); }
.ii-btn-accent:hover { background:#e68e09; }
.ii-header-actions { display:flex; flex-wrap:wrap; gap:8px; margin-top:16px; }
.ii-content { padding:24px 28px; }
.ii-kpi-grid {
    display:grid; grid-template-columns:repeat(auto-fit,minmax(175px,1fr)); gap:16px; margin-bottom:24px;
}
.ii-kpi-card {
    background:var(--ii-card); border-radius:var(--ii-radius); padding:20px 20px 16px;
    box-shadow:var(--ii-shadow); border:1px solid var(--ii-border); cursor:pointer;
    transition:.2s ease; position:relative; overflow:hidden;
}
.ii-kpi-card:hover { transform:translateY(-3px); box-shadow:var(--ii-shadow-lg); }
.ii-kpi-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:var(--kpi-color,#2980b9); }
.ii-kpi-icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center;
    justify-content:center; font-size:18px; margin-bottom:12px;
    background:var(--kpi-bg,rgba(41,128,185,.1)); color:var(--kpi-color,#2980b9); }
.ii-kpi-num   { font-size:26px; font-weight:800; color:var(--ii-dark); line-height:1; }
.ii-kpi-label { font-size:12px; color:#64748b; margin-top:4px; font-weight:500; }
.ii-kpi-trend { font-size:11px; margin-top:8px; display:flex; align-items:center; gap:4px; font-weight:600; }
.ii-kpi-trend.up   { color:var(--ii-green); }
.ii-kpi-trend.down { color:var(--ii-red); }
.ii-kpi-trend.neu  { color:var(--ii-gray); }
.ii-mid-row { display:grid; grid-template-columns:260px 1fr; gap:20px; margin-bottom:24px; }
@media(max-width:900px) { .ii-mid-row { grid-template-columns:1fr; } }
.ii-health-card {
    background:var(--ii-card); border-radius:var(--ii-radius); box-shadow:var(--ii-shadow);
    border:1px solid var(--ii-border); padding:24px 20px;
    display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center;
}
.ii-gauge-wrap { position:relative; width:140px; height:140px; margin:0 auto 16px; }
.ii-gauge-svg  { transform:rotate(-90deg); }
.ii-gauge-track { fill:none; stroke:#e8ecf5; stroke-width:10; }
.ii-gauge-fill  { fill:none; stroke-width:10; stroke-linecap:round; transition:stroke-dashoffset 1s cubic-bezier(.4,0,.2,1); }
.ii-gauge-label { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.ii-gauge-num  { font-size:32px; font-weight:900; color:var(--ii-dark); line-height:1; }
.ii-gauge-pct  { font-size:13px; color:#64748b; }
.ii-health-title { font-size:13px; font-weight:700; color:var(--ii-dark); margin-bottom:6px; letter-spacing:.3px; text-transform:uppercase; }
.ii-health-text  { font-size:12px; color:#64748b; line-height:1.5; }
.ii-alerts-card { background:var(--ii-card); border-radius:var(--ii-radius); box-shadow:var(--ii-shadow); border:1px solid var(--ii-border); overflow:hidden; }
.ii-card-header { padding:14px 20px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid var(--ii-border); }
.ii-card-title { font-size:14px; font-weight:700; color:var(--ii-dark); display:flex; align-items:center; gap:8px; }
.ii-card-body  { padding:0; }
.ii-alert-row { display:grid; grid-template-columns:32px 1fr auto; align-items:center; gap:12px;
    padding:11px 20px; border-bottom:1px solid #f1f5f9; transition:.15s; }
.ii-alert-row:last-child { border-bottom:none; }
.ii-alert-row:hover { background:#f8faff; }
.ii-alert-icon { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; color:#fff; flex-shrink:0; }
.ii-alert-prod  { font-size:12px; font-weight:600; color:var(--ii-dark); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:260px; }
.ii-alert-desc  { font-size:11px; color:#64748b; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:260px; }
.ii-alert-tipo  { font-size:10px; font-weight:700; padding:2px 8px; border-radius:12px; white-space:nowrap; }
.ii-badge-alta  { background:#fde8e8; color:#c0392b; }
.ii-badge-media { background:#fef3cd; color:#856404; }
.ii-badge-info  { background:#d1f2eb; color:#1a7a50; }
.ii-btn-xs { font-size:11px; padding:4px 10px; border-radius:12px; border:1px solid var(--ii-border);
    background:#fff; color:#475569; cursor:pointer; margin-left:6px; transition:.15s; white-space:nowrap; text-decoration:none; display:inline-block; }
.ii-btn-xs:hover { background:#f1f5f9; color:#1a2035; }
.ii-alert-actions { display:flex; align-items:center; flex-shrink:0; gap:2px; }
.ii-charts-row { display:grid; grid-template-columns:2fr 1.4fr 1fr; gap:20px; margin-bottom:24px; }
@media(max-width:1100px) { .ii-charts-row { grid-template-columns:1fr 1fr; } }
@media(max-width:700px)  { .ii-charts-row { grid-template-columns:1fr; } }
.ii-chart-card { background:var(--ii-card); border-radius:var(--ii-radius); box-shadow:var(--ii-shadow); border:1px solid var(--ii-border); overflow:hidden; }
.ii-chart-body { padding:16px 20px 20px; }
.ii-table-card { background:var(--ii-card); border-radius:var(--ii-radius); box-shadow:var(--ii-shadow); border:1px solid var(--ii-border); overflow:hidden; }
.ii-tabs { display:flex; border-bottom:1px solid var(--ii-border); background:#f8faff; overflow-x:auto; }
.ii-tab { padding:11px 20px; font-size:12px; font-weight:600; color:#64748b; cursor:pointer;
    border-bottom:2px solid transparent; transition:.15s; white-space:nowrap; }
.ii-tab.active { color:#2980b9; border-bottom-color:#2980b9; background:#fff; }
.ii-tab:hover:not(.active) { background:#edf2f7; }
.ii-table-filters { display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; padding:14px 18px; background:#fff; border-bottom:1px solid var(--ii-border); }
.ii-table-filter { display:flex; flex-direction:column; gap:5px; min-width:220px; }
.ii-table-filter label { color:#64748b; font-size:10px; font-weight:700; text-transform:uppercase; }
.ii-table-filter select, .ii-table-filter input { height:34px; border:1px solid #cbd5e1; border-radius:6px; padding:6px 10px; color:#334155; font-size:12px; outline:none; background:#fff; }
.ii-table-filter input:focus, .ii-table-filter select:focus { border-color:#2980b9; box-shadow:0 0 0 2px rgba(41,128,185,.12); }
button.ii-btn-export { background:#1f8f4e; color:#fff; border-radius:6px; padding:7px 13px; }
button.ii-btn-export:hover { background:#187640; }
button.ii-btn-export:disabled { opacity:.55; cursor:not-allowed; transform:none; }
.ii-pagination { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding:14px 18px; border-top:1px solid var(--ii-border); background:#fff; }
.ii-pagination-info { color:#64748b; font-size:11px; }
.ii-pagination nav { margin-left:auto; }
.ii-pagination .pagination { margin:0; }
.ii-table-wrap { overflow-x:auto; }
.ii-table { width:100%; border-collapse:collapse; font-size:12px; }
.ii-table thead th { background:linear-gradient(135deg,#1a2035,#2d3561); color:#a8c8e0;
    font-weight:700; padding:10px 14px; text-align:left; white-space:nowrap; font-size:11px; letter-spacing:.3px; }
.ii-table tbody td { padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#334155; }
.ii-table tbody tr:hover td { background:#f8faff; }
.ii-table tbody tr:last-child td { border-bottom:none; }
.ii-table .prod-name { font-weight:600; color:var(--ii-dark); max-width:240px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:block; }
.ii-table .cat-badge { background:#eef2ff; color:#3730a3; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:600; }
.ii-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:700; }
.ii-badge-green  { background:#d1f2eb; color:#1a7a50; }
.ii-badge-red    { background:#fde8e8; color:#c0392b; }
.ii-badge-yellow { background:#fef3cd; color:#856404; }
.ii-badge-gray   { background:#f1f5f9; color:#475569; }
.ii-badge-blue   { background:#dbeafe; color:#1e40af; }
.trend-up   { color:var(--ii-green); font-weight:700; }
.trend-down { color:var(--ii-red);   font-weight:700; }
.trend-neu  { color:var(--ii-gray);  font-weight:600; }
.ii-empty { text-align:center; padding:40px; color:#94a3b8; }
.ii-empty i { font-size:32px; opacity:.4; margin-bottom:10px; display:block; }
.ii-spin { animation:ii-spin .7s linear infinite; display:inline-block; }
@keyframes ii-spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
@media(max-width:768px) { .ii-content{padding:16px;} .ii-header{padding:20px 16px 16px;} .ii-kpi-num{font-size:22px;} }
</style>

<div class="ii-wrap">

{{-- HEADER --}}
<div class="ii-header">
    <div class="ii-header-top">
        <div>
            <h1><i class="fa fa-bar-chart mr-2" style="color:var(--ii-accent);"></i> Inteligencia de Inventario</h1>
            <p>Resumen ejecutivo de rotación, abastecimiento y comportamiento del inventario.</p>
        </div>
        <div class="ii-header-actions">
            <a href="{{ route('dashboard') }}" class="ii-btn ii-btn-light"><i class="fa fa-arrow-left"></i> Volver</a>
            <button wire:click="actualizarMetricas" class="ii-btn ii-btn-accent">
                <i class="fa fa-refresh" wire:loading.class="ii-spin" wire:target="actualizarMetricas,filtroCategoria,filtroMarca,filtroFechaInicio,filtroFechaFin"></i>
                Actualizar
            </button>
        </div>
    </div>
    <div class="ii-filters">
        <div style="display:flex;align-items:center;gap:6px;">
            <i class="fa fa-calendar" style="opacity:.6;font-size:12px;"></i>
            <input type="date" wire:model="filtroFechaInicio">
        </div>
        <span style="color:rgba(255,255,255,.4);font-size:11px;">—</span>
        <input type="date" wire:model="filtroFechaFin">
        <select wire:model="filtroCategoria">
            <option value="">Todas las categorías</option>
            @foreach($categorias as $cat)
                <option value="{{ $cat['id'] }}">{{ $cat['descripcion'] }}</option>
            @endforeach
        </select>
        <select wire:model="filtroMarca">
            <option value="">Todas las marcas</option>
            @foreach($marcas as $m)
                <option value="{{ $m['id'] }}">{{ $m['nombre'] }}</option>
            @endforeach
        </select>
        <span style="margin-left:4px;">
            <span wire:loading><i class="fa fa-circle-o-notch fa-spin" style="color:#f39c12;font-size:13px;"></i></span>
            <span wire:loading.remove><i class="fa fa-check-circle" style="color:rgba(255,255,255,.3);font-size:13px;"></i></span>
        </span>
    </div>
</div>

<div class="ii-content">

{{-- KPI CARDS --}}
<div class="ii-kpi-grid">
    <div class="ii-kpi-card" style="--kpi-color:#2980b9;--kpi-bg:rgba(41,128,185,.1);">
        <div class="ii-kpi-icon"><i class="fa fa-line-chart"></i></div>
        <div class="ii-kpi-num">L {{ number_format($kpis['valor_ventas'] ?? 0, 0, '.', ',') }}</div>
        <div class="ii-kpi-label">Ventas en el período</div>
        <div class="ii-kpi-trend {{ ($kpis['pct_cambio'] ?? 0) >= 0 ? 'up' : 'down' }}">
            <i class="fa fa-{{ ($kpis['pct_cambio'] ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
            {{ abs($kpis['pct_cambio'] ?? 0) }}% vs período anterior
        </div>
    </div>
    <div class="ii-kpi-card" style="--kpi-color:#27ae60;--kpi-bg:rgba(39,174,96,.1);">
        <div class="ii-kpi-icon"><i class="fa fa-cube"></i></div>
        <div class="ii-kpi-num">{{ number_format($kpis['total_activos'] ?? 0) }}</div>
        <div class="ii-kpi-label">Productos activos</div>
        <div class="ii-kpi-trend up">
            <i class="fa fa-check-circle"></i>
            {{ number_format($kpis['con_movimiento'] ?? 0) }} con movimiento
        </div>
    </div>
    <div class="ii-kpi-card" style="--kpi-color:#e74c3c;--kpi-bg:rgba(231,76,60,.1);">
        <div class="ii-kpi-icon"><i class="fa fa-pause-circle"></i></div>
        <div class="ii-kpi-num">{{ number_format($kpis['estancados'] ?? 0) }}</div>
        <div class="ii-kpi-label">Sin movimiento</div>
        <div class="ii-kpi-trend down"><i class="fa fa-exclamation-triangle"></i> Sin ventas en el período</div>
    </div>
    <div class="ii-kpi-card" style="--kpi-color:#8e44ad;--kpi-bg:rgba(142,68,173,.1);">
        <div class="ii-kpi-icon"><i class="fa fa-refresh"></i></div>
        <div class="ii-kpi-num">{{ $kpis['rotacion_promedio'] ?? 0 }}x</div>
        <div class="ii-kpi-label">Rotación mensual promedio</div>
        <div class="ii-kpi-trend neu"><i class="fa fa-info-circle"></i> Unidades por producto/mes</div>
    </div>
    <div class="ii-kpi-card" style="--kpi-color:#f39c12;--kpi-bg:rgba(243,156,18,.1);">
        <div class="ii-kpi-icon"><i class="fa fa-file-text-o"></i></div>
        <div class="ii-kpi-num">{{ number_format($kpis['total_facturas'] ?? 0) }}</div>
        <div class="ii-kpi-label">Facturas emitidas</div>
        <div class="ii-kpi-trend up"><i class="fa fa-calendar-check-o"></i> En el período seleccionado</div>
    </div>
    <div class="ii-kpi-card" style="--kpi-color:#16a085;--kpi-bg:rgba(22,160,133,.1);">
        <div class="ii-kpi-icon"><i class="fa fa-shopping-cart"></i></div>
        <div class="ii-kpi-num">{{ number_format($kpis['total_unidades'] ?? 0) }}</div>
        <div class="ii-kpi-label">Unidades vendidas</div>
        <div class="ii-kpi-trend up"><i class="fa fa-arrow-up"></i> Total en el período</div>
    </div>
</div>

{{-- SALUD + ALERTAS --}}
<div class="ii-mid-row">
    @php
        $salud = $saludGeneral;
        $circ  = 2 * M_PI * 54;
        $offset = $circ - ($salud / 100) * $circ;
        $hColor = $salud >= 80 ? '#27ae60' : ($salud >= 60 ? '#f39c12' : ($salud >= 40 ? '#e67e22' : '#e74c3c'));
        $alertasAlta  = count(array_filter($alertas, fn($a) => $a['prioridad'] === 'alta'));
        $alertasMedia = count(array_filter($alertas, fn($a) => $a['prioridad'] === 'media'));
    @endphp
    <div class="ii-health-card">
        <div class="ii-health-title">Salud del Inventario</div>
        <div class="ii-gauge-wrap">
            <svg class="ii-gauge-svg" width="140" height="140" viewBox="0 0 140 140">
                <circle class="ii-gauge-track" cx="70" cy="70" r="54"/>
                <circle class="ii-gauge-fill" cx="70" cy="70" r="54"
                    stroke="{{ $hColor }}"
                    stroke-dasharray="{{ round($circ, 2) }}"
                    stroke-dashoffset="{{ round($offset, 2) }}"/>
            </svg>
            <div class="ii-gauge-label">
                <span class="ii-gauge-num" style="color:{{ $hColor }}">{{ $salud }}%</span>
                <span class="ii-gauge-pct">Saludable</span>
            </div>
        </div>
        <div class="ii-health-text">{{ $saludTexto }}</div>
        <div style="display:flex;gap:10px;margin-top:14px;width:100%;">
            <div style="text-align:center;flex:1;background:#fde8e8;border-radius:8px;padding:8px 4px;">
                <div style="font-size:18px;font-weight:800;color:#e74c3c;">{{ $alertasAlta }}</div>
                <div style="font-size:10px;color:#c0392b;font-weight:600;">Críticas</div>
            </div>
            <div style="text-align:center;flex:1;background:#fef3cd;border-radius:8px;padding:8px 4px;">
                <div style="font-size:18px;font-weight:800;color:#f39c12;">{{ $alertasMedia }}</div>
                <div style="font-size:10px;color:#856404;font-weight:600;">Advertencias</div>
            </div>
            <div style="text-align:center;flex:1;background:#d1f2eb;border-radius:8px;padding:8px 4px;">
                <div style="font-size:18px;font-weight:800;color:#27ae60;">{{ count($alertas) - $alertasAlta - $alertasMedia }}</div>
                <div style="font-size:10px;color:#1a7a50;font-weight:600;">Info</div>
            </div>
        </div>
    </div>

    <div class="ii-alerts-card">
        <div class="ii-card-header">
            <div class="ii-card-title"><i class="fa fa-bell" style="color:#f39c12;"></i> Centro de Alertas Inteligentes</div>
            <span style="font-size:11px;color:#94a3b8;">{{ count($alertas) }} alertas activas</span>
        </div>
        <div class="ii-card-body">
            @forelse($alertas as $alerta)
            <div class="ii-alert-row">
                <div class="ii-alert-icon" style="background:{{ $alerta['color'] }};"><i class="fa {{ $alerta['icono'] }}"></i></div>
                <div style="min-width:0;">
                    <div class="ii-alert-prod" title="{{ $alerta['producto'] }}">
                        {{ $alerta['producto'] }}
                        @if($alerta['categoria'])
                            <span style="font-size:10px;color:#94a3b8;font-weight:400;"> — {{ $alerta['categoria'] }}</span>
                        @endif
                    </div>
                    <div class="ii-alert-desc" title="{{ $alerta['descripcion'] }}">{{ $alerta['descripcion'] }}</div>
                </div>
                <div class="ii-alert-actions">
                    <span class="ii-alert-tipo ii-badge-{{ $alerta['prioridad'] }}">{{ ucfirst($alerta['prioridad']) }}</span>
                    <a href="/reportes/analitica_de_productos/{{ $alerta['producto_id'] }}" target="_blank" class="ii-btn-xs">
                        {{ $alerta['accion'] }}
                    </a>
                </div>
            </div>
            @empty
            <div class="ii-empty">
                <i class="fa fa-check-circle" style="color:#27ae60;opacity:1;font-size:28px;"></i>
                <p style="margin:8px 0 0;font-size:13px;color:#27ae60;font-weight:600;">Sin alertas críticas</p>
                <p style="font-size:12px;color:#94a3b8;margin:4px 0 0;">El inventario está bajo control</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- GRÁFICAS --}}
<div class="ii-charts-row" wire:ignore>
    <div class="ii-chart-card">
        <div class="ii-card-header">
            <div class="ii-card-title"><i class="fa fa-area-chart" style="color:#2980b9;"></i> Tendencia de Ventas</div>
            <span style="font-size:11px;color:#94a3b8;">Últimos 6 meses</span>
        </div>
        <div class="ii-chart-body" style="height:220px;position:relative;">
            <canvas id="ii-chartTendencia"></canvas>
        </div>
    </div>
    <div class="ii-chart-card">
        <div class="ii-card-header">
            <div class="ii-card-title"><i class="fa fa-bar-chart" style="color:#27ae60;"></i> Rotación por Categoría</div>
            <span style="font-size:11px;color:#94a3b8;">Top 8</span>
        </div>
        <div class="ii-chart-body" style="height:220px;position:relative;">
            <canvas id="ii-chartRotacion"></canvas>
        </div>
    </div>
    <div class="ii-chart-card">
        <div class="ii-card-header">
            <div class="ii-card-title"><i class="fa fa-pie-chart" style="color:#8e44ad;"></i> Estado Inventario</div>
            <span style="font-size:11px;color:#94a3b8;">Distribución %</span>
        </div>
        <div class="ii-chart-body" style="height:220px;position:relative;">
            <canvas id="ii-chartEstado"></canvas>
        </div>
    </div>
</div>

{{-- TABLA DINÁMICA --}}
<div class="ii-table-card">
    <div class="ii-card-header">
        <div class="ii-card-title"><i class="fa fa-table" style="color:#2980b9;"></i> Análisis Detallado de Productos</div>
        <button type="button" class="ii-btn ii-btn-export" wire:click="descargarExcel" wire:loading.attr="disabled" wire:target="descargarExcel" @if($tablaPaginada->isEmpty()) disabled @endif>
            <i class="fa fa-file-excel-o" wire:loading.remove wire:target="descargarExcel"></i>
            <i class="fa fa-circle-o-notch fa-spin" wire:loading wire:target="descargarExcel"></i>
            Descargar Excel
        </button>
    </div>
    <div class="ii-tabs">
        <div class="ii-tab {{ $tablaTab === 'criticos' ? 'active' : '' }}" wire:click="$set('tablaTab','criticos')">🔥 Más vendidos</div>
        <div class="ii-tab {{ $tablaTab === 'top_rotacion' ? 'active' : '' }}" wire:click="$set('tablaTab','top_rotacion')">📈 Mayor rotación</div>
        <div class="ii-tab {{ $tablaTab === 'sin_movimiento' ? 'active' : '' }}" wire:click="$set('tablaTab','sin_movimiento')">🛑 Sin movimiento</div>
        <div class="ii-tab {{ $tablaTab === 'mayor_crecimiento' ? 'active' : '' }}" wire:click="$set('tablaTab','mayor_crecimiento')">🚀 Mayor crecimiento</div>
        <div class="ii-tab {{ $tablaTab === 'sin_imagenes' ? 'active' : '' }}" wire:click="$set('tablaTab','sin_imagenes')"><i class="fa fa-picture-o"></i> Productos sin imágenes</div>
        <div class="ii-tab {{ $tablaTab === 'precios' ? 'active' : '' }}" wire:click="$set('tablaTab','precios')"><i class="fa fa-tags"></i> Precios de productos</div>
    </div>
    @if($tablaTab === 'precios')
    <div class="ii-table-filters">
        <div class="ii-table-filter">
            <label for="ii-tipo-cliente">Tipo de cliente</label>
            <select id="ii-tipo-cliente" wire:model="filtroTipoCliente">
                <option value="">Todos los tipos de cliente</option>
                @foreach($tiposCliente as $tipoCliente)
                    <option value="{{ $tipoCliente['id'] }}">{{ $tipoCliente['nombre'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="ii-table-filter" style="flex:1;max-width:420px;">
            <label for="ii-producto-precio">Producto</label>
            <input id="ii-producto-precio" type="search" wire:model.debounce.500ms="filtroProducto" placeholder="Buscar por ID, código de barra o nombre">
        </div>
        <span wire:loading wire:target="filtroTipoCliente,filtroProducto" style="color:#64748b;font-size:11px;padding-bottom:8px;">
            <i class="fa fa-circle-o-notch fa-spin"></i> Filtrando escalas...
        </span>
    </div>
    @endif
    @if($tablaTab === 'sin_imagenes')
    <div class="ii-table-filters">
        <div class="ii-table-filter" style="flex:1;max-width:420px;">
            <label for="ii-producto-sin-imagen">Buscar producto</label>
            <input id="ii-producto-sin-imagen" type="search" wire:model.debounce.500ms="filtroProductoSinImagen" placeholder="Buscar por ID, código o nombre">
        </div>
        <span wire:loading wire:target="filtroProductoSinImagen" style="color:#64748b;font-size:11px;padding-bottom:8px;">
            <i class="fa fa-circle-o-notch fa-spin"></i> Buscando productos...
        </span>
    </div>
    @endif
    <div class="ii-table-wrap">
        <table class="ii-table">
            @if($tablaTab === 'sin_imagenes')
            <thead>
                <tr>
                    <th>#</th><th>ID</th><th>Código de barra</th><th>Producto</th>
                    <th>Categoría</th><th>Marca</th><th>Precio base</th><th>Estado</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tablaPaginada as $i => $prod)
                @php $prod = (object)$prod; @endphp
                <tr>
                    <td style="color:#94a3b8;font-size:11px;">{{ $tablaPaginada->firstItem()+$i }}</td>
                    <td>{{ $prod->id }}</td>
                    <td>{{ $prod->codigo_barra ?: '—' }}</td>
                    <td><span class="prod-name" title="{{ $prod->nombre }}">{{ $prod->nombre }}</span></td>
                    <td><span class="cat-badge">{{ $prod->categoria ?? '—' }}</span></td>
                    <td>{{ $prod->marca ?? '—' }}</td>
                    <td style="font-weight:700;white-space:nowrap;">L {{ number_format($prod->precio_base ?? 0, 2) }}</td>
                    <td><span class="ii-badge ii-badge-{{ (int)$prod->estado_producto_id === 1 ? 'green' : 'gray' }}">{{ $prod->estado }}</span></td>
                    <td style="white-space:nowrap;">
                        <button type="button" class="ii-btn-xs" onclick="abrirModalFotoProducto({{ $prod->id }})" title="Subir imagen"><i class="fa fa-upload"></i> Subir imagen</button>
                        <a href="/reportes/analitica_de_productos/{{ $prod->id }}" target="_blank" class="ii-btn-xs" title="Ver producto"><i class="fa fa-external-link"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="ii-empty"><i class="fa fa-picture-o"></i><p>No hay productos sin imágenes con los filtros actuales</p></td></tr>
                @endforelse
            </tbody>
            @elseif($tablaTab === 'precios')
            <thead>
                <tr>
                    <th>#</th><th>ID</th><th>Código</th><th>Producto</th><th>Marca</th>
                    <th>Tipo de cliente</th><th>Escala</th><th>Precio base</th>
                    <th>Precio A</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tablaPaginada as $i => $prod)
                @php $prod = (object)$prod; @endphp
                <tr>
                    <td style="color:#94a3b8;font-size:11px;">{{ $tablaPaginada->firstItem()+$i }}</td>
                    <td>{{ $prod->id }}</td>
                    <td>{{ $prod->codigo_barra ?: '—' }}</td>
                    <td><span class="prod-name" title="{{ $prod->nombre }}">{{ $prod->nombre }}</span></td>
                    <td>{{ $prod->marca ?? '—' }}</td>
                    <td><span class="cat-badge">{{ $prod->tipo_cliente }}</span></td>
                    <td><span class="ii-badge ii-badge-blue">{{ $prod->escala }}</span></td>
                    <td style="font-weight:700;white-space:nowrap;">L {{ number_format($prod->precio_base_venta ?? 0, 2) }}</td>
                    <td style="white-space:nowrap;">L {{ number_format($prod->precio_a ?? 0, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="9" class="ii-empty"><i class="fa fa-tags"></i><p>No hay escalas de precios con los filtros seleccionados</p></td></tr>
                @endforelse
            </tbody>
            @else
            <thead>
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Unid. Vendidas</th>
                    <th>Rot. Mensual</th>
                    @if($tablaTab === 'mayor_crecimiento')<th>Crecimiento</th>@endif
                    <th>Última Venta</th>
                    <th>Rec. (meses)</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($tablaPaginada as $i => $prod)
                @php
                    $prod = (object)$prod;
                    $rec  = $prod->tiempo_recuperacion_meses ?? null;
                    $uv   = $prod->total_vendido ?? 0;
                    $rm   = $prod->rotacion_mensual ?? 0;
                    if ($tablaTab === 'sin_movimiento')     { $estado='Estancado';    $badge='gray';   $icon='🛑'; }
                    elseif ($tablaTab === 'mayor_crecimiento') { $estado='Crecimiento'; $badge='blue';   $icon='🚀'; }
                    elseif ($rm >= 5)  { $estado='Alta rotación'; $badge='green';  $icon='🔥'; }
                    elseif ($rm >= 2)  { $estado='Normal';        $badge='blue';   $icon='📦'; }
                    else               { $estado='Baja rotación'; $badge='yellow'; $icon='⚠️'; }
                    $ult = $prod->ultima_venta
                        ? \Carbon\Carbon::parse($prod->ultima_venta)->format('d/m/Y')
                        : '—';
                @endphp
                <tr>
                    <td style="color:#94a3b8;font-size:11px;">{{ $i+1 }}</td>
                    <td><span class="prod-name" title="{{ $prod->nombre }}">{{ $prod->nombre }}</span></td>
                    <td><span class="cat-badge">{{ $prod->categoria ?? '—' }}</span></td>
                    <td style="font-weight:700;color:var(--ii-dark);">{{ $uv > 0 ? number_format($uv) : '—' }}</td>
                    <td>
                        @if($rm > 0)
                            <span style="font-weight:700;color:{{ $rm>=5 ? 'var(--ii-green)' : ($rm>=2 ? 'var(--ii-blue)' : 'var(--ii-yellow)') }}">{{ number_format($rm,1) }}x</span>
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                    @if($tablaTab === 'mayor_crecimiento')
                    <td>
                        @if(isset($prod->pct_crecimiento) && $prod->pct_crecimiento > 0)
                            <span class="trend-up">+{{ $prod->pct_crecimiento }}%</span>
                        @else
                            <span class="trend-neu">—</span>
                        @endif
                    </td>
                    @endif
                    <td style="color:#64748b;">{{ $ult }}</td>
                    <td>
                        @if($rec)
                            <span class="ii-badge ii-badge-yellow">{{ $rec }} m</span>
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                    <td><span class="ii-badge ii-badge-{{ $badge }}">{{ $icon }} {{ $estado }}</span></td>
                    <td>
                        <a href="/reportes/analitica_de_productos/{{ $prod->id }}" target="_blank" style="color:#2980b9;font-size:12px;">
                            <i class="fa fa-external-link"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="ii-empty">
                        <i class="fa fa-inbox"></i>
                        <p>No hay datos con los filtros actuales</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @endif
        </table>
    </div>
    @if(in_array($tablaTab, ['sin_imagenes', 'precios']))
    <div class="ii-pagination">
        <span class="ii-pagination-info">
            Mostrando {{ $tablaPaginada->firstItem() ?? 0 }}-{{ $tablaPaginada->lastItem() ?? 0 }} de {{ $tablaPaginada->total() }} registros
        </span>
        {{ $tablaPaginada->onEachSide(1)->links() }}
    </div>
    @endif
</div>

</div>{{-- /ii-content --}}
</div>{{-- /ii-wrap --}}

<div wire:ignore>
    @include('components.producto.modal-subir-fotografia', ['productoId' => null, 'incluirSpinner' => true])
</div>

<script src="{{ asset('js/js_proyecto/inventario/modal-foto-producto.js') }}?v={{ filemtime(public_path('js/js_proyecto/inventario/modal-foto-producto.js')) }}"></script>

<script>
(function () {
    var _c1 = null, _c2 = null, _c3 = null;
    var TENDENCIA    = @json($tendenciaVentas);
    var ROTACION     = @json($rotacionCategorias);
    var DISTRIBUCION = @json($distribucionEstado);
    var MESES = {'01':'Ene','02':'Feb','03':'Mar','04':'Abr','05':'May','06':'Jun',
                 '07':'Jul','08':'Ago','09':'Sep','10':'Oct','11':'Nov','12':'Dic'};

    function fmtP(p) { var s=p.split('-'); return (MESES[s[1]]||s[1])+' '+s[0].slice(2); }

    function destroyAll() {
        if (_c1) { _c1.destroy(); _c1=null; }
        if (_c2) { _c2.destroy(); _c2=null; }
        if (_c3) { _c3.destroy(); _c3=null; }
    }

    function initCharts() {
        destroyAll();

        // Chart 1 — Line: Tendencia ventas
        var el1 = document.getElementById('ii-chartTendencia');
        if (el1 && TENDENCIA.length) {
            _c1 = new Chart(el1.getContext('2d'), {
                type: 'line',
                data: {
                    labels: TENDENCIA.map(function(r){ return fmtP(r.periodo); }),
                    datasets: [{
                        label: 'Ventas (L)',
                        data: TENDENCIA.map(function(r){ return parseFloat(r.monto)||0; }),
                        borderColor: '#2980b9', backgroundColor: 'rgba(41,128,185,.1)',
                        borderWidth: 2.5, fill: true, tension: 0.38,
                        pointBackgroundColor: '#2980b9', pointRadius: 4, pointHoverRadius: 6,
                    }]
                },
                options: {
                    responsive:true, maintainAspectRatio:false,
                    plugins:{ legend:{display:false} },
                    scales:{
                        x:{ grid:{display:false}, ticks:{font:{size:11},color:'#94a3b8'} },
                        y:{ grid:{color:'#f1f5f9'}, ticks:{font:{size:10},color:'#94a3b8',
                            callback:function(v){ return v>=1e6?(v/1e6).toFixed(1)+'M':v>=1e3?(v/1e3).toFixed(0)+'K':v; }
                        }}
                    }
                }
            });
        }

        // Chart 2 — Horizontal Bar: Rotación categorías
        var el2 = document.getElementById('ii-chartRotacion');
        if (el2 && ROTACION.length) {
            var paleta = ['#2980b9','#27ae60','#f39c12','#8e44ad','#e74c3c','#16a085','#d35400','#2c3e50'];
            _c2 = new Chart(el2.getContext('2d'), {
                type: 'horizontalBar',
                data: {
                    labels: ROTACION.map(function(r){ return r.categoria; }),
                    datasets: [{
                        label: 'Unidades vendidas',
                        data: ROTACION.map(function(r){ return parseFloat(r.total_vendido)||0; }),
                        backgroundColor: paleta,
                    }]
                },
                options: {
                    responsive:true, maintainAspectRatio:false,
                    plugins:{ legend:{display:false} },
                    scales:{
                        x:{ grid:{color:'#f1f5f9'}, ticks:{font:{size:10},color:'#94a3b8',
                            callback:function(v){ return v>=1e6?(v/1e6).toFixed(1)+'M':v>=1e3?(v/1e3).toFixed(0)+'K':v; }
                        }},
                        y:{ grid:{display:false}, ticks:{font:{size:10},color:'#475569'} }
                    }
                }
            });
        }

        // Chart 3 — Doughnut: Distribución estado
        var el3 = document.getElementById('ii-chartEstado');
        if (el3 && DISTRIBUCION.length) {
            _c3 = new Chart(el3.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: DISTRIBUCION.map(function(r){ return r.label; }),
                    datasets: [{
                        data: DISTRIBUCION.map(function(r){ return r.valor; }),
                        backgroundColor: DISTRIBUCION.map(function(r){ return r.color; }),
                        borderWidth: 2, borderColor: '#fff', hoverOffset: 8,
                    }]
                },
                options: {
                    responsive:true, maintainAspectRatio:false,
                    cutoutPercentage: 65,
                    plugins:{ legend:{position:'bottom', labels:{font:{size:11},color:'#475569',padding:12,boxWidth:12}} }
                }
            });
        }
    }

    // Arranque
    document.readyState === 'loading'
        ? document.addEventListener('DOMContentLoaded', function(){ setTimeout(initCharts,100); })
        : setTimeout(initCharts, 100);

    // Re-render al actualizar métricas
    window.addEventListener('metricas-actualizadas', function () { setTimeout(initCharts, 100); });

    // Re-render al cambiar filtros (Livewire update)
    document.addEventListener('livewire:load', function () {
        Livewire.hook('message.processed', function (message, component) {
            var name = (component.fingerprint && component.fingerprint.name) || '';
            if (name.toLowerCase().indexOf('analitica') !== -1) {
                try {
                    var d = component.data;
                    if (d.tendenciaVentas)    TENDENCIA    = d.tendenciaVentas;
                    if (d.rotacionCategorias) ROTACION     = d.rotacionCategorias;
                    if (d.distribucionEstado) DISTRIBUCION = d.distribucionEstado;
                } catch(e) {}
                setTimeout(initCharts, 120);
            }
        });
    });
})();
</script>

