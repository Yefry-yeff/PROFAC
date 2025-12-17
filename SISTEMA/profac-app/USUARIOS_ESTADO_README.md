# Módulo de Estado para Usuarios - Guía de Implementación

## ✅ Cambios Realizados

Se modificó el módulo de usuarios para incluir gestión de **estado** (Activo/Inactivo).

## 📋 Estructura de Base de Datos

### Tabla `estado` (Ya existente en BD)
```sql
CREATE TABLE `estado` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(45) NOT NULL,
  `created_at` DATETIME NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
);
```

**Estados:**
- `1` = Activo
- `2` = Inactivo

### Nueva columna en `users`
```sql
ALTER TABLE `users` 
ADD COLUMN `estado_id` INT NOT NULL DEFAULT 1 AFTER `rol_id`;
```

## 🔧 Instalación

### Opción 1: Script SQL Simple (RECOMENDADO)
```bash
mysql -u root -p profac_app < database/migrations/EJECUTAR_agregar_estado_users.sql
```

### Opción 2: Migraciones Laravel
```bash
cd SISTEMA/profac-app
php artisan migrate
```

### Opción 3: Ejecutar SQL manualmente
```sql
-- 1. Insertar estados si no existen
INSERT IGNORE INTO `estado` (`id`, `descripcion`, `created_at`, `updated_at`) 
VALUES (1, 'Activo', NOW(), NOW()), (2, 'Inactivo', NOW(), NOW());

-- 2. Agregar columna
ALTER TABLE `users` ADD COLUMN `estado_id` INT NOT NULL DEFAULT 1 AFTER `rol_id`;

-- 3. Actualizar usuarios existentes
UPDATE `users` SET `estado_id` = 1;

-- 4. Agregar índice y foreign key
ALTER TABLE `users` ADD INDEX `fk_users_estado1_idx` (`estado_id`);
ALTER TABLE `users` ADD CONSTRAINT `fk_users_estado1`
  FOREIGN KEY (`estado_id`) REFERENCES `estado` (`id`)
  ON DELETE RESTRICT ON UPDATE CASCADE;
```

## 📁 Archivos Modificados

### Backend (PHP/Laravel)

1. **app/Models/usuario.php**
   - ✅ Agregado `estado_id` al fillable
   - ✅ Relación con modelo Estado
   - ✅ Métodos: `darDeBaja()`, `activar()`
   - ✅ Scopes: `activos()`, `inactivos()`

2. **app/Models/Estado.php** (NUEVO)
   - ✅ Modelo para tabla estado

3. **app/Http/Livewire/Usuarios/ListarUsuarios.php**
   - ✅ `listarUsuarios()`: Incluye estado en query
   - ✅ `guardarUsuarios()`: Asigna estado Activo por defecto
   - ✅ `baja()`: Cambia estado a Inactivo
   - ✅ `activar()`: Cambia estado a Activo (NUEVO)
   - ✅ Opciones del menú cambian según estado

4. **routes/web.php**
   - ✅ Nueva ruta: `/usuario/activar/{idUsuario}`

### Frontend (JavaScript)

5. **public/js/js_proyecto/usuarios/usuarios.js**
   - ✅ Corregida URL de DataTables (CORS)
   - ✅ Agregada columna "Estado" con badges
   - ✅ Función `baja()` con confirmación
   - ✅ Función `activar()` (NUEVA)
   - ✅ Recarga de tabla sin refresh de página

## 🎯 Funcionalidades

### Dar de Baja Usuario
```php
// Desde controlador
$this->baja($idUsuario);

// Desde modelo
$usuario->darDeBaja();
```

### Activar Usuario
```php
// Desde controlador
$this->activar($idUsuario);

// Desde modelo  
$usuario->activar();
```

### Consultas con Estado
```php
// Solo activos
$activos = usuario::activos()->get();

// Solo inactivos
$inactivos = usuario::inactivos()->get();

// Con relación
$usuario = usuario::with('estado')->find($id);
echo $usuario->estado->descripcion; // "Activo" o "Inactivo"
```

## 🎨 Interfaz de Usuario

### Tabla de Usuarios
- ✅ Columna "Estado" con badges de colores
  - Verde: Activo
  - Rojo: Inactivo
- ✅ Menú contextual dinámico según estado
  - Usuario activo: opción "Dar de baja"
  - Usuario inactivo: opción "Activar"

### Confirmaciones
- ✅ SweetAlert2 para confirmar acciones
- ✅ Recarga automática de tabla sin refresh

## 🐛 Correcciones de Errores

### Error CORS (DataTables)
**Problema resuelto:** Cambio de `//cdn.datatables.net/...` a `https://cdn.datatables.net/...`

### Error 404 Storage
**No afecta funcionalidad:** Advertencias de recursos faltantes que no impactan el módulo de usuarios.

## 📝 Notas Importantes

1. **Sin eliminación de datos**: Los usuarios mantienen toda su información
2. **Valor por defecto**: Nuevos usuarios = Activo (1)
3. **Usuarios existentes**: Se actualizan a Activo automáticamente
4. **Protección BD**: Foreign key con `RESTRICT` previene eliminar estados en uso

## 🔍 Verificación Post-Instalación

```sql
-- Ver estados del sistema
SELECT * FROM estado;

-- Ver distribución de usuarios
SELECT 
  e.descripcion as estado,
  COUNT(u.id) as cantidad
FROM users u
INNER JOIN estado e ON u.estado_id = e.id
GROUP BY e.id, e.descripcion;
```

## 🆘 Solución de Problemas

**Error: "Column 'estado_id' already exists"**
- La columna ya existe, no es necesario ejecutar el script nuevamente

**Error: "Foreign key already exists"**
- La foreign key ya está creada, sistema funcionando correctamente

**No aparece la columna Estado en la tabla**
- Limpiar caché del navegador (Ctrl+F5)
- Verificar que `usuarios.js` esté actualizado
