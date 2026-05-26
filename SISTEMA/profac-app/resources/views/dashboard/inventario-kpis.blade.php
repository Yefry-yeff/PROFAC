{{-- ═══════════════════════════════════════════════════════════════
     PARTIAL — KPIs DE INVENTARIO
     Incluido en: mercadeo, auditoria, aux-admin
     Variable requerida: $kpisInventario
═══════════════════════════════════════════════════════════════ --}}

{{-- ── KPIs Inventario ── --}}
<div class="dash-kpi-grid">
    <div class="dash-kpi blue">
        <div class="dash-kpi-icon"><i class="fa fa-cubes"></i></div>
        <div class="dash-kpi-label">Productos activos</div>
        <div class="dash-kpi-value">{{ number_format($kpisInventario['total_productos'] ?? 0) }}</div>
        <div class="dash-kpi-sub">en catálogo</div>
    </div>
    <div class="dash-kpi red">
        <div class="dash-kpi-icon"><i class="fa fa-exclamation-triangle"></i></div>
        <div class="dash-kpi-label">Bajos en stock</div>
        <div class="dash-kpi-value">{{ number_format($kpisInventario['bajos_stock'] ?? 0) }}</div>
        <div class="dash-kpi-sub">menos de 10 unidades</div>
    </div>
    <div class="dash-kpi orange">
        <div class="dash-kpi-icon"><i class="fa fa-picture-o"></i></div>
        <div class="dash-kpi-label">Sin imagen</div>
        <div class="dash-kpi-value">{{ number_format($kpisInventario['sin_imagen'] ?? 0) }}</div>
        <div class="dash-kpi-sub">productos sin foto</div>
    </div>
    <div class="dash-kpi">
        <div class="dash-kpi-icon"><i class="fa fa-pause-circle-o"></i></div>
        <div class="dash-kpi-label">Sin movimiento</div>
        <div class="dash-kpi-value">{{ number_format($kpisInventario['sin_movimiento_90'] ?? 0) }}</div>
        <div class="dash-kpi-sub">últimos 90 días</div>
    </div>
</div>

{{-- ── Top productos bajos en stock ── --}}
<div class="dash-card">
    <div class="dash-card-header red">
        <h5><i class="fa fa-warning"></i> Productos Bajos en Stock — Top 15</h5>
    </div>
    <div class="dash-card-body" style="padding:0;">
        <div style="overflow-x:auto; max-height:360px; overflow-y:auto;">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th style="text-align:center;">Stock actual</th>
                        <th style="text-align:center;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kpisInventario['top_bajos_stock'] ?? [] as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $p['nombre'] }}</td>
                        <td style="text-align:center; font-weight:600;">
                            {{ $p['stock'] }}
                        </td>
                        <td style="text-align:center;">
                            @if($p['stock'] === 0)
                                <span style="color:#e74c3c; font-weight:600;">Sin stock</span>
                            @else
                                <span style="color:#f39c12; font-weight:600;">Crítico</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center; color:var(--dash-muted); padding:24px;">Sin productos bajos en stock</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
