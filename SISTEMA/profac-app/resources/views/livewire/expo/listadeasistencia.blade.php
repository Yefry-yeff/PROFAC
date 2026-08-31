<div class="attendance-page">
    @push('styles')
    <style>
        .attendance-page { --attendance-ink:#2d3748; --attendance-muted:#78909c; --attendance-line:#e8d5bf; --attendance-orange:#e65100; --attendance-gradient:linear-gradient(135deg,#e65100 0%,#f9a826 100%); }
        .attendance-heading { display:flex; align-items:flex-start; justify-content:space-between; gap:18px; }
        .attendance-heading h2 { margin-bottom:4px; color:var(--attendance-ink); font-size:24px; }
        .attendance-heading p { margin:0; color:var(--attendance-muted); font-size:12px; }
        .attendance-back { display:inline-flex; align-items:center; gap:6px; border:1px solid #e0cbb0; border-radius:7px; background:#fff; color:#c0622a; font-size:11px; font-weight:700; box-shadow:0 1px 3px rgba(0,0,0,.06); }
        .attendance-back:hover { border-color:#e67e22; background:#fff8f0; color:#a94c17; }
        .attendance-alert { margin-bottom:14px; border:0; border-radius:6px; box-shadow:0 3px 12px rgba(35,55,65,.07); }
        .attendance-card { overflow:visible; border:1px solid var(--attendance-line); border-radius:8px; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.10); }
        .attendance-card-head { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:13px 20px; border-radius:7px 7px 0 0; background:var(--attendance-gradient); color:#fff; }
        .attendance-card-head h5 { margin:0 0 2px; color:#fff; font-size:15px; font-weight:800; }
        .attendance-card-head small { color:rgba(255,255,255,.82); }
        .attendance-state { display:inline-flex; align-items:center; gap:5px; border-radius:12px; padding:4px 9px; background:rgba(255,255,255,.16); font-size:9px; font-weight:800; text-transform:uppercase; white-space:nowrap; }
        .attendance-state i { color:#d9ffd8; font-size:7px; }
        .attendance-content { padding:16px 20px 20px; }
        .attendance-summary { display:grid; grid-template-columns:minmax(220px,1.5fr) repeat(2,minmax(160px,1fr)); gap:9px; margin-bottom:15px; }
        .attendance-summary-item { min-height:68px; padding:10px 12px; border:1px solid var(--attendance-line); border-radius:8px; background:#fdf6ee; }
        .attendance-summary-item:nth-child(2) { border-color:#dbe5ea; background:#f8fafc; }
        .attendance-summary-item:nth-child(3) { border-color:#bbf7d0; background:#f0fdf4; }
        .attendance-summary-item label, .attendance-summary-item > span { display:block; margin:0 0 4px; color:#7d3f00; font-size:9px; font-weight:800; text-transform:uppercase; }
        .attendance-summary-item:nth-child(2) > span { color:#607d8b; }
        .attendance-summary-item:nth-child(3) > span { color:#1a7a4e; }
        .attendance-summary-item strong { display:block; overflow:hidden; color:var(--attendance-ink); font-size:15px; text-overflow:ellipsis; white-space:nowrap; }
        .attendance-summary-item small { color:var(--attendance-muted); font-size:10px; }
        .attendance-summary-item select { height:34px; padding:3px 36px 3px 8px; border:1.5px solid #e0cbb0; border-radius:7px; background-color:#fff; background-repeat:no-repeat; background-position:right 10px center; background-size:20px 20px; font-size:11px; }
        .attendance-summary-item select:focus { border-color:var(--attendance-orange); box-shadow:0 0 0 3px rgba(230,81,0,.11); }
        .attendance-toolbar { display:grid; grid-template-columns:minmax(280px,1fr) auto; align-items:end; gap:14px; margin-bottom:14px; }
        .attendance-search { position:relative; max-width:680px; }
        .attendance-search label { margin-bottom:5px; color:#4a5568; font-size:10px; font-weight:800; text-transform:uppercase; }
        .attendance-search-box { position:relative; }
        .attendance-search-box > i { position:absolute; z-index:2; top:12px; left:12px; color:var(--attendance-orange); }
        .attendance-search input { min-height:38px; padding-left:36px; border:1.5px solid #dde2ec; border-radius:8px; font-size:12px; }
        .attendance-search input:focus { border-color:var(--attendance-orange); box-shadow:0 0 0 3px rgba(230,81,0,.11); }
        .attendance-results { position:absolute; z-index:30; top:calc(100% + 4px); right:0; left:0; max-height:290px; overflow:auto; border:1px solid #e0cbb0; border-radius:8px; background:#fff; box-shadow:0 8px 20px rgba(44,62,80,.16); }
        .attendance-result { width:100%; display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 12px; border:0; border-bottom:1px solid #edf1f3; background:#fff; color:#344b56; text-align:left; }
        .attendance-result:last-child { border-bottom:0; }
        .attendance-result:hover { background:#fff5eb; }
        .attendance-result strong { font-size:11px; }
        .attendance-result small { color:#758992; font-size:9px; }
        .attendance-result-icon { display:inline-flex; width:26px; height:26px; flex:0 0 26px; align-items:center; justify-content:center; border-radius:50%; background:#fff1df; color:var(--attendance-orange); }
        .attendance-actions { display:flex; align-items:center; justify-content:flex-end; gap:7px; flex-wrap:wrap; }
        .attendance-export { display:inline-flex; align-items:center; gap:5px; min-height:33px; border-radius:7px; background:#fff; font-size:10px; font-weight:700; }
        .attendance-export.excel { border-color:#9bc8a7; color:#27733c; }
        .attendance-export.excel:hover { background:#f0fdf4; }
        .attendance-export.pdf { border-color:#e0adad; color:#9b3434; }
        .attendance-export.pdf:hover { background:#fff5f5; }
        .attendance-section-head { display:flex; align-items:center; justify-content:space-between; gap:12px; margin:0; padding:9px 12px; border:1px solid #e8d5bf; border-bottom:0; border-radius:7px 7px 0 0; background:#fdf4e7; }
        .attendance-section-head h3 { margin:0; color:var(--attendance-ink); font-size:14px; font-weight:800; }
        .attendance-count { display:inline-flex; align-items:center; gap:5px; border:1px solid #bbf7d0; border-radius:12px; padding:4px 9px; background:#f0fdf4; color:#1a7a4e; font-size:9px; font-weight:800; }
        .attendance-table-wrap { overflow:auto; border:1px solid var(--attendance-line); border-radius:0 0 7px 7px; }
        .attendance-table { min-width:1100px; margin:0; font-size:11px; }
        .attendance-table th { padding:8px 10px!important; border-bottom:2px solid #f2d49a!important; background:#fffaf3; color:#7d3f00; font-size:9px; font-weight:800; text-transform:uppercase; white-space:nowrap; }
        .attendance-table td { padding:9px 10px!important; vertical-align:middle!important; border-color:#edf1f3!important; color:#344b56; }
        .attendance-table tbody tr:hover { background:#fffcf5; }
        .attendance-client { color:var(--attendance-ink); font-size:11px; font-weight:800; }
        .attendance-client-id { color:#7c8e97; font-size:9px; }
        .attendance-contact { color:#607d8b; }
        .attendance-registered { line-height:1.35; white-space:nowrap; }
        .attendance-tickets { width:76px; height:32px; margin:auto; border:1.5px solid #d7dee3; border-radius:6px; text-align:center; }
        .attendance-tickets:focus { border-color:var(--attendance-orange); box-shadow:0 0 0 3px rgba(230,81,0,.11); }
        .attendance-gift { width:18px; height:18px; cursor:pointer; accent-color:var(--attendance-orange); }
        .attendance-comment { min-width:210px; height:34px; resize:vertical; border:1.5px solid #d7dee3; border-radius:6px; font-size:11px; }
        .attendance-comment:focus { border-color:var(--attendance-orange); box-shadow:0 0 0 3px rgba(230,81,0,.11); }
        .attendance-remove { width:30px; height:30px; padding:0; border:1px solid #e2b8b8; border-radius:7px; background:#fff; color:#ad3d3d; box-shadow:0 1px 3px rgba(0,0,0,.06); }
        .attendance-remove:hover { background:#fff1f1; color:#8f2929; }
        .attendance-empty { padding:40px 20px!important; color:#84949c!important; text-align:center; }
        .attendance-empty i { display:block; margin-bottom:7px; color:#b7c4ca; font-size:25px; }
        @media (max-width:767px) {
            .attendance-heading { flex-direction:column; }
            .attendance-summary { grid-template-columns:1fr; }
            .attendance-toolbar { grid-template-columns:1fr; }
            .attendance-search { max-width:none; }
            .attendance-actions { justify-content:flex-start; }
            .attendance-export { flex:1 1 auto; justify-content:center; }
            .attendance-content { padding:12px; }
        }
    </style>
    @endpush
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-12 attendance-heading">
            <div>
                <h2><i class="fa fa-calendar-check-o mr-2" style="color:#e67e22;"></i>Lista de asistencia</h2>
                <p>Registro de clientes presentes y control de beneficios de la Expo.</p>
                <ol class="breadcrumb mt-2 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('/flujo_de_venta/expo') }}">Expo</a></li>
                    <li class="breadcrumb-item active"><strong>Lista de asistencia</strong></li>
                </ol>
            </div>
            <a href="{{ url('/flujo_de_venta/expo') }}" class="btn btn-sm attendance-back"><i class="fa fa-arrow-left"></i>Volver a Expos</a>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show attendance-alert" role="alert">
                <i class="fa fa-check-circle mr-1"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show attendance-alert" role="alert">
                <i class="fa fa-exclamation-circle mr-1"></i>{{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="attendance-card">
            @if($expo)
                <div class="attendance-card-head">
                    <div>
                        <h5><i class="fa fa-calendar-check-o mr-2"></i>{{ $expo->nombre }}</h5>
                        <small>Expo #{{ $expo->id }} · {{ $expo->descripcion ?: 'Sin descripción registrada' }}</small>
                    </div>
                    <span class="attendance-state"><i class="fa fa-circle"></i>Expo activa</span>
                </div>
            @else
                <div class="attendance-card-head">
                    <div>
                        <h5><i class="fa fa-calendar-check-o mr-2"></i>Control de asistencia</h5>
                        <small>Seleccione una Expo activa para comenzar.</small>
                    </div>
                </div>
            @endif

            <div class="attendance-content">
                @if($expos->isEmpty())
                    <div class="attendance-empty">
                        <i class="fa fa-calendar-times-o"></i>
                        No hay exposiciones activas en este momento.
                    </div>
                @else
                    <div class="attendance-summary">
                        <div class="attendance-summary-item">
                            <label for="expo-asistencia">Exposición activa</label>
                            <select id="expo-asistencia" wire:model="expoId" class="form-control">
                                @foreach($expos as $item)
                                    <option value="{{ $item->id }}">{{ $item->nombre }} | {{ date('d/m/Y H:i', strtotime($item->fecha_inicio)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="attendance-summary-item">
                            <span>Inicio</span>
                            <strong>{{ date('d/m/Y H:i', strtotime($expo->fecha_inicio)) }}</strong>
                            <small>{{ $expo->fecha_fin ? 'Finaliza ' . date('d/m/Y H:i', strtotime($expo->fecha_fin)) : 'Sin fecha de cierre' }}</small>
                        </div>
                        <div class="attendance-summary-item">
                            <span>Clientes registrados</span>
                            <strong>{{ $asistentes->count() }}</strong>
                            <small>Asistentes confirmados en esta Expo</small>
                        </div>
                    </div>

                    <div class="attendance-toolbar">
                        <div class="attendance-search">
                            <label for="buscar-cliente">Registrar asistencia de cliente</label>
                            <div class="attendance-search-box">
                                <i class="fa fa-search"></i>
                                <input id="buscar-cliente" type="search" wire:model.debounce.300ms="busquedaCliente" class="form-control" placeholder="Buscar por nombre, RTN o código de cliente" autocomplete="off">
                            </div>
                            @error('busquedaCliente') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                            @if(trim($busquedaCliente) !== '' && mb_strlen(trim($busquedaCliente)) >= 2)
                                <div class="attendance-results">
                                    @forelse($clientesEncontrados as $cliente)
                                        <button type="button" wire:click="agregarCliente({{ $cliente->id }})" wire:loading.attr="disabled" class="attendance-result">
                                            <span><strong>{{ $cliente->nombre }}</strong><br><small>Cliente #{{ $cliente->id }} · RTN {{ $cliente->rtn }}</small></span>
                                            <span class="attendance-result-icon"><i class="fa fa-plus"></i></span>
                                        </button>
                                    @empty
                                        <div class="p-3 text-muted small">No hay clientes disponibles con ese criterio.</div>
                                    @endforelse
                                </div>
                            @endif
                        </div>

                        <div class="attendance-actions">
                            <button type="button" wire:click="descargarExcel" wire:loading.attr="disabled" class="btn btn-sm btn-outline-success attendance-export excel" title="Exportar asistencia a Excel"><i class="fa fa-file-excel-o"></i>Asistencia Excel</button>
                            <button type="button" wire:click="descargarPdf" wire:loading.attr="disabled" class="btn btn-sm btn-outline-danger attendance-export pdf" title="Exportar asistencia a PDF"><i class="fa fa-file-pdf-o"></i>Asistencia PDF</button>
                        </div>
                    </div>

                    <div class="attendance-section-head">
                        <h3><i class="fa fa-users mr-2" style="color:#e67e22;"></i>Clientes asistentes</h3>
                        <span class="attendance-count"><i class="fa fa-check"></i>{{ $asistentes->count() }} registrado(s)</span>
                    </div>
                    <div class="attendance-table-wrap">
                        <table class="table table-hover attendance-table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>RTN</th>
                                    <th>Teléfono</th>
                                    <th>Correo</th>
                                    <th>Fecha de asistencia</th>
                                    <th class="text-center">Tickets</th>
                                    <th class="text-center">Regalo</th>
                                    <th>Comentario</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($asistentes as $cliente)
                                    <tr wire:key="asistente-{{ $cliente->id }}">
                                        <td><span class="attendance-client">{{ $cliente->nombre }}</span><br><span class="attendance-client-id">Cliente #{{ $cliente->id }}</span></td>
                                        <td>{{ $cliente->rtn ?: 'Sin RTN' }}</td>
                                        <td class="attendance-contact">{{ $cliente->telefono_empresa ?: 'Sin teléfono' }}</td>
                                        <td class="attendance-contact">{{ $cliente->correo ?: 'Sin correo' }}</td>
                                        <td class="attendance-registered">{{ date('d/m/Y H:i', strtotime($cliente->registrado_at)) }}<br><small class="text-muted">Por {{ $cliente->registrado_por }}</small></td>
                                        <td class="text-center">
                                            <input type="number" min="0" step="1" value="{{ $cliente->tickets }}" wire:change="actualizarTickets({{ $cliente->id }}, $event.target.value)" class="form-control form-control-sm attendance-tickets" aria-label="Tickets de {{ $cliente->nombre }}">
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" id="regalo-asistente-{{ $cliente->id }}" wire:change="actualizarRegalo({{ $cliente->id }})" class="attendance-gift" title="Marcar si recibió regalo" aria-label="Regalo entregado a {{ $cliente->nombre }}" @checked($cliente->recibio_regalo)>
                                        </td>
                                        <td>
                                            <textarea maxlength="1000" wire:change="actualizarComentario({{ $cliente->id }}, $event.target.value)" class="form-control form-control-sm attendance-comment" placeholder="Agregar comentario" aria-label="Comentario de {{ $cliente->nombre }}">{{ $cliente->comentario }}</textarea>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" wire:click="eliminarCliente({{ $cliente->id }})" wire:loading.attr="disabled" onclick="return confirm('¿Eliminar este cliente de la asistencia?')" class="attendance-remove" title="Eliminar de asistencia" aria-label="Eliminar de asistencia"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="attendance-empty"><i class="fa fa-user-plus"></i>Aún no hay clientes registrados en esta Expo.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

