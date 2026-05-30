<?php

namespace App\Http\Livewire\Configuracion;

use App\Jobs\AlertasRotacionInventarioJob;
use App\Models\AlertaRotacionConfig;
use App\Models\Area;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Livewire component incrustado en /configuracion/notificaciones/flujo
 * que gestiona las reglas de Alertas Inteligentes de Rotación e Inventario.
 */
class ConfiguracionAlertasRotacion extends Component
{
    // ─── Estado del modal ─────────────────────────────────────────────────────
    public bool  $showModal    = false;
    public ?int  $editandoId   = null;

    // ─── Campos del formulario ────────────────────────────────────────────────
    public string  $nombre          = '';
    public string  $tipo            = '';
    public string  $prioridad       = 'media';
    public string  $icono           = 'fa-exclamation-triangle';
    public string  $color           = '#f59e0b';
    public bool    $activo          = true;
    public string  $targetTipo      = 'rol';   // 'rol' | 'area'
    public ?int    $rolId           = null;
    public ?int    $areaId          = null;
    public ?int    $parametroDias   = null;
    public ?float  $parametroUmbral = null;

    // ─── Catálogos ────────────────────────────────────────────────────────────
    public array $roles = [];
    public array $areas = [];

    // ─── Datos de reglas existentes ───────────────────────────────────────────
    public array $reglas = [];

    // ─── Viabilidad por tipo ──────────────────────────────────────────────────
    public array $viabilidad = [];

    // ─── Mensajes de feedback ─────────────────────────────────────────────────
    public ?string $mensajeEjecucion = null;

    // ─── Definición de tipos (para UI) ───────────────────────────────────────
    public array $tiposAlertas = [
        'recuperacion_proxima' => [
            'label'          => 'Recuperación próxima',
            'desc'           => 'Avisa N días antes de que el producto alcance su tiempo de recuperación.',
            'param_dias'     => true,
            'param_umbral'   => false,
            'param_dias_label' => 'Días de anticipación',
        ],
        'recuperacion_vencida' => [
            'label'          => 'Recuperación vencida',
            'desc'           => 'Alerta cuando la fecha límite de recuperación ya fue superada y aún hay stock.',
            'param_dias'     => false,
            'param_umbral'   => false,
            'param_dias_label' => null,
        ],
        'sin_ventas' => [
            'label'          => 'Sin ventas recientes',
            'desc'           => 'Dispara si el producto no registra ventas en N días y tiene stock.',
            'param_dias'     => true,
            'param_umbral'   => false,
            'param_dias_label' => 'Días sin ventas',
        ],
        'baja_rotacion' => [
            'label'          => 'Baja rotación',
            'desc'           => 'Alerta si las ventas de los últimos 60 días están por debajo del umbral mínimo.',
            'param_dias'     => false,
            'param_umbral'   => true,
            'param_umbral_label' => 'Ventas mínimas (60 días)',
        ],
        'sobreinventario' => [
            'label'          => 'Sobreinventario',
            'desc'           => 'Avisa si la cobertura (stock / promedio mensual) supera X meses.',
            'param_dias'     => false,
            'param_umbral'   => true,
            'param_umbral_label' => 'Meses de cobertura máxima',
        ],
        'incremento_demanda' => [
            'label'          => 'Incremento de demanda',
            'desc'           => 'Notifica cuando las ventas crecen X% o más respecto al periodo anterior.',
            'param_dias'     => false,
            'param_umbral'   => true,
            'param_umbral_label' => 'Crecimiento mínimo (%)',
        ],
    ];

    // ─── Lifecycle ────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->roles = Rol::where('estado_id', 1)->orderBy('nombre')->get(['id', 'nombre'])->toArray();
        $this->areas = Area::activas()->get(['id', 'nombre'])->toArray();
        $this->cargarViabilidad();
        $this->cargarReglas();
    }

    private function cargarViabilidad(): void
    {
        // Productos con tiempo_recuperacion_meses Y compras registradas
        $conRecuperacion = DB::table('producto as p')
            ->join('compra_has_producto as chp', 'chp.producto_id', '=', 'p.id')
            ->where('p.tiempo_recuperacion_meses', '>', 0)
            ->distinct('p.id')->count('p.id');

        // Productos activos con stock > 0
        $conStock = DB::table('recibido_bodega as rb')
            ->join('producto as p', 'p.id', '=', 'rb.producto_id')
            ->where('p.estado_producto_id', 1)
            ->groupBy('rb.producto_id')
            ->havingRaw('SUM(rb.cantidad_disponible) > 0')
            ->get('rb.producto_id')->count();

        // Productos con ventas en ambos periodos (para incremento demanda)
        $conVentas30d = DB::table('factura as f')
            ->join('venta_has_producto as vhp', 'vhp.factura_id', '=', 'f.id')
            ->where('f.estado_factura_id', 1)
            ->where('f.fecha_emision', '>=', DB::raw('DATE_SUB(CURDATE(), INTERVAL 30 DAY)'))
            ->distinct('vhp.producto_id')->count('vhp.producto_id');

        $conVentas60d = DB::table('factura as f')
            ->join('venta_has_producto as vhp', 'vhp.factura_id', '=', 'f.id')
            ->where('f.estado_factura_id', 1)
            ->whereBetween('f.fecha_emision', [
                DB::raw('DATE_SUB(CURDATE(), INTERVAL 60 DAY)'),
                DB::raw('DATE_SUB(CURDATE(), INTERVAL 30 DAY)'),
            ])
            ->distinct('vhp.producto_id')->count('vhp.producto_id');

        // ok  = puede disparar hoy
        // warn = tiene datos pero puede que no haya coincidencias
        // lock = datos fundamentales ausentes, no puede disparar
        $this->viabilidad = [
            'recuperacion_proxima' => [
                'estado' => $conRecuperacion > 0 ? 'warn' : 'lock',
                'label'  => $conRecuperacion > 0
                    ? "Requiere revisar fechas ({$conRecuperacion} prod. con recuperación)"
                    : 'Sin datos — ningún producto tiene tiempo de recuperación + compra registrada',
                'puede_activar' => $conRecuperacion > 0,
            ],
            'recuperacion_vencida' => [
                'estado' => $conRecuperacion > 0 ? 'warn' : 'lock',
                'label'  => $conRecuperacion > 0
                    ? "Requiere revisar fechas ({$conRecuperacion} prod. con recuperación)"
                    : 'Sin datos — ningún producto tiene tiempo de recuperación + compra registrada',
                'puede_activar' => $conRecuperacion > 0,
            ],
            'sin_ventas' => [
                'estado' => $conStock > 0 ? 'ok' : 'lock',
                'label'  => "{$conStock} productos con stock activo",
                'puede_activar' => $conStock > 0,
            ],
            'baja_rotacion' => [
                'estado' => $conStock > 0 ? 'ok' : 'lock',
                'label'  => "{$conStock} productos con stock activo",
                'puede_activar' => $conStock > 0,
            ],
            'sobreinventario' => [
                'estado' => $conStock > 0 ? 'ok' : 'lock',
                'label'  => "{$conStock} productos con stock activo",
                'puede_activar' => $conStock > 0,
            ],
            'incremento_demanda' => [
                'estado' => ($conVentas30d > 0 && $conVentas60d > 0) ? 'ok' : 'warn',
                'label'  => "Ventas últ. 30d: {$conVentas30d} prod. | 30d anteriores: {$conVentas60d} prod.",
                'puede_activar' => $conVentas30d > 0,
            ],
        ];
    }

    private function cargarReglas(): void
    {
        $this->reglas = AlertaRotacionConfig::with(['rol', 'area'])
            ->orderByRaw("FIELD(tipo, 'recuperacion_proxima','recuperacion_vencida','sin_ventas','baja_rotacion','sobreinventario','incremento_demanda')")
            ->orderBy('parametro_dias')
            ->orderBy('parametro_umbral')
            ->get()
            ->map(fn ($r) => [
                'id'               => $r->id,
                'nombre'           => $r->nombre,
                'tipo'             => $r->tipo,
                'prioridad'        => $r->prioridad,
                'prioridad_label'  => $r->prioridad_label,
                'prioridad_color'  => $r->prioridad_color,
                'icono'            => $r->icono,
                'color'            => $r->color,
                'activo'           => $r->activo,
                'parametro_dias'   => $r->parametro_dias,
                'parametro_umbral' => $r->parametro_umbral,
                'descripcion'      => $r->descripcion_parametro,
                'rol_nombre'       => $r->rol?->nombre,
                'area_nombre'      => $r->area?->nombre,
                'rol_id'           => $r->rol_id,
                'area_id'          => $r->area_id,
            ])
            ->toArray();
    }

    // ─── CRUD ─────────────────────────────────────────────────────────────────

    public function nuevaRegla(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function editar(int $id): void
    {
        $regla = AlertaRotacionConfig::findOrFail($id);
        $this->editandoId      = $id;
        $this->nombre          = $regla->nombre;
        $this->tipo            = $regla->tipo;
        $this->prioridad       = $regla->prioridad;
        $this->icono           = $regla->icono;
        $this->color           = $regla->color;
        $this->activo          = $regla->activo;
        $this->rolId           = $regla->rol_id;
        $this->areaId          = $regla->area_id;
        $this->parametroDias   = $regla->parametro_dias;
        $this->parametroUmbral = $regla->parametro_umbral;
        $this->targetTipo      = $regla->rol_id ? 'rol' : 'area';
        $this->showModal       = true;
    }

    public function guardar(): void
    {
        $this->validate([
            'nombre'   => 'required|string|max:120',
            'tipo'     => 'required|in:recuperacion_proxima,recuperacion_vencida,sin_ventas,baja_rotacion,sobreinventario,incremento_demanda',
            'prioridad' => 'required|in:informativa,media,alta,critica',
            'rolId'    => 'nullable|integer',
            'areaId'   => 'nullable|integer',
            'parametroDias'   => 'nullable|integer|min:1|max:365',
            'parametroUmbral' => 'nullable|numeric|min:0',
        ]);

        $data = [
            'nombre'           => trim($this->nombre),
            'tipo'             => $this->tipo,
            'prioridad'        => $this->prioridad,
            'icono'            => $this->icono ?: 'fa-exclamation-triangle',
            'color'            => $this->color ?: '#f59e0b',
            'activo'           => $this->activo,
            'rol_id'           => $this->targetTipo === 'rol' ? $this->rolId : null,
            'area_id'          => $this->targetTipo === 'area' ? $this->areaId : null,
            'parametro_dias'   => $this->parametroDias,
            'parametro_umbral' => $this->parametroUmbral,
        ];

        if ($this->editandoId) {
            AlertaRotacionConfig::where('id', $this->editandoId)->update($data);
            session()->flash('success_alertas', 'Regla actualizada correctamente.');
        } else {
            AlertaRotacionConfig::create($data);
            session()->flash('success_alertas', 'Nueva regla de alerta creada correctamente.');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->cargarReglas();
    }

    public function toggleActivo(int $id): void
    {
        $regla = AlertaRotacionConfig::findOrFail($id);

        // Bloquear activación si la viabilidad indica que no puede disparar
        $viab = $this->viabilidad[$regla->tipo] ?? null;
        if ($viab && !$viab['puede_activar'] && !$regla->activo) {
            session()->flash('error_alertas', 'Esta regla no puede activarse: ' . $viab['label']);
            return;
        }

        $regla->update(['activo' => !$regla->activo]);
        $this->cargarReglas();
    }

    public function eliminar(int $id): void
    {
        AlertaRotacionConfig::destroy($id);
        session()->flash('success_alertas', 'Regla eliminada.');
        $this->cargarReglas();
    }

    // ─── Ejecución manual ─────────────────────────────────────────────────────

    /**
     * Dispara el job inmediatamente (modo sync, sin cola) para pruebas.
     */
    public function ejecutarAhora(): void
    {
        dispatch_sync(new AlertasRotacionInventarioJob());
        $this->mensajeEjecucion = 'Evaluación de alertas ejecutada. Revisa la campana de notificaciones para ver los resultados.';
    }

    // ─── Reset form ───────────────────────────────────────────────────────────

    private function resetForm(): void
    {
        $this->editandoId      = null;
        $this->nombre          = '';
        $this->tipo            = '';
        $this->prioridad       = 'media';
        $this->icono           = 'fa-exclamation-triangle';
        $this->color           = '#f59e0b';
        $this->activo          = true;
        $this->targetTipo      = 'rol';
        $this->rolId           = null;
        $this->areaId          = null;
        $this->parametroDias   = null;
        $this->parametroUmbral = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.configuracion.configuracion-alertas-rotacion');
    }
}
