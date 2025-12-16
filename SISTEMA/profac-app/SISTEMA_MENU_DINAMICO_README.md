# Sistema de Menús Dinámicos - PROFAC

## Descripción General

Este sistema permite gestionar los menús de la aplicación de forma dinámica desde la base de datos, eliminando la necesidad de modificar código para agregar, editar o eliminar menús y permisos.

## Características Principales

- ✅ **Gestión completa de menús y submenus** desde interfaz web
- ✅ **Control de permisos por rol** (relación muchos a muchos)
- ✅ **Menús automáticos**: Si un usuario tiene acceso a un submenu, el menú padre aparece automáticamente
- ✅ **Ordenamiento personalizado** de menús y submenus
- ✅ **Estados activo/inactivo** para control de visibilidad
- ✅ **Iconos Font Awesome** para personalización visual
- ✅ **DataTables** para gestión eficiente de datos

## Estructura de Base de Datos

### Tabla `menu`
```sql
- id (INT, PK)
- icon (VARCHAR) - Clase de Font Awesome
- nombre_menu (VARCHAR) - Nombre del menú
- orden (INT) - Orden de visualización
- estado_id (INT, FK → estado)
```

### Tabla `sub_menu`
```sql
- id (INT, PK)
- url (VARCHAR) - Ruta del submenu
- nombre (VARCHAR) - Nombre del submenu
- menu_id (INT, FK → menu) CASCADE DELETE
- orden (INT) - Orden dentro del menú
- estado_id (INT, FK → estado)
- icono (VARCHAR, NULLABLE) - Icono opcional
```

### Tabla `rol_submenu` (Pivote)
```sql
- id (INT, PK)
- rol_id (INT, FK → rol) CASCADE DELETE
- sub_menu_id (INT, FK → sub_menu) CASCADE DELETE
- UNIQUE (rol_id, sub_menu_id)
```

## Instalación y Configuración

### 1. Ejecutar Migraciones

```bash
# Opción 1: Ejecutar el SQL directo
mysql -u usuario -p profac_app < database/migrations/crear_sistema_menu_dinamico.sql

# Opción 2: Ejecutar migraciones Laravel
php artisan migrate
```

### 2. Poblar Datos Iniciales (Seeder)

```bash
php artisan db:seed --class=MenuSeeder
```

Este seeder migra los menús actuales del código a la base de datos con sus permisos correspondientes.

### 3. Integrar Menú Dinámico en la Vista

Reemplazar el código estático en `navigation-menu.blade.php` con:

```blade
{{-- Menús estáticos anteriores --}}
@if (Auth::user()->rol_id == '1')
    {{-- ... código estático ... --}}
@endif

{{-- REEMPLAZAR CON: --}}
@include('partials.menu-dinamico')
```

## Uso del Sistema

### Acceso a la Gestión de Menús

1. Navegar a: `/menu/gestion`
2. Solo usuarios con rol Administrador (rol_id = 1) tienen acceso por defecto

### Crear un Nuevo Menú

1. Click en botón **"Nuevo Menú"**
2. Completar formulario:
   - **Nombre del Menú**: Ej. "Inventario"
   - **Icono**: Clase Font Awesome, Ej. `fa fa-boxes` ([Ver iconos](https://fontawesome.com/v4/icons/))
   - **Orden**: Número para ordenar (menor = más arriba)
   - **Estado**: Activo/Inactivo
3. Click en **"Guardar"**

### Crear un Nuevo Submenu

1. Click en botón **"Nuevo Submenu"**
2. Completar formulario:
   - **Menú Principal**: Seleccionar menú padre
   - **Nombre del Submenu**: Ej. "Listar Productos"
   - **URL/Ruta**: Ruta sin "/" inicial, Ej. `productos/listar`
   - **Icono** (Opcional): Clase Font Awesome
   - **Orden**: Número para ordenar dentro del menú
   - **Estado**: Activo/Inactivo
   - **Roles con Acceso**: Seleccionar uno o más roles (obligatorio)
3. Click en **"Guardar"**

> **Importante**: Al asignar submenus a roles, el menú padre aparecerá automáticamente para esos usuarios.

### Editar Menús/Submenus

1. Click en botón **"Editar"** (icono lápiz)
2. Modificar datos necesarios
3. Click en **"Guardar"**

### Cambiar Estado (Activar/Desactivar)

- Click en botón **"Estado"** (icono ✓ o ✗)
- Elementos inactivos no se mostrarán en el sidebar

### Eliminar

- **Menús**: Solo se pueden eliminar si no tienen submenus asociados
- **Submenus**: Se pueden eliminar en cualquier momento (se eliminan automáticamente las relaciones con roles)

## Lógica del Sistema

### Cómo Funciona la Visualización de Menús

```php
// MenuHelper::getMenusUsuario()
1. Obtiene el rol_id del usuario autenticado
2. Busca todos los submenus activos asignados a ese rol
3. Agrupa submenus por su menú padre (solo menús con submenus visibles)
4. Ordena por campo "orden"
5. Retorna colección de menús con sus submenus accesibles
```

### Ejemplo Práctico

**Escenario**: Usuario con rol "Vendedor" (rol_id = 5)

**Configuración en BD**:
- Submenu "Lista de Clientes" → Roles: [1, 5]
- Submenu "Crear Venta" → Roles: [1, 4, 5]
- Submenu "Mis comisiones" → Roles: [1, 5, 8, 9]

**Resultado**: El usuario verá:
- Menú "Clientes" (porque tiene acceso a "Lista de Clientes")
- Menú "Ventas" (porque tiene acceso a "Crear Venta")
- Menú "Comisiones" (porque tiene acceso a "Mis comisiones")

## Modelos y Relaciones

### Menu Model
```php
// Relaciones
$menu->submenus // HasMany
$menu->estado   // BelongsTo

// Métodos
Menu::activos() // Scope
Menu::getMenusParaRol($rolId) // Static
```

### SubMenu Model
```php
// Relaciones
$submenu->menu  // BelongsTo
$submenu->roles // BelongsToMany
$submenu->estado // BelongsTo

// Métodos
SubMenu::activos() // Scope
$submenu->tieneAcceso($rolId) // Boolean
```

### Rol Model
```php
// Relaciones
$rol->submenus // BelongsToMany
$rol->usuarios // HasMany
$rol->estado   // BelongsTo

// Métodos
$rol->getMenusConSubmenus() // Collection
```

## Rutas Disponibles

```php
// Vista principal
GET  /menu/gestion

// Menús
POST /menu/guardar
GET  /menu/obtener/{id}
PUT  /menu/actualizar/{id}

// Submenus
POST /submenu/guardar
GET  /submenu/obtener/{id}
PUT  /submenu/actualizar/{id}
```

## Archivos Creados/Modificados

### Backend
- `app/Models/Menu.php` - Modelo de menú
- `app/Models/SubMenu.php` - Modelo de submenu
- `app/Models/Rol.php` - Modelo de rol (actualizado)
- `app/Http/Controllers/MenuController.php` - Controlador AJAX
- `app/Http/Controllers/MenuHelper.php` - Helper para cargar menús
- `app/Http/Livewire/Menu/GestionMenu.php` - Componente Livewire

### Frontend
- `resources/views/livewire/menu/gestion-menu.blade.php` - Vista principal
- `resources/views/partials/menu-dinamico.blade.php` - Partial del sidebar
- `public/js/js_proyecto/menu/gestion-menu.js` - JavaScript

### Base de Datos
- `database/migrations/crear_sistema_menu_dinamico.sql` - SQL completo
- `database/migrations/2025_12_15_000003_create_menu_table.php`
- `database/migrations/2025_12_15_000004_create_sub_menu_table.php`
- `database/migrations/2025_12_15_000005_create_rol_submenu_table.php`
- `database/seeders/MenuSeeder.php` - Seeder de datos iniciales

### Rutas
- `routes/web.php` - Rutas agregadas

## Validaciones y Restricciones

### Validaciones Backend
- ✅ Nombre de menú obligatorio (máx 255 caracteres)
- ✅ Icono obligatorio para menús
- ✅ URL obligatoria para submenus
- ✅ Al menos un rol debe estar asignado a cada submenu
- ✅ Orden debe ser número entero positivo
- ✅ Foreign keys validan existencia de estados y menús

### Restricciones de Eliminación
- ❌ No se puede eliminar un menú con submenus asociados
- ✅ Al eliminar un submenu, se eliminan automáticamente sus relaciones con roles
- ✅ Al eliminar un menú (sin submenus), se eliminan sus relaciones
- ✅ CASCADE DELETE configurado en foreign keys

## Troubleshooting

### Problema: No aparecen menús en el sidebar

**Solución**:
1. Verificar que el usuario tenga `rol_id` asignado
2. Verificar que existan submenus activos (`estado_id = 1`) para ese rol
3. Revisar tabla `rol_submenu` para confirmar asignaciones
4. Ejecutar en tinker:
```php
$menus = \App\Http\Controllers\MenuHelper::getMenusUsuario();
dd($menus);
```

### Problema: Error "Foreign key constraint fails"

**Solución**:
1. Verificar que exista la tabla `estado` con registros (id=1 Activo, id=2 Inactivo)
2. Verificar que exista la tabla `rol` con registros
3. Ejecutar migraciones en orden correcto

### Problema: Submenu no se guarda con roles

**Solución**:
1. Verificar que se envíe array de roles en el request
2. Verificar validación: al menos un rol debe estar seleccionado
3. Revisar consola del navegador para errores JavaScript

## Extensiones Futuras

Posibles mejoras al sistema:

1. **Permisos granulares**: CRUD por submenu (ver, crear, editar, eliminar)
2. **Menús multinivel**: Soporte para submenus anidados (tercer nivel)
3. **Copia de permisos**: Clonar configuración de un rol a otro
4. **Historial de cambios**: Auditoría de modificaciones
5. **Importación/Exportación**: JSON para migrar configuración entre ambientes
6. **Vista previa**: Simular cómo vería el menú otro rol
7. **Drag & Drop**: Reorganizar orden de menús visualmente

## Soporte y Contacto

Para consultas sobre este sistema, contactar al equipo de desarrollo.

## Notas de Versión

**Versión 1.0.0** - 15 de diciembre de 2025
- Implementación inicial del sistema de menús dinámicos
- Migración de menús estáticos a base de datos
- Interfaz de gestión completa
- Documentación completa

---

**Fecha de creación**: 15 de diciembre de 2025  
**Última actualización**: 15 de diciembre de 2025  
**Autor**: Sistema PROFAC v5.0.0.1
