<div>
    {{-- ── Page heading ── --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2><i class="fa fa-clock-o" style="color:#e65100;"></i> Tiempo de Prefacturación</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item">Configuración</li>
                <li class="breadcrumb-item active"><strong>Tiempo de Prefacturación</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="ibox" style="border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.07);">

                    <div class="ibox-title" style="background:linear-gradient(135deg,#e65100 0%,#f9a826 100%); border:none; padding:16px 24px;">
                        <h3 style="color:#fff; margin:0; font-size:16px;">
                            <i class="fa fa-clock-o mr-2"></i>Configurar Validez de Prefacturas
                        </h3>
                        <small style="color:rgba(255,255,255,.85); font-size:12px; margin-top:4px; display:block;">
                            Define cuánto tiempo estarán vigentes las prefacturas desde su fecha de emisión
                        </small>
                    </div>

                    <div class="ibox-content" style="padding:28px;">

                        {{-- ── Alerts ── --}}
                        @if($mensajeExito)
                        <div class="alert alert-success alert-dismissible" role="alert" style="border-radius:10px;">
                            <i class="fa fa-check-circle mr-2"></i> {{ $mensajeExito }}
                            <button type="button" class="close" wire:click="$set('mensajeExito','')">
                                <span>&times;</span>
                            </button>
                        </div>
                        @endif
                        @if($mensajeError)
                        <div class="alert alert-danger alert-dismissible" role="alert" style="border-radius:10px;">
                            <i class="fa fa-exclamation-circle mr-2"></i> {{ $mensajeError }}
                            <button type="button" class="close" wire:click="$set('mensajeError','')">
                                <span>&times;</span>
                            </button>
                        </div>
                        @endif

                        {{-- ── Opciones rápidas ── --}}
                        <h6 style="font-weight:800; color:#546e7a; font-size:11px; text-transform:uppercase; letter-spacing:.5px; margin-bottom:12px;">
                            Opciones rápidas
                        </h6>
                        <div class="d-flex flex-wrap" style="gap:8px; margin-bottom:24px;">
                            @foreach($opciones as $opcion)
                                <button type="button"
                                    wire:click="seleccionarOpcion({{ $opcion['dias'] }}, '{{ $opcion['label'] }}')"
                                    class="btn btn-sm {{ $diasValidez == $opcion['dias'] && $opcion['dias'] > 0 ? 'btn-warning' : 'btn-outline-secondary' }}"
                                    style="border-radius:20px; font-weight:700; font-size:12px; padding:6px 16px;">
                                    {{ $opcion['label'] }}
                                </button>
                            @endforeach
                        </div>

                        {{-- ── Formulario ── --}}
                        <div style="background:#f8f9fc; border-radius:12px; padding:20px; border:1px solid #e8eaf0;">
                            <div class="row">
                                <div class="col-md-5">
                                    <label style="font-size:11px; font-weight:700; color:#546e7a; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px; display:block;">
                                        Días de validez <span style="color:#e53935;">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" wire:model.defer="diasValidez" min="1"
                                               class="form-control" style="border-radius:8px 0 0 8px; font-size:15px; font-weight:700; text-align:center;"
                                               placeholder="Ej: 7">
                                        <div class="input-group-append">
                                            <span class="input-group-text" style="background:#e65100; color:#fff; border-color:#e65100; border-radius:0 8px 8px 0; font-weight:700; font-size:12px;">días</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-7 mt-3 mt-md-0">
                                    <label style="font-size:11px; font-weight:700; color:#546e7a; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px; display:block;">
                                        Descripción (aparece en la prefactura)
                                    </label>
                                    <input type="text" wire:model.defer="descripcionValidez"
                                           class="form-control" style="border-radius:8px; font-size:13px;"
                                           placeholder="Ej: 1 semana, 15 días…">
                                </div>
                            </div>

                            {{-- ── Vista previa ── --}}
                            <div style="margin-top:16px; padding:12px 16px; background:#fff3e0; border-radius:8px; border-left:4px solid #e65100;">
                                <p style="margin:0; font-size:12px; color:#cc4400; font-weight:700;">
                                    <i class="fa fa-eye mr-1"></i> Vista previa en la prefactura:
                                </p>
                                <p style="margin:4px 0 0; font-size:12px; color:#555; font-style:italic;">
                                    "** ESTA PREFACTURA TIENE UN TIEMPO DE VALIDACIÓN DE
                                    <strong>{{ strtoupper($descripcionValidez ?: $diasValidez . ' DÍAS') }}</strong>
                                    DESDE SU FECHA DE EMISIÓN **"
                                </p>
                            </div>
                        </div>

                        {{-- ── Guardar ── --}}
                        <div class="text-right mt-4">
                            <button type="button" wire:click="guardar"
                                    style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none;
                                           border-radius:20px; padding:10px 32px; font-size:14px; font-weight:800;
                                           cursor:pointer; box-shadow:0 4px 16px rgba(230,81,0,.35);">
                                <i class="fa fa-save mr-2"></i> Guardar Configuración
                            </button>
                        </div>

                    </div>
                </div>

                {{-- ── Info card ── --}}
                <div class="alert alert-info" style="border-radius:12px; font-size:13px;">
                    <i class="fa fa-info-circle mr-2"></i>
                    <strong>¿Cómo funciona?</strong><br>
                    Cuando se guarde una prefactura, su fecha de vencimiento se calculará automáticamente
                    sumando <strong>{{ $diasValidez }}</strong> días a la fecha de emisión.
                    Esta configuración aplica a todas las prefacturas nuevas.
                </div>
            </div>
        </div>
    </div>
</div>
