<div>
    <style>
        /* ── Summary cards ── */
        .pf-stat-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:24px; }
        @media(max-width:700px){ .pf-stat-grid{ grid-template-columns:1fr; } }
        .pf-stat-card {
            background:#fff; border-radius:14px; border-left:5px solid transparent;
            box-shadow:0 2px 12px rgba(0,0,0,.07); padding:18px 20px;
            display:flex; align-items:center; gap:16px; cursor:default;
        }
        .pf-stat-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; }
        .pf-stat-num  { font-size:26px; font-weight:800; line-height:1; }
        .pf-stat-lbl  { font-size:12px; color:#8a96a3; margin-top:2px; }

        .pf-activa    { border-left-color:#0097a7; }
        .pf-activa    .pf-stat-icon { background:rgba(0,151,167,.1); color:#0097a7; }
        .pf-activa    .pf-stat-num  { color:#0097a7; }

        .pf-ganadora  { border-left-color:#f9a826; }
        .pf-ganadora  .pf-stat-icon { background:rgba(249,168,38,.12); color:#f9a826; }
        .pf-ganadora  .pf-stat-num  { color:#f9a826; }

        .pf-hist      { border-left-color:#6c5ce7; }
        .pf-hist      .pf-stat-icon { background:rgba(108,92,231,.10); color:#6c5ce7; }
        .pf-hist      .pf-stat-num  { color:#6c5ce7; }

        /* ── Section cards ── */
        .pf-section { background:#fff; border-radius:14px; border:1px solid #e8eaef; box-shadow:0 2px 12px rgba(0,0,0,.06); margin-bottom:20px; overflow:hidden; }
        .pf-section-hdr {
            display:flex; align-items:center; gap:10px;
            padding:14px 20px; cursor:pointer; user-select:none;
            border-bottom:1px solid #f0f2f5;
        }
        .pf-section-hdr .pf-hdr-title { font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; }
        .pf-section-hdr .pf-chevron { margin-left:auto; font-size:12px; color:#9ca3af; transition:transform .25s; }
        .pf-section-hdr.collapsed .pf-chevron { transform:rotate(-90deg); }
        .pf-section-body { padding:18px 20px; }
    </style>

    {{-- ── Mensaje de éxito ─────────────────────────────────────────────── --}}
    @if($mensajeExito)
    <div style="background:#e8f5e9; border:1px solid #a5d6a7; border-radius:10px; padding:12px 18px; margin-bottom:16px;
                color:#2e7d32; font-weight:600; font-size:13px; display:flex; align-items:center; gap:10px;">
        <i class="fa fa-check-circle fa-lg"></i> {{ $mensajeExito }}
    </div>
    @endif

    {{-- ── Stats cards ──────────────────────────────────────────────────── --}}
    <div class="pf-stat-grid">
        <div class="pf-stat-card pf-activa">
            <div class="pf-stat-icon"><i class="fa fa-clock-o"></i></div>
            <div>
                <div class="pf-stat-num">{{ count($ofertas) }}</div>
                <div class="pf-stat-lbl">Ofertas activas</div>
            </div>
        </div>
        <div class="pf-stat-card pf-ganadora">
            <div class="pf-stat-icon"><i class="fa fa-trophy"></i></div>
            <div>
                <div class="pf-stat-num">{{ count($ganadoras) }}</div>
                <div class="pf-stat-lbl">Listas para facturar</div>
            </div>
        </div>
        <div class="pf-stat-card pf-hist">
            <div class="pf-stat-icon"><i class="fa fa-history"></i></div>
            <div>
                <div class="pf-stat-num">{{ count($historial) }}</div>
                <div class="pf-stat-lbl">Historial (canceladas)</div>
            </div>
        </div>
    </div>

    {{-- ── Modal confirmación ──────────────────────────────────────────── --}}
    @if($confirmandoId)
    <div style="position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1050; display:flex; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:16px; padding:28px 32px; max-width:420px; width:90%; box-shadow:0 12px 40px rgba(0,0,0,.2);">
            <div style="text-align:center; margin-bottom:20px;">
                <div style="background:#fff8e1; width:70px; height:70px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
                    <i class="fa fa-check-circle" style="font-size:32px; color:#f9a826;"></i>
                </div>
                <h5 style="font-weight:800; color:#2c3e50; margin-bottom:8px;">Aprobar Oferta #{{ $confirmandoId }}</h5>
                <p style="color:#546e7a; font-size:13px; margin:0;">
                    ¿Confirmas que esta oferta es la <strong>ganadora</strong>? Se marcará como <strong>Prefactura</strong>
                    y estará disponible para convertirse en factura.
                </p>
            </div>
            <div class="d-flex gap-2 justify-content-center">
                <button type="button" wire:click="cancelarConfirmacion"
                        style="background:#f5f5f5; color:#546e7a; border:none; border-radius:8px; padding:10px 22px; font-weight:700; cursor:pointer;">
                    Cancelar
                </button>
                <button type="button" wire:click="aprobarOferta({{ $confirmandoId }})"
                        style="background:linear-gradient(135deg,#00695c,#00897b); color:#fff; border:none;
                               border-radius:8px; padding:10px 22px; font-weight:700; cursor:pointer;
                               box-shadow:0 2px 8px rgba(0,137,123,.3);">
                    <i class="fa fa-check mr-1"></i> Sí, Aprobar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- SECCIÓN 1: Ofertas activas (pendientes de aprobación)            --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="pf-section">
        <div class="pf-section-hdr" onclick="togglePfSection('body_activas', this)">
            <i class="fa fa-clock-o" style="color:#0097a7;"></i>
            <span class="pf-hdr-title" style="color:#0097a7;">Ofertas activas</span>
            <span style="background:#0097a7; color:#fff; border-radius:20px; padding:1px 10px; font-size:11px; font-weight:700;">{{ count($ofertas) }}</span>
            <div class="input-group ml-auto" style="max-width:280px;" onclick="event.stopPropagation()">
                <div class="input-group-prepend"><span class="input-group-text" style="background:#0097a7; color:#fff; border-color:#0097a7; font-size:12px;"><i class="fa fa-search"></i></span></div>
                <input type="text" wire:model.debounce.300ms="busqueda" class="form-control form-control-sm" placeholder="Buscar…">
            </div>
            <i class="fa fa-chevron-down pf-chevron"></i>
        </div>
        <div id="body_activas" class="pf-section-body">
            @if(count($ofertas) === 0)
            <div class="text-center py-4">
                <i class="fa fa-file-text-o fa-2x mb-2" style="color:#b2dfdb; display:block;"></i>
                <p style="color:#78909c; font-size:13px; margin:0;">No hay ofertas activas pendientes.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover" style="font-size:13px;">
                    <thead style="background:#e0f7fa;">
                        <tr>
                            <th># Oferta</th><th>Pedido</th><th>Cliente</th><th>RTN</th>
                            <th>Productos</th><th class="text-right">Total L.</th><th>Fecha</th><th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ofertas as $oferta)
                        @php $o = (array)$oferta; @endphp
                        <tr>
                            <td><span style="background:linear-gradient(135deg,#00838f,#0097a7); color:#fff; border-radius:6px; padding:3px 10px; font-weight:800;">#{{ $o['cotizacion_id'] }}</span></td>
                            <td>
                                @if($o['pedido_id'])
                                    <span style="background:#e3f2fd; color:#1565c0; border-radius:12px; padding:2px 8px; font-size:11px; font-weight:700;">Pedido #{{ $o['pedido_id'] }}</span>
                                @else
                                    <span style="color:#b0bec5; font-size:11px;">Sin pedido</span>
                                @endif
                            </td>
                            <td style="font-weight:600; color:#2c3e50;">{{ $o['nombre_cliente'] }}</td>
                            <td style="color:#546e7a;">{{ $o['RTN'] ?: '—' }}</td>
                            <td><span style="background:#e8eaf6; color:#3949ab; border-radius:20px; padding:2px 8px; font-size:11px; font-weight:700;">{{ $o['total_productos'] }}</span></td>
                            <td class="text-right" style="font-weight:700; color:#2e7d32;">L. {{ $o['total'] }}</td>
                            <td style="color:#78909c; font-size:11px;">{{ \Carbon\Carbon::parse($o['created_at'])->format('d/m/Y') }}</td>
                            <td style="white-space:nowrap;">
                                <button type="button" wire:click="confirmarAprobar({{ $o['hf_id'] }})"
                                        style="background:linear-gradient(135deg,#e65100,#f57f17); color:#fff; border:none; border-radius:8px; padding:5px 12px; font-size:12px; font-weight:700; cursor:pointer;">
                                    <i class="fa fa-check-circle mr-1"></i> Aprobar
                                </button>
                                <button type="button" wire:click="verFlujoPorCotizacion({{ $o['cotizacion_id'] }})"
                                        style="background:#e3f2fd; color:#1565c0; border:none; border-radius:8px; padding:5px 12px; font-size:12px; font-weight:700; cursor:pointer; margin-left:4px;">
                                    <i class="fa fa-sitemap mr-1"></i> Ver Flujo
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- SECCIÓN 2: Prefacturas listas para facturar (ganadoras)          --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="pf-section">
        <div class="pf-section-hdr" onclick="togglePfSection('body_ganadoras', this)">
            <i class="fa fa-trophy" style="color:#f9a826;"></i>
            <span class="pf-hdr-title" style="color:#d4860a;">Prefacturas listas para facturar</span>
            <span style="background:#f9a826; color:#fff; border-radius:20px; padding:1px 10px; font-size:11px; font-weight:700;">{{ count($ganadoras) }}</span>
            <div class="input-group ml-auto" style="max-width:280px;" onclick="event.stopPropagation()">
                <div class="input-group-prepend"><span class="input-group-text" style="background:#f9a826; color:#fff; border-color:#f9a826; font-size:12px;"><i class="fa fa-search"></i></span></div>
                <input type="text" wire:model.debounce.300ms="busquedaGanadora" class="form-control form-control-sm" placeholder="Buscar…">
            </div>
            <i class="fa fa-chevron-down pf-chevron"></i>
        </div>
        <div id="body_ganadoras" class="pf-section-body">
            @if(count($ganadoras) === 0)
            <div class="text-center py-4">
                <i class="fa fa-trophy fa-2x mb-2" style="color:#ffe082; display:block;"></i>
                <p style="color:#78909c; font-size:13px; margin:0;">No hay prefacturas ganadoras pendientes de facturar.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover" style="font-size:13px;">
                    <thead style="background:#fff8e1;">
                        <tr>
                            <th># Oferta</th><th>Pedido</th><th>Cliente</th><th>RTN</th>
                            <th>Productos</th><th class="text-right">Total L.</th><th>Fecha</th><th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ganadoras as $oferta)
                        @php $o = (array)$oferta; @endphp
                        <tr>
                            <td>
                                <span style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border-radius:6px; padding:3px 10px; font-weight:800;">#{{ $o['cotizacion_id'] }}</span>
                                <span style="background:#fff8e1; color:#f57f17; border-radius:20px; padding:1px 8px; font-size:10px; font-weight:700; margin-left:4px;"><i class="fa fa-trophy mr-1"></i>Prefactura</span>
                            </td>
                            <td>
                                @if($o['pedido_id'])
                                    <span style="background:#e3f2fd; color:#1565c0; border-radius:12px; padding:2px 8px; font-size:11px; font-weight:700;">Pedido #{{ $o['pedido_id'] }}</span>
                                @else
                                    <span style="color:#b0bec5; font-size:11px;">Sin pedido</span>
                                @endif
                            </td>
                            <td style="font-weight:600; color:#2c3e50;">{{ $o['nombre_cliente'] }}</td>
                            <td style="color:#546e7a;">{{ $o['RTN'] ?: '—' }}</td>
                            <td><span style="background:#e8eaf6; color:#3949ab; border-radius:20px; padding:2px 8px; font-size:11px; font-weight:700;">{{ $o['total_productos'] }}</span></td>
                            <td class="text-right" style="font-weight:700; color:#2e7d32;">L. {{ $o['total'] }}</td>
                            <td style="color:#78909c; font-size:11px;">{{ \Carbon\Carbon::parse($o['created_at'])->format('d/m/Y') }}</td>
                            <td style="white-space:nowrap;">
                                <button type="button" wire:click="verFlujoPorCotizacion({{ $o['cotizacion_id'] }})"
                                        style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none; border-radius:8px; padding:6px 14px; font-size:12px; font-weight:700; cursor:pointer;">
                                    <i class="fa fa-sitemap mr-1"></i> Abrir Flujo
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- SECCIÓN 3: Historial (canceladas / ya facturadas)                --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="pf-section">
        <div class="pf-section-hdr" onclick="togglePfSection('body_historial', this)">
            <i class="fa fa-history" style="color:#6c5ce7;"></i>
            <span class="pf-hdr-title" style="color:#6c5ce7;">Historial</span>
            <span style="background:#6c5ce7; color:#fff; border-radius:20px; padding:1px 10px; font-size:11px; font-weight:700;">{{ count($historial) }}</span>
            <div class="input-group ml-auto" style="max-width:280px;" onclick="event.stopPropagation()">
                <div class="input-group-prepend"><span class="input-group-text" style="background:#6c5ce7; color:#fff; border-color:#6c5ce7; font-size:12px;"><i class="fa fa-search"></i></span></div>
                <input type="text" wire:model.debounce.300ms="busquedaHist" class="form-control form-control-sm" placeholder="Buscar…">
            </div>
            <i class="fa fa-chevron-down pf-chevron"></i>
        </div>
        <div id="body_historial" class="pf-section-body">
            @if(count($historial) === 0)
            <div class="text-center py-4">
                <i class="fa fa-history fa-2x mb-2" style="color:#d1c4e9; display:block;"></i>
                <p style="color:#78909c; font-size:13px; margin:0;">No hay registros en el historial.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover" style="font-size:13px;">
                    <thead style="background:#ede7f6;">
                        <tr>
                            <th># Oferta</th><th>Estado</th><th>Pedido</th><th>Cliente</th><th>RTN</th>
                            <th>Productos</th><th class="text-right">Total L.</th><th>Fecha</th><th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historial as $oferta)
                        @php $o = (array)$oferta; @endphp
                        <tr>
                            <td><span style="background:linear-gradient(135deg,#5e35b1,#7e57c2); color:#fff; border-radius:6px; padding:3px 10px; font-weight:800;">#{{ $o['cotizacion_id'] }}</span></td>
                            <td>
                                @php
                                    $est = $o['estado_oferta'] ?? '';
                                    if (str_starts_with($est, 'Anulado')) {
                                        $ec = ['#fce4ec','#b71c1c'];
                                    } elseif (str_starts_with($est, 'QuitadaGanadora')) {
                                        $ec = ['#fff3e0','#e65100'];
                                    } else {
                                        $ec = ['#f5f5f5','#546e7a'];
                                    }
                                @endphp
                                <span style="background:{{ $ec[0] }}; color:{{ $ec[1] }}; border-radius:20px; padding:2px 10px; font-size:11px; font-weight:700;" title="{{ $est }}">{{ str_starts_with($est,'Anulado') ? 'Anulada' : 'Quitada Ganadora' }}</span>
                            </td>
                            <td>
                                @if($o['pedido_id'])
                                    <span style="background:#e3f2fd; color:#1565c0; border-radius:12px; padding:2px 8px; font-size:11px; font-weight:700;">Pedido #{{ $o['pedido_id'] }}</span>
                                @else
                                    <span style="color:#b0bec5; font-size:11px;">Sin pedido</span>
                                @endif
                            </td>
                            <td style="font-weight:600; color:#2c3e50;">{{ $o['nombre_cliente'] }}</td>
                            <td style="color:#546e7a;">{{ $o['RTN'] ?: '—' }}</td>
                            <td><span style="background:#e8eaf6; color:#3949ab; border-radius:20px; padding:2px 8px; font-size:11px; font-weight:700;">{{ $o['total_productos'] }}</span></td>
                            <td class="text-right" style="font-weight:700; color:#2e7d32;">L. {{ $o['total'] }}</td>
                            <td style="color:#78909c; font-size:11px;">{{ \Carbon\Carbon::parse($o['created_at'])->format('d/m/Y') }}</td>
                            <td>
                                <button type="button" wire:click="verFlujoPorCotizacion({{ $o['cotizacion_id'] }})"
                                        style="background:#ede7f6; color:#5e35b1; border:none; border-radius:8px; padding:5px 12px; font-size:12px; font-weight:700; cursor:pointer;">
                                    <i class="fa fa-sitemap mr-1"></i> Ver Flujo
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- Modal flujo del pedido (reutilizable) --}}
    @livewire('flujo.modal-flujo-pedido')

    <div wire:loading class="text-center py-3">
        <i class="fa fa-spinner fa-spin" style="color:#0097a7; font-size:20px;"></i>
    </div>

    <script>
    function togglePfSection(bodyId, hdr) {
        var body = document.getElementById(bodyId);
        if (!body) return;
        var open = body.style.display !== 'none';
        body.style.display = open ? 'none' : '';
        hdr.classList.toggle('collapsed', open);
    }
    </script>
</div>
