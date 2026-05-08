<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MenuGeneratorService
{
    /**
     * Genera automáticamente controlador, vista y ruta para un submenu
     * 
     * @param string $nombre Nombre del submenu
     * @param string $url URL del submenu (ej: usuarios/listar)
     * @return array Resultados de la generación
     */
    public function generarArchivosSubmenu($nombre, $url)
    {
        $resultados = [
            'success' => true,
            'archivos_creados' => [],
            'errores' => [],
            'ruta_generada' => null
        ];

        try {
            // Parsear la URL para determinar la estructura
            $urlParts = explode('/', trim($url, '/'));
            $carpeta = count($urlParts) > 1 ? Str::studly($urlParts[0]) : 'General';
            $accion = count($urlParts) > 1 ? Str::studly($urlParts[1]) : Str::studly($urlParts[0]);
            
            // Nombre de clase del componente Livewire
            $componenteNombre = $carpeta . '\\' . $accion;
            
            // 1. Crear Controlador Livewire
            $controladorCreado = $this->crearControladorLivewire($componenteNombre, $nombre);
            if ($controladorCreado['success']) {
                $resultados['archivos_creados'][] = $controladorCreado['archivo'];
            } else {
                $resultados['errores'][] = $controladorCreado['error'];
            }

            // 2. Crear Vista Blade
            $vistaCreada = $this->crearVistaBlade($componenteNombre, $nombre);
            if ($vistaCreada['success']) {
                $resultados['archivos_creados'][] = $vistaCreada['archivo'];
            } else {
                $resultados['errores'][] = $vistaCreada['error'];
            }

            // 3. Generar texto de ruta (no se inserta automáticamente por seguridad)
            $rutaTexto = $this->generarTextoRuta($url, $componenteNombre);
            $resultados['ruta_generada'] = $rutaTexto;

            // Si hay errores, marcar como parcialmente exitoso
            if (!empty($resultados['errores'])) {
                $resultados['success'] = false;
            }

        } catch (\Exception $e) {
            $resultados['success'] = false;
            $resultados['errores'][] = 'Error general: ' . $e->getMessage();
        }

        return $resultados;
    }

    /**
     * Crea el controlador Livewire
     */
    private function crearControladorLivewire($componenteNombre, $tituloModulo)
    {
        try {
            $namespace = 'App\\Http\\Livewire\\' . str_replace('/', '\\', dirname($componenteNombre));
            $className = basename(str_replace('\\', '/', $componenteNombre));
            
            // Crear directorio si no existe
            $directorio = app_path('Http/Livewire/' . dirname(str_replace('\\', '/', $componenteNombre)));
            if (!File::exists($directorio)) {
                File::makeDirectory($directorio, 0755, true);
            }

            // Ruta del archivo
            $rutaArchivo = $directorio . '/' . $className . '.php';

            // Verificar si ya existe
            if (File::exists($rutaArchivo)) {
                return [
                    'success' => false,
                    'error' => 'El controlador ya existe: ' . $rutaArchivo
                ];
            }

            // Plantilla del controlador
            $contenido = "<?php

namespace {$namespace};

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class {$className} extends Component
{
    // Propiedades del componente
    public \$titulo = '{$tituloModulo}';

    /**
     * Inicializar el componente
     */
    public function mount()
    {
        // Inicialización si es necesaria
    }

    /**
     * Renderizar la vista
     */
    public function render()
    {
        return view('livewire." . strtolower(str_replace('\\', '.', $componenteNombre)) . "');
    }

    /**
     * Ejemplo: Listar datos
     */
    public function listarDatos()
    {
        try {
            // TODO: Implementar lógica de listado
            \$datos = DB::table('tu_tabla')->get();
            
            return response()->json([
                'success' => true,
                'data' => \$datos
            ], 200);
        } catch (\\Exception \$e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al listar datos: ' . \$e->getMessage()
            ], 500);
        }
    }

    /**
     * Ejemplo: Guardar registro
     */
    public function guardar(\$request)
    {
        try {
            DB::beginTransaction();
            
            // TODO: Implementar lógica de guardado
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'mensaje' => 'Registro guardado correctamente'
            ], 201);
        } catch (\\Exception \$e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al guardar: ' . \$e->getMessage()
            ], 500);
        }
    }
}
";

            // Crear archivo
            File::put($rutaArchivo, $contenido);

            return [
                'success' => true,
                'archivo' => $rutaArchivo
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al crear controlador: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Crea la vista Blade
     */
    private function crearVistaBlade($componenteNombre, $tituloModulo)
    {
        try {
            // Convertir namespace a ruta de carpetas
            $rutaVista = strtolower(str_replace('\\', '/', $componenteNombre));
            
            // Crear directorio si no existe
            $directorio = resource_path('views/livewire/' . dirname($rutaVista));
            if (!File::exists($directorio)) {
                File::makeDirectory($directorio, 0755, true);
            }

            // Ruta del archivo
            $rutaArchivo = $directorio . '/' . basename($rutaVista) . '.blade.php';

            // Verificar si ya existe
            if (File::exists($rutaArchivo)) {
                return [
                    'success' => false,
                    'error' => 'La vista ya existe: ' . $rutaArchivo
                ];
            }

            // Plantilla de la vista
            $contenido = "<div>
    <div class=\"row wrapper border-bottom white-bg page-heading\">
        <div class=\"col-lg-10\">
            <h2>{$tituloModulo}</h2>
            <ol class=\"breadcrumb\">
                <li class=\"breadcrumb-item\">
                    <a href=\"{{ route('dashboard') }}\">Inicio</a>
                </li>
                <li class=\"breadcrumb-item active\">
                    <strong>{$tituloModulo}</strong>
                </li>
            </ol>
        </div>
    </div>

    <div class=\"wrapper wrapper-content animated fadeInRight\">
        
        @if (session()->has('success'))
            <div class=\"alert alert-success alert-dismissible fade show\" role=\"alert\">
                {{ session('success') }}
                <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">
                {{ session('error') }}
                <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>
        @endif

        <!-- Contenido Principal -->
        <div class=\"row\">
            <div class=\"col-lg-12\">
                <div class=\"ibox\">
                    <div class=\"ibox-title\">
                        <h5>{$tituloModulo}</h5>
                        <div class=\"ibox-tools\">
                            <button type=\"button\" class=\"btn btn-primary btn-sm\" onclick=\"abrirModal()\">
                                <i class=\"fa fa-plus\"></i> Nuevo
                            </button>
                        </div>
                    </div>
                    <div class=\"ibox-content\">
                        <div class=\"table-responsive\">
                            <table class=\"table table-striped table-bordered table-hover\" id=\"tablaData\">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Los datos se cargarán vía AJAX/Livewire --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
    <script>
        // TODO: Implementar lógica JavaScript si es necesaria
        console.log('{$tituloModulo} - Vista cargada');
    </script>
@endpush
";

            // Crear archivo
            File::put($rutaArchivo, $contenido);

            return [
                'success' => true,
                'archivo' => $rutaArchivo
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al crear vista: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Genera el texto de la ruta para agregar manualmente
     */
    private function generarTextoRuta($url, $componenteNombre)
    {
        $componenteCompleto = 'App\\Http\\Livewire\\' . str_replace('/', '\\', $componenteNombre);
        
        return "Route::get('/{$url}', {$componenteCompleto}::class);";
    }
}
