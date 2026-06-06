<div wire:init="loadData">
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>Bitácora de Login</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item active">
                    <strong>Bitácora de Login</strong>
                </li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Filtros de Búsqueda -->
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5><i class="fa fa-filter"></i> Filtros de Búsqueda</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Búsqueda</label>
                                    <input type="text" class="form-control" wire:model.debounce.500ms="search" 
                                           placeholder="Buscar por nombre, IP o terminal...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha Inicio</label>
                                    <input type="date" class="form-control" wire:model="fechaInicio">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha Fin</label>
                                    <input type="date" class="form-control" wire:model="fechaFin">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Registros por página</label>
                                    <select class="form-control" wire:model="perPage">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="200">200</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-sm btn-primary" wire:click="$refresh">
                                    <i class="fa fa-search"></i> Buscar
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" wire:click="limpiarFiltros">
                                    <i class="fa fa-eraser"></i> Limpiar Filtros
                                </button>
                                <button type="button" class="btn btn-sm btn-success" wire:click="exportarExcel">
                                    <i class="fa fa-file-excel-o"></i> Exportar Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Bitácora -->
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Historial de Accesos al Sistema</h5>
                        <div class="ibox-tools">
                            @if($readyToLoad)
                                <span class="badge badge-primary">Total: {{ $loginHistory->total() ?? 0 }} registros</span>
                            @endif
                        </div>
                    </div>
                    <div class="ibox-content">
                        @if(!$readyToLoad)
                            <!-- Estado de carga inicial -->
                            <div class="text-center p-5">
                                <div class="sk-spinner sk-spinner-wave">
                                    <div class="sk-rect1"></div>
                                    <div class="sk-rect2"></div>
                                    <div class="sk-rect3"></div>
                                    <div class="sk-rect4"></div>
                                    <div class="sk-rect5"></div>
                                </div>
                                <p class="mt-3 text-muted">Cargando bitácora de login...</p>
                            </div>
                        @else
                        <!-- Contenido de la tabla -->
                        <div wire:loading.class="opacity-50">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="cursor: pointer;" wire:click="sortBy('id')">
                                            ID 
                                            @if($sortField === 'id')
                                                <i class="fa fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fa fa-sort"></i>
                                            @endif
                                        </th>
                                        <th style="cursor: pointer;" wire:click="sortBy('nombre')">
                                            Nombre de Usuario
                                            @if($sortField === 'nombre')
                                                <i class="fa fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fa fa-sort"></i>
                                            @endif
                                        </th>
                                        <th style="cursor: pointer;" wire:click="sortBy('ip_address')">
                                            Dirección IP
                                            @if($sortField === 'ip_address')
                                                <i class="fa fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fa fa-sort"></i>
                                            @endif
                                        </th>
                                        <th style="cursor: pointer;" wire:click="sortBy('terminal')">
                                            Terminal
                                            @if($sortField === 'terminal')
                                                <i class="fa fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fa fa-sort"></i>
                                            @endif
                                        </th>
                                        <th style="cursor: pointer;" wire:click="sortBy('fecha_ingreso')">
                                            Fecha y Hora de Ingreso
                                            @if($sortField === 'fecha_ingreso')
                                                <i class="fa fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fa fa-sort"></i>
                                            @endif
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($loginHistory as $login)
                                        <tr>
                                            <td>{{ $login->id }}</td>
                                            <td>
                                                <i class="fa fa-user text-navy"></i>
                                                <strong>{{ $login->nombre }}</strong>
                                            </td>
                                            <td>
                                                <i class="fa fa-globe text-primary"></i>
                                                {{ $login->ip_address }}
                                            </td>
                                            <td>
                                                <i class="fa fa-desktop text-warning"></i>
                                                {{ $login->terminal ?? 'N/A' }}
                                            </td>
                                            <td>
                                                <i class="fa fa-calendar text-success"></i>
                                                {{ \Carbon\Carbon::parse($login->fecha_ingreso)->format('d/m/Y H:i:s') }}
                                                <br>
                                                <small class="text-muted">
                                                    ({{ \Carbon\Carbon::parse($login->fecha_ingreso)->diffForHumans() }})
                                                </small>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                <i class="fa fa-info-circle"></i> No hay registros disponibles
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        </div>

                        <!-- Paginación -->
                        <div class="mt-3">
                            {{ $loginHistory->links() }}
                        </div>

                        <!-- Información de Paginación -->
                        <div class="mt-2">
                            <small class="text-muted">
                                Mostrando {{ $loginHistory->firstItem() ?? 0 }} a {{ $loginHistory->lastItem() ?? 0 }} 
                                de {{ $loginHistory->total() }} registros
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
    <style>
        .opacity-50 {
            opacity: 0.5;
            pointer-events: none;
        }
        
        /* Animación del spinner */
        .sk-spinner-wave.sk-spinner {
            margin: 0 auto;
            width: 50px;
            height: 40px;
            text-align: center;
            font-size: 10px;
        }
        
        .sk-spinner-wave div {
            background-color: #1ab394;
            height: 100%;
            width: 6px;
            display: inline-block;
            -webkit-animation: sk-waveStretchDelay 1.2s infinite ease-in-out;
            animation: sk-waveStretchDelay 1.2s infinite ease-in-out;
        }
        
        .sk-spinner-wave .sk-rect2 {
            -webkit-animation-delay: -1.1s;
            animation-delay: -1.1s;
        }
        
        .sk-spinner-wave .sk-rect3 {
            -webkit-animation-delay: -1.0s;
            animation-delay: -1.0s;
        }
        
        .sk-spinner-wave .sk-rect4 {
            -webkit-animation-delay: -0.9s;
            animation-delay: -0.9s;
        }
        
        .sk-spinner-wave .sk-rect5 {
            -webkit-animation-delay: -0.8s;
            animation-delay: -0.8s;
        }
        
        @-webkit-keyframes sk-waveStretchDelay {
            0%, 40%, 100% {
                -webkit-transform: scaleY(0.4);
                transform: scaleY(0.4);
            }
            20% {
                -webkit-transform: scaleY(1.0);
                transform: scaleY(1.0);
            }
        }
        
        @keyframes sk-waveStretchDelay {
            0%, 40%, 100% {
                -webkit-transform: scaleY(0.4);
                transform: scaleY(0.4);
            }
            20% {
                -webkit-transform: scaleY(1.0);
                transform: scaleY(1.0);
            }
        }
    </style>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Bitácora de Login - Vista cargada con lazy loading');
        });
    </script>
@endpush
