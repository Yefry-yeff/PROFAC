<div>
{{-- ══════════════════════════════════════════════════════════════════════
     ANÁLISIS INDIVIDUAL DE PRODUCTO — Inventory Intelligence Pro
     ══════════════════════════════════════════════════════════════════════ --}}
<style>
:root{
    --ap-bg:#f0f2f8;
    --ap-card:#ffffff;
    --ap-border:#e2e8f0;
    --ap-shadow:0 2px 12px rgba(30,40,80,.08);
    --ap-shadow-lg:0 8px 32px rgba(30,40,80,.14);
    --ap-green:#27ae60; --ap-green-light:rgba(39,174,96,.12);
    --ap-red:#e74c3c;   --ap-red-light:rgba(231,76,60,.12);
    --ap-yellow:#f39c12;--ap-yellow-light:rgba(243,156,18,.12);
    --ap-blue:#2980b9;  --ap-blue-light:rgba(41,128,185,.12);
    --ap-purple:#8e44ad;--ap-purple-light:rgba(142,68,173,.12);
    --ap-gray:#95a5a6;  --ap-gray-light:rgba(149,165,166,.12);
    --ap-dark:#1a2035;
    --ap-radius:14px; --ap-radius-sm:8px;
}

/* ─── BASE ─── */
.ap-wrap{background:var(--ap-bg);min-height:100vh;padding:0 0 48px;}

/* ─── HEADER ─── */
.ap-header{
    background:linear-gradient(135deg,#0f1729 0%,#1e2d5e 50%,#0f1729 100%);
    padding:28px 32px 0; color:#fff; position:relative; overflow:hidden;
}
.ap-header::before{
    content:'';position:absolute;top:-60px;right:-80px;
    width:340px;height:340px;border-radius:50%;
    background:radial-gradient(circle,rgba(41,128,185,.18) 0%,transparent 70%);
}
.ap-header-top{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;position:relative;}
.ap-back-btn{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;
    border-radius:20px;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.08);
    color:rgba(255,255,255,.7);font-size:12px;font-weight:600;cursor:pointer;transition:.2s;text-decoration:none;}
.ap-back-btn:hover{background:rgba(255,255,255,.15);color:#fff;}

.ap-prod-hero{display:flex;align-items:flex-start;gap:20px;padding:24px 0 0;position:relative;}
.ap-prod-img{width:80px;height:80px;border-radius:12px;object-fit:cover;
    border:2px solid rgba(255,255,255,.2);flex-shrink:0;background:rgba(255,255,255,.08);}
.ap-prod-img-placeholder{width:80px;height:80px;border-radius:12px;flex-shrink:0;
    background:rgba(255,255,255,.08);border:2px solid rgba(255,255,255,.15);
    display:flex;align-items:center;justify-content:center;font-size:28px;}
.ap-prod-info{flex:1;min-width:0;}
.ap-prod-name{font-size:22px;font-weight:800;color:#fff;line-height:1.2;margin:0 0 6px;}
.ap-prod-meta{display:flex;flex-wrap:wrap;gap:6px;align-items:center;margin-bottom:10px;}
.ap-meta-pill{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);
    color:rgba(255,255,255,.8);font-size:11px;font-weight:600;padding:3px 10px;border-radius:12px;}
.ap-status-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;
    border-radius:20px;font-size:12px;font-weight:700;letter-spacing:.3px;}

.ap-prod-quick{display:flex;flex-wrap:wrap;gap:16px;padding:16px 0 0;border-top:1px solid rgba(255,255,255,.1);margin-top:16px;}
.ap-quick-stat{display:flex;align-items:center;gap:8px;}
.ap-quick-stat i{width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;
    background:rgba(255,255,255,.1);color:#fff;font-size:12px;}
.ap-quick-val{font-size:15px;font-weight:800;color:#fff;line-height:1;}
.ap-quick-lbl{font-size:10px;color:rgba(255,255,255,.5);font-weight:500;}

.ap-actions-bar{display:flex;flex-wrap:wrap;gap:8px;padding:16px 32px;
    background:rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.08);}
.ap-action-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 16px;
    border-radius:20px;font-size:12px;font-weight:600;border:1px solid rgba(255,255,255,.2);
    background:rgba(255,255,255,.08);color:rgba(255,255,255,.85);cursor:pointer;
    transition:.2s;text-decoration:none;}
.ap-action-btn:hover{background:rgba(255,255,255,.18);color:#fff;transform:translateY(-1px);}
.ap-action-btn.accent{background:#f39c12;border-color:#f39c12;color:#fff;
    box-shadow:0 4px 14px rgba(243,156,18,.35);}
.ap-action-btn.accent:hover{background:#e68e09;}

/* ─── CONTENT ─── */
.ap-content{padding:24px 28px;}

/* ─── KPI GRID ─── */
.ap-kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(165px,1fr));gap:14px;margin-bottom:24px;}
.ap-kpi-card{background:var(--ap-card);border-radius:var(--ap-radius);padding:18px 18px 14px;
    box-shadow:var(--ap-shadow);border:1px solid var(--ap-border);transition:.2s;position:relative;overflow:hidden;}
.ap-kpi-card:hover{transform:translateY(-3px);box-shadow:var(--ap-shadow-lg);}
.ap-kpi-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--kpi-clr,var(--ap-blue));}
.ap-kpi-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;
    justify-content:center;font-size:16px;margin-bottom:10px;
    background:var(--kpi-bg,var(--ap-blue-light));color:var(--kpi-clr,var(--ap-blue));}
.ap-kpi-num{font-size:24px;font-weight:900;color:var(--ap-dark);line-height:1;}
.ap-kpi-lbl{font-size:11px;color:#64748b;margin-top:3px;font-weight:500;}
.ap-kpi-sub{font-size:10px;margin-top:6px;display:flex;align-items:center;gap:4px;font-weight:600;}
.ap-kpi-sub.ok{color:var(--ap-green);} .ap-kpi-sub.warn{color:var(--ap-yellow);}
.ap-kpi-sub.bad{color:var(--ap-red);} .ap-kpi-sub.neu{color:var(--ap-gray);}
.ap-kpi-sub.info{color:var(--ap-blue);}

/* ─── SECTION TITLES ─── */
.ap-section-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;}
.ap-section-title{font-size:14px;font-weight:800;color:var(--ap-dark);display:flex;align-items:center;gap:8px;
    text-transform:uppercase;letter-spacing:.5px;}
.ap-section-sub{font-size:12px;color:#94a3b8;font-weight:400;text-transform:none;letter-spacing:0;}

/* ─── CARDS ─── */
.ap-card{background:var(--ap-card);border-radius:var(--ap-radius);box-shadow:var(--ap-shadow);
    border:1px solid var(--ap-border);overflow:hidden;margin-bottom:0;}
.ap-card-header{padding:14px 20px;border-bottom:1px solid var(--ap-border);
    display:flex;align-items:center;justify-content:space-between;}
.ap-card-title{font-size:13px;font-weight:700;color:var(--ap-dark);display:flex;align-items:center;gap:7px;}
.ap-card-body{padding:20px;}

/* ─── 2-COL GRIDS ─── */
.ap-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px;margin-bottom:24px;}
.ap-row-2{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:24px;}
.ap-row-2-1{display:grid;grid-template-columns:1fr 1.6fr;gap:18px;margin-bottom:24px;}
.ap-row-1-2{display:grid;grid-template-columns:1.6fr 1fr;gap:18px;margin-bottom:24px;}
@media(max-width:1100px){.ap-row-3{grid-template-columns:1fr 1fr;}}
@media(max-width:800px) {.ap-row-3,.ap-row-2,.ap-row-2-1,.ap-row-1-2{grid-template-columns:1fr;}}

/* ─── ROTATION SCORE ─── */
.ap-rot-score{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;}
.ap-gauge-wrap{position:relative;width:130px;height:130px;margin:0 auto 12px;}
.ap-gauge-svg{transform:rotate(-90deg);}
.ap-gauge-track{fill:none;stroke:#eef2f7;stroke-width:10;}
.ap-gauge-fill{fill:none;stroke-width:10;stroke-linecap:round;transition:stroke-dashoffset 1.2s cubic-bezier(.4,0,.2,1);}
.ap-gauge-center{position:absolute;inset:0;display:flex;flex-direction:column;
    align-items:center;justify-content:center;}
.ap-gauge-num{font-size:26px;font-weight:900;line-height:1;}
.ap-gauge-lbl{font-size:10px;color:#94a3b8;}
.ap-rot-class{font-size:13px;font-weight:800;margin-bottom:4px;}
.ap-rot-detail{display:grid;grid-template-columns:1fr 1fr;gap:10px;width:100%;margin-top:12px;}
.ap-rot-stat{background:#f8faff;border-radius:8px;padding:10px;text-align:center;}
.ap-rot-stat-v{font-size:16px;font-weight:800;color:var(--ap-dark);}
.ap-rot-stat-l{font-size:10px;color:#94a3b8;margin-top:2px;}

/* ─── TABLE ─── */
.ap-table-wrap{overflow-x:auto;}
.ap-table{width:100%;border-collapse:collapse;font-size:12px;}
.ap-table thead th{background:linear-gradient(135deg,#1a2035,#2d3561);color:#a8c8e0;
    font-weight:700;padding:10px 14px;text-align:left;white-space:nowrap;font-size:11px;letter-spacing:.3px;}
.ap-table tbody td{padding:9px 14px;border-bottom:1px solid #f1f5f9;color:#334155;vertical-align:middle;}
.ap-table tbody tr:hover td{background:#f8faff;}
.ap-table tbody tr:last-child td{border-bottom:none;}
.ap-table .prod-name{font-weight:600;color:var(--ap-dark);}

/* ─── BADGES ─── */
.ap-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;
    border-radius:12px;font-size:10px;font-weight:700;white-space:nowrap;}
.ap-badge-green {background:#d1f2eb;color:#1a7a50;}
.ap-badge-red   {background:#fde8e8;color:#c0392b;}
.ap-badge-yellow{background:#fef3cd;color:#856404;}
.ap-badge-blue  {background:#dbeafe;color:#1e40af;}
.ap-badge-purple{background:#f3e8ff;color:#6b21a8;}
.ap-badge-gray  {background:#f1f5f9;color:#475569;}

/* ─── TYPE PILL ─── */
.ap-type-pill{display:inline-flex;align-items:center;gap:5px;padding:3px 9px;
    border-radius:12px;font-size:10px;font-weight:700;white-space:nowrap;}

/* ─── PREDICTIVE CARDS ─── */
.ap-pred-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;}
.ap-pred-card{border-radius:12px;padding:18px;border:1px solid var(--ap-border);position:relative;overflow:hidden;}
.ap-pred-card::before{content:'';position:absolute;top:0;left:0;bottom:0;width:4px;background:var(--pred-clr,#2980b9);}
.ap-pred-icon{font-size:22px;margin-bottom:10px;}
.ap-pred-val{font-size:20px;font-weight:900;color:var(--ap-dark);line-height:1;}
.ap-pred-lbl{font-size:12px;font-weight:700;color:var(--ap-dark);margin:4px 0 2px;}
.ap-pred-txt{font-size:11px;color:#64748b;line-height:1.5;}
.ap-pred-action{font-size:10px;font-weight:700;margin-top:8px;padding:3px 8px;
    border-radius:8px;display:inline-block;background:rgba(0,0,0,.05);color:#475569;}

/* ─── ALERTS ─── */
.ap-alert-item{display:grid;grid-template-columns:36px 1fr auto;align-items:center;
    gap:12px;padding:12px 16px;border-bottom:1px solid #f1f5f9;transition:.15s;}
.ap-alert-item:last-child{border-bottom:none;}
.ap-alert-item:hover{background:#f8faff;}
.ap-alert-icon{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;
    justify-content:center;font-size:13px;color:#fff;flex-shrink:0;}
.ap-alert-title{font-size:12px;font-weight:700;color:var(--ap-dark);}
.ap-alert-text{font-size:11px;color:#64748b;margin-top:2px;}
.ap-alert-action{font-size:10px;color:#2980b9;font-weight:600;white-space:nowrap;
    background:#eff6ff;padding:3px 8px;border-radius:8px;cursor:default;}
.ap-priority-dot{width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:4px;}

/* ─── COMPARATIVOS ─── */
.ap-comp-box{background:#f8faff;border-radius:10px;padding:16px;text-align:center;border:1px solid var(--ap-border);}
.ap-comp-label{font-size:11px;color:#94a3b8;font-weight:600;margin-bottom:6px;text-transform:uppercase;letter-spacing:.4px;}
.ap-comp-val{font-size:28px;font-weight:900;color:var(--ap-dark);line-height:1;}
.ap-comp-sub{font-size:11px;margin-top:6px;font-weight:600;}
.ap-comp-change{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;
    border-radius:10px;font-size:11px;font-weight:700;margin-top:6px;}

/* ─── FILTERS BAR ─── */
.ap-filters{display:flex;flex-wrap:wrap;gap:8px;align-items:center;padding:12px 16px;
    background:#f8faff;border-bottom:1px solid var(--ap-border);}
.ap-filter-select,.ap-filter-input{
    border:1px solid var(--ap-border);border-radius:var(--ap-radius-sm);
    padding:6px 10px;font-size:12px;color:var(--ap-dark);outline:none;
    background:#fff;transition:.15s;}
.ap-filter-select:focus,.ap-filter-input:focus{border-color:#2980b9;box-shadow:0 0 0 3px rgba(41,128,185,.1);}

/* ─── STOCK BARS ─── */
.ap-stock-bar-wrap{padding:16px 20px;}
.ap-stock-row{display:flex;align-items:center;gap:10px;margin-bottom:10px;}
.ap-stock-row:last-child{margin-bottom:0;}
.ap-stock-bodega{font-size:11px;font-weight:600;color:var(--ap-dark);width:130px;flex-shrink:0;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ap-stock-bar-bg{flex:1;height:8px;border-radius:4px;background:#eef2f7;overflow:hidden;}
.ap-stock-bar-fill{height:100%;border-radius:4px;background:var(--ap-blue);transition:.8s ease;}
.ap-stock-qty{font-size:11px;font-weight:700;color:var(--ap-dark);width:50px;text-align:right;flex-shrink:0;}

/* ─── EMPTY STATE ─── */
.ap-empty{text-align:center;padding:40px;color:#94a3b8;}
.ap-empty i{font-size:32px;opacity:.35;margin-bottom:10px;display:block;}

/* ─── ANIMATIONS ─── */
.ap-fade-in{animation:apFadeIn .4s ease;}
@keyframes apFadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
.ap-spin{animation:apSpin .7s linear infinite;display:inline-block;}
@keyframes apSpin{from{transform:rotate(0)}to{transform:rotate(360deg)}}

/* ─── RESPONSIVE ─── */
@media(max-width:768px){
    .ap-content{padding:16px;}
    .ap-header{padding:20px 16px 0;}
    .ap-actions-bar{padding:12px 16px;}
    .ap-prod-name{font-size:18px;}
    .ap-kpi-num{font-size:20px;}
}
</style>

@if(!$producto)
<div style="text-align:center;padding:80px 20px;">
    <div style="font-size:48px;margin-bottom:16px;">🔍</div>
    <div style="font-size:18px;font-weight:700;color:#1a2035;margin-bottom:8px;">Producto no encontrado</div>
    <div style="font-size:13px;color:#94a3b8;margin-bottom:20px;">El ID de producto solicitado no existe en el sistema.</div>
    <a href="{{ url('/reportes/analitica_de_productos') }}" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;background:#2980b9;color:#fff;border-radius:20px;text-decoration:none;font-size:13px;font-weight:600;">
        <i class="fa fa-arrow-left"></i> Volver a Analítica
    </a>
</div>
@else
<div class="ap-wrap ap-fade-in">

{{-- ════════════════════════════════════════════════
     1. HEADER DEL PRODUCTO
     ════════════════════════════════════════════════ --}}
<div class="ap-header">
    <div class="ap-header-top">
        <a href="{{ url('/reportes/analitica_de_productos') }}" class="ap-back-btn">
            <i class="fa fa-arrow-left"></i> Inteligencia de Inventario
        </a>
        <div style="display:flex;align-items:center;gap:8px;">
            <button wire:click="actualizar" class="ap-back-btn" style="border-color:rgba(243,156,18,.5);color:#f39c12;">
                <i class="fa fa-refresh" wire:loading.class="ap-spin" wire:target="actualizar"></i>
                Actualizar
            </button>
            <span wire:loading style="color:#f39c12;font-size:12px;">
                <i class="fa fa-circle-o-notch fa-spin"></i>
            </span>
        </div>
    </div>

    <div class="ap-prod-hero">
        @if($imagenProducto)
            <img src="{{ asset('storage/'.$imagenProducto) }}" class="ap-prod-img" alt="{{ $producto['nombre'] }}">
        @else
            <div class="ap-prod-img-placeholder">📦</div>
        @endif

        <div class="ap-prod-info">
            <div class="ap-prod-name">{{ $producto['nombre'] }}</div>
            <div class="ap-prod-meta">
                @if($producto['codigo_barra'])
                    <span class="ap-meta-pill"><i class="fa fa-barcode"></i> {{ $producto['codigo_barra'] }}</span>
                @endif
                @if($producto['marca'])
                    <span class="ap-meta-pill">{{ $producto['marca'] }}</span>
                @endif
                @if($producto['categoria'])
                    <span class="ap-meta-pill">{{ $producto['categoria'] }}</span>
                @endif
                @if($producto['sub_categoria'])
                    <span class="ap-meta-pill" style="background:rgba(255,255,255,.06);">{{ $producto['sub_categoria'] }}</span>
                @endif
                <span class="ap-status-badge" style="background:{{ $estadoColor }}22;border:1px solid {{ $estadoColor }}44;color:{{ $estadoColor }};">
                    {{ $estadoEmoji }} {{ $estadoBadge }}
                </span>
            </div>

            <div class="ap-prod-quick">
                <div class="ap-quick-stat">
                    <div class="ap-quick-stat" style="flex-direction:column;gap:0;">
                        <div class="ap-quick-val">{{ number_format($stockActual) }}</div>
                        <div class="ap-quick-lbl">Stock disponible</div>
                    </div>
                </div>
                <div style="width:1px;background:rgba(255,255,255,.1);"></div>
                @if($proveedorPrincipal)
                <div style="flex-direction:column;gap:0;">
                    <div class="ap-quick-val" style="font-size:12px;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $proveedorPrincipal['nombre'] }}</div>
                    <div class="ap-quick-lbl">Proveedor principal</div>
                </div>
                <div style="width:1px;background:rgba(255,255,255,.1);"></div>
                @endif
                @if($ultimaVenta)
                <div style="flex-direction:column;gap:0;">
                    <div class="ap-quick-val" style="font-size:13px;">{{ \Carbon\Carbon::parse($ultimaVenta['fecha'])->format('d/m/Y') }}</div>
                    <div class="ap-quick-lbl">Última venta</div>
                </div>
                <div style="width:1px;background:rgba(255,255,255,.1);"></div>
                @endif
                @if($ultimaCompra)
                <div style="flex-direction:column;gap:0;">
                    <div class="ap-quick-val" style="font-size:13px;">{{ \Carbon\Carbon::parse($ultimaCompra['fecha'])->format('d/m/Y') }}</div>
                    <div class="ap-quick-lbl">Última compra</div>
                </div>
                @endif
                <div style="width:1px;background:rgba(255,255,255,.1);"></div>
                <div style="flex-direction:column;gap:0;">
                    <div class="ap-quick-val">L {{ number_format($producto['precio_base'], 2) }}</div>
                    <div class="ap-quick-lbl">Precio base</div>
                </div>
            </div>
        </div>

        {{-- Stock por bodega --}}
        @if(count($stockPorBodega) > 0)
        <div style="background:rgba(255,255,255,.06);border-radius:12px;padding:14px 18px;min-width:220px;border:1px solid rgba(255,255,255,.1);">
            <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">
                📍 Ubicación en bodega
            </div>
            @foreach($stockPorBodega as $sb)
            @php $sb = (object)$sb; @endphp
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                <div>
                    <div style="font-size:12px;font-weight:600;color:rgba(255,255,255,.85);">{{ $sb->bodega }}</div>
                    @if($sb->seccion)<div style="font-size:10px;color:rgba(255,255,255,.4);">{{ $sb->seccion }}</div>@endif
                </div>
                <div style="font-size:14px;font-weight:800;color:#f39c12;">{{ number_format($sb->cantidad) }}</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Acciones rápidas --}}
    <div class="ap-actions-bar" style="padding:16px 0;">
        <a href="{{ url('/producto/compra') }}" class="ap-action-btn accent">
            <i class="fa fa-truck"></i> Comprar
        </a>
        <a href="{{ url('/inventario/translado') }}" class="ap-action-btn">
            <i class="fa fa-exchange"></i> Transferir
        </a>
        <a href="{{ url('/inventario/ajustes') }}" class="ap-action-btn">
            <i class="fa fa-sliders"></i> Ajustar inventario
        </a>
        <a href="{{ url('/cardex') }}" class="ap-action-btn">
            <i class="fa fa-history"></i> Ver kardex
        </a>
        <button class="ap-action-btn" onclick="exportarAnalisis()">
            <i class="fa fa-download"></i> Exportar análisis
        </button>
    </div>
</div>{{-- /ap-header --}}

<div class="ap-content">

{{-- ════════════════════════════════════════════════
     2. KPIs INTELIGENTES
     ════════════════════════════════════════════════ --}}
@php
    $k = $kpis;
    $tendDir = ($k['tendencia_pct'] ?? 0) >= 0 ? 'up' : 'down';
    $tendClr = ($k['tendencia_pct'] ?? 0) >= 0 ? 'ok' : 'bad';
    $diasSinMov = $k['dias_sin_mov'] ?? 0;
    $diasEstado = $diasSinMov <= 3 ? ['txt' => 'Excelente', 'cls' => 'ok']
        : ($diasSinMov <= 10  ? ['txt' => 'Normal',    'cls' => 'info']
        : ($diasSinMov <= 30  ? ['txt' => 'Riesgo',    'cls' => 'warn']
        : ['txt' => 'Crítico', 'cls' => 'bad']));
    $cobClr = ($k['cobertura'] ?? 0) > 6 ? 'bad' : (($k['cobertura'] ?? 0) < 1 ? 'warn' : 'ok');
    $diasAg = $k['dias_agotamiento'] ?? null;
    $agEstado = $diasAg === null ? ['txt' => 'Sin datos', 'cls' => 'neu']
        : ($diasAg <= 7  ? ['txt' => "🚨 {$diasAg} días", 'cls' => 'bad']
        : ($diasAg <= 15 ? ['txt' => "⚠️ {$diasAg} días", 'cls' => 'warn']
        : ($diasAg <= 30 ? ['txt' => "{$diasAg} días",    'cls' => 'info']
        : ['txt' => "~{$diasAg} días",  'cls' => 'ok'])));
@endphp

<div class="ap-kpi-grid">
    {{-- Stock actual --}}
    <div class="ap-kpi-card" style="--kpi-clr:#2980b9;--kpi-bg:rgba(41,128,185,.1);">
        <div class="ap-kpi-icon"><i class="fa fa-cubes"></i></div>
        <div class="ap-kpi-num">{{ number_format($stockActual) }}</div>
        <div class="ap-kpi-lbl">Stock disponible</div>
        <div class="ap-kpi-sub {{ $stockActual > 0 ? 'ok' : 'bad' }}">
            <i class="fa fa-{{ $stockActual > 0 ? 'check-circle' : 'exclamation-triangle' }}"></i>
            {{ $stockActual > 0 ? 'En bodega' : 'Sin existencias' }}
        </div>
    </div>
    {{-- Rotación mensual --}}
    <div class="ap-kpi-card" style="--kpi-clr:#e67e22;--kpi-bg:rgba(230,126,34,.1);">
        <div class="ap-kpi-icon"><i class="fa fa-refresh"></i></div>
        <div class="ap-kpi-num">{{ number_format($k['rotacion_mensual'] ?? 0) }}</div>
        <div class="ap-kpi-lbl">Unidades / últimos 30 días</div>
        <div class="ap-kpi-sub info"><i class="fa fa-calendar"></i> Rotación del mes</div>
    </div>
    {{-- Días sin movimiento --}}
    <div class="ap-kpi-card" style="--kpi-clr:{{ $diasEstado['cls']==='bad' ? 'var(--ap-red)' : ($diasEstado['cls']==='warn' ? 'var(--ap-yellow)' : 'var(--ap-green)') }};--kpi-bg:{{ $diasEstado['cls']==='bad' ? 'var(--ap-red-light)' : ($diasEstado['cls']==='warn' ? 'var(--ap-yellow-light)' : 'var(--ap-green-light)') }};">
        <div class="ap-kpi-icon"><i class="fa fa-clock-o"></i></div>
        <div class="ap-kpi-num">{{ $diasSinMov }}</div>
        <div class="ap-kpi-lbl">Días desde última venta</div>
        <div class="ap-kpi-sub {{ $diasEstado['cls'] }}"><i class="fa fa-circle"></i> {{ $diasEstado['txt'] }}</div>
    </div>
    {{-- Cobertura estimada --}}
    <div class="ap-kpi-card" style="--kpi-clr:{{ $cobClr==='bad' ? 'var(--ap-red)' : ($cobClr==='warn' ? 'var(--ap-yellow)' : 'var(--ap-blue)') }};--kpi-bg:{{ $cobClr==='bad' ? 'var(--ap-red-light)' : ($cobClr==='warn' ? 'var(--ap-yellow-light)' : 'var(--ap-blue-light)') }};">
        <div class="ap-kpi-icon"><i class="fa fa-umbrella"></i></div>
        <div class="ap-kpi-num">{{ $k['cobertura'] !== null ? $k['cobertura'].'m' : '—' }}</div>
        <div class="ap-kpi-lbl">Cobertura estimada</div>
        <div class="ap-kpi-sub {{ $cobClr }}">
            <i class="fa fa-info-circle"></i>
            {{ $k['cobertura'] !== null ? 'Stock / promedio mensual' : 'Sin ventas recientes' }}
        </div>
    </div>
    {{-- Promedio mensual --}}
    <div class="ap-kpi-card" style="--kpi-clr:#27ae60;--kpi-bg:rgba(39,174,96,.1);">
        <div class="ap-kpi-icon"><i class="fa fa-bar-chart"></i></div>
        <div class="ap-kpi-num">{{ number_format($k['promedio_mensual'] ?? 0, 1) }}</div>
        <div class="ap-kpi-lbl">Promedio mensual (90 días)</div>
        <div class="ap-kpi-sub info"><i class="fa fa-calculator"></i> Unidades / mes</div>
    </div>
    {{-- Tendencia --}}
    <div class="ap-kpi-card" style="--kpi-clr:{{ ($k['tendencia_pct']??0)>=0 ? 'var(--ap-green)' : 'var(--ap-red)' }};--kpi-bg:{{ ($k['tendencia_pct']??0)>=0 ? 'var(--ap-green-light)' : 'var(--ap-red-light)' }};">
        <div class="ap-kpi-icon"><i class="fa fa-{{ ($k['tendencia_pct']??0)>=0 ? 'arrow-up' : 'arrow-down' }}"></i></div>
        <div class="ap-kpi-num">{{ ($k['tendencia_pct']??0) >= 0 ? '+' : '' }}{{ $k['tendencia_pct'] ?? 0 }}%</div>
        <div class="ap-kpi-lbl">Tendencia de ventas</div>
        <div class="ap-kpi-sub {{ $tendClr }}">
            <i class="fa fa-calendar-check-o"></i> Últimos 30 vs 30 ant.
        </div>
    </div>
    {{-- Predicción agotamiento --}}
    <div class="ap-kpi-card" style="--kpi-clr:{{ $agEstado['cls']==='bad' ? 'var(--ap-red)' : ($agEstado['cls']==='warn' ? 'var(--ap-yellow)' : 'var(--ap-blue)') }};--kpi-bg:{{ $agEstado['cls']==='bad' ? 'var(--ap-red-light)' : ($agEstado['cls']==='warn' ? 'var(--ap-yellow-light)' : 'var(--ap-blue-light)') }};">
        <div class="ap-kpi-icon"><i class="fa fa-hourglass-half"></i></div>
        <div class="ap-kpi-num" style="font-size:{{ strlen($agEstado['txt']) > 8 ? '15px' : '20px' }};">{{ $agEstado['txt'] }}</div>
        <div class="ap-kpi-lbl">Predicción de agotamiento</div>
        <div class="ap-kpi-sub {{ $agEstado['cls'] }}"><i class="fa fa-bolt"></i> Al ritmo actual</div>
    </div>
    {{-- Valor en inventario --}}
    <div class="ap-kpi-card" style="--kpi-clr:#8e44ad;--kpi-bg:rgba(142,68,173,.1);">
        <div class="ap-kpi-icon"><i class="fa fa-money"></i></div>
        <div class="ap-kpi-num" style="font-size:18px;">L {{ number_format($k['valor_inventario'] ?? 0, 0) }}</div>
        <div class="ap-kpi-lbl">Valor en inventario</div>
        <div class="ap-kpi-sub info"><i class="fa fa-tag"></i> Costo promedio × stock</div>
    </div>
</div>

{{-- ════════════════════════════════════════════════
     3. TENDENCIA HISTÓRICA — GRÁFICAS
     ════════════════════════════════════════════════ --}}
<div class="ap-section-head">
    <div class="ap-section-title">
        <i class="fa fa-area-chart" style="color:#2980b9;"></i>
        Tendencia histórica
        <span class="ap-section-sub">— Últimos 12 meses</span>
    </div>
</div>

<div class="ap-row-3" wire:ignore>
    {{-- Gráfica 1: Ventas por mes --}}
    <div class="ap-card">
        <div class="ap-card-header">
            <div class="ap-card-title"><i class="fa fa-line-chart" style="color:#2980b9;"></i> Ventas históricas</div>
            <span style="font-size:10px;color:#94a3b8;">Unidades por mes</span>
        </div>
        <div class="ap-card-body" style="padding:12px 16px 16px;">
            <div id="ap-chart-ventas" style="height:200px;"></div>
        </div>
    </div>
    {{-- Gráfica 2: Compras vs Ventas --}}
    <div class="ap-card">
        <div class="ap-card-header">
            <div class="ap-card-title"><i class="fa fa-balance-scale" style="color:#27ae60;"></i> Compras vs Ventas</div>
            <span style="font-size:10px;color:#94a3b8;">Equilibrio abastecimiento</span>
        </div>
        <div class="ap-card-body" style="padding:12px 16px 16px;">
            <div id="ap-chart-compras-ventas" style="height:200px;"></div>
        </div>
    </div>
    {{-- Gráfica 3: Entradas / Salidas --}}
    <div class="ap-card">
        <div class="ap-card-header">
            <div class="ap-card-title"><i class="fa fa-exchange" style="color:#8e44ad;"></i> Entradas / Salidas</div>
            <span style="font-size:10px;color:#94a3b8;">Movimientos de stock</span>
        </div>
        <div class="ap-card-body" style="padding:12px 16px 16px;">
            <div id="ap-chart-stock-mov" style="height:200px;"></div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════
     4. ANÁLISIS DE ROTACIÓN
     ════════════════════════════════════════════════ --}}
<div class="ap-section-head" style="margin-top:8px;">
    <div class="ap-section-title">
        <i class="fa fa-refresh" style="color:#e67e22;"></i>
        Análisis de rotación
        <span class="ap-section-sub">— Comportamiento anual</span>
    </div>
</div>

@php
    $rd   = $rotacionData;
    $cls  = $rd['clasificacion'] ?? ['label'=>'—','color'=>'#95a5a6','score'=>0,'emoji'=>'📦'];
    $circ = 2 * M_PI * 50;
    $offs = $circ - ($cls['score'] / 100) * $circ;
@endphp

<div class="ap-row-2-1" style="align-items:stretch;">
    {{-- Score / Gauge --}}
    <div class="ap-card">
        <div class="ap-card-header">
            <div class="ap-card-title"><i class="fa fa-tachometer" style="color:#e67e22;"></i> Índice de rotación</div>
        </div>
        <div class="ap-card-body" style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
            <div class="ap-rot-score" style="flex:0 0 160px;">
                <div class="ap-gauge-wrap">
                    <svg class="ap-gauge-svg" width="130" height="130" viewBox="0 0 130 130">
                        <circle class="ap-gauge-track" cx="65" cy="65" r="50"/>
                        <circle class="ap-gauge-fill" cx="65" cy="65" r="50"
                            stroke="{{ $cls['color'] }}"
                            stroke-dasharray="{{ round($circ, 2) }}"
                            stroke-dashoffset="{{ round($offs, 2) }}"/>
                    </svg>
                    <div class="ap-gauge-center">
                        <div class="ap-gauge-num" style="color:{{ $cls['color'] }}">{{ $cls['score'] }}</div>
                        <div class="ap-gauge-lbl">/ 100</div>
                    </div>
                </div>
                <div class="ap-rot-class" style="color:{{ $cls['color'] }}">{{ $cls['emoji'] }} {{ $cls['label'] }}</div>
            </div>
            <div class="ap-rot-detail" style="flex:1;min-width:220px;">
                <div class="ap-rot-stat">
                    <div class="ap-rot-stat-v">{{ $rd['indice_rotacion'] ?? '—' }}x</div>
                    <div class="ap-rot-stat-l">Índice anualizado</div>
                </div>
                <div class="ap-rot-stat">
                    <div class="ap-rot-stat-v">{{ $rd['promedio_mensual_12m'] ?? '—' }}</div>
                    <div class="ap-rot-stat-l">Promedio mensual</div>
                </div>
                <div class="ap-rot-stat">
                    <div class="ap-rot-stat-v">{{ $rd['meses_con_venta'] ?? 0 }}/12</div>
                    <div class="ap-rot-stat-l">Meses activos</div>
                </div>
                <div class="ap-rot-stat">
                    <div class="ap-rot-stat-v">
                        {{ ($rd['dias_entre_ventas'] ?? 0) > 0 ? $rd['dias_entre_ventas'].'d' : '—' }}
                    </div>
                    <div class="ap-rot-stat-l">Días entre ventas</div>
                </div>
                <div class="ap-rot-stat" style="grid-column:span 2;">
                    <div style="font-size:11px;color:#64748b;">
                        <strong style="color:#27ae60;">📈 Mes fuerte:</strong> {{ $rd['mes_mayor'] ?? '—' }}
                    </div>
                    <div style="font-size:11px;color:#64748b;margin-top:4px;">
                        <strong style="color:#e74c3c;">📉 Mes débil:</strong> {{ $rd['mes_menor'] ?? '—' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Total 12 meses --}}
    <div class="ap-card">
        <div class="ap-card-header">
            <div class="ap-card-title"><i class="fa fa-calendar" style="color:#2980b9;"></i> Resumen 12 meses</div>
        </div>
        <div class="ap-card-body" style="display:flex;flex-direction:column;gap:16px;">
            <div style="text-align:center;background:linear-gradient(135deg,#1a2035,#2d3561);border-radius:10px;padding:20px;">
                <div style="font-size:36px;font-weight:900;color:#fff;">{{ number_format($rd['total_unidades_12m'] ?? 0) }}</div>
                <div style="font-size:12px;color:rgba(255,255,255,.5);margin-top:4px;">unidades vendidas en 12 meses</div>
            </div>
            @if(count($stockPorBodega) > 0)
            <div>
                <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px;margin-bottom:10px;">
                    Distribución de stock por bodega
                </div>
                @php $maxStock = max(array_column($stockPorBodega, 'cantidad') ?: [1]); @endphp
                @foreach($stockPorBodega as $sb)
                @php $sb = (object)$sb; $pct = $maxStock > 0 ? round(($sb->cantidad / $maxStock) * 100) : 0; @endphp
                <div class="ap-stock-row">
                    <div class="ap-stock-bodega">{{ $sb->bodega }}</div>
                    <div class="ap-stock-bar-bg"><div class="ap-stock-bar-fill" style="width:{{ $pct }}%;"></div></div>
                    <div class="ap-stock-qty">{{ number_format($sb->cantidad) }}</div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════
     5. MOVIMIENTOS RECIENTES
     ════════════════════════════════════════════════ --}}
<div class="ap-section-head">
    <div class="ap-section-title">
        <i class="fa fa-history" style="color:#8e44ad;"></i>
        Movimientos recientes
        <span class="ap-section-sub">— Últimos 50 registros del kardex</span>
    </div>
</div>

<div class="ap-card" style="margin-bottom:24px;">
    <div class="ap-filters">
        <select class="ap-filter-select" wire:model="filtroMovTipo">
            <option value="">Todos los tipos</option>
            <option value="venta">Ventas</option>
            <option value="compra">Compras</option>
            <option value="ajuste">Ajustes</option>
            <option value="traslado">Traslados</option>
            <option value="devolucion">Devoluciones</option>
            <option value="credito">Notas de crédito</option>
        </select>
        <input type="date" class="ap-filter-input" wire:model="filtroMovFechaInicio">
        <span style="font-size:11px;color:#94a3b8;">—</span>
        <input type="date" class="ap-filter-input" wire:model="filtroMovFechaFin">
        <span wire:loading wire:target="filtroMovTipo,filtroMovFechaInicio,filtroMovFechaFin">
            <i class="fa fa-circle-o-notch fa-spin" style="color:#2980b9;"></i>
        </span>
    </div>
    <div class="ap-table-wrap">
        <table class="ap-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Documento</th>
                    <th>Descripción</th>
                    <th style="text-align:right;color:#27ae60;">Entrada</th>
                    <th style="text-align:right;color:#e74c3c;">Salida</th>
                    <th style="text-align:right;">Stock</th>
                    <th>Bodega</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movimientosRecientes as $mov)
                @php $mov = (object)$mov; @endphp
                <tr>
                    <td style="white-space:nowrap;color:#64748b;font-size:11px;">
                        {{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        <span class="ap-type-pill" style="background:{{ $mov->color }}22;color:{{ $mov->color }};border:1px solid {{ $mov->color }}33;">
                            <i class="fa {{ $mov->icono }}"></i>
                            {{ $mov->tipo }}
                        </span>
                    </td>
                    <td style="font-size:11px;font-weight:600;color:#334155;">{{ $mov->documento }}</td>
                    <td style="font-size:11px;color:#64748b;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $mov->descripcion }}">{{ $mov->descripcion }}</td>
                    <td style="text-align:right;font-weight:700;color:#27ae60;">
                        {{ $mov->entrada > 0 ? '+'.number_format($mov->entrada) : '' }}
                    </td>
                    <td style="text-align:right;font-weight:700;color:#e74c3c;">
                        {{ $mov->salida > 0 ? '-'.number_format($mov->salida) : '' }}
                    </td>
                    <td style="text-align:right;font-weight:800;color:#1a2035;">{{ number_format($mov->stock) }}</td>
                    <td style="font-size:11px;color:#64748b;white-space:nowrap;">
                        {{ $mov->bodega_origen ?: ($mov->bodega_destino ?: '—') }}
                    </td>
                    <td style="font-size:11px;color:#94a3b8;">{{ $mov->usuario ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="ap-empty">
                        <i class="fa fa-inbox"></i>
                        <p>Sin movimientos con los filtros aplicados</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ════════════════════════════════════════════════
     6. ANÁLISIS PREDICTIVO  +  7. ALERTAS
     ════════════════════════════════════════════════ --}}
<div class="ap-row-2" style="align-items:start;">

    {{-- 6. PREDICTIVO --}}
    <div>
        <div class="ap-section-head">
            <div class="ap-section-title">
                <i class="fa fa-magic" style="color:#2980b9;"></i>
                Análisis predictivo
                <span class="ap-section-sub">— IA de inventario</span>
            </div>
        </div>
        @php $pred = $analisisPredictivo; @endphp
        <div class="ap-pred-grid">
            {{-- Agotamiento --}}
            @php
                $dA = $pred['dias_agotamiento'] ?? null;
                $pClr = $dA !== null && $dA <= 7  ? '#e74c3c'
                      : ($dA !== null && $dA <= 15 ? '#e67e22'
                      : ($dA !== null && $dA <= 30 ? '#f39c12' : '#27ae60'));
            @endphp
            <div class="ap-pred-card" style="--pred-clr:{{ $pClr }};background:{{ $pClr }}08;">
                <div class="ap-pred-icon">⏳</div>
                <div class="ap-pred-val" style="color:{{ $pClr }};">
                    {{ $dA !== null ? $dA.' días' : 'Stock suficiente' }}
                </div>
                <div class="ap-pred-lbl">Predicción de agotamiento</div>
                <div class="ap-pred-txt">
                    @if($dA !== null && $dA <= 30)
                        Con el ritmo actual el producto se agotará en <strong>{{ $dA }} días</strong>.
                    @elseif($dA !== null)
                        El stock alcanzará aproximadamente por {{ round($dA/30, 1) }} meses.
                    @else
                        Sin ventas recientes para proyectar.
                    @endif
                </div>
                <div class="ap-pred-action">Ritmo: {{ $pred['ritmo_diario'] ?? 0 }} uds/día</div>
            </div>
            {{-- Recomendación compra --}}
            @php $cantC = $pred['cantidad_comprar'] ?? 0; @endphp
            <div class="ap-pred-card" style="--pred-clr:#27ae60;background:#27ae6008;">
                <div class="ap-pred-icon">🛒</div>
                <div class="ap-pred-val" style="color:#27ae60;">
                    {{ $cantC > 0 ? number_format($cantC).' uds' : 'Stock OK' }}
                </div>
                <div class="ap-pred-lbl">Recomendación de compra</div>
                <div class="ap-pred-txt">
                    @if($cantC > 0)
                        Se recomienda comprar <strong>{{ number_format($cantC) }} unidades</strong> para cubrir la demanda proyectada.
                    @else
                        El nivel de stock actual cubre la demanda estimada.
                    @endif
                </div>
                <div class="ap-pred-action">Tiempo recuperación: {{ $producto['tiempo_recuperacion_meses'] ?? 1 }} mes(es)</div>
            </div>
            {{-- Sobreinventario --}}
            <div class="ap-pred-card" style="--pred-clr:{{ !empty($pred['sobreinventario']) ? '#2980b9' : '#27ae60' }};background:{{ !empty($pred['sobreinventario']) ? '#2980b908' : '#27ae6008' }};">
                <div class="ap-pred-icon">📦</div>
                <div class="ap-pred-val" style="color:{{ !empty($pred['sobreinventario']) ? '#2980b9' : '#27ae60' }};">
                    {{ $pred['meses_cubiertos'] !== null ? $pred['meses_cubiertos'].'m' : '—' }}
                </div>
                <div class="ap-pred-lbl">Riesgo de sobreinventario</div>
                <div class="ap-pred-txt">
                    @if(!empty($pred['sobreinventario']))
                        Hay <strong>{{ $pred['meses_cubiertos'] }} meses</strong> de inventario acumulado. Capital inmovilizado.
                    @else
                        El nivel de cobertura está dentro del rango saludable.
                    @endif
                </div>
                <div class="ap-pred-action">Cobertura: {{ $pred['meses_cubiertos'] ?? '—' }} meses</div>
            </div>
            {{-- Tendencia futura --}}
            @php $tp = $pred['tendencia_pct'] ?? 0; @endphp
            <div class="ap-pred-card" style="--pred-clr:{{ $tp >= 0 ? '#27ae60' : '#e74c3c' }};background:{{ $tp >= 0 ? '#27ae6008' : '#e74c3c08' }};">
                <div class="ap-pred-icon">{{ $tp >= 0 ? '📈' : '📉' }}</div>
                <div class="ap-pred-val" style="color:{{ $tp >= 0 ? '#27ae60' : '#e74c3c' }};">
                    {{ $tp >= 0 ? '+' : '' }}{{ $tp }}%
                </div>
                <div class="ap-pred-lbl">Tendencia futura</div>
                <div class="ap-pred-txt">
                    @if($tp >= 20)
                        Se proyecta <strong>incremento</strong> de ventas para el próximo mes. Proyección: <strong>{{ number_format($pred['proyeccion_mes'] ?? 0) }}</strong> uds.
                    @elseif($tp <= -20)
                        Se proyecta <strong>caída</strong> en ventas. Proyección: <strong>{{ number_format($pred['proyeccion_mes'] ?? 0) }}</strong> uds.
                    @else
                        Ventas estables. Proyección próximo mes: <strong>{{ number_format($pred['proyeccion_mes'] ?? 0) }}</strong> uds.
                    @endif
                </div>
                <div class="ap-pred-action">{{ $pred['riesgo']['texto'] ?? '' }}</div>
            </div>
        </div>
    </div>

    {{-- 7. ALERTAS --}}
    <div>
        <div class="ap-section-head">
            <div class="ap-section-title">
                <i class="fa fa-bell" style="color:#f39c12;"></i>
                Alertas inteligentes
                <span class="ap-section-sub">— {{ count($alertasProducto) }} activas</span>
            </div>
        </div>
        <div class="ap-card">
            @forelse($alertasProducto as $alerta)
            <div class="ap-alert-item">
                <div class="ap-alert-icon" style="background:{{ $alerta['color'] }};flex-shrink:0;">
                    <i class="fa {{ $alerta['icono'] }}"></i>
                </div>
                <div style="min-width:0;">
                    <div class="ap-alert-title">
                        <span class="ap-priority-dot" style="background:{{ $alerta['color'] }};"></span>
                        {{ $alerta['titulo'] }}
                        <span class="ap-badge ap-badge-{{ $alerta['prioridad']==='crítica' ? 'red' : ($alerta['prioridad']==='alta' ? 'red' : ($alerta['prioridad']==='media' ? 'yellow' : 'blue')) }}" style="font-size:9px;padding:1px 6px;margin-left:4px;">{{ ucfirst($alerta['prioridad']) }}</span>
                    </div>
                    <div class="ap-alert-text">{{ $alerta['texto'] }}</div>
                    <div style="font-size:10px;color:#2980b9;margin-top:3px;font-weight:600;">
                        <i class="fa fa-lightbulb-o"></i> {{ $alerta['accion'] }}
                    </div>
                </div>
            </div>
            @empty
            <div class="ap-empty" style="padding:32px;">
                <i class="fa fa-check-circle" style="color:#27ae60;opacity:1;font-size:28px;margin-bottom:10px;display:block;"></i>
                <p style="margin:0;font-size:13px;color:#27ae60;font-weight:700;">Sin alertas activas</p>
                <p style="font-size:12px;color:#94a3b8;margin:4px 0 0;">El producto está en condiciones normales</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════
     8. COMPARATIVOS HISTÓRICOS
     ════════════════════════════════════════════════ --}}
<div class="ap-section-head">
    <div class="ap-section-title">
        <i class="fa fa-line-chart" style="color:#27ae60;"></i>
        Comparativos históricos
        <span class="ap-section-sub">— Análisis de tendencia temporal</span>
    </div>
</div>

@php
    $comp = $comparativos;
    $meses = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    $pctA = $comp['pct_anual'] ?? null;
    $pctM = $comp['pct_mensual'] ?? null;
@endphp

<div class="ap-row-3" style="margin-bottom:16px;">
    {{-- Comparativo anual --}}
    <div class="ap-comp-box">
        <div class="ap-comp-label">{{ $comp['anio_anterior'] ?? '' }}</div>
        <div class="ap-comp-val" style="color:#94a3b8;">{{ number_format($comp['ventas_anio_ant'] ?? 0) }}</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px;">unidades</div>

        @if($pctA !== null)
        <div class="ap-comp-change" style="background:{{ $pctA>=0?'#d1f2eb':'#fde8e8' }};color:{{ $pctA>=0?'#1a7a50':'#c0392b' }};">
            <i class="fa fa-{{ $pctA>=0?'arrow-up':'arrow-down' }}"></i>
            {{ abs($pctA) }}%
        </div>
        @endif

        <div class="ap-comp-label" style="margin-top:12px;">{{ $comp['anio_actual'] ?? '' }}</div>
        <div class="ap-comp-val" style="color:{{ $pctA!==null&&$pctA>=0 ? '#27ae60' : '#2980b9' }};">{{ number_format($comp['ventas_anio_actual'] ?? 0) }}</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px;">unidades</div>
        <div style="font-size:10px;color:#94a3b8;margin-top:8px;font-weight:600;">Comparativo anual</div>
    </div>

    {{-- Comparativo mensual --}}
    @php $ma = $comp['mes_actual'] ?? now()->month; $mant = $comp['mes_anterior'] ?? (now()->month-1>0?now()->month-1:12); @endphp
    <div class="ap-comp-box">
        <div class="ap-comp-label">{{ $meses[$mant] ?? '' }}</div>
        <div class="ap-comp-val" style="color:#94a3b8;">{{ number_format($comp['ventas_mes_ant'] ?? 0) }}</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px;">unidades</div>

        @if($pctM !== null)
        <div class="ap-comp-change" style="background:{{ $pctM>=0?'#d1f2eb':'#fde8e8' }};color:{{ $pctM>=0?'#1a7a50':'#c0392b' }};">
            <i class="fa fa-{{ $pctM>=0?'arrow-up':'arrow-down' }}"></i>
            {{ abs($pctM) }}%
        </div>
        @endif

        <div class="ap-comp-label" style="margin-top:12px;">{{ $meses[$ma] ?? '' }}</div>
        <div class="ap-comp-val" style="color:{{ $pctM!==null&&$pctM>=0 ? '#27ae60' : '#2980b9' }};">{{ number_format($comp['ventas_mes_actual'] ?? 0) }}</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:2px;">unidades</div>
        <div style="font-size:10px;color:#94a3b8;margin-top:8px;font-weight:600;">Comparativo mensual</div>
    </div>

    {{-- Estacionalidad --}}
    <div class="ap-comp-box" style="text-align:left;">
        <div class="ap-comp-label" style="margin-bottom:10px;">Estacionalidad</div>
        @php
            $est = $comp['estacional'] ?? [];
            $maxEst = count($est) ? max(array_column($est, 'promedio') ?: [1]) : 1;
        @endphp
        @if(count($est))
            @foreach($est as $e)
            @php $e=(object)$e; $pctBar=round(($e->promedio/$maxEst)*100); @endphp
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                <div style="width:26px;font-size:10px;color:#64748b;font-weight:600;">{{ $meses[(int)$e->mes_num] ?? '' }}</div>
                <div style="flex:1;height:6px;border-radius:3px;background:#eef2f7;overflow:hidden;">
                    <div style="width:{{ $pctBar }}%;height:100%;background:{{ $pctBar>=80 ? '#27ae60' : ($pctBar>=50 ? '#2980b9' : '#f39c12') }};border-radius:3px;"></div>
                </div>
                <div style="width:36px;font-size:10px;font-weight:700;color:#334155;text-align:right;">{{ number_format($e->promedio,0) }}</div>
            </div>
            @endforeach
        @else
            <div style="font-size:12px;color:#94a3b8;padding:16px 0;">Sin datos suficientes</div>
        @endif
    </div>
</div>

{{-- Gráfica comparativa anual por mes (wire:ignore para ApexCharts) --}}
<div class="ap-card" wire:ignore>
    <div class="ap-card-header">
        <div class="ap-card-title">
            <i class="fa fa-bar-chart" style="color:#27ae60;"></i>
            {{ $comp['anio_anterior'] ?? '' }} vs {{ $comp['anio_actual'] ?? '' }} — Ventas por mes
        </div>
    </div>
    <div class="ap-card-body" style="padding:12px 16px 16px;">
        <div id="ap-chart-comparativo" style="height:220px;"></div>
    </div>
</div>

</div>{{-- /ap-content --}}
</div>{{-- /ap-wrap --}}

{{-- ═══════════════════════════════════════════════════════
     JAVASCRIPT — ApexCharts
     ═══════════════════════════════════════════════════════ --}}
<script>
(function(){
    var _c1=null,_c2=null,_c3=null,_c4=null;

    var TENDENCIA     = @json($tendenciaVentas);
    var COMP_VS       = @json($comprasVsVentas);
    var MOV_STOCK     = @json($movimientosStockData);
    var COMP_ANUAL    = @json($comparativos['comparativo_anual'] ?? []);
    var ANIO_ACTUAL   = @json($comparativos['anio_actual'] ?? date('Y'));
    var ANIO_ANT      = @json($comparativos['anio_anterior'] ?? (date('Y')-1));

    var MESES = {'01':'Ene','02':'Feb','03':'Mar','04':'Abr','05':'May','06':'Jun',
                 '07':'Jul','08':'Ago','09':'Sep','10':'Oct','11':'Nov','12':'Dic'};
    var MESES_ARR = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

    function fmtP(p){ var s=p.split('-'); return (MESES[s[1]]||s[1])+' '+s[0].slice(2); }

    var apexDef = {
        chart:{ toolbar:{show:false}, fontFamily:'inherit', animations:{enabled:true,easing:'easeinout',speed:600} },
        tooltip:{ theme:'light' },
        grid:{ borderColor:'#f1f5f9', strokeDashArray:3, yaxis:{lines:{show:true}}, xaxis:{lines:{show:false}} },
        dataLabels:{ enabled:false },
    };

    function destroyAll(){
        if(_c1){ try{_c1.destroy();}catch(e){} _c1=null; }
        if(_c2){ try{_c2.destroy();}catch(e){} _c2=null; }
        if(_c3){ try{_c3.destroy();}catch(e){} _c3=null; }
        if(_c4){ try{_c4.destroy();}catch(e){} _c4=null; }
    }

    function initCharts(){
        destroyAll();

        /* ── Chart 1: Ventas históricas (Area) ── */
        var el1 = document.getElementById('ap-chart-ventas');
        if(el1 && typeof ApexCharts !== 'undefined'){
            var labels1 = TENDENCIA.map(function(r){ return fmtP(r.periodo); });
            var data1   = TENDENCIA.map(function(r){ return parseFloat(r.unidades)||0; });
            _c1 = new ApexCharts(el1, Object.assign({}, apexDef, {
                chart: Object.assign({}, apexDef.chart, { type:'area', height:200 }),
                series:[{ name:'Unidades vendidas', data: data1 }],
                labels: labels1,
                colors:['#2980b9'],
                fill:{ type:'gradient', gradient:{ shadeIntensity:.9, opacityFrom:.5, opacityTo:.05, stops:[0,100] }},
                stroke:{ curve:'smooth', width:2.5 },
                xaxis:{ categories: labels1, labels:{ style:{fontSize:'10px',colors:'#94a3b8'} }, axisBorder:{show:false} },
                yaxis:{ labels:{ style:{fontSize:'10px',colors:'#94a3b8'}, formatter:function(v){ return v>=1000?(v/1000).toFixed(0)+'K':v; } }},
                markers:{ size:3, strokeWidth:0, fillOpacity:1 },
            }));
            _c1.render();
        }

        /* ── Chart 2: Compras vs Ventas (Bar) ── */
        var el2 = document.getElementById('ap-chart-compras-ventas');
        if(el2 && typeof ApexCharts !== 'undefined'){
            var labels2 = COMP_VS.map(function(r){ return fmtP(r.periodo); });
            _c2 = new ApexCharts(el2, Object.assign({}, apexDef, {
                chart: Object.assign({}, apexDef.chart, { type:'bar', height:200, stacked:false }),
                series:[
                    { name:'Entradas (compra)', data: COMP_VS.map(function(r){ return r.unidades_compra||0; }) },
                    { name:'Salidas (venta)',   data: COMP_VS.map(function(r){ return r.unidades_venta||0; }) },
                ],
                colors:['#27ae60','#e74c3c'],
                plotOptions:{ bar:{ borderRadius:3, columnWidth:'60%', grouped:true }},
                xaxis:{ categories: labels2, labels:{ style:{fontSize:'10px',colors:'#94a3b8'}, rotate:-30 }, axisBorder:{show:false} },
                yaxis:{ labels:{ style:{fontSize:'10px',colors:'#94a3b8'}, formatter:function(v){ return v>=1000?(v/1000).toFixed(0)+'K':v; } }},
                legend:{ position:'top', fontSize:'11px', markers:{radius:4}, labels:{colors:'#475569'} },
            }));
            _c2.render();
        }

        /* ── Chart 3: Entradas / Salidas stock (Bar apilado) ── */
        var el3 = document.getElementById('ap-chart-stock-mov');
        if(el3 && typeof ApexCharts !== 'undefined'){
            var labels3 = MOV_STOCK.map(function(r){ return fmtP(r.periodo); });
            _c3 = new ApexCharts(el3, Object.assign({}, apexDef, {
                chart: Object.assign({}, apexDef.chart, { type:'bar', height:200, stacked:true }),
                series:[
                    { name:'Entradas', data: MOV_STOCK.map(function(r){ return parseInt(r.entradas)||0; }) },
                    { name:'Salidas',  data: MOV_STOCK.map(function(r){ return -(parseInt(r.salidas)||0); }) },
                ],
                colors:['#27ae60','#e74c3c'],
                plotOptions:{ bar:{ borderRadius:2, columnWidth:'70%' }},
                xaxis:{ categories: labels3, labels:{ style:{fontSize:'10px',colors:'#94a3b8'} }, axisBorder:{show:false} },
                yaxis:{ labels:{ style:{fontSize:'10px',colors:'#94a3b8'}, formatter:function(v){ return Math.abs(v)>=1000?(Math.abs(v)/1000).toFixed(0)+'K':Math.abs(v); } }},
                legend:{ position:'top', fontSize:'11px', markers:{radius:4}, labels:{colors:'#475569'} },
                tooltip:{ y:{ formatter:function(v){ return Math.abs(v)+' uds'; } }},
            }));
            _c3.render();
        }

        /* ── Chart 4: Comparativo anual por mes ── */
        var el4 = document.getElementById('ap-chart-comparativo');
        if(el4 && COMP_ANUAL.length && typeof ApexCharts !== 'undefined'){
            _c4 = new ApexCharts(el4, Object.assign({}, apexDef, {
                chart: Object.assign({}, apexDef.chart, { type:'line', height:220 }),
                series:[
                    { name: String(ANIO_ANT),    data: COMP_ANUAL.map(function(r){ return r.anio_ant||0; }) },
                    { name: String(ANIO_ACTUAL), data: COMP_ANUAL.map(function(r){ return r.anio_actual||0; }) },
                ],
                colors:['#94a3b8','#2980b9'],
                stroke:{ curve:'smooth', width:[2,3], dashArray:[4,0] },
                markers:{ size:[3,4], strokeWidth:0 },
                xaxis:{
                    categories: MESES_ARR,
                    labels:{ style:{fontSize:'11px',colors:'#94a3b8'} },
                    axisBorder:{show:false}
                },
                yaxis:{ labels:{ style:{fontSize:'10px',colors:'#94a3b8'}, formatter:function(v){ return v>=1000?(v/1000).toFixed(0)+'K':v; } }},
                legend:{ position:'top', fontSize:'11px', markers:{radius:4}, labels:{colors:'#475569'} },
                fill:{ type:['solid','solid'] },
            }));
            _c4.render();
        }
    }

    // Arranque
    document.readyState === 'loading'
        ? document.addEventListener('DOMContentLoaded', function(){ setTimeout(initCharts,150); })
        : setTimeout(initCharts, 150);

    // Re-render al actualizar
    window.addEventListener('analisis-actualizado', function(){ setTimeout(initCharts,200); });

    // Livewire re-render
    document.addEventListener('livewire:load', function(){
        Livewire.hook('message.processed', function(message, component){
            var name = (component.fingerprint && component.fingerprint.name) || '';
            if(name.toLowerCase().indexOf('analisis') !== -1 && name.toLowerCase().indexOf('producto') !== -1){
                try{
                    var d = component.data;
                    if(d.tendenciaVentas)      TENDENCIA   = d.tendenciaVentas;
                    if(d.comprasVsVentas)      COMP_VS     = d.comprasVsVentas;
                    if(d.movimientosStockData) MOV_STOCK   = d.movimientosStockData;
                    if(d.comparativos && d.comparativos.comparativo_anual) COMP_ANUAL = d.comparativos.comparativo_anual;
                }catch(e){}
                setTimeout(initCharts, 200);
            }
        });
    });

    // Exportar análisis (imprimir)
    window.exportarAnalisis = function(){
        window.print();
    };
})();
</script>

<script>
// Exportar análisis - definido fuera del IIFE para garantizar disponibilidad
window.exportarAnalisis = function(){ window.print(); };
</script>

@endif
</div>
