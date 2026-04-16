<div>
    {{-- ── Filtro ───────────────────────────────────────────────────────── --}}
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <div class="input-group" style="max-width:360px;">
            <div class="input-group-prepend">
                <span class="input-group-text" style="background:#f9a826; color:#fff; border-color:#f9a826; border-radius:8px 0 0 8px;">
                    <i class="fa fa-search"></i>
                </span>
            </div>
            <input type="text" wire:model.debounce.300ms="busqueda"
                   class="form-control" placeholder="Buscar por cliente, RTN o # prefactura…"
                   style="border-radius:0 8px 8px 0;">
        </div>
    </div>

    <div class="mb-2" style="font-size:12px; color:#78909c;">
        <i class="fa fa-list mr-1"></i> {{ count($prefacturas) }} prefactura(s) pendiente(s)
    </div>

    {{-- ── Modal selector tipo factura ────────────────────────────────── --}}
    @if($ofertaParaFacturar)
    <div style="position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1060; display:flex; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:18px; padding:28px 32px; max-width:520px; width:94%; box-shadow:0 16px 50px rgba(0,0,0,.22);">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h5 style="font-weight:800; color:#2c3e50; margin:0;">
                    <i class="fa fa-file-invoice mr-2" style="color:#f9a826;"></i>
                    Seleccionar Tipo de Factura
                </h5>
                <button type="button" wire:click="cancelarSeleccion"
                        style="background:none; border:none; font-size:20px; cursor:pointer; color:#78909c;">×</button>
            </div>
            <p style="color:#546e7a; font-size:13px; margin-bottom:20px;">
                Prefactura <strong>#{{ $ofertaParaFacturar }}</strong> → elige el tipo de factura a emitir:
            </p>
            <div class="row g-2">
                @foreach([
                    ['clientes_a',    'fa-user-tie',      '#1199c1', '#e3f2fd', 'Clientes A',     'Facturación estándar Tipo A'],
                    ['clientes_b',    'fa-users',         '#3a53a3', '#e8eaf6', 'Clientes B',     'Facturación estándar Tipo B'],
                    ['sr_clientes_a', 'fa-shield-alt',    '#d32f2f', '#fce4ec', 'SR / Clientes A','Sin restricción – Tipo A'],
                    ['sr_clientes_b', 'fa-lock-open',     '#424242', '#f5f5f5', 'SR / Clientes B','Sin restricción – Tipo B'],
                    ['exonerada',     'fa-file-invoice',  '#00897b', '#e8f5e9', 'Exonerada',      'Para clientes exonerados'],
                ] as [$subtipo, $icon, $color, $bg, $label, $desc])
                <div class="col-6 mb-2">
                    <button type="button" wire:click="crearFactura('{{ $subtipo }}')"
                            style="background:{{ $bg }}; color:{{ $color }}; border:2px solid {{ $color }}20;
                                   border-radius:12px; padding:12px 14px; width:100%; text-align:left;
                                   cursor:pointer; transition:box-shadow .15s;"
                            onmouseover="this.style.boxShadow='0 4px 16px {{ $color }}30';"
                            onmouseout="this.style.boxShadow='none';">
                        <div style="font-size:18px; margin-bottom:4px;"><i class="fa {{ $icon }}"></i></div>
                        <div style="font-weight:800; font-size:13px;">{{ $label }}</div>
                        <div style="font-size:11px; opacity:.8;">{{ $desc }}</div>
                    </button>
                </div>
                @endforeach
            </div>
            <div class="text-right mt-3">
                <button type="button" wire:click="cancelarSeleccion"
                        style="background:#f5f5f5; color:#546e7a; border:none; border-radius:8px;
                               padding:9px 20px; font-weight:700; cursor:pointer;">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Tabla ───────────────────────────────────────────────────────── --}}
    @if(count($prefacturas) === 0)
    <div class="text-center py-5">
        <i class="fa fa-file-text fa-3x mb-3" style="color:#ffe082; display:block;"></i>
        <p style="color:#78909c; font-size:14px;">No hay prefacturas (ofertas ganadoras) pendientes de facturar.</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-hover" style="font-size:13px;">
            <thead style="background:#fff8e1;">
                <tr>
                    <th># Prefactura</th>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>RTN</th>
                    <th>Vendedor</th>
                    <th>Productos</th>
                    <th class="text-right">Total L.</th>
                    <th>Fecha</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prefacturas as $pref)
                @php $p = (array)$pref; @endphp
                <tr>
                    <td>
                        <span style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff;
                                     border-radius:6px; padding:3px 10px; font-weight:800;">#{{ $p['id'] }}</span>
                    </td>
                    <td>
                        @if($p['pedido_id'])
                            <span style="background:#e3f2fd; color:#1565c0; border-radius:12px; padding:2px 8px; font-size:11px; font-weight:700;">
                                Pedido #{{ $p['pedido_id'] }}
                            </span>
                        @else
                            <span style="color:#b0bec5; font-size:11px;">—</span>
                        @endif
                    </td>
                    <td style="font-weight:600; color:#2c3e50;">{{ $p['nombre_cliente'] }}</td>
                    <td style="color:#546e7a;">{{ $p['RTN'] ?: '—' }}</td>
                    <td style="color:#546e7a; font-size:12px;">{{ $p['vendedor_nombre'] ?: '—' }}</td>
                    <td>
                        <span style="background:#e8eaf6; color:#3949ab; border-radius:20px; padding:2px 8px; font-size:11px; font-weight:700;">
                            {{ $p['total_productos'] }}
                        </span>
                    </td>
                    <td class="text-right" style="font-weight:700; color:#e65100;">L. {{ $p['total'] }}</td>
                    <td style="color:#78909c; font-size:11px;">
                        {{ \Carbon\Carbon::parse($p['created_at'])->format('d/m/Y') }}
                    </td>
                    <td>
                        <button type="button" wire:click="seleccionarParaFacturar({{ $p['id'] }})"
                                style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none;
                                       border-radius:8px; padding:7px 14px; font-size:12px; font-weight:700;
                                       cursor:pointer; white-space:nowrap; box-shadow:0 2px 8px rgba(249,168,38,.3);">
                            <i class="fa fa-file-invoice mr-1"></i> Crear Factura
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div wire:loading class="text-center py-3">
        <i class="fa fa-spinner fa-spin" style="color:#f9a826; font-size:20px;"></i>
    </div>
</div>
