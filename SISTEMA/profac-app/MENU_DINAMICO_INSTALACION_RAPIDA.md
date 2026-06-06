# Guía Rápida - Implementación Sistema de Menús Dinámicos

## 🚀 Pasos de Instalación (5 minutos)

### 1️⃣ Ejecutar Migraciones

Opción A - SQL Directo (Recomendado):
```bash
# Conectar a MySQL y ejecutar:
mysql -u root -p profac_app < database/migrations/crear_sistema_menu_dinamico.sql
```

Opción B - Artisan:
```bash
php artisan migrate
```

### 2️⃣ Poblar Datos Iniciales

```bash
php artisan db:seed --class=MenuSeeder
```

### 3️⃣ Integrar en la Vista Principal

Abrir: `resources/views/navigation-menu.blade.php`

**ANTES** (línea ~234):
```blade
@if (Auth::user()->rol_id == '1')
    <li>
        <a href="{{ route('dashboard') }}">...</a>
    </li>
    <li>
        <a><i class="fa-solid fa-user">...</i></a>
    </li>
    <!-- ... más menús estáticos ... -->
@endif

@if (Auth::user()->rol_id == '2' or Auth::user()->rol_id == '1')
    <!-- ... más menús ... -->
@endif
```

**DESPUÉS** (reemplazar TODO el bloque de menús estáticos con):
```blade
{{-- Menús dinámicos desde base de datos --}}
@include('partials.menu-dinamico')
```

### 4️⃣ Verificar Instalación

1. Iniciar servidor:
```bash
php artisan serve
```

2. Acceder a gestión de menús:
```
http://localhost:8000/menu/gestion
```

3. Verificar que aparezcan los menús en el sidebar según tu rol

## ✅ Checklist de Verificación

- [ ] Tablas creadas: `menu`, `sub_menu`, `rol_submenu`
- [ ] Datos insertados (ver en MySQL/phpMyAdmin)
- [ ] Archivo `menu-dinamico.blade.php` existe en `resources/views/partials/`
- [ ] Menús aparecen en sidebar según rol del usuario
- [ ] Acceso a `/menu/gestion` funciona (solo admin)
- [ ] Se pueden crear nuevos menús/submenus

## 🔧 Prueba Rápida

### Verificar en Base de Datos:
```sql
-- Ver menús creados
SELECT * FROM menu;

-- Ver submenus y sus menús
SELECT sm.id, m.nombre_menu, sm.nombre, sm.url 
FROM sub_menu sm 
INNER JOIN menu m ON sm.menu_id = m.id;

-- Ver asignaciones de roles
SELECT r.nombre as rol, sm.nombre as submenu, m.nombre_menu as menu
FROM rol_submenu rs
INNER JOIN rol r ON rs.rol_id = r.id
INNER JOIN sub_menu sm ON rs.sub_menu_id = sm.id
INNER JOIN menu m ON sm.menu_id = m.id
ORDER BY r.nombre, m.orden, sm.orden;
```

### Verificar en Laravel Tinker:
```bash
php artisan tinker
```

```php
// Ver menús del usuario actual
$menus = \App\Http\Controllers\MenuHelper::getMenusUsuario();
dd($menus);

// Ver menús de un rol específico
$menus = \App\Models\Menu::getMenusParaRol(1); // 1 = Administrador
dd($menus);
```

## 📋 Uso Básico

### Crear un Nuevo Menú:
1. Ir a `/menu/gestion`
2. Click "Nuevo Menú"
3. Llenar:
   - Nombre: "Mi Nuevo Menú"
   - Icono: `fa fa-star` ([ver iconos](https://fontawesome.com/v4/icons/))
   - Orden: 12 (para que aparezca al final)
   - Estado: Activo
4. Guardar

### Crear un Submenu:
1. Click "Nuevo Submenu"
2. Llenar:
   - Menú Principal: Seleccionar menú padre
   - Nombre: "Mi Submenu"
   - URL: `mi-ruta/listar` (sin "/" inicial)
   - Orden: 1
   - Estado: Activo
   - **Roles**: ✅ Seleccionar al menos uno
3. Guardar

### Asignar a Múltiples Roles:
Al crear/editar un submenu, simplemente marca los checkboxes de todos los roles que deben tener acceso.

## 🆘 Problemas Comunes

### ❌ "Call to undefined method getMenusUsuario()"
**Solución**: Verificar que existe `app/Http/Controllers/MenuHelper.php`

### ❌ "Table 'menu' doesn't exist"
**Solución**: Ejecutar las migraciones (paso 1)

### ❌ No aparecen menús en el sidebar
**Solución**: 
1. Ejecutar seeder (paso 2)
2. Verificar que usuario tenga `rol_id` asignado
3. Verificar que partial esté incluido en navigation-menu.blade.php

### ❌ "Foreign key constraint fails"
**Solución**: Verificar que exista tabla `estado` con registros (id=1 y id=2)

### ❌ No aparece "/menu/gestion" en menú
**Solución**: 
1. Ejecutar seeder que ya incluye este submenu
2. O crear manualmente:
   - Menú: "Usuarios"
   - Submenu: "Gestión de Menús", URL: `menu/gestion`, Roles: [1]

## 📖 Documentación Completa

Ver archivo: `SISTEMA_MENU_DINAMICO_README.md` para:
- Arquitectura detallada
- Modelos y relaciones
- API de rutas
- Extensiones futuras
- Troubleshooting avanzado

## 🎯 Resultado Esperado

Después de completar estos pasos:

✅ El sidebar mostrará menús dinámicos según el rol del usuario  
✅ Los administradores verán opción "Gestión de Menús"  
✅ Se pueden agregar/editar/eliminar menús sin tocar código  
✅ Los permisos se gestionan mediante checkboxes de roles  
✅ Los menús se muestran automáticamente cuando el usuario tiene acceso a sus submenus  

---

**Tiempo estimado**: 5-10 minutos  
**Requisitos**: MySQL, Laravel, tablas `estado` y `rol` existentes
