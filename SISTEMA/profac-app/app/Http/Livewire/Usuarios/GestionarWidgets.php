<?php

namespace App\Http\Livewire\Usuarios;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GestionarWidgets extends Component
{
    // ─── Modal state ────────────────────────────────────────────────────────
    public bool $modalAbierto = false;
    public bool $esNuevo      = true;

    // ─── Form fields ────────────────────────────────────────────────────────
    public ?int    $editandoId        = null;
    public string  $fTitle            = '';
    public string  $fIcon             = 'fa-bar-chart';
    public string  $fColor            = '#1ab394';
    public string  $fWidgetType       = 'stat_usuarios_activos';
    public bool    $fEnabled          = true;
    public int     $fSortOrder        = 99;
    public array   $fRolesCheck       = [];   // rol_id => bool
    // Config extras
    public int     $fStockMinimo      = 10;
    public int     $fStockLimite      = 20;

    // ─── Validation ─────────────────────────────────────────────────────────
    protected $rules = [
        'fTitle'       => 'required|min:3|max:120',
        'fIcon'        => 'required|max:60',
        'fColor'       => 'required|max:20',
        'fWidgetType'  => 'required',
        'fSortOrder'   => 'integer|min:0|max:9999',
        'fStockMinimo' => 'integer|min:0',
        'fStockLimite' => 'integer|min:1|max:200',
    ];

    // ─── Helpers ─────────────────────────────────────────────────────────────

    protected function allRoles(): \Illuminate\Support\Collection
    {
        return DB::table('rol')
            ->select('id', 'nombre')
            ->where('estado_id', 1)
            ->orderBy('nombre')
            ->get();
    }

    protected function buildKey(string $title, ?int $excludeId = null): string
    {
        $base = Str::slug($title, '_');
        $key  = $base;
        $i    = 2;
        while (
            DB::table('dashboard_widgets')
                ->where('key', $key)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $key = $base . '_' . $i++;
        }
        return $key;
    }

    protected function buildConfig(): ?string
    {
        if ($this->fWidgetType === 'tabla_stock_bajo') {
            return json_encode([
                'stock_minimo' => (int) $this->fStockMinimo,
                'limite'       => (int) $this->fStockLimite,
            ]);
        }
        return null;
    }

    // ─── Actions ─────────────────────────────────────────────────────────────

    public function abrirNuevo(): void
    {
        $this->esNuevo       = true;
        $this->editandoId    = null;
        $this->fTitle        = '';
        $this->fIcon         = 'fa-bar-chart';
        $this->fColor        = '#1ab394';
        $this->fWidgetType   = 'stat_usuarios_activos';
        $this->fEnabled      = true;
        $this->fSortOrder    = (int) DB::table('dashboard_widgets')->max('sort_order') + 10;
        $this->fStockMinimo  = 10;
        $this->fStockLimite  = 20;

        // Init all roles as unchecked
        $this->fRolesCheck = $this->allRoles()
            ->pluck('id')
            ->mapWithKeys(fn($id) => [$id => false])
            ->toArray();

        $this->resetValidation();
        $this->modalAbierto = true;
    }

    public function abrirEditar(int $id): void
    {
        $w = DB::table('dashboard_widgets')->find($id);
        if (!$w) return;

        $this->esNuevo       = false;
        $this->editandoId    = $id;
        $this->fTitle        = $w->title;
        $this->fIcon         = $w->icon;
        $this->fColor        = $w->color;
        $this->fWidgetType   = $w->widget_type;
        $this->fEnabled      = (bool) $w->enabled;
        $this->fSortOrder    = (int) $w->sort_order;

        $cfg = $w->config ? json_decode($w->config, true) : [];
        $this->fStockMinimo = (int) ($cfg['stock_minimo'] ?? 10);
        $this->fStockLimite = (int) ($cfg['limite'] ?? 20);

        // Load roles
        $assigned = DB::table('dashboard_widget_roles')
            ->where('widget_id', $id)
            ->pluck('rol_id')
            ->toArray();

        $this->fRolesCheck = $this->allRoles()
            ->pluck('id')
            ->mapWithKeys(fn($rid) => [$rid => in_array($rid, $assigned)])
            ->toArray();

        $this->resetValidation();
        $this->modalAbierto = true;
    }

    public function guardar(): void
    {
        $this->validate();

        $data = [
            'title'       => $this->fTitle,
            'icon'        => $this->fIcon,
            'color'       => $this->fColor,
            'widget_type' => $this->fWidgetType,
            'enabled'     => $this->fEnabled,
            'sort_order'  => $this->fSortOrder,
            'config'      => $this->buildConfig(),
            'updated_at'  => now(),
        ];

        if ($this->esNuevo) {
            $data['key']        = $this->buildKey($this->fTitle);
            $data['created_at'] = now();
            $widgetId = DB::table('dashboard_widgets')->insertGetId($data);
        } else {
            $widgetId = $this->editandoId;
            DB::table('dashboard_widgets')->where('id', $widgetId)->update($data);
            // Delete old role assignments
            DB::table('dashboard_widget_roles')->where('widget_id', $widgetId)->delete();
        }

        // Insert selected roles
        foreach ($this->fRolesCheck as $rolId => $checked) {
            if ($checked) {
                DB::table('dashboard_widget_roles')->insert([
                    'widget_id'  => $widgetId,
                    'rol_id'     => $rolId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->modalAbierto = false;
        session()->flash('success', $this->esNuevo ? 'Widget creado correctamente.' : 'Widget actualizado correctamente.');
    }

    public function toggleEnabled(int $id): void
    {
        $w = DB::table('dashboard_widgets')->find($id);
        if (!$w) return;

        DB::table('dashboard_widgets')
            ->where('id', $id)
            ->update(['enabled' => !$w->enabled, 'updated_at' => now()]);
    }

    public function eliminar(int $id): void
    {
        DB::table('dashboard_widgets')->where('id', $id)->delete();
        session()->flash('success', 'Widget eliminado.');
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->resetValidation();
    }

    // ─── Render ──────────────────────────────────────────────────────────────

    public function render()
    {
        $widgets = DB::table('dashboard_widgets')
            ->orderBy('sort_order')
            ->get();

        // Attach roles to each widget
        $allRoleRows = DB::table('dashboard_widget_roles')
            ->join('rol', 'dashboard_widget_roles.rol_id', '=', 'rol.id')
            ->select('dashboard_widget_roles.widget_id', 'rol.nombre')
            ->get()
            ->groupBy('widget_id');

        foreach ($widgets as $w) {
            $w->roles = isset($allRoleRows[$w->id])
                ? $allRoleRows[$w->id]->pluck('nombre')->toArray()
                : [];
        }

        return view('livewire.usuarios.gestionar-widgets', [
            'widgets'     => $widgets,
            'roles'       => $this->allRoles(),
            'widgetTypes' => Dashboard::widgetTypes(),
        ]);
    }
}
