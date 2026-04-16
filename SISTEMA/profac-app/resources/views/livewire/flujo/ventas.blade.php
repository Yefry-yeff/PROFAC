<div>
    <style>
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
            padding: 36px 20px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .flujo-card .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            font-size: 32px;
        }
        .flujo-card .card-label {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 6px;
            color: #2c3e50;
        }
        .flujo-card .card-desc {
            font-size: 13px;
            color: #8e9aaa;
            margin-bottom: 20px;
            text-align: center;
        }
        .flujo-card .card-footer-bar {
            padding: 14px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-align: center;
            color: #fff;
        }

        /* Colores por tipo */
        .flujo-pedido   .icon-circle { background: rgba(26, 115, 232, 0.12); color: #1a73e8; }
        .flujo-pedido   .card-footer-bar { background: #1a73e8; }
        .flujo-pedido:hover { border-top: 4px solid #1a73e8; }

        .flujo-cotizacion .icon-circle { background: rgba(0, 168, 107, 0.12); color: #00a86b; }
        .flujo-cotizacion .card-footer-bar { background: #00a86b; }
        .flujo-cotizacion:hover { border-top: 4px solid #00a86b; }

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
                    <h3><i class="fa fa-exchange mr-2"></i> Ventas</h3>
                    <p>Seleccione el tipo de operación que desea realizar</p>
                </div>
                <div class="banner-icon d-none d-md-block">
                    <i class="fa fa-chart-line"></i>
                </div>
            </div>

            <div class="row justify-content-center">

                {{-- PEDIDO --}}
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="flujo-card flujo-pedido ibox" wire:click="selectPedido">
                        <div class="card-icon-wrap">
                            <div class="icon-circle">
                                <i class="fa fa-clipboard-list"></i>
                            </div>
                            <div class="card-label">Pedido</div>
                            <div class="card-desc">Registra y gestiona pedidos de clientes</div>
                        </div>
                        <div class="card-footer-bar">
                            <i class="fa fa-arrow-right mr-1"></i> Ir a Pedidos
                        </div>
                    </div>
                </div>

                {{-- COTIZACIÓN --}}
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="flujo-card flujo-cotizacion ibox" wire:click="selectCotizacion">
                        <div class="card-icon-wrap">
                            <div class="icon-circle">
                                <i class="fa fa-file-signature"></i>
                            </div>
                            <div class="card-label">Cotización</div>
                            <div class="card-desc">Elabora y envía cotizaciones u ofertas</div>
                        </div>
                        <div class="card-footer-bar">
                            <i class="fa fa-arrow-right mr-1"></i> Ir a Cotización
                        </div>
                    </div>
                </div>

                {{-- FACTURA --}}
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="flujo-card flujo-factura ibox" wire:click="selectFactura">
                        <div class="card-icon-wrap">
                            <div class="icon-circle">
                                <i class="fa fa-receipt"></i>
                            </div>
                            <div class="card-label">Factura</div>
                            <div class="card-desc">Emite facturas para clientes A, B, SR o Exonerada</div>
                        </div>
                        <div class="card-footer-bar">
                            <i class="fa fa-arrow-right mr-1"></i> Seleccionar Tipo
                        </div>
                    </div>
                </div>

                {{-- HISTORIAL DE VENTAS --}}
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <a href="{{ route('flujo.ventas.historico') }}" style="text-decoration:none; display:block;">
                        <div class="flujo-card ibox" style="border-top:4px solid transparent; transition:border-top-color .2s;"
                             onmouseover="this.style.borderTopColor='#6c5ce7';"
                             onmouseout="this.style.borderTopColor='transparent';">
                            <div class="card-icon-wrap">
                                <div class="icon-circle" style="background:rgba(108,92,231,0.12); color:#6c5ce7;">
                                    <i class="fa fa-history"></i>
                                </div>
                                <div class="card-label">Historial de Ventas</div>
                                <div class="card-desc">Consulta y gestiona el registro histórico de ventas</div>
                            </div>
                            <div class="card-footer-bar" style="background:#6c5ce7;">
                                <i class="fa fa-arrow-right mr-1"></i> Ver Historial
                            </div>
                        </div>
                    </a>
                </div>

            </div>

        @elseif($step == 'factura_options')

            {{-- Banner superior --}}
            <div class="flujo-header-banner d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #f9a826 0%, #e65100 100%);">
                <div>
                    <button type="button" class="btn btn-outline-light flujo-back-btn mb-3" wire:click="goBack">
                        <i class="fa fa-arrow-left mr-1"></i> Volver
                    </button>
                    <h3><i class="fa fa-receipt mr-2"></i> Tipo de Factura</h3>
                    <p>Seleccione la categoría de factura que desea emitir</p>
                </div>
                <div class="banner-icon d-none d-md-block">
                    <i class="fa fa-file-invoice-dollar"></i>
                </div>
            </div>

            <div class="row justify-content-center">

                {{-- CLIENTES A --}}
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="flujo-card flujo-ca ibox" wire:click="selectFacturaSubtype('clientes_a')">
                        <div class="card-icon-wrap">
                            <div class="icon-circle">
                                <i class="fa fa-user-tie"></i>
                            </div>
                            <div class="card-label">Clientes A</div>
                            <div class="card-desc">Facturación estándar para clientes tipo A</div>
                        </div>
                        <div class="card-footer-bar">
                            <i class="fa fa-arrow-right mr-1"></i> Ir a Factura
                        </div>
                    </div>
                </div>

                {{-- CLIENTES B --}}
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="flujo-card flujo-cb ibox" wire:click="selectFacturaSubtype('clientes_b')">
                        <div class="card-icon-wrap">
                            <div class="icon-circle">
                                <i class="fa fa-users"></i>
                            </div>
                            <div class="card-label">Clientes B</div>
                            <div class="card-desc">Facturación estándar para clientes tipo B</div>
                        </div>
                        <div class="card-footer-bar">
                            <i class="fa fa-arrow-right mr-1"></i> Ir a Factura
                        </div>
                    </div>
                </div>

                {{-- SR / CLIENTES A --}}
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="flujo-card flujo-sra ibox" wire:click="selectFacturaSubtype('sr_clientes_a')">
                        <div class="card-icon-wrap">
                            <div class="icon-circle">
                                <i class="fa fa-shield-alt"></i>
                            </div>
                            <div class="card-label">SR / Clientes A</div>
                            <div class="card-desc">Sin restricción de precio – Clientes A</div>
                        </div>
                        <div class="card-footer-bar">
                            <i class="fa fa-arrow-right mr-1"></i> Ir a Factura
                        </div>
                    </div>
                </div>

                {{-- SR / CLIENTES B --}}
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="flujo-card flujo-srb ibox" wire:click="selectFacturaSubtype('sr_clientes_b')">
                        <div class="card-icon-wrap">
                            <div class="icon-circle">
                                <i class="fa fa-lock-open"></i>
                            </div>
                            <div class="card-label">SR / Clientes B</div>
                            <div class="card-desc">Sin restricción de precio – Clientes B</div>
                        </div>
                        <div class="card-footer-bar">
                            <i class="fa fa-arrow-right mr-1"></i> Ir a Factura
                        </div>
                    </div>
                </div>

                {{-- EXONERADA --}}
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="flujo-card flujo-exonerada ibox" wire:click="selectFacturaSubtype('exonerada')">
                        <div class="card-icon-wrap">
                            <div class="icon-circle">
                                <i class="fa fa-file-invoice"></i>
                            </div>
                            <div class="card-label">Exonerada</div>
                            <div class="card-desc">Facturación para clientes exonerados de impuesto</div>
                        </div>
                        <div class="card-footer-bar">
                            <i class="fa fa-arrow-right mr-1"></i> Ir a Factura
                        </div>
                    </div>
                </div>

            </div>

        @endif

    </div>
</div>
