<div>
@if ($showModal && $pedidoData)
<style>
    @@keyframes flujoIn {
        from { opacity:0; transform:scale(.94) translateY(-24px); }
        to   { opacity:1; transform:scale(1)  translateY(0);      }
    }
    @@keyframes stepIn {
        from { opacity:0; transform:translateY(16px); }
        to   { opacity:1; transform:translateY(0);    }
    }
    @@keyframes checkPop {
        from { transform:scale(0) rotate(-45deg); opacity:0; }
        to   { transform:scale(1) rotate(0deg);   opacity:1; }
    }
    @@keyframes connFill {
        from { transform:scaleX(0); transform-origin:left; }
        to   { transform:scaleX(1); transform-origin:left; }
    }
    @@keyframes dotBlink {
        0%,100% { opacity:1; } 50% { opacity:.35; }
    }
    @@keyframes cancelShake {
        0%,100% { transform:translateX(0); }
        20%,60% { transform:translateX(-6px); }
        40%,80% { transform:translateX(6px);  }
    }
    .fmp-dlg  { max-width:920px; width:100%; animation:flujoIn .32s cubic-bezier(.34,1.28,.64,1) both; }
    .fmp-cnt  { border-radius:18px !important; overflow:hidden !important; }
    .fmp-body { padding:20px 24px 24px !important; overflow-y:auto; max-height:calc(90vh - 140px); }
    .fmp-foot { padding:12px 24px 18px !important; display:flex !important; flex-wrap:wrap !important; gap:8px !important; justify-content:flex-end !important; }
    .fmp-pipeline { scrollbar-width:thin; scrollbar-color:#e0e3ee transparent; }
    .fmp-pipeline::-webkit-scrollbar { height:4px; }
    .fmp-pipeline::-webkit-scrollbar-thumb { background:#d0d4e4; border-radius:4px; }
    .fmp-step-clickable { cursor:pointer; transition:transform .15s ease; }
    .fmp-step-clickable:hover { transform:translateY(-3px); }
    .fmp-offers-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
    .fmp-offers-wrap table { min-width:480px; }
    .fmp-info-grid { display:flex; gap:14px; flex-wrap:wrap; font-size:12px; color:#666; }
</style>

@php
    $d            = $pedidoData;
    $fCancelado   = ($d['estado'] === 'cancelado');
    $tieneOfertas  = count($ofertasPedido) > 0 || ($d['total_ofertas'] > 0);
    $tieneGanadora = ($d['has_ganadora'] > 0);
    $tienePrefact  = in_array(4, $flujoTipos);
    $tieneFactura  = in_array(3, $flujoTipos);
    $tieneEntrega  = in_array(5, $flujoTipos);

    $fPaso = match(true) {
        $fCancelado   => 0,
        $tieneEntrega => 5,
        $tieneFactura => 4,
        $tienePrefact => 3,
        $tieneGanadora => 3,
        $tieneOfertas  => 2,
        default        => 1,
    };

    $fPasos = [
        1 => ['key' => 'pedido',      'icon' => 'fa-shopping-cart', 'title' => 'Pedido'],
        2 => ['key' => 'ofertas',     'icon' => 'fa-tag',           'title' => 'Ofertas'],
        3 => ['key' => 'prefactura',  'icon' => 'fa-file-o',        'title' => 'Pre Factura'],
        4 => ['key' => 'factura',     'icon' => 'fa-file-text',     'title' => 'Factura'],
        5 => ['key' => 'entrega',     'icon' => 'fa-truck',         'title' => 'Entregas'],
        6 => ['key' => 'cobro',       'icon' => 'fa-money',         'title' => 'Cobro'],
    ];

    $pasoMap = [
        'pedido' => 1, 'ofertas' => 2, 'prefactura' => 3,
        'factura' => 4, 'entrega' => 5, 'cobro' => 6,
    ];
    $pasoActivoNum = $pasoMap[$pasoActivo] ?? 1;
@endphp

{{-- ── Overlay ─────────────────────────────────────────────────────────── --}}
<div id="fmpModalWrap" tabindex="-1" role="dialog"
     style="position:fixed; inset:0; z-index:9999;
            display:flex; align-items:center; justify-content:center; padding:20px;
            background:rgba(15,15,35,.58); backdrop-filter:blur(3px); -webkit-backdrop-filter:blur(3px);">

    <div class="fmp-dlg" role="document">
        <div class="modal-content fmp-cnt" style="border:none; box-shadow:0 20px 60px rgba(0,0,0,.35);">

            {{-- ── Header ─────────────────────────────────────────────── --}}
            <div class="modal-header" style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%);
                        border:none; padding:16px 24px;">
                <div>
                    <h5 class="modal-title m-0" style="color:#fff; font-size:17px; font-weight:700;">
                        <i class="fa fa-map-o mr-2"></i>Flujo del Pedido
                        <span style="background:rgba(255,255,255,.22); border-radius:20px;
                                     padding:2px 12px; font-size:14px; margin-left:6px;">
                            #{{ $d['id'] }}
                        </span>
                    </h5>
                    <small style="color:rgba(255,255,255,.85); font-size:12px;">
                        <i class="fa fa-user mr-1"></i>{{ $d['cliente'] }}
                        &nbsp;&bull;&nbsp;
                        <i class="fa fa-calendar mr-1"></i>
                        {{ \Carbon\Carbon::parse($d['created_at'])->format('d/m/Y H:i') }}
                    </small>
                </div>
                <button type="button" wire:click="cerrar"
                        class="close" style="color:#fff; opacity:1; font-size:22px; margin-top:-8px;">
                    <span>&times;</span>
                </button>
            </div>

            {{-- ── Body ───────────────────────────────────────────────── --}}
            <div class="modal-body fmp-body" style="background:#f8f9fc;">

                {{-- Banner cancelado --}}
                @if ($fCancelado)
                <div style="text-align:center; padding:16px 0 8px;">
                    <div style="display:inline-block; background:linear-gradient(135deg,#e74c3c,#c0392b);
                                border-radius:50%; width:72px; height:72px; line-height:72px;
                                font-size:32px; color:#fff; margin-bottom:12px;
                                box-shadow:0 8px 24px rgba(231,76,60,.45);
                                animation:cancelShake .5s ease;">
                        <i class="fa fa-ban"></i>
                    </div>
                    <h4 style="color:#e74c3c; font-weight:700; margin:0 0 4px;">Pedido Cancelado</h4>
                    <p class="text-muted" style="font-size:13px; margin:0;">
                        Este pedido fue cancelado y no continuará en el flujo.
                    </p>
                </div>
                @else

                {{-- ── Stepper pipeline ─────────────────────────────── --}}
                <div class="fmp-pipeline" style="display:flex; align-items:center; justify-content:center;
                            flex-wrap:nowrap; overflow-x:auto; padding:18px 8px 10px;">
                    @foreach ($fPasos as $paso => $info)
                    @php
                        $completado = ($paso < $fPaso);
                        $activo     = ($paso === $fPaso);
                        $pendiente  = ($paso > $fPaso);
                        $esSeleccionado = ($info['key'] === $pasoActivo);
                        $delay      = ($paso - 1) * 100;
                        $labelColor = $completado ? '#1ab394' : ($activo ? '#1a7efb' : '#aab');
                        $puedeClick = ($completado || $activo);
                    @endphp

                    {{-- Step card --}}
                    <div class="{{ $puedeClick ? 'fmp-step-clickable' : '' }}"
                         @if($puedeClick) wire:click="seleccionarPaso('{{ $info['key'] }}')" @endif
                         style="display:flex; flex-direction:column; align-items:center; min-width:100px;
                                animation:stepIn .5s cubic-bezier(.34,1.56,.64,1) {{ $delay }}ms both;
                                {{ $esSeleccionado ? 'background:rgba(26,126,251,.06); border-radius:12px; padding:4px 6px;' : 'padding:4px 6px;' }}">

                        {{-- Circle --}}
                        @if ($completado)
                        <div style="width:60px; height:60px; border-radius:50%;
                                    background:linear-gradient(135deg,#1ab394,#0fa37a); color:#fff;
                                    margin-bottom:8px; box-shadow:0 4px 16px rgba(26,179,148,.4);
                                    display:flex; align-items:center; justify-content:center;
                                    font-size:22px; flex-shrink:0;
                                    {{ $esSeleccionado ? 'box-shadow:0 4px 16px rgba(26,179,148,.4), 0 0 0 4px rgba(26,179,148,.25);' : '' }}">
                            <i class="fa fa-check"
                               style="animation:checkPop .4s cubic-bezier(.34,1.56,.64,1) {{ $delay + 200 }}ms both;"></i>
                        </div>
                        @elseif ($activo)
                        <div style="width:60px; height:60px; border-radius:50%;
                                    background:linear-gradient(135deg,#1a7efb,#0d6efd); color:#fff;
                                    margin-bottom:8px;
                                    box-shadow:0 6px 20px rgba(26,126,251,.5), 0 0 0 5px rgba(26,126,251,.2), 0 0 0 10px rgba(26,126,251,.08);
                                    display:flex; align-items:center; justify-content:center;
                                    font-size:22px; flex-shrink:0;
                                    {{ $esSeleccionado ? 'outline:3px solid rgba(26,126,251,.35); outline-offset:4px;' : '' }}">
                            <i class="fa fa-check"
                               style="animation:checkPop .4s cubic-bezier(.34,1.56,.64,1) {{ $delay + 200 }}ms both;"></i>
                        </div>
                        @else
                        <div style="width:60px; height:60px; border-radius:50%;
                                    background:#e8eaf0; color:#c0c2cc; margin-bottom:8px;
                                    display:flex; align-items:center; justify-content:center;
                                    font-size:22px; flex-shrink:0;">
                            <i class="fa {{ $info['icon'] }}"></i>
                        </div>
                        @endif

                        {{-- Label --}}
                        <div style="text-align:center;">
                            <div style="font-size:12px; font-weight:700; color:{{ $labelColor }};
                                        {{ $esSeleccionado ? 'text-decoration:underline;' : '' }}">
                                {{ $info['title'] }}
                            </div>
                            <div style="font-size:10px; color:{{ $labelColor }}; opacity:{{ $pendiente ? '.5' : '1' }};">
                                @if ($completado)
                                    <i class="fa fa-check-circle"></i> Completado
                                @elseif ($activo)
                                    <i class="fa fa-map-marker" style="animation:dotBlink 1s ease-in-out infinite;"></i> Actual
                                @else
                                    <i class="fa fa-clock-o"></i> Pendiente
                                @endif
                            </div>
                            @if ($activo && $paso === 3)
                            <div style="font-size:10px; color:#f39c12; margin-top:3px; font-weight:700;
                                        background:rgba(243,156,18,.12); border-radius:8px; padding:1px 6px;">
                                <i class="fa fa-trophy"></i> Oferta ganadora
                            </div>
                            @endif
                        </div>
                    </div>{{-- /step --}}

                    {{-- Conector --}}
                    @if ($paso < 6)
                    @php $connDelay = $delay + 80; @endphp
                    <div style="flex:1; min-width:16px; max-width:40px; height:4px; border-radius:4px;
                                margin-bottom:30px; position:relative; overflow:hidden; background:#e0e3ee;">
                        @if ($completado)
                        <div style="position:absolute; top:0; left:0; width:100%; height:100%;
                                    background:linear-gradient(90deg,#1ab394,#1a7efb);
                                    animation:connFill .6s ease {{ $connDelay }}ms both;
                                    border-radius:4px;"></div>
                        @endif
                    </div>
                    @endif

                    @endforeach
                </div>
                @endif {{-- /cancelado --}}

                {{-- ── Info grid ─────────────────────────────────────── --}}
                <div class="fmp-info-grid" style="margin-top:12px; padding:12px 16px; background:#fff;
                            border-radius:12px; border:1px solid #e8eaf0;">
                    <span>
                        <i class="fa fa-hashtag text-primary mr-1"></i>
                        <strong>Pedido #{{ $d['id'] }}</strong>
                    </span>
                    <span>
                        <i class="fa fa-user text-info mr-1"></i>
                        {{ $d['cliente'] }}
                    </span>
                    <span>
                        <i class="fa fa-user-circle-o text-muted mr-1"></i>
                        Por: {{ $d['registrado_por'] ?? '—' }}
                    </span>
                    <span>
                        <i class="fa fa-calendar text-muted mr-1"></i>
                        {{ \Carbon\Carbon::parse($d['created_at'])->format('d/m/Y H:i') }}
                    </span>
                    @if ($fCancelado)
                    <span style="color:#e74c3c; font-weight:700;">
                        <i class="fa fa-ban mr-1"></i> Cancelado
                    </span>
                    @endif
                </div>

                {{-- ── Mensajes ──────────────────────────────────────── --}}
                @if ($mensajeExito)
                <div style="margin-top:10px; background:#d4edda; border:1px solid #c3e6cb;
                            border-radius:10px; padding:9px 14px; font-size:13px; color:#155724;">
                    <i class="fa fa-check-circle mr-1"></i> {{ $mensajeExito }}
                </div>
                @endif
                @if ($mensajeError && $confirmAccion !== 'anular' && $confirmAccionOferta !== 'anular_oferta')
                <div style="margin-top:10px; background:#f8d7da; border:1px solid #f5c6cb;
                            border-radius:10px; padding:9px 14px; font-size:13px; color:#721c24;">
                    <i class="fa fa-exclamation-triangle mr-1"></i> {{ $mensajeError }}
                </div>
                @endif

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- CONTENIDO SEGÚN PASO ACTIVO                       --}}
                {{-- ══════════════════════════════════════════════════ --}}

                {{-- ── PASO: PEDIDO ──────────────────────────────────── --}}
                @if ($pasoActivo === 'pedido')

                {{-- Observaciones del pedido --}}
                @if (!empty($d['observaciones']))
                <div style="margin-top:12px; padding:10px 14px; background:#fffde7;
                            border-left:4px solid #f9a825; border-radius:8px; font-size:12px; color:#555;">
                    <i class="fa fa-sticky-note text-warning mr-1"></i>
                    <strong>Observaciones:</strong> {{ $d['observaciones'] }}
                </div>
                @endif

                {{-- Productos del pedido --}}
                <div style="margin-top:12px; border-radius:12px; overflow:hidden; border:1px solid #e8eaf0;">
                    <div style="background:linear-gradient(135deg,#1a7efb 0%,#0d6efd 100%); padding:10px 16px;">
                        <span style="color:#fff; font-size:13px; font-weight:700;">
                            <i class="fa fa-list-ul mr-1"></i>
                            Productos del Pedido
                            <span style="background:rgba(255,255,255,.22); border-radius:20px; padding:1px 9px; font-size:11px; margin-left:6px;">
                                {{ count($pedidoDetalles) }}
                            </span>
                        </span>
                    </div>
                    <div style="background:#fff; max-height:200px; overflow-y:auto; padding:10px 14px;">
                        @if (count($pedidoDetalles) === 0)
                        <p class="text-muted text-center" style="font-size:12px; margin:12px 0;">
                            <i class="fa fa-inbox d-block mb-1" style="opacity:.3; font-size:22px;"></i>
                            Sin productos registrados.
                        </p>
                        @else
                        <table style="width:100%; font-size:12px; border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f8f9fc; color:#888;">
                                    <th style="padding:4px 8px; text-align:left;">Producto</th>
                                    <th style="padding:4px 8px; text-align:center;">Cant.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pedidoDetalles as $det)
                                <tr style="border-bottom:1px solid #f0f0f0;">
                                    <td style="padding:5px 8px; color:#2c3e50;">
                                        {{ $det['nombre_producto'] ?? ($det['producto'] ?? '—') }}
                                    </td>
                                    <td style="padding:5px 8px; text-align:center; font-weight:700; color:#1a7efb;">
                                        {{ $det['cantidad'] ?? '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                </div>

                {{-- Acciones del pedido --}}
                @if ($confirmAccion === 'anular')
                <div style="margin-top:14px; background:#fff8e1; border:1px solid #ffe082;
                            border-radius:12px; padding:14px;">
                    <p style="font-size:13px; color:#555; margin:0 0 8px;">
                        <i class="fa fa-exclamation-triangle text-warning mr-1"></i>
                        ¿Anular el <strong>Pedido #{{ $d['id'] }}</strong>? Esta acción no se puede deshacer.
                    </p>
                    @if ($mensajeError)
                    <div style="font-size:12px; color:#721c24; background:#f8d7da;
                                border-radius:8px; padding:6px 10px; margin-bottom:8px;">
                        {{ $mensajeError }}
                    </div>
                    @endif
                    <textarea wire:model.defer="motivoAnulacion" rows="2"
                              placeholder="Motivo de anulación (obligatorio)…"
                              style="width:100%; border:1px solid #ddd; border-radius:8px;
                                     padding:6px 10px; font-size:12px; resize:none;"></textarea>
                    <div style="display:flex; gap:8px; margin-top:8px;">
                        <button type="button" wire:click="anularPedido"
                                style="background:linear-gradient(135deg,#e74c3c,#c0392b); color:#fff;
                                       border:none; border-radius:8px; padding:7px 18px; font-size:12px;
                                       font-weight:700; cursor:pointer;">
                            <i class="fa fa-ban mr-1"></i> Confirmar anulación
                        </button>
                        <button type="button" wire:click="cancelarConfirmacion"
                                style="background:#f0f0f0; color:#555; border:none;
                                       border-radius:8px; padding:7px 18px; font-size:12px; cursor:pointer;">
                            Cancelar
                        </button>
                    </div>
                </div>
                @endif

                @if ($confirmAccion === 'duplicar')
                <div style="margin-top:14px; background:#e3f2fd; border:1px solid #90caf9;
                            border-radius:12px; padding:14px; text-align:center;">
                    <p style="font-size:13px; color:#555; margin:0 0 10px;">
                        <i class="fa fa-copy text-primary mr-1"></i>
                        ¿Duplicar el <strong>Pedido #{{ $d['id'] }}</strong>?<br>
                        Se abrirá el formulario con los mismos productos.
                    </p>
                    <div style="display:flex; gap:8px; justify-content:center;">
                        <button type="button" wire:click="duplicarPedido"
                                style="background:linear-gradient(135deg,#1a7efb,#0d6efd); color:#fff;
                                       border:none; border-radius:8px; padding:7px 18px; font-size:12px;
                                       font-weight:700; cursor:pointer;">
                            <i class="fa fa-copy mr-1"></i> Duplicar
                        </button>
                        <button type="button" wire:click="cancelarConfirmacion"
                                style="background:#f0f0f0; color:#555; border:none;
                                       border-radius:8px; padding:7px 18px; font-size:12px; cursor:pointer;">
                            Cancelar
                        </button>
                    </div>
                </div>
                @endif

                @endif
                {{-- /paso pedido --}}

                {{-- ── PASO: OFERTAS ─────────────────────────────────── --}}
                @if ($pasoActivo === 'ofertas')

                @if ($ofertaSeleccionada)
                {{-- ── Detalle de la oferta seleccionada ── --}}
                <div style="margin-top:12px;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                        <button type="button" wire:click="cerrarOferta"
                                style="background:#f0f0f0; color:#555; border:none; border-radius:8px;
                                       padding:5px 12px; font-size:12px; cursor:pointer;">
                            <i class="fa fa-arrow-left mr-1"></i> Volver
                        </button>
                        <span style="font-size:14px; font-weight:700; color:#2c3e50;">
                            Oferta #{{ $ofertaSeleccionada['id'] }}
                            @php
                                $obsOfert = $ofertaSeleccionada['hf_observaciones'] ?? '';
                                $esGanDet = ($obsOfert === 'ganadora');
                                $esAnuDet = str_starts_with($obsOfert, 'Anulado:');
                            @endphp
                            @if ($esGanDet)
                                <span style="background:#d4edda; color:#155724; border-radius:10px;
                                             padding:2px 8px; font-size:11px; font-weight:700; margin-left:4px;">
                                    <i class="fa fa-trophy"></i> Ganadora
                                </span>
                            @elseif ($esAnuDet)
                                <span style="background:#f8d7da; color:#721c24; border-radius:10px;
                                             padding:2px 8px; font-size:11px; font-weight:700; margin-left:4px;">
                                    <i class="fa fa-ban"></i> Anulada
                                </span>
                            @else
                                <span style="background:#e8f0fe; color:#1a7efb; border-radius:10px;
                                             padding:2px 8px; font-size:11px; font-weight:700; margin-left:4px;">
                                    Activa
                                </span>
                            @endif
                        </span>
                    </div>

                    {{-- Info de la oferta --}}
                    <div style="background:#fff; border-radius:10px; border:1px solid #e8eaf0;
                                padding:12px 14px; margin-bottom:10px; font-size:12px; color:#555;">
                        <div style="display:flex; flex-wrap:wrap; gap:12px;">
                            <span><i class="fa fa-user text-info mr-1"></i>{{ $ofertaSeleccionada['nombre_cliente'] }}</span>
                            @if (!empty($ofertaSeleccionada['fecha_emision']))
                            <span><i class="fa fa-calendar text-muted mr-1"></i>{{ \Carbon\Carbon::parse($ofertaSeleccionada['fecha_emision'])->format('d/m/Y') }}</span>
                            @endif
                            <span><i class="fa fa-dollar text-success mr-1"></i>
                                Sub: L {{ number_format($ofertaSeleccionada['sub_total'] ?? 0, 2) }}
                            </span>
                            @if (($ofertaSeleccionada['monto_descuento'] ?? 0) > 0)
                            <span><i class="fa fa-minus-circle text-warning mr-1"></i>
                                Desc: L {{ number_format($ofertaSeleccionada['monto_descuento'], 2) }}
                                ({{ $ofertaSeleccionada['porc_descuento'] }}%)
                            </span>
                            @endif
                            <span><i class="fa fa-file-text text-muted mr-1"></i>
                                ISV: L {{ number_format($ofertaSeleccionada['isv'] ?? 0, 2) }}
                            </span>
                            <strong style="color:#e65100;">
                                Total: L {{ number_format($ofertaSeleccionada['total'] ?? 0, 2) }}
                            </strong>
                        </div>
                    </div>

                    {{-- Productos de la oferta --}}
                    @if (!empty($ofertaSeleccionada['productos']))
                    <div style="border-radius:10px; overflow:hidden; border:1px solid #e8eaf0;
                                max-height:170px; overflow-y:auto;">
                        <table style="width:100%; font-size:11px; border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f8f9fc; color:#888; position:sticky; top:0;">
                                    <th style="padding:4px 8px; text-align:left;">Producto</th>
                                    <th style="padding:4px 8px; text-align:center;">Cant.</th>
                                    <th style="padding:4px 8px; text-align:right;">P.Unit.</th>
                                    <th style="padding:4px 8px; text-align:right;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ofertaSeleccionada['productos'] as $pr)
                                <tr style="border-bottom:1px solid #f0f0f0;">
                                    <td style="padding:4px 8px; color:#2c3e50;">{{ $pr['nombre_producto'] }}</td>
                                    <td style="padding:4px 8px; text-align:center; color:#1a7efb; font-weight:700;">{{ $pr['cantidad'] }}</td>
                                    <td style="padding:4px 8px; text-align:right; color:#555;">
                                        @if ($pr['precio_unidad']) L {{ number_format($pr['precio_unidad'], 2) }} @else — @endif
                                    </td>
                                    <td style="padding:4px 8px; text-align:right; font-weight:700; color:#1ab394;">
                                        @if ($pr['total']) L {{ number_format($pr['total'], 2) }} @else — @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    {{-- Acciones de la oferta --}}
                    @if ($confirmAccionOferta === null)
                    <div style="display:flex; flex-wrap:wrap; gap:6px; margin-top:12px;">
                        <a href="/oferta/imprimir/{{ $ofertaSeleccionada['id'] }}" target="_blank"
                           style="text-align:center; background:#f8f9fc; color:#1a7efb;
                                  border:1px solid #e8eaf0; border-radius:8px; padding:5px 10px;
                                  font-size:11px; font-weight:700; text-decoration:none;">
                            <i class="fa fa-print mr-1"></i> Imprimir
                        </a>
                        @if (!$esGanDet && !$esAnuDet && !$tieneGanadora)
                        <button type="button" wire:click="confirmarAccionOferta('ganadora')"
                                style="background:linear-gradient(135deg,#1ab394,#0fa37a); color:#fff;
                                       border:none; border-radius:8px; padding:5px 10px;
                                       font-size:11px; font-weight:700; cursor:pointer;">
                            <i class="fa fa-trophy mr-1"></i> Ganadora
                        </button>
                        @endif
                        @if ($esGanDet && !$esAnuDet)
                        <button type="button" wire:click="confirmarAccionOferta('quitar_ganadora')"
                                style="background:linear-gradient(135deg,#e67e22,#d35400); color:#fff;
                                       border:none; border-radius:8px; padding:5px 10px;
                                       font-size:11px; font-weight:700; cursor:pointer;">
                            <i class="fa fa-times-circle mr-1"></i> Quitar Ganadora
                        </button>
                        @endif
                        @if (!$esAnuDet)
                        <button type="button" wire:click="confirmarAccionOferta('anular_oferta')"
                                style="background:linear-gradient(135deg,#e74c3c,#c0392b); color:#fff;
                                       border:none; border-radius:8px; padding:5px 10px;
                                       font-size:11px; font-weight:700; cursor:pointer;">
                            <i class="fa fa-ban mr-1"></i> Anular
                        </button>
                        @endif
                        <button type="button" wire:click="confirmarAccionOferta('duplicar_oferta')"
                                style="background:linear-gradient(135deg,#1a7efb,#0d6efd); color:#fff;
                                       border:none; border-radius:8px; padding:5px 10px;
                                       font-size:11px; font-weight:700; cursor:pointer;">
                            <i class="fa fa-copy mr-1"></i> Duplicar
                        </button>
                    </div>
                    @endif

                    {{-- Confirmación: Ganadora --}}
                    @if ($confirmAccionOferta === 'ganadora')
                    <div style="margin-top:12px; background:#fff8e1; border:1px solid #ffe082;
                                border-radius:12px; padding:14px; text-align:center;">
                        <p style="font-size:13px; color:#555; margin:0 0 10px;">
                            <i class="fa fa-trophy text-warning mr-1"></i>
                            ¿Marcar la <strong>Oferta #{{ $ofertaSeleccionada['id'] }}</strong> como <strong>ganadora</strong>?
                        </p>
                        <div style="display:flex; gap:8px; justify-content:center;">
                            <button type="button" wire:click="ganadoraOferta"
                                    style="background:linear-gradient(135deg,#1ab394,#0fa37a); color:#fff;
                                           border:none; border-radius:8px; padding:7px 18px;
                                           font-size:12px; font-weight:700; cursor:pointer;">
                                <i class="fa fa-trophy mr-1"></i> Confirmar
                            </button>
                            <button type="button" wire:click="cancelarConfirmOferta"
                                    style="background:#f0f0f0; color:#555; border:none;
                                           border-radius:8px; padding:7px 16px; font-size:12px; cursor:pointer;">
                                Cancelar
                            </button>
                        </div>
                    </div>
                    @endif

                    {{-- Confirmación: Quitar Ganadora --}}
                    @if ($confirmAccionOferta === 'quitar_ganadora')
                    <div style="margin-top:12px; background:#fff3e0; border:1px solid #ffcc80;
                                border-radius:12px; padding:14px;">
                        <p style="font-size:13px; color:#555; margin:0 0 8px;">
                            <i class="fa fa-times-circle text-warning mr-1"></i>
                            ¿Quitar el estado <strong>Ganadora</strong> de la <strong>Oferta #{{ $ofertaSeleccionada['id'] }}</strong>?
                        </p>
                        @if ($mensajeError)
                        <div style="font-size:12px; color:#721c24; background:#f8d7da;
                                    border-radius:8px; padding:6px 10px; margin-bottom:8px;">
                            {{ $mensajeError }}
                        </div>
                        @endif
                        <textarea wire:model.defer="motivoAnulOferta" rows="2"
                                  placeholder="Motivo (obligatorio)…"
                                  style="width:100%; border:1px solid #ddd; border-radius:8px;
                                         padding:6px 10px; font-size:12px; resize:none;"></textarea>
                        <div style="display:flex; gap:8px; margin-top:8px;">
                            <button type="button" wire:click="quitarGanadora"
                                    style="background:linear-gradient(135deg,#e67e22,#d35400); color:#fff;
                                           border:none; border-radius:8px; padding:7px 18px;
                                           font-size:12px; font-weight:700; cursor:pointer;">
                                <i class="fa fa-times-circle mr-1"></i> Confirmar
                            </button>
                            <button type="button" wire:click="cancelarConfirmOferta"
                                    style="background:#f0f0f0; color:#555; border:none;
                                           border-radius:8px; padding:7px 16px; font-size:12px; cursor:pointer;">
                                Cancelar
                            </button>
                        </div>
                    </div>
                    @endif

                    {{-- Confirmación: Anular oferta --}}
                    @if ($confirmAccionOferta === 'anular_oferta')
                    <div style="margin-top:12px; background:#fff5f5; border:1px solid #feb2b2;
                                border-radius:12px; padding:14px;">
                        <p style="font-size:13px; color:#555; margin:0 0 8px;">
                            <i class="fa fa-ban text-danger mr-1"></i>
                            ¿Anular la <strong>Oferta #{{ $ofertaSeleccionada['id'] }}</strong>?
                        </p>
                        @if ($mensajeError)
                        <div style="font-size:12px; color:#721c24; background:#f8d7da;
                                    border-radius:8px; padding:6px 10px; margin-bottom:8px;">
                            {{ $mensajeError }}
                        </div>
                        @endif
                        <textarea wire:model.defer="motivoAnulOferta" rows="2"
                                  placeholder="Motivo de anulación (obligatorio)…"
                                  style="width:100%; border:1px solid #ddd; border-radius:8px;
                                         padding:6px 10px; font-size:12px; resize:none;"></textarea>
                        @if (!$esAnuDet)
                        <div style="display:flex; gap:8px; margin-top:8px;">
                            <button type="button" wire:click="anularOferta"
                                    style="background:linear-gradient(135deg,#e74c3c,#c0392b); color:#fff;
                                           border:none; border-radius:8px; padding:7px 18px;
                                           font-size:12px; font-weight:700; cursor:pointer;">
                                <i class="fa fa-ban mr-1"></i> Confirmar anulación
                            </button>
                            <button type="button" wire:click="cancelarConfirmOferta"
                                    style="background:#f0f0f0; color:#555; border:none;
                                           border-radius:8px; padding:7px 16px; font-size:12px; cursor:pointer;">
                                Cancelar
                            </button>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Confirmación: Duplicar oferta --}}
                    @if ($confirmAccionOferta === 'duplicar_oferta')
                    <div style="margin-top:12px; background:#e3f2fd; border:1px solid #90caf9;
                                border-radius:12px; padding:14px; text-align:center;">
                        <p style="font-size:13px; color:#555; margin:0 0 10px;">
                            <i class="fa fa-copy text-primary mr-1"></i>
                            ¿Duplicar la <strong>Oferta #{{ $ofertaSeleccionada['id'] }}</strong>?
                        </p>
                        <div style="display:flex; gap:8px; justify-content:center; flex-wrap:wrap;">
                            <button type="button" wire:click="duplicarOferta(true)"
                                    style="background:linear-gradient(135deg,#1a7efb,#0d6efd); color:#fff;
                                           border:none; border-radius:8px; padding:7px 16px;
                                           font-size:12px; font-weight:700; cursor:pointer;">
                                <i class="fa fa-user mr-1"></i> Mismo cliente
                            </button>
                            <button type="button" wire:click="duplicarOferta(false)"
                                    style="background:linear-gradient(135deg,#1ab394,#0fa37a); color:#fff;
                                           border:none; border-radius:8px; padding:7px 16px;
                                           font-size:12px; font-weight:700; cursor:pointer;">
                                <i class="fa fa-exchange mr-1"></i> Otro cliente
                            </button>
                            <button type="button" wire:click="cancelarConfirmOferta"
                                    style="background:#f0f0f0; color:#555; border:none;
                                           border-radius:8px; padding:7px 14px; font-size:12px; cursor:pointer;">
                                Cancelar
                            </button>
                        </div>
                    </div>
                    @endif

                </div>
                {{-- /oferta seleccionada --}}

                @else
                {{-- ── Lista de ofertas ─────────────────────────────── --}}
                <div style="margin-top:12px; border-radius:12px; overflow:hidden; border:1px solid #ede9f7;">
                    <div style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%); padding:10px 16px;">
                        <span style="color:#fff; font-size:13px; font-weight:700;">
                            <i class="fa fa-tag mr-1"></i> Ofertas asociadas
                            @if (count($ofertasPedido) > 0)
                            <span style="background:rgba(255,255,255,.22); border-radius:20px; padding:1px 9px; font-size:11px; margin-left:6px;">
                                {{ count($ofertasPedido) }}
                            </span>
                            @endif
                        </span>
                    </div>
                    <div class="fmp-offers-wrap" style="background:#fff; padding:10px 14px; max-height:250px; overflow-y:auto;">
                        @if (count($ofertasPedido) === 0)
                        <div class="text-center py-3 text-muted" style="font-size:12px;">
                            <i class="fa fa-inbox fa-lg d-block mb-1" style="opacity:.3;"></i>
                            Sin ofertas aún para este pedido.
                        </div>
                        @else
                        @foreach ($ofertasPedido as $of)
                        @php
                            $obs2      = $of['hf_observaciones'] ?? '';
                            $isGan2    = ($obs2 === 'ganadora');
                            $isAnu2    = str_starts_with($obs2, 'Anulado:');
                            $listBadgeBg    = $isGan2 ? '#d4edda' : ($isAnu2 ? '#f8d7da' : '#e8f0fe');
                            $listBadgeColor = $isGan2 ? '#155724' : ($isAnu2 ? '#721c24' : '#1a7efb');
                            $listBadgeText  = $isGan2 ? 'Ganadora' : ($isAnu2 ? 'Anulada' : 'Activa');
                        @endphp
                        <div wire:click="verOferta({{ $of['cotizacion_id'] }})"
                             style="padding:9px 12px; border:1px solid #f0f0f0; border-radius:10px;
                                    margin-bottom:6px; cursor:pointer; transition:box-shadow .15s ease;
                                    opacity:{{ $isAnu2 ? '.55' : '1' }};"
                             onmouseover="this.style.boxShadow='0 2px 12px rgba(0,0,0,.1)'"
                             onmouseout="this.style.boxShadow='none'">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <span style="font-weight:800; color:#2c3e50; font-size:13px;">
                                        Oferta #{{ $of['cotizacion_id'] }}
                                    </span>
                                    <span style="background:{{ $listBadgeBg }}; color:{{ $listBadgeColor }};
                                                 border-radius:12px; padding:1px 8px; font-size:10px;
                                                 font-weight:700; margin-left:6px;">
                                        @if ($isGan2)<i class="fa fa-trophy mr-1"></i>@endif
                                        {{ $listBadgeText }}
                                    </span>
                                </div>
                                <span style="font-weight:800; color:#e65100; font-size:13px;">
                                    L {{ number_format($of['total'], 2) }}
                                </span>
                            </div>
                            <div style="font-size:11px; color:#90a4ae; margin-top:3px;">
                                {{ $of['nombre_cliente'] }}
                                &nbsp;·&nbsp;
                                {{ \Carbon\Carbon::parse($of['hf_fecha'])->format('d/m/Y') }}
                            </div>
                        </div>
                        @endforeach
                        @endif
                    </div>
                </div>


                @endif
                {{-- /ofertaSeleccionada else --}}

                @endif
                {{-- /paso ofertas --}}

                {{-- ── PASOS PENDIENTES (prefactura, factura, entrega, cobro) ── --}}
                @if (!in_array($pasoActivo, ['pedido', 'ofertas']))
                <div style="margin-top:20px; text-align:center; padding:24px; color:#90a4ae;">
                    <i class="fa fa-clock-o fa-2x d-block mb-2" style="opacity:.4;"></i>
                    <p style="font-size:13px; margin:0; font-weight:600;">
                        Esta etapa aún está pendiente.
                    </p>
                    <p style="font-size:12px; margin:4px 0 0; opacity:.7;">
                        El pedido avanzará aquí cuando progrese en el flujo.
                    </p>
                </div>
                @endif

            </div>{{-- /modal-body --}}

            {{-- ── Footer ─────────────────────────────────────────────── --}}
            <div class="modal-footer fmp-foot" style="border:none; background:#f8f9fc;">

                <button type="button" wire:click="cerrar"
                        style="border-radius:20px; padding:6px 20px; background:#f0f0f0;
                               border:none; color:#555; font-size:13px; cursor:pointer;">
                    <i class="fa fa-times mr-1"></i> Cerrar
                </button>

                @if (!$fCancelado && $pasoActivo === 'pedido')
                <a href="/flujo/pedido/editar/{{ $d['id'] }}"
                   target="_blank"
                   style="border-radius:20px; padding:6px 20px; background:linear-gradient(135deg,#f39c12,#e67e22);
                          color:#fff; font-size:13px; font-weight:700; text-decoration:none;
                          display:inline-block;">
                    <i class="fa fa-pencil mr-1"></i> Editar pedido
                </a>

                <button type="button" wire:click="confirmarAccion('duplicar')"
                        style="border-radius:20px; padding:6px 20px; background:linear-gradient(135deg,#1a7efb,#0d6efd);
                               color:#fff; border:none; font-size:13px; font-weight:700; cursor:pointer;">
                    <i class="fa fa-copy mr-1"></i> Duplicar
                </button>

                @if (!$tieneGanadora)
                <button type="button" wire:click="nuevaOferta"
                        style="border-radius:20px; padding:6px 20px; background:linear-gradient(135deg,#e65100,#f9a826);
                               color:#fff; border:none; font-size:13px; font-weight:700; cursor:pointer;">
                    <i class="fa fa-tag mr-1"></i> Ag. Oferta
                </button>
                @endif

                <button type="button" wire:click="confirmarAccion('anular')"
                        style="border-radius:20px; padding:6px 20px; background:linear-gradient(135deg,#e74c3c,#c0392b);
                               color:#fff; border:none; font-size:13px; font-weight:700; cursor:pointer;">
                    <i class="fa fa-ban mr-1"></i> Anular
                </button>
                @endif

            </div>

        </div>
    </div>
</div>{{-- /overlay --}}

@endif

<script>
    window.addEventListener('abrir-nueva-pestana', function(e) {
        window.open(e.detail.url, '_blank');
    });
</script>
</div>
