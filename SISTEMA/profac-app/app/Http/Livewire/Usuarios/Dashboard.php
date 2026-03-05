<?php

namespace App\Http\Livewire\Usuarios;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\usuario;

class Dashboard extends Component
{
    /** Array widget_key => visible (bool), loaded from DB or defaults */
    public $widgetPrefs = [];

    // ─── Widget Catalogue ───────────────────────────────────────────────────

    /**
     * Define every available widget.
     * 'roles' => []   means all authenticated users can see it.
     * 'roles' => ['Administrador'] restricts to that role name.
     */
    public function getWidgetConfig(): array
    {
        return [
            'usuarios_activos' => [
                'title'  => 'Usuarios Activos',
                'icon'   => 'fa-users',
                'color'  => '#1ab394',
                'roles'  => [],                               // all roles
            ],
            'ventas_mes' => [
                'title'  => 'Ventas del Mes',
                'icon'   => 'fa-shopping-cart',
                'color'  => '#1c84c6',
                'roles'  => [
                    'Administrador', 'Asesor Comercial', 'Televendedor',
                    'Auxiliar Administrativo', 'Auxiliar Contable',
                    'Créditos y Cobros', 'Auditoria y Logistica',
                ],
            ],
            'mejor_vendedor' => [
                'title'  => 'Mejor Vendedor (mes)',
                'icon'   => 'fa-trophy',
                'color'  => '#f8ac59',
                'roles'  => ['Administrador', 'Asesor Comercial', 'Auxiliar Administrativo', 'Auxiliar Contable'],
            ],
            'mejor_cliente' => [
                'title'  => 'Cliente Top (mes)',
                'icon'   => 'fa-star',
                'color'  => '#ed5565',
                'roles'  => [
                    'Administrador', 'Asesor Comercial', 'Auxiliar Administrativo',
                    'Créditos y Cobros', 'Auxiliar Contable',
                ],
            ],
            'ultimas_ventas' => [
                'title'  => 'Últimas Ventas',
                'icon'   => 'fa-list-alt',
                'color'  => '#23c6c8',
                'roles'  => [
                    'Administrador', 'Asesor Comercial', 'Televendedor',
                    'Auxiliar Administrativo', 'Auxiliar Contable', 'Créditos y Cobros',
                ],
            ],
            'grafico_ventas' => [
                'title'  => 'Gráfico Ventas (6 meses)',
                'icon'   => 'fa-line-chart',
                'color'  => '#6f42c1',
                'roles'  => ['Administrador', 'Asesor Comercial', 'Auxiliar Administrativo', 'Auxiliar Contable'],
            ],
            'usuarios_roles' => [
                'title'  => 'Usuarios y Roles',
                'icon'   => 'fa-id-card',
                'color'  => '#2f4050',
                'roles'  => ['Administrador', 'Recursos Humanos'],
            ],
        ];
    }

    // ─── Lifecycle ───────────────────────────────────────────────────────────

    public function mount(): void
    {
        $userId = Auth::id();
        $saved  = DB::table('dashboard_widget_preferences')
            ->where('user_id', $userId)
            ->pluck('visible', 'widget_key')
            ->toArray();

        foreach ($this->getWidgetConfig() as $key => $cfg) {
            $this->widgetPrefs[$key] = isset($saved[$key]) ? (bool) $saved[$key] : true;
        }
    }

    // ─── Actions ─────────────────────────────────────────────────────────────

    public function toggleWidget(string $key): void
    {
        if (!array_key_exists($key, $this->widgetPrefs)) return;

        $this->widgetPrefs[$key] = !$this->widgetPrefs[$key];

        DB::table('dashboard_widget_preferences')->updateOrInsert(
            ['user_id' => Auth::id(), 'widget_key' => $key],
            ['visible' => $this->widgetPrefs[$key], 'created_at' => now(), 'updated_at' => now()]
        );
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function canSeeWidget(string $key): bool
    {
        $config = $this->getWidgetConfig();
        if (!isset($config[$key])) return false;

        $roles = $config[$key]['roles'];
        if (empty($roles)) return true;

        $userRolNombre = optional(Auth::user()->rol)->nombre ?? '';
        return in_array($userRolNombre, $roles);
    }

    public function isVisible(string $key): bool
    {
        return $this->canSeeWidget($key) && ($this->widgetPrefs[$key] ?? true);
    }

    // ─── Render ──────────────────────────────────────────────────────────────

    public function render()
    {
        $data   = [];
        $mes    = now()->month;
        $anio   = now()->year;
        $config = $this->getWidgetConfig();

        // --- Usuarios activos ---
        if ($this->canSeeWidget('usuarios_activos')) {
            $data['totalUsuariosActivos'] = DB::table('users')->where('estado_id', 1)->count();
            $data['totalUsuarios']        = DB::table('users')->count();
        }

        // --- Ventas del mes ---
        if ($this->canSeeWidget('ventas_mes')) {
            $base = DB::table('factura')
                ->where('estado_factura_id', 1)
                ->whereMonth('fecha_emision', $mes)
                ->whereYear('fecha_emision', $anio);

            $data['ventasMesTotal'] = (clone $base)->sum('total');
            $data['ventasMesCount'] = (clone $base)->count();
        }

        // --- Mejor vendedor del mes ---
        if ($this->canSeeWidget('mejor_vendedor')) {
            $mv = DB::table('factura')
                ->select('vendedor', DB::raw('COUNT(*) as cnt'), DB::raw('SUM(total) as monto'))
                ->where('estado_factura_id', 1)
                ->whereMonth('fecha_emision', $mes)
                ->whereYear('fecha_emision', $anio)
                ->groupBy('vendedor')
                ->orderByDesc('cnt')
                ->first();

            if ($mv) {
                $u = DB::table('users')->select('name')->find($mv->vendedor);
                $mv->nombre_vendedor = $u ? $u->name : ('ID #' . $mv->vendedor);
            }
            $data['mejorVendedor'] = $mv;
        }

        // --- Mejor cliente del mes ---
        if ($this->canSeeWidget('mejor_cliente')) {
            $data['mejorCliente'] = DB::table('factura')
                ->select('cliente_id', 'nombre_cliente', DB::raw('COUNT(*) as cnt'), DB::raw('SUM(total) as monto'))
                ->where('estado_factura_id', 1)
                ->whereMonth('fecha_emision', $mes)
                ->whereYear('fecha_emision', $anio)
                ->groupBy('cliente_id', 'nombre_cliente')
                ->orderByDesc('monto')
                ->first();
        }

        // --- Últimas ventas ---
        if ($this->canSeeWidget('ultimas_ventas')) {
            $data['ultimasVentas'] = DB::table('factura')
                ->select('id', 'numero_factura', 'nombre_cliente', 'total', 'fecha_emision', 'vendedor')
                ->where('estado_factura_id', 1)
                ->orderByDesc('fecha_emision')
                ->limit(10)
                ->get();
        }

        // --- Gráfico ventas 6 meses ---
        if ($this->canSeeWidget('grafico_ventas')) {
            $rows = DB::table('factura')
                ->select(
                    DB::raw('YEAR(fecha_emision) as anio'),
                    DB::raw('MONTH(fecha_emision) as mes'),
                    DB::raw('SUM(total) as total_ventas'),
                    DB::raw('COUNT(*) as num_facturas')
                )
                ->where('estado_factura_id', 1)
                ->where('fecha_emision', '>=', now()->subMonths(5)->startOfMonth())
                ->groupBy(DB::raw('YEAR(fecha_emision)'), DB::raw('MONTH(fecha_emision)'))
                ->orderBy('anio')->orderBy('mes')
                ->get();

            $meses_es = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            $data['graficoCategorias'] = $rows->map(fn($r) => $meses_es[$r->mes] . ' ' . substr($r->anio, -2))->values()->toArray();
            $data['graficoTotales']    = $rows->map(fn($r) => round($r->total_ventas, 2))->values()->toArray();
            $data['graficoFacturas']   = $rows->map(fn($r) => (int) $r->num_facturas)->values()->toArray();
        }

        // --- Usuarios y roles ---
        if ($this->canSeeWidget('usuarios_roles')) {
            $data['usuariosRoles'] = DB::table('users')
                ->leftJoin('rol', 'users.rol_id', '=', 'rol.id')
                ->select('users.id', 'users.name', 'users.email', 'rol.nombre as rol_nombre', 'users.estado_id', 'users.created_at')
                ->orderBy('users.name')
                ->get();
        }

        return view('livewire.usuarios.dashboard', array_merge($data, [
            'widgetConfig' => $config,
        ]));
    }
}
