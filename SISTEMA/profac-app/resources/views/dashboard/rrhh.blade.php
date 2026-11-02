{{-- ═══════════════════════════════════════════════════════════════
     DASHBOARD — RRHH
     Rol: 8 (RRHH)
     Enfoque: Productividad del equipo de ventas
═══════════════════════════════════════════════════════════════ --}}

{{-- ── KPIs generales del equipo ── --}}
<div class="dash-kpi-grid">
    @include('dashboard.kpi', ['etiqueta' => 'Facturas del equipo', 'icono' => 'fa-file-invoice', 'valor' => number_format($kpis['num_facturas'] ?? 0), 'ayuda' => 'Emitidas en el período', 'accion' => 'facturas', 'color' => '', 'enlace' => null])
    <div class="dash-kpi blue">
        <div class="dash-kpi-icon"><i class="fa fa-users"></i></div>
        <div class="dash-kpi-label">Vendedores activos</div>
        <div class="dash-kpi-value">{{ count($productividad) }}</div>
        <div class="dash-kpi-sub">con ventas en el período</div>
    </div>
    @include('dashboard.kpi', ['etiqueta' => 'Total facturas', 'icono' => 'fa-file-invoice', 'valor' => number_format($kpis['num_facturas'] ?? 0), 'ayuda' => 'Emitidas en el período', 'accion' => 'facturas', 'color' => 'green', 'enlace' => null])

    @include('dashboard.kpi-poco-movimiento')
</div>

{{-- ── Productividad por vendedor ── --}}
<div class="dash-card" style="margin-bottom:20px;">
    <div class="dash-card-header blue">
        <h5><i class="fa fa-bar-chart"></i> Productividad del Equipo — {{ $periodoLabel }}</h5>
    </div>
    <div class="dash-card-body">
        <div id="chart-rrhh-productividad" wire:ignore style="min-height:280px; margin-bottom:20px;"></div>
        <div style="overflow-x:auto;">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Vendedor</th>
                        <th>Venta Total</th>
                        <th>Facturas</th>
                        <th>Clientes</th>
                        <th>Ticket Prom.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productividad as $i => $p)
                    <tr>
                        <td><strong>{{ $i+1 }}</strong></td>
                        <td>{{ $p['vendedor'] }}</td>
                        <td>L. {{ number_format($p['venta_total'],2) }}</td>
                        <td>{{ number_format($p['num_facturas']) }}</td>
                        <td>{{ number_format($p['clientes_atendidos']) }}</td>
                        <td>L. {{ $p['num_facturas'] > 0 ? number_format($p['venta_total']/$p['num_facturas'],2) : '0.00' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center; color:var(--dash-muted); padding:24px;">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
