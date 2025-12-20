<?php

namespace App\Http\Livewire\Registro;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\LoginHistory;
use App\Exports\LoginHistoryExport;
use Maatwebsite\Excel\Facades\Excel;

class Login extends Component
{
    use WithPagination;

    // Propiedades del componente
    public $titulo = 'Bitácora de Login';
    public $search = '';
    public $perPage = 25;
    public $sortField = 'fecha_ingreso';
    public $sortDirection = 'desc';
    public $fechaInicio = '';
    public $fechaFin = '';
    public $readyToLoad = false;

    protected $paginationTheme = 'bootstrap';
    protected $queryString = ['search', 'sortField', 'sortDirection'];

    /**
     * Inicializar el componente
     */
    public function mount()
    {
        // Establecer fechas por defecto (último mes)
        $this->fechaFin = date('Y-m-d');
        $this->fechaInicio = date('Y-m-d', strtotime('-30 days'));
    }

    /**
     * Activar carga de datos (lazy loading)
     */
    public function loadData()
    {
        $this->readyToLoad = true;
    }

    /**
     * Limpiar paginación al buscar
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Cambiar orden de columnas
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Limpiar filtros
     */
    public function limpiarFiltros()
    {
        $this->search = '';
        $this->fechaInicio = date('Y-m-d', strtotime('-30 days'));
        $this->fechaFin = date('Y-m-d');
        $this->resetPage();
    }

    /**
     * Renderizar la vista con los datos de login
     */
    public function render()
    {
        // Lazy loading: solo cargar datos cuando esté listo
        if (!$this->readyToLoad) {
            return view('livewire.registro.login', [
                'loginHistory' => collect()
            ]);
        }

        $loginHistory = LoginHistory::query()
            ->select('id', 'user_id', 'nombre', 'ip_address', 'terminal', 'fecha_ingreso')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('ip_address', 'like', '%' . $this->search . '%')
                      ->orWhere('terminal', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->fechaInicio, function ($query) {
                $query->whereDate('fecha_ingreso', '>=', $this->fechaInicio);
            })
            ->when($this->fechaFin, function ($query) {
                $query->whereDate('fecha_ingreso', '<=', $this->fechaFin);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.registro.login', [
            'loginHistory' => $loginHistory
        ]);
    }

    /**
     * Exportar a Excel
     */
    public function exportarExcel()
    {
        try {
            $data = LoginHistory::query()
                ->select('id', 'user_id', 'nombre', 'ip_address', 'terminal', 'fecha_ingreso')
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('nombre', 'like', '%' . $this->search . '%')
                          ->orWhere('ip_address', 'like', '%' . $this->search . '%')
                          ->orWhere('terminal', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->fechaInicio, function ($query) {
                    $query->whereDate('fecha_ingreso', '>=', $this->fechaInicio);
                })
                ->when($this->fechaFin, function ($query) {
                    $query->whereDate('fecha_ingreso', '<=', $this->fechaFin);
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->get();

            $fileName = 'bitacora_login_' . date('Y-m-d_His') . '.xlsx';
            
            return Excel::download(
                new LoginHistoryExport($data, $this->fechaInicio, $this->fechaFin), 
                $fileName
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Error al exportar: ' . $e->getMessage());
        }
    }
}
