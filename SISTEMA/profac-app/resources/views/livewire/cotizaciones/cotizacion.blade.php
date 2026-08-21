<div>
    @push('styles')
    <style>
        /* ── Spin hide ─────────────────────────────────────────────── */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }

        /* ── Section header ─────────────────────────────────────────── */
        .ofr-section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: .4px;
            text-transform: uppercase;
        }
        .ofr-section-header i { font-size: 16px; }

        /* ── Pedido panel ───────────────────────────────────────────── */
        .pedido-link-panel {
            border: 2px dashed #b2dfdb;
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 24px;
            background: #f0fdf4;
            transition: border-color .2s;
        }
        .pedido-link-panel.linked {
            border: 2px solid #00897b;
            background: #e8f5e9;
        }
        .ped-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            background: #fff;
            border: 1px solid #e0f2f1;
            margin-bottom: 6px;
            transition: box-shadow .15s, border-color .15s;
            cursor: pointer;
        }
        .ped-row:hover { box-shadow: 0 3px 12px rgba(0,0,0,.09); border-color: #80cbc4; }

        /* ── Form fields ────────────────────────────────────────────── */
        .ofr-label {
            font-size: 11px;
            font-weight: 700;
            color: #546e7a;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 4px;
            display: block;
        }
        .ofr-label .req { color: #e53935; margin-left: 2px; }
        .ofr-input {
            border-radius: 8px !important;
            border: 1px solid #cfd8dc !important;
            font-size: 13px !important;
            transition: border-color .15s, box-shadow .15s;
        }
        .ofr-input:focus {
            border-color: #00897b !important;
            box-shadow: 0 0 0 3px rgba(0,137,123,.12) !important;
        }

        /* ── Totales grid ───────────────────────────────────────────── */
        .totales-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
        }
        .total-card {
            border-radius: 10px;
            padding: 12px 16px;
            text-align: center;
        }
        .total-card .tc-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            opacity: .75;
            margin-bottom: 4px;
        }
        .total-card .tc-val {
            font-size: 18px;
            font-weight: 800;
            line-height: 1;
        }

        /* ── Main ibox override ─────────────────────────────────────── */
        .ofr-main-ibox { border-radius: 16px !important; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.07) !important; }
        .ofr-main-ibox .ibox-title {
            background: linear-gradient(135deg,#004d40 0%,#00897b 100%) !important;
            border: none !important;
            padding: 16px 24px !important;
        }
        .ofr-main-ibox .ibox-title h3 { color: #fff !important; margin: 0; font-size: 16px; }
        .ofr-main-ibox .ibox-title .badge { background: rgba(255,255,255,.2); color: #fff; }
        .ofr-main-ibox .ibox-content { padding: 28px 28px 20px !important; }

        /* carousel */
        .img-size { width: 100%; height: 16rem; margin: 0 auto; object-fit: contain; }
        @media (min-width: 670px) and (max-width:767px) { .img-size { width: 85%; height: 16rem; } }
        @media (min-width: 768px) and (max-width:960px) { .img-size { width: 75%; height: 12rem; } }

        /* hide-container */
        @media (max-width: 767.5px) { .hide-container { display: none; } }
        .center-div { text-align: center }
    </style>
    @endpush

    {{-- ===== ENCABEZADO ===== --}}
    @if($fromFlujo)
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
                <i class="fa fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>
    @endif

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">

                {{-- ===== PANEL: VINCULAR A PEDIDO ===== --}}
                <div class="pedido-link-panel {{ $pedidoVinculado ? 'linked' : '' }}">

                    @if(!$pedidoVinculado)
                    {{-- Estado: sin pedido vinculado --}}
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <div>
                            <h6 style="margin:0; font-weight:800; color:#00695c;">
                                <i class="fa fa-link mr-2"></i>Vincular a un Pedido
                                <span style="font-size:11px; font-weight:400; color:#78909c; margin-left:8px;">(opcional — puedes crear la oferta sin pedido)</span>
                            </h6>
                        </div>
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
                                       wire:model.debounce.350ms="busquedaPedido"
                                       class="form-control ofr-input"
                                       placeholder="Buscar pedido por # o nombre de cliente…"
                                       style="border-radius:0 8px 8px 0 !important;"
                                       autocomplete="off">
                            </div>
                            @if(strlen(trim($busquedaPedido)) > 0 && strlen(trim($busquedaPedido)) < 2)
                                <small class="text-muted mt-1 d-block">Escribe al menos 2 caracteres</small>
                            @endif
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <small class="text-muted">
                                <i class="fa fa-info-circle mr-1 text-info"></i>
                                Puedes crear <strong>múltiples ofertas</strong> para el mismo pedido.
                            </small>
                        </div>
                    </div>

                    @if(count($pedidosEncontrados) > 0)
                    <div style="max-height:280px; overflow-y:auto; margin-top:12px;">
                        @foreach($pedidosEncontrados as $ped)
                        @php $p = (array)$ped; @endphp
                        <div class="ped-row" wire:click="seleccionarPedido({{ $p['id'] }})">
                            <div style="flex-shrink:0;">
                                <span style="background:linear-gradient(135deg,#00695c,#00897b); color:#fff;
                                             border-radius:8px; padding:4px 12px; font-size:13px; font-weight:800;">
                                    #{{ $p['id'] }}
                                </span>
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div style="font-weight:700; color:#2c3e50; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $p['cliente'] }}
                                </div>
                                <div style="font-size:11px; color:#90a4ae;">
                                    RTN: {{ $p['rtn'] ?: '—' }}
                                    &nbsp;·&nbsp;
                                    {{ \Carbon\Carbon::parse($p['created_at'])->format('d/m/Y') }}
                                </div>
                            </div>
                            <div style="flex-shrink:0; text-align:center; min-width:70px;">
                                <div style="font-size:10px; color:#90a4ae;">Ofertas</div>
                                <div style="font-weight:700; color:{{ $p['total_ofertas'] > 0 ? '#00897b' : '#b0bec5' }}; font-size:15px;">
                                    {{ $p['total_ofertas'] }}
                                    @if($p['has_ganadora'] > 0)
                                        <i class="fa fa-trophy text-warning" style="font-size:12px;" title="Ya tiene ganadora"></i>
                                    @endif
                                </div>
                            </div>
                            <div style="flex-shrink:0;">
                                @php
                                    $estMap=['pendiente'=>['#e3f2fd','#1565c0'],'pre_factura'=>['#fff8e1','#f57f17'],'activo'=>['#e8f5e9','#2e7d32'],'cancelado'=>['#fce4ec','#b71c1c']];
                                    $col=$estMap[$p['estado']]??['#f5f5f5','#546e7a'];
                                @endphp
                                <span style="background:{{ $col[0] }}; color:{{ $col[1] }}; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700;">
                                    {{ ucfirst(str_replace('_',' ',$p['estado'])) }}
                                </span>
                            </div>
                            <div style="flex-shrink:0;">
                                <span style="background:#e8f5e9; color:#2e7d32; border-radius:8px; padding:6px 14px; font-size:12px; font-weight:700;">
                                    <i class="fa fa-plus-circle mr-1"></i> Seleccionar
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @elseif(strlen(trim($busquedaPedido)) >= 2)
                    <div class="text-center py-3 mt-2">
                        <i class="fa fa-search fa-2x mb-2" style="color:#b2dfdb; display:block;"></i>
                        <p style="color:#78909c; font-size:13px; margin:0;">No se encontraron pedidos activos con ese criterio.</p>
                    </div>
                    @endif

                    @else
                    {{-- Estado: pedido vinculado --}}
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div style="background:linear-gradient(135deg,#00695c,#00897b); color:#fff;
                                         border-radius:10px; padding:6px 16px; font-size:15px; font-weight:800; flex-shrink:0;">
                                <i class="fa fa-link mr-1"></i> Pedido #{{ $pedidoVinculado['id'] }}
                            </div>
                            <div>
                                <div style="font-weight:700; color:#2c3e50; font-size:14px;">{{ $pedidoVinculado['cliente'] }}</div>
                                <div style="font-size:12px; color:#78909c;">
                                    RTN: {{ $pedidoVinculado['rtn'] ?: '—' }}
                                    &nbsp;·&nbsp;
                                    {{ \Carbon\Carbon::parse($pedidoVinculado['created_at'])->format('d/m/Y') }}
                                </div>
                            </div>
                            <span style="background:#e8f5e9; color:#2e7d32; border-radius:20px; padding:4px 14px; font-size:11px; font-weight:700;">
                                <i class="fa fa-check-circle mr-1"></i> Vinculado
                            </span>
                        </div>
                        <button type="button" wire:click="desvincularPedido"
                                style="background:#fce4ec; color:#b71c1c; border:none; border-radius:8px;
                                       padding:7px 16px; font-size:12px; font-weight:700; cursor:pointer;">
                            <i class="fa fa-times mr-1"></i> Desvincular
                        </button>
                    </div>
                    @endif
                </div>

                {{-- ===== FORMULARIO PRINCIPAL ===== --}}
                <div class="ibox ofr-main-ibox">
                    <div class="ibox-title">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <h3>
                                    @php
                                        $labelTipo = match((int)$tipoCotizacion) {
                                            1 => 'Corporativo',
                                            3 => 'Exonerado',
                                            default => 'Cliente A',
                                        };
                                    @endphp
                                    <i class="fa fa-file-text-o mr-2"></i>Nueva Oferta &mdash; {{ $labelTipo }}
                                </h3>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge badge-pill" style="font-size:12px; padding:6px 14px;">
                                    Categoría: <strong id="categoria_cliente_nombre"></strong>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="ibox-content">
                        <form onkeydown="return event.key != 'Enter';" autocomplete="off" id="crear_venta"
                              name="crear_venta" data-parsley-validate>

                            {{-- Campos ocultos de configuración --}}
                            <input type="hidden" id="tipo_venta_id"       name="tipo_venta_id"       value="{{ $tipoCotizacion }}">
                            <input type="hidden" id="pedido_vinculado_id" name="pedido_id"            value="{{ $pedidoId }}">

                            {{-- ── SECCIÓN 1: Datos del Cliente ─────────────────────────── --}}
                            <div class="ofr-section-header" style="background:linear-gradient(135deg,#e3f2fd,#e8f5e9); color:#1a73e8;">
                                <i class="fa fa-user"></i> 1. Datos del Cliente
                            </div>
                            <div class="row mb-4">
                                <div class="col-12 col-md-6 col-lg-6 mb-3">
                                    <label class="ofr-label">Seleccionar Cliente <span class="req">*</span></label>
                                    <select id="seleccionarCliente" name="seleccionarCliente"
                                            class="form-control ofr-input" data-parsley-required
                                            onchange="obtenerDatosCliente()">
                                        <option value="" selected disabled>-- Seleccionar un cliente --</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 col-lg-6 mb-3">
                                    <label class="ofr-label">Nombre del Cliente <span class="req">*</span></label>
                                    <input class="form-control ofr-input" type="text" id="nombre_cliente_ventas"
                                           name="nombre_cliente_ventas" data-parsley-required readonly
                                           placeholder="Se completa al seleccionar cliente">
                                </div>
                                <div class="col-12 col-md-4 col-lg-4 mb-3">
                                    <label class="ofr-label">Asesor Comercial <span class="req">*</span></label>
                                    <select name="vendedor" id="vendedor" class="form-control ofr-input" data-parsley-required>
                                        <option value="" selected disabled>-- Seleccionar asesor --</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4 col-lg-4 mb-3">
                                    <label class="ofr-label">RTN <span class="req">*</span></label>
                                    <input class="form-control ofr-input" type="text" id="rtn_ventas" name="rtn_ventas"
                                           readonly placeholder="Se completa automáticamente">
                                </div>
                                <div class="col-12 col-md-4 col-lg-4 mb-3">
                                    <label class="ofr-label">Fecha de Emisión <span class="req">*</span></label>
                                    <input class="form-control ofr-input" type="date" id="fecha_emision"
                                           onchange="sumarDiasCredito()" name="fecha_emision"
                                           value="{{ date('Y-m-d') }}" data-parsley-required>
                                </div>
                                <div class="col-12 col-md-4 col-lg-4 mb-3">
                                    <label class="ofr-label">Descuento Aplicado % <span class="req">*</span></label>
                                    <input class="form-control ofr-input" oninput="validarDescuento()"
                                           onchange="calcularTotalesInicioPagina()"
                                           type="number" min="0" max="50" value="0" id="porDescuento"
                                           name="porDescuento" data-parsley-required>
                                    <p id="mensajeError" class="text-danger mb-0" style="font-size:12px;"></p>
                                </div>

                                {{-- Campos ocultos que el JS necesita --}}
                                <div style="display:none;">
                                    <select name="tipoPagoVenta" id="tipoPagoVenta" onchange="validarFechaPago()"></select>
                                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" min="{{ date('Y-m-d') }}" readonly>
                                </div>
                            </div>

                            {{-- ── SECCIÓN 2: Agregar Producto ──────────────────────────── --}}
                            <div class="ofr-section-header" style="background:linear-gradient(135deg,#e8f5e9,#e0f7fa); color:#00695c;">
                                <i class="fa fa-plus-circle"></i> 2. Agregar Producto
                            </div>

                            <div class="row mb-3">
                                <div class="col-12 col-md-4 mb-3">
                                    <label class="ofr-label">Buscar Producto <span class="req">*</span></label>
                                    <div class="input-group">
                                        <input type="text" id="codigoProductoCotizacion" class="form-control ofr-input"
                                               placeholder="ID o nombre del producto…" autocomplete="off"
                                               style="border-radius:8px 0 0 8px !important;"
                                               onkeydown="if(event.key==='Enter'){buscarPorCodigoCotizacion(this.value);return false;}">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success" title="Buscar producto"
                                                    style="border-radius:0 8px 8px 0;"
                                                    onclick="limpiarProductoCotizacion(); window['abrirBuscador_buscadorProductoCotizacion'](document.getElementById('codigoProductoCotizacion').value||'')">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small id="productoSeleccionadoCotizacion" class="text-success font-weight-bold mt-1 d-block d-none"></small>
                                    <select id="seleccionarProducto" name="seleccionarProducto" hidden>
                                        <option value="" selected disabled></option>
                                    </select>
                                    <x-buscador-producto id-modal="buscadorProductoCotizacion" callback="alSeleccionarProductoCotizacion" />
                                    @push('scripts')
                                    <script>
                                    function limpiarProductoCotizacion() {
                                        document.getElementById('seleccionarProducto').innerHTML = '<option value="" selected disabled></option>';
                                        document.getElementById('codigoProductoCotizacion').value = '';
                                        var lbl = document.getElementById('productoSeleccionadoCotizacion');
                                        lbl.classList.add('d-none'); lbl.textContent = '';
                                        document.getElementById('historialPreciosPanel').classList.add('d-none');
                                    }
                                    function alSeleccionarProductoCotizacion(producto) {
                                        var select = document.getElementById('seleccionarProducto');
                                        select.innerHTML = '<option value="' + producto.id + '" selected>' + producto.nombre + '</option>';
                                        document.getElementById('codigoProductoCotizacion').value = producto.nombre;
                                        var label = document.getElementById('productoSeleccionadoCotizacion');
                                        label.textContent = '\u2713 ' + producto.nombre + ' (ID: ' + producto.id + ')';
                                        label.classList.remove('d-none');
                                        cargarCategoriasProducto();
                                        cargarHistorialPrecosCotizacion();
                                    }
                                    function buscarPorCodigoCotizacion(cod) {
                                        cod = String(cod).trim();
                                        if (!cod) { window['abrirBuscador_buscadorProductoCotizacion'](''); return; }
                                        axios.get('/productos/buscar', { params: { q: cod, page: 1 } })
                                            .then(function(r) {
                                                var items = r.data.data;
                                                var exact = items.find(function(p) { return String(p.id) === cod; });
                                                if (exact) { alSeleccionarProductoCotizacion(exact); }
                                                else if (items.length === 1) { alSeleccionarProductoCotizacion(items[0]); }
                                                else { window['abrirBuscador_buscadorProductoCotizacion'](cod); }
                                            });
                                    }
                                    function cargarHistorialPrecosCotizacion() {
                                        var productoId = $('#seleccionarProducto').val();
                                        var clienteId  = $('#seleccionarCliente').val();
                                        var panel  = document.getElementById('historialPreciosPanel');
                                        var cuerpo = document.getElementById('historialPreciosCuerpo');
                                        if (!productoId || !clienteId) { panel.classList.add('d-none'); return; }
                                        cuerpo.innerHTML = '<p class="text-muted small"><i class="fa fa-spinner fa-spin"></i> Cargando historial...</p>';
                                        panel.classList.remove('d-none');
                                        axios.post('/estatal/historial/precios', { cliente_id: clienteId, producto_id: productoId })
                                        .then(function(response) {
                                            var rows = response.data.historial;
                                            if (!rows || rows.length === 0) { cuerpo.innerHTML = '<p class="text-muted small">No hay ventas previas de este producto a este cliente.</p>'; return; }
                                            var fmt = new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL', minimumFractionDigits: 2 });
                                            var html = '<div class="table-responsive"><table class="table table-sm table-bordered table-hover mb-0" style="font-size:0.82rem;"><thead class="thead-light"><tr><th>Fecha</th><th>Factura</th><th>Precio Unit.</th><th>Cant.</th><th>Total</th><th>Categor\u00eda</th></tr></thead><tbody>';
                                            rows.forEach(function(r) { html += '<tr><td>' + r.fecha_emision + '</td><td>' + r.numero_factura + '</td><td class="text-right font-weight-bold text-success">' + fmt.format(r.precio_unidad) + '</td><td class="text-center">' + r.cantidad + '</td><td class="text-right">' + fmt.format(r.total) + '</td><td><span class="badge badge-secondary">' + r.categoria + '</span></td></tr>'; });
                                            html += '</tbody></table></div>';
                                            cuerpo.innerHTML = html;
                                        }).catch(function() { cuerpo.innerHTML = '<p class="text-danger small">Error al cargar el historial.</p>'; });
                                    }
                                    </script>
                                    @endpush
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label class="ofr-label">Categoría Precio <span class="req">*</span></label>
                                    <select id="categoria_cliente_venta_id" name="categoria_cliente_venta_id"
                                            class="form-control ofr-input" onchange="listaCategoríaClientes()">
                                        <option value="" selected disabled>-- Seleccione un producto primero --</option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label class="ofr-label">Bodega asignada</label>
                                    <select id="bodega" name="bodega" class="form-control ofr-input" disabled>
                                        <option value="" selected>-- Se asignará automáticamente --</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Imagen + agregar producto --}}
                            <div class="row mb-4">
                                <div class="col-12 col-md-5 text-center">
                                    <a id="detalleProducto" href="" class="font-bold h5 d-none text-success" target="_blank">
                                        <i class="fa fa-info-circle"></i> Ver Detalles Del Producto
                                    </a>
                                    <div id="carouselProducto" class="carousel slide mt-2" data-ride="carousel">
                                        <div id="bloqueImagenes" class="carousel-inner"></div>
                                        <a class="carousel-control-prev" href="#carouselProducto" role="button" data-slide="prev">
                                            <span class="carousel-control-prev-icon"></span>
                                        </a>
                                        <a class="carousel-control-next" href="#carouselProducto" role="button" data-slide="next">
                                            <span class="carousel-control-next-icon"></span>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-12 col-md-7">
                                    <div id="botonAdd" class="my-4 text-center d-none">
                                        <button type="button"
                                                class="btn btn-success btn-lg px-5"
                                                style="border-radius:12px; font-weight:700; box-shadow:0 4px 14px rgba(0,137,123,.3);"
                                                onclick="agregarProductoCarrito()">
                                            <i class="fa fa-plus-circle mr-2"></i> Añadir Producto a la Oferta
                                        </button>
                                    </div>
                                    <div id="historialPreciosPanel" class="d-none mt-2">
                                        <div style="background:#f0fdf4; border:1px solid #c8e6c9; border-radius:10px; padding:14px 16px;">
                                            <h6 style="color:#2e7d32; font-weight:700; margin-bottom:10px;">
                                                <i class="fa fa-history mr-1"></i> Últimas ventas a este cliente
                                            </h6>
                                            <div id="historialPreciosCuerpo"><p class="text-muted small">Cargando...</p></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ── Lista de productos agregados ──────────────────────────── --}}
                            <div id="divProductos"></div>

                            {{-- hide-container (el JS necesita estos elementos de referencia) --}}
                            <div class="hide-container" style="display:none;">
                                <p>Nota: El campo "Unidad" describe la unidad de medida para la venta del producto.</p>
                                <div class="row no-gutters">
                                    <div class="form-group col-12 col-md-2">
                                        <input type="text" placeholder="Producto" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-6 col-md-1">
                                        <input type="number" placeholder="Bodega" class="form-control" disabled>
                                    </div>
                                    <div class="form-group col-6 col-md-2">
                                        <input type="number" placeholder="Opciones" class="form-control" min="1" disabled>
                                    </div>
                                    <div class="form-group col-6 col-md-2">
                                        <input type="number" placeholder="Precio" class="form-control" min="1" disabled>
                                    </div>
                                    <div class="form-group col-4 col-md-1">
                                        <input type="text" placeholder="Cantidad" class="form-control" min="1" disabled>
                                    </div>
                                    <div class="form-group col-4 col-md-1">
                                        <input type="text" placeholder="Unidad" class="form-control" min="1" disabled>
                                    </div>
                                    <div class="form-group col-4 col-md-1">
                                        <input type="number" placeholder="Sub total" class="form-control" min="1" disabled>
                                    </div>
                                    <div class="form-group col-4 col-md-1">
                                        <input type="number" placeholder="ISV" class="form-control" min="1" disabled>
                                    </div>
                                    <div class="form-group col-4 col-md-1">
                                        <input type="number" placeholder="Total" class="form-control" min="1" disabled>
                                    </div>
                                </div>
                            </div>

                            {{-- ── SECCIÓN 3: Totales ────────────────────────────────────── --}}
                            <div class="ofr-section-header mt-4" style="background:linear-gradient(135deg,#e8eaf6,#fce4ec); color:#3949ab;">
                                <i class="fa fa-calculator"></i> 3. Totales
                            </div>
                            <div class="totales-grid mb-4">
                                <div class="total-card" style="background:#fff3e0; color:#e65100;">
                                    <div class="tc-label">Descuento L.</div>
                                    <div class="tc-val">
                                        <input type="text" id="descuentoMostrar" name="descuentoMostrar"
                                               class="form-control text-center font-weight-bold"
                                               style="border:none; background:transparent; font-size:18px; font-weight:800; color:#e65100; padding:0;"
                                               placeholder="0.00" data-parsley-required autocomplete="off" readonly>
                                        <input type="hidden" id="descuentoGeneral" name="descuentoGeneral" required>
                                    </div>
                                </div>
                                <div class="total-card" style="background:#e8f5e9; color:#2e7d32;">
                                    <div class="tc-label">Sub Total L.</div>
                                    <div class="tc-val">
                                        <input type="text" id="subTotalGeneralMostrar" name="subTotalGeneralMostrar"
                                               class="form-control text-center"
                                               style="border:none; background:transparent; font-size:18px; font-weight:800; color:#2e7d32; padding:0;"
                                               placeholder="0.00" data-parsley-required autocomplete="off" readonly>
                                        <input id="subTotalGeneral" name="subTotalGeneral" type="hidden" value="" required>
                                    </div>
                                </div>
                                <div class="total-card" style="background:#e3f2fd; color:#1565c0;">
                                    <div class="tc-label">Sub Total Grabado L.</div>
                                    <div class="tc-val">
                                        <input type="text" id="subTotalGeneralGrabadoMostrar" name="subTotalGeneralGrabadoMostrar"
                                               class="form-control text-center"
                                               style="border:none; background:transparent; font-size:18px; font-weight:800; color:#1565c0; padding:0;"
                                               placeholder="0.00" data-parsley-required autocomplete="off" readonly>
                                        <input id="subTotalGeneralGrabado" name="subTotalGeneralGrabado" type="hidden" value="" required>
                                    </div>
                                </div>
                                <div class="total-card" style="background:#f3e5f5; color:#6a1b9a;">
                                    <div class="tc-label">Sub Total Excento L.</div>
                                    <div class="tc-val">
                                        <input type="text" id="subTotalGeneralExcentoMostrar" name="subTotalGeneralExcentoMostrar"
                                               class="form-control text-center"
                                               style="border:none; background:transparent; font-size:18px; font-weight:800; color:#6a1b9a; padding:0;"
                                               placeholder="0.00" data-parsley-required autocomplete="off" readonly>
                                        <input id="subTotalGeneralExcento" name="subTotalGeneralExcento" type="hidden" value="" required>
                                    </div>
                                </div>
                                <div class="total-card" style="background:#fce4ec; color:#b71c1c;">
                                    <div class="tc-label">ISV L.</div>
                                    <div class="tc-val">
                                        <input type="text" id="isvGeneralMostrar" name="isvGeneralMostrar"
                                               class="form-control text-center"
                                               style="border:none; background:transparent; font-size:18px; font-weight:800; color:#b71c1c; padding:0;"
                                               placeholder="0.00" data-parsley-required autocomplete="off" readonly>
                                        <input id="isvGeneral" name="isvGeneral" type="hidden" value="" required>
                                    </div>
                                </div>
                                <div class="total-card" style="background:linear-gradient(135deg,#004d40,#00695c); color:#fff;">
                                    <div class="tc-label" style="opacity:.8;">TOTAL L.</div>
                                    <div class="tc-val">
                                        <input type="text" id="totalGeneralMostrar" name="totalGeneralMostrar"
                                               class="form-control text-center"
                                               style="border:none; background:transparent; font-size:22px; font-weight:900; color:#fff; padding:0;"
                                               placeholder="0.00" data-parsley-required autocomplete="off" readonly>
                                        <input id="totalGeneral" name="totalGeneral" type="hidden" value="" required>
                                    </div>
                                </div>
                            </div>

                            {{-- ── SECCIÓN 4: Nota + Guardar ────────────────────────────── --}}
                            <div class="ofr-section-header" style="background:linear-gradient(135deg,#eceff1,#f5f5f5); color:#546e7a;">
                                <i class="fa fa-sticky-note-o"></i> 4. Nota y Confirmación
                            </div>
                            <div class="row mb-4">
                                <div class="col-12 col-md-8">
                                    <label class="ofr-label">Nota interna</label>
                                    <textarea class="form-control ofr-input" id="nota" name="nota"
                                              rows="3" maxlength="250"
                                              placeholder="Observaciones, condiciones especiales de la oferta…"></textarea>
                                </div>
                                <div class="col-12 col-md-4 d-flex align-items-end">
                                    <div class="w-100">
                                        @if($pedidoVinculado)
                                        <div class="mb-3 p-3" style="background:#e8f5e9; border-radius:10px; border:1px solid #c8e6c9;">
                                            <div style="font-size:11px; font-weight:700; color:#2e7d32; text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px;">
                                                <i class="fa fa-link mr-1"></i> Vinculado a Pedido
                                            </div>
                                            <div style="font-weight:700; color:#1b5e20; font-size:15px;">#{{ $pedidoVinculado['id'] }} — {{ $pedidoVinculado['cliente'] }}</div>
                                        </div>
                                        @endif
                                        <button id="guardar_cotizacion_btn"
                                                class="btn btn-success btn-block"
                                                style="border-radius:12px; padding:14px; font-size:15px; font-weight:800;
                                                       box-shadow:0 4px 18px rgba(0,137,123,.35);
                                                       background:linear-gradient(135deg,#00695c,#00897b); border:none;">
                                            <i class="fa fa-save mr-2"></i> Guardar Oferta
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $('#seleccionarCliente').select2({
            ajax: {
                url: '/cotizacion/clientes',
                data: function(params) {
                    return {
                        search: params.term,
                        tipoCotizacion: {{ $tipoCotizacion }},
                        type: 'public',
                        page: params.page || 1
                    };
                }
            }
        });
    </script>
    <script>var public_path = "{{ asset('catalogo/') }}";</script>
    <script src="{{ asset('js/js_proyecto/cotizaciones/cotizacion.js') }}"></script>
    @endpush
</div>
