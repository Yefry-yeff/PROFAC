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
        /* SweetAlert sobre modales de autorización */
        .swal-sobre-modal { z-index: 99999 !important; }
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

        /* ── Selector compacto de líneas pendientes Expo ─────────────── */
        .expo-pendientes-card { padding: 0; overflow: hidden; border-color: #ffd59a; }
        .expo-pendientes-encabezado {
            padding: 15px 20px; display: flex; flex-wrap: wrap;
            align-items: center; justify-content: space-between; gap: 12px;
            background: linear-gradient(135deg,#fff8ed,#fffdf8); border-bottom: 1px solid #ffe2b8;
        }
        .expo-pendientes-titulo { min-width: 220px; }
        .expo-pendientes-titulo .of-card-title { color: #7c4a12; margin-bottom: 3px !important; }
        .expo-pendientes-titulo small { color: #8a8178; font-size: 11px; }
        .expo-pendientes-contadores { display: flex; flex-wrap: wrap; gap: 6px; }
        .expo-contador {
            padding: 3px 9px; border-radius: 12px; background: #fff;
            border: 1px solid #eadfce; color: #6b6258; font-size: 10px; font-weight: 800;
        }
        .expo-contador.seleccionado { background: #e8f5e9; border-color: #a5d6a7; color: #1b5e20; }
        .expo-pendientes-herramientas {
            padding: 12px 20px; display: grid;
            grid-template-columns: minmax(240px,2fr) minmax(170px,1fr) minmax(160px,1fr);
            gap: 9px; border-bottom: 1px solid #eef0f3; background: #fff;
        }
        .expo-buscador { position: relative; }
        .expo-buscador i { position: absolute; left: 11px; top: 10px; color: #9aa3ab; }
        .expo-buscador input { padding-left: 34px; }
        .expo-pendientes-acciones {
            padding: 10px 20px; display: flex; flex-wrap: wrap;
            justify-content: space-between; align-items: center; gap: 8px;
            background: #fafbfc; border-bottom: 1px solid #eef0f3;
        }
        .expo-pendientes-lista {
            max-height: 390px; overflow-y: auto; padding: 10px 14px;
            display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 7px;
            background: #f7f8fa;
        }
        .expo-linea-item {
            min-width: 0; margin: 0; padding: 9px 11px; display: flex;
            align-items: flex-start; gap: 9px; border: 1px solid #e2e6ea;
            border-radius: 7px; background: #fff; cursor: pointer;
        }
        .expo-linea-item:hover { border-color: #f0ad4e; box-shadow: 0 2px 8px rgba(70,55,35,.08); }
        .expo-linea-item.seleccionada { border-color: #43a047; background: #f1f8f2; }
        .expo-linea-item.en-carrito { border-color: #90caf9; background: #f1f7fc; cursor: default; }
        .expo-linea-item input { margin-top: 3px; flex-shrink: 0; accent-color: #2e7d32; }
        .expo-linea-info { min-width: 0; flex: 1; }
        .expo-linea-nombre {
            display: block; color: #37474f; font-size: 11px; font-weight: 700;
            line-height: 1.35; overflow-wrap: anywhere;
        }
        .expo-linea-meta { margin-top: 5px; display: flex; flex-wrap: wrap; align-items: center; gap: 5px; }
        .expo-linea-chip { padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 800; }
        .expo-linea-chip.marca { color: #795548; background: #fff3e0; }
        .expo-linea-chip.codigo { color: #455a64; background: #eceff1; }
        .expo-linea-pendiente { margin-left: auto; color: #e65100; font-size: 10px; font-weight: 800; }
        .expo-pendientes-vacio { grid-column: 1/-1; padding: 34px 18px; text-align: center; color: #78909c; }
        .expo-pendientes-paginacion {
            padding: 10px 20px; display: flex; justify-content: space-between;
            align-items: center; gap: 10px; border-top: 1px solid #eef0f3; background: #fff;
        }
        @media (max-width: 767.5px) {
            .expo-pendientes-herramientas { grid-template-columns: 1fr; }
            .expo-pendientes-lista { grid-template-columns: 1fr; max-height: 440px; }
            .expo-pendientes-acciones > div {
                display: grid !important; grid-template-columns: repeat(2,minmax(0,1fr)); width: 100%;
            }
            .expo-pendientes-acciones .btn { min-width: 0; width: 100%; white-space: normal; }
            .expo-pendientes-acciones .btn:last-child { grid-column: 1/-1; }
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
        /* Select2 dentro de modales Bootstrap: permitir overflow del dropdown */
        .select2-container--open { z-index: 9999 !important; }
        #modal_gestor_entrega .modal-dialog { overflow: visible !important; }
        #modal_gestor_entrega .modal-content { overflow: visible !important; }
        #modal_gestor_entrega .modal-body { overflow: visible !important; }
        #cargandoTemporales {
            position: fixed; inset: 0; z-index: 9998;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,.88);
        }
        html.temporales-cargados #cargandoTemporales { display: none !important; }
        #cargandoTemporales .carga-temporales-contenido {
            display: flex; flex-direction: column; align-items: center; gap: 12px;
            color: #00897b; font-size: 14px; font-weight: 700;
        }
        #cargandoTemporales .fa { font-size: 42px; }

        /* ── Resumen compacto de liquidación Expo ──────────────────── */
        .swal2-container.liquidacion-expo-container { align-items: center !important; padding: 16px !important; }
        .swal2-popup.liquidacion-expo-popup {
            width: min(680px, calc(100vw - 32px)) !important;
            max-height: calc(100vh - 32px); padding: 18px 20px 14px !important;
            border-radius: 8px !important; overflow: hidden;
        }
        .liquidacion-expo-popup .swal2-icon { width: 46px; height: 46px; margin: 0 auto 8px; }
        .liquidacion-expo-popup .swal2-icon .swal2-icon-content { font-size: 30px; }
        .liquidacion-expo-popup .swal2-title {
            padding: 0; color: #263238; font-size: 20px; line-height: 1.25;
        }
        .liquidacion-expo-popup .swal2-html-container {
            max-height: calc(100vh - 190px); margin: 12px 0 0; padding: 0 2px 2px;
            overflow-y: auto; overflow-x: hidden; color: #37474f;
        }
        .liquidacion-expo-popup .swal2-actions { margin: 12px 0 0; }
        .liquidacion-expo-popup .swal2-styled { margin: 0 4px; padding: 8px 18px; font-size: 13px; }
        .expo-liquidacion-estado {
            display: flex; align-items: center; gap: 8px; margin-bottom: 10px;
            padding: 8px 10px; border: 1px solid #b8dfc5; border-radius: 6px;
            background: #edf8f0; color: #25633a; font-size: 12px; text-align: left;
        }
        .expo-liquidacion-estado.info { border-color: #b7d7ef; background: #eef7fd; color: #245a7a; }
        .expo-liquidacion-metricas {
            display: grid; grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 7px; margin-bottom: 10px;
        }
        .expo-liquidacion-metrica {
            min-width: 0; padding: 8px 9px; border: 1px solid #e1e7e5;
            border-radius: 6px; background: #f8faf9; text-align: left;
        }
        .expo-liquidacion-metrica.destacada { border-color: #f2c98d; background: #fff8ed; }
        .expo-liquidacion-metrica span {
            display: block; margin-bottom: 2px; overflow: hidden; color: #718079;
            font-size: 9px; font-weight: 700; line-height: 1.2; text-overflow: ellipsis;
            text-transform: uppercase; white-space: nowrap;
        }
        .expo-liquidacion-metrica strong { display: block; color: #263832; font-size: 13px; line-height: 1.25; }
        .expo-liquidacion-metrica.destacada strong { color: #9a5800; }
        .expo-liquidacion-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 10px; align-items: start; }
        .expo-liquidacion-seccion { min-width: 0; }
        .expo-liquidacion-seccion.completa { grid-column: 1 / -1; }
        .expo-liquidacion-titulo {
            margin: 0 0 5px; color: #52615b; font-size: 10px; font-weight: 800;
            letter-spacing: 0; text-align: left; text-transform: uppercase;
        }
        .expo-liquidacion-tabla { width: 100%; margin: 0; border-collapse: collapse; font-size: 11px; }
        .expo-liquidacion-tabla th {
            padding: 6px 7px; border-bottom: 1px solid #dce5e1; background: #f1f5f3;
            color: #607069; font-size: 9px; font-weight: 800; text-transform: uppercase;
        }
        .expo-liquidacion-tabla td { padding: 6px 7px; border-bottom: 1px solid #edf1ef; color: #394943; }
        .expo-liquidacion-tabla tbody tr:last-child td { border-bottom: 0; }
        .expo-liquidacion-tabla-contenedor { border: 1px solid #dfe7e3; border-radius: 6px; overflow-x: auto; }
        @media (max-width: 575.5px) {
            .swal2-container.liquidacion-expo-container { padding: 8px !important; }
            .swal2-popup.liquidacion-expo-popup {
                width: calc(100vw - 16px) !important; max-height: calc(100vh - 16px);
                padding: 14px 12px 12px !important;
            }
            .liquidacion-expo-popup .swal2-title { font-size: 17px; }
            .liquidacion-expo-popup .swal2-html-container { max-height: calc(100vh - 172px); }
            .expo-liquidacion-metricas { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .expo-liquidacion-grid { grid-template-columns: 1fr; }
            .expo-liquidacion-seccion.completa { grid-column: auto; }
        }
    </style>
    @endpush

    <div id="cargandoTemporales" role="status" aria-live="polite" aria-label="Cargando registros temporales">
        <div class="carga-temporales-contenido">
            <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
            <span>Cargando registros temporales...</span>
        </div>
    </div>

    {{-- ===== PAGE HEADING (solo en flujo) ===== --}}
    @if($fromFlujo && ($config->codigo ?? '') === 'cotizacion_clientes_a')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2><i class="fa fa-file-text-o" style="color:#00897b;"></i> {{ $expoConfig ? 'Oferta de Expo' : 'Nueva Oferta' }}</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('flujo.ventas') }}">Ventas</a></li>
                <li class="breadcrumb-item"><a href="{{ route('flujo.ofertas') }}">Ofertas</a></li>
                <li class="breadcrumb-item active"><strong>{{ $expoConfig ? 'Oferta de Expo' : 'Nueva Oferta' }}</strong></li>
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
        @if($fromPrefactura && !$esOfertaExpo)
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
                                    {{ $expoConfig ? 'Oferta de Expo - '.$expoConfig['nombre'] : 'Nueva Oferta' }}
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
                            <input type="hidden" id="expo_id"            name="expo_id"            value="{{ $expoConfig['id'] ?? '' }}">
                            <input type="hidden" id="idComprobante"      name="idComprobante"      value="">
                            <input type="hidden" id="codigo_autorizacion" name="codigo_autorizacion" value="">
                            <input type="hidden" id="pedido_vinculado_id" name="pedido_id"          value="{{ $pedidoId ?? '' }}"> {{-- vinculación a pedido --}}
                            <input type="hidden" id="flujo_vinculado_id"  name="flujo_id"           value="{{ $flujoVinculadoId ?? '' }}"> {{-- flujo directo (sin pedido) --}}
                            <input type="hidden" id="prefactura_vinculada_id" name="prefactura_id"   value="{{ $prefacturaVinculadaId ?? '' }}"> {{-- prefactura vinculada --}}
                            <input type="hidden" id="cotizacion_vinculada_id" name="cotizacion_id" value="{{ $prefacturaVinculada['cotizacion_id'] ?? ($duplicandoOferta ? '' : request()->get('cotizacionId', '')) }}">
                            <input type="hidden" name="duplicar_cotizacion_id" value="{{ $duplicandoOferta ? request()->get('cotizacionId', '') : '' }}">

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
                                    <label class="ofr-label">Asesor Comercial <span class="req">*</span></label>
                                    <select name="vendedor" id="vendedor" class="form-control form-control-sm" required>
                                        <option value="" selected disabled>--Seleccionar--</option>
                                    </select>
                                </div>
                                {{-- Gestor de Entrega (se selecciona en modal al facturar) --}}
                                <input type="hidden" name="gestor_entrega" id="gestor_entrega_hidden" value="">
                                <input type="hidden" name="tele_asesor" id="tele_asesor_hidden" value="{{ Auth::id() }}">
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
                                        @if($expoConfig) readonly @endif
                                        onchange="calcularTotalesInicioPagina()">
                                </div>
                                {{-- Fecha emisión --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">Fecha Emisión <span class="req">*</span></label>
                                    <input class="form-control form-control-sm" type="date" id="fecha_emision"
                                        onchange="sumarDiasCredito()" name="fecha_emision"
                                        value="{{ date('Y-m-d') }}" data-parsley-required
                                        @if(request()->query('modo') === 'editar_factura') readonly @endif>
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
                                    <div class="input-group input-group-sm">
                                        <select id="codigoExoneracion" name="codigoExoneracion" class="form-control form-control-sm">
                                            <option value="" selected disabled>--Seleccione--</option>
                                        </select>
                                        <div class="input-group-append">
                                            <button type="button" id="btnNuevaExoneracionInline"
                                                onclick="abrirModalNuevaExoneracion()"
                                                title="Crear nuevo código de exoneración"
                                                style="background:linear-gradient(135deg,#1b5e20,#2e7d32); color:#fff; border:none;
                                                       border-radius:0 6px 6px 0; padding:0 10px; font-size:13px;
                                                       font-weight:700; cursor:pointer; white-space:nowrap;">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                {{-- ── N° Orden de Compra y Forma F01 ──────── --}}
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">N° Orden de Compra</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control form-control-sm"
                                            id="numero_orden_compra" name="numero_orden_compra"
                                            maxlength="100" placeholder="Número de orden de compra (opcional)">
                                        <div class="input-group-append">
                                            <label class="mb-0" title="Adjuntar archivo (imagen o PDF)"
                                                style="background:linear-gradient(135deg,#1565c0,#1976d2); color:#fff;
                                                       border-radius:0 6px 6px 0; padding:0 10px; font-size:13px;
                                                       cursor:pointer; display:flex; align-items:center; height:100%;">
                                                <i class="fa fa-paperclip"></i>
                                                <input type="file" id="archivo_orden_compra_input"
                                                    accept=".pdf,image/jpeg,image/png,image/gif"
                                                    style="display:none"
                                                    onchange="subirArchivoOferta('orden_compra', this)">
                                            </label>
                                        </div>
                                    </div>
                                    <div id="preview_archivo_orden_compra" style="margin-top:4px; font-size:11px; color:#1565c0; display:none;">
                                        <i class="fa fa-check-circle mr-1"></i><span id="txt_archivo_orden_compra"></span>
                                        <a href="#" onclick="limpiarArchivoOferta('orden_compra'); return false;"
                                            style="color:#c62828; margin-left:6px;"><i class="fa fa-times"></i></a>
                                    </div>
                                    <input type="hidden" id="archivo_orden_compra" name="archivo_orden_compra" value="">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="ofr-label">N° Forma F01</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control form-control-sm"
                                            id="numero_forma_f01" name="numero_forma_f01"
                                            maxlength="100" placeholder="Número de Forma F01 (opcional)">
                                        <div class="input-group-append">
                                            <label class="mb-0" title="Adjuntar archivo (imagen o PDF)"
                                                style="background:linear-gradient(135deg,#1565c0,#1976d2); color:#fff;
                                                       border-radius:0 6px 6px 0; padding:0 10px; font-size:13px;
                                                       cursor:pointer; display:flex; align-items:center; height:100%;">
                                                <i class="fa fa-paperclip"></i>
                                                <input type="file" id="archivo_forma_f01_input"
                                                    accept=".pdf,image/jpeg,image/png,image/gif"
                                                    style="display:none"
                                                    onchange="subirArchivoOferta('forma_f01', this)">
                                            </label>
                                        </div>
                                    </div>
                                    <div id="preview_archivo_forma_f01" style="margin-top:4px; font-size:11px; color:#1565c0; display:none;">
                                        <i class="fa fa-check-circle mr-1"></i><span id="txt_archivo_forma_f01"></span>
                                        <a href="#" onclick="limpiarArchivoOferta('forma_f01'); return false;"
                                            style="color:#c62828; margin-left:6px;"><i class="fa fa-times"></i></a>
                                    </div>
                                    <input type="hidden" id="archivo_forma_f01" name="archivo_forma_f01" value="">
                                </div>
                            </div>

                            </div>{{-- /sec_cliente --}}
                            </div>{{-- /body_cliente --}}
                            </div>{{-- /of-card cliente --}}

                            {{-- ── SECCIÓN 2: Agregar Producto ─────────────────────────── --}}
                            <span id="ico_sec_producto" style="display:none;"></span>
                            <div class="of-card">
                            <div class="of-card-title d-flex align-items-center" onclick="toggleOfCard('body_producto', this)">
                                <span><i class="fa fa-plus-circle text-success"></i> Agregar producto al carrito</span>
                                @if(!empty($expoConfig))
                                <button type="button" onclick="event.stopPropagation(); abrirCotizadorDescuentosExpo();"
                                    class="btn btn-outline-success btn-sm ml-auto mr-2" style="font-size:11px; font-weight:700; border-radius:6px;">
                                    <i class="mr-1 fa fa-calculator"></i> Cotizar descuentos
                                </button>
                                @endif
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
                                                onfocus="manejarFocoBusquedaProducto()"
                                                oninput="prepararNuevaBusquedaProducto(this.value)"
                                                onkeydown="if(event.key==='Enter'){event.preventDefault();buscarPorCodigo(this.value);return false;}">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary btn-sm" title="Buscar producto"
                                                    onclick="abrirBusquedaProductoActual()">
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
                                            <div id="bloqueImagenes" class="carousel-inner" style="border-radius:10px; overflow:hidden; height:220px; background:#f8f9fa;"></div>
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

                            @if(!$duplicandoOferta && $esOfertaExpo && (!$fromPrefactura || request()->boolean('expo_parcial')) && count($productosParaCarrito) > 0)
                            <div class="of-card expo-pendientes-card">
                                <div class="expo-pendientes-encabezado">
                                    <div class="expo-pendientes-titulo">
                                        <span class="of-card-title"><i class="fa fa-tags text-warning"></i> Líneas pendientes de la Oferta Expo</span>
                                        <small>Busque, filtre y seleccione únicamente los productos que desea facturar.</small>
                                    </div>
                                    <div class="expo-pendientes-contadores">
                                        <span class="expo-contador"><span id="expoTotalLineas">{{ count($productosParaCarrito) }}</span> pendientes</span>
                                        <span class="expo-contador"><span id="expoResultadosLineas">{{ count($productosParaCarrito) }}</span> resultados</span>
                                        <span class="expo-contador seleccionado"><span id="expoSeleccionadasLineas">0</span> seleccionadas</span>
                                    </div>
                                </div>
                                <div class="expo-pendientes-herramientas">
                                    <div class="expo-buscador">
                                        <i class="fa fa-search" aria-hidden="true"></i>
                                        <input type="search" id="expoBuscarLinea" class="form-control form-control-sm"
                                            placeholder="Buscar por código, producto o marca..." autocomplete="off">
                                    </div>
                                    <select id="expoFiltrarMarca" class="form-control form-control-sm" aria-label="Filtrar por marca">
                                        <option value="">Todas las marcas</option>
                                        @foreach(collect($productosParaCarrito)->groupBy('marca_id')->sortBy(fn($lineas) => $lineas->first()['marca_nombre'] ?? 'SIN MARCA') as $marcaId => $lineasMarca)
                                        <option value="{{ (int)$marcaId }}">{{ $lineasMarca->first()['marca_nombre'] ?? 'SIN MARCA' }} ({{ $lineasMarca->count() }})</option>
                                        @endforeach
                                    </select>
                                    <select id="expoFiltrarEstado" class="form-control form-control-sm" aria-label="Filtrar por estado">
                                        <option value="todos">Todos los estados</option>
                                        <option value="sin_carrito">Sin agregar al carrito</option>
                                        <option value="parciales">Agregados parcialmente</option>
                                        <option value="seleccionados">Seleccionados</option>
                                    </select>
                                </div>
                                <div class="expo-pendientes-acciones">
                                    <small id="expoRangoLineas" class="text-muted"></small>
                                    <div class="d-flex flex-wrap" style="gap:6px;">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="expoAlternarTodas(false)"><i class="fa fa-times mr-1"></i>Limpiar selección</button>
                                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="expoAlternarTodas(true)" title="Selecciona todos los productos que coinciden con los filtros"><i class="fa fa-check-square-o mr-1"></i>Seleccionar resultados</button>
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="expoSeleccionarMarca()" title="Selecciona todos los productos pendientes de la marca filtrada"><i class="fa fa-tags mr-1"></i>Seleccionar marca</button>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="expoAgregarSeleccionados()"><i class="fa fa-cart-plus mr-1"></i>Agregar <span id="expoCantidadAgregar">0</span></button>
                                    </div>
                                </div>
                                <div id="expoPendientesLista" class="expo-pendientes-lista"></div>
                                <div class="expo-pendientes-paginacion">
                                    <button id="expoPaginaAnterior" type="button" class="btn btn-sm btn-outline-secondary" onclick="expoCambiarPagina(-1)" aria-label="Página anterior"><i class="fa fa-chevron-left"></i></button>
                                    <small id="expoPaginaEstado" class="text-muted"></small>
                                    <button id="expoPaginaSiguiente" type="button" class="btn btn-sm btn-outline-secondary" onclick="expoCambiarPagina(1)" aria-label="Página siguiente"><i class="fa fa-chevron-right"></i></button>
                                </div>
                            </div>
                            @endif

                            {{-- ── CARRITO DE PRODUCTOS ────────────────────────────────── --}}
                            <div class="of-card" style="padding:0; overflow:hidden;">
                                <div style="padding:16px 24px 12px; border-bottom:1px solid #f0f2f5; display:flex; align-items:center; gap:8px; cursor:pointer;"
                                     onclick="toggleOfCard('body_carrito', this)">
                                    <span class="mb-0 of-card-title" style="cursor:pointer; margin-bottom:0 !important;">
                                        <i class="fa fa-shopping-cart text-warning"></i> Carrito de productos
                                    </span>
                                    <span id="cart-count-badge">0 producto(s)</span>
                                    @if(!empty($expoConfig))
                                    <button type="button" class="btn btn-sm btn-outline-success ml-auto"
                                        onclick="event.stopPropagation(); abrirResumenMarcasCarritoExpo();"
                                        title="Ver resumen del carrito por marca">
                                        <i class="fa fa-tags mr-1"></i> Resumen por marca
                                    </button>
                                    @endif
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
                                                    @if(!empty($expoConfig))<th style="min-width:190px;">Descuento Expo</th>@endif
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
                                            @if(!empty($expoConfig))
                                            <div id="descuentoExpoResumenMarcas" style="padding:0 14px 8px; font-size:11px;"></div>
                                            @endif
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
        {{-- MODAL: Solicitar autorización SR (con tabla de precios)        --}}
        {{-- (Visible solo para tipos que requieren código)                 --}}
        {{-- ============================================================== --}}
        <div class="modal fade" id="modal_sr_autorizacion" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background:linear-gradient(135deg,#e65100,#f9a826); border:none; padding:14px 20px;">
                        <h3 class="modal-title" style="color:#fff; font-size:16px; font-weight:700; margin:0;">
                            <i class="fa-solid fa-shield-halved mr-2"></i>Solicitar Autorización SR
                        </h3>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3 text-muted" style="font-size:12px;">
                            Se enviará un código de autorización por correo al departamento de autorizaciones.
                            Las filas marcadas en <span style="color:#c62828; font-weight:700;">rojo</span> tienen precio inferior al precio de escala (OPC).
                        </p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" style="font-size:12px; margin-bottom:0;">
                                <thead style="background:#f5f5f5;">
                                    <tr>
                                        <th>Producto</th>
                                        <th style="text-align:right; white-space:nowrap;">Precio OPC</th>
                                        <th style="text-align:right; white-space:nowrap;">P.Unitario</th>
                                    </tr>
                                </thead>
                                <tbody id="srTableBody"></tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <button class="btn btn-primary" type="button" id="btnSolicitarCodigo" onclick="solicitarCodigo()">
                                <i class="fa-solid fa-paper-plane mr-1"></i> Solicitar Código
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL: Seleccionar Gestor de Entrega y Tele Asesor            --}}
        {{-- ============================================================== --}}
        <div class="modal fade" id="modal_gestor_entrega" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background:linear-gradient(135deg,#1565c0,#42a5f5); border:none; padding:14px 20px;">
                        <h3 class="modal-title" style="color:#fff; font-size:16px; font-weight:700; margin:0;">
                            <i class="fa-solid fa-users mr-2"></i>Actores de la Factura
                        </h3>
                    </div>
                    <div class="modal-body" style="padding: 20px;">
                        <p class="text-muted mb-3" style="font-size:12px;">
                            Seleccione el responsable de entrega y el tele asesor para esta factura.
                        </p>
                        <div class="form-group">
                            <label class="ofr-label">Gestor de Entrega</label>
                            <select id="gestor_entrega_modal" class="form-control form-control-sm" style="width:100%;">
                                <option value="">-- Sin gestor --</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="ofr-label">Tele Asesor <span class="req">*</span></label>
                            <select id="tele_asesor_modal" class="form-control form-control-sm" style="width:100%;">
                                <option value="">-- Seleccionar tele asesor --</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary btn-sm" id="btn_confirmar_gestor">
                            <i class="fa-solid fa-check mr-1"></i> Confirmar y Facturar
                        </button>
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

                        {{-- 4 botones --}}
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">

                            {{-- Facturar directo --}}
                            <button id="btnPrefFacturarDirecto" onclick="prefacturaAccion('facturar')"
                                    style="background:linear-gradient(135deg,#1b5e20,#2e7d32); color:#fff; border:none;
                                           border-radius:10px; padding:11px 6px; font-size:11px; font-weight:700;
                                           cursor:pointer; text-align:center; box-shadow:0 3px 10px rgba(27,94,32,.25); transition:opacity .15s;"
                                    onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                                <i class="fa fa-bolt d-block" style="font-size:20px; margin-bottom:4px;"></i>
                                Facturar
                            </button>

                            {{-- Editar factura (requiere autorización) --}}
                            <button onclick="prefacturaAccion('editar')"
                                    style="background:linear-gradient(135deg,#1565c0,#1a7efb); color:#fff; border:none;
                                           border-radius:10px; padding:11px 6px; font-size:11px; font-weight:700;
                                           cursor:pointer; text-align:center; box-shadow:0 3px 10px rgba(21,101,192,.25); transition:opacity .15s;"
                                    onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                                <i class="fa fa-pencil d-block" style="font-size:20px; margin-bottom:4px;"></i>
                                Editar Factura
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

        {{-- MODAL: Oferta enviada a Revisión de Crédito --}}
        <div class="modal fade" id="modalRevisionCredito" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" style="z-index:2075;">
            <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:420px;">
                <div class="modal-content" style="border-radius:20px; overflow:hidden; border:none; box-shadow:0 20px 60px rgba(0,0,0,.18); position:relative;">
                    <button type="button" data-dismiss="modal" aria-label="Cerrar"
                            style="position:absolute; top:12px; right:14px; background:none; border:none;
                                   font-size:20px; color:#9e9e9e; cursor:pointer; line-height:1; z-index:1;
                                   padding:4px 8px; border-radius:50%;" title="Cerrar">&times;</button>
                    <div class="modal-body" style="padding:36px 32px 28px; text-align:center;">

                        {{-- Ícono crédito --}}
                        <div style="width:90px; height:90px; border-radius:50%;
                                    background:linear-gradient(135deg,#1565c0,#1a7efb);
                                    display:flex; align-items:center; justify-content:center;
                                    margin:0 auto 20px; box-shadow:0 8px 24px rgba(21,101,192,.30);">
                            <i class="fa fa-credit-card" style="font-size:42px; color:#fff; line-height:1;"></i>
                        </div>

                        <h4 style="font-weight:800; color:#1565c0; margin-bottom:8px; font-size:18px;">Revisión de Crédito</h4>
                        <p style="color:#546e7a; font-size:13px; margin-bottom:6px;">
                            La oferta fue marcada como ganadora y enviada a <strong>Revisión de Crédito</strong>.
                        </p>
                        <p style="color:#90a4ae; font-size:11px; margin-bottom:24px; line-height:1.6;">
                            <i class="mr-1 fa fa-info-circle"></i>
                            El equipo de crédito evaluará las condiciones del cliente.<br>
                            Una vez aprobada, se continuará con la generación de la prefactura.
                        </p>

                        <div id="revisionCredMeta"
                             style="display:none; text-align:left; background:#f7fbff; border:1px solid #dbeafe;
                                    border-radius:10px; padding:10px 12px; margin-bottom:16px; font-size:12px; color:#374151;">
                            <div><strong>Fecha emisión:</strong> <span id="revMetaEmision">—</span></div>
                            <div><strong>Fecha vencimiento:</strong> <span id="revMetaVencimiento">—</span></div>
                            <div><strong>Días solicitados:</strong> <span id="revMetaDias">0</span></div>
                            <div><strong>Monto oferta:</strong> <span id="revMetaMonto">L 0.00</span></div>
                        </div>

                        {{-- Botón Ver flujo --}}
                        <button onclick="revisionCredAccion('flujo')"
                                style="width:100%; background:linear-gradient(135deg,#1565c0,#1a7efb); color:#fff; border:none;
                                       border-radius:10px; padding:13px 8px; font-size:13px; font-weight:700;
                                       cursor:pointer; text-align:center; box-shadow:0 3px 10px rgba(21,101,192,.25); transition:opacity .15s;"
                                onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                            <i class="fa fa-sitemap mr-2" style="font-size:16px;"></i>
                            Ver flujo
                        </button>

                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL: Autorización para editar factura desde prefactura --}}
        <div class="modal fade" id="modalAutorizacionEditarPref" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" style="z-index:2080;">
            <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:400px;">
                <div class="modal-content" style="border-radius:16px; overflow:hidden; border:none; box-shadow:0 16px 48px rgba(0,0,0,.18);">
                    <div class="modal-header" style="background:linear-gradient(135deg,#1565c0,#1a7efb); border:none; padding:16px 24px;">
                        <h5 class="modal-title" style="color:#fff; font-weight:700; margin:0;">
                            <i class="mr-2 fa fa-lock"></i> Autorización requerida
                        </h5>
                    </div>
                    <div class="modal-body" style="padding:24px;">
                        <p style="color:#546e7a; font-size:13px; margin-bottom:16px;">
                            Para <strong>editar la factura</strong> desde una prefactura se requiere un código de autorización de supervisor.
                        </p>

                        <div class="mb-3">
                            <button type="button" id="btnSolicitarCodigoEditarPref"
                                    onclick="solicitarCodigoEditarPref(this)"
                                    class="btn btn-outline-primary btn-sm btn-block">
                                <i class="fa fa-paper-plane mr-1"></i> Solicitar código por correo
                            </button>
                        </div>

                        <div class="form-group mb-2">
                            <label style="font-size:11px; font-weight:700; color:#546e7a; text-transform:uppercase; letter-spacing:.5px;">
                                Código de autorización <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="codigoEditarPref" class="form-control"
                                   placeholder="Ingrese el código de 4 dígitos"
                                   onkeydown="event.stopPropagation()" autocomplete="off" maxlength="10">
                            <span id="errCodigoEditarPref" class="text-danger" style="font-size:12px; display:none;">Código incorrecto.</span>
                        </div>

                        <div class="form-group mb-0">
                            <label style="font-size:11px; font-weight:700; color:#546e7a; text-transform:uppercase; letter-spacing:.5px;">
                                Motivo <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="motivoEditarPref" class="form-control"
                                   placeholder="Indique el motivo de edición"
                                   onkeydown="event.stopPropagation()" autocomplete="off">
                            <span id="errMotivoEditarPref" class="text-danger" style="font-size:12px; display:none;">El motivo es requerido.</span>
                        </div>
                    </div>
                    <div class="modal-footer" style="border:none; padding:12px 24px 20px;">
                        <button type="button" class="btn btn-secondary btn-sm"
                                onclick="$('#modalAutorizacionEditarPref').modal('hide'); $('#modalPrefacturaExito').modal('show');">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="verificarCodigoEditarPref()">
                            <i class="fa fa-check mr-1"></i> Verificar y continuar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL: Éxito guardado factura – mismo estilo que oferta --}}
        <div class="modal fade" id="modalExitoFactura" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:420px;">
                <div class="modal-content" style="border-radius:20px; overflow:hidden; border:none; box-shadow:0 20px 60px rgba(0,0,0,.18); position:relative;">
                    <button type="button" data-dismiss="modal" aria-label="Cerrar"
                            style="position:absolute; top:12px; right:14px; background:none; border:none;
                                   font-size:20px; color:#9e9e9e; cursor:pointer; line-height:1; z-index:1;
                                   padding:4px 8px; border-radius:50%;" title="Cerrar">&times;</button>
                    <div class="modal-body" style="padding:36px 32px 28px; text-align:center;">

                        <div style="width:90px; height:90px; border-radius:50%;
                                    background:linear-gradient(135deg,#00c853,#69f0ae);
                                    display:flex; align-items:center; justify-content:center;
                                    margin:0 auto 20px; box-shadow:0 8px 24px rgba(0,200,83,.30);">
                            <i class="fa fa-check" style="font-size:46px; color:#fff; line-height:1;"></i>
                        </div>

                        <h4 style="font-weight:800; color:#1b5e20; margin-bottom:6px; font-size:18px;">¡Factura guardada!</h4>
                        <p id="msgNumFactura" style="color:#546e7a; font-size:13px; margin-bottom:24px;">La factura fue registrada exitosamente.</p>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <button onclick="facturaAccion('nueva')"
                                    style="background:#f0fdf4; color:#1b5e20; border:1.5px solid #a7f3d0;
                                           border-radius:10px; padding:11px 8px; font-size:12px; font-weight:700;
                                           cursor:pointer; text-align:center; transition:background .15s;"
                                    onmouseover="this.style.background='#dcfce7'" onmouseout="this.style.background='#f0fdf4'">
                                <i class="fa fa-plus-circle d-block" style="font-size:20px; margin-bottom:4px; color:#16a34a;"></i>
                                Nueva factura
                            </button>

                            <button onclick="facturaAccion('flujo')"
                                    style="background:#eff6ff; color:#1e40af; border:1.5px solid #bfdbfe;
                                           border-radius:10px; padding:11px 8px; font-size:12px; font-weight:700;
                                           cursor:pointer; text-align:center; transition:background .15s;"
                                    onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                                <i class="fa fa-sitemap d-block" style="font-size:20px; margin-bottom:4px; color:#2563eb;"></i>
                                Ver flujo
                            </button>

                            <button onclick="facturaAccion('imprimir')"
                                    style="background:#fafafa; color:#374151; border:1.5px solid #e5e7eb;
                                           border-radius:10px; padding:11px 8px; font-size:12px; font-weight:700;
                                           cursor:pointer; text-align:center; transition:background .15s;"
                                    onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fafafa'">
                                <i class="fa fa-print d-block" style="font-size:20px; margin-bottom:4px; color:#6b7280;"></i>
                                Imprimir factura
                            </button>

                            <button onclick="facturaAccion('vale')"
                                    style="background:#eef2ff; color:#3730a3; border:1.5px solid #c7d2fe;
                                           border-radius:10px; padding:11px 8px; font-size:12px; font-weight:700;
                                           cursor:pointer; text-align:center; transition:background .15s;"
                                    onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='#eef2ff'">
                                <i class="fa fa-ticket d-block" style="font-size:20px; margin-bottom:4px; color:#4338ca;"></i>
                                Crear vale
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

    {{-- MODAL INLINE: Crear nuevo Código de Exoneración desde Facturación --}}
    <div class="modal fade" id="modal_nueva_exoneracion_inline" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:440px;">
            <div class="modal-content" style="border-radius:14px; overflow:hidden;">
                <div class="modal-header" style="background:linear-gradient(135deg,#1b5e20,#2e7d32); border:none; padding:14px 20px;">
                    <h5 class="modal-title" style="color:#fff; font-weight:800; margin:0; font-size:14px;">
                        <i class="mr-2 fa fa-plus-circle"></i> Nuevo Código de Exoneración
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;"><span>&times;</span></button>
                </div>
                <div class="modal-body" style="padding:20px 24px;">
                    <form id="formNuevaExoneracionInline" autocomplete="off">
                        <div class="mb-3">
                            <label class="ofr-label">Cliente</label>
                            <input type="text" id="nuevaExoneracionClienteNombre" class="form-control form-control-sm" readonly
                                style="background:#f1f8e9; color:#1b5e20; font-weight:700; border-color:#a5d6a7;">
                            <input type="hidden" id="nuevaExoneracionClienteId">
                        </div>
                        <div class="mb-3">
                            <label class="ofr-label">Código de Exoneración <span class="req">*</span></label>
                            <input type="text" id="nuevaExoneracionCodigo"
                                class="form-control form-control-sm ofr-input"
                                placeholder="Ej: EX-2026-001" required autocomplete="off" maxlength="100">
                        </div>
                        <div class="mb-1">
                            <label class="ofr-label">Correlativo / Orden</label>
                            <input type="text" id="nuevaExoneracionCorrOrd"
                                class="form-control form-control-sm ofr-input"
                                placeholder="Opcional" autocomplete="off" maxlength="100">
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="padding:10px 20px;">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formNuevaExoneracionInline" id="btnGuardarNuevaExoneracion"
                        style="background:linear-gradient(135deg,#1b5e20,#2e7d32); color:#fff; border:none;
                               font-weight:700; border-radius:8px; padding:6px 18px; font-size:13px; cursor:pointer;">
                        <i class="mr-1 fa fa-save"></i> Guardar Código
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL INLINE: Crear nueva Orden de Compra desde Facturación --}}
    <div class="modal fade" id="modal_nueva_orden_inline" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:440px;">
            <div class="modal-content" style="border-radius:14px; overflow:hidden;">
                <div class="modal-header" style="background:linear-gradient(135deg,#e65100,#f9a826); border:none; padding:14px 20px;">
                    <h5 class="modal-title" style="color:#fff; font-weight:800; margin:0; font-size:14px;">
                        <i class="mr-2 fa fa-plus-circle"></i> Nueva Orden de Compra
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;"><span>&times;</span></button>
                </div>
                <div class="modal-body" style="padding:20px 24px;">
                    <form id="formNuevaOrdenInline" autocomplete="off">
                        <div class="mb-3">
                            <label class="ofr-label">Cliente</label>
                            <input type="text" id="nuevaOrdenClienteNombre" class="form-control form-control-sm" readonly
                                style="background:#f1f8e9; color:#1b5e20; font-weight:700; border-color:#a5d6a7;">
                            <input type="hidden" id="nuevaOrdenClienteId">
                        </div>
                        <div class="mb-1">
                            <label class="ofr-label">Número de Orden <span class="req">*</span></label>
                            <input type="text" id="nuevaOrdenNumero" name="numero_orden"
                                class="form-control form-control-sm ofr-input"
                                placeholder="Ej: OC-2026-001" required autocomplete="off" maxlength="100">
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="padding:10px 20px;">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formNuevaOrdenInline" id="btnGuardarNuevaOrden"
                        style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none;
                               font-weight:700; border-radius:8px; padding:6px 18px; font-size:13px; cursor:pointer;">
                        <i class="mr-1 fa fa-save"></i> Guardar Orden
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($expoConfig))
    <style>
        #modalCotizadorDescuentosExpo .modal-dialog { max-width: 1050px; }
        #modalCotizadorDescuentosExpo .modal-content { background:#fff; color:#37474f; border:0; border-radius:8px; overflow:hidden; box-shadow:0 18px 55px rgba(28,49,58,.35); }
        #modalCotizadorDescuentosExpo .modal-header { background:#1f6f50; color:#fff; border:0; padding:16px 20px; }
        #modalCotizadorDescuentosExpo .modal-header .close { color:#fff; opacity:.9; text-shadow:none; }
        #modalCotizadorDescuentosExpo .modal-body { background:#f7faf8; padding:20px; }
        #modalCotizadorDescuentosExpo .modal-footer { background:#fff; border-top:1px solid #dce7e1; }
        #modalCotizadorDescuentosExpo .cotizador-expo-campo { background:#fff; border:1px solid #c9d8d0; border-radius:6px; padding:14px; }
        #modalCotizadorDescuentosExpo .cotizador-expo-resumen { background:#fff; border-left:4px solid #ef8c22; padding:10px 12px; margin-bottom:12px; }
        #modalCotizadorDescuentosExpo .cotizador-expo-tabla { background:#fff; font-size:12px; }
        #modalCotizadorDescuentosExpo .cotizador-expo-tabla thead th { background:#e6f1eb; color:#245c46; border-color:#cbded4; vertical-align:middle; }
        #modalCotizadorDescuentosExpo .cotizador-expo-tabla td { color:#37474f; border-color:#dce7e1; vertical-align:middle; }
    </style>
    <div class="modal fade" id="modalCotizadorDescuentosExpo" tabindex="-1" role="dialog" aria-labelledby="tituloCotizadorDescuentosExpo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="tituloCotizadorDescuentosExpo"><i class="fa fa-calculator mr-2"></i>Cotizar descuentos Expo</h5>
                        <small style="color:#d9eee4;">Cantidades mínimas y precios según las reglas parametrizadas.</small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="cotizador-expo-campo mb-3">
                        <div class="row align-items-end">
                            <div class="col-12 col-md-8 mb-2 mb-md-0">
                                <label class="ofr-label">Producto</label>
                                <div class="input-group">
                                    <input type="text" id="cotizadorExpoCodigoProducto" class="form-control"
                                        placeholder="ID, nombre o código de barras del producto..." autocomplete="off"
                                        oninput="prepararNuevaBusquedaCotizadorExpo(this.value)"
                                        onkeydown="if(event.key==='Enter'){event.preventDefault();buscarProductoCotizadorExpo(this.value);return false;}">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success" title="Buscar producto" onclick="abrirBusquedaProductoCotizadorExpo()">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <small id="cotizadorExpoProductoLabel" class="mt-1 text-success font-weight-bold d-block d-none" style="font-size:11px;"></small>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="ofr-label">Escala de precio</label>
                                <select id="cotizadorExpoEscala" class="form-control" onchange="recalcularCotizadorDescuentosExpo()" disabled>
                                    <option value="">Seleccione un producto</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="cotizadorExpoResultado">
                        <div class="text-center text-muted py-4"><i class="fa fa-tags fa-2x mb-2 d-block"></i>Seleccione un producto para consultar sus descuentos.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalResumenMarcasCarritoExpo" tabindex="-1" role="dialog" aria-labelledby="tituloResumenMarcasCarritoExpo" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="border:0; border-radius:8px; overflow:hidden;">
                <div class="modal-header" style="background:#1f6f50; color:#fff; border:0;">
                    <h5 class="modal-title" id="tituloResumenMarcasCarritoExpo"><i class="fa fa-tags mr-2"></i>Resumen del carrito por marca</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" style="color:#fff; opacity:.9; text-shadow:none;"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body" style="background:#f7faf8;">
                    <div id="resumenMarcasCarritoExpoContenido"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Buscador de producto reutilizable --}}
    <x-buscador-producto
        id-modal="buscadorProductoUnificado"
        callback="alSeleccionarProducto"
        :expo-id="$filtrarProductosExpo && $expoConfig ? $expoConfig['id'] : null"
    />

    {{-- Modal global de flujo (escucha abrirFlujoPedido / abrirFlujoCotizacion) --}}
    <livewire:flujo.modal-flujo-pedido />

    @push('scripts')
    <script>
    // ================================================================
    // CONFIGURACIÓN DEL TIPO DE FACTURA (desde PHP)
    // ================================================================
    var tipoFacturaConfig = @json($config);
    var expoConfig = @json($expoConfig ?? null);
    var esOfertaExpo = {!! $esOfertaExpo ? 'true' : 'false' !!};
    var filtrarProductosExpo = {!! $filtrarProductosExpo ? 'true' : 'false' !!};
    var reglasExpoOferta = @json($reglasExpoOferta ?? []);
    var atribucionesDescuentoExpo = @json($atribucionesDescuentoExpo ?? []);
    var seleccionandoProductoCotizadorExpo = false;
    var productoCotizadorExpo = null;
    var datosCalculoCotizadorExpo = null;
    var productoExpoAgregandoAutomaticamente = null;
    var datosProductoExpoPrecargados = null;
    var bodegaExpoCapturaRapida = null;

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
            vendedores: '/cotizacion/vendedores-asignados',
            orden_compra: null,
            codigos_exoneracion: null
        }
    };

    // Obtener URLs del tipo actual
    var codigoActual = tipoFacturaConfig ? tipoFacturaConfig.codigo : 'estatal';
    var urls = urlsPorTipo[codigoActual] || urlsPorTipo['estatal'];

    var numeroInputs = 0;
    var arregloIdInputs = [];
    var ventaTemporalId = new URLSearchParams(window.location.search).get('temporal_id');
    var ventaTemporalTipo = codigoActual === 'cotizacion_clientes_a' ? 'oferta' : 'factura';
    var ventaTemporalRestaurando = false;
    var ventaTemporalFinalizada = false;
    var ventaTemporalTimer = null;
    var ventaTemporalObserver = null;
    var ventaTemporalAutosaveActivo = false;
    var retencionEstado = false;
    var diasCredito = 0;
    var diasCreditoAprobadosFlujo = null;
    var modoEditarFactura = new URLSearchParams(window.location.search).get('modo') === 'editar_factura';
    var secuenciaBusquedaProducto = 0;
    var omitirLimpiezaFocoProducto = false;
    var idAutorizacion = 0;
    var idFactura = 0;
    var public_path = "{{ asset('catalogo/') }}";
    // Datos de la oferta original cuando se viene de duplicar
    var _ofertaDuplicada = @json($datosOfertaDuplicada ?? null);

    // ================================================================
    // INICIALIZACIÓN
    // ================================================================
    window.onload = function() {
        inicializarFormulario();
    };

    function inicializarFormulario() {
        inicializarSelect2();
        Promise.resolve(obtenerTipoPago()).finally(function() {
            inicializarVentaTemporal();
        });
    }

    function urlReanudacionTemporal() {
        var url = new URL(window.location.href);
        url.searchParams.delete('temporal_id');
        return url.pathname + url.search;
    }

    function obtenerControlesTemporal() {
        var controles = [];
        document.querySelectorAll('#crear_venta input, #crear_venta select, #crear_venta textarea, #numero_venta').forEach(function(control) {
            if (!control.id || control.type === 'file' || control.type === 'submit' || control.type === 'button') return;
            controles.push({
                id: control.id,
                value: control.value,
                checked: !!control.checked,
                options: control.tagName === 'SELECT'
                    ? Array.from(control.selectedOptions).map(function(option) { return { value: option.value, text: option.text }; })
                    : []
            });
        });
        return controles;
    }

    function crearInstantaneaTemporal() {
        var carrito = document.getElementById('carritoTbody');
        return {
            version: 1,
            controles: obtenerControlesTemporal(),
            carrito_html: carrito ? carrito.innerHTML : '',
            numero_inputs: numeroInputs,
            arreglo_id_inputs: arregloIdInputs.slice()
        };
    }

    function tituloVentaTemporal() {
        var cliente = $('#seleccionarCliente option:selected').text();
        var numero = document.getElementById('numero_venta');
        var prefijo = ventaTemporalTipo === 'oferta' ? 'Oferta' : 'Factura';
        return prefijo + (cliente && cliente.indexOf('--') !== 0 ? ' - ' + cliente : '') + (numero && numero.value ? ' #' + numero.value : '');
    }

    function programarGuardadoTemporal() {
        if (ventaTemporalRestaurando || ventaTemporalFinalizada) return;
        clearTimeout(ventaTemporalTimer);
        ventaTemporalTimer = setTimeout(guardarVentaTemporal, 600);
    }

    function guardarVentaTemporal() {
        if (ventaTemporalRestaurando || ventaTemporalFinalizada) return;
        var instantanea = crearInstantaneaTemporal();

        axios.post('/ventas/temporales', {
            id: ventaTemporalId || null,
            tipo: ventaTemporalTipo,
            codigo_tipo: codigoActual,
            titulo: tituloVentaTemporal(),
            url_reanudacion: urlReanudacionTemporal(),
            contenido: instantanea
        }).then(function(response) {
            ventaTemporalId = response.data.id;
            var url = new URL(window.location.href);
            url.searchParams.set('temporal_id', ventaTemporalId);
            window.history.replaceState({}, '', url.toString());
        }).catch(function(error) {
            console.warn('No se pudo guardar el registro temporal:', error);
        });
    }

    function eliminarVentaTemporal() {
        ventaTemporalFinalizada = true;
        clearTimeout(ventaTemporalTimer);
        if (!ventaTemporalId) return Promise.resolve();
        return axios.delete('/ventas/temporales/' + ventaTemporalId).catch(function(error) {
            console.warn('No se pudo eliminar el registro temporal:', error);
        });
    }

    function aplicarControlTemporal(controlGuardado) {
        var control = document.getElementById(controlGuardado.id);
        if (!control) return;
        if (control.id === 'vendedor') return;
        if (control.tagName === 'SELECT') {
            (controlGuardado.options || []).forEach(function(optionGuardada) {
                if (!Array.from(control.options).some(function(option) { return option.value == optionGuardada.value; })) {
                    control.add(new Option(optionGuardada.text, optionGuardada.value));
                }
            });
        }
        control.value = controlGuardado.value;
        if (control.type === 'checkbox' || control.type === 'radio') control.checked = controlGuardado.checked;
        if ($(control).hasClass('select2-hidden-accessible')) {
            var manejadorChange = control.onchange;
            control.onchange = null;
            $(control).trigger('change.select2');
            control.onchange = manejadorChange;
        }
    }

    function restaurarVentaTemporal(instantanea) {
        ventaTemporalRestaurando = true;
        var carrito = document.getElementById('carritoTbody');
        if (carrito) carrito.innerHTML = instantanea.carrito_html || '';
        numeroInputs = parseInt(instantanea.numero_inputs, 10) || 0;
        arregloIdInputs = Array.isArray(instantanea.arreglo_id_inputs)
            ? instantanea.arreglo_id_inputs.map(function(id) { return parseInt(id, 10); })
            : [];
        var controlesTemporales = instantanea.controles || [];
        controlesTemporales.forEach(aplicarControlTemporal);
        var asesorTemporal = controlesTemporales.find(function(control) { return control.id === 'vendedor'; });
        var clienteTemporal = document.getElementById('seleccionarCliente');
        if (clienteTemporal && clienteTemporal.value) {
            aplicarAsesorAsignado(clienteTemporal.value, asesorTemporal ? asesorTemporal.value : null);
        }
        normalizarFilasCarritoExpo();

        var tieneProductos = arregloIdInputs.length > 0;
        var tabla = document.getElementById('carritoTablaWrapper');
        var vacio = document.getElementById('carritoVacio');
        if (tabla) tabla.classList.toggle('d-none', !tieneProductos);
        if (vacio) vacio.classList.toggle('d-none', tieneProductos);
        actualizarContadorCarrito();
        ventaTemporalRestaurando = false;
    }

    function escaparHtmlTemporal(texto) {
        var elemento = document.createElement('div');
        elemento.textContent = texto || '';
        return elemento.innerHTML;
    }

    function ocultarCargaTemporales() {
        document.documentElement.classList.add('temporales-cargados');
        var cargando = document.getElementById('cargandoTemporales');
        if (cargando) cargando.style.display = 'none';
    }

    function iniciarNuevaVentaTemporal() {
        ocultarCargaTemporales();
        ventaTemporalId = null;
        var url = new URL(window.location.href);
        url.searchParams.delete('temporal_id');
        window.history.replaceState({}, '', url.toString());
        activarAutosaveTemporal();
    }

    function continuarVentaTemporal(temporal) {
        var separador = temporal.url_reanudacion.indexOf('?') >= 0 ? '&' : '?';
        if (temporal.url_reanudacion !== urlReanudacionTemporal()) {
            window.location.href = temporal.url_reanudacion + separador + 'temporal_id=' + temporal.id;
            return;
        }

        ventaTemporalId = temporal.id;
        axios.get('/ventas/temporales/' + temporal.id).then(function(response) {
            restaurarVentaTemporal(response.data.data.contenido || {});
            var url = new URL(window.location.href);
            url.searchParams.set('temporal_id', temporal.id);
            window.history.replaceState({}, '', url.toString());
            activarAutosaveTemporal();
        });
    }

    function ofrecerSeleccionTemporales(temporales) {
        var etiquetaDocumento = ventaTemporalTipo === 'oferta' ? 'oferta' : 'factura';
        var seleccionado = temporales.some(function(temporal) { return String(temporal.id) === String(ventaTemporalId); })
            ? String(ventaTemporalId)
            : String(temporales[0].id);
        var registrosHtml = temporales.map(function(temporal) {
            var fecha = new Date(temporal.updated_at);
            var actualizado = isNaN(fecha.getTime()) ? '' : fecha.toLocaleString('es-HN', { dateStyle: 'short', timeStyle: 'short' });
            return '<label style="display:flex;align-items:flex-start;gap:10px;padding:10px 12px;margin:0 0 7px;border:1px solid #dfe5eb;border-radius:7px;cursor:pointer;text-align:left;">'
                + '<input type="radio" name="temporal_seleccionado" value="' + temporal.id + '" ' + (String(temporal.id) === seleccionado ? 'checked' : '') + ' style="margin-top:3px;accent-color:#00897b;">'
                + '<span style="min-width:0;"><strong style="display:block;color:#37474f;font-size:13px;">' + escaparHtmlTemporal(temporal.titulo || 'Registro temporal') + '</strong>'
                + '<small style="color:#78909c;">Actualizado ' + escaparHtmlTemporal(actualizado) + '</small></span></label>';
        }).join('');

        Swal.fire({
            title: 'Registros temporales',
            html: '<p style="color:#607d8b;font-size:13px;text-align:left;">Seleccione la ' + etiquetaDocumento + ' que desea continuar.</p>' + registrosHtml,
            showDenyButton: true,
            confirmButtonText: '<i class="fa fa-play mr-1"></i>Continuar ' + etiquetaDocumento,
            denyButtonText: '<i class="fa fa-plus mr-1"></i>Realizar una nueva ' + etiquetaDocumento,
            confirmButtonColor: '#00897b',
            denyButtonColor: '#e65100',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: ocultarCargaTemporales,
            preConfirm: function() {
                var opcion = document.querySelector('input[name="temporal_seleccionado"]:checked');
                if (!opcion) {
                    Swal.showValidationMessage('Seleccione un registro temporal.');
                    return false;
                }
                return opcion.value;
            }
        }).then(function(result) {
            if (result.isConfirmed) {
                var temporal = temporales.find(function(item) { return String(item.id) === String(result.value); });
                if (temporal) continuarVentaTemporal(temporal);
            } else if (result.isDenied) {
                iniciarNuevaVentaTemporal();
            }
        });
    }

    function activarAutosaveTemporal() {
        if (ventaTemporalAutosaveActivo) return;
        var formulario = document.getElementById('crear_venta');
        if (!formulario) return;
        ventaTemporalAutosaveActivo = true;
        formulario.addEventListener('input', programarGuardadoTemporal);
        formulario.addEventListener('change', programarGuardadoTemporal);
        var carrito = document.getElementById('carritoTbody');
        if (carrito) {
            ventaTemporalObserver = new MutationObserver(programarGuardadoTemporal);
            ventaTemporalObserver.observe(carrito, { childList: true, subtree: true });
        }
    }

    function inicializarVentaTemporal() {
        axios.get('/ventas/temporales', { params: { tipo: ventaTemporalTipo } }).then(function(response) {
            var temporales = (response.data.data || []).filter(function(item) {
                return item.tipo === ventaTemporalTipo;
            });
            if (temporales.length > 0) {
                ofrecerSeleccionTemporales(temporales);
            } else {
                iniciarNuevaVentaTemporal();
            }
        }).catch(function() {
            iniciarNuevaVentaTemporal();
        });
    }

    function inicializarSelect2() {
        var urlClientes = urls.listar_clientes;
        var urlVendedores = urls.vendedores;

        // Destruir select2 existentes si los hay
        if ($('#vendedor').hasClass('select2-hidden-accessible')) {
            $('#vendedor').select2('destroy');
        }
        // gestor_entrega ya no tiene select2 en el form principal
        if ($('#seleccionarCliente').hasClass('select2-hidden-accessible')) {
            $('#seleccionarCliente').select2('destroy');
        }

        $('#vendedor').select2({
            ajax: {
                url: urlVendedores,
                data: function(params) {
                    var clienteSel = document.getElementById('seleccionarCliente');
                    return {
                        search: params.term,
                        type: 'public',
                        page: params.page || 1,
                        cliente_id: clienteSel ? clienteSel.value : null
                    };
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

        // ── Pre-seleccionar vendedor = usuario actual (o Asesor Comercial original si es duplicado) ──
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
        const modo = urlParams.get('modo');
        const autorizacionId = urlParams.get('autorizacion_id');
        const autorizadorId = urlParams.get('autorizador_id');

        let newUrl = '/' + rutaMenu;
        if (from && prefacturaId && flujoId) {
            newUrl += '?from=' + from + '&prefactura_id=' + prefacturaId + '&flujoId=' + flujoId;
            if (modo) newUrl += '&modo=' + encodeURIComponent(modo);
            if (autorizacionId) newUrl += '&autorizacion_id=' + encodeURIComponent(autorizacionId);
            if (autorizadorId) newUrl += '&autorizador_id=' + encodeURIComponent(autorizadorId);
        }
        window.location.href = newUrl;
    }

    // ================================================================
    // BROWSER EVENTS: Pedido vinculado / desvinculado (Livewire → JS)
    // ================================================================
    function aplicarDocumentosComercialesEnFormulario(d) {
        var numeroOrdenEl = document.getElementById('numero_orden_compra');
        var numeroF01El   = document.getElementById('numero_forma_f01');
        var archOrdenEl   = document.getElementById('archivo_orden_compra');
        var archF01El     = document.getElementById('archivo_forma_f01');

        if (numeroOrdenEl) numeroOrdenEl.value = d.numeroOrdenCompra || '';
        if (numeroF01El) numeroF01El.value = d.numeroFormaF01 || '';
        if (archOrdenEl) archOrdenEl.value = d.archivoOrdenCompra || '';
        if (archF01El) archF01El.value = d.archivoFormaF01 || '';

        var previewOrden = document.getElementById('preview_archivo_orden_compra');
        var txtOrden     = document.getElementById('txt_archivo_orden_compra');
        var rutaOrden    = d.archivoOrdenCompra || '';
        if (previewOrden && txtOrden) {
            if (rutaOrden) {
                previewOrden.style.display = 'block';
                txtOrden.textContent = rutaOrden.split('/').pop();
            } else {
                previewOrden.style.display = 'none';
                txtOrden.textContent = '';
            }
        }

        var previewF01 = document.getElementById('preview_archivo_forma_f01');
        var txtF01     = document.getElementById('txt_archivo_forma_f01');
        var rutaF01    = d.archivoFormaF01 || '';
        if (previewF01 && txtF01) {
            if (rutaF01) {
                previewF01.style.display = 'block';
                txtF01.textContent = rutaF01.split('/').pop();
            } else {
                previewF01.style.display = 'none';
                txtF01.textContent = '';
            }
        }
    }

    window.addEventListener('pedido-seleccionado', function(e) {
        var d = e.detail;
        diasCreditoAprobadosFlujo = (d.diasCreditoAprobados === null || typeof d.diasCreditoAprobados === 'undefined')
            ? null
            : Math.max(0, parseInt(d.diasCreditoAprobados, 10) || 0);
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
        bloquearCamposEdicionFactura();

        aplicarDocumentosComercialesEnFormulario(d);

        // Bloquear cliente cuando hay pedido vinculado
        $('#seleccionarCliente').prop('disabled', true);
    });

    window.addEventListener('pedido-desvinculado', function(e) {
        var d = e.detail;
        diasCreditoAprobadosFlujo = null;
        diasCredito = 0;
        // Habilitar cliente nuevamente
        $('#seleccionarCliente').prop('disabled', false);
        // Limpiar cliente
        $('#seleccionarCliente').empty().append('<option value="" selected disabled>--Seleccionar un cliente--</option>').trigger('change');
        document.getElementById('nombre_cliente_ventas').value = '';
        document.getElementById('rtn_ventas').value = '';

        aplicarDocumentosComercialesEnFormulario({});

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
        var btnSolicitar = document.getElementById('btnSolicitarCodigo');
        if (btnSolicitar) btnSolicitar.disabled = true;
        var flujoIdSR   = document.getElementById('flujo_vinculado_id')?.value || '';
        var numVentaSR  = document.getElementById('numero_venta')?.value || '';
        axios.post('/ventas/solicitud/codigo', {
            productos:    window._srProductos || [],
            flujo_id:     flujoIdSR,
            numero_venta: numVentaSR,
        })
            .then(response => {
                $('#modal_sr_autorizacion').removeClass('fade').modal('hide');
                document.getElementById('codigo').value = '';
                document.getElementById('mensajeCodigo').classList.add('d-none');
                $('#modalPermiso').modal('show').addClass('fade');
            })
            .catch(err => {
                console.log(err);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al solicitar código' });
            })
            .finally(function() {
                if (btnSolicitar) btnSolicitar.disabled = false;
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
                    $('#modalPermiso').removeClass('fade').modal('hide');
                    document.getElementById('mensajeCodigo').classList.add('d-none');
                    document.getElementById('codigo_autorizacion').value = data.idAutorizacion;
                    // Proceder automáticamente con el guardado
                    guardarVenta();
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
        $('#categoria_cliente_venta_id').empty()
            .append('<option value="" selected disabled>--Seleccione primero un producto--</option>')
            .prop('disabled', true);
        $('#bodega').empty()
            .append('<option value="" selected disabled>--Seleccione una categoría primero--</option>')
            .prop('disabled', true);
        document.getElementById('botonAdd').classList.add('d-none');
        document.getElementById('bloqueImagenes').innerHTML = '';
        document.getElementById('historialPreciosPanel').querySelector('#historialPreciosCuerpo').innerHTML =
            '<p class="mb-0 text-muted small">Sin ventas previas de este producto a este cliente.</p>';
        document.getElementById('historialPreciosPanel').classList.remove('d-none');
    }

    function prepararNuevaBusquedaProducto(valorActual) {
        if (!document.getElementById('seleccionarProducto').value) return;
        limpiarProducto();
        document.getElementById('codigoProductoBuscar').value = valorActual || '';
    }

    function manejarFocoBusquedaProducto() {
        if (!omitirLimpiezaFocoProducto) prepararNuevaBusquedaProducto('');
    }

    function enfocarBusquedaProducto() {
        var campo = document.getElementById('codigoProductoBuscar');
        omitirLimpiezaFocoProducto = true;
        campo.focus();
        omitirLimpiezaFocoProducto = false;
    }

    function reiniciarCapturaProducto() {
        secuenciaBusquedaProducto++;
        limpiarProducto();
        enfocarBusquedaProducto();
    }

    function abrirBusquedaProductoActual() {
        var campo = document.getElementById('codigoProductoBuscar');
        var termino = campo.value.trim();
        prepararNuevaBusquedaProducto(termino);
        window['abrirBuscador_buscadorProductoUnificado'](termino);
    }

    function alSeleccionarProducto(producto) {
        if (seleccionandoProductoCotizadorExpo) {
            seleccionandoProductoCotizadorExpo = false;
            cotizarDescuentosProductoExpo(producto);
            return;
        }
        productoExpoAgregandoAutomaticamente = null;
        datosProductoExpoPrecargados = null;
        bodegaExpoCapturaRapida = null;
        var select = document.getElementById('seleccionarProducto');
        select.innerHTML = '<option value="' + producto.id + '" selected>' + producto.nombre + '</option>';
        var campoBusqueda = document.getElementById('codigoProductoBuscar');
        campoBusqueda.value = '';
        var label = document.getElementById('productoSeleccionadoLabel');
        label.textContent = '✓ ' + producto.nombre + ' (ID: ' + producto.id + ')';
        label.classList.remove('d-none');
        cargarCategoriasProducto();
        enfocarBusquedaProducto();
    }

    function buscarPorCodigo(cod) {
        cod = String(cod).trim();
        if (!cod) { window['abrirBuscador_buscadorProductoUnificado'](''); return; }
        if (esCapturaRapidaExpo()) {
            capturarProductoExpoPorCodigo(cod);
            return;
        }
        var secuenciaActual = ++secuenciaBusquedaProducto;
        axios.get('/productos/buscar', { params: { q: cod, page: 1 } })
            .then(function(r) {
                if (secuenciaActual !== secuenciaBusquedaProducto) return;
                var items = r.data.data || [];
                var exact = items.find(function(p) {
                    return String(p.id) === cod ||
                        String(p.codigo_barra || '').trim() === cod ||
                        String(p.codigo_estatal || '').trim() === cod;
                });
                if (exact) { alSeleccionarProducto(exact); }
                else if (items.length === 1) { alSeleccionarProducto(items[0]); }
                else { window['abrirBuscador_buscadorProductoUnificado'](cod); }
            })
            .catch(function() {
                if (secuenciaActual !== secuenciaBusquedaProducto) return;
                window['abrirBuscador_buscadorProductoUnificado'](cod);
            });
    }

    $(document).on('hidden.bs.modal', '#buscadorProductoUnificado', function() {
        if ($('#modalCotizadorDescuentosExpo').hasClass('show')) {
            $('body').addClass('modal-open');
            document.getElementById('cotizadorExpoCodigoProducto')?.focus();
            return;
        }
        enfocarBusquedaProducto();
    });

    // ================================================================
    // CLIENTE
    // ================================================================
    // ================================================================
    // ASESOR COMERCIAL ASIGNADO (solo modo "Nueva Oferta" / cotizacion_clientes_a)
    // Carga únicamente los asesores comerciales asignados al cliente en la cartera.
    // ================================================================
    function aplicarAsesorAsignado(idCliente, asesorPreferido) {
        if (!idCliente) return Promise.resolve();
        if (modoEditarFactura) {
            bloquearCamposEdicionFactura();
            return Promise.resolve();
        }

        return axios.post('/cotizacion/asesor-asignado', { cliente_id: idCliente })
            .then(response => {
                var data = response.data;
                var vendedorSelect = $('#vendedor');
                var asesores = data.asesores || [];
                vendedorSelect.empty();

                if (asesores.length === 0) {
                    vendedorSelect.append(new Option('Sin asesores asignados en cartera', '', true, false));
                } else {
                    if (asesores.length > 1) {
                        vendedorSelect.append(new Option('-- Seleccionar asesor --', '', true, false));
                    }
                    asesores.forEach(function(asesor) {
                        var seleccionado = String(asesor.id) === String(asesorPreferido)
                            || (asesores.length === 1 && !asesorPreferido);
                        vendedorSelect.append(new Option(asesor.text, asesor.id, seleccionado, seleccionado));
                    });
                }

                vendedorSelect.prop('disabled', asesores.length <= 1).trigger('change');
                bloquearCamposEdicionFactura();
            })
            .catch(err => {
                console.log(err);
            });
    }

    function obtenerDatosCliente() {
        let idCliente = document.getElementById("seleccionarCliente").value;
        if (!idCliente) return; // Evitar error al desvincular pedido
        var urlDatosCliente = urls.datos_cliente;

        aplicarAsesorAsignado(idCliente);

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
                    $('#categoria_cliente_venta_id').data('categoria-precio-id', data.categoria_precios_id || null);

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
                    $('#categoria_cliente_venta_id').data('categoria-precio-id', data.categoria_precios_id || null);

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
                calcularTotalesInicioPagina();
                recalcularCotizadorDescuentosExpo();
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

                let selPago = document.getElementById('tipoPagoVenta');

                // Si es duplicado de oferta, pre-seleccionar el tipo de pago original;
                // si no, auto-seleccionar "Contado" por defecto
                if (diasCreditoAprobadosFlujo !== null) {
                    var tipoPagoAprobado = diasCreditoAprobadosFlujo > 0 ? 2 : 1;
                    for (let i = 0; i < selPago.options.length; i++) {
                        if (parseInt(selPago.options[i].value, 10) === tipoPagoAprobado) {
                            selPago.selectedIndex = i;
                            break;
                        }
                    }
                    diasCredito = diasCreditoAprobadosFlujo;
                } else if (_ofertaDuplicada && _ofertaDuplicada.tipo_pago_id) {
                    for (let i = 0; i < selPago.options.length; i++) {
                        if (selPago.options[i].value == _ofertaDuplicada.tipo_pago_id) {
                            selPago.selectedIndex = i;
                            break;
                        }
                    }
                } else {
                    for (let i = 0; i < selPago.options.length; i++) {
                        if (selPago.options[i].text.toLowerCase().includes('contado')) {
                            selPago.selectedIndex = i;
                            break;
                        }
                    }
                }

                validarFechaPago();
                bloquearCamposEdicionFactura();

                // Pre-llenar campos adicionales de la oferta duplicada
                if (_ofertaDuplicada) {
                    // Mantener el vencimiento calculado desde el cliente seleccionado.
                    // El plazo del documento original no debe imponerse sobre el crédito actual.
                    if (selPago.value == 2) {
                        sumarDiasCredito();
                    }
                    // Descuento
                    if (_ofertaDuplicada.porc_descuento) {
                        document.getElementById('porDescuento').value = _ofertaDuplicada.porc_descuento;
                        calcularTotalesInicioPagina();
                    }
                    // Nota
                    if (_ofertaDuplicada.nota) {
                        document.getElementById('nota_comen').value = _ofertaDuplicada.nota;
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
        let categoriaEscalaId = $('#categoria_cliente_venta_id').data('categoria-cliente-id') || null;

        if (!productoId) {
            $('#categoria_cliente_venta_id').empty().append('<option value="" selected disabled>--Seleccione primero un producto--</option>');
            return;
        }

        $('#categoria_cliente_venta_id').empty().append('<option value="" selected disabled>Cargando categorías...</option>');

        if (esCapturaRapidaExpo()) {
            cargarProductoExpoCapturaRapida(productoId);
            return;
        }

        axios.post('/producto/categorias-disponibles', {
            producto_id: productoId,
            cliente_categoria_escala_id: categoriaEscalaId,
            expo_id: expoConfig ? expoConfig.id : null
        })
            .then(response => {
                if (String($('#seleccionarProducto').val() || '') !== String(productoId)) return;
                let categorias = response.data.categorias;
                if (categorias.length > 0) {
                    categorias.sort((a, b) => (parseFloat(b.precio_a) || 0) - (parseFloat(a.precio_a) || 0));

                    $('#categoria_cliente_venta_id').empty().append('<option value="" selected disabled>--Seleccione una categoría--</option>');

                    // Usar el tier específico del cliente si existe, si no el de mayor precio
                    let categoriaPrecioId = $('#categoria_cliente_venta_id').data('categoria-precio-id') || null;
                    let haySeleccionada = false;

                    categorias.forEach((categoria, index) => {
                        let precio = parseFloat(categoria.precio_a) || 0;
                        let precioFormateado = new Intl.NumberFormat('es-HN', {
                            style: 'currency', currency: 'HNL', minimumFractionDigits: 2
                        }).format(precio);
                        let textoOpcion = categoria.nombre_categoria + ' - ' + precioFormateado;
                        // Seleccionar el tier asignado al cliente; fallback: mayor precio_a
                        let isSelected = categoriaPrecioId
                            ? (categoria.id == categoriaPrecioId)
                            : (index === 0);
                        if (isSelected) haySeleccionada = true;
                        let option = new Option(textoOpcion, categoria.id, isSelected, isSelected);
                        $('#categoria_cliente_venta_id').append(option);
                    });
                    // Si el tier del cliente no estaba en la lista, seleccionar el primero
                    if (!haySeleccionada && categorias.length > 0) {
                        $('#categoria_cliente_venta_id option:nth-child(2)').prop('selected', true);
                    }
                    $('#categoria_cliente_venta_id').prop('disabled', false);
                    precargarDatosProductoExpo(productoId);
                    intentarAgregarProductoExpoAutomaticamente();

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

        if (!panel || !cuerpo) return;

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
        document.getElementById('bodega').innerHTML = "<option selected disabled>--Cargando bodega--</option>";
        $('#bodega').prop('disabled', false);
        var urlBase = filtrarProductosExpo
            ? '/expo/oferta/listar-bodega/{idProducto}'
            : urls.bodegas;
        var url = urlBase.replace('{idProducto}', id);

        function parametrosBodega(params) {
            var _urlParams = new URLSearchParams(window.location.search);
            var _modo = _urlParams.get('modo') || '';
            var _flujoId = _urlParams.get('flujoId') || document.getElementById('flujo_vinculado_id')?.value || '';
            var _prefacturaId = _urlParams.get('prefactura_id') || document.getElementById('prefactura_vinculada_id')?.value || '';
            return {
                search: params?.term || '',
                type: 'public',
                page: params?.page || 1,
                idProducto: id,
                flujo_id: _flujoId,
                modo: _modo,
                prefactura_id: _prefacturaId,
                permitir_sin_existencia: codigoActual === 'cotizacion_clientes_a' && !filtrarProductosExpo ? 1 : 0,
                expo_id: filtrarProductosExpo && expoConfig ? expoConfig.id : null
            };
        }

        $('#bodega').select2({
            ajax: {
                url: url,
                data: parametrosBodega
            }
        });

        if (filtrarProductosExpo) {
            axios.get(url, { params: parametrosBodega() })
                .then(function(response) {
                    if (String($('#seleccionarProducto').val() || '') !== String(id)) return;
                    var primeraBodega = response.data?.results?.[0] || null;
                    $('#bodega').empty();

                    if (!primeraBodega) {
                        $('#bodega').append(new Option('No hay existencia en las bodegas de la Expo', '', true, true));
                        $('#bodega').prop('disabled', true);
                        document.getElementById('botonAdd').classList.add('d-none');
                        return;
                    }

                    var opcion = new Option(primeraBodega.text, primeraBodega.id, true, true);
                    $(opcion).data('data', primeraBodega);
                    $('#bodega').append(opcion).trigger('change');
                    if ($('#categoria_cliente_venta_id').val()) {
                        prueba();
                        intentarAgregarProductoExpoAutomaticamente();
                    }
                })
                .catch(function() {
                    $('#bodega').empty().append(new Option('No se pudo cargar la bodega', '', true, true));
                    document.getElementById('botonAdd').classList.add('d-none');
                });
        }
    }

    function intentarAgregarProductoExpoAutomaticamente() {
        if (!esCapturaRapidaExpo()) return;

        var productoId = String($('#seleccionarProducto').val() || '');
        var categoriaId = String($('#categoria_cliente_venta_id').val() || '');
        var bodega = $('#bodega').hasClass('select2-hidden-accessible')
            ? $('#bodega').select2('data')[0]
            : bodegaExpoCapturaRapida;

        if (!productoId || !categoriaId || !bodega || bodega.idBodega === undefined || !bodega.id) return;

        var clave = productoId + '|' + categoriaId + '|' + bodega.idBodega + '|' + bodega.id;
        if (productoExpoAgregandoAutomaticamente === clave) return;

        productoExpoAgregandoAutomaticamente = clave;
        document.getElementById('botonAdd').classList.add('d-none');
        agregarProductoCarrito(bodega, clave);
    }

    function esCapturaRapidaExpo() {
        return filtrarProductosExpo && expoConfig && expoConfig.bodegas.length > 0;
    }

    function cargarProductoExpoCapturaRapida(productoId) {
        var categoriaPreferidaId = $('#categoria_cliente_venta_id').data('categoria-precio-id') || null;
        axios.get('/expo/captura-rapida/producto/' + productoId, {
            params: {
                expo_id: expoConfig.id,
                categoria_precio_id: categoriaPreferidaId
            }
        }).then(function(response) {
            if (String($('#seleccionarProducto').val() || '') !== String(productoId)) return;

            procesarProductoExpoCapturaRapida(response.data, productoId);
        }).catch(mostrarErrorCapturaRapidaExpo);
    }

    function capturarProductoExpoPorCodigo(codigo) {
        var secuenciaActual = ++secuenciaBusquedaProducto;
        var categoriaPreferidaId = $('#categoria_cliente_venta_id').data('categoria-precio-id') || null;
        axios.get('/expo/captura-rapida/producto/' + encodeURIComponent(codigo), {
            params: {
                expo_id: expoConfig.id,
                categoria_precio_id: categoriaPreferidaId
            }
        }).then(function(response) {
            if (secuenciaActual !== secuenciaBusquedaProducto) return;
            var producto = response.data.producto;
            productoExpoAgregandoAutomaticamente = null;
            datosProductoExpoPrecargados = null;
            bodegaExpoCapturaRapida = null;
            document.getElementById('seleccionarProducto').innerHTML = '<option value="' + producto.id + '" selected>' + producto.nombre + '</option>';
            document.getElementById('productoSeleccionadoLabel').textContent = '✓ ' + producto.nombre;
            document.getElementById('productoSeleccionadoLabel').classList.remove('d-none');
            procesarProductoExpoCapturaRapida(response.data, producto.id);
        }).catch(mostrarErrorCapturaRapidaExpo);
    }

    function procesarProductoExpoCapturaRapida(datos, productoId) {
            var response = { data: datos };

            var categorias = response.data.categorias || [];
            var categoriaSeleccionadaId = String(response.data.categoria_id || '');
            $('#categoria_cliente_venta_id').empty();
            categorias.forEach(function(categoria) {
                var precio = Number(categoria.precio_a || 0).toLocaleString('es-HN', {
                    style: 'currency', currency: 'HNL', minimumFractionDigits: 2
                });
                var seleccionada = String(categoria.id) === categoriaSeleccionadaId;
                $('#categoria_cliente_venta_id').append(new Option(
                    categoria.nombre_categoria + ' - ' + precio,
                    categoria.id,
                    seleccionada,
                    seleccionada
                ));
            });
            $('#categoria_cliente_venta_id').prop('disabled', false);

            var bodega = response.data.bodega;
            bodegaExpoCapturaRapida = bodega;
            var opcion = new Option(bodega.text, bodega.id, true, true);
            $(opcion).data('data', bodega);
            $('#bodega').empty().append(opcion).trigger('change');

            datosProductoExpoPrecargados = {
                clave: String(productoId) + '|' + categoriaSeleccionadaId,
                promesa: Promise.resolve({
                    data: {
                        producto: response.data.producto,
                        unidades: response.data.unidades
                    }
                })
            };
            intentarAgregarProductoExpoAutomaticamente();
    }

    function mostrarErrorCapturaRapidaExpo(error) {
        document.getElementById('botonAdd').classList.add('d-none');
        Swal.fire({
            icon: 'warning',
            title: 'Producto no disponible',
            text: error.response?.data?.message || 'No se pudo preparar el producto para la Oferta Expo.'
        });
    }

    function precargarDatosProductoExpo(productoId) {
        if (!esCapturaRapidaExpo()) return;
        var categoriaId = String($('#categoria_cliente_venta_id').val() || '');
        if (!productoId || !categoriaId) return;

        var clave = String(productoId) + '|' + categoriaId;
        datosProductoExpoPrecargados = {
            clave: clave,
            promesa: axios.post(urls.datos_producto, {
                idProducto: productoId,
                categoria_cliente_venta_id: categoriaId
            })
        };
    }

    // ================================================================
    // IMÁGENES
    // ================================================================
    function obtenerImagenes() {
        let id = document.getElementById('seleccionarProducto').value;
        let htmlImagenes = '';
        axios.post('/producto/listar/imagenes', { id: id })
            .then(response => {
                if (String(document.getElementById('seleccionarProducto').value || '') !== String(id)) return;
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
                            '<img class="d-block" src="' + public_path + '/' + element.url_img + '" alt="imagen ' + element.contador + '" onerror="this.onerror=null;this.src=\'/img/no-image.png\';" style="width:100%;height:220px;object-fit:contain;cursor:pointer;"></a></div>';
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
    function agregarProductoCarrito(bodegaExpo, claveAgregadoAutomatico) {
        let idProducto = document.getElementById('seleccionarProducto').value;
        let categoria_cliente_venta_id = document.getElementById('categoria_cliente_venta_id').value;
        let data = bodegaExpo || $("#bodega").select2('data')[0];
        if (!data) {
            Swal.fire({ icon: 'warning', title: 'Advertencia', text: 'Seleccione una bodega.' });
            return;
        }
        let esSinExistencia = !!(data && (data.esSinExistencia || data.id === 'sin_existencia'));
        let bodega = esSinExistencia ? 'SIN EXISTENCIA' : data.bodegaSeccion;
        let idBodega = esSinExistencia ? '' : data.idBodega;
        let idSeccion = esSinExistencia ? '' : data.id;

        var urlDatosProducto = urls.datos_producto;
        var claveDatosProducto = String(idProducto) + '|' + String(categoria_cliente_venta_id);
        var solicitudDatosProducto = datosProductoExpoPrecargados?.clave === claveDatosProducto
            ? datosProductoExpoPrecargados.promesa
            : axios.post(urlDatosProducto, {
                idProducto: idProducto,
                categoria_cliente_venta_id: categoria_cliente_venta_id
            });

        solicitudDatosProducto
            .then(response => {
                // Verificar duplicados
                let flag = false;
                arregloIdInputs.forEach(idInpunt => {
                    let idProductoFila = document.getElementById("idProducto" + idInpunt).value;
                    let idSeccionFila = document.getElementById("idSeccion" + idInpunt).value;
                    let sinExistenciaFila = (document.getElementById("sinExistencia" + idInpunt)?.value || '0') === '1';
                    if (esSinExistencia) {
                        if (idProducto == idProductoFila && sinExistenciaFila && !flag) flag = true;
                    } else if (idProducto == idProductoFila && idSeccion == idSeccionFila && !flag) {
                        flag = true;
                    }
                });

                if (flag) {
                    if (productoExpoAgregandoAutomaticamente === claveAgregadoAutomatico) {
                        productoExpoAgregandoAutomaticamente = null;
                    }
                    Swal.fire({
                        icon: 'warning', title: 'Advertencia!',
                        html: '<p class="text-left">La sección de bodega y producto ha sido agregada anteriormente.<br><br>Por favor verificar la sección de bodega y producto sea distinto a los ya existentes.</p>'
                    });
                    return;
                }

                let producto = response.data.producto;
                let arrayUnidades = response.data.unidades;
                let bodegaBadgeBg = esSinExistencia ? '#ffebee' : '#e3f2fd';
                let bodegaBadgeColor = esSinExistencia ? '#c62828' : '#1565c0';
                let bodegaBadgeIcon = esSinExistencia ? 'fa-exclamation-circle' : 'fa-archive';
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

                // Precio de referencia de la escala seleccionada. NO se usa para bloquear la escritura
                // (una Oferta debe poder guardarse con un valor menor); se valida al enviar el formulario
                // en las facturas con restricción — ver validarPrecioEscalaAntesDeGuardar().
                let precioEscalaRef = producto.precio1;

                let html = `
                <tr id='${numeroInputs}'>
                    <td style="vertical-align:middle; text-align:center; padding:4px 6px;">
                        <input id="idProducto${numeroInputs}" name="idProducto${numeroInputs}" type="hidden" value="${producto.id}">
                        <input id="marcaExpoId${numeroInputs}" type="hidden" value="${producto.marca_id || 0}">
                        <input id="marcaExpoNombre${numeroInputs}" type="hidden" value="${producto.marca || 'SIN MARCA'}">
                        <input id="precios_producto_carga_id${numeroInputs}" name="precios_producto_carga_id${numeroInputs}" type="hidden" value="${producto.precios_producto_carga_id || ''}">
                        <input id="isv${numeroInputs}" name="isv${numeroInputs}" type="hidden" value="${producto.isv}">
                        <input id="idBodega${numeroInputs}" name="idBodega${numeroInputs}" type="hidden" value="${idBodega}">
                        <input id="idSeccion${numeroInputs}" name="idSeccion${numeroInputs}" type="hidden" value="${idSeccion}">
                        <input id="sinExistencia${numeroInputs}" name="sinExistencia${numeroInputs}" type="hidden" value="${esSinExistencia ? 1 : 0}">
                        <input id="restaInventario${numeroInputs}" name="restaInventario${numeroInputs}" type="hidden" value="${esSinExistencia ? 0 : ''}">
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
                        <span style="background:${bodegaBadgeBg}; color:${bodegaBadgeColor}; border-radius:6px; padding:2px 8px; font-size:11px; font-weight:700;">
                            <i class="fa ${bodegaBadgeIcon}" style="font-size:10px;"></i> ${bodega}
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
                            data-precio-escala="${precioEscalaRef}" data-parsley-required step="any" autocomplete="off" style="min-width:80px; font-size:11px;"
                            oninput="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <input type="number" id="cantidad${numeroInputs}" name="cantidad${numeroInputs}" class="form-control form-control-sm" min="1" step="any" inputmode="decimal" data-parsley-required autocomplete="off" style="min-width:60px; font-size:11px;"
                            oninput="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <select class="form-control form-control-sm" name="unidad${numeroInputs}" id="unidad${numeroInputs}" data-parsley-required style="font-size:11px; min-width:80px;"
                            onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                            ${htmlSelectUnidades}
                        </select>
                    </td>
                    ${expoConfig ? `<td style="vertical-align:middle; padding:4px 6px;"><div id="descuentoExpoProducto${numeroInputs}" style="font-size:10px; line-height:1.35;"></div></td>` : ''}
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
                actualizarContadorCarrito();
                reiniciarCapturaProducto();
                programarGuardadoTemporal();
                enfocarCantidadCarrito(numeroInputs);
            })
            .catch(err => {
                if (productoExpoAgregandoAutomaticamente === claveAgregadoAutomatico) {
                    productoExpoAgregandoAutomaticamente = null;
                }
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
        let calculoExpo = calcularDescuentosCarritoExpo();
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

                if (calculoExpo?.lineas[id]) {
                    subTotal = calculoExpo.lineas[id].subtotalNeto;
                    descuentoCalculado = calculoExpo.lineas[id].descuentoTotal;
                    isv = calculoExpo.lineas[id].isv;
                    total = calculoExpo.lineas[id].total;
                } else if (descuento > 0) {
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
        let esSinExistencia = (document.getElementById('sinExistencia' + id)?.value || '0') === '1';

        // Si no aplica ISV, forzar a 0
        if (tipoFacturaConfig && !tipoFacturaConfig.aplica_isv) {
            isvProducto = 0;
        }

        if (esSinExistencia) {
            idRestaInventario.value = 0;
        } else {
            idRestaInventario.value = valorInputCantidad * valorSelectUnidad;
        }

        if (expoConfig) {
            calcularTotalesInicioPagina();
            idPrecio.value = valorInputPrecio;
            actualizarContadorCarrito();
            return;
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

            this.totalesGenerales();
        }

        idPrecio.value = valorInputPrecio;
        actualizarContadorCarrito();
    }

    function formatoMoneda(valor) {
        return new Intl.NumberFormat('es-HN', {
            style: 'currency', currency: 'HNL', minimumFractionDigits: 2
        }).format(valor);
    }

    function enfocarCantidadCarrito(id) {
        window.setTimeout(function() {
            var cantidad = document.getElementById('cantidad' + id);
            if (!cantidad) return;
            cantidad.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
            cantidad.focus({ preventScroll: true });
            cantidad.select();
        }, 80);
    }

    function normalizarFilasCarritoExpo() {
        if (!expoConfig) return;
        var categoriaId = document.getElementById('categoria_cliente_venta_id')?.value || '';
        var consultasMarca = [];

        arregloIdInputs.forEach(function(id) {
            var cantidad = document.getElementById('cantidad' + id);
            if (cantidad) {
                cantidad.setAttribute('inputmode', 'decimal');
                cantidad.setAttribute('step', 'any');
                if (!cantidad.dataset.expoInputActivo) {
                    cantidad.dataset.expoInputActivo = '1';
                    cantidad.addEventListener('input', function() {
                        calcularTotales(
                            document.getElementById('precio' + id),
                            cantidad,
                            Number(document.getElementById('isv' + id)?.value || 0),
                            document.getElementById('unidad' + id),
                            id,
                            document.getElementById('restaInventario' + id)
                        );
                    });
                }
            }

            var campoSubtotal = document.getElementById('subTotalMostrar' + id);
            var celdaSubtotal = campoSubtotal ? campoSubtotal.closest('td') : null;
            var filaProducto = celdaSubtotal ? celdaSubtotal.parentNode : null;
            ['descuentoExpoMarcaProducto' + id, 'descuentoExpoSubtotalProducto' + id].forEach(function(legacyId) {
                var legacy = document.getElementById(legacyId);
                if (legacy?.closest('td')) legacy.closest('td').remove();
            });

            var indicadores = filaProducto
                ? Array.from(filaProducto.querySelectorAll('[id="descuentoExpoProducto' + id + '"]'))
                : [];
            indicadores.slice(1).forEach(function(duplicado) {
                if (duplicado.closest('td')) duplicado.closest('td').remove();
            });
            var indicador = indicadores[0] || null;
            if (!indicador && celdaSubtotal && filaProducto) {
                    var celdaDescuento = document.createElement('td');
                    celdaDescuento.style.cssText = 'vertical-align:middle; padding:4px 6px;';
                    celdaDescuento.innerHTML = '<div id="descuentoExpoProducto' + id + '" style="font-size:10px; line-height:1.35;"></div>';
                    filaProducto.insertBefore(celdaDescuento, celdaSubtotal);
                    indicador = celdaDescuento.firstElementChild;
            } else if (indicador && celdaSubtotal && indicador.closest('td')?.nextElementSibling !== celdaSubtotal) {
                filaProducto.insertBefore(indicador.closest('td'), celdaSubtotal);
            }

            var marca = document.getElementById('marcaExpoId' + id);
            var marcaNombre = document.getElementById('marcaExpoNombre' + id);
            if (!marca) {
                marca = document.createElement('input');
                marca.type = 'hidden';
                marca.id = 'marcaExpoId' + id;
                marca.value = '0';
                var producto = document.getElementById('idProducto' + id);
                if (producto?.parentNode) producto.parentNode.insertBefore(marca, producto.nextSibling);

                marcaNombre = document.createElement('input');
                marcaNombre.type = 'hidden';
                marcaNombre.id = 'marcaExpoNombre' + id;
                marcaNombre.value = 'SIN MARCA';
                if (marca.parentNode) marca.parentNode.insertBefore(marcaNombre, marca.nextSibling);

                var precioCargaId = document.getElementById('precios_producto_carga_id' + id)?.value || null;
                consultasMarca.push(axios.post(urls.datos_producto, {
                    idProducto: producto?.value,
                    categoria_cliente_venta_id: categoriaId,
                    precios_producto_carga_id: precioCargaId
                }).then(function(response) {
                    marca.value = response.data.producto?.marca_id || 0;
                    marcaNombre.value = response.data.producto?.marca || 'SIN MARCA';
                }).catch(function() {
                    marca.value = 0;
                }));
            }
        });

        calcularTotalesInicioPagina();
        if (consultasMarca.length > 0) {
            Promise.allSettled(consultasMarca).then(calcularTotalesInicioPagina);
        }
    }

    function abrirCotizadorDescuentosExpo() {
        productoCotizadorExpo = null;
        datosCalculoCotizadorExpo = null;
        document.getElementById('cotizadorExpoCodigoProducto').value = '';
        document.getElementById('cotizadorExpoProductoLabel').textContent = '';
        document.getElementById('cotizadorExpoProductoLabel').classList.add('d-none');
        document.getElementById('cotizadorExpoEscala').innerHTML = '<option value="">Seleccione un producto</option>';
        document.getElementById('cotizadorExpoEscala').disabled = true;
        document.getElementById('cotizadorExpoResultado').innerHTML = '<div class="text-center text-muted py-4"><i class="fa fa-tags fa-2x mb-2 d-block"></i>Seleccione un producto para consultar sus descuentos.</div>';
        $('#modalCotizadorDescuentosExpo').modal('show');
    }

    function abrirBusquedaProductoCotizadorExpo() {
        var termino = document.getElementById('cotizadorExpoCodigoProducto').value.trim();
        seleccionandoProductoCotizadorExpo = true;
        window['abrirBuscador_buscadorProductoUnificado'](termino);
    }

    function prepararNuevaBusquedaCotizadorExpo(valorActual) {
        if (!productoCotizadorExpo) return;
        productoCotizadorExpo = null;
        datosCalculoCotizadorExpo = null;
        document.getElementById('cotizadorExpoProductoLabel').textContent = '';
        document.getElementById('cotizadorExpoProductoLabel').classList.add('d-none');
        document.getElementById('cotizadorExpoEscala').innerHTML = '<option value="">Seleccione un producto</option>';
        document.getElementById('cotizadorExpoEscala').disabled = true;
        document.getElementById('cotizadorExpoResultado').innerHTML = '<div class="text-center text-muted py-4"><i class="fa fa-tags fa-2x mb-2 d-block"></i>Presione Enter para buscar el producto.</div>';
    }

    function buscarProductoCotizadorExpo(codigo) {
        codigo = String(codigo || '').trim();
        if (!codigo) {
            abrirBusquedaProductoCotizadorExpo();
            return;
        }

        axios.get('/productos/buscar', { params: { q: codigo, page: 1, expo_id: expoConfig?.id } }).then(function(response) {
            var productos = response.data.data || [];
            var producto = productos.find(function(item) {
                return String(item.id) === codigo
                    || String(item.codigo_barra || '').trim() === codigo
                    || String(item.codigo_estatal || '').trim() === codigo;
            });
            if (producto || productos.length === 1) {
                cotizarDescuentosProductoExpo(producto || productos[0]);
                return;
            }
            abrirBusquedaProductoCotizadorExpo();
        }).catch(abrirBusquedaProductoCotizadorExpo);
    }

    function recalcularCotizadorDescuentosExpo() {
        if (productoCotizadorExpo) cotizarDescuentosProductoExpo(productoCotizadorExpo);
    }

    function cotizarDescuentosProductoExpo(productoSeleccionado) {
        var productoId = productoSeleccionado?.id;
        var selectorEscala = document.getElementById('cotizadorExpoEscala');
        var resultado = document.getElementById('cotizadorExpoResultado');
        if (!productoId || !selectorEscala || !resultado) return;

        var mismoProducto = Number(productoCotizadorExpo?.id || 0) === Number(productoId);
        var categoriaPreferidaId = mismoProducto ? selectorEscala.value : null;

        productoCotizadorExpo = productoSeleccionado;
        document.getElementById('cotizadorExpoCodigoProducto').value = '';
        var productoLabel = document.getElementById('cotizadorExpoProductoLabel');
        productoLabel.textContent = (productoSeleccionado.nombre || 'Producto') + ' (ID: ' + productoId + ')';
        productoLabel.classList.remove('d-none');
        selectorEscala.innerHTML = '<option value="">Consultando escalas...</option>';
        selectorEscala.disabled = true;
        resultado.innerHTML = '<div class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin mr-1"></i> Calculando descuentos...</div>';
        axios.get('/expo/captura-rapida/producto/' + encodeURIComponent(productoId), {
            params: {
                expo_id: expoConfig.id,
                categoria_precio_id: categoriaPreferidaId
            }
        }).then(function(response) {
            var categorias = Array.isArray(response.data.categorias) ? response.data.categorias : [];
            selectorEscala.innerHTML = '';
            categorias.forEach(function(categoria) {
                selectorEscala.add(new Option(categoria.nombre_categoria, categoria.id));
            });
            selectorEscala.value = String(response.data.categoria_id || '');
            selectorEscala.disabled = categorias.length <= 1;

            var categoriaId = selectorEscala.value;
            var producto = response.data.producto || {};
            var precio = Number(producto.precio1 || 0);
            var porcentajeIsv = Number(producto.isv || 0);
            var generales = Array.isArray(expoConfig?.descuentos) ? expoConfig.descuentos : [];
            var clienteExpoId = Number(document.getElementById('seleccionarCliente')?.value || 0);
            var asistentesExpo = Array.isArray(expoConfig?.clientes_asistentes) ? expoConfig.clientes_asistentes.map(Number) : [];
            var reglasMarcaElegibles = (Array.isArray(expoConfig?.descuentos_marca) ? expoConfig.descuentos_marca : [])
                .filter(function(regla) {
                    return !regla.requiere_asistencia || asistentesExpo.includes(clienteExpoId);
                });
            var reglasMarcaProducto = reglasMarcaElegibles
                .filter(function(regla) { return Number(regla.marca_id) === Number(producto.marca_id); });
            var umbrales = generales.map(function(regla) { return Number(regla.venta_minima || 0); });
            reglasMarcaProducto.forEach(function(regla) {
                umbrales.push(Number(regla.venta_minima || 0));
            });
            umbrales = Array.from(new Set(umbrales.filter(function(valor) { return valor > 0; }))).sort(function(a, b) { return a - b; });

            if (!(precio > 0) || umbrales.length === 0) {
                resultado.innerHTML = '<div class="alert alert-info mb-0">Este producto no tiene escenarios de descuento disponibles en la Expo.</div>';
                return;
            }

            datosCalculoCotizadorExpo = {
                precio: precio,
                porcentajeIsv: porcentajeIsv,
                generales: generales,
                reglasMarca: reglasMarcaProducto
            };

            var filas = umbrales.map(function(umbral, indice) {
                var cantidad = Math.max(1, Math.ceil((umbral - 0.005) / precio));
                return '<tr data-cotizador-fila="' + indice + '"><td style="min-width:105px;">'
                    + '<input type="number" min="1" step="1" inputmode="numeric" value="' + cantidad + '" class="form-control form-control-sm text-center cotizador-expo-cantidad" oninput="recalcularFilaCotizadorExpo(this)"></td>'
                    + '<td class="text-right" data-campo="precio"></td>'
                    + '<td class="text-right" data-campo="compra"></td>'
                    + '<td class="text-center" data-campo="marca"></td>'
                    + '<td class="text-center" data-campo="general"></td>'
                    + '<td class="text-right" data-campo="final-sin-isv"></td>'
                    + '<td class="text-right" data-campo="isv"></td>'
                    + '<td class="text-right" data-campo="final"></td>'
                    + '<td class="text-right" data-campo="ahorro"></td>'
                    + '<td class="text-right" data-campo="ahorro-total"></td></tr>';
            }).join('');

                var nombreEscala = selectorEscala.options[selectorEscala.selectedIndex]?.text || ('Escala ' + categoriaId);
                resultado.innerHTML = '<div class="cotizador-expo-resumen"><strong>' + $('<div>').text(producto.nombre || 'Producto').html() + '</strong><br>'
                    + '<small><i class="fa fa-tag mr-1 text-success"></i>Marca: ' + $('<div>').text(producto.marca || 'SIN MARCA').html()
                    + ' &nbsp; <i class="fa fa-list-alt mr-1 text-success"></i>Escala: <strong>' + $('<div>').text(nombreEscala).html() + '</strong>'
                    + ' &nbsp; Precio unitario base: <strong>' + formatoMoneda(precio) + '</strong></small></div>'
                    + '<div class="table-responsive"><table class="table table-sm table-bordered cotizador-expo-tabla mb-2">'
                        + '<thead><tr><th class="text-center">Desde cantidad</th><th class="text-right">Precio unitario</th><th class="text-right">Subtotal de la compra</th><th class="text-center">Desc. marca</th><th class="text-center">Desc. subtotal</th><th class="text-right">Precio unitario final</th><th class="text-right">ISV</th><th class="text-right">Precio U.F. + ISV</th><th class="text-right">Ahorro por unidad</th><th class="text-right">Ahorro total</th></tr></thead>'
                    + '<tbody>' + filas + '</tbody></table></div>'
                    + '<small class="text-muted">Estimación basada en las reglas vigentes de esta Expo. Marca primero y descuento general después.</small>';
                    resultado.querySelectorAll('.cotizador-expo-cantidad').forEach(recalcularFilaCotizadorExpo);
        }).catch(function(error) {
            selectorEscala.innerHTML = '<option value="">Sin escalas disponibles</option>';
            selectorEscala.disabled = true;
            var mensaje = error.response?.data?.message || 'No fue posible consultar el precio y los descuentos del producto.';
            resultado.innerHTML = '<div class="alert alert-danger mb-0">' + $('<div>').text(mensaje).html() + '</div>';
        });
    }

    function recalcularFilaCotizadorExpo(campoCantidad) {
        if (!datosCalculoCotizadorExpo) return;
        var cantidad = Number(campoCantidad.value || 0);
        if (!(cantidad > 0)) return;

        var fila = campoCantidad.closest('tr');
        var precio = datosCalculoCotizadorExpo.precio;
        var compra = cantidad * precio;
        var reglaMarca = datosCalculoCotizadorExpo.reglasMarca
            .filter(function(regla) {
                return compra + 0.005 >= Number(regla.venta_minima || 0);
            })
            .sort(function(a, b) {
                return Number(b.venta_minima || 0) - Number(a.venta_minima || 0);
            })[0] || null;
        var porcentajeMarca = reglaMarca ? Number(reglaMarca.porcentaje_descuento || 0) : 0;
        var porcentajeGeneral = 0;
        var minimoGeneral = -1;
        datosCalculoCotizadorExpo.generales.forEach(function(regla) {
            var minimo = Number(regla.venta_minima || 0);
            if (compra + 0.005 >= minimo && minimo >= minimoGeneral) {
                minimoGeneral = minimo;
                porcentajeGeneral = Number(regla.porcentaje_descuento || 0);
            }
        });

        var precioTrasMarca = precio * (1 - porcentajeMarca / 100);
        var precioConDescuento = precioTrasMarca * (1 - porcentajeGeneral / 100);
        var isvUnitario = precioConDescuento * datosCalculoCotizadorExpo.porcentajeIsv / 100;
        var precioFinal = precioConDescuento + isvUnitario;

        fila.querySelector('[data-campo="compra"]').textContent = formatoMoneda(compra);
        fila.querySelector('[data-campo="precio"]').textContent = formatoMoneda(precio);
        fila.querySelector('[data-campo="marca"]').textContent = porcentajeMarca.toFixed(2) + '%';
        fila.querySelector('[data-campo="general"]').textContent = porcentajeGeneral.toFixed(2) + '%';
        fila.querySelector('[data-campo="final-sin-isv"]').innerHTML = '<strong class="text-success">' + formatoMoneda(precioConDescuento) + '</strong>';
        fila.querySelector('[data-campo="isv"]').innerHTML = formatoMoneda(isvUnitario) + '<small class="d-block text-muted">' + datosCalculoCotizadorExpo.porcentajeIsv.toFixed(2) + '%</small>';
        fila.querySelector('[data-campo="final"]').innerHTML = '<strong class="text-success">' + formatoMoneda(precioFinal) + '</strong>';
        fila.querySelector('[data-campo="ahorro"]').textContent = formatoMoneda(precio - precioConDescuento);
        fila.querySelector('[data-campo="ahorro-total"]').textContent = formatoMoneda((precio - precioConDescuento) * cantidad);
    }

    function actualizarContadorCarrito() {
        var badge = document.getElementById('cart-count-badge');
        if (!badge) return;

        var totalCantidad = 0;
        for (var i = 0; i < arregloIdInputs.length; i++) {
            var id = arregloIdInputs[i];
            var inputCantidad = document.getElementById('cantidad' + id);
            var cantidad = inputCantidad ? parseFloat(inputCantidad.value) : NaN;

            // Si la cantidad aun no fue digitada, contar al menos 1 por fila.
            if (!isNaN(cantidad) && cantidad > 0) {
                totalCantidad += cantidad;
            } else {
                totalCantidad += 1;
            }
        }

        var cantidadTexto = Number.isInteger(totalCantidad)
            ? totalCantidad.toString()
            : totalCantidad.toFixed(2);

        badge.textContent = cantidadTexto + ' producto(s)';
        if (typeof window.expoActualizarPendientes === 'function') {
            window.expoActualizarPendientes();
        }
    }

    function totalesGenerales() {
        if (numeroInputs == 0) {
            actualizarSimulacionDescuentoMarcaExpo();
            return;
        }

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
        renderizarResumenDescuentoExpo(calcularDescuentosCarritoExpo());
        actualizarSimulacionDescuentoMarcaExpo();
    }

    function actualizarSimulacionDescuentoMarcaExpo() {
        actualizarDescuentosProductosExpo();
        if (!esOfertaExpo) return;
        var panel = document.getElementById('simulacionDescuentoMarcaExpo');
        var detalle = document.getElementById('simulacionDescuentoMarcaExpoDetalle');
        if (!panel || !detalle) return;
        var calculo = calcularDescuentosCarritoExpo();
        var filas = Object.values(calculo?.marcas || {}).filter(function(marca) {
            return marca.subtotal > 0;
        }).map(function(marca) {
            return '<tr><td>' + $('<div>').text(marca.nombre).html() + '</td>'
                + '<td class="text-right">' + formatoMoneda(marca.subtotal) + '</td>'
                + '<td class="text-right"><small>' + Number(marca.porcentajeMarca || 0).toFixed(2) + '%</small><br>' + formatoMoneda(marca.descuentoMarca) + '</td>'
                + '<td class="text-right"><small>' + Number(marca.porcentajeGeneral || 0).toFixed(2) + '%</small><br>' + formatoMoneda(marca.descuentoGeneral) + '</td>'
                + '<td class="text-right"><strong>' + formatoMoneda(marca.descuentoTotal) + '</strong></td></tr>';
        }).join('');

        panel.style.display = filas ? 'block' : 'none';
        detalle.innerHTML = filas
            ? '<div class="table-responsive"><table class="table table-sm table-bordered mb-2"><thead><tr><th>Marca</th><th class="text-right">Subtotal</th><th class="text-right">Desc. marca</th><th class="text-right">Desc. subtotal</th><th class="text-right">Descuento total</th></tr></thead><tbody>' + filas + '</tbody></table></div>'
            : '';
    }

    function actualizarDescuentosProductosExpo() {
        var calculo = calcularDescuentosCarritoExpo();
        if (!calculo) return;
        arregloIdInputs.forEach(function(id) {
            var linea = calculo.lineas[id];
            var indicador = document.getElementById('descuentoExpoProducto' + id);
            if (indicador) {
                if (esOfertaExpo) {
                    var detalles = [];
                    if (Number(linea?.descuentoMarca || 0) > 0.005) {
                        detalles.push('<div style="white-space:nowrap;"><span style="color:#546e7a;">Marca ('
                            + Number(linea?.porcentajeMarca || 0).toFixed(2) + '%):</span> <strong style="color:#1b5e20;">'
                            + formatoMoneda(linea.descuentoMarca) + '</strong></div>');
                    }
                    if (Number(linea?.descuentoGeneral || 0) > 0.005) {
                        detalles.push('<div style="white-space:nowrap;"><span style="color:#546e7a;">General ('
                            + Number(linea?.porcentajeGeneral || 0).toFixed(2) + '%):</span> <strong style="color:#1565c0;">'
                            + formatoMoneda(linea.descuentoGeneral) + '</strong></div>');
                    }
                    detalles.push('<div style="white-space:nowrap;"><span style="color:#546e7a;">Firmado:</span> '
                        + '<strong style="color:#1b5e20;">' + formatoMoneda(linea?.descuentoTotal || 0) + '</strong></div>');
                    indicador.innerHTML = detalles.join('');
                    return;
                }
                indicador.innerHTML = '<div style="white-space:nowrap;"><span style="color:#546e7a;">Marca:</span> '
                    + '<strong style="color:#1b5e20;">' + formatoMoneda(linea?.descuentoMarca || 0) + '</strong></div>'
                    + '<div style="white-space:nowrap;"><span style="color:#546e7a;">Subtotal:</span> '
                    + '<strong style="color:#1565c0;">' + formatoMoneda(linea?.descuentoGeneral || 0) + '</strong></div>';
            }
        });
    }

    function calcularDescuentosCarritoExpo() {
        if (!expoConfig) return null;
        var usarReglasFirmadas = esOfertaExpo && !filtrarProductosExpo;
        var configuracion = usarReglasFirmadas ? reglasExpoOferta : expoConfig;
        var reglasMarca = usarReglasFirmadas
            ? (Array.isArray(configuracion.marcas) ? configuracion.marcas : [])
            : (Array.isArray(configuracion.descuentos_marca) ? configuracion.descuentos_marca : []);
        if (!usarReglasFirmadas) {
            var clienteExpoId = Number(document.getElementById('seleccionarCliente')?.value || 0);
            var asistentesExpo = Array.isArray(expoConfig.clientes_asistentes) ? expoConfig.clientes_asistentes.map(Number) : [];
            reglasMarca = reglasMarca.filter(function(regla) {
                return !regla.requiere_asistencia || asistentesExpo.includes(clienteExpoId);
            });
        }
        var reglasGenerales = usarReglasFirmadas
            ? (Array.isArray(configuracion.generales) ? configuracion.generales : [])
            : (Array.isArray(configuracion.descuentos) ? configuracion.descuentos : []);
        var totalBruto = 0;
        var importes = {};
        var subtotalesMarca = {};

        arregloIdInputs.forEach(function(id) {
            var precio = Number(document.getElementById('precio' + id)?.value || 0);
            var cantidad = Number(document.getElementById('cantidad' + id)?.value || 0);
            var unidad = Number(document.getElementById('unidad' + id)?.value || 0);
            var marcaId = Number(document.getElementById('marcaExpoId' + id)?.value || 0);
            var lineaId = Number(document.getElementById('lineaExpoOrigenId' + id)?.value
                || document.getElementById('cotizacionLineaId' + id)?.value || 0);
            var cantidadOfertada = Number(document.getElementById('cantidadOfertaExpo' + id)?.value || 0);
            var descuentoFirmado = Number(document.getElementById('descuentoOfertaExpo' + id)?.value || 0);
            var importe = precio * cantidad * unidad;
            importes[id] = {
                precio: precio,
                cantidad: cantidad,
                unidad: unidad,
                marcaId: marcaId,
                lineaId: lineaId,
                cantidadOfertada: cantidadOfertada,
                descuentoFirmado: descuentoFirmado,
                importe: importe
            };
            totalBruto += importe;
            subtotalesMarca[marcaId] = (subtotalesMarca[marcaId] || 0) + importe;
        });

        var porcentajeGeneral = 0;
        var minimoGeneral = -1;
        reglasGenerales.forEach(function(regla) {
            var minimo = Number(regla.venta_minima || 0);
            if (totalBruto + 0.005 >= minimo && minimo >= minimoGeneral) {
                minimoGeneral = minimo;
                porcentajeGeneral = Number(regla.porcentaje_descuento || 0);
            }
        });

        var resultado = { lineas: {}, marcas: {}, totalDescuento: 0, totalBruto: totalBruto, porcentajeGeneral: porcentajeGeneral };
        Object.keys(importes).forEach(function(id) {
            var datos = importes[id];
            var redondearMoneda = function(valor) { return Math.round((valor + Number.EPSILON) * 100) / 100; };
            var subtotalMarca = Number(subtotalesMarca[datos.marcaId] || 0);
            var reglaMarca = reglasMarca
                .filter(function(regla) {
                    return Number(regla.marca_id) === datos.marcaId
                        && subtotalMarca + 0.005 >= Number(regla.venta_minima || 0);
                })
                .sort(function(a, b) {
                    return Number(b.venta_minima || 0) - Number(a.venta_minima || 0);
                })[0] || null;
            var porcentajeMarca = reglaMarca ? Number(reglaMarca.porcentaje_descuento || 0) : 0;
            var descuentoMarca = redondearMoneda(datos.importe * porcentajeMarca / 100);
            var descuentoGeneral = redondearMoneda((datos.importe - descuentoMarca) * porcentajeGeneral / 100);
            var descuentoTotal = redondearMoneda(descuentoMarca + descuentoGeneral);

            if (usarReglasFirmadas) {
                var proporcionCantidad = datos.cantidadOfertada > 0
                    ? Math.min(Math.max(datos.cantidad / datos.cantidadOfertada, 0), 1)
                    : 0;
                descuentoTotal = redondearMoneda(datos.descuentoFirmado * proporcionCantidad);

                var atribucion = atribucionesDescuentoExpo[datos.lineaId] || {};
                var proporcionMarca = Math.min(Math.max(Number(atribucion.proporcion_marca || 0), 0), 1);
                descuentoMarca = redondearMoneda(descuentoTotal * proporcionMarca);
                descuentoGeneral = redondearMoneda(descuentoTotal - descuentoMarca);
                porcentajeMarca = datos.importe > 0 ? descuentoMarca * 100 / datos.importe : 0;
                porcentajeGeneral = datos.importe - descuentoMarca > 0
                    ? descuentoGeneral * 100 / (datos.importe - descuentoMarca)
                    : 0;
            }
            var subtotalNeto = redondearMoneda(datos.importe - descuentoTotal);
            var porcentajeIsv = tipoFacturaConfig && !tipoFacturaConfig.aplica_isv
                ? 0
                : Number(document.getElementById('isv' + id)?.value || 0);
            var isv = redondearMoneda(subtotalNeto * porcentajeIsv / 100);
            var nombreMarca = document.getElementById('marcaExpoNombre' + id)?.value
                || reglaMarca?.marca || reglaMarca?.marca_nombre || (datos.marcaId ? 'Marca ' + datos.marcaId : 'SIN MARCA');

            resultado.lineas[id] = {
                porcentajeMarca: porcentajeMarca,
                porcentajeGeneral: porcentajeGeneral,
                descuentoMarca: descuentoMarca,
                descuentoGeneral: descuentoGeneral,
                descuentoTotal: descuentoTotal,
                subtotalNeto: subtotalNeto,
                isv: isv,
                total: redondearMoneda(subtotalNeto + isv)
            };
            if (!resultado.marcas[datos.marcaId]) {
                resultado.marcas[datos.marcaId] = { nombre: nombreMarca, porcentajeMarca: porcentajeMarca, porcentajeGeneral: porcentajeGeneral, cantidad: 0, subtotal: 0, descuentoMarca: 0, descuentoGeneral: 0, descuentoTotal: 0 };
            }
            var marca = resultado.marcas[datos.marcaId];
            marca.cantidad += datos.cantidad * datos.unidad;
            marca.subtotal += datos.importe;
            marca.descuentoMarca += descuentoMarca;
            marca.descuentoGeneral += descuentoGeneral;
            marca.descuentoTotal += descuentoTotal;
            resultado.totalDescuento += descuentoTotal;
        });
        return resultado;
    }

    function renderizarResumenDescuentoExpo(calculo) {
        var contenedor = document.getElementById('descuentoExpoResumenMarcas');
        if (!contenedor || !calculo) return;
        var todasLasMarcas = Object.values(calculo.marcas).filter(function(marca) { return marca.subtotal > 0; });
        var marcas = todasLasMarcas.filter(function(marca) { return marca.descuentoMarca > 0.005; });
        var filasMarca = marcas.map(function(marca) {
            return '<div class="d-flex justify-content-between" style="gap:12px; color:#546e7a;">'
                + '<span>Marca ' + $('<div>').text(marca.nombre).html() + ' <strong>(' + Number(marca.porcentajeMarca || 0).toFixed(2) + '%)</strong></span>'
                + '<strong>' + formatoMoneda(marca.descuentoMarca) + '</strong></div>';
        }).join('');
        var totalGeneral = todasLasMarcas.reduce(function(total, marca) { return total + marca.descuentoGeneral; }, 0);
        var filaGeneral = '<div class="d-flex justify-content-between" style="gap:12px; color:#546e7a;">'
            + '<span>Descuento general <strong>(' + Number(calculo.porcentajeGeneral || 0).toFixed(2) + '%)</strong></span>'
            + '<strong>' + formatoMoneda(totalGeneral) + '</strong></div>';
        contenedor.innerHTML = filasMarca + filaGeneral;
    }

    function abrirResumenMarcasCarritoExpo() {
        var calculo = calcularDescuentosCarritoExpo();
        var contenido = document.getElementById('resumenMarcasCarritoExpoContenido');
        if (!contenido || !calculo) return;
        var marcas = Object.values(calculo.marcas).filter(function(marca) { return marca.subtotal > 0; });
        if (marcas.length === 0) {
            contenido.innerHTML = '<div class="text-center text-muted py-4">No hay productos en el carrito.</div>';
        } else {
            var filas = marcas.map(function(marca) {
                return '<tr><td>' + $('<div>').text(marca.nombre).html() + '</td>'
                    + '<td class="text-right">' + Number(marca.cantidad).toLocaleString('es-HN', { maximumFractionDigits: 2 }) + '</td>'
                    + '<td class="text-right">' + formatoMoneda(marca.subtotal) + '</td>'
                    + '<td class="text-right"><small class="text-muted">' + Number(marca.porcentajeMarca || 0).toFixed(2) + '%</small><br>' + formatoMoneda(marca.descuentoMarca) + '</td>'
                    + '<td class="text-right"><small class="text-muted">' + Number(marca.porcentajeGeneral || 0).toFixed(2) + '%</small><br>' + formatoMoneda(marca.descuentoGeneral) + '</td>'
                    + '<td class="text-right"><strong class="text-success">' + formatoMoneda(marca.descuentoTotal) + '</strong></td></tr>';
            }).join('');
            contenido.innerHTML = '<div class="table-responsive"><table class="table table-sm table-bordered mb-0" style="background:#fff; font-size:12px;">'
                + '<thead style="background:#e6f1eb; color:#245c46;"><tr><th>Marca</th><th class="text-right">Cantidad</th><th class="text-right">Subtotal</th><th class="text-right">Desc. marca</th><th class="text-right">Desc. subtotal</th><th class="text-right">Descuento total</th></tr></thead>'
                + '<tbody>' + filas + '</tbody></table></div>';
        }
        $('#modalResumenMarcasCarritoExpo').modal('show');
    }

    function actualizarDescuentoExpo() {
        if (!expoConfig || !Array.isArray(expoConfig.descuentos)) return false;

        var ventaBruta = 0;
        arregloIdInputs.forEach(function(id) {
            var precio = parseFloat(document.getElementById('precio' + id)?.value) || 0;
            var cantidad = parseFloat(document.getElementById('cantidad' + id)?.value) || 0;
            var unidad = parseFloat(document.getElementById('unidad' + id)?.value) || 0;
            ventaBruta += precio * cantidad * unidad;
        });

        var porcentaje = 0;
        expoConfig.descuentos.forEach(function(regla) {
            if (ventaBruta >= parseFloat(regla.venta_minima || 0)) {
                porcentaje = parseFloat(regla.porcentaje_descuento || 0);
            }
        });

        var input = document.getElementById('porDescuento');
        var anterior = parseFloat(input.value) || 0;
        input.value = porcentaje;
        return Math.abs(anterior - porcentaje) > 0.0001;
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
        actualizarContadorCarrito();
        programarGuardadoTemporal();
    }

    function validacionPrecio(idPrecios, idprecio) {
        var idPrecioSeleccionado = idPrecios.options[idPrecios.selectedIndex].getAttribute("data-id");
        var precioSeleccionado = idPrecios.value;
        var idprecioIngresado = idprecio.id;

        document.getElementById(idprecioIngresado).value = precioSeleccionado;
        // Guarda el precio de la escala seleccionada como referencia. No bloquea la escritura;
        // se valida al enviar el formulario — ver validarPrecioEscalaAntesDeGuardar().
        document.getElementById(idprecioIngresado).setAttribute("data-precio-escala", precioSeleccionado);
    }

    // ================================================================
    // VALIDACIÓN: precio ingresado vs. precio de la escala seleccionada
    // Aplica solo a facturas con restricción (no Ofertas, no Facturas SR).
    // Las Ofertas pueden guardarse con un valor menor; las Facturas SR ya
    // manejan su propio flujo de autorización (mostrarModalSrAutorizacion).
    // ================================================================
    function obtenerProductosPorDebajoEscala() {
        var items = [];
        for (var i = 0; i < arregloIdInputs.length; i++) {
            var idx = arregloIdInputs[i];
            var precioInput = document.getElementById('precio' + idx);
            if (!precioInput) continue;
            var precioEscala = parseFloat(precioInput.getAttribute('data-precio-escala'));
            var precioIngresado = parseFloat(precioInput.value);
            if (!isNaN(precioEscala) && precioEscala > 0 && !isNaN(precioIngresado) && precioIngresado < precioEscala) {
                var nombreEl = document.getElementById('nombre' + idx);
                items.push({
                    nombre: nombreEl ? nombreEl.value : ('Producto #' + idx),
                    precioEscala: precioEscala,
                    precioIngresado: precioIngresado
                });
            }
        }
        return items;
    }

    function mostrarErrorPrecioBajoEscala(productos) {
        var filas = productos.map(function (p) {
            return '<tr>'
                + '<td style="padding:4px 8px; text-align:left;">' + p.nombre + '</td>'
                + '<td style="padding:4px 8px; text-align:right;">L. ' + p.precioEscala.toFixed(2) + '</td>'
                + '<td style="padding:4px 8px; text-align:right; color:#c62828; font-weight:700;">L. ' + p.precioIngresado.toFixed(2) + '</td>'
                + '</tr>';
        }).join('');

        Swal.fire({
            icon: 'error',
            title: 'No se puede facturar',
            width: 560,
            html: '<p class="text-left">El valor ingresado es <b>menor</b> al precio de la escala seleccionada para uno o más productos:</p>'
                + '<table class="table table-sm" style="font-size:12px;"><thead><tr><th>Producto</th><th>Escala</th><th>Ingresado</th></tr></thead><tbody>' + filas + '</tbody></table>'
                + '<p class="text-left mt-2" style="margin-top:10px;">Si necesita facturar con un valor menor al de la escala, debe realizar la factura desde <b>Editar Factura</b> seleccionando el tipo <b>Factura SR</b>.</p>'
        });
    }

    // ================================================================
    // FECHAS Y PAGOS
    // ================================================================
    function validarFechaPago() {
        let tipoPago = document.getElementById('tipoPagoVenta').value;
        if (tipoPago == 2) {
            document.getElementById('fecha_vencimiento').readOnly = modoEditarFactura;
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
            if (!fechaEmision) return;
            let date = new Date(fechaEmision + 'T00:00:00');
            date.setDate(date.getDate() + diasCredito);
            document.getElementById("fecha_vencimiento").value = date.toISOString().split('T')[0];
        }
    }

    if (modoEditarFactura) {
        document.getElementById('fecha_emision').readOnly = true;
        document.getElementById('fecha_vencimiento').readOnly = true;
    }

    function bloquearCamposEdicionFactura() {
        if (!modoEditarFactura) return;
        $('#vendedor').prop('disabled', true);
        $('#tipoPagoVenta').prop('disabled', true);
        document.getElementById('fecha_emision').readOnly = true;
        document.getElementById('fecha_vencimiento').readOnly = true;
    }

    function obtenerOrdenesCompra() {
        if (!tipoFacturaConfig || !tipoFacturaConfig.requiere_orden_compra || !urls.orden_compra) return;

        var idCliente = document.getElementById('seleccionarCliente').value;
        if (!idCliente) return;

        var selectEl = document.getElementById('ordenCompra');
        if (!selectEl) return;
        selectEl.innerHTML = '<option value="" disabled selected>Cargando...</option>';

        axios.get(urls.orden_compra, { params: { idCliente: idCliente } })
            .then(function(response) {
                var ordenes = response.data.results || [];
                if (ordenes.length === 0) {
                    selectEl.innerHTML = '<option value="" disabled selected>-- Sin órdenes disponibles --</option>';
                } else {
                    var html = '<option value="" disabled selected>--Seleccionar--</option>';
                    ordenes.forEach(function(o) {
                        html += '<option value="' + o.id + '">' + o.text + '</option>';
                    });
                    selectEl.innerHTML = html;
                }
            })
            .catch(function() {
                selectEl.innerHTML = '<option value="" disabled selected>--Seleccionar--</option>';
            });
    }

    // ================================================================
    // NUEVA ORDEN DE COMPRA INLINE (desde facturación)
    // ================================================================
    function abrirModalNuevaOrden() {
        var idCliente = $('#seleccionarCliente').val();
        if (!idCliente) {
            Swal.fire({
                icon: 'warning',
                title: 'Cliente requerido',
                text: 'Debe seleccionar un cliente antes de crear una orden de compra.'
            });
            return;
        }

        // Obtener nombre del cliente: desde el campo de texto o desde el select
        var nombreCliente = document.getElementById('nombre_cliente_ventas').value;
        if (!nombreCliente) {
            var selOpt = document.querySelector('#seleccionarCliente option:checked');
            nombreCliente = selOpt ? selOpt.text : 'Cliente seleccionado';
        }

        document.getElementById('nuevaOrdenClienteId').value    = idCliente;
        document.getElementById('nuevaOrdenClienteNombre').value = nombreCliente;
        document.getElementById('nuevaOrdenNumero').value        = '';

        $('#modal_nueva_orden_inline').modal('show');
        setTimeout(function() {
            document.getElementById('nuevaOrdenNumero').focus();
        }, 400);
    }

    $(document).on('submit', '#formNuevaOrdenInline', function(e) {
        e.preventDefault();
        guardarNuevaOrdenInline();
    });

    function guardarNuevaOrdenInline() {
        var idCliente   = document.getElementById('nuevaOrdenClienteId').value;
        var numeroOrden = document.getElementById('nuevaOrdenNumero').value.trim();

        if (!idCliente || !numeroOrden) {
            Swal.fire({ icon: 'warning', title: 'Campos requeridos', text: 'Complete el número de orden.' });
            return;
        }

        var btn = document.getElementById('btnGuardarNuevaOrden');
        btn.disabled = true;
        btn.innerHTML = '<i class="mr-1 fa fa-spinner fa-spin"></i> Guardando...';

        var data = new FormData();
        data.append('cliente', idCliente);
        data.append('numero_orden', numeroOrden);

        axios.post('/estatal/ordenes/guardar', data)
            .then(function(response) {
                btn.disabled = false;
                btn.innerHTML = '<i class="mr-1 fa fa-save"></i> Guardar Orden';

                $('#modal_nueva_orden_inline').modal('hide');

                // Recargar órdenes del cliente y preseleccionar la nueva
                axios.get(urls.orden_compra, { params: { idCliente: idCliente } })
                    .then(function(res) {
                        var ordenes = res.data.results || [];
                        var selectEl = document.getElementById('ordenCompra');
                        if (!selectEl) return;
                        var html = '<option value="" disabled>--Seleccionar--</option>';
                        ordenes.forEach(function(o) {
                            var sel = (o.text === numeroOrden) ? ' selected' : '';
                            html += '<option value="' + o.id + '"' + sel + '>' + o.text + '</option>';
                        });
                        selectEl.innerHTML = html;
                    })
                    .catch(function() {});

                Swal.fire({
                    icon: 'success',
                    title: '¡Orden creada!',
                    text: 'Orden "' + numeroOrden + '" registrada y seleccionada.',
                    timer: 2200,
                    showConfirmButton: false
                });
            })
            .catch(function(err) {
                btn.disabled = false;
                btn.innerHTML = '<i class="mr-1 fa fa-save"></i> Guardar Orden';
                var d = err.response ? err.response.data : {};
                Swal.fire({
                    icon: d.icon || 'error',
                    title: d.title || 'Error',
                    text: d.text || 'Error al guardar la orden de compra.'
                });
            });
    }

    // ================================================================
    // ARCHIVOS ADJUNTOS EN OFERTA (orden de compra / forma F01)
    // ================================================================
    function subirArchivoOferta(tipo, inputEl) {
        var file = inputEl.files[0];
        if (!file) return;

        var maxMb = 5;
        if (file.size > maxMb * 1024 * 1024) {
            Swal.fire({ icon: 'warning', title: 'Archivo muy grande', text: 'El archivo no debe superar los ' + maxMb + ' MB.' });
            inputEl.value = '';
            return;
        }

        var formData = new FormData();
        formData.append('archivo', file);
        formData.append('tipo', tipo);
        formData.append('_token', '{{ csrf_token() }}');

        var previewEl  = document.getElementById('preview_archivo_'  + tipo);
        var txtEl      = document.getElementById('txt_archivo_'      + tipo);
        var hiddenEl   = document.getElementById('archivo_'          + tipo);

        previewEl.style.display = 'none';
        txtEl.textContent = 'Subiendo...';

        axios.post('/cotizacion/adjunto/subir', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
            .then(function(res) {
                hiddenEl.value       = res.data.ruta;
                txtEl.textContent    = res.data.nombre;
                previewEl.style.display = 'block';
            })
            .catch(function(err) {
                inputEl.value = '';
                var d = err.response ? err.response.data : {};
                Swal.fire({ icon: 'error', title: d.title || 'Error', text: d.text || 'No se pudo subir el archivo.' });
            });
    }

    function limpiarArchivoOferta(tipo) {
        document.getElementById('archivo_' + tipo).value = '';
        document.getElementById('preview_archivo_' + tipo).style.display = 'none';
        document.getElementById('txt_archivo_' + tipo).textContent = '';
        document.getElementById('archivo_' + tipo + '_input').value = '';
    }

    // ================================================================
    // NUEVO CÓDIGO DE EXONERACIÓN INLINE (desde facturación)
    // ================================================================
    function abrirModalNuevaExoneracion() {
        var idCliente = $('#seleccionarCliente').val();
        if (!idCliente) {
            Swal.fire({
                icon: 'warning',
                title: 'Cliente requerido',
                text: 'Debe seleccionar un cliente antes de crear un código de exoneración.'
            });
            return;
        }

        var nombreCliente = document.getElementById('nombre_cliente_ventas').value;
        if (!nombreCliente) {
            var selOpt = document.querySelector('#seleccionarCliente option:checked');
            nombreCliente = selOpt ? selOpt.text : 'Cliente seleccionado';
        }

        document.getElementById('nuevaExoneracionClienteId').value      = idCliente;
        document.getElementById('nuevaExoneracionClienteNombre').value   = nombreCliente;
        document.getElementById('nuevaExoneracionCodigo').value          = '';
        document.getElementById('nuevaExoneracionCorrOrd').value         = '';

        $('#modal_nueva_exoneracion_inline').modal('show');
        setTimeout(function() {
            document.getElementById('nuevaExoneracionCodigo').focus();
        }, 400);
    }

    $(document).on('submit', '#formNuevaExoneracionInline', function(e) {
        e.preventDefault();
        guardarNuevaExoneracionInline();
    });

    function guardarNuevaExoneracionInline() {
        var idCliente = document.getElementById('nuevaExoneracionClienteId').value;
        var codigo    = document.getElementById('nuevaExoneracionCodigo').value.trim();
        var corrOrd   = document.getElementById('nuevaExoneracionCorrOrd').value.trim();

        if (!idCliente || !codigo) {
            Swal.fire({ icon: 'warning', title: 'Campos requeridos', text: 'Complete el código de exoneración.' });
            return;
        }

        var btn = document.getElementById('btnGuardarNuevaExoneracion');
        btn.disabled = true;
        btn.innerHTML = '<i class="mr-1 fa fa-spinner fa-spin"></i> Guardando...';

        var data = new FormData();
        data.append('cliente', idCliente);
        data.append('codigo', codigo);
        if (corrOrd) data.append('corrOrd', corrOrd);

        axios.post('/estatal/exonerado/guardar', data)
            .then(function(response) {
                btn.disabled = false;
                btn.innerHTML = '<i class="mr-1 fa fa-save"></i> Guardar Código';

                $('#modal_nueva_exoneracion_inline').modal('hide');

                // Recargar códigos y preseleccionar el nuevo
                axios.get('/exonerado/listar/codigos', { params: { idCliente: idCliente } })
                    .then(function(res) {
                        var codigos = res.data.results || [];
                        var selectEl = document.getElementById('codigoExoneracion');
                        var html = '<option value="" disabled>--Seleccione--</option>';
                        codigos.forEach(function(c) {
                            var sel = (c.text === codigo) ? ' selected' : '';
                            html += '<option value="' + c.id + '"' + sel + '>' + c.text + '</option>';
                        });
                        selectEl.innerHTML = html;
                    })
                    .catch(function() {});

                Swal.fire({
                    icon: 'success',
                    title: '¡Código creado!',
                    text: 'Código "' + codigo + '" registrado y seleccionado.',
                    timer: 2200,
                    showConfirmButton: false
                });
            })
            .catch(function(err) {
                btn.disabled = false;
                btn.innerHTML = '<i class="mr-1 fa fa-save"></i> Guardar Código';
                var d = err.response ? err.response.data : {};
                Swal.fire({
                    icon: d.icon || 'error',
                    title: d.title || 'Error',
                    text: d.text || 'Error al guardar el código de exoneración.'
                });
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
    var _facturaGuardadaId = null;
    var _facturaFlujoId    = null;
    var _revisionFlujoId   = null;

    function limpiarFormularioVenta(data) {
        var bloqueImagenes = document.getElementById('bloqueImagenes');
        if (bloqueImagenes) bloqueImagenes.innerHTML = '';
        var carritoTbodyEl = document.getElementById('carritoTbody');
        if (carritoTbodyEl) carritoTbodyEl.innerHTML = '';
        document.getElementById("crear_venta").reset();
        $('#crear_venta').parsley().reset();

        // Cliente
        document.getElementById("seleccionarCliente").innerHTML = '<option value="" selected disabled>--Seleccionar un cliente--</option>';
        $('#seleccionarCliente').prop('disabled', false);
        $('#cat_cliente_badge').hide();
        $('#cat_badge_text').text('');
        $('#categoria_cliente_venta_id').data('categoria-cliente-id', null)
            .empty().append('<option value="" selected disabled>--Seleccione una categoría--</option>')
            .prop('disabled', true);
        diasCredito = 0;

        // Orden de compra
        var selectOrden = document.getElementById('ordenCompra');
        if (selectOrden) selectOrden.innerHTML = '<option value="" selected disabled>--Seleccionar--</option>';

        // Producto
        var seleccionarProducto = document.getElementById('seleccionarProducto');
        if (seleccionarProducto) seleccionarProducto.innerHTML = '<option value="" selected disabled></option>';
        var codigoProductoBuscar = document.getElementById('codigoProductoBuscar');
        if (codigoProductoBuscar) codigoProductoBuscar.value = '';
        var lblProd = document.getElementById('productoSeleccionadoLabel');
        if (lblProd) { lblProd.classList.add('d-none'); lblProd.textContent = ''; }
        var bodega = document.getElementById('bodega');
        if (bodega) {
            bodega.innerHTML = '<option value="" selected disabled>--Seleccione un producto--</option>';
            bodega.disabled = true;
        }
        var botonAddEl = document.getElementById('botonAdd');
        if (botonAddEl) botonAddEl.classList.add('d-none');

        // Historial de precios
        var histCuerpo = document.getElementById('historialPreciosCuerpo');
        if (histCuerpo) histCuerpo.innerHTML = '<p class="mb-0 text-muted small">Sin ventas previas de este producto a este cliente.</p>';

        // Ocultar tabla carrito
        var carritoTabla = document.getElementById('carritoTablaWrapper');
        var carritoVacio = document.getElementById('carritoVacio');
        if (carritoTabla) carritoTabla.style.display = 'none';
        if (carritoVacio) carritoVacio.style.display = '';

        arregloIdInputs = [];
        numeroInputs = 0;
        actualizarContadorCarrito();
        retencionEstado = false;

        if (data && data.numeroVenta) document.getElementById('numero_venta').value = data.numeroVenta;
        document.getElementById("btn_venta_coorporativa").disabled = false;

        // Resetear estado del modal de gestor para próxima factura
        var gestorHidden = document.getElementById('gestor_entrega_hidden');
        if (gestorHidden) { gestorHidden.value = ''; gestorHidden.removeAttribute('data-confirmed'); }

        document.getElementById('restriccion').value = tipoFacturaConfig ? tipoFacturaConfig.restriccion : 1;
        document.getElementById('tipo_venta_id').value = tipoFacturaConfig ? tipoFacturaConfig.tipo_venta_id : 2;
        document.getElementById('tipo_factura_id').value = tipoFacturaConfig ? tipoFacturaConfig.id : '';

        const urlParams = new URLSearchParams(window.location.search);
        const autorizacionId = urlParams.get('autorizacion_id');
        if (autorizacionId) {
            document.getElementById('codigo_autorizacion').value = autorizacionId;
        }
    }

    function abrirModalFlujoDesdeContexto(pasoPreferido, pedidoId, flujoId) {
        var pId = pedidoId || document.getElementById('pedido_vinculado_id')?.value || null;
        var fId = flujoId  || document.getElementById('flujo_vinculado_id')?.value || null;

        pId = pId ? parseInt(pId, 10) : null;
        fId = fId ? parseInt(fId, 10) : null;

        if (pId) {
            Livewire.emit('abrirFlujoPedido', pId, pasoPreferido || 'pedido');
            return;
        }

        if (fId) {
            Livewire.emit('abrirFlujoCotizacion', fId);
            return;
        }

        Swal.fire({ icon: 'info', title: 'Sin flujo', text: 'No se encontró flujo o pedido vinculado para mostrar.' });
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
            abrirModalFlujoDesdeContexto('ofertas', idPedido, idFlujo);
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
            var runPrefacturar = function(comentarioCredito) {
                var btn = document.getElementById('btnPrefacturarOferta');
                if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin d-block" style="font-size:20px;margin-bottom:4px;"></i>Procesando...'; }

                axios.post('/cotizacion/prefacturar-desde-oferta',
                    {
                        cotizacion_id: idOferta,
                        flujo_id: idFlujo || null,
                        comentario_credito: comentarioCredito !== '' ? comentarioCredito : null
                    },
                    { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } }
                ).then(function(res) {
                    var d = res.data;
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-file-text-o d-block" style="font-size:20px;margin-bottom:4px;"></i>Oferta ganadora'; }

                    if (d.en_revision_credito) {
                        _revisionFlujoId = d.flujoId || idFlujo;
                        var metaWrap = document.getElementById('revisionCredMeta');
                        if (metaWrap) {
                            var fmt = new Intl.NumberFormat('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            document.getElementById('revMetaEmision').textContent = d.fecha_emision || '—';
                            document.getElementById('revMetaVencimiento').textContent = d.fecha_vencimiento || '—';
                            document.getElementById('revMetaDias').textContent = (d.dias_solicitados !== undefined && d.dias_solicitados !== null) ? d.dias_solicitados : 0;
                            document.getElementById('revMetaMonto').textContent = 'L ' + fmt.format(parseFloat(d.monto_total_oferta || 0));
                            metaWrap.style.display = '';
                        }
                        $('#modalRevisionCredito').modal('show');
                        setTimeout(function() { $('.modal-backdrop').last().css('z-index', '2070'); }, 50);
                        return;
                    }

                    _prefacturaId    = d.idPrefactura;
                    _prefacturaFlujoId = d.flujoId || idFlujo;
                    document.getElementById('msgPrefactura').textContent = 'Prefactura #' + d.idPrefactura + ' generada. Válida por ' + (d.diasValidez || 7) + ' día(s).';
                    $('#modalPrefacturaExito').modal('show');
                    setTimeout(function() { $('.modal-backdrop').last().css('z-index', '2070'); }, 50);
                }).catch(function(err) {
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-file-text-o d-block" style="font-size:20px;margin-bottom:4px;"></i>Oferta ganadora'; }
                    var d = err.response ? err.response.data : {};
                    if (d.sin_existencia_errors && d.sin_existencia_errors.length) {
                        var listadoSinExistencia = d.sin_existencia_errors.map(function(e) {
                            return '<li style="margin-bottom:4px;">' + (e.producto || 'Producto') + '</li>';
                        }).join('');
                        Swal.fire({
                            icon: 'warning',
                            title: 'No se puede pasar a Prefactura',
                            html: '<p style="font-size:13px;margin-bottom:10px;">La cotización contiene productos marcados como <strong>SIN EXISTENCIA</strong>. Debe ajustarlos en la oferta antes de continuar.</p>'
                                + '<ul style="text-align:left;padding-left:18px;margin:0;">' + listadoSinExistencia + '</ul>',
                            confirmButtonColor: '#e65100',
                        });
                    } else if (d.stock_errors && d.stock_errors.length) {
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
            };

            $('#modalExitoOferta').one('hidden.bs.modal', function() {
                Swal.fire({
                    title: 'Comentario para Créditos',
                    text: 'Opcional: agrega una observación antes de marcar la oferta ganadora.',
                    input: 'textarea',
                    inputPlaceholder: 'Escribe aquí el comentario para créditos...',
                    inputAttributes: { maxlength: 1000 },
                    showCancelButton: true,
                    confirmButtonText: 'Continuar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#e65100'
                }).then(function(result) {
                    if (!result.isConfirmed) {
                        $('#modalExitoOferta').modal('show');
                        return;
                    }
                    var comentarioCredito = ((result.value || '') + '').trim();
                    runPrefacturar(comentarioCredito);
                });
            });
            $('#modalExitoOferta').modal('hide');

        } else if (tipo === 'imprimir') {
            var urlImprimir = urls.imprimir;
            if (urlImprimir && idOferta) {
                window.open(urlImprimir.replace('{id}', idOferta), '_blank');
            }
            // Modal permanece abierto intencionalmente
        }
    }

    function revisionCredAccion(tipo) {
        if (tipo === 'flujo') {
            $('#modalRevisionCredito').modal('hide');
            var metaWrap = document.getElementById('revisionCredMeta');
            if (metaWrap) metaWrap.style.display = 'none';
            abrirModalFlujoDesdeContexto('ofertas', _ofertaPedidoId, _revisionFlujoId);
        }
    }

    function facturaAccion(tipo) {
        var idFactura = _facturaGuardadaId;
        var idFlujo   = _facturaFlujoId;

        if (tipo === 'nueva') {
            $('#modalExitoFactura').modal('hide');
            window.location.reload();
            return;
        }

        if (tipo === 'flujo') {
            abrirModalFlujoDesdeContexto('entrega', null, idFlujo);
            return;
        }

        if (tipo === 'imprimir') {
            if (idFactura) {
                var printUrl = (urls && urls.imprimir) ? urls.imprimir : '/factura/cooporativo/{id}';
                window.open(printUrl.replace('{id}', idFactura), '_blank');
            }
            return;
        }

        if (tipo === 'cobro') {
            if (idFactura) {
                window.location.href = '/venta/cobro/' + idFactura;
            }
            return;
        }

        if (tipo === 'vale') {
            if (idFactura) {
                window.location.href = '/crear/vale/lista/espera/' + idFactura;
            }
        }
    }

    function prefacturaAccion(tipo) {
        if (tipo === 'imprimir') {
            if (_prefacturaId) {
                window.open('/prefactura/imprimir/' + _prefacturaId, '_blank');
            }
        } else if (tipo === 'flujo') {
            var flujoId = _prefacturaFlujoId;
            abrirModalFlujoDesdeContexto('prefactura', null, flujoId);
        } else if (tipo === 'editar') {
            // Requiere autorización → esperar a que cierre el modal anterior antes de abrir el nuevo
            $('#codigoEditarPref').val('');
            $('#motivoEditarPref').val('');
            $('#errCodigoEditarPref').hide();
            $('#errMotivoEditarPref').hide();
            if ($('#modalPrefacturaExito').hasClass('show') || $('#modalPrefacturaExito').is(':visible')) {
                $('#modalPrefacturaExito').one('hidden.bs.modal', function() {
                    $('#modalAutorizacionEditarPref').modal('show');
                });
                $('#modalPrefacturaExito').modal('hide');
            } else {
                $('#modalAutorizacionEditarPref').modal('show');
            }
        } else if (tipo === 'facturar') {
            // Facturar directamente sin salir de la página
            var prefId = _prefacturaId;
            if (!prefId) {
                Swal.fire({ icon: 'warning', title: 'Sin prefactura', text: 'No se encontró la prefactura.' });
                return;
            }
            var btn = document.getElementById('btnPrefFacturarDirecto');
            if (btn) { btn.disabled = true; btn.style.opacity = '.6'; }

            $('#modalPrefacturaExito').modal('hide');

            axios.post('/prefactura/' + prefId + '/facturar-directo', { tipo_pago: 1 }, {
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
            }).then(function(res) {
                var data = res.data || {};
                if (data.print_url) {
                    window.open(data.print_url, '_blank');
                }
                // Mostrar modal de éxito de factura
                var msgEl = document.getElementById('msgNumFactura');
                if (msgEl) msgEl.textContent = 'Factura #' + (data.factura_id || '') + ' registrada exitosamente.';
                _facturaGuardadaId = data.factura_id || null;
                _facturaFlujoId    = _prefacturaFlujoId || null;
                // Llenar btn imprimir del modal éxito
                var btnImp = document.getElementById('btn_post_factura_imprimir_pref');
                if (btnImp && data.print_url) btnImp.href = data.print_url;
                $('#modalExitoFactura').modal('show');
                if (btn) { btn.disabled = false; btn.style.opacity = '1'; }
            }).catch(function(err) {
                if (btn) { btn.disabled = false; btn.style.opacity = '1'; }
                var msg = (err.response && err.response.data) ? (err.response.data.error || err.response.data.warning || 'Error al facturar.') : 'Error al facturar.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            });
        }
    }

    function solicitarCodigoEditarPref(btnEl) {
        if (!btnEl) return;
        var textoOriginal = btnEl.innerHTML;
        btnEl.disabled = true;
        btnEl.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Enviando...';
        axios.get('/ventas/solicitud/codigo').then(function() {
            btnEl.disabled = false;
            btnEl.innerHTML = textoOriginal;
            Swal.fire({ icon: 'info', title: 'Código enviado', text: 'Solicíteselo a su supervisor.', timer: 3000, showConfirmButton: false, customClass: { container: 'swal-sobre-modal' } });
        }).catch(function() {
            btnEl.disabled = false;
            btnEl.innerHTML = textoOriginal;
            Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo enviar el código.', customClass: { container: 'swal-sobre-modal' } });
        });
    }

    function verificarCodigoEditarPref() {
        var codigo = document.getElementById('codigoEditarPref').value.trim();
        var motivo = document.getElementById('motivoEditarPref').value.trim();
        var errCod = document.getElementById('errCodigoEditarPref');
        var errMot = document.getElementById('errMotivoEditarPref');
        errCod.style.display = 'none';
        errMot.style.display = 'none';

        if (!codigo) { errCod.style.display = ''; errCod.textContent = 'El código es requerido.'; return; }
        if (!motivo)  { errMot.style.display = ''; return; }

        axios.post('/ventas/verificar/codigo', { codigo: codigo }).then(function(response) {
            var data = response.data;
            if (data.estado != 1) {
                errCod.style.display = '';
                errCod.textContent = 'Código incorrecto.';
                return;
            }
            var autorizacionId = data.idAutorizacion;
            // Desactivar código (await para garantizar consumo antes de navegar)
            axios.post('/ventas/autorizacion/desactivar', { idAutorizacion: autorizacionId })
                .finally(function() {
                    $('#modalAutorizacionEditarPref').modal('hide');

                    // Redirigir a formulario de edición
                    var prefId = _prefacturaId;
                    axios.post('/prefactura/' + prefId + '/facturar', {}, {
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                    }).then(function(res) {
                        window.location.href = res.data.url;
                    }).catch(function(err) {
                        Swal.fire({ icon: 'error', title: 'Error', text: (err.response && err.response.data && err.response.data.error) ? err.response.data.error : 'Error al procesar.' });
                    });
                });
        }).catch(function() {
            errCod.style.display = '';
            errCod.textContent = 'Error al verificar el código.';
        });
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
        // Mover modales al <body> para evitar offset de IBOX y conflictos de backdrop
        var ogModal = document.getElementById('modalOfertasGanadoras');
        if (ogModal && ogModal.parentElement !== document.body) {
            document.body.appendChild(ogModal);
        }
        var authPrefModal = document.getElementById('modalAutorizacionEditarPref');
        if (authPrefModal && authPrefModal.parentElement !== document.body) {
            document.body.appendChild(authPrefModal);
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
        // 0. Facturas con restricción (no Oferta, no Factura SR): bloquear si algún precio
        //    ingresado quedó por debajo del precio de la escala seleccionada.
        if (codigoActual !== 'cotizacion_clientes_a' && !(tipoFacturaConfig && tipoFacturaConfig.multiples_precios)) {
            var productosBajoEscala = obtenerProductosPorDebajoEscala();
            if (productosBajoEscala.length > 0) {
                mostrarErrorPrecioBajoEscala(productosBajoEscala);
                return;
            }
        }
        // 1. Toda factura debe confirmar sus actores antes de guardar.
        if (codigoActual !== 'cotizacion_clientes_a') {
            var gestorHidden = document.getElementById('gestor_entrega_hidden');
            if (!gestorHidden || !gestorHidden.getAttribute('data-confirmed')) {
                mostrarModalGestorEntrega();
                return;
            }
        }
        // 2. Para tipos SR: interceptar si aún no tiene código de autorización
        if (tipoFacturaConfig && tipoFacturaConfig.requiere_codigo_autorizacion) {
            var codigoId = document.getElementById('codigo_autorizacion').value;
            if (!codigoId) {
                mostrarModalSrAutorizacion();
                return;
            }
        }
        guardarVenta();
    });

    function mostrarModalSrAutorizacion() {
        var tbody = document.getElementById('srTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';
        var productosSR = [];
        for (var i = 0; i < arregloIdInputs.length; i++) {
            var idx = arregloIdInputs[i];
            var nombreEl = document.getElementById('nombre' + idx);
            var precioSelectEl = document.getElementById('precios' + idx);
            var precioUnitEl = document.getElementById('precio' + idx);
            var nombre = nombreEl ? nombreEl.value : '—';
            var precioOpc = precioSelectEl ? parseFloat(precioSelectEl.value) || 0 : 0;
            var precioUnitario = precioUnitEl ? parseFloat(precioUnitEl.value) || 0 : 0;
            var esBajo = precioUnitario < precioOpc;
            // Solo agregar al arreglo y mostrar en tabla si el precio está por debajo del OPC
            if (!esBajo) continue;
            productosSR.push({ nombre: nombre, precioOpc: precioOpc, precioUnitario: precioUnitario });
            var tr = '<tr style="background:#ffebee;">'
                + '<td>' + nombre + '</td>'
                + '<td style="text-align:right;">' + precioOpc.toFixed(2) + '</td>'
                + '<td style="text-align:right;color:#c62828;font-weight:700;">' + precioUnitario.toFixed(2) + '</td>'
                + '</tr>';
            tbody.insertAdjacentHTML('beforeend', tr);
        }
        window._srProductos = productosSR;
        $('#modal_sr_autorizacion').modal('show');
    }

    function mostrarModalGestorEntrega() {
        var urlVendedores = urls.vendedores;
        var clienteId = $('#seleccionarCliente').val();
        var teleHidden = document.getElementById('tele_asesor_hidden');
        var teleIdActual = teleHidden && teleHidden.value ? teleHidden.value : '{{ Auth::id() }}';
        // Inicializar select2 en el modal si aún no lo está
        if (!$('#gestor_entrega_modal').hasClass('select2-hidden-accessible')) {
            $('#gestor_entrega_modal').select2({
                dropdownParent: $('#modal_gestor_entrega'),
                ajax: {
                    url: urlVendedores,
                    data: function(params) {
                        return { search: params.term, type: 'public', page: params.page || 1 };
                    }
                },
                allowClear: true,
                placeholder: '-- Sin gestor --'
            });
        } else {
            $('#gestor_entrega_modal').val(null).trigger('change');
        }
        if (!$('#tele_asesor_modal').hasClass('select2-hidden-accessible')) {
            $('#tele_asesor_modal').select2({
                dropdownParent: $('#modal_gestor_entrega'),
                allowClear: false,
                placeholder: '-- Seleccionar tele asesor --'
            });
        }
        $('#tele_asesor_modal').empty();
        $('#btn_confirmar_gestor').prop('disabled', true);
        $.get('/cotizacion/actores-asignados', { cliente_id: clienteId, rol_id: 3 })
            .done(function(data) {
                var teleasesores = data.results || [];
                var actualAsignado = teleasesores.some(function(usuario) {
                    return Number(usuario.id) === Number(teleIdActual);
                });
                teleasesores.forEach(function(usuario) {
                    var seleccionado = actualAsignado
                        ? Number(usuario.id) === Number(teleIdActual)
                        : teleasesores.length === 1;
                    $('#tele_asesor_modal').append(
                        new Option(usuario.text, usuario.id, seleccionado, seleccionado)
                    );
                });
                $('#tele_asesor_modal').trigger('change');
            })
            .always(function() {
                $('#btn_confirmar_gestor').prop('disabled', false);
            });
        $('#modal_gestor_entrega').modal('show');
    }

    $(document).on('click', '#btn_confirmar_gestor', function() {
        var gestorId = $('#gestor_entrega_modal').val() || '';
        var teleId = $('#tele_asesor_modal').val() || '';
        var teleData = $('#tele_asesor_modal').select2('data');
        var teleNombre = (teleData && teleData[0] && teleData[0].text) ? teleData[0].text : '';
        if (!teleId) {
            Swal.fire({ icon: 'warning', title: 'Tele asesor requerido', text: 'Debe seleccionar un tele asesor.', customClass: { container: 'swal-sobre-modal' } });
            return;
        }
        var gestorHidden = document.getElementById('gestor_entrega_hidden');
        var teleHidden = document.getElementById('tele_asesor_hidden');
        gestorHidden.value = gestorId;
        gestorHidden.setAttribute('data-confirmed', '1');
        if (teleHidden) {
            teleHidden.value = teleId;
            teleHidden.setAttribute('data-name', teleNombre);
        }
        // Esperar a que el modal termine de cerrarse antes de re-submit
        // para evitar conflicto de aria-hidden/foco con el modal SR
        $('#modal_gestor_entrega').one('hidden.bs.modal', function() {
            document.body.focus();
            $('#crear_venta').submit();
        });
        $('#modal_gestor_entrega').modal('hide');
    });

    function guardarVenta() {
        var ultimaFactura = document.getElementById('ultima_factura');
        var motivoCierre = document.getElementById('motivo_cierre');
        if (ultimaFactura && ultimaFactura.checked && (!motivoCierre || !motivoCierre.value.trim())) {
            Swal.fire({ icon: 'warning', title: 'Motivo requerido', text: 'Indique por qué el cliente no comprará las cantidades restantes.' });
            return;
        }
        document.getElementById("btn_venta_coorporativa").disabled = true;

        var data = new FormData($('#crear_venta').get(0));
        // Forzar inclusión del cliente aunque el select esté deshabilitado (pedido vinculado)
        var clienteVal = $('#seleccionarCliente').val();
        if (clienteVal) data.set('seleccionarCliente', clienteVal);

        // Forzar inclusión del Asesor Comercial aunque el select esté deshabilitado
        // (bloqueado por el asesor asignado en Cartera de Clientes)
        var vendedorVal = $('#vendedor').val();
        if (vendedorVal) data.set('vendedor', vendedorVal);

        var tipoPagoVal = $('#tipoPagoVenta').val();
        if (tipoPagoVal) data.set('tipoPagoVenta', tipoPagoVal);

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

        // numero_venta está fuera del <form>; añadirlo manualmente
        var numeroVentaEl = document.getElementById('numero_venta');
        if (numeroVentaEl && numeroVentaEl.value) {
            data.set('numero_venta', numeroVentaEl.value);
        }

        // Exoneradas: el select se llama "codigoExoneracion" pero el backend espera "codigo"
        if (codigoActual === 'exoneradas') {
            var codigoExonerEl = document.getElementById('codigoExoneracion');
            if (codigoExonerEl && codigoExonerEl.value) {
                data.set('codigo', codigoExonerEl.value);
            }
        }

        var urlParams = new URLSearchParams(window.location.search);
        var modoEdicion = urlParams.get('modo');
        var autorizacionId = urlParams.get('autorizacion_id');
        var autorizadorId = urlParams.get('autorizador_id');
        var flujoIdUrl = urlParams.get('flujoId');
        if (modoEdicion) data.set('modo', modoEdicion);
        if (autorizacionId) data.set('autorizacion_id', autorizacionId);
        if (autorizadorId) data.set('autorizador_id', autorizadorId);
        // En editar_factura el campo flujo_vinculado_id puede estar vacío; usar URL como fallback
        if (flujoIdUrl && !data.get('flujo_id')) data.set('flujo_id', flujoIdUrl);

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
                    var btnImprimir = document.getElementById('btn_imprimir');
                    if (btnImprimir) btnImprimir.href = urlImprimir.replace('{id}', idFactura);
                }

                if (data.idFactura == 0) {
                    Swal.fire({ icon: data.icon, title: data.title, html: data.text });
                    document.getElementById("btn_venta_coorporativa").disabled = false;
                    var gestorHErr = document.getElementById('gestor_entrega_hidden');
                    if (gestorHErr) { gestorHErr.removeAttribute('data-confirmed'); }
                    return;
                }

                // Para cotizaciones, mostrar modal de opciones post-guardado
                if (codigoActual === 'cotizacion_clientes_a') {
                    _ofertaGuardadaId = data.idFactura;
                    _ofertaPedidoId   = data.pedidoId  || null;
                    _ofertaFlujoId    = data.flujoId   || null;
                    var msgEl = document.getElementById('msgNumOferta');
                    if (msgEl) msgEl.textContent = 'Oferta #' + data.idFactura + ' registrada exitosamente.';
                    eliminarVentaTemporal();
                    limpiarFormularioVenta(data);
                    $('#modalExitoOferta').modal('show');
                    return;
                }

                // ── Obtener flujo_id pre-seleccionado (desde URL param o campo oculto) ──
                var urlParams   = new URLSearchParams(window.location.search);
                var flujoIdUrl  = urlParams.get('flujoId');
                var flujoIdEl   = document.getElementById('flujo_vinculado_id');
                var flujoIdVal  = flujoIdUrl || (flujoIdEl ? flujoIdEl.value : '') || null;

                // ── Crear/actualizar registro de flujo para esta factura ───────
                // Se llama siempre: si no hay flujo previo, el backend lo crea automáticamente.
                var pedidoVinculadoEl = document.getElementById('pedido_vinculado_id');
                var pedidoIdVal = pedidoVinculadoEl ? (pedidoVinculadoEl.value || 0) : 0;
                var prefacturaVinculadaEl = document.getElementById('prefactura_vinculada_id');
                var prefacturaIdVal = prefacturaVinculadaEl ? (prefacturaVinculadaEl.value || 0) : 0;
                axios.post('/flujo/factura/confirmar', {
                    flujo_id:        flujoIdVal || 0,
                    factura_id:      data.idFactura,
                    pedido_id:       pedidoIdVal,
                    prefactura_id:   prefacturaIdVal,
                    expo_parcial:    urlParams.get('expo_parcial') === '1' ? 1 : 0,
                    tipo_factura_id: (tipoFacturaConfig ? tipoFacturaConfig.id : '')
                }).then(function(res) {
                    if (res.data && res.data.flujoId) {
                        _facturaFlujoId = res.data.flujoId;
                    }
                }).catch(function(err) {
                    console.warn('No se pudo registrar el flujo de factura:', err);
                });

                // ── Mostrar modal post-factura (mismo estilo que oferta) ───────
                _facturaGuardadaId = data.idFactura;
                _facturaFlujoId    = flujoIdVal ? parseInt(flujoIdVal, 10) : null;
                var msgFacturaEl = document.getElementById('msgNumFactura');
                if (msgFacturaEl) msgFacturaEl.textContent = 'Factura #' + data.idFactura + ' registrada exitosamente.';
                eliminarVentaTemporal();
                limpiarFormularioVenta(data);
                if (data.liquidacionExpo && ['PENDIENTE_LIQUIDACION', 'LIQUIDADA'].indexOf(data.liquidacionExpo.estado) !== -1) {
                    mostrarResumenLiquidacionExpo(data.liquidacionExpo).then(function() {
                        $('#modalExitoFactura').modal('show');
                    });
                } else {
                    $('#modalExitoFactura').modal('show');
                }

                // Limpiar código de autorización para próxima venta
                document.getElementById('codigo_autorizacion').value = '';
            })
            .catch(err => {
                document.getElementById("btn_venta_coorporativa").disabled = false;
                var gestorH = document.getElementById('gestor_entrega_hidden');
                if (gestorH) { gestorH.removeAttribute('data-confirmed'); }
                let data = err.response ? err.response.data : {};
                console.error('Error al guardar – status:', err.response ? err.response.status : 'sin respuesta', '| body:', data);
                let msg = data.text || data.mensaje || data.message || 'Error al guardar';
                if (data.errors) {
                    msg = Object.values(data.errors).flat().join('<br>');
                }
                Swal.fire({ icon: data.icon || 'error', title: data.title || 'Error', html: msg });
            });
    }

    function mostrarResumenLiquidacionExpo(resumen) {
        var moneda = function(valor) {
            return 'L ' + Number(valor || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };
        var escapar = function(valor) {
            return $('<div>').text(valor == null ? '' : String(valor)).html();
        };
        var facturas = (resumen.facturas || []).map(function(factura) {
            return '<tr><td>#' + factura.id + '</td><td>' + escapar(factura.numero) + '</td><td class="text-right">' + moneda(factura.subtotal_bruto) + '</td><td class="text-right">' + moneda(factura.total) + '</td></tr>';
        }).join('');
        var marcas = (resumen.descuentos_marca || []).map(function(regla) {
            return '<tr><td>' + escapar(regla.marca) + '</td><td class="text-right">' + Number(regla.porcentaje_descuento || 0).toFixed(2) + '%</td></tr>';
        }).join('');
        var pendientes = (resumen.lineas_pendientes || []).map(function(linea) {
            return '<tr><td>#' + linea.linea_id + '</td><td>' + escapar(linea.producto) + '</td><td class="text-right">' + Number(linea.cantidad_facturada || 0).toFixed(2) + '</td><td class="text-right">' + Number(linea.cantidad_pendiente || 0).toFixed(2) + '</td></tr>';
        }).join('');
        var aplicaciones = (resumen.aumentos_realizados || []).map(function(aplicacion) {
            return '<tr><td>' + escapar(aplicacion.factura || ('#' + aplicacion.factura_id)) + '</td><td class="text-right">' + moneda(aplicacion.monto) + '</td></tr>';
        }).join('');
        var requiereConfirmacion = resumen.estado === 'PENDIENTE_LIQUIDACION';
        var alerta = requiereConfirmacion
            ? '<div class="expo-liquidacion-estado info"><i class="fa fa-info-circle"></i><span>Este cierre pendiente aplicará el aumento mediante otros movimientos.</span></div>'
            : '<div class="expo-liquidacion-estado"><i class="fa fa-check-circle"></i><span>Oferta liquidada. El aumento correspondiente fue aplicado.</span></div>';
        var metrica = function(etiqueta, valor, destacada) {
            return '<div class="expo-liquidacion-metrica' + (destacada ? ' destacada' : '') + '"><span title="' + escapar(etiqueta) + '">' + escapar(etiqueta) + '</span><strong>' + valor + '</strong></div>';
        };
        var tabla = function(titulo, encabezado, filas, completa) {
            if (!filas) return '';
            return '<section class="expo-liquidacion-seccion' + (completa ? ' completa' : '') + '"><h3 class="expo-liquidacion-titulo">' + escapar(titulo) + '</h3><div class="expo-liquidacion-tabla-contenedor"><table class="expo-liquidacion-tabla"><thead>' + encabezado + '</thead><tbody>' + filas + '</tbody></table></div></section>';
        };
        return Swal.fire({
            icon: resumen.estado === 'LIQUIDADA' ? 'success' : 'warning',
            title: 'Liquidación final de la Oferta Expo',
            position: 'center',
            customClass: {
                container: 'liquidacion-expo-container',
                popup: 'liquidacion-expo-popup'
            },
            confirmButtonText: requiereConfirmacion ? 'Aplicar aumento' : 'Continuar',
            showCancelButton: requiereConfirmacion,
            cancelButtonText: 'Más tarde',
            showLoaderOnConfirm: requiereConfirmacion,
            allowOutsideClick: function() { return !Swal.isLoading(); },
            preConfirm: requiereConfirmacion ? function() {
                return axios.post('/expo/liquidacion/confirmar', {
                    cotizacion_id: resumen.cotizacion_id,
                    flujo_id: resumen.flujo_id
                }, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                }).then(function(response) {
                    return response.data.liquidacionExpo;
                }).catch(function(error) {
                    var data = error.response ? error.response.data : {};
                    Swal.showValidationMessage(escapar(data.text || data.message || 'No se pudo aplicar el aumento.'));
                    return false;
                });
            } : undefined,
            html: alerta
                + '<div class="expo-liquidacion-metricas">'
                + metrica('Total original', moneda(resumen.total_oferta))
                + metrica('Subtotal facturado', moneda(resumen.total_facturado))
                + metrica('Aumento aplicado', moneda(resumen.aumento_calculado), Number(resumen.aumento_calculado || 0) > 0)
                + metrica('Descuento por marca', moneda(resumen.descuento_marca_total))
                + metrica('Base general', moneda(resumen.base_general))
                + metrica('Descuento general', Number(resumen.porcentaje_descuento || 0).toFixed(2) + '% · ' + moneda(resumen.descuento_general))
                + metrica('Descuento otorgado', moneda(resumen.descuento_otorgado))
                + metrica('Descuento ganado', moneda(resumen.descuento_ganado))
                + '</div><div class="expo-liquidacion-grid">'
                + tabla('Facturas', '<tr><th>ID</th><th>Factura</th><th class="text-right">Subtotal</th><th class="text-right">Total</th></tr>', facturas)
                + tabla('Escalón por marca', '<tr><th>Marca</th><th class="text-right">%</th></tr>', marcas)
                + tabla('Productos no facturados', '<tr><th>Línea</th><th>Producto</th><th class="text-right">Facturado</th><th class="text-right">Pendiente</th></tr>', pendientes, true)
                + tabla('Aumentos realizados', '<tr><th>Factura</th><th class="text-right">Monto</th></tr>', aplicaciones, true)
                + '</div>'
        }).then(function(result) {
            if (requiereConfirmacion && result.isConfirmed && result.value) {
                return mostrarResumenLiquidacionExpo(result.value);
            }
            return result;
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
                    flujoId:       {!! json_encode($flujoVinculadoId ?? null) !!},
                    diasCreditoAprobados: {!! json_encode($diasCreditoAprobados) !!},
                    numeroOrdenCompra: {!! json_encode($documentosComerciales['numero_orden_compra'] ?? null) !!},
                    archivoOrdenCompra: {!! json_encode($documentosComerciales['archivo_orden_compra'] ?? null) !!},
                    numeroFormaF01: {!! json_encode($documentosComerciales['numero_forma_f01'] ?? null) !!},
                    archivoFormaF01: {!! json_encode($documentosComerciales['archivo_forma_f01'] ?? null) !!},
                }
            }));
        });
    </script>
    @endpush
    @endif

    @if(!empty($errorEscalaDuplicado))
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'Escala no disponible',
                text: @json($errorEscalaDuplicado)
            });
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
        var _modoPrefactura = {!! ($fromPrefactura && !$esOfertaExpo) ? 'true' : 'false' !!};
        var _seleccionExpo = {!! (!$duplicandoOferta && $esOfertaExpo && (!$fromPrefactura || request()->boolean('expo_parcial'))) ? 'true' : 'false' !!};
        var _productosDisponibles = @json($productosParaCarrito);

        function cargarProductosIniciales() {
            if (_productosAutoAgregados) return;
            if (_seleccionExpo) return;
            _productosAutoAgregados = true;

            var productos = _productosDisponibles;
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
                var esExonerada  = (codigoActual === 'exoneradas') || (tipoFacturaConfig && !tipoFacturaConfig.aplica_isv);
                if (esExonerada) {
                    isvUsar = 0;
                    isvPct = 0;
                    totalUsar = subTotalUsar;
                }
                var bodegaTexto  = prod.nombre_bodega || '';
                var esSinExistencia = !(parseFloat(prod.resta_inventario || 0) > 0);
                if (esSinExistencia) {
                    bodegaTexto = 'SIN EXISTENCIA';
                }
                var idBodega     = esSinExistencia ? '' : (prod.Bodega_id ?? '');
                var idSeccion    = esSinExistencia ? '' : (prod.seccion_id || '');
                var idUnidadVenta = prod.unidad_medida_venta_id || '';
                var bodegaBadgeBg = esSinExistencia ? '#ffebee' : '#e3f2fd';
                var bodegaBadgeColor = esSinExistencia ? '#c62828' : '#1565c0';
                var bodegaBadgeIcon = esSinExistencia ? 'fa-exclamation-circle' : 'fa-archive';

                var html = `
                <tr id='${idx}'>
                    <td style="vertical-align:middle; text-align:center; padding:4px 6px;">
                        <input id="idProducto${idx}" name="idProducto${idx}" type="hidden" value="${prod.producto_id || ''}">
                        <input id="cotizacionLineaId${idx}" name="cotizacionLineaId${idx}" type="hidden" value="${prod.cotizacion_has_producto_id || ''}">
                        <input id="cantidadOfertaExpo${idx}" type="hidden" value="${prod.cantidad_ofertada || prod.cantidad || 0}">
                        <input id="descuentoOfertaExpo${idx}" type="hidden" value="${prod.monto_descProducto || 0}">
                        <input id="marcaExpoId${idx}" type="hidden" value="${prod.marca_id || 0}">
                        <input id="marcaExpoNombre${idx}" type="hidden" value="${prod.marca_nombre || 'SIN MARCA'}">
                        <input id="precios_producto_carga_id${idx}" name="precios_producto_carga_id${idx}" type="hidden" value="${prod.precios_producto_carga_id || ''}">
                        <input id="isv${idx}" name="isv${idx}" type="hidden" value="${isvPct}">
                        <input id="idBodega${idx}" name="idBodega${idx}" type="hidden" value="${idBodega}">
                        <input id="idSeccion${idx}" name="idSeccion${idx}" type="hidden" value="${idSeccion}">
                        <input id="sinExistencia${idx}" name="sinExistencia${idx}" type="hidden" value="${esSinExistencia ? 1 : 0}">
                        <input id="restaInventario${idx}" name="restaInventario${idx}" type="hidden" value="${esSinExistencia ? 0 : cantidadUsar}">
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
                        <span style="background:${bodegaBadgeBg}; color:${bodegaBadgeColor}; border-radius:6px; padding:2px 8px; font-size:11px; font-weight:700;">
                            <i class="fa ${bodegaBadgeIcon}" style="font-size:10px;"></i> ${bodegaTexto}
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
                            oninput="calcularTotales(precio${idx},cantidad${idx},${isvPct},unidad${idx},${idx},restaInventario${idx})">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <input type="number" id="cantidad${idx}" name="cantidad${idx}" value="${cantidadUsar}" class="form-control form-control-sm" min="1" step="any" inputmode="decimal" data-parsley-required autocomplete="off" style="min-width:60px; font-size:11px;"
                            oninput="calcularTotales(precio${idx},cantidad${idx},${isvPct},unidad${idx},${idx},restaInventario${idx})">
                    </td>
                    <td style="vertical-align:middle; padding:4px 6px;">
                        <select class="form-control form-control-sm" name="unidad${idx}" id="unidad${idx}" data-parsley-required style="font-size:11px; min-width:80px;"
                            onchange="calcularTotales(precio${idx},cantidad${idx},${isvPct},unidad${idx},${idx},restaInventario${idx})">
                            <option value="1" data-id="${idUnidadVenta}" selected>U.</option>
                        </select>
                    </td>
                    ${expoConfig ? `<td style="vertical-align:middle; padding:4px 6px;"><div id="descuentoExpoProducto${idx}" style="font-size:10px; line-height:1.35;"></div></td>` : ''}
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
                actualizarContadorCarrito();
                programarGuardadoTemporal();
                enfocarCantidadCarrito(idx);
                resolve();
            });
        }

        function agregarProductoDesdeOferta(prod) {
            return new Promise(function (resolve) {
                if (!prod.producto_id) { resolve(); return; }

                // Priorizar la categoria_precios_id de la oferta original (prod) para
                // respetar la categoría con la que fue cotizado, no la actual del cliente.
                var categoriaId = prod.categoria_precios_id
                    || $('#categoria_cliente_venta_id').data('categoria-precio-id')
                    || $('#categoria_cliente_venta_id').val()
                    || '';

                axios.post(urls.datos_producto, {
                    idProducto: prod.producto_id,
                    categoria_cliente_venta_id: categoriaId,
                    precios_producto_carga_id: prod.precios_producto_carga_id || null
                }).then(function (response) {
                    var producto = response.data.producto;
                    var arrayUnidades = response.data.unidades;
                    var categoriaNombre = (prod.categoria_precios_nombre || '').toString().trim();
                    numeroInputs += 1;
                    var idx = numeroInputs;

                    // Construir select de unidades – pre-seleccionar la del duplicado
                    var htmlSelectUnidades = '';
                    arrayUnidades.forEach(function (u) {
                        var sel = (u.idUnidadVenta == prod.unidad_medida_venta_id) ? 'selected' : (u.valor_defecto == 1 && htmlSelectUnidades === '' ? 'selected' : '');
                        htmlSelectUnidades += '<option ' + sel + ' value="' + u.id + '" data-id="' + u.idUnidadVenta + '">' + u.nombre + '</option>';
                    });

                    // Precio de escala actual según el idPrecioSeleccionado de la oferta original
                    var idEscala = ((prod.idPrecioSeleccionado || '') + '').toLowerCase().trim();
                    var precioEscalaActual = parseFloat(prod.precioSeleccionado || 0);
                    if (!(precioEscalaActual > 0)) {
                        switch (idEscala) {
                            case 'a': case 'p1': precioEscalaActual = parseFloat(producto.precio1 || 0); break;
                            case 'b': case 'p2': precioEscalaActual = parseFloat(producto.precio2 || 0); break;
                            case 'c': case 'p3': precioEscalaActual = parseFloat(producto.precio3 || 0); break;
                            case 'd': case 'p4': precioEscalaActual = parseFloat(producto.precio4 || 0); break;
                            default:             precioEscalaActual = parseFloat(producto.precio1 || 0); break;
                        }
                    }
                    // PRECIO OPC = precio de escala actual; P. UNITARIO = precio que cobró el vendedor
                    var precioOpcFmt    = precioEscalaActual.toFixed(2);
                    var precioUnidFmt   = (parseFloat(prod.precio_unidad) || precioEscalaActual).toFixed(2);

                    // Precios
                    var htmlprecios = '';
                    if (categoriaNombre !== '') {
                        htmlprecios = '<option value="' + precioOpcFmt + '" data-id="p1" selected>' + precioOpcFmt + ' - ' + categoriaNombre + '</option>';
                    } else if (tipoFacturaConfig && tipoFacturaConfig.multiples_precios) {
                        var escalaMap = { 'p1': 'A', 'a': 'A', 'p2': 'B', 'b': 'B', 'p3': 'C', 'c': 'C', 'p4': 'D', 'd': 'D' };
                        var letraEscala = escalaMap[idEscala] || 'A';
                        htmlprecios = '<option value="' + producto.precio1 + '" data-id="p1"' + (letraEscala === 'A' ? ' selected' : '') + '>' + producto.precio1 + ' - A</option>';
                        if (producto.precio2) htmlprecios += '<option value="' + producto.precio2 + '" data-id="p2"' + (letraEscala === 'B' ? ' selected' : '') + '>' + producto.precio2 + ' - B</option>';
                        if (producto.precio3) htmlprecios += '<option value="' + producto.precio3 + '" data-id="p3"' + (letraEscala === 'C' ? ' selected' : '') + '>' + producto.precio3 + ' - C</option>';
                        if (producto.precio4) htmlprecios += '<option value="' + producto.precio4 + '" data-id="p4"' + (letraEscala === 'D' ? ' selected' : '') + '>' + producto.precio4 + ' - D</option>';
                    } else {
                        // Escala actual como única opción de referencia
                        htmlprecios = '<option value="' + precioOpcFmt + '" data-id="p1" selected>' + precioOpcFmt + ' - Escala</option>';
                    }

                    // Precio de referencia de la escala actual. NO bloquea la escritura (ver nota arriba);
                    // se valida al enviar el formulario en las facturas con restricción.
                    var precioEscalaRef = precioOpcFmt;
                    var cantidadUsar = prod.cantidad || 1;
                    var esSinExistencia = !(parseFloat(prod.resta_inventario || 0) > 0);
                    var bodegaTexto = esSinExistencia ? 'SIN EXISTENCIA' : (prod.nombre_bodega || '');
                    var idBodega = esSinExistencia ? '' : (prod['Bodega_id'] ?? '');
                    var idSeccion = esSinExistencia ? '' : (prod.seccion_id || '');
                    var bodegaBadgeBg = esSinExistencia ? '#ffebee' : '#e3f2fd';
                    var bodegaBadgeColor = esSinExistencia ? '#c62828' : '#1565c0';
                    var bodegaBadgeIcon = esSinExistencia ? 'fa-exclamation-circle' : 'fa-archive';

                    var html = `
                    <tr id='${idx}'>
                        <td style="vertical-align:middle; text-align:center; padding:4px 6px;">
                            <input id="idProducto${idx}" name="idProducto${idx}" type="hidden" value="${producto.id}">
                            <input id="cotizacionLineaId${idx}" name="cotizacionLineaId${idx}" type="hidden" value="${prod.cotizacion_has_producto_id || ''}">
                            <input id="lineaExpoOrigenId${idx}" type="hidden" value="${prod.linea_expo_origen_id || ''}">
                            <input id="cantidadOfertaExpo${idx}" type="hidden" value="${prod.cantidad_ofertada || prod.cantidad || 0}">
                            <input id="descuentoOfertaExpo${idx}" type="hidden" value="${prod.monto_descProducto || 0}">
                            <input id="marcaExpoId${idx}" type="hidden" value="${prod.marca_id || 0}">
                            <input id="marcaExpoNombre${idx}" type="hidden" value="${prod.marca_nombre || producto.marca || 'SIN MARCA'}">
                            <input id="precios_producto_carga_id${idx}" name="precios_producto_carga_id${idx}" type="hidden" value="${producto.precios_producto_carga_id || ''}">
                            <input id="isv${idx}" name="isv${idx}" type="hidden" value="${producto.isv}">
                            <input id="idBodega${idx}" name="idBodega${idx}" type="hidden" value="${idBodega}">
                            <input id="idSeccion${idx}" name="idSeccion${idx}" type="hidden" value="${idSeccion}">
                            <input id="sinExistencia${idx}" name="sinExistencia${idx}" type="hidden" value="${esSinExistencia ? 1 : 0}">
                            <input id="restaInventario${idx}" name="restaInventario${idx}" type="hidden" value="${esSinExistencia ? 0 : ''}">
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
                            <span style="background:${bodegaBadgeBg}; color:${bodegaBadgeColor}; border-radius:6px; padding:2px 8px; font-size:11px; font-weight:700;">
                                <i class="fa ${bodegaBadgeIcon}" style="font-size:10px;"></i> ${bodegaTexto}
                            </span>
                        </td>
                        <td style="vertical-align:middle; padding:4px 6px;">
                            <select class="form-control form-control-sm" name="precios${idx}" id="precios${idx}" data-parsley-required style="font-size:11px; min-width:100px;"
                                onchange="validacionPrecio(precios${idx}, precio${idx})">
                                ${htmlprecios}
                            </select>
                        </td>
                        <td style="vertical-align:middle; padding:4px 6px;">
                            <input type="number" id="precio${idx}" name="precio${idx}" value="${precioUnidFmt}" class="form-control form-control-sm"
                                data-precio-escala="${precioEscalaRef}" data-parsley-required step="any" autocomplete="off" style="min-width:80px; font-size:11px;"
                                oninput="calcularTotales(precio${idx},cantidad${idx},${producto.isv},unidad${idx},${idx},restaInventario${idx})">
                        </td>
                        <td style="vertical-align:middle; padding:4px 6px;">
                            <input type="number" id="cantidad${idx}" name="cantidad${idx}" value="${cantidadUsar}" class="form-control form-control-sm" min="1" step="any" inputmode="decimal" data-parsley-required autocomplete="off" style="min-width:60px; font-size:11px;"
                                oninput="calcularTotales(precio${idx},cantidad${idx},${producto.isv},unidad${idx},${idx},restaInventario${idx})">
                        </td>
                        <td style="vertical-align:middle; padding:4px 6px;">
                            <select class="form-control form-control-sm" name="unidad${idx}" id="unidad${idx}" data-parsley-required style="font-size:11px; min-width:80px;"
                                onchange="calcularTotales(precio${idx},cantidad${idx},${producto.isv},unidad${idx},${idx},restaInventario${idx})">
                                ${htmlSelectUnidades}
                            </select>
                        </td>
                        ${expoConfig ? `<td style="vertical-align:middle; padding:4px 6px;"><div id="descuentoExpoProducto${idx}" style="font-size:10px; line-height:1.35;"></div></td>` : ''}
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
                    actualizarContadorCarrito();

                    // Calcular totales para esta fila
                    calcularTotales(
                        document.getElementById('precio' + idx),
                        document.getElementById('cantidad' + idx),
                        producto.isv,
                        document.getElementById('unidad' + idx),
                        idx,
                        document.getElementById('restaInventario' + idx)
                    );
                    programarGuardadoTemporal();
                    enfocarCantidadCarrito(idx);
                    resolve();
                }).catch(function () { resolve(); });
            });
        }

        var expoSeleccionIndices = new Set();
        var expoPaginaActual = 1;
        var expoLineasPorPagina = 50;

        function expoNormalizarTexto(valor) {
            return String(valor || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        }

        function expoEscaparHtml(valor) {
            var elemento = document.createElement('div');
            elemento.textContent = valor == null ? '' : String(valor);
            return elemento.innerHTML;
        }

        function expoCantidadesCarritoPorLinea() {
            var cantidades = new Map();
            document.querySelectorAll('input[id^="cotizacionLineaId"]').forEach(function(inputLinea) {
                var lineaId = Number(inputLinea.value);
                if (!(lineaId > 0)) return;
                var indiceFila = inputLinea.id.replace('cotizacionLineaId', '');
                var cantidad = Number(document.getElementById('cantidad' + indiceFila)?.value || 0);
                var unidad = Number(document.getElementById('unidad' + indiceFila)?.value || 1);
                var cantidadBase = cantidad > 0 ? cantidad * (unidad > 0 ? unidad : 1) : 0;
                cantidades.set(lineaId, (cantidades.get(lineaId) || 0) + cantidadBase);
            });
            return cantidades;
        }

        function expoLineasConSaldo() {
            var cantidadesCarrito = expoCantidadesCarritoPorLinea();

            return _productosDisponibles.map(function(producto, indice) {
                var cantidadOriginal = Number(producto.cantidad || 0);
                var cantidadCarrito = cantidadesCarrito.get(Number(producto.cotizacion_has_producto_id)) || 0;
                return {
                    producto: producto,
                    indice: indice,
                    cantidadCarrito: cantidadCarrito,
                    cantidadPendiente: Math.max(0, cantidadOriginal - cantidadCarrito)
                };
            }).filter(function(item) {
                if (item.cantidadPendiente <= 0.0001) {
                    expoSeleccionIndices.delete(item.indice);
                    return false;
                }
                return true;
            });
        }

        function expoProductosFiltrados(lineasConSaldo) {
            var busqueda = expoNormalizarTexto((document.getElementById('expoBuscarLinea') || {}).value);
            var marca = String((document.getElementById('expoFiltrarMarca') || {}).value || '');
            var estado = String((document.getElementById('expoFiltrarEstado') || {}).value || 'todos');

            return (lineasConSaldo || expoLineasConSaldo()).filter(function(item) {
                var producto = item.producto;
                var coincideTexto = !busqueda || expoNormalizarTexto([
                    producto.producto_id,
                    producto.cotizacion_has_producto_id,
                    producto.nombre_producto,
                    producto.marca_nombre
                ].join(' ')).indexOf(busqueda) !== -1;
                var coincideMarca = !marca || String(producto.marca_id || 0) === marca;
                var seleccionado = expoSeleccionIndices.has(item.indice);
                var coincideEstado = estado === 'todos'
                    || (estado === 'sin_carrito' && item.cantidadCarrito <= 0.0001)
                    || (estado === 'parciales' && item.cantidadCarrito > 0.0001)
                    || (estado === 'seleccionados' && seleccionado)
                    ;
                return coincideTexto && coincideMarca && coincideEstado;
            });
        }

        function expoRenderizarPendientes() {
            var lista = document.getElementById('expoPendientesLista');
            if (!lista) return;
            var lineasConSaldo = expoLineasConSaldo();
            var filtrados = expoProductosFiltrados(lineasConSaldo);
            var totalPaginas = Math.max(1, Math.ceil(filtrados.length / expoLineasPorPagina));
            expoPaginaActual = Math.min(expoPaginaActual, totalPaginas);
            var inicio = (expoPaginaActual - 1) * expoLineasPorPagina;
            var pagina = filtrados.slice(inicio, inicio + expoLineasPorPagina);

            if (pagina.length === 0) {
                lista.innerHTML = '<div class="expo-pendientes-vacio"><i class="fa fa-search fa-2x mb-2"></i><br>No hay líneas que coincidan con los filtros.</div>';
            } else {
                lista.innerHTML = pagina.map(function(item) {
                    var producto = item.producto;
                    var seleccionado = expoSeleccionIndices.has(item.indice);
                    var enCarrito = item.cantidadCarrito > 0.0001;
                    var clases = 'expo-linea-item' + (seleccionado ? ' seleccionada' : '') + (enCarrito ? ' en-carrito' : '');
                    var pendiente = item.cantidadPendiente.toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    var cantidadCarrito = item.cantidadCarrito.toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    return '<label class="' + clases + '">'
                        + '<input type="checkbox" class="expo-linea-selector" data-producto-indice="' + item.indice + '" '
                        + (seleccionado ? 'checked ' : '') + (enCarrito ? 'disabled ' : '')
                        + 'onchange="expoCambiarSeleccion(' + item.indice + ', this.checked)">'
                        + '<span class="expo-linea-info"><span class="expo-linea-nombre">' + expoEscaparHtml(producto.nombre_producto) + '</span>'
                        + '<span class="expo-linea-meta"><span class="expo-linea-chip codigo">#' + expoEscaparHtml(producto.producto_id) + '</span>'
                        + '<span class="expo-linea-chip marca">' + expoEscaparHtml(producto.marca_nombre || 'SIN MARCA') + '</span>'
                        + (enCarrito ? '<span class="expo-linea-chip" style="background:#e3f2fd;color:#1565c0;">EN CARRITO: ' + cantidadCarrito + '</span>' : '')
                        + '<span class="expo-linea-pendiente">Pendiente: ' + pendiente + '</span></span></span></label>';
                }).join('');
            }

            document.getElementById('expoTotalLineas').textContent = lineasConSaldo.length;
            document.getElementById('expoResultadosLineas').textContent = filtrados.length;
            document.getElementById('expoSeleccionadasLineas').textContent = expoSeleccionIndices.size;
            document.getElementById('expoCantidadAgregar').textContent = expoSeleccionIndices.size;
            document.getElementById('expoRangoLineas').textContent = filtrados.length
                ? 'Mostrando ' + (inicio + 1) + '-' + Math.min(inicio + expoLineasPorPagina, filtrados.length) + ' de ' + filtrados.length
                : 'Sin resultados';
            document.getElementById('expoPaginaEstado').textContent = 'Página ' + expoPaginaActual + ' de ' + totalPaginas;
            document.getElementById('expoPaginaAnterior').disabled = expoPaginaActual <= 1;
            document.getElementById('expoPaginaSiguiente').disabled = expoPaginaActual >= totalPaginas;
        }

        window.expoCambiarSeleccion = function(indice, seleccionado) {
            if (seleccionado) expoSeleccionIndices.add(Number(indice));
            else expoSeleccionIndices.delete(Number(indice));
            expoRenderizarPendientes();
        };

        window.expoCambiarPagina = function(direccion) {
            expoPaginaActual += Number(direccion);
            expoRenderizarPendientes();
            var lista = document.getElementById('expoPendientesLista');
            if (lista) lista.scrollTop = 0;
        };

        window.expoAlternarTodas = function(seleccionado) {
            if (!seleccionado) {
                expoSeleccionIndices.clear();
                expoRenderizarPendientes();
                return;
            }
            expoProductosFiltrados().forEach(function(item) {
                if (item.cantidadCarrito <= 0.0001) expoSeleccionIndices.add(item.indice);
            });
            expoRenderizarPendientes();
        };

        window.expoSeleccionarMarca = function() {
            var filtroMarca = document.getElementById('expoFiltrarMarca');
            var marcaId = String(filtroMarca?.value || '');
            if (!marcaId) {
                Swal.fire({ icon: 'info', title: 'Seleccione una marca', text: 'Use el filtro de marca y luego presione Seleccionar marca.' });
                return;
            }
            expoLineasConSaldo().forEach(function(item) {
                if (String(item.producto.marca_id || 0) === marcaId && item.cantidadCarrito <= 0.0001) {
                    expoSeleccionIndices.add(item.indice);
                }
            });
            expoRenderizarPendientes();
        };

        window.expoAgregarSeleccionados = function() {
            var cantidadesCarrito = expoCantidadesCarritoPorLinea();
            var seleccionados = Array.from(expoSeleccionIndices)
                .map(function(indice) { return _productosDisponibles[indice]; })
                .filter(function(prod) {
                    if (!prod) return false;
                    return !(cantidadesCarrito.get(Number(prod.cotizacion_has_producto_id)) > 0.0001);
                });

            if (seleccionados.length === 0) {
                Swal.fire({ icon: 'info', title: 'Sin líneas nuevas', text: 'Seleccione al menos una línea pendiente que no esté en el carrito.' });
                return;
            }

            var chain = Promise.resolve();
            seleccionados.forEach(function(prod) {
                chain = chain.then(function() { return agregarProductoDesdeOferta(prod); });
            });
            chain.then(function() {
                expoSeleccionIndices.clear();
                expoPaginaActual = 1;
                expoRenderizarPendientes();
                Swal.fire({
                    icon: 'success',
                    title: 'Selección agregada',
                    text: seleccionados.length + ' línea(s) agregada(s). Puede reducir sus cantidades antes de facturar.',
                    timer: 2200,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            });
        };

        function inicializarExpoPendientes() {
            var buscador = document.getElementById('expoBuscarLinea');
            var filtroMarca = document.getElementById('expoFiltrarMarca');
            var filtroEstado = document.getElementById('expoFiltrarEstado');
            if (!buscador || !filtroMarca || !filtroEstado) return;
            buscador.addEventListener('input', function() { expoPaginaActual = 1; expoRenderizarPendientes(); });
            filtroMarca.addEventListener('change', function() { expoPaginaActual = 1; expoRenderizarPendientes(); });
            filtroEstado.addEventListener('change', function() { expoPaginaActual = 1; expoRenderizarPendientes(); });
            expoRenderizarPendientes();
        }

        window.expoActualizarPendientes = expoRenderizarPendientes;
        inicializarExpoPendientes();

        // Disparar auto-carga cuando el cliente esté completamente cargado
        window.addEventListener('cliente-datos-cargados', function onClienteListo() {
            window.removeEventListener('cliente-datos-cargados', onClienteListo);
            cargarProductosIniciales();
        });

        @if(!$clientePedido)
        // Otro cliente: no hay cliente pre-seleccionado; cargar productos al iniciar la página
        document.addEventListener('livewire:load', function () {
            setTimeout(function() { cargarProductosIniciales(); }, 300);
        });
        @endif
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
