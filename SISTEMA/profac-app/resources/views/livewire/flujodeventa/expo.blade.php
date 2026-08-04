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
        .expo-panel select.form-control:not([multiple]) {
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 20px 20px;
            padding-right: 36px;
        }
        .expo-panel select[multiple] {
            min-height: 178px;
            padding: 5px;
            background-image: none;
        }
        .expo-panel select[multiple] option {
            border-radius: 5px;
            padding: 6px 8px;
            margin-bottom: 2px;
        }
        .expo-panel select[multiple] option:checked {
            background: #fff3e0 linear-gradient(0deg,#fff3e0 0%,#fff3e0 100%);
            color: #bf360c;
            font-weight: 700;
        }
        .expo-hint { color: #90a4ae; font-size: 11px; margin-top: 5px; }
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
                        <h5><i class="fa {{ $expoEditandoId ? 'fa-clone' : 'fa-plus-circle' }} mr-2"></i>{{ $expoEditandoId ? 'Crear nueva versión de la Expo' : 'Nueva Expo' }}</h5>
                        <small>{{ $expoEditandoId ? 'La configuración anterior permanecerá disponible en el historial.' : 'Defina la vigencia y las condiciones comerciales de la Expo.' }}</small>
                    </div>
                    <div class="ibox-content">
                        @if ($expoEditandoId)
                            <div class="expo-version-note">
                                <i class="fa fa-info-circle mr-1"></i>
                                La versión anterior conservará sus bodegas, escalas y descuentos para auditoría.
                            </div>
                        @endif

                        <form wire:submit.prevent="guardar">
                            <div class="expo-section">
                            <div class="expo-section-title"><i class="fa fa-info-circle"></i>Información general y vigencia</div>
                            <div class="row">
                                <div class="form-group col-md-8">
                                    <label>Nombre <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.defer="nombre" class="form-control @error('nombre') is-invalid @enderror" maxlength="150">
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
                                    <input type="datetime-local" wire:model.defer="fechaInicio" class="form-control @error('fechaInicio') is-invalid @enderror">
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
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Bodegas permitidas <span class="text-danger">*</span></label>
                                    <select wire:model.defer="bodegasSeleccionadas" class="form-control @error('bodegasSeleccionadas') is-invalid @enderror" multiple size="7">
                                        @foreach ($bodegas as $bodega)
                                            <option value="{{ $bodega->id }}">{{ $bodega->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <div class="expo-hint"><i class="fa fa-info-circle mr-1"></i>Use Ctrl para seleccionar varias opciones.</div>
                                    @error('bodegasSeleccionadas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Escalas permitidas <span class="text-danger">*</span></label>
                                    <select wire:model.defer="escalasSeleccionadas" class="form-control @error('escalasSeleccionadas') is-invalid @enderror" multiple size="7">
                                        @foreach ($escalas as $escala)
                                            <option value="{{ $escala->id }}">{{ $escala->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <div class="expo-hint"><i class="fa fa-info-circle mr-1"></i>Las ofertas mostrarán únicamente estas escalas.</div>
                                    @error('escalasSeleccionadas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
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
                                                    <input type="number" step="0.01" min="0" wire:model.defer="descuentos.{{ $indice }}.venta_minima" class="form-control form-control-sm">
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
                                        $totalDescuentos = DB::table('expo_descuento')->where('expo_id', $expo->id)->count();
                                    @endphp
                                    <tr>
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
                                                <span class="expo-config-chip"><i class="fa fa-percent"></i>{{ $totalDescuentos }} regla(s)</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" wire:click="editar({{ $expo->id }})" class="btn btn-xs btn-white" title="Crear nueva versión">
                                                <i class="fa fa-clone text-primary"></i>
                                            </button>
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
        </div>
    </div>
