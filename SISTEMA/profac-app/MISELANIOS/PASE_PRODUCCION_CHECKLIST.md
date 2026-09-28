# CHECKLIST — PASE A PRODUCCIÓN
## Sistema PROFAC
> **Actualizar este documento en cada sesión si hay nuevos puntos a considerar para producción.**
> Última actualización: 2026-05-20

---

## INSTRUCCIONES DE USO
- Cada ítem tiene un estado: `[ ]` pendiente / `[x]` completado
- Seguir el orden de secciones (Base de Datos → Backend → Config → Verificaciones)
- Anotar fecha y responsable al marcar completado

---

## ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## SECCIÓN 1 — BASE DE DATOS

### 1.1 Migración de Categorías de Precios
**Origen:** Sesión 2026-05-15  
**Contexto:** Se consolidaron ~35 categorías de `cliente_categoria_escala` en 6 grupos para normalizar el sistema de precios.

- [ ] Ejecutar migración Laravel:
  ```bash
  php artisan migrate
  ```
  - **Archivo:** `database/migrations/2026_05_15_000001_add_categoria_precios_id_to_cliente_table.php`
  - **Efecto:** Agrega columna `categoria_precios_id INT NULL` a tabla `cliente`

- [ ] Ejecutar script SQL de consolidación:
  - **Archivo:** `MISELANIOS/sp/migracion_consolidar_categorias_clientes.sql`
  - **Efecto:** Consolida categorías, crea nuevas (Co-Distribuidor id=36, Final Empresarial id=37, Gobierno id=38), asigna `categoria_precios_id` a ~1,288 clientes, inactiva ~30 categorías antiguas
  - ⚠️ Ejecutar **después** de la migración Laravel, no antes

- [ ] Verificar post-ejecución:
  - Total clientes con `categoria_precios_id` asignado ≈ 1,288 (de ~1,337)
  - Clientes sin asignar → revisar manualmente
  - Tabla `precios_producto_carga` (162,210 registros) **no debe modificarse**

### 1.2 Migración Sistema de Notificaciones
**Origen:** Sesión 2026-05-19  
**Contexto:** Se crearon tablas para el sistema de notificaciones de flujo.

- [ ] Verificar que las migraciones ya están aplicadas en producción:
  - `notifications` (tabla nativa de Laravel, UUID)
  - `notificacion_flujo_config`
  - Comando para verificar: `php artisan migrate:status`
- [ ] Si no están aplicadas: `php artisan migrate`

---

## SECCIÓN 2 — BACKEND / CÓDIGO

### 2.1 Fix tabla `recibido_bodega` en PrefacturaController
**Origen:** Sesión 2026-05-19  
**Archivo:** `app/Http/Livewire/Flujo/PrefacturaController.php`

- [ ] Confirmar que el código en producción ya no referencia la tabla `inventario` (que no existe)
- [ ] El método `guardar()` debe descontar de `recibido_bodega.cantidad_disponible` — verificar que el código actualizado está desplegado

### 2.2 Archivos de categorías de precios — verificar en prod
**Origen:** Sesión 2026-05-15

- [ ] `app/Http/Livewire/Ventas/FacturacionCorporativa.php` — selecciona `categoria_precios_id`
- [ ] `app/Http/Livewire/Ventas/FacturacionEstatal.php` — selecciona `categoria_precios_id`
- [ ] `app/Http/Livewire/Cotizaciones/Editarcotizacion.php` — selecciona `categoria_precios_id`
- [ ] `app/Http/Livewire/Exports/expo.php` — JOIN directo en `categoria_precios_id`
- [ ] `app/Models/ModelCliente.php` — `categoria_precios_id` en `$fillable`
- [ ] `resources/views/livewire/ventas/facturacion-unificada.blade.php` — pre-selección inteligente de tier

### 2.3 Sistema de Notificaciones — archivos nuevos
**Origen:** Sesión 2026-05-19  
Confirmar que los siguientes archivos están en producción:

- [ ] `app/Events/FlujoAvanzadoEvent.php`
- [ ] `app/Listeners/NotificarPersonalFlujoListener.php`
- [ ] `app/Notifications/FlujoNotification.php`
- [ ] `app/Jobs/EscalarNotificacionesJob.php`
- [ ] `app/Http/Livewire/NotificacionesBell.php`
- [ ] `app/Http/Livewire/Configuracion/ConfiguracionNotificaciones.php`
- [ ] `app/Models/NotificacionFlujoConfig.php`
- [ ] `resources/views/livewire/notificaciones-bell.blade.php`
- [ ] `resources/views/livewire/configuracion/configuracion-notificaciones.blade.php`
- [ ] `resources/views/navigation-menu.blade.php` — debe incluir `@livewire('notificaciones-bell')`
- [ ] `app/Providers/EventServiceProvider.php` — debe tener el binding del Listener

---

## SECCIÓN 3 — CONFIGURACIÓN DEL SERVIDOR

### 3.1 Variables de entorno (`.env` en producción)
- [ ] Verificar `QUEUE_CONNECTION=sync` (o decidir si se usará `database`/`redis` con queue:work)
  - Con `sync`: no se necesita `queue:work`, todo ejecuta en la misma request
  - Con `database`/`redis`: necesita worker corriendo permanentemente

### 3.2 Scheduler (Cron) — Escalaciones de notificaciones
- [ ] Configurar en el cron del servidor:
  ```
  * * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
  ```
- [ ] Verificar que `EscalarNotificacionesJob` (horario cada hora) esté funcionando
  - Este job escala notificaciones no leídas después de N horas al nivel superior

### 3.3 Caché — Interruptor de notificaciones
- [ ] El interruptor ON/OFF del sistema de notificaciones usa `Cache::forever('notificaciones_sistema_activo', bool)`
- [ ] Verificar que el driver de caché en producción sea persistente (no `array`)
- [ ] Si se limpia el caché en el pase, el interruptor quedará en `false` → el admin debe activarlo desde `/configuracion/notificaciones/flujo`

---

## SECCIÓN 4 — BASE DE DATOS — MENÚ DINÁMICO

### 4.1 Insertar ítem de menú para Notificaciones de Flujo
**Origen:** Sesión 2026-05-19  
Si la BD de producción no tiene este registro, insertarlo:

- [ ] Verificar si existe en `sub_menu` donde `url = 'configuracion/notificaciones/flujo'`
- [ ] Si no existe, insertar:
  ```sql
  -- Verificar primero
  SELECT * FROM sub_menu WHERE url = 'configuracion/notificaciones/flujo';

  -- Insertar si no existe
  INSERT INTO sub_menu (nombre, url, menu_id, orden, icono, activo)
  VALUES ('Notificaciones de Flujo', 'configuracion/notificaciones/flujo', 2, 4, 'fa fa-bell', 1);

  -- Asignar al rol Administrador (id=1)
  INSERT INTO rol_submenu (rol_id, sub_menu_id)
  VALUES (1, LAST_INSERT_ID());
  ```

---

## SECCIÓN 5 — PENDIENTES DE DESARROLLO (no bloquean el pase, pero registrar)

| # | Descripción | Archivo | Origen |
|---|---|---|---|
| 1 | `buscarSimilares()` referencia tabla `inventario` que no existe | `app/Http/Livewire/Ventas/FacturacionUnificada.php` línea ~473 | Sesión 2026-05-19 |
| ~~2~~ | ~~Dispatch de `FlujoAvanzadoEvent` para estados 5, 6, 7 y 8~~ | — | ~~Sesión 2026-05-19~~ — **RESUELTO 2026-05-20** |
| 3 | Prueba E2E completa del flujo de notificaciones | — | Sesión 2026-05-19 |

> **Nota ítem 1:** El archivo `FacturacionUnificada.php` pertenece a un compañero. Consultar con él antes de modificar la referencia a `inventario` en `buscarSimilares()`.

---

## HISTORIAL DE ACTUALIZACIONES DE ESTE DOCUMENTO

| Fecha | Cambio |
|---|---|
| 2026-05-20 | Creación inicial — consolidado desde sesiones 2026-05-15 y 2026-05-19 |
| 2026-05-20 | Sección 5 ítem 2: Dispatches estados 5-8 resueltos — tachado |
