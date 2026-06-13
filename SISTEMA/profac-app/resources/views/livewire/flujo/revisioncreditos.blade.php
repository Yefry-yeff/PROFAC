<div>
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{--                    REVISIÓN DE CRÉDITO                           --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}

    {{-- Page header --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2><i class="mr-2 fa fa-credit-card text-primary"></i>Revisión de Crédito</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item active">
                    <strong>Revisión de Crédito</strong>
                </li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- ── Mensajes globales ─────────────────────────────────────── --}}
        @if ($mensajeExito && !$flujoId)
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mr-1 fa fa-check-circle"></i> {{ $mensajeExito }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        @endif
        @if ($mensajeError && !$flujoId)
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mr-1 fa fa-exclamation-triangle"></i> {{ $mensajeError }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- VISTA DETALLE                                                  --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @if ($flujoId)

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox" style="border-radius:14px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.09);">

                    {{-- Header detalle --}}
                    @php
                        $esAprobado  = ($estadoCredito === 'aprobado');
                        $esRechazado = ($estadoCredito === 'rechazado');
                        $headerColor = $esAprobado ? '#27ae60,#1e8449' : ($esRechazado ? '#e74c3c,#c0392b' : '#1a7efb,#0d6efd');
                    @endphp
                    <div class="ibox-title d-flex align-items-center justify-content-between"
                         style="background:linear-gradient(135deg,{{ $headerColor }}); border:none; padding:14px 22px;">
                        <div>
                            <h5 style="color:#fff; margin:0; font-weight:700; font-size:15px;">
                                <i class="mr-2 fa fa-credit-card"></i>
                                Revisando Flujo #{{ $flujoId }}
                                @if($flujoData) — {{ $flujoData['cliente'] ?? '—' }} @endif
                                <span style="background:rgba(255,255,255,.2); border-radius:20px; padding:2px 12px; font-size:12px; margin-left:8px;">
                                    {{ strtoupper($estadoCredito ?? 'pendiente') }}
                                </span>
                            </h5>
                            @if($flujoData && ($flujoData['pedido_id'] ?? null))
                            <small style="color:rgba(255,255,255,.8); font-size:11px;">
                                Pedido #{{ $flujoData['pedido_id'] }}
                                @if($flujoData['pedido_fecha'] ?? null)
                                    · {{ \Carbon\Carbon::parse($flujoData['pedido_fecha'])->format('d/m/Y') }}
                                @endif
                            </small>
                            @endif
                        </div>
                        <button type="button" wire:click="cerrarDetalle"
                                style="background:rgba(255,255,255,.2); color:#fff; border:none;
                                       border-radius:8px; padding:6px 14px; font-size:12px; font-weight:700; cursor:pointer;">
                            <i class="mr-1 fa fa-arrow-left"></i> Volver a bandeja
                        </button>
                    </div>

                    <div class="ibox-content" style="padding:22px 26px;">

                        {{-- Mensajes detalle --}}
                        @if ($mensajeExito)
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <i class="mr-1 fa fa-check-circle"></i> {{ $mensajeExito }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                        @endif
                        @if ($mensajeError)
                        <div class="alert alert-danger" role="alert">
                            <i class="mr-1 fa fa-exclamation-triangle"></i> {{ $mensajeError }}
                        </div>
                        @endif

                        {{-- Resumen del crédito si ya fue procesado --}}
                        @if ($esAprobado)
                        <div style="background:#e8f5e9; border:1px solid #a5d6a7; border-radius:12px; padding:14px 18px; margin-bottom:18px;">
                            <h6 style="color:#2e7d32; font-weight:700; margin-bottom:8px;">
                                <i class="mr-1 fa fa-check-circle"></i> Crédito Aprobado
                            </h6>
                            <div style="font-size:13px; color:#2c3e50; display:flex; flex-wrap:wrap; gap:18px;">
                                @if ($fechaAprobacionActual)
                                <div>
                                    <div style="font-size:11px; color:#66bb6a; font-weight:700; text-transform:uppercase;">Fecha autorización</div>
                                    <div style="font-weight:700;">{{ \Carbon\Carbon::parse($fechaAprobacionActual)->format('d/m/Y') }}</div>
                                </div>
                                @endif
                                @if ($fechaVencimientoActual)
                                <div>
                                    <div style="font-size:11px; color:#66bb6a; font-weight:700; text-transform:uppercase;">Vence</div>
                                    <div style="font-weight:700;">{{ \Carbon\Carbon::parse($fechaVencimientoActual)->format('d/m/Y') }}</div>
                                </div>
                                @endif
                                @if ($usuarioAprobadorActual)
                                <div>
                                    <div style="font-size:11px; color:#66bb6a; font-weight:700; text-transform:uppercase;">Aprobado por</div>
                                    <div style="font-weight:700;">{{ $usuarioAprobadorActual }}</div>
                                </div>
                                @endif
                                @if ($obsAprobacionActual)
                                <div>
                                    <div style="font-size:11px; color:#66bb6a; font-weight:700; text-transform:uppercase;">Comentario</div>
                                    <div>{{ $obsAprobacionActual }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @elseif ($esRechazado)
                        <div style="background:#fce4e4; border:1px solid #ef9a9a; border-radius:12px; padding:14px 18px; margin-bottom:18px;">
                            <h6 style="color:#c62828; font-weight:700; margin-bottom:6px;">
                                <i class="mr-1 fa fa-times-circle"></i> Crédito Rechazado
                            </h6>
                            <div style="font-size:13px; color:#2c3e50;">
                                <strong>Motivo:</strong> {{ $motivoRechazoActual }}
                            </div>
                        </div>
                        @endif

                        {{-- Acciones financieras + resumen de solicitud --}}
                        <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
                            @if($clienteId)
                            <a href="/estadoCuenta/imprimir/aplicpagos/{{ $clienteId }}"
                               target="_blank"
                               class="btn btn-primary"
                               style="border-radius:8px; font-weight:700;">
                                <i class="mr-1 fa fa-file-text-o"></i> Estado de cuenta
                            </a>
                            @endif

                            @if($cotizacionId)
                            <a href="/cotizacion/imprimir/{{ $cotizacionId }}"
                               target="_blank"
                               class="btn btn-default"
                               style="border-radius:8px; font-weight:700; border:1px solid #d7dde5;">
                                <i class="mr-1 fa fa-print"></i> Imprimir oferta ganadora
                            </a>
                            @endif
                        </div>

                        <div style="border-radius:12px; overflow:hidden; border:1px solid #e8eaf0; margin-bottom:20px;">
                            <div style="background:linear-gradient(135deg,#546e7a,#37474f); padding:10px 16px;">
                                <span style="color:#fff; font-size:13px; font-weight:700;">
                                    <i class="mr-1 fa fa-line-chart"></i>
                                    Validación financiera de la oferta
                                </span>
                            </div>
                            <div style="padding:14px 16px; background:#fff;">
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <div style="font-size:11px; color:#78909c; text-transform:uppercase; font-weight:700;">Tipo de pago</div>
                                        <div style="font-size:14px; font-weight:700; color:#2c3e50; text-transform:capitalize;">{{ $tipoPagoSolicitud }}</div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div style="font-size:11px; color:#78909c; text-transform:uppercase; font-weight:700;">Fecha emisión</div>
                                        <div style="font-size:14px; font-weight:700; color:#2c3e50;">
                                            {{ $fechaEmisionOferta ? \Carbon\Carbon::parse($fechaEmisionOferta)->format('d/m/Y') : '—' }}
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div style="font-size:11px; color:#78909c; text-transform:uppercase; font-weight:700;">Fecha vencimiento</div>
                                        <div style="font-size:14px; font-weight:700; color:#2c3e50;">
                                            {{ $fechaVencimientoOferta ? \Carbon\Carbon::parse($fechaVencimientoOferta)->format('d/m/Y') : '—' }}
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div style="font-size:11px; color:#78909c; text-transform:uppercase; font-weight:700;">Días solicitados del crédito</div>
                                        <div style="font-size:15px; font-weight:800; color:#1565c0;">{{ $diasSolicitadosCredito }}</div>
                                    </div>
                                </div>
                                <div style="margin-top:10px; padding-top:10px; border-top:1px solid #eef1f5;">
                                    <div style="font-size:11px; color:#78909c; text-transform:uppercase; font-weight:700;">Monto total de la oferta</div>
                                    <div style="font-size:20px; font-weight:800; color:#e65100;">L {{ number_format($montoTotalOferta, 2, '.', ',') }}</div>
                                </div>
                                {{-- Documentos de la oferta --}}
                                @if($numeroOrdenCompra || $archivoOrdenCompra || $numeroFormaF01 || $archivoFormaF01)
                                <div style="margin-top:12px; padding-top:12px; border-top:1px solid #eef1f5;">
                                    <div style="font-size:11px; color:#78909c; text-transform:uppercase; font-weight:700; margin-bottom:8px;">
                                        <i class="mr-1 fa fa-paperclip"></i> Documentos de la oferta
                                    </div>
                                    <div class="row">
                                        @if($numeroOrdenCompra || $archivoOrdenCompra)
                                        <div class="col-md-6 mb-2">
                                            <div style="font-size:11px; color:#78909c; text-transform:uppercase; font-weight:700;">N° Orden de Compra</div>
                                            <div style="font-size:14px; font-weight:700; color:#2c3e50;">{{ $numeroOrdenCompra ?: '—' }}</div>
                                            @if($archivoOrdenCompra)
                                            <a href="/{{ $archivoOrdenCompra }}" target="_blank"
                                               style="font-size:12px; color:#1565c0;">
                                                <i class="fa fa-file mr-1"></i> Ver adjunto
                                            </a>
                                            @endif
                                        </div>
                                        @endif
                                        @if($numeroFormaF01 || $archivoFormaF01)
                                        <div class="col-md-6 mb-2">
                                            <div style="font-size:11px; color:#78909c; text-transform:uppercase; font-weight:700;">N° Forma F01</div>
                                            <div style="font-size:14px; font-weight:700; color:#2c3e50;">{{ $numeroFormaF01 ?: '—' }}</div>
                                            @if($archivoFormaF01)
                                            <a href="/{{ $archivoFormaF01 }}" target="_blank"
                                               style="font-size:12px; color:#1565c0;">
                                                <i class="fa fa-file mr-1"></i> Ver adjunto
                                            </a>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- ── Acciones (solo disponibles en estado Pendiente) ── --}}
                        @if ($estadoCredito === 'pendiente')
                        @if (!$confirmAccion)
                        <div class="d-flex gap-2" style="gap:10px;">
                            <button type="button"
                                    wire:click="confirmarAccion('aprobar')"
                                    class="btn btn-success"
                                    style="border-radius:8px; font-weight:700; padding:8px 22px;">
                                <i class="mr-1 fa fa-check-circle"></i> Aprobar Crédito
                            </button>
                            <button type="button"
                                    wire:click="confirmarAccion('rechazar')"
                                    class="btn btn-danger"
                                    style="border-radius:8px; font-weight:700; padding:8px 22px; margin-left:8px;">
                                <i class="mr-1 fa fa-times-circle"></i> Rechazar Crédito
                            </button>
                        </div>
                        @endif

                        {{-- Formulario Aprobar --}}
                        @if ($confirmAccion === 'aprobar')
                        <div style="background:#e8f5e9; border:1px solid #a5d6a7; border-radius:12px; padding:20px 24px; margin-top:16px;">
                            <h6 style="color:#2e7d32; font-weight:700; margin-bottom:14px;">
                                <i class="mr-1 fa fa-check-circle"></i> Confirmar Aprobación de Crédito
                            </h6>
                            <div style="border:1px solid #c8e6c9; border-radius:10px; background:#fff; padding:12px 14px; margin-bottom:14px;">
                                <div style="font-size:12px; font-weight:700; color:#2e7d32; margin-bottom:10px; text-transform:uppercase;">
                                    Datos crediticios del cliente
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label style="font-size:12px; font-weight:700; color:#2e7d32;">Monto de crédito</label>
                                         <input type="text"
                                             wire:model.lazy="montoCreditoEditableTexto"
                                               class="form-control" style="border-radius:8px; font-size:13px;">
                                                                                <small class="text-muted">Editable solo para este flujo</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label style="font-size:12px; font-weight:700; color:#2e7d32;">Monto disponible</label>
                                        <input type="text" class="form-control" readonly
                                             value="L {{ number_format($montoDisponibleActual, 2, '.', ',') }}"
                                               style="border-radius:8px; font-size:13px; background:#f8f9fa; cursor:default;">
                                        <small class="text-muted">Calculado automáticamente</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label style="font-size:12px; font-weight:700; color:#2e7d32;">Días de crédito</label>
                                        <input type="number" min="0"
                                               wire:model.debounce.300ms="diasCreditoEditable"
                                               class="form-control" style="border-radius:8px; font-size:13px;">
                                             <small class="text-muted">Editable solo para este flujo</small>
                                    </div>
                                </div>

                                @if (!empty($bloqueosAutorizacion))
                                <div class="alert alert-danger" style="margin-bottom:0; border-radius:8px; font-size:12px;">
                                    @foreach($bloqueosAutorizacion as $bloqueo)
                                        <div><i class="mr-1 fa fa-ban"></i> {{ $bloqueo }}</div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                                    <div class="col-md-12 mb-3">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label style="font-size:12px; font-weight:700; color:#2e7d32;">
                                        Observaciones <small style="font-weight:400;">(opcional)</small>
                                    </label>
                                    <input type="text"
                                           wire:model.defer="observaciones"
                                           class="form-control"
                                           placeholder="Notas adicionales..."
                                           style="border-radius:8px; font-size:13px;">
                                </div>
                            </div>
                            <div class="d-flex" style="gap:8px;">
                                <button type="button"
                                        wire:click="aprobarCredito"
                                        wire:loading.attr="disabled"
                                        @if(!$puedeAutorizar) disabled @endif
                                        class="btn btn-success"
                                    style="border-radius:8px; font-weight:700; {{ !$puedeAutorizar ? 'opacity:.6; cursor:not-allowed;' : '' }}">
                                    <span wire:loading.remove wire:target="aprobarCredito">
                                        <i class="mr-1 fa fa-check"></i> Confirmar Aprobación
                                    </span>
                                    <span wire:loading wire:target="aprobarCredito">
                                        <i class="fa fa-spinner fa-spin mr-1"></i> Guardando...
                                    </span>
                                </button>
                                <button type="button"
                                        wire:click="cancelarAccion"
                                        class="btn btn-default"
                                        style="border-radius:8px; font-weight:700; margin-left:8px;">
                                    <i class="mr-1 fa fa-times"></i> Cancelar
                                </button>
                            </div>
                        </div>
                        @endif

                        {{-- Formulario Rechazar --}}
                        @if ($confirmAccion === 'rechazar')
                        <div style="background:#fce4e4; border:1px solid #ef9a9a; border-radius:12px; padding:20px 24px; margin-top:16px;">
                            <h6 style="color:#c62828; font-weight:700; margin-bottom:14px;">
                                <i class="mr-1 fa fa-times-circle"></i> Confirmar Rechazo de Crédito
                            </h6>
                            <div class="alert alert-warning" style="border-radius:8px; font-size:13px;">
                                <i class="mr-1 fa fa-exclamation-triangle"></i>
                                <strong>Atención:</strong> Al rechazar el crédito, el flujo quedará <strong>cancelado</strong>
                                y no podrá continuar a Revisión de Inventario ni Prefactura.
                            </div>
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label style="font-size:12px; font-weight:700; color:#c62828;">
                                        Motivo de Rechazo <span style="color:#e74c3c;">*</span>
                                    </label>
                                    <textarea wire:model.defer="motivoRechazo"
                                              class="form-control"
                                              rows="3"
                                              placeholder="Ingrese el motivo del rechazo de crédito..."
                                              style="border-radius:8px; font-size:13px;"></textarea>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label style="font-size:12px; font-weight:700; color:#c62828;">
                                        Observaciones <small style="font-weight:400;">(opcional)</small>
                                    </label>
                                    <textarea wire:model.defer="observaciones"
                                              class="form-control"
                                              rows="3"
                                              placeholder="Notas adicionales..."
                                              style="border-radius:8px; font-size:13px;"></textarea>
                                </div>
                            </div>
                            <div class="d-flex" style="gap:8px;">
                                <button type="button"
                                        wire:click="rechazarCredito"
                                        wire:loading.attr="disabled"
                                        class="btn btn-danger"
                                        style="border-radius:8px; font-weight:700;">
                                    <span wire:loading.remove wire:target="rechazarCredito">
                                        <i class="mr-1 fa fa-ban"></i> Confirmar Rechazo
                                    </span>
                                    <span wire:loading wire:target="rechazarCredito">
                                        <i class="fa fa-spinner fa-spin mr-1"></i> Guardando...
                                    </span>
                                </button>
                                <button type="button"
                                        wire:click="cancelarAccion"
                                        class="btn btn-default"
                                        style="border-radius:8px; font-weight:700; margin-left:8px;">
                                    <i class="mr-1 fa fa-times"></i> Cancelar
                                </button>
                            </div>
                        </div>
                        @endif
                        @endif {{-- end if pendiente --}}

                        {{-- ── Historial de auditoría ── --}}
                        @if (count($historialCredito) > 0)
                        <div style="margin-top:24px;">
                            <h6 style="font-weight:700; color:#546e7a; margin-bottom:12px;">
                                <i class="mr-1 fa fa-history"></i> Historial de Auditoría
                            </h6>
                            <div style="border-radius:10px; overflow:hidden; border:1px solid #e8eaf0;">
                                <table class="table table-sm" style="font-size:12px; margin:0;">
                                    <thead style="background:#f8f9fc;">
                                        <tr>
                                            <th style="padding:8px 12px;">Fecha</th>
                                            <th style="padding:8px 12px;">Acción</th>
                                            <th style="padding:8px 12px;">Estado anterior</th>
                                            <th style="padding:8px 12px;">Estado nuevo</th>
                                            <th style="padding:8px 12px;">Usuario</th>
                                            <th style="padding:8px 12px;">Descripción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($historialCredito as $h)
                                        <tr style="border-bottom:1px solid #f0f0f0;">
                                            <td style="padding:7px 12px; white-space:nowrap; color:#78909c;">
                                                {{ \Carbon\Carbon::parse($h['fecha_evento'])->format('d/m/Y H:i') }}
                                            </td>
                                            <td style="padding:7px 12px;">
                                                @php
                                                    $accionColor = match($h['accion']) {
                                                        'aprobado'  => '#27ae60',
                                                        'rechazado' => '#e74c3c',
                                                        'cancelado' => '#95a5a6',
                                                        'creado'    => '#3498db',
                                                        default     => '#546e7a',
                                                    };
                                                @endphp
                                                <span style="font-weight:700; color:{{ $accionColor }}; text-transform:uppercase; font-size:11px;">
                                                    {{ $h['accion'] }}
                                                </span>
                                            </td>
                                            <td style="padding:7px 12px; color:#78909c;">{{ $h['estado_anterior'] ?? '—' }}</td>
                                            <td style="padding:7px 12px; font-weight:700; color:#2c3e50;">{{ $h['estado_nuevo'] ?? '—' }}</td>
                                            <td style="padding:7px 12px; color:#546e7a;">{{ $h['usuario_nombre'] ?? '—' }}</td>
                                            <td style="padding:7px 12px; color:#546e7a; font-size:11px;">{{ $h['descripcion'] ?? '' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- BANDEJA (cuando NO hay flujo seleccionado)                    --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @else

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox" style="border-radius:14px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.07);">
                    <div class="ibox-title d-flex align-items-center justify-content-between"
                         style="background:linear-gradient(135deg,#1a5276,#154360); border:none; padding:12px 20px;">
                        <h5 style="color:#fff; margin:0; font-weight:700; font-size:14px;">
                            <i class="mr-2 fa fa-credit-card"></i>Bandeja de Revisión de Crédito
                        </h5>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span style="background:rgba(255,255,255,.15); color:#ecf0f1; border-radius:20px;
                                         padding:3px 14px; font-size:12px; font-weight:600;">
                                {{ count($bandejaLlegando) }} pendiente(s)
                            </span>
                        </div>
                    </div>

                    <div class="ibox-content" style="padding:0;">

                        {{-- Buscador --}}
                        <div style="padding:16px 20px; border-bottom:1px solid #f0f2f5; display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                            <div class="input-group" style="max-width:420px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" style="background:#1a5276; border-color:#1a5276; color:#fff;">
                                        <i class="fa fa-search"></i>
                                    </span>
                                </div>
                                <input type="text"
                                       wire:model.debounce.400ms="busqueda"
                                       class="form-control"
                                       placeholder="Buscar por cliente, RTN o número de flujo..."
                                       style="font-size:13px;">
                                @if ($busqueda)
                                <div class="input-group-append">
                                    <button type="button"
                                            wire:click="$set('busqueda','')"
                                            class="btn btn-default"
                                            style="border-radius:0 8px 8px 0;">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                                @endif
                            </div>
                            <div class="d-flex align-items-center" style="gap:6px;">
                                <label style="font-size:12px; color:#78909c; font-weight:600; margin:0; white-space:nowrap;">
                                    Filas por p&aacute;gina:
                                </label>
                                <select wire:model="perPage"
                                        class="form-control form-control-sm"
                                        style="width:70px; font-size:13px; border-radius:8px;">
                                    <option value="8">8</option>
                                    <option value="15">15</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                        </div>

                        {{-- Pestañas --}}
                        <div style="padding:0 20px; border-bottom:2px solid #e8eaf0;">
                            <ul class="nav" style="gap:0; border:none;">
                                @foreach ([
                                    ['llegando',   'fa-inbox',         'Llegando',  count($bandejaLlegando),   '#1a5276'],
                                    ['aprobadas',  'fa-check-circle',  'Aprobadas', count($bandejaAprobadas),  '#27ae60'],
                                    ['rechazadas', 'fa-times-circle',  'Rechazadas',count($bandejaRechazadas), '#e74c3c'],
                                ] as [$tab, $icon, $label, $count, $color])
                                <li class="nav-item">
                                    <button type="button"
                                            wire:click="cambiarTab('{{ $tab }}')"
                                            style="background:none; border:none; padding:12px 20px;
                                                   font-size:13px; font-weight:700; cursor:pointer;
                                                   color:{{ $tabActiva === $tab ? $color : '#90a4ae' }};
                                                   border-bottom:3px solid {{ $tabActiva === $tab ? $color : 'transparent' }};
                                                   transition:all .2s;">
                                        <i class="mr-1 fa {{ $icon }}"></i>
                                        {{ $label }}
                                        <span style="background:{{ $tabActiva === $tab ? $color : '#e0e3ee' }};
                                                     color:{{ $tabActiva === $tab ? '#fff' : '#78909c' }};
                                                     border-radius:20px; padding:1px 8px; font-size:11px; margin-left:4px;">
                                            {{ $count }}
                                        </span>
                                    </button>
                                </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Contenido de la pestaña activa --}}
                        @php
                            $esSoloLectura = in_array($tabActiva, ['aprobadas', 'rechazadas']);
                        @endphp

                        <div style="padding:16px 20px 20px;">
                            @if ($totalRegistros === 0)
                            <div style="text-align:center; padding:40px; color:#aab;">
                                <i class="fa fa-inbox fa-3x d-block mb-3" style="opacity:.3;"></i>
                                <p style="font-size:14px; margin:0;">
                                    @if ($tabActiva === 'llegando')
                                        No hay flujos pendientes de revisión de crédito.
                                    @elseif ($tabActiva === 'aprobadas')
                                        No hay créditos aprobados registrados.
                                    @else
                                        No hay créditos rechazados registrados.
                                    @endif
                                </p>
                            </div>
                            @else
                            <div style="overflow-x:auto;">
                                <table class="table table-hover" style="font-size:13px; min-width:600px;">
                                    <thead style="background:#f8f9fc;">
                                        <tr>
                                            <th style="padding:10px 14px; color:#546e7a;">Flujo</th>
                                            <th style="padding:10px 14px; color:#546e7a;">Cliente</th>
                                            <th style="padding:10px 14px; color:#546e7a;">Oferta</th>
                                            <th style="padding:10px 14px; color:#546e7a;">Días solicitados</th>
                                            <th style="padding:10px 14px; color:#546e7a;">Monto oferta</th>
                                            <th style="padding:10px 14px; color:#546e7a;">
                                                @if ($tabActiva === 'llegando') Fecha ingreso
                                                @elseif ($tabActiva === 'aprobadas') Fecha aprobación
                                                @else Motivo rechazo
                                                @endif
                                            </th>
                                            @if ($tabActiva === 'aprobadas')
                                            <th style="padding:10px 14px; color:#546e7a;">Aprobado por</th>
                                            <th style="padding:10px 14px; color:#546e7a;">Comentario</th>
                                            @endif
                                            <th style="padding:10px 14px; color:#546e7a; text-align:center;">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($registrosPagina as $reg)
                                        @php $r = (array) $reg; @endphp
                                        <tr style="border-bottom:1px solid #f0f2f5; transition:background .15s;"
                                            onmouseover="this.style.background='#f8f9fc'"
                                            onmouseout="this.style.background=''">
                                            <td style="padding:10px 14px;">
                                                <span style="background:#e3f2fd; color:#1565c0; border-radius:20px;
                                                             padding:3px 10px; font-size:12px; font-weight:700;">
                                                    #{{ $r['flujo_id'] }}
                                                </span>
                                                @if($r['identificacion'])
                                                <small class="d-block text-muted" style="font-size:11px; margin-top:2px;">
                                                    ID: {{ $r['identificacion'] }}
                                                </small>
                                                @endif
                                            </td>
                                            <td style="padding:10px 14px;">
                                                <div style="font-weight:600; color:#2c3e50;">{{ $r['cliente'] }}</div>
                                                @if($r['rtn'])
                                                <small style="color:#78909c; font-size:11px;">RTN: {{ $r['rtn'] }}</small>
                                                @endif
                                            </td>
                                            <td style="padding:10px 14px;">
                                                @if($r['cotizacion_id'])
                                                <span style="background:#f3e5f5; color:#6a1b9a; border-radius:20px;
                                                             padding:2px 10px; font-size:12px; font-weight:700;">
                                                    #{{ $r['cotizacion_id'] }}
                                                </span>
                                                @else
                                                <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td style="padding:10px 14px; text-align:center;">
                                                <span style="background:#e8eaf0; border-radius:20px;
                                                             padding:2px 10px; font-size:12px; color:#546e7a;">
                                                    {{ (int)($r['dias_solicitados_credito'] ?? 0) }}
                                                </span>
                                            </td>
                                            <td style="padding:10px 14px; font-weight:700; color:#e65100;">
                                                L {{ number_format((float)($r['monto_total_oferta'] ?? 0), 2, '.', ',') }}
                                            </td>
                                            <td style="padding:10px 14px; font-size:12px; color:#78909c;">
                                                @if ($tabActiva === 'llegando')
                                                    {{ \Carbon\Carbon::parse($r['fecha_revision'])->format('d/m/Y H:i') }}
                                                @elseif ($tabActiva === 'aprobadas')
                                                    {{ $r['fecha_aprobacion']
                                                        ? \Carbon\Carbon::parse($r['fecha_aprobacion'])->format('d/m/Y')
                                                        : \Carbon\Carbon::parse($r['fecha_accion'])->format('d/m/Y H:i') }}
                                                @else
                                                    <span style="color:#c62828; font-size:12px;">
                                                        {{ \Illuminate\Support\Str::limit($r['motivo_rechazo'] ?? '—', 40) }}
                                                    </span>
                                                @endif
                                            </td>
                                            @if ($tabActiva === 'aprobadas')
                                            <td style="padding:10px 14px; font-size:12px; color:#2c3e50;">
                                                {{ $r['usuario_aprobador'] ?? '—' }}
                                            </td>
                                            <td style="padding:10px 14px; font-size:12px; color:#546e7a;">
                                                {{ $r['obs_credito'] ? \Illuminate\Support\Str::limit($r['obs_credito'], 50) : '—' }}
                                            </td>
                                            @endif
                                            <td style="padding:10px 14px; text-align:center;">
                                                <button type="button"
                                                        wire:click="seleccionarFlujo({{ $r['flujo_id'] }})"
                                                        class="btn btn-sm"
                                                        style="background:#1a5276; color:#fff; border-radius:20px;
                                                               font-size:12px; padding:4px 14px; font-weight:600;">
                                                    <i class="fa fa-eye mr-1"></i>
                                                    {{ $esSoloLectura ? 'Ver' : 'Revisar' }}
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                            {{-- Paginación --}}
                            @if ($totalRegistros > 0)
                            @php
                                $rangoInicio = max(1, $paginaActual - 2);
                                $rangoFin    = min($totalPaginas, $paginaActual + 2);
                            @endphp
                            <div class="d-flex align-items-center justify-content-between mt-3" style="font-size:13px; flex-wrap:wrap; gap:8px;">
                                <div style="color:#78909c;">
                                    Mostrando
                                    <strong>{{ ($paginaActual - 1) * $perPage + 1 }}</strong>
                                    –
                                    <strong>{{ min($paginaActual * $perPage, $totalRegistros) }}</strong>
                                    de <strong>{{ $totalRegistros }}</strong> registros
                                </div>
                                @if ($totalPaginas > 1)
                                <ul class="pagination pagination-sm mb-0" style="flex-wrap:wrap;">
                                    <li class="page-item {{ $paginaActual <= 1 ? 'disabled' : '' }}">
                                        <button type="button"
                                                wire:click="irPagina('{{ $tabActiva }}', {{ $paginaActual - 1 }})"
                                                class="page-link">
                                            <i class="fa fa-chevron-left"></i>
                                        </button>
                                    </li>
                                    @if ($rangoInicio > 1)
                                    <li class="page-item">
                                        <button type="button" wire:click="irPagina('{{ $tabActiva }}', 1)" class="page-link">1</button>
                                    </li>
                                    @if ($rangoInicio > 2)
                                    <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                                    @endif
                                    @endif
                                    @for ($i = $rangoInicio; $i <= $rangoFin; $i++)
                                    <li class="page-item {{ $paginaActual === $i ? 'active' : '' }}">
                                        <button type="button"
                                                wire:click="irPagina('{{ $tabActiva }}', {{ $i }})"
                                                class="page-link">{{ $i }}</button>
                                    </li>
                                    @endfor
                                    @if ($rangoFin < $totalPaginas)
                                    @if ($rangoFin < $totalPaginas - 1)
                                    <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                                    @endif
                                    <li class="page-item">
                                        <button type="button" wire:click="irPagina('{{ $tabActiva }}', {{ $totalPaginas }})" class="page-link">{{ $totalPaginas }}</button>
                                    </li>
                                    @endif
                                    <li class="page-item {{ $paginaActual >= $totalPaginas ? 'disabled' : '' }}">
                                        <button type="button"
                                                wire:click="irPagina('{{ $tabActiva }}', {{ $paginaActual + 1 }})"
                                                class="page-link">
                                            <i class="fa fa-chevron-right"></i>
                                        </button>
                                    </li>
                                </ul>
                                @endif
                            </div>
                            @endif

                        </div>

                    </div>
                </div>
            </div>
        </div>

        @endif {{-- end if flujoId --}}

    </div>
</div>

