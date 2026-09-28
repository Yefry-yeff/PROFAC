# Sesión de Trabajo — 19 de Mayo 2026
## Sistema PROFAC — Resumen de Cambios y Estado Actual

---

## 1. PROBLEMA RESUELTO: `Table 'profac_app.inventario' doesn't exist`

### Contexto
Al guardar una oferta/prefactura en el flujo, el sistema lanzaba error `Table 'profac_app.inventario' doesn't exist` porque el código intentaba decrementar existencias en una tabla que ya no existe en la BD actual.

### Tabla real en la BD
La tabla de inventario actual es `recibido_bodega` con columna `cantidad_disponible` (no `inventario.existencia`).

### Archivo modificado
**`app/Http/Livewire/Flujo/PrefacturaController.php`** — método `guardar()`

### Cambio aplicado
Se reemplazó el bloque que hacía `DB::table('inventario')->decrement('existencia', ...)` por un patrón multi-fila que descuenta de `recibido_bodega`:

```php
foreach ($arrayProductos as $prod) {
    if ($prod['resta_inventario'] && $prod['producto_id'] && $prod['seccion_id']) {
        $restante = (float) $prod['cantidad'];
        $filas = DB::table('recibido_bodega')
            ->where('producto_id', $prod['producto_id'])
            ->where('seccion_id', $prod['seccion_id'])
            ->where('cantidad_disponible', '>', 0)
            ->orderByDesc('cantidad_disponible')
            ->get(['id', 'cantidad_disponible']);
        foreach ($filas as $fila) {
            if ($restante <= 0) break;
            $descontar = min((float) $fila->cantidad_disponible, $restante);
            DB::table('recibido_bodega')
                ->where('id', $fila->id)
                ->decrement('cantidad_disponible', $descontar);
            $restante -= $descontar;
        }
    }
}
```

### ⚠️ PENDIENTE — NO TOCAR
**`app/Http/Livewire/Ventas/FacturacionUnificada.php`** línea ~473, método `buscarSimilares()`:
```php
->whereRaw('id IN (SELECT producto_id FROM inventario WHERE cantidad > 0)')
```
Este archivo pertenece a un compañero. El usuario indicó **no modificarlo** hasta consultar con él.

---

## 2. REDISEÑO: Panel de Campana de Notificaciones

### Archivo
**`resources/views/livewire/notificaciones-bell.blade.php`**

### Qué se hizo
Rediseño visual completo del panel dropdown de notificaciones. El diseño anterior era básico; el nuevo usa Tailwind (que ya carga en el proyecto) con:
- Botón campana con badge contador rojo
- Animación CSS `bellBounce` al abrir
- Panel con encabezado, lista de notificaciones con avatares/iconos, timestamps
- Acciones: marcar todas leídas, ver todas
- Transición suave de apertura/cierre con Alpine.js x-transition

### Bugs corregidos en este archivo (misma sesión)

**Bug 1 — `wire:poll` en el div raíz (atribuido al navigation-menu padre)**
- Livewire 2 atribuye el `wire:poll` al componente dueño del div raíz
- Si está en el `<div>` raíz del bell (que está anidado dentro de navigation-menu), el poll se atribuye al componente padre
- **Solución:** Mover `wire:poll.30s="cargar"` a un `<div style="display:none;">` interno

**Bug 2 — `$wire.cargar()` en el `@click` del botón**
- `$wire` en un componente Alpine anidado puede apuntar al componente padre (navigation-menu)
- **Solución:** Eliminar `$wire.cargar()` del click (el método `cargar()` ya se ejecuta en `mount()` y vía el poll)

**Bug 3 — `<style>` como elemento raíz (Livewire "Multiple root elements")**
- El bloque `<style>` estaba FUERA del `<div>` raíz, creando 2 raíces
- Livewire 2 ponía `wire:id` en el `<style>` en lugar del `<div>`
- Console mostraba: `Livewire: Multiple root elements detected`
- **Solución:** Mover el bloque `<style>` adentro del `<div>` raíz

### Estructura actual del archivo
```html
<div class="relative ml-3" x-data="..." x-init="...">
    <style>@keyframes bellBounce { ... }.bell-anim { ... }</style>
    <div wire:poll.30s="cargar" style="display:none;"></div>
    <button @click="open = !open" ...>
        <!-- icono campana + badge -->
    </button>
    <div x-show="open" x-transition ...>
        <!-- panel dropdown -->
    </div>
</div>
```

---

## 3. REDISEÑO: Página de Configuración de Notificaciones

### Archivo
**`resources/views/livewire/configuracion/configuracion-notificaciones.blade.php`**

### Componente PHP (no modificado)
**`app/Http/Livewire/Configuracion/ConfiguracionNotificaciones.php`**
- Métodos públicos disponibles: `toggleSistema()`, `nuevaRegla()`, `editarRegla(int $id)`, `guardar()`, `toggleActivo(int $id)`, `eliminar(int $id)`
- Propiedades: `$showModal`, `$notificacionesActivas`, `$configs[]`, `$tiposTramites[]`, `$roles[]`, `$areas[]`, `$niveles[]`

### Qué se hizo
Rediseño visual del diseño antiguo (Inspinia theme básico) a un diseño moderno con:
- Hero header con gradiente azul (`linear-gradient(135deg,#1e3a5f,#2563eb,#3b82f6)`)
- Tarjetas por tipo de trámite con tabla interna
- Toggle switch CSS personalizado para activar/desactivar reglas
- Modal de crear/editar regla con diseño limpio
- Indicadores de cobertura de usuarios
- Warning collapsible para roles sin jerarquía configurada

### Ruta
```
GET /configuracion/notificaciones/flujo → ConfiguracionNotificaciones::class
```

### Bugs corregidos en este archivo (misma sesión)

**Bug 1 — Contenido duplicado (nuevo diseño + viejo diseño concatenados)**
- `replace_string_in_file` solo hizo match de las primeras 3 líneas del archivo viejo y reemplazó con todo el contenido nuevo, pero las líneas 4-fin del archivo viejo quedaron appended al final
- El archivo llegó a tener 893 líneas (551 nuevo + 342 viejo)
- **Solución:** Truncar el archivo en línea 551 usando PowerShell

**Bug 2 — `<style>` como elemento raíz (igual que el bell)**
- El bloque `<style>` estaba fuera del `<div>` raíz
- **Solución:** Reestructurar con PowerShell para que `<div>` sea el primer elemento y `<style>` quede adentro

**Bug 3 — UTF-8 BOM causando que `wire:click` no funcionara**
- Al usar `Set-Content -Encoding UTF8` en PowerShell 5.1, se agrega automáticamente un BOM (`EF BB BF`) al inicio del archivo
- Livewire 2 usa regex para encontrar el primer tag HTML del componente y agregarle `wire:id`
- Con BOM antes del `<div>`, la regex fallaba → `wire:id` nunca se ponía → Livewire JS no inicializaba el componente → los botones no hacían nada, sin errores en consola
- **Solución:** Reescribir con `[System.IO.File]::WriteAllText(path, content, new UTF8Encoding(false))` (sin BOM)

### Estructura actual del archivo
```html
<div>                          ← raíz, Livewire pone wire:id aquí
    <style>
    /* animaciones, hero, cards, tabla, toggle, warning */
    </style>

    {{-- HERO HEADER --}}
    <div class="cfg-hero">
        <!-- breadcrumb, título, botón toggleSistema, botón nuevaRegla -->
    </div>

    {{-- CONTENIDO PRINCIPAL --}}
    <div class="container-fluid p-4">
        @if(session('success')) ... @endif
        @if($rolesIncompletos) ... @endif  {{-- warning collapsible --}}
        
        @forelse($configs grouped by tipo_tramite_id)
            <div class="notif-card">
                <!-- tabla de reglas por tipo de trámite -->
                <!-- botones editar/eliminar/toggle por fila -->
            </div>
        @empty
            <!-- estado vacío con CTA -->
        @endforelse
    </div>

    {{-- MODAL --}}
    @if($showModal)
    <div class="modal d-block" style="background:rgba(0,0,0,.5);">
        <!-- formulario crear/editar regla -->
        <!-- campos: tipo_tramite, targetTipo (rol|area), rol/area, nivel_max, escalado -->
    </div>
    @endif
</div>
```

---

## 4. BASE DE DATOS — Sin cambios directos en esta sesión

No se ejecutaron migraciones ni scripts SQL nuevos. Los cambios son exclusivamente de código PHP y Blade.

### Tabla usada para inventario (ya existente)
```
recibido_bodega
├── id
├── producto_id (FK)
├── seccion_id  (FK)
├── cantidad_disponible  ← columna que se decrementa al crear oferta
└── ... otros campos
```

---

## 5. LECCIONES TÉCNICAS IMPORTANTES (para evitar repetir)

### Livewire 2 — Reglas críticas
1. **Un solo elemento raíz:** El componente blade debe tener EXACTAMENTE UN elemento HTML raíz. `<style>` suelto antes del `<div>` cuenta como segundo elemento raíz.
2. **`wire:poll` en componentes anidados:** Si el componente está embebido en otro (ej: bell dentro de navigation-menu), el `wire:poll` en el div raíz se atribuye al padre. Ponerlo en un div hijo interno (`display:none`) lo ata correctamente al componente correcto.
3. **`$wire` en Alpine anidado:** `$wire` puede apuntar al componente padre si el componente Alpine no está directamente en el root del Livewire component. Preferir que las llamadas a métodos vengan del propio `wire:click` y no de `$wire.metodo()` dentro de Alpine.

### PowerShell — Escritura de archivos Blade
- `Set-Content -Encoding UTF8` → **AGREGA BOM** → rompe Livewire
- `[System.IO.File]::WriteAllText(path, content, [System.Text.UTF8Encoding]::new($false))` → **Sin BOM** → correcto

---

## 6. ESTADO FINAL AL CIERRE DE SESIÓN

| Componente | Estado |
|---|---|
| Bell (`notificaciones-bell.blade.php`) | ✅ Funcionando — diseño nuevo, poll correcto, sin errores |
| Config Notificaciones blade | ✅ Funcionando — diseño nuevo, botones activos |
| Config Notificaciones PHP | ✅ Sin cambios — métodos todos operativos |
| PrefacturaController (inventario fix) | ✅ Funcionando — usa recibido_bodega |
| FacturacionUnificada (inventario) | ⚠️ PENDIENTE — contiene ref a tabla `inventario` en buscarSimilares(), NO tocar hasta hablar con el compañero |

---

## 7. PRÓXIMOS PASOS SUGERIDOS

1. **FacturacionUnificada.php** — Consultar con el compañero la lógica de `buscarSimilares()` en línea ~473 para reemplazar la ref a tabla `inventario` con la tabla `recibido_bodega` (igual que se hizo en PrefacturaController)
2. **Probar flujo completo** de notificaciones: crear regla → generar evento de flujo → verificar que llega notificación al bell
3. **Ver pendiente de producción** en `MISELANIOS/SESION_20260519_RESUMEN.md` y el archivo de categorías de precios (documentado en sesión anterior)
