<div>
    {{-- ── Mensaje de éxito ─────────────────────────────────────────────── --}}
    @if($mensajeExito)
    <div style="background:#e8f5e9; border:1px solid #a5d6a7; border-radius:10px; padding:12px 18px; margin-bottom:16px;
                color:#2e7d32; font-weight:600; font-size:13px; display:flex; align-items:center; gap:10px;">
        <i class="fa fa-check-circle fa-lg"></i> {{ $mensajeExito }}
    </div>
    @endif

    {{-- ── Filtros ─────────────────────────────────────────────────────── --}}
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <div class="input-group" style="max-width:360px;">
            <div class="input-group-prepend">
                <span class="input-group-text" style="background:#0097a7; color:#fff; border-color:#0097a7; border-radius:8px 0 0 8px;">
                    <i class="fa fa-search"></i>
                </span>
            </div>
            <input type="text" wire:model.debounce.300ms="busqueda"
                   class="form-control" placeholder="Buscar por cliente, RTN o # oferta…"
                   style="border-radius:0 8px 8px 0;">
        </div>

        <button type="button" wire:click="verDetallePrefactura"
                style="background:linear-gradient(135deg,#00838f,#0097a7); color:#fff; border:none;
                       border-radius:8px; padding:8px 18px; font-size:13px; font-weight:700;
                       cursor:pointer; box-shadow:0 2px 8px rgba(0,151,167,.3);">
            <i class="fa fa-search mr-1"></i> Ver Prefacturas Aprobadas
        </button>
    </div>

    <div class="mb-2" style="font-size:12px; color:#78909c;">
        <i class="fa fa-list mr-1"></i> {{ count($ofertas) }} oferta(s) activa(s)
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

    {{-- ── Tabla ───────────────────────────────────────────────────────── --}}
    @if(count($ofertas) === 0)
    <div class="text-center py-5">
        <i class="fa fa-file-text-o fa-3x mb-3" style="color:#b2dfdb; display:block;"></i>
        <p style="color:#78909c; font-size:14px;">No hay ofertas activas pendientes de aprobación.</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-hover" style="font-size:13px;">
            <thead style="background:#e0f7fa;">
                <tr>
                    <th># Oferta</th>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>RTN</th>
                    <th>Productos</th>
                    <th class="text-right">Total L.</th>
                    <th>Fecha</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ofertas as $oferta)
                @php $o = (array)$oferta; @endphp
                <tr>
                    <td>
                        <span style="background:linear-gradient(135deg,#00838f,#0097a7); color:#fff;
                                     border-radius:6px; padding:3px 10px; font-weight:800;">#{{ $o['id'] }}</span>
                    </td>
                    <td>
                        @if($o['pedido_id'])
                            <span style="background:#e3f2fd; color:#1565c0; border-radius:12px; padding:2px 8px; font-size:11px; font-weight:700;">
                                Pedido #{{ $o['pedido_id'] }}
                            </span>
                        @else
                            <span style="color:#b0bec5; font-size:11px;">Sin pedido</span>
                        @endif
                    </td>
                    <td style="font-weight:600; color:#2c3e50;">{{ $o['nombre_cliente'] }}</td>
                    <td style="color:#546e7a;">{{ $o['RTN'] ?: '—' }}</td>
                    <td>
                        <span style="background:#e8eaf6; color:#3949ab; border-radius:20px; padding:2px 8px; font-size:11px; font-weight:700;">
                            {{ $o['total_productos'] }}
                        </span>
                    </td>
                    <td class="text-right" style="font-weight:700; color:#2e7d32;">L. {{ $o['total'] }}</td>
                    <td style="color:#78909c; font-size:11px;">
                        {{ \Carbon\Carbon::parse($o['created_at'])->format('d/m/Y') }}
                    </td>
                    <td>
                        <button type="button" wire:click="confirmarAprobar({{ $o['id'] }})"
                                style="background:linear-gradient(135deg,#e65100,#f57f17); color:#fff; border:none;
                                       border-radius:8px; padding:7px 14px; font-size:12px; font-weight:700;
                                       cursor:pointer; white-space:nowrap;">
                            <i class="fa fa-check-circle mr-1"></i> Aprobar
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div wire:loading class="text-center py-3">
        <i class="fa fa-spinner fa-spin" style="color:#0097a7; font-size:20px;"></i>
    </div>
</div>
