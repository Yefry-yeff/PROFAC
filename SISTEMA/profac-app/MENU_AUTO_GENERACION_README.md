# Generación Automática de Archivos para Menús - PROFAC

## Compatibilidad con Livewire 4

La generación conserva el formato de **componente de clase PHP + vista Blade**
que utiliza el proyecto. Livewire 4 admite este formato; no es necesario migrar
estos módulos a componentes de archivo único.

Al crear un submenu con **Generar archivos automáticamente**, el sistema crea:

1. **Componente** en `app/Livewire/`, con namespace `App\Livewire`.
   Incluye una propiedad pública tipada `$titulo` y `render(): View`.
2. **Vista** en `resources/views/livewire/`, con título escapado por Blade,
   breadcrumbs, alertas de sesión y una tabla que indica que el módulo está
   pendiente de implementar.
3. **Ruta** insertada en `routes/web.php` antes de
   `// [auto-routes-anchor]`, dentro del grupo protegido existente.

No se generan consultas a tablas ficticias, acciones de guardado vacías ni
botones que invoquen funciones JavaScript inexistentes.

## Cómo usar

1. Abre **Gestión de Menús** (`/menu/gestion`) y selecciona **Nuevo Submenu**.
2. Completa menú padre, nombre, URL, orden, estado y roles con acceso.
3. Activa **Generar archivos automáticamente** solo para módulos nuevos.
4. Guarda y revisa los archivos y la ruta que informa el sistema.
5. Implementa la lógica de negocio en el componente y su vista.

La ruta ya queda registrada: **no debes pegarla nuevamente** en
`routes/web.php`. Si el despliegue usa rutas cacheadas, ejecuta
`php artisan route:clear` y reconstruye la caché según el proceso de despliegue.

Para enlazar una pantalla existente, desactiva la generación.

## Convenciones de nombres

Se admiten rutas locales con segmentos que comienzan por una letra y contienen
letras ASCII, números, guiones o guiones bajos. No se admiten URLs externas,
parámetros de consulta, segmentos vacíos ni nombres de clase PHP inválidos.
Se normalizan las barras al inicio y al final.

| URL | Componente bajo `app/Livewire/` | Vista bajo `resources/views/livewire/` |
|-----|--------------------------------|---------------------------------------|
| `usuarios/listar` | `Usuarios/Listar.php` | `usuarios/listar.blade.php` |
| `cuentas_por_cobrar/pagos_no_identificados` | `CuentasPorCobrar/PagosNoIdentificados.php` | `cuentasporcobrar/pagosnoidentificados.blade.php` |
| `ventas/reportes/por-cliente` | `Ventas/Reportes/PorCliente.php` | `ventas/reportes/porcliente.blade.php` |
| `reportes` | `General/Reportes.php` | `general/reportes.blade.php` |

Los namespaces corresponden a la ubicación del componente. Por ejemplo:

```php
namespace App\Livewire\Usuarios;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Listar extends Component
{
    public string $titulo = 'Usuarios';

    public function render(): View
    {
        return view('livewire.usuarios.listar');
    }
}
```

```php
Route::get('/usuarios/listar', \App\Livewire\Usuarios\Listar::class);
```

El layout de las páginas completas se toma de `component_layout` en
`config/livewire.php` (`layouts.app`).

## Errores y protección de archivos

- Los componentes y vistas existentes no se sobrescriben.
- La ruta se inserta únicamente después de crear ambos archivos.
- Si falla un paso, se informa el error y se eliminan los archivos nuevos de
  ese intento; los archivos preexistentes se conservan.
- Una ruta duplicada, un archivo de rutas ausente o un ancla ausente se informa
  como error, no como una generación exitosa.
- El submenu puede quedar guardado aunque falle la generación. La interfaz
  muestra una advertencia; esto no significa que su pantalla esté disponible.

### Error «Invalid route action» o clase no encontrada

Verifica que una clase `App\Livewire\Carpeta\Pantalla` esté en
`app/Livewire/Carpeta/Pantalla.php`, no en `app/Http/Livewire/`.
El generador antiguo utilizaba esa carpeta de Livewire 2 con el namespace
nuevo, impidiendo que Composer cargara la clase por PSR-4.

### Permisos insuficientes

Comprueba permisos de escritura en `app/Livewire/`,
`resources/views/livewire/` y `routes/web.php`.

## Pruebas de regresión

```console
php vendor/phpunit/phpunit/phpunit tests/Unit/MenuGeneratorServiceTest.php
```

Las pruebas usan un directorio temporal aislado, sin modificar menús, rutas ni
datos reales. Cubren autoload PSR-4, renderizado Blade, títulos con comillas,
rutas anidadas, URLs inválidas, archivos existentes y fallos al registrar rutas.

## Recursos

- [Documentación de Livewire 4](https://livewire.laravel.com/docs/4.x/components)
- [Rutas de Laravel](https://laravel.com/docs/routing)
- [Sistema de menús dinámicos](./SISTEMA_MENU_DINAMICO_README.md)
