# MÓDULO: Comisiones / General (Reportería de Comisiones)
**URL:** `/comisiones/general`

---

## ¿Para qué sirve este módulo?

Es el módulo de **reportería administrativa y supervisión** del sistema de comisiones. Permite a gerencia y administración analizar comisiones de todos los empleados, filtrar por período/rol/empleado y obtener trazabilidad completa desde el consolidado mensual hasta el producto individual vendido.

**Estado actual de la UI:** la vista muestra únicamente la pestaña de **Nómina** (simplificada en la última iteración). El backend conserva todos los endpoints de los demás reportes listos para reactivarse.

---

## Archivos involucrados

| Capa | Archivo |
|------|---------|
| Backend (Livewire) | `app/Http/Livewire/Comisiones/Escalado/ReportesComisionesGenerales.php` |
| Vista | `resources/views/livewire/comisiones/escalado/reportes-comisiones-generales.blade.php` |
| JS | `public/js/js_proyecto/comisiones/Escalado/reportesComisionesGenerales.js` |

---

## Tablas de base de datos que consume

| Tabla | Para qué |
|-------|---------|
| `comision_empleado` | Fuente de acumulados mensuales. Base de todos los reportes agregados. |
| `facturas_comision` | Trazabilidad de facturas y montos por rol. |
| `producto_comision` | Detalle de líneas de producto por factura comisionada. |
| `comision_escala` | Para obtener el % vigente por rol/categoría de precio en el detalle. |
| `comision_reversiones` | Para el reporte de reversiones y su impacto en comision_empleado. |
| `retencion_mora_log` | Para el KPI de total retenido por mora. |
| `factura` | Datos del documento: CAI, cliente, vendedor, facturador, gestor. |
| `cliente` | Nombre del cliente. |
| `producto` | Nombre y código del producto. |
| `rol` | Nombre del rol comisionado. |
| `users` | Nombre del empleado. |
| `precios_producto_carga` | Para obtener la categoría de precio vendida. |
| `categoria_precios` | Nombre de la categoría de precio. |
| `cliente_categoria_escala` | Nombre de la categoría del cliente. |

---

## Todos los endpoints del módulo

### Catálogos para filtros
| Método | URL | Función |
|--------|-----|---------|
| GET | `/comision/empleados/lista` | Lista de empleados activos para Select2 (filtro q) |
| GET | `/comision/roles/lista` | Lista de roles activos para Select2 (filtro q) |

### KPIs del período
| Método | URL | Función |
|--------|-----|---------|
| GET | `/comision/reporte/stats` | 5 indicadores del período seleccionado |

### Reportes (todos soportan filtros de fecha, empleado_id, rol_id)
| Método | URL | Función |
|--------|-----|---------|
| GET | `/comision/reporte/nomina` | Nómina consolidada por empleado/mes |
| GET | `/comision/reporte/nomina/detalle` | Detalle de facturas de un empleado en un mes |
| GET | `/comision/reporte/empleado` | Detalle por empleado: producto a producto |
| GET | `/comision/reporte/rol` | Comisiones agrupadas por rol y empleado |
| GET | `/comision/reporte/usuarios` | General por usuario: total, facturas, productos |
| GET | `/comision/reporte/productos` | Ranking de productos por monto de comisión |
| GET | `/comision/reporte/facturas` | Auditoría: cada factura con su empleado y comisión |
| GET | `/comision/reporte/ranking` | Ranking de empleados por total de comisión |
| GET | `/comision/reporte/comparativo` | Comparativo mensual de comisiones |
| GET | `/comision/reporte/reversiones` | Auditoría de comisiones revertidas por anulaciones |
| GET | `/comision/reporte/excel` | Descarga Excel del reporte seleccionado |

---

## Filtros del módulo

Los filtros aplican globalmente a todos los reportes y se gestionan desde el panel superior:

| Filtro | Tipo | Descripción |
|--------|------|-------------|
| Fecha inicio | Date | Inicio del rango (YYYY-MM-DD). Default: 1° del mes actual. |
| Fecha fin | Date | Fin del rango. Default: hoy. |
| Empleado | Select2 Ajax | Opcional. Filtra por usuario específico. |
| Rol | Select2 Ajax | Opcional. Filtra por rol comisionado. |

**Normalización de fechas (backend):** soporta tres formatos de entrada:
- `Y-m-d` → uso directo
- `d/m/Y` → convierte a `Y-m-d`
- `m/d/Y` → convierte a `Y-m-d`

Si la fecha viene vacía, usa la fecha actual como fallback.

**Normalización de fechas (frontend):** el JS normaliza antes de enviar usando expresiones regulares, convirtiendo `DD/MM/YYYY` a `YYYY-MM-DD`.

---

## Funcionalidades detalladas

### 1. KPIs del período — `stats`

Calcula 5 indicadores para el período y filtros seleccionados:

| KPI | Origen | Fórmula |
|-----|--------|---------|
| `total_comision` | `comision_empleado` | `SUM(comision_acumulada)` en el rango de meses |
| `total_facturas` | `facturas_comision` | `COUNT(DISTINCT factura_id)` mapeado al usuario real |
| `total_empleados` | `comision_empleado` | `COUNT(DISTINCT users_comision)` |
| `total_retenido` | `retencion_mora_log` | `SUM(monto_retenido)` en el rango de fechas |
| `total_revertido` | `comision_reversiones` | Suma del campo JSON `comisiones_revertidas[].monto_revertido` |

El cálculo de `total_facturas` usa el **mapeo de usuario real por tipo_comision**:
```sql
CASE fc.tipo_comision
  WHEN 1 THEN f.users_id
  WHEN 2 THEN f.users_id
  WHEN 3 THEN f.vendedor
  WHEN 4 THEN f.gestor_entrega
END = empleado_id (si filtro aplica)
```

---

### 2. Nómina consolidada — `reporteNomina`

**Fuente principal:** `comision_empleado` (datos ya acreditados, no recalcula).

**Granularidad:** una fila por empleado + mes.

| Columna | Descripción |
|---------|-------------|
| Empleado | Nombre del usuario |
| Roles | Cantidad y nombres de los roles en que comisionó ese mes |
| Mes | Período en texto (Enero 2026, etc.) |
| Facturas comisionadas | Count de facturas mapeadas al usuario real en ese mes |
| Comisión total | `SUM(comision_acumulada)` del empleado en el mes |
| Botón Ver | Abre modal de detalle para ese empleado/mes |

**Total en pie de tabla:** suma de todas las comisiones mostradas en la página actual.

**Regla crítica del conteo de facturas:**
El conteo no usa directamente `facturas_comision.users_id`. En su lugar usa un sub-query que agrupa `facturas_comision` por el usuario real según `tipo_comision`. Esto evita inflar los conteos cuando un empleado comisiona en múltiples roles para la misma factura.

---

### 3. Detalle de nómina — `detalleNomina`

Se muestra al hacer clic en **"Ver"** en la nómina. Es un modal de ancho ampliado con DataTable.

**Entrada:**
- `empleado_id`
- `mes_clave` (YYYY-MM)

**Proceso:**
1. Obtiene roles activos del empleado en ese mes desde `comision_empleado`.
2. Busca en `facturas_comision` usando mapeo `tipo_comision → usuario real`.
3. Filtra por los roles que tiene el empleado ese mes.
4. Por cada factura calcula:

| Campo | Cálculo |
|-------|---------|
| `comision_original` | `SUM(producto_comision.monto_comision × cantidad)` |
| `retencion_aplicada` | `facturas_comision.retencion_mora_monto` |
| `comision_final` | `facturas_comision.monto_rol` |
| `estado` | `ACTIVA` si estado_id=1 / `REVERTIDA` si estado_id=2 |

5. Agrega observaciones de reversión desde `comision_reversiones` (join por factura_id del mes).
6. Construye resumen y detalle de productos por factura.

**Consistencia con la nómina:** la suma de `comision_final` en el detalle debe coincidir con el `comision_total` del consolidado, porque ambos vienen de la misma fuente (`comision_empleado` y `monto_rol`).

---

### 4. Modal secundario: Detalle de productos por factura

Al hacer clic en **"Ver detalle"** en cualquier fila del detalle de nómina, se abre un segundo modal con el desglose producto a producto de esa factura.

| Columna | Origen |
|---------|--------|
| Producto | `producto.nombre` |
| Categoría Cliente Escala | `cliente_categoria_escala.nombre_categoria` |
| Categoría Precio Vendida | `categoria_precios.nombre` |
| % Comisión | Consultado de `comision_escala` por rol + categoria_precios_id |
| Cantidad | `producto_comision.cantidad` |
| Precio Venta | `producto_comision.precio_venta` |
| Comisión | `producto_comision.monto_comision` |

---

### 5. Exportaciones Excel

**Export detalle de nómina** (desde modal principal):
- Encabezado ejecutivo con empleado, período y fecha de descarga.
- Columnas: Factura, Cliente, Fecha Cierre, Rol, Comisión Original, Retención, Comisión Final, Resumen Producto/Escala, Estado, Observaciones.
- Formato monetario `L. #,##0.00` en columnas monetarias.
- Filtros automáticos en fila de encabezados.

**Export detalle de productos por factura** (desde modal secundario):
- Encabezado con factura, cliente y fecha.
- Columnas: Producto, Cat. Cliente Escala, Cat. Precio Vendida, %, Cantidad, Precio Venta, Comisión.
- Formato porcentaje y monetario aplicado.
- Auto-filtro en encabezados.

Ambos exports usan **SheetJS (XLSX)** desde el frontend, sin llamadas adicionales al servidor.

---

### 6. Reporte por empleado — `reporteEmpleado` *(backend activo, UI no visible)*

Filtra producto a producto para un empleado específico en el rango de fechas.

**Requiere:** `empleado_id` obligatorio.

Usa el mismo CASE de mapeo por `tipo_comision` para garantizar que solo muestra registros realmente asignados a ese empleado.

---

### 7. Reporte por rol — `reporteRol` *(backend activo)*

Muestra la comisión agrupada por rol + empleado usando el acumulado real del mes. Para cada combinación rol/empleado: total de comisiones y número de facturas.

---

### 8. Reporte de productos — `reporteProductos` *(backend activo)*

Agrupa comisiones por producto. Por cada producto: cantidad vendida y total de comisión acumulada.

$$
total\_comision_{producto} = \sum (monto\_comision \times cantidad)_{lineas}
$$

---

### 9. Reporte de facturas — `reporteFacturas` *(backend activo)*

Auditoría: una fila por factura + empleado. Muestra el total de venta, el monto de comisión y el porcentaje efectivo.

$$
\%_{efectivo} = \frac{total\_comision}{total\_venta} \times 100
$$

---

### 10. Ranking — `reporteRanking` *(backend activo)*

Ordena empleados de mayor a menor por comisión total en el período. Por cada uno:
- Total comisión
- Meses activos
- Mejor mes
- Promedio mensual

---

### 11. Comparativo mensual — `reporteComparativo` *(backend activo)*

Agrupa por mes, mostrando por período:
- Total de comisiones
- Empleados activos
- Roles distintos
- Mayor y menor comisión individual

Útil para detectar estacionalidad y comparar meses.

---

### 12. Reversiones — `reporteReversiones` *(backend activo)*

Auditoría de anulaciones de pago que tuvieron impacto en comisiones.

Por cada reversión:
- Factura y cliente
- Usuario que anuló
- Monto del abono anulado
- Total revertido en comisiones (calculado del JSON `comisiones_revertidas`)
- Cantidad de comisiones afectadas
- Si la factura fue reabierta
- Motivo de la anulación

---

## Regla de mapeo de usuario real (aplica a todos los reportes)

Es la regla más crítica del módulo. Garantiza que el empleado correcto aparezca en cada reporte.

```sql
CASE fc.tipo_comision
  WHEN 1 THEN f.users_id        -- Facturador (rol fijo)
  WHEN 2 THEN f.users_id        -- Facturador (rol real)
  WHEN 3 THEN f.vendedor        -- Vendedor
  WHEN 4 THEN f.gestor_entrega  -- Gestor de entrega
END = empleado_id_filtrado
```

Sin esta regla, los reportes mostrarían a todos los involucrados en cada factura como si todos fueran el mismo empleado.

---

## Reglas de negocio completas

| # | Regla |
|---|-------|
| R1 | El selector de empleados solo muestra usuarios con `estado_id = 1` (activos). |
| R2 | Si no se selecciona empleado, los reportes agregan todos. |
| R3 | La nómina consolida por empleado+mes usando `comision_empleado` (dato acreditado, no recalculado). |
| R4 | El conteo de facturas en la nómina usa mapeo por `tipo_comision` para no inflar conteos. |
| R5 | El detalle de nómina filtra solo facturas de los roles que el empleado tuvo activos ese mes. |
| R6 | La suma de `comision_final` en el detalle debe coincidir con `comision_total` del consolidado. |
| R7 | El campo `comision_original` = suma de `producto_comision`; `comision_final` = `monto_rol` (ya con retención aplicada). |
| R8 | El estado `REVERTIDA` en el detalle se determina por `facturas_comision.estado_id = 2`. |
| R9 | Los exports Excel son generados completamente en el cliente (JS/SheetJS), sin carga extra al servidor. |
| R10 | El reporte de reversiones lee el JSON `comisiones_revertidas` para calcular el total revertido. |
| R11 | Los filtros de fecha normalizan formato tanto en frontend como en backend para evitar ambigüedad. |
| R12 | El porcentaje del modal de productos se consulta en vivo de `comision_escala` por rol + categoría de precio vigente. |

---

## Estructura visual de la vista (estado actual)

```
Header — Reportería de Comisiones
│
Panel de Filtros
├── Fecha inicio / Fecha fin
├── Empleado (Select2)
├── Rol (Select2)
├── Botón "Generar Reporte"
└── Botón "Limpiar"
│
Pestaña: Nómina
├── Tabla consolidada empleado/mes
│   ├── Footer con total del período
│   └── Columna Ver → Modal detalle
│
Modal Detalle de Nómina
├── Tabla: factura, cliente, fecha, rol, original, retención, final, resumen, estado
├── Botón Ver detalle → Modal secundario
└── Botón Exportar Excel
│
Modal Detalle de Productos por Factura
├── Tabla: producto, cat.cliente, cat.precio, %, cantidad, precio, comisión
└── Botón Exportar Excel
```

---

## Resumen de propósito

`/comisiones/general` es la **herramienta de supervisión y cierre de nómina** del sistema de comisiones. Permite a administración ver, validar y exportar la nómina variable de comisiones con trazabilidad completa: desde el consolidado mensual por empleado hasta el porcentaje aplicado sobre cada producto vendido. La congruencia entre el consolidado y el detalle está garantizada por diseño, usando las mismas fuentes de datos y el mismo mapeo de usuario real.
