<div>
    @push('styles')
        <style>
            :root {
                --pf-grad: linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
                --pf-orange: #e67e22;
                --pf-radius: 8px;
                --pf-shadow: 0 2px 8px rgba(0,0,0,.10);
            }

            .roles-card {
                border: 1px solid #e8d5bf;
                border-radius: var(--pf-radius);
                box-shadow: var(--pf-shadow);
                background: #fff;
                overflow: hidden;
            }

            .roles-card-header {
                background: var(--pf-grad);
                padding: 12px 20px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                flex-wrap: wrap;
            }

            .roles-card-header h5 {
                margin: 0;
                color: #fff;
                font-size: .85rem;
                font-weight: 700;
                letter-spacing: .05em;
                text-transform: uppercase;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .roles-card-body { padding: 16px 20px; }

            .roles-stat-pill {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                background: #fdf6ee;
                border: 1px solid #e8d5bf;
                border-radius: 20px;
                padding: 4px 14px 4px 10px;
                font-size: .78rem;
                color: #555;
                font-weight: 500;
                margin-right: 8px;
                margin-bottom: 8px;
            }

            .roles-stat-pill .stat-num { font-size: .9rem; font-weight: 700; color: var(--pf-orange); }
            .roles-stat-pill.green { background: #f0fdf4; border-color: #bbf7d0; }
            .roles-stat-pill.green .stat-num { color: #1a7a4e; }

            .invoice-table thead th {
                background: #fdf4e7;
                color: #7d3f00;
                font-size: .72rem;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
                border-bottom: 2px solid #f2d49a;
                white-space: nowrap;
            }

            .invoice-table tbody td { font-size: .83rem; vertical-align: middle; }
            .invoice-card { border: 1px solid #e8d5bf; border-radius: 7px; overflow: hidden; }
            .invoice-card .panel-heading {
                background: #fdf4e7;
                color: #7d3f00;
                font-weight: 700;
                border-bottom: 1px solid #e8d5bf;
            }

            .btn-roles-primary {
                background: var(--pf-orange) !important;
                border-color: var(--pf-orange) !important;
                color: #fff !important;
                font-weight: 600;
                box-shadow: 0 1px 3px rgba(0,0,0,.12);
            }

            .btn-roles-primary:hover {
                background: #cf6d12 !important;
                border-color: #cf6d12 !important;
                color: #fff !important;
            }

            .roles-card-body .select2-container { width: 100% !important; }
            .roles-card-body .select2-container .select2-selection--single {
                min-height: 34px;
                border: 1px solid #e5e6e7;
                border-radius: 4px;
            }
            .roles-card-body .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 32px;
                color: #676a6c;
                padding-left: 12px;
                padding-right: 28px;
            }
            .roles-card-body .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 32px;
                right: 8px;
            }
            .select2-container--open { z-index: 99999 !important; }
        </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-12">
            <h2><i class="fa fa-file-text mr-2" style="color:#e67e22"></i>Modificar actores en Factura</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Modificar actores en Factura</strong></li>
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

        <div class="row">
            <div class="col-lg-5">
                <div class="roles-card">
                    <div class="roles-card-header">
                        <h5><i class="fa fa-search"></i> Buscar factura</h5>
                    </div>
                    <div class="roles-card-body">
                        <div class="roles-stat-pill green">
                            <span class="stat-num">{{ count($facturas) }}</span>
                            <span>Resultados</span>
                        </div>

                        <div class="form-group mt-2">
                            <label for="busqueda" class="font-weight-bold small text-uppercase text-muted">Buscar por secuencia CAI, cliente o RTN</label>
                            <input type="text"
                                   id="busqueda"
                                   class="form-control"
                                   placeholder="Ej: 000-001-01-00012345"
                                   wire:model.debounce.400ms="busqueda">
                        </div>

                        <div class="table-responsive" style="max-height: 460px; overflow-y: auto;">
                            <table class="table table-bordered table-hover table-sm mb-0 invoice-table">
                                <thead>
                                    <tr>
                                        <th style="width: 145px;">Secuencia CAI</th>
                                        <th>Cliente</th>
                                        <th style="width: 120px;">Fecha</th>
                                        <th style="width: 92px;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($facturas as $factura)
                                        <tr class="{{ ($facturaSeleccionada['id'] ?? null) === $factura['id'] ? 'table-warning' : '' }}">
                                            <td>
                                                <div class="font-weight-bold">{{ $factura['numero_secuencia_cai'] ?? '-' }}</div>
                                                <small class="text-muted">Factura: {{ $factura['numero_factura'] ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <div>{{ $factura['nombre_cliente'] }}</div>
                                                <small class="text-muted">{{ $factura['rtn'] }}</small>
                                            </td>
                                            <td>{{ $factura['fecha_emision'] }}</td>
                                            <td>
                                                <button type="button"
                                                        class="btn btn-xs btn-roles-primary"
                                                        style="background:#e67e22 !important;border-color:#e67e22 !important;color:#fff !important;opacity:1 !important;"
                                                        wire:click="seleccionarFactura({{ $factura['id'] }})">
                                                    Editar
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                No se encontraron facturas para la búsqueda actual.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="roles-card">
                    <div class="roles-card-header">
                        <h5><i class="fa fa-pencil"></i> Detalle y edición</h5>
                        @if (!empty($facturaSeleccionada))
                            <button type="button" class="btn btn-roles-new" wire:click="limpiarFormulario">Limpiar</button>
                        @endif
                    </div>
                    <div class="roles-card-body">
                        @if (empty($facturaSeleccionada))
                            <div class="alert alert-info mb-0">
                                Busca una factura y presiona <strong>Editar</strong> para cambiar el asesor comercial,
                                el gestor de entregas y el tele asesor.
                            </div>
                        @else
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <div class="invoice-card">
                                        <div class="panel-heading px-3 py-2">Factura seleccionada</div>
                                        <div class="panel-body p-3">
                                            <p class="mb-1"><strong>Secuencia CAI:</strong> {{ $facturaSeleccionada['numero_secuencia_cai'] ?? '-' }}</p>
                                            <p class="mb-1"><strong>Factura:</strong> {{ $facturaSeleccionada['cai'] ?? '-' }}</p>
                                            <p class="mb-1"><strong>Cliente:</strong> {{ $facturaSeleccionada['nombre_cliente'] }}</p>
                                            <p class="mb-1"><strong>RTN:</strong> {{ $facturaSeleccionada['rtn'] }}</p>
                                            <p class="mb-1"><strong>Fecha:</strong> {{ $facturaSeleccionada['fecha_emision'] }}</p>
                                            <p class="mb-1"><strong>Total:</strong> L. {{ number_format((float) ($facturaSeleccionada['total'] ?? 0), 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="invoice-card">
                                        <div class="panel-heading px-3 py-2">Actores actuales</div>
                                        <div class="panel-body p-3">
                                            <p class="mb-1"><strong>Asesor comercial:</strong> {{ $facturaSeleccionada['vendedor_nombre'] ?? '-' }}</p>
                                            <p class="mb-1"><strong>Gestor de entregas:</strong> {{ $facturaSeleccionada['gestor_nombre'] ?? '-' }}</p>
                                            <p class="mb-1"><strong>Tele asesor:</strong> {{ $facturaSeleccionada['tele_asesor_nombre'] ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning">
                                Este cambio modifica únicamente los actores de la factura. No altera totales, productos ni CAI.
                            </div>

                            <div class="form-group">
                                <label for="vendedorId">Asesor comercial</label>
                                <select id="vendedorId" class="form-control" wire:model.defer="vendedorId">
                                    <option value="">Seleccione un asesor comercial</option>
                                    @foreach ($usuarios as $usuario)
                                        <option value="{{ $usuario['id'] }}">{{ $usuario['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('vendedorId') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label for="teleAsesorId">Tele asesor</label>
                                <select id="teleAsesorId" class="form-control" wire:model.defer="teleAsesorId">
                                    <option value="">Seleccione un tele asesor</option>
                                    @foreach ($usuarios as $usuario)
                                        <option value="{{ $usuario['id'] }}">{{ $usuario['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('teleAsesorId') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label for="gestorEntregaId">Gestor de entregas</label>
                                <select id="gestorEntregaId" class="form-control" wire:model.defer="gestorEntregaId">
                                    <option value="">Sin asignar</option>
                                    @foreach ($usuarios as $usuario)
                                        <option value="{{ $usuario['id'] }}">{{ $usuario['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('gestorEntregaId') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button"
                                        class="btn btn-roles-primary"
                                        wire:click="guardarCambios"
                                        wire:loading.attr="disabled"
                                        wire:target="guardarCambios">
                                    <span wire:loading.remove wire:target="guardarCambios">Guardar cambios</span>
                                    <span wire:loading wire:target="guardarCambios">Guardando...</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            function getLivewireComponent(selectEl) {
                var wireRoot = selectEl.closest('[wire\\:id]');
                if (!wireRoot) return null;
                var componentId = wireRoot.getAttribute('wire:id');
                if (window.Livewire && typeof window.Livewire.find === 'function') {
                    return window.Livewire.find(componentId);
                }
                if (window.livewire && typeof window.livewire.find === 'function') {
                    return window.livewire.find(componentId);
                }
                return null;
            }

            function initActorSelect2() {
                if (typeof $ === 'undefined' || !$.fn || !$.fn.select2) return;

                var fieldMap = {
                    vendedorId: 'vendedorId',
                    teleAsesorId: 'teleAsesorId',
                    gestorEntregaId: 'gestorEntregaId'
                };

                Object.keys(fieldMap).forEach(function (fieldId) {
                    var $el = $('#' + fieldId);
                    if (!$el.length) return;

                    if ($el.data('select2')) {
                        $el.off('change.actorSelect2');
                        $el.select2('destroy');
                    }

                    $el.select2({
                        width: '100%',
                        placeholder: $el.find('option:first').text(),
                        allowClear: fieldId === 'gestorEntregaId'
                    });

                    $el.on('change.actorSelect2', function () {
                        var component = getLivewireComponent(this);
                        if (!component || typeof component.set !== 'function') return;
                        component.set(fieldMap[fieldId], $(this).val() || '');
                    });
                });
            }

            document.addEventListener('livewire:load', function () {
                initActorSelect2();

                if (window.Livewire && typeof window.Livewire.hook === 'function') {
                    window.Livewire.hook('message.processed', function () {
                        initActorSelect2();
                    });
                }
            });
        })();
    </script>
@endpush
