<div>

{{-- ===== ESTILOS Y ANIMACIONES ===== --}}
<style>
    /* ── Keyframes ── */
    @keyframes popIn {
        0%   { transform: scale(0.75) translateY(12px); opacity: 0; }
        70%  { transform: scale(1.04) translateY(-2px); }
        100% { transform: scale(1)    translateY(0);    opacity: 1; }
    }
    @keyframes slideDown {
        from { transform: translateY(-8px); opacity: 0; }
        to   { transform: translateY(0);   opacity: 1; }
    }
    @keyframes pulseRing {
        0%, 100% { box-shadow: 0 0 0 0 rgba(26,126,251,.35); }
        50%       { box-shadow: 0 0 0 7px rgba(26,126,251,.0); }
    }
    @keyframes fadeInUp {
        from { transform: translateY(16px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }

    /* ── Badge numerado step ── */
    .step-badge {
        animation: pulseRing 2.5s ease-in-out infinite;
        display: inline-flex; align-items: center; justify-content: center;
        width: 28px; height: 28px; font-size: 13px;
        border-radius: 50%; font-weight: 700; flex-shrink: 0;
    }

    /* ── Section heading ── */
    .pedido-section-heading {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 0 8px;
        border-bottom: 2px solid #edf1f9;
        margin-bottom: 16px;
    }
    .pedido-section-heading h5 {
        margin: 0; font-size: 15px; color: #2c3e50; font-weight: 700;
    }

    /* ── Buscador cliente ── */
    .pedido-search-wrap { max-width: 520px; }
    .pedido-search-wrap .input-group {
        box-shadow: 0 2px 10px rgba(26,126,251,.10);
        border-radius: 10px; overflow: hidden;
    }
    .pedido-search-wrap .form-control {
        font-size: 14px; border-color: #d9e4ff; border-left: none;
        padding: 10px 12px; height: auto;
    }
    .pedido-search-wrap .input-group-text {
        border-color: #d9e4ff; background: #fff; padding: 0 12px;
    }

    /* ── Dropdown sugerencias ── */
    .sugerencias-box {
        max-width: 520px;
        border-radius: 10px; box-shadow: 0 8px 24px rgba(26,126,251,.15);
        border: 1px solid #d9e4ff; background: #fff;
        animation: slideDown .18s ease both; overflow: hidden;
    }
    .sugerencia-item {
        transition: background .1s, padding-left .1s; cursor: pointer;
    }
    .sugerencia-item:hover { background: #f0f7ff !important; padding-left: 24px !important; }

    /* ── Tarjeta cliente seleccionado ── */
    .cliente-card { animation: fadeInUp .3s ease both; }
    .cliente-card .card-body { padding: 16px 20px; }
    .cliente-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 12px 16px;
        margin-top: 12px;
    }
    .cliente-info-item small { font-size: 10px; text-transform: uppercase;
        letter-spacing: .7px; color: #9aabb8; display: block; margin-bottom: 2px; }
    .cliente-info-item strong { font-size: 13px; color: #2c3e50; word-break: break-word; }

    /* ── Tabla de productos ── */
    .productos-card { border: 1px solid #dce3f0 !important; border-radius: 12px !important; overflow: hidden; }
    .productos-card table { font-size: 13px; }
    .productos-card thead tr { background: linear-gradient(90deg, #f4f6fb, #f0f4ff); }
    .productos-card thead th { border-bottom: 2px solid #dce3f0 !important; color: #5a6e8a; font-weight: 700; padding: 10px 8px; }
    .productos-card .form-control-sm { border-color: #dce3f0; border-radius: 6px; font-size: 13px; }
    .productos-card .card-footer { background: #f8faff; border-top: 1px solid #e4eaf8; padding: 8px 14px; }

    /* ── Tarjeta Excel ── */
    .excel-card {
        border: 2px dashed #93c5fd !important; border-radius: 12px !important;
        background: linear-gradient(160deg, #f7fbff, #f0fff8) !important;
    }

    /* ── Botones de acción post-guardado ── */
    .accion-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }
    @media (max-width: 576px) {
        .accion-grid { grid-template-columns: repeat(2, 1fr); }
    }
    .accion-btn {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        border-radius: 12px; padding: 14px 8px; text-align: center;
        text-decoration: none !important; color: #fff !important;
        transition: transform .2s cubic-bezier(.34,1.56,.64,1), box-shadow .2s;
        position: relative; overflow: hidden; min-height: 72px; gap: 6px;
    }
    .accion-btn::after {
        content: ''; position: absolute; inset: 0;
        background: rgba(255,255,255,.15);
        transform: translateX(-100%) skewX(-15deg);
        transition: transform .3s ease;
    }
    .accion-btn:hover::after { transform: translateX(120%) skewX(-15deg); }
    .accion-btn:hover { transform: translateY(-3px) scale(1.03); }
    .accion-btn .accion-icon { font-size: 20px; }
    .accion-btn .accion-label { font-size: 11px; font-weight: 700; letter-spacing: .4px; }

    /* ── Panel de éxito ── */
    .panel-exito { animation: fadeInUp .4s ease both; }

    /* ── Responsive: form content padding ── */
    @media (max-width: 575px) {
        .pedido-ibox-content { padding: 16px !important; }
        .pedido-search-wrap, .sugerencias-box { max-width: 100%; }
        .cliente-info-grid { grid-template-columns: repeat(2, 1fr); }
        .productos-card .table-responsive { font-size: 12px; }
    }
    @media (min-width: 992px) {
        .pedido-ibox-content { padding: 28px 32px !important; }
    }
</style>

    {{-- ===== ENCABEZADO ===== --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2><i class="fa fa-shopping-cart text-primary"></i> Nuevo Pedido</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('flujo.ventas') }}">Ventas</a>
                </li>
                <li class="breadcrumb-item active">
                    <strong>Pedido</strong>
                </li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- ===== MODAL DE ÉXITO: PEDIDO GUARDADO ===== --}}
        @if ($pedidoGuardadoId)
            <div class="fv-overlay" style="z-index:2050;" tabindex="-1" role="dialog"
                 data-backdrop="static" data-keyboard="false">
                <div class="fv-dialog-sm" role="document" style="position:relative;">
                    <div class="fv-modal-content">
                        <button type="button"
                                wire:click="cerrarModalPedidoGuardado"
                                aria-label="Cerrar"
                                class="fv-modal-close"
                                style="position:absolute; top:12px; right:12px; background:#fff; color:#6b7280; border:1px solid #e5e7eb; z-index:2;">
                            <i class="fa fa-times"></i>
                        </button>
                        <div class="fv-modal-body" style="padding:36px 32px 28px; text-align:center;">

                            <div style="width:90px; height:90px; border-radius:50%;
                                        background:linear-gradient(135deg,#00c853,#69f0ae);
                                        display:flex; align-items:center; justify-content:center;
                                        margin:0 auto 20px; box-shadow:0 8px 24px rgba(0,200,83,.30);">
                                <i class="fa fa-check" style="font-size:46px; color:#fff; line-height:1;"></i>
                            </div>

                            <h4 style="font-weight:800; color:#1b5e20; margin-bottom:6px; font-size:18px;">¡Pedido guardado!</h4>
                            <p style="color:#546e7a; font-size:13px; margin-bottom:24px;">Pedido #{{ $pedidoGuardadoId }} registrado exitosamente.</p>

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">

                                <button type="button" wire:click="nuevoPedido"
                                        style="background:#f0fdf4; color:#1b5e20; border:1.5px solid #a7f3d0;
                                               border-radius:10px; padding:11px 8px; font-size:12px; font-weight:700;
                                               cursor:pointer; text-align:center; transition:background .15s;"
                                        onmouseover="this.style.background='#dcfce7'" onmouseout="this.style.background='#f0fdf4'">
                                    <i class="fa fa-plus-circle d-block" style="font-size:20px; margin-bottom:4px; color:#16a34a;"></i>
                                    Nuevo pedido
                                </button>

                                    <button type="button"
                                              data-pedido-id="{{ $pedidoGuardadoId }}"
                                              onclick="abrirFlujoPedidoDesdeExito(this.dataset.pedidoId)"
                                    style="background:#eff6ff; color:#1e40af; border:1.5px solid #bfdbfe;
                                        border-radius:10px; padding:11px 8px; font-size:12px; font-weight:700;
                                        cursor:pointer; text-align:center; transition:background .15s; text-decoration:none;"
                                    onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                                    <i class="fa fa-sitemap d-block" style="font-size:20px; margin-bottom:4px; color:#2563eb;"></i>
                                    Ver flujo
                                    </button>

                                <button type="button" onclick="window.open('/flujo/pedido/imprimir/{{ $pedidoGuardadoId }}', '_blank')"
                                        style="background:#fafafa; color:#374151; border:1.5px solid #e5e7eb;
                                               border-radius:10px; padding:11px 8px; font-size:12px; font-weight:700;
                                               cursor:pointer; text-align:center; transition:background .15s;"
                                        onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fafafa'">
                                    <i class="fa fa-print d-block" style="font-size:20px; margin-bottom:4px; color:#6b7280;"></i>
                                    Imprimir pedido
                                </button>

                                <a href="/proforma/cotizacion/2?from=flujo&pedidoId={{ $pedidoGuardadoId }}"
                                   style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none;
                                          border-radius:10px; padding:11px 8px; font-size:12px; font-weight:700;
                                          cursor:pointer; text-align:center; box-shadow:0 3px 10px rgba(230,81,0,.25); transition:opacity .15s; text-decoration:none;"
                                   onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                                    <i class="fa fa-file-text-o d-block" style="font-size:20px; margin-bottom:4px;"></i>
                                    Generar oferta
                                </a>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($mensajeError)
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" wire:click="$set('mensajeError', '')"><span>&times;</span></button>
                <i class="fa fa-exclamation-triangle"></i> {{ $mensajeError }}
            </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title pedido-main-title" style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%); color:#fff; border-radius:4px 4px 0 0;">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="m-0" style="color:#fff;">
                                <i class="fa fa-shopping-cart"></i> &nbsp;Registrar Pedido
                            </h5>
                            <a href="{{ route('flujo.ventas') }}" class="btn btn-outline-light btn-sm" style="border-radius:8px; font-size:12px; font-weight:600;">
                                <i class="mr-1 fa fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>

                    <div class="ibox-content pedido-ibox-content" style="padding: 24px;">

                        {{-- N° Pedido --}}
                        <div class="mb-3 d-flex align-items-center" style="gap:10px;">
                            <span style="background:linear-gradient(135deg,#f39c12,#e67e22); color:#fff; border-radius:8px; padding:5px 14px; font-size:13px; font-weight:700; box-shadow:0 2px 8px rgba(243,156,18,.3);">
                                <i class="mr-1 fa fa-hashtag"></i> N° Pedido: {{ $pedidoGuardadoId ?? $numeroPedido }}
                            </span>
                        </div>

                        {{-- ==================== SECCIÓN 1: CLIENTE ==================== --}}
                        <div class="pedido-section-heading">
                            <span class="badge badge-primary step-badge">1</span>
                            <h5>Información del Cliente</h5>
                        </div>

                        {{-- ── Fila buscador + info condensada ── --}}
                        <div class="d-flex align-items-flex-start" style="gap:12px; flex-wrap:wrap;">

                            {{-- Columna izquierda: buscador --}}
                            <div style="flex:0 0 320px; min-width:200px; position:relative;">
                                @if (!$clienteSeleccionado)
                                <div class="input-group" style="border-radius:8px; overflow:hidden;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text border-right-0">
                                            <i class="fa fa-search" style="color:#1a7efb; font-size:13px;"></i>
                                        </span>
                                    </div>
                                    <input
                                        type="text"
                                        wire:model.debounce.350ms="busqueda"
                                        class="form-control border-left-0"
                                        placeholder="Nombre o RTN — escribe para buscar..."
                                        autocomplete="off">
                                    <div class="input-group-append">
                                        <span class="input-group-text border-left-0" style="padding:0 12px;">
                                            <span wire:loading wire:target="updatedBusqueda,buscarCliente">
                                                <i class="fa fa-spinner fa-spin" style="color:#1a7efb; font-size:11px;"></i>
                                            </span>
                                            <span wire:loading.remove wire:target="updatedBusqueda,buscarCliente">
                                                <i class="fa fa-user" style="color:#ccd; font-size:11px;"></i>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                @error('clienteSeleccionado')
                                    <small class="mt-1 text-danger d-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</small>
                                @enderror

                                {{-- Sugerencias --}}
                                @if (count($resultadosBusqueda) > 0)
                                <div class="sugerencias-box" style="position:absolute; z-index:50; left:0; right:0; top:calc(100% + 4px);">
                                    @foreach ($resultadosBusqueda as $r)
                                    <button type="button"
                                            class="px-3 py-2 border-0 sugerencia-item list-group-item list-group-item-action"
                                            wire:click="seleccionarCliente({{ $r['id'] }})">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="mr-1 fa fa-user-circle-o text-primary" style="font-size:12px;"></i>
                                                <strong style="font-size:13px;">{{ $r['nombre'] }}</strong>
                                            </div>
                                            @if ($r['rtn'])
                                            <span class="border badge badge-light" style="font-size:10px; color:#666;">{{ $r['rtn'] }}</span>
                                            @endif
                                        </div>
                                        @if (!empty($r['direccion']))
                                        <small class="text-muted d-block" style="font-size:11px; margin-top:1px;">
                                            <i class="mr-1 fa fa-map-marker"></i>{{ Str::limit($r['direccion'], 60) }}
                                        </small>
                                        @endif
                                    </button>
                                    @endforeach
                                    <button type="button"
                                            class="px-3 py-2 border-0 list-group-item list-group-item-action text-success"
                                            wire:click="abrirModalCrearCliente"
                                            style="font-size:12px; background:#f0fdf4; border-top:1px solid #dcfce7 !important;">
                                        <i class="mr-1 fa fa-plus-circle"></i>
                                        No lo veo — <strong>Crear nuevo cliente</strong>
                                    </button>
                                </div>
                                @elseif ($hasBuscado && strlen(trim($busqueda)) >= 2)
                                <div style="background:#fff8e1; border:1px solid #ffc107; border-radius:8px; padding:8px 12px; font-size:12px; margin-top:4px; display:flex; align-items:center; justify-content:space-between;">
                                    <span><i class="mr-1 fa fa-info-circle text-warning"></i>Sin resultados para <strong>"{{ $busqueda }}"</strong></span>
                                    <button type="button" class="ml-2 btn btn-success btn-sm" wire:click="abrirModalCrearCliente" style="border-radius:20px; font-size:11px;">
                                        <i class="fa fa-plus"></i> Crear
                                    </button>
                                </div>
                                @endif
                                @else
                                {{-- Cliente ya seleccionado: botón cambiar compact --}}
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100" wire:click="limpiarCliente"
                                        style="border-radius:8px; font-size:12px;">
                                    <i class="mr-1 fa fa-times"></i>Cambiar cliente
                                </button>
                                @endif
                            </div>

                            {{-- Columna derecha: info condensada del cliente seleccionado --}}
                            @if ($clienteSeleccionado)
                            <div class="cliente-card" style="flex:1; min-width:260px; animation:fadeInUp .25s ease both;">
                                <div style="background:linear-gradient(135deg,#f0fff8,#e8f4ff); border-radius:10px;
                                            box-shadow:0 2px 10px rgba(26,179,148,.12); padding:10px 16px;
                                            display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                                    <div style="flex:0 0 auto;">
                                        <div style="font-size:10px; text-transform:uppercase; letter-spacing:.8px; color:#999; line-height:1;">Cliente</div>
                                        <div style="font-size:15px; font-weight:700; color:#1a7efb; margin-top:2px;">
                                            <i class="mr-1 fa fa-user-circle"></i>{{ $clienteSeleccionado['nombre'] }}
                                        </div>
                                    </div>
                                    <div style="display:flex; gap:16px; flex-wrap:wrap; flex:1;">
                                        @if($clienteSeleccionado['rtn'] ?? null)
                                        <div style="line-height:1.3;">
                                            <div style="font-size:9px; text-transform:uppercase; color:#aaa;">RTN</div>
                                            <div style="font-size:12px; font-weight:600; color:#444;">{{ $clienteSeleccionado['rtn'] }}</div>
                                        </div>
                                        @endif
                                        @if($clienteSeleccionado['telefono_empresa'] ?? null)
                                        <div style="line-height:1.3;">
                                            <div style="font-size:9px; text-transform:uppercase; color:#aaa;">Tel</div>
                                            <div style="font-size:12px; font-weight:600; color:#444;">{{ $clienteSeleccionado['telefono_empresa'] }}</div>
                                        </div>
                                        @endif
                                        @if($clienteSeleccionado['credito'] ?? null)
                                        <div style="line-height:1.3;">
                                            <div style="font-size:9px; text-transform:uppercase; color:#aaa;">Crédito</div>
                                            <div style="font-size:12px; font-weight:700; color:#27ae60;">L. {{ number_format($clienteSeleccionado['credito'], 2) }}</div>
                                        </div>
                                        @endif
                                        @if($clienteSeleccionado['direccion'] ?? null)
                                        <div style="line-height:1.3;">
                                            <div style="font-size:9px; text-transform:uppercase; color:#aaa;">Dirección</div>
                                            <div style="font-size:12px; color:#555;">{{ Str::limit($clienteSeleccionado['direccion'], 45) }}</div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                        </div>{{-- /fila buscador+info --}}

                        {{-- ==================== SECCIÓN 2: PRODUCTOS ==================== --}}
                        <div class="pedido-section-heading">
                            <span class="badge badge-primary step-badge">2</span>
                            <h5>Productos del Pedido</h5>
                        </div>

                        <div class="row align-items-stretch">

                            {{-- ─────────── IZQUIERDA: lista editable de ítems ─────────── --}}
                            <div class="mb-3 col-12 col-md-7 d-flex flex-column">
                                <div class="mb-0 card productos-card flex-grow-1" style="box-shadow:0 2px 10px rgba(0,0,0,.06);">
                                    <div class="p-0 card-body">
                                        <div class="table-responsive">
                                            <table class="table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="pl-3 text-center" style="width:38px;">#</th>
                                                        <th>Producto <span class="text-danger">*</span></th>
                                                        <th class="text-center" style="width:110px;">Cantidad <span class="text-danger">*</span></th>
                                                        <th style="width:40px;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($items as $i => $item)
                                                    <tr wire:key="item-{{ $i }}" style="border-bottom:1px solid #f0f4ff; transition:background .1s;" onmouseover="this.style.background='#fafbff'" onmouseout="this.style.background=''">
                                                        <td class="pl-3 text-center align-middle">
                                                            <span class="text-muted" style="font-size:11px;">{{ $i + 1 }}</span>
                                                        </td>
                                                        <td class="py-2 align-middle">
                                                            <input
                                                                type="text"
                                                                wire:model.lazy="items.{{ $i }}.nombre_producto"
                                                                class="form-control form-control-sm @error('items.'.$i.'.nombre_producto') is-invalid @enderror"
                                                                placeholder="Nombre del producto..."
                                                            >
                                                            @error('items.'.$i.'.nombre_producto')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td class="py-2 text-center align-middle">
                                                            <input
                                                                type="number"
                                                                wire:model.lazy="items.{{ $i }}.cantidad"
                                                                class="form-control form-control-sm text-center @error('items.'.$i.'.cantidad') is-invalid @enderror"
                                                                placeholder="0"
                                                                min="0.01"
                                                                step="0.01"
                                                            >
                                                            @error('items.'.$i.'.cantidad')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td class="py-2 pr-2 text-center align-middle">
                                                            @if (count($items) > 1)
                                                            <button
                                                                type="button"
                                                                class="btn btn-danger btn-sm"
                                                                wire:click="eliminarItem({{ $i }})"
                                                                style="border-radius:50%; width:26px; height:26px; padding:0; line-height:24px;"
                                                                title="Quitar"
                                                            ><i class="fa fa-times" style="font-size:10px;"></i></button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center" style="gap:10px;">
                                        <button type="button" class="btn btn-outline-primary btn-sm" wire:click="agregarItem" style="border-radius:20px; font-size:12px;">
                                            <i class="mr-1 fa fa-plus-circle"></i>Agregar fila
                                        </button>
                                        <small class="text-muted" style="font-size:12px;">
                                            <i class="mr-1 fa fa-list-ul"></i>{{ count($items) }} producto(s) en el pedido
                                        </small>
                                    </div>
                                </div>
                            </div>

                            {{-- ─────────── DERECHA: importar desde Excel ─────────── --}}
                            <div class="mb-3 col-12 col-md-5 d-flex flex-column">
                                {{-- ── ESTADO: ya importado ── --}}
                                @if ($excelImportado)
                                <div class="px-3 py-3 d-flex align-items-center justify-content-between"
                                     style="background:linear-gradient(90deg,#f0fdf4,#ecfdf5); border:1.5px solid #86efac; border-radius:12px;">
                                    <div class="d-flex align-items-center" style="gap:10px;">
                                        <div style="width:34px; height:34px; background:#dcfce7; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                                            <i class="fa fa-check" style="color:#16a34a; font-size:14px;"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0" style="font-size:13px; font-weight:600; color:#15803d;">Excel importado</p>
                                            <p class="mb-0" style="font-size:11px; color:#4ade80;">Productos añadidos a la lista</p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="limpiarExcel"
                                        class="btn btn-sm btn-outline-secondary"
                                        style="border-radius:20px; font-size:11px;"
                                        title="Importar otro Excel"
                                    >
                                        <i class="mr-1 fa fa-refresh"></i>Nuevo Excel
                                    </button>
                                </div>

                                {{-- ── ESTADO: zona de carga + vista previa ── --}}
                                @else
                                <div class="mb-0 card excel-card flex-grow-1 d-flex flex-column justify-content-center">

                                    {{-- Cabecera solo si no hay preview activo --}}
                                    @if (!$showExcelPreview)
                                    <div class="px-3 py-4 text-center card-body">
                                        <div style="width:54px; height:54px; background:linear-gradient(135deg,#dbeafe,#d1fae5); border-radius:50%; margin:0 auto 10px; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(26,179,148,.15);">
                                            <i class="fa fa-file-excel-o" style="font-size:22px; color:#1ab394;"></i>
                                        </div>
                                        <h6 class="mb-1 font-weight-bold" style="color:#1e3a5f; font-size:14px;">Importar desde Excel</h6>
                                        <p class="mb-3" style="font-size:11px; color:#7a8fa6; line-height:1.7;">
                                            Sube un <strong>.xlsx</strong> con dos columnas:<br>
                                            <code style="background:#e0f2fe; color:#0369a1; padding:1px 5px; border-radius:3px; font-size:10px;">Producto</code>
                                            &nbsp;<span class="text-muted" style="font-size:10px;">y</span>&nbsp;
                                            <code style="background:#d1fae5; color:#065f46; padding:1px 5px; border-radius:3px; font-size:10px;">Cantidad</code>
                                            <br><span style="font-size:10px; color:#aab;">(datos desde la fila 2)</span>
                                        </p>
                                        <div style="position:relative; display:inline-block;">
                                            <label
                                                for="inputExcel"
                                                class="px-4 mb-1 btn btn-success btn-sm"
                                                style="border-radius:20px; cursor:pointer; font-size:12px; box-shadow:0 2px 6px rgba(26,179,148,.3);"
                                            >
                                                <span wire:loading.remove wire:target="archivoExcel">
                                                    <i class="mr-1 fa fa-upload"></i> Seleccionar .xlsx
                                                </span>
                                                <span wire:loading wire:target="archivoExcel">
                                                    <i class="mr-1 fa fa-spinner fa-spin"></i> Procesando...
                                                </span>
                                            </label>
                                            <input
                                                id="inputExcel"
                                                type="file"
                                                wire:model="archivoExcel"
                                                accept=".xlsx,.xls"
                                                style="position:absolute; width:1px; height:1px; opacity:0; overflow:hidden;"
                                            >
                                        </div>
                                        <div>
                                            <a href="#" wire:click.prevent="descargarPlantilla" style="font-size:11px; color:#64748b;">
                                                <i class="mr-1 fa fa-download"></i>Descargar plantilla .xlsx
                                            </a>
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Errores --}}
                                    @if ($mensajeExcelError)
                                    <div class="px-3 pt-2 pb-2">
                                        <div class="px-3 py-2 mb-0 alert alert-warning" style="border-radius:8px; font-size:12px;">
                                            <i class="mr-1 fa fa-exclamation-triangle"></i>{{ $mensajeExcelError }}
                                        </div>
                                    </div>
                                    @endif
                                    @error('archivoExcel')
                                    <div class="px-3 pt-2 pb-2">
                                        <div class="px-3 py-2 mb-0 alert alert-warning" style="border-radius:8px; font-size:12px;">
                                            <i class="mr-1 fa fa-exclamation-triangle"></i>{{ $message }}
                                        </div>
                                    </div>
                                    @enderror

                                    {{-- Vista previa con checkboxes --}}
                                    @if ($showExcelPreview && count($excelPreview) > 0)
                                    @php
                                        $totalExcel         = count($excelPreview);
                                        $seleccionadosExcel = count(array_filter($excelSeleccionados));
                                        $totalPags          = (int) ceil($totalExcel / $excelPorPagina);
                                        $offset             = ($excelPagina - 1) * $excelPorPagina;
                                        $paginaItems        = array_slice($excelPreview, $offset, $excelPorPagina, true);
                                    @endphp
                                    <div style="border-top:2px solid #93c5fd;">

                                        {{-- Barra seleccionados + seleccionar todos --}}
                                        <div class="px-3 py-2 d-flex justify-content-between align-items-center" style="background:#eff6ff; border-bottom:1px solid #bfdbfe;">
                                            <span style="font-size:12px; font-weight:600; color:#1d4ed8;">
                                                <i class="mr-1 fa fa-check-square-o"></i>
                                                {{ $seleccionadosExcel }}&nbsp;/&nbsp;{{ $totalExcel }}
                                                <span style="color:#64748b; font-weight:400;">seleccionados</span>
                                            </span>
                                            <div style="display:flex; gap:6px; align-items:center;">
                                                <button type="button" wire:click="seleccionarTodosExcel"
                                                    class="p-0 btn btn-link"
                                                    style="font-size:11px; color:#1d4ed8; text-decoration:none; font-weight:600;">Todos</button>
                                                <span style="color:#cbd5e1; font-size:10px;">|</span>
                                                <button type="button" wire:click="deseleccionarTodosExcel"
                                                    class="p-0 btn btn-link"
                                                    style="font-size:11px; color:#94a3b8; text-decoration:none;">Ninguno</button>
                                                <span style="color:#cbd5e1; font-size:10px;">|</span>
                                                <button type="button" wire:click="limpiarExcel"
                                                    class="p-0 btn btn-link"
                                                    style="font-size:11px; color:#ef4444; text-decoration:none;"
                                                    title="Cancelar importación">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Tabla paginada (10 por página) --}}
                                        <div style="background:#fff;">
                                            <table class="table mb-0 table-sm" style="font-size:12px;">
                                                <thead style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                                                    <tr>
                                                        <th class="py-2 pl-2 text-center" style="width:34px;"></th>
                                                        <th class="py-2" style="color:#475569; font-weight:600;">Producto</th>
                                                        <th class="py-2 text-center" style="width:64px; color:#475569; font-weight:600;">Cant.</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($paginaItems as $pi => $prow)
                                                    @php $checked = !empty($excelSeleccionados[$pi]); @endphp
                                                    <tr wire:key="prev-{{ $pi }}"
                                                        style="border-bottom:1px solid #f1f5f9; {{ $checked ? '' : 'opacity:.35;' }} transition:opacity .15s;">
                                                        <td class="py-2 pl-2 text-center align-middle">
                                                            <input
                                                                type="checkbox"
                                                                wire:model="excelSeleccionados.{{ $pi }}"
                                                                style="width:14px; height:14px; cursor:pointer; accent-color:#10b981;"
                                                            >
                                                        </td>
                                                        <td class="py-2 align-middle"
                                                            style="{{ $checked ? 'color:#1e293b;' : 'text-decoration:line-through; color:#94a3b8;' }}">
                                                            {{ $prow['nombre_producto'] }}
                                                        </td>
                                                        <td class="py-2 text-center align-middle font-weight-bold"
                                                            style="{{ $checked ? 'color:#10b981;' : 'color:#cbd5e1;' }}">
                                                            {{ $prow['cantidad'] }}
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        {{-- Paginación --}}
                                        @if ($totalPags > 1)
                                        <div class="px-3 py-2 d-flex justify-content-between align-items-center"
                                             style="background:#fafafa; border-top:1px solid #e2e8f0;">
                                            <button
                                                type="button"
                                                wire:click="excelPaginaAnterior"
                                                class="btn btn-sm btn-light"
                                                style="font-size:11px; border-radius:6px;"
                                                {{ $excelPagina <= 1 ? 'disabled' : '' }}
                                            ><i class="fa fa-chevron-left"></i></button>
                                            <span style="font-size:11px; color:#64748b;">
                                                Página {{ $excelPagina }} / {{ $totalPags }}
                                                &nbsp;&middot;&nbsp;
                                                {{ $totalExcel }} productos
                                            </span>
                                            <button
                                                type="button"
                                                wire:click="excelPaginaSiguiente"
                                                class="btn btn-sm btn-light"
                                                style="font-size:11px; border-radius:6px;"
                                                {{ $excelPagina >= $totalPags ? 'disabled' : '' }}
                                            ><i class="fa fa-chevron-right"></i></button>
                                        </div>
                                        @endif

                                        {{-- Pie: confirmar --}}
                                        <div class="px-3 py-2 d-flex justify-content-end align-items-center"
                                             style="border-top:1px solid #e2e8f0; background:#f8fafc;">
                                            <button
                                                type="button"
                                                class="px-4 btn btn-success btn-sm"
                                                wire:click="importarDesdeExcel"
                                                style="border-radius:20px; font-size:12px; {{ $seleccionadosExcel === 0 ? 'opacity:.45; cursor:not-allowed;' : 'box-shadow:0 2px 6px rgba(16,185,129,.3);' }}"
                                                {{ $seleccionadosExcel === 0 ? 'disabled' : '' }}
                                            >
                                                <i class="mr-1 fa fa-check"></i>
                                                Agregar {{ $seleccionadosExcel }} al pedido
                                            </button>
                                        </div>
                                    </div>
                                    @endif

                                </div>{{-- /card excel --}}
                                @endif

                            </div>{{-- /col derecha --}}

                        </div>{{-- /row dos columnas --}}

                        <hr class="my-3" style="border-color:#edf1f9;">

                        {{-- ==================== SECCIÓN 3: OBSERVACIONES + BOTÓN ==================== --}}
                        <div class="pedido-section-heading">
                            <span class="badge badge-primary step-badge">3</span>
                            <h5>Observaciones</h5>
                        </div>

                        <div class="row align-items-end">

                            {{-- Textarea --}}
                            <div class="mb-3 col-12 col-md-7 mb-md-0">
                                <textarea
                                    wire:model.lazy="observaciones"
                                    class="form-control"
                                    rows="3"
                                    style="border-radius:8px; resize:vertical; font-size:13px; border-color:#dce3f0;"
                                    placeholder="Notas u observaciones adicionales del pedido (opcional)..."
                                ></textarea>
                            </div>

                            {{-- Botón guardar --}}
                            <div class="text-right col-12 col-md-5">
                                <button
                                    type="button"
                                    class="btn btn-primary btn-block btn-lg"
                                    wire:click="guardarPedido"
                                    style="border-radius:10px; font-size:15px; padding:13px 28px;
                                           background:linear-gradient(135deg,#1a7efb,#1ab394);
                                           border:none; box-shadow:0 4px 16px rgba(26,126,251,.35);
                                           transition:transform .15s, box-shadow .15s;"
                                    onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(26,126,251,.45)';"
                                    onmouseout="this.style.transform='';this.style.boxShadow='0 4px 16px rgba(26,126,251,.35)';"
                                >
                                    <span wire:loading.remove wire:target="guardarPedido">
                                        <i class="mr-1 fa fa-save"></i> Registrar Pedido
                                    </span>
                                    <span wire:loading wire:target="guardarPedido">
                                        <i class="mr-1 fa fa-spinner fa-spin"></i> Registrando...
                                    </span>
                                </button>
                            </div>

                        </div>

                    </div>{{-- /ibox-content --}}
                </div>{{-- /ibox --}}
            </div>
        </div>

    </div>{{-- /wrapper-content --}}

    {{-- Modal global de flujo (escucha abrirFlujoPedido / abrirFlujoCotizacion) --}}
    <livewire:flujo.modal-flujo-pedido />

    {{-- ===== SCROLL TO TOP ON SAVE ===== --}}
    <script>
        function abrirFlujoPedidoDesdeExito(pedidoId) {
            var pId = pedidoId ? parseInt(pedidoId, 10) : null;
            if (!pId) {
                Swal.fire({ icon: 'info', title: 'Sin pedido', text: 'No se encontró el pedido para abrir el flujo.' });
                return;
            }

            Livewire.emit('abrirFlujoPedido', pId, 'pedido');
        }

        window.addEventListener('scroll-top', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>

    {{-- ==================== MODAL: CREAR NUEVO CLIENTE ==================== --}}
    @if ($showModalCliente)
    <div class="fv-overlay" style="z-index:2060;" tabindex="-1" role="dialog">
        <div class="fv-dialog-md" role="document">
            <div class="fv-modal-content">

                {{-- Header --}}
                <div class="fv-modal-header fv-hdr-orange">
                    <h5 class="fv-modal-title">
                        <i class="fa fa-user-plus mr-2"></i>Crear Nuevo Cliente
                    </h5>
                    <button type="button" class="fv-modal-close" wire:click="cerrarModalCrearCliente">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="fv-modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Nombre <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                wire:model.lazy="nc_nombre"
                                class="form-control @error('nc_nombre') is-invalid @enderror"
                                placeholder="Nombre completo del cliente"
                                style="border-radius:6px;"
                            >
                            @error('nc_nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">RTN</label>
                            <input
                                type="text"
                                wire:model.lazy="nc_rtn"
                                class="form-control @error('nc_rtn') is-invalid @enderror"
                                placeholder="0000-0000-000000"
                                style="border-radius:6px;"
                            >
                            @error('nc_rtn') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Correo Electrónico</label>
                            <input
                                type="email"
                                wire:model.lazy="nc_correo"
                                class="form-control @error('nc_correo') is-invalid @enderror"
                                placeholder="correo@ejemplo.com"
                                style="border-radius:6px;"
                            >
                            @error('nc_correo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Teléfono</label>
                            <input
                                type="text"
                                wire:model.lazy="nc_telefono"
                                class="form-control"
                                placeholder="+504 0000-0000"
                                style="border-radius:6px;"
                            >
                        </div>
                        <div class="mb-0 col-12 form-group">
                            <label class="font-weight-bold">Dirección</label>
                            <textarea
                                wire:model.lazy="nc_direccion"
                                class="form-control"
                                rows="2"
                                placeholder="Dirección del cliente..."
                                style="border-radius:6px;"
                            ></textarea>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="fv-modal-footer">
                    <button type="button" class="btn btn-default" wire:click="cerrarModalCrearCliente">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="px-4 btn btn-primary" wire:click="guardarNuevoCliente">
                        <span wire:loading.remove wire:target="guardarNuevoCliente">
                            <i class="fa fa-save"></i> Guardar Cliente
                        </span>
                        <span wire:loading wire:target="guardarNuevoCliente">
                            <i class="fa fa-spinner fa-spin"></i> Guardando...
                        </span>
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

</div>
