<div>
    @push('styles')
    <style>
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
        @media (max-width: 767.5px) { .hide-container { display: none; } }
        .center-div { text-align: center; }
        .img-size { width: 100%; height: 16rem; margin: 0 auto; object-fit: contain; }
        .tipo-factura-selector .btn { margin-right: 5px; margin-bottom: 5px; }
        .tipo-factura-selector .btn.active { box-shadow: 0 0 0 3px rgba(0,123,255,.5); }

        /* ── Pedido panel ─────────────────────────────────────────── */
        .pedido-link-panel {
            border: 2px dashed #b2dfdb;
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 24px;
            background: #f0fdf4;
        }
        .pedido-link-panel.linked { border: 2px solid #00897b; background: #e8f5e9; }
        .ped-row {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 14px; border-radius: 10px;
            background: #fff; border: 1px solid #e0f2f1;
            margin-bottom: 6px; cursor: pointer;
            transition: box-shadow .15s, border-color .15s;
        }
        .ped-row:hover { box-shadow: 0 3px 12px rgba(0,0,0,.09); border-color: #80cbc4; }

        /* ── Section headers ──────────────────────────────────────── */
        .ofr-section-header {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 18px; border-radius: 10px;
            margin: 20px 0 16px;
            font-weight: 700; font-size: 13px;
            letter-spacing: .4px; text-transform: uppercase;
        }

        /* ── Main ibox ────────────────────────────────────────────── */
        .ofr-main-ibox { border-radius: 16px !important; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.07) !important; }
        .ofr-main-ibox > .ibox-title {
            background: linear-gradient(135deg,#e65100 0%,#f9a826 100%) !important;
            border: none !important; padding: 16px 24px !important;
        }
        .ofr-main-ibox > .ibox-title h3 { color: #fff !important; margin: 0; font-size: 16px; }
        .ofr-main-ibox > .ibox-title .badge { background: rgba(255,255,255,.2); color: #fff; }
        .ofr-main-ibox > .ibox-content { padding: 24px 28px !important; }

        /* ── Field labels ─────────────────────────────────────────── */
        .ofr-label {
            font-size: 11px; font-weight: 700; color: #546e7a;
            text-transform: uppercase; letter-spacing: .5px;
            margin-bottom: 4px; display: block;
        }
        .ofr-label .req { color: #e53935; margin-left: 2px; }
        .form-control.ofr-input {
            border-radius: 8px !important; border: 1px solid #cfd8dc !important; font-size: 13px !important;
        }
        .form-control.ofr-input:focus {
            border-color: #00897b !important; box-shadow: 0 0 0 3px rgba(0,137,123,.12) !important;
        }

        /* ── Totales grid ─────────────────────────────────────────── */
        .totales-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px; margin-bottom: 24px;
        }
        .total-card { border-radius: 10px; padding: 12px 16px; text-align: center; }
        .total-card .tc-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; opacity: .75; margin-bottom: 4px; }
        .total-card input {
            border: none !important; background: transparent !important;
            font-size: 18px !important; font-weight: 800 !important;
            padding: 0 !important; text-align: center; width: 100%;
        }
        .total-card.total-final input { font-size: 22px !important; font-weight: 900 !important; color: #fff !important; }

        /* ── Modal centrado (compatibilidad Bootstrap 3/4) ─────────── */
        .modal-dialog-centered {
            display: flex !important;
            align-items: center !important;
            min-height: calc(100% - 3.5rem) !important;
        }
        /* ── Carrito items ─────────────────────────────────────────── */
        .cart-item-card { transition: box-shadow .15s; }
        .cart-item-card:hover { box-shadow: 0 4px 18px rgba(27,94,32,.14) !important; }
        .cart-field-label { font-size:10px; color:#78909c; font-weight:700; text-transform:uppercase; letter-spacing:.3px; margin-bottom:3px; }

        /* ── of-card system ────────────────────────────────────────────── */
        .ofr-main-ibox { border: none !important; box-shadow: none !important; background: transparent !important; }
        .ofr-main-ibox > .ibox-title { display: none !important; }
        .ofr-main-ibox > .ibox-content { padding: 0 !important; background: transparent !important; border: none !important; }

        .of-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e8eaef;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            padding: 22px 24px;
            margin-bottom: 18px;
        }
        .of-card-title {
            font-size: 13px; font-weight: 700; color: #6c757d;
            text-transform: uppercase; letter-spacing: .6px;
            margin-bottom: 16px;
            display: flex; align-items: center; gap: 7px;
        }
        .of-card-title i { font-size: 14px; }

        /* ── of-totals-card ─────────────────────────────────────────────── */
        .of-totals-card {
            background: #fff; border: 1.5px solid #e8eaef;
            border-radius: 14px; overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,.07);
            margin-bottom: 18px;
        }
        .of-totals-header {
            background: linear-gradient(135deg,#2d3748,#4a5568);
            padding: 12px 20px; color: #fff; font-size: 13px; font-weight: 700;
            display: flex; align-items: center; gap: 8px;
        }
        .of-totals-body { padding: 16px 20px; }
        .of-total-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 7px 0; border-bottom: 1px solid #f0f2f5; font-size: 13px;
        }
        .of-total-row:last-child { border-bottom: none; }
        .of-total-row .lbl { color: #6b7280; font-weight: 500; }
        .of-total-row .val {
            font-weight: 700; color: #1a202c;
            background: #f7f8fa; border: 1px solid #e8eaef;
            border-radius: 7px; padding: 4px 12px; font-size: 13px;
            min-width: 140px; text-align: right; font-family: monospace;
            outline: none;
        }
        .of-total-grand .lbl { font-size: 15px; font-weight: 800; color: #1a202c; }
        .of-total-grand .val {
            background: linear-gradient(135deg,#1ab394,#0fa37a);
            color: #fff; font-size: 15px; border: none;
            box-shadow: 0 3px 10px rgba(26,179,148,.3);
        }

        /* ── Cart empty state ────────────────────────────────────────────── */
        #carritoVacio {
            text-align: center; padding: 36px 20px; color: #aab;
        }
        #carritoVacio i { font-size: 48px; opacity: .25; display: block; margin-bottom: 10px; color: #aab; }
        #cart-count-badge {
            background: #e65100; color: #fff; border-radius: 20px;
            font-size: 11px; font-weight: 700; padding: 2px 10px;
        }

        /* ── Collapsible of-cards ─────────────────────────────────────── */
        .of-card-title { cursor: pointer; user-select: none; }
        .of-card-title .of-chevron {
            margin-left: auto; font-size: 12px; color: #9ca3af;
            transition: transform .25s ease; flex-shrink: 0;
        }
        .of-card-title.is-collapsed .of-chevron { transform: rotate(-90deg); }

        /* ── Historial panel naranja ──────────────────────────────────── */
        .of-historial-header {
            background: linear-gradient(135deg,#e65100,#f9a826);
            color: #fff; border-radius: 8px 8px 0 0;
            padding: 8px 14px; font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .4px;
            display: flex; align-items: center; gap: 6px;
        }
        .of-historial-body {
            border: 1.5px solid #ffe0b2; border-top: none;
            border-radius: 0 0 8px 8px; padding: 8px 12px;
            background: #fffbf7; font-size: 12px; min-height: 38px;
        }
    </style>
    @endpush

    {{-- ===== PAGE HEADING (solo en flujo) ===== --}}
    @if($fromFlujo && ($config->codigo ?? '') === 'cotizacion_clientes_a')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2><i class="fa fa-file-text-o" style="color:#00897b;"></i> Nueva Oferta</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('flujo.ventas') }}">Ventas</a></li>
                <li class="breadcrumb-item"><a href="{{ route('flujo.ofertas') }}">Ofertas</a></li>
                <li class="breadcrumb-item active"><strong>Nueva Oferta</strong></li>
            </ol>
        </div>
        <div class="col-lg-2 d-flex align-items-center justify-content-end">
            <a href="{{ route('flujo.ventas') }}" class="btn btn-default btn-sm">
                <i class="mr-1 fa fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
    @elseif($fromFlujo)
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>{{ $config->nombre ?? 'Venta' }}</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('flujo.ventas') }}">Ventas</a></li>
                <li class="breadcrumb-item active"><strong>{{ $config->nombre ?? 'Factura' }}</strong></li>
            </ol>
        </div>
        <div class="col-lg-2 d-flex align-items-center justify-content-end">
            <a href="{{ route('flujo.ventas') }}" class="btn btn-default btn-sm">
                <i class="mr-1 fa fa-arrow-left"></i> Volver
            </a>

            
        </div>
    </div>
    @elseif($fromPrefactura)
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2><i class="fa fa-file-text-o" style="color:#1b5e20;"></i> Factura desde Prefactura</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('flujo.ventas') }}">Ventas</a></li>
                <li class="breadcrumb-item active"><strong>Factura desde Prefactura</strong></li>
            </ol>
        </div>
        <div class="col-lg-2 d-flex align-items-center justify-content-end">
            <a href="{{ route('flujo.ventas') }}" class="btn btn-default btn-sm">
                <i class="mr-1 fa fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
    @endif

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- ===== SELECTOR DE TIPO (para facturación desde prefactura) ===== --}}
        @if($fromPrefactura)
        <div class="mb-4 row">
            <div class="col-12">
                <div class="ibox">
                    <div class="py-3 ibox-content" style="background: linear-gradient(135deg, #f1f8e9, #e8f5e9); border: 2px solid #a5d6a7;">
                        <h6 style="margin:0 0 12px; font-weight:800; color:#1b5e20; display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-file-text-o"></i> Selecciona el tipo de facturación:
                        </h6>
                        <div class="flex-wrap d-flex align-items-center tipo-factura-selector" style="gap:8px;">
                            @foreach($tiposFactura as $tipo)
                                <button type="button"
                                    class="btn btn-sm {{ $config && $config->id == $tipo->id ? 'btn-success active' : 'btn-outline-success' }}"
                                    onclick="cambiarTipoFacturaDesdeUrl('{{ $tipo->ruta_menu }}')"
                                    style="border-radius:8px; padding:8px 16px; font-weight:700; font-size:13px;">
                                    <i class="mr-1 fa fa-file-text"></i> {{ $tipo->nombre }}
                                </button>
                            @endforeach
                        </div>
                        <small class="mt-2 d-block text-muted">Selecciona el tipo de facturación y los datos de la prefactura se cargarán automáticamente.</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ===== SELECTOR DE TIPO (fuera de flujo) ===== --}}
        @if(!$fromFlujo && !$fromPrefactura)
        <div class="mb-3 row">
            <div class="col-12">
                <div class="ibox">
                    <div class="py-2 ibox-content">
                        <div class="flex-wrap d-flex align-items-center tipo-factura-selector">
                            <strong class="mr-3">Tipo de Facturación:</strong>
                            @foreach($tiposFactura as $tipo)
                                <button type="button" id="btnTipo_{{ $tipo->id }}"
                                    class="btn btn-sm {{ $config && $config->id == $tipo->id ? 'btn-primary active' : 'btn-outline-secondary' }}"
                                    onclick="cambiarTipoFactura('{{ $tipo->ruta_menu }}')">{{ $tipo->nombre }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ===== PANEL: VINCULAR A UNA PREFACTURA (modo facturación desde prefactura) ===== --}}
        @if($fromPrefactura)
        <div class="pedido-link-panel {{ $prefacturaVinculada ? 'linked' : '' }}" style="border-color:#a5d6a7; background:#f1f8e9;">
            @if(!$prefacturaVinculada)
            <div class="mb-3">
                <h6 style="margin:0; font-weight:800; color:#1b5e20;">
                    <i class="mr-2 fa fa-file-text-o"></i>Vincular a una Prefactura
                </h6>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="background:#1b5e20; color:#fff; border-color:#1b5e20; border-radius:8px 0 0 8px;">
                                <i class="fa fa-search"></i>
                            </span>
                        </div>
                        <input type="text"
                               wire:model.debounce.350ms="busquedaPrefactura"
                               class="form-control"
                               placeholder="Buscar por # prefactura, # flujo, cliente o RTN..."
                               style="border-radius:0 8px 8px 0;"
                               autocomplete="off">
                    </div>
                    @if(strlen(trim($busquedaPrefactura)) > 0 && strlen(trim($busquedaPrefactura)) < 2)
                        <small class="mt-1 text-muted d-block">Escribe al menos 2 caracteres</small>
                    @endif
                </div>
            </div>

            @if(count($prefacturasEncontradas) > 0)
            <div style="max-height:280px; overflow-y:auto; margin-top:12px;">
                @foreach($prefacturasEncontradas as $pf)
                <div class="ped-row" wire:click="seleccionarPrefactura({{ $pf['id'] }})" style="cursor:pointer; border-color:#c8e6c9;">
                    <div style="flex-shrink:0; display:flex; flex-direction:column; align-items:center; gap:3px;">
                        <span style="background:linear-gradient(135deg,#1b5e20,#2e7d32); color:#fff; border-radius:8px; padding:3px 10px; font-size:12px; font-weight:800;"># Pref. {{ $pf['id'] }}</span>
                        @if(!empty($pf['flujo_id']))
                        <span style="background:#e8f0fe; color:#1a5276; border-radius:6px; padding:1px 8px; font-size:10px; font-weight:700;">Flujo #{{ $pf['flujo_id'] }}</span>
                        @endif
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-weight:700; color:#2c3e50; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $pf['nombre_cliente'] }}</div>
                        <div style="font-size:11px; color:#90a4ae;">RTN: {{ $pf['RTN'] ?: '—' }} &nbsp;·&nbsp; Emisión: {{ \Carbon\Carbon::parse($pf['fecha_emision'])->format('d/m/Y') }} &nbsp;·&nbsp; Vence: {{ \Carbon\Carbon::parse($pf['fecha_vencimiento'])->format('d/m/Y') }}</div>
                    </div>
                    <div style="flex-shrink:0; text-align:right; min-width:110px;">
                        <div style="font-weight:800; color:#e65100; font-size:14px;">L {{ number_format($pf['total'], 2) }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            @elseif(strlen(trim($busquedaPrefactura)) >= 2)
            <div class="py-3 mt-2 text-center">
                <i class="mb-2 fa fa-search fa-2x" style="color:#b2dfdb; display:block;"></i>
                <p style="color:#78909c; font-size:13px; margin:0;">No se encontraron prefacturas activas con ese criterio.</p>
            </div>
            @endif

            @else
            <div class="flex-wrap d-flex align-items-center justify-content-between" style="gap:8px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="background:linear-gradient(135deg,#1b5e20,#2e7d32); color:#fff; border-radius:8px; padding:4px 14px; font-size:13px; font-weight:800;">
                        <i class="mr-1 fa fa-link"></i> Prefactura Vinculada
                    </span>
                    <span style="font-weight:700; color:#1b5e20; font-size:14px;">
                        #{{ $prefacturaVinculada['id'] }} — {{ $prefacturaVinculada['nombre_cliente'] }}
                    </span>
                    <span style="background:#fff3e0; color:#e65100; border-radius:6px; padding:2px 10px; font-size:11px; font-weight:700;">
                        Total: L {{ number_format($prefacturaVinculada['total'], 2) }}
                    </span>
                </div>
                <button type="button" wire:click="desvincularPrefactura"
                        style="background:#fce4ec; color:#b71c1c; border:1px solid #ffcdd2; border-radius:8px; padding:5px 14px; font-size:12px; font-weight:700; cursor:pointer;">
                    <i class="mr-1 fa fa-unlink"></i> Desvincular
                </button>
            </div>
            @endif
        </div>
        @endif

        {{-- ===== PANEL: VINCULAR A UN FLUJO (solo en modo oferta desde flujo) ===== --}}
        @if($fromFlujo && ($config->codigo ?? '') === 'cotizacion_clientes_a')
        <div class="pedido-link-panel {{ $flujoVinculado ? 'linked' : '' }}">
            @if(!$flujoVinculado)
            <div class="mb-3">
                <h6 style="margin:0; font-weight:800; color:#00695c;">
                    <i class="mr-2 fa fa-link"></i>Vincular a un Flujo
                    <span style="font-size:11px; font-weight:400; color:#78909c; margin-left:8px;">(opcional)</span>
                </h6>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="background:#00897b; color:#fff; border-color:#00897b; border-radius:8px 0 0 8px;">
                                <i class="fa fa-search"></i>
                            </span>
                        </div>
                        <input type="text"
                               wire:model.debounce.350ms="busquedaFlujo"
                               class="form-control"
                               placeholder="Buscar por cliente, RTN, # flujo, # pedido u # oferta…"
                               style="border-radius:0 8px 8px 0;"
                               autocomplete="off">
                    </div>
                    @if(strlen(trim($busquedaFlujo)) > 0 && strlen(trim($busquedaFlujo)) < 2)
                        <small class="mt-1 text-muted d-block">Escribe al menos 2 caracteres</small>
                    @endif
                </div>
                <div class="col-md-6 d-flex align-items-center">
                    <small class="text-muted">
                        <i class="mr-1 fa fa-info-circle text-info"></i>
                        Puedes crear <strong>múltiples ofertas</strong> para el mismo flujo.
                    </small>
                </div>
            </div>

            @if(count($flujoEncontrados) > 0)
            <div style="max-height:280px; overflow-y:auto; margin-top:12px;">
                @foreach($flujoEncontrados as $flujo)
                @php $fl = (array)$flujo; @endphp
                <div class="ped-row" wire:click="seleccionarFlujo({{ $fl['flujo_id'] }})" style="cursor:pointer;">
                    <div style="flex-shrink:0; display:flex; flex-direction:column; align-items:center; gap:3px;">
                        <span style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border-radius:8px; padding:3px 10px; font-size:12px; font-weight:800;"># Flujo {{ $fl['flujo_id'] }}</span>
                        @if($fl['pedido_id'])
                        <span style="background:#e3f2fd; color:#1565c0; border-radius:6px; padding:1px 8px; font-size:10px; font-weight:700;">Ped. #{{ $fl['pedido_id'] }}</span>
                        @else
                        <span style="background:#fff3e0; color:#e65100; border-radius:6px; padding:1px 8px; font-size:10px; font-weight:700;">Sin pedido</span>
                        @endif
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-weight:700; color:#2c3e50; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $fl['cliente'] }}</div>
                        <div style="font-size:11px; color:#90a4ae;">RTN: {{ $fl['rtn'] ?: '—' }} &nbsp;·&nbsp; {{ \Carbon\Carbon::parse($fl['created_at'])->format('d/m/Y') }}</div>
                    </div>
                    <div style="flex-shrink:0; text-align:center; min-width:70px;">
                        <div style="font-size:10px; color:#90a4ae;">Ofertas</div>
                        <div style="font-weight:700; color:{{ $fl['total_ofertas'] > 0 ? '#00897b' : '#b0bec5' }}; font-size:15px;">
                            {{ $fl['total_ofertas'] }}
                            @if($fl['has_ganadora'] > 0)<i class="fa fa-trophy text-warning" style="font-size:12px;"></i>@endif
                        </div>
                    </div>
                    <div style="flex-shrink:0;">
                        @php
                            $estMapFl=['pedido'=>['#e3f2fd','#1565c0'],'Ofertas'=>['#fff3e0','#e65100'],'prefactura'=>['#e0f7fa','#006064'],'factura'=>['#e8f5e9','#1b5e20'],'cancelado'=>['#fce4ec','#b71c1c']];
                            $colFl=$estMapFl[$fl['flujo_estado']]??['#f5f5f5','#546e7a'];
                        @endphp
                        <span style="background:{{ $colFl[0] }}; color:{{ $colFl[1] }}; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700;">{{ ucfirst(str_replace('_',' ',$fl['flujo_estado'])) }}</span>
                    </div>
                    @if($fl['pedido_id'])
                    <div style="flex-shrink:0;" wire:click.stop="verDetallePedido({{ $fl['pedido_id'] }})">
                        <span style="background:#1565c0; color:#fff; border-radius:8px; padding:6px 14px; font-size:12px; font-weight:700; cursor:pointer;"><i class="mr-1 fa fa-eye"></i> Detalle</span>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @elseif(strlen(trim($busquedaFlujo)) >= 2)
            <div class="py-3 mt-2 text-center">
                <i class="mb-2 fa fa-search fa-2x" style="color:#b2dfdb; display:block;"></i>
                <p style="color:#78909c; font-size:13px; margin:0;">No se encontraron flujos activos con ese criterio.</p>
            </div>
            @endif

            @else
            {{-- Flujo vinculado: versión compacta con desvincular --}}
            <div class="flex-wrap d-flex align-items-center justify-content-between" style="gap:8px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="background:linear-gradient(135deg,#1b5e20,#2e7d32); color:#fff; border-radius:8px; padding:4px 14px; font-size:13px; font-weight:800;">
                        <i class="mr-1 fa fa-link"></i> Flujo Vinculado
                    </span>
                    <span style="font-weight:700; color:#1b5e20; font-size:14px;">
                        #{{ $flujoVinculado['flujo_id'] }} — {{ $flujoVinculado['cliente'] }}
                    </span>
                    @if($flujoVinculado['pedido_id'])
                    <span style="background:#e3f2fd; color:#1565c0; border-radius:6px; padding:2px 10px; font-size:11px; font-weight:700;">
                        Ped. #{{ $flujoVinculado['pedido_id'] }}
                    </span>
                    @else
                    <span style="background:#fff3e0; color:#e65100; border-radius:6px; padding:2px 10px; font-size:11px; font-weight:700;">
                        <i class="mr-1 fa fa-tag"></i>Sin pedido
                    </span>
                    @endif
                </div>
                <button type="button" wire:click="desvincularFlujo"
                        style="background:#fce4ec; color:#b71c1c; border:1px solid #ffcdd2; border-radius:8px; padding:5px 14px; font-size:12px; font-weight:700; cursor:pointer;">
                    <i class="mr-1 fa fa-unlink"></i> Desvincular
                </button>
            </div>
            @endif
        </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ofr-main-ibox">
                    <div class="ibox-title">
                        <div class="d-flex align-items-center justify-content-between">
                            <h3>
                                <i class="mr-2 fa fa-file-text-o"></i>
                                @if($fromFlujo && ($config->codigo ?? '') === 'cotizacion_clientes_a')
                                    Nueva Oferta
                                @else
                                    <span id="titulo_factura">{{ $config->nombre ?? 'Venta' }}</span>
                                @endif
                            </h3>
                            <div class="gap-3 d-flex align-items-center">
                                <input type="text" id="numero_venta" name="numero_venta"
                                    style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); color:#fff; border-radius:8px; padding:4px 10px; max-width:150px; font-size:13px; font-weight:700;" readonly
                                    placeholder="# Oferta">
                            </div>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <form onkeydown="return event.key != 'Enter';" autocomplete="off" id="crear_venta"
                            name="crear_venta" data-parsley-validate>

                            {{-- Campos ocultos de configuración --}}
                            <input type="hidden" id="restriccion"        name="restriccion"        value="{{ $config->restriccion ?? 1 }}">
                            <input type="hidden" id="tipo_venta_id"      name="tipo_venta_id"      value="{{ $config->tipo_venta_id ?? 2 }}">
                            <input type="hidden" id="tipo_factura_id"    name="tipo_factura_id"    value="{{ $config->id ?? '' }}">
                            <input type="hidden" id="idComprobante"      name="idComprobante"      value="">
                            <input type="hidden" id="codigo_autorizacion" name="codigo_autorizacion" value="">
                            <input type="hidden" id="pedido_vinculado_id" name="pedido_id"          value="{{ $pedidoId ?? '' }}"> {{-- vinculación a pedido --}}
                            <input type="hidden" id="flujo_vinculado_id"  name="flujo_id"           value="{{ $flujoVinculadoId ?? '' }}"> {{-- flujo directo (sin pedido) --}}
                            <input type="hidden" id="prefactura_vinculada_id" name="prefactura_id"   value="{{ $prefacturaVinculadaId ?? '' }}"> {{-- prefactura vinculada --}}

                            {{-- ── SECCIÓN 1: Datos del Cliente ────────────────────────── --}}
                            <span id="ico_sec_cliente" style="display:none;"></span>
                            <div class="of-card">
                            <div class="of-card-title" onclick="toggleOfCard('body_cliente', this)">
                                <i class="fa fa-user text-primary"></i> Datos del cliente
                                @if($fromFlujo && ($config->codigo ?? '') === 'cotizacion_clientes_a')
                                <span id="cat_cliente_badge" style="display:none; background:rgba(230,81,0,.1); color:#e65100; border:1px solid rgba(230,81,0,.2); border-radius:20px; padding:2px 12px; font-size:11px; font-weight:700;">
                                    <i class="mr-1 fa fa-tag"></i><span id="cat_badge_text"></span>
                                </span>
                                @endif
                                <i class="fa fa-chevron-down of-chevron"></i>
                            </div>
                            <div id="body_cliente">
                            <div id="sec_cliente">

                            <div class="row" style="row-gap:10px;">
                                {{-- Cliente --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">Cliente <span class="req">*</span></label>
                                    <select id="seleccionarCliente" name="seleccionarCliente"
                                        class="form-control form-control-sm" data-parsley-required
                                        onchange="obtenerDatosCliente()"
                                        {{ ($flujoVinculado || $prefacturaVinculada) ? 'disabled' : '' }}>
                                        <option value="" selected disabled>--Seleccionar--</option>
                                    </select>
                                </div>
                                {{-- Nombre --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">Nombre del Cliente <span class="req">*</span></label>
                                    <input class="form-control form-control-sm" required type="text" id="nombre_cliente_ventas"
                                        name="nombre_cliente_ventas" data-parsley-required readonly placeholder="(autocompletado)">
                                </div>
                                {{-- RTN --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">RTN <span class="req">*</span></label>
                                    <input class="form-control form-control-sm" type="text" id="rtn_ventas" name="rtn_ventas" readonly placeholder="(autocompletado)">
                                </div>
                                {{-- Vendedor --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">Vendedor <span class="req">*</span></label>
                                    <select name="vendedor" id="vendedor" class="form-control form-control-sm" required>
                                        <option value="" selected disabled>--Seleccionar--</option>
                                    </select>
                                </div>
                                {{-- Tipo de pago --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">Tipo de Pago <span class="req">*</span></label>
                                    <select class="form-control form-control-sm" name="tipoPagoVenta" id="tipoPagoVenta"
                                        data-parsley-required onchange="validarFechaPago()">
                                    </select>
                                </div>
                                {{-- Descuento --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">Descuento %</label>
                                    <input class="form-control form-control-sm" type="number" min="0"
                                        max="{{ $config->max_descuento ?? 50 }}"
                                        value="0" id="porDescuento" name="porDescuento"
                                        onchange="calcularTotalesInicioPagina()">
                                </div>
                                {{-- Fecha emisión --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">Fecha Emisión <span class="req">*</span></label>
                                    <input class="form-control form-control-sm" type="date" id="fecha_emision"
                                        onchange="sumarDiasCredito()" name="fecha_emision"
                                        value="{{ date('Y-m-d') }}" data-parsley-required>
                                </div>
                                {{-- Fecha vencimiento --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label" style="color:#f57f17;">Vencimiento</label>
                                    <input class="form-control form-control-sm" type="date" id="fecha_vencimiento"
                                        name="fecha_vencimiento" value="" data-parsley-required
                                        min="{{ date('Y-m-d') }}" readonly>
                                </div>
                                {{-- Nota --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">Nota</label>
                                    <textarea class="form-control form-control-sm" id="nota_comen" name="nota_comen" rows="1" maxlength="250"></textarea>
                                </div>
                                {{-- Código de exoneración (oculto por defecto) --}}
                                <div class="col-12 col-md-4" id="campo_codigo_exoneracion"
                                    style="{{ ($config->requiere_codigo_exoneracion ?? false) ? '' : 'display:none' }}">
                                    <label class="ofr-label">Código Exoneración <span class="req">*</span></label>
                                    <select id="codigoExoneracion" name="codigoExoneracion" class="form-control form-control-sm">
                                        <option value="" selected disabled>--Seleccione--</option>
                                    </select>
                                </div>
                                {{-- Orden de compra (oculto por defecto) --}}
                                <div class="col-12 col-md-4" id="campo_orden_compra"
                                    style="{{ ($config->requiere_orden_compra ?? false) ? '' : 'display:none' }}">
                                    <label class="ofr-label">Orden de Compra</label>
                                    <select class="form-control form-control-sm" name="ordenCompra" id="ordenCompra">
                                        <option value="" selected disabled>--Seleccionar--</option>
                                    </select>
                                </div>
                            </div>

                            </div>{{-- /sec_cliente --}}
                            </div>{{-- /body_cliente --}}
                            </div>{{-- /of-card cliente --}}

                            {{-- ── SECCIÓN 2: Agregar Producto ─────────────────────────── --}}
                            <span id="ico_sec_producto" style="display:none;"></span>
                            <div class="of-card">
                            <div class="of-card-title" onclick="toggleOfCard('body_producto', this)">
                                <i class="fa fa-plus-circle text-success"></i> Agregar producto al carrito
                                <i class="fa fa-chevron-down of-chevron"></i>
                            </div>
                            <div id="body_producto">
                            <div id="sec_producto">

                                {{-- Sugerencias del pedido --}}
                                @if($fromFlujo && ($config->codigo ?? '') === 'cotizacion_clientes_a' && count($productosSugeridos) > 0)
                                <div style="border:1.5px solid #c8e6c9; border-radius:8px; padding:7px 12px; margin-bottom:12px; background:#f1f8e9; display:flex; align-items:center; justify-content:space-between;">
                                    <span style="font-weight:700; color:#1b5e20; font-size:12px;">
                                        <i class="mr-1 fa fa-list-ul"></i> {{ count($productosSugeridos) }} ítem(s) en el pedido
                                    </span>
                                    <button type="button" data-toggle="modal" data-target="#modalProductosPedido"
                                            style="background:linear-gradient(135deg,#1b5e20,#2e7d32); color:#fff; border:none; border-radius:6px; padding:4px 10px; font-size:11px; font-weight:700; cursor:pointer;">
                                        <i class="mr-1 fa fa-eye"></i> Ver Productos
                                    </button>
                                </div>
                                @endif

                                {{-- Fila 1: Producto | Categoría | Bodega --}}
                                <div class="row" style="row-gap:10px; margin-bottom:10px;">
                                    <div class="col-12 col-md-4">
                                        <label class="ofr-label">Seleccionar Producto <span class="req">*</span></label>
                                        <div class="input-group">
                                            <input type="text" id="codigoProductoBuscar" class="form-control form-control-sm"
                                                placeholder="ID o nombre del producto…" autocomplete="off"
                                                onkeydown="if(event.key==='Enter'){buscarPorCodigo(this.value);return false;}">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary btn-sm" title="Buscar producto"
                                                    onclick="limpiarProducto(); window['abrirBuscador_buscadorProductoUnificado'](document.getElementById('codigoProductoBuscar').value||'')">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <small id="productoSeleccionadoLabel" class="mt-1 text-success font-weight-bold d-block d-none" style="font-size:11px;"></small>
                                        <select id="seleccionarProducto" name="seleccionarProducto" class="d-none">
                                            <option value="" selected disabled></option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="ofr-label">Categoría Precio <span class="req">*</span></label>
                                        <select id="categoria_cliente_venta_id" name="categoria_cliente_venta_id"
                                            class="form-control form-control-sm" onchange="habilitarBodega()">
                                            <option value="" selected disabled>--Seleccione primero un producto--</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="ofr-label">Bodega <span class="req">*</span></label>
                                        <select id="bodega" name="bodega" class="form-control form-control-sm" onchange="prueba()">
                                            <option value="" selected disabled>--Seleccione una categoría primero--</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Botón añadir --}}
                                <div id="botonAdd" class="mb-3 d-none">
                                    <button type="button" onclick="agregarProductoCarrito()"
                                        style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none;
                                               border-radius:8px; padding:5px 14px; font-size:12px; font-weight:700;
                                               box-shadow:0 2px 8px rgba(230,81,0,.3); cursor:pointer;">
                                        <i class="mr-1 fa fa-shopping-cart"></i> Añadir al Carrito
                                    </button>
                                </div>

                                {{-- Fila 2: Imagen | Historial --}}
                                <div class="row">
                                    <div class="col-12 col-md-5">
                                        <div id="carouselProducto" class="carousel slide" data-ride="carousel">
                                            <div id="bloqueImagenes" class="carousel-inner" style="border-radius:10px; overflow:hidden; max-height:220px;"></div>
                                            <a class="carousel-control-prev" href="#carouselProducto" role="button" data-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="sr-only">Previous</span>
                                            </a>
                                            <a class="carousel-control-next" href="#carouselProducto" role="button" data-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="sr-only">Next</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-7">
                                        <div id="historialPreciosPanel">
                                            <div class="of-historial-header">
                                                <i class="fa fa-history"></i> Últimas 5 ventas de este producto a este cliente
                                            </div>
                                            <div class="of-historial-body" id="historialPreciosCuerpo">
                                                <p class="mb-0 text-muted small">Sin ventas previas de este producto a este cliente.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>{{-- /sec_producto --}}
                            </div>{{-- /body_producto --}}
                            </div>{{-- /of-card producto --}}

                            {{-- ── CARRITO DE PRODUCTOS ────────────────────────────────── --}}
                            <div class="of-card" style="padding:0; overflow:hidden;">
                                <div style="padding:16px 24px 12px; border-bottom:1px solid #f0f2f5; display:flex; align-items:center; gap:8px; cursor:pointer;"
                                     onclick="toggleOfCard('body_carrito', this)">
                                    <span class="mb-0 of-card-title" style="cursor:pointer; margin-bottom:0 !important;">
                                        <i class="fa fa-shopping-cart text-warning"></i> Carrito de productos
                                    </span>
                                    <span id="cart-count-badge">0 producto(s)</span>
                                    <i class="ml-2 fa fa-chevron-down of-chevron" style="margin-left:8px;"></i>
                                </div>

                                {{-- ── Lista productos ────────────────────────────────────────── --}}
                                <div id="body_carrito">
                                <div id="divProductos" style="padding:0 0 4px;">
                                    <div id="carritoVacio" class="py-3 text-center">
                                        <i class="mb-2 fa fa-inbox fa-3x d-block"></i>
                                        <p style="font-size:13px; margin:0;">No hay productos en el carrito.<br><small>Use el buscador de arriba para agregar productos.</small></p>
                                    </div>
                                    <div id="carritoTablaWrapper" class="d-none table-responsive" style="max-height:400px; overflow-y:auto;">
                                        <table class="table mb-0 table-sm table-bordered" style="font-size:12px; min-width:900px;">
                                            <thead style="background:linear-gradient(135deg,#e8f5e9,#e0f7fa); position:sticky; top:0; z-index:1;">
                                                <tr style="color:#00695c; font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:.3px;">
                                                    <th style="width:36px;"></th>
                                                    <th style="min-width:150px;">Producto</th>
                                                    <th style="min-width:100px;">Bodega</th>
                                                    <th style="min-width:110px;">Precio Opc.</th>
                                                    <th style="min-width:90px;">P. Unitario</th>
                                                    <th style="min-width:70px;">Cantidad</th>
                                                    <th style="min-width:90px;">Unidad</th>
                                                    <th style="min-width:90px;">Subtotal</th>
                                                    <th style="min-width:80px;">ISV</th>
                                                    <th style="min-width:90px; background:linear-gradient(135deg,#e65100,#f9a826); color:#fff;">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody id="carritoTbody"></tbody>
                                        </table>
                                    </div>
                                </div>
                                </div>{{-- /body_carrito --}}
                            </div>{{-- /of-card carrito --}}

                            {{-- ── SECCIÓN 3: Totales ───────────────────────────────────── --}}
                            <span id="ico_sec_totales" style="display:none;"></span>
                            <div id="sec_totales" style="display:none;">{{-- kept for JS compat --}}</div>

                            {{-- ── Botón principal + Totales ────────────────────────── --}}
                            <div class="row">
                                <div class="col-12 col-lg-6 offset-lg-6">
                                    <div class="of-totals-card">
                                        <div class="of-totals-header">
                                            <i class="fa fa-calculator"></i> Resumen de totales
                                        </div>
                                        <div class="of-totals-body">
                                            <div class="of-total-row">
                                                <span class="lbl"><i class="mr-1 fa fa-tag text-muted"></i> Descuento</span>
                                                <input type="text" id="descuentoMostrar" name="descuentoMostrar" class="val" placeholder="L. 0.00" data-parsley-required autocomplete="off" readonly>
                                                <input type="hidden" value="0" id="porDescuentoCalculado" name="porDescuentoCalculado">
                                            </div>
                                            <div class="of-total-row">
                                                <span class="lbl"><i class="mr-1 fa fa-list text-muted"></i> Sub Total</span>
                                                <input type="text" id="subTotalGeneralMostrar" class="val" placeholder="L. 0.00" readonly autocomplete="off">
                                                <input id="subTotalGeneral" name="subTotalGeneral" type="hidden" value="" required>
                                            </div>
                                            <div class="of-total-row">
                                                <span class="lbl"><i class="mr-1 fa fa-file-text-o text-muted"></i> Sub Total Grabado</span>
                                                <input type="text" id="subTotalGeneralGrabadoMostrar" class="val" placeholder="L. 0.00" readonly autocomplete="off">
                                                <input id="subTotalGeneralGrabado" name="subTotalGeneralGrabado" type="hidden" value="" required>
                                            </div>
                                            <div class="of-total-row">
                                                <span class="lbl"><i class="mr-1 fa fa-minus-circle text-muted"></i> Sub Total Exento</span>
                                                <input type="text" id="subTotalGeneralExcentoMostrar" class="val" placeholder="L. 0.00" readonly autocomplete="off">
                                                <input id="subTotalGeneralExcento" name="subTotalGeneralExcento" type="hidden" value="" required>
                                            </div>
                                            <div class="of-total-row" id="fila_isv" style="{{ ($config->aplica_isv ?? true) ? '' : 'display:none' }}">
                                                <span class="lbl"><i class="mr-1 fa fa-percent text-muted"></i> ISV</span>
                                                <input type="text" id="isvGeneralMostrar" class="val" placeholder="L. 0.00" readonly autocomplete="off">
                                                <input id="isvGeneral" name="isvGeneral" type="hidden" value="" required>
                                            </div>
                                            <div class="of-total-row of-total-grand" style="padding-top:12px; margin-top:4px;">
                                                <span class="lbl">TOTAL</span>
                                                <input type="text" id="totalGeneralMostrar" class="val" placeholder="L. 0.00" readonly autocomplete="off">
                                                <input id="totalGeneral" name="totalGeneral" type="hidden" value="" required>
                                            </div>
                                        </div>
                                    </div>

                                    <button id="btn_venta_coorporativa"
                                            style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none;
                                                   border-radius:12px; padding:14px 32px; font-size:15px; font-weight:800;
                                                   box-shadow:0 4px 18px rgba(230,81,0,.35); width:100%; cursor:pointer;
                                                   display:flex; align-items:center; justify-content:center; gap:10px;">
                                        @if($fromFlujo && ($config->codigo ?? '') === 'cotizacion_clientes_a')
                                            <i class="fa fa-save"></i> Guardar Oferta
                                        @else
                                            <i class="fa fa-check-circle"></i> Realizar Venta
                                        @endif
                                    </button>
                                </div>
                            </div>

                        </form>

                        {{-- ===== PANEL POST-FACTURA (oculto hasta guardar) ===== --}}
                        <div id="panel_post_factura" style="display:none; margin-top:24px;">
                            <div style="background:linear-gradient(135deg,#e8f5e9,#f1f8e9); border:2px solid #a5d6a7; border-radius:16px; padding:24px 28px;">
                                <div style="text-align:center; margin-bottom:20px;">
                                    <div style="display:inline-flex; align-items:center; justify-content:center;
                                                width:64px; height:64px; border-radius:50%;
                                                background:linear-gradient(135deg,#1b5e20,#2e7d32);
                                                box-shadow:0 6px 20px rgba(27,94,32,.35); margin-bottom:12px;">
                                        <i class="fa fa-check" style="color:#fff; font-size:28px;"></i>
                                    </div>
                                    <h5 style="color:#1b5e20; font-weight:800; margin:0 0 4px;">Factura guardada exitosamente</h5>
                                    <p id="pfactura_numero" style="color:#555; font-size:13px; margin:0;"></p>
                                </div>
                                <div style="display:flex; flex-wrap:wrap; gap:12px; justify-content:center;">
                                    {{-- Imprimir factura --}}
                                    <a id="btn_post_imprimir" href="#" target="_blank"
                                       style="display:inline-flex; align-items:center; gap:8px;
                                              background:linear-gradient(135deg,#1565c0,#1a7efb); color:#fff;
                                              border:none; border-radius:12px; padding:12px 22px;
                                              font-size:14px; font-weight:700; text-decoration:none;
                                              box-shadow:0 4px 14px rgba(21,101,192,.35);">
                                        <i class="fa fa-print fa-lg"></i> Imprimir Factura
                                    </a>
                                    {{-- Registrar Cobro --}}
                                    <a id="btn_post_cobro" href="#"
                                       style="display:inline-flex; align-items:center; gap:8px;
                                              background:linear-gradient(135deg,#e65100,#f9a826); color:#fff;
                                              border:none; border-radius:12px; padding:12px 22px;
                                              font-size:14px; font-weight:700; text-decoration:none;
                                              box-shadow:0 4px 14px rgba(230,81,0,.35);">
                                        <i class="fa fa-dollar fa-lg"></i> Registrar Cobro
                                    </a>
                                    {{-- Registrar Entrega --}}
                                    <a id="btn_post_entrega" href="/logistica/distribuciones"
                                       style="display:inline-flex; align-items:center; gap:8px;
                                              background:linear-gradient(135deg,#00695c,#00897b); color:#fff;
                                              border:none; border-radius:12px; padding:12px 22px;
                                              font-size:14px; font-weight:700; text-decoration:none;
                                              box-shadow:0 4px 14px rgba(0,137,123,.35);">
                                        <i class="fa fa-truck fa-lg"></i> Registrar Entrega
                                    </a>
                                    {{-- Nueva factura --}}
                                    <button type="button" onclick="window.location.reload()"
                                       style="display:inline-flex; align-items:center; gap:8px;
                                              background:#f5f5f5; color:#555;
                                              border:1px solid #ddd; border-radius:12px; padding:12px 22px;
                                              font-size:14px; font-weight:700; cursor:pointer;">
                                        <i class="fa fa-plus fa-lg"></i> Nueva Factura
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL: Solicitar código de autorización                        --}}
        {{-- (Visible solo para tipos que requieren código)                 --}}
        {{-- ============================================================== --}}
        <div class="modal fade" id="modal_solicitar_codigo" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Solicitar código</h3>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <button class="btn btn-primary btn-large-dim" type="button" onclick="solicitarCodigo()">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                        <div id="div_imprimir" class="mt-2 text-center d-none">
                            <a id="btn_imprimir" target="_blank" class="text-white btn add-btn btn-success">
                                <i class="fa-solid fa-file-invoice"></i> Imprimir Factura
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="history.back()">Salir</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL: Ingresar código de autorización --}}
        <div class="modal fade" id="modalPermiso" data-backdrop="static" tabindex="1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Ingresar código</h3>
                    </div>
                    <div class="modal-body">
                        <form id="verificarCodigoForm" autocomplete="off" data-parsley-validate>
                            <label for="codigo" class="col-form-label focus-label">
                                Código de autorización:<span class="text-danger">*</span>
                            </label>
                            <input class="form-control" required type="text" id="codigo" name="codigo" data-parsley-required>
                        </form>
                        <span id="mensajeCodigo" class="text-danger d-none">Código incorrecto</span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="history.back()">Salir</button>
                        <button type="submit" form="verificarCodigoForm" class="btn btn-primary">Verificar</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL: Detalle del Pedido --}}
        <div class="modal fade" id="modalDetallePedido" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content" style="border-radius:14px; overflow:hidden;">
                    <div class="modal-header" style="background:linear-gradient(135deg,#e65100,#f9a826); border:none; padding:16px 24px;">
                        <h5 class="modal-title" style="color:#fff; font-weight:700; margin:0;">
                            <i class="mr-2 fa fa-clipboard-list"></i>
                            Detalle del Pedido
                            @if($pedidoDetalle)
                                <span style="opacity:.8;">#{{ $pedidoDetalle['pedido']['id'] }}</span>
                            @endif
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding:24px;">
                        @if($pedidoDetalle)
                        @php $ped = $pedidoDetalle['pedido']; @endphp
                        {{-- Info del pedido --}}
                        <div class="mb-3 row">
                            <div class="col-md-6">
                                <div style="font-size:11px; color:#78909c; font-weight:700; text-transform:uppercase;">Cliente</div>
                                <div style="font-weight:700; color:#2c3e50;">{{ $ped['cliente'] }}</div>
                            </div>
                            <div class="col-md-3">
                                <div style="font-size:11px; color:#78909c; font-weight:700; text-transform:uppercase;">RTN</div>
                                <div style="color:#546e7a;">{{ $ped['rtn'] ?: '\u2014' }}</div>
                            </div>
                            <div class="col-md-3">
                                <div style="font-size:11px; color:#78909c; font-weight:700; text-transform:uppercase;">Estado</div>
                                <span style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border-radius:20px; padding:2px 10px; font-size:11px; font-weight:700;">{{ ucfirst(str_replace('_',' ',$ped['estado'])) }}</span>
                            </div>
                        </div>
                        @if($ped['observaciones'])
                        <div class="p-2 mb-3" style="background:#fff8e1; border-radius:8px; font-size:12px; color:#7b6000;">
                            <i class="mr-1 fa fa-comment"></i> {{ $ped['observaciones'] }}
                        </div>
                        @endif
                        {{-- Tabla de productos del pedido --}}
                        <div style="font-size:11px; font-weight:700; color:#546e7a; text-transform:uppercase; margin-bottom:8px;">Productos solicitados</div>
                        @if(count($pedidoDetalle['productos']) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm" style="font-size:13px;">
                                <thead style="background:linear-gradient(135deg,#e65100,#f9a826);">
                                    <tr style="color:#fff; font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:.3px;">
                                        <th style="border:none;">Producto</th>
                                        <th style="width:80px; text-align:center; border:none;">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody id="pdTbody">
                                    @foreach($pedidoDetalle['productos'] as $pd)
                                    @php $pd = (array)$pd; @endphp
                                    <tr data-pd-idx="{{ $loop->index }}" style="{{ $loop->index >= 5 ? 'display:none;' : '' }}">
                                        <td style="font-weight:600; vertical-align:middle;">{{ $pd['nombre_producto'] }}</td>
                                        <td style="text-align:center; vertical-align:middle;"><span style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border-radius:20px; padding:2px 12px; font-weight:700;">{{ intval($pd['cantidad']) }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{-- Paginación detalle --}}
                        @if(count($pedidoDetalle['productos']) > 5)
                        <div id="pdPaginacion" class="mt-2 d-flex align-items-center justify-content-center" style="gap:10px;">
                            <button onclick="pdChangePage(-1)" id="pdPrev" style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none; border-radius:8px; padding:4px 14px; font-size:12px; cursor:pointer; font-weight:700;">&#8592; Anterior</button>
                            <span id="pdPageInfo" style="font-size:12px; font-weight:700; color:#546e7a;"></span>
                            <button onclick="pdChangePage(1)" id="pdNext" style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none; border-radius:8px; padding:4px 14px; font-size:12px; cursor:pointer; font-weight:700;">Siguiente &#8594;</button>
                        </div>
                        @endif
                        @else
                        <p class="text-center text-muted">Sin productos registrados.</p>
                        @endif
                        @else
                        <div class="py-4 text-center">
                            <i class="fa fa-spinner fa-spin fa-2x" style="color:#00897b;"></i>
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        @if($pedidoDetalle)
                        <button type="button"
                                wire:click="seleccionarFlujoDesdePedido({{ $pedidoDetalle['pedido']['id'] }})" data-dismiss="modal"
                                style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none; border-radius:8px; padding:8px 20px; font-weight:700; cursor:pointer;">
                            <i class="mr-1 fa fa-link"></i> Vincular este Flujo
                        </button>
                        @endif
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL: Éxito guardado oferta – check verde + 4 botones --}}
        <div class="modal fade" id="modalExitoOferta" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:420px;">
                <div class="modal-content" style="border-radius:20px; overflow:hidden; border:none; box-shadow:0 20px 60px rgba(0,0,0,.18); position:relative;">
                    {{-- Botón cerrar --}}
                    <button type="button" data-dismiss="modal" aria-label="Cerrar"
                            style="position:absolute; top:12px; right:14px; background:none; border:none;
                                   font-size:20px; color:#9e9e9e; cursor:pointer; line-height:1; z-index:1;
                                   padding:4px 8px; border-radius:50%;" title="Cerrar">&times;</button>
                    <div class="modal-body" style="padding:36px 32px 28px; text-align:center;">

                        {{-- Ícono check grande --}}
                        <div style="width:90px; height:90px; border-radius:50%;
                                    background:linear-gradient(135deg,#00c853,#69f0ae);
                                    display:flex; align-items:center; justify-content:center;
                                    margin:0 auto 20px; box-shadow:0 8px 24px rgba(0,200,83,.30);">
                            <i class="fa fa-check" style="font-size:46px; color:#fff; line-height:1;"></i>
                        </div>

                        <h4 style="font-weight:800; color:#1b5e20; margin-bottom:6px; font-size:18px;">¡Oferta guardada!</h4>
                        <p id="msgNumOferta" style="color:#546e7a; font-size:13px; margin-bottom:24px;">La oferta fue registrada exitosamente.</p>

                        {{-- 4 botones compactos --}}
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">

                            <button onclick="ofertaAccion('nueva')"
                                    style="background:#f0fdf4; color:#1b5e20; border:1.5px solid #a7f3d0;
                                           border-radius:10px; padding:11px 8px; font-size:12px; font-weight:700;
                                           cursor:pointer; text-align:center; transition:background .15s;"
                                    onmouseover="this.style.background='#dcfce7'" onmouseout="this.style.background='#f0fdf4'">
                                <i class="fa fa-plus-circle d-block" style="font-size:20px; margin-bottom:4px; color:#16a34a;"></i>
                                Nueva oferta
                            </button>

                            <button onclick="ofertaAccion('flujo')"
                                    style="background:#eff6ff; color:#1e40af; border:1.5px solid #bfdbfe;
                                           border-radius:10px; padding:11px 8px; font-size:12px; font-weight:700;
                                           cursor:pointer; text-align:center; transition:background .15s;"
                                    onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                                <i class="fa fa-sitemap d-block" style="font-size:20px; margin-bottom:4px; color:#2563eb;"></i>
                                Ver flujo
                            </button>

                            <button onclick="ofertaAccion('imprimir')"
                                    style="background:#fafafa; color:#374151; border:1.5px solid #e5e7eb;
                                           border-radius:10px; padding:11px 8px; font-size:12px; font-weight:700;
                                           cursor:pointer; text-align:center; transition:background .15s;"
                                    onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fafafa'">
                                <i class="fa fa-print d-block" style="font-size:20px; margin-bottom:4px; color:#6b7280;"></i>
                                Imprimir oferta
                            </button>

                            <button onclick="ofertaAccion('prefacturar')" id="btnPrefacturarOferta"
                                    style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none;
                                           border-radius:10px; padding:11px 8px; font-size:12px; font-weight:700;
                                           cursor:pointer; text-align:center; box-shadow:0 3px 10px rgba(230,81,0,.25); transition:opacity .15s;"
                                    onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                                <i class="fa fa-file-text-o d-block" style="font-size:20px; margin-bottom:4px;"></i>
                                Oferta ganadora
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL: Resultado de prefacturación --}}
        <div class="modal fade" id="modalPrefacturaExito" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" style="z-index:2075;">
            <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:440px;">
                <div class="modal-content" style="border-radius:20px; overflow:hidden; border:none; box-shadow:0 20px 60px rgba(0,0,0,.18); position:relative;">
                    {{-- Botón cerrar --}}
                    <button type="button" data-dismiss="modal" aria-label="Cerrar"
                            style="position:absolute; top:12px; right:14px; background:none; border:none;
                                   font-size:20px; color:#9e9e9e; cursor:pointer; line-height:1; z-index:1;
                                   padding:4px 8px; border-radius:50%;" title="Cerrar">&times;</button>
                    <div class="modal-body" style="padding:36px 32px 28px; text-align:center;">

                        {{-- Ícono check --}}
                        <div style="width:90px; height:90px; border-radius:50%;
                                    background:linear-gradient(135deg,#00897b,#26c6da);
                                    display:flex; align-items:center; justify-content:center;
                                    margin:0 auto 20px; box-shadow:0 8px 24px rgba(0,137,123,.28);">
                            <i class="fa fa-check" style="font-size:46px; color:#fff; line-height:1;"></i>
                        </div>

                        <h4 style="font-weight:800; color:#004d40; margin-bottom:6px; font-size:18px;">¡Prefactura generada!</h4>
                        <p id="msgPrefactura" style="color:#546e7a; font-size:13px; margin-bottom:6px;"></p>
                        <p style="color:#90a4ae; font-size:11px; margin-bottom:24px; line-height:1.5;">
                            <i class="mr-1 fa fa-info-circle"></i>
                            La prefactura <strong>reserva el inventario</strong> por el período de validez configurado.
                            Una vez vencido, la prefactura pierde validez automáticamente.
                        </p>

                        {{-- 3 botones --}}
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">

                            <button onclick="prefacturaAccion('facturar')"
                                    style="background:linear-gradient(135deg,#1b5e20,#2e7d32); color:#fff; border:none;
                                           border-radius:10px; padding:11px 6px; font-size:11px; font-weight:700;
                                           cursor:pointer; text-align:center; box-shadow:0 3px 10px rgba(27,94,32,.25); transition:opacity .15s;"
                                    onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                                <i class="fa fa-file-invoice d-block" style="font-size:20px; margin-bottom:4px;"></i>
                                Facturar
                            </button>

                            <button onclick="prefacturaAccion('flujo')"
                                    style="background:#eff6ff; color:#1e40af; border:1.5px solid #bfdbfe;
                                           border-radius:10px; padding:11px 6px; font-size:11px; font-weight:700;
                                           cursor:pointer; text-align:center; transition:background .15s;"
                                    onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                                <i class="fa fa-sitemap d-block" style="font-size:20px; margin-bottom:4px; color:#2563eb;"></i>
                                Ver flujo
                            </button>

                            <button onclick="prefacturaAccion('imprimir')"
                                    style="background:#fafafa; color:#374151; border:1.5px solid #e5e7eb;
                                           border-radius:10px; padding:11px 6px; font-size:11px; font-weight:700;
                                           cursor:pointer; text-align:center; transition:background .15s;"
                                    onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fafafa'">
                                <i class="fa fa-print d-block" style="font-size:20px; margin-bottom:4px; color:#6b7280;"></i>
                                Imprimir
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL: Seleccionar oferta ganadora --}}
        <style>
            /* Z-index por encima de los valores de IBOX (.modal=2050, .modal-dialog=2200) */
            #modalOfertasGanadoras { z-index: 2060 !important; }
            /* Prefactura éxito: siempre encima del modal de oferta y su backdrop */
            #modalPrefacturaExito { z-index: 2075 !important; }
            /* El backdrop del segundo modal debe apilarse correctamente */
            #modalPrefacturaExito ~ .modal-backdrop { z-index: 2070 !important; }

            /* Lista con scroll: altura fija para mostrar ~3 ofertas a la vez */
            #ogLista {
                max-height: 310px;
                overflow-y: auto;
                overflow-x: hidden;
                padding-right: 2px;
            }
            #ogLista::-webkit-scrollbar { width: 5px; }
            #ogLista::-webkit-scrollbar-thumb { background: #f9a826; border-radius: 4px; }

            /* Accordion de productos */
            .og-card { border-radius: 10px; margin-bottom: 10px; overflow: hidden; transition: box-shadow .2s; }
            .og-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.10); }
            .og-card-header {
                display: flex; align-items: center; gap: 8px;
                padding: 11px 14px; cursor: default;
            }
            .og-toggle-btn {
                background: none; border: none; padding: 0;
                display: flex; align-items: center; gap: 5px;
                font-size: 11px; font-weight: 700; color: #e65100;
                cursor: pointer; white-space: nowrap; flex-shrink: 0;
                transition: color .15s;
            }
            .og-toggle-btn:hover { color: #bf360c; }
            .og-toggle-icon {
                display: inline-block; width: 18px; height: 18px; line-height: 16px;
                border-radius: 50%; background: #fff3e0; border: 1.5px solid #f9a826;
                text-align: center; font-size: 13px; font-weight: 900; color: #e65100;
                transition: transform .3s, background .2s;
                flex-shrink: 0;
            }
            .og-toggle-btn.open .og-toggle-icon {
                transform: rotate(45deg);
                background: #e65100; color: #fff; border-color: #e65100;
            }
            .og-products {
                overflow: hidden;
                max-height: 0;
                transition: max-height .35s cubic-bezier(.4,0,.2,1), padding .25s;
                padding: 0 14px;
            }
            .og-products.open { max-height: 600px; padding: 0 14px 10px; }
            .og-prod-row {
                display: flex; justify-content: space-between; align-items: center;
                padding: 4px 0; border-bottom: 1px solid #f5f5f5;
                font-size: 12px;
            }
            .og-prod-row:last-child { border-bottom: none; }
        </style>
        <div class="modal fade" id="modalOfertasGanadoras" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document"
                 style="max-width:660px; width:94%;">
                <div class="modal-content" style="border-radius:16px; overflow:hidden; display:flex; flex-direction:column;">
                    <div class="modal-header" style="background:linear-gradient(135deg,#e65100,#f9a826); border:none; padding:14px 20px; flex-shrink:0;">
                        <h5 class="modal-title" style="color:#fff; font-weight:800; margin:0; font-size:14px;">
                            <i class="mr-2 fa fa-trophy"></i> Seleccionar oferta ganadora
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;"><span>&times;</span></button>
                    </div>
                    <div class="modal-body" style="padding:16px 18px; overflow:hidden; display:flex; flex-direction:column; flex:1; min-height:0;">
                        <div id="ogLoading" class="py-3 text-center" style="display:none;">
                            <i class="fa fa-spinner fa-spin fa-2x" style="color:#e65100;"></i>
                        </div>
                        <div id="ogLista"></div>
                    </div>
                    <div class="modal-footer" style="border-top:1px solid #f0f0f0; padding:10px 18px; flex-shrink:0; background:#fafafa;">
                        <button type="button" id="ogBtnVolver"
                                style="background:#fff; color:#e65100; border:2px solid #f9a826; border-radius:9px;
                                       padding:7px 22px; font-size:13px; font-weight:700; cursor:pointer; transition:background .15s;"
                                onmouseover="this.style.background='#fff3e0';" onmouseout="this.style.background='#fff';">
                            <i class="mr-1 fa fa-arrow-left"></i> Volver
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL: Productos del Pedido con sugerencias --}}
        <div class="modal fade" id="modalProductosPedido" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content" style="border-radius:14px; overflow:hidden;">
                    <div class="modal-header" style="background:linear-gradient(135deg,#e65100,#f9a826); border:none; padding:14px 24px;">
                        <h5 class="modal-title" style="color:#fff; font-weight:700; margin:0; font-size:14px;">
                            <i class="mr-2 fa fa-list-ul"></i> Productos del Pedido
                            @if(count($productosSugeridos) > 0)
                            <span style="background:rgba(255,255,255,.2); border-radius:20px; padding:1px 10px; font-size:12px; margin-left:6px;">{{ count($productosSugeridos) }}</span>
                            @endif
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;"><span>&times;</span></button>
                    </div>
                    <div class="modal-body" style="padding:16px 20px;">
                        <p style="font-size:11px; color:#78909c; margin-bottom:12px;">
                            <i class="mr-1 fa fa-info-circle text-info"></i>
                            Selecciona un producto sugerido para pre-cargarlo. Luego elige la bodega y categoría.
                        </p>
                        @if(count($productosSugeridos) > 0)
                        <div id="ppPagItems">
                        @foreach($productosSugeridos as $idx => $item)
                        <div class="pp-item" data-idx="{{ $idx }}" style="{{ $idx >= 5 ? 'display:none;' : '' }}background:#f9f9f9; border:1px solid #e0e0e0; border-radius:10px; padding:12px 16px; margin-bottom:10px;">
                            <div class="flex-wrap d-flex align-items-center" style="gap:8px; margin-bottom:6px;">
                                <span style="font-weight:700; color:#2c3e50; font-size:13px;">{{ $item['nombre_pedido'] }}</span>
                                <span style="background:#e8f5e9; color:#2e7d32; border-radius:12px; padding:1px 9px; font-size:11px; font-weight:700;">x{{ $item['cantidad'] }}</span>
                            </div>
                            @if(count($item['similares']) > 0)
                            <div style="font-size:10px; color:#546e7a; font-weight:700; text-transform:uppercase; letter-spacing:.3px; margin-bottom:5px;">Similares en catálogo:</div>
                            <div class="flex-wrap d-flex" style="gap:6px;">
                                @foreach($item['similares'] as $sim)
                                @php $s = (array)$sim; @endphp
                                <button type="button"
                                    onclick="preseleccionarProductoSugerido({{ $s['id'] }}, '{{ addslashes($s['nombre']) }}');"
                                    style="background:#e8f5e9; color:#1b5e20; border:1px solid #a5d6a7; border-radius:7px; padding:6px 12px; font-size:12px; font-weight:600; cursor:pointer;"
                                    onmouseover="this.style.background='#c8e6c9';" onmouseout="this.style.background='#e8f5e9';">
                                    <i class="mr-1 fa fa-plus-circle"></i>{{ Str::limit($s['nombre'], 38) }}
                                </button>
                                @endforeach
                            </div>
                            @else
                            <div style="font-size:11px; color:#90a4ae;"><i class="mr-1 fa fa-exclamation-triangle"></i>Sin coincidencias en catálogo.</div>
                            @endif
                        </div>
                        @endforeach
                        </div>
                        {{-- Paginación --}}
                        @if(count($productosSugeridos) > 5)
                        <div class="mt-2 d-flex align-items-center justify-content-between" id="ppPagNav">
                            <button type="button" onclick="ppChangePage(-1)"
                                style="background:#f5f5f5; border:1px solid #e0e0e0; border-radius:7px; padding:5px 14px; font-size:12px; font-weight:600; cursor:pointer;" id="ppBtnPrev" disabled>
                                <i class="mr-1 fa fa-chevron-left"></i> Anterior
                            </button>
                            <span id="ppPageInfo" style="font-size:12px; color:#546e7a; font-weight:700;"></span>
                            <button type="button" onclick="ppChangePage(1)"
                                style="background:#1b5e20; color:#fff; border:none; border-radius:7px; padding:5px 14px; font-size:12px; font-weight:600; cursor:pointer;" id="ppBtnNext">
                                Siguiente <i class="ml-1 fa fa-chevron-right"></i>
                            </button>
                        </div>
                        @endif
                        @else
                        <p class="text-center text-muted">No hay productos sugeridos.</p>
                        @endif
                    </div>
                    <div class="modal-footer" style="padding:10px 20px;">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Buscador de producto reutilizable --}}
    <x-buscador-producto id-modal="buscadorProductoUnificado" callback="alSeleccionarProducto" />

    @push('scripts')
    <script>
    // ================================================================
    // CONFIGURACIÓN DEL TIPO DE FACTURA (desde PHP)
    // ================================================================
    var tipoFacturaConfig = @json($config);

    // Mapa de URLs por código de tipo de factura
    var urlsPorTipo = {
        estatal: {
            guardar: '/ventas/estatal/guardar',
            listar_clientes: '/estatal/lista/clientes',
            datos_cliente: '/estatal/datos/cliente',
            datos_producto: '/estatal/datos/producto',
            tipo_pago: '/estatal/tipo/pago',
            bodegas: '/estatal/listar/bodegas/{idProducto}',
            imprimir: '/estatal/factura/{id}',
            historial_precios: '/estatal/historial/precios',
            vendedores: '/ventas/corporativo/vendedores',
            orden_compra: '/ventas/numero/orden',
            codigos_exoneracion: null
        },
        sin_restriccion_gobierno: {
            guardar: '/ventas/estatal/guardar',
            listar_clientes: '/estatal/lista/clientes',
            datos_cliente: '/estatal/datos/cliente',
            datos_producto: '/estatal/datos/producto',
            tipo_pago: '/estatal/tipo/pago',
            bodegas: '/estatal/listar/bodegas/{idProducto}',
            imprimir: '/estatal/factura/{id}',
            historial_precios: '/estatal/historial/precios',
            vendedores: '/ventas/corporativo/vendedores',
            orden_compra: null,
            codigos_exoneracion: null
        },
        corporativa: {
            guardar: '/ventas/corporativo/guardar',
            listar_clientes: '/ventas/lista/clientes',
            datos_cliente: '/ventas/datos/cliente',
            datos_producto: '/ventas/datos/producto',
            tipo_pago: '/ventas/tipo/pago',
            bodegas: '/ventas/listar/bodegas/{idProducto}',
            imprimir: '/factura/cooporativo/{id}',
            historial_precios: '/estatal/historial/precios',
            vendedores: '/ventas/corporativo/vendedores',
            orden_compra: '/ventas/numero/orden',
            codigos_exoneracion: null
        },
        sin_restriccion_precio: {
            guardar: '/ventas/corporativo/guardar',
            listar_clientes: '/ventas/lista/clientes',
            datos_cliente: '/ventas/datos/cliente',
            datos_producto: '/ventas/datos/producto',
            tipo_pago: '/ventas/tipo/pago',
            bodegas: '/ventas/listar/bodegas/{idProducto}',
            imprimir: '/factura/cooporativo/{id}',
            historial_precios: '/estatal/historial/precios',
            vendedores: '/ventas/corporativo/vendedores',
            orden_compra: null,
            codigos_exoneracion: null
        },
        exoneradas: {
            guardar: '/exonerado/venta/guardar',
            listar_clientes: '/exonerado/lista/clientes',
            datos_cliente: '/ventas/datos/cliente',
            datos_producto: '/estatal/datos/producto',
            tipo_pago: '/estatal/tipo/pago',
            bodegas: '/estatal/listar/bodegas/{idProducto}',
            imprimir: '/exonerado/factura/{id}',
            historial_precios: '/estatal/historial/precios',
            vendedores: '/ventas/corporativo/vendedores',
            orden_compra: null,
            codigos_exoneracion: '/exonerado/listar/codigos'
        },
        cotizacion_clientes_a: {
            guardar: '/guardar/cotizacion',
            listar_clientes: '/cotizacion/clientes',
            datos_cliente: '/estatal/datos/cliente',
            datos_producto: '/estatal/datos/producto',
            tipo_pago: '/estatal/tipo/pago',
            bodegas: '/estatal/listar/bodegas/{idProducto}',
            imprimir: '/cotizacion/imprimir/{id}',
            historial_precios: '/estatal/historial/precios',
            vendedores: '/ventas/corporativo/vendedores',
            orden_compra: null,
            codigos_exoneracion: null
        }
    };

    // Obtener URLs del tipo actual
    var codigoActual = tipoFacturaConfig ? tipoFacturaConfig.codigo : 'estatal';
    var urls = urlsPorTipo[codigoActual] || urlsPorTipo['estatal'];

    var numeroInputs = 0;
    var arregloIdInputs = [];
    var retencionEstado = false;
    var diasCredito = 0;
    var idAutorizacion = 0;
    var idFactura = 0;
    var public_path = "{{ asset('catalogo/') }}";

    // ================================================================
    // INICIALIZACIÓN
    // ================================================================
    window.onload = function() {
        inicializarFormulario();
    };

    function inicializarFormulario() {
        obtenerTipoPago();
        inicializarSelect2();

        // Si requiere código de autorización, mostrar modal
        if (tipoFacturaConfig && tipoFacturaConfig.requiere_codigo_autorizacion) {
            $('#modal_solicitar_codigo').modal('show');
        }
    }

    function inicializarSelect2() {
        var urlClientes = urls.listar_clientes;
        var urlVendedores = urls.vendedores;

        // Destruir select2 existentes si los hay
        if ($('#vendedor').hasClass('select2-hidden-accessible')) {
            $('#vendedor').select2('destroy');
        }
        if ($('#seleccionarCliente').hasClass('select2-hidden-accessible')) {
            $('#seleccionarCliente').select2('destroy');
        }

        $('#vendedor').select2({
            ajax: {
                url: urlVendedores,
                data: function(params) {
                    return { search: params.term, type: 'public', page: params.page || 1 };
                }
            }
        });

        $('#seleccionarCliente').select2({
            ajax: {
                url: urlClientes,
                data: function(params) {
                    return { search: params.term, type: 'public', page: params.page || 1 };
                }
            }
        });

        // ── Pre-seleccionar vendedor = usuario actual ──────────────────────
        @if(!empty($vendedorDefault))
        (function() {
            var opt = new Option(
                '{{ addslashes($vendedorDefault['name']) }}',
                '{{ $vendedorDefault['id'] }}',
                true, true
            );
            $('#vendedor').append(opt).trigger('change');
        })();
        @endif

        // ── Pre-seleccionar cliente si viene de un pedido ─────────────────
        @if($clientePedido)
        (function() {
            var opt = new Option(
                '{{ addslashes($clientePedido['nombre']) }}',
                '{{ $clientePedido['id'] }}',
                true, true
            );
            $('#seleccionarCliente').append(opt).trigger('change');
            setTimeout(function() { obtenerDatosCliente(); }, 300);
        })();
        @endif
    }

    // ================================================================
    // PRODUCTO SUGERIDO DESDE PEDIDO → Pre-selecciona en el selector
    // ================================================================
    function preseleccionarProductoSugerido(id, nombre) {
        // Cierra el modal de productos del pedido
        $('#modalProductosPedido').modal('hide');

        // Rellena el select oculto igual que alSeleccionarProducto()
        var select = document.getElementById('seleccionarProducto');
        select.innerHTML = '<option value="' + id + '" selected>' + nombre + '</option>';
        document.getElementById('codigoProductoBuscar').value = nombre;
        var label = document.getElementById('productoSeleccionadoLabel');
        label.textContent = '\u2713 ' + nombre;
        label.classList.remove('d-none');

        // Cargar categorías e imágenes (cargarCategoriasProducto llama obtenerImagenes internamente)
        cargarCategoriasProducto();
    }

    // ================================================================
    // ================================================================
    // OF-CARD TOGGLE
    // ================================================================
    function toggleOfCard(bodyId, titleEl) {
        var body = document.getElementById(bodyId);
        if (!body) return;
        var isOpen = body.style.display !== 'none';
        body.style.display = isOpen ? 'none' : '';
        // Rotate chevron
        var chevron = titleEl.querySelector('.of-chevron');
        if (chevron) chevron.style.transform = isOpen ? 'rotate(-90deg)' : '';
    }

    // ================================================================
    // SECCIONES COLAPSABLES
    // ================================================================
    function toggleSeccion(id, headerEl) {
        var el = document.getElementById(id);
        if (!el) return;
        var isOpen = el.style.display !== 'none';
        el.style.display = isOpen ? 'none' : '';
        // Girar ícono chevron
        var icoId = 'ico_' + id;
        var ico = document.getElementById(icoId);
        if (ico) {
            if (isOpen) {
                ico.classList.remove('fa-chevron-up');
                ico.classList.add('fa-chevron-down');
            } else {
                ico.classList.remove('fa-chevron-down');
                ico.classList.add('fa-chevron-up');
            }
        }
    }

    // ================================================================
    // PAGINACIÓN MODAL PRODUCTOS PEDIDO
    // ================================================================
    var ppCurrentPage = 0;
    var ppItemsPerPage = 5;

    function ppChangePage(dir) {
        var items = document.querySelectorAll('#ppPagItems .pp-item');
        var total = items.length;
        var totalPages = Math.ceil(total / ppItemsPerPage);
        ppCurrentPage = Math.max(0, Math.min(ppCurrentPage + dir, totalPages - 1));
        var from = ppCurrentPage * ppItemsPerPage;
        var to   = from + ppItemsPerPage;
        items.forEach(function(el, i) { el.style.display = (i >= from && i < to) ? '' : 'none'; });
        var prev = document.getElementById('ppBtnPrev');
        var next = document.getElementById('ppBtnNext');
        var info = document.getElementById('ppPageInfo');
        if (prev) prev.disabled = ppCurrentPage === 0;
        if (next) next.disabled = ppCurrentPage >= totalPages - 1;
        if (next) next.style.opacity = ppCurrentPage >= totalPages - 1 ? '.5' : '1';
        if (info) info.textContent = 'Página ' + (ppCurrentPage + 1) + ' / ' + totalPages;
    }

    // Inicializar paginación cuando el modal se abre
    $('#modalProductosPedido').on('show.bs.modal', function() {
        ppCurrentPage = 0;
        ppChangePage(0);
    });

    // ================================================================
    // PAGINACIÓN MODAL DETALLE PEDIDO
    // ================================================================
    var pdCurrentPage = 0;
    var pdItemsPerPage = 5;

    function pdChangePage(dir) {
        var rows = document.querySelectorAll('#pdTbody tr[data-pd-idx]');
        var total = rows.length;
        if (total === 0) return;
        var totalPages = Math.ceil(total / pdItemsPerPage);
        pdCurrentPage = Math.max(0, Math.min(pdCurrentPage + dir, totalPages - 1));
        var from = pdCurrentPage * pdItemsPerPage;
        var to   = from + pdItemsPerPage;
        rows.forEach(function(el, i) { el.style.display = (i >= from && i < to) ? '' : 'none'; });
        var prev = document.getElementById('pdPrev');
        var next = document.getElementById('pdNext');
        var info = document.getElementById('pdPageInfo');
        if (prev) prev.disabled = pdCurrentPage === 0;
        if (prev) prev.style.opacity = pdCurrentPage === 0 ? '.5' : '1';
        if (next) next.disabled = pdCurrentPage >= totalPages - 1;
        if (next) next.style.opacity = pdCurrentPage >= totalPages - 1 ? '.5' : '1';
        if (info) info.textContent = 'Página ' + (pdCurrentPage + 1) + ' / ' + totalPages;
    }

    $('#modalDetallePedido').on('show.bs.modal', function() {
        pdCurrentPage = 0;
        pdChangePage(0);
    });

    function cambiarTipoFactura(rutaMenu) {
        window.location.href = '/' + rutaMenu;
    }

    function cambiarTipoFacturaDesdeUrl(rutaMenu) {
        // Preserva los parámetros de prefactura (from=prefactura, prefactura_id, flujoId)
        const urlParams = new URLSearchParams(window.location.search);
        const from = urlParams.get('from');
        const prefacturaId = urlParams.get('prefactura_id');
        const flujoId = urlParams.get('flujoId');

        let newUrl = '/' + rutaMenu;
        if (from && prefacturaId && flujoId) {
            newUrl += '?from=' + from + '&prefactura_id=' + prefacturaId + '&flujoId=' + flujoId;
        }
        window.location.href = newUrl;
    }

    // ================================================================
    // BROWSER EVENTS: Pedido vinculado / desvinculado (Livewire → JS)
    // ================================================================
    window.addEventListener('pedido-seleccionado', function(e) {
        var d = e.detail;
        // Re-habilitar Select2 de cliente (puede estar disabled en re-render)
        var selC = document.getElementById('seleccionarCliente');
        if (selC) selC.removeAttribute('disabled');

        // Reinicializar Select2 si es necesario
        if (!$('#seleccionarCliente').hasClass('select2-hidden-accessible')) {
            inicializarSelect2();
        }

        // Pre-seleccionar cliente
        if (d.clienteId) {
            $('#seleccionarCliente').empty();
            var optC = new Option(d.clienteNombre, d.clienteId, true, true);
            $('#seleccionarCliente').append(optC).trigger('change');
            setTimeout(function() { obtenerDatosCliente(); }, 300);
        }

        // Pre-seleccionar vendedor por defecto
        if (d.vendedorId) {
            $('#vendedor').empty();
            var optV = new Option(d.vendedorNombre, d.vendedorId, true, true);
            $('#vendedor').append(optV).trigger('change');
        }

        // Bloquear cliente cuando hay pedido vinculado
        $('#seleccionarCliente').prop('disabled', true);
    });

    window.addEventListener('pedido-desvinculado', function(e) {
        var d = e.detail;
        // Habilitar cliente nuevamente
        $('#seleccionarCliente').prop('disabled', false);
        // Limpiar cliente
        $('#seleccionarCliente').empty().append('<option value="" selected disabled>--Seleccionar un cliente--</option>').trigger('change');
        document.getElementById('nombre_cliente_ventas').value = '';
        document.getElementById('rtn_ventas').value = '';

        // Restaurar vendedor por defecto
        if (d.vendedorId) {
            $('#vendedor').empty();
            var optV = new Option(d.vendedorNombre, d.vendedorId, true, true);
            $('#vendedor').append(optV).trigger('change');
        }

        // Actualizar badge categoría
        $('#cat_badge_text').text('\u2014');
    });

    window.addEventListener('mostrar-modal-detalle-pedido', function() {
        $('#modalDetallePedido').modal('show');
    });

    // ================================================================
    // CÓDIGO DE AUTORIZACIÓN (sin restricción gobierno / sin restricción precio)
    // ================================================================
    function solicitarCodigo() {
        axios.get('/ventas/solicitud/codigo')
            .then(response => {
                $("#modal_solicitar_codigo").removeClass("fade").modal("hide");
                $("#modalPermiso").modal("show").addClass("fade");
            })
            .catch(err => {
                console.log(err);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al solicitar código' });
            });
    }

    $(document).on('submit', '#verificarCodigoForm', function(event) {
        event.preventDefault();
        ocultarModalVerificar();
    });

    function ocultarModalVerificar() {
        let codigo = document.getElementById('codigo').value;
        axios.post('/ventas/verificar/codigo', { codigo: codigo })
            .then(response => {
                let data = response.data;
                if (data.estado == 1) {
                    $("#modalPermiso").removeClass("fade").modal("hide");
                    document.getElementById('mensajeCodigo').classList.add('d-none');
                    document.getElementById('codigo_autorizacion').value = data.idAutorizacion;
                } else {
                    document.getElementById('mensajeCodigo').classList.remove('d-none');
                    document.getElementById('codigo_autorizacion').value = '';
                }
            })
            .catch(err => { console.log(err); });
    }

    function desactivarCodigo() {
        if (!idAutorizacion) return;
        axios.post('/ventas/autorizacion/desactivar', { idAutorizacion: idAutorizacion })
            .then(response => {
                let element = document.getElementById("div_imprimir");
                element.classList.remove("d-none");
                $("#modal_solicitar_codigo").modal("show").addClass("fade");
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Error!', text: 'Error al desactivar código' });
            });
    }

    // ================================================================
    // BUSCADOR DE PRODUCTO
    // ================================================================
    function limpiarProducto() {
        document.getElementById('seleccionarProducto').innerHTML = '<option value="" selected disabled></option>';
        document.getElementById('codigoProductoBuscar').value = '';
        var lbl = document.getElementById('productoSeleccionadoLabel');
        lbl.classList.add('d-none'); lbl.textContent = '';
        document.getElementById('historialPreciosPanel').querySelector('#historialPreciosCuerpo').innerHTML =
            '<p class="mb-0 text-muted small">Sin ventas previas de este producto a este cliente.</p>';
        document.getElementById('historialPreciosPanel').classList.remove('d-none');
    }

    function alSeleccionarProducto(producto) {
        var select = document.getElementById('seleccionarProducto');
        select.innerHTML = '<option value="' + producto.id + '" selected>' + producto.nombre + '</option>';
        document.getElementById('codigoProductoBuscar').value = producto.nombre;
        var label = document.getElementById('productoSeleccionadoLabel');
        label.textContent = '✓ ' + producto.nombre + ' (ID: ' + producto.id + ')';
        label.classList.remove('d-none');
        cargarCategoriasProducto();
    }

    function buscarPorCodigo(cod) {
        cod = String(cod).trim();
        if (!cod) { window['abrirBuscador_buscadorProductoUnificado'](''); return; }
        axios.get('/productos/buscar', { params: { q: cod, page: 1 } })
            .then(function(r) {
                var items = r.data.data;
                var exact = items.find(function(p) { return String(p.id) === cod; });
                if (exact) { alSeleccionarProducto(exact); }
                else if (items.length === 1) { alSeleccionarProducto(items[0]); }
                else { window['abrirBuscador_buscadorProductoUnificado'](cod); }
            });
    }

    // ================================================================
    // CLIENTE
    // ================================================================
    function obtenerDatosCliente() {
        let idCliente = document.getElementById("seleccionarCliente").value;
        if (!idCliente) return; // Evitar error al desvincular pedido
        var urlDatosCliente = urls.datos_cliente;

        axios.post(urlDatosCliente, { id: idCliente })
            .then(response => {
                let data = response.data.datos;

                if (data.id == 1) {
                    document.getElementById("nombre_cliente_ventas").readOnly = false;
                    document.getElementById("rtn_ventas").readOnly = false;

                    let selectBox = document.getElementById("tipoPagoVenta");
                    if (selectBox.options.length > 2) selectBox.remove(2);

                    $('#categoria_cliente_nombre').text(data.nombre_categoria);
                    if (data.nombre_categoria) { $('#cat_badge_text').text(data.nombre_categoria); $('#cat_cliente_badge').show(); }
                    $('#categoria_cliente_venta_id').data('categoria-cliente-id', data.idcategoriacliente);

                    if ($('#seleccionarProducto').val()) {
                        cargarCategoriasProducto();
                    } else {
                        $('#categoria_cliente_venta_id').empty()
                            .append(new Option(data.nombre_categoria, data.idcategoriacliente, true, true));
                    }
                } else {
                    document.getElementById("nombre_cliente_ventas").readOnly = true;
                    document.getElementById("rtn_ventas").readOnly = true;
                    document.getElementById("nombre_cliente_ventas").value = data.nombre;
                    document.getElementById("rtn_ventas").value = data.rtn;

                    $('#categoria_cliente_nombre').text(data.nombre_categoria);
                    if (data.nombre_categoria) { $('#cat_badge_text').text(data.nombre_categoria); $('#cat_cliente_badge').show(); }
                    $('#categoria_cliente_venta_id').data('categoria-cliente-id', data.idcategoriacliente);

                    if ($('#seleccionarProducto').val()) {
                        cargarCategoriasProducto();
                    } else {
                        $('#categoria_cliente_venta_id').empty()
                            .append(new Option(data.nombre_categoria, data.idcategoriacliente, true, true));
                    }

                    diasCredito = data.dias_credito;
                    obtenerTipoPago();

                    // Orden de compra si aplica
                    if (tipoFacturaConfig && tipoFacturaConfig.requiere_orden_compra) {
                        obtenerOrdenesCompra();
                    }

                    // Códigos de exoneración si aplica
                    if (tipoFacturaConfig && tipoFacturaConfig.requiere_codigo_exoneracion) {
                        obtenerCodigosExoneracion(idCliente);
                    }

                    cargarHistorialPrecios();
                }
            })
            .then(() => {
                window.dispatchEvent(new CustomEvent('cliente-datos-cargados'));
            })
            .catch(err => {
                console.log(err);
                Swal.fire({ icon: 'error', title: 'Error...', text: "Error al obtener datos del cliente" });
            });
    }

    // ================================================================
    // CÓDIGOS DE EXONERACIÓN (solo para tipo exoneradas)
    // ================================================================
    function obtenerCodigosExoneracion(idCliente) {
        if (!urls.codigos_exoneracion) return;

        axios.get(urls.codigos_exoneracion, { params: { cliente_id: idCliente } })
            .then(response => {
                let codigos = response.data.codigos || response.data;
                let html = '<option value="" selected disabled>--Seleccione un código--</option>';
                if (Array.isArray(codigos)) {
                    codigos.forEach(c => {
                        html += '<option value="' + c.id + '">' + (c.codigo || c.correlativo || c.id) + '</option>';
                    });
                }
                document.getElementById('codigoExoneracion').innerHTML = html;
            })
            .catch(err => {
                console.log(err);
            });
    }

    // ================================================================
    // TIPO DE PAGO
    // ================================================================
    function obtenerTipoPago() {
        var urlTipoPago = urls.tipo_pago;

        axios.get(urlTipoPago)
            .then(response => {
                let tipoDePago = response.data.tipos;
                let numeroVenta = response.data.numeroVenta.numero;
                let htmlPagos = '<option value="" selected disabled>--Seleccione una opcion--</option>';
                tipoDePago.forEach(element => {
                    htmlPagos += '<option value="' + element.id + '">' + element.descripcion + '</option>';
                });
                document.getElementById('tipoPagoVenta').innerHTML = htmlPagos;
                document.getElementById("numero_venta").value = numeroVenta;
                // Auto-seleccionar "Contado" por defecto
                let selPago = document.getElementById('tipoPagoVenta');
                for (let i = 0; i < selPago.options.length; i++) {
                    if (selPago.options[i].text.toLowerCase().includes('contado')) {
                        selPago.selectedIndex = i;
                        validarFechaPago();
                        break;
                    }
                }
            })
            .catch(err => {
                console.log(err);
                Swal.fire({ icon: 'error', title: 'Error...', text: "Error al obtener tipos de pago" });
            });
    }

    // ================================================================
    // CATEGORÍAS DE PRODUCTO
    // ================================================================
    function cargarCategoriasProducto() {
        let productoId = $('#seleccionarProducto').val();
        let clienteId = $('#seleccionarCliente').val();

        if (!productoId) {
            $('#categoria_cliente_venta_id').empty().append('<option value="" selected disabled>--Seleccione primero un producto--</option>');
            return;
        }

        $('#categoria_cliente_venta_id').empty().append('<option value="" selected disabled>Cargando categorías...</option>');

        axios.post('/producto/categorias-disponibles', { producto_id: productoId })
            .then(response => {
                let categorias = response.data.categorias;
                if (categorias.length > 0) {
                    categorias.sort((a, b) => (parseFloat(b.precio_a) || 0) - (parseFloat(a.precio_a) || 0));

                    $('#categoria_cliente_venta_id').empty().append('<option value="" selected disabled>--Seleccione una categoría--</option>');
                    let categoriaClienteId = $('#categoria_cliente_venta_id').data('categoria-cliente-id');

                    categorias.forEach(categoria => {
                        let precio = parseFloat(categoria.precio_a) || 0;
                        let precioFormateado = new Intl.NumberFormat('es-HN', {
                            style: 'currency', currency: 'HNL', minimumFractionDigits: 2
                        }).format(precio);
                        let textoOpcion = categoria.nombre_categoria + ' - ' + precioFormateado;
                        let isSelected = (clienteId && categoria.id == categoriaClienteId);
                        let option = new Option(textoOpcion, categoria.id, isSelected, isSelected);
                        $('#categoria_cliente_venta_id').append(option);
                    });
                    $('#categoria_cliente_venta_id').prop('disabled', false);
                } else {
                    $('#categoria_cliente_venta_id').empty().append('<option value="" selected disabled>No hay categorías disponibles</option>');
                    Swal.fire({ icon: 'warning', title: 'Advertencia', text: 'Este producto no tiene escalas de precio.' });
                }
            })
            .catch(err => {
                console.log(err);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar categorías.' });
            });

        obtenerImagenes();
        cargarHistorialPrecios();
    }

    function habilitarBodega() {
        let categoriaId = $('#categoria_cliente_venta_id').val();
        let productoId = $('#seleccionarProducto').val();
        if (categoriaId && productoId) {
            $('#bodega').prop('disabled', false);
            obtenerBodegas(productoId);
        }
    }

    // ================================================================
    // HISTORIAL DE PRECIOS
    // ================================================================
    function cargarHistorialPrecios() {
        var productoId = $('#seleccionarProducto').val();
        var clienteId = $('#seleccionarCliente').val();
        var panel = document.getElementById('historialPreciosPanel');
        var cuerpo = document.getElementById('historialPreciosCuerpo');

        if (!productoId || !clienteId) {
            cuerpo.innerHTML = '<p class="mb-0 text-muted small">Sin ventas previas de este producto a este cliente.</p>';
            return;
        }

        var urlHistorial = urls.historial_precios;

        cuerpo.innerHTML = '<p class="text-muted small"><i class="fa fa-spinner fa-spin"></i> Cargando historial...</p>';
        panel.classList.remove('d-none');

        axios.post(urlHistorial, { cliente_id: clienteId, producto_id: productoId })
            .then(function(response) {
                var rows = response.data.historial;
                if (!rows || rows.length === 0) {
                    cuerpo.innerHTML = '<p class="text-muted small">No hay ventas previas.</p>';
                    return;
                }
                var fmt = new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL', minimumFractionDigits: 2 });
                var html = '<div class="table-responsive"><table class="table mb-0 table-sm table-bordered table-hover" style="font-size:0.82rem;"><thead class="thead-light"><tr><th>Fecha</th><th>Factura</th><th>Precio Unit.</th><th>Cant.</th><th>Total</th><th>Categoría</th></tr></thead><tbody>';
                rows.forEach(function(r) {
                    html += '<tr><td>' + r.fecha_emision + '</td><td>' + r.numero_factura + '</td><td class="text-right font-weight-bold text-success">' + fmt.format(r.precio_unidad) + '</td><td class="text-center">' + r.cantidad + '</td><td class="text-right">' + fmt.format(r.total) + '</td><td><span class="badge badge-secondary">' + r.categoria + '</span></td></tr>';
                });
                html += '</tbody></table></div>';
                cuerpo.innerHTML = html;
            })
            .catch(function() { cuerpo.innerHTML = '<p class="text-danger small">Error al cargar historial.</p>'; });
    }

    // ================================================================
    // BODEGAS
    // ================================================================
    function obtenerBodegas(id) {
        document.getElementById('bodega').innerHTML = "<option selected disabled>--Seleccione una bodega--</option>";
        var urlBase = urls.bodegas;
        var url = urlBase.replace('{idProducto}', id);

        $('#bodega').select2({
            ajax: {
                url: url,
                data: function(params) {
                    return { search: params.term, type: 'public', page: params.page || 1, idProducto: id };
                }
            }
        });
    }

    // ================================================================
    // IMÁGENES
    // ================================================================
    function obtenerImagenes() {
        let id = document.getElementById('seleccionarProducto').value;
        let htmlImagenes = '';
        axios.post('/producto/listar/imagenes', { id: id })
            .then(response => {
                let imagenes = response.data.imagenes;
                let detalleUrl = '/producto/detalle/' + id;
                if (imagenes.length == 0) {
                    document.getElementById('bloqueImagenes').innerHTML =
                        '<div class="carousel-item active">' +
                        '<a href="' + detalleUrl + '" target="_blank" title="Ver detalles del producto" style="display:block;">' +
                        '<div style="height:200px;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#f5f5f5;border-radius:8px;cursor:pointer;border:2px dashed #cfd8dc;">' +
                        '<i class="fa fa-image" style="font-size:3rem;color:#b0bec5;margin-bottom:8px;"></i>' +
                        '<span style="font-size:12px;color:#78909c;font-weight:600;">Sin imagen — clic para ver detalles</span>' +
                        '</div></a></div>';
                } else {
                    imagenes.forEach(element => {
                        let activeClass = element.contador == 1 ? ' active' : '';
                        htmlImagenes += '<div class="carousel-item' + activeClass + '">' +
                            '<a href="' + detalleUrl + '" target="_blank" title="Ver detalles del producto" style="display:block;">' +
                            '<img class="d-block" src="' + public_path + '/' + element.url_img + '" alt="imagen ' + element.contador + '" style="width:100%;height:30rem;cursor:pointer;"></a></div>';
                    });
                    document.getElementById('bloqueImagenes').innerHTML = htmlImagenes;
                }
                document.getElementById('botonAdd').classList.add("d-none");
            })
            .catch(err => { console.log(err); });

        obtenerBodegas(id);
    }

    // ================================================================
    // AGREGAR PRODUCTO AL CARRITO
    // ================================================================
    function agregarProductoCarrito() {
        let idProducto = document.getElementById('seleccionarProducto').value;
        let categoria_cliente_venta_id = document.getElementById('categoria_cliente_venta_id').value;
        let data = $("#bodega").select2('data')[0];
        let bodega = data.bodegaSeccion;
        let idBodega = data.idBodega;
        let idSeccion = data.id;

        var urlDatosProducto = urls.datos_producto;

        axios.post(urlDatosProducto, { idProducto: idProducto, categoria_cliente_venta_id: categoria_cliente_venta_id })
            .then(response => {
                // Verificar duplicados
                let flag = false;
                arregloIdInputs.forEach(idInpunt => {
                    let idProductoFila = document.getElementById("idProducto" + idInpunt).value;
                    let idSeccionFila = document.getElementById("idSeccion" + idInpunt).value;
                    if (idProducto == idProductoFila && idSeccion == idSeccionFila && !flag) flag = true;
                });

                if (flag) {
                    Swal.fire({
                        icon: 'warning', title: 'Advertencia!',
                        html: '<p class="text-left">La sección de bodega y producto ha sido agregada anteriormente.<br><br>Por favor verificar la sección de bodega y producto sea distinto a los ya existentes.</p>'
                    });
                    return;
                }

                let producto = response.data.producto;
                let arrayUnidades = response.data.unidades;
                numeroInputs += 1;

                let htmlSelectUnidades = "";
                arrayUnidades.forEach(unidad => {
                    let sel = unidad.valor_defecto == 1 ? 'selected' : '';
                    htmlSelectUnidades += '<option ' + sel + ' value="' + unidad.id + '" data-id="' + unidad.idUnidadVenta + '">' + unidad.nombre + '</option>';
                });

                // Determinar opciones de precios según configuración
                let htmlprecios = '';
                if (tipoFacturaConfig && tipoFacturaConfig.multiples_precios) {
                    // Múltiples precios A/B/C/D (sin restricción)
                    htmlprecios = '<option value="' + producto.precio1 + '" data-id="p1" selected>' + producto.precio1 + ' - A</option>';
                    if (producto.precio2) htmlprecios += '<option value="' + producto.precio2 + '" data-id="p2">' + producto.precio2 + ' - B</option>';
                    if (producto.precio3) htmlprecios += '<option value="' + producto.precio3 + '" data-id="p3">' + producto.precio3 + ' - C</option>';
                    if (producto.precio4) htmlprecios += '<option value="' + producto.precio4 + '" data-id="p4">' + producto.precio4 + ' - D</option>';
                } else {
                    // Solo precio A (con restricción)
                    htmlprecios = '<option value="' + producto.precio1 + '" data-id="p1" selected>' + producto.precio1 + ' - A</option>';
                }

                // Determinar el min del precio
                let minPrecio = (tipoFacturaConfig && tipoFacturaConfig.multiples_precios) ? '' : 'min="' + producto.precio1 + '"';

                let html = `
                <tr id='${numeroInputs}'>
                    <td style="vertical-align:middle; text-align:center; padding:4px 6px;">
                        <input id="idProducto${numeroInputs}" name="idProducto${numeroInputs}" type="hidden" value="${producto.id}">
                        <input id="precios_producto_carga_id${numeroInputs}" name="precios_producto_carga_id${numeroInputs}" type="hidden" value="${producto.precios_producto_carga_id || ''}">
                        <input id="isv${numeroInputs}" name="isv${numeroInputs}" type="hidden" value="${producto.isv}">
                        <input id="idBodega${numeroInputs}" name="idBodega${numeroInputs}" type="hidden" value="${idBodega}">
                        <input id="idSeccion${numeroInputs}" name="idSeccion${numeroInputs}" type="hidden" value="${idSeccion}">
                        <input id="restaInventario${numeroInputs}" name="restaInventario${numeroInputs}" type="hidden" value="">
                        <input id="subTotal${numeroInputs}" name="subTotal${numeroInputs}" type="hidden" value="" required>
                        <input id="isvProducto${numeroInputs}" name="isvProducto${numeroInputs}" type="hidden" value="" required>
                        <input id="acumuladoDescuento${numeroInputs}" name="acumuladoDescuento${numeroInputs}" type="hidden">
                        <input id="total${numeroInputs}" name="total${numeroInputs}" type="hidden" value="" required>
                        <input id="bodega${numeroInputs}" name="bodega${numeroInputs}" type="hidden" value="${bodega}">
                        <button class="btn btn-danger btn-xs" type="button" onclick="eliminarInput(${numeroInputs})" title="Eliminar" style="padding:2px 6px; font-size:11px; border-radius:5px;">
                            <i class="fa fa-times"></i>
                        </button>
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <input type="text" id="nombre${numeroInputs}" name="nombre${numeroInputs}" value='${producto.nombre}' readonly data-parsley-required
                            style="border:none; background:transparent; font-size:12px; font-weight:700; color:#1b5e20; width:100%; min-width:130px;">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px; white-space:nowrap;">
                        <span style="background:#e3f2fd; color:#1565c0; border-radius:6px; padding:2px 8px; font-size:11px; font-weight:700;">
                            <i class="fa fa-archive" style="font-size:10px;"></i> ${bodega}
                        </span>
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <select class="form-control form-control-sm" name="precios${numeroInputs}" id="precios${numeroInputs}" data-parsley-required style="font-size:11px; min-width:100px;"
                            onchange="validacionPrecio(precios${numeroInputs}, precio${numeroInputs})">
                            ${htmlprecios}
                        </select>
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <input type="number" id="precio${numeroInputs}" name="precio${numeroInputs}" value="${producto.precio1}" class="form-control form-control-sm"
                            ${minPrecio} data-parsley-required step="any" autocomplete="off" style="min-width:80px; font-size:11px;"
                            onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <input type="number" id="cantidad${numeroInputs}" name="cantidad${numeroInputs}" class="form-control form-control-sm" min="1" data-parsley-required autocomplete="off" style="min-width:60px; font-size:11px;"
                            onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <select class="form-control form-control-sm" name="unidad${numeroInputs}" id="unidad${numeroInputs}" data-parsley-required style="font-size:11px; min-width:80px;"
                            onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                            ${htmlSelectUnidades}
                        </select>
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px; text-align:right;">
                        <input type="text" id="subTotalMostrar${numeroInputs}" name="subTotalMostrar${numeroInputs}" placeholder="0.00" readonly autocomplete="off"
                            style="border:none; background:#f1f8e9; border-radius:5px; font-weight:700; color:#2e7d32; font-size:12px; padding:2px 6px; text-align:right; width:100%; min-width:75px;">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px; text-align:right;">
                        <input type="text" id="isvProductoMostrar${numeroInputs}" name="isvProductoMostrar${numeroInputs}" placeholder="0.00" readonly autocomplete="off"
                            style="border:none; background:#fce4ec; border-radius:5px; font-weight:700; color:#b71c1c; font-size:12px; padding:2px 6px; text-align:right; width:100%; min-width:65px;">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px; text-align:right;">
                        <input type="text" id="totalMostrar${numeroInputs}" name="totalMostrar${numeroInputs}" placeholder="0.00" readonly autocomplete="off"
                            style="border:none; background:linear-gradient(135deg,#e65100,#f9a826); border-radius:5px; font-weight:800; color:#fff; font-size:12px; padding:2px 6px; text-align:right; width:100%; min-width:80px;">
                    </td>
                </tr>`;

                arregloIdInputs.splice(numeroInputs, 0, numeroInputs);
                document.getElementById('carritoTbody').insertAdjacentHTML('beforeend', html);
                // Mostrar tabla, ocultar mensaje vacío
                document.getElementById('carritoVacio').classList.add('d-none');
                document.getElementById('carritoTablaWrapper').classList.remove('d-none');
            })
            .catch(err => {
                const mensaje = err.response?.data?.message || 'Error al agregar producto';
                Swal.fire({ icon: 'error', title: 'Error', html: mensaje });
            });
    }

    function prueba() {
        document.getElementById('botonAdd').classList.remove("d-none");
    }

    // ================================================================
    // CÁLCULOS
    // ================================================================
    function calcularTotalesInicioPagina() {
        let arrayInputs = this.arregloIdInputs;
        arrayInputs.forEach(id => {
            let valorInputPrecio = document.getElementById('precio' + id).value;
            let valorInputCantidad = document.getElementById('cantidad' + id).value;
            let valorSelectUnidad = document.getElementById('unidad' + id).value;
            let isvProducto = document.getElementById("isv" + id).value;

            // Si no aplica ISV, forzar a 0
            if (tipoFacturaConfig && !tipoFacturaConfig.aplica_isv) {
                isvProducto = 0;
            }

            if (valorInputPrecio && valorInputCantidad) {
                let descuento = document.getElementById("porDescuento").value;
                let subTotal = 0, isv = 0, total = 0, descuentoCalculado = 0;

                if (descuento > 0) {
                    subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                    descuentoCalculado = subTotal * (descuento / 100);
                    subTotal = subTotal - descuentoCalculado;
                    isv = parseFloat((subTotal * (isvProducto / 100)).toFixed(2));
                    total = subTotal + (subTotal * (isvProducto / 100));
                } else {
                    descuentoCalculado = 0;
                    subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                    isv = parseFloat((subTotal * (isvProducto / 100)).toFixed(2));
                    total = subTotal + subTotal * (isvProducto / 100);
                }

                document.getElementById("acumuladoDescuento" + id).value = descuentoCalculado.toFixed(2);
                document.getElementById('total' + id).value = total.toFixed(2);
                document.getElementById('totalMostrar' + id).value = formatoMoneda(total);
                document.getElementById('subTotal' + id).value = subTotal.toFixed(2);
                document.getElementById('subTotalMostrar' + id).value = formatoMoneda(subTotal);
                document.getElementById('isvProducto' + id).value = isv.toFixed(2);
                document.getElementById('isvProductoMostrar' + id).value = formatoMoneda(isv);
            }
        });
        this.totalesGenerales();
    }

    function calcularTotales(idPrecio, idCantidad, isvProducto, idUnidad, id, idRestaInventario) {
        let valorInputPrecio = Number(idPrecio.value).toFixed(2);
        let valorInputCantidad = idCantidad.value;
        let valorSelectUnidad = idUnidad.value;

        // Si no aplica ISV, forzar a 0
        if (tipoFacturaConfig && !tipoFacturaConfig.aplica_isv) {
            isvProducto = 0;
        }

        if (valorInputPrecio && valorInputCantidad) {
            let descuento = document.getElementById('porDescuento').value;
            let subTotal = 0, isv = 0, total = 0, descuentoCalculado = 0;

            if (descuento > 0) {
                subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                descuentoCalculado = subTotal * (descuento / 100);
                subTotal = subTotal - descuentoCalculado;
                isv = subTotal * (isvProducto / 100);
                total = subTotal + (subTotal * (isvProducto / 100));
            } else {
                descuentoCalculado = 0;
                subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                isv = subTotal * (isvProducto / 100);
                total = subTotal + subTotal * (isvProducto / 100);
            }

            document.getElementById('acumuladoDescuento' + id).value = descuentoCalculado.toFixed(2);
            document.getElementById('total' + id).value = total.toFixed(2);
            document.getElementById('totalMostrar' + id).value = formatoMoneda(total);
            document.getElementById('subTotal' + id).value = subTotal.toFixed(2);
            document.getElementById('subTotalMostrar' + id).value = formatoMoneda(subTotal);
            document.getElementById('isvProducto' + id).value = isv.toFixed(2);
            document.getElementById('isvProductoMostrar' + id).value = formatoMoneda(isv);

            idRestaInventario.value = valorInputCantidad * valorSelectUnidad;
            this.totalesGenerales();
        }

        idPrecio.value = valorInputPrecio;
    }

    function formatoMoneda(valor) {
        return new Intl.NumberFormat('es-HN', {
            style: 'currency', currency: 'HNL', minimumFractionDigits: 2
        }).format(valor);
    }

    function totalesGenerales() {
        if (numeroInputs == 0) return;

        let totalGeneralValor = 0, totalISV = 0, subTotalGeneralGrabadoValor = 0;
        let subTotalGeneralExcentoValor = 0, subTotalGeneral = 0, acumularDescuento = 0;

        for (let i = 0; i < arregloIdInputs.length; i++) {
            let subTotalFila = Number(document.getElementById('subTotal' + arregloIdInputs[i]).value);
            let isvFila = Number(document.getElementById('isvProducto' + arregloIdInputs[i]).value);

            if (isvFila == 0) {
                subTotalGeneralExcentoValor += subTotalFila;
            } else if (subTotalFila > 0) {
                subTotalGeneralGrabadoValor += subTotalFila;
            }

            subTotalGeneral += subTotalFila;
            totalISV += isvFila;
            totalGeneralValor += Number(document.getElementById('total' + arregloIdInputs[i]).value);
            acumularDescuento += Number(document.getElementById('acumuladoDescuento' + arregloIdInputs[i]).value);
        }

        document.getElementById('porDescuentoCalculado').value = acumularDescuento.toFixed(2);
        document.getElementById('descuentoMostrar').value = formatoMoneda(acumularDescuento);
        document.getElementById('subTotalGeneral').value = subTotalGeneral.toFixed(2);
        document.getElementById('subTotalGeneralMostrar').value = formatoMoneda(subTotalGeneral);
        document.getElementById('subTotalGeneralGrabado').value = subTotalGeneralGrabadoValor.toFixed(2);
        document.getElementById('subTotalGeneralGrabadoMostrar').value = formatoMoneda(subTotalGeneralGrabadoValor);
        document.getElementById('subTotalGeneralExcento').value = subTotalGeneralExcentoValor.toFixed(2);
        document.getElementById('subTotalGeneralExcentoMostrar').value = formatoMoneda(subTotalGeneralExcentoValor);
        document.getElementById('isvGeneral').value = totalISV.toFixed(2);
        document.getElementById('isvGeneralMostrar').value = formatoMoneda(totalISV);
        document.getElementById('totalGeneral').value = totalGeneralValor.toFixed(2);
        document.getElementById('totalGeneralMostrar').value = formatoMoneda(totalGeneralValor);
    }

    function eliminarInput(id) {
        document.getElementById(id).remove();
        var myIndex = arregloIdInputs.indexOf(id);
        if (myIndex !== -1) {
            arregloIdInputs.splice(myIndex, 1);
            this.totalesGenerales();
        }
        // Ocultar tabla si no quedan productos
        if (arregloIdInputs.length === 0) {
            document.getElementById('carritoTablaWrapper').classList.add('d-none');
            document.getElementById('carritoVacio').classList.remove('d-none');
        }
    }

    function validacionPrecio(idPrecios, idprecio) {
        var idPrecioSeleccionado = idPrecios.options[idPrecios.selectedIndex].getAttribute("data-id");
        var precioSeleccionado = idPrecios.value;
        var idprecioIngresado = idprecio.id;

        document.getElementById(idprecioIngresado).value = precioSeleccionado;

        // Solo aplicar mínimo si NO es sin restricción
        if (tipoFacturaConfig && !tipoFacturaConfig.multiples_precios) {
            document.getElementById(idprecioIngresado).setAttribute("min", precioSeleccionado);
        }
    }

    // ================================================================
    // FECHAS Y PAGOS
    // ================================================================
    function validarFechaPago() {
        let tipoPago = document.getElementById('tipoPagoVenta').value;
        if (tipoPago == 2) {
            document.getElementById('fecha_vencimiento').readOnly = false;
            sumarDiasCredito();
        } else {
            document.getElementById('fecha_vencimiento').value = "{{ date('Y-m-d') }}";
            document.getElementById('fecha_vencimiento').readOnly = true;
        }
    }

    function sumarDiasCredito() {
        let tipoPago = document.getElementById('tipoPagoVenta').value;
        if (tipoPago == 2) {
            let fechaEmision = document.getElementById("fecha_emision").value;
            let date = new Date(fechaEmision);
            date.setDate(date.getDate() + diasCredito);
            document.getElementById("fecha_vencimiento").value = date.toISOString().split('T')[0];
        }
    }

    function obtenerOrdenesCompra() {
        if (!tipoFacturaConfig || !tipoFacturaConfig.requiere_orden_compra || !urls.orden_compra) return;

        let idCliente = document.getElementById('seleccionarCliente').value;
        $('#ordenCompra').select2({
            ajax: {
                url: urls.orden_compra,
                data: function(params) {
                    return { idCliente: idCliente, search: params.term, type: 'public', page: params.page || 1 };
                }
            }
        });
    }

    // ================================================================
    // GUARDAR VENTA
    // ================================================================
    var _ofertaGuardadaId  = null;
    var _ofertaPedidoId    = null;
    var _ofertaFlujoId     = null;
    var _prefacturaId      = null;
    var _prefacturaFlujoId = null;

    function limpiarFormularioVenta(data) {
        document.getElementById('bloqueImagenes').innerHTML = '';
        document.getElementById('divProductos').innerHTML = '';
        document.getElementById("crear_venta").reset();
        $('#crear_venta').parsley().reset();

        document.getElementById("seleccionarCliente").innerHTML = '<option value="" selected disabled>--Seleccionar un cliente--</option>';
        $('#seleccionarCliente').prop('disabled', false);
        document.getElementById('seleccionarProducto').innerHTML = '<option value="" selected disabled></option>';
        document.getElementById('codigoProductoBuscar').value = '';
        var lblProd = document.getElementById('productoSeleccionadoLabel');
        if (lblProd) { lblProd.classList.add('d-none'); lblProd.textContent = ''; }
        document.getElementById('bodega').innerHTML = '<option value="" selected disabled>--Seleccione un producto--</option>';
        document.getElementById("bodega").disabled = true;

        // Ocultar tabla carrito
        var carritoTabla = document.getElementById('carritoTablaWrapper');
        var carritoVacio = document.getElementById('carritoVacio');
        if (carritoTabla) carritoTabla.style.display = 'none';
        if (carritoVacio) carritoVacio.style.display = '';

        arregloIdInputs = [];
        numeroInputs = 0;
        retencionEstado = false;

        if (data && data.numeroVenta) document.getElementById('numero_venta').value = data.numeroVenta;
        document.getElementById("btn_venta_coorporativa").disabled = false;

        document.getElementById('restriccion').value = tipoFacturaConfig ? tipoFacturaConfig.restriccion : 1;
        document.getElementById('tipo_venta_id').value = tipoFacturaConfig ? tipoFacturaConfig.tipo_venta_id : 2;
        document.getElementById('tipo_factura_id').value = tipoFacturaConfig ? tipoFacturaConfig.id : '';
    }

    function ofertaAccion(tipo) {
        var idOferta  = _ofertaGuardadaId;
        var idPedido  = _ofertaPedidoId;
        var idFlujo   = _ofertaFlujoId;

        if (tipo === 'nueva') {
            $('#modalExitoOferta').modal('hide');
            // Recargar la página para restaurar los datos del pedido vinculado
            window.location.reload();
            return;

        } else if (tipo === 'flujo') {
            $('#modalExitoOferta').one('hidden.bs.modal', function () {
                if (idPedido) {
                    Livewire.emit('abrirFlujoPedido', idPedido, 'ofertas');
                } else if (idFlujo) {
                    Livewire.emit('abrirFlujoCotizacion', idFlujo);
                } else {
                    window.location.href = '/flujo/prefactura';
                }
            });
            $('#modalExitoOferta').modal('hide');
            return;

        } else if (tipo === 'ganadora') {
            if (!idPedido) {
                $('#modalExitoOferta').modal('hide');
                Swal.fire({ icon: 'info', title: 'Sin pedido', text: 'Esta oferta no está vinculada a un pedido.' });
                return;
            }
            document.getElementById('ogLista').innerHTML = '';
            document.getElementById('ogLoading').style.display = '';
            // Esperar a que el primer modal cierre completamente antes de abrir el segundo
            $('#modalExitoOferta').one('hidden.bs.modal', function () {
                $('#modalOfertasGanadoras').modal('show');
                axios.get('/cotizacion/por-pedido/' + idPedido)
                    .then(function(res) {
                        document.getElementById('ogLoading').style.display = 'none';
                        var ofertas = res.data;
                        if (!ofertas.length) {
                            document.getElementById('ogLista').innerHTML = '<p class="text-center text-muted">No hay ofertas para este pedido.</p>';
                            return;
                        }
                        var fmt = new Intl.NumberFormat('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        var html = '';
                        ofertas.forEach(function(o, idx) {
                            var esActual   = (o.id == idOferta) ? ' <span style="background:#e3f2fd;color:#1565c0;border-radius:12px;padding:1px 8px;font-size:10px;font-weight:700;">Esta oferta</span>' : '';
                            var esGanadora = o.es_ganadora ? ' <span style="background:#fff8e1;color:#f57f17;border-radius:12px;padding:1px 8px;font-size:10px;font-weight:700;"><i class="fa fa-trophy"></i> Ganadora</span>' : '';
                            var cardBorder = o.es_ganadora ? 'border:2px solid #f9a826;background:#fffde7;' : 'border:1px solid #e0e0e0;background:#fff;';
                            var numProds   = (o.productos && o.productos.length) ? o.productos.length : 0;

                            html += '<div class="og-card" style="' + cardBorder + '">';
                            html += '<div class="og-card-header">';
                            html += '<div style="flex:1;min-width:0;">';
                            html += '<div style="font-weight:800;font-size:13px;color:#2d3748;display:flex;align-items:center;flex-wrap:wrap;gap:4px;">';
                            html += 'Oferta #' + o.id + esActual + esGanadora;
                            html += '</div>';
                            html += '<div style="font-size:11px;color:#90a4ae;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + (o.nombre_cliente || '') + '</div>';
                            html += '</div>';
                            html += '<div style="display:flex;flex-direction:column;align-items:flex-end;gap:5px;flex-shrink:0;">';
                            html += '<span style="font-weight:800;color:#e65100;font-size:14px;">L ' + fmt.format(o.total) + '</span>';
                            html += '<button onclick="confirmarGanadora(' + o.id + ')" style="background:linear-gradient(135deg,#e65100,#f9a826);color:#fff;border:none;border-radius:8px;padding:4px 14px;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">';
                            html += '<i class="mr-1 fa fa-trophy"></i>Seleccionar</button>';
                            html += '</div>';
                            html += '</div>';

                            if (numProds > 0) {
                                html += '<div style="padding:0 14px 8px;">';
                                html += '<button type="button" class="og-toggle-btn" id="ogToggle_' + o.id + '" onclick="ogToggle(' + o.id + ')">';
                                html += '<span class="og-toggle-icon" id="ogIcon_' + o.id + '">+</span>';
                                html += 'Productos (' + numProds + ')';
                                html += '</button>';
                                html += '</div>';
                                html += '<div class="og-products" id="ogProds_' + o.id + '">';
                                o.productos.forEach(function(p) {
                                    html += '<div class="og-prod-row">';
                                    html += '<span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#2d3748;" title="' + p.nombre + '">' + p.nombre + '</span>';
                                    html += '<span style="margin-left:12px;white-space:nowrap;color:#546e7a;font-size:11px;font-weight:600;">x' + p.cantidad + ' &nbsp; L ' + fmt.format(p.total) + '</span>';
                                    html += '</div>';
                                });
                                html += '</div>';
                            }
                            html += '</div>';
                        });
                        document.getElementById('ogLista').innerHTML = html;
                    })
                    .catch(function() {
                        document.getElementById('ogLoading').style.display = 'none';
                        document.getElementById('ogLista').innerHTML = '<p class="text-center text-danger">Error al cargar ofertas.</p>';
                    });
            });
            $('#modalExitoOferta').modal('hide');

        } else if (tipo === 'prefacturar') {
            var btn = document.getElementById('btnPrefacturarOferta');
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin d-block" style="font-size:20px;margin-bottom:4px;"></i>Procesando...'; }

            axios.post('/cotizacion/prefacturar-desde-oferta',
                { cotizacion_id: idOferta, flujo_id: idFlujo || null },
                { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } }
            ).then(function(res) {
                var d = res.data;
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-file-text-o d-block" style="font-size:20px;margin-bottom:4px;"></i>Oferta ganadora'; }
                _prefacturaId    = d.idPrefactura;
                _prefacturaFlujoId = d.flujoId || idFlujo;
                document.getElementById('msgPrefactura').textContent = 'Prefactura #' + d.idPrefactura + ' generada. Válida por ' + (d.diasValidez || 7) + ' día(s).';
                $('#modalExitoOferta').one('hidden.bs.modal', function() {
                    $('#modalPrefacturaExito').modal('show');
                    // Asegurar que el backdrop del modal de prefactura quede encima
                    setTimeout(function() {
                        $('.modal-backdrop').last().css('z-index', '2070');
                    }, 50);
                });
                $('#modalExitoOferta').modal('hide');
            }).catch(function(err) {
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-file-text-o d-block" style="font-size:20px;margin-bottom:4px;"></i>Oferta ganadora'; }
                var d = err.response ? err.response.data : {};
                if (d.stock_errors && d.stock_errors.length) {
                    var rows = d.stock_errors.map(function(e) {
                        return '<tr><td style="padding:4px 8px;font-weight:600;">' + e.producto + '</td>'
                             + '<td style="padding:4px 8px;text-align:center;color:#e65100;font-weight:700;">' + e.solicitado + '</td>'
                             + '<td style="padding:4px 8px;text-align:center;color:#b71c1c;font-weight:700;">' + e.disponible + '</td></tr>';
                    }).join('');
                    Swal.fire({
                        icon: 'error',
                        title: 'Inventario insuficiente',
                        html: '<p style="font-size:13px;margin-bottom:10px;">Los siguientes productos no tienen stock suficiente:</p>'
                            + '<table style="width:100%;font-size:12px;border-collapse:collapse;">'
                            + '<thead><tr style="background:#fce4ec;">'
                            + '<th style="padding:4px 8px;text-align:left;">Producto</th>'
                            + '<th style="padding:4px 8px;">Solicitado</th>'
                            + '<th style="padding:4px 8px;">Disponible</th>'
                            + '</tr></thead><tbody>' + rows + '</tbody></table>',
                        confirmButtonColor: '#e65100',
                    });
                } else {
                    Swal.fire({ icon: 'error', title: d.title || 'Error', text: d.text || 'No se pudo prefacturar la oferta.' });
                }
            });

        } else if (tipo === 'imprimir') {
            var urlImprimir = urls.imprimir;
            if (urlImprimir && idOferta) {
                window.open(urlImprimir.replace('{id}', idOferta), '_blank');
            }
            // Modal permanece abierto intencionalmente
        }
    }

    function prefacturaAccion(tipo) {
        if (tipo === 'imprimir') {
            if (_prefacturaId) {
                window.open('/prefactura/imprimir/' + _prefacturaId, '_blank');
            }
        } else if (tipo === 'flujo') {
            var flujoId = _prefacturaFlujoId;
            $('#modalPrefacturaExito').one('hidden.bs.modal', function () {
                if (flujoId) {
                    axios.get('/flujo/' + flujoId + '/pedido-id').then(function(r) {
                        if (r.data.pedido_id) {
                            Livewire.emit('abrirFlujoPedido', r.data.pedido_id, 'prefactura');
                        } else {
                            Livewire.emit('abrirFlujoCotizacion', flujoId);
                        }
                    }).catch(function() {
                        window.location.href = '/flujo/prefactura';
                    });
                } else {
                    window.location.href = '/flujo/prefactura';
                }
            });
            $('#modalPrefacturaExito').modal('hide');
        } else if (tipo === 'facturar') {
            var prefId = _prefacturaId;
            if (!prefId) {
                Swal.fire({ icon: 'warning', title: 'Sin prefactura', text: 'No se encontró la prefactura.' });
                return;
            }
            $('#modalPrefacturaExito').one('hidden.bs.modal', function() {
                axios.post('/prefactura/' + prefId + '/facturar', {}, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                }).then(function(res) {
                    window.location.href = res.data.url;
                }).catch(function(err) {
                    Swal.fire({ icon: 'error', title: 'Error', text: (err.response && err.response.data && err.response.data.error) ? err.response.data.error : 'Error al procesar.' });
                });
            });
            $('#modalPrefacturaExito').modal('hide');
        }
    }

    function confirmarGanadora(cotizacionId) {
        axios.post('/cotizacion/marcar-ganadora', { cotizacion_id: cotizacionId }, { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } })
            .then(function() {
                $('#modalOfertasGanadoras').modal('hide');
                Swal.fire({ icon: 'success', title: '¡Ganadora seleccionada!', text: 'La oferta #' + cotizacionId + ' fue marcada como ganadora.' });
            })
            .catch(function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo marcar la oferta como ganadora.' });
            });
    }

    // Accordion: abrir un panel cierra los demás
    var _ogOpenId = null;
    function ogToggle(ofertaId) {
        var prods  = document.getElementById('ogProds_'  + ofertaId);
        var toggle = document.getElementById('ogToggle_' + ofertaId);
        if (!prods || !toggle) return;

        var isOpen = prods.classList.contains('open');

        // Cerrar el que estaba abierto (si era otro)
        if (_ogOpenId && _ogOpenId !== ofertaId) {
            var prevProds  = document.getElementById('ogProds_'  + _ogOpenId);
            var prevToggle = document.getElementById('ogToggle_' + _ogOpenId);
            if (prevProds)  prevProds.classList.remove('open');
            if (prevToggle) prevToggle.classList.remove('open');
            _ogOpenId = null;
        }

        if (isOpen) {
            prods.classList.remove('open');
            toggle.classList.remove('open');
            _ogOpenId = null;
        } else {
            prods.classList.add('open');
            toggle.classList.add('open');
            _ogOpenId = ofertaId;
        }
    }

    // Botón Volver: cierra ganadora y reabre el modal de éxito
    document.addEventListener('DOMContentLoaded', function () {
        // Mover el modal al <body> para evitar el offset del page-wrapper de IBOX
        var ogModal = document.getElementById('modalOfertasGanadoras');
        if (ogModal && ogModal.parentElement !== document.body) {
            document.body.appendChild(ogModal);
        }

        var btnVolver = document.getElementById('ogBtnVolver');
        if (btnVolver) {
            btnVolver.addEventListener('click', function () {
                $('#modalOfertasGanadoras').one('hidden.bs.modal', function () {
                    $('#modalExitoOferta').modal('show');
                });
                $('#modalOfertasGanadoras').modal('hide');
            });
        }
    });

    $(document).on('submit', '#crear_venta', function(event) {
        event.preventDefault();
        guardarVenta();
    });

    function guardarVenta() {
        document.getElementById("btn_venta_coorporativa").disabled = true;

        var data = new FormData($('#crear_venta').get(0));
        // Forzar inclusión del cliente aunque el select esté deshabilitado (pedido vinculado)
        var clienteVal = $('#seleccionarCliente').val();
        if (clienteVal) data.set('seleccionarCliente', clienteVal);

        let longitudArreglo = arregloIdInputs.length;
        for (var i = 0; i < longitudArreglo; i++) {
            let name = "unidad" + arregloIdInputs[i];
            let nameForm = "idUnidadVenta" + arregloIdInputs[i];
            let e = document.getElementById(name);
            let idUnidadVenta = e.options[e.selectedIndex].getAttribute("data-id");
            data.append(nameForm, idUnidadVenta);

            let name2 = "precios" + arregloIdInputs[i];
            let nameForm2 = "idPrecioSeleccionado" + arregloIdInputs[i];
            let a = document.getElementById(name2);
            let idPrecioSeleccionado = a.options[a.selectedIndex].getAttribute("data-id");
            data.append(nameForm2, idPrecioSeleccionado);
        }

        data.append("numeroInputs", numeroInputs);
        let text = arregloIdInputs.toString();
        data.append("arregloIdInputs", text);

        const formDataObj = {};
        data.forEach((value, key) => (formDataObj[key] = value));

        var urlGuardar = urls.guardar;

        axios.post(urlGuardar, formDataObj, { headers: { "content-type": "application/json" } })
            .then(response => {
                let data = response.data;

                // Para tipos con código de autorización
                if (tipoFacturaConfig && tipoFacturaConfig.requiere_codigo_autorizacion) {
                    idAutorizacion = document.getElementById('codigo_autorizacion').value;
                    idFactura = data.idFactura;
                    var urlImprimir = urls.imprimir || '/factura/cooporativo/{id}';
                    document.getElementById('btn_imprimir').href = urlImprimir.replace('{id}', idFactura);
                }

                if (data.idFactura == 0) {
                    Swal.fire({ icon: data.icon, title: data.title, html: data.text });
                    document.getElementById("btn_venta_coorporativa").disabled = false;
                    return;
                }

                // Para cotizaciones, mostrar modal de opciones post-guardado
                if (codigoActual === 'cotizacion_clientes_a') {
                    _ofertaGuardadaId = data.idFactura;
                    _ofertaPedidoId   = data.pedidoId  || null;
                    _ofertaFlujoId    = data.flujoId   || null;
                    var msgEl = document.getElementById('msgNumOferta');
                    if (msgEl) msgEl.textContent = 'Oferta #' + data.idFactura + ' registrada exitosamente.';
                    limpiarFormularioVenta(data);
                    $('#modalExitoOferta').modal('show');
                    return;
                }

                // ── Obtener flujo_id (desde URL param o campo oculto) ──────────
                var urlParams   = new URLSearchParams(window.location.search);
                var flujoIdUrl  = urlParams.get('flujoId');
                var flujoIdEl   = document.getElementById('flujo_vinculado_id');
                var flujoIdVal  = flujoIdUrl || (flujoIdEl ? flujoIdEl.value : '');

                // ── Si hay flujo vinculado: actualizar estado del flujo ────────
                if (flujoIdVal && data.idFactura) {
                    axios.post('/flujo/factura/confirmar', {
                        flujo_id:       flujoIdVal,
                        factura_id:     data.idFactura,
                        tipo_factura_id: (tipoFacturaConfig ? tipoFacturaConfig.id : '')
                    }).catch(function(err) {
                        console.warn('No se pudo registrar el flujo de factura:', err);
                    });
                }

                // ── Mostrar panel post-factura en lugar de Swal ───────────────
                var panel = document.getElementById('panel_post_factura');
                if (panel) {
                    // Actualizar número de factura
                    var numEl = document.getElementById('pfactura_numero');
                    if (numEl) numEl.textContent = 'Factura #' + data.idFactura + ' registrada exitosamente.';

                    // Botón imprimir
                    var btnImprimir = document.getElementById('btn_post_imprimir');
                    if (btnImprimir && urls.imprimir) {
                        btnImprimir.href = urls.imprimir.replace('{id}', data.idFactura);
                    }

                    // Botón cobro
                    var btnCobro = document.getElementById('btn_post_cobro');
                    if (btnCobro) {
                        btnCobro.href = '/venta/cobro/' + data.idFactura;
                    }

                    // Ocultar formulario y mostrar panel
                    var form = document.getElementById('crear_venta');
                    var btnVenta = document.getElementById('btn_venta_coorporativa');
                    if (form) form.style.display = 'none';
                    if (btnVenta) btnVenta.style.display = 'none';
                    panel.style.display = 'block';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    Swal.fire({ icon: data.icon, title: data.title, html: data.text });
                    limpiarFormularioVenta(data);
                }

                // Desactivar código si aplica
                if (tipoFacturaConfig && tipoFacturaConfig.requiere_codigo_autorizacion) {
                    setTimeout(function() { desactivarCodigo(); }, 30000);
                }
            })
            .catch(err => {
                document.getElementById("btn_venta_coorporativa").disabled = false;
                let data = err.response ? err.response.data : {};
                console.log(err);
                Swal.fire({ icon: data.icon || 'error', title: data.title || 'Error', text: data.text || 'Error al guardar' });
            });
    }

    function obtenerCategoriasClientes() {
        $('#categoria_cliente_venta_id').select2({
            placeholder: 'Seleccione una categoría',
            allowClear: true,
            ajax: {
                url: '/clientes/categorias-escala',
                dataType: 'json',
                delay: 250,
                data: function(params) { return { q: params.term || '', page: params.page || 1 }; },
                processResults: function(data) {
                    return { results: data.categorias.map(function(item) { return { id: item.id, text: item.nombre_categoria }; }) };
                }
            }
        });
    }
    </script>

    <script>
    <?php
        date_default_timezone_set('America/Tegucigalpa');
        $act_fecha = date('Y-m-d');
        $year = date('Y');
    ?>
    function mostrarHora() {
        var fecha = new Date();
        var hora = fecha.getHours();
        var minutos = fecha.getMinutes();
        var segundos = fecha.getSeconds();
        minutos = minutos < 10 ? "0" + minutos : minutos;
        segundos = segundos < 10 ? "0" + segundos : segundos;
        var el = document.getElementById("reloj");
        if (el) el.innerHTML = hora + ":" + minutos + ":" + segundos;
    }
    setInterval(mostrarHora, 1000);
    </script>
    @endpush

    @if($clientePedido)
    @push('scripts')
    {{-- Re-despacha el evento pedido-seleccionado al cargar si el pedido ya estaba vinculado (desde URL pedidoId) --}}
    <script>
        document.addEventListener('livewire:load', function () {
            window.dispatchEvent(new CustomEvent('pedido-seleccionado', {
                detail: {
                    clienteId:     {!! (int)$clientePedido['id'] !!},
                    clienteNombre: {!! json_encode($clientePedido['nombre']) !!},
                    vendedorId:    {!! (int)($vendedorDefault['id'] ?? 0) !!},
                    vendedorNombre:{!! json_encode($vendedorDefault['name'] ?? '') !!},
                }
            }));
        });
    </script>
    @endpush
    @endif

    @if(count($productosParaCarrito) > 0)
    @push('scripts')
    {{-- Auto-agregar productos al carrito: oferta duplicada o prefactura vinculada --}}
    <script>
    (function () {
        var _productosAutoAgregados = false;
        var _modoPrefactura = {!! $fromPrefactura ? 'true' : 'false' !!};

        function cargarProductosIniciales() {
            if (_productosAutoAgregados) return;
            _productosAutoAgregados = true;

            var productos = @json($productosParaCarrito);
            if (!productos || productos.length === 0) return;

            var chain = Promise.resolve();
            productos.forEach(function (prod) {
                chain = chain.then(function () {
                    return _modoPrefactura ? agregarProductoDesdePrefactura(prod) : agregarProductoDesdeOferta(prod);
                });
            });
            chain.then(function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Productos cargados',
                    text: productos.length + ' producto(s) cargado(s) desde ' + (_modoPrefactura ? 'la prefactura vinculada' : 'la oferta duplicada') + '.',
                    timer: 2500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            });
        }

        function agregarProductoDesdePrefactura(prod) {
            return new Promise(function (resolve) {
                numeroInputs += 1;
                var idx = numeroInputs;

                var precioUsar   = parseFloat(prod.precio_unidad || 0);
                var cantidadUsar = parseFloat(prod.cantidad || 0);
                var subTotalUsar = parseFloat(prod.sub_total || 0);
                var isvUsar      = parseFloat(prod.isv || 0);
                var totalUsar    = parseFloat(prod.total || 0);
                var isvPct       = parseFloat(prod.isv_producto || 0);
                var bodegaTexto  = prod.nombre_bodega || '';
                var idBodega     = prod.Bodega_id || '';
                var idSeccion    = prod.seccion_id || '';
                var idUnidadVenta = prod.unidad_medida_venta_id || '';

                var html = `
                <tr id='${idx}'>
                    <td style="vertical-align:middle; text-align:center; padding:4px 6px;">
                        <input id="idProducto${idx}" name="idProducto${idx}" type="hidden" value="${prod.producto_id || ''}">
                        <input id="precios_producto_carga_id${idx}" name="precios_producto_carga_id${idx}" type="hidden" value="${prod.precios_producto_carga_id || ''}">
                        <input id="isv${idx}" name="isv${idx}" type="hidden" value="${isvPct}">
                        <input id="idBodega${idx}" name="idBodega${idx}" type="hidden" value="${idBodega}">
                        <input id="idSeccion${idx}" name="idSeccion${idx}" type="hidden" value="${idSeccion}">
                        <input id="restaInventario${idx}" name="restaInventario${idx}" type="hidden" value="${cantidadUsar}">
                        <input id="subTotal${idx}" name="subTotal${idx}" type="hidden" value="${subTotalUsar.toFixed(2)}" required>
                        <input id="isvProducto${idx}" name="isvProducto${idx}" type="hidden" value="${isvUsar.toFixed(2)}" required>
                        <input id="acumuladoDescuento${idx}" name="acumuladoDescuento${idx}" type="hidden" value="0.00">
                        <input id="total${idx}" name="total${idx}" type="hidden" value="${totalUsar.toFixed(2)}" required>
                        <input id="bodega${idx}" name="bodega${idx}" type="hidden" value="${bodegaTexto}">
                        <button class="btn btn-danger btn-xs" type="button" onclick="eliminarInput(${idx})" title="Eliminar" style="padding:2px 6px; font-size:11px; border-radius:5px;">
                            <i class="fa fa-times"></i>
                        </button>
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <input type="text" id="nombre${idx}" name="nombre${idx}" value='${prod.nombre_producto || ''}' readonly data-parsley-required
                            style="border:none; background:transparent; font-size:12px; font-weight:700; color:#1b5e20; width:100%; min-width:130px;">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px; white-space:nowrap;">
                        <span style="background:#e3f2fd; color:#1565c0; border-radius:6px; padding:2px 8px; font-size:11px; font-weight:700;">
                            <i class="fa fa-archive" style="font-size:10px;"></i> ${bodegaTexto}
                        </span>
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <select class="form-control form-control-sm" name="precios${idx}" id="precios${idx}" data-parsley-required style="font-size:11px; min-width:100px;"
                            onchange="validacionPrecio(precios${idx}, precio${idx})">
                            <option value="${precioUsar.toFixed(2)}" data-id="p1" selected>${precioUsar.toFixed(2)} - Fijo</option>
                        </select>
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <input type="number" id="precio${idx}" name="precio${idx}" value="${precioUsar.toFixed(2)}" class="form-control form-control-sm"
                            data-parsley-required step="any" autocomplete="off" style="min-width:80px; font-size:11px;"
                            onchange="calcularTotales(precio${idx},cantidad${idx},${isvPct},unidad${idx},${idx},restaInventario${idx})">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <input type="number" id="cantidad${idx}" name="cantidad${idx}" value="${cantidadUsar}" class="form-control form-control-sm" min="1" data-parsley-required autocomplete="off" style="min-width:60px; font-size:11px;"
                            onchange="calcularTotales(precio${idx},cantidad${idx},${isvPct},unidad${idx},${idx},restaInventario${idx})">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <select class="form-control form-control-sm" name="unidad${idx}" id="unidad${idx}" data-parsley-required style="font-size:11px; min-width:80px;"
                            onchange="calcularTotales(precio${idx},cantidad${idx},${isvPct},unidad${idx},${idx},restaInventario${idx})">
                            <option value="1" data-id="${idUnidadVenta}" selected>U.</option>
                        </select>
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px; text-align:right;">
                        <input type="text" id="subTotalMostrar${idx}" name="subTotalMostrar${idx}" value="${formatoMoneda(subTotalUsar)}" readonly autocomplete="off"
                            style="border:none; background:#f1f8e9; border-radius:5px; font-weight:700; color:#2e7d32; font-size:12px; padding:2px 6px; text-align:right; width:100%; min-width:75px;">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px; text-align:right;">
                        <input type="text" id="isvProductoMostrar${idx}" name="isvProductoMostrar${idx}" value="${formatoMoneda(isvUsar)}" readonly autocomplete="off"
                            style="border:none; background:#fce4ec; border-radius:5px; font-weight:700; color:#b71c1c; font-size:12px; padding:2px 6px; text-align:right; width:100%; min-width:65px;">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px; text-align:right;">
                        <input type="text" id="totalMostrar${idx}" name="totalMostrar${idx}" value="${formatoMoneda(totalUsar)}" readonly autocomplete="off"
                            style="border:none; background:linear-gradient(135deg,#e65100,#f9a826); border-radius:5px; font-weight:800; color:#fff; font-size:12px; padding:2px 6px; text-align:right; width:100%; min-width:80px;">
                    </td>
                </tr>`;

                arregloIdInputs.splice(idx, 0, idx);
                document.getElementById('carritoTbody').insertAdjacentHTML('beforeend', html);
                document.getElementById('carritoVacio').classList.add('d-none');
                document.getElementById('carritoTablaWrapper').classList.remove('d-none');
                totalesGenerales();
                resolve();
            });
        }

        function agregarProductoDesdeOferta(prod) {
            return new Promise(function (resolve) {
                if (!prod.producto_id) { resolve(); return; }

                var categoriaId = $('#categoria_cliente_venta_id').val()
                    || $('#categoria_cliente_venta_id').data('categoria-cliente-id')
                    || prod.precios_producto_carga_id
                    || '';

                axios.post(urls.datos_producto, {
                    idProducto: prod.producto_id,
                    categoria_cliente_venta_id: categoriaId
                }).then(function (response) {
                    var producto = response.data.producto;
                    var arrayUnidades = response.data.unidades;
                    numeroInputs += 1;
                    var idx = numeroInputs;

                    // Construir select de unidades – pre-seleccionar la del duplicado
                    var htmlSelectUnidades = '';
                    arrayUnidades.forEach(function (u) {
                        var sel = (u.idUnidadVenta == prod.unidad_medida_venta_id) ? 'selected' : (u.valor_defecto == 1 && htmlSelectUnidades === '' ? 'selected' : '');
                        htmlSelectUnidades += '<option ' + sel + ' value="' + u.id + '" data-id="' + u.idUnidadVenta + '">' + u.nombre + '</option>';
                    });

                    // Precios
                    var htmlprecios = '';
                    if (tipoFacturaConfig && tipoFacturaConfig.multiples_precios) {
                        htmlprecios = '<option value="' + producto.precio1 + '" data-id="p1" selected>' + producto.precio1 + ' - A</option>';
                        if (producto.precio2) htmlprecios += '<option value="' + producto.precio2 + '" data-id="p2">' + producto.precio2 + ' - B</option>';
                        if (producto.precio3) htmlprecios += '<option value="' + producto.precio3 + '" data-id="p3">' + producto.precio3 + ' - C</option>';
                        if (producto.precio4) htmlprecios += '<option value="' + producto.precio4 + '" data-id="p4">' + producto.precio4 + ' - D</option>';
                    } else {
                        htmlprecios = '<option value="' + producto.precio1 + '" data-id="p1" selected>' + producto.precio1 + ' - A</option>';
                    }

                    var minPrecio = (tipoFacturaConfig && tipoFacturaConfig.multiples_precios) ? '' : 'min="' + producto.precio1 + '"';
                    var precioUsar = prod.precio_unidad || producto.precio1;
                    var cantidadUsar = prod.cantidad || 1;
                    var bodegaTexto = prod.nombre_bodega || '';
                    var idBodega = prod['Bodega_id'] || '';
                    var idSeccion = prod.seccion_id || '';

                    var html = `
                    <tr id='${idx}'>
                        <td style="vertical-align:middle; text-align:center; padding:4px 6px;">
                            <input id="idProducto${idx}" name="idProducto${idx}" type="hidden" value="${producto.id}">
                            <input id="precios_producto_carga_id${idx}" name="precios_producto_carga_id${idx}" type="hidden" value="${producto.precios_producto_carga_id || ''}">
                            <input id="isv${idx}" name="isv${idx}" type="hidden" value="${producto.isv}">
                            <input id="idBodega${idx}" name="idBodega${idx}" type="hidden" value="${idBodega}">
                            <input id="idSeccion${idx}" name="idSeccion${idx}" type="hidden" value="${idSeccion}">
                            <input id="restaInventario${idx}" name="restaInventario${idx}" type="hidden" value="">
                            <input id="subTotal${idx}" name="subTotal${idx}" type="hidden" value="" required>
                            <input id="isvProducto${idx}" name="isvProducto${idx}" type="hidden" value="" required>
                            <input id="acumuladoDescuento${idx}" name="acumuladoDescuento${idx}" type="hidden">
                            <input id="total${idx}" name="total${idx}" type="hidden" value="" required>
                            <input id="bodega${idx}" name="bodega${idx}" type="hidden" value="${bodegaTexto}">
                            <button class="btn btn-danger btn-xs" type="button" onclick="eliminarInput(${idx})" title="Eliminar" style="padding:2px 6px; font-size:11px; border-radius:5px;">
                                <i class="fa fa-times"></i>
                            </button>
                        </td>
                        <td style="vertical-align:middle; padding:4px 6px;">
                            <input type="text" id="nombre${idx}" name="nombre${idx}" value='${producto.nombre}' readonly data-parsley-required
                                style="border:none; background:transparent; font-size:12px; font-weight:700; color:#1b5e20; width:100%; min-width:130px;">
                        </td>
                        <td style="vertical-align:middle; padding:4px 6px; white-space:nowrap;">
                            <span style="background:#e3f2fd; color:#1565c0; border-radius:6px; padding:2px 8px; font-size:11px; font-weight:700;">
                                <i class="fa fa-archive" style="font-size:10px;"></i> ${bodegaTexto}
                            </span>
                        </td>
                        <td style="vertical-align:middle; padding:4px 6px;">
                            <select class="form-control form-control-sm" name="precios${idx}" id="precios${idx}" data-parsley-required style="font-size:11px; min-width:100px;"
                                onchange="validacionPrecio(precios${idx}, precio${idx})">
                                ${htmlprecios}
                            </select>
                        </td>
                        <td style="vertical-align:middle; padding:4px 6px;">
                            <input type="number" id="precio${idx}" name="precio${idx}" value="${precioUsar}" class="form-control form-control-sm"
                                ${minPrecio} data-parsley-required step="any" autocomplete="off" style="min-width:80px; font-size:11px;"
                                onchange="calcularTotales(precio${idx},cantidad${idx},${producto.isv},unidad${idx},${idx},restaInventario${idx})">
                        </td>
                        <td style="vertical-align:middle; padding:4px 6px;">
                            <input type="number" id="cantidad${idx}" name="cantidad${idx}" value="${cantidadUsar}" class="form-control form-control-sm" min="1" data-parsley-required autocomplete="off" style="min-width:60px; font-size:11px;"
                                onchange="calcularTotales(precio${idx},cantidad${idx},${producto.isv},unidad${idx},${idx},restaInventario${idx})">
                        </td>
                        <td style="vertical-align:middle; padding:4px 6px;">
                            <select class="form-control form-control-sm" name="unidad${idx}" id="unidad${idx}" data-parsley-required style="font-size:11px; min-width:80px;"
                                onchange="calcularTotales(precio${idx},cantidad${idx},${producto.isv},unidad${idx},${idx},restaInventario${idx})">
                                ${htmlSelectUnidades}
                            </select>
                        </td>
                        <td style="vertical-align:middle; padding:4px 6px; text-align:right;">
                            <input type="text" id="subTotalMostrar${idx}" name="subTotalMostrar${idx}" placeholder="0.00" readonly autocomplete="off"
                                style="border:none; background:#f1f8e9; border-radius:5px; font-weight:700; color:#2e7d32; font-size:12px; padding:2px 6px; text-align:right; width:100%; min-width:75px;">
                        </td>
                        <td style="vertical-align:middle; padding:4px 6px; text-align:right;">
                            <input type="text" id="isvProductoMostrar${idx}" name="isvProductoMostrar${idx}" placeholder="0.00" readonly autocomplete="off"
                                style="border:none; background:#fce4ec; border-radius:5px; font-weight:700; color:#b71c1c; font-size:12px; padding:2px 6px; text-align:right; width:100%; min-width:65px;">
                        </td>
                        <td style="vertical-align:middle; padding:4px 6px; text-align:right;">
                            <input type="text" id="totalMostrar${idx}" name="totalMostrar${idx}" placeholder="0.00" readonly autocomplete="off"
                                style="border:none; background:linear-gradient(135deg,#e65100,#f9a826); border-radius:5px; font-weight:800; color:#fff; font-size:12px; padding:2px 6px; text-align:right; width:100%; min-width:80px;">
                        </td>
                    </tr>`;

                    arregloIdInputs.splice(idx, 0, idx);
                    document.getElementById('carritoTbody').insertAdjacentHTML('beforeend', html);
                    document.getElementById('carritoVacio').classList.add('d-none');
                    document.getElementById('carritoTablaWrapper').classList.remove('d-none');

                    // Calcular totales para esta fila
                    calcularTotales(
                        document.getElementById('precio' + idx),
                        document.getElementById('cantidad' + idx),
                        producto.isv,
                        document.getElementById('unidad' + idx),
                        idx,
                        document.getElementById('restaInventario' + idx)
                    );
                    resolve();
                }).catch(function () { resolve(); });
            });
        }

        // Disparar auto-carga cuando el cliente esté completamente cargado
        window.addEventListener('cliente-datos-cargados', function onClienteListo() {
            window.removeEventListener('cliente-datos-cargados', onClienteListo);
            cargarProductosIniciales();
        });
    })();
    </script>
    @endpush
    @endif

    <div class="mt-3">
        <div class="float-right">
            <?php echo "$act_fecha"; ?> <strong id="reloj"></strong>
        </div>
        <div>
            <strong>Copyright</strong> Distribuciones Valencia &copy; <?php echo "$year"; ?>
        </div>
        <div style="clear: both;"></div>
    </div>
</div>
