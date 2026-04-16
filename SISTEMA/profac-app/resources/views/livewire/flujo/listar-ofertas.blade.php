<div>
    {{-- ===== ESTILOS ===== --}}
    <style>
        .oferta-badge {
            display:inline-block; border-radius:20px; font-size:11px;
            font-weight:700; padding:3px 10px; white-space:nowrap;
        }
    </style>

    {{-- ===== ENCABEZADO ===== --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2><i class="fa fa-tag text-warning"></i> Historial de Ofertas</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item">Flujo</li>
                <li class="breadcrumb-item active"><strong>Historial de Ofertas</strong></li>
            </ol>
        </div>
        <div class="col-lg-2 d-flex align-items-center justify-content-end">
            <a href="{{ route('flujo.pedidos') }}" class="btn btn-default btn-sm mr-2">
                <i class="fa fa-list-alt"></i> Pedidos
            </a>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- ===== ALERTAS ===== --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" style="border-radius:10px;">
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                <i class="fa fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox" style="border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.06);">
                    <div class="ibox-title py-3 px-4"
                         style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%); border:none;">
                        <h5 class="m-0" style="color:#fff; font-size:15px;">
                            <i class="fa fa-tag mr-2"></i>Ofertas de Pedidos Registradas
                        </h5>
                    </div>

                    <div class="ibox-content" style="padding:24px;">

                        {{-- ===== FILTROS ===== --}}
                        <div class="row mb-4" style="align-items:flex-end;">

                            {{-- Búsqueda cliente --}}
                            <div class="col-lg-4 col-md-6 mb-2">
                                <label class="font-weight-bold" style="font-size:12px; color:#555;">
                                    <i class="fa fa-search text-primary"></i> Buscar por cliente
                                </label>
                                <div class="input-group shadow-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0">
                                            <i class="fa fa-user text-muted"></i>
                                        </span>
                                    </div>
                                    <input type="text"
                                           wire:model.debounce.300ms="busquedaCliente"
                                           class="form-control border-left-0"
                                           placeholder="Nombre o RTN..."
                                           style="border-radius:0 8px 8px 0;">
                                </div>
                            </div>

                            {{-- Filtro por pedido --}}
                            <div class="col-lg-2 col-md-3 mb-2">
                                <label class="font-weight-bold" style="font-size:12px; color:#555;">
                                    <i class="fa fa-hashtag text-primary"></i> Pedido #
                                </label>
                                <input type="number" wire:model.debounce.400ms="filtroPedido"
                                       class="form-control shadow-sm" placeholder="ID pedido..."
                                       style="border-radius:8px;">
                            </div>

                            {{-- Filtro fecha --}}
                            <div class="col-lg-3 col-md-3 mb-2">
                                <label class="font-weight-bold" style="font-size:12px; color:#555;">
                                    <i class="fa fa-calendar text-primary"></i> Fecha exacta
                                </label>
                                <input type="date" wire:model="filtroFecha"
                                       class="form-control shadow-sm" style="border-radius:8px;">
                            </div>

                            {{-- Limpiar --}}
                            <div class="col-lg-3 col-md-3 mb-2">
                                <label class="font-weight-bold" style="font-size:12px;">&nbsp;</label>
                                <button type="button" wire:click="limpiarFiltros"
                                        class="btn btn-default btn-block shadow-sm"
                                        style="border-radius:8px;">
                                    <i class="fa fa-times-circle text-muted"></i> Limpiar
                                </button>
                            </div>
                        </div>

                        {{-- Contador --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i>
                                {{ $totalOfertas }} oferta(s) encontrada(s)
                            </small>
                            <small class="text-muted">
                                Página {{ $pagina }} de {{ $totalPaginas ?: 1 }}
                            </small>
                        </div>

                        {{-- ===== TABLA ===== --}}
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" style="font-size:13px;">
                                <thead>
                                    <tr style="background:#f5f7fb;">
                                        <th style="width:55px;">#Oferta</th>
                                        <th style="width:70px;">Pedido</th>
                                        <th>Cliente</th>
                                        <th style="width:75px; text-align:center;">Prods.</th>
                                        <th style="width:110px; text-align:right;">Sub Total</th>
                                        <th style="width:90px; text-align:right;">ISV</th>
                                        <th style="width:110px; text-align:right;">Total</th>
                                        <th style="width:90px; text-align:center;">Desc %</th>
                                        <th>Registrado por</th>
                                        <th style="width:140px;">Fecha</th>
                                        <th style="width:140px; text-align:center;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($ofertas as $oferta)
                                        <tr>
                                            {{-- ID oferta --}}
                                            <td>
                                                <span class="font-weight-bold text-warning">#{{ $oferta->id }}</span>
                                            </td>

                                            {{-- Pedido link --}}
                                            <td>
                                                <a href="{{ route('flujo.pedidos') }}"
                                                   title="Ver pedido">
                                                    <span class="oferta-badge"
                                                          style="background:#e8f0fe; color:#1a7efb;">
                                                        #{{ $oferta->pedido_id ?? '—' }}
                                                    </span>
                                                </a>
                                            </td>

                                            {{-- Cliente --}}
                                            <td>
                                                <span class="font-weight-bold">{{ $oferta->nombre_cliente }}</span>
                                                @if($oferta->RTN)
                                                    <br><small class="text-muted">RTN: {{ $oferta->RTN }}</small>
                                                @endif
                                            </td>

                                            {{-- Productos --}}
                                            <td class="text-center">
                                                <span class="badge badge-info"
                                                      style="font-size:12px; padding:4px 8px; border-radius:12px;">
                                                    {{ $oferta->total_productos }}
                                                </span>
                                            </td>

                                            {{-- Subtotal --}}
                                            <td class="text-right">
                                                <span class="text-muted" style="font-size:11px;">L.</span>
                                                {{ $oferta->sub_total }}
                                            </td>

                                            {{-- ISV --}}
                                            <td class="text-right">
                                                <span class="text-muted" style="font-size:11px;">L.</span>
                                                {{ $oferta->isv }}
                                            </td>

                                            {{-- Total --}}
                                            <td class="text-right">
                                                <strong class="text-success">
                                                    <span style="font-size:11px;">L.</span>
                                                    {{ $oferta->total }}
                                                </strong>
                                            </td>

                                            {{-- Descuento --}}
                                            <td class="text-center">
                                                @if($oferta->porc_descuento > 0)
                                                    <span class="label label-warning"
                                                          style="font-size:11px; padding:3px 8px; border-radius:10px;">
                                                        {{ $oferta->porc_descuento }}%
                                                    </span>
                                                @else
                                                    <span class="text-muted" style="font-size:11px;">—</span>
                                                @endif
                                            </td>

                                            {{-- Registrado por --}}
                                            <td>
                                                <i class="fa fa-user-circle text-muted"></i>
                                                &nbsp;{{ $oferta->registrado_por }}
                                            </td>

                                            {{-- Fecha --}}
                                            <td>
                                                <i class="fa fa-calendar-o text-muted"></i>
                                                &nbsp;{{ \Carbon\Carbon::parse($oferta->created_at)->format('d/m/Y H:i') }}
                                            </td>

                                            {{-- Acciones --}}
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    {{-- Imprimir --}}
                                                    <a href="/oferta/imprimir/{{ $oferta->id }}"
                                                       target="_blank"
                                                       class="btn btn-xs btn-default"
                                                       title="Imprimir oferta"
                                                       style="border-radius:6px 0 0 6px;">
                                                        <i class="fa fa-print text-info"></i>
                                                    </a>
                                                    {{-- Ver pedido relacionado --}}
                                                    @if($oferta->pedido_id)
                                                        <a href="/flujo/pedido/editar/{{ $oferta->pedido_id }}"
                                                           class="btn btn-xs btn-warning"
                                                           title="Ver pedido #{{ $oferta->pedido_id }}"
                                                           style="color:#fff; border-radius:0 6px 6px 0;">
                                                            <i class="fa fa-shopping-cart"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-5 text-muted">
                                                <i class="fa fa-tag fa-2x d-block mb-2" style="opacity:.3;"></i>
                                                No se encontraron ofertas con los filtros aplicados.
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
                                            <button class="page-link" wire:click="paginaAnterior"
                                                    {{ $pagina <= 1 ? 'disabled' : '' }}>&laquo;</button>
                                        </li>
                                        @for ($p = max(1, $pagina - 2); $p <= min($totalPaginas, $pagina + 2); $p++)
                                            <li class="page-item {{ $p == $pagina ? 'active' : '' }}">
                                                <button class="page-link" wire:click="irPagina({{ $p }})">{{ $p }}</button>
                                            </li>
                                        @endfor
                                        <li class="page-item {{ $pagina >= $totalPaginas ? 'disabled' : '' }}">
                                            <button class="page-link" wire:click="paginaSiguiente"
                                                    {{ $pagina >= $totalPaginas ? 'disabled' : '' }}>&raquo;</button>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        @endif

                    </div>{{-- /ibox-content --}}
                </div>{{-- /ibox --}}
            </div>
        </div>
    </div>
</div>
