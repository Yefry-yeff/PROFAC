<div>
    <style>
        /* ── Fix: fadeInRight debe terminar en transform:none para no crear
                  un "containing block" que rompa position:fixed en modales ── */
        @keyframes fadeInRight {
            0%   { opacity: 0; transform: translateX(20px); }
            100% { opacity: 1; transform: none; }
        }
        @-webkit-keyframes fadeInRight {
            0%   { opacity: 0; -webkit-transform: translateX(20px); }
            100% { opacity: 1; -webkit-transform: none; }
        }

        /* ── Cards de navegación principal ── */
        .vnav-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            margin-top: 4px;
        }
        @media (max-width: 1100px) { .vnav-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width:  700px) { .vnav-grid { grid-template-columns: repeat(2, 1fr); } }

        .vnav-item {
            background: #fff;
            border-radius: 14px;
            border-left: 5px solid transparent;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 28px 22px 24px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-left-color 0.15s ease;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }
        .vnav-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 28px rgba(0,0,0,0.13);
            text-decoration: none;
        }
        .vnav-item::after {
            content: "→";
            position: absolute;
            bottom: 18px;
            right: 20px;
            font-size: 16px;
            opacity: 0;
            transition: opacity 0.2s ease, right 0.2s ease;
        }
        .vnav-item:hover::after { opacity: 0.5; right: 16px; }

        .vnav-icon {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 18px;
            flex-shrink: 0;
        }
        .vnav-title {
            font-size: 15px;
            font-weight: 700;
            color: #1a2332;
            margin-bottom: 4px;
            line-height: 1.2;
        }
        .vnav-desc {
            font-size: 12px;
            color: #8a96a3;
            line-height: 1.4;
            margin: 0;
        }

        /* Paleta por opción */
        .vnav-pedido    { border-left-color: #1a73e8; }
        .vnav-pedido    .vnav-icon { background: rgba(26,115,232,0.10); color: #1a73e8; }
        .vnav-pedido::after { color: #1a73e8; }

        .vnav-oferta    { border-left-color: #00a86b; }
        .vnav-oferta    .vnav-icon { background: rgba(0,168,107,0.10); color: #00a86b; }
        .vnav-oferta::after { color: #00a86b; }

        .vnav-prefactura{ border-left-color: #0097a7; }
        .vnav-prefactura .vnav-icon { background: rgba(0,151,167,0.10); color: #0097a7; }
        .vnav-prefactura::after { color: #0097a7; }

        .vnav-factura   { border-left-color: #f9a826; }
        .vnav-factura   .vnav-icon { background: rgba(249,168,38,0.12); color: #f9a826; }
        .vnav-factura::after { color: #f9a826; }

        .vnav-historial { border-left-color: #6c5ce7; }
        .vnav-historial .vnav-icon { background: rgba(108,92,231,0.10); color: #6c5ce7; }
        .vnav-historial::after { color: #6c5ce7; }

        /* Mantener clases legacy para submenús */
        .flujo-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            cursor: pointer;
            overflow: hidden;
        }
        .flujo-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.15);
        }
        .flujo-card .card-icon-wrap {
            padding: 18px 16px 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .flujo-card .icon-circle {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            font-size: 22px;
        }
        .flujo-card .card-label {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 0;
            color: #2c3e50;
            text-align: center;
        }

        /* Colores por tipo */
        .flujo-pedido   .icon-circle { background: rgba(26, 115, 232, 0.12); color: #1a73e8; }
        .flujo-pedido   .card-footer-bar { background: #1a73e8; }
        .flujo-pedido:hover { border-top: 4px solid #1a73e8; }

        .flujo-oferta .icon-circle { background: rgba(0, 168, 107, 0.12); color: #00a86b; }
        .flujo-oferta .card-footer-bar { background: #00a86b; }
        .flujo-oferta:hover { border-top: 4px solid #00a86b; }

        .flujo-prefactura .icon-circle { background: rgba(0, 151, 167, 0.12); color: #0097a7; }
        .flujo-prefactura .card-footer-bar { background: #0097a7; }
        .flujo-prefactura:hover { border-top: 4px solid #0097a7; }

        .flujo-factura  .icon-circle { background: rgba(249, 168, 38, 0.15); color: #f9a826; }
        .flujo-factura  .card-footer-bar { background: #f9a826; }
        .flujo-factura:hover { border-top: 4px solid #f9a826; }

        /* Sub-tipos factura */
        .flujo-ca  .icon-circle { background: rgba(17, 153, 193, 0.12); color: #1199c1; }
        .flujo-ca  .card-footer-bar { background: #1199c1; }
        .flujo-ca:hover { border-top: 4px solid #1199c1; }

        .flujo-cb  .icon-circle { background: rgba(58, 83, 163, 0.12); color: #3a53a3; }
        .flujo-cb  .card-footer-bar { background: #3a53a3; }
        .flujo-cb:hover { border-top: 4px solid #3a53a3; }

        .flujo-sra .icon-circle { background: rgba(211, 47, 47, 0.12); color: #d32f2f; }
        .flujo-sra .card-footer-bar { background: #d32f2f; }
        .flujo-sra:hover { border-top: 4px solid #d32f2f; }

        .flujo-srb .icon-circle { background: rgba(66, 66, 66, 0.1); color: #424242; }
        .flujo-srb .card-footer-bar { background: #424242; }
        .flujo-srb:hover { border-top: 4px solid #424242; }

        .flujo-exonerada .icon-circle { background: rgba(0, 137, 123, 0.12); color: #00897b; }
        .flujo-exonerada .card-footer-bar { background: #00897b; }
        .flujo-exonerada:hover { border-top: 4px solid #00897b; }

        .flujo-header-banner {
            background: linear-gradient(135deg, #EC401B 0%, #b52e10 100%);
            border-radius: 16px;
            padding: 32px 36px;
            color: #fff;
            margin-bottom: 32px;
        }
        .flujo-header-banner h3 { font-size: 22px; font-weight: 700; margin: 0 0 6px; }
        .flujo-header-banner p  { margin: 0; opacity: 0.85; font-size: 14px; }
        .flujo-header-banner .banner-icon { font-size: 48px; opacity: 0.25; }

        .flujo-back-btn {
            border-radius: 8px;
            padding: 8px 18px;
            font-weight: 600;
            font-size: 13px;
        }
    </style>

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>{{ $titulo }}</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Inicio</a>
                </li>
                @if($step == 'factura_options')
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)" wire:click="goBack">Ventas</a>
                    </li>
                    <li class="breadcrumb-item active"><strong>Tipo de Factura</strong></li>
                @elseif($step == 'oferta')
                    <li class="breadcrumb-item active"><strong>Oferta</strong></li>
                @elseif($step == 'prefactura')
                    <li class="breadcrumb-item active"><strong>Prefactura</strong></li>
                @elseif($step == 'pedido_options')
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)" wire:click="goBack">Ventas</a>
                    </li>
                    <li class="breadcrumb-item active"><strong>Pedido</strong></li>
                @elseif($step == 'historial_pedidos')
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)" wire:click="goBack">Ventas</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)" wire:click="selectPedido">Pedido</a>
                    </li>
                    <li class="breadcrumb-item active"><strong>Historial de Pedidos</strong></li>
                @else
                    <li class="breadcrumb-item active"><strong>Ventas</strong></li>
                @endif
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        @if($step == 'select')

            {{-- Banner superior --}}
            <div class="flujo-header-banner d-flex align-items-center justify-content-between">
                <div>
                    <h3><i class="mr-2 fa fa-exchange"></i> Ventas</h3>
                    <p>Seleccione el tipo de operación que desea realizar</p>
                </div>
                <div class="banner-icon d-none d-md-block">
                    <i class="fa fa-chart-line"></i>
                </div>
            </div>

            <div class="vnav-grid">

                {{-- PEDIDO --}}
                <div class="vnav-item vnav-pedido" wire:click="selectPedido">
                    <div class="vnav-icon"><i class="fa fa-clipboard-list"></i></div>
                    <div class="vnav-title">Pedido</div>
                    <p class="vnav-desc">Registra y gestiona pedidos de clientes</p>
                </div>

                {{-- OFERTA --}}
                <div class="vnav-item vnav-oferta" wire:click="selectOferta">
                    <div class="vnav-icon"><i class="fa fa-tags"></i></div>
                    <div class="vnav-title">Oferta</div>
                    <p class="vnav-desc">Elabora y envía cotizaciones a clientes</p>
                </div>

                {{-- PREFACTURA --}}
                <div class="vnav-item vnav-prefactura" wire:click="selectPrefactura">
                    <div class="vnav-icon"><i class="fa fa-file-invoice"></i></div>
                    <div class="vnav-title">Prefactura</div>
                    <p class="vnav-desc">Genera prefactura desde oferta ganadora</p>
                </div>

                {{-- FACTURA --}}
                <div class="vnav-item vnav-factura" wire:click="selectFactura">
                    <div class="vnav-icon"><i class="fa fa-receipt"></i></div>
                    <div class="vnav-title">Factura</div>
                    <p class="vnav-desc">Emite facturas A, B, SR o Exonerada</p>
                </div>

                {{-- HISTORIAL DE VENTAS --}}
                <a href="{{ route('flujo.ventas.historico') }}" class="vnav-item vnav-historial">
                    <div class="vnav-icon"><i class="fa fa-history"></i></div>
                    <div class="vnav-title">Historial</div>
                    <p class="vnav-desc">Consulta el registro histórico de ventas</p>
                </a>

            </div>

        @elseif($step == 'pedido_options')

            {{-- Submenu Pedido: Historial / Ingresar --}}
            <div class="flujo-header-banner d-flex align-items-center justify-content-between"
                 style="background: linear-gradient(135deg, #1a73e8 0%, #0d6efd 100%);">
                <div>
                    <button type="button" class="mb-3 btn btn-outline-light flujo-back-btn" wire:click="goBack">
                        <i class="mr-1 fa fa-arrow-left"></i> Volver
                    </button>
                    <h3><i class="mr-2 fa fa-clipboard-list"></i> Pedido</h3>
                    <p>Seleccione la opción que desea realizar</p>
                </div>
                <div class="banner-icon d-none d-md-block"><i class="fa fa-clipboard-list"></i></div>
            </div>

            <div class="row justify-content-center">

                {{-- Historial de Pedidos --}}
                <div class="mb-4 col-lg-3 col-md-4 col-sm-6">
                    <div class="flujo-card ibox" style="border-top:4px solid transparent; cursor:pointer;"
                         onmouseover="this.style.borderTopColor='#1a73e8';this.style.transform='translateY(-6px)';"
                         onmouseout="this.style.borderTopColor='transparent';this.style.transform='';"
                         wire:click="selectHistorialPedidos">
                        <div class="card-icon-wrap">
                            <div class="icon-circle" style="background:rgba(26,115,232,0.12); color:#1a73e8;">
                                <i class="fa fa-history"></i>
                            </div>
                            <div class="card-label">Historial de Pedidos</div>
                            <div class="card-desc">Consulta todos los pedidos, genera ofertas o anula</div>
                        </div>
                        <div class="card-footer-bar" style="background:#1a73e8;">
                            <i class="mr-1 fa fa-arrow-right"></i> Ver Historial
                        </div>
                    </div>
                </div>

                {{-- Ingresar Pedido --}}
                <div class="mb-4 col-lg-3 col-md-4 col-sm-6">
                    <div class="flujo-card flujo-pedido ibox" wire:click="selectIngresarPedido">
                        <div class="card-icon-wrap">
                            <div class="icon-circle">
                                <i class="fa fa-plus-circle"></i>
                            </div>
                            <div class="card-label">Ingresar Pedido</div>
                            <div class="card-desc">Registra un nuevo pedido de cliente</div>
                        </div>
                        <div class="card-footer-bar">
                            <i class="mr-1 fa fa-arrow-right"></i> Nuevo Pedido
                        </div>
                    </div>
                </div>

            </div>

        @elseif($step == 'historial_pedidos')

            {{-- Historial de todos los pedidos --}}
            <div class="ibox" style="border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.07);">
                <div class="ibox-title d-flex align-items-center justify-content-between"
                     style="background:linear-gradient(135deg,#1565c0,#1a73e8); border:none; padding:14px 22px;">
                    <h5 style="color:#fff; margin:0; font-weight:700;">
                        <i class="mr-2 fa fa-history"></i> Historial de Pedidos
                    </h5>
                    <button type="button" class="btn btn-outline-light btn-sm flujo-back-btn" wire:click="selectPedido">
                        <i class="mr-1 fa fa-arrow-left"></i> Volver
                    </button>
                </div>
                <div class="ibox-content" style="padding:20px 24px;">
                    <livewire:flujo.listar-historial-pedidos />
                </div>
            </div>

        @elseif($step == 'oferta')

            {{-- Componente lista de pedidos para ofertar --}}
            <div class="ibox" style="border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.07);">
                <div class="ibox-title d-flex align-items-center justify-content-between"
                     style="background:linear-gradient(135deg,#e65100,#f9a826); border:none; padding:14px 22px;">
                    <h5 style="color:#fff; margin:0; font-weight:700;">
                        <i class="mr-2 fa fa-file-text-o"></i> Ofertas
                    </h5>
                    <button type="button" class="btn btn-outline-light btn-sm flujo-back-btn" wire:click="goBack">
                        <i class="mr-1 fa fa-arrow-left"></i> Volver
                    </button>
                </div>
                <div class="ibox-content" style="padding:20px 24px;">
                    <livewire:flujo.listar-pedidos-para-ofertar />
                </div>
            </div>

        @elseif($step == 'prefactura')

            {{-- Banner --}}
            <div class="flujo-header-banner d-flex align-items-center justify-content-between"
                 style="background: linear-gradient(135deg, #00838f 0%, #0097a7 100%);">
                <div>
                    <button type="button" class="mb-3 btn btn-outline-light flujo-back-btn" wire:click="goBack">
                        <i class="mr-1 fa fa-arrow-left"></i> Volver
                    </button>
                    <h3><i class="mr-2 fa fa-file-invoice"></i> Prefactura</h3>
                    <p>Aprueba una oferta como ganadora para convertirla en prefactura</p>
                </div>
                <div class="banner-icon d-none d-md-block"><i class="fa fa-file-invoice"></i></div>
            </div>

            {{-- Componente lista de ofertas para aprobar como prefactura --}}
            <div class="ibox" style="border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.07);">
                <div class="ibox-title" style="background:linear-gradient(135deg,#00838f,#0097a7); border:none; padding:14px 22px;">
                    <h5 style="color:#fff; margin:0; font-weight:700;">
                        <i class="mr-2 fa fa-check-circle"></i> Ofertas activas — selecciona la ganadora
                    </h5>
                </div>
                <div class="ibox-content" style="padding:20px 24px;">
                    <livewire:flujo.listar-ofertas-para-prefactura />
                </div>
            </div>

        @elseif($step == 'factura_options')

            {{-- Banner superior --}}
            <div class="flujo-header-banner d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #f9a826 0%, #e65100 100%);">
                <div>
                    <button type="button" class="mb-3 btn btn-outline-light flujo-back-btn" wire:click="goBack">
                        <i class="mr-1 fa fa-arrow-left"></i> Volver
                    </button>
                    <h3><i class="mr-2 fa fa-receipt"></i> Tipo de Factura</h3>
                    <p>Seleccione la categoría de factura que desea emitir</p>
                </div>
                <div class="banner-icon d-none d-md-block">
                    <i class="fa fa-file-invoice-dollar"></i>
                </div>
            </div>

            {{-- Prefacturas pendientes de convertir a factura --}}
            <div class="mb-4 ibox" style="border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.07);">
                <div class="ibox-title" style="background:linear-gradient(135deg,#e65100,#f9a826); border:none; padding:14px 22px;">
                    <h5 style="color:#fff; margin:0; font-weight:700;">
                        <i class="mr-2 fa fa-file-invoice"></i> Prefacturas listas para facturar
                    </h5>
                </div>
                <div class="ibox-content" style="padding:20px 24px;">
                    <livewire:flujo.listar-prefacturas-para-factura />
                </div>
            </div>

            <h5 style="color:#546e7a; font-weight:700; margin-bottom:16px; text-align:center;">
                <i class="mr-2 fa fa-plus-circle"></i> — O crea una factura nueva —
            </h5>

            <div class="row justify-content-center">

                {{-- CLIENTES A --}}
                <div class="mb-4 col-lg-3 col-md-4 col-sm-6">
                    <div class="flujo-card flujo-ca ibox" wire:click="selectFacturaSubtype('clientes_a')">
                        <div class="card-icon-wrap">
                            <div class="icon-circle">
                                <i class="fa fa-user-tie"></i>
                            </div>
                            <div class="card-label">Clientes A</div>
                            <div class="card-desc">Facturación estándar para clientes tipo A</div>
                        </div>
                        <div class="card-footer-bar">
                            <i class="mr-1 fa fa-arrow-right"></i> Ir a Factura
                        </div>
                    </div>
                </div>

                {{-- CLIENTES B --}}
                <div class="mb-4 col-lg-3 col-md-4 col-sm-6">
                    <div class="flujo-card flujo-cb ibox" wire:click="selectFacturaSubtype('clientes_b')">
                        <div class="card-icon-wrap">
                            <div class="icon-circle">
                                <i class="fa fa-users"></i>
                            </div>
                            <div class="card-label">Clientes B</div>
                            <div class="card-desc">Facturación estándar para clientes tipo B</div>
                        </div>
                        <div class="card-footer-bar">
                            <i class="mr-1 fa fa-arrow-right"></i> Ir a Factura
                        </div>
                    </div>
                </div>

                {{-- SR / CLIENTES A --}}
                <div class="mb-4 col-lg-3 col-md-4 col-sm-6">
                    <div class="flujo-card flujo-sra ibox" wire:click="selectFacturaSubtype('sr_clientes_a')">
                        <div class="card-icon-wrap">
                            <div class="icon-circle">
                                <i class="fa fa-shield-alt"></i>
                            </div>
                            <div class="card-label">SR / Clientes A</div>
                            <div class="card-desc">Sin restricción de precio – Clientes A</div>
                        </div>
                        <div class="card-footer-bar">
                            <i class="mr-1 fa fa-arrow-right"></i> Ir a Factura
                        </div>
                    </div>
                </div>

                {{-- SR / CLIENTES B --}}
                <div class="mb-4 col-lg-3 col-md-4 col-sm-6">
                    <div class="flujo-card flujo-srb ibox" wire:click="selectFacturaSubtype('sr_clientes_b')">
                        <div class="card-icon-wrap">
                            <div class="icon-circle">
                                <i class="fa fa-lock-open"></i>
                            </div>
                            <div class="card-label">SR / Clientes B</div>
                            <div class="card-desc">Sin restricción de precio – Clientes B</div>
                        </div>
                        <div class="card-footer-bar">
                            <i class="mr-1 fa fa-arrow-right"></i> Ir a Factura
                        </div>
                    </div>
                </div>

                {{-- EXONERADA --}}
                <div class="mb-4 col-lg-3 col-md-4 col-sm-6">
                    <div class="flujo-card flujo-exonerada ibox" wire:click="selectFacturaSubtype('exonerada')">
                        <div class="card-icon-wrap">
                            <div class="icon-circle">
                                <i class="fa fa-file-invoice"></i>
                            </div>
                            <div class="card-label">Exonerada</div>
                            <div class="card-desc">Facturación para clientes exonerados de impuesto</div>
                        </div>
                        <div class="card-footer-bar">
                            <i class="mr-1 fa fa-arrow-right"></i> Ir a Factura
                        </div>
                    </div>
                </div>

            </div>

        @endif

    </div>

    {{-- Modal flujo de pedido (compartido entre Historial y Oferta) --}}
    <livewire:flujo.modal-flujo-pedido />
</div>
