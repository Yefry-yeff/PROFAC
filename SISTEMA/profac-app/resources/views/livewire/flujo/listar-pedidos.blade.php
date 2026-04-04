<div>
    {{-- ===== ENCABEZADO ===== --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2><i class="fa fa-list-alt text-primary"></i> Historial de Pedidos</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item">Flujo</li>
                <li class="breadcrumb-item active"><strong>Historial de Pedidos</strong></li>
            </ol>
        </div>
        <div class="col-lg-2 d-flex align-items-center justify-content-end">
            <a href="/flujo/pedido" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> &nbsp;Nuevo Pedido
            </a>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- ===== ALERTAS ===== --}}
        @if ($mensajeExito)
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" wire:click="$set('mensajeExito', '')"><span>&times;</span></button>
                <i class="fa fa-check-circle"></i> <strong>¡Éxito!</strong> {{ $mensajeExito }}
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
                    {{-- ── Encabezado del ibox ── --}}
                    <div class="ibox-title"
                         style="background: linear-gradient(135deg,#1a7efb 0%,#1ab394 100%);
                                color:#fff; border-radius:4px 4px 0 0;">
                        <h5 class="m-0" style="color:#fff;">
                            <i class="fa fa-list-alt"></i> &nbsp;Pedidos Registrados
                        </h5>
                    </div>

                    <div class="ibox-content" style="padding:24px;">

                        {{-- ===== FILTROS ===== --}}
                        <div class="row mb-4" style="align-items:flex-end;">

                            {{-- Búsqueda de cliente --}}
                            <div class="col-lg-5 col-md-6 mb-2">
                                <label class="font-weight-bold" style="font-size:12px; color:#555;">
                                    <i class="fa fa-search text-primary"></i> &nbsp;Buscar por cliente
                                </label>
                                <div class="input-group shadow-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0">
                                            <i class="fa fa-user text-muted"></i>
                                        </span>
                                    </div>
                                    <input
                                        type="text"
                                        wire:model.debounce.300ms="busquedaCliente"
                                        class="form-control border-left-0"
                                        placeholder="Nombre o RTN del cliente..."
                                        style="border-radius:0 8px 8px 0;"
                                    >
                                </div>
                            </div>

                            {{-- Filtro estado --}}
                            <div class="col-lg-2 col-md-3 mb-2">
                                <label class="font-weight-bold" style="font-size:12px; color:#555;">
                                    <i class="fa fa-filter text-primary"></i> &nbsp;Estado
                                </label>
                                <select wire:model="filtroEstado" class="form-control shadow-sm"
                                        style="border-radius:8px;">
                                    <option value="">Todos</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="procesado">Procesado</option>
                                    <option value="anulado">Anulado</option>
                                </select>
                            </div>

                            {{-- Filtro fecha --}}
                            <div class="col-lg-3 col-md-3 mb-2">
                                <label class="font-weight-bold" style="font-size:12px; color:#555;">
                                    <i class="fa fa-calendar text-primary"></i> &nbsp;Fecha exacta
                                </label>
                                <input
                                    type="date"
                                    wire:model="filtroFecha"
                                    class="form-control shadow-sm"
                                    style="border-radius:8px;"
                                >
                            </div>

                            {{-- Botón limpiar --}}
                            <div class="col-lg-2 col-md-3 mb-2">
                                <label class="font-weight-bold" style="font-size:12px; color:#555;">&nbsp;</label>
                                <button
                                    type="button"
                                    wire:click="limpiarFiltros"
                                    class="btn btn-default btn-block shadow-sm"
                                    style="border-radius:8px; width:100%;"
                                    title="Limpiar filtros"
                                >
                                    <i class="fa fa-times-circle text-muted"></i> &nbsp;Limpiar
                                </button>
                            </div>
                        </div>

                        {{-- Contador de resultados --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i>
                                {{ $totalPedidos }} pedido(s) encontrado(s)
                            </small>
                            <small class="text-muted">
                                Página {{ $pagina }} de {{ $totalPaginas ?: 1 }}
                            </small>
                        </div>

                        {{-- ===== TABLA ===== --}}
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" style="font-size:14px;">
                                <thead>
                                    <tr style="background:#f5f7fb;">
                                        <th style="width:60px;">#</th>
                                        <th>Cliente</th>
                                        <th style="width:80px; text-align:center;">Productos</th>
                                        <th style="width:110px; text-align:center;">Estado</th>
                                        <th>Registrado por</th>
                                        <th style="width:155px;">Fecha</th>
                                        <th style="width:110px; text-align:center;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pedidos as $pedido)
                                        <tr>
                                            {{-- ID --}}
                                            <td class="font-weight-bold text-primary">
                                                #{{ $pedido->id }}
                                            </td>

                                            {{-- Cliente --}}
                                            <td>
                                                <span class="font-weight-bold">{{ $pedido->cliente }}</span>
                                                @if ($pedido->rtn)
                                                    <br><small class="text-muted">RTN: {{ $pedido->rtn }}</small>
                                                @endif
                                            </td>

                                            {{-- Cantidad de productos --}}
                                            <td class="text-center">
                                                <span class="badge badge-info"
                                                      style="font-size:12px; padding:4px 8px; border-radius:12px;">
                                                    {{ $pedido->total_productos }}
                                                </span>
                                            </td>

                                            {{-- Estado --}}
                                            <td class="text-center">
                                                @if ($pedido->estado === 'pendiente')
                                                    <span class="label label-warning"
                                                          style="font-size:11px; padding:4px 8px; border-radius:12px;">
                                                        <i class="fa fa-clock-o"></i> Pendiente
                                                    </span>
                                                @elseif ($pedido->estado === 'procesado')
                                                    <span class="label label-success"
                                                          style="font-size:11px; padding:4px 8px; border-radius:12px;">
                                                        <i class="fa fa-check"></i> Procesado
                                                    </span>
                                                @elseif ($pedido->estado === 'anulado')
                                                    <span class="label label-danger"
                                                          style="font-size:11px; padding:4px 8px; border-radius:12px;">
                                                        <i class="fa fa-ban"></i> Anulado
                                                    </span>
                                                @else
                                                    <span class="label label-default"
                                                          style="font-size:11px; padding:4px 8px; border-radius:12px;">
                                                        {{ ucfirst($pedido->estado ?? '—') }}
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Registrado por --}}
                                            <td>
                                                <i class="fa fa-user-circle text-muted"></i>
                                                &nbsp;{{ $pedido->registrado_por }}
                                            </td>

                                            {{-- Fecha --}}
                                            <td>
                                                <i class="fa fa-calendar-o text-muted"></i>
                                                &nbsp;{{ \Carbon\Carbon::parse($pedido->created_at)->format('d/m/Y H:i') }}
                                            </td>

                                            {{-- Acciones --}}
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    {{-- Imprimir --}}
                                                    <a
                                                        href="/flujo/pedido/imprimir/{{ $pedido->id }}"
                                                        target="_blank"
                                                        class="btn btn-xs btn-default"
                                                        title="Imprimir pedido"
                                                        style="border-radius:6px 0 0 6px;"
                                                    >
                                                        <i class="fa fa-print text-info"></i>
                                                    </a>
                                                    {{-- Exportar Excel --}}
                                                    <a
                                                        href="/flujo/pedido/exportar/{{ $pedido->id }}"
                                                        class="btn btn-xs btn-default"
                                                        title="Exportar Excel"
                                                    >
                                                        <i class="fa fa-file-excel-o text-success"></i>
                                                    </a>
                                                    {{-- Anular (solo si no está anulado) --}}
                                                    @if ($pedido->estado !== 'anulado')
                                                        <button
                                                            type="button"
                                                            wire:click="confirmarAnular({{ $pedido->id }})"
                                                            class="btn btn-xs btn-default"
                                                            title="Anular pedido"
                                                            style="border-radius:0 6px 6px 0;"
                                                        >
                                                            <i class="fa fa-ban text-danger"></i>
                                                        </button>
                                                    @else
                                                        <span class="btn btn-xs btn-default disabled"
                                                              style="border-radius:0 6px 6px 0; opacity:.4;">
                                                            <i class="fa fa-ban text-danger"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                <i class="fa fa-inbox fa-2x d-block mb-2"></i>
                                                No se encontraron pedidos con los filtros aplicados.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- ===== PAGINACIÓN ===== --}}
                        @if ($totalPaginas > 1)
                            <div class="d-flex justify-content-center mt-3">
                                <nav>
                                    <ul class="pagination pagination-sm">
                                        <li class="page-item {{ $pagina <= 1 ? 'disabled' : '' }}">
                                            <button
                                                class="page-link"
                                                wire:click="paginaAnterior"
                                                {{ $pagina <= 1 ? 'disabled' : '' }}
                                            >&laquo;</button>
                                        </li>

                                        @for ($p = max(1, $pagina - 2); $p <= min($totalPaginas, $pagina + 2); $p++)
                                            <li class="page-item {{ $p == $pagina ? 'active' : '' }}">
                                                <button class="page-link" wire:click="irPagina({{ $p }})">
                                                    {{ $p }}
                                                </button>
                                            </li>
                                        @endfor

                                        <li class="page-item {{ $pagina >= $totalPaginas ? 'disabled' : '' }}">
                                            <button
                                                class="page-link"
                                                wire:click="paginaSiguiente"
                                                {{ $pagina >= $totalPaginas ? 'disabled' : '' }}
                                            >&raquo;</button>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        @endif

                    </div>{{-- /ibox-content --}}
                </div>{{-- /ibox --}}
            </div>
        </div>

    </div>{{-- /wrapper-content --}}

    {{-- ===== MODAL DE CONFIRMACIÓN: ANULAR ===== --}}
    @if ($showModalAnular)
    <div
        class="modal fade show"
        style="display:block; background:rgba(0,0,0,.55); z-index:1050;"
        tabindex="-1"
        role="dialog"
    >
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius:12px; border:none; box-shadow:0 10px 40px rgba(0,0,0,.25);">
                <div class="modal-header" style="background:#e74c3c; border:none;">
                    <h5 class="modal-title text-white m-0">
                        <i class="fa fa-ban"></i> &nbsp;Anular Pedido
                    </h5>
                    <button type="button" class="close text-white" wire:click="cancelarAnular" style="opacity:1;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 text-center">
                    <i class="fa fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                    <p class="mb-0">
                        ¿Está seguro que desea anular el pedido <strong>#{{ $pedidoAnularId }}</strong>?
                        <br><small class="text-muted">Esta acción no puede deshacerse.</small>
                    </p>
                </div>
                <div class="modal-footer" style="border:none; justify-content:center;">
                    <button type="button" wire:click="cancelarAnular" class="btn btn-default">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="button" wire:click="anularPedido" class="btn btn-danger">
                        <i class="fa fa-ban"></i> &nbsp;Sí, Anular
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
