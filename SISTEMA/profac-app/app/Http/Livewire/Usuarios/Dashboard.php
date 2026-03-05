<?php

namespace App\Http\Livewire\Usuarios;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    /** widget_key => visible (bool), loaded from DB prefs */
    public $widgetPrefs = [];

    /** Whether to show the config panel (hidden when embedded in main dashboard) */
    public $showConfigPanel = true;

    /** widget_key => row from dashboard_widgets (as array) */
    protected $widgetCatalog = null;

    // ─── Catalog: available widget type labels (used by GestionarWidgets) ──
    public static function widgetTypes(): array
    {
        return [
            'stat_usuarios_activos' => 'Usuarios Activos (contador)',
            'stat_ventas_mes'       => 'Ventas del Mes (contador)',
            'stat_mejor_vendedor'   => 'Mejor Vendedor del Mes',
            'stat_mejor_cliente'    => 'Cliente Top del Mes',
            'tabla_ultimas_ventas'  => 'Últimas Ventas (tabla)',
            'grafico_ventas_6m'     => 'Gráfico Ventas 6 Meses',
            'tabla_usuarios_roles'  => 'Usuarios y Roles (tabla)',
            'tabla_stock_bajo'      => 'Productos con Stock Bajo (tabla)',
        ];
    }

    // ─── Column size per widget key (Bootstrap grid) ─────────────────────────
    public static function widgetColClass(): array
    {
        return [
            'usuarios_activos' => 'col-lg-3 col-md-6',
            'ventas_mes'       => 'col-lg-6 col-md-12',
            'mejor_vendedor'   => 'col-lg-4 col-md-6',
            'mejor_cliente'    => 'col-lg-4 col-md-6',
            'grafico_ventas'   => 'col-lg-8',
            'ultimas_ventas'   => 'col-lg-12',
            'usuarios_roles'   => 'col-lg-12',
            'stock_bajo'       => 'col-lg-12',
        ];
    }

    // ─── Load widget config from DB ─────────────────────────────────────────

    protected function loadCatalog(): array
    {
        if ($this->widgetCatalog !== null) return $this->widgetCatalog;

        $widgets = DB::table('dashboard_widgets')
            ->where('enabled', true)
            ->orderBy('sort_order')
            ->get();

        // Preload roles for all widgets in one query
        $allRoleRows = DB::table('dashboard_widget_roles')
            ->join('rol', 'dashboard_widget_roles.rol_id', '=', 'rol.id')
            ->select('dashboard_widget_roles.widget_id', 'rol.nombre')
            ->get()
            ->groupBy('widget_id');

        $catalog = [];
        foreach ($widgets as $w) {
            $roleNames = isset($allRoleRows[$w->id])
                ? $allRoleRows[$w->id]->pluck('nombre')->toArray()
                : [];

            $catalog[$w->key] = [
                'id'          => $w->id,
                'title'       => $w->title,
                'icon'        => $w->icon,
                'color'       => $w->color,
                'widget_type' => $w->widget_type,
                'config'      => $w->config ? json_decode($w->config, true) : [],
                'roles'       => $roleNames,  // empty = visible to all
            ];
        }

        $this->widgetCatalog = $catalog;
        return $catalog;
    }

    // ─── Lifecycle ───────────────────────────────────────────────────────────

    public function mount(): void
    {
        $catalog = $this->loadCatalog();
        $userId  = Auth::id();

        $saved = DB::table('dashboard_widget_preferences')
            ->where('user_id', $userId)
            ->pluck('visible', 'widget_key')
            ->toArray();

        foreach ($catalog as $key => $cfg) {
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

    // ─── Save per-user widget order ───────────────────────────────────────────

    public function saveWidgetOrder(array $orderedKeys): void
    {
        $userId = Auth::id();
        foreach ($orderedKeys as $position => $key) {
            DB::table('dashboard_widget_preferences')->upsert(
                [
                    'user_id'    => $userId,
                    'widget_key' => $key,
                    'visible'    => $this->widgetPrefs[$key] ?? true,
                    'sort_order' => $position,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                ['user_id', 'widget_key'],
                ['sort_order', 'updated_at']
            );
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function canSeeWidget(string $key): bool
    {
        $catalog = $this->loadCatalog();
        if (!isset($catalog[$key])) return false;

        $roles = $catalog[$key]['roles'];
        if (empty($roles)) return true;   // no role restriction → everyone

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
        $this->widgetCatalog = null; // reset cache on each render
        $catalog = $this->loadCatalog();
        $data    = [];
        $mes     = now()->month;
        $anio    = now()->year;

        // Ensure widgetPrefs has all current catalog keys
        foreach ($catalog as $key => $cfg) {
            if (!array_key_exists($key, $this->widgetPrefs)) {
                $this->widgetPrefs[$key] = true;
            }
        }

        // ── stat_usuarios_activos ──
        if ($this->canSeeWidget('usuarios_activos')) {
            $data['totalUsuariosActivos'] = DB::table('users')->where('estado_id', 1)->count();
            $data['totalUsuarios']        = DB::table('users')->count();
        }

        // ── stat_ventas_mes ──
        if ($this->canSeeWidget('ventas_mes')) {
            $base = DB::table('factura')
                ->where('estado_factura_id', 1)
                ->whereMonth('fecha_emision', $mes)
                ->whereYear('fecha_emision', $anio);
            $data['ventasMesTotal'] = (clone $base)->sum('total');
            $data['ventasMesCount'] = (clone $base)->count();
        }

        // ── stat_mejor_vendedor ──
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

        // ── stat_mejor_cliente ──
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

        // ── tabla_ultimas_ventas ──
        if ($this->canSeeWidget('ultimas_ventas')) {
            $data['ultimasVentas'] = DB::table('factura')
                ->select('id', 'numero_factura', 'nombre_cliente', 'total', 'fecha_emision', 'vendedor')
                ->where('estado_factura_id', 1)
                ->orderByDesc('fecha_emision')
                ->limit(10)
                ->get();
        }

        // ── grafico_ventas_6m ──
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

        // ── tabla_usuarios_roles ──
        if ($this->canSeeWidget('usuarios_roles')) {
            $data['usuariosRoles'] = DB::table('users')
                ->leftJoin('rol', 'users.rol_id', '=', 'rol.id')
                ->select('users.id', 'users.name', 'users.email', 'rol.nombre as rol_nombre', 'users.estado_id', 'users.created_at')
                ->orderBy('users.name')
                ->get();
        }

        // ── tabla_stock_bajo ──
        if ($this->canSeeWidget('stock_bajo')) {
            $cfg       = $catalog['stock_bajo']['config'] ?? [];
            $minStock  = (int) ($cfg['stock_minimo'] ?? 10);
            $limite    = (int) ($cfg['limite'] ?? 20);

            $data['productosStockBajo'] = DB::table('producto')
                ->select(
                    'producto.id',
                    'producto.nombre',
                    'producto.codigo_barra',
                    DB::raw('COALESCE(SUM(cardex.cantidad), 0) as stock_actual')
                )
                ->leftJoin('cardex', 'cardex.id_producto', '=', 'producto.id')
                ->where('producto.estado_producto_id', 1)
                ->groupBy('producto.id', 'producto.nombre', 'producto.codigo_barra')
                ->havingRaw('COALESCE(SUM(cardex.cantidad), 0) <= ?', [$minStock])
                ->orderBy('stock_actual')
                ->limit($limite)
                ->get();

            $data['stockMinimo'] = $minStock;
        }

        // ── Sort catalog by user's saved sort_order preference ──
        $userSortOrders = DB::table('dashboard_widget_preferences')
            ->where('user_id', Auth::id())
            ->whereNotNull('sort_order')
            ->pluck('sort_order', 'widget_key')
            ->toArray();

        $colClasses = static::widgetColClass();
        $catalogKeys = array_keys($catalog);
        usort($catalogKeys, function ($a, $b) use ($userSortOrders, $catalog) {
            $aOrd = $userSortOrders[$a] ?? ($catalog[$a]['sort_order'] ?? 999);
            $bOrd = $userSortOrders[$b] ?? ($catalog[$b]['sort_order'] ?? 999);
            return $aOrd <=> $bOrd;
        });
        $sortedCatalog = [];
        foreach ($catalogKeys as $k) {
            $sortedCatalog[$k] = $catalog[$k];
        }

        return view('livewire.usuarios.dashboard', array_merge($data, [
            'widgetConfig' => $sortedCatalog,
            'colClasses'   => $colClasses,
        ]));
    }
}
