<div>
@push('styles')
<style>
.ci-hero {
    background: linear-gradient(135deg,#1a202c 0%,#2d3748 60%,#4a5568 100%);
    border-radius: 14px; padding: 24px 28px; margin-bottom: 20px;
    color: #fff; display: flex; align-items: center; gap: 18px;
    box-shadow: 0 6px 24px rgba(0,0,0,.16);
}
.ci-hero-icon {
    width: 52px; height: 52px; border-radius: 12px;
    background: rgba(243,156,18,.2); border: 2px solid rgba(243,156,18,.4);
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: #f39c12; flex-shrink: 0;
}
.ci-hero-body h3 { margin: 0; font-size: 17px; font-weight: 800; }
.ci-hero-body p  { margin: 4px 0 0; font-size: 12px; color: rgba(255,255,255,.6); }

.ci-panel {
    background: #fff; border-radius: 14px;
    box-shadow: 0 4px 20px rgba(0,0,0,.07);
    border: 1px solid #e2e8f0; overflow: hidden;
}
.ci-panel-header {
    background: linear-gradient(135deg,#2d3748 0%,#4a5568 100%);
    padding: 14px 20px; display: flex; align-items: center;
    justify-content: space-between; color: #fff;
}
.ci-panel-header h6 { margin: 0; font-size: 14px; font-weight: 800; }
.ci-panel-body { padding: 20px; }

.ci-table { width: 100%; border-collapse: collapse; }
.ci-table th {
    background: #f5f7fb; color: #4a5568; font-size: 11px;
    font-weight: 800; text-transform: uppercase; letter-spacing: .4px;
    padding: 10px 14px; border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}
.ci-table td { padding: 10px 14px; border-bottom: 1px solid #f0f4f8; font-size: 13px; vertical-align: middle; }
.ci-table tr:last-child td { border-bottom: none; }
.ci-table tr:nth-child(even) td { background: #fafbfc; }

.ci-badge-activo   { background: #d1fae5; color: #065f46; border-radius: 20px; padding: 2px 10px; font-size: 11px; font-weight: 700; }
.ci-badge-inactivo { background: #fee2e2; color: #991b1b; border-radius: 20px; padding: 2px 10px; font-size: 11px; font-weight: 700; }

.ci-btn-add {
    background: linear-gradient(135deg,#f39c12,#e67e22) !important;
    color: #fff !important; border: none !important; border-radius: 9px;
    padding: 8px 18px; font-size: 13px; font-weight: 700; cursor: pointer;
    display: inline-flex; align-items: center; gap: 7px;
}

.ci-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    z-index: 9998; display: flex; align-items: center; justify-content: center;
}
.ci-modal-box {
    background: #fff; border-radius: 16px; width: 100%; max-width: 540px;
    padding: 28px 32px; box-shadow: 0 20px 60px rgba(0,0,0,.25);
    z-index: 9999; position: relative;
}
.ci-modal-title { font-size: 16px; font-weight: 800; color: #1a202c; margin-bottom: 20px; }
.ci-form-group  { margin-bottom: 16px; }
.ci-form-group label { font-size: 11px; font-weight: 700; color: #718096; text-transform: uppercase; letter-spacing: .4px; display: block; margin-bottom: 5px; }
.ci-form-group .form-control { border: 1.5px solid #dde2ec; border-radius: 8px; font-size: 13px; transition: border-color .18s, box-shadow .18s; }
.ci-form-group .form-control:focus { border-color: #f39c12; box-shadow: 0 0 0 3px rgba(243,156,18,.15); outline: none; }

.ci-alert-ok   { background: #d1fae5; border-left: 4px solid #059669; border-radius: 6px; padding: 10px 14px; font-size: 13px; color: #065f46; margin-bottom: 14px; }
.ci-alert-err  { background: #fee2e2; border-left: 4px solid #dc2626; border-radius: 6px; padding: 10px 14px; font-size: 13px; color: #991b1b; margin-bottom: 14px; }
.ci-info-box   { background: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 6px; padding: 10px 14px; font-size: 12px; color: #1e40af; margin-bottom: 18px; }
</style>
@endpush

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2><i class="fa fa-percent text-warning"></i> Configuración de Intereses Moratorios</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
            <li class="breadcrumb-item">Configuración</li>
            <li class="breadcrumb-item active"><strong>Intereses Moratorios</strong></li>
        </ol>
    </div>
</div>

<div class="wrapper wrapper-content pb-4">

    {{-- Hero --}}
    <div class="ci-hero">
        <div class="ci-hero-icon"><i class="fa fa-percent"></i></div>
        <div class="ci-hero-body">
            <h3>Intereses por Mora</h3>
            <p>Administre las tasas de interés aplicables a facturas vencidas. La tasa activa vigente se aplica automáticamente en todo el sistema.</p>
        </div>
    </div>

    {{-- Alertas --}}
    @if($mensajeExito)
        <div class="ci-alert-ok"><i class="fa fa-check-circle mr-1"></i> {{ $mensajeExito }}</div>
    @endif
    @if($mensajeError)
        <div class="ci-alert-err"><i class="fa fa-times-circle mr-1"></i> {{ $mensajeError }}</div>
    @endif

    {{-- Info --}}
    <div class="ci-info-box">
        <i class="fa fa-info-circle mr-1"></i>
        <strong>Principio de integridad histórica:</strong> Las configuraciones utilizadas no pueden eliminarse ni modificarse en su tasa. Solo pueden inactivarse. Cree una nueva configuración para cambiar la tasa vigente.
    </div>

    {{-- Panel principal --}}
    <div class="ci-panel">
        <div class="ci-panel-header">
            <h6><i class="fa fa-list mr-2"></i> Configuraciones Registradas</h6>
            <button class="ci-btn-add" wire:click="abrirCrear">
                <i class="fa fa-plus"></i> Nueva Configuración
            </button>
        </div>
        <div class="ci-panel-body">
            <table class="ci-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tasa Mensual</th>
                        <th>Estado</th>
                        <th>Fecha Vigencia</th>
                        <th>Fin Vigencia</th>
                        <th>Observaciones</th>
                        <th>Creado Por</th>
                        <th>Última Actualización</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($configuraciones as $cfg)
                    <tr>
                        <td>{{ $cfg['id'] }}</td>
                        <td>
                            <strong style="font-size:15px;color:#c05621;">{{ number_format($cfg['tasa_mensual'], 4) }}%</strong>
                            <span style="font-size:10px;color:#a0aec0;display:block;">mensual</span>
                        </td>
                        <td>
                            @if($cfg['estado'])
                                <span class="ci-badge-activo"><i class="fa fa-check mr-1"></i> Activo</span>
                            @else
                                <span class="ci-badge-inactivo"><i class="fa fa-times mr-1"></i> Inactivo</span>
                            @endif
                        </td>
                        <td>{{ $cfg['fecha_vigencia'] }}</td>
                        <td>{{ $cfg['fecha_fin_vigencia'] ?? '—' }}</td>
                        <td style="max-width:200px;font-size:12px;color:#718096;">{{ $cfg['observaciones'] ?? '—' }}</td>
                        <td style="font-size:12px;">
                            @if(!empty($cfg['usuario_creador']))
                                {{ $cfg['usuario_creador']['name'] ?? '—' }}
                            @else
                                <span style="color:#a0aec0;">Sistema</span>
                            @endif
                        </td>
                        <td style="font-size:12px;color:#a0aec0;">{{ $cfg['updated_at'] ?? '—' }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary mr-1" wire:click="abrirEditar({{ $cfg['id'] }})" title="Editar">
                                <i class="fa fa-edit"></i>
                            </button>
                            @if($cfg['estado'])
                                <button class="btn btn-sm btn-outline-danger" wire:click="inactivar({{ $cfg['id'] }})"
                                    onclick="return confirm('¿Inactivar esta configuración?')" title="Inactivar">
                                    <i class="fa fa-ban"></i>
                                </button>
                            @else
                                <button class="btn btn-sm btn-outline-success" wire:click="activar({{ $cfg['id'] }})" title="Activar">
                                    <i class="fa fa-toggle-on"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:40px;color:#a0aec0;">
                            <i class="fa fa-inbox" style="font-size:40px;display:block;margin-bottom:10px;"></i>
                            No hay configuraciones registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="ci-modal-overlay">
        <div class="ci-modal-box">
            <div class="ci-modal-title">
                <i class="fa fa-percent mr-2 text-warning"></i>
                {{ $editandoId ? 'Editar Configuración #' . $editandoId : 'Nueva Configuración de Interés' }}
            </div>

            @if($mensajeError)
                <div class="ci-alert-err"><i class="fa fa-times-circle mr-1"></i> {{ $mensajeError }}</div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="ci-form-group">
                        <label><i class="fa fa-percent mr-1"></i> Tasa Mensual (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.0001" min="0.0001" max="999.9999"
                               class="form-control" wire:model.defer="tasa_mensual"
                               placeholder="Ej: 3.2500">
                        @error('tasa_mensual') <span style="color:#e53e3e;font-size:11px;">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="ci-form-group">
                        <label><i class="fa fa-toggle-on mr-1"></i> Estado</label>
                        <select class="form-control" wire:model.defer="estado">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="ci-form-group">
                        <label><i class="fa fa-calendar mr-1"></i> Fecha de Vigencia <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" wire:model.defer="fecha_vigencia">
                        @error('fecha_vigencia') <span style="color:#e53e3e;font-size:11px;">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="ci-form-group">
                        <label><i class="fa fa-calendar-times-o mr-1"></i> Fin de Vigencia</label>
                        <input type="date" class="form-control" wire:model.defer="fecha_fin_vigencia">
                        @error('fecha_fin_vigencia') <span style="color:#e53e3e;font-size:11px;">{{ $message }}</span> @enderror
                        <span style="font-size:10px;color:#a0aec0;">Opcional. Dejar vacío = vigencia indefinida.</span>
                    </div>
                </div>
                <div class="col-12">
                    <div class="ci-form-group">
                        <label><i class="fa fa-comment mr-1"></i> Observaciones</label>
                        <textarea class="form-control" rows="2" wire:model.defer="observaciones"
                                  placeholder="Ej: Tasa aprobada en reunión del 01/07/2026..."></textarea>
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px;">
                <button class="btn btn-outline-secondary btn-sm" wire:click="cerrarModal">Cancelar</button>
                <button class="ci-btn-add btn-sm" wire:click="guardar" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="guardar"><i class="fa fa-save mr-1"></i> Guardar</span>
                    <span wire:loading wire:target="guardar"><i class="fa fa-spinner fa-spin mr-1"></i> Guardando...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
</div>
