<div wire:poll.60s id="dash-root" class="{{ $darkMode ? 'dash-dark' : '' }}">
@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════
   Dashboard Comercial — Base Styles
═══════════════════════════════════════════════════════════════ */
:root {
    --dash-accent:    #1ab394;
    --dash-accent2:   #17a589;
    --dash-orange:    #f39c12;
    --dash-red:       #e74c3c;
    --dash-green:     #27ae60;
    --dash-blue:      #2980b9;
    --dash-bg:        #f4f6f9;
    --dash-card-bg:   #ffffff;
    --dash-border:    #e5e7eb;
    --dash-text:      #1a202c;
    --dash-muted:     #6b7280;
    --dash-shadow:    0 4px 24px rgba(0,0,0,.08);
}
.dash-dark {
    --dash-bg:      #1a1d23;
    --dash-card-bg: #242830;
    --dash-border:  #373d4a;
    --dash-text:    #e2e8f0;
    --dash-muted:   #94a3b8;
    --dash-shadow:  0 4px 24px rgba(0,0,0,.4);
}

/* ── Layout ── */
.dash-wrap { background: var(--dash-bg); min-height: 100vh; padding: 20px 16px 40px; }

/* ── Top bar ── */
.dash-topbar {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px;
    background: var(--dash-card-bg);
    border: 1px solid var(--dash-border);
    border-radius: 14px; padding: 14px 20px;
    margin-bottom: 20px;
    box-shadow: var(--dash-shadow);
}
.dash-topbar-title {
    display: flex; align-items: center; gap: 10px;
    font-size: 17px; font-weight: 700; color: var(--dash-text);
}
.dash-topbar-title i { color: var(--dash-accent); font-size: 20px; }
.dash-role-badge {
    font-size: 11px; font-weight: 600; padding: 3px 12px;
    border-radius: 20px; background: #e6f5f1; color: var(--dash-accent);
    border: 1px solid #b2dfd7;
    letter-spacing: .3px;
}
.dash-topbar-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

/* ── Filtros ── */
.dash-filters {
    background: var(--dash-card-bg);
    border: 1px solid var(--dash-border);
    border-radius: 14px; padding: 16px 20px;
    margin-bottom: 20px;
    box-shadow: var(--dash-shadow);
}
.dash-filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
    align-items: end;
}
.dash-filter-group label {
    display: block; font-size: 11px; font-weight: 600;
    color: var(--dash-muted); margin-bottom: 4px; text-transform: uppercase;
    letter-spacing: .5px;
}
.dash-filter-group { position: relative; }
.dash-filter-group select,
.dash-filter-group input {
    width: 100%; border: 1px solid var(--dash-border);
    border-radius: 8px; padding: 7px 10px; font-size: 13px;
    background: var(--dash-bg); color: var(--dash-text);
    transition: border-color .2s;
    box-shadow: none !important;
}
/* ── Quitar todas las flechas nativas y de Bootstrap/InspineAdmin ── */
.dash-filter-group select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    padding-right: 30px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236b7280' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 12px 8px;
    cursor: pointer;
}
.dash-dark .dash-filter-group select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%2394a3b8' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
}
.dash-filter-group select:focus,
.dash-filter-group input:focus {
    outline: none; border-color: var(--dash-accent);
}

/* ── KPI Grid ── */
.dash-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 14px; margin-bottom: 24px;
}
.dash-kpi {
    background: var(--dash-card-bg);
    border: 1px solid var(--dash-border);
    border-radius: 14px; padding: 18px 20px;
    box-shadow: var(--dash-shadow);
    transition: transform .2s, box-shadow .2s;
    position: relative; overflow: hidden;
}
.dash-kpi:hover { transform: translateY(-3px); box-shadow: 0 8px 32px rgba(0,0,0,.12); }
.dash-kpi::before {
    content: ''; position: absolute; top: 0; left: 0;
    width: 4px; height: 100%;
    background: var(--dash-accent);
    border-radius: 4px 0 0 4px;
}
.dash-kpi.orange::before { background: var(--dash-orange); }
.dash-kpi.red::before    { background: var(--dash-red); }
.dash-kpi.blue::before   { background: var(--dash-blue); }
.dash-kpi.green::before  { background: var(--dash-green); }

.dash-kpi-icon {
    width: 40px; height: 40px;
    border-radius: 10px; background: #e6f5f1;
    display: flex; align-items: center; justify-content: center;
    color: var(--dash-accent); font-size: 17px; margin-bottom: 12px;
}
.dash-kpi.orange .dash-kpi-icon { background: #fef3e0; color: var(--dash-orange); }
.dash-kpi.red    .dash-kpi-icon { background: #fde8e7; color: var(--dash-red); }
.dash-kpi.blue   .dash-kpi-icon { background: #dbeafe; color: var(--dash-blue); }
.dash-kpi.green  .dash-kpi-icon { background: #d1fae5; color: var(--dash-green); }

.dash-kpi-label { font-size: 11px; color: var(--dash-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px; }
.dash-kpi-value { font-size: 22px; font-weight: 800; color: var(--dash-text); line-height: 1.1; }
.dash-kpi-sub   { font-size: 12px; color: var(--dash-muted); margin-top: 4px; }
.dash-up   { color: var(--dash-green); font-weight: 600; }
.dash-down { color: var(--dash-red);   font-weight: 600; }

/* ── Card ── */
.dash-card {
    background: var(--dash-card-bg);
    border: 1px solid var(--dash-border);
    border-radius: 14px; overflow: hidden;
    box-shadow: var(--dash-shadow); margin-bottom: 20px;
}
.dash-card-header {
    padding: 14px 20px;
    background: linear-gradient(135deg, var(--dash-accent), var(--dash-accent2));
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
}
.dash-card-header.orange { background: linear-gradient(135deg,#f39c12,#e67e22); }
.dash-card-header.blue   { background: linear-gradient(135deg,#2980b9,#1a6fa5); }
.dash-card-header.red    { background: linear-gradient(135deg,#c0392b,#e74c3c); }
.dash-card-header.purple { background: linear-gradient(135deg,#8e44ad,#7d3c98); }
.dash-card-header h5 {
    margin: 0; color: #fff; font-size: 14px; font-weight: 700;
    display: flex; align-items: center; gap: 8px;
}
.dash-card-body { padding: 20px; }

/* ── Table ── */
.dash-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.dash-table th {
    background: var(--dash-bg); color: var(--dash-muted);
    font-size: 11px; font-weight: 600; text-transform: uppercase;
    letter-spacing: .4px; padding: 9px 12px; text-align: left;
    border-bottom: 1px solid var(--dash-border);
}
.dash-table td {
    padding: 9px 12px; color: var(--dash-text);
    border-bottom: 1px solid var(--dash-border);
    vertical-align: middle;
}
.dash-table tr:last-child td { border-bottom: none; }
.dash-table tr:hover td { background: rgba(26,179,148,.04); }
.dash-badge {
    display: inline-block; font-size: 10px; font-weight: 700;
    padding: 2px 8px; border-radius: 20px; letter-spacing: .3px;
}
.dash-badge-success { background: #d1fae5; color: #065f46; }
.dash-badge-danger  { background: #fde8e7; color: #9b1c1c; }
.dash-badge-warning { background: #fef3e0; color: #92400e; }

/* ── Botones ── */
.dash-btn {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12px; font-weight: 600; padding: 7px 14px;
    border-radius: 8px; border: none; cursor: pointer;
    transition: all .2s; text-decoration: none;
}
.dash-btn-primary  { background: var(--dash-accent); color: #fff; }
.dash-btn-primary:hover { background: var(--dash-accent2); color:#fff; }
.dash-btn-outline  { background: transparent; border: 1px solid var(--dash-border); color: var(--dash-text); }
.dash-btn-outline:hover  { background: var(--dash-bg); }
.dash-btn-dark { background: #374151; color: #fff; }
.dash-btn-dark:hover { background: #1f2937; color:#fff; }
.dash-btn-export { background: #16a34a; color: #fff; }
.dash-btn-export:hover { background: #15803d; color:#fff; }

/* ── Alert ── */
.dash-alert {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 12px 16px; border-radius: 10px; margin-bottom: 8px;
    font-size: 13px;
}
.dash-alert-warning { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; }
.dash-alert-danger  { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }

/* ── Grids ── */
.dash-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.dash-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
@media (max-width: 900px) { .dash-grid-2, .dash-grid-3 { grid-template-columns: 1fr; } }
@media (max-width: 640px) { .dash-kpi-grid { grid-template-columns: 1fr 1fr; } }

/* ── Progress ── */
.dash-progress { height: 5px; background: var(--dash-border); border-radius: 3px; margin-top: 6px; overflow: hidden; }
.dash-progress-fill { height: 100%; background: var(--dash-accent); border-radius: 3px; transition: width .6s; }

/* ── Auto-refresh badge ── */
.dash-refresh-badge {
    font-size: 10px; color: var(--dash-muted); display: flex;
    align-items: center; gap: 4px;
}
.dash-refresh-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: var(--dash-green); display: inline-block;
    animation: pulse-dot 2s infinite;
}
@keyframes pulse-dot {
    0%,100% { opacity: 1; transform: scale(1); }
    50%      { opacity: .5; transform: scale(.8); }
}
</style>
@endpush

<div class="dash-wrap">

    {{-- ══ TOP BAR ══ --}}
    <div class="dash-topbar">
        <div class="dash-topbar-title">
            <i class="fa fa-area-chart"></i>
            Dashboard Comercial
            <span class="dash-role-badge">{{ $this->rolNombre }}</span>
        </div>
        <div class="dash-topbar-actions">
            <span class="dash-refresh-badge">
                <span class="dash-refresh-dot"></span> Auto-refresh 60s
            </span>

            {{-- Exportar CSV --}}
            <button wire:click="exportarCsv" class="dash-btn dash-btn-export">
                <i class="fa fa-download"></i> CSV
            </button>

            {{-- Modo oscuro --}}
            <button wire:click="$toggle('darkMode')" class="dash-btn dash-btn-dark">
                <i class="fa fa-{{ $darkMode ? 'sun-o' : 'moon-o' }}"></i>
            </button>
        </div>
    </div>

    {{-- ══ FILTROS GLOBALES ══ --}}
    <div class="dash-filters">
        <div style="font-size:12px; font-weight:700; color:var(--dash-muted); text-transform:uppercase; letter-spacing:.5px; margin-bottom:12px;">
            <i class="fa fa-filter"></i> Filtros
        </div>
        <div class="dash-filters-grid">
            <div class="dash-filter-group">
                <label>Mes</label>
                <select wire:model="filtroMes">
                    @foreach(['1'=>'Enero','2'=>'Febrero','3'=>'Marzo','4'=>'Abril','5'=>'Mayo','6'=>'Junio','7'=>'Julio','8'=>'Agosto','9'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'] as $k=>$v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-filter-group">
                <label>Año</label>
                <select wire:model="filtroAnio">
                    @for($y = now()->year; $y >= now()->year - 4; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="dash-filter-group">
                <label>Fecha inicio</label>
                <input type="date" wire:model="fechaInicio">
            </div>
            <div class="dash-filter-group">
                <label>Fecha fin</label>
                <input type="date" wire:model="fechaFin">
            </div>

            @if(in_array($this->rolId, [1,4,8,9,19]))
            <div class="dash-filter-group">
                <label>Vendedor</label>
                <select wire:model="filtroVendedorId">
                    <option value="">— Todos —</option>
                    @foreach($vendedores as $v)
                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="dash-filter-group">
                <label>Top clientes</label>
                <select wire:model="topLimit">
                    <option value="10">Top 10</option>
                    <option value="20">Top 20</option>
                    <option value="50">Top 50</option>
                </select>
            </div>

            <div class="dash-filter-group" style="align-self:end;">
                <button wire:click="$refresh" class="dash-btn dash-btn-primary" style="width:100%;">
                    <i class="fa fa-refresh"></i> Actualizar
                </button>
            </div>
        </div>
    </div>

    {{-- ══ SELECTOR DE VISTAS (solo Administrador) ══ --}}
    @if($rolId === 1)
    <div style="margin-bottom:16px; background:var(--dash-card-bg); border:1px solid var(--dash-border); border-radius:12px; padding:10px 14px; box-shadow:var(--dash-shadow); overflow-x:auto;">
        <div style="display:flex; gap:6px; align-items:center; min-width:max-content;">
            <span style="font-size:11px; font-weight:700; color:var(--dash-muted); text-transform:uppercase; letter-spacing:.5px; white-space:nowrap; margin-right:6px;">
                <i class="fa fa-eye"></i> Vista:
            </span>
            @foreach(\App\Http\Livewire\Dashboard\DashboardComercial::$adminViewOptions as $key => $opt)
            <button wire:click="$set('vistaAdmin', '{{ $key }}')"
                style="
                    display:inline-flex; align-items:center; gap:5px;
                    padding:5px 13px; border-radius:20px; font-size:12px; font-weight:600;
                    border:1px solid {{ $vistaAdmin === $key ? 'var(--dash-accent)' : 'var(--dash-border)' }};
                    background:{{ $vistaAdmin === $key ? 'var(--dash-accent)' : 'transparent' }};
                    color:{{ $vistaAdmin === $key ? '#fff' : 'var(--dash-text)' }};
                    cursor:pointer; transition:all .15s; white-space:nowrap;
                ">
                <i class="fa {{ $opt['icon'] }}"></i> {{ $opt['label'] }}
            </button>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ══ CARGAR VISTA SEGÚN ROL ══ --}}
    @include($rolView, [
        'kpis'           => $kpis,
        'topClientes'    => $topClientes,
        'tendencia'      => $tendencia,
        'productividad'  => $productividad,
        'participacion'  => $participacion,
        'comparativa'    => $comparativa,
        'kpisCobros'     => $kpisCobros,
        'kpisInventario' => $kpisInventario,
        'evolucion'      => $evolucion,
        'periodoLabel'   => $periodoLabel,
    ])

{{-- ── Datos para charts (se actualiza en cada render de Livewire) ── --}}
<span id="dash-data"
    data-cobros='@json($kpisCobros)'
    data-inventario='@json($kpisInventario)'
    data-tendencia='@json($tendencia)'
    data-productividad='@json($productividad)'
    data-participacion='@json($participacion)'
    style="display:none;"></span>

</div>

@push('scripts')
<script>
// ─── Helpers ApexCharts reutilizables ─────────────────────────────────────
const DASH_COLORS = ['#1ab394','#f39c12','#e74c3c','#2980b9','#8e44ad','#27ae60','#e67e22','#c0392b'];
const DASH_FONT   = 'Nunito, -apple-system, sans-serif';

function dashBaseOpts(darkMode) {
    return {
        chart: {
            toolbar: { show: true, tools: { download: true } },
            fontFamily: DASH_FONT,
            animations: { enabled: true, speed: 600 },
            background: 'transparent',
        },
        theme: { mode: darkMode ? 'dark' : 'light' },
        grid: { borderColor: darkMode ? '#373d4a' : '#f0f0f0', strokeDashArray: 3 },
        tooltip: { theme: darkMode ? 'dark' : 'light' },
    };
}

// Formateadores de moneda hondureña (L.)
function lpsK(v) {
    if (v === null || v === undefined || isNaN(v)) return 'L. 0';
    var a = Math.abs(v);
    if (a >= 1000000) return 'L. ' + (v/1000000).toLocaleString('en-US',{minimumFractionDigits:1,maximumFractionDigits:2}) + 'M';
    if (a >= 1000)    return 'L. ' + (v/1000).toLocaleString('en-US',{minimumFractionDigits:0,maximumFractionDigits:2}) + 'k';
    return 'L. ' + Number(v).toLocaleString('en-US',{minimumFractionDigits:0,maximumFractionDigits:2});
}
function lps(v) {
    if (v === null || v === undefined || isNaN(v)) return 'L. 0.00';
    return 'L. ' + Number(v).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
}

// Se llamará desde cada vista parcial
window.DASH_DARK   = {{ $darkMode ? 'true' : 'false' }};
window.DASH_LABEL  = @json($periodoLabel);
// Señal para que los scripts de vistas parciales sepan si Livewire ya cargó
window.DASH_LOADED = false;
document.addEventListener('livewire:load', function() { window.DASH_LOADED = true; });
</script>
@endpush
</div>
