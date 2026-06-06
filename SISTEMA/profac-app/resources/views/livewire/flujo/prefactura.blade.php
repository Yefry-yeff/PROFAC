<div>
    {{-- ===== ENCABEZADO ===== --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-12">
            <h2><i class="fa fa-file-invoice text-info"></i> Prefactura</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('flujo.ventas') }}">Ventas</a></li>
                <li class="breadcrumb-item active"><strong>Prefactura</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">

                    {{-- ── Encabezado del ibox ── --}}
                    <div class="ibox-title d-flex align-items-center justify-content-between"
                         style="background:linear-gradient(135deg,#0097a7 0%,#00bcd4 100%);
                                color:#fff; border-radius:4px 4px 0 0; padding:12px 20px;">
                        <h5 class="m-0" style="color:#fff;">
                            <i class="fa fa-search"></i> &nbsp;Búsqueda de Oferta Ganadora
                        </h5>
                        <a href="{{ route('flujo.ventas') }}"
                           style="background:#fff; color:#0097a7; border:none; border-radius:8px;
                                  padding:7px 18px; font-size:13px; font-weight:700;
                                  display:inline-flex; align-items:center; gap:6px; text-decoration:none;
                                  box-shadow:0 2px 8px rgba(0,0,0,.15); transition:transform .15s, box-shadow .15s;"
                           onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 14px rgba(0,0,0,.2)';"
                           onmouseout="this.style.transform='';this.style.boxShadow='0 2px 8px rgba(0,0,0,.15)';">
                            <i class="fa fa-arrow-left"></i> Volver
                        </a>
                    </div>

                    <div class="ibox-content" style="padding:28px;">

                        @if(!$ofertaSeleccionada)

                            {{-- ===== PANEL DE BÚSQUEDA ===== --}}
                            <div style="background:linear-gradient(135deg,#e0f7fa,#f1f8e9); border-radius:14px;
                                        padding:28px 32px; margin-bottom:28px; border:1px solid #b2ebf2;">
                                <h5 style="color:#006064; font-weight:700; margin-bottom:18px;">
                                    <i class="fa fa-search mr-2"></i>Buscar oferta ganadora
                                </h5>
                                <p style="color:#546e7a; font-size:13px; margin-bottom:22px;">
                                    Puedes buscar por nombre de cliente / RTN  <strong>o</strong>  ingresando directamente el número de oferta.
                                </p>

                                <div class="row">
                                    {{-- Búsqueda por cliente --}}
                                    <div class="col-md-5 mb-3">
                                        <label style="font-size:12px; font-weight:700; color:#006064; letter-spacing:.4px; text-transform:uppercase;">
                                            <i class="fa fa-user mr-1"></i> Cliente / RTN
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" style="background:#00bcd4; border-color:#00bcd4; color:#fff;">
                                                    <i class="fa fa-user"></i>
                                                </span>
                                            </div>
                                            <input type="text"
                                                   wire:model.debounce.400ms="busquedaCliente"
                                                   class="form-control"
                                                   placeholder="Escribe el nombre del cliente o RTN..."
                                                   autocomplete="off"
                                                   @if(trim($busquedaNumero) !== '') disabled @endif
                                                   style="font-size:14px;">
                                        </div>
                                        @if(trim($busquedaNumero) === '' && strlen(trim($busquedaCliente)) > 0 && strlen(trim($busquedaCliente)) < 2)
                                            <small class="text-muted">Escribe al menos 2 caracteres</small>
                                        @endif
                                    </div>

                                    <div class="col-md-1 d-flex align-items-center justify-content-center mb-3"
                                         style="padding-top:24px;">
                                        <span style="font-weight:700; color:#78909c; font-size:13px;">— O —</span>
                                    </div>

                                    {{-- Búsqueda por número de oferta --}}
                                    <div class="col-md-3 mb-3">
                                        <label style="font-size:12px; font-weight:700; color:#006064; letter-spacing:.4px; text-transform:uppercase;">
                                            <i class="fa fa-hashtag mr-1"></i> Nº de Oferta
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" style="background:#0097a7; border-color:#0097a7; color:#fff;">
                                                    <i class="fa fa-hashtag"></i>
                                                </span>
                                            </div>
                                            <input type="number"
                                                   wire:model.debounce.400ms="busquedaNumero"
                                                   class="form-control"
                                                   placeholder="Ej: 142"
                                                   min="1"
                                                   @if(strlen(trim($busquedaCliente)) >= 2) disabled @endif
                                                   style="font-size:14px;">
                                        </div>
                                    </div>

                                    {{-- Botón limpiar --}}
                                    <div class="col-md-3 mb-3 d-flex align-items-end">
                                        <button type="button"
                                                wire:click="limpiar"
                                                class="btn btn-default btn-block"
                                                style="border-radius:8px; font-weight:600; height:40px;">
                                            <i class="fa fa-times mr-1"></i> Limpiar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- ===== RESULTADOS ===== --}}
                            @if($buscado)
                                @if($totalResultados > 0)
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span style="font-size:13px; color:#546e7a;">
                                            <i class="fa fa-check-circle text-success mr-1"></i>
                                            Se encontraron <strong>{{ $totalResultados }}</strong> oferta(s) ganadora(s)
                                        </span>
                                        <span class="badge badge-success" style="border-radius:20px; padding:5px 14px;">
                                            Solo ofertas ganadoras
                                        </span>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered" style="font-size:13px;">
                                            <thead style="background:#006064; color:#fff;">
                                                <tr>
                                                    <th style="width:70px;">#</th>
                                                    <th style="width:60px;">Pedido</th>
                                                    <th>Cliente</th>
                                                    <th style="width:110px;">Fecha</th>
                                                    <th style="width:80px;">Prods.</th>
                                                    <th style="width:130px;">Total</th>
                                                    <th style="width:120px;">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($resultados as $oferta)
                                                @php $o = (array)$oferta; @endphp
                                                <tr>
                                                    <td>
                                                        <span class="badge badge-info" style="border-radius:20px; font-size:12px; padding:4px 10px;">
                                                            #{{ $o['id'] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($o['pedido_id'])
                                                            <a href="{{ route('flujo.pedido.editar', $o['pedido_id']) }}" target="_blank"
                                                               title="Ver pedido #{{ $o['pedido_id'] }}"
                                                               style="color:#0097a7; font-weight:700;">
                                                                #{{ $o['pedido_id'] }}
                                                            </a>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div style="font-weight:600; color:#2c3e50;">{{ $o['nombre_cliente'] ?? '—' }}</div>
                                                        @if($o['RTN'])
                                                            <small class="text-muted">RTN: {{ $o['RTN'] }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <small>{{ $o['fecha_emision'] ?? \Carbon\Carbon::parse($o['created_at'])->format('d/m/Y') }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-secondary" style="border-radius:20px;">
                                                            {{ $o['total_productos'] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <strong style="color:#00897b;">L {{ $o['total'] }}</strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button"
                                                                wire:click="seleccionarOferta({{ $o['id'] }})"
                                                                class="btn btn-sm"
                                                                style="background:#0097a7; color:#fff; border-radius:20px;
                                                                       font-size:12px; padding:4px 14px; font-weight:600;">
                                                            <i class="fa fa-eye mr-1"></i> Seleccionar
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                @else
                                    <div class="text-center py-5">
                                        <i class="fa fa-search fa-3x mb-3" style="color:#b2dfdb; display:block;"></i>
                                        <h5 style="color:#78909c;">No se encontraron ofertas ganadoras</h5>
                                        <p style="color:#90a4ae; font-size:13px;">
                                            Intenta con otro nombre de cliente o número de oferta.
                                        </p>
                                    </div>
                                @endif

                            @else
                                {{-- Estado inicial --}}
                                <div class="text-center py-5">
                                    <i class="fa fa-file-invoice fa-4x mb-3" style="color:#b2ebf2; display:block;"></i>
                                    <h5 style="color:#78909c; font-weight:700;">Busca una oferta ganadora</h5>
                                    <p style="color:#90a4ae; font-size:14px; max-width:420px; margin:0 auto;">
                                        Usa los campos de arriba para buscar por nombre de cliente o número de oferta.
                                        Solo aparecerán ofertas con estado <strong>ganadora</strong>.
                                    </p>
                                </div>
                            @endif

                        @else
                            {{-- ===== DETALLE DE LA OFERTA SELECCIONADA ===== --}}
                            @php $of = $ofertaSeleccionada; @endphp

                            {{-- Header de detalle --}}
                            <div class="d-flex align-items-center justify-content-between mb-4"
                                 style="background:linear-gradient(135deg,#e0f7fa,#f0fdf4); border-radius:12px;
                                        padding:16px 24px; border:1px solid #b2ebf2;">
                                <div>
                                    <h5 class="m-0" style="color:#006064; font-weight:700;">
                                        <i class="fa fa-file-invoice mr-2"></i>
                                        Oferta Ganadora &nbsp;
                                        <span style="background:#0097a7; color:#fff; border-radius:20px;
                                                     padding:3px 14px; font-size:14px;">
                                            #{{ $of['id'] }}
                                        </span>
                                        @if($of['pedido_id'])
                                            &nbsp;
                                            <span style="background:#546e7a; color:#fff; border-radius:20px;
                                                         padding:3px 14px; font-size:13px;">
                                                Pedido #{{ $of['pedido_id'] }}
                                            </span>
                                        @endif
                                    </h5>
                                    <p class="m-0 mt-1" style="color:#546e7a; font-size:13px;">
                                        Registrada el {{ \Carbon\Carbon::parse($of['created_at'])->format('d/m/Y H:i') }}
                                        @if($of['registrado_por']) — por <strong>{{ $of['registrado_por'] }}</strong> @endif
                                    </p>
                                </div>
                                <button type="button" wire:click="volverResultados"
                                        class="btn btn-default"
                                        style="border-radius:20px; font-size:13px; font-weight:600; padding:6px 18px;">
                                    <i class="fa fa-arrow-left mr-1"></i> Volver a resultados
                                </button>
                            </div>

                            <div class="row">
                                {{-- ── Datos del cliente ── --}}
                                <div class="col-md-6 mb-4">
                                    <div style="border:1px solid #e8f5e9; border-radius:12px; padding:20px; height:100%;">
                                        <h6 style="color:#2e7d32; font-weight:700; border-bottom:2px solid #c8e6c9; padding-bottom:8px; margin-bottom:14px;">
                                            <i class="fa fa-user mr-2"></i>Datos del Cliente
                                        </h6>
                                        <table class="table table-sm table-borderless mb-0" style="font-size:13px;">
                                            <tr>
                                                <td style="color:#78909c; width:130px;">Nombre</td>
                                                <td><strong>{{ $of['cliente_nombre'] ?: $of['nombre_cliente'] }}</strong></td>
                                            </tr>
                                            <tr>
                                                <td style="color:#78909c;">RTN</td>
                                                <td>{{ $of['cliente_rtn'] ?: $of['RTN'] ?: '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color:#78909c;">Vendedor</td>
                                                <td>{{ $of['vendedor_nombre'] ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color:#78909c;">F. Emisión</td>
                                                <td>{{ $of['fecha_emision'] ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color:#78909c;">F. Vencimiento</td>
                                                <td>{{ $of['fecha_vencimiento'] ?? '—' }}</td>
                                            </tr>
                                            @if($of['nota'])
                                            <tr>
                                                <td style="color:#78909c; vertical-align:top;">Nota</td>
                                                <td><em style="color:#546e7a;">{{ $of['nota'] }}</em></td>
                                            </tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>

                                {{-- ── Totales ── --}}
                                <div class="col-md-6 mb-4">
                                    <div style="border:1px solid #e3f2fd; border-radius:12px; padding:20px; height:100%;">
                                        <h6 style="color:#1565c0; font-weight:700; border-bottom:2px solid #bbdefb; padding-bottom:8px; margin-bottom:14px;">
                                            <i class="fa fa-calculator mr-2"></i>Resumen de Totales
                                        </h6>
                                        <table class="table table-sm table-borderless mb-0" style="font-size:13px;">
                                            <tr>
                                                <td style="color:#78909c;">Subtotal Gravado</td>
                                                <td class="text-right"><strong>L {{ $of['sub_total_grabado'] }}</strong></td>
                                            </tr>
                                            <tr>
                                                <td style="color:#78909c;">Subtotal Exento</td>
                                                <td class="text-right"><strong>L {{ $of['sub_total_excento'] }}</strong></td>
                                            </tr>
                                            <tr>
                                                <td style="color:#78909c;">Subtotal</td>
                                                <td class="text-right"><strong>L {{ $of['sub_total'] }}</strong></td>
                                            </tr>
                                            @if((float)str_replace(',','',$of['porc_descuento']) > 0)
                                            <tr>
                                                <td style="color:#e53935;">Descuento ({{ $of['porc_descuento'] }}%)</td>
                                                <td class="text-right" style="color:#e53935;">- L {{ $of['monto_descuento'] }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td style="color:#78909c;">ISV (15%)</td>
                                                <td class="text-right"><strong>L {{ $of['isv'] }}</strong></td>
                                            </tr>
                                            <tr style="border-top:2px solid #1565c0;">
                                                <td style="font-weight:700; font-size:15px; color:#1565c0;">TOTAL</td>
                                                <td class="text-right" style="font-weight:700; font-size:15px; color:#1565c0;">L {{ $of['total'] }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- ── Productos ── --}}
                            <h6 style="font-weight:700; color:#37474f; margin-bottom:12px;">
                                <i class="fa fa-list mr-2 text-info"></i>
                                Productos ({{ count($productosOferta) }})
                            </h6>

                            @if(count($productosOferta) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered" style="font-size:13px;">
                                    <thead style="background:#006064; color:#fff;">
                                        <tr>
                                            <th style="width:40px;">#</th>
                                            <th>Producto</th>
                                            <th>Bodega</th>
                                            <th style="width:90px;" class="text-right">Cantidad</th>
                                            <th style="width:110px;" class="text-right">P. Unitario</th>
                                            <th style="width:100px;" class="text-right">Descuento</th>
                                            <th style="width:90px;" class="text-right">ISV</th>
                                            <th style="width:110px;" class="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($productosOferta as $prod)
                                        @php $p = (array)$prod; @endphp
                                        <tr>
                                            <td class="text-center" style="color:#90a4ae;">{{ $p['indice'] + 1 }}</td>
                                            <td><strong style="color:#2c3e50;">{{ $p['nombre_producto'] ?? '—' }}</strong></td>
                                            <td><small class="text-muted">{{ $p['nombre_bodega'] ?? '—' }}</small></td>
                                            <td class="text-right">{{ $p['cantidad'] }}</td>
                                            <td class="text-right">L {{ $p['precio_unidad'] }}</td>
                                            <td class="text-right" style="color:#e53935;">
                                                @if((float)str_replace(',','',$p['descuento']) > 0)
                                                    L {{ $p['descuento'] }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-right">L {{ $p['isv'] }}</td>
                                            <td class="text-right"><strong>L {{ $p['total'] }}</strong></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot style="background:#f5f5f5;">
                                        <tr>
                                            <td colspan="7" class="text-right" style="font-weight:700; color:#1565c0;">
                                                TOTAL GENERAL
                                            </td>
                                            <td class="text-right" style="font-weight:700; color:#1565c0;">
                                                L {{ $of['total'] }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            @else
                                <div class="text-center text-muted py-3" style="font-size:13px;">
                                    <i class="fa fa-exclamation-circle mr-1"></i> Esta oferta no tiene productos registrados.
                                </div>
                            @endif

                        @endif

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
