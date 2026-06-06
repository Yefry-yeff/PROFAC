# 🎯 Sistema de Gestión de Roles - Documentación Completa

## 📋 Descripción

Sistema CRUD completo para gestionar roles de usuarios en la aplicación PROFAC. Permite crear, listar, editar, eliminar y cambiar el estado de los roles.

---

## ✨ Funcionalidades Implementadas

### ✅ CRUD Completo
- **Crear** nuevos roles
- **Listar** roles con DataTables (paginación, búsqueda, ordenamiento)
- **Editar** roles existentes
- **Eliminar** roles (solo si no tienen usuarios asignados)
- **Activar/Desactivar** roles

### ✅ Información Adicional
- Cantidad de usuarios asignados al rol
- Cantidad de permisos (submenus) asignados
- Fecha de creación
- Estado visual (badge activo/inactivo)

### ✅ Validaciones
- Nombre único del rol
- No se puede eliminar rol con usuarios asignados
- Validación de formularios con Parsley
- Mensajes de confirmación con SweetAlert2

---

## 📁 Archivos Creados/Modificados

### 1. **Componente Livewire**
```
Ruta: app/Http/Livewire/Usuarios/Roles.php
```

**Métodos principales:**
- `listarRoles()` - Lista roles para DataTables con información agregada
- `guardarRol()` - Crea un nuevo rol
- `obtenerRol($id)` - Obtiene datos de un rol específico
- `actualizarRol($id)` - Actualiza un rol existente
- `cambiarEstadoRol($id)` - Activa/desactiva un rol
- `eliminarRol($id)` - Elimina un rol (si no tiene usuarios)
- `listarEstados()` - Lista los estados disponibles

### 2. **Vista Blade**
```
Ruta: resources/views/livewire/usuarios/roles.blade.php
```

**Componentes incluidos:**
- Tabla DataTables con 7 columnas
- Modal para crear/editar rol
- Modal de confirmación para eliminar
- Modal spinner de carga
- Alertas de sesión (success/error)

### 3. **JavaScript**
```
Ruta: public/js/js_proyecto/roles/roles.js
```

**Funciones principales:**
- `inicializarDataTable()` - Configura DataTable con datos del servidor
- `abrirModalRol()` - Abre modal para crear rol
- `editarRol(id)` - Carga datos y abre modal para editar
- `guardarRol()` - Envía datos para crear/actualizar rol
- `cambiarEstadoRol(id, estado)` - Cambia estado del rol
- `eliminarRol(id)` - Muestra confirmación para eliminar
- `confirmarEliminarRol()` - Ejecuta la eliminación

### 4. **Rutas**
```
Ruta: routes/web.php
```

**Rutas agregadas:**
```php
// Vista principal
GET    /usuarios/roles                - Renderiza la vista

// API para CRUD
GET    /roles/listar                  - Lista roles (DataTables)
POST   /roles/guardar                 - Crea nuevo rol
GET    /roles/obtener/{id}            - Obtiene datos de rol
PUT    /roles/actualizar/{id}         - Actualiza rol
POST   /roles/cambiar-estado/{id}     - Cambia estado
DELETE /roles/eliminar/{id}           - Elimina rol
GET    /roles/estados                 - Lista estados
```

---

## 🗄️ Estructura de Base de Datos

### Tabla `rol`
```sql
CREATE TABLE `rol` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL UNIQUE,
  `estado_id` INT NOT NULL DEFAULT 1,
  `created_at` DATETIME NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_rol_estado_idx` (`estado_id`),
  CONSTRAINT `fk_rol_estado`
    FOREIGN KEY (`estado_id`)
    REFERENCES `estado` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
);
```

**Campos:**
- `id` - Identificador único
- `nombre` - Nombre del rol (único)
- `estado_id` - Estado (1=Activo, 2=Inactivo)
- `created_at` - Fecha de creación
- `updated_at` - Última actualización

### Relaciones
- **1:N** con `users` (Un rol puede tener muchos usuarios)
- **N:1** con `estado` (Un rol tiene un estado)
- **N:M** con `sub_menu` a través de `rol_submenu` (Un rol puede tener muchos permisos)

---

## 🚀 Cómo Usar

### Acceder al Módulo

1. **URL:** `http://tu-dominio/usuarios/roles`
2. Solo usuarios con permisos de administración pueden acceder

### Crear un Nuevo Rol

1. Haz clic en **"Nuevo Rol"**
2. Llena el formulario:
   - **Nombre del Rol:** Ej: "Supervisor", "Contador", etc.
   - **Estado:** Activo o Inactivo
3. Haz clic en **"Guardar"**
4. El rol aparecerá en la tabla

### Editar un Rol

1. En la tabla, haz clic en el botón **amarillo (Editar)** ✏️
2. Modifica los campos necesarios
3. Haz clic en **"Guardar"**
4. Los cambios se reflejarán inmediatamente

### Cambiar Estado de un Rol

1. En la tabla, haz clic en:
   - Botón **rojo (Desactivar)** ❌ - Para roles activos
   - Botón **verde (Activar)** ✅ - Para roles inactivos
2. Confirma la acción
3. El estado cambiará instantáneamente

### Eliminar un Rol

1. En la tabla, haz clic en el botón **rojo (Eliminar)** 🗑️
   - Solo aparece si el rol NO tiene usuarios asignados
2. Confirma la eliminación
3. El rol será eliminado permanentemente

---

## ⚙️ Configuración Requerida

### 1. Dependencias JavaScript

Asegúrate de que estén disponibles:
- **jQuery** (3.x)
- **DataTables** (1.10+)
- **Axios** (Para peticiones AJAX)
- **Parsley** (Validación de formularios)
- **SweetAlert2** (Alertas modernas)

### 2. Permisos de Usuario

Para acceder al módulo, el usuario debe tener:
- Rol de Administrador (rol_id = 1)
- O permiso específico asignado en `rol_submenu`

### 3. Configuración de Autoload

Ejecutar una vez:
```bash
cd c:\laragon\www\Valencia\PROFAC\SISTEMA\profac-app
composer dump-autoload
```

---

## 📊 Ejemplo de Uso

### Crear Rol "Supervisor de Ventas"

```javascript
// 1. Abrir modal
abrirModalRol();

// 2. Llenar formulario
$('#rolNombre').val('Supervisor de Ventas');
$('#rolEstado').val('1'); // Activo

// 3. Guardar
$('#formRol').submit();

// Resultado: Rol creado con ID automático
```

### Consultar Roles via API

```javascript
// Listar todos los roles
axios.get('/roles/listar')
  .then(response => {
    console.log(response.data);
  });

// Obtener rol específico
axios.get('/roles/obtener/5')
  .then(response => {
    console.log(response.data.data);
  });
```

---

## 🔒 Reglas de Negocio

### ✅ Permitido
- Crear múltiples roles con nombres únicos
- Editar el nombre de un rol existente
- Cambiar estado de cualquier rol
- Eliminar roles sin usuarios asignados

### ❌ No Permitido
- Crear roles con nombres duplicados
- Eliminar roles con usuarios asignados
- Dejar el nombre del rol vacío

---

## 🐛 Solución de Problemas

### Error: "Class Rol not found"
**Causa:** Autoload no actualizado  
**Solución:**
```bash
composer dump-autoload
```

### Error 404 en rutas
**Causa:** Rutas no agregadas en web.php  
**Solución:** Verificar que las rutas estén dentro del grupo `Route::middleware(['auth:sanctum', 'verified'])`

### DataTable no carga datos
**Causa:** Ruta de DataTables incorrecta  
**Solución:** Verificar que la ruta `/roles/listar` esté accesible

### No se puede eliminar rol
**Causa:** El rol tiene usuarios asignados  
**Solución:** Reasignar los usuarios a otro rol primero

---

## 📝 Validaciones Implementadas

### Lado del Servidor (PHP)
```php
'nombre' => 'required|string|max:255|unique:rol,nombre',
'estado_id' => 'required|integer|exists:estado,id'
```

### Lado del Cliente (JavaScript)
- Campo obligatorio: `required`
- Longitud máxima: `data-parsley-maxlength="255"`
- Mensajes personalizados en español

---

## 🎨 Interfaz de Usuario

### Tabla de Roles
| ID | Nombre | Estado | # Usuarios | # Permisos | Fecha | Acciones |
|----|--------|--------|------------|------------|-------|----------|
| 1  | Admin  | ✅ Activo | 5 | 25 | 15/12/2025 | 🟡 🔴 |
| 2  | Vendedor | ✅ Activo | 12 | 8 | 10/01/2025 | 🟡 🔴 🔴 |

### Badges de Estado
- **Activo:** <span style="background:green;color:white;padding:2px 8px;border-radius:3px">Activo</span>
- **Inactivo:** <span style="background:red;color:white;padding:2px 8px;border-radius:3px">Inactivo</span>

### Botones de Acción
- 🟡 **Editar** - Botón amarillo con ícono de lápiz
- 🔴 **Desactivar** - Botón rojo con ícono X (solo activos)
- 🟢 **Activar** - Botón verde con ícono ✓ (solo inactivos)
- 🔴 **Eliminar** - Botón rojo con ícono basura (solo sin usuarios)

---

## 🔗 Relación con Otros Módulos

### Gestión de Usuarios
- Los usuarios tienen asignado un `rol_id`
- Al cambiar rol de usuario, se actualiza su acceso

### Gestión de Menús
- Los permisos se asignan por rol en el módulo de menús
- Un rol puede tener múltiples permisos (submenus)

### Sistema de Autenticación
- Los menús se filtran según el rol del usuario logueado
- Los roles inactivos no pueden asignarse a usuarios

---

## 📈 Mejoras Futuras (Opcional)

- [ ] Duplicar rol con sus permisos
- [ ] Historial de cambios en roles
- [ ] Exportar lista de roles a Excel/PDF
- [ ] Asignación masiva de permisos
- [ ] Vista de árbol de permisos por rol
- [ ] Roles heredados (jerarquía)

---

## ✅ Checklist de Verificación

Antes de usar en producción, verificar:

- [x] Controlador Livewire creado
- [x] Vista Blade completa
- [x] JavaScript funcional
- [x] Rutas agregadas en web.php
- [x] Modelo Rol configurado
- [x] DataTables inicializado
- [x] Validaciones implementadas
- [x] Permisos de acceso configurados
- [x] Pruebas de CRUD realizadas

---

## 📞 Soporte

Para problemas o dudas:
1. Revisar esta documentación
2. Verificar logs en `storage/logs/laravel.log`
3. Consultar con el equipo de desarrollo

---

**Última actualización:** 15 de Diciembre 2025  
**Versión:** 1.0.0  
**Autor:** Sistema PROFAC
