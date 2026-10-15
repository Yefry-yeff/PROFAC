<?php

namespace Tests\Unit;

use App\Services\MenuGeneratorService;
use Composer\Autoload\ClassLoader;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\ViewServiceProvider;
use Livewire\Component;
use PHPUnit\Framework\TestCase;

class MenuGeneratorServiceTest extends TestCase
{
    private string $directory;
    private Filesystem $files;
    private ClassLoader $loader;
    private Container $previousContainer;
    private $previousFacadeApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousContainer = Container::getInstance();
        $this->previousFacadeApplication = Facade::getFacadeApplication();
        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'profac-menu-' . bin2hex(random_bytes(8));
        $this->files = new Filesystem();
        $this->files->makeDirectory($this->directory . '/routes', 0755, true);
        $this->files->makeDirectory($this->directory . '/compiled', 0755, true);
        $this->files->put($this->directory . '/routes/web.php', "<?php\n    // [auto-routes-anchor]\n");

        $app = new Application($this->directory);
        $app->instance('files', $this->files);
        $app->instance('config', new Repository([
            'view' => [
                'paths' => [$this->directory . '/resources/views'],
                'compiled' => $this->directory . '/compiled',
            ],
        ]));
        $app->instance('events', new Dispatcher($app));
        $app->instance('session', new Store('menu-test', new ArraySessionHandler(120)));
        $routes = new RouteCollection();
        $routes->add((new Route('GET', '/dashboard', fn () => ''))->name('dashboard'));
        $app->instance('url', new UrlGenerator($routes, Request::create('http://localhost')));
        $app->register(ViewServiceProvider::class);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);

        $this->loader = new ClassLoader();
        $this->loader->addPsr4('App\\', $this->directory . '/app');
        $this->loader->register(true);
    }

    protected function tearDown(): void
    {
        $this->loader->unregister();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previousFacadeApplication);
        Container::setInstance($this->previousContainer);
        $this->files->deleteDirectory($this->directory);

        parent::tearDown();
    }

    public function test_generates_an_autoloadable_livewire_page_and_renderable_blade_view(): void
    {
        $title = "Pagos de O'Brien <script>alert('x')</script> {{ literal }}";
        $result = (new MenuGeneratorService())->generarArchivosSubmenu(
            $title, '/cobros_prueba/pagos_no_identificados/'
        );

        $this->assertTrue($result['success'], implode("\n", $result['errores']));
        $this->assertTrue($result['ruta_insertada']);
        $this->assertCount(2, $result['archivos_creados']);
        $class = 'App\\Livewire\\CobrosPrueba\\PagosNoIdentificados';
        $this->assertTrue(class_exists($class), 'El componente debe cargar mediante PSR-4.');
        $component = new $class();
        $this->assertInstanceOf(Component::class, $component);
        $this->assertSame($title, $component->titulo);
        $this->assertFalse(method_exists($component, 'guardar'));
        $html = $component->render()->with(get_object_vars($component))->render();
        $this->assertStringContainsString(e($title), $html);
        $this->assertStringNotContainsString('onclick="abrirModal()"', $html);
        $this->assertStringContainsString('Modulo pendiente de implementar.', $html);
        $router = new Router(app('events'), app());
        $route = $router->get('/cobros_prueba/pagos_no_identificados', $class);
        $this->assertSame($class . '@__invoke', $route->getAction('uses'));
        $this->assertStringContainsString($result['ruta_generada'], $this->routes());
        $this->assertDirectoryDoesNotExist($this->directory . '/app/Http/Livewire');
    }

    public function test_preserves_all_url_segments_and_supports_single_segment_urls(): void
    {
        foreach ([
            'reportes_prueba' => 'App\\Livewire\\General\\ReportesPrueba',
            'ventas_prueba/reportes/por-cliente' => 'App\\Livewire\\VentasPrueba\\Reportes\\PorCliente',
        ] as $url => $class) {
            $result = (new MenuGeneratorService())->generarArchivosSubmenu('Reporte', $url);

            $this->assertTrue($result['success'], implode("\n", $result['errores']));
            $this->assertTrue(class_exists($class));
            $this->assertStringContainsString('\\' . $class . '::class', $result['ruta_generada']);
        }
    }

    public function test_invalid_urls_do_not_create_files_or_routes(): void
    {
        foreach (['', '../escape', 'https://example.com', 'ventas//pagos', "ventas/pagos');", 'ventas/class'] as $url) {
            $before = $this->routes();
            $result = (new MenuGeneratorService())->generarArchivosSubmenu('Prueba', $url);

            $this->assertFalse($result['success'], $url);
            $this->assertNotEmpty($result['errores']);
            $this->assertSame([], $result['archivos_creados']);
            $this->assertSame($before, $this->routes());
        }
    }

    public function test_existing_components_are_not_overwritten_or_registered_again(): void
    {
        $service = new MenuGeneratorService();
        $first = $service->generarArchivosSubmenu('Original', 'existentes/prueba');
        $this->assertTrue($first['success']);
        $before = $this->routes();
        $contents = $this->files->get($first['archivos_creados'][0]);

        $second = $service->generarArchivosSubmenu('Reemplazo', 'existentes/prueba');

        $this->assertFalse($second['success']);
        $this->assertSame($contents, $this->files->get($first['archivos_creados'][0]));
        $this->assertSame($before, $this->routes());
    }

    public function test_existing_views_are_preserved_and_new_component_is_removed_on_failure(): void
    {
        $view = $this->directory . '/resources/views/livewire/conflicto/vista.blade.php';
        $this->files->makeDirectory(dirname($view), 0755, true);
        $this->files->put($view, 'Vista existente');
        $before = $this->routes();

        $result = (new MenuGeneratorService())->generarArchivosSubmenu('Prueba', 'conflicto/vista');

        $this->assertFalse($result['success']);
        $this->assertSame('Vista existente', $this->files->get($view));
        $this->assertFileDoesNotExist($this->directory . '/app/Livewire/Conflicto/Vista.php');
        $this->assertSame($before, $this->routes());
    }

    public function test_missing_route_anchor_is_reported_and_generated_files_are_removed(): void
    {
        $this->files->put($this->directory . '/routes/web.php', '<?php');

        $result = (new MenuGeneratorService())->generarArchivosSubmenu('Prueba', 'sin_ancla/prueba');

        $this->assertFalse($result['success']);
        $this->assertFalse($result['ruta_insertada']);
        $this->assertNull($result['ruta_generada']);
        $this->assertStringContainsString('ancla', implode("\n", $result['errores']));
        $this->assertSame([], $result['archivos_creados']);
        $this->assertSame([], $this->files->allFiles($this->directory . '/app'));
        $this->assertSame([], $this->files->allFiles($this->directory . '/resources'));
    }

    public function test_existing_double_quoted_routes_are_not_duplicated(): void
    {
        $before = "<?php\nRoute::get(\"/duplicado/prueba\", fn () => '');\n    // [auto-routes-anchor]\n";
        $this->files->put($this->directory . '/routes/web.php', $before);

        $result = (new MenuGeneratorService())->generarArchivosSubmenu('Prueba', 'duplicado/prueba');

        $this->assertFalse($result['success']);
        $this->assertSame($before, $this->routes());
        $this->assertSame([], $result['archivos_creados']);
    }

    public function test_missing_routes_file_is_reported_without_leaving_a_partial_module(): void
    {
        $this->files->delete($this->directory . '/routes/web.php');

        $result = (new MenuGeneratorService())->generarArchivosSubmenu('Prueba', 'sin_rutas/prueba');

        $this->assertFalse($result['success']);
        $this->assertFalse($result['ruta_insertada']);
        $this->assertNotEmpty($result['errores']);
        $this->assertSame([], $result['archivos_creados']);
        $this->assertSame([], $this->files->allFiles($this->directory . '/app'));
        $this->assertSame([], $this->files->allFiles($this->directory . '/resources'));
    }

    private function routes(): string
    {
        return $this->files->get($this->directory . '/routes/web.php');
    }
}
