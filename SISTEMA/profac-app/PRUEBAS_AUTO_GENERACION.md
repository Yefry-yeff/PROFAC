# 🧪 Guía de Prueba - Generación Automática de Menús

## Prueba rápida del sistema

Sigue estos pasos para probar la nueva funcionalidad de generación automática de archivos:

### 1️⃣ Crear un submenu de prueba

1. **Accede a la gestión de menús**
   ```
   URL: http://tu-dominio/menu/gestion
   ```

2. **Crea un nuevo submenu de prueba**
   - Haz clic en el botón **"Nuevo Submenu"**
   - Llena el formulario con estos datos de prueba:

   ```
   Menú Principal: [Selecciona cualquier menú existente]
   Nombre del Submenu: Prueba Auto-Generación
   URL/Ruta: pruebas/autogen
   Icono: fa fa-flask
   Orden: 999
   Estado: Activo
   Roles con Acceso: [Marca tu rol actual]
   ✅ Generar archivos automáticamente: [ACTIVADO]
   ```

3. **Haz clic en "Guardar"**

### 2️⃣ Verificar archivos generados

El sistema debería mostrar un mensaje similar a:

```
✅ Submenu creado correctamente - Se generaron 2 archivo(s).

📁 Archivos creados:
✓ C:\laragon\www\Valencia\PROFAC\SISTEMA\profac-app\app\Http\Livewire\Pruebas\Autogen.php
✓ C:\laragon\www\Valencia\PROFAC\SISTEMA\profac-app\resources\views\livewire\pruebas\autogen.blade.php

🔗 Agrega esta ruta a routes/web.php:
Route::get('/pruebas/autogen', App\Http\Livewire\Pruebas\Autogen::class);
```

### 3️⃣ Verificar los archivos creados

**Archivo 1: Controlador Livewire**
```bash
Ruta: app/Http/Livewire/Pruebas/Autogen.php
```

Abre el archivo y verifica que contiene:
- ✅ Namespace correcto: `App\Http\Livewire\Pruebas`
- ✅ Clase: `Autogen`
- ✅ Método `render()` que retorna la vista
- ✅ Métodos de ejemplo: `listarDatos()`, `guardar()`

**Archivo 2: Vista Blade**
```bash
Ruta: resources/views/livewire/pruebas/autogen.blade.php
```

Abre el archivo y verifica que contiene:
- ✅ Estructura HTML básica
- ✅ Breadcrumbs
- ✅ Alertas de sesión
- ✅ Tabla con DataTable
- ✅ Sección de scripts

### 4️⃣ Agregar la ruta

1. Abre el archivo: `routes/web.php`

2. Busca la sección de rutas protegidas (aprox. línea 126):
   ```php
   Route::middleware(['auth:sanctum', 'verified'])->group(function () {
   ```

3. Agrega la ruta generada al final del grupo:
   ```php
   Route::middleware(['auth:sanctum', 'verified'])->group(function () {
       // ... otras rutas existentes ...
       
       // Ruta de prueba - Auto-generación
       Route::get('/pruebas/autogen', App\Http\Livewire\Pruebas\Autogen::class);
   });
   ```

4. Guarda el archivo

### 5️⃣ Probar el acceso

1. **Recarga la página** o haz logout/login
2. Verás el nuevo submenu en el menú lateral
3. Haz clic en **"Prueba Auto-Generación"**
4. Deberías ver la página generada automáticamente

### 6️⃣ Limpieza (opcional)

Si solo era una prueba, puedes eliminar:

1. **El submenu de la base de datos**
   - Ve a Gestión de Menús
   - Elimina el submenu "Prueba Auto-Generación"

2. **Los archivos generados**
   ```bash
   # Elimina estos archivos:
   app/Http/Livewire/Pruebas/Autogen.php
   resources/views/livewire/pruebas/autogen.blade.php
   
   # Elimina la carpeta si está vacía:
   app/Http/Livewire/Pruebas/
   resources/views/livewire/pruebas/
   ```

3. **La ruta en web.php**
   - Elimina la línea de la ruta que agregaste

---

## 🎯 Casos de prueba adicionales

### Prueba 1: URL simple (sin subcarpeta)
```
URL: dashboard-admin
Resultado esperado:
- Controlador: App\Http\Livewire\General\DashboardAdmin.php
- Vista: resources/views/livewire/general/dashboardadmin.blade.php
```

### Prueba 2: URL con múltiples niveles
```
URL: administracion/usuarios/permisos
Resultado esperado:
- Controlador: App\Http\Livewire\Administracion\Usuarios\Permisos.php (⚠️ Solo soporta 2 niveles)
- Vista: resources/views/livewire/administracion/usuarios/permisos.blade.php
```

### Prueba 3: Submenu sin generar archivos
```
1. Desmarca el checkbox "Generar archivos automáticamente"
2. Guarda el submenu
3. Verifica que NO se crean archivos
4. El submenu se guarda en BD pero no hay archivos físicos
```

### Prueba 4: Archivo ya existente
```
1. Crea un submenu con URL: usuarios/listar
2. Genera los archivos
3. Intenta crear otro submenu con la misma URL
4. Deberías ver advertencia: "El controlador ya existe"
```

---

## 🐛 Errores comunes y soluciones

### Error: "Class 'App\Services\MenuGeneratorService' not found"
**Solución**:
```bash
cd c:\laragon\www\Valencia\PROFAC\SISTEMA\profac-app
composer dump-autoload
```

### Error: "Unable to create directory"
**Solución**: Verifica permisos
```bash
# En PowerShell (como administrador):
icacls "c:\laragon\www\Valencia\PROFAC\SISTEMA\profac-app\app\Http\Livewire" /grant Everyone:F /T
icacls "c:\laragon\www\Valencia\PROFAC\SISTEMA\profac-app\resources\views\livewire" /grant Everyone:F /T
```

### Error 404 al acceder al submenu
**Causa**: Olvidaste agregar la ruta a web.php
**Solución**: Copia la ruta generada y pégala en routes/web.php

### El submenu no aparece en el menú
**Causas posibles**:
1. No tienes el rol asignado ➜ Asigna tu rol al submenu
2. El menú padre está inactivo ➜ Activa el menú padre
3. El submenu está inactivo ➜ Activa el submenu

---

## ✅ Checklist de verificación

Marca cada item después de probarlo:

- [ ] Crear submenu con generación automática activada
- [ ] Verificar que se generó el controlador Livewire
- [ ] Verificar que se generó la vista Blade
- [ ] Copiar y pegar la ruta en web.php
- [ ] Recargar la aplicación
- [ ] Ver el submenu en el menú lateral
- [ ] Hacer clic y ver la vista generada
- [ ] Probar crear submenu SIN generación automática
- [ ] Verificar advertencia cuando archivo ya existe
- [ ] Limpiar archivos de prueba

---

## 📊 Registro de resultados

| Fecha | Usuario | Resultado | Observaciones |
|-------|---------|-----------|---------------|
| 15-Dic-2025 | Admin | ✅ | Funciona correctamente |
|       |         |           |               |
|       |         |           |               |

---

**Nota**: Si encuentras algún problema durante las pruebas, documéntalo en la tabla de arriba o repórtalo al equipo de desarrollo.
