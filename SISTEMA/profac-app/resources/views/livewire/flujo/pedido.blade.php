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
    @keyframes shimmer {
        0%   { background-position: -400px 0; }
        100% { background-position: 400px 0; }
    }
    @keyframes fadeInUp {
        from { transform: translateY(16px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }

    /* ── Badge numerado animado ── */
    .step-badge {
        animation: pulseRing 2.5s ease-in-out infinite;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px; height: 26px;
        font-size: 12px;
        border-radius: 50%;
        font-weight: 700;
        flex-shrink: 0;
    }

    /* ── Botones de acción post-guardado ── */
    .accion-row {
        display: flex;
        flex-wrap: nowrap;
        margin: 0 -6px;
    }
    .accion-col {
        flex: 1;
        padding: 0 6px;
        display: flex;
        flex-direction: column;
    }
    .accion-btn {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        padding: 11px 8px;
        text-align: center;
        text-decoration: none !important;
        transition: transform .2s cubic-bezier(.34,1.56,.64,1), box-shadow .2s ease;
        position: relative;
        overflow: hidden;
        min-height: 68px;
    }
    .accion-btn::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(255,255,255,.12);
        transform: translateX(-100%) skewX(-15deg);
        transition: transform .35s ease;
    }
    .accion-btn:hover::after  { transform: translateX(120%) skewX(-15deg); }
    .accion-btn:hover {
        transform: translateY(-4px) scale(1.04);
    }
    .accion-btn:active { transform: translateY(-1px) scale(.98); }

    /* ── Sugerencias live search ── */
    .sugerencia-item {
        transition: background .12s, padding-left .12s;
        cursor: pointer;
    }
    .sugerencia-item:hover { background: #f0f7ff !important; padding-left: 20px !important; }

    /* ── Tarjeta cliente seleccionado ── */
    .cliente-card {
        animation: fadeInUp .35s ease both;
    }

    /* ── Filas de la tabla de productos ── */
    .item-row { transition: background .15s ease; }

    /* ── Panel de éxito ── */
    .panel-exito { animation: fadeInUp .4s ease both; }
</style>

    {{-- ===== ENCABEZADO ===== --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2><i class="fa fa-shopping-cart text-primary"></i> Nuevo Pedido</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item active">
                    <strong>Pedido</strong>
                </li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- ===== PANEL DE ACCIONES POST-GUARDADO ===== --}}
        @if ($pedidoGuardadoId)
            <div class="row panel-exito">
                <div class="col-lg-12">
                    <div class="ibox mb-0" style="border-radius:12px; overflow:hidden; border:2px solid #1ab394; box-shadow:0 6px 28px rgba(26,179,148,.18);">

                        {{-- Header compacto --}}
                        <div class="ibox-title py-2 px-3" style="background:linear-gradient(135deg,#1ab394 0%,#1a7efb 100%); border:none;">
                            <div class="d-flex align-items-center justify-content-between">
                                <span style="color:#fff; font-size:14px; font-weight:600;">
                                    <i class="fa fa-check-circle mr-1"></i>
                                    ¡Pedido <strong>#{{ $pedidoGuardadoId }}</strong> registrado con éxito!
                                </span>
                                <span style="color:rgba(255,255,255,.8); font-size:12px;">¿Qué desea hacer?</span>
                            </div>
                        </div>

                        <div class="ibox-content py-3 px-3">
                            <div class="accion-row">

                                {{-- Crear Oferta ─ delay 0.1s --}}
                                <div class="accion-col">
                                    <div style="animation:popIn .4s ease .08s both; flex:1; display:flex; flex-direction:column;">
                                        <a href="/proforma/cotizacion/1"
                                           class="accion-btn"
                                           style="background:linear-gradient(135deg,#1a7efb,#0d6efd); color:#fff; box-shadow:0 4px 14px rgba(26,126,251,.35);">
                                            <i class="fa fa-file-text-o d-block mb-1" style="font-size:18px;"></i>
                                            <span style="font-size:11px; font-weight:700; letter-spacing:.3px;">CREAR OFERTA</span>
                                        </a>
                                    </div>
                                </div>

                                {{-- Imprimir ─ delay 0.2s --}}
                                <div class="accion-col">
                                    <div style="animation:popIn .4s ease .18s both; flex:1; display:flex; flex-direction:column;">
                                        <a href="/flujo/pedido/imprimir/{{ $pedidoGuardadoId }}"
                                           target="_blank"
                                           class="accion-btn"
                                           style="background:linear-gradient(135deg,#1ab394,#0fa37a); color:#fff; box-shadow:0 4px 14px rgba(26,179,148,.35);">
                                            <i class="fa fa-print d-block mb-1" style="font-size:18px;"></i>
                                            <span style="font-size:11px; font-weight:700; letter-spacing:.3px;">IMPRIMIR</span>
                                        </a>
                                    </div>
                                </div>

                                {{-- Exportar Excel ─ delay 0.3s --}}
                                <div class="accion-col">
                                    <div style="animation:popIn .4s ease .28s both; flex:1; display:flex; flex-direction:column;">
                                        <a href="/flujo/pedido/exportar/{{ $pedidoGuardadoId }}"
                                           class="accion-btn"
                                           style="background:linear-gradient(135deg,#217346,#1a5c38); color:#fff; box-shadow:0 4px 14px rgba(33,115,70,.35);">
                                            <i class="fa fa-file-excel-o d-block mb-1" style="font-size:18px;"></i>
                                            <span style="font-size:11px; font-weight:700; letter-spacing:.3px;">EXCEL</span>
                                        </a>
                                    </div>
                                </div>

                                {{-- Historial ─ delay 0.4s --}}
                                <div class="accion-col">
                                    <div style="animation:popIn .4s ease .38s both; flex:1; display:flex; flex-direction:column;">
                                        <a href="/flujo/pedidos/historico"
                                           class="accion-btn"
                                           style="background:linear-gradient(135deg,#6c5ce7,#5544d0); color:#fff; box-shadow:0 4px 14px rgba(108,92,231,.35);">
                                            <i class="fa fa-list-alt d-block mb-1" style="font-size:18px;"></i>
                                            <span style="font-size:11px; font-weight:700; letter-spacing:.3px;">HISTORIAL</span>
                                        </a>
                                    </div>
                                </div>

                            </div>

                            <div class="text-center mt-2">
                                <button
                                    type="button"
                                    wire:click="nuevoPedido"
                                    class="btn btn-default btn-xs"
                                    style="border-radius:20px; font-size:11px; padding:4px 14px; color:#888;">
                                    <i class="fa fa-plus-circle mr-1"></i>Registrar otro pedido
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div style="height:16px;"></div>
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
                    <div class="ibox-title" style="background: linear-gradient(135deg,#1a7efb 0%,#1ab394 100%); color:#fff; border-radius:4px 4px 0 0;">
                        <h5 class="m-0" style="color:#fff;">
                            <i class="fa fa-shopping-cart"></i> &nbsp;Registrar Pedido
                        </h5>
                    </div>

                    <div class="ibox-content" style="padding: 30px;">

                        {{-- ==================== SECCIÓN 1: CLIENTE ==================== --}}
                        <div class="row mb-2">
                            <div class="col-12">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge badge-primary step-badge mr-2">1</span>
                                    <h5 class="m-0 text-dark" style="font-size:15px;">Información del Cliente</h5>
                                </div>
                            </div>
                        </div>

                        @if (!$clienteSeleccionado)
                        {{-- ── Buscador live ── --}}
                        <div class="row mb-2">
                            <div class="col-lg-6">
                                <div class="input-group" style="box-shadow:0 2px 8px rgba(26,126,251,.10); border-radius:8px; overflow:hidden;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-right:none;">
                                            <i class="fa fa-search" style="color:#1a7efb; font-size:12px;"></i>
                                        </span>
                                    </div>
                                    <input
                                        type="text"
                                        wire:model.debounce.350ms="busqueda"
                                        class="form-control border-left-0"
                                        style="font-size:13px; border-color:#e0e9ff; border-left:none;"
                                        placeholder="Nombre o RTN — escribe para buscar..."
                                        autocomplete="off"
                                    >
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-white" style="border-left:none; border-color:#e0e9ff; padding:0 10px;">
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
                                    <small class="text-danger"><i class="fa fa-exclamation-circle"></i> {{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        {{-- ── Sugerencias (dropdown live) ── --}}
                        @if (count($resultadosBusqueda) > 0)
                        <div class="row mb-2">
                            <div class="col-lg-6">
                                <div style="border-radius:8px; box-shadow:0 6px 20px rgba(26,126,251,.15); border:1px solid #e0e9ff; background:#fff; animation:slideDown .2s ease both; overflow:hidden;">
                                    @foreach ($resultadosBusqueda as $r)
                                    <button
                                        type="button"
                                        class="sugerencia-item list-group-item list-group-item-action border-0 py-2 px-3"
                                        wire:click="seleccionarCliente({{ $r['id'] }})"
                                    >
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fa fa-user-circle-o text-primary mr-1" style="font-size:12px;"></i>
                                                <strong style="font-size:13px;">{{ $r['nombre'] }}</strong>
                                            </div>
                                            @if ($r['rtn'])
                                            <span class="badge badge-light border" style="font-size:10px; color:#666;">{{ $r['rtn'] }}</span>
                                            @endif
                                        </div>
                                        @if (!empty($r['direccion']))
                                        <small class="text-muted d-block" style="font-size:11px; margin-top:1px;"><i class="fa fa-map-marker mr-1"></i>{{ Str::limit($r['direccion'], 55) }}</small>
                                        @endif
                                    </button>
                                    @endforeach
                                    {{-- Opción: crear cliente nuevo --}}
                                    <button
                                        type="button"
                                        class="list-group-item list-group-item-action border-0 py-2 px-3 text-success"
                                        wire:click="abrirModalCrearCliente"
                                        style="font-size:12px; background:#f0fdf4; border-top:1px solid #dcfce7 !important;"
                                    >
                                        <i class="fa fa-plus-circle mr-1"></i>
                                        No lo veo — <strong>Crear nuevo cliente</strong>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Sin resultados tras búsqueda explícita --}}
                        @elseif ($hasBuscado && strlen(trim($busqueda)) >= 2)
                        <div class="row mb-2">
                            <div class="col-lg-6">
                                <div class="d-flex align-items-center justify-content-between"
                                     style="background:#fff8e1; border:1px solid #ffc107; border-radius:8px; padding:8px 12px; font-size:13px;">
                                    <div>
                                        <i class="fa fa-info-circle text-warning mr-1"></i>
                                        Sin resultados para <strong>"{{ $busqueda }}"</strong>
                                    </div>
                                    <button type="button" class="btn btn-success btn-xs ml-2" wire:click="abrirModalCrearCliente"
                                            style="border-radius:20px; font-size:11px;">
                                        <i class="fa fa-plus"></i> Crear
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endif

                        {{-- Tarjeta del cliente seleccionado --}}
                        @if ($clienteSeleccionado)
                        <div class="row mb-4">
                            <div class="col-lg-9">
                                <div class="card mb-0" style="border:none; background:linear-gradient(135deg,#f0fff8,#e8f4ff); border-radius:12px; box-shadow:0 2px 12px rgba(26,179,148,.15);">
                                    <div class="card-body py-3 px-4">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <p class="mb-0 text-muted" style="font-size:11px; text-transform:uppercase; letter-spacing:1px;">Cliente seleccionado</p>
                                                <h4 class="mb-0 mt-1" style="color:#1a7efb;">
                                                    <i class="fa fa-user-circle"></i>
                                                    {{ $clienteSeleccionado['nombre'] }}
                                                </h4>
                                            </div>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="limpiarCliente">
                                                <i class="fa fa-times"></i> Cambiar
                                            </button>
                                        </div>
                                        <hr class="my-2">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <p class="mb-0">
                                                    <small class="text-muted d-block">RTN</small>
                                                    <strong>{{ $clienteSeleccionado['rtn'] ?? '—' }}</strong>
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="mb-0">
                                                    <small class="text-muted d-block">Teléfono</small>
                                                    <strong>{{ $clienteSeleccionado['telefono_empresa'] ?? '—' }}</strong>
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="mb-0">
                                                    <small class="text-muted d-block">Correo</small>
                                                    <strong>{{ $clienteSeleccionado['correo'] ?? '—' }}</strong>
                                                </p>
                                            </div>
                                            @if ($clienteSeleccionado['credito'])
                                            <div class="col-md-3">
                                                <p class="mb-0">
                                                    <small class="text-muted d-block">Crédito</small>
                                                    <span class="badge badge-success" style="font-size:13px;">
                                                        L. {{ number_format($clienteSeleccionado['credito'], 2) }}
                                                    </span>
                                                </p>
                                            </div>
                                            @endif
                                            <div class="col-md-9 mt-2">
                                                <p class="mb-0">
                                                    <small class="text-muted d-block">Dirección</small>
                                                    <strong>{{ $clienteSeleccionado['direccion'] ?? '—' }}</strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <hr class="my-3" style="border-color:#edf1f9;">

                        {{-- ==================== SECCIÓN 2: PRODUCTOS ==================== --}}
                        <div class="row mb-2">
                            <div class="col-12">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge badge-primary step-badge mr-2">2</span>
                                    <h5 class="m-0 text-dark" style="font-size:15px;">Productos del Pedido</h5>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-start">

                            {{-- ─────────── IZQUIERDA: lista editable de ítems ─────────── --}}
                            <div class="col-lg-7 mb-4">
                                <div class="card mb-0" style="border:1px solid #dce3f0; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.06);">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table mb-0" style="font-size:13px;">
                                                <thead>
                                                    <tr style="background:#f0f4ff;">
                                                        <th class="text-center py-2 pl-3" style="width:38px; border-bottom:2px solid #dce3f0; color:#8899bb; font-weight:600;">#</th>
                                                        <th class="py-2" style="border-bottom:2px solid #dce3f0; font-weight:600;">Producto <span class="text-danger">*</span></th>
                                                        <th class="py-2 text-center" style="width:120px; border-bottom:2px solid #dce3f0; font-weight:600;">Cantidad <span class="text-danger">*</span></th>
                                                        <th class="py-2" style="width:44px; border-bottom:2px solid #dce3f0;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($items as $i => $item)
                                                    <tr wire:key="item-{{ $i }}" style="border-bottom:1px solid #f0f4ff; transition:background .1s;" onmouseover="this.style.background='#fafbff'" onmouseout="this.style.background=''">
                                                        <td class="align-middle text-center pl-3">
                                                            <span class="text-muted" style="font-size:11px;">{{ $i + 1 }}</span>
                                                        </td>
                                                        <td class="align-middle py-2">
                                                            <input
                                                                type="text"
                                                                wire:model.lazy="items.{{ $i }}.nombre_producto"
                                                                class="form-control form-control-sm @error('items.'.$i.'.nombre_producto') is-invalid @enderror"
                                                                placeholder="Nombre del producto..."
                                                                style="border-radius:6px; border-color:#dce3f0;"
                                                            >
                                                            @error('items.'.$i.'.nombre_producto')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td class="align-middle py-2 text-center">
                                                            <input
                                                                type="number"
                                                                wire:model.lazy="items.{{ $i }}.cantidad"
                                                                class="form-control form-control-sm text-center @error('items.'.$i.'.cantidad') is-invalid @enderror"
                                                                placeholder="0"
                                                                min="0.01"
                                                                step="0.01"
                                                                style="border-radius:6px; border-color:#dce3f0;"
                                                            >
                                                            @error('items.'.$i.'.cantidad')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td class="align-middle text-center py-2 pr-2">
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
                                    <div class="card-footer py-2 px-3 d-flex align-items-center" style="background:#fafbff; border-top:1px solid #e8eef8; gap:10px;">
                                        <button type="button" class="btn btn-outline-primary btn-sm" wire:click="agregarItem" style="border-radius:20px; font-size:12px;">
                                            <i class="fa fa-plus-circle mr-1"></i>Agregar fila
                                        </button>
                                        <small class="text-muted" style="font-size:12px;">
                                            <i class="fa fa-list-ul mr-1"></i>{{ count($items) }} producto(s) en el pedido
                                        </small>
                                    </div>
                                </div>
                            </div>

                            {{-- ─────────── DERECHA: importar desde Excel ─────────── --}}
                            <div class="col-lg-5 mb-4">

                                {{-- ── ESTADO: ya importado ── --}}
                                @if ($excelImportado)
                                <div class="d-flex align-items-center justify-content-between px-3 py-3"
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
                                        <i class="fa fa-refresh mr-1"></i>Nuevo Excel
                                    </button>
                                </div>

                                {{-- ── ESTADO: zona de carga + vista previa ── --}}
                                @else
                                <div class="card mb-0" style="border:2px dashed #93c5fd; border-radius:12px; background:linear-gradient(160deg,#f7fbff 0%,#f0fff8 100%); overflow:hidden; box-shadow:none;">

                                    {{-- Cabecera solo si no hay preview activo --}}
                                    @if (!$showExcelPreview)
                                    <div class="card-body text-center py-4 px-3">
                                        <div style="width:54px; height:54px; background:linear-gradient(135deg,#dbeafe,#d1fae5); border-radius:50%; margin:0 auto 10px; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(26,179,148,.15);">
                                            <i class="fa fa-file-excel-o" style="font-size:22px; color:#1ab394;"></i>
                                        </div>
                                        <h6 class="font-weight-bold mb-1" style="color:#1e3a5f; font-size:14px;">Importar desde Excel</h6>
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
                                                class="btn btn-success btn-sm px-4 mb-1"
                                                style="border-radius:20px; cursor:pointer; font-size:12px; box-shadow:0 2px 6px rgba(26,179,148,.3);"
                                            >
                                                <span wire:loading.remove wire:target="archivoExcel">
                                                    <i class="fa fa-upload mr-1"></i> Seleccionar .xlsx
                                                </span>
                                                <span wire:loading wire:target="archivoExcel">
                                                    <i class="fa fa-spinner fa-spin mr-1"></i> Procesando...
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
                                                <i class="fa fa-download mr-1"></i>Descargar plantilla .xlsx
                                            </a>
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Errores --}}
                                    @if ($mensajeExcelError)
                                    <div class="px-3 pb-2 pt-2">
                                        <div class="alert alert-warning py-2 px-3 mb-0" style="border-radius:8px; font-size:12px;">
                                            <i class="fa fa-exclamation-triangle mr-1"></i>{{ $mensajeExcelError }}
                                        </div>
                                    </div>
                                    @endif
                                    @error('archivoExcel')
                                    <div class="px-3 pb-2 pt-2">
                                        <div class="alert alert-warning py-2 px-3 mb-0" style="border-radius:8px; font-size:12px;">
                                            <i class="fa fa-exclamation-triangle mr-1"></i>{{ $message }}
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
                                        <div class="d-flex justify-content-between align-items-center px-3 py-2" style="background:#eff6ff; border-bottom:1px solid #bfdbfe;">
                                            <span style="font-size:12px; font-weight:600; color:#1d4ed8;">
                                                <i class="fa fa-check-square-o mr-1"></i>
                                                {{ $seleccionadosExcel }}&nbsp;/&nbsp;{{ $totalExcel }}
                                                <span style="color:#64748b; font-weight:400;">seleccionados</span>
                                            </span>
                                            <div style="display:flex; gap:6px; align-items:center;">
                                                <button type="button" wire:click="seleccionarTodosExcel"
                                                    class="btn btn-link p-0"
                                                    style="font-size:11px; color:#1d4ed8; text-decoration:none; font-weight:600;">Todos</button>
                                                <span style="color:#cbd5e1; font-size:10px;">|</span>
                                                <button type="button" wire:click="deseleccionarTodosExcel"
                                                    class="btn btn-link p-0"
                                                    style="font-size:11px; color:#94a3b8; text-decoration:none;">Ninguno</button>
                                                <span style="color:#cbd5e1; font-size:10px;">|</span>
                                                <button type="button" wire:click="limpiarExcel"
                                                    class="btn btn-link p-0"
                                                    style="font-size:11px; color:#ef4444; text-decoration:none;"
                                                    title="Cancelar importación">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Tabla paginada (10 por página) --}}
                                        <div style="background:#fff;">
                                            <table class="table table-sm mb-0" style="font-size:12px;">
                                                <thead style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                                                    <tr>
                                                        <th class="text-center py-2 pl-2" style="width:34px;"></th>
                                                        <th class="py-2" style="color:#475569; font-weight:600;">Producto</th>
                                                        <th class="text-center py-2" style="width:64px; color:#475569; font-weight:600;">Cant.</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($paginaItems as $pi => $prow)
                                                    @php $checked = !empty($excelSeleccionados[$pi]); @endphp
                                                    <tr wire:key="prev-{{ $pi }}"
                                                        style="border-bottom:1px solid #f1f5f9; {{ $checked ? '' : 'opacity:.35;' }} transition:opacity .15s;">
                                                        <td class="text-center align-middle pl-2 py-2">
                                                            <input
                                                                type="checkbox"
                                                                wire:model="excelSeleccionados.{{ $pi }}"
                                                                style="width:14px; height:14px; cursor:pointer; accent-color:#10b981;"
                                                            >
                                                        </td>
                                                        <td class="align-middle py-2"
                                                            style="{{ $checked ? 'color:#1e293b;' : 'text-decoration:line-through; color:#94a3b8;' }}">
                                                            {{ $prow['nombre_producto'] }}
                                                        </td>
                                                        <td class="text-center align-middle py-2 font-weight-bold"
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
                                        <div class="d-flex justify-content-between align-items-center px-3 py-2"
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
                                        <div class="d-flex justify-content-end align-items-center px-3 py-2"
                                             style="border-top:1px solid #e2e8f0; background:#f8fafc;">
                                            <button
                                                type="button"
                                                class="btn btn-success btn-sm px-4"
                                                wire:click="importarDesdeExcel"
                                                style="border-radius:20px; font-size:12px; {{ $seleccionadosExcel === 0 ? 'opacity:.45; cursor:not-allowed;' : 'box-shadow:0 2px 6px rgba(16,185,129,.3);' }}"
                                                {{ $seleccionadosExcel === 0 ? 'disabled' : '' }}
                                            >
                                                <i class="fa fa-check mr-1"></i>
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
                        <div class="row align-items-end">

                            {{-- Título sección --}}
                            <div class="col-12 mb-2">
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-primary step-badge mr-2">3</span>
                                    <h5 class="m-0 text-dark" style="font-size:15px;">Observaciones</h5>
                                </div>
                            </div>

                            {{-- Textarea --}}
                            <div class="col-lg-7">
                                <textarea
                                    wire:model.lazy="observaciones"
                                    class="form-control"
                                    rows="3"
                                    style="border-radius:8px; resize:vertical; font-size:13px; border-color:#dce3f0;"
                                    placeholder="Notas u observaciones adicionales del pedido (opcional)..."
                                ></textarea>
                            </div>

                            {{-- Botón guardar (a la par) --}}
                            <div class="col-lg-5 text-right mt-2 mt-lg-0">
                                <button
                                    type="button"
                                    class="btn btn-primary btn-lg"
                                    wire:click="guardarPedido"
                                    style="border-radius:10px; font-size:15px; padding:12px 32px;
                                           background:linear-gradient(135deg,#1a7efb,#1ab394);
                                           border:none;
                                           box-shadow:0 4px 16px rgba(26,126,251,.35);
                                           transition:transform .15s, box-shadow .15s;"
                                    onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(26,126,251,.45)';"
                                    onmouseout="this.style.transform='';this.style.boxShadow='0 4px 16px rgba(26,126,251,.35)';"
                                    onmousedown="this.style.transform='translateY(0)';"
                                >
                                    <span wire:loading.remove wire:target="guardarPedido">
                                        <i class="fa fa-save mr-1"></i> Registrar Pedido
                                    </span>
                                    <span wire:loading wire:target="guardarPedido">
                                        <i class="fa fa-spinner fa-spin mr-1"></i> Registrando...
                                    </span>
                                </button>
                            </div>

                        </div>

                    </div>{{-- /ibox-content --}}
                </div>{{-- /ibox --}}
            </div>
        </div>

    </div>{{-- /wrapper-content --}}

    {{-- ===== SCROLL TO TOP ON SAVE ===== --}}
    <script>
        window.addEventListener('scroll-top', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>

    {{-- ==================== MODAL: CREAR NUEVO CLIENTE ==================== --}}
    @if ($showModalCliente)
    <div
        class="modal fade show"
        style="display:block; background:rgba(0,0,0,.55); z-index:1050;"
        tabindex="-1"
        role="dialog"
    >
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius:12px; overflow:hidden; border:none; box-shadow:0 10px 40px rgba(0,0,0,.25);">

                {{-- Header --}}
                <div class="modal-header" style="background:linear-gradient(135deg,#1a7efb,#1ab394); border:none;">
                    <h5 class="modal-title text-white m-0">
                        <i class="fa fa-user-plus"></i> &nbsp;Crear Nuevo Cliente
                    </h5>
                    <button type="button" class="close text-white" wire:click="cerrarModalCrearCliente" style="opacity:1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                {{-- Body --}}
                <div class="modal-body p-4">
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
                        <div class="col-12 form-group mb-0">
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
                <div class="modal-footer" style="border-top:1px solid #f0f0f0;">
                    <button type="button" class="btn btn-default" wire:click="cerrarModalCrearCliente">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-primary px-4" wire:click="guardarNuevoCliente">
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
