# NOTIFICACIONES DE FLUJO — DOCUMENTACIÓN COMPLETA

**Sistema:** PROFAC  
**Módulo:** Notificaciones de Flujo de Ventas  
**Framework:** Laravel 8, Livewire 2, canal `database`  
**Fecha de documentación:** Junio 2026

---

## 1. PROPÓSITO Y CONCEPTO

Las **Notificaciones de Flujo** son alertas internas (in-app) que se generan automáticamente cada vez que una tramitación del flujo de ventas avanza de estado. Su objetivo es mantener informado en tiempo real al personal relevante sobre el progreso de cada trámite, sin necesidad de que los usuarios revisen manualmente el sistema.

### Problema que resuelven

En un flujo de ventas con múltiples etapas (cotización → aprobación → facturación → despacho → etc.), distintos roles o áreas necesitan saber cuándo es su turno de actuar. Sin notificaciones automáticas, el personal depende de comunicación verbal o revisión manual del sistema, lo que genera demoras y errores de seguimiento.

### Solución implementada

Cada vez que el sistema registra el avance de un trámite, dispara un **evento** (`FlujoAvanzadoEvent`) que un **listener** captura. El listener consulta las **reglas de notificación configuradas** para ese tipo de trámite, resuelve qué usuarios deben ser notificados, y les envía una **notificación** que queda guardada en base de datos. Cuando el usuario inicia sesión (o tiene la pantalla abierta), ve el ícono de campana con el contador actualizado, y al hacer clic puede navegar directamente al trámite en cuestión.

---

## 2. ARQUITECTURA DE 5 CAPAS

```
[Evento del Sistema]
         │
         ▼
FlujoAvanzadoEvent          ← Disparo: cualquier punto del código que advance un flujo
         │
         ▼
NotificarPersonalFlujoListener  ← Listener sincrónico registrado en EventServiceProvider
         │
         ├─ Verifica switch global (Cache-Aside)
         ├─ Carga reglas activas para el tipo de trámite
         ├─ Resuelve usuarios destino (por ROL o ÁREA)
         ├─ Deduplica usuarios
         │
         ▼
FlujoNotification               ← Clase Notification de Laravel
         │
         ▼
Canal: database                 ← Tabla `notifications` (UUID, morphs, data JSON)
         │
         ▼
UI: Campana + Modal automático  ← Livewire polling / broadcast
```

---

## 3. SWITCH GLOBAL ON/OFF

### Descripción

El sistema tiene un **interruptor global** que habilita o deshabilita **todas** las notificaciones de flujo de forma inmediata. Cuando está desactivado, el listener retorna sin enviar ninguna notificación, aunque los eventos sigan disparándose normalmente.

### Implementación: Cache-Aside Pattern

```php
// app/Http/Livewire/Configuracion/ConfiguracionNotificaciones.php

public function toggleSistema()
{
    $nuevoEstado = !$this->sistemaActivo;
    
    // 1. Actualizar en base de datos
    DB::table('configuracion_sistema')
        ->where('clave', 'notificaciones_sistema_activo')
        ->update(['valor' => $nuevoEstado ? '1' : '0']);
    
    // 2. Actualizar caché de forma indefinida
    Cache::forever('notificaciones_sistema_activo', $nuevoEstado);
    
    $this->sistemaActivo = $nuevoEstado;
}
```

### Lectura en el Listener

```php
// app/Listeners/NotificarPersonalFlujoListener.php

$sistemaActivo = Cache::remember('notificaciones_sistema_activo', 3600, function () {
    return DB::table('configuracion_sistema')
        ->where('clave', 'notificaciones_sistema_activo')
        ->value('valor') === '1';
});

if (!$sistemaActivo) {
    return; // Sale sin notificar
}
```

### Tabla `configuracion_sistema`

| clave | valor | tipo | descripcion |
|-------|-------|------|-------------|
| `notificaciones_sistema_activo` | `1` / `0` | boolean | Switch global de notificaciones |

---

## 4. TABLA `notificacion_flujo_config`

Esta tabla almacena las **reglas de notificación**: qué roles/áreas reciben notificación cuando avanza cada tipo de trámite.

### Estructura de la tabla

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | BIGINT PK | Identificador único |
| `tipo_tramite_id` | INT FK | Referencia a `tipo_tramite.id` — tipo de flujo (1-11) |
| `rol_id` | INT FK NULLABLE | Si se notifica por ROL: referencia a `roles.id` |
| `area_id` | INT FK NULLABLE | Si se notifica por ÁREA: referencia a `area.id` (deshabilitado) |
| `nivel_max_id` | INT FK NULLABLE | Nivel máximo de área a notificar (deshabilitado) |
| `escalar_activo` | TINYINT | Si está habilitada la escalación (0=no, 1=sí) |
| `escalar_horas` | INT NULLABLE | Horas antes de escalar |
| `escalar_nivel_id` | INT FK NULLABLE | Nivel al que escala (deshabilitado) |
| `activo` | TINYINT | Si esta regla está activa (0=no, 1=sí) |
| `created_at` / `updated_at` | TIMESTAMP | Auditoría |

### Tipos de Destino

**Por ROL** (`rol_id` != null): todos los usuarios activos con ese rol reciben la notificación.  
**Por ÁREA** (`area_id` != null): usuarios del área y hasta el `nivel_max_id` (arquitectura lista, UI deshabilitada).

### Reglas actuales en BD (estado al 10/06/2026)

| ID | tipo_tramite_id | Descripción Trámite | rol_id | Rol | activo |
|----|-----------------|---------------------|--------|-----|--------|
| 1 | 10 | Aprobación de crédito | 1 | Administrador | 1 |
| 2 | 9 | Solicitud de crédito | 1 | Administrador | 1 |
| 3 | 4 | Cotización enviada | 1 | Administrador | 1 |
| 4 | 7 | Factura emitida | 1 | Administrador | 1 |
| 5 | 11 | Despacho/Entrega | 1 | Administrador | 1 |

> **Nota:** Todos los roles configurados actualmente apuntan al Administrador (rol_id=1). Para agregar otros roles (Vendedor, Logística, etc.) se deben crear nuevas reglas desde la UI de Configuración → Notificaciones → sección "Notificaciones de Flujo".

---

## 5. RESOLUCIÓN DE USUARIOS DESTINO

### Via ROL (mecanismo activo)

```php
// app/Models/NotificacionFlujoConfig.php

public function resolverUsuariosDestino(): Collection
{
    if ($this->rol_id) {
        // Obtiene todos los usuarios activos con este rol
        return User::whereHas('roles', function ($q) {
                $q->where('roles.id', $this->rol_id);
            })
            ->where('estado', 1) // Solo usuarios activos
            ->get();
    }
    
    // Via ÁREA (deshabilitado en UI actual)
    if ($this->area_id) {
        return User::where('area_id', $this->area_id)
            ->where('nivel_id', '<=', $this->nivel_max_id)
            ->where('estado', 1)
            ->get();
    }
    
    return collect();
}
```

### Via ÁREA (arquitectura lista, UI deshabilitada)

El modelo tiene el método `resolverUsuariosEscalacion()` implementado y las columnas en BD existen. La UI de configuración actualmente oculta la opción "Por Área". Para habilitar: descomentear el bloque de radio buttons en la vista del modal de creación de regla.

### Deduplicación de usuarios

Si múltiples reglas para el mismo tipo de trámite apuntan al mismo usuario (por diferentes roles), el listener deduplica:

```php
$usuariosNotificar = $usuariosNotificar->unique('id');
```

---

## 6. FLUJO TÉCNICO COMPLETO (PASO A PASO)

### Paso 1: Disparo del evento

En cualquier punto del código donde avanza el flujo de un trámite:

```php
event(new FlujoAvanzadoEvent(
    flujoId: $flujo->id,
    tipoTramiteId: $flujo->tipo_tramite_id,
    contexto: [
        'cliente'    => $flujo->cliente->nombre ?? '',
        'monto'      => $flujo->monto_total ?? 0,
        'referencia' => $flujo->referencia ?? '',
    ]
));
```

### Paso 2: Registro en EventServiceProvider

```php
// app/Providers/EventServiceProvider.php

protected $listen = [
    FlujoAvanzadoEvent::class => [
        NotificarPersonalFlujoListener::class,
    ],
];
```

El listener es **sincrónico** (no implementa `ShouldQueue`), por lo tanto se ejecuta en la misma request que dispara el evento.

### Paso 3: Listener verifica el switch global

```php
if (!$sistemaActivo) { return; }
```

### Paso 4: Carga reglas activas

```php
$reglas = NotificacionFlujoConfig::activos()
    ->paraTramite($event->tipoTramiteId)
    ->get();

if ($reglas->isEmpty()) { return; }
```

Los scopes `activos()` y `paraTramite($id)` están definidos en el modelo.

### Paso 5: Acumulación de usuarios por todas las reglas

```php
$usuariosNotificar = collect();

foreach ($reglas as $regla) {
    $usuarios = $regla->resolverUsuariosDestino();
    $usuariosNotificar = $usuariosNotificar->merge($usuarios);
}
```

### Paso 6: Deduplicación

```php
$usuariosNotificar = $usuariosNotificar->unique('id');
```

### Paso 7: Envío de notificación a cada usuario

```php
foreach ($usuariosNotificar as $usuario) {
    $usuario->notify(new FlujoNotification(
        $event->flujoId,
        $event->tipoTramiteId,
        $event->contexto
    ));
}
```

### Paso 8: La notificación construye el payload

`FlujoNotification::toDatabase()` mapea el `tipo_tramite_id` a un array con título, ícono, color y URL de destino (ver sección 7).

### Paso 9: Laravel inserta en `notifications`

```sql
INSERT INTO notifications 
(id, type, notifiable_type, notifiable_id, data, read_at, created_at, updated_at)
VALUES (UUID(), 'App\\Notifications\\FlujoNotification', 'App\\Models\\User', ?, JSON, null, NOW(), NOW())
```

### Paso 10: UI actualiza el contador

El componente de campana de notificaciones usa Livewire polling cada N segundos y detecta el incremento en `notifications` sin leer donde `read_at IS NULL`.

### Paso 11: Usuario hace clic en la notificación

Se abre un modal con el resumen de la notificación y un botón de "Ver Trámite".

### Paso 12: Redireccionamiento al trámite

La URL en el payload lleva directamente al flujo específico: `/flujos/{flujoId}` o la ruta configurada por tipo.

### Paso 13: Marcado como leída

Al abrir el modal o hacer clic, la notificación se marca como leída (`read_at = NOW()`).

---

## 7. CLASE `FlujoAvanzadoEvent`

```php
// app/Events/FlujoAvanzadoEvent.php

class FlujoAvanzadoEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int   $flujoId,
        public int   $tipoTramiteId,
        public array $contexto  // ['cliente' => '', 'monto' => 0, 'referencia' => '']
    ) {}
}
```

### Propiedades del contexto

| Clave | Tipo | Descripción |
|-------|------|-------------|
| `cliente` | string | Nombre del cliente del trámite |
| `monto` | float | Monto total del trámite |
| `referencia` | string | Referencia o código del trámite |

---

## 8. CLASE `FlujoNotification`

### Mapa `TRAMITE_CONFIG` (11 tipos)

La notificación usa un mapa interno para traducir el `tipo_tramite_id` a información legible y navegable:

| tipo_tramite_id | Título | Ícono FA | Color | URL destino |
|-----------------|--------|----------|-------|-------------|
| 1 | Nueva Cotización | `fa-file-text` | `#3498db` (azul) | `/flujos/1/{id}` |
| 2 | Cotización Revisada | `fa-edit` | `#f39c12` (naranja) | `/flujos/2/{id}` |
| 3 | Cotización Aprobada | `fa-check-circle` | `#27ae60` (verde) | `/flujos/3/{id}` |
| 4 | Cotización Enviada | `fa-paper-plane` | `#2980b9` (azul oscuro) | `/flujos/4/{id}` |
| 5 | Orden de Compra | `fa-shopping-cart` | `#8e44ad` (morado) | `/flujos/5/{id}` |
| 6 | Orden Aprobada | `fa-thumbs-up` | `#27ae60` (verde) | `/flujos/6/{id}` |
| 7 | Factura Emitida | `fa-file-invoice` | `#e74c3c` (rojo) | `/flujos/7/{id}` |
| 8 | Factura Enviada | `fa-send` | `#16a085` (verde azulado) | `/flujos/8/{id}` |
| 9 | Solicitud de Crédito | `fa-credit-card` | `#c0392b` (rojo oscuro) | `/flujos/9/{id}` |
| 10 | Aprobación de Crédito | `fa-bank` | `#27ae60` (verde) | `/flujos/10/{id}` |
| 11 | Despacho/Entrega | `fa-truck` | `#2c3e50` (gris oscuro) | `/flujos/11/{id}` |

> Los tipos sin configuración en BD (1,2,3,5,6,8) tienen el mapa de presentación listo, pero no tienen reglas activas — la notificación no se enviará salvo que se agreguen reglas.

### Método `toDatabase()`

```php
public function toDatabase($notifiable): array
{
    $config = self::TRAMITE_CONFIG[$this->tipoTramiteId] ?? [
        'titulo' => 'Trámite Avanzado',
        'icono'  => 'fa-bell',
        'color'  => '#6c757d',
        'url'    => '/flujos/' . $this->flujoId,
    ];
    
    return [
        'titulo'          => $config['titulo'],
        'mensaje'         => $this->construirMensaje(),
        'url'             => str_replace('{id}', $this->flujoId, $config['url']),
        'icono'           => $config['icono'],
        'color'           => $config['color'],
        'tipo_tramite_id' => $this->tipoTramiteId,
        'flujo_id'        => $this->flujoId,
        'cliente'         => $this->contexto['cliente'] ?? '',
        'monto'           => $this->contexto['monto'] ?? 0,
        'referencia'      => $this->contexto['referencia'] ?? '',
    ];
}
```

---

## 9. PUNTOS DE DESPACHO EN EL CÓDIGO

El evento se dispara en los siguientes tipos de trámite cuando el flujo avanza:

| tipo_tramite_id | Descripción | ¿Código de dispatch? | ¿Regla en BD? |
|-----------------|-------------|----------------------|----------------|
| 4 | Cotización Enviada | ✅ Sí | ✅ Sí (rol 1) |
| 6 | Orden Aprobada | ✅ Sí | ❌ No |
| 7 | Factura Emitida | ✅ Sí | ✅ Sí (rol 1) |
| 8 | Factura Enviada | ✅ Sí | ❌ No |
| 9 | Solicitud de Crédito | ✅ Sí | ✅ Sí (rol 1) |
| 10 | Aprobación de Crédito | ✅ Sí | ✅ Sí (rol 1) |
| 11 | Despacho/Entrega | ✅ Sí | ✅ Sí (rol 1) |

> Los tipos 1, 2, 3, 5 no tienen dispatch de evento implementado (el flujo avanza pero no notifica).

---

## 10. TABLA `notifications` (Laravel estándar)

### Estructura

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | CHAR(36) PK | UUID v4 generado por Laravel |
| `type` | VARCHAR | Clase completa: `App\Notifications\FlujoNotification` |
| `notifiable_type` | VARCHAR | Siempre: `App\Models\User` |
| `notifiable_id` | BIGINT | ID del usuario que recibe la notificación |
| `data` | LONGTEXT (JSON) | Payload completo (ver sección 8) |
| `read_at` | TIMESTAMP NULL | NULL = no leída; fecha = leída |
| `created_at` | TIMESTAMP | Cuándo se creó la notificación |
| `updated_at` | TIMESTAMP | Última actualización |

### Ejemplo de payload JSON en `data`

```json
{
  "titulo": "Factura Emitida",
  "mensaje": "Se emitió factura para cliente: Distribuidora XYZ — Referencia: FAC-2026-0890 — Monto: Lps. 45,200.00",
  "url": "/flujos/7/1234",
  "icono": "fa-file-invoice",
  "color": "#e74c3c",
  "tipo_tramite_id": 7,
  "flujo_id": 1234,
  "cliente": "Distribuidora XYZ",
  "monto": 45200.00,
  "referencia": "FAC-2026-0890"
}
```

---

## 11. CONSULTAS SQL ÚTILES

### Ver notificaciones no leídas de un usuario

```sql
SELECT 
    id,
    type,
    JSON_UNQUOTE(JSON_EXTRACT(data, '$.titulo'))    AS titulo,
    JSON_UNQUOTE(JSON_EXTRACT(data, '$.mensaje'))   AS mensaje,
    JSON_UNQUOTE(JSON_EXTRACT(data, '$.url'))       AS url,
    JSON_UNQUOTE(JSON_EXTRACT(data, '$.cliente'))   AS cliente,
    JSON_EXTRACT(data, '$.monto')                   AS monto,
    created_at
FROM notifications
WHERE notifiable_type = 'App\\Models\\User'
  AND notifiable_id   = 1         -- ID del usuario
  AND read_at IS NULL
ORDER BY created_at DESC;
```

### Ver todas las notificaciones de flujo (últimas 24h)

```sql
SELECT 
    n.notifiable_id                                   AS usuario_id,
    u.name                                            AS usuario,
    JSON_UNQUOTE(JSON_EXTRACT(n.data, '$.titulo'))    AS titulo,
    JSON_UNQUOTE(JSON_EXTRACT(n.data, '$.cliente'))   AS cliente,
    JSON_EXTRACT(n.data, '$.tipo_tramite_id')         AS tipo_tramite,
    JSON_EXTRACT(n.data, '$.flujo_id')                AS flujo_id,
    n.read_at,
    n.created_at
FROM notifications n
JOIN users u ON u.id = n.notifiable_id
WHERE n.type = 'App\\Notifications\\FlujoNotification'
  AND n.created_at >= NOW() - INTERVAL 1 DAY
ORDER BY n.created_at DESC;
```

### Contar notificaciones no leídas por usuario

```sql
SELECT 
    u.name,
    COUNT(*) AS pendientes
FROM notifications n
JOIN users u ON u.id = n.notifiable_id
WHERE n.type    = 'App\\Notifications\\FlujoNotification'
  AND n.read_at IS NULL
GROUP BY n.notifiable_id, u.name
ORDER BY pendientes DESC;
```

### Ver reglas configuradas con nombre de rol y tipo de trámite

```sql
SELECT 
    nfc.id,
    tt.nombre              AS tipo_tramite,
    nfc.tipo_tramite_id,
    r.name                 AS rol,
    nfc.rol_id,
    nfc.activo,
    nfc.escalar_activo,
    nfc.escalar_horas
FROM notificacion_flujo_config nfc
LEFT JOIN tipo_tramite tt ON tt.id = nfc.tipo_tramite_id
LEFT JOIN roles r         ON r.id  = nfc.rol_id
ORDER BY nfc.tipo_tramite_id;
```

---

## 12. ESCALACIÓN (ARQUITECTURA LISTA, FUNCIONALIDAD DESHABILITADA)

### Qué está preparado

- Columnas en BD: `escalar_activo`, `escalar_horas`, `escalar_nivel_id`
- Método en modelo: `resolverUsuariosEscalacion()` implementado
- Lógica conceptual: si han pasado `escalar_horas` horas desde la creación de la notificación sin que nadie la haya leído, se notifica al nivel superior

### Por qué está deshabilitado

La UI del modal de creación/edición de reglas no muestra el toggle de escalación. El listener no llama al método de escalación. Para habilitarlo, se necesitaría:
1. Un Job programado que evalúe notificaciones viejas no leídas
2. Un scheduler que ejecute ese Job periódicamente
3. Descomentar la UI en el formulario de configuración

---

## 13. GESTIÓN DESDE LA UI (Configuración → Notificaciones)

### Sección "Notificaciones de Flujo"

La pantalla `/configuracion/notificaciones` (componente Livewire `ConfiguracionNotificaciones`) permite:

**Switch global:** Toggle ON/OFF que activa/desactiva todo el sistema de notificaciones de flujo. Escribe en `configuracion_sistema` y actualiza el caché.

**Tabla de reglas:** Muestra todas las reglas en `notificacion_flujo_config` con:
- Tipo de trámite
- Destino (Rol o Área)
- Estado (Activo/Inactivo)
- Cobertura (badge verde si el tipo tiene evento dispatch implementado)
- Botones: Editar / Eliminar

**Formulario Nueva Regla / Editar:**
- Selector de tipo de trámite (11 opciones)
- Radio: Por ROL (activo) / Por ÁREA (oculto)
- Selector de rol
- Toggle activo/inactivo

**Resumen por tipo:** Cards que muestran cuántas reglas tiene cada tipo de trámite y si tiene dispatch implementado.

---

## 14. CÓMO AGREGAR UNA NUEVA REGLA

### Desde la UI

1. Ir a **Configuración → Notificaciones**
2. En la sección "Notificaciones de Flujo", clic en **"+ Nueva Regla"**
3. Seleccionar el tipo de trámite (ej: "Orden de Compra")
4. Seleccionar "Por ROL"
5. Elegir el rol (ej: "Vendedor")
6. Activar el toggle
7. Guardar

### Desde SQL

```sql
INSERT INTO notificacion_flujo_config 
(tipo_tramite_id, rol_id, area_id, nivel_max_id, escalar_activo, escalar_horas, escalar_nivel_id, activo, created_at, updated_at)
VALUES 
(5, 3, null, null, 0, null, null, 1, NOW(), NOW());
-- tipo 5 = Orden de Compra, rol 3 = Vendedor (ajustar IDs según BD)
```

---

## 15. MODELOS Y ARCHIVOS RELEVANTES

| Archivo | Descripción |
|---------|-------------|
| `app/Events/FlujoAvanzadoEvent.php` | Evento que se dispara al avanzar un flujo |
| `app/Listeners/NotificarPersonalFlujoListener.php` | Listener que procesa el evento |
| `app/Notifications/FlujoNotification.php` | Clase de notificación con mapa de 11 tipos |
| `app/Models/NotificacionFlujoConfig.php` | Modelo de reglas + métodos de resolución de usuarios |
| `app/Http/Livewire/Configuracion/ConfiguracionNotificaciones.php` | Componente Livewire de gestión |
| `resources/views/livewire/configuracion/configuracion-notificaciones.blade.php` | Vista de gestión (rediseñada Jun 2026) |
| `database/migrations/*_create_notificacion_flujo_config_table.php` | Migración de la tabla de reglas |

---

## 16. CONSIDERACIONES Y LIMITACIONES ACTUALES

| Aspecto | Estado | Notas |
|---------|--------|-------|
| Canal | `database` únicamente | No hay email, SMS, push |
| Tipos configurados | 5 de 11 | Solo tipos 4,7,9,10,11 tienen reglas |
| Roles configurados | Solo Administrador | Agregar vendedor, logística, etc. según necesidad |
| Notificación en tiempo real | Polling Livewire | No hay WebSockets/broadcasting implementado |
| Escalación | Deshabilitada | Arquitectura lista, falta implementar el Job evaluador |
| Via Área | Deshabilitada en UI | Código presente, descomentar para habilitar |
| Deduplicación | Por ID de usuario | Funciona correctamente con múltiples reglas por tipo |
| Sincronicidad | Listener síncrono | Agrega latencia mínima a la request original (BD writes rápidas) |
