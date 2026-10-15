<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MenuGeneratorService
{
    /**
     * Genera un componente de clase Livewire 4, su vista y su ruta.
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
            'ruta_generada' => null,
            'ruta_insertada' => false
        ];

        try {
            // Parsear la URL para determinar la estructura
            $url = trim((string) $url, '/');
            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*(?:\/[a-zA-Z][a-zA-Z0-9_-]*)*$/D', $url)) {
                throw new \InvalidArgumentException('La URL debe ser una ruta local con segmentos de letras, numeros, guiones o guiones bajos.');
            }
            $urlParts = array_map([Str::class, 'studly'], explode('/', $url));
            if (count($urlParts) === 1) {
                array_unshift($urlParts, 'General');
            }
            
            // Nombre de clase del componente Livewire
            $componenteNombre = implode('\\', $urlParts);
            
            // 1. Crear Controlador Livewire
            $controladorCreado = $this->crearControladorLivewire($componenteNombre, $nombre);
            if ($controladorCreado['success']) {
                $resultados['archivos_creados'][] = $controladorCreado['archivo'];
            } else {
                throw new \RuntimeException($controladorCreado['error']);
            }

            // 2. Crear Vista Blade
            $vistaCreada = $this->crearVistaBlade($componenteNombre);
            if ($vistaCreada['success']) {
                $resultados['archivos_creados'][] = $vistaCreada['archivo'];
            } else {
                throw new \RuntimeException($vistaCreada['error']);
            }

            // Registrar la ruta solo cuando ambos archivos existen.
            $rutaTexto = $this->generarTextoRuta($url, $componenteNombre);
            $resultados['ruta_generada'] = $rutaTexto;
            $resultados['ruta_insertada'] = true;

        } catch (\Exception $e) {
            foreach ($resultados['archivos_creados'] as $archivo) {
                if (!File::delete($archivo)) {
                    $resultados['errores'][] = 'No se pudo eliminar el archivo incompleto: ' . $archivo;
                }
            }
            $resultados['archivos_creados'] = [];
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
            $partes = explode('\\', $componenteNombre);
            $className = array_pop($partes);
            $namespace = 'App\\Livewire\\' . implode('\\', $partes);
            
            // Crear directorio si no existe
            $directorio = app_path('Livewire/' . implode('/', $partes));
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

            $titulo = var_export($tituloModulo, true);
            // Mantener componentes de clase, compatibles con Livewire 4.
            $contenido = "<?php

namespace {$namespace};

use Livewire\Component;
use Illuminate\Contracts\View\View;

class {$className} extends Component
{
    public string \$titulo = {$titulo};

    public function render(): View
    {
        return view('livewire." . strtolower(str_replace('\\', '.', $componenteNombre)) . "');
    }
}
";

            try {
                token_get_all($contenido, TOKEN_PARSE);
            } catch (\ParseError $e) {
                throw new \InvalidArgumentException('La URL no produce un nombre de componente PHP valido.', 0, $e);
            }
            if (File::put($rutaArchivo, $contenido) === false) {
                throw new \RuntimeException('No se pudo escribir el componente: ' . $rutaArchivo);
            }

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
    private function crearVistaBlade($componenteNombre)
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
            <h2>{{ \$titulo }}</h2>
            <ol class=\"breadcrumb\">
                <li class=\"breadcrumb-item\">
                    <a href=\"{{ route('dashboard') }}\">Inicio</a>
                </li>
                <li class=\"breadcrumb-item active\">
                    <strong>{{ \$titulo }}</strong>
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
                        <h5>{{ \$titulo }}</h5>
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
                                    <tr>
                                        <td colspan=\"4\" class=\"text-center text-muted\">Modulo pendiente de implementar.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

";

            // Crear archivo
            if (File::put($rutaArchivo, $contenido) === false) {
                throw new \RuntimeException('No se pudo escribir la vista: ' . $rutaArchivo);
            }

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
     * Genera el texto de la ruta y lo inserta en web.php
     */
    private function generarTextoRuta($url, $componenteNombre)
    {
        $componenteCompleto = 'App\\Livewire\\' . str_replace('/', '\\', $componenteNombre);
        
        $rutaTexto = "Route::get('/{$url}', \\{$componenteCompleto}::class);";
        
        // Intentar insertar la ruta en web.php automáticamente
        $webPhpPath = base_path('routes/web.php');
        if (File::exists($webPhpPath)) {
            $contenido = File::get($webPhpPath);
            
            // Verificar que la ruta no existe ya
            if (!preg_match('~[\'"]/' . preg_quote($url, '~') . '[\'"]~', $contenido)) {
                // Agregar la ruta antes del ancla // [auto-routes-anchor]
                $lineaRuta = "\n    // Ruta auto-generada para: {$componenteNombre}\n    {$rutaTexto}\n\n";
                $ancla = '    // [auto-routes-anchor]';
                $posAncla = strpos($contenido, $ancla);
                if ($posAncla !== false) {
                    $contenido = substr($contenido, 0, $posAncla) . $lineaRuta . substr($contenido, $posAncla);
                    if (File::put($webPhpPath, $contenido, true) === false) {
                        throw new \RuntimeException('No se pudo escribir routes/web.php.');
                    }
                    return $rutaTexto;
                }
                throw new \RuntimeException('No se encontro el ancla [auto-routes-anchor] en routes/web.php.');
            }
            throw new \RuntimeException('Ya existe una ruta para la URL: /' . $url);
        }
        throw new \RuntimeException('No se encontro routes/web.php.');
    }
}
