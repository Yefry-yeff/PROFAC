# CONEXIÓN ENTRE MÓDULOS: ALERTAS INTELIGENTES ↔ ANALÍTICA DE PRODUCTOS ↔ NOTIFICACIONES

**Sistema:** PROFAC  
**Módulos:** Alertas Inteligentes de Inventario + Analítica de Productos + Notificaciones de Flujo  
**Fecha de documentación:** Junio 2026

---

## 1. VISIÓN GENERAL DE LA CONEXIÓN

Los tres módulos forman un **ecosistema de inteligencia de inventario** que comparte datos, criterios y lógica de negocio de forma coordinada:

```
┌─────────────────────────────────────────────────────────────────┐
│                    TABLAS DE BASE DE DATOS                       │
│  producto | recibido_bodega | venta_has_producto | factura       │
│  compra_has_producto | sub_categoria | categoria                │
└──────────────────────┬───────────────────────────────────────────┘
                       │ (todas leen las mismas tablas)
         ┌─────────────┼─────────────┐
         │             │             │
         ▼             ▼             ▼
┌──────────────┐ ┌──────────────┐ ┌──────────────────────────┐
│  ANALÍTICA   │ │   ALERTAS    │ │  NOTIFICACIONES DE FLUJO │
│  DE          │ │  INTELIGENTES│ │  (canal separado,        │
│  PRODUCTOS   │ │  DE          │ │   no comparte lógica     │
│              │ │  INVENTARIO  │ │   de inventario)         │
│ Vista pasiva │ │ Vista activa │ └──────────────────────────┘
│ (solo lee)   │ │ (notifica)   │
└──────┬───────┘ └──────┬───────┘
       │                │
       └────────────────┘
       Comparten MISMAS condiciones
       y MISMOS datos fuente
```

> **Las Notificaciones de Flujo** son un sistema independiente (ventas y tramitación), documentadas por separado en `NOTIFICACIONES_FLUJO_DOCUMENTACION.md`. Este documento se centra en la conexión Alertas Inteligentes ↔ Analítica de Productos.

---

## 2. FUENTE DE VERDAD COMPARTIDA

El método central `AlertaRotacionConfig::getProductosAfectados()` es la **única fuente de verdad** para determinar qué productos están en cada condición. Este mismo criterio aparece tanto en:

- El **Job** `AlertasRotacionInventarioJob` (para enviar notificaciones)
- El **Reporte de alerta** `/alertas/rotacion/{regla_id}/reporte` (para visualizar productos)
- La **Vista general de Analítica** en el Centro de Alertas
- La **Vista individual de Analítica** en las Alertas Inteligentes del producto

### Mapa de equivalencia de condiciones

| Tipo en `alerta_rotacion_config` | Condición SQL | Equivalente en Analítica |
|----------------------------------|---------------|--------------------------|
| `sin_ventas` | `dias_sin_venta > umbral (default 60)` | Alerta "Sin Movimiento" + Badge "SIN MOVIMIENTO" + Tab "Críticos" |
| `baja_rotacion` | `rotacion_mensual < umbral` + `tendencia_pct <= -35%` | Alerta "Caída de Ventas" + Badge "EN DECLIVE" |
| `sobreinventario` | `cobertura_meses > umbral (default 6)` | Alerta "Sobreinventario" + Badge "SOBREINVENTARIO" |
| `recuperacion_proxima` | `dias_sin_venta > umbral` Y próxima reposición calculada | Alerta "Sin Reposición Reciente" |
| `recuperacion_vencida` | Recuperación esperada ya superó fecha | KPI "Días para Agotamiento" < 0 |
| `incremento_demanda` | `tendencia_pct >= +50%` | Alerta "Tendencia Positiva" + Badge "EN CRECIMIENTO" + Tab "Mayor Crecimiento" |

---

## 3. FLUJO DE DATOS COMPARTIDO

### Diagrama de flujo completo

```
[Tablas BD: producto, recibido_bodega, venta_has_producto, factura]
                          │
              ┌───────────┴────────────┐
              │                        │
              ▼                        ▼
   ┌─────────────────────┐   ┌──────────────────────────┐
   │  AlertaRotacionConfig│   │  AnaliticaDeProductos    │
   │  ::getProductosAfec- │   │  (Livewire component)    │
   │  tados()            │   │                          │
   │                     │   │  - Calcula mismas métricas│
   │  Retorna: lista de  │   │  - Muestra alertas       │
   │  productos con       │   │  - Links a individual   │
   │  métricas calculadas│   └──────────┬───────────────┘
   └──────────┬──────────┘              │
              │                         │ clic en alerta
              │                         ▼
              │              ┌──────────────────────────┐
              │              │  AnalisisProductoIndividual│
              │              │  /reportes/analitica/3936 │
              │              │                          │
              │              │  - KPIs individuales     │
              │              │  - Alertas del producto  │
              │              │  - Predicciones          │
              │              └──────────────────────────┘
              │
              ▼
   ┌─────────────────────┐
   │AlertasRotacionInven-│
   │tarioJob             │
   │                     │
   │  - Verifica dedup   │
   │  - Envía notif.     │
   │  via canal database │
   └──────────┬──────────┘
              │ notificación enviada
              ▼
   ┌─────────────────────┐
   │  notifications      │
   │  (tabla Laravel)    │
   │                     │
   │  URL payload:       │
   │  /alertas/rotacion/ │
   │  {regla_id}/reporte │
   └──────────┬──────────┘
              │ usuario hace clic
              ▼
   ┌─────────────────────────────────────┐
   │  Reporte de Alerta                  │
   │  /alertas/rotacion/{regla_id}/reporte│
   │                                     │
   │  Muestra los mismos productos que   │
   │  getProductosAfectados() retornaría │
   │  para esa regla en tiempo real      │
   └─────────────────────────────────────┘
```

---

## 4. CONEXIONES DIRECTAS (LINKS)

### Desde Vista General de Analítica → Vista Individual

En el **Centro de Alertas** de `/reportes/analitica_de_productos`, cada producto listado tiene un enlace directo:

```html
<a href="/reportes/analitica_de_productos/{{ $producto->id }}">
    Ver análisis
</a>
```

Esto lleva a la vista individual del producto donde se pueden ver todas las métricas detalladas.

### Desde Notificación de Alerta → Reporte de Alerta

Cuando el Job envía una notificación al usuario, el payload contiene:

```json
{
  "url": "/alertas/rotacion/3/reporte",
  "tipo_alerta": "sin_ventas",
  "regla_id": 3,
  "regla_nombre": "Productos Sin Ventas 60 días",
  "productos_count": 15,
  "productos_resumen": ["Producto A", "Producto B", "Producto C"]
}
```

La URL lleva al **Reporte de la Regla** que lista todos los productos que esa regla específica detectó.

### Desde Vista Individual → Vista General

El botón "Volver" en `/reportes/analitica_de_productos/3936` navega de regreso a `/reportes/analitica_de_productos` manteniendo el contexto del usuario.

---

## 5. EQUIVALENCIAS DE CÁLCULO (MISMO SQL, DOS CONTEXTOS)

### Condición `sin_ventas` vs Alerta "Sin Movimiento"

**En `AlertaRotacionConfig::querySinVentas()`:**
```sql
SELECT p.*, rb.existencia_actual as stock,
       DATEDIFF(NOW(), MAX(f.fecha_emision)) as dias_sin_venta
FROM producto p
LEFT JOIN recibido_bodega rb ON rb.producto_id = p.id
LEFT JOIN venta_has_producto vhp ON vhp.producto_id = p.id
LEFT JOIN factura f ON f.id = vhp.factura_id AND f.estado_factura_id = 1
WHERE p.estado_producto_id = 1
GROUP BY p.id
HAVING dias_sin_venta > {umbral_dias}   -- default: 60
```

**En Analítica (Centro de Alertas — Alerta "Sin Movimiento"):**
```sql
-- Misma lógica: dias_sin_venta > 60
-- Mismas tablas, mismo filtro estado_producto_id=1 y estado_factura_id=1
-- Misma función DATEDIFF(NOW(), MAX(fecha_emision))
```

→ **Resultado idéntico:** Un producto que aparece en el Centro de Alertas de Analítica como "Sin Movimiento" también aparecerá en el reporte de la regla `sin_ventas` del módulo de Alertas.

### Condición `sobreinventario` vs Predicción de Cobertura

**En `AlertaRotacionConfig::querySobreinventario()`:**
```sql
HAVING (rb.existencia_actual / rotacion_mensual) > {umbral_meses}  -- default: 6
```

**En Analítica — KPI "Cobertura":**
$$C = \frac{\text{stock\_actual}}{\text{rotacion\_mensual}}$$

**En Analítica — Predicción "Cantidad Recomendada a Comprar":**
$$Q = (\text{rotacion\_mensual} \times 3) - \text{stock\_actual}$$

→ **Conexión:** Un producto con `cobertura > 6` en Analítica aparecerá como sobreinventario tanto en la alerta de Analítica como en el módulo de Alertas Inteligentes si existe una regla activa de tipo `sobreinventario`.

### Condición `incremento_demanda` vs Badge "EN CRECIMIENTO"

**En `AlertaRotacionConfig::queryIncrementoDemanda()`:**
```sql
HAVING tendencia_pct >= {umbral_pct}  -- default: 50
```

**En Analítica — Badge de estado individual:**
```
SI tendencia_pct >= 50% → Badge "EN CRECIMIENTO" (verde)
```

**En Analítica — Tab "Mayor Crecimiento":**
```
Ordena por tendencia_pct DESC → Los primeros son los mismos que el Job detectaría
```

---

## 6. FLUJO DE TRABAJO PRÁCTICO INTEGRADO

### Escenario: Un producto cae en ventas

```
Día 1: Analítica detecta en vista general (Centro de Alertas):
       → "Caída de Ventas" para producto ID 3936
       → tendencia_pct = -45%

Día 1 (misma noche, 06:00 AM via scheduler):
       → alertas:evaluar-rotacion se ejecuta
       → Regla "Baja Rotación" activa detecta producto 3936
       → Sin notificación previa en últimas 23h → ENVÍA
       → Administrador recibe notificación en campana

Día 2: Administrador hace clic en notificación:
       → Navega a /alertas/rotacion/2/reporte
       → Ve lista: 3936 + 12 productos más con baja rotación

Día 2 (misma sesión): Administrador quiere análisis profundo de 3936:
       → Navega manualmente a /reportes/analitica_de_productos/3936
       → Ve Gráfica de 12 meses: caída visual clara
       → Ve KPI Tendencia: -45%
       → Ve Análisis Predictivo: se agota en 4.2 meses
       → Ve Alerta "Caída de Ventas" con recomendación
       → Toma acción: ajuste de precio, promoción, etc.
```

### Escenario: Producto en riesgo de agotamiento

```
Analítica (Vista General):
  → KPI "En Riesgo Agotamiento" = 3 productos
  → Centro Alertas: "Riesgo de Agotamiento" lista 3 productos

Alertas Inteligentes:
  → Si existe regla "recuperacion_proxima" activa: notificación enviada
  → Si NO hay regla activa: solo visible en Analítica (sin notificación automática)

Vista Individual /3936:
  → KPI "Días para Agotamiento" = 18 días ← CRÍTICO
  → Predicción "Cantidad Recomendada": 45 unidades
  → Alerta ⏰ "Próximo a Agotarse" visible
```

---

## 7. CONFIGURACIÓN: DÓNDE SE GESTIONAN

| Componente | Ruta de gestión | Descripción |
|------------|-----------------|-------------|
| Reglas de alerta | `/configuracion/alertas-rotacion` | CRUD de reglas, tipo, umbral, usuarios destino |
| Switch notificaciones flujo | `/configuracion/notificaciones` | ON/OFF global de notificaciones |
| Reglas notificaciones flujo | `/configuracion/notificaciones` | CRUD de reglas por tipo de trámite |
| Analítica (solo lectura) | `/reportes/analitica_de_productos` | No tiene configuración — solo visualización |

---

## 8. INDEPENDENCIA DE MÓDULOS

Aunque comparten los mismos datos fuente, los módulos son independientes en su ejecución:

| Característica | Analítica | Alertas Inteligentes |
|----------------|-----------|----------------------|
| Ejecución | On-demand (usuario abre la página) | Programada (scheduler diario 06:00) |
| Output | Visualización en pantalla | Notificación en tabla `notifications` |
| Configuración necesaria | Ninguna (siempre disponible) | Requiere reglas activas en BD |
| Usuarios que ven | El que tiene permiso de ver el reporte | Los usuarios configurados en cada regla |
| Deduplicación | No aplica (no envía notificaciones) | 23 horas: no duplica notificaciones del día |

---

## 9. RESUMEN DE ARCHIVOS DE CADA MÓDULO

### Alertas Inteligentes de Inventario
- `app/Models/AlertaRotacionConfig.php` → Lógica central, 6 queries
- `app/Jobs/AlertasRotacionInventarioJob.php` → Job que envía notificaciones
- `app/Notifications/InventarioAlertaNotification.php` → Clase de notificación
- `app/Console/Commands/AlertasEvaluarRotacion.php` → Comando artisan
- `app/Http/Livewire/Configuracion/ConfiguracionAlertasRotacion.php` → UI de gestión
- `MISELANIOS/ALERTAS_INTELIGENTES_INVENTARIO.md` → Documentación completa

### Analítica de Productos
- `app/Http/Livewire/Reportes/AnaliticaDeProductos.php` → Vista general
- `app/Http/Livewire/Reportes/AnalisisProductoIndividual.php` → Vista individual
- `MISELANIOS/ANALITICA_PRODUCTOS_DOCUMENTACION.md` → Documentación completa
- `MISELANIOS/ANALITICA_DE_PRODUCTOS_DOCUMENTACION_COMPLETA.md` → Versión extendida anterior

### Notificaciones de Flujo
- `app/Events/FlujoAvanzadoEvent.php` → Evento
- `app/Listeners/NotificarPersonalFlujoListener.php` → Listener
- `app/Notifications/FlujoNotification.php` → Clase de notificación
- `app/Models/NotificacionFlujoConfig.php` → Reglas de configuración
- `MISELANIOS/NOTIFICACIONES_FLUJO_DOCUMENTACION.md` → Documentación completa
