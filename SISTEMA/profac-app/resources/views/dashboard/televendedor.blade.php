{{-- ═══════════════════════════════════════════════════════════════
     DASHBOARD — TELEVENDEDOR
     Roles: 3 (Televendedor), 14 (Tele-Asesor)
     Igual que vendedor pero con foco en cotizaciones/llamadas
═══════════════════════════════════════════════════════════════ --}}
@include('dashboard.vendedor', [
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
