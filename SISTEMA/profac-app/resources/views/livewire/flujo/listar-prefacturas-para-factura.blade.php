<div>
    <style>
        .pf-th {
            white-space: nowrap;
            background: #fff3e0;
            font-size: 12px;
            font-weight: 700;
            color: #e65100;
            padding: 10px 12px;
            border-bottom: 2px solid #ffcc80 !important;
        }
        .pf-badge {
            display: inline-block;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }
        .pf-row {
            transition: background .1s;
        }
        .pf-row:hover > td {
            background: #fffaf2 !important;
        }
    </style>

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
                <div class="col-6 mb-2">
                    <button type="button" wire:click="crearFactura('clientes_a')"
                            style="background:#e3f2fd; color:#1199c1; border:2px solid #1199c1; border-radius:12px; padding:12px 14px; width:100%; text-align:left; cursor:pointer; transition:box-shadow .15s;"
                        onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.12)';"
                            onmouseout="this.style.boxShadow='none';">
                        <div style="font-size:18px; margin-bottom:4px;"><i class="fa fa-user-tie"></i></div>
                        <div style="font-weight:800; font-size:13px;">Clientes A</div>
                        <div style="font-size:11px; opacity:.8;">Facturación estándar Tipo A</div>
                    </button>
                </div>
                <div class="col-6 mb-2">
                    <button type="button" wire:click="crearFactura('clientes_b')"
                            style="background:#e8eaf6; color:#3a53a3; border:2px solid #3a53a3; border-radius:12px; padding:12px 14px; width:100%; text-align:left; cursor:pointer; transition:box-shadow .15s;"
                            onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.12)';"
                            onmouseout="this.style.boxShadow='none';">
                        <div style="font-size:18px; margin-bottom:4px;"><i class="fa fa-users"></i></div>
                        <div style="font-weight:800; font-size:13px;">Clientes B</div>
                        <div style="font-size:11px; opacity:.8;">Facturación estándar Tipo B</div>
                    </button>
                </div>
                <div class="col-6 mb-2">
                    <button type="button" wire:click="crearFactura('sr_clientes_a')"
                            style="background:#fce4ec; color:#d32f2f; border:2px solid #d32f2f; border-radius:12px; padding:12px 14px; width:100%; text-align:left; cursor:pointer; transition:box-shadow .15s;"
                            onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.12)';"
                            onmouseout="this.style.boxShadow='none';">
                        <div style="font-size:18px; margin-bottom:4px;"><i class="fa fa-shield-alt"></i></div>
                        <div style="font-weight:800; font-size:13px;">SR / Clientes A</div>
                        <div style="font-size:11px; opacity:.8;">Sin restricción – Tipo A</div>
                    </button>
                </div>
                <div class="col-6 mb-2">
                    <button type="button" wire:click="crearFactura('sr_clientes_b')"
                            style="background:#f5f5f5; color:#424242; border:2px solid #424242; border-radius:12px; padding:12px 14px; width:100%; text-align:left; cursor:pointer; transition:box-shadow .15s;"
                            onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.12)';"
                            onmouseout="this.style.boxShadow='none';">
                        <div style="font-size:18px; margin-bottom:4px;"><i class="fa fa-lock-open"></i></div>
                        <div style="font-weight:800; font-size:13px;">SR / Clientes B</div>
                        <div style="font-size:11px; opacity:.8;">Sin restricción – Tipo B</div>
                    </button>
                </div>
                <div class="col-6 mb-2">
                    <button type="button" wire:click="crearFactura('exonerada')"
                            style="background:#e8f5e9; color:#00897b; border:2px solid #00897b; border-radius:12px; padding:12px 14px; width:100%; text-align:left; cursor:pointer; transition:box-shadow .15s;"
                            onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.12)';"
                            onmouseout="this.style.boxShadow='none';">
                        <div style="font-size:18px; margin-bottom:4px;"><i class="fa fa-file-invoice"></i></div>
                        <div style="font-weight:800; font-size:13px;">Exonerada</div>
                        <div style="font-size:11px; opacity:.8;">Para clientes exonerados</div>
                    </button>
                </div>
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
        <table class="table table-bordered mb-0" style="font-size:13px; border-color:#dee2e6;">
            <thead>
                <tr>
                    <th class="pf-th" style="width:170px;">Identificadores</th>
                    <th class="pf-th">Cliente</th>
                    <th class="pf-th" style="width:110px; text-align:center;">Fecha</th>
                    <th class="pf-th" style="width:140px; text-align:center;">Estado</th>
                    <th class="pf-th" style="width:140px; text-align:center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prefacturas as $pref)
                @php $p = (array)$pref; @endphp
                <tr class="pf-row">
                    <td class="align-middle" style="padding:10px 12px;">
                        <span style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff;
                                     border-radius:6px; padding:3px 10px; font-weight:800;">#{{ $p['id'] }}</span>
                        <div style="margin-top:6px; display:flex; flex-wrap:wrap; gap:6px;">
                            @if($p['pedido_id'])
                                <span class="pf-badge" style="background:#e3f2fd; color:#1565c0;">
                                    Pedido #{{ $p['pedido_id'] }}
                                </span>
                            @endif
                            <span class="pf-badge" style="background:#ede7f6; color:#5e35b1;">
                                {{ $p['total_productos'] }} producto(s)
                            </span>
                        </div>
                    </td>
                    <td class="align-middle" style="padding:10px 12px;">
                        <div style="font-weight:700; color:#2c3e50; line-height:1.3;">{{ $p['nombre_cliente'] }}</div>
                        <div style="font-size:11px; color:#78909c; margin-top:3px; display:flex; flex-wrap:wrap; gap:10px;">
                            <span><i class="fa fa-id-card-o mr-1"></i>{{ $p['RTN'] ?: 'Sin RTN' }}</span>
                            <span><i class="fa fa-user-circle-o mr-1"></i>{{ $p['vendedor_nombre'] ?: 'Sin vendedor' }}</span>
                            <span style="font-weight:700; color:#e65100;"><i class="fa fa-money mr-1"></i>L. {{ $p['total'] }}</span>
                        </div>
                    </td>
                    <td class="text-center align-middle" style="padding:10px 8px; color:#78909c; font-size:11px; white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($p['created_at'])->format('d/m/Y') }}
                        <div style="font-size:10px; color:#b0bec5;">
                            {{ \Carbon\Carbon::parse($p['created_at'])->format('H:i') }}
                        </div>
                    </td>
                    <td class="text-center align-middle" style="padding:10px 8px;">
                        <span class="pf-badge" style="background:#e0f7fa; color:#006064; border:1px solid #00606422;">
                            <i class="fa fa-file-o mr-1"></i>Prefactura
                        </span>
                    </td>
                    <td class="text-center align-middle" style="padding:10px 8px;">
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
