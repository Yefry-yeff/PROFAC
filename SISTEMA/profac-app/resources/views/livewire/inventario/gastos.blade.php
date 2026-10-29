<div class="gastos-reporte">
    @push('styles')
    <style>
        .gastos-reporte .gastos-header { background:#fff; padding:22px 28px; color:#676a6c; border-bottom:1px solid #e7eaec; box-shadow:0 1px 3px rgba(0,0,0,.04); }
        .gastos-reporte .gastos-header h2 { margin:0 0 5px; font-weight:700; color:#343a40; }
        .gastos-reporte .gastos-header p { margin:0; color:#889198; font-size:.82rem; }
        .gastos-reporte .reporte-selector { width:auto; min-width:210px; height:40px; padding:0 36px 0 13px; color:#69421f; background-color:#fff; border:1px solid #ead9c8; border-radius:7px; font-size:.84rem; font-weight:600; box-shadow:0 2px 6px rgba(125,63,0,.08); cursor:pointer; transition:border-color .18s, box-shadow .18s; }
        .gastos-reporte .reporte-selector:hover,.gastos-reporte .reporte-selector:focus { border-color:#e67e22; box-shadow:0 0 0 .18rem rgba(230,126,34,.16); outline:0; }
        .gastos-reporte .reporte-create-button { display:inline-flex; align-items:center; gap:7px; padding:10px 16px; color:#fff!important; background:linear-gradient(135deg,#f39c12 0%,#e05a00 100%); border:0; border-radius:7px; font-size:.82rem; font-weight:700; box-shadow:0 3px 8px rgba(224,90,0,.22); transition:transform .18s, box-shadow .18s, filter .18s; }
        .gastos-reporte .reporte-create-button:hover,.gastos-reporte .reporte-create-button:focus { color:#fff!important; filter:brightness(.96); transform:translateY(-1px); box-shadow:0 5px 12px rgba(224,90,0,.28); }
        .gastos-reporte .gastos-actions { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
        .gastos-reporte .gastos-panel { border:1px solid #e8d5bf; border-radius:8px; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.10); padding:0; overflow:hidden; }
        .gastos-reporte .gastos-panel-body { padding:16px 20px 20px; }
        .gastos-reporte .gastos-toolbar { display:flex; align-items:center; justify-content:flex-end; gap:8px; flex-wrap:wrap; padding:12px 20px; background:linear-gradient(135deg,#f39c12 0%,#e05a00 100%); }
        .gastos-reporte .btn-gastos-action { background:rgba(255,255,255,.18)!important; color:#fff!important; border:1.5px solid rgba(255,255,255,.5)!important; border-radius:5px!important; font-weight:600!important; font-size:.78rem; padding:5px 14px; transition:background .18s; white-space:nowrap; cursor:pointer; }
        .gastos-reporte .btn-gastos-action:hover { background:rgba(255,255,255,.30)!important; color:#fff!important; }
        .gastos-reporte .gastos-filtros-bar { display:flex; padding:8px 16px; background:#fdfaf5; border-bottom:1px solid #e8d5bf; align-items:center; gap:6px; flex-wrap:wrap; font-size:.78rem; }
        .gastos-reporte .gastos-filtro-badge { display:inline-flex; align-items:center; gap:5px; background:#fff8ee; border:1px solid #f2d49a; border-radius:12px; padding:2px 10px; font-size:.75rem; color:#7d3f00; }
        #modalFiltrosGastos .modal-header { background:linear-gradient(135deg,#f39c12 0%,#e05a00 100%); color:#fff; border-radius:8px 8px 0 0; padding:14px 20px; }
        #modalFiltrosGastos .modal-title { color:#fff; font-size:.95rem; font-weight:700; }
        #modalFiltrosGastos .close { color:#fff; opacity:.8; text-shadow:none; font-size:1.4rem; }
        #modalFiltrosGastos .modal-body { background:#fdfaf6; padding:18px 20px 8px; }
        #modalFiltrosGastos .modal-footer { background:#f8f4ef; border-top:1px solid #ead9c8; padding:10px 20px; }
        #modalFiltrosGastos .form-group label { font-size:.78rem; font-weight:600; color:#555; margin-bottom:3px; }
        #modalFiltrosGastos .form-control { border-color:#ddd; border-radius:5px; font-size:.82rem; }
        #modalFiltrosGastos .form-control:focus { border-color:#e67e22; box-shadow:0 0 0 .15rem rgba(230,126,34,.18); }
        #modalFiltrosGastos .select2-container { width:100% !important; }
        .gastos-reporte .modal-section-label { font-size:.68rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:#e67e22; border-bottom:2px solid #fdebd0; padding-bottom:5px; margin-bottom:14px; margin-top:6px; display:flex; align-items:center; gap:5px; }
        .gastos-reporte .modal-filter-grid { background:#fff; border:1px solid #ead9c8; border-radius:7px; padding:14px 16px 6px; margin-bottom:14px; }
        .gastos-reporte .date-input-icon { position:relative; }
        .gastos-reporte .date-input-icon i { position:absolute; left:9px; top:50%; transform:translateY(-50%); color:#aaa; font-size:.78rem; pointer-events:none; }
        .gastos-reporte .date-input-icon input { padding-left:28px; }
        .gastos-reporte .gastos-buscar { background:linear-gradient(135deg,#f39c12,#e05a00); color:#fff; border:none; font-weight:600; padding:6px 20px; border-radius:5px; }
        .gastos-reporte .gastos-transferir { background:#e8751a; border-color:#e8751a; color:#fff; }
        .gastos-reporte .gastos-transferir:hover { filter:brightness(.92); color:#fff; }
        .gastos-reporte .table thead th { background:#f3f6f7; color:#52636a; border-bottom:2px solid #dce4e6; white-space:nowrap; }
        .gastos-reporte .table td { vertical-align:middle; }
        .gastos-reporte .gastos-sort { display:flex; align-items:center; justify-content:space-between; gap:10px; width:100%; padding:0; border:0; background:transparent; color:inherit; font:inherit; text-align:left; cursor:pointer; }
        .gastos-reporte .gastos-sort:focus-visible { outline:2px solid #e67e22; outline-offset:3px; }
        .gastos-reporte .gastos-sort i { color:#e67e22; }
        .gastos-reporte input[type=checkbox] { accent-color:#16856f; width:16px; height:16px; vertical-align:middle; cursor:pointer; }
        .gastos-reporte input[type=checkbox].gastos-checkbox-anulado { appearance:none; background:#dfe3e6; border:1px solid #b0b8bf; border-radius:2px; cursor:not-allowed; opacity:1; }
        .gastos-reporte .gastos-estado { display:inline-block; padding:5px 9px; border-radius:12px; font-size:11px; font-weight:700; }
        .gastos-reporte .gastos-registrado { background:#e8f5f1; color:#126b5b; }
        .gastos-reporte .gastos-compras { background:#fff0e4; color:#9c4609; }
        .gastos-reporte .gastos-anulado { background:#ffebee; color:#b71c1c; }
        .gastos-reporte .gastos-total { text-align:right; font-weight:700; white-space:nowrap; }
        .gastos-reporte .gastos-fila { cursor:pointer; }
        .gastos-reporte [x-cloak] { display:none !important; }
    </style>
    @endpush

    <div class="gastos-header d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h2><i class="fa fa-list-alt mr-2"></i>Reporte de gastos</h2>
            <p>Inventario / Gastos</p>
        </div>
        <div class="d-flex align-items-center flex-wrap" style="gap:8px">
            <label for="selectorReporteInventario" class="sr-only">Seleccionar reporte</label>
            <select id="selectorReporteInventario" class="form-control reporte-selector" onchange="if (this.value) window.location.href = this.value">
                <option value="{{ url('/producto/listar/compras') }}">Reporte de compras</option>
                <option value="{{ route('gastos') }}" selected>Reporte de gastos</option>
            </select>
            <a href="{{ url('/producto/compra?modo=gasto') }}" class="btn reporte-create-button"><i class="fa fa-plus"></i>Nuevo gasto</a>
        </div>
    </div>
    <div class="wrapper wrapper-content">
        <div class="gastos-panel">
            <div class="gastos-toolbar">
                <button type="button" class="btn-gastos-action" wire:click="exportarExcel" wire:loading.attr="disabled"
                    title="Exportar todos los gastos filtrados, con sus estados">
                    <i class="fa fa-file-excel-o mr-1"></i>Excel
                </button>
                <button type="button" class="btn-gastos-action" data-toggle="modal" data-target="#modalFiltrosGastos">
                    <i class="fa fa-filter mr-1"></i>Filtros
                </button>
            </div>
            <div class="gastos-filtros-bar" aria-live="polite">
                <span class="gastos-filtro-badge"><strong>Desde:</strong> {{ $filtrosAplicados['desde'] }}</span>
                <span class="gastos-filtro-badge"><strong>Hasta:</strong> {{ $filtrosAplicados['hasta'] }}</span>
                @if (!empty($filtrosAplicados['factura']))
                    <span class="gastos-filtro-badge"><strong>Factura:</strong> {{ $filtrosAplicados['factura'] }}</span>
                @endif
                @if (!empty($filtrosAplicados['proveedor']))
                    <span class="gastos-filtro-badge"><strong>Proveedor:</strong> {{ $proveedores->firstWhere('id', $filtrosAplicados['proveedor'])?->nombre }}</span>
                @endif
                @if (!empty($filtrosAplicados['estado']))
                    <span class="gastos-filtro-badge"><strong>Estado:</strong> {{ \App\Models\Gasto::ESTADOS_REPORTE[$filtrosAplicados['estado']] }}</span>
                @endif
            </div>
            <div class="gastos-panel-body">
            <p class="text-muted">Todos los gastos, incluidos los anulados y los pasados al reporte de compras. No se registran abonos ni movimientos de inventario.</p>
            @if (session()->has('gastos_mensaje'))
                <div class="alert alert-success" role="status">{{ session('gastos_mensaje') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif
            <div class="gastos-actions mb-3">
                <span class="badge badge-primary">{{ count($seleccionados) }} seleccionados</span>
                <button type="button" class="btn btn-white btn-sm" wire:click="limpiarSeleccion" wire:loading.attr="disabled" @disabled(count($seleccionados) === 0)>Limpiar selección</button>
                <span class="text-muted">El checkbox del encabezado selecciona todos los gastos activos filtrados, de todas las páginas.</span>
            </div>
            <div class="gastos-actions mb-3">
                <button type="button" class="btn gastos-transferir" data-toggle="modal" data-target="#modalPasarComprasGastos"
                    wire:loading.attr="disabled" @disabled(!$puedeIncluir)>
                    <i class="fa fa-exchange-alt mr-1"></i>Pasar al reporte de compras
                </button>
                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modalAnularGastos"
                    wire:loading.attr="disabled" @disabled(!$puedeAnular)>
                    <i class="fa fa-ban mr-1"></i>Anular seleccionados
                </button>
                <button type="button" class="btn btn-white" data-toggle="modal" data-target="#modalQuitarComprasGastos"
                    wire:loading.attr="disabled" @disabled(!$puedeQuitar)>
                    <i class="fa fa-undo mr-1"></i>Quitar del reporte de compras
                </button>
                <span wire:loading role="status" class="text-muted">Procesando...</span>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead><tr><th><input id="seleccionarTodosGastos" type="checkbox"
                        aria-label="Seleccionar todos los gastos activos filtrados" title="Seleccionar todos (todas las páginas)"
                        wire:model.live="seleccionarTodos" wire:loading.attr="disabled"></th>
                        @foreach (['id' => 'ID', 'numero_factura' => 'Factura', 'proveedor' => 'Proveedor', 'fecha_emision' => 'Emisión', 'total' => 'Total', 'estado' => 'Estado', 'anulado_por' => 'Anulado por', 'anulado_at' => 'Fecha y hora de anulación', 'motivo_anulacion' => 'Motivo de anulación'] as $columna => $etiqueta)
                            <th scope="col" aria-sort="{{ $columnaOrden === $columna ? ($direccionOrden === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                <button type="button" class="gastos-sort" wire:click="ordenarPor('{{ $columna }}')" wire:loading.attr="disabled"
                                    aria-label="Ordenar por {{ $etiqueta }} {{ $columnaOrden === $columna && $direccionOrden === 'asc' ? 'descendente' : 'ascendente' }}">
                                    {{ $etiqueta }}
                                    <i class="fa {{ $columnaOrden === $columna ? ($direccionOrden === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}" aria-hidden="true"></i>
                                </button>
                            </th>
                        @endforeach
                    </tr></thead>
                    <tbody x-data="{ historial: null }">
                    @forelse ($gastos as $gasto)
                        <tr wire:key="gasto-{{ $gasto->id }}" class="gastos-fila" tabindex="0"
                            title="Ver cambios del gasto"
                            x-on:click="historial = historial === {{ $gasto->id }} ? null : {{ $gasto->id }}"
                            x-on:keydown.enter.prevent="historial = historial === {{ $gasto->id }} ? null : {{ $gasto->id }}"
                            x-on:keydown.space.prevent="historial = historial === {{ $gasto->id }} ? null : {{ $gasto->id }}"
                            x-bind:aria-expanded="historial === {{ $gasto->id }}"
                            aria-controls="cambiosGasto{{ $gasto->id }}">
                            <td x-on:click.stop x-on:keydown.stop><input type="checkbox" aria-label="Seleccionar gasto G-{{ $gasto->id }}" value="{{ $gasto->id }}"
                                class="{{ $gasto->estado === 'anulado' ? 'gastos-checkbox-anulado' : '' }}"
                                title="{{ $gasto->estado === 'anulado' ? 'Gasto anulado: seleccion bloqueada' : 'Seleccionar gasto' }}"
                                wire:model.live="seleccionados" wire:loading.attr="disabled" @disabled($gasto->estado !== 'activo')></td>
                            <td>G-{{ $gasto->id }}</td><td>{{ $gasto->numero_factura }}</td><td>{{ $gasto->proveedor }}</td>
                            <td>{{ $gasto->fecha_emision }}</td><td class="gastos-total">L. {{ number_format($gasto->total, 2) }}</td>
                            <td>
                                <span class="gastos-estado {{ $gasto->estado === 'anulado' ? 'gastos-anulado' : ($gasto->incluir_reporte_compras ? 'gastos-compras' : 'gastos-registrado') }}">{{ $gasto->estado_reporte }}</span>
                                @if ($gasto->estado === 'anulado' && $gasto->incluir_reporte_compras)
                                    <small class="d-block text-muted">Pasado al reporte de compras; excluido por anulación.</small>
                                @endif
                            </td>
                            <td>{{ $gasto->anulado_por_nombre ?? '—' }}</td>
                            <td class="text-nowrap">{{ $gasto->anulado_at?->format('d/m/Y H:i:s') ?? '—' }}</td>
                            <td>{{ $gasto->motivo_anulacion ?? '—' }}</td>
                        </tr>
                        <tr wire:key="auditoria-gasto-{{ $gasto->id }}" id="cambiosGasto{{ $gasto->id }}"
                            x-show="historial === {{ $gasto->id }}" x-cloak>
                            <td colspan="10">
                                    @forelse ($auditoria->get($gasto->id, collect()) as $evento)
                                        <div class="border-bottom py-2">
                                            <strong>{{ ['registrar' => 'Registro del gasto', 'anular' => 'Anulación', 'incluir_compras' => 'Inclusión en compras', 'quitar_compras' => 'Retiro de compras'][$evento->accion] ?? $evento->accion }}</strong>
                                            — {{ $evento->usuario ?? 'Usuario ID '.$evento->usuario_id }}
                                            — {{ \Carbon\Carbon::parse($evento->created_at)->format('d/m/Y H:i:s') }}
                                            @if ($evento->motivo)<div>Motivo: {{ $evento->motivo }}</div>@endif
                                            <small class="d-block text-muted">Antes: {{ $evento->antes ?? 'Sin registro' }} · Después: {{ $evento->despues }}</small>
                                        </div>
                                    @empty
                                        <p class="text-muted mb-0">Registro anterior a la auditoría. Los cambios siguientes quedarán registrados.</p>
                                    @endforelse
                            </td>
                        </tr>
                    @empty <tr><td colspan="10" class="text-center text-muted">No hay gastos registrados.</td></tr> @endforelse
                    </tbody>
                </table>
            </div>
            {{ $gastos->links() }}
            </div>
        </div>
        <div class="modal fade" id="modalAnularGastos" tabindex="-1" role="dialog"
            aria-labelledby="tituloAnularGastos" aria-hidden="true" wire:ignore.self data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form class="modal-content" wire:submit="anularSeleccionados">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title text-white" id="tituloAnularGastos"><i class="fa fa-ban mr-2"></i>Anular gastos</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" wire:loading.attr="disabled"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Desea anular los {{ count($seleccionados) }} gastos seleccionados?</p>
                        <p class="text-muted">Se registrará su usuario y la fecha y hora. No puede anular gastos incluidos en compras: debe quitarlos primero de ese reporte.</p>
                        @error('seleccionados')<div class="alert alert-danger" role="alert">{{ $message }}</div>@enderror
                        @foreach ($errors->get('seleccionados.*') as $mensajes)
                            @foreach ($mensajes as $mensaje)<div class="alert alert-danger" role="alert">{{ $mensaje }}</div>@endforeach
                        @endforeach
                        <label for="motivoAnulacionGasto">Motivo de anulación <span class="text-danger">*</span></label>
                        <textarea id="motivoAnulacionGasto" class="form-control" rows="3" maxlength="1000" required aria-required="true" wire:model="motivoAnulacion" wire:loading.attr="disabled"></textarea>
                        @error('motivoAnulacion')<div class="text-danger" role="alert">{{ $message }}</div>@enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-dismiss="modal" wire:loading.attr="disabled">Cancelar</button>
                        <button type="submit" class="btn btn-danger" wire:loading.attr="disabled" @disabled(!$puedeAnular)>Confirmar anulación</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal fade" id="modalPasarComprasGastos" tabindex="-1" role="dialog"
            aria-labelledby="tituloPasarComprasGastos" aria-hidden="true" wire:ignore.self data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form class="modal-content" wire:submit="pasarACompras">
                    <div class="modal-header gastos-transferir">
                        <h5 class="modal-title text-white" id="tituloPasarComprasGastos"><i class="fa fa-exchange-alt mr-2"></i>Pasar al reporte de compras</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" wire:loading.attr="disabled"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Desea pasar los {{ count($seleccionados) }} gastos seleccionados al reporte de compras?</p>
                        <p class="text-muted">Seguirán visibles en Gastos con el estado «Pasado al reporte de compras», sin duplicar los registros.</p>
                        @error('seleccionados')<div class="alert alert-danger" role="alert">{{ $message }}</div>@enderror
                        @foreach ($errors->get('seleccionados.*') as $mensajes)
                            @foreach ($mensajes as $mensaje)<div class="alert alert-danger" role="alert">{{ $mensaje }}</div>@endforeach
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-dismiss="modal" wire:loading.attr="disabled">Cancelar</button>
                        <button type="submit" class="btn gastos-transferir" wire:loading.attr="disabled" @disabled(!$puedeIncluir)>Confirmar traslado</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal fade" id="modalQuitarComprasGastos" tabindex="-1" role="dialog"
            aria-labelledby="tituloQuitarComprasGastos" aria-hidden="true" wire:ignore.self data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form class="modal-content" wire:submit="quitarDeCompras">
                    <div class="modal-header gastos-transferir">
                        <h5 class="modal-title text-white" id="tituloQuitarComprasGastos">Quitar del reporte de compras</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" wire:loading.attr="disabled"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Desea quitar del reporte de compras los gastos incluidos de su selección?</p>
                        <p>Permanecerán en Gastos como registrados. Se auditarán el usuario, la fecha y hora y el estado anterior y posterior. Los gastos que no estén en compras no se modificarán.</p>
                        @error('seleccionados')<div class="alert alert-danger" role="alert">{{ $message }}</div>@enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-dismiss="modal" wire:loading.attr="disabled">Cancelar</button>
                        <button type="submit" class="btn gastos-transferir" wire:loading.attr="disabled" @disabled(!$puedeQuitar)>Confirmar retiro</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal fade" id="modalFiltrosGastos" tabindex="-1" role="dialog"
            aria-labelledby="tituloFiltrosGastos" aria-hidden="true" wire:ignore.self>
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <form class="modal-content" wire:submit="aplicarFiltros">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tituloFiltrosGastos"><i class="fa fa-filter mr-2"></i>Filtros de Búsqueda</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @if ($errors->has('filtros.*'))
                            <div class="alert alert-danger" role="alert">
                                @foreach ($errors->get('filtros.*') as $mensajes)
                                    @foreach ($mensajes as $mensaje)<div>{{ $mensaje }}</div>@endforeach
                                @endforeach
                            </div>
                        @endif
                        <p class="modal-section-label"><i class="fa fa-calendar"></i>Rango de fechas</p>
                        <div class="modal-filter-grid">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="gastosDesde">Desde</label>
                                <div class="date-input-icon"><i class="fa fa-calendar-o"></i>
                                    <input id="gastosDesde" type="date" class="form-control form-control-sm" wire:model="filtros.desde" required>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="gastosHasta">Hasta</label>
                                <div class="date-input-icon"><i class="fa fa-calendar-o"></i>
                                    <input id="gastosHasta" type="date" class="form-control form-control-sm" wire:model="filtros.hasta" required>
                                </div>
                            </div>
                        </div>
                        </div>
                        <p class="modal-section-label"><i class="fa fa-search"></i>Criterios de búsqueda</p>
                        <div class="modal-filter-grid">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="gastosFactura">Número de factura</label>
                                <input id="gastosFactura" type="text" class="form-control form-control-sm" maxlength="255" wire:model="filtros.factura">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="gastosEstado">Estado</label>
                                <select id="gastosEstado" class="form-control form-control-sm" wire:model="filtros.estado">
                                    <option value="">Todos los estados</option>
                                    @foreach (\App\Models\Gasto::ESTADOS_REPORTE as $valor => $etiqueta)
                                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="gastosProveedor">Proveedor</label>
                                <div wire:ignore>
                                    <select id="gastosProveedor" class="form-control">
                                        <option value="">Todos los proveedores</option>
                                        @foreach ($proveedores as $proveedor)
                                            <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="limpiarFiltros" wire:loading.attr="disabled"><i class="fa fa-eraser mr-1"></i>Limpiar filtros</button>
                        <button type="submit" class="btn btn-sm gastos-buscar" wire:loading.attr="disabled"><i class="fa fa-search mr-1"></i>Buscar</button>
                    </div>
                </form>
            </div>
        </div>
        @script
        <script>
            const modal = $('#modalFiltrosGastos');
            const proveedor = $('#gastosProveedor');
            proveedor.select2({
                dropdownParent: modal,
                placeholder: 'Todos los proveedores',
                allowClear: true,
                width: '100%'
            }).on('change', function () {
                $wire.set('filtros.proveedor', this.value, false);
            });
            $wire.on('gastos-filtros-aplicados', () => modal.modal('hide'));
            $wire.on('gastos-proveedor-limpiado', () => proveedor.val('').trigger('change.select2'));
            $wire.on('gastos-accion-completada', () => {
                $('#modalAnularGastos, #modalPasarComprasGastos, #modalQuitarComprasGastos').modal('hide');
            });
        </script>
        @endscript
    </div>
</div>
