# Generación Automática de Archivos para Menús - PROFAC

## 📋 Descripción

Esta funcionalidad permite **generar automáticamente** los archivos necesarios (controlador, vista y ruta) cuando se crea un nuevo submenu en el sistema de gestión de menús dinámicos.

## ✨ ¿Qué se genera automáticamente?

Cuando creas un submenu con la opción "Generar archivos automáticamente" activada, el sistema crea:

1. **Controlador Livewire** (`app/Http/Livewire/`)
   - Con estructura básica lista para usar
   - Métodos de ejemplo: `listarDatos()`, `guardar()`
   - Manejo de errores incluido

2. **Vista Blade** (`resources/views/livewire/`)
   - Layout responsive con breadcrumbs
   - Alertas de éxito/error
   - Tabla con DataTables lista
   - Modal de ejemplo

3. **Ruta** (texto generado para copiar en `routes/web.php`)
   - Formato correcto con namespace completo
   - Lista para pegar en el archivo de rutas

## 🚀 Cómo usar

### Paso 1: Crear un Submenu

1. Ve a la **Gestión de Menús** (`/menu/gestion`)
2. Haz clic en **"Nuevo Submenu"**
3. Llena el formulario:
   - **Menú Principal**: Selecciona el menú padre
   - **Nombre del Submenu**: Ej: "Gestión de Usuarios"
   - **URL/Ruta**: Ej: `usuarios/gestion` (sin `/` al inicio)
   - **Orden**: Número de orden
   - **Estado**: Activo/Inactivo
   - **Roles con Acceso**: Selecciona los roles que pueden ver este submenu
   - ✅ **Generar archivos automáticamente**: Déjalo activado

4. Haz clic en **"Guardar"**

### Paso 2: Revisar los archivos generados

El sistema mostrará un mensaje con:
- ✅ Lista de archivos creados
- 📝 Ruta generada para agregar a `web.php`
- ⚠️ Advertencias (si algún archivo ya existía)

**Ejemplo de mensaje:**

```
Submenu creado correctamente - Se generaron 2 archivo(s).

📁 Archivos creados:
✓ C:\...\app\Http\Livewire\Usuarios\Gestion.php
✓ C:\...\resources\views\livewire\usuarios\gestion.blade.php

🔗 Agrega esta ruta a routes/web.php:
Route::get('/usuarios/gestion', App\Http\Livewire\Usuarios\Gestion::class);
```

### Paso 3: Agregar la ruta

1. Abre el archivo `routes/web.php`
2. Dentro del grupo de rutas protegidas (`Route::middleware(['auth:sanctum', 'verified'])->group(function () {`), agrega la ruta generada:

```php
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // ... otras rutas ...
    
    // Nueva ruta generada
    Route::get('/usuarios/gestion', App\Http\Livewire\Usuarios\Gestion::class);
});
```

3. Guarda el archivo

### Paso 4: Personalizar los archivos

Los archivos generados son plantillas básicas. Personalízalos según tus necesidades:

#### Controlador (`app/Http/Livewire/Usuarios/Gestion.php`)
```php
public function listarDatos()
{
    try {
        // Cambia 'tu_tabla' por tu tabla real
        $datos = DB::table('usuarios')
            ->select('id', 'nombre', 'email', 'estado_id')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $datos
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'mensaje' => 'Error al listar datos: ' . $e->getMessage()
        ], 500);
    }
}
```

#### Vista (`resources/views/livewire/usuarios/gestion.blade.php`)
- Personaliza la tabla según tus campos
- Agrega modales para crear/editar
- Agrega scripts JavaScript si necesitas DataTables o validaciones

## 📝 Convenciones de Nombres

El sistema convierte la URL en nombres de archivos siguiendo estas reglas:

| URL ingresada | Controlador | Vista | Namespace |
|---------------|-------------|-------|-----------|
| `usuarios/listar` | `Usuarios\Listar.php` | `usuarios/listar.blade.php` | `App\Http\Livewire\Usuarios\Listar` |
| `inventario/productos` | `Inventario\Productos.php` | `inventario/productos.blade.php` | `App\Http\Livewire\Inventario\Productos` |
| `reportes` | `General\Reportes.php` | `general/reportes.blade.php` | `App\Http\Livewire\General\Reportes` |

## ⚠️ Advertencias importantes

1. **No sobrescribe archivos existentes**
   - Si el controlador o vista ya existe, mostrará una advertencia
   - Los archivos existentes NO se modifican

2. **La ruta NO se agrega automáticamente**
   - Por seguridad, debes agregar la ruta manualmente a `web.php`
   - El sistema solo genera el texto que debes copiar

3. **Archivos son plantillas básicas**
   - Debes personalizarlos según tu lógica de negocio
   - Incluyen comentarios `// TODO:` donde necesitas agregar código

## 🔧 Estructura de archivos generados

### Controlador Livewire
```php
<?php

namespace App\Http\Livewire\Carpeta;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NombreClase extends Component
{
    public $titulo = 'Título del Módulo';

    public function mount() { }

    public function render()
    {
        return view('livewire.carpeta.nombreclase');
    }

    public function listarDatos() { }
    public function guardar($request) { }
}
```

### Vista Blade
```blade
<div>
    <div class="row wrapper border-bottom white-bg page-heading">
        <!-- Breadcrumbs y título -->
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <!-- Alertas de sesión -->
        
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <!-- Contenido: tabla, botones, etc. -->
                </div>
            </div>
        </div>
    </div>
</div>
```

## 🎯 Casos de uso

### ✅ Cuándo usar la generación automática

- Estás creando un módulo nuevo desde cero
- Quieres una estructura base rápida
- El módulo seguirá el patrón estándar de la aplicación

### ❌ Cuándo NO usar la generación automática

- El módulo ya tiene archivos creados manualmente
- Necesitas una estructura muy personalizada
- Solo quieres agregar una opción al menú sin crear archivos nuevos

## 🐛 Solución de problemas

### Error: "El controlador ya existe"
**Causa**: Ya existe un archivo con ese nombre
**Solución**: 
- Desmarca "Generar archivos automáticamente"
- O usa una URL diferente para evitar conflicto de nombres

### Error: "No se puede escribir en el directorio"
**Causa**: Permisos insuficientes en las carpetas
**Solución**: Verifica permisos de escritura en:
- `app/Http/Livewire/`
- `resources/views/livewire/`

### El submenu aparece pero da error 404
**Causa**: No agregaste la ruta a `web.php`
**Solución**: Copia la ruta generada y pégala en `routes/web.php`

## 📚 Recursos adicionales

- [Documentación de Livewire](https://laravel-livewire.com/docs)
- [Guía de Rutas en Laravel](https://laravel.com/docs/routing)
- [Sistema de Menús Dinámicos](./SISTEMA_MENU_DINAMICO_README.md)

## 🔄 Historial de cambios

### v1.0.0 (Diciembre 2025)
- ✨ Implementación inicial de generación automática
- ✅ Generación de controladores Livewire
- ✅ Generación de vistas Blade
- ✅ Generación de texto para rutas
- ✅ Checkbox opcional en el formulario

---

**Nota**: Esta funcionalidad acelera el desarrollo pero los archivos generados son plantillas básicas. Siempre revisa y personaliza el código generado según los requerimientos específicos de tu módulo.
