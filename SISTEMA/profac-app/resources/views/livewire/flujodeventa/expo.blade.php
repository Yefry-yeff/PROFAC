<div>
    @push('styles')
    <style>
        .expo-panel {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,.07);
            border: 1px solid #e8eaef;
        }
        .expo-panel .ibox-title {
            min-height: auto;
            padding: 14px 20px;
            border: 0;
            background: linear-gradient(135deg,#e65100 0%,#f9a826 100%);
        }
        .expo-panel .ibox-title h5 {
            color: #fff;
            margin: 0;
            font-size: 15px;
            font-weight: 700;
        }
        .expo-panel .ibox-title small {
            display: block;
            margin-top: 3px;
            color: rgba(255,255,255,.84);
            font-size: 11px;
        }
        .expo-panel .ibox-content { padding: 20px 22px; }
        .expo-section {
            background: #fafbfc;
            border: 1px solid #e8eaef;
            border-radius: 10px;
            padding: 16px 18px 4px;
            margin-bottom: 16px;
        }
        .expo-section-title {
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0 0 14px;
            padding-bottom: 9px;
            border-bottom: 2px solid #edf0f4;
            color: #546e7a;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .expo-section-title i { color: #e65100; font-size: 13px; }
        .expo-panel label {
            display: block;
            margin-bottom: 4px;
            color: #4a5568;
            font-size: 12px;
            font-weight: 700;
        }
        .expo-panel .form-control {
            min-height: 36px;
            border: 1.5px solid #dde2ec;
            border-radius: 8px;
            background: #fff;
            color: #2d3748;
            font-size: 13px;
            transition: border .15s, box-shadow .15s;
        }
        .expo-panel .form-control:focus {
            border-color: #e65100;
            box-shadow: 0 0 0 3px rgba(230,81,0,.11);
        }
        .expo-panel .form-control[readonly] {
            background: #eef1f5;
            color: #607d8b;
            cursor: not-allowed;
        }
        .expo-locked-hint { color: #78909c; font-size: 10px; margin-top: 4px; }
        .expo-panel select.form-control:not([multiple]) {
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 20px 20px;
            padding-right: 36px;
        }
        .expo-checklist {
            max-height: 230px;
            overflow-y: auto;
            border: 1.5px solid #dde2ec;
            border-radius: 8px;
            background: #fff;
        }
        .expo-check-all,
        .expo-check-item label {
            display: flex;
            align-items: center;
            gap: 9px;
            width: 100%;
            margin: 0;
            padding: 8px 11px;
            cursor: pointer;
            font-size: 12px;
        }
        .expo-check-all {
            position: sticky;
            top: 0;
            z-index: 2;
            border-bottom: 1px solid #f0d4bd;
            background: #fff5eb;
            color: #bf4b00;
            font-weight: 800;
        }
        .expo-check-item { border-bottom: 1px solid #f0f3f6; }
        .expo-check-item:last-child { border-bottom: 0; }
        .expo-check-item:hover { background: #fffbf7; }
        .expo-checklist input[type="checkbox"] {
            flex: 0 0 auto;
            width: 15px;
            height: 15px;
            margin: 0;
            accent-color: #e65100;
        }
        .expo-check-summary { color: #78909c; font-size: 10px; font-weight: 700; }
        .expo-hint { color: #90a4ae; font-size: 11px; margin-top: 5px; }
        .expo-user-search { position: relative; }
        .expo-user-results {
            position: absolute;
            z-index: 20;
            top: calc(100% + 3px);
            left: 0;
            right: 0;
            max-height: 210px;
            overflow-y: auto;
            border: 1px solid #d9dee7;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 8px 20px rgba(44,62,80,.16);
        }
        button.expo-user-result {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 9px 12px;
            border: 0;
            border-bottom: 1px solid #f0f3f6;
            background: #fff !important;
            color: #37474f !important;
            text-align: left;
        }
        button.expo-user-result:hover { background: #fff5eb !important; }
        .expo-user-result i { color: #e65100; }
        .expo-user-result span { min-width: 0; }
        .expo-user-result strong,
        .expo-user-result small { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .expo-user-result small { color: #90a4ae; }
        .expo-users-table { margin: 0; }
        .expo-users-table th {
            padding: 8px 10px;
            background: #f8fafc;
            color: #64748b;
            font-size: 10px;
            text-transform: uppercase;
        }
        .expo-users-table td { padding: 8px 10px; vertical-align: middle; font-size: 12px; }
        .expo-version-note {
            background: #e3f2fd;
            border: 1px solid #90caf9;
            border-left: 4px solid #1a73e8;
            border-radius: 8px;
            padding: 10px 14px;
            color: #1565c0;
            font-size: 12px;
            margin-bottom: 16px;
        }
        .expo-discount-wrap {
            border: 1px solid #e8eaef;
            border-radius: 9px;
            overflow: hidden;
            background: #fff;
        }
        .expo-discount-table { margin: 0; }
        .expo-discount-table thead th,
        .expo-history-table thead th {
            padding: 9px 12px;
            border-bottom: 2px solid #e8edf5;
            background: #f8fafc;
            color: #64748b;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .45px;
            white-space: nowrap;
        }
        .expo-discount-table tbody td { padding: 7px 10px; vertical-align: top; border-color: #f0f3f6; }
        .expo-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding-top: 4px;
        }
        .expo-save-btn {
            border: 0 !important;
            border-radius: 8px !important;
            padding: 9px 20px !important;
            background: linear-gradient(135deg,#e65100,#f9a826) !important;
            box-shadow: 0 3px 10px rgba(230,81,0,.25);
            color: #fff !important;
            font-weight: 700;
        }
        .expo-history-table { margin-bottom: 0; }
        .expo-history-table tbody td {
            padding: 10px 12px;
            border-color: #f0f3f6;
            vertical-align: middle;
            color: #455a64;
            font-size: 12px;
        }
        .expo-history-table tbody tr:hover { background: #fffbf7; }
        .expo-history-table tbody tr.expo-clickable { cursor: pointer; }
        .expo-name { color: #2c3e50; font-size: 13px; font-weight: 800; }
        .expo-version { color: #90a4ae; font-size: 10px; }
        .expo-state {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border-radius: 20px;
            padding: 3px 9px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .expo-state-active { background: #e8f5e9; color: #2e7d32; }
        .expo-state-inactive { background: #eceff1; color: #607d8b; }
        .expo-config-counts { display: flex; flex-wrap: wrap; gap: 4px; }
        .expo-row-actions { display: flex; justify-content: center; gap: 5px; }
        .expo-config-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border-radius: 6px;
            padding: 3px 7px;
            background: #f1f5f9;
            color: #546e7a;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }
        .expo-empty { padding: 38px 20px; text-align: center; }
        .expo-empty i { display: block; margin-bottom: 10px; color: #cfd8dc; font-size: 36px; }
        .expo-empty strong { display: block; color: #546e7a; font-size: 13px; }
        .expo-empty span { color: #90a4ae; font-size: 11px; }
        .expo-new-btn {
            border: 1px solid rgba(255,255,255,.55) !important;
            border-radius: 7px !important;
            background: rgba(255,255,255,.18) !important;
            color: #fff !important;
            font-size: 11px !important;
            font-weight: 700 !important;
        }
        .expo-detail-backdrop { position:fixed; inset:0; z-index:2050; display:flex; align-items:center; justify-content:center; padding:20px; background:rgba(27,39,51,.55); }
        .expo-detail-modal { width:min(900px, 100%); max-height:calc(100vh - 40px); overflow-y:auto; border-radius:10px; background:#fff; box-shadow:0 20px 55px rgba(0,0,0,.28); }
        .expo-detail-head { display:flex; align-items:flex-start; justify-content:space-between; gap:14px; padding:17px 20px; background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; }
        .expo-detail-head h4 { margin:0; color:#fff; font-size:17px; font-weight:800; }
        .expo-detail-head small { color:rgba(255,255,255,.85); }
        .expo-detail-close { border:0; background:transparent!important; color:#fff!important; font-size:22px; line-height:1; }
        .expo-detail-body { padding:20px; }
        .expo-detail-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px 20px; }
        .expo-detail-field label { margin:0 0 2px; color:#90a4ae; font-size:10px; font-weight:800; text-transform:uppercase; }
        .expo-detail-field div { color:#37474f; font-size:13px; }
        .expo-detail-section { margin-top:18px; padding-top:14px; border-top:1px solid #edf0f4; }
        .expo-detail-section h6 { margin:0 0 9px; color:#546e7a; font-size:11px; font-weight:800; text-transform:uppercase; }
        .expo-detail-tags { display:flex; flex-wrap:wrap; gap:6px; }
        .expo-detail-tag { padding:5px 9px; border-radius:6px; background:#f1f5f9; color:#455a64; font-size:11px; }
        .expo-detail-users { width:100%; margin:0; }
        .expo-detail-users td { padding:6px 8px; border-top:1px solid #f0f3f6; font-size:12px; }
        @media (max-width: 767px) {
            .expo-panel .ibox-content { padding: 14px; }
            .expo-section { padding: 14px 12px 2px; }
            .expo-actions { flex-direction: column-reverse; }
            .expo-actions .btn { width: 100%; }
            .expo-panel .ibox-title { padding: 12px 14px; }
            .expo-history-table { min-width: 880px; }
        }
    </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2><i class="fa fa-calendar-check-o mr-2" style="color:#e65100;"></i>Configuración de Expos</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item active">
                    <strong>Expo</strong>
                </li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

            @if ($mostrarFormulario)
                <div class="ibox expo-panel">
                    <div class="ibox-title">
                        <h5>
                            <i class="fa {{ $expoEditandoId ? 'fa-pencil' : ($expoDuplicandoId ? 'fa-clone' : 'fa-plus-circle') }} mr-2"></i>
                            {{ $expoEditandoId ? 'Editar Expo' : ($expoDuplicandoId ? 'Duplicar Expo' : 'Nueva Expo') }}
                        </h5>
                        <small>{{ $expoEditandoId ? 'Actualice la configuración comercial y los usuarios autorizados.' : ($expoDuplicandoId ? 'Revise los datos copiados y defina la vigencia de la nueva Expo.' : 'Defina la vigencia y las condiciones comerciales de la Expo.') }}</small>
                    </div>
                    <div class="ibox-content">
                        @if ($expoEditandoId)
                            <div class="expo-version-note">
                                <i class="fa fa-lock mr-1"></i>
                                El nombre y la fecha de inicio no pueden modificarse. Los demás campos se actualizarán en esta Expo.
                            </div>
                        @elseif ($expoDuplicandoId)
                            <div class="expo-version-note">
                                <i class="fa fa-clone mr-1"></i>
                                Se copiaron el alcance, los usuarios y descuentos. Al guardar se creará una Expo nueva.
                            </div>
                        @endif

                        <form wire:submit.prevent="guardar">
                            <div class="expo-section">
                            <div class="expo-section-title"><i class="fa fa-info-circle"></i>Información general y vigencia</div>
                            <div class="row">
                                <div class="form-group col-md-8">
                                    <label>Nombre <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.defer="nombre" class="form-control @error('nombre') is-invalid @enderror" maxlength="150" {{ $expoEditandoId ? 'readonly' : '' }}>
                                    @if ($expoEditandoId)<div class="expo-locked-hint"><i class="fa fa-lock mr-1"></i>Campo no editable.</div>@endif
                                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Estado <span class="text-danger">*</span></label>
                                    <select wire:model.defer="estado" class="form-control @error('estado') is-invalid @enderror">
                                        <option value="Inactivo">Inactiva</option>
                                        <option value="Activo">Activa</option>
                                    </select>
                                    @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-12">
                                    <label>Descripción</label>
                                    <textarea wire:model.defer="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="3"></textarea>
                                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Inicio de vigencia <span class="text-danger">*</span></label>
                                    <input type="datetime-local" wire:model.defer="fechaInicio" class="form-control @error('fechaInicio') is-invalid @enderror" {{ $expoEditandoId ? 'readonly' : '' }}>
                                    @if ($expoEditandoId)<div class="expo-locked-hint"><i class="fa fa-lock mr-1"></i>Campo no editable.</div>@endif
                                    @error('fechaInicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Finalización</label>
                                    <input type="datetime-local" wire:model.defer="fechaFin" class="form-control @error('fechaFin') is-invalid @enderror">
                                    @error('fechaFin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            </div>

                            <div class="expo-section">
                            <div class="expo-section-title"><i class="fa fa-sliders"></i>Alcance comercial</div>
                            @php
                                $idsBodegas = $bodegas->pluck('id')->map(fn ($id) => (string) $id)->all();
                                $idsEscalas = $escalas->pluck('id')->map(fn ($id) => (string) $id)->all();
                                $todasBodegas = count($idsBodegas) > 0 && empty(array_diff($idsBodegas, array_map('strval', $bodegasSeleccionadas)));
                                $todasEscalas = count($idsEscalas) > 0 && empty(array_diff($idsEscalas, array_map('strval', $escalasSeleccionadas)));
                            @endphp
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Bodegas permitidas <span class="text-danger">*</span></label>
                                    <div class="expo-checklist @error('bodegasSeleccionadas') border-danger @enderror">
                                        <label class="expo-check-all">
                                            <input type="checkbox" wire:click="alternarTodasBodegas" {{ $todasBodegas ? 'checked' : '' }}>
                                            <span>Seleccionar todas</span>
                                            <span class="expo-check-summary ml-auto">{{ count($bodegasSeleccionadas) }}/{{ count($bodegas) }}</span>
                                        </label>
                                        @foreach ($bodegas as $bodega)
                                            <div class="expo-check-item" wire:key="expo-bodega-{{ $bodega->id }}">
                                                <label for="expo-bodega-{{ $bodega->id }}">
                                                    <input id="expo-bodega-{{ $bodega->id }}" type="checkbox" value="{{ $bodega->id }}" wire:model.defer="bodegasSeleccionadas">
                                                    <span>{{ $bodega->nombre }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="expo-hint"><i class="fa fa-check-square-o mr-1"></i>Marque una, varias o todas las bodegas.</div>
                                    @error('bodegasSeleccionadas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Escalas permitidas <span class="text-danger">*</span></label>
                                    <div class="expo-checklist @error('escalasSeleccionadas') border-danger @enderror">
                                        <label class="expo-check-all">
                                            <input type="checkbox" wire:click="alternarTodasEscalas" {{ $todasEscalas ? 'checked' : '' }}>
                                            <span>Seleccionar todas</span>
                                            <span class="expo-check-summary ml-auto">{{ count($escalasSeleccionadas) }}/{{ count($escalas) }}</span>
                                        </label>
                                        @foreach ($escalas as $escala)
                                            <div class="expo-check-item" wire:key="expo-escala-{{ $escala->id }}">
                                                <label for="expo-escala-{{ $escala->id }}">
                                                    <input id="expo-escala-{{ $escala->id }}" type="checkbox" value="{{ $escala->id }}" wire:model.defer="escalasSeleccionadas">
                                                    <span>{{ $escala->nombre }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="expo-hint"><i class="fa fa-check-square-o mr-1"></i>Marque una, varias o todas las escalas permitidas.</div>
                                    @error('escalasSeleccionadas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            </div>

                            <div class="expo-section">
                                <div class="expo-section-title"><i class="fa fa-users"></i>Usuarios autorizados</div>
                                <p class="expo-hint mb-2">Solo los usuarios agregados en esta tabla podrán ver y abrir el botón <strong>Oferta de Expo</strong>.</p>
                                <div class="expo-user-search mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-search"></i></span></div>
                                        <input type="search" wire:model.debounce.300ms="busquedaUsuario" class="form-control" placeholder="Buscar usuario por nombre o correo..." autocomplete="off">
                                    </div>
                                    @if (mb_strlen(trim($busquedaUsuario)) >= 2)
                                        <div class="expo-user-results">
                                            @forelse ($usuariosEncontrados as $usuario)
                                                <button type="button" wire:click="agregarUsuario({{ $usuario->id }})" class="expo-user-result" wire:key="expo-usuario-resultado-{{ $usuario->id }}">
                                                    <i class="fa fa-user-plus"></i>
                                                    <span><strong>{{ $usuario->name }}</strong><small>{{ $usuario->email }}</small></span>
                                                </button>
                                            @empty
                                                <div class="p-3 text-center text-muted small">No se encontraron usuarios activos.</div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                                <div class="expo-discount-wrap @error('usuariosSeleccionados') border-danger @enderror">
                                    <table class="table table-sm expo-users-table">
                                        <thead><tr><th>Usuario</th><th>Correo</th><th style="width:70px;" class="text-center">Acción</th></tr></thead>
                                        <tbody>
                                            @forelse ($usuariosAgregados as $usuario)
                                                <tr wire:key="expo-usuario-agregado-{{ $usuario->id }}">
                                                    <td><i class="fa fa-user-circle-o mr-2 text-muted"></i><strong>{{ $usuario->name }}</strong></td>
                                                    <td class="text-muted">{{ $usuario->email }}</td>
                                                    <td class="text-center">
                                                        <button type="button" wire:click="eliminarUsuario({{ $usuario->id }})" class="btn btn-xs btn-white" title="Quitar usuario">
                                                            <i class="fa fa-trash text-danger"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="text-center text-muted py-3">Busque y agregue los usuarios que podrán usar la Expo.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @error('usuariosSeleccionados') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="expo-section">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:8px;">
                                <div class="expo-section-title mb-0" style="flex:1; min-width:200px;"><i class="fa fa-percent"></i>Reglas de descuento</div>
                                <button type="button" wire:click="agregarDescuento" class="btn btn-sm btn-outline-warning" style="border-radius:7px; font-weight:700; font-size:11px;">
                                    <i class="fa fa-plus mr-1"></i> Agregar regla
                                </button>
                            </div>
                            <div class="table-responsive expo-discount-wrap mb-3">
                                <table class="table table-sm expo-discount-table">
                                    <thead>
                                        <tr><th>Venta mínima (L.)</th><th>Descuento (%)</th><th style="width:60px;">Acción</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($descuentos as $indice => $regla)
                                            <tr wire:key="expo-descuento-{{ $indice }}">
                                                <td>
                                                    <input type="text" inputmode="decimal"
                                                           wire:model.defer="descuentos.{{ $indice }}.venta_minima"
                                                           class="form-control form-control-sm expo-money-input"
                                                           placeholder="0.00" autocomplete="off"
                                                           x-data="{}"
                                                           x-init="$nextTick(() => { const amount = Number($el.value.replace(/,/g, '')); if (Number.isFinite(amount)) $el.value = amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); })"
                                                           x-on:focus="$el.value = $el.value.replace(/,/g, '')"
                                                           x-on:input="$el.value = $el.value.replace(/,/g, '').replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')"
                                                           x-on:blur="const amount = Number($el.value.replace(/,/g, '')); $el.value = Number.isFinite(amount) ? amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : ''">
                                                    @error('descuentos.'.$indice.'.venta_minima') <small class="text-danger">{{ $message }}</small> @enderror
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" max="100" wire:model.defer="descuentos.{{ $indice }}.porcentaje_descuento" class="form-control form-control-sm">
                                                    @error('descuentos.'.$indice.'.porcentaje_descuento') <small class="text-danger">{{ $message }}</small> @enderror
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" wire:click="eliminarDescuento({{ $indice }})" class="btn btn-xs btn-white" title="Eliminar regla">
                                                        <i class="fa fa-trash text-danger"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted">Sin reglas de descuento.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            </div>


                            <div class="expo-actions">
                                <button type="button" wire:click="cancelar" class="btn btn-default" style="border-radius:8px; font-weight:700;">
                                    <i class="fa fa-times mr-1"></i> Cancelar
                                </button>
                                <button type="submit" class="btn expo-save-btn" wire:loading.attr="disabled">
                                    <i class="fa fa-save mr-1"></i> Guardar configuración
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="ibox expo-panel">
                <div class="ibox-title">
                    <h5><i class="fa fa-history mr-2"></i>Historial de Expos</h5>
                    <small>{{ count($expos) }} configuración(es) registrada(s)</small>
                    <div class="ibox-tools">
                        @unless ($mostrarFormulario)
                            <button type="button" wire:click="nueva" class="btn btn-sm expo-new-btn">
                                <i class="fa fa-plus mr-1"></i> Nueva Expo
                            </button>
                        @endunless
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-hover expo-history-table">
                            <thead>
                                <tr>
                                    <th>Expo</th><th>Estado</th><th>Vigencia</th><th>Creación</th><th>Última modificación</th><th>Configuración</th><th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($expos as $expo)
                                    @php
                                        $totalBodegas = DB::table('expo_bodega')->where('expo_id', $expo->id)->count();
                                        $totalEscalas = DB::table('expo_escala')->where('expo_id', $expo->id)->count();
                                        $totalUsuarios = DB::table('expo_usuario')->where('expo_id', $expo->id)->count();
                                        $totalDescuentos = DB::table('expo_descuento')->where('expo_id', $expo->id)->count();
                                        $expoFinalizada = $expo->fecha_fin && strtotime($expo->fecha_fin) <= time();
                                    @endphp
                                    <tr class="expo-clickable" wire:click="verDetalle({{ $expo->id }})" title="Ver detalle completo">
                                        <td><span class="expo-name">{{ $expo->nombre }}</span><br><span class="expo-version">Versión #{{ $expo->id }}</span></td>
                                        <td>
                                            <span class="expo-state {{ $expo->estado === 'Activo' ? 'expo-state-active' : 'expo-state-inactive' }}">
                                                <i class="fa {{ $expo->estado === 'Activo' ? 'fa-check-circle' : 'fa-pause-circle' }}"></i>{{ $expo->estado }}
                                            </span>
                                        </td>
                                        <td><strong>{{ date('d/m/Y H:i', strtotime($expo->fecha_inicio)) }}</strong><br><small class="text-muted">Hasta {{ $expo->fecha_fin ? date('d/m/Y H:i', strtotime($expo->fecha_fin)) : 'sin fecha final' }}</small></td>
                                        <td><i class="fa fa-user-circle-o mr-1 text-muted"></i>{{ $expo->creado_por }}<br><small class="text-muted">{{ date('d/m/Y H:i', strtotime($expo->created_at)) }}</small></td>
                                        <td><i class="fa fa-pencil-square-o mr-1 text-muted"></i>{{ $expo->modificado_por }}<br><small class="text-muted">{{ date('d/m/Y H:i', strtotime($expo->updated_at)) }}</small></td>
                                        <td>
                                            <div class="expo-config-counts">
                                                <span class="expo-config-chip"><i class="fa fa-archive"></i>{{ $totalBodegas }} bodega(s)</span>
                                                <span class="expo-config-chip"><i class="fa fa-tags"></i>{{ $totalEscalas }} escala(s)</span>
                                                <span class="expo-config-chip"><i class="fa fa-users"></i>{{ $totalUsuarios }} usuario(s)</span>
                                                <span class="expo-config-chip"><i class="fa fa-percent"></i>{{ $totalDescuentos }} regla(s)</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="expo-row-actions">
                                                @unless ($expoFinalizada)
                                                    <button type="button" wire:click.stop="editar({{ $expo->id }})" class="btn btn-xs btn-white" title="Editar Expo">
                                                        <i class="fa fa-pencil text-primary"></i>
                                                    </button>
                                                @endunless
                                                <button type="button" wire:click.stop="duplicar({{ $expo->id }})" class="btn btn-xs btn-white" title="Duplicar Expo">
                                                    <i class="fa fa-clone" style="color:#e65100;"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="expo-empty"><i class="fa fa-calendar-o"></i><strong>No hay Expos configuradas</strong><span>Cree la primera configuración para habilitar ofertas de Expo.</span></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($mostrarDetalle && !empty($expoDetalle['expo']))
                @php $detalle = $expoDetalle['expo']; @endphp
                <div class="expo-detail-backdrop" wire:click.self="cerrarDetalle">
                    <div class="expo-detail-modal">
                        <div class="expo-detail-head">
                            <div><h4><i class="fa fa-calendar-check-o mr-2"></i>{{ $detalle['nombre'] }}</h4><small>Expo #{{ $detalle['id'] }}</small></div>
                            <button type="button" wire:click="cerrarDetalle" class="expo-detail-close" title="Cerrar"><i class="fa fa-times"></i></button>
                        </div>
                        <div class="expo-detail-body">
                            <div class="expo-detail-grid">
                                <div class="expo-detail-field"><label>Estado</label><div>{{ $detalle['estado'] }}</div></div>
                                <div class="expo-detail-field"><label>Vigencia</label><div>{{ date('d/m/Y H:i', strtotime($detalle['fecha_inicio'])) }} a {{ $detalle['fecha_fin'] ? date('d/m/Y H:i', strtotime($detalle['fecha_fin'])) : 'sin fecha final' }}</div></div>
                                <div class="expo-detail-field"><label>Creada por</label><div>{{ $detalle['creado_por'] }} · {{ date('d/m/Y H:i', strtotime($detalle['created_at'])) }}</div></div>
                                <div class="expo-detail-field"><label>Última modificación</label><div>{{ $detalle['modificado_por'] }} · {{ date('d/m/Y H:i', strtotime($detalle['updated_at'])) }}</div></div>
                                <div class="expo-detail-field" style="grid-column:1/-1;"><label>Descripción</label><div>{{ $detalle['descripcion'] ?: 'Sin descripción.' }}</div></div>
                            </div>
                            <div class="expo-detail-section"><h6><i class="fa fa-archive mr-1"></i>Bodegas</h6><div class="expo-detail-tags">@forelse($expoDetalle['bodegas'] as $item)<span class="expo-detail-tag">{{ $item }}</span>@empty<span class="text-muted small">Sin bodegas.</span>@endforelse</div></div>
                            <div class="expo-detail-section"><h6><i class="fa fa-tags mr-1"></i>Escalas</h6><div class="expo-detail-tags">@forelse($expoDetalle['escalas'] as $item)<span class="expo-detail-tag">{{ $item }}</span>@empty<span class="text-muted small">Sin escalas.</span>@endforelse</div></div>
                            <div class="expo-detail-section"><h6><i class="fa fa-percent mr-1"></i>Reglas de descuento</h6><div class="expo-detail-tags">@forelse($expoDetalle['descuentos'] as $regla)<span class="expo-detail-tag">Desde L {{ number_format($regla['venta_minima'], 2) }}: <strong>{{ number_format($regla['porcentaje_descuento'], 2) }}%</strong></span>@empty<span class="text-muted small">Sin descuentos.</span>@endforelse</div></div>
                            <div class="expo-detail-section"><h6><i class="fa fa-users mr-1"></i>Usuarios autorizados</h6><table class="expo-detail-users"><tbody>@forelse($expoDetalle['usuarios'] as $usuario)<tr><td><strong>{{ $usuario['name'] }}</strong></td><td class="text-muted">{{ $usuario['email'] }}</td></tr>@empty<tr><td class="text-muted">Sin usuarios autorizados.</td></tr>@endforelse</tbody></table></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
