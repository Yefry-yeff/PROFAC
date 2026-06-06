<div>
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>Gestionar Widgets del Dashboard</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/usuarios/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active"><strong>Gestionar Widgets</strong></li>
            </ol>
        </div>
        <div class="col-lg-2" style="padding-top:20px; text-align:right;">
            <a href="{{ url('/usuarios/dashboard') }}" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
        @endif

        {{-- ── Tabla de Widgets ────────────────────────────────────────── --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title" style="border-top: 3px solid #1ab394;">
                        <h5><i class="fa fa-th-large" style="color:#1ab394; margin-right:6px;"></i> Widgets Configurados</h5>
                    </div>
                    <div class="ibox-content" style="padding:0;">
                        <div class="table-responsive">
                            <table class="table table-hover" style="margin:0; font-size:13px;">
                                <thead style="background:#f5f5f5;">
                                    <tr>
                                        <th style="width:40px;">Ord.</th>
                                        <th style="width:40px;"></th>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Roles con acceso</th>
                                        <th style="width:90px; text-align:center;">Estado</th>
                                        <th style="width:110px; text-align:center;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($widgets as $w)
                                    <tr style="{{ $w->enabled ? '' : 'opacity:.5;' }}">
                                        <td class="text-muted" style="font-size:11px;">{{ $w->sort_order }}</td>
                                        <td>
                                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                                 style="width:32px;height:32px;background:{{ $w->color }}1a;border:1.5px solid {{ $w->color }};">
                                                <i class="fa {{ $w->icon }}" style="color:{{ $w->color }};font-size:13px;"></i>
                                            </div>
                                        </td>
                                        <td style="font-weight:600; color:#333;">{{ $w->title }}</td>
                                        <td>
                                            <span class="label label-default" style="font-size:11px;">
                                                {{ $widgetTypes[$w->widget_type] ?? $w->widget_type }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(empty($w->roles))
                                                <span class="label label-success" style="font-size:11px;">Todos los roles</span>
                                            @else
                                                @foreach($w->roles as $r)
                                                <span class="label" style="background:#1c84c6; color:#fff; font-size:11px; margin:1px;">{{ $r }}</span>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td style="text-align:center;">
                                            <button wire:click="toggleEnabled({{ $w->id }})"
                                                    class="btn btn-xs {{ $w->enabled ? 'btn-success' : 'btn-default' }}">
                                                <i class="fa {{ $w->enabled ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                                {{ $w->enabled ? 'Activo' : 'Inactivo' }}
                                            </button>
                                        </td>
                                        <td style="text-align:center; white-space:nowrap;">
                                            <button wire:click="abrirEditar({{ $w->id }})"
                                                    class="btn btn-xs btn-warning"
                                                    title="Editar">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <button onclick="confirmarEliminar({{ $w->id }}, '{{ addslashes($w->title) }}')"
                                                    class="btn btn-xs btn-danger"
                                                    title="Eliminar">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted" style="padding:24px;">
                                            No hay widgets configurados. <a wire:click="abrirNuevo" href="#" style="color:#1ab394;">Crear el primero</a>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════  MODAL CREAR / EDITAR WIDGET  ══════════════════════════ --}}
    @if($modalAbierto)
    <div class="modal fade show" style="display:block; background:rgba(0,0,0,.5); z-index:9000; overflow-x:hidden; overflow-y:auto;" tabindex="-1">
        <div class="modal-dialog" style="max-width:640px; margin:30px auto;">
            <div class="modal-content">

                <div class="modal-header" style="background:#1ab394;">
                    <h4 class="modal-title" style="color:#fff;">
                        <i class="fa {{ $esNuevo ? 'fa-plus' : 'fa-pencil' }}"></i>
                        {{ $esNuevo ? 'Nuevo Widget' : 'Editar Widget' }}
                    </h4>
                    <button type="button" class="close" wire:click="cerrarModal" style="color:#fff; opacity:1;">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body" style="padding:20px 24px; max-height:calc(100vh - 180px); overflow-y:auto;">

                    {{-- Nombre --}}
                    <div class="form-group">
                        <label style="font-size:13px; font-weight:600;">Nombre del widget <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm @error('fTitle') is-invalid @enderror"
                               wire:model.lazy="fTitle"
                               placeholder="Ej: Ventas del Mes">
                        @error('fTitle')<div class="invalid-feedback" style="display:block; font-size:12px;">{{ $message }}</div>@enderror
                    </div>

                    {{-- Tipo de Widget --}}
                    <div class="form-group">
                        <label style="font-size:13px; font-weight:600;">Tipo de widget <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm @error('fWidgetType') is-invalid @enderror"
                                wire:model="fWidgetType">
                            @foreach($widgetTypes as $typeKey => $typeLabel)
                            <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                            @endforeach
                        </select>
                        @error('fWidgetType')<div class="invalid-feedback" style="display:block; font-size:12px;">{{ $message }}</div>@enderror
                        <small class="text-muted" style="font-size:11px;">El tipo determina qué datos se muestran.</small>
                    </div>

                    {{-- Config stock si aplica --}}
                    @if($fWidgetType === 'tabla_stock_bajo')
                    <div class="alert alert-warning" style="padding:10px 14px; font-size:12px;">
                        <strong><i class="fa fa-exclamation-triangle"></i> Configuración de Stock Bajo</strong>
                        <div class="row" style="margin-top:8px;">
                            <div class="col-6">
                                <label style="font-size:12px;">Stock mínimo (umbral)</label>
                                <input type="number" class="form-control form-control-sm" wire:model.lazy="fStockMinimo" min="0">
                                <small class="text-muted">Mostrar productos con stock ≤ este valor</small>
                            </div>
                            <div class="col-6">
                                <label style="font-size:12px;">Límite de filas</label>
                                <input type="number" class="form-control form-control-sm" wire:model.lazy="fStockLimite" min="1" max="100">
                                <small class="text-muted">Máximo de productos a mostrar</small>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Icono + Color + Orden --}}
                    <div class="row">
                        <div class="col-5">
                            <div class="form-group">
                                <label style="font-size:13px; font-weight:600;">Ícono (Font Awesome)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon"><i class="fa {{ $fIcon ?: 'fa-question' }}"></i></span>
                                    <input type="text" class="form-control @error('fIcon') is-invalid @enderror"
                                           wire:model.lazy="fIcon"
                                           placeholder="fa-bar-chart">
                                </div>
                                @error('fIcon')<div class="text-danger" style="font-size:12px;">{{ $message }}</div>@enderror
                                <small class="text-muted" style="font-size:11px;">Ej: fa-users, fa-chart-bar, fa-shopping-cart</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label style="font-size:13px; font-weight:600;">Color</label>
                                <div class="d-flex align-items-center" style="gap:6px;">
                                    <input type="color" class="form-control form-control-sm" wire:model="fColor"
                                           style="height:34px; width:50px; padding:2px; cursor:pointer;">
                                    <input type="text" class="form-control form-control-sm" wire:model.lazy="fColor"
                                           placeholder="#1ab394" style="width:90px;">
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <label style="font-size:13px; font-weight:600;">Orden</label>
                                <input type="number" class="form-control form-control-sm" wire:model.lazy="fSortOrder" min="0">
                            </div>
                        </div>
                    </div>

                    {{-- Activo --}}
                    <div class="form-group">
                        <label class="d-flex align-items-center" style="font-size:13px; gap:8px; cursor:pointer;">
                            <input type="checkbox" wire:model="fEnabled" style="width:16px; height:16px;">
                            <span style="font-weight:600;">Widget activo (visible en el dashboard)</span>
                        </label>
                    </div>

                    {{-- Roles ──────────────────────────────────────────────── --}}
                    <div class="form-group">
                        <label style="font-size:13px; font-weight:600;">
                            Roles que pueden ver este widget
                            <small class="text-muted" style="font-weight:400; margin-left:4px;">(sin selección = todos los roles)</small>
                        </label>
                        <div class="p-2" style="border:1px solid #e7eaec; border-radius:4px; max-height:200px; overflow-y:auto;">
                            @foreach($roles as $rol)
                            <label class="d-flex align-items-center mb-1" style="font-size:13px; cursor:pointer; gap:8px; padding:3px 6px; border-radius:3px;"
                                   onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox"
                                       wire:model="fRolesCheck.{{ $rol->id }}"
                                       style="width:15px; height:15px;">
                                {{ $rol->nombre }}
                            </label>
                            @endforeach
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" wire:click="cerrarModal" class="btn btn-default">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="button" wire:click="guardar" class="btn btn-primary">
                        <i class="fa fa-save"></i> {{ $esNuevo ? 'Crear Widget' : 'Guardar Cambios' }}
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
    function confirmarEliminar(id, nombre) {
        if (typeof Swal === 'undefined') {
            if (confirm('¿Eliminar el widget "' + nombre + '"? Esta acción no se puede deshacer.')) {
                window.livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id')).call('eliminar', id);
            }
            return;
        }
        Swal.fire({
            title: '¿Eliminar widget?',
            html: 'Se eliminará <strong>' + nombre + '</strong>.<br>Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
        }).then(function(result) {
            if (result.isConfirmed) {
                var lw = document.querySelector('[wire\\:id]');
                if (lw) {
                    window.livewire.find(lw.getAttribute('wire:id')).call('eliminar', id);
                }
            }
        });
    }
    </script>
    @endpush
</div>
