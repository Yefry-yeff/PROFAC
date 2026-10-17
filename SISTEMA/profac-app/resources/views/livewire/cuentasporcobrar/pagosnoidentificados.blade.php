<div class="pni-page">
    @push('styles')
        <style>
            .pni-page { --pni-grad:linear-gradient(135deg,#f39c12 0%,#e05a00 100%); }
            .pni-page .pni-card { border:1px solid #e8d5bf; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.10); background:#fff; }
            .pni-page .pni-header { background:var(--pni-grad); padding:12px 20px; border-radius:8px 8px 0 0; display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; }
            .pni-page .pni-header h5 { margin:0; color:#fff; font-size:.85rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; }
            .pni-page .pni-actions { display:flex; flex-wrap:wrap; gap:8px; }
            .pni-page .pni-action { background:rgba(255,255,255,.18); color:#fff; border:1.5px solid rgba(255,255,255,.5); border-radius:5px; font-weight:600; font-size:.78rem; padding:5px 14px; cursor:pointer; }
            .pni-page .pni-action:hover { background:rgba(255,255,255,.30); }
            .pni-page .pni-action:disabled { opacity:.5; cursor:not-allowed; }
            .pni-page .pni-stats { display:flex; gap:10px; flex-wrap:wrap; padding:10px 20px; background:#fdfaf5; border-bottom:1px solid #e8d5bf; }
            .pni-page .pni-stat { display:flex; align-items:center; gap:7px; background:#fdf6ee; border:1px solid #e8d5bf; border-radius:20px; padding:4px 14px 4px 10px; font-size:.78rem; color:#555; }
            .pni-page .pni-stat strong { color:#e67e22; font-size:.9rem; }
            .pni-page .pni-stat.green { background:#f0fdf4; border-color:#bbf7d0; }
            .pni-page .pni-stat.green strong { color:#1a7a4e; }
            .pni-page .pni-body { padding:16px 20px; }
            .pni-page .pni-filter-bar { display:flex; flex-wrap:wrap; gap:6px; padding:8px 16px; background:#fdfaf5; border-bottom:1px solid #e8d5bf; }
            .pni-page .pni-filter-badge { display:inline-flex; align-items:center; gap:5px; background:#fff8ee; border:1px solid #f2d49a; border-radius:12px; padding:2px 10px; font-size:.75rem; color:#7d3f00; }
            .pni-page .pni-filter-badge button { border:0; background:none; color:#c0622a; font-weight:700; cursor:pointer; }
            .pni-page .pni-selection { display:flex; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:14px; font-size:.78rem; }
            .pni-page #pni-table thead th { background:#fdf4e7; color:#7d3f00; font-size:.72rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; border-bottom:2px solid #f2d49a; white-space:nowrap; padding:8px 10px; vertical-align:middle; }
            .pni-page #pni-table thead button { color:inherit; font:inherit; text-transform:inherit; }
            .pni-page #pni-table tbody td { font-size:.83rem; vertical-align:middle; padding:8px 10px; }
            .pni-page #pni-table tbody tr:hover>td { background:#fffcf5; }
            .pni-page .modal-content { border-radius:8px; }
            .pni-page .modal { z-index:5001 !important; }
            .pni-page .modal-backdrop { z-index:5000 !important; }
            .pni-page .modal-header { background:var(--pni-grad); color:#fff; border-radius:8px 8px 0 0; padding:14px 20px; }
            .pni-page .modal-title { color:#fff; font-size:.95rem; font-weight:700; }
            .pni-page .modal-header .close { color:#fff; opacity:.8; text-shadow:none; }
            .pni-page .pni-filter-modal .modal-body { background:#fdfaf6; padding:18px 20px 8px; }
            .pni-page .pni-filter-modal .modal-footer { background:#f8f4ef; border-top:1px solid #ead9c8; }
            .pni-page .pni-section { font-size:.68rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:#e67e22; border-bottom:2px solid #fdebd0; padding-bottom:5px; margin:6px 0 14px; }
            .pni-page .pni-filter-grid { background:#fff; border:1px solid #ead9c8; border-radius:7px; padding:14px 16px 6px; margin-bottom:14px; }
            .pni-page .pni-filter-modal label { font-size:.78rem; font-weight:600; color:#555; }
            .pni-page .pni-filter-modal .form-control { border-color:#ddd; border-radius:5px; font-size:.82rem; }
            .pni-page .pni-filter-modal .form-control:focus { border-color:#e67e22; box-shadow:0 0 0 .15rem rgba(230,126,34,.18); }
            .pni-page .pni-search { background:var(--pni-grad); color:#fff; border:none; font-weight:600; padding:6px 20px; border-radius:5px; }
            .pni-page .pni-editor { width:calc(100% - 32px) !important; max-width:960px !important; margin:30px auto; }
            .pni-page .pni-editor .modal-content { border:0; border-radius:14px; box-shadow:0 24px 80px rgba(36,24,9,.28); overflow:hidden; }
            .pni-page .pni-editor .modal-header { padding:22px 28px; border:0; border-radius:0; }
            .pni-page .pni-editor .modal-body { padding:24px 28px; background:#faf9f6; }
            .pni-page .pni-editor .modal-footer { padding:18px 28px; background:#fff; border-top:1px solid #ece7df; }
            .pni-page .pni-editor label { font-weight:600; color:#46505c; font-size:13px; margin-bottom:8px; }
            .pni-page .pni-editor .form-control { height:44px; border:1px solid #dce1e7; border-radius:7px; font-size:14px; padding:10px 12px; }
            .pni-page .pni-editor textarea.form-control { height:auto; }
            .pni-page .pni-editor select.form-control { height:44px !important; padding:0 32px 0 12px !important; line-height:normal; }
            .pni-page .pni-editor .form-control:focus { border-color:#e99936; box-shadow:0 0 0 3px rgba(243,156,18,.12); }
            .pni-page .pni-editor small { display:block; margin-top:7px; color:#7b8490; }
            .pni-page .pni-panel { background:#fff; border:1px solid #e8e5df; border-radius:10px; padding:20px 22px 4px; margin-bottom:18px; }
            .pni-page .pni-intro { color:#737d88; margin:0 0 22px; line-height:1.6; }
            .pni-page .pni-money { display:flex; align-items:center; border:1px solid #dce1e7; border-radius:7px; background:#fff; overflow:hidden; }
            .pni-page .pni-money span { padding:0 14px; color:#a85d12; font-weight:700; border-right:1px solid #e8e5df; }
            .pni-page .pni-money .form-control { border:0; border-radius:0; text-align:right; font-weight:600; font-size:18px; min-width:0; }
            .pni-page .pni-editor .btn { padding:10px 20px; border-radius:7px; font-weight:600; }
            .pni-page .pni-editor .select2-container { width:100% !important; }
            .pni-page .pni-editor .select2-selection--single { height:44px; border:1px solid #dce1e7; border-radius:7px; }
            .pni-page .pni-editor .select2-selection__rendered { line-height:42px; padding-left:12px; padding-right:35px; color:#46505c; font-size:14px; }
            .pni-page .pni-editor .select2-selection__arrow { height:42px; right:8px; }
            .pni-page .select2-dropdown { z-index:5010 !important; border-color:#e8c291; border-radius:7px; box-shadow:0 10px 28px rgba(36,24,9,.12); }
            .pni-page .select2-search__field { border:1px solid #dce1e7 !important; padding:9px !important; border-radius:5px; }
            .pni-page .select2-results__option { padding:9px 12px; font-size:13px; }
            .pni-page .pni-detail-title { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:18px; }
            .pni-page .pni-detail-title h3 { margin:0 0 6px; color:#344050; font-size:20px; font-weight:700; }
            .pni-page .pni-status { background:#fff1da; color:#95580b; border:1px solid #f2d49a; border-radius:20px; padding:7px 14px; font-weight:600; }
            .pni-page .pni-amounts { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin-bottom:20px; }
            .pni-page .pni-amount-card { padding:16px; border:1px solid #e8e5df; border-radius:9px; background:#fff; }
            .pni-page .pni-amount-card span { display:block; color:#737d88; margin-bottom:8px; font-size:12px; }
            .pni-page .pni-amount-card strong { color:#344050; font-size:20px; overflow-wrap:anywhere; }
            .pni-page .pni-amount-card.available { background:#effaf3; border-color:#c6e6d0; }
            .pni-page .pni-amount-card.available strong { color:#227b4d; }
            .pni-page .pni-facts { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px 24px; margin-bottom:18px; }
            .pni-page .pni-facts dt { font-size:12px; color:#737d88; margin-bottom:4px; font-weight:500; }
            .pni-page .pni-facts dd { color:#344050; font-weight:600; margin:0; overflow-wrap:anywhere; }
            .pni-page .pni-history-card { padding:18px; border:1px solid #e8e5df; border-radius:10px; background:#fff; margin-bottom:14px; }
            .pni-page .pni-history-heading { display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; padding-bottom:14px; margin-bottom:14px; border-bottom:1px solid #eee; }
            .pni-page .pni-invoice-link { display:inline-flex; align-items:center; gap:8px; border:0; border-radius:6px; padding:8px 12px; color:#a75c0d; background:#fff3e1; font-weight:700; cursor:pointer; text-align:left; overflow-wrap:anywhere; max-width:100%; }
            .pni-page .pni-invoice-link:hover { background:#ffe4b9; }
            .pni-page .pni-history-card .pni-facts { margin-bottom:0; }
            .pni-page .pni-empty { text-align:center; padding:26px 18px; color:#737d88; }
            .pni-page .pni-empty i { font-size:28px; color:#d1b28c; margin-bottom:12px; }
            .pni-page .pni-note { white-space:pre-wrap; overflow-wrap:anywhere; }
            .pni-page .pni-application-table { font-size:13px; margin-bottom:16px; }
            .pni-page .pni-application-table th { background:#faf5ed; color:#896237; border-top:0; font-size:11px; text-transform:uppercase; letter-spacing:.03em; }
            .pni-page .pni-application-table td { vertical-align:middle; color:#46505c; }
            .pni-page .pni-application-table .pni-numeric { text-align:right; white-space:nowrap; }
            .pni-page .pni-invoice-summary { background:#f5f8fc; border:1px solid #dfe7f1; border-radius:8px; padding:16px; margin-bottom:18px; }
            .pni-page .pni-invoice-summary .pni-facts { margin:12px 0 0; }
            .pni-page .pni-compact { max-width:1100px !important; margin:12px auto; }
            .pni-page .pni-compact .modal-content { max-height:calc(100dvh - 24px); }
            .pni-page .pni-compact .modal-header { padding:12px 20px; flex-shrink:0; }
            .pni-page .pni-compact .modal-body { padding:12px 16px; overflow-y:auto; }
            .pni-page .pni-compact .modal-footer { padding:10px 16px; flex-shrink:0; }
            .pni-page .pni-compact .pni-intro { margin-bottom:10px; font-size:12px; }
            .pni-page .pni-compact .pni-amounts { gap:10px; margin-bottom:10px; }
            .pni-page .pni-compact .pni-amount-card { padding:9px 12px; }
            .pni-page .pni-compact .pni-amount-card span { margin-bottom:3px; }
            .pni-page .pni-compact .pni-amount-card strong { font-size:18px; }
            .pni-page .pni-compact .pni-panel { padding:10px 12px 0; margin-bottom:10px; min-width:0; }
            .pni-page .pni-compact .pni-section { margin:0 0 9px; padding-bottom:5px; }
            .pni-page .pni-compact .form-group { margin-bottom:10px; }
            .pni-page .pni-compact textarea.form-control { padding:6px 10px; line-height:1.3; }
            .pni-page .pni-compact label { margin-bottom:4px; font-size:12px; }
            .pni-page .pni-compact small { margin-top:3px; font-size:11px; }
            .pni-page .pni-compact .pni-application-table { margin-bottom:8px; font-size:12px; }
            .pni-page .pni-compact .pni-application-table td, .pni-page .pni-compact .pni-application-table th { padding:5px 7px; }
            .pni-page .pni-compact .table-responsive { max-height:130px; overflow:auto; }
            .pni-page .pni-compact .pni-invoice-summary { padding:10px 12px; margin-bottom:10px; }
            .pni-page .pni-compact .pni-facts { gap:8px 12px; margin-top:8px; font-size:12px; }
            .pni-page .pni-compact .pni-facts dt { margin-bottom:2px; font-size:11px; }
            .pni-page .pni-compact .pni-empty { padding:12px; }
            .pni-page .pni-compact .alert { padding:8px 12px; font-size:12px; }
            .pni-page .pni-application-grid { display:grid; grid-template-columns:minmax(0,1.2fr) minmax(0,1fr); gap:12px; }
            .pni-page .pni-application-grid .pni-panel { margin-bottom:0; }
            @media(max-width:767px) {
                .pni-page .pni-application-grid { grid-template-columns:1fr; }
            }
            @media(max-width:575px) {
                .pni-page .pni-editor .modal-body, .pni-page .pni-editor .modal-header, .pni-page .pni-editor .modal-footer { padding:18px; }
                .pni-page .pni-panel { padding:16px 14px 0; }
                .pni-page .pni-amounts, .pni-page .pni-facts { grid-template-columns:1fr; }
            }
        </style>
    @endpush
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-12">
            <h2><i class="fa fa-bank mr-2" style="color:#e67e22" aria-hidden="true"></i>Pagos No Identificados</h2>
            <ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li><li class="breadcrumb-item active">Cuentas por cobrar</li></ol>
        </div>
    </div>
    <div class="wrapper wrapper-content animated fadeInRight">
        @if(session()->has('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <div class="pni-card">
            <div class="pni-header">
                <h5><i class="fa fa-bank mr-1" aria-hidden="true"></i> Pagos No Identificados</h5>
                <div class="pni-actions">
                    <button class="pni-action" wire:click="abrirNuevo"><i class="fa fa-plus mr-1" aria-hidden="true"></i>Nuevo Pago No Identificado</button>
                    <button class="pni-action" wire:click="abrirAplicacion" wire:loading.attr="disabled" @disabled(!$seleccionados)><i class="fa fa-check mr-1" aria-hidden="true"></i>Aplicar Pago ({{ count($seleccionados) }})</button>
                    <button class="pni-action" wire:click="abrirFiltros"><i class="fa fa-filter mr-1" aria-hidden="true"></i>Filtros</button>
                </div>
            </div>
            <div class="pni-stats">
                <span class="pni-stat"><i class="fa fa-list" aria-hidden="true"></i><strong>{{ $resumen->cantidad }}</strong> Pagos</span>
                <span class="pni-stat"><i class="fa fa-bank" aria-hidden="true"></i><strong>L {{ number_format($resumen->depositos, 2) }}</strong> Depositado</span>
                <span class="pni-stat green"><i class="fa fa-money" aria-hidden="true"></i><strong>L {{ number_format($resumen->disponible, 2) }}</strong> Disponible</span>
            </div>
            @if(array_filter($filtros, fn ($v) => $v !== ''))
                <div class="pni-filter-bar" aria-label="Filtros activos">
                    @foreach(['desde' => 'Desde', 'hasta' => 'Hasta', 'cliente' => 'Cliente', 'banco' => 'Banco', 'referencia' => 'Referencia bancaria', 'factura' => 'Factura', 'estado' => 'Estado', 'buscar' => 'Buscar'] as $campo => $etiqueta)
                        @if($filtros[$campo] !== '')
                            @php
                                $valor = match($campo) {
                                    'cliente' => $clientesFiltro->firstWhere('id', (int) $filtros[$campo])->nombre ?? $filtros[$campo],
                                    'banco' => $bancos->firstWhere('id', (int) $filtros[$campo])->nombre ?? $filtros[$campo],
                                    'estado' => \App\Services\PagosNoIdentificadosService::ESTADOS[$filtros[$campo]] ?? $filtros[$campo],
                                    default => $filtros[$campo],
                                };
                            @endphp
                            <span class="pni-filter-badge">{{ $etiqueta }}: <strong>{{ $valor }}</strong><button wire:click="quitarFiltro('{{ $campo }}')" aria-label="Quitar filtro {{ $etiqueta }}">&times;</button></span>
                        @endif
                    @endforeach
                </div>
            @endif
            <div class="pni-body">
                <div class="pni-selection"><button class="btn btn-outline-secondary btn-sm" wire:click="seleccionarPagina">Seleccionar disponibles de esta página</button> <button class="btn btn-default btn-sm" wire:click="$set('seleccionados', [])">Quitar selección</button> <strong>{{ count($seleccionados) }} seleccionados · Disponible L {{ number_format($totalSeleccionado, 2) }}</strong></div>
                <div class="table-responsive">
                    <table id="pni-table" class="table table-striped table-bordered">
                        <thead><tr><th>Seleccionar</th><th><button class="btn btn-link btn-sm p-0" wire:click="ordenar('p.id')">ID ↕</button></th><th>Cliente</th><th><button class="btn btn-link btn-sm p-0" wire:click="ordenar('p.fecha_pago')">Fecha de pago ↕</button></th><th>Banco / cuenta</th><th><button class="btn btn-link btn-sm p-0" wire:click="ordenar('p.monto_original')">Depósito ↕</button></th><th>Aplicado</th><th><button class="btn btn-link btn-sm p-0" wire:click="ordenar('p.saldo_disponible')">Disponible ↕</button></th><th>Estado</th><th><button class="btn btn-link btn-sm p-0" wire:click="ordenar('p.created_at')">Creación ↕</button></th><th>Registrado por</th><th>Acciones</th></tr></thead>
                        <tbody>
                            @forelse($pagos as $pago)
                                <tr wire:key="pni-{{ $pago->id }}">
                                    <td><input type="checkbox" aria-label="Seleccionar pago {{ $pago->id }}" wire:model.live="seleccionados" value="{{ $pago->id }}" @disabled($pago->estado === 'aplicado')></td>
                                    <td><button class="btn btn-link p-0" wire:click="verDetalle({{ $pago->id }})">#{{ $pago->id }}</button></td>
                                    <td>{{ $pago->cliente ?: 'Sin identificar' }}</td><td class="text-nowrap">{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}<small class="d-block text-muted">{{ $pago->hora_pago ? substr($pago->hora_pago, 0, 5) : 'Hora no registrada' }}</small></td>
                                    <td>{{ $pago->banco }}<br><small>{{ $pago->cuenta_bancaria }}</small>@if($pago->referencia_bancaria)<small class="d-block text-muted">Ref. {{ $pago->referencia_bancaria }}</small>@endif</td>
                                    <td class="text-nowrap">L {{ number_format($pago->monto_original, 2) }}</td>
                                    <td class="text-nowrap">L {{ number_format($pago->monto_original - $pago->saldo_disponible, 2) }}</td>
                                    <td class="text-nowrap">L {{ number_format($pago->saldo_disponible, 2) }}</td>
                                    <td><span class="badge {{ $pago->estado === 'aplicado' ? 'badge-success' : 'badge-warning' }}">{{ \App\Services\PagosNoIdentificadosService::ESTADOS[$pago->estado] }}</span></td>
                                    <td>{{ $pago->created_at }}</td><td>{{ $pago->creador }}</td>
                                    <td><button class="btn btn-outline-info btn-sm" wire:click="verDetalle({{ $pago->id }})">Ver aplicaciones</button></td>
                                </tr>
                            @empty
                                <tr><td colspan="12" class="text-center">No se encontraron pagos con los filtros seleccionados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $pagos->links() }}
                <div wire:loading role="status">Procesando, espere...</div>
            </div>
        </div>
    </div>

    @if($modal)
        @teleport('body')
        <div class="pni-page">
        <div class="modal-backdrop show"></div>
        <div class="modal show d-block" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="pni-modal-title" style="overflow-y:auto;" wire:key="modal-{{ $modal }}" x-init="$el.focus()" x-on:keydown.escape.window="$wire.cerrar()">
            <div class="modal-dialog {{ $modal === 'filtros' ? 'modal-lg modal-dialog-centered pni-filter-modal' : 'modal-xl' }} {{ in_array($modal, ['nuevo', 'aplicar', 'detalle']) ? 'pni-editor' : '' }} {{ $modal === 'aplicar' ? 'pni-compact' : '' }}" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="pni-modal-title" class="modal-title"><i class="fa {{ $modal === 'filtros' ? 'fa-filter' : 'fa-bank' }} mr-2" aria-hidden="true"></i>{{ ['nuevo' => 'Nuevo Pago No Identificado', 'aplicar' => 'Aplicar Pago', 'detalle' => 'Detalle y aplicaciones del pago', 'filtros' => 'Filtros de búsqueda'][$modal] }}</h4>
                        <button class="close" aria-label="Cerrar" wire:click="cerrar" wire:loading.attr="disabled">&times;</button>
                    </div>
                    <div class="modal-body">
                        @if($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

                        @if($modal === 'filtros')
                            <form wire:submit="aplicarFiltros" id="pni-filtros">
                                <p class="pni-section"><i class="fa fa-calendar mr-1" aria-hidden="true"></i>Rango de fechas de pago</p>
                                <div class="pni-filter-grid"><div class="row">
                                    <div class="col-md-6 form-group"><label for="pni-desde">Fecha inicial</label><input id="pni-desde" type="date" class="form-control" wire:model="filtrosEdicion.desde"></div>
                                    <div class="col-md-6 form-group"><label for="pni-hasta">Fecha final</label><input id="pni-hasta" type="date" class="form-control" wire:model="filtrosEdicion.hasta"></div>
                                </div></div>
                                <p class="pni-section"><i class="fa fa-search mr-1" aria-hidden="true"></i>Criterios de búsqueda</p>
                                <div class="pni-filter-grid"><div class="row">
                                    <div class="col-md-6 form-group"><label for="pni-cliente">Cliente</label><input aria-label="Buscar cliente en filtros" class="form-control mb-1" placeholder="Buscar nombre o código" wire:model.live.debounce.400ms="buscarClienteFiltro"><select id="pni-cliente" class="form-control" wire:model="filtrosEdicion.cliente"><option value="">Todos</option>@foreach($clientesFiltro as $cliente)<option value="{{ $cliente->id }}">{{ $cliente->id }} - {{ $cliente->nombre }}</option>@endforeach</select></div>
                                    <div class="col-md-6 form-group"><label for="pni-banco">Banco</label><select id="pni-banco" class="form-control" wire:model="filtrosEdicion.banco"><option value="">Todos</option>@foreach($bancos as $banco)<option value="{{ $banco->id }}">{{ $banco->nombre }} · {{ $banco->cuenta }}</option>@endforeach</select></div>
                                    <div class="col-md-6 form-group"><label for="pni-referencia">Referencia bancaria</label><input id="pni-referencia" maxlength="500" class="form-control" wire:model="filtrosEdicion.referencia" placeholder="Número o texto de referencia"></div>
                                    <div class="col-md-6 form-group"><label for="pni-factura">N.º de factura</label><input id="pni-factura" class="form-control" wire:model="filtrosEdicion.factura" placeholder="CAI o secuencia"></div>
                                    <div class="col-md-6 form-group"><label for="pni-estado">Estado</label><select id="pni-estado" class="form-control" wire:model="filtrosEdicion.estado"><option value="">Todos</option>@foreach(\App\Services\PagosNoIdentificadosService::ESTADOS as $valor => $texto)<option value="{{ $valor }}">{{ $texto }}</option>@endforeach</select></div>
                                    <div class="col-md-6 form-group"><label for="pni-buscar">Buscar</label><input id="pni-buscar" class="form-control" wire:model="filtrosEdicion.buscar" placeholder="ID, cliente, referencia u observaciones"></div>
                                </div></div>
                                <p class="text-muted small">El filtro de factura consulta facturas ya vinculadas. Buscar limpia la selección; puede seleccionar hasta 100 pagos entre páginas.</p>
                            </form>
                        @elseif($modal === 'nuevo')
                            <form wire:submit="guardar" id="pni-registro">
                                <p class="pni-intro">Registre los datos del depósito recibido. Puede identificar al cliente ahora o asociarlo más adelante al aplicar el pago.</p>
                                <div class="pni-panel">
                                <p class="pni-section"><i class="fa fa-user mr-1" aria-hidden="true"></i>Identificación del depósito</p>
                                <div class="form-group">
                                        <label for="pni-cliente-deposito">Cliente <span class="text-muted font-weight-normal">(opcional)</span></label>
                                        <div wire:ignore x-data="pniSelect('nuevo.cliente_id', @js($nuevo['cliente_id']), false)">
                                            <select id="pni-cliente-deposito" x-ref="select" class="form-control" aria-label="Cliente del depósito" data-placeholder="Sin identificar · Buscar cliente por nombre o código"><option value=""></option>@foreach($clientes as $cliente)<option value="{{ $cliente->id }}">{{ $cliente->id }} - {{ $cliente->nombre }}</option>@endforeach</select>
                                        </div>
                                        <small>Todos los clientes disponibles. Escriba el nombre o código para buscar.</small>
                                    </div>
                                <div class="row">
                                    <div class="col-md-5 form-group"><label for="pni-monto">Total depósito *</label><div class="pni-money" x-data="pniMoney($wire.entangle('nuevo.monto'))"><span aria-hidden="true">L</span><input id="pni-monto" type="text" inputmode="decimal" required class="form-control" aria-label="Total depósito en lempiras" placeholder="0.00" x-ref="amount" x-on:input="input()" x-on:blur="format()"></div><small>Lempiras (HNL) · Ejemplo: L 12,500.00</small></div>
                                    <div class="col-md-4 form-group"><label for="pni-fecha">Fecha de pago *</label><input id="pni-fecha" type="date" max="{{ now()->toDateString() }}" required class="form-control" wire:model="nuevo.fecha_pago"></div>
                                    <div class="col-md-3 form-group"><label for="pni-hora">Hora de pago <span class="text-muted font-weight-normal">(opcional)</span></label><input id="pni-hora" type="time" class="form-control" wire:model="nuevo.hora_pago"><small>Hora local del depósito, si se conoce.</small></div>
                                </div></div>
                                <div class="pni-panel">
                                <p class="pni-section"><i class="fa fa-bank mr-1" aria-hidden="true"></i>Cuenta receptora</p>
                                <div class="row">
                                    <div class="col-md-6 form-group"><label for="pni-nuevo-banco">Banco *</label><select id="pni-nuevo-banco" required class="form-control" wire:model.live.change="nuevo.banco_id"><option value="">Seleccione</option>@foreach($bancos as $banco)<option value="{{ $banco->id }}">{{ $banco->nombre }}</option>@endforeach</select></div>
                                    <div class="col-md-6 form-group"><label for="pni-cuenta">Cuenta bancaria *</label><input id="pni-cuenta" class="form-control" wire:model="nuevo.cuenta_bancaria" readonly required><small>Cuenta receptora del catálogo bancario.</small></div>
                                    <div class="col-12 form-group"><label for="pni-referencia-bancaria">Referencia bancaria <span class="text-muted font-weight-normal">(opcional)</span></label><input id="pni-referencia-bancaria" maxlength="500" class="form-control" wire:model="nuevo.referencia_bancaria" placeholder="Ejemplo: TRX-2026/001245"><small>Admite letras, números, espacios, guiones y otros caracteres de referencia.</small></div>
                                    <div class="col-12 form-group"><label for="pni-observaciones">Observaciones</label><textarea id="pni-observaciones" maxlength="2000" rows="3" class="form-control" wire:model="nuevo.observaciones"></textarea></div>
                                </div>
                                </div>
                            </form>
                        @elseif($modal === 'aplicar')
                            <p class="pni-intro">Seleccione la factura que desea abonar y revise cómo se distribuirá el importe antes de confirmar.</p>
                            <div class="pni-amounts">
                                <div class="pni-amount-card"><span>Depósitos seleccionados</span><strong>{{ count($origenes) }}</strong></div>
                                <div class="pni-amount-card available"><span>Total disponible de los depósitos</span><strong>L {{ number_format($total / 100, 2) }}</strong></div>
                                <div class="pni-amount-card"><span>Máximo aplicable a esta factura</span><strong>L {{ number_format($maximoAplicable / 100, 2) }}</strong></div>
                            </div>
                            <div class="pni-panel">
                            <p class="pni-section"><i class="fa fa-bank mr-1" aria-hidden="true"></i>1. Depósitos que utilizará</p>
                            <div class="table-responsive"><table class="table table-sm pni-application-table"><thead><tr><th>Depósito</th><th>Fecha del pago</th><th>Cuenta receptora</th><th class="pni-numeric">Monto original</th><th class="pni-numeric">Ya aplicado</th><th class="pni-numeric">Disponible</th></tr></thead><tbody>
                                @foreach($origenes as $origen)<tr><td><strong>#{{ $origen['id'] }}</strong></td><td>{{ \Carbon\Carbon::parse($origen['fecha_pago'])->format('d/m/Y') }}<small>{{ $origen['hora_pago'] ? substr($origen['hora_pago'], 0, 5) : 'Hora no registrada' }}</small></td><td>{{ $origen['cuenta_bancaria'] }}</td><td class="pni-numeric">L {{ number_format($origen['monto_original'], 2) }}</td><td class="pni-numeric">L {{ number_format($origen['monto_original'] - $origen['saldo_disponible'], 2) }}</td><td class="pni-numeric"><strong>L {{ number_format($origen['saldo_disponible'], 2) }}</strong></td></tr>@endforeach
                            </tbody></table></div>
                            </div>
                            <div class="pni-application-grid">
                            <div class="pni-panel">
                            <p class="pni-section"><i class="fa fa-file-text-o mr-1" aria-hidden="true"></i>2. Cliente y factura destino</p>
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label for="pni-cliente-destino">Cliente destino *</label>
                                    <div wire:ignore x-data="pniSelect('clienteDestino', @js($clienteDestino), true)">
                                        <select id="pni-cliente-destino" x-ref="select" class="form-control" data-placeholder="Buscar y seleccionar cliente" @disabled($clienteFijo !== '')><option value=""></option>@foreach($clientes as $cliente)<option value="{{ $cliente->id }}">{{ $cliente->id }} - {{ $cliente->nombre }}</option>@endforeach</select>
                                    </div>
                                    @if($clienteFijo)<small>El cliente está definido por los depósitos seleccionados.</small>@endif
                                </div>
                                <div class="col-md-12 form-group">
                                    <label for="pni-factura-destino">Factura destino *</label>
                                    <div wire:key="facturas-cliente-{{ $clienteDestino ?: 'ninguno' }}" wire:ignore x-data="pniSelect('facturaDestino', @js($facturaDestino), true)">
                                        <select id="pni-factura-destino" x-ref="select" class="form-control" data-placeholder="Buscar factura pendiente" @disabled(!$clienteDestino)><option value=""></option>@foreach($facturas as $factura)<option value="{{ $factura->factura_id }}">{{ $factura->cai ?: $factura->numero_secuencia_cai }} · Vence {{ $factura->fecha_vencimiento }} · Saldo L {{ number_format($factura->saldo, 2) }}</option>@endforeach</select>
                                    </div>
                                    @if($clienteDestino && $facturas->isEmpty())<small class="text-warning">No hay facturas con saldo pendiente que coincidan.</small>@endif
                                </div>
                            </div>
                            @if($facturaActual = $facturas->firstWhere('factura_id', (int) $facturaDestino))
                                <div class="pni-invoice-summary"><strong>Factura {{ $facturaActual->cai ?: $facturaActual->numero_secuencia_cai }}</strong>
                                    <dl class="pni-facts">
                                        <div><dt>Fecha de emisión</dt><dd>{{ \Carbon\Carbon::parse($facturaActual->fecha_emision)->format('d/m/Y') }}</dd></div>
                                        <div><dt>Fecha de vencimiento</dt><dd>{{ \Carbon\Carbon::parse($facturaActual->fecha_vencimiento)->format('d/m/Y') }}</dd></div>
                                        <div><dt>Total de la factura</dt><dd>L {{ number_format($facturaActual->total, 2) }}</dd></div>
                                        <div><dt>Saldo pendiente después de aplicar</dt><dd id="pni-saldo-despues">{{ $saldoDespues !== null ? 'L ' . number_format($saldoDespues / 100, 2) : 'Ingrese un monto válido' }}</dd></div>
                                    </dl>
                                </div>
                            @endif
                            </div>
                            <div class="pni-panel">
                            <p class="pni-section"><i class="fa fa-money mr-1" aria-hidden="true"></i>3. Importe y distribución</p>
                            <div class="row">
                                <div class="col-12 form-group" wire:key="importe-{{ $clienteDestino }}-{{ $facturaDestino }}">
                                    <label for="pni-aplicar-monto">Monto a aplicar *</label>
                                    <div class="pni-money" x-data="pniMoney($wire.entangle('montoAplicar'))">
                                        <span aria-hidden="true">L</span><input id="pni-aplicar-monto" type="text" inputmode="decimal" required class="form-control" aria-label="Monto a aplicar en lempiras" placeholder="0.00" x-ref="amount" x-on:input="input()" x-on:input.debounce.350ms="$wire.set('montoAplicar', raw)" x-on:blur="format()" @disabled(!$facturaDestino)>
                                    </div>
                                    <small>Máximo aplicable: L {{ number_format($maximoAplicable / 100, 2) }}. Puede aplicar un importe menor para conservar saldo.</small>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="pni-observacion-aplicacion">Observación de la aplicación <span class="text-muted font-weight-normal">(opcional)</span></label>
                                <textarea id="pni-observacion-aplicacion" class="form-control" rows="2" maxlength="2000" wire:model="observacionAplicacion" placeholder="Escriba el detalle o referencia de este pago"></textarea>
                                <small>Se guardará en los abonos y se mostrará en el Libro de Cobros.</small>
                            </div>
                            @if(collect($origenes)->contains(fn ($p) => !$p['cliente_id']))<div class="alert alert-info">Los pagos sin cliente que reciban una aplicación se asignarán al cliente destino. Los pagos no consumidos permanecerán sin identificar.</div>@endif
                            <p class="text-muted">Se utilizan primero los depósitos más antiguos. Esta es la distribución prevista:</p>
                            @if($distribucion)
                                <div class="table-responsive"><table class="table table-sm pni-application-table"><thead><tr><th>Depósito</th><th class="pni-numeric">Monto que se aplicará</th><th class="pni-numeric">Saldo que quedará disponible</th></tr></thead><tbody>@foreach($distribucion as $linea)<tr><td><strong>#{{ $linea['pago']->id }}</strong></td><td class="pni-numeric">L {{ number_format($linea['monto'] / 100, 2) }}</td><td class="pni-numeric">L {{ number_format($linea['despues'] / 100, 2) }}</td></tr>@endforeach</tbody></table></div>
                            @else
                                <p class="pni-empty">Seleccione una factura e ingrese un importe válido para ver la distribución.</p>
                            @endif
                            </div>
                            </div>
                            @if($traslados)
                                <div class="alert alert-warning">
                                    @foreach($traslados as $traslado)<p>Pago #{{ $traslado['pago'] }}: período {{ $traslado['original'] }} conciliado. Comisión trasladada a {{ $traslado['asignado'] }}; se conserva la fecha original del depósito.</p>@endforeach
                                    <label><input type="checkbox" wire:model.live="confirmarPeriodo"> Confirmo el traslado de comisiones al período abierto indicado.</label>
                                </div>
                            @endif
                        @elseif($detalle)
                            <div class="pni-detail-title">
                                <div><h3>Pago #{{ $detalle->id }}</h3><span>{{ $detalle->cliente ?: 'Cliente sin identificar' }}</span></div>
                                <span class="pni-status">{{ \App\Services\PagosNoIdentificadosService::ESTADOS[$detalle->estado] }}</span>
                            </div>
                            <div class="pni-amounts">
                                <div class="pni-amount-card"><span>Total del depósito</span><strong>L {{ number_format($detalle->monto_original, 2) }}</strong></div>
                                <div class="pni-amount-card"><span>Aplicado a facturas</span><strong>L {{ number_format($detalle->monto_original - $detalle->saldo_disponible, 2) }}</strong></div>
                                <div class="pni-amount-card available"><span>Saldo disponible para aplicar</span><strong>L {{ number_format($detalle->saldo_disponible, 2) }}</strong></div>
                            </div>
                            <div class="pni-panel">
                                <p class="pni-section"><i class="fa fa-bank mr-1" aria-hidden="true"></i>Datos del depósito</p>
                                <dl class="pni-facts">
                                    <div><dt>Banco receptor</dt><dd>{{ $detalle->banco }}</dd></div>
                                    <div><dt>Cuenta bancaria</dt><dd>{{ $detalle->cuenta_bancaria }}</dd></div>
                                    <div><dt>Referencia bancaria</dt><dd>{{ $detalle->referencia_bancaria ?: 'Sin referencia registrada' }}</dd></div>
                                    <div><dt>Fecha y hora del pago</dt><dd>{{ \Carbon\Carbon::parse($detalle->fecha_pago)->format('d/m/Y') }} {{ $detalle->hora_pago ? substr($detalle->hora_pago, 0, 5) : '(hora no registrada)' }}</dd></div>
                                    <div><dt>Registrado por</dt><dd>{{ $detalle->creador }}<span class="d-block text-muted font-weight-normal">{{ \Carbon\Carbon::parse($detalle->created_at)->format('d/m/Y H:i') }}</span></dd></div>
                                </dl>
                                <p><strong>Observaciones</strong></p>
                                <p class="pni-note">{{ $detalle->observaciones ?: 'Sin observaciones' }}</p>
                                @if(!$detalle->cliente_original_conocido && $detalle->identificado_at)<p class="text-muted">Registrado originalmente sin cliente. Identificado el {{ \Carbon\Carbon::parse($detalle->identificado_at)->format('d/m/Y H:i') }} por {{ $identificador }}.</p>@endif
                            </div>
                            <h5 class="pni-section">Todas las aplicaciones de este pago ({{ $historial->count() }})</h5>
                            <p class="pni-intro">Cada aplicación indica a qué factura se destinó el dinero y cuánto saldo quedó. Haga clic en la factura vinculada para descargar su PDF.</p>
                                @forelse($historial as $aplicacion)
                                    <article class="pni-history-card">
                                        <div class="pni-history-heading">
                                            <div><strong>Aplicación #{{ $aplicacion->id }}</strong><small>{{ \Carbon\Carbon::parse($aplicacion->created_at)->format('d/m/Y H:i') }} · {{ $aplicacion->usuario }}</small></div>
                                            <div><small class="mt-0 mb-1">Factura vinculada</small><button type="button" class="pni-invoice-link" wire:click="descargarFactura({{ $aplicacion->factura_id }})" wire:loading.attr="disabled" aria-label="Descargar factura {{ $aplicacion->cai ?: $aplicacion->numero_secuencia_cai }} en PDF"><i class="fa fa-file-pdf-o" aria-hidden="true"></i>{{ $aplicacion->cai ?: $aplicacion->numero_secuencia_cai }}<i class="fa fa-download" aria-hidden="true"></i></button></div>
                                        </div>
                                        <dl class="pni-facts">
                                            <div><dt>Monto aplicado de este depósito</dt><dd>L {{ number_format($aplicacion->monto, 2) }}</dd></div>
                                            <div><dt>Saldo del depósito</dt><dd>L {{ number_format($aplicacion->saldo_antes, 2) }} <span class="text-muted font-weight-normal">antes →</span> L {{ number_format($aplicacion->saldo_despues, 2) }} <span class="text-muted font-weight-normal">después</span></dd></div>
                                            <div><dt>Saldo de la factura en esta operación</dt><dd>L {{ number_format($aplicacion->saldo_factura_antes, 2) }} <span class="text-muted font-weight-normal">antes →</span> L {{ number_format($aplicacion->saldo_factura_despues, 2) }} <span class="text-muted font-weight-normal">después</span></dd></div>
                                            <div><dt>Operación que agrupa esta aplicación</dt><dd><button class="btn btn-link p-0" wire:click="verOperacion({{ $aplicacion->operacion_id }})">Operación #{{ $aplicacion->operacion_id }}</button></dd></div>
                                        </dl>
                                        <div class="mt-3"><strong>Observaciones del abono</strong><p class="pni-note mb-0">{{ $aplicacion->observaciones ?: 'Sin observaciones' }}</p></div>
                                    </article>
                                @empty
                                    <div class="pni-history-card pni-empty"><i class="fa fa-file-text-o d-block" aria-hidden="true"></i><strong class="d-block mb-2">Este pago aún no tiene aplicaciones a facturas.</strong><span>El saldo continúa disponible. Para utilizarlo, cierre este detalle, seleccione el pago y presione «Aplicar Pago».</span></div>
                                @endforelse
                            @if($operacionDetalle->isNotEmpty())
                                <h5>Orígenes de la operación #{{ $operacionId }}</h5>
                                <p class="text-muted">Depósitos utilizados en esta operación. El saldo de la factura refleja el total de todos estos depósitos.</p>
                                <div class="table-responsive">
                                <table class="table table-sm"><thead><tr><th>Pago</th><th>Banco / cuenta / referencia</th><th>Fecha de pago</th><th>Monto</th></tr></thead><tbody>@foreach($operacionDetalle as $origen)<tr><td>#{{ $origen->pago_id }}</td><td>{{ $origen->banco }} / {{ $origen->cuenta_bancaria }}<small class="d-block text-muted">{{ $origen->referencia_bancaria ?: 'Sin referencia registrada' }}</small></td><td>{{ \Carbon\Carbon::parse($origen->fecha_pago)->format('d/m/Y') }} {{ $origen->hora_pago ? substr($origen->hora_pago, 0, 5) : '(hora no registrada)' }}</td><td>L {{ number_format($origen->monto, 2) }}</td></tr>@endforeach</tbody><tfoot><tr><th colspan="3">Total operación</th><th>L {{ number_format($operacionDetalle->sum('monto'), 2) }}</th></tr></tfoot></table>
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="modal-footer">
                        @if($modal === 'filtros')<button class="btn btn-outline-secondary btn-sm mr-auto" wire:click="limpiarFiltros"><i class="fa fa-eraser mr-1" aria-hidden="true"></i>Limpiar</button>@endif
                        <button class="btn btn-default" wire:click="cerrar" wire:loading.attr="disabled">Cerrar</button>
                        @if($modal === 'filtros')<button class="pni-search" type="submit" form="pni-filtros" wire:loading.attr="disabled"><i class="fa fa-search mr-1" aria-hidden="true"></i>Buscar</button>@endif
                        @if($modal === 'nuevo')<button class="btn btn-primary" type="submit" form="pni-registro" wire:loading.attr="disabled">Guardar depósito</button>@endif
                        @if($modal === 'aplicar')<button class="btn btn-success" wire:click="aplicar" wire:loading.attr="disabled" @disabled(!$facturaDestino || !$distribucion || array_sum(array_column($distribucion, 'monto')) > $maximoAplicable || ($traslados && !$confirmarPeriodo))>Confirmar aplicación</button>@endif
                    </div>
                </div>
            </div>
        </div>
        </div>
        @endteleport
    @endif
    @script
    <script>
        Alpine.data('pniSelect', (field, value, live) => ({
            control: null,
            init() {
                this.control = $(this.$refs.select);
                this.control.select2({
                    width: '100%',
                    dropdownParent: this.control.closest('.modal'),
                    placeholder: this.$refs.select.dataset.placeholder,
                    allowClear: true,
                    language: {
                        noResults: () => 'No se encontraron resultados',
                        searching: () => 'Buscando...'
                    }
                }).val(value).trigger('change.select2');
                this.control.on('change.pni', () => this.$wire.set(field, this.control.val() || '', live));
            },
            destroy() {
                this.control.off('.pni');
                this.control.select2('destroy');
            }
        }));
        Alpine.data('pniMoney', (value) => ({
            raw: value,
            init() {
                this.format();
            },
            input() {
                const input = this.$refs.amount;
                const position = input.value.slice(0, input.selectionStart).replaceAll(',', '').length;
                this.raw = input.value.replaceAll(',', '');
                this.format(false);
                let cursor = 0;
                let characters = 0;
                while (cursor < input.value.length && characters < position) {
                    if (input.value[cursor] !== ',') characters++;
                    cursor++;
                }
                input.setSelectionRange(cursor, cursor);
            },
            format(complete = true) {
                const input = this.$refs.amount;
                const valid = /^\d{1,10}(\.\d{0,2})?$/.test(this.raw);
                input.setCustomValidity(this.raw === '' || valid ? '' : 'Ingrese hasta 10 dígitos enteros y 2 decimales, separados por punto.');
                input.value = this.raw;
                if (/^\d+(\.\d{0,2})?$/.test(this.raw)) {
                    const [integer, fraction] = this.raw.split('.');
                    const grouped = integer.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    if (complete && valid) this.raw = integer + '.' + (fraction || '').padEnd(2, '0');
                    input.value = grouped + (complete ? '.' + (fraction || '').padEnd(2, '0')
                        : fraction !== undefined ? '.' + fraction : '');
                }
            }
        }));
    </script>
    @endscript
</div>
