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
    .fmp-dlg  { max-width:920px; width:100%; animation:flujoIn .32s cubic-bezier(.34,1.28,.64,1) both; pointer-events:auto; }
    .fmp-cnt  { border-radius:18px !important; overflow:hidden !important; }
    .fmp-body { padding:20px 24px 24px !important; overflow-y:auto; max-height:calc(90vh - 140px); }
    .fmp-foot { padding:12px 24px 18px !important; display:flex !important; flex-wrap:wrap !important; gap:8px !important; justify-content:flex-end !important; }
    .fmp-pipeline { scrollbar-width:thin; scrollbar-color:#e0e3ee transparent; -webkit-overflow-scrolling:touch; scroll-behavior:smooth; }
    .fmp-pipeline::-webkit-scrollbar { height:4px; }
    .fmp-pipeline::-webkit-scrollbar-thumb { background:#d0d4e4; border-radius:4px; }
    .fmp-step-num     { font-size:20px; font-weight:700; line-height:1; }
    .fmp-step-icon-sm { font-size:11px; margin-top:2px; }
    .fmp-step-clickable { cursor:pointer; transition:transform .15s ease; }
    .fmp-step-clickable:hover { transform:translateY(-3px); }
    .fmp-overlay {
        position:fixed !important;
        top:0 !important;
        right:0 !important;
        bottom:0 !important;
        left:0 !important;
        width:100vw !important;
        height:100vh !important;
        z-index:2147483646 !important;
        display:flex !important;
        align-items:flex-start !important;
        justify-content:center !important;
        padding:72px 16px 16px !important;
        overflow:auto !important;
    }
    @@media (max-width: 575px) {
        .fmp-overlay { padding:12px !important; }
        .fmp-step-circle { width:44px !important; height:44px !important; }
        .fmp-step-circle > div { width:44px !important; height:44px !important; }
        .fmp-step-num { font-size:15px !important; }
        .fmp-step-card  { min-width:72px !important; }
    }
    .fmp-offers-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
    .fmp-offers-wrap table { min-width:480px; }
    .fmp-offer-card {
        width:100%;
        text-align:left;
        padding:9px 12px;
        border:1px solid #f0f0f0;
        border-radius:10px;
        margin-bottom:6px;
        cursor:pointer;
        background:#fff;
        opacity:1;
        transition:transform .16s ease, box-shadow .16s ease, border-color .16s ease, background-color .16s ease, opacity .16s ease;
        will-change:transform;
    }
    .fmp-offer-card:hover,
    .fmp-offer-card:focus-visible {
        transform:translateY(-2px);
        border-color:#cfe0ff;
        box-shadow:0 10px 24px rgba(26,126,251,.14);
        background:linear-gradient(180deg,#ffffff 0%,#f8fbff 100%);
        outline:none;
    }
    .fmp-offer-card:active {
        transform:translateY(0);
        box-shadow:0 4px 12px rgba(26,126,251,.10);
    }
    .fmp-info-grid { display:flex; gap:14px; flex-wrap:wrap; font-size:12px; color:#666; }
    /* Modal gestor de entrega: encima del flujo modal y su backdrop */
    #modal-gestor-flujo { z-index: 1060 !important; }
    /* Select2 dentro de modales Bootstrap: permitir overflow del dropdown */
    .select2-container--open { z-index: 9999 !important; }
    #modal-gestor-flujo .modal-dialog { overflow: visible !important; }
    #modal-gestor-flujo .modal-content { overflow: visible !important; }
    #modal-gestor-flujo .modal-body { overflow: visible !important; }
</style>

@php
    $d            = $pedidoData;
    $fCancelado   = ($d['estado'] === 'cancelado');
    $tieneOfertas  = count($ofertasPedido) > 0 || ($d['total_ofertas'] > 0);
    $tieneGanadora = ($d['has_ganadora'] > 0);
    $tieneRevision         = in_array(9, $flujoTipos);   // Revision de Inventario (incluye devueltos)
    $tieneRevisionActiva   = $tieneRevision && !($revisionDevuelta ?? false);  // ciclo activo
    $tieneRevisionDevuelta = $tieneRevision && ($revisionDevuelta ?? false);   // último ciclo devuelto

    // Revisión de Crédito (tipo 10)
    $tieneRevisionCredito         = in_array(10, $flujoTipos);
    $creditoRevisionEstado        = $creditoRevisionData['estado'] ?? null;  // 'pendiente'|'aprobado'|'rechazado'
    // Activa = hay un historico_flujo tipo=10 con estado_id=5 (pendiente), ó el registro ya existe con estado pendiente
    $tieneRevisionCreditoActiva   = $tieneRevisionCredito && ($revisionCreditoPendiente || $creditoRevisionEstado === 'pendiente');
    $tieneRevisionCreditoAprobada = $tieneRevisionCredito && ($creditoRevisionEstado === 'aprobado');
    $tieneRevisionCreditoRechazada= $tieneRevisionCredito && ($creditoRevisionEstado === 'rechazado');

    $tienePrefact  = in_array(4, $flujoTipos);
    $tieneFactura  = in_array(3, $flujoTipos) || in_array(5, $flujoTipos);

    // Estado de Entrega y Cobro leídos desde historico_flujo.estado_id
    // 5 = Pendiente (azul) | 1 = Completado (verde) | null = no existe aún (gris)
    $entregaEstadoId = $estadoEntrega;  // null | 1 | 5
    $cobroEstadoId   = $estadoCobro;    // null | 1 | 5
    $tieneEntrega    = ($entregaEstadoId !== null);
    $entregaEsCompletada = ($entregaEstadoId === 1);
    $cobroCompletado     = ($cobroEstadoId === 1);
    $finalizadoCompletado = $entregaEsCompletada && $cobroCompletado;
    $facturaCompletada = in_array(5, $flujoTipos) || in_array(3, $flujoTipos);
    $facturaAnulada = $facturaCompletada && isset($facturaData['estado_venta_id']) && (int)$facturaData['estado_venta_id'] === 2;

    // fPaso: número del paso activo en el stepper (1-6 para el pipeline principal)
    // 1=Pedido, 2=Ofertas, 3=RevCrédito, 4=RevInventario, 5=PreFactura, 6=Factura
    $fPaso = match(true) {
        $fCancelado                  => 0,
        $finalizadoCompletado        => 8,
        $cobroCompletado             => 7,
        $tieneEntrega                => 7,
        $tieneFactura                => 7,
        $tienePrefact                => 5,
        $tieneRevisionActiva         => 4,   // en revisión de inventario (ciclo activo)
        $tieneRevisionCreditoActiva   => 3,   // en revisión de crédito (pendiente)
        $tieneRevisionCreditoRechazada => 3,   // crédito rechazado: mantener en paso 3 (Pendiente en rev. inv y prefact)
        $tieneGanadora               => 5,   // ganadora sin revisiones → directamente prefactura
        $tieneOfertas                => 2,
        default                      => 1,
    };

    $fPasos = [
        1 => ['key' => 'pedido',              'icon' => 'fa-shopping-cart', 'title' => 'Pedido'],
        2 => ['key' => 'ofertas',             'icon' => 'fa-tag',           'title' => 'Ofertas'],
        3 => ['key' => 'revision_credito',    'icon' => 'fa-credit-card',   'title' => 'Rev. Crédito'],
        4 => ['key' => 'revision_inventario', 'icon' => 'fa-search',        'title' => 'Rev. Inventario'],
        5 => ['key' => 'prefactura',          'icon' => 'fa-file-o',        'title' => 'Pre Factura'],
        6 => ['key' => 'factura',             'icon' => 'fa-file-text',     'title' => 'Factura'],
    ];

    $pasoMap = [
        'pedido' => 1, 'ofertas' => 2, 'revision_credito' => 3,
        'revision_inventario' => 4, 'prefactura' => 5, 'factura' => 6,
        'entrega' => 7, 'cobro' => 8, 'finalizado' => 9,
    ];
    $pasoActivoNum = $pasoMap[$pasoActivo] ?? 1;
@endphp

{{-- ── Overlay ─────────────────────────────────────────────────────────── --}}
<div id="fmpModalWrap" role="dialog"
    class="fmp-overlay"
     style="position:fixed; inset:0; z-index:99999;
          display:flex;
            pointer-events:none;
            background:rgba(15,15,35,.62); backdrop-filter:blur(4px); -webkit-backdrop-filter:blur(4px);">

    <div class="fmp-dlg" role="document">
        <div class="modal-content fmp-cnt" style="border:none; box-shadow:0 20px 60px rgba(0,0,0,.35);">

            {{-- ── Header ─────────────────────────────────────────────── --}}
            <div class="modal-header" style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%);
                        border:none; padding:16px 24px;">
                <div>
                    <h5 class="m-0 modal-title" style="color:#fff; font-size:17px; font-weight:700;">
                        <i class="mr-2 fa fa-map-o"></i>
                        @if(!empty($d['sin_pedido']))
                        Flujo de Cotización
                        @else
                        Flujo del Pedido
                        @endif
                        <span style="background:rgba(255,255,255,.22); border-radius:20px;
                                     padding:2px 12px; font-size:14px; margin-left:6px;">
                            #{{ $flujoId ?? $d['flujo_id'] ?? $d['id'] }}
                        </span>
                    </h5>
                    <small style="color:rgba(255,255,255,.85); font-size:12px;">
                        <i class="mr-1 fa fa-user"></i>{{ $d['cliente'] }}
                        &nbsp;&bull;&nbsp;
                        <i class="mr-1 fa fa-calendar"></i>
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
                <div class="fmp-pipeline" style="display:flex; align-items:center; justify-content:flex-start;
                            flex-wrap:nowrap; overflow-x:auto; padding:18px 16px 10px;">
                    @foreach ($fPasos as $paso => $info)
                    @php
                        $fPasoLinea = min($fPaso, 6);
                        $esSinPedido = ($paso === 1 && !empty($d['sin_pedido']));
                        // Pasos que no aplican porque el flujo fue directo (sin ofertas/prefactura)
                        $esSinAplica = !$esSinPedido && !empty($d['sin_pedido']) && (
                            ($paso === 2 && !$tieneOfertas && $fPasoLinea > 2) ||
                            ($paso === 3 && !$tieneRevisionCredito && !$tieneRevision && !$tienePrefact && $fPasoLinea > 3) ||
                            ($paso === 4 && !$tieneRevision && !$tienePrefact && $fPasoLinea > 4) ||
                            ($paso === 5 && !$tienePrefact && $fPasoLinea > 5)
                        );
                        $completado = !$esSinPedido && !$esSinAplica && (($paso < $fPasoLinea) || ($paso === 6 && $fPaso > 6));
                        $activo     = !$esSinPedido && !$esSinAplica && ($paso === $fPasoLinea) && !($paso === 6 && $fPaso > 6);
                        $pendiente  = !$esSinPedido && !$esSinAplica && ($paso > $fPasoLinea);
                        $esSeleccionado = ($info['key'] === $pasoActivo);
                        $delay      = ($paso - 1) * 100;
                        // Rev. Inventario devuelta: mostrar como estado especial (naranja)
                        $esDevuelto = ($info['key'] === 'revision_inventario')
                            && ($tieneRevisionDevuelta ?? false)
                            && !($tieneRevisionActiva ?? false)
                            && $pendiente;
                        // Rev. Crédito rechazada: mostrar como rojo con X
                        $esRechazado = ($info['key'] === 'revision_credito')
                            && ($tieneRevisionCreditoRechazada ?? false);
                        // Factura anulada: mostrar paso 6 como rojo con X
                        $esFacturaAnulada = ($info['key'] === 'factura') && ($facturaAnulada ?? false);
                        if ($esDevuelto)     $pendiente = false;
                        if ($esRechazado)    { $pendiente = false; $completado = false; $activo = false; }
                        if ($esFacturaAnulada) { $pendiente = false; $completado = false; $activo = false; }
                        $labelColor = ($esSinPedido || $esSinAplica) ? '#e74c3c' : ($esRechazado || $esFacturaAnulada ? '#e74c3c' : ($completado ? '#1ab394' : ($activo ? '#1a7efb' : ($esDevuelto ? '#e67e22' : '#aab'))));
                        $puedeClick = ($completado || $activo || $esDevuelto || $esRechazado || $esFacturaAnulada) && !$esSinPedido && !$esSinAplica;
                    @endphp

                    {{-- Step card --}}
                    <div class="{{ $puedeClick ? 'fmp-step-clickable' : '' }}"
                         @if($puedeClick) wire:click="seleccionarPaso('{{ $info['key'] }}')" @endif
                         style="display:flex; flex-direction:column; align-items:center; min-width:100px;
                                animation:stepIn .5s cubic-bezier(.34,1.56,.64,1) {{ $delay }}ms both;
                                {{ $esSeleccionado ? 'background:rgba(26,126,251,.06); border-radius:12px; padding:4px 6px;' : 'padding:4px 6px;' }}"
                         class="fmp-step-card">

                        {{-- Circle --}}
                        @if ($esSinPedido || $esSinAplica)
                        <div style="width:60px; height:60px; border-radius:50%;
                                    background:linear-gradient(135deg,#e74c3c,#c0392b); color:#fff;
                                    margin-bottom:8px; box-shadow:0 4px 16px rgba(231,76,60,.4);
                                    display:flex; align-items:center; justify-content:center;
                                    font-size:24px; flex-shrink:0;">
                            <i class="fa fa-times"
                               style="animation:checkPop .4s cubic-bezier(.34,1.56,.64,1) {{ $delay + 200 }}ms both;"></i>
                        </div>
                        @elseif ($completado)
                        <div class="fmp-step-circle" style="position:relative; width:60px; height:60px; margin-bottom:8px; flex-shrink:0;">
                            <div style="width:60px; height:60px; border-radius:50%;
                                        background:linear-gradient(135deg,#1ab394,#0fa37a); color:#fff;
                                        box-shadow:0 4px 16px rgba(26,179,148,.4);
                                        display:flex; align-items:center; justify-content:center; font-size:22px;
                                        {{ $esSeleccionado ? 'box-shadow:0 4px 16px rgba(26,179,148,.4), 0 0 0 4px rgba(26,179,148,.25);' : '' }}">
                                <i class="fa fa-check"
                                   style="animation:checkPop .4s cubic-bezier(.34,1.56,.64,1) {{ $delay + 200 }}ms both;"></i>
                            </div>
                            <span style="position:absolute; top:-4px; right:-4px; background:#0fa37a; color:#fff; border-radius:50%;
                                         width:20px; height:20px; display:flex; align-items:center; justify-content:center;
                                         font-size:10px; font-weight:800; border:2px solid #fff; line-height:1;">{{ $paso }}</span>
                        </div>
                        @elseif ($activo)
                        <div class="fmp-step-circle" style="width:60px; height:60px; border-radius:50%;
                                    background:linear-gradient(135deg,#1a7efb,#0d6efd); color:#fff;
                                    margin-bottom:8px;
                                    box-shadow:0 6px 20px rgba(26,126,251,.5), 0 0 0 5px rgba(26,126,251,.2), 0 0 0 10px rgba(26,126,251,.08);
                                    display:flex; align-items:center; justify-content:center; flex-direction:column;
                                    flex-shrink:0;
                                    {{ $esSeleccionado ? 'outline:3px solid rgba(26,126,251,.35); outline-offset:4px;' : '' }}">
                            <span class="fmp-step-num" style="font-size:20px; font-weight:800; line-height:1;
                                  animation:checkPop .4s cubic-bezier(.34,1.56,.64,1) {{ $delay + 200 }}ms both;">{{ $paso }}</span>
                            <i class="fmp-step-icon-sm fa {{ $info['icon'] }}" style="font-size:11px; margin-top:2px; opacity:.85;"></i>
                        </div>
                        @elseif ($esDevuelto)
                        <div class="fmp-step-circle" style="position:relative; width:60px; height:60px; margin-bottom:8px; flex-shrink:0;">
                            <div style="width:60px; height:60px; border-radius:50%;
                                        background:linear-gradient(135deg,#e67e22,#d35400); color:#fff;
                                        box-shadow:0 4px 16px rgba(230,126,34,.4);
                                        display:flex; align-items:center; justify-content:center; font-size:22px;
                                        {{ $esSeleccionado ? 'box-shadow:0 4px 16px rgba(230,126,34,.4), 0 0 0 4px rgba(230,126,34,.25);' : '' }}">
                                <i class="fa fa-reply"
                                   style="animation:checkPop .4s cubic-bezier(.34,1.56,.64,1) {{ $delay + 200 }}ms both;"></i>
                            </div>
                            <span style="position:absolute; top:-4px; right:-4px; background:#d35400; color:#fff; border-radius:50%;
                                         width:20px; height:20px; display:flex; align-items:center; justify-content:center;
                                         font-size:10px; font-weight:800; border:2px solid #fff; line-height:1;">{{ $paso }}</span>
                        </div>
                        @elseif ($esRechazado || $esFacturaAnulada)
                        <div class="fmp-step-circle" style="position:relative; width:60px; height:60px; margin-bottom:8px; flex-shrink:0;">
                            <div style="width:60px; height:60px; border-radius:50%;
                                        background:linear-gradient(135deg,#e74c3c,#c0392b); color:#fff;
                                        box-shadow:0 4px 16px rgba(231,76,60,.4);
                                        display:flex; align-items:center; justify-content:center; font-size:22px;
                                        {{ $esSeleccionado ? 'box-shadow:0 4px 16px rgba(231,76,60,.4), 0 0 0 4px rgba(231,76,60,.25);' : '' }}">
                                <i class="fa fa-times"
                                   style="animation:checkPop .4s cubic-bezier(.34,1.56,.64,1) {{ $delay + 200 }}ms both;"></i>
                            </div>
                            <span style="position:absolute; top:-4px; right:-4px; background:#c0392b; color:#fff; border-radius:50%;
                                         width:20px; height:20px; display:flex; align-items:center; justify-content:center;
                                         font-size:10px; font-weight:800; border:2px solid #fff; line-height:1;">{{ $paso }}</span>
                        </div>
                        @else
                        <div class="fmp-step-circle" style="width:60px; height:60px; border-radius:50%;
                                    background:#e8eaf0; color:#c0c2cc; margin-bottom:8px;
                                    display:flex; align-items:center; justify-content:center; flex-direction:column;
                                    flex-shrink:0;">
                            <span class="fmp-step-num" style="font-size:20px; font-weight:700; line-height:1; color:#aab;">{{ $paso }}</span>
                            <i class="fmp-step-icon-sm fa {{ $info['icon'] }}" style="font-size:11px; margin-top:2px; color:#c0c2cc;"></i>
                        </div>
                        @endif

                        {{-- Label --}}
                        <div style="text-align:center;">
                            <div style="font-size:12px; font-weight:700; color:{{ $labelColor }};
                                        {{ $esSeleccionado ? 'text-decoration:underline;' : '' }}">
                                @if($esSinPedido)
                                Flujo #{{ $d['flujo_id'] ?? $flujoId ?? $d['id'] }}
                                @else
                                {{ $info['title'] }}
                                @endif
                            </div>
                            <div style="font-size:10px; color:{{ $labelColor }}; opacity:{{ $pendiente ? '.5' : '1' }};">
                                @if ($esSinPedido)
                                    <i class="fa fa-hashtag"></i> Flujo #{{ $d['flujo_id'] ?? $flujoId ?? $d['id'] }}
                                @elseif ($esSinAplica)
                                    <i class="fa fa-times-circle"></i> N/A
                                @elseif ($completado)
                                    <i class="fa fa-check-circle"></i> Completado
                                @elseif ($activo)
                                    <i class="fa fa-map-marker" style="animation:dotBlink 1s ease-in-out infinite;"></i> Actual
                                @elseif ($esDevuelto)
                                    <i class="fa fa-reply"></i> Devuelto
                                @elseif ($esFacturaAnulada)
                                    <i class="fa fa-times-circle"></i> Anulada
                                @elseif ($esRechazado)
                                    <i class="fa fa-times-circle"></i> Rechazado
                                @else
                                    <i class="fa fa-clock-o"></i> Pendiente
                                @endif
                            </div>
                            @if ($activo && $paso === 5)
                            <div style="font-size:10px; color:#f39c12; margin-top:3px; font-weight:700;
                                        background:rgba(243,156,18,.12); border-radius:8px; padding:1px 6px;">
                                <i class="fa fa-trophy"></i> Oferta ganadora
                            </div>
                            @endif
                            @if ($activo && $paso === 4)
                            <div style="font-size:10px; color:#9c27b0; margin-top:3px; font-weight:700;
                                        background:rgba(156,39,176,.1); border-radius:8px; padding:1px 6px;">
                                <i class="fa fa-search"></i> En revisión
                            </div>
                            @endif
                            @if ($activo && $paso === 3)
                            <div style="font-size:10px; color:#1565c0; margin-top:3px; font-weight:700;
                                        background:rgba(21,101,192,.1); border-radius:8px; padding:1px 6px;">
                                <i class="fa fa-credit-card"></i> Rev. Crédito
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

                {{-- Ramificación real desde Factura: Entregas/Cobro -> Finalizado --}}
                @php
                    $entregaSeleccionada = ($pasoActivo === 'entrega');
                    $cobroSeleccionado   = ($pasoActivo === 'cobro');
                    $etapaEntregaCobroActiva = ($pasoActivo === 'entrega' && $tieneEntrega && !$finalizadoCompletado);
                    $entregaActiva    = $entregaSeleccionada || $etapaEntregaCobroActiva;
                    $cobroActiva      = $cobroSeleccionado || $etapaEntregaCobroActiva;
                    $facturaActiva    = ($pasoActivo === 'factura');
                    $finalActiva      = ($pasoActivo === 'finalizado');
                    $puedeEntrega     = $tieneFactura;
                    $puedeCobro       = $tieneFactura;
                    $puedeFinal       = $tieneFactura;

                    $facturaLineaCompletada = ($fPaso > 5);

                    // Colores según estado_id: activo=azul, completado(1)=verde, pendiente(5)=azul, null=gris
                    $entregaColor = $entregaEsCompletada
                        ? '#1ab394'
                        : ($entregaActiva ? '#1a7efb' : ($tieneEntrega ? '#1a7efb' : '#aab'));
                    $cobroColor   = $cobroCompletado
                        ? '#1ab394'
                        : ($cobroActiva ? '#1a7efb' : ($cobroEstadoId === 5 ? '#1a7efb' : '#aab'));
                    $finalColor   = $finalizadoCompletado ? '#1ab394' : ($finalActiva ? '#1a7efb' : '#aab');

                    $lineaFacturaEstado = $facturaLineaCompletada
                        ? '#1ab394'
                        : ($facturaActiva ? '#1a7efb' : '#d6dbe8');
                    $lineaEntregaEstado = $entregaEsCompletada
                        ? '#1ab394'
                        : ($entregaActiva ? '#1a7efb' : ($tieneEntrega ? '#1a7efb' : '#d6dbe8'));
                    $lineaCobroEstado   = $cobroCompletado
                        ? '#1ab394'
                        : ($cobroActiva ? '#1a7efb' : ($cobroEstadoId === 5 ? '#1a7efb' : '#d6dbe8'));
                @endphp
                <div style="display:flex; align-items:center; gap:20px; margin-left:8px; padding:0 6px 30px 6px; position:relative;">

                        <div style="width:44px; height:170px; position:relative;">
                            <div style="position:absolute; left:-22px; top:80px; width:22px; height:3px; border-radius:3px;
                                        background:{{ $lineaFacturaEstado }};"></div>
                            <div style="position:absolute; left:0; top:38px; width:3px; height:87px; border-radius:3px;
                                        background:{{ $lineaFacturaEstado }};"></div>
                            <div style="position:absolute; left:3px; top:38px; width:41px; height:3px; border-radius:3px;
                                        background:{{ $lineaEntregaEstado }};"></div>
                            <div style="position:absolute; left:3px; top:122px; width:41px; height:3px; border-radius:3px;
                                        background:{{ $lineaCobroEstado }};"></div>
                        </div>

                        <div style="display:flex; flex-direction:column; gap:16px; min-width:100px;">
                            <div class="{{ $puedeEntrega ? 'fmp-step-clickable' : '' }}"
                                 @if($puedeEntrega) wire:click="seleccionarPaso('entrega')" @endif
                                 style="display:flex; flex-direction:column; align-items:center; min-width:100px;
                                    {{ ($pasoActivo === 'entrega') ? (($entregaEsCompletada ? 'background:rgba(26,179,148,.08);' : 'background:rgba(26,126,251,.06);') . ' border-radius:12px; padding:4px 6px;') : 'padding:4px 6px;' }}">
                                @if ($entregaEsCompletada)
                                <div class="fmp-step-circle" style="position:relative; width:60px; height:60px; margin-bottom:8px; flex-shrink:0;">
                                    <div style="width:60px; height:60px; border-radius:50%;
                                            background:linear-gradient(135deg,#1ab394,#0fa37a); color:#fff;
                                            box-shadow:0 4px 16px rgba(26,179,148,.4);
                                            display:flex; align-items:center; justify-content:center; font-size:22px;
                                            {{ ($pasoActivo === 'entrega') ? 'box-shadow:0 4px 16px rgba(26,179,148,.4), 0 0 0 4px rgba(26,179,148,.25);' : '' }}">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <span style="position:absolute; top:-4px; right:-4px; background:#0fa37a; color:#fff; border-radius:50%;
                                                 width:20px; height:20px; display:flex; align-items:center; justify-content:center;
                                                 font-size:10px; font-weight:800; border:2px solid #fff; line-height:1;">6</span>
                                </div>
                                @elseif ($entregaActiva)
                                <div class="fmp-step-circle" style="width:60px; height:60px; border-radius:50%;
                                            background:linear-gradient(135deg,#1a7efb,#0d6efd); color:#fff;
                                            margin-bottom:8px;
                                            box-shadow:0 6px 20px rgba(26,126,251,.5), 0 0 0 5px rgba(26,126,251,.2), 0 0 0 10px rgba(26,126,251,.08);
                                            display:flex; align-items:center; justify-content:center; flex-direction:column;
                                            flex-shrink:0; outline:3px solid rgba(26,126,251,.35); outline-offset:4px;">
                                    <span class="fmp-step-num" style="font-size:20px; font-weight:800; line-height:1;">6</span>
                                    <i class="fmp-step-icon-sm fa fa-truck" style="font-size:11px; margin-top:2px; opacity:.85;"></i>
                                </div>
                                @elseif ($entregaEstadoId === 5)
                                <div class="fmp-step-circle" style="width:60px; height:60px; border-radius:50%;
                                            background:linear-gradient(135deg,#1a7efb,#0d6efd); color:#fff;
                                            margin-bottom:8px;
                                            box-shadow:0 4px 16px rgba(26,126,251,.35);
                                            display:flex; align-items:center; justify-content:center; flex-direction:column;
                                            flex-shrink:0;">
                                    <span class="fmp-step-num" style="font-size:20px; font-weight:800; line-height:1;">6</span>
                                    <i class="fmp-step-icon-sm fa fa-truck" style="font-size:11px; margin-top:2px; opacity:.85;"></i>
                                </div>
                                @else
                                <div class="fmp-step-circle" style="width:60px; height:60px; border-radius:50%;
                                            background:#e8eaf0; color:#c0c2cc; margin-bottom:8px;
                                            display:flex; align-items:center; justify-content:center; flex-direction:column;
                                            flex-shrink:0;">
                                    <span class="fmp-step-num" style="font-size:20px; font-weight:700; line-height:1; color:#aab;">6</span>
                                    <i class="fmp-step-icon-sm fa fa-truck" style="font-size:11px; margin-top:2px; color:#c0c2cc;"></i>
                                </div>
                                @endif
                                <div style="text-align:center;">
                                    <div style="font-size:12px; font-weight:700; color:{{ $entregaColor }};
                                                {{ ($pasoActivo === 'entrega') ? 'text-decoration:underline;' : '' }}">Entregas</div>
                                    <div style="font-size:10px; color:{{ $entregaColor }}; opacity:{{ $tieneEntrega ? '1' : '.7' }};">
                                        @if ($entregaEsCompletada)
                                            <i class="fa fa-check-circle"></i> Completado
                                        @elseif ($entregaActiva)
                                            <i class="fa fa-map-marker" style="animation:dotBlink 1s ease-in-out infinite;"></i> Actual
                                        @elseif ($entregaEstadoId === 5)
                                            <i class="fa fa-clock-o"></i> Pendiente
                                        @else
                                            <i class="fa fa-clock-o"></i> Pendiente
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="{{ $puedeCobro ? 'fmp-step-clickable' : '' }}"
                                 @if($puedeCobro) wire:click="seleccionarPaso('cobro')" @endif
                                 style="display:flex; flex-direction:column; align-items:center; min-width:100px;
                                        {{ ($pasoActivo === 'cobro') ? (($cobroCompletado ? 'background:rgba(26,179,148,.08);' : 'background:rgba(26,126,251,.06);') . ' border-radius:12px; padding:4px 6px;') : 'padding:4px 6px;' }}">
                                @if ($cobroCompletado)
                                <div class="fmp-step-circle" style="position:relative; width:60px; height:60px; margin-bottom:8px; flex-shrink:0;">
                                    <div style="width:60px; height:60px; border-radius:50%;
                                                background:linear-gradient(135deg,#1ab394,#0fa37a); color:#fff;
                                                box-shadow:0 4px 16px rgba(26,179,148,.4);
                                                display:flex; align-items:center; justify-content:center; font-size:22px;
                                                {{ ($pasoActivo === 'cobro') ? 'box-shadow:0 4px 16px rgba(26,179,148,.4), 0 0 0 4px rgba(26,179,148,.25);' : '' }}">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <span style="position:absolute; top:-4px; right:-4px; background:#0fa37a; color:#fff; border-radius:50%;
                                                 width:20px; height:20px; display:flex; align-items:center; justify-content:center;
                                                 font-size:10px; font-weight:800; border:2px solid #fff; line-height:1;">7</span>
                                </div>
                                @elseif ($cobroActiva)
                                <div class="fmp-step-circle" style="width:60px; height:60px; border-radius:50%;
                                            background:linear-gradient(135deg,#1a7efb,#0d6efd); color:#fff;
                                            margin-bottom:8px;
                                            box-shadow:0 6px 20px rgba(26,126,251,.5), 0 0 0 5px rgba(26,126,251,.2), 0 0 0 10px rgba(26,126,251,.08);
                                            display:flex; align-items:center; justify-content:center; flex-direction:column;
                                            flex-shrink:0; outline:3px solid rgba(26,126,251,.35); outline-offset:4px;">
                                    <span class="fmp-step-num" style="font-size:20px; font-weight:800; line-height:1;">7</span>
                                    <i class="fmp-step-icon-sm fa fa-dollar" style="font-size:11px; margin-top:2px; opacity:.85;"></i>
                                </div>
                                @elseif ($cobroEstadoId === 5)
                                <div class="fmp-step-circle" style="width:60px; height:60px; border-radius:50%;
                                            background:linear-gradient(135deg,#1a7efb,#0d6efd); color:#fff;
                                            margin-bottom:8px;
                                            box-shadow:0 4px 16px rgba(26,126,251,.35);
                                            display:flex; align-items:center; justify-content:center; flex-direction:column;
                                            flex-shrink:0;">
                                    <span class="fmp-step-num" style="font-size:20px; font-weight:800; line-height:1;">7</span>
                                    <i class="fmp-step-icon-sm fa fa-dollar" style="font-size:11px; margin-top:2px; opacity:.85;"></i>
                                </div>
                                @else
                                <div class="fmp-step-circle" style="width:60px; height:60px; border-radius:50%;
                                            background:#e8eaf0; color:#c0c2cc; margin-bottom:8px;
                                            display:flex; align-items:center; justify-content:center; flex-direction:column;
                                            flex-shrink:0;">
                                    <span class="fmp-step-num" style="font-size:20px; font-weight:700; line-height:1; color:#aab;">7</span>
                                    <i class="fmp-step-icon-sm fa fa-dollar" style="font-size:11px; margin-top:2px; color:#c0c2cc;"></i>
                                </div>
                                @endif
                                <div style="text-align:center;">
                                    <div style="font-size:12px; font-weight:700; color:{{ $cobroColor }};
                                                {{ ($pasoActivo === 'cobro') ? 'text-decoration:underline;' : '' }}">Cobro</div>
                                    <div style="font-size:10px; color:{{ $cobroColor }}; opacity:{{ $cobroEstadoId !== null ? '1' : '.7' }};">
                                        @if ($cobroCompletado)
                                            <i class="fa fa-check-circle"></i> Completado
                                        @elseif ($cobroActiva)
                                            <i class="fa fa-map-marker" style="animation:dotBlink 1s ease-in-out infinite;"></i> Actual
                                        @elseif ($cobroEstadoId === 5)
                                            <i class="fa fa-clock-o"></i> Pendiente
                                        @else
                                            <i class="fa fa-clock-o"></i> Pendiente
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="width:80px; height:170px; position:relative;">
                            <div style="position:absolute; left:0; top:38px; width:48px; height:3px; border-radius:3px;
                                        background:{{ $entregaEsCompletada ? '#1ab394' : ($tieneEntrega ? '#1a7efb' : '#d6dbe8') }};"></div>
                            <div style="position:absolute; left:0; top:122px; width:48px; height:3px; border-radius:3px;
                                        background:{{ $cobroCompletado ? '#1ab394' : ($cobroEstadoId === 5 ? '#1a7efb' : '#d6dbe8') }};"></div>
                            <div style="position:absolute; left:48px; top:38px; width:3px; height:87px; border-radius:3px;
                                        background:{{ ($tieneEntrega || $cobroEstadoId !== null) ? '#1a7efb' : '#d6dbe8' }};"></div>
                            <div style="position:absolute; left:48px; top:82px; width:32px; height:3px; border-radius:3px;
                                        background:{{ $finalizadoCompletado ? '#1ab394' : '#d6dbe8' }};"></div>
                        </div>

                        <div class="{{ $puedeFinal ? 'fmp-step-clickable' : '' }}"
                             @if($puedeFinal) wire:click="seleccionarPaso('finalizado')" @endif
                             style="display:flex; flex-direction:column; align-items:center; min-width:100px;">
                            @if ($finalizadoCompletado)
                            <div class="fmp-step-circle" style="position:relative; width:60px; height:60px; margin-bottom:8px; flex-shrink:0;">
                                <div style="width:60px; height:60px; border-radius:50%;
                                            background:linear-gradient(135deg,#1ab394,#0fa37a); color:#fff;
                                            box-shadow:0 4px 16px rgba(26,179,148,.4);
                                            display:flex; align-items:center; justify-content:center; font-size:22px;
                                            {{ $finalActiva ? 'box-shadow:0 4px 16px rgba(26,179,148,.4), 0 0 0 4px rgba(26,179,148,.25);' : '' }}">
                                    <i class="fa fa-check"></i>
                                </div>
                                <span style="position:absolute; top:-4px; right:-4px; background:#0fa37a; color:#fff; border-radius:50%;
                                             width:20px; height:20px; display:flex; align-items:center; justify-content:center;
                                             font-size:10px; font-weight:800; border:2px solid #fff; line-height:1;">8</span>
                            </div>
                            @elseif ($finalActiva)
                            <div class="fmp-step-circle" style="width:60px; height:60px; border-radius:50%;
                                        background:linear-gradient(135deg,#1a7efb,#0d6efd); color:#fff;
                                        margin-bottom:8px;
                                        box-shadow:0 6px 20px rgba(26,126,251,.5), 0 0 0 5px rgba(26,126,251,.2), 0 0 0 10px rgba(26,126,251,.08);
                                        display:flex; align-items:center; justify-content:center; flex-direction:column;
                                        flex-shrink:0; outline:3px solid rgba(26,126,251,.35); outline-offset:4px;">
                                <span class="fmp-step-num" style="font-size:20px; font-weight:800; line-height:1;">8</span>
                                <i class="fmp-step-icon-sm fa fa-flag-checkered" style="font-size:11px; margin-top:2px; opacity:.85;"></i>
                            </div>
                            @else
                            <div class="fmp-step-circle" style="width:60px; height:60px; border-radius:50%;
                                        background:#e8eaf0; color:#c0c2cc; margin-bottom:8px;
                                        display:flex; align-items:center; justify-content:center; flex-direction:column;
                                        flex-shrink:0;">
                                <span class="fmp-step-num" style="font-size:20px; font-weight:700; line-height:1; color:#aab;">8</span>
                                <i class="fmp-step-icon-sm fa fa-flag-checkered" style="font-size:11px; margin-top:2px; color:#c0c2cc;"></i>
                            </div>
                            @endif
                            <div style="text-align:center;">
                                <div style="font-size:12px; font-weight:700; color:{{ $finalColor }};
                                            {{ $finalActiva ? 'text-decoration:underline;' : '' }}">Finalizado</div>
                                <div style="font-size:10px; color:{{ $finalColor }}; opacity:{{ $finalizadoCompletado ? '1' : '.7' }};">
                                    @if ($finalizadoCompletado)
                                        <i class="fa fa-check-circle"></i> Completado
                                    @elseif ($finalActiva)
                                        <i class="fa fa-map-marker" style="animation:dotBlink 1s ease-in-out infinite;"></i> Actual
                                    @else
                                        <i class="fa fa-clock-o"></i> Pendiente
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ── Barra de progreso ─────────────────────────────── --}}
                @if (!$fCancelado)
                @php $progressPct = min(round(($fPaso / 8) * 100), 100); @endphp
                <div style="height:5px; border-radius:5px; background:#e8eaf0; margin:0 4px 6px; overflow:hidden;">
                    <div style="height:100%; border-radius:5px;
                                background:linear-gradient(90deg,#1ab394,#1a7efb);
                                width:{{ $progressPct }}%; transition:width .6s ease;"></div>
                </div>
                @endif

                @endif {{-- /cancelado --}}

                {{-- ── Barra de oferta seleccionada (encima de info grid) ─── --}}
                @if ($pasoActivo === 'ofertas' && $ofertaSeleccionada)
                    @php
                        $obsOfertTop = $ofertaSeleccionada['hf_observaciones'] ?? '';
                        $esGanTop    = ($obsOfertTop === 'ganadora');
                        $esAnuTop    = str_starts_with($obsOfertTop, 'Anulado:');
                        $esVencTop   = str_starts_with($obsOfertTop, 'VencidaPrecios:');
                    @endphp
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px; flex-wrap:wrap;">
                    <button type="button" wire:click="cerrarOferta"
                            style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff;
                                   border:none; border-radius:20px; padding:5px 16px;
                                   font-size:12px; font-weight:700; cursor:pointer;">
                        <i class="mr-1 fa fa-arrow-left"></i> Volver
                    </button>
                    <span style="font-size:14px; font-weight:700; color:#2c3e50;">
                        Oferta #{{ $ofertaSeleccionada['id'] }}
                    </span>
                    @if ($esGanTop)
                        <span style="background:#d4edda; color:#155724; border-radius:10px;
                                     padding:2px 8px; font-size:11px; font-weight:700;">
                            <i class="fa fa-trophy"></i> Ganadora
                        </span>
                    @elseif ($esAnuTop)
                        <span style="background:#f8d7da; color:#721c24; border-radius:10px;
                                     padding:2px 8px; font-size:11px; font-weight:700;">
                            <i class="fa fa-ban"></i> Anulada
                        </span>
                    @elseif ($esVencTop)
                        <span style="background:#fff3e0; color:#e65100; border-radius:10px;
                                     padding:2px 8px; font-size:11px; font-weight:700;">
                            <i class="fa fa-exclamation-triangle"></i> Precios cambiaron
                        </span>
                    @else
                        <span style="background:#e8f0fe; color:#1a7efb; border-radius:10px;
                                     padding:2px 8px; font-size:11px; font-weight:700;">
                            Activa
                        </span>
                    @endif
                </div>
                @endif

                {{-- ── Info grid ─────────────────────────────────────── --}}
                <div class="fmp-info-grid" style="margin-top:12px; padding:12px 16px; background:#fff;
                            border-radius:12px; border:1px solid #e8eaf0;">
                    <span>
                        @if(!empty($d['sin_pedido']))
                    <i class="mr-1 fa fa-hashtag" style="color:#1a7efb;"></i>
                    <strong style="color:#1a7efb;">Flujo #{{ $d['flujo_id'] ?? $flujoId ?? $d['id'] }}</strong>
                        @else
                        <i class="mr-1 fa fa-hashtag text-primary"></i>
                        <strong>Pedido #{{ $d['id'] }}</strong>
                        @endif
                    </span>
                    <span>
                        <i class="mr-1 fa fa-user text-info"></i>
                        {{ $d['cliente'] }}
                    </span>
                    <span>
                        <i class="mr-1 fa fa-user-circle-o text-muted"></i>
                        Por: {{ $d['registrado_por'] ?? '—' }}
                    </span>
                    <span>
                        <i class="mr-1 fa fa-calendar text-muted"></i>
                        {{ \Carbon\Carbon::parse($d['created_at'])->format('d/m/Y H:i') }}
                    </span>
                    @if ($fCancelado)
                    <span style="color:#e74c3c; font-weight:700;">
                        <i class="mr-1 fa fa-ban"></i> Cancelado
                    </span>
                    @endif
                </div>

                {{-- ── Mensajes ──────────────────────────────────────── --}}
                @if ($mensajeExito)
                <div style="margin-top:10px; background:#d4edda; border:1px solid #c3e6cb;
                            border-radius:10px; padding:9px 14px; font-size:13px; color:#155724;">
                    <i class="mr-1 fa fa-check-circle"></i> {{ $mensajeExito }}
                </div>
                @endif
                @if ($mensajeError && $confirmAccion !== 'anular' && $confirmAccionOferta !== 'anular_oferta')
                <div style="margin-top:10px; background:#f8d7da; border:1px solid #f5c6cb;
                            border-radius:10px; padding:9px 14px; font-size:13px; color:#721c24;">
                    <i class="mr-1 fa fa-exclamation-triangle"></i> {{ $mensajeError }}
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
                    <i class="mr-1 fa fa-sticky-note text-warning"></i>
                    <strong>Observaciones:</strong> {{ $d['observaciones'] }}
                </div>
                @endif

                {{-- Productos del pedido --}}
                <div style="margin-top:12px; border-radius:12px; overflow:hidden; border:1px solid #e8eaf0;">
                    <div style="background:linear-gradient(135deg,#1a7efb 0%,#0d6efd 100%); padding:10px 16px;">
                        <span style="color:#fff; font-size:13px; font-weight:700;">
                            <i class="mr-1 fa fa-list-ul"></i>
                            Productos del Pedido
                            <span style="background:rgba(255,255,255,.22); border-radius:20px; padding:1px 9px; font-size:11px; margin-left:6px;">
                                {{ count($pedidoDetalles) }}
                            </span>
                        </span>
                    </div>
                    <div style="background:#fff; max-height:200px; overflow-y:auto; padding:10px 14px;">
                        @if (count($pedidoDetalles) === 0)
                        <p class="text-center text-muted" style="font-size:12px; margin:12px 0;">
                            <i class="mb-1 fa fa-inbox d-block" style="opacity:.3; font-size:22px;"></i>
                            Sin productos registrados.
                        </p>
                        @else
                        <table style="width:100%; font-size:12px; border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f8f9fc; color:#888;">
                                    <th style="padding:4px 8px; text-align:left;">Producto</th>
                                    <th style="padding:4px 8px; text-align:center;">Cant.</th>
                                    <th style="padding:4px 8px; text-align:center;">Escala</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pedidoDetalles as $det)
                                <tr style="border-bottom:1px solid #f0f0f0;">
                                    <td style="padding:5px 8px; color:#2c3e50;">
                                        {{ $det['nombre_producto'] ?? ($det['producto'] ?? '—') }}
                                    </td>
                                    <td style="padding:5px 8px; text-align:center; font-weight:700; color:#1a7efb;">
                                        {{ isset($det['cantidad']) ? (int)$det['cantidad'] : '—' }}
                                    </td>
                                    <td style="padding:5px 8px; text-align:center; color:#888; font-size:11px;">
                                        {{ $d['nombre_escala'] ?? '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                </div>

                {{-- Acciones del pedido --}}
                    @if (!$facturaCompletada && $confirmAccion === 'anular')
                <div style="margin-top:14px; background:#fff8e1; border:1px solid #ffe082;
                            border-radius:12px; padding:14px;">
                    <p style="font-size:13px; color:#555; margin:0 0 8px;">
                        <i class="mr-1 fa fa-exclamation-triangle text-warning"></i>
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
                            <i class="mr-1 fa fa-ban"></i> Confirmar anulación
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
                            border-radius:12px; padding:14px;">
                    <p style="font-size:13px; color:#555; margin:0 0 10px; text-align:center;">
                        <i class="mr-1 fa fa-copy text-primary"></i>
                        ¿Duplicar el <strong>Pedido #{{ $d['id'] }}</strong>?
                    </p>
                    @if ($facturaCompletada)
                    <div style="background:#fff3e0; border:1px solid #ffcc80; border-radius:8px;
                                padding:8px 12px; margin-bottom:10px; font-size:12px; color:#e65100; text-align:left;">
                        <i class="mr-1 fa fa-external-link"></i>
                        Este flujo ya tiene <strong>factura registrada</strong>. Se creará un <strong>nuevo flujo</strong> con los mismos productos para el mismo cliente.
                    </div>
                    @else
                    <div style="background:#e8f5e9; border:1px solid #a5d6a7; border-radius:8px;
                                padding:8px 12px; margin-bottom:10px; font-size:12px; color:#2e7d32; text-align:left;">
                        <i class="mr-1 fa fa-sitemap"></i>
                        Se creará una nueva oferta dentro del <strong>mismo flujo</strong> con los mismos productos.
                    </div>
                    @endif

                    @if (count($productosPrecioEscalaCambiado) > 0)
                    <div style="background:#fff8e1; border:1px solid #ffe082; border-radius:8px;
                                padding:10px 12px; margin-bottom:10px; font-size:12px; color:#7b4f00; text-align:left;">
                        <p style="margin:0 0 8px; font-weight:700; color:#e65100;">
                            <i class="mr-1 fa fa-exclamation-triangle"></i>
                            {{ count($productosPrecioEscalaCambiado) }} producto(s) tienen precio diferente con la escala actual del cliente:
                        </p>
                        <table style="width:100%; border-collapse:collapse; font-size:11px;">
                            <thead>
                                <tr style="background:#fff3cd; color:#856404;">
                                    <th style="padding:4px 8px; text-align:left;">Producto</th>
                                    <th style="padding:4px 8px; text-align:right;">Precio anterior</th>
                                    <th style="padding:4px 8px; text-align:right;">Precio nuevo (escala actual)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($productosPrecioEscalaCambiado as $pc)
                                <tr style="border-bottom:1px solid #ffeeba;">
                                    <td style="padding:4px 8px; color:#2c3e50;">{{ $pc['nombre'] }}</td>
                                    <td style="padding:4px 8px; text-align:right; color:#c0392b; text-decoration:line-through;">
                                        L {{ number_format($pc['precio_original'], 2) }}
                                    </td>
                                    <td style="padding:4px 8px; text-align:right; font-weight:700;
                                               color:{{ $pc['precio_nuevo'] > $pc['precio_original'] ? '#e74c3c' : '#27ae60' }};">
                                        L {{ number_format($pc['precio_nuevo'], 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p style="margin:8px 0 0; font-size:11px; color:#555; font-style:italic;">
                            <i class="mr-1 fa fa-info-circle"></i>
                            Al duplicar, la oferta se generará con los <strong>nuevos precios de la escala actual</strong>.
                        </p>
                    </div>
                    @endif

                    <div style="display:flex; gap:8px; justify-content:center;">
                        <button type="button" wire:click="duplicarPedido"
                                style="background:linear-gradient(135deg,#1a7efb,#0d6efd); color:#fff;
                                       border:none; border-radius:8px; padding:7px 18px; font-size:12px;
                                       font-weight:700; cursor:pointer;">
                            <i class="mr-1 fa fa-copy"></i>
                            @if (count($productosPrecioEscalaCambiado) > 0)
                                Sí, duplicar con nuevos precios
                            @else
                                Duplicar
                            @endif
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

                {{-- Aviso: prefactura vencida procesada en esta sesión --}}
                @if ($vencimientoProcesado)
                <div style="margin-top:12px; background:#fff3e0; border:1px solid #ffcc80;
                            border-radius:12px; padding:12px 16px; font-size:12px; color:#e65100;">
                    <i class="mr-1 fa fa-clock-o"></i>
                    <strong>Prefactura vencida.</strong>
                    El sistema revisó los precios y reactivar las ofertas con precios vigentes.
                    Las ofertas con precios desactualizados fueron marcadas como inactivas
                    (solo pueden duplicarse).
                </div>
                @endif

                @if ($ofertaSeleccionada)
                {{-- ── Detalle de la oferta seleccionada ── --}}
                <div style="margin-top:12px;">
                    @php
                        $obsOfert = $ofertaSeleccionada['hf_observaciones'] ?? '';
                        $esGanDet = ($obsOfert === 'ganadora');
                        $esAnuDet = str_starts_with($obsOfert, 'Anulado:');
                        $esVencDet = str_starts_with($obsOfert, 'VencidaPrecios:');
                    @endphp
                    {{-- Info de la oferta --}}
                    <div style="background:#fff; border-radius:10px; border:1px solid #e8eaf0;
                                padding:12px 14px; margin-bottom:10px; font-size:12px; color:#555;">
                        @php
                            $diasSolicitadosDet = 0;
                            if (!empty($ofertaSeleccionada['fecha_emision']) && !empty($ofertaSeleccionada['fecha_vencimiento'])) {
                                try {
                                    $diasSolicitadosDet = max(0, \Carbon\Carbon::parse($ofertaSeleccionada['fecha_emision'])->diffInDays(\Carbon\Carbon::parse($ofertaSeleccionada['fecha_vencimiento']), false));
                                } catch (\Throwable $e) {
                                    $diasSolicitadosDet = 0;
                                }
                            }
                        @endphp
                        <div style="display:flex; flex-wrap:wrap; gap:12px;">
                            <span><i class="mr-1 fa fa-user text-info"></i>{{ $ofertaSeleccionada['nombre_cliente'] }}</span>
                            @if (!empty($ofertaSeleccionada['fecha_emision']))
                            <span><i class="mr-1 fa fa-calendar text-muted"></i>{{ \Carbon\Carbon::parse($ofertaSeleccionada['fecha_emision'])->format('d/m/Y') }}</span>
                            @endif
                            @if (!empty($ofertaSeleccionada['fecha_vencimiento']))
                            <span><i class="mr-1 fa fa-clock-o text-muted"></i>Vence: {{ \Carbon\Carbon::parse($ofertaSeleccionada['fecha_vencimiento'])->format('d/m/Y') }}</span>
                            @endif
                            <span><i class="mr-1 fa fa-hourglass-half text-primary"></i>Días solicitados: {{ $diasSolicitadosDet }}</span>
                            <span><i class="mr-1 fa fa-dollar text-success"></i>
                                Sub: L {{ number_format($ofertaSeleccionada['sub_total'] ?? 0, 2) }}
                            </span>
                            @if (($ofertaSeleccionada['monto_descuento'] ?? 0) > 0)
                            <span><i class="mr-1 fa fa-minus-circle text-warning"></i>
                                Desc: L {{ number_format($ofertaSeleccionada['monto_descuento'], 2) }}
                                ({{ $ofertaSeleccionada['porc_descuento'] }}%)
                            </span>
                            @endif
                            <span><i class="mr-1 fa fa-file-text text-muted"></i>
                                ISV: L {{ number_format($ofertaSeleccionada['isv'] ?? 0, 2) }}
                            </span>
                            <strong style="color:#e65100;">
                                Total: L {{ number_format($ofertaSeleccionada['total'] ?? 0, 2) }}
                            </strong>
                        </div>
                    </div>

                    {{-- Productos de la oferta --}}
                    @if (!empty($ofertaSeleccionada['productos']))
                    @php
                        $sinExistenciaCount = collect($ofertaSeleccionada['productos'])
                            ->filter(fn ($pr) => (bool) ($pr['sin_existencia_linea'] ?? false))
                            ->count();
                    @endphp
                    <div style="border-radius:10px; overflow:hidden; border:1px solid #e8eaf0;
                                max-height:170px; overflow-y:auto;">
                        <table style="width:100%; font-size:11px; border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f8f9fc; color:#888; position:sticky; top:0;">
                                    <th style="padding:4px 8px; text-align:left;">Producto</th>
                                    <th style="padding:4px 8px; text-align:center;">Cant.</th>
                                    <th style="padding:4px 8px; text-align:right;">P.Unit.</th>
                                    <th style="padding:4px 8px; text-align:center;">Categ.</th>
                                    <th style="padding:4px 8px; text-align:right;">Escala Selec.</th>
                                    <th style="padding:4px 8px; text-align:right;">Escala Act.</th>
                                    <th style="padding:4px 8px; text-align:right;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ofertaSeleccionada['productos'] as $pr)
                                @php
                                    $precioVendedor         = isset($pr['precio_unidad'])              ? (float)$pr['precio_unidad']              : null;
                                    $precioEscalaSel        = isset($pr['precio_escala_seleccionada']) ? (float)$pr['precio_escala_seleccionada'] : null;
                                    $precioEscalaActual     = isset($pr['precio_escala_actual'])       ? (float)$pr['precio_escala_actual']       : null;
                                    // Alerta si el precio de escala subió
                                    $precioSubio = ($precioEscalaSel !== null && $precioEscalaActual !== null)
                                                    && $precioEscalaActual > $precioEscalaSel + 0.0001;
                                @endphp
                                <tr style="border-bottom:1px solid #f0f0f0;">
                                    <td style="padding:4px 8px; color:#2c3e50;">{{ $pr['nombre_producto'] }}</td>
                                    <td style="padding:4px 8px; text-align:center; color:#1a7efb; font-weight:700;">{{ (int)$pr['cantidad'] }}</td>
                                    <td style="padding:4px 8px; text-align:right; color:#555;">
                                        @if ($precioVendedor !== null) L {{ number_format($precioVendedor, 2) }} @else — @endif
                                    </td>
                                    <td style="padding:4px 8px; text-align:center; color:#7f8c8d; font-size:10px;">{{ $pr['nombre_categoria_precio'] ?? '—' }}</td>
                                    <td style="padding:4px 8px; text-align:right; color:{{ $precioSubio ? '#c0392b' : '#555' }}; font-weight:{{ $precioSubio ? '700' : '400' }};">
                                        @if ($precioEscalaSel !== null) L {{ number_format($precioEscalaSel, 2) }} @else — @endif
                                    </td>
                                    <td style="padding:4px 8px; text-align:right; color:{{ $precioSubio ? '#e67e22' : '#aaa' }}; font-weight:{{ $precioSubio ? '700' : '400' }};">
                                        @if ($precioEscalaActual !== null) L {{ number_format($precioEscalaActual, 2) }} @else — @endif
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

                    @if ($sinExistenciaCount > 0 && !$esAnuDet && !$esVencDet)
                    <div style="display:flex; justify-content:flex-end; margin-top:12px;">
                        <button type="button" wire:click="abrirEdicionProductosSinExistencia"
                                style="background:linear-gradient(135deg,#7b1fa2,#9c27b0); color:#fff;
                                       border:none; border-radius:8px; padding:6px 12px;
                                       font-size:11px; font-weight:700; cursor:pointer;">
                            <i class="mr-1 fa fa-pencil-square-o"></i>
                            Editar productos sin existencia
                        </button>
                    </div>
                    @endif

                    {{-- Acciones de la oferta --}}
                    @if ($confirmAccionOferta === null)
                    <div style="display:flex; flex-wrap:wrap; gap:6px; margin-top:12px;">

                        {{-- Aviso de oferta inactiva por precios --}}
                        @if ($esVencDet)
                        <div style="width:100%; background:#fff3e0; border:1px solid #ffcc80;
                                    border-radius:10px; padding:8px 12px; font-size:11px; color:#e65100;
                                    margin-bottom:4px;">
                            <i class="mr-1 fa fa-exclamation-triangle"></i>
                            <strong>Precios desactualizados.</strong>
                            Esta oferta fue inactivada porque uno o más precios cambiaron desde que fue creada.
                            Solo puede duplicarse para generar una nueva oferta con precios actualizados.
                        </div>
                        @endif

                        <a href="/cotizacion/imprimir/{{ $ofertaSeleccionada['id'] }}" target="_blank"
                           style="text-align:center; background:#f8f9fc; color:#1a7efb;
                                  border:1px solid #e8eaf0; border-radius:8px; padding:5px 10px;
                                  font-size:11px; font-weight:700; text-decoration:none;">
                            <i class="mr-1 fa fa-print"></i> Imprimir
                        </a>

                        <a href="/oferta/{{ $ofertaSeleccionada['id'] }}/ficha-pdf" target="_blank"
                           style="text-align:center; background:linear-gradient(135deg,#27ae60,#1e8449); color:#fff;
                                  border:none; border-radius:8px; padding:5px 10px;
                                  font-size:11px; font-weight:700; text-decoration:none; display:inline-block;">
                            <i class="mr-1 fa fa-file-pdf-o"></i> Catálogo PDF
                        </a>

                        @if (!$facturaCompletada && !$esGanDet && !$esAnuDet && !$esVencDet && !$tieneGanadora)
                        <button type="button" wire:click="confirmarAccionOferta('ganadora')"
                                style="background:linear-gradient(135deg,#1ab394,#0fa37a); color:#fff;
                                       border:none; border-radius:8px; padding:5px 10px;
                                       font-size:11px; font-weight:700; cursor:pointer;">
                            <i class="mr-1 fa fa-trophy"></i> Ganadora
                        </button>
                        @endif
                        @if (!$facturaCompletada && $esGanDet && !$esAnuDet && !($tieneRevisionCreditoRechazada ?? false))
                        <button type="button" wire:click="confirmarAccionOferta('quitar_ganadora')"
                                style="background:linear-gradient(135deg,#e67e22,#d35400); color:#fff;
                                       border:none; border-radius:8px; padding:5px 10px;
                                       font-size:11px; font-weight:700; cursor:pointer;">
                            <i class="mr-1 fa fa-times-circle"></i> Quitar Ganadora
                        </button>
                        @endif
                        @if (!$facturaCompletada && !$esAnuDet && !$esVencDet && !($tieneRevisionCreditoRechazada ?? false))
                        <button type="button" wire:click="confirmarAccionOferta('anular_oferta')"
                                style="background:linear-gradient(135deg,#e74c3c,#c0392b); color:#fff;
                                       border:none; border-radius:8px; padding:5px 10px;
                                       font-size:11px; font-weight:700; cursor:pointer;">
                            <i class="mr-1 fa fa-ban"></i> Anular
                        </button>
                        @endif
                        <button type="button" wire:click="confirmarAccionOferta('duplicar_oferta')"
                                style="background:linear-gradient(135deg,#1a7efb,#0d6efd); color:#fff;
                                       border:none; border-radius:8px; padding:5px 10px;
                                       font-size:11px; font-weight:700; cursor:pointer;">
                            <i class="mr-1 fa fa-copy"></i> Duplicar
                        </button>
                    </div>
                    @endif

                    {{-- Confirmación: Ganadora --}}
                    @if (!$facturaCompletada && $confirmAccionOferta === 'ganadora')
                    <div style="margin-top:12px; background:#fff8e1; border:1px solid #ffe082;
                                border-radius:12px; padding:14px;">

                        {{-- Errores de inventario (si los hay) --}}
                        @if (!empty($stockErrors))
                        <div style="background:#fce4ec; border:1px solid #f48fb1; border-radius:8px;
                                    padding:10px 12px; margin-bottom:10px;">
                            <p style="font-size:12px; color:#b71c1c; font-weight:700; margin:0 0 8px;">
                                <i class="mr-1 fa fa-exclamation-triangle"></i> Inventario insuficiente
                            </p>
                            <table style="width:100%; font-size:11px; border-collapse:collapse;">
                                <thead>
                                    <tr style="background:#f8bbd0;">
                                        <th style="padding:3px 8px; text-align:left;">Producto</th>
                                        <th style="padding:3px 8px; text-align:center;">Solicitado</th>
                                        <th style="padding:3px 8px; text-align:center;">Disponible</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stockErrors as $se)
                                    <tr style="border-bottom:1px solid #f0f0f0;">
                                        <td style="padding:3px 8px; color:#2c3e50;">{{ $se['producto'] }}</td>
                                        <td style="padding:3px 8px; text-align:center; color:#e65100; font-weight:700;">{{ $se['solicitado'] }}</td>
                                        <td style="padding:3px 8px; text-align:center; color:#b71c1c; font-weight:700;">{{ $se['disponible'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif

                        <p style="font-size:13px; color:#555; margin:0 0 6px; text-align:center;">
                            <i class="mr-1 fa fa-trophy text-warning"></i>
                            ¿Marcar la <strong>Oferta #{{ $ofertaSeleccionada['id'] }}</strong> como <strong>ganadora</strong>?
                        </p>
                        @if ($revisionInventarioActiva)
                        <p style="font-size:12px; color:#6a1b9a; margin:0 0 10px; text-align:center;">
                            <i class="mr-1 fa fa-search"></i>
                            La oferta pasará a <strong>Revisión de Inventario</strong> antes de convertirse en Pre-Factura.
                        </p>
                        @else
                        <p style="font-size:12px; color:#1b5e20; margin:0 0 10px; text-align:center;">
                            <i class="mr-1 fa fa-check-circle"></i>
                            Se creará la <strong>Pre-Factura automáticamente</strong> y se reservará el inventario.
                        </p>
                        @endif
                        <div style="margin:0 0 10px;">
                            <label style="display:block; font-size:11px; font-weight:700; color:#616161; margin-bottom:4px;">
                                Comentario para Créditos (opcional)
                            </label>
                            <textarea wire:model.defer="comentarioCreditoGanadora"
                                      rows="2"
                                      placeholder="Escribe una observación para el área de créditos..."
                                      style="width:100%; border:1px solid #ddd; border-radius:8px; padding:8px 10px; font-size:12px; resize:vertical;"></textarea>
                        </div>
                        <div style="display:flex; gap:8px; justify-content:center;">
                            <button type="button" wire:click="ganadoraOferta"
                                    style="background:{{ $revisionInventarioActiva ? 'linear-gradient(135deg,#7b1fa2,#9c27b0)' : 'linear-gradient(135deg,#e65100,#f9a826)' }}; color:#fff;
                                           border:none; border-radius:8px; padding:7px 18px;
                                           font-size:12px; font-weight:700; cursor:pointer;">
                                @if ($revisionInventarioActiva)
                                    <i class="mr-1 fa fa-search"></i> Confirmar y enviar a Revisión
                                @else
                                    <i class="mr-1 fa fa-trophy"></i> Confirmar y crear Pre-Factura
                                @endif
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
                    @if (!$facturaCompletada && $confirmAccionOferta === 'quitar_ganadora')
                    <div x-data="{}"
                         x-on:focus-motivo-oferta.window="setTimeout(() => $refs.quitarGanTA && $refs.quitarGanTA.focus(), 100)"
                         style="margin-top:12px; background:#fff3e0; border:1px solid #ffcc80;
                                border-radius:12px; padding:14px;">
                        <p style="font-size:13px; color:#555; margin:0 0 8px;">
                            <i class="mr-1 fa fa-times-circle text-warning"></i>
                            ¿Quitar el estado <strong>Ganadora</strong> de la <strong>Oferta #{{ $ofertaSeleccionada['id'] }}</strong>?
                        </p>
                        @if ($mensajeError)
                        <div style="font-size:12px; color:#721c24; background:#f8d7da;
                                    border-radius:8px; padding:6px 10px; margin-bottom:8px;">
                            {{ $mensajeError }}
                        </div>
                        @endif
                        <textarea wire:model.defer="motivoAnulOferta" rows="2"
                                  x-ref="quitarGanTA"
                                  placeholder="Motivo (obligatorio)…"
                                  style="width:100%; border:1px solid #ddd; border-radius:8px;
                                         padding:6px 10px; font-size:12px; resize:none;"></textarea>
                        <div style="display:flex; gap:8px; margin-top:8px;">
                            <button type="button" wire:click="quitarGanadora"
                                    style="background:linear-gradient(135deg,#e67e22,#d35400); color:#fff;
                                           border:none; border-radius:8px; padding:7px 18px;
                                           font-size:12px; font-weight:700; cursor:pointer;">
                                <i class="mr-1 fa fa-times-circle"></i> Confirmar
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
                    @if (!$facturaCompletada && $confirmAccionOferta === 'anular_oferta')
                    <div x-data="{}"
                         x-on:focus-motivo-oferta.window="setTimeout(() => $refs.anulOfertaTA && $refs.anulOfertaTA.focus(), 100)"
                         style="margin-top:12px; background:#fff5f5; border:1px solid #feb2b2;
                                border-radius:12px; padding:14px;">
                        <p style="font-size:13px; color:#555; margin:0 0 8px;">
                            <i class="mr-1 fa fa-ban text-danger"></i>
                            ¿Anular la <strong>Oferta #{{ $ofertaSeleccionada['id'] }}</strong>?
                        </p>
                        @if ($mensajeError)
                        <div style="font-size:12px; color:#721c24; background:#f8d7da;
                                    border-radius:8px; padding:6px 10px; margin-bottom:8px;">
                            {{ $mensajeError }}
                        </div>
                        @endif
                        <textarea wire:model.defer="motivoAnulOferta" rows="2"
                                  x-ref="anulOfertaTA"
                                  placeholder="Motivo de anulación (obligatorio)…"
                                  style="width:100%; border:1px solid #ddd; border-radius:8px;
                                         padding:6px 10px; font-size:12px; resize:none;"></textarea>
                        @if (!$esAnuDet)
                        <div style="display:flex; gap:8px; margin-top:8px;">
                            <button type="button" wire:click="anularOferta"
                                    style="background:linear-gradient(135deg,#e74c3c,#c0392b); color:#fff;
                                           border:none; border-radius:8px; padding:7px 18px;
                                           font-size:12px; font-weight:700; cursor:pointer;">
                                <i class="mr-1 fa fa-ban"></i> Confirmar anulación
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
                            <i class="mr-1 fa fa-copy text-primary"></i>
                            ¿Duplicar la <strong>Oferta #{{ $ofertaSeleccionada['id'] }}</strong>?
                        </p>
                        @if ($flujoCancelado)
                        <div style="background:#fff3cd; border:1px solid #ffc107; border-radius:8px;
                                    padding:8px 12px; margin-bottom:10px; font-size:12px; color:#856404; text-align:left;">
                            <i class="mr-1 fa fa-info-circle"></i>
                            Este flujo fue <strong>rechazado</strong>. La oferta duplicada se creará en un <strong>nuevo flujo independiente</strong>.
                        </div>
                        @endif

                        @if ($preciosCambioMostrado && count($productosPrecioEscalaCambiado) > 0)
                        {{-- Aviso de cambio de escala: informativo, permite continuar --}}
                        <div style="background:#fff8e1; border:1px solid #ffe082; border-radius:10px;
                                    padding:12px; text-align:left; margin-bottom:8px;">
                            <p style="font-size:12px; color:#795548; font-weight:700; margin:0 0 6px;">
                                <i class="mr-1 fa fa-arrow-up text-warning"></i>
                                El precio de escala subió en los siguientes productos. Al duplicar se usarán los <strong>precios actuales</strong>:
                            </p>
                            <div style="border-radius:8px; overflow:hidden; border:1px solid #ffe0b2; margin-bottom:8px;">
                                <table style="width:100%; border-collapse:collapse; font-size:11px;">
                                    <thead>
                                        <tr style="background:#fff3e0; color:#bf360c;">
                                            <th style="padding:5px 8px; text-align:left;">Producto</th>
                                            <th style="padding:5px 8px; text-align:right;">Escala Selec.</th>
                                            <th style="padding:5px 8px; text-align:right;">Escala Act.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($productosPrecioEscalaCambiado as $pc)
                                        <tr style="border-top:1px solid #ffe0b2;">
                                            <td style="padding:5px 8px; color:#333;">{{ $pc['nombre_producto'] }}</td>
                                            <td style="padding:5px 8px; text-align:right; color:#777; text-decoration:line-through;">
                                                L {{ number_format((float)$pc['precio_original'], 2) }}
                                            </td>
                                            <td style="padding:5px 8px; text-align:right; font-weight:700; color:#c0392b;">
                                                L {{ number_format((float)$pc['precio_nuevo'], 2) }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p style="font-size:11px; color:#795548; margin:0 0 8px;">
                                <i class="mr-1 fa fa-info-circle"></i>
                                Al continuar, la oferta duplicada usará los precios de escala actuales.
                            </p>
                            <div style="display:flex; gap:8px; justify-content:flex-end;">
                                <button type="button" wire:click="confirmarDuplicarConNuevosPrecios"
                                        style="background:linear-gradient(135deg,#e67e22,#d35400); color:#fff;
                                               border:none; border-radius:8px; padding:6px 14px;
                                               font-size:11px; font-weight:700; cursor:pointer;">
                                    <i class="mr-1 fa fa-check"></i> Continuar con nuevos precios
                                </button>
                                <button type="button" wire:click="cancelarConfirmOferta"
                                        style="background:#f0f0f0; color:#555; border:none;
                                               border-radius:8px; padding:6px 12px; font-size:11px; cursor:pointer;">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                        @elseif (!$mostrarSelectorClienteDuplicar)
                        {{-- Info: mismo flujo o nuevo flujo --}}
                        @if (!$flujoCancelado)
                        @if (in_array(3, $flujoTipos) || in_array(5, $flujoTipos))
                        <div style="background:#fff3e0; border:1px solid #ffcc80; border-radius:8px;
                                    padding:8px 12px; margin-bottom:8px; font-size:12px; color:#e65100; text-align:left;">
                            <i class="mr-1 fa fa-info-circle"></i>
                            Este flujo ya tiene <strong>factura</strong>. Al duplicar con "<em>Mismo cliente</em>" se creará un <strong>nuevo flujo</strong> con la escala actual del cliente.
                        </div>
                        @else
                        <div style="background:#e8f5e9; border:1px solid #a5d6a7; border-radius:8px;
                                    padding:8px 12px; margin-bottom:8px; font-size:12px; color:#2e7d32; text-align:left;">
                            <i class="mr-1 fa fa-info-circle"></i>
                            La oferta duplicada se agregará al <strong>mismo flujo</strong> activo.
                        </div>
                        @endif
                        @endif
                        {{-- Botones iniciales --}}
                        <div style="display:flex; gap:8px; justify-content:center; flex-wrap:wrap;">
                            <button type="button" wire:click="duplicarOferta(true)"
                                    style="background:linear-gradient(135deg,#1a7efb,#0d6efd); color:#fff;
                                           border:none; border-radius:8px; padding:7px 16px;
                                           font-size:12px; font-weight:700; cursor:pointer;">
                                <i class="mr-1 fa fa-user"></i> Mismo cliente
                            </button>
                            <button type="button" wire:click="iniciarDuplicarOtroCliente()"
                                    style="background:linear-gradient(135deg,#1ab394,#0fa37a); color:#fff;
                                           border:none; border-radius:8px; padding:7px 16px;
                                           font-size:12px; font-weight:700; cursor:pointer;">
                                <i class="mr-1 fa fa-exchange"></i> Otro cliente
                            </button>
                            <button type="button" wire:click="cancelarConfirmOferta"
                                    style="background:#f0f0f0; color:#555; border:none;
                                           border-radius:8px; padding:7px 14px; font-size:12px; cursor:pointer;">
                                Cancelar
                            </button>
                        </div>
                        @else
                        {{-- Panel selector de cliente para "Otro cliente" --}}
                        <div style="background:#fff; border:1px solid #c8e6c9; border-radius:10px;
                                    padding:12px; text-align:left; margin-top:6px;">
                            <p style="font-size:12px; font-weight:700; color:#2e7d32; margin:0 0 8px;">
                                <i class="mr-1 fa fa-search"></i> Seleccionar cliente destino
                            </p>
                            <div style="position:relative;">
                                <input type="text"
                                       wire:model.debounce.350ms="busquedaClienteDuplicar"
                                       placeholder="Buscar cliente por nombre o código…"
                                       style="width:100%; padding:7px 10px; border:1px solid #ccc;
                                              border-radius:6px; font-size:12px; box-sizing:border-box;"
                                       autocomplete="off" />
                                @if (count($resultadosClienteDuplicar) > 0)
                                <ul style="position:absolute; top:100%; left:0; right:0; z-index:9999;
                                           background:#fff; border:1px solid #ccc; border-top:none;
                                           border-radius:0 0 6px 6px; margin:0; padding:0;
                                           list-style:none; max-height:180px; overflow-y:auto;
                                           box-shadow:0 4px 12px rgba(0,0,0,.12);">
                                    @foreach ($resultadosClienteDuplicar as $rc)
                                    <li wire:click="seleccionarClienteDuplicar({{ $rc['id'] }}, '{{ addslashes($rc['nombre']) }}')"
                                        style="padding:7px 12px; font-size:12px; cursor:pointer;
                                               border-bottom:1px solid #f0f0f0; color:#333;"
                                        onmouseover="this.style.background='#e8f5e9'"
                                        onmouseout="this.style.background=''">
                                        <span style="font-weight:600;">{{ $rc['nombre'] }}</span>
                                        <span style="color:#999; font-size:11px;"> #{{ $rc['id'] }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                                @elseif (mb_strlen(trim($busquedaClienteDuplicar)) >= 2 && !$clienteDuplicarId)
                                <div style="position:absolute; top:100%; left:0; right:0; z-index:9999;
                                            background:#fff; border:1px solid #ccc; border-top:none;
                                            border-radius:0 0 6px 6px; padding:9px 12px;
                                            box-shadow:0 4px 12px rgba(0,0,0,.12);
                                            color:#777; font-size:12px;">
                                    <i class="mr-1 fa fa-info-circle"></i>
                                    No hay clientes asignados que coincidan con la búsqueda.
                                </div>
                                @endif
                            </div>

                            @if ($clienteDuplicarId)
                            <div style="margin-top:8px; background:#e8f5e9; border:1px solid #a5d6a7;
                                        border-radius:6px; padding:7px 10px; font-size:12px; color:#2e7d32;">
                                <i class="mr-1 fa fa-check-circle"></i>
                                <strong>{{ $clienteDuplicarNombre }}</strong>
                                <span style="color:#777;"> #{{ $clienteDuplicarId }}</span>
                            </div>
                            @endif

                            @if ($clienteDuplicarError === 'escala_diferente' && count($clienteDuplicarEscalaConflicto) > 0)
                            <div style="margin-top:8px; background:#fdecea; border:1px solid #ef9a9a; border-radius:8px; overflow:hidden;">
                                <div style="background:#f8d7da; padding:8px 12px; font-size:12px; font-weight:700; color:#7b1c28;">
                                    <i class="mr-1 fa fa-ban"></i>
                                    No se puede duplicar la oferta: los clientes cuentan con distinta escala de precios.
                                </div>
                                <table style="width:100%; border-collapse:collapse; font-size:12px;">
                                    <thead>
                                        <tr style="background:#fff5f5; color:#7b1c28;">
                                            <th style="padding:6px 10px; text-align:left; border-bottom:1px solid #ef9a9a;">Cliente</th>
                                            <th style="padding:6px 10px; text-align:center; border-bottom:1px solid #ef9a9a;">Categoría</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($clienteDuplicarEscalaConflicto as $i => $row)
                                        <tr style="background:{{ $i === 0 ? '#fffaf9' : '#fff' }}; border-bottom:1px solid #f5c6cb;">
                                            <td style="padding:6px 10px; color:#333;">{{ $row['nombre'] }}</td>
                                            <td style="padding:6px 10px; text-align:center;">
                                                <span style="background:#f8d7da; color:#7b1c28; border-radius:10px; padding:2px 10px; font-size:11px; font-weight:700;">
                                                    {{ $row['categoria'] }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @elseif ($clienteDuplicarError)
                            <div style="margin-top:6px; background:#fdecea; border:1px solid #ef9a9a;
                                        border-radius:6px; padding:7px 10px; font-size:12px; color:#c62828;">
                                <i class="mr-1 fa fa-exclamation-circle"></i> {{ $clienteDuplicarError }}
                            </div>
                            @endif

                            @if (count($productosPrecioEscalaCambiado) > 0)
                            <div style="margin-top:8px; border:1px solid #ef9a9a; border-radius:8px; overflow:hidden;">
                                <div style="background:#fff5f5; color:#8a1f1f; padding:6px 8px; font-size:11px; font-weight:700;">
                                    Productos con cambio de precio de escala
                                </div>
                                <div style="max-height:130px; overflow-y:auto; background:#fff;">
                                    <table style="width:100%; border-collapse:collapse; font-size:11px;">
                                        <thead>
                                            <tr style="background:#fff9f9; color:#8a1f1f;">
                                                <th style="padding:5px 7px; text-align:left;">Producto</th>
                                                <th style="padding:5px 7px; text-align:center;">Escala</th>
                                                <th style="padding:5px 7px; text-align:right;">Oferta</th>
                                                <th style="padding:5px 7px; text-align:right;">Actual</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($productosPrecioEscalaCambiado as $prdCambio)
                                            <tr style="border-top:1px solid #f3e0e0;">
                                                <td style="padding:5px 7px; color:#333;">{{ $prdCambio['nombre_producto'] }}</td>
                                                <td style="padding:5px 7px; text-align:center; color:#8a1f1f; font-weight:700;">{{ $prdCambio['escala'] }}</td>
                                                <td style="padding:5px 7px; text-align:right; color:#666;">L {{ number_format((float) $prdCambio['precio_oferta'], 2) }}</td>
                                                <td style="padding:5px 7px; text-align:right; color:#b71c1c; font-weight:700;">L {{ number_format((float) $prdCambio['precio_escala_actual'], 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif

                            <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:10px;">
                                <button type="button" wire:click="confirmarDuplicarOtroCliente()"
                                        style="background:linear-gradient(135deg,#1ab394,#0fa37a); color:#fff;
                                               border:none; border-radius:8px; padding:7px 16px;
                                               font-size:12px; font-weight:700; cursor:pointer;"
                                        {{ $clienteDuplicarId ? '' : 'disabled' }}>
                                    <i class="mr-1 fa fa-check"></i> Duplicar
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
                    @endif

                </div>
                {{-- /oferta seleccionada --}}

                @else
                {{-- ── Lista de ofertas ─────────────────────────────── --}}
                <div style="margin-top:12px; border-radius:12px; overflow:hidden; border:1px solid #ede9f7;">
                    <div style="background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%); padding:10px 16px;">
                        <span style="color:#fff; font-size:13px; font-weight:700;">
                            <i class="mr-1 fa fa-tag"></i> Ofertas asociadas
                            @if (count($ofertasPedido) > 0)
                            <span style="background:rgba(255,255,255,.22); border-radius:20px; padding:1px 9px; font-size:11px; margin-left:6px;">
                                {{ count($ofertasPedido) }}
                            </span>
                            @endif
                        </span>
                    </div>
                    <div class="fmp-offers-wrap" style="background:#fff; padding:10px 14px; max-height:250px; overflow-y:auto;">
                        @if (count($ofertasPedido) === 0)
                        <div class="py-3 text-center text-muted" style="font-size:12px;">
                            <i class="mb-1 fa fa-inbox fa-lg d-block" style="opacity:.3;"></i>
                            Sin ofertas aún para este pedido.
                        </div>
                        @else
                        @foreach ($ofertasPedido as $of)
                        @php
                            $obs2      = $of['hf_observaciones'] ?? '';
                            $isGan2    = ($obs2 === 'ganadora');
                            $isAnu2    = str_starts_with($obs2, 'Anulado:');
                            $isVenc2   = str_starts_with($obs2, 'VencidaPrecios:');
                            $listBadgeBg    = $isGan2 ? '#d4edda' : ($isAnu2 ? '#f8d7da' : ($isVenc2 ? '#fff3e0' : '#e8f0fe'));
                            $listBadgeColor = $isGan2 ? '#155724' : ($isAnu2 ? '#721c24' : ($isVenc2 ? '#e65100' : '#1a7efb'));
                            $listBadgeText  = $isGan2 ? 'Ganadora' : ($isAnu2 ? 'Anulada' : ($isVenc2 ? 'Precios cambiaron' : 'Activa'));
                        @endphp
                        <button type="button"
                                wire:key="oferta-card-{{ $of['cotizacion_id'] }}"
                                wire:click.prevent.stop="verOferta({{ $of['cotizacion_id'] }})"
                            class="fmp-offer-card"
                            style="opacity:{{ ($isAnu2 || $isVenc2) ? '.65' : '1' }};">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <span style="font-weight:800; color:#2c3e50; font-size:13px;">
                                        Oferta #{{ $of['cotizacion_id'] }}
                                    </span>
                                    @if (!empty($of['categoria_producto_nombre']))
                                    <span style="background:#f0f4ff; color:#3d5a9e;
                                                 border-radius:10px; padding:1px 7px; font-size:10px;
                                                 font-weight:600; margin-left:5px;">
                                        {{ $of['categoria_producto_nombre'] }}
                                    </span>
                                    @endif
                                    <span style="background:{{ $listBadgeBg }}; color:{{ $listBadgeColor }};
                                                 border-radius:12px; padding:1px 8px; font-size:10px;
                                                 font-weight:700; margin-left:6px;">
                                        @if ($isGan2)<i class="mr-1 fa fa-trophy"></i>@elseif($isVenc2)<i class="mr-1 fa fa-exclamation-triangle"></i>@endif
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
                        </button>
                        @endforeach
                        @endif
                    </div>
                </div>


                @endif
                {{-- /ofertaSeleccionada else --}}

                @endif
                {{-- /paso ofertas --}}

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- PASO: REVISIÓN DE CRÉDITO (informativo en modal)   --}}
                {{-- ══════════════════════════════════════════════════ --}}
                @if ($pasoActivo === 'revision_credito')
                @php
                    $crEstado     = $creditoRevisionData['estado'] ?? 'pendiente';
                    $crAprobado   = ($crEstado === 'aprobado');
                    $crRechazado  = ($crEstado === 'rechazado');
                    $crMensajeOficial = trim((string)($creditoRevisionData['observaciones'] ?? ''));
                    $crColor      = $crAprobado ? '#1b5e20,#2e7d32' : ($crRechazado ? '#b71c1c,#c62828' : '#0d47a1,#1565c0');
                    $crIcon       = $crAprobado ? 'fa-check-circle' : ($crRechazado ? 'fa-times-circle' : 'fa-credit-card');
                @endphp
                <div style="margin-top:14px;">
                    <div style="background:linear-gradient(135deg,#e3f2fd,#bbdefb);
                                border:1.5px solid #64b5f6; border-radius:14px;
                                padding:16px 22px; display:flex; align-items:center; gap:16px; margin-bottom:14px;">
                        <div style="width:52px; height:52px; border-radius:50%;
                                    background:linear-gradient(135deg,{{ $crColor }});
                                    display:flex; align-items:center; justify-content:center;
                                    font-size:24px; color:#fff; flex-shrink:0;
                                    box-shadow:0 4px 16px rgba(26,126,251,.35);">
                            <i class="fa {{ $crIcon }}"></i>
                        </div>
                        <div>
                            <h5 style="color:#0d47a1; font-weight:700; margin:0 0 6px; font-size:15px;">
                                Revisión de Crédito
                                <span style="font-size:12px; background:{{ $crAprobado ? '#c8e6c9' : ($crRechazado ? '#ffcdd2' : '#bbdefb') }};
                                             color:{{ $crAprobado ? '#2e7d32' : ($crRechazado ? '#c62828' : '#1565c0') }};
                                             border-radius:20px; padding:2px 10px; margin-left:6px; vertical-align:middle;">
                                    {{ strtoupper($crEstado) }}
                                </span>
                            </h5>
                            <p style="font-size:12px; color:#1565c0; margin:0;">
                                @if ($crAprobado)
                                    <i class="mr-1 fa fa-check-circle"></i>
                                    Crédito aprobado.
                                    @if(!empty($creditoRevisionData['fecha_aprobacion']))
                                        Autorizado el {{ \Carbon\Carbon::parse($creditoRevisionData['fecha_aprobacion'])->format('d/m/Y') }}.
                                    @endif
                                    @if(array_key_exists('dias_credito_aprobados', $creditoRevisionData) && !is_null($creditoRevisionData['dias_credito_aprobados']))
                                        Días de crédito aprobados: <strong>{{ (int) $creditoRevisionData['dias_credito_aprobados'] }}</strong>.
                                    @elseif(!empty($creditoRevisionData['fecha_aprobacion']) && !empty($creditoRevisionData['fecha_vencimiento_credito']))
                                        Días de crédito aprobados: <strong>{{ max(0, \Carbon\Carbon::parse($creditoRevisionData['fecha_aprobacion'])->diffInDays(\Carbon\Carbon::parse($creditoRevisionData['fecha_vencimiento_credito']), false)) }}</strong>.
                                    @endif
                                    @if(!empty($creditoRevisionData['usuario_revision']))
                                        <span style="display:block; margin-top:4px;">
                                            <i class="fa fa-user mr-1"></i>Aprobado por: <strong>{{ $creditoRevisionData['usuario_revision_nombre'] ?? '—' }}</strong>
                                        </span>
                                    @endif
                                    @if($crMensajeOficial !== '')
                                        <span style="display:block; margin-top:4px; word-break:break-word; overflow-wrap:anywhere;">
                                            <strong>Mensaje:</strong> {{ $crMensajeOficial }}
                                        </span>
                                    @endif
                                @elseif ($crRechazado)
                                    <i class="mr-1 fa fa-times-circle"></i>
                                    Crédito rechazado.
                                    @if(!empty($creditoRevisionData['usuario_revision']))
                                        <span style="display:block; margin-top:4px;">
                                            <i class="fa fa-user mr-1"></i>Rechazado por: <strong>{{ $creditoRevisionData['usuario_revision_nombre'] ?? '—' }}</strong>
                                        </span>
                                    @endif
                                    @if(!empty($creditoRevisionData['motivo_rechazo']))
                                        <span style="display:block; margin-top:4px; word-break:break-word; overflow-wrap:anywhere;">
                                            Motivo: {{ $creditoRevisionData['motivo_rechazo'] }}
                                        </span>
                                    @endif
                                    @if($crMensajeOficial !== '')
                                        <span style="display:block; margin-top:4px; word-break:break-word; overflow-wrap:anywhere;">
                                            <strong>Mensaje:</strong> {{ $crMensajeOficial }}
                                        </span>
                                    @endif
                                @else
                                    <i class="mr-1 fa fa-clock-o"></i>
                                    Pendiente de revisión en la bandeja de Revisión de Crédito.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                @endif {{-- /paso revision_credito --}}

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- PASO: REVISIÓN DE INVENTARIO (informativo)         --}}
                {{-- ══════════════════════════════════════════════════ --}}
                @if ($pasoActivo === 'revision_inventario')
                @php
                    $ciclos      = $revisionHistorial ?? [];
                    $totalCiclos = count($ciclos);
                @endphp
                <div style="margin-top:14px;">

                    {{-- Encabezado de estado --}}
                    <div style="background:linear-gradient(135deg,#f3e5f5,#ede7f6);
                                border:1.5px solid #ce93d8; border-radius:14px;
                                padding:16px 22px; display:flex; align-items:center; gap:16px; margin-bottom:14px;">
                        <div style="width:52px; height:52px; border-radius:50%;
                                    background:linear-gradient(135deg,#9c27b0,#7b1fa2);
                                    display:flex; align-items:center; justify-content:center;
                                    font-size:24px; color:#fff; flex-shrink:0;
                                    box-shadow:0 4px 16px rgba(156,39,176,.35);">
                            <i class="fa fa-search"></i>
                        </div>
                        <div>
                            <h5 style="color:#6a1b9a; font-weight:700; margin:0 0 6px; font-size:15px;">
                                Revisión de Inventario
                            </h5>
                            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                @if ($revisionDevuelta ?? false)
                                <span style="background:#fff3e0; color:#e65100; border-radius:6px;
                                             padding:2px 10px; font-size:12px; font-weight:700;">
                                    <i class="fa fa-reply mr-1"></i>Devuelta a Oferta
                                </span>
                                @elseif ($totalCiclos > 0)
                                <span style="background:#e3f2fd; color:#1565c0; border-radius:6px;
                                             padding:2px 10px; font-size:12px; font-weight:700;">
                                    <i class="fa fa-clock-o mr-1"></i>Pendiente de revisión
                                </span>
                                @else
                                <span style="background:#f3e5f5; color:#6a1b9a; border-radius:6px;
                                             padding:2px 10px; font-size:12px; font-weight:700;">
                                    <i class="fa fa-clock-o mr-1"></i>Sin revisión aún
                                </span>
                                @endif
                                @if ($totalCiclos > 0)
                                <span style="font-size:11px; color:#9c27b0;">
                                    {{ $totalCiclos }} ciclo(s)
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Historial de ciclos de revisión --}}
                    @forelse ($ciclos as $i => $ciclo)
                    @php
                        $cNum   = $i + 1;
                        $cEst   = (int) ($ciclo['estado_id'] ?? 5);
                        $cObs   = $ciclo['observaciones'] ?? '';
                        // Parsear observaciones: "Motivo | [Producto A]: nota | [Producto B]: nota"
                        $cMotivo = $cObs;
                        $cProds  = [];
                        if (str_contains($cObs, ' | [')) {
                            $cParts  = explode(' | [', $cObs);
                            $cMotivo = trim(array_shift($cParts));
                            foreach ($cParts as $cp) {
                                if (str_contains($cp, ']: ')) {
                                    [$cNom, $cNota] = explode(']: ', $cp, 2);
                                    $cProds[] = ['nombre' => trim($cNom), 'nota' => trim($cNota)];
                                }
                            }
                        }
                        // Limpiar prefijo almacenado del motivo
                        $cMotivo = trim(preg_replace('/^(Devuelto a Oferta\s*:\s*|En Revisi\xC3\xB3n de Inventario\.?\s*|Revisi\xC3\xB3n aprobada\.?\s*)/iu', '', $cMotivo));
                        $cLabel  = match($cEst) {
                            1       => ['txt' => 'Aprobada',          'color' => '#2e7d32', 'bg' => '#e8f5e9', 'icon' => 'fa-check-circle'],
                            7       => ['txt' => 'Devuelta a Oferta', 'color' => '#e65100', 'bg' => '#fff3e0', 'icon' => 'fa-reply'],
                            default => ['txt' => 'Pendiente',         'color' => '#1565c0', 'bg' => '#e3f2fd', 'icon' => 'fa-clock-o'],
                        };
                        $cRevisor = $ciclo['revisor_nombre']   ?? null;
                        $cAprobad = $ciclo['aprobador_nombre'] ?? null;
                        $cFechaC  = $ciclo['created_at']  ?? null;
                        $cFechaU  = $ciclo['updated_at']  ?? null;
                    @endphp
                    <div style="background:#fff; border:1px solid #e8eaf0; border-radius:12px;
                                padding:14px 18px; margin-bottom:12px;
                                border-left:4px solid {{ $cLabel['color'] }};">

                        {{-- Ciclo header --}}
                        <div style="display:flex; align-items:center; justify-content:space-between;
                                    flex-wrap:wrap; gap:8px; margin-bottom:10px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="background:#f0f2f8; color:#555; border-radius:20px;
                                             padding:2px 10px; font-size:11px; font-weight:700;">
                                    Ciclo #{{ $cNum }}
                                </span>
                                <span style="background:{{ $cLabel['bg'] }}; color:{{ $cLabel['color'] }};
                                             border-radius:8px; padding:2px 10px; font-size:12px; font-weight:700;">
                                    <i class="fa {{ $cLabel['icon'] }} mr-1"></i>{{ $cLabel['txt'] }}
                                </span>
                            </div>
                            @if ($cFechaC)
                            <span style="font-size:11px; color:#888;">
                                <i class="fa fa-calendar mr-1"></i>
                                {{ \Carbon\Carbon::parse($cFechaC)->format('d/m/Y H:i') }}
                            </span>
                            @endif
                        </div>

                        {{-- Revisor y Aprobador --}}
                        <div style="display:flex; gap:16px; flex-wrap:wrap; margin-bottom:8px;">
                            @if ($cRevisor)
                            <div style="font-size:12px; color:#555;">
                                <i class="fa fa-user-circle-o mr-1 text-muted"></i>
                                <strong>Revisado por:</strong> {{ $cRevisor }}
                            </div>
                            @endif
                            @if ($cAprobad && $cEst === 1)
                            <div style="font-size:12px; color:#2e7d32;">
                                <i class="fa fa-check-circle mr-1"></i>
                                <strong>Autorizado por:</strong> {{ $cAprobad }}
                                @if ($cFechaU)
                                <span style="color:#888;"> · {{ \Carbon\Carbon::parse($cFechaU)->format('d/m/Y H:i') }}</span>
                                @endif
                            </div>
                            @endif
                        </div>

                        {{-- Motivo de devolución (solo si devuelta) --}}
                        @if ($cMotivo !== '' && $cEst === 7)
                        <div style="background:#fff8f0; border-radius:8px; padding:8px 12px; margin-bottom:8px;">
                            <div style="font-size:11px; color:#888; margin-bottom:3px; font-weight:700;">
                                <i class="fa fa-comment-o mr-1"></i>MOTIVO DE DEVOLUCIÓN
                            </div>
                            <p style="font-size:12px; color:#555; margin:0;">{{ $cMotivo }}</p>
                        </div>
                        @endif

                        {{-- Productos con observaciones --}}
                        @if (!empty($cProds))
                        <div>
                            <div style="font-size:11px; color:#888; margin-bottom:6px; font-weight:700;
                                        text-transform:uppercase; letter-spacing:.4px;">
                                <i class="fa fa-list-ul mr-1"></i>Productos con notas
                            </div>
                            @foreach ($cProds as $cp)
                            <div style="background:#f8f9fc; border-radius:8px; padding:8px 12px;
                                        margin-bottom:5px; border-left:3px solid #e67e22;">
                                <div style="font-size:12px; color:#555; font-weight:700; margin-bottom:2px;">
                                    <i class="fa fa-cube mr-1 text-muted"></i>{{ $cp['nombre'] }}
                                </div>
                                <div style="font-size:12px; color:#666;">{{ $cp['nota'] }}</div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        {{-- Info para ciclo pendiente --}}
                        @if ($cEst === 5)
                        <div style="font-size:12px; color:#666; margin-top:4px;">
                            <i class="fa fa-info-circle mr-1 text-muted"></i>
                            El encargado de inventario validará la disponibilidad de los productos.
                        </div>
                        @endif
                    </div>
                    @empty
                    <div style="background:#fff; border-radius:12px; border:1px solid #e8eaf0;
                                padding:20px; text-align:center; color:#aaa;">
                        <i class="fa fa-inbox d-block" style="font-size:28px; margin-bottom:8px; opacity:.3;"></i>
                        <p style="margin:0; font-size:13px;">Sin historial de revisión aún.</p>
                    </div>
                    @endforelse
                </div>
                @endif
                {{-- /paso revision_inventario --}}

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- PASO: PREFACTURA                                       --}}
                {{-- ══════════════════════════════════════════════════ --}}
                @if ($pasoActivo === 'prefactura')

                @if ($prefacturaData)
                @php $pref = $prefacturaData; @endphp
                <div style="margin-top:12px;">

                    @if(count($prefacturasData) > 1)
                    <div class="table-responsive" style="margin-bottom:10px;">
                        <table class="table table-sm table-bordered" style="font-size:11px; background:#fff;">
                            <thead><tr><th>Prefactura</th><th>Emisión</th><th>Vencimiento</th><th>Estado</th><th class="text-right">Total</th></tr></thead>
                            <tbody>@foreach($prefacturasData as $prefHist)<tr>
                                <td>#{{ $prefHist['id'] }}</td><td>{{ $prefHist['fecha_emision'] }}</td><td>{{ $prefHist['fecha_vencimiento'] }}</td>
                                <td>{{ ucfirst($prefHist['estado']) }}</td><td class="text-right">L {{ number_format($prefHist['total'], 2) }}</td>
                            </tr>@endforeach</tbody>
                        </table>
                    </div>
                    @endif

                    {{-- Cabecera de prefactura --}}
                    <div style="background:#fff; border-radius:10px; border:1px solid #e8eaf0;
                                padding:12px 14px; margin-bottom:10px; font-size:12px; color:#555;">
                        <div style="display:flex; flex-wrap:wrap; gap:12px;">
                            <span><i class="mr-1 fa fa-user text-info"></i>{{ $pref['nombre_cliente'] }}</span>
                            <span><i class="mr-1 fa fa-calendar text-muted"></i>
                                {{ \Carbon\Carbon::parse($pref['fecha_emision'])->format('d/m/Y') }}
                            </span>
                            <span style="color:#e67e22;">
                                <i class="mr-1 fa fa-clock-o"></i>
                                Vence: {{ \Carbon\Carbon::parse($pref['fecha_vencimiento'])->format('d/m/Y') }}
                            </span>
                            @if ($prefacturaVencida)
                            <span style="background:#fff3e0; color:#ef6c00; border-radius:8px; padding:1px 8px; font-size:10px; font-weight:700;">
                                <i class="mr-1 fa fa-exclamation-triangle"></i> Vencida (reserva liberada)
                            </span>
                            @endif
                            <strong style="color:#e65100;">
                                Total: L {{ number_format($pref['total'], 2) }}
                            </strong>
                            @if(($pref['estado'] ?? '') === 'convertida')
                            <span style="background:#e3f2fd; color:#1565c0; border-radius:8px; padding:1px 8px; font-size:10px; font-weight:700;">
                                <i class="mr-1 fa fa-file-text"></i> Facturada
                            </span>
                            @else
                                @if ($prefacturaReservaCompleta)
                                <span style="background:#e8f5e9; color:#1b5e20; border-radius:8px; padding:1px 8px; font-size:10px; font-weight:700;">
                                    <i class="mr-1 fa fa-check-circle"></i> Activa
                                </span>
                                @else
                                <span style="background:#fff3e0; color:#e65100; border-radius:8px; padding:1px 8px; font-size:10px; font-weight:700;">
                                    <i class="mr-1 fa fa-exclamation-triangle"></i> Activa sin reserva
                                </span>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Productos de la prefactura --}}
                    @if (!empty($pref['productos']))
                    <div style="border-radius:10px; overflow:hidden; border:1px solid #e8eaf0;
                                max-height:160px; overflow-y:auto; margin-bottom:10px;">
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
                                @foreach ($pref['productos'] as $pp)
                                <tr style="border-bottom:1px solid #f0f0f0;">
                                    <td style="padding:4px 8px; color:#2c3e50;">{{ $pp['nombre_producto'] }}</td>
                                    <td style="padding:4px 8px; text-align:center; color:#1a7efb; font-weight:700;">{{ (int)$pp['cantidad'] }}</td>
                                    <td style="padding:4px 8px; text-align:right; color:#555;">
                                        @if ($pp['precio_unidad']) L {{ number_format($pp['precio_unidad'], 2) }} @else — @endif
                                    </td>
                                    <td style="padding:4px 8px; text-align:right; font-weight:700; color:#1ab394;">
                                        @if ($pp['total']) L {{ number_format($pp['total'], 2) }} @else — @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    {{-- Botones de acción --}}
                    @if ($confirmAccionPrefactura === null)
                    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:4px;">
                        @if (!$facturaCompletada)
<button type="button" wire:click="solicitarAutorizacionPrefactura('revertir_prefactura')"
                                style="background:linear-gradient(135deg,#1a7efb,#0d6efd); color:#fff;
                                       border:none; border-radius:8px; padding:6px 14px;
                                       font-size:12px; font-weight:700; cursor:pointer;">
                            <i class="mr-1 fa fa-arrow-left"></i> Pasar a Oferta
                        </button>

                        <button type="button" wire:click="solicitarAutorizacionPrefactura('anular_prefactura')"
                                style="background:linear-gradient(135deg,#e74c3c,#c0392b); color:#fff;
                                       border:none; border-radius:8px; padding:6px 14px;
                                       font-size:12px; font-weight:700; cursor:pointer;">
                            <i class="mr-1 fa fa-ban"></i> Anular
                        </button>
                        @endif

                        <a href="/prefactura/imprimir/{{ $pref['id'] }}" target="_blank"
                           style="background:#f8f9fc; color:#1a7efb; border:1px solid #e8eaf0;
                                  border-radius:8px; padding:6px 14px; font-size:12px; font-weight:700;
                                  text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                            <i class="fa fa-print"></i> Imprimir prefactura
                        </a>

                        @if (!$facturaCompletada)
                        @if ($prefacturaPuedeFacturar)
                        <button id="btn-facturar-directo" type="button" wire:click="facturarPrefacturaDirecta"
                                wire:loading.attr="disabled" wire:target="facturarPrefacturaDirecta"
                                style="background:linear-gradient(135deg,#1b5e20,#2e7d32); color:#fff;
                                       border:none; border-radius:8px; padding:6px 14px;
                                       font-size:12px; font-weight:700; cursor:pointer; transition:opacity .2s;">
                            <span id="btn-facturar-icon"><i class="mr-1 fa fa-file-text"></i> Facturar</span>
                            <span wire:loading wire:target="facturarPrefacturaDirecta" style="display:none;"
                                  id="btn-facturar-loading">
                                <i class="fa fa-spinner fa-spin mr-1"></i> Procesando...
                            </span>
                        </button>
                        @else
                        <button type="button" disabled
                                style="background:#cfd8dc; color:#607d8b; border:none; border-radius:8px; padding:6px 14px;
                                       font-size:12px; font-weight:700; cursor:not-allowed; opacity:.9;">
                            <i class="mr-1 fa fa-ban"></i> Facturar no disponible
                        </button>
                        @endif
                        <button type="button" wire:click="solicitarAutorizacionPrefactura('editar_factura')"
                                style="background:linear-gradient(135deg,#0f766e,#0ea5a4); color:#fff;
                                       border:none; border-radius:8px; padding:6px 14px;
                                       font-size:12px; font-weight:700; cursor:pointer;">
                            <i class="mr-1 fa fa-pencil"></i> Editar Factura
                        </button>
                        @endif

                    </div>
                    @endif

                    @if (!$prefacturaPuedeFacturar)
                    <div style="margin-top:10px; background:#fff8e1; border:1px solid #ffe082;
                                border-radius:10px; padding:10px 12px; font-size:12px; color:#7b4f00;">
                        <div style="font-weight:700; color:#e65100; margin-bottom:5px;">
                            <i class="mr-1 fa fa-exclamation-triangle"></i>
                            No es posible generar la factura porque uno o más productos ya no cuentan con inventario disponible. Actualice la prefactura antes de continuar.
                        </div>
                        @if (!empty($prefacturaStockFaltante))
                        <ul style="margin:0; padding-left:18px;">
                            @foreach ($prefacturaStockFaltante as $faltante)
                            <li>
                                {{ $faltante['producto'] }}: solicitado {{ $faltante['solicitado'] }}, disponible {{ $faltante['disponible'] }}
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    @endif

                    @if (!$prefacturaReservaCompleta)
                    <div style="margin-top:10px; background:#fff3e0; border:1px solid #ffcc80;
                                border-radius:10px; padding:10px 12px; font-size:12px; color:#7b4f00;">
                        <div style="font-weight:700; color:#e65100; margin-bottom:5px;">
                            <i class="mr-1 fa fa-lock"></i>
                            Esta prefactura no aparta producto porque no cuenta con la cantidad total requerida.
                        </div>
                        @if (!empty($prefacturaReservaFaltante))
                        <ul style="margin:0; padding-left:18px;">
                            @foreach ($prefacturaReservaFaltante as $faltante)
                            <li>
                                {{ $faltante['producto'] }}: requerido {{ $faltante['solicitado'] }}, disponible {{ $faltante['disponible'] }}.
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    @endif

                    @if ($mostrarAutorizacionPrefactura)
                    <div style="margin-top:10px; background:#fff8e1; border:1px solid #ffe082;
                                border-radius:12px; padding:14px;">
                        <p style="font-size:13px; color:#555; margin:0 0 8px; font-weight:700;">
                            <i class="mr-1 fa fa-shield text-warning"></i>
                            Se requiere autorización para
                            @if($accionAutorizacionPrefactura === 'editar_factura') editar la factura
                            @elseif($accionAutorizacionPrefactura === 'revertir_prefactura') pasar la prefactura a oferta
                            @else anular la prefactura
                            @endif.
                        </p>

                        {{-- Botón solicitar código --}}
                        <div style="margin-bottom:10px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                            <button type="button"
                                    wire:click="enviarCodigoPrefactura"
                                    wire:loading.attr="disabled"
                                    wire:target="enviarCodigoPrefactura"
                                    style="background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff;
                                           border:none; border-radius:8px; padding:6px 14px;
                                           font-size:12px; font-weight:700; cursor:pointer;">
                                <span wire:loading.remove wire:target="enviarCodigoPrefactura">
                                    <i class="mr-1 fa fa-send"></i> Solicitar código por correo
                                </span>
                                <span wire:loading wire:target="enviarCodigoPrefactura">
                                    <i class="fa fa-spinner fa-spin mr-1"></i> Enviando...
                                </span>
                            </button>
                            <span id="msgCodigoFlujo" style="font-size:12px; color:#2e7d32; display:none;">
                                <i class="fa fa-check mr-1"></i> Código enviado. Solicíteselo a su supervisor.
                            </span>
                        </div>

                        <div class="row" style="row-gap:8px;">
                            <div class="col-12 col-md-4">
                                <label style="font-size:11px; font-weight:700; margin-bottom:4px;">Código de autorización</label>
                                {{-- onkeydown stopPropagation: evita que Bootstrap modal capture el teclado --}}
                                <input type="password" class="form-control form-control-sm"
                                       wire:model.defer="codigoAutorizacion"
                                       onkeydown="event.stopPropagation()"
                                       placeholder="Ingrese el código">
                            </div>
                            <div class="col-12 col-md-8">
                                <label style="font-size:11px; font-weight:700; margin-bottom:4px;">Motivo / comentario <span style="color:#c0392b;">*</span></label>
                                <input type="text" class="form-control form-control-sm"
                                       wire:model.defer="motivoAutorizacion"
                                       onkeydown="event.stopPropagation()"
                                       placeholder="Motivo requerido">
                            </div>
                        </div>
                        @if($mensajeError)
                        <div style="margin-top:8px; color:#b71c1c; font-size:12px; font-weight:700;">
                            {{ $mensajeError }}
                        </div>
                        @endif
                        <div style="display:flex; gap:8px; margin-top:10px;">
                            <button type="button" wire:click="validarCodigoAutorizacionPrefactura"
                                    style="background:linear-gradient(135deg,#1a7efb,#0d6efd); color:#fff;
                                           border:none; border-radius:8px; padding:7px 18px;
                                           font-size:12px; font-weight:700; cursor:pointer;">
                                Validar y continuar
                            </button>
                            <button type="button" wire:click="cancelarAutorizacionPrefactura"
                                    style="background:#f0f0f0; color:#555; border:none;
                                           border-radius:8px; padding:7px 16px; font-size:12px; cursor:pointer;">
                                Cancelar
                            </button>
                        </div>
                    </div>
                    @endif



                </div>{{-- /prefacturaData --}}

                @else
                {{-- No hay prefactura activa todavía --}}
                <div style="margin-top:20px; text-align:center; padding:24px; color:#90a4ae;">
                    <i class="mb-2 fa fa-clock-o fa-2x d-block" style="opacity:.4;"></i>
                    <p style="font-size:13px; margin:0; font-weight:600;">Sin prefactura activa.</p>
                    <p style="font-size:12px; margin:4px 0 0; opacity:.7;">Marca una oferta como ganadora para generar la prefactura.</p>
                </div>
                @endif

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- PASO: FACTURA                                         --}}
                {{-- ══════════════════════════════════════════════════ --}}
                @elseif ($pasoActivo === 'factura')

                @if (!empty($facturasData))
                <div style="font-size:12px; color:#607d8b; margin:10px 0 4px;">
                    <i class="fa fa-files-o mr-1"></i>{{ count($facturasData) }} factura(s) registradas en este flujo
                </div>
                @foreach ($facturasData as $fac)
                <div style="margin-top:12px; background:#fff; border:1px solid #e8eaf0; border-radius:10px; padding:12px;">
                    <div style="background:#fff; border-radius:10px; border:1px solid #e8eaf0;
                                padding:12px 14px; margin-bottom:10px; font-size:12px; color:#555;">
                        <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
                            <span><i class="mr-1 fa fa-file-text text-primary"></i><strong>Factura #{{ $fac['id'] }}</strong></span>
                            <span><i class="mr-1 fa fa-user text-info"></i>{{ $fac['nombre_cliente'] ?? ($d['cliente'] ?? '—') }}</span>
                            <span><i class="mr-1 fa fa-calendar text-muted"></i>{{ \Carbon\Carbon::parse($fac['fecha_emision'] ?? $fac['created_at'])->format('d/m/Y') }}</span>
                            <strong style="color:#e65100;">Total: L {{ number_format($fac['total'] ?? 0, 2) }}</strong>
                            <span style="background:{{ (int)($fac['estado_venta_id'] ?? 0) === 1 ? '#e8f5e9' : '#ffebee' }}; color:{{ (int)($fac['estado_venta_id'] ?? 0) === 1 ? '#1b5e20' : '#b71c1c' }}; border-radius:8px; padding:2px 9px; font-weight:700;">
                                {{ (int)($fac['estado_venta_id'] ?? 0) === 1 ? 'Activa' : 'Anulada' }}
                            </span>
                        </div>
                    </div>

                    @if (!empty($fac['productos']))
                    <div style="border-radius:10px; overflow:hidden; border:1px solid #e8eaf0;
                                max-height:170px; overflow-y:auto; margin-bottom:10px;">
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
                                @foreach ($fac['productos'] as $fp)
                                <tr style="border-bottom:1px solid #f0f0f0;">
                                    <td style="padding:4px 8px; color:#2c3e50;">{{ $fp['nombre_producto'] ?? '—' }}</td>
                                    <td style="padding:4px 8px; text-align:center; color:#1a7efb; font-weight:700;">{{ isset($fp['cantidad']) ? (int)$fp['cantidad'] : '—' }}</td>
                                    <td style="padding:4px 8px; text-align:right; color:#555;">
                                        @if (!empty($fp['precio_unidad'])) L {{ number_format($fp['precio_unidad'], 2) }} @else — @endif
                                    </td>
                                    <td style="padding:4px 8px; text-align:right; font-weight:700; color:#1ab394;">
                                        @if (!empty($fp['total'])) L {{ number_format($fp['total'], 2) }} @else — @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @if ($confirmAccionFactura === null)
                    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:4px;">
                        <a href="/crear/vale/lista/espera/{{ $fac['id'] }}" target="_blank"
                           style="background:#eef2ff; color:#3730a3; border:1px solid #c7d2fe;
                                  border-radius:8px; padding:6px 14px; font-size:12px; font-weight:700;
                                  text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                            <i class="fa fa-ticket"></i> Crear vale
                        </a>

                        <a href="{{ $fac['print_url'] ?? ('/factura/cooporativo/' . $fac['id']) }}" target="_blank"
                           style="background:#f8f9fc; color:#1a7efb; border:1px solid #e8eaf0;
                                  border-radius:8px; padding:6px 14px; font-size:12px; font-weight:700;
                                  text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                            <i class="fa fa-print"></i> Imprimir factura
                        </a>

                        <a href="{{ $fac['print_copia_url'] ?? ('/factura/cooporativoCopia/' . $fac['id']) }}" target="_blank"
                           style="background:#f8f9fc; color:#455a64; border:1px solid #e8eaf0;
                                  border-radius:8px; padding:6px 14px; font-size:12px; font-weight:700;
                                  text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                            <i class="fa fa-copy"></i> Imprimir copia
                        </a>

                                <a href="{{ $fac['print_acta_rec_url'] ?? (((int)($fac['tipo_venta_id'] ?? 0) === 3) ? ('/exonerado/actaRec/' . $fac['id']) : ('/facturaCoor/actaRec/' . $fac['id'])) }}" target="_blank"
                                    style="background:#f8f9fc; color:#455a64; border:1px solid #e8eaf0;
                                             border-radius:8px; padding:6px 14px; font-size:12px; font-weight:700;
                                             text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                                     <i class="fa fa-print"></i> Imprimir Acta de Recepción
                                </a>

                        @if(!empty($fac['vale_id']))
                        <a href="/vale/imprimir/{{ $fac['vale_id'] }}" target="_blank"
                           style="background:#fff8e1; color:#e67e22; border:1px solid #fce4b3;
                                  border-radius:8px; padding:6px 14px; font-size:12px; font-weight:700;
                                  text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                            <i class="fa fa-ticket"></i> Imprimir Vale
                            @if(!empty($fac['vale_numero']))
                                <span style="background:#e67e22; color:#fff; border-radius:10px; padding:1px 7px; font-size:10px;">#{{ $fac['vale_numero'] }}</span>
                            @endif
                        </a>
                        @endif

                        @if((int)($fac['estado_venta_id'] ?? 0) === 1)
                        <button type="button" wire:click="confirmarAccionFactura('anular', {{ $fac['id'] }})"
                            style="background:#ffebee; color:#b71c1c; border:1px solid #ffcdd2; border-radius:8px; padding:6px 14px; font-size:12px; font-weight:700; cursor:pointer;">
                            <i class="fa fa-ban mr-1"></i>Anular esta factura
                        </button>
                        @endif

                    </div>
                    @endif

                    @if ($confirmAccionFactura === 'anular' && $facturaSeleccionadaId === (int)$fac['id'])
                    <div style="background:#fff3e0; border:1px solid #ffcc80; border-radius:8px; padding:12px; margin-top:10px;">
                        <label style="font-size:12px; font-weight:700; color:#e65100;">Motivo de anulación de la factura #{{ $fac['id'] }}</label>
                        <textarea class="form-control form-control-sm" wire:model.defer="motivoAnulacionFactura" rows="2" maxlength="500"></textarea>
                        @if($mensajeError)<div class="text-danger mt-1" style="font-size:12px;">{{ $mensajeError }}</div>@endif
                        <div style="display:flex; gap:8px; margin-top:8px;">
                            <button type="button" wire:click="anularFactura" class="btn btn-danger btn-sm">Confirmar anulación</button>
                            <button type="button" wire:click="cancelarConfirmFactura" class="btn btn-default btn-sm">Cancelar</button>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach

                @if(!empty($liquidacionExpoPendiente))
                @php $liq = $liquidacionExpoPendiente; @endphp
                <div style="margin-top:14px; border:1px solid #90caf9; border-radius:10px; overflow:hidden;">
                    <div style="padding:10px 12px; background:#e3f2fd; color:#0d47a1; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:8px;">
                        <strong style="font-size:12px;"><i class="fa fa-calculator mr-1"></i>Liquidación Expo pendiente de confirmación</strong>
                        <button type="button" class="btn btn-sm btn-primary" onclick='confirmarLiquidacionExpoFlujo(@json($liq))'>
                            <i class="fa fa-file-text-o mr-1"></i>Revisar y generar nota
                        </button>
                    </div>
                    <div style="padding:10px 12px; font-size:11px; color:#455a64;">
                        <div style="display:flex; flex-wrap:wrap; gap:18px; margin-bottom:8px;">
                            <span>Subtotal facturado: <strong>L {{ number_format($liq['total_facturado'] ?? 0, 2) }}</strong></span>
                            <span>Descuento por marca: <strong>L {{ number_format($liq['descuento_marca_total'] ?? 0, 2) }}</strong></span>
                            <span>Descuento general: <strong>{{ number_format($liq['porcentaje_descuento'] ?? 0, 2) }}% · L {{ number_format($liq['descuento_general'] ?? 0, 2) }}</strong></span>
                            <span>Total descuento: <strong style="color:#0d47a1;">L {{ number_format($liq['descuento_calculado'] ?? 0, 2) }}</strong></span>
                        </div>
                        @if(!empty($liq['descuentos_marca']))
                        <div class="table-responsive">
                            <table style="width:100%; border-collapse:collapse;">
                                <thead><tr style="color:#607d8b;"><th style="padding:3px 5px; text-align:left;">Factura</th><th style="padding:3px 5px; text-align:left;">Marca</th><th style="padding:3px 5px; text-align:right;">Subtotal</th><th style="padding:3px 5px; text-align:center;">Regla</th><th style="padding:3px 5px; text-align:right;">Descuento</th></tr></thead>
                                <tbody>@foreach($liq['descuentos_marca'] as $marca)<tr style="border-top:1px solid #e3f2fd;"><td style="padding:3px 5px;">{{ $marca['factura'] }}</td><td style="padding:3px 5px;">{{ $marca['marca'] }}</td><td style="padding:3px 5px; text-align:right;">L {{ number_format($marca['subtotal_bruto'], 2) }}</td><td style="padding:3px 5px; text-align:center;">{{ $marca['cumple'] ? 'Cumple' : 'No cumple' }}</td><td style="padding:3px 5px; text-align:right;">L {{ number_format($marca['descuento'], 2) }}</td></tr>@endforeach</tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @if(!empty($notasCreditoData))
                <div style="margin-top:14px; border:1px solid #b2dfdb; border-radius:10px; overflow:hidden;">
                    <div style="padding:9px 12px; background:#e0f2f1; color:#00695c; font-size:12px; font-weight:700;">
                        <i class="fa fa-file-text-o mr-1"></i>Notas de crédito relacionadas
                    </div>
                    @foreach($notasCreditoData as $nota)
                    <div style="padding:10px 12px; border-top:1px solid #d7eeec; font-size:11px;">
                        <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
                            <strong>Nota #{{ $nota['id'] }}</strong>
                            <span>{{ $nota['cai'] ?: ($nota['numero_nota'] ?? 'Sin correlativo') }}</span>
                            <span>{{ $nota['fecha'] ? \Carbon\Carbon::parse($nota['fecha'])->format('d/m/Y') : '—' }}</span>
                            <strong style="color:#00695c;">L {{ number_format($nota['total'] ?? 0, 2) }}</strong>
                            <span>Aplicado: L {{ number_format($nota['monto_aplicado'] ?? 0, 2) }}</span>
                            <span>Disponible: L {{ number_format($nota['saldo_disponible'] ?? 0, 2) }}</span>
                        </div>
                        @if(!empty($nota['aplicaciones']))
                        <table style="width:100%; margin-top:7px; border-collapse:collapse;">
                            <thead><tr style="color:#607d8b;"><th style="text-align:left; padding:3px 5px;">Factura aplicada</th><th style="text-align:left; padding:3px 5px;">Fecha</th><th style="text-align:right; padding:3px 5px;">Monto</th></tr></thead>
                            <tbody>
                                @foreach($nota['aplicaciones'] as $aplicacion)
                                <tr style="border-top:1px solid #edf4f3;">
                                    <td style="padding:3px 5px;">{{ $aplicacion['factura'] ?: ('#' . $aplicacion['factura_id']) }}</td>
                                    <td style="padding:3px 5px;">{{ \Carbon\Carbon::parse($aplicacion['fecha_movimiento'])->format('d/m/Y') }}</td>
                                    <td style="padding:3px 5px; text-align:right; font-weight:700;">L {{ number_format($aplicacion['monto'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
                @else
                <div style="margin-top:20px; text-align:center; padding:24px; color:#90a4ae;">
                    <i class="mb-2 fa fa-file-text-o fa-2x d-block" style="opacity:.4;"></i>
                    <p style="font-size:13px; margin:0; font-weight:600;">Sin factura registrada en este flujo.</p>
                </div>
                @endif

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- PASO: ENTREGAS                                        --}}
                {{-- ══════════════════════════════════════════════════ --}}
                @elseif ($pasoActivo === 'entrega')

                @if (!empty($historialEntregasFactura))
                <div style="margin-top:12px;">
                    <div style="background:#fff; border-radius:10px; border:1px solid #e8eaf0;
                                padding:12px 14px; margin-bottom:10px; font-size:12px; color:#555;">
                        <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
                            <span><i class="mr-1 fa fa-truck text-primary"></i><strong>Entregas de la factura</strong></span>
                            <span><i class="mr-1 fa fa-list-ul text-info"></i>{{ count($historialEntregasFactura) }} distribución(es) asociada(s)</span>
                            <span><i class="mr-1 fa fa-sort-amount-asc text-muted"></i>Orden cronológico</span>
                        </div>
                    </div>

                    <div style="border-radius:10px; overflow:hidden; border:1px solid #e8eaf0; background:#fff;">
                        <div style="background:linear-gradient(135deg,#1a7efb 0%,#0d6efd 100%); padding:10px 14px; color:#fff; font-size:13px; font-weight:700;">
                            <i class="mr-1 fa fa-history"></i> Historial de entregas
                        </div>

                        <div style="max-height:300px; overflow-y:auto;">
                            <table style="width:100%; font-size:11px; border-collapse:collapse;">
                                <thead>
                                    <tr style="background:#f8f9fc; color:#888; position:sticky; top:0;">
                                        <th style="padding:6px 8px; text-align:left;">Factura</th>
                                        <th style="padding:6px 8px; text-align:left;">Distribución</th>
                                        <th style="padding:6px 8px; text-align:left;">Fecha programada</th>
                                        <th style="padding:6px 8px; text-align:left;">Estado</th>
                                        <th style="padding:6px 8px; text-align:left;">Equipo responsable</th>
                                        <th style="padding:6px 8px; text-align:left;">Miembros</th>
                                        <th style="padding:6px 8px; text-align:center;">Orden</th>
                                        <th style="padding:6px 8px; text-align:left;">Entrega real</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($historialEntregasFactura as $entrega)
                                    @php
                                        $estadoEntregaDistribucion = (int) ($entrega['estado_id'] ?? 0);
                                        $estadoTexto = match($estadoEntregaDistribucion) {
                                            1 => 'Pendiente',
                                            2 => 'En proceso de entrega',
                                            3 => 'Completada',
                                            4 => 'Cancelada',
                                            default => 'Desconocido',
                                        };
                                        $estadoColor = match($estadoEntregaDistribucion) {
                                            1 => '#f39c12',
                                            2 => '#1a7efb',
                                            3 => '#1ab394',
                                            4 => '#e74c3c',
                                            default => '#9ca3af',
                                        };
                                    @endphp
                                    <tr style="border-bottom:1px solid #f0f0f0;">
                                        <td style="padding:6px 8px; color:#2c3e50; font-weight:700;">#{{ $entrega['factura_id'] }}</td>
                                        <td style="padding:6px 8px; color:#2c3e50; font-weight:700;">#{{ $entrega['distribucion_id'] }}</td>
                                        <td style="padding:6px 8px; color:#555;">{{ !empty($entrega['fecha_programada']) ? \Carbon\Carbon::parse($entrega['fecha_programada'])->format('d/m/Y') : '—' }}</td>
                                        <td style="padding:6px 8px;">
                                            <span style="display:inline-block; border-radius:10px; padding:2px 8px; font-weight:700; font-size:10px; color:#fff; background:{{ $estadoColor }};">
                                                {{ $estadoTexto }}
                                            </span>
                                        </td>
                                        <td style="padding:6px 8px; color:#555;">{{ !empty($entrega['nombre_equipo']) ? $entrega['nombre_equipo'] : '—' }}</td>
                                        <td style="padding:6px 8px; color:#6b7280;">{{ !empty($entrega['equipo_miembros']) ? $entrega['equipo_miembros'] : 'Sin miembros registrados' }}</td>
                                        <td style="padding:6px 8px; text-align:center; font-weight:700; color:#1a7efb;">{{ $entrega['orden_entrega'] ?? '—' }}</td>
                                        <td style="padding:6px 8px; color:#555;">{{ !empty($entrega['fecha_entrega_real']) ? \Carbon\Carbon::parse($entrega['fecha_entrega_real'])->format('d/m/Y H:i') : '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @else
                <div style="margin-top:20px; text-align:center; padding:24px; color:#90a4ae;">
                    <i class="mb-2 fa fa-truck fa-2x d-block" style="opacity:.4;"></i>
                    <p style="font-size:13px; margin:0; font-weight:600;">No hay entregas asociadas a esta factura.</p>
                    <p style="font-size:12px; margin:4px 0 0; opacity:.7;">Cuando se programe una distribución en logística, aparecerá aquí su historial completo.</p>
                </div>
                @endif

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- PASO: COBRO                                           --}}
                {{-- ══════════════════════════════════════════════════ --}}
                @elseif ($pasoActivo === 'cobro')

                @if ($cobroFacturaData)
                @php
                    $saldoCobro = (float) ($saldoPendienteFactura ?? 0);
                    $totalAbonado = collect($historialPagosFactura)->sum(function ($p) {
                        return (float) ($p['monto_abonado'] ?? 0);
                    });
                @endphp
                <div style="margin-top:12px;">
                    <div style="background:#fff; border-radius:10px; border:1px solid #e8eaf0;
                                padding:12px 14px; margin-bottom:10px; font-size:12px; color:#555;">
                        <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
                            <span><i class="mr-1 fa fa-file-text text-primary"></i><strong>Factura #{{ $cobroFacturaData['id'] }}</strong></span>
                            <span><i class="mr-1 fa fa-user text-info"></i>{{ $cobroFacturaData['nombre'] ?? ($d['cliente'] ?? '—') }}</span>
                            <span><i class="mr-1 fa fa-calendar text-muted"></i>{{ \Carbon\Carbon::parse($cobroFacturaData['fecha_emision'])->format('d/m/Y') }}</span>
                            <strong style="color:#e65100;">Total factura: L {{ number_format($cobroFacturaData['total'] ?? 0, 2) }}</strong>
                        </div>
                    </div>

                    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:10px;">
                        <div style="flex:1; min-width:180px; background:#fff; border:1px solid #e8eaf0; border-radius:10px; padding:12px 14px;">
                            <div style="font-size:11px; color:#888; font-weight:700; text-transform:uppercase; letter-spacing:.3px;">Saldo pendiente</div>
                            <div style="font-size:22px; font-weight:800; color:{{ $saldoCobro <= 0 ? '#1ab394' : '#1a7efb' }};">
                                L {{ number_format($saldoCobro, 2) }}
                            </div>
                            <div style="font-size:11px; color:#90a4ae;">Aplicación de pagos #{{ $aplicacionPagoId ?? '—' }}</div>
                        </div>

                        <div style="flex:1; min-width:180px; background:#fff; border:1px solid #e8eaf0; border-radius:10px; padding:12px 14px;">
                            <div style="font-size:11px; color:#888; font-weight:700; text-transform:uppercase; letter-spacing:.3px;">Total abonado</div>
                            <div style="font-size:22px; font-weight:800; color:#1ab394;">
                                L {{ number_format($totalAbonado, 2) }}
                            </div>
                            <div style="font-size:11px; color:#90a4ae;">{{ count($historialPagosFactura) }} pago(s) registrados</div>
                        </div>
                    </div>

                    <div style="border-radius:10px; overflow:hidden; border:1px solid #e8eaf0; background:#fff;">
                        <div style="background:linear-gradient(135deg,#1a7efb 0%,#0d6efd 100%); padding:10px 14px; color:#fff; font-size:13px; font-weight:700;">
                            <i class="mr-1 fa fa-list-ul"></i> Historial de pagos
                        </div>

                        @if (count($historialPagosFactura) === 0)
                        <div style="padding:16px; text-align:center; color:#90a4ae; font-size:12px;">
                            <i class="mb-1 fa fa-inbox d-block" style="opacity:.35; font-size:20px;"></i>
                            No hay pagos registrados para esta factura.
                        </div>
                        @else
                        <div style="max-height:260px; overflow-y:auto;">
                            <table style="width:100%; font-size:11px; border-collapse:collapse;">
                                <thead>
                                    <tr style="background:#f8f9fc; color:#888; position:sticky; top:0;">
                                        <th style="padding:6px 8px; text-align:left;">Factura</th>
                                        <th style="padding:6px 8px; text-align:left;">Fecha</th>
                                        <th style="padding:6px 8px; text-align:right;">Monto</th>
                                        <th style="padding:6px 8px; text-align:left;">Método</th>
                                        <th style="padding:6px 8px; text-align:left;">Banco</th>
                                        <th style="padding:6px 8px; text-align:left;">Recibo</th>
                                        <th style="padding:6px 8px; text-align:left;">Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($historialPagosFactura as $pago)
                                    <tr style="border-bottom:1px solid #f0f0f0;">
                                        <td style="padding:6px 8px; color:#2c3e50; font-weight:700;">#{{ $pago['factura_id'] }}</td>
                                        <td style="padding:6px 8px; color:#2c3e50;">{{ !empty($pago['fecha_pago']) ? \Carbon\Carbon::parse($pago['fecha_pago'])->format('d/m/Y') : '—' }}</td>
                                        <td style="padding:6px 8px; text-align:right; font-weight:700; color:#1ab394;">L {{ number_format((float) ($pago['monto_abonado'] ?? 0), 2) }}</td>
                                        <td style="padding:6px 8px; color:#555;">{{ $pago['tipo_pago'] ?? '—' }}</td>
                                        <td style="padding:6px 8px; color:#555;">{{ !empty($pago['banco']) ? $pago['banco'] : '—' }}</td>
                                        <td style="padding:6px 8px; color:#555;">{{ $pago['numero_recibo'] ?? '—' }}</td>
                                        <td style="padding:6px 8px; color:#90a4ae;">{{ $pago['usuario'] ?? '—' }}</td>
                                    </tr>
                                    @if (!empty($pago['comentario']))
                                    <tr style="border-bottom:1px solid #f0f0f0; background:#fafbff;">
                                        <td colspan="7" style="padding:6px 8px; color:#6b7280; font-size:10px;">
                                            <i class="mr-1 fa fa-sticky-note-o"></i>{{ $pago['comentario'] }}
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
                @else
                <div style="margin-top:20px; text-align:center; padding:24px; color:#90a4ae;">
                    <i class="mb-2 fa fa-clock-o fa-2x d-block" style="opacity:.4;"></i>
                    <p style="font-size:13px; margin:0; font-weight:600;">No se encontró información de cobro.</p>
                    <p style="font-size:12px; margin:4px 0 0; opacity:.7;">Primero debe existir una factura y su registro en aplicación de pagos.</p>
                </div>
                @endif

                {{-- Pasos pendientes: factura, entrega, cobro --}}
                @elseif (!in_array($pasoActivo, ['pedido', 'ofertas']))
                <div style="margin-top:20px; text-align:center; padding:24px; color:#90a4ae;">
                    <i class="mb-2 fa fa-clock-o fa-2x d-block" style="opacity:.4;"></i>
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
                    <i class="mr-1 fa fa-times"></i> Cerrar
                </button>

                  @if (!$fCancelado && $pasoActivo === 'pedido')
                  <a href="/flujo/pedido/imprimir/{{ $d['id'] }}"
                     target="_blank"
                     style="border-radius:20px; padding:6px 20px; background:#f8f9fc;
                         color:#1a7efb; font-size:13px; font-weight:700; text-decoration:none;
                         border:1px solid #e8eaf0; display:inline-block;">
                      <i class="mr-1 fa fa-print"></i> Imprimir
                  </a>

                  <button type="button" wire:click="confirmarAccion('duplicar')"
                       style="border-radius:20px; padding:6px 20px; background:linear-gradient(135deg,#1a7efb,#0d6efd);
                           color:#fff; border:none; font-size:13px; font-weight:700; cursor:pointer;">
                      <i class="mr-1 fa fa-copy"></i> Duplicar
                  </button>

                  @if (!$facturaCompletada)
                  <a href="/flujo/pedido/editar/{{ $d['id'] }}"
                     target="_blank"
                     style="border-radius:20px; padding:6px 20px; background:linear-gradient(135deg,#f39c12,#e67e22);
                         color:#fff; font-size:13px; font-weight:700; text-decoration:none;
                         display:inline-block;">
                      <i class="mr-1 fa fa-pencil"></i> Editar pedido
                  </a>

                  @if (!$tieneGanadora)
                  <button type="button" wire:click="nuevaOferta"
                       style="border-radius:20px; padding:6px 20px; background:linear-gradient(135deg,#e65100,#f9a826);
                           color:#fff; border:none; font-size:13px; font-weight:700; cursor:pointer;">
                      <i class="mr-1 fa fa-tag"></i> Ag. Oferta
                  </button>
                  @endif

                  <button type="button" wire:click="confirmarAccion('anular')"
                       style="border-radius:20px; padding:6px 20px; background:linear-gradient(135deg,#e74c3c,#c0392b);
                           color:#fff; border:none; font-size:13px; font-weight:700; cursor:pointer;">
                      <i class="mr-1 fa fa-ban"></i> Anular
                  </button>
                  @endif
                @endif

                  @if ($pasoActivo === 'ofertas' && !$tieneGanadora && !$facturaCompletada)
                <button type="button" wire:click="nuevaOferta"
                        style="border-radius:20px; padding:6px 20px; background:linear-gradient(135deg,#e65100,#f9a826);
                               color:#fff; border:none; font-size:13px; font-weight:700; cursor:pointer;">
                    <i class="mr-1 fa fa-tag"></i> Ag. Oferta
                </button>
                @endif

            </div>

        </div>
    </div>
</div>{{-- /overlay --}}

@endif

@if ($modalSinExistenciaVisible)
<div style="position:fixed !important; top:0 !important; right:0 !important; bottom:0 !important; left:0 !important; width:100vw !important; height:100vh !important; z-index:2147483647 !important; background:rgba(15,15,35,.62); backdrop-filter:blur(4px); -webkit-backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; padding:16px;">
    <div style="width:min(1100px, 100%); max-height:calc(100vh - 32px); overflow:hidden; background:#fff; border-radius:16px; box-shadow:0 24px 70px rgba(0,0,0,.35); display:flex; flex-direction:column;">
        <div style="background:linear-gradient(135deg,#7b1fa2,#9c27b0); padding:14px 20px; display:flex; align-items:flex-start; justify-content:space-between; gap:16px;">
            <div>
                <h5 style="color:#fff; font-weight:700; font-size:14px; margin:0;">
                    <i class="fa fa-pencil-square-o mr-2"></i>Editar productos sin existencia
                </h5>
                <small style="color:rgba(255,255,255,.85); font-size:11px; display:block; margin-top:2px;">
                    Reasigne únicamente los productos de esta oferta que no cuentan con existencia.
                </small>
            </div>
            <button type="button" wire:click="$set('modalSinExistenciaVisible', false)"
                    style="background:transparent; color:#fff; border:none; font-size:24px; line-height:1; cursor:pointer; padding:0;">
                &times;
            </button>
        </div>
        <div style="padding:18px 20px; overflow:auto;">
            @if ($mensajeErrorSinExistencia)
            <div class="alert alert-danger" style="font-size:12px; margin-bottom:12px;">
                <i class="fa fa-exclamation-triangle mr-1"></i>{{ $mensajeErrorSinExistencia }}
            </div>
            @endif

            <div class="alert alert-info" style="font-size:12px; margin-bottom:12px;">
                Seleccione la bodega y sección destino solo para los productos que ya tengan stock disponible en otra ubicación.
            </div>
            <div class="table-responsive" style="max-height:420px; overflow-y:auto; border:1px solid #e8eaf0; border-radius:10px;">
                <table class="table table-sm mb-0" style="font-size:12px;">
                    <thead style="background:#f8f9fc; position:sticky; top:0; z-index:1;">
                        <tr>
                            <th style="padding:8px 12px; color:#555; font-weight:700;">Producto</th>
                            <th style="padding:8px 12px; color:#555; font-weight:700;">Cantidad</th>
                            <th style="padding:8px 12px; color:#555; font-weight:700;">Bodega actual</th>
                            <th style="padding:8px 12px; color:#555; font-weight:700;">Destino con stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productosSinExistenciaModal as $idx => $linea)
                        <tr style="border-bottom:1px solid #f0f0f0;">
                            <td style="padding:10px 12px; color:#2c3e50; font-weight:600;">{{ $linea['nombre_producto'] }}</td>
                            <td style="padding:10px 12px; text-align:center; color:#e65100; font-weight:700;">{{ (int) $linea['cantidad'] }}</td>
                            <td style="padding:10px 12px; color:#607d8b;">
                                {{ $linea['bodega_actual_nombre'] ?? 'SIN EXISTENCIA' }}
                                @if(!empty($linea['seccion_actual_descripcion']))
                                <div style="font-size:11px; color:#90a4ae;">{{ $linea['seccion_actual_descripcion'] }}</div>
                                @endif
                            </td>
                            <td style="padding:10px 12px; min-width:340px;">
                                @if(!empty($linea['destinos']))
                                <select wire:model.defer="productosSinExistenciaModal.{{ $idx }}.destino_seleccionado"
                                        class="form-control form-control-sm"
                                        style="border-radius:8px; font-size:12px;">
                                    <option value="">Mantener sin cambio</option>
                                    @foreach ($linea['destinos'] as $destino)
                                    <option value="{{ $destino['value'] }}">{{ $destino['text'] }}</option>
                                    @endforeach
                                </select>
                                @else
                                <div style="background:#fff5f5; border:1px solid #ffcdd2; color:#b71c1c; border-radius:8px; padding:6px 10px; font-size:11px;">
                                    No hay bodegas con stock disponible para este producto.
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="padding:18px; text-align:center; color:#888;">
                                No hay productos sin existencia para editar.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:12px;">
                <label style="font-size:12px; font-weight:700; color:#334155; margin-bottom:4px; display:block;">
                    Motivo de la actualización
                </label>
                <textarea wire:model.defer="motivoEdicionSinExistencia"
                          rows="2"
                          class="form-control"
                          placeholder="Opcional: describa por qué se reasignaron estos productos..."
                          style="border-radius:8px; font-size:12px; resize:vertical;"></textarea>
            </div>
        </div>
        <div style="border-top:1px solid #eef0f5; padding:10px 20px; display:flex; justify-content:flex-end; gap:8px;">
            <button type="button" class="btn btn-primary" wire:click="guardarEdicionProductosSinExistencia" style="border-radius:8px; font-weight:700;">Guardar cambios</button>
            <button type="button" class="btn btn-default" wire:click="$set('modalSinExistenciaVisible', false)" style="border-radius:8px;">Cancelar</button>
        </div>
    </div>
</div>
@endif

<script>
    function confirmarLiquidacionExpoFlujo(resumen) {
        var moneda = function(valor) {
            return 'L ' + Number(valor || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };
        var aplicaciones = (resumen.aplicaciones || []).map(function(aplicacion) {
            return '<tr><td>' + (aplicacion.factura || ('#' + aplicacion.factura_id)) + '</td><td style="text-align:right">' + moneda(aplicacion.monto) + '</td></tr>';
        }).join('');

        Swal.fire({
            icon: 'warning',
            title: 'Generar nota de crédito',
            width: 680,
            showCancelButton: true,
            confirmButtonText: 'Confirmar y generar',
            cancelButtonText: 'Más tarde',
            showLoaderOnConfirm: true,
            allowOutsideClick: function() { return !Swal.isLoading(); },
            html: '<p style="font-size:13px">La nota se aplicará únicamente a las facturas activas de este flujo.</p>'
                + '<div style="display:flex;justify-content:space-around;margin:12px 0"><span>Descuento<br><strong>' + moneda(resumen.descuento_calculado) + '</strong></span><span>Aplicable<br><strong>' + moneda(resumen.saldo_aplicable) + '</strong></span><span>Diferencia<br><strong>' + moneda(resumen.diferencia) + '</strong></span></div>'
                + '<table class="table table-sm table-bordered"><thead><tr><th>Factura</th><th style="text-align:right">Aplicación propuesta</th></tr></thead><tbody>' + aplicaciones + '</tbody></table>',
            preConfirm: function() {
                return axios.post('/expo/liquidacion/confirmar', {
                    cotizacion_id: resumen.cotizacion_id,
                    flujo_id: resumen.flujo_id
                }).then(function(response) {
                    return response.data.liquidacionExpo;
                }).catch(function(error) {
                    var data = error.response ? error.response.data : {};
                    Swal.showValidationMessage(data.text || data.message || 'No se pudo generar la nota de crédito.');
                });
            }
        }).then(function(result) {
            if (!result.isConfirmed || !result.value) return;
            var liquidacion = result.value;
            Swal.fire({
                icon: liquidacion.estado === 'LIQUIDADA' ? 'success' : 'warning',
                title: liquidacion.estado === 'LIQUIDADA' ? 'Oferta liquidada' : 'Revisión contable pendiente',
                text: liquidacion.mensaje || 'La nota de crédito fue generada y aplicada.',
                confirmButtonText: 'Aceptar'
            }).then(function() {
                Livewire.emit('recargarFlujo');
            });
        });
    }

    function solicitarCodigoPrefactura(btn) {
        var msgEl = document.getElementById('msgSolicitarCodigo');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Enviando...';
        msgEl.style.color = '#555';
        msgEl.textContent = '';
        axios.get('/ventas/solicitud/codigo')
            .then(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-send mr-1"></i> Solicitar código por correo';
                msgEl.style.color = '#2e7d32';
                msgEl.textContent = 'Solicíteselo a su supervisor.';
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-send mr-1"></i> Solicitar código por correo';
                msgEl.style.color = '#b71c1c';
                msgEl.textContent = 'Error al enviar el código. Intente de nuevo.';
            });
    }

    if (!window._fmpListenerSet) {
        window._fmpListenerSet = true;
        window.addEventListener('flujo-codigo-enviado', function() {
            var msg = document.getElementById('msgCodigoFlujo');
            if (msg) msg.style.display = 'inline';
        });
        window.addEventListener('abrir-nueva-pestana', function(e) {
            window.open(e.detail.url, '_blank');
        });
        window.addEventListener('fmp-redirigir', function(e) {
            if (e.detail && e.detail.url) {
                window.location.href = e.detail.url;
            }
        });
        window.addEventListener('fmp-facturar-directo', function(e) {
            if (!e.detail || !e.detail.url) return;
            var detail = e.detail;

            Swal.fire({
                title: '<i class="fa fa-truck mr-2" style="color:#1565c0;"></i> Gestor de Entrega',
                html: '<div style="text-align:left;">'
                    + '<p style="font-size:13px;color:#666;margin-bottom:16px;">Seleccione el responsable de entrega y el tele asesor para la factura.</p>'
                    + '<label style="display:block;font-size:12px;font-weight:700;color:#455a64;margin:0 0 6px;">Gestor de entrega</label>'
                    + '<select id="swal-gestor-select" style="width:100%;"></select>'
                    + '<label style="display:block;font-size:12px;font-weight:700;color:#455a64;margin:14px 0 6px;">Tele asesor</label>'
                    + '<select id="swal-tele-asesor-select" style="width:100%;"></select>'
                    + '</div>',
                showCancelButton: true,
                confirmButtonText: '<i class="fa fa-check mr-1"></i> Confirmar y Facturar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#1b5e20',
                cancelButtonColor: '#6c757d',
                allowOutsideClick: false,
                background: '#f9fbe7',
                customClass: { popup: 'swal-gestor-popup' },
                didOpen: function() {
                    // Inicializar Select2 con búsqueda AJAX dentro del SweetAlert2
                    $('#swal-gestor-select').select2({
                        dropdownParent: $('.swal-gestor-popup'),
                        placeholder: '-- Sin gestor --',
                        allowClear: true,
                        ajax: {
                            url: '/ventas/corporativo/vendedores',
                            data: function(params) {
                                return { search: params.term || '', type: 'public', page: params.page || 1 };
                            },
                            processResults: function(data) {
                                return { results: [{ id: '', text: '-- Sin gestor --' }].concat(data.results || []) };
                            }
                        }
                    });
                    $('#swal-tele-asesor-select').select2({
                        dropdownParent: $('.swal-gestor-popup'),
                        placeholder: '-- Seleccionar tele asesor --',
                        allowClear: false
                    });
                    $.get('/cotizacion/actores-asignados', {
                        cliente_id: detail.cliente_id,
                        rol_id: 3
                    }).done(function(data) {
                        var teleasesores = data.results || [];
                        var actualAsignado = teleasesores.some(function(usuario) {
                            return Number(usuario.id) === Number(detail.tele_asesor_id);
                        });
                        teleasesores.forEach(function(usuario) {
                            var seleccionado = actualAsignado
                                ? Number(usuario.id) === Number(detail.tele_asesor_id)
                                : teleasesores.length === 1;
                            $('#swal-tele-asesor-select').append(
                                new Option(usuario.text, usuario.id, seleccionado, seleccionado)
                            );
                        });
                        $('#swal-tele-asesor-select').trigger('change');
                    });
                    // Evitar que el clic en el dropdown de Select2 cierre el SweetAlert
                    $(document).on('mousedown.swal2gestorfix', '.select2-container', function(e) {
                        e.stopPropagation();
                    });
                },
                willClose: function() {
                    $(document).off('mousedown.swal2gestorfix');
                    if ($('#swal-gestor-select').hasClass('select2-hidden-accessible')) {
                        $('#swal-gestor-select').select2('destroy');
                    }
                    if ($('#swal-tele-asesor-select').hasClass('select2-hidden-accessible')) {
                        $('#swal-tele-asesor-select').select2('destroy');
                    }
                },
                preConfirm: function() {
                    var teleAsesorId = $('#swal-tele-asesor-select').val() || null;
                    if (!teleAsesorId) {
                        Swal.showValidationMessage('Debe seleccionar un tele asesor.');
                        return false;
                    }
                    return {
                        gestorId: $('#swal-gestor-select').val() || null,
                        teleAsesorId: teleAsesorId
                    };
                }
            }).then(function(result) {
                if (!result.isConfirmed) return;
                var gestorId = result.value ? result.value.gestorId : null;
                var teleAsesorId = result.value ? result.value.teleAsesorId : null;

                // Bloquear botón y mostrar spinner durante el POST
                var btn = document.getElementById('btn-facturar-directo');
                var iconSpan    = document.getElementById('btn-facturar-icon');
                var loadingSpan = document.getElementById('btn-facturar-loading');
                function setLoading(loading) {
                    if (!btn) return;
                    btn.disabled = loading;
                    btn.style.opacity = loading ? '0.7' : '1';
                    btn.style.cursor  = loading ? 'not-allowed' : 'pointer';
                    if (iconSpan)    iconSpan.style.display    = loading ? 'none'   : '';
                    if (loadingSpan) loadingSpan.style.display = loading ? 'inline' : 'none';
                }
                setLoading(true);

                axios.post(detail.url, {
                    tipo_pago: detail.tipo_pago || 1,
                    gestor_entrega: gestorId,
                    tele_asesor: teleAsesorId
                })
                    .then(function(response) {
                        var data = response.data || {};
                        if (data.print_url) {
                            window.open(data.print_url, '_blank');
                        }
                        setTimeout(function() {
                            window.location.reload();
                        }, 800);
                    })
                    .catch(function(error) {
                        setLoading(false);
                        var data = error.response ? error.response.data : {};
                        Swal.fire({
                            icon: data.icon || 'error',
                            title: data.title || 'Error',
                            text: data.text || data.warning || (data.detail && data.detail.text) || data.error || 'No se pudo facturar la prefactura.',
                            html: (data.warning && data.warning.includes('<')) ? data.warning : undefined
                        });
                    });
            });
        });
    }
</script>
</div>
