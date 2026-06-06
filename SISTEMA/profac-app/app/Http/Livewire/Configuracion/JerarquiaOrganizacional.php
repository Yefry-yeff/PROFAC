<?php

namespace App\Http\Livewire\Configuracion;

use App\Models\Area;
use App\Models\NivelRol;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class JerarquiaOrganizacional extends Component
{
    // ─── Áreas ───────────────────────────────────────────────────────────────
    public array  $areas          = [];
    public bool   $showAreaModal  = false;
    public ?int   $editAreaId     = null;
    public string $areaNombre     = '';
    public string $areaDescripcion = '';

    // ─── Niveles Jerárquicos ─────────────────────────────────────────────────
    public array  $niveles          = [];
    public bool   $showNivelModal   = false;
    public ?int   $editNivelId      = null;
    public string $nivelNombre      = '';
    public string $nivelDescripcion = '';
    public int    $nivelOrden       = 1;

    // ─── UI ──────────────────────────────────────────────────────────────────
    public ?string $successMsg = null;

    public function mount(): void
    {
        $this->cargarAreas();
        $this->cargarNiveles();
    }

    // =========================================================================
    // ÁREAS
    // =========================================================================

    private function cargarAreas(): void
    {
        $this->areas = Area::orderBy('nombre')->get()->map(fn ($a) => [
            'id'          => $a->id,
            'nombre'      => $a->nombre,
            'descripcion' => $a->descripcion,
            'estado_id'   => $a->estado_id,
            'roles_count' => DB::table('rol')->where('area_id', $a->id)->where('estado_id', 1)->count(),
        ])->toArray();
    }

    public function nuevaArea(): void
    {
        $this->resetAreaForm();
        $this->showAreaModal = true;
    }

    public function editarArea(int $id): void
    {
        $area = Area::findOrFail($id);
        $this->editAreaId      = $id;
        $this->areaNombre      = $area->nombre;
        $this->areaDescripcion = $area->descripcion ?? '';
        $this->showAreaModal   = true;
    }

    public function guardarArea(): void
    {
        $this->validate([
            'areaNombre' => 'required|string|min:2|max:100',
        ], [
            'areaNombre.required' => 'El nombre del área es requerido.',
            'areaNombre.min'      => 'Mínimo 2 caracteres.',
        ]);

        if ($this->editAreaId) {
            Area::findOrFail($this->editAreaId)->update([
                'nombre'      => trim($this->areaNombre),
                'descripcion' => $this->areaDescripcion ?: null,
            ]);
            $this->successMsg = 'Área actualizada correctamente.';
        } else {
            Area::create([
                'nombre'      => trim($this->areaNombre),
                'descripcion' => $this->areaDescripcion ?: null,
                'estado_id'   => 1,
            ]);
            $this->successMsg = 'Área creada correctamente.';
        }

        $this->resetAreaForm();
        $this->showAreaModal = false;
        $this->cargarAreas();
    }

    public function toggleArea(int $id): void
    {
        $area = Area::findOrFail($id);
        $area->update(['estado_id' => $area->estado_id === 1 ? 2 : 1]);
        $this->cargarAreas();
        $this->successMsg = null;
    }

    public function eliminarArea(int $id): void
    {
        $roles = DB::table('rol')->where('area_id', $id)->count();
        if ($roles > 0) {
            session()->flash('error_area', 'No se puede eliminar: hay ' . $roles . ' rol(es) asignado(s) a esta área.');
            return;
        }
        Area::findOrFail($id)->delete();
        $this->cargarAreas();
        $this->successMsg = 'Área eliminada.';
    }

    private function resetAreaForm(): void
    {
        $this->editAreaId      = null;
        $this->areaNombre      = '';
        $this->areaDescripcion = '';
        $this->resetErrorBag('areaNombre');
    }

    // =========================================================================
    // NIVELES JERÁRQUICOS
    // =========================================================================

    private function cargarNiveles(): void
    {
        $this->niveles = NivelRol::orderBy('orden')->get()->map(fn ($n) => [
            'id'          => $n->id,
            'nombre'      => $n->nombre,
            'descripcion' => $n->descripcion,
            'orden'       => $n->orden,
            'estado_id'   => $n->estado_id,
            'roles_count' => DB::table('rol')->where('nivel_id', $n->id)->where('estado_id', 1)->count(),
        ])->toArray();
    }

    public function nuevoNivel(): void
    {
        $this->resetNivelForm();
        $maxOrden = count($this->niveles) > 0
            ? max(array_column($this->niveles, 'orden'))
            : 0;
        $this->nivelOrden    = $maxOrden + 1;
        $this->showNivelModal = true;
    }

    public function editarNivel(int $id): void
    {
        $nivel = NivelRol::findOrFail($id);
        $this->editNivelId      = $id;
        $this->nivelNombre      = $nivel->nombre;
        $this->nivelDescripcion = $nivel->descripcion ?? '';
        $this->nivelOrden       = $nivel->orden;
        $this->showNivelModal   = true;
    }

    public function guardarNivel(): void
    {
        $this->validate([
            'nivelNombre' => 'required|string|min:2|max:100',
            'nivelOrden'  => 'required|integer|min:1|max:99',
        ], [
            'nivelNombre.required' => 'El nombre del nivel es requerido.',
            'nivelOrden.required'  => 'El orden jerárquico es requerido.',
            'nivelOrden.min'       => 'El orden mínimo es 1.',
        ]);

        if ($this->editNivelId) {
            NivelRol::findOrFail($this->editNivelId)->update([
                'nombre'      => trim($this->nivelNombre),
                'descripcion' => $this->nivelDescripcion ?: null,
                'orden'       => $this->nivelOrden,
            ]);
            $this->successMsg = 'Nivel jerárquico actualizado.';
        } else {
            NivelRol::create([
                'nombre'      => trim($this->nivelNombre),
                'descripcion' => $this->nivelDescripcion ?: null,
                'orden'       => $this->nivelOrden,
                'estado_id'   => 1,
            ]);
            $this->successMsg = 'Nivel jerárquico creado.';
        }

        $this->resetNivelForm();
        $this->showNivelModal = false;
        $this->cargarNiveles();
    }

    public function toggleNivel(int $id): void
    {
        $nivel = NivelRol::findOrFail($id);
        $nivel->update(['estado_id' => $nivel->estado_id === 1 ? 2 : 1]);
        $this->cargarNiveles();
        $this->successMsg = null;
    }

    public function eliminarNivel(int $id): void
    {
        $roles = DB::table('rol')->where('nivel_id', $id)->count();
        if ($roles > 0) {
            session()->flash('error_nivel', 'No se puede eliminar: hay ' . $roles . ' rol(es) con este nivel asignado.');
            return;
        }
        NivelRol::findOrFail($id)->delete();
        $this->cargarNiveles();
        $this->successMsg = 'Nivel eliminado.';
    }

    private function resetNivelForm(): void
    {
        $this->editNivelId      = null;
        $this->nivelNombre      = '';
        $this->nivelDescripcion = '';
        $this->nivelOrden       = 1;
        $this->resetErrorBag('nivelNombre');
        $this->resetErrorBag('nivelOrden');
    }

    public function render()
    {
        return view('livewire.configuracion.jerarquia-organizacional');
    }
}
