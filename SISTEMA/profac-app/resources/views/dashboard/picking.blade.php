{{-- ═══════════════════════════════════════════════════════════════
     DASHBOARD — PICKING
     Rol: 10 (Picking / Bodega)
═══════════════════════════════════════════════════════════════ --}}
@include('dashboard.logistica', [
    'kpis'        => $kpis,
    'topClientes' => $topClientes,
    'tendencia'   => $tendencia,
    'productividad'=> $productividad,
    'participacion'=> $participacion,
    'comparativa' => $comparativa,
    'kpisCobros'  => $kpisCobros,
    'evolucion'   => $evolucion,
    'periodoLabel'=> $periodoLabel,
])
