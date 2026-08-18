# MÓDULO: Comisiones / Empleado (Mis Comisiones)
**URL:** `/comisiones/empleado`

---

## ¿Para qué sirve este módulo?

Es el **tablero personal de comisiones** del empleado autenticado. Permite a cada colaborador ver, de forma transparente y detallada, cuánto ha ganado en comisiones, en qué meses, por qué facturas y qué productos generaron esos montos.

**No genera ni recalcula comisiones.** Solo presenta datos ya acreditados en `comision_empleado` y datos de trazabilidad en `facturas_comision` y `producto_comision`.

---

## Archivos involucrados

| Capa | Archivo |
|------|---------|
| Backend (Livewire) | `app/Http/Livewire/Comisiones/Escalado/MisComisiones.php` |
| Vista | `resources/views/livewire/comisiones/escalado/mis-comisiones.blade.php` |
| JS | `public/js/js_proyecto/comisiones/Escalado/misComisiones.js` |

---

## Tablas de base de datos que consume

| Tabla | Para qué |
|-------|---------|
| `comision_empleado` | Acumulados mensuales por usuario/rol. Fuente principal de KPIs y tabla histórica. |
| `facturas_comision` | Facturas comisionadas para conteos y detalle por mes. |
| `producto_comision` | Detalle de productos para el ranking de top productos. |
| `users` | Nombre e identificación del usuario autenticado. |
| `rol` | Nombre del rol para mostrar en cada fila del historial. |
| `factura` | Datos de la factura (cliente, fecha). |
| `cliente` | Nombre del cliente para el modal de detalle. |
| `producto` | Nombre del producto para el top. |

---

## Todos los endpoints del módulo

| Método | URL | Función |
|--------|-----|---------|
| GET | `/comisiones/empleado` | Vista principal con KPIs, gráfica y tablas (render en servidor) |
| GET | `/listar/empleado/comision` | Historial mensual en DataTable (Ajax) |
| GET | `/comision/empleado/top-productos` | Top 10 productos por monto de comisión |
| GET | `/comision/empleado/chart-mensual` | Serie mensual para la gráfica (últimos 18 meses) |
| GET | `/comision/empleado/detalle-mes` | Detalle de facturas de un mes y rol específico |

---

## Funcionalidades detalladas

### 1. Render inicial — KPIs del hero banner

Al cargar la página, el servidor calcula en una sola consulta SQL los siguientes indicadores para el usuario autenticado:

| KPI | Descripción | Fórmula |
|-----|-------------|---------|
| `total_historico` | Todo lo ganado en comisiones desde siempre | `SUM(comision_acumulada)` |
| `total_mes_actual` | Lo ganado en el mes en curso | `SUM WHERE mes = mes_actual` |
| `total_anio_actual` | Lo ganado en el año en curso | `SUM WHERE YEAR = año_actual` |
| `meses_activos` | Meses en que tuvo al menos una comisión | `COUNT(DISTINCT mes_comision)` |
| `facturas_totales` | Total de facturas comisionadas históricamente | `COUNT(DISTINCT factura_id)` |
| `facturas_mes_actual` | Facturas comisionadas en el mes en curso | `COUNT(DISTINCT factura_id WHERE mes = actual` |

---

### 2. KPI adicionales calculados en la vista (Blade)

| KPI | Fórmula |
|-----|---------|
| Promedio mensual | `total_historico / meses_activos` (0 si sin meses) |
| Mejor mes | Consulta adicional: mes con mayor `SUM(comision_acumulada)` |
| Variación vs mes anterior | `(último_mes - penúltimo_mes) / penúltimo_mes × 100` (si hay al menos 2 meses) |

---

### 3. Gráfica histórica — `chartMensual`

Devuelve serie de hasta los **últimos 18 meses** con comisión del usuario.

Campos por punto:
- `periodo` (YYYY-MM)
- `etiqueta` (Ene 2026, Feb 2026, etc.)
- `comision_acumulada` (suma del mes, agrupada por mes_comision)

Renderizado con **Chart.js** como gráfica de línea con relleno de área y tooltip en Lempiras.

---

### 4. Tabla de historial mensual — `listarComisionesEmpleado`

Una fila por **mes + rol** del usuario autenticado.

Columnas:
| Columna | Origen |
|---------|--------|
| Mes | `mes_letra` (Enero, Febrero...) |
| Año | `YEAR(mes_comision)` |
| Rol | `rol.nombre` |
| Comisión | `SUM(comision_acumulada)` por mes/rol |
| Facturas | `COUNT(DISTINCT facturas_comision.factura_id)` del mes/rol |
| Última actualización | `MAX(fecha_ult_modificacion)` |
| Badge | Indicador visual si es el mes actual |

**Interacción:** al hacer clic en una fila, se abre automáticamente el modal de detalle de facturas de ese mes/rol.

**Export:** botón de exportación a Excel incluido en la DataTable.

---

### 5. Top 10 productos — `topProductos`

Permite filtrar por:
- **Todo el historial**
- **Año actual**
- **Mes actual**

Por cada producto calcula:

| Campo | Fórmula |
|-------|---------|
| Unidades vendidas | `SUM(pc.cantidad)` |
| Monto total comisión | `SUM(pc.monto_comision × pc.cantidad)` |
| Precio promedio | `AVG(pc.precio_venta)` |
| En cuántas facturas apareció | `COUNT(DISTINCT fc.factura_id)` |

Muestra el ranking con barra de progreso proporcional al producto de mayor monto.

**Filtro de usuario:** solo incluye facturas_comision cuyos `rol_id` están asociados al usuario autenticado en `comision_empleado`.

---

### 6. Detalle de facturas del mes — `detalleFacturasMes`

Se muestra en un modal al hacer clic en cualquier fila del historial.

**Entrada:**
- `periodo` (formato YYYY-MM)
- `rol_id` (opcional; si se pasa, filtra solo ese rol)

**Por cada factura:**
| Campo | Origen |
|-------|--------|
| # Factura | `facturas_comision.factura_id` |
| Fecha cierre | `facturas_comision.fecha_cierre_factura` |
| Monto comisión | `facturas_comision.monto_rol` |
| Rol | `rol.nombre` |
| Cliente | `cliente.nombre` |
| Productos | `COUNT(producto_comision.id)` |
| Unidades | `SUM(producto_comision.cantidad)` |

**Filtro:** solo facturas cuyo rol está en los roles del empleado en ese mes.

---

## Reglas de negocio del módulo

| # | Regla |
|---|-------|
| R1 | Solo muestra datos del usuario autenticado. No hay filtro por otro empleado. |
| R2 | El historial agrupa por `mes_comision + rol_id`, por lo que un empleado con 2 roles distintos en el mismo mes verá 2 filas. |
| R3 | Los KPIs del render son calculados en el servidor con SQL optimizado en una sola consulta. |
| R4 | El top de productos filtra por `rol_id IN (SELECT rol_id FROM comision_empleado WHERE users_comision = auth)`, garantizando que solo muestra productos de roles propios. |
| R5 | La variación mensual solo se calcula si existen al menos 2 meses con datos. |
| R6 | El detalle de facturas filtra por período exacto (YYYY-MM) y, opcionalmente, por rol. |
| R7 | Los montos se formatean en Lempiras (L.) con 2 decimales en toda la vista. |
| R8 | El módulo no expone datos de otros empleados; todos los queries filtran por `Auth::id()`. |

---

## Estructura visual de la vista

```
Hero banner
├── Avatar con iniciales del nombre
├── Nombre + Rol + ID + Meses activos
└── Total histórico acumulado

KPI Cards (6 tarjetas)
├── Mes actual (L. X.XX)
├── Mejor mes histórico
├── Total año actual
├── Total facturas históricas
├── Promedio mensual
└── Variación vs mes anterior (%)

Sección principal (2 columnas)
├── Gráfica histórica (Chart.js — línea)
└── Top 10 productos (con selector de período)

Tabla histórica
├── DataTable mensual con export Excel
└── Click en fila → Modal detalle facturas del mes

Modal detalle facturas
└── DataTable de facturas con columnas: factura, fecha, cliente, rol, comisión, productos, unidades
```

---

## Resumen de propósito

`/comisiones/empleado` es la **vista de transparencia personal** del sistema. Permite al colaborador conocer en todo momento cuánto ha ganado, cómo evoluciona su comisión y qué facturas/productos generan ese valor. Es una herramienta de motivación, trazabilidad y confianza en el sistema de comisiones.
