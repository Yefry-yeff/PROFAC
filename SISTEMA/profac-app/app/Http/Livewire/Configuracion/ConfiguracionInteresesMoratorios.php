<?php

namespace App\Http\Livewire\Configuracion;

use App\Models\ConfiguracionInteres;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Módulo de Configuración de Intereses Moratorios.
 *
 * Administra las tasas de interés por mora del sistema.
 * Principio: nunca se eliminan registros utilizados, solo se inactivan.
 */
class ConfiguracionInteresesMoratorios extends Component
{
    // ─── Lista ────────────────────────────────────────────────────────────────
    public array $configuraciones = [];

    // ─── Modal ────────────────────────────────────────────────────────────────
    public bool   $showModal    = false;
    public ?int   $editandoId   = null;

    // ─── Campos del formulario ────────────────────────────────────────────────
    public string $tasa_mensual       = '3.2500';
    public bool   $estado             = true;
    public string $fecha_vigencia     = '';
    public string $fecha_fin_vigencia = '';
    public string $observaciones      = '';

    // ─── Feedback ────────────────────────────────────────────────────────────
    public ?string $mensajeExito  = null;
    public ?string $mensajeError  = null;

    // ─── Listeners ───────────────────────────────────────────────────────────
    protected $listeners = ['refrescarLista' => 'cargarLista'];

    public function mount(): void
    {
        $this->fecha_vigencia = now()->toDateString();
        $this->cargarLista();
    }

    public function render()
    {
        return view('livewire.configuracion.configuracion-intereses-moratorios');
    }

    // ─── Carga de datos ───────────────────────────────────────────────────────

    public function cargarLista(): void
    {
        $this->configuraciones = ConfiguracionInteres::with(['usuarioCreador', 'usuarioModificador'])
            ->orderByDesc('fecha_vigencia')
            ->orderByDesc('id')
            ->get()
            ->toArray();
    }

    // ─── Abrir modal ──────────────────────────────────────────────────────────

    public function abrirCrear(): void
    {
        $this->resetFormulario();
        $this->showModal  = true;
        $this->editandoId = null;
    }

    public function abrirEditar(int $id): void
    {
        $cfg = ConfiguracionInteres::findOrFail($id);
        $this->editandoId        = $id;
        $this->tasa_mensual      = (string) $cfg->tasa_mensual;
        $this->estado            = (bool) $cfg->estado;
        $this->fecha_vigencia    = $cfg->fecha_vigencia?->toDateString() ?? '';
        $this->fecha_fin_vigencia = $cfg->fecha_fin_vigencia?->toDateString() ?? '';
        $this->observaciones     = $cfg->observaciones ?? '';
        $this->showModal         = true;
    }

    // ─── Guardar ──────────────────────────────────────────────────────────────

    public function guardar(): void
    {
        $this->mensajeExito = null;
        $this->mensajeError = null;

        $this->validate([
            'tasa_mensual'       => 'required|numeric|min:0.0001|max:999.9999',
            'estado'             => 'required|boolean',
            'fecha_vigencia'     => 'required|date',
            'fecha_fin_vigencia' => 'nullable|date|after_or_equal:fecha_vigencia',
            'observaciones'      => 'nullable|string|max:500',
        ], [
            'tasa_mensual.required'      => 'La tasa mensual es obligatoria.',
            'tasa_mensual.numeric'       => 'La tasa mensual debe ser un número.',
            'tasa_mensual.min'           => 'La tasa debe ser mayor a 0.',
            'fecha_vigencia.required'    => 'La fecha de vigencia es obligatoria.',
            'fecha_vigencia.date'        => 'La fecha de vigencia no es válida.',
            'fecha_fin_vigencia.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la de inicio.',
        ]);

        DB::beginTransaction();
        try {
            if ($this->editandoId) {
                $cfg = ConfiguracionInteres::findOrFail($this->editandoId);

                // Si está cobrado/en uso, solo se puede inactivar — no modificar la tasa
                $enUso = DB::table('factura_interes')
                    ->where('configuracion_interes_id', $this->editandoId)
                    ->exists();

                if ($enUso && (float) $cfg->tasa_mensual !== (float) $this->tasa_mensual) {
                    $this->mensajeError = 'Esta configuración ya tiene intereses registrados. No se puede modificar la tasa mensual. Solo puede cambiar el estado o las observaciones.';
                    DB::rollBack();
                    return;
                }

                $cfg->update([
                    'tasa_mensual'       => $this->tasa_mensual,
                    'estado'             => $this->estado,
                    'fecha_vigencia'     => $this->fecha_vigencia,
                    'fecha_fin_vigencia' => $this->fecha_fin_vigencia ?: null,
                    'observaciones'      => $this->observaciones ?: null,
                    'usr_modificador'    => Auth::id(),
                ]);

                $this->mensajeExito = 'Configuración actualizada correctamente.';
            } else {
                ConfiguracionInteres::create([
                    'tasa_mensual'       => $this->tasa_mensual,
                    'estado'             => $this->estado,
                    'fecha_vigencia'     => $this->fecha_vigencia,
                    'fecha_fin_vigencia' => $this->fecha_fin_vigencia ?: null,
                    'observaciones'      => $this->observaciones ?: null,
                    'usr_creador'        => Auth::id(),
                    'usr_modificador'    => Auth::id(),
                ]);

                $this->mensajeExito = 'Nueva configuración de intereses creada correctamente.';
            }

            DB::commit();
            $this->showModal = false;
            $this->resetFormulario();
            $this->cargarLista();

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->mensajeError = 'Ocurrió un error al guardar: ' . $e->getMessage();
        }
    }

    // ─── Inactivar ────────────────────────────────────────────────────────────

    public function inactivar(int $id): void
    {
        $cfg = ConfiguracionInteres::findOrFail($id);
        $cfg->update([
            'estado'          => false,
            'usr_modificador' => Auth::id(),
        ]);
        $this->mensajeExito = 'Configuración inactivada. Los registros históricos permanecen intactos.';
        $this->cargarLista();
    }

    public function activar(int $id): void
    {
        $cfg = ConfiguracionInteres::findOrFail($id);
        $cfg->update([
            'estado'          => true,
            'usr_modificador' => Auth::id(),
        ]);
        $this->mensajeExito = 'Configuración activada.';
        $this->cargarLista();
    }

    // ─── Reset formulario ─────────────────────────────────────────────────────

    public function cerrarModal(): void
    {
        $this->showModal = false;
        $this->resetFormulario();
    }

    private function resetFormulario(): void
    {
        $this->tasa_mensual       = '3.2500';
        $this->estado             = true;
        $this->fecha_vigencia     = now()->toDateString();
        $this->fecha_fin_vigencia = '';
        $this->observaciones      = '';
        $this->editandoId         = null;
        $this->mensajeExito       = null;
        $this->mensajeError       = null;
    }
}
