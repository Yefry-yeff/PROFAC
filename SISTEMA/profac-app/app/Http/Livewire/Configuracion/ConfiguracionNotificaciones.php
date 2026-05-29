<?php

namespace App\Http\Livewire\Configuracion;

use App\Jobs\AlertasRotacionInventarioJob;
use App\Models\AlertaRotacionConfig;
use App\Models\Area;
use App\Models\FlujoEtapa;
use App\Models\NivelRol;
use App\Models\NotificacionFlujoConfig;
use App\Models\Rol;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ConfiguracionNotificaciones extends Component
{
    // ─── Estado del formulario (modal crear/editar) ───────────────────────────
    public bool   $showModal     = false;
    public ?int   $editandoId    = null;

    public string $targetTipo    = 'rol';   // 'rol' | 'area'
    public ?int   $tipoTramiteId = null;
    public ?int   $rolId         = null;
    public ?int   $areaId        = null;
    public ?int   $nivelMaxId    = null;
    public bool   $escalarActivo = false;
    public ?int   $escalarHoras  = null;
    public ?int   $escalarNivelId = null;
    public bool   $activo        = true;

    // ─── Datos de catálogos ───────────────────────────────────────────────────
    public array $tiposTramites = [];
    public array $roles         = [];
    public array $areas         = [];
    public array $niveles       = [];

    // ─── Datos de la tabla principal ─────────────────────────────────────────
    public array $configs       = [];

    // ─── Advertencia de jerarquías incompletas ────────────────────────────────
    public array $rolesIncompletos = [];

    // ─── Estado global del sistema de notificaciones ──────────────────────────
    public bool $notificacionesActivas = false;

    // ─── Alertas de Rotación e Inventario ────────────────────────────────────────
    public bool    $showAlertaModal        = false;
    public ?int    $alertaEditandoId       = null;
    public string  $alertaNombre           = '';
    public string  $alertaTipo             = '';
    public string  $alertaPrioridad        = 'media';
    public string  $alertaIcono            = 'fa-exclamation-triangle';
    public string  $alertaColor            = '#f59e0b';
    public bool    $alertaActivo           = false;
    public string  $alertaTargetTipo       = 'rol';
    public ?int    $alertaRolId            = null;
    public ?int    $alertaAreaId           = null;
    public ?int    $alertaParametroDias    = null;
    public ?float  $alertaParametroUmbral  = null;
    public array   $alertasReglas          = [];
    public ?string $alertaMensajeEjecucion = null;

    public function mount(): void
    {
        // Cache-Aside: si caché existe úsala; si no (tras cache:clear), lee BD y repobla caché
        $this->notificacionesActivas = (bool)(int) Cache::remember(
            'notificaciones_sistema_activo',
            3600,
            fn () => DB::table('configuracion_sistema')
                ->where('clave', 'notificaciones_sistema_activo')
                ->value('valor') ?? '0'
        );
        $this->cargarCatalogos();
        $this->cargarConfigs();
        $this->verificarJerarquias();
        $this->cargarAlertasReglas();
    }

    public function toggleSistema(): void
    {
        // Bloquear activación si no hay ninguna regla configurada
        if (!$this->notificacionesActivas && empty($this->configs)) {
            session()->flash('error', 'No se pueden activar las notificaciones: primero debes crear al menos una regla de notificación.');
            return;
        }

        $this->notificacionesActivas = !$this->notificacionesActivas;

        // Cache-Aside: escribir en BD primero (fuente de verdad)
        DB::table('configuracion_sistema')
            ->where('clave', 'notificaciones_sistema_activo')
            ->update([
                'valor'      => $this->notificacionesActivas ? '1' : '0',
                'updated_at' => now(),
            ]);

        // Luego actualizar caché para que el listener no haga una query innecesaria
        Cache::forever('notificaciones_sistema_activo', $this->notificacionesActivas);

        $estado = $this->notificacionesActivas ? 'ACTIVADAS' : 'DESACTIVADAS';
        session()->flash('success', "Sistema de notificaciones {$estado} correctamente.");
    }

    private function cargarCatalogos(): void
    {
        // Lee desde flujo_etapas (catálogo ordenado del pipeline de venta).
        // Al agregar un paso nuevo al flujo, basta con insertar una fila ahí
        // y automáticamente aparecerá disponible en la configuración de notificaciones.
        $this->tiposTramites = FlujoEtapa::activas()
            ->map(fn ($e) => [
                'id'          => $e->tipo_tramite_id,
                'nombre'      => $e->nombre_display,
                'es_opcional' => $e->es_opcional,
            ])
            ->toArray();

        $this->roles   = Rol::where('estado_id', 1)->orderBy('nombre')->get(['id', 'nombre'])->toArray();
        $this->areas   = Area::activas()->get(['id', 'nombre'])->toArray();
        $this->niveles = NivelRol::activos()->get(['id', 'nombre', 'orden'])->toArray();
    }

    private function cargarConfigs(): void
    {
        $this->configs = NotificacionFlujoConfig::with(['rol', 'area', 'nivelMax', 'escalarNivel'])
            ->orderBy('tipo_tramite_id')
            ->orderBy('id')
            ->get()
            ->map(function ($c) {
                $tramiteNombre = collect($this->tiposTramites)->firstWhere('id', $c->tipo_tramite_id);
                return [
                    'id'               => $c->id,
                    'tipo_tramite_id'  => $c->tipo_tramite_id,
                    'tramite_nombre'   => $tramiteNombre ? $tramiteNombre['nombre'] : 'Tramite #'.$c->tipo_tramite_id,
                    'rol_nombre'       => $c->rol ? $c->rol->nombre : null,
                    'area_nombre'      => $c->area ? $c->area->nombre : null,
                    'nivel_max_nombre' => $c->nivelMax ? $c->nivelMax->nombre : null,
                    'escalar_activo'   => $c->escalar_activo,
                    'escalar_horas'    => $c->escalar_horas,
                    'escalar_nivel'    => $c->escalarNivel ? $c->escalarNivel->nombre : null,
                    'activo'           => $c->activo,
                    'rol_id'           => $c->rol_id,
                    'area_id'          => $c->area_id,
                ];
            })
            ->toArray();
    }

    public function verificarJerarquias(): void
    {
        // Encuentra roles activos sin nivel o sin área asignada
        $this->rolesIncompletos = Rol::where('estado_id', 1)
            ->where(function ($q) {
                $q->whereNull('nivel_id')->orWhereNull('area_id');
            })
            ->get(['id', 'nombre', 'nivel_id', 'area_id'])
            ->map(fn ($r) => [
                'id'     => $r->id,
                'nombre' => $r->nombre,
                'falta'  => (!$r->nivel_id && !$r->area_id)
                    ? 'nivel y área'
                    : (!$r->nivel_id ? 'nivel jerárquico' : 'área'),
            ])
            ->toArray();
    }

    // ─── CRUD ─────────────────────────────────────────────────────────────────

    public function nuevaRegla(): void
    {
        $this->resetForm();
        $this->showModal  = true;
        $this->editandoId = null;
    }

    public function editarRegla(int $id): void
    {
        $config = NotificacionFlujoConfig::findOrFail($id);
        $this->editandoId     = $id;
        $this->tipoTramiteId  = $config->tipo_tramite_id;
        $this->rolId          = $config->rol_id;
        $this->areaId         = $config->area_id;
        $this->nivelMaxId     = $config->nivel_max_id;
        $this->escalarActivo  = $config->escalar_activo;
        $this->escalarHoras   = $config->escalar_horas;
        $this->escalarNivelId = $config->escalar_nivel_id;
        $this->activo         = $config->activo;
        $this->targetTipo     = $config->rol_id ? 'rol' : 'area';
        $this->showModal      = true;
    }

    public function guardar(): void
    {
        $this->validate([
            'tipoTramiteId'  => 'required|integer',
            'targetTipo'     => 'required|in:rol,area',
            'rolId'          => 'nullable|integer|exists:rol,id',
            'areaId'         => 'nullable|integer|exists:area,id',
            'nivelMaxId'     => 'nullable|integer|exists:nivel_rol,id',
            'escalarHoras'   => 'nullable|integer|min:1|max:720',
            'escalarNivelId' => 'nullable|integer|exists:nivel_rol,id',
        ]);

        // Validar que al menos tenga rol o área según targetTipo
        if ($this->targetTipo === 'rol' && !$this->rolId) {
            $this->addError('rolId', 'Debe seleccionar un rol.');
            return;
        }
        if ($this->targetTipo === 'area' && !$this->areaId) {
            $this->addError('areaId', 'Debe seleccionar un área.');
            return;
        }

        $datos = [
            'tipo_tramite_id'  => $this->tipoTramiteId,
            'rol_id'           => $this->targetTipo === 'rol'  ? $this->rolId  : null,
            'area_id'          => $this->targetTipo === 'area' ? $this->areaId : null,
            'nivel_max_id'     => $this->targetTipo === 'area' ? $this->nivelMaxId : null,
            'escalar_activo'   => $this->escalarActivo,
            'escalar_horas'    => $this->escalarActivo ? $this->escalarHoras : null,
            'escalar_nivel_id' => $this->escalarActivo ? $this->escalarNivelId : null,
            'activo'           => $this->activo,
        ];

        if ($this->editandoId) {
            NotificacionFlujoConfig::findOrFail($this->editandoId)->update($datos);
        } else {
            NotificacionFlujoConfig::create($datos);
        }

        $this->resetForm();
        $this->showModal = false;
        $this->cargarConfigs();
        $this->verificarJerarquias();
        session()->flash('success', 'Regla de notificación guardada correctamente.');
    }

    public function toggleActivo(int $id): void
    {
        $config = NotificacionFlujoConfig::findOrFail($id);
        $config->update(['activo' => !$config->activo]);
        $this->cargarConfigs();
    }

    public function eliminar(int $id): void
    {
        NotificacionFlujoConfig::findOrFail($id)->delete();
        $this->cargarConfigs();
        session()->flash('success', 'Regla eliminada.');
    }

    // ─── Cobertura: cuántos usuarios recibirán la notificación ───────────────

    public function getCobertura(int $id): int
    {
        $config = NotificacionFlujoConfig::find($id);
        return $config ? $config->resolverUsuariosDestino()->count() : 0;
    }

    private function resetForm(): void
    {
        $this->editandoId     = null;
        $this->tipoTramiteId  = null;
        $this->rolId          = null;
        $this->areaId         = null;
        $this->nivelMaxId     = null;
        $this->escalarActivo  = false;
        $this->escalarHoras   = null;
        $this->escalarNivelId = null;
        $this->activo         = true;
        $this->targetTipo     = 'rol';
        $this->resetErrorBag();
    }

    // ─── Alertas: carga ───────────────────────────────────────────────────────

    private function cargarAlertasReglas(): void
    {
        $this->alertasReglas = AlertaRotacionConfig::with(['rol', 'area'])
            ->orderByRaw("FIELD(tipo,'recuperacion_proxima','recuperacion_vencida','sin_ventas','baja_rotacion','sobreinventario','incremento_demanda')")
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

    // ─── Alertas: CRUD ────────────────────────────────────────────────────────

    public function alertaNuevaRegla(): void
    {
        $this->alertaResetForm();
        $this->showAlertaModal = true;
    }

    public function alertaEditar(int $id): void
    {
        $r = AlertaRotacionConfig::findOrFail($id);
        $this->alertaEditandoId      = $id;
        $this->alertaNombre          = $r->nombre;
        $this->alertaTipo            = $r->tipo;
        $this->alertaPrioridad       = $r->prioridad;
        $this->alertaIcono           = $r->icono;
        $this->alertaColor           = $r->color;
        $this->alertaActivo          = $r->activo;
        $this->alertaRolId           = $r->rol_id;
        $this->alertaAreaId          = $r->area_id;
        $this->alertaParametroDias   = $r->parametro_dias;
        $this->alertaParametroUmbral = $r->parametro_umbral;
        $this->alertaTargetTipo      = $r->rol_id ? 'rol' : 'area';
        $this->showAlertaModal       = true;
    }

    public function alertaGuardar(): void
    {
        $this->validate([
            'alertaNombre'          => 'required|string|max:120',
            'alertaTipo'            => 'required|in:recuperacion_proxima,recuperacion_vencida,sin_ventas,baja_rotacion,sobreinventario,incremento_demanda',
            'alertaPrioridad'       => 'required|in:informativa,media,alta,critica',
            'alertaRolId'           => 'nullable|integer',
            'alertaAreaId'          => 'nullable|integer',
            'alertaParametroDias'   => 'nullable|integer|min:1|max:365',
            'alertaParametroUmbral' => 'nullable|numeric|min:0',
        ]);

        $data = [
            'nombre'           => trim($this->alertaNombre),
            'tipo'             => $this->alertaTipo,
            'prioridad'        => $this->alertaPrioridad,
            'icono'            => $this->alertaIcono ?: 'fa-exclamation-triangle',
            'color'            => $this->alertaColor  ?: '#f59e0b',
            'activo'           => $this->alertaActivo,
            'rol_id'           => $this->alertaTargetTipo === 'rol'  ? $this->alertaRolId  : null,
            'area_id'          => $this->alertaTargetTipo === 'area' ? $this->alertaAreaId : null,
            'parametro_dias'   => $this->alertaParametroDias,
            'parametro_umbral' => $this->alertaParametroUmbral,
        ];

        if ($this->alertaEditandoId) {
            AlertaRotacionConfig::where('id', $this->alertaEditandoId)->update($data);
            session()->flash('success_alertas', 'Regla actualizada correctamente.');
        } else {
            AlertaRotacionConfig::create($data);
            session()->flash('success_alertas', 'Nueva regla creada correctamente.');
        }

        $this->showAlertaModal = false;
        $this->alertaResetForm();
        $this->cargarAlertasReglas();
    }

    public function alertaToggleActivo(int $id): void
    {
        $r = AlertaRotacionConfig::findOrFail($id);
        $r->update(['activo' => !$r->activo]);
        $this->cargarAlertasReglas();
    }

    public function alertaEliminar(int $id): void
    {
        AlertaRotacionConfig::destroy($id);
        session()->flash('success_alertas', 'Regla eliminada.');
        $this->cargarAlertasReglas();
    }

    public function alertaEjecutarAhora(): void
    {
        // Lanza el job como proceso PHP independiente para no bloquear la petición HTTP.
        // Necesario cuando QUEUE_CONNECTION=sync (no hay worker separado).
        $php     = PHP_BINARY;
        $artisan = escapeshellarg(base_path('artisan'));

        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen("start /B \"\" \"{$php}\" {$artisan} alertas:evaluar-rotacion", 'r'));
        } else {
            exec("{$php} {$artisan} alertas:evaluar-rotacion > /dev/null 2>&1 &");
        }

        $this->alertaMensajeEjecucion = 'Evaluación enviada. Las notificaciones aparecerán en la campana en breve.';
    }

    private function alertaResetForm(): void
    {
        $this->alertaEditandoId      = null;
        $this->alertaNombre          = '';
        $this->alertaTipo            = '';
        $this->alertaPrioridad       = 'media';
        $this->alertaIcono           = 'fa-exclamation-triangle';
        $this->alertaColor           = '#f59e0b';
        $this->alertaActivo          = false;
        $this->alertaTargetTipo      = 'rol';
        $this->alertaRolId           = null;
        $this->alertaAreaId          = null;
        $this->alertaParametroDias   = null;
        $this->alertaParametroUmbral = null;
        $this->resetErrorBag();
    }

    private function getTiposAlertasCatalogo(): array
    {
        return [
            'recuperacion_proxima' => [
                'label'              => 'Recuperación próxima',
                'desc'               => 'Avisa N días antes de que el producto alcance su tiempo de recuperación.',
                'param_dias'         => true,
                'param_umbral'       => false,
                'param_dias_label'   => 'Días de anticipación',
                'param_umbral_label' => null,
            ],
            'recuperacion_vencida' => [
                'label'              => 'Recuperación vencida',
                'desc'               => 'Alerta cuando la fecha límite de recuperación ya fue superada y aún hay stock.',
                'param_dias'         => false,
                'param_umbral'       => false,
                'param_dias_label'   => null,
                'param_umbral_label' => null,
            ],
            'sin_ventas' => [
                'label'              => 'Sin ventas recientes',
                'desc'               => 'Dispara si el producto no registra ventas en N días y tiene stock.',
                'param_dias'         => true,
                'param_umbral'       => false,
                'param_dias_label'   => 'Días sin ventas',
                'param_umbral_label' => null,
            ],
            'baja_rotacion' => [
                'label'              => 'Baja rotación',
                'desc'               => 'Alerta si las ventas de los últimos 60 días están por debajo del umbral mínimo.',
                'param_dias'         => false,
                'param_umbral'       => true,
                'param_dias_label'   => null,
                'param_umbral_label' => 'Ventas mínimas (60 días)',
            ],
            'sobreinventario' => [
                'label'              => 'Sobreinventario',
                'desc'               => 'Avisa si la cobertura (stock / promedio mensual) supera X meses.',
                'param_dias'         => false,
                'param_umbral'       => true,
                'param_dias_label'   => null,
                'param_umbral_label' => 'Meses de cobertura máxima',
            ],
            'incremento_demanda' => [
                'label'              => 'Incremento de demanda',
                'desc'               => 'Notifica cuando las ventas crecen X% o más respecto al periodo anterior.',
                'param_dias'         => false,
                'param_umbral'       => true,
                'param_dias_label'   => null,
                'param_umbral_label' => 'Crecimiento mínimo (%)',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.configuracion.configuracion-notificaciones', [
            'tiposAlertas' => $this->getTiposAlertasCatalogo(),
        ]);
    }
}
