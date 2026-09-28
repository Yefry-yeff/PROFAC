# ALERTAS INTELIGENTES DE INVENTARIO — DOCUMENTACIÓN COMPLETA
**Sistema PROFAC | Mayo 2026 | Laravel 8 + Livewire + MySQL**

---

## ÍNDICE

1. [Propósito y Problema que Resuelve](#1-propósito-y-problema-que-resuelve)
2. [Arquitectura del Sistema](#2-arquitectura-del-sistema)
3. [Tabla de Datos — alerta_rotacion_config](#3-tabla-de-datos--alerta_rotacion_config)
4. [Los 6 Tipos de Alerta](#4-los-6-tipos-de-alerta)
5. [Cómo se Evalúan las Reglas — Consultas SQL](#5-cómo-se-evalúan-las-reglas--consultas-sql)
6. [El Job de Evaluación](#6-el-job-de-evaluación)
7. [Sistema de Deduplicación](#7-sistema-de-deduplicación)
8. [Destino de las Notificaciones — Resolución de Usuarios](#8-destino-de-las-notificaciones--resolución-de-usuarios)
9. [La Notificación Generada (InventarioAlertaNotification)](#9-la-notificación-generada-inventarioalertanotification)
10. [Ciclo de Vida Completo de una Alerta](#10-ciclo-de-vida-completo-de-una-alerta)
11. [Disparadores — Ejecución Automática vs. Manual](#11-disparadores--ejecución-automática-vs-manual)
12. [Pantalla de Configuración](#12-pantalla-de-configuración)
13. [Reglas Predeterminadas del Sistema](#13-reglas-predeterminadas-del-sistema)
14. [Modelo AlertaRotacionConfig — Métodos y Lógica](#14-modelo-alertarotacionconfig--métodos-y-lógica)
15. [Tablas de la Base de Datos Involucradas](#15-tablas-de-la-base-de-datos-involucradas)
16. [Conexión con el Módulo de Analítica](#16-conexión-con-el-módulo-de-analítica)

---

## 1. PROPÓSITO Y PROBLEMA QUE RESUELVE

### El problema
El inventario de una empresa cambia constantemente. Sin supervisión activa, productos
importantes pueden agotar su stock sin que nadie lo detecte a tiempo, otros pueden
acumular meses sin venderse mientras inmovilizan capital, y algunos experimentan picos
de demanda que sin reabastecimiento preventivo generan quiebres de venta.

Detectar estos problemas manualmente requeriría que alguien revisara cada producto
diariamente — algo impráctico con catálogos de cientos o miles de ítems.

### La solución
El módulo de **Alertas Inteligentes de Inventario** automatiza completamente este
proceso. Cada día evalúa automáticamente reglas configuradas contra los datos reales
del inventario y las ventas, y cuando un producto cumple una condición de riesgo,
envía una notificación directamente a los usuarios responsables dentro del sistema.

### Lo que hace en la práctica
- Avisa que un producto va a necesitar reabastecerse dentro de N días (antes de que
  se agote).
- Alerta cuando un producto ya debería haber sido reabastecido pero aún tiene stock
  (fecha límite vencida).
- Detecta productos con stock disponible que no se han vendido en 30, 60 o 90 días.
- Identifica productos con ventas anormalmente bajas en los últimos 2 meses.
- Señala productos cuyo stock cubre más de 6 meses de demanda (sobreinventario).
- Notifica cuando un producto experimenta un crecimiento súbito de ventas (para
  pre-abastecer antes de quedarse sin stock).

---

## 2. ARQUITECTURA DEL SISTEMA

El sistema tiene 4 capas:

```
┌────────────────────────────────────────────────────────────────────────────┐
│ CAPA 1: CONFIGURACIÓN                                                      │
│ Tabla: alerta_rotacion_config                                              │
│ Modelo: App\Models\AlertaRotacionConfig                                    │
│ Pantalla: /configuracion/notificaciones (sección inferior)                 │
│ Define: tipo de alerta, parámetros, destinatario, ícono, prioridad         │
└──────────────────────────────────┬─────────────────────────────────────────┘
                                   ↓  (ejecutado diariamente o manualmente)
┌────────────────────────────────────────────────────────────────────────────┐
│ CAPA 2: EVALUACIÓN                                                         │
│ Comando: php artisan alertas:evaluar-rotacion                              │
│ Clase: App\Console\Commands\AlertasEvaluarRotacion                         │
│ Job: App\Jobs\AlertasRotacionInventarioJob                                 │
│ Para cada regla activa: ejecuta la consulta SQL del tipo de alerta         │
│ Si hay productos afectados → envía notificación                            │
└──────────────────────────────────┬─────────────────────────────────────────┘
                                   ↓
┌────────────────────────────────────────────────────────────────────────────┐
│ CAPA 3: NOTIFICACIÓN                                                       │
│ Clase: App\Notifications\InventarioAlertaNotification                      │
│ Canal: database (tabla notifications)                                      │
│ Payload: título contextual, mensaje con resumen de productos, URL al       │
│ reporte detallado (/alertas/rotacion/{id}/reporte)                         │
└──────────────────────────────────┬─────────────────────────────────────────┘
                                   ↓
┌────────────────────────────────────────────────────────────────────────────┐
│ CAPA 4: VISUALIZACIÓN                                                      │
│ Campana de notificaciones en el header del sistema                         │
│ Al hacer clic → va a /alertas/rotacion/{regla_id}/reporte                  │
│ Reporte: App\Http\Livewire\Alertas\AlertasRotacionReporte                  │
│ Muestra la lista completa de productos afectados por esa regla             │
└────────────────────────────────────────────────────────────────────────────┘
```

---

## 3. TABLA DE DATOS — alerta_rotacion_config

**Tabla:** `alerta_rotacion_config`  
**Modelo:** `App\Models\AlertaRotacionConfig`

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | int (PK) | Identificador único de la regla |
| `nombre` | varchar(120) | Nombre descriptivo que ve el usuario |
| `tipo` | enum | Categoría de alerta (ver sección 4) |
| `parametro_dias` | int nullable | Días de aviso previo o días sin ventas (según tipo) |
| `parametro_umbral` | decimal(8,2) nullable | Umbral numérico (meses cobertura / % crecimiento / ventas mínimas) |
| `rol_id` | int nullable | Rol que recibirá la notificación |
| `area_id` | int nullable | Área que recibirá la notificación (alternativa a rol_id) |
| `icono` | varchar(40) | Clase FontAwesome 4 para mostrar en campana (ej: `fa-clock-o`) |
| `color` | varchar(20) | Color hexadecimal del badge en campana (ej: `#ef4444`) |
| `prioridad` | enum | `informativa` / `media` / `alta` / `critica` |
| `activo` | boolean | Solo las reglas activas se evalúan en cada ejecución |
| `created_at` / `updated_at` | timestamps | — |

**Notas de diseño:**
- Una regla debe tener `rol_id` o `area_id`, no ambos.
- `parametro_dias` y `parametro_umbral` son mutuamente excluyentes según el tipo.
- Los tipos `recuperacion_vencida` no necesitan ningún parámetro.

---

## 4. LOS 6 TIPOS DE ALERTA

### 4.1 — `recuperacion_proxima` (Recuperación próxima)
**¿Qué detecta?** Productos que según su fecha de última compra y su tiempo de recuperación configurado, tienen su fecha límite de reabastecimiento a N días o menos.

**Parámetro:** `parametro_dias` — días de anticipación (ej: 15, 7, 1)

**Condición de activación:**
```
fecha_limite = ultima_compra + tiempo_recuperacion_meses (en meses)
Se activa cuando: fecha_limite está entre HOY + (N-1) días y HOY + (N+1) días
                  Y el producto aún tiene stock > 0
```

**Ejemplo práctico:** Un producto tiene tiempo de recuperación de 2 meses y se compró el 1 de mayo. Su fecha límite es el 1 de julio. Con una regla de 15 días de aviso, la alerta se dispara el ~16 de junio, cuando aún hay stock y hay tiempo de hacer el pedido.

**Prioridades típicas:** Media (15 días), Alta (7 días), Crítica (1 día)

---

### 4.2 — `recuperacion_vencida` (Recuperación vencida)
**¿Qué detecta?** Productos cuya fecha límite de recuperación ya pasó pero que aún tienen stock disponible.

**Parámetro:** Ninguno (no requiere configuración de días)

**Condición de activación:**
```
fecha_limite = ultima_compra + tiempo_recuperacion_meses
Se activa cuando: fecha_limite < HOY Y stock_actual > 0
También calcula: DATEDIFF(HOY, fecha_limite) → días_vencido
```

**Significado:** El proveedor debería haber entregado ya y el stock existente es el último. Si se agota, no hay reabastecimiento inmediato previsto.

**Columna extra calculada:** `dias_vencido` — cuántos días lleva vencida la fecha.

---

### 4.3 — `sin_ventas` (Sin ventas recientes)
**¿Qué detecta?** Productos activos con stock disponible que no han registrado ninguna venta en los últimos N días.

**Parámetro:** `parametro_dias` — días sin ventas (ej: 30, 60, 90)

**Condición de activación:**
```
ultima_venta < CURDATE() - N días   (o es NULL — nunca se vendió)
Y stock_actual > 0
```

**¿Por qué importa?** Un producto con stock disponible que no vende es capital inmovilizado. Puede necesitar ajuste de precio, campaña de ventas o ser descatalogado.

**Escalado típico:**
- 30 días → Media (revisar)
- 60 días → Alta (acción urgente)
- 90 días → Crítica (liquidar o descatalogar)

---

### 4.4 — `baja_rotacion` (Baja rotación)
**¿Qué detecta?** Productos activos con stock que vendieron menos de N unidades en los últimos 60 días.

**Parámetro:** `parametro_umbral` — ventas mínimas esperadas en 60 días (ej: 5)

**Condición de activación:**
```
SUM(ventas_ultimos_60_dias) < parametro_umbral
Y stock_actual > 0
```

**Diferencia con `sin_ventas`:** Este tipo detecta productos que SÍ se venden pero con volumen insuficiente. Un producto que vendió 3 unidades en 60 días no está muerto pero tampoco justifica el espacio y el capital que ocupa.

**Columna calculada:** `ventas_60d` — unidades vendidas en los últimos 60 días.

---

### 4.5 — `sobreinventario` (Sobreinventario)
**¿Qué detecta?** Productos cuyo stock actual cubre más de N meses de demanda proyectada.

**Parámetro:** `parametro_umbral` — meses de cobertura máxima aceptable (ej: 6)

**Condición de activación:**
```
promedio_mensual = ventas_90_dias / 3
cobertura_meses  = stock_actual / promedio_mensual
Se activa cuando: cobertura_meses > parametro_umbral Y stock_actual > 0
```

**Caso especial:** Si ventas_90_dias = 0, la cobertura es 9999 (infinita) — se activa siempre si hay stock.

**Columnas calculadas:** `prom_mensual`, `cobertura_meses`

**¿Qué hacer?** Evaluar promotar el producto, transferirlo a otra sucursal, o suspender compras temporalmente.

---

### 4.6 — `incremento_demanda` (Incremento de demanda)
**¿Qué detecta?** Productos cuyas ventas de los últimos 30 días crecieron X% o más respecto a los 30 días anteriores (días 31-60 atrás).

**Parámetro:** `parametro_umbral` — porcentaje mínimo de crecimiento (ej: 40)

**Fórmula de crecimiento:**
```
pct_crecimiento = ((ventas_0_30d - ventas_30_60d) / ventas_30_60d) × 100
Se activa cuando: pct_crecimiento >= parametro_umbral Y ventas_30_60d > 0
```

**¿Por qué es informativa y no crítica?** Un aumento de demanda es una buena señal pero requiere acción preventiva (comprar más antes de quedarse sin stock), no una corrección urgente. Por eso la prioridad típica es "informativa".

**Columnas calculadas:** `ventas_30d`, `ventas_30d_ant`, `pct_crecimiento`

---

## 5. CÓMO SE EVALÚAN LAS REGLAS — CONSULTAS SQL

Todas las consultas comparten la misma base:
- Solo productos activos (`estado_producto_id = 1`)
- Solo facturas válidas (`estado_factura_id = 1`)
- Stock calculado desde `recibido_bodega.cantidad_disponible`
- Información de subcategoría desde `sub_categoria` (JOIN izquierdo)

**Columnas comunes devueltas por todos los tipos:**
```
producto_id, producto_nombre, stock_actual,
codigo_barra, precio_base, ultimo_costo_compra,
costo_promedio, sub_categoria
```

**Ejecución:** El método `getProductosAfectados()` del modelo recibe el tipo de la regla
y enruta a la consulta correspondiente con un `match()`:

```php
return match ($this->tipo) {
    'recuperacion_proxima' => $this->queryRecuperacionProxima(),
    'recuperacion_vencida' => $this->queryRecuperacionVencida(),
    'sin_ventas'           => $this->querySinVentas(),
    'baja_rotacion'        => $this->queryBajaRotacion(),
    'sobreinventario'      => $this->querySobreinventario(),
    'incremento_demanda'   => $this->queryIncrementoDemanda(),
    default                => collect(),
};
```

Este método es compartido entre el Job de evaluación diaria y la pantalla de reporte,
garantizando que lo que se notificó es exactamente lo que aparece en el informe.

---

## 6. EL JOB DE EVALUACIÓN

**Clase:** `App\Jobs\AlertasRotacionInventarioJob`  
**Interface:** `ShouldQueue` (ejecuta en la cola de Laravel)

### Flujo de ejecución del Job

```
1. AlertaRotacionConfig::activas()->get()
   → Obtiene todas las reglas con activo=true

2. Para cada regla:
   a. $regla->resolverUsuariosDestino()
      → Obtiene usuarios del rol o área configurada
      → Si está vacío → SKIP (no hay a quién notificar)

   b. $regla->getProductosAfectados()
      → Ejecuta la consulta SQL del tipo
      → Si está vacío → SKIP (ningún producto cumple la condición hoy)

   c. $this->reglaYaNotificada($regla->id)
      → Busca en tabla notifications una notificación de esta regla
        creada en las últimas 23 horas
      → Si existe → SKIP (deduplicación, evita spam diario)

   d. Arma el mensaje con mensajeResumen():
      → Título contextual según tipo de alerta (con emojis)
      → Mensaje con los 3 primeros productos afectados + conteo total

   e. Notification::send($usuarios, new InventarioAlertaNotification(...))
      → Guarda una fila en tabla notifications por cada usuario
```

### Construcción del mensaje de notificación

```php
// Ejemplos de títulos generados según tipo:
'recuperacion_proxima' → "⏰ 4 producto(s) con recuperación próxima"
'recuperacion_vencida' → "🚨 2 producto(s) con recuperación vencida"
'sin_ventas'           → "📦 12 producto(s) sin ventas recientes"
'baja_rotacion'        → "📉 8 producto(s) con baja rotación"
'sobreinventario'      → "🏭 3 producto(s) en sobreinventario"
'incremento_demanda'   → "📈 5 producto(s) con aumento de demanda"

// Mensaje cuerpo:
"Regla «Sin ventas — 60 días»: «Aceite Motor 20W50», «Filtro Aire Premium»,
«Pastillas de Freno» y 9 más. Toca para ver el informe completo."
```

---

## 7. SISTEMA DE DEDUPLICACIÓN

**Problema que resuelve:** Sin deduplicación, si el Job corre a las 06:00 todos los días,
el mismo producto estancado generaría una notificación nueva cada día hasta que se resuelva,
saturando la campana del usuario.

**Mecanismo:** Antes de enviar, el Job consulta la tabla `notifications`:
```sql
SELECT COUNT(*) FROM notifications
WHERE type = 'App\Notifications\InventarioAlertaNotification'
  AND created_at >= NOW() - INTERVAL 23 HOUR
  AND JSON_EXTRACT(data, '$.regla_id') = {regla_id}
```

Si existe al menos un registro → la regla ya fue notificada hoy → se salta.

**Ventana de 23 horas** (no 24): margen de seguridad para que aunque el cron
corra unos minutos antes de la hora exacta, no vuelva a enviar.

**Importante:** La deduplicación es por REGLA, no por producto. Si la regla "Sin ventas 60 días"
fue notificada hoy, no se vuelve a enviar aunque mañana tenga más productos afectados.
Solo al día siguiente (pasadas las 23h) puede volver a notificar.

---

## 8. DESTINO DE LAS NOTIFICACIONES — RESOLUCIÓN DE USUARIOS

El método `resolverUsuariosDestino()` del modelo determina quién recibe la alerta:

```
Si rol_id está definido:
   → User::where('rol_id', rol_id)->where('estado_id', 1)->get()
   → Todos los usuarios activos con ese rol

Si area_id está definido (y rol_id = NULL):
   → User::whereHas('rol', fn($q) => $q->where('area_id', area_id))
       ->where('estado_id', 1)->get()
   → Todos los usuarios activos cuyos roles pertenecen a esa área

Si ninguno está definido:
   → collect() vacío → la regla no notifica a nadie
```

**Filtro de usuario activo:** Solo `estado_id = 1`. Los usuarios desactivados
no reciben alertas aunque estén en el rol configurado.

---

## 9. LA NOTIFICACIÓN GENERADA (InventarioAlertaNotification)

**Clase:** `App\Notifications\InventarioAlertaNotification`  
**Canal:** `database` (se almacena en tabla `notifications`)

### Payload almacenado en el campo JSON `data`:

```json
{
  "titulo":             "⏰ 4 producto(s) con recuperación próxima",
  "mensaje":            "Regla «Recuperación próxima — 7 días»: «Aceite 20W50»...",
  "url":                "/alertas/rotacion/2/reporte",
  "icono":              "fa-clock-o",
  "color":              "#f59e0b",
  "tipo_alerta":        "recuperacion_proxima",
  "prioridad":          "alta",
  "regla_id":           2,
  "regla_nombre":       "Recuperación próxima — 7 días",
  "productos_count":    4,
  "productos_resumen":  ["Aceite Motor 20W50", "Filtro Aceite", "Filtro Aire"]
}
```

**URL de destino:** `/alertas/rotacion/{regla_id}/reporte` — apunta directamente
al reporte completo de la regla, donde el usuario ve la lista completa de productos
afectados con todos sus datos.

---

## 10. CICLO DE VIDA COMPLETO DE UNA ALERTA

```
DÍA 1 — 06:00
│
├─ Scheduler: php artisan schedule:run
│  └─ Ejecuta AlertasEvaluarRotacion::handle()
│     └─ new AlertasRotacionInventarioJob()->handle()
│
├─ Regla evaluada: "Sin ventas — 60 días" (activo=true)
│
├─ resolverUsuariosDestino() → 3 usuarios con rol Administrador
│
├─ querySinVentas(parametro_dias=60) → 12 productos sin ventas en 60d
│
├─ reglaYaNotificada(id=5) → false (no hay notificaciones previas de esta regla)
│
├─ mensajeResumen() → "📦 12 producto(s) sin ventas recientes"
│
├─ Notification::send([user1, user2, user3], new InventarioAlertaNotification(...))
│  └─ Inserta 3 filas en tabla notifications (una por usuario)
│
└─ Campana del header: muestra badge "+1" no leída
   └─ Usuario hace clic → va a /alertas/rotacion/5/reporte
      └─ Componente AlertasRotacionReporte ejecuta de nuevo querySinVentas()
         y muestra la lista completa actualizada de los 12 productos
```

---

## 11. DISPARADORES — EJECUCIÓN AUTOMÁTICA VS. MANUAL

### Ejecución automática (Scheduler)
El comando `alertas:evaluar-rotacion` está registrado en el Kernel de Laravel
para ejecutar diariamente (generalmente a las 06:00 AM):

```php
// App\Console\Kernel.php
$schedule->command('alertas:evaluar-rotacion')->dailyAt('06:00');
```

**Requisito:** El servidor debe tener configurado el cron:
```
* * * * * php /ruta/al/proyecto/artisan schedule:run >> /dev/null 2>&1
```

### Ejecución manual (desde la UI)
El botón "Ejecutar ahora" en la pantalla de configuración dispara el método
`alertaEjecutarAhora()` del componente Livewire, que ejecuta el comando
como proceso PHP independiente para no bloquear la petición HTTP:

```php
// En Windows (Laragon):
pclose(popen("start /B \"\" \"{$php}\" {$artisan} alertas:evaluar-rotacion", 'r'));

// En Linux/servidor:
exec("{$php} {$artisan} alertas:evaluar-rotacion > /dev/null 2>&1 &");
```

Tras ejecutar, aparece el mensaje: *"Evaluación enviada. Las notificaciones
aparecerán en la campana en breve."*

---

## 12. PANTALLA DE CONFIGURACIÓN

**URL:** `/configuracion/notificaciones` (sección inferior de la página)  
**Componente:** `App\Http\Livewire\Configuracion\ConfiguracionNotificaciones`

### Lo que muestra la sección de alertas:

**Cards de resumen por tipo** — Para cada uno de los 6 tipos de alerta, muestra
cuántas reglas están configuradas y cuántas están activas.

**Tabla de reglas** — Lista todas las reglas de `alerta_rotacion_config` con:
- Nombre de la regla
- Tipo (badge verde con texto)
- Parámetro (descripción legible: "Aviso con 7 día(s) de anticipación")
- Prioridad (badge con color: rojo=crítica, naranja=alta, amarillo=media, indigo=informativa)
- Destinatario (rol o área)
- Toggle activo/inactivo

**Acciones disponibles:**
- Crear nueva regla (modal)
- Editar regla existente (modal pre-poblado)
- Activar / desactivar toggle
- Ejecutar ahora (botón en el header verde)

### Modal de creación/edición — Campos:

| Campo | Descripción |
|---|---|
| Nombre | Texto libre descriptivo (máx 120 caracteres) |
| Tipo | Select con los 6 tipos (cambia dinámicamente los parámetros mostrados) |
| Días de anticipación | Solo aparece si el tipo usa `parametro_dias` |
| Umbral numérico | Solo aparece si el tipo usa `parametro_umbral` |
| Prioridad | Select: Informativa / Media / Alta / Crítica |
| Destinatario | Tabs "Por rol" / "Por área" + select según elección |
| Ícono | Input de texto (clase FontAwesome 4, ej: `fa-bell`) |
| Color | Selector de color + presets de 7 colores |
| Activo | Toggle on/off |

---

## 13. REGLAS PREDETERMINADAS DEL SISTEMA

La migración `2026_06_10_000001_create_alerta_rotacion_config_table.php` instala
estas reglas al hacer `php artisan migrate` (todas inactivas por defecto):

| Nombre | Tipo | Parámetro | Prioridad |
|---|---|---|---|
| Recuperación próxima — 15 días | recuperacion_proxima | 15 días | Media |
| Recuperación próxima — 7 días | recuperacion_proxima | 7 días | Alta |
| Recuperación próxima — 1 día | recuperacion_proxima | 1 día | Crítica |
| Recuperación vencida con stock activo | recuperacion_vencida | — | Alta |
| Sin ventas — 30 días | sin_ventas | 30 días | Media |
| Sin ventas — 60 días | sin_ventas | 60 días | Alta |
| Sin ventas — 90 días | sin_ventas | 90 días | Crítica |
| Baja rotación (< 5 ventas en 60 días) | baja_rotacion | umbral=5 | Media |
| Sobreinventario (cobertura > 6 meses) | sobreinventario | umbral=6 | Media |
| Incremento de demanda (≥ 40%) | incremento_demanda | umbral=40 | Informativa |

Todas apuntan por defecto al `rol_id = 1` (Administrador). Para activar cualquiera:
ir a la pantalla de configuración y encender el toggle.

---

## 14. MODELO AlertaRotacionConfig — MÉTODOS Y LÓGICA

**Clase:** `App\Models\AlertaRotacionConfig`  
**Tabla:** `alerta_rotacion_config`

### Scopes
```php
scopeActivas()          // where('activo', true)
scopePorTipo($tipo)     // where('tipo', $tipo)
```

### Atributos calculados (accessors)
```php
getPrioridadLabelAttribute()      // "Crítica", "Alta", "Media", "Informativa"
getPrioridadColorAttribute()      // "#ef4444", "#f97316", "#f59e0b", "#6366f1"
getDescripcionParametroAttribute() // Texto legible del parámetro según tipo
```

**Ejemplos de descripciones:**
- recuperacion_proxima → "Aviso con 7 día(s) de anticipación"
- sin_ventas → "Dispara si no hay ventas en 60 días"
- baja_rotacion → "Dispara si ventas 60d < 5.0 unidades"
- sobreinventario → "Dispara si cobertura > 6.0 meses"
- incremento_demanda → "Dispara si demanda crece ≥ 40.0%"

### Método principal
```php
getProductosAfectados(): Collection
```
Enruta al método de consulta según `$this->tipo`. Devuelve colección de productos
que cumplen la condición de la regla HOY.

### Método de resolución de usuarios
```php
resolverUsuariosDestino(): Collection
```
Devuelve usuarios activos del rol o área configurada.

---

## 15. TABLAS DE LA BASE DE DATOS INVOLUCRADAS

| Tabla | Rol en el sistema de alertas |
|---|---|
| `alerta_rotacion_config` | Configuración de cada regla de alerta |
| `producto` | Catálogo de productos (estado, tiempo recuperación, precios) |
| `recibido_bodega` | Stock disponible actual por bodega/sección |
| `sub_categoria` | Subcategoría del producto (contexto adicional en reporte) |
| `compra` | Historial de compras — fecha última compra para tipos de recuperación |
| `compra_has_producto` | Detalle de compras — vincula compra con producto |
| `venta_has_producto` | Ventas por producto — base para sin_ventas, baja_rotacion, incremento_demanda |
| `factura` | Filtro de facturas válidas (`estado_factura_id = 1`) |
| `notifications` | Tabla estándar Laravel donde se almacenan las alertas enviadas |
| `users` | Usuarios destinatarios de las notificaciones |
| `rol` | Para resolver usuarios por rol |
| `area` | Para resolver usuarios por área |
| `configuracion_sistema` | Switch global del sistema (aplica a notificaciones de flujo, no a estas alertas) |

**Nota:** Las alertas inteligentes de inventario NO están sujetas al switch global
`notificaciones_sistema_activo`. Ese switch solo controla las notificaciones de flujo
de venta. Las alertas de inventario corren siempre mientras el scheduler esté activo
y la regla esté marcada como activa.

---

## 16. CONEXIÓN CON EL MÓDULO DE ANALÍTICA

Las Alertas Inteligentes de Inventario y el módulo de Analítica de Productos comparten
el mismo conjunto de datos e indicadores. Ver el archivo `CONEXION_ALERTAS_ANALITICA.md`
para la documentación completa de esta integración.

**Puntos de conexión directa:**
- El **Centro de Alertas** de `/reportes/analitica_de_productos` usa los mismos
  criterios (sin ventas en el período, caída de ventas, crecimiento acelerado) que
  las reglas de alertas, pero calculados sobre el período filtrado.
- El **badge de estado** de `/reportes/analitica_de_productos/{id}` refleja
  las mismas condiciones que dispararían alertas de baja rotación o sobreinventario.
- El reporte `/alertas/rotacion/{id}/reporte` muestra la misma lista de productos
  que `getProductosAfectados()` calcula al momento de la evaluación diaria.

---

*Documento generado a partir del análisis del código fuente. Mayo 2026.*  
*Archivos fuente principales:*  
*`App\Models\AlertaRotacionConfig`*  
*`App\Jobs\AlertasRotacionInventarioJob`*  
*`App\Console\Commands\AlertasEvaluarRotacion`*  
*`App\Notifications\InventarioAlertaNotification`*  
*`App\Http\Livewire\Configuracion\ConfiguracionNotificaciones`*
