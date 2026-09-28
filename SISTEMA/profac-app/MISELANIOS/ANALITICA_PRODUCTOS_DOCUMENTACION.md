# ANALÍTICA DE PRODUCTOS — DOCUMENTACIÓN COMPLETA

**Sistema:** PROFAC  
**Módulo:** Reportes → Analítica de Productos  
**Rutas:** `/reportes/analitica_de_productos` y `/reportes/analitica_de_productos/{productoId}`  
**Framework:** Laravel 8, Livewire 2  
**Fecha de documentación:** Junio 2026

---

## 1. PROPÓSITO Y VISIÓN GENERAL

El módulo **Analítica de Productos** es el centro de inteligencia de inventario del sistema PROFAC. Provee dos niveles de análisis:

1. **Vista General** (`/reportes/analitica_de_productos`): Dashboard panorámico de toda la cartera de productos activos — KPIs globales, alertas del sistema, gráficas de tendencia y clasificación de productos por comportamiento de ventas.

2. **Vista Individual** (`/reportes/analitica_de_productos/{productoId}`, ej: `/reportes/analitica_de_productos/3936`): Análisis profundo de un producto específico — historial completo de ventas y compras, análisis de rotación, kardex, predicciones de agotamiento y alertas inteligentes personalizadas.

### Problema que resuelve

Sin este módulo, un administrador o jefe de inventario tendría que ejecutar múltiples consultas SQL manuales para identificar:
- ¿Qué productos no se han vendido en 60+ días?
- ¿Cuáles tienen tendencia negativa de ventas?
- ¿Cuánto tiempo de cobertura tiene cada producto según el stock actual?
- ¿Cuándo se va a agotar un producto específico?

El módulo concentra toda esta información en tiempo real con visualizaciones gráficas y alertas automáticas.

---

## 2. COMPONENTES LARAVEL/LIVEWIRE

| Vista | Componente Livewire | Clase |
|-------|---------------------|-------|
| `/reportes/analitica_de_productos` | `analitica-de-productos` | `App\Http\Livewire\Reportes\AnaliticaDeProductos` |
| `/reportes/analitica_de_productos/{id}` | `analisis-producto-individual` | `App\Http\Livewire\Reportes\AnalisisProductoIndividual` |

---

## 3. FUENTES DE DATOS (TABLAS BD)

Ambas vistas leen de las siguientes tablas:

| Tabla | Propósito |
|-------|-----------|
| `producto` | Catálogo maestro de productos |
| `sub_categoria` | Categorización de productos |
| `categoria` | Categoría padre de sub_categoría |
| `marca` | Marca del producto |
| `unidad_medida` | Unidad de medida |
| `recibido_bodega` | Stock actual (campo `existencia_actual`) |
| `venta_has_producto` | Líneas de detalle de ventas |
| `factura` | Cabecera de factura (filtra `estado_factura_id = 1` = válidas) |
| `cai` | Asociación factura-CAI |
| `compra_has_producto` | Líneas de detalle de compras |
| `compra` | Cabecera de compra |
| `recibido_bodega_detalle` | Movimientos de entrada al kardex |
| `devolucion_has_producto` | Devoluciones (cuando aplica) |
| `tipo_factura` | Tipo de documento |
| `cliente` | Datos del cliente (para contexto) |
| `proveedor` | Datos del proveedor (para contexto compras) |
| `estado_producto` | Estado: activo(1), inactivo, descontinuado |

### Filtros base invariables

Siempre se aplican los siguientes filtros:
- `producto.estado_producto_id = 1` → Solo productos activos
- `factura.estado_factura_id = 1` → Solo facturas válidas (no anuladas)

---

## 4. VISTA GENERAL — `/reportes/analitica_de_productos`

### 4.1 Encabezado y Filtros

- **Período de análisis:** Selector de meses (1, 3, 6, 12 meses). Afecta todos los KPIs y gráficas dinámicamente.
- **Filtro de categoría:** Filtra la tabla de productos por sub_categoria.
- **Fecha del reporte:** Mostrada en el header.

### 4.2 KPIs Globales (8 indicadores)

| # | Nombre | Cálculo | Icono | Color |
|---|--------|---------|-------|-------|
| 1 | **Total Activos** | `COUNT(producto.id) WHERE estado_producto_id=1` | `fa-box` | Azul |
| 2 | **En Movimiento** | Productos con al menos 1 venta en el período | `fa-chart-line` | Verde |
| 3 | **Estancados** | Productos sin ventas en el período seleccionado | `fa-pause-circle` | Naranja |
| 4 | **Valor Facturado** | `SUM(vhp.subtotal)` en el período, facturas válidas | `fa-dollar-sign` | Verde oscuro |
| 5 | **Cambio vs Período Anterior** | `((período_actual - período_anterior) / período_anterior) * 100` | `fa-percent` | Verde/Rojo dinámico |
| 6 | **Rotación Promedio** | Promedio de `rotacion_mensual` de todos los productos activos | `fa-sync` | Morado |
| 7 | **Total Facturas** | `COUNT(DISTINCT factura.id)` en el período | `fa-file-invoice` | Azul oscuro |
| 8 | **En Riesgo Agotamiento** | Productos con `cobertura_meses < 1.0` y ventas recientes | `fa-exclamation-triangle` | Rojo |

#### Fórmulas clave KPIs

**Rotación Mensual por producto:**
$$R_{mensual} = \frac{\text{unidades vendidas en período}}{\text{meses del período}}$$

**Valor Facturado:**
$$V = \sum_{i} \text{subtotal}_i \quad \text{donde factura válida y dentro del período}$$

**Cambio porcentual:**
$$\Delta\% = \frac{V_{actual} - V_{anterior}}{V_{anterior}} \times 100$$

**Cobertura en meses:**
$$C = \frac{\text{stock\_actual}}{\text{promedio\_mensual\_ventas}}$$

### 4.3 Índice de Salud del Inventario (0–100)

Un score compuesto que mide la "salud" general del portafolio de productos:

| Componente | Peso | Cálculo |
|------------|------|---------|
| Productos en movimiento | 40% | `(en_movimiento / total_activos) * 40` |
| Valor facturado vs período anterior | 30% | Escala según crecimiento/decrecimiento |
| Productos sin riesgo de agotamiento | 20% | `((total - en_riesgo) / total) * 20` |
| Diversificación por categoría | 10% | Número de categorías con ventas / total categorías |

**Interpretación:**

| Rango | Estado | Color |
|-------|--------|-------|
| 80–100 | Excelente | Verde (`#27ae60`) |
| 60–79 | Bueno | Azul (`#3498db`) |
| 40–59 | Regular | Naranja (`#f39c12`) |
| 0–39 | Crítico | Rojo (`#e74c3c`) |

### 4.4 Gráficas (3 gráficas interactivas)

#### Gráfica 1: Tendencia de Ventas (6 meses)
- **Tipo:** Línea con área (area chart)
- **X:** Últimos 6 meses (nombres: "Ene 2026", "Feb 2026", etc.)
- **Y:** Valor total facturado por mes
- **Datos:** Agrupación `DATE_FORMAT(factura.fecha_emision, '%Y-%m')` con `SUM(subtotal)`
- **Color:** Azul con relleno semitransparente

#### Gráfica 2: Rotación por Categoría (Top 8)
- **Tipo:** Barras horizontales
- **X:** Rotación promedio mensual de la categoría
- **Y:** Nombre de la sub_categoría (top 8 por rotación)
- **Datos:** `AVG(unidades_mes)` agrupado por `sub_categoria_id`
- **Color:** Gradiente verde (mayor rotación) a rojo (menor rotación)

#### Gráfica 3: Distribución de Estados
- **Tipo:** Dona (doughnut)
- **Segmentos:**
  - En Movimiento (verde)
  - Estancados (naranja)
  - En Riesgo (rojo)
  - Sin Datos (gris)
- **Centro:** Muestra total de productos activos

### 4.5 Centro de Alertas del Sistema (7 tipos de alerta)

El centro de alertas detecta automáticamente condiciones problemáticas y muestra listas de productos afectados:

| # | Tipo de Alerta | Condición | Acción disponible |
|---|----------------|-----------|-------------------|
| 1 | **Sin Movimiento** | `dias_sin_venta > 60` | Link a vista individual |
| 2 | **Caída de Ventas** | `tendencia_pct <= -35%` comparando semestres | Link a vista individual |
| 3 | **Tendencia Positiva** | `tendencia_pct >= +50%` (oportunidad) | Link a vista individual |
| 4 | **Riesgo de Agotamiento** | `cobertura_meses < 1` | Link a vista individual |
| 5 | **Sobreinventario** | `cobertura_meses > 6` | Link a vista individual |
| 6 | **Sin Compras Recientes** | Sin entradas en bodega en 90+ días | Link a vista individual |
| 7 | **Alta Rotación Sin Stock** | Alta demanda + stock bajo | Link a vista individual |

Cada alerta muestra el conteo de productos afectados y una lista expandible con los primeros productos, con enlace directo a `/reportes/analitica_de_productos/{id}`.

### 4.6 Tabs de Clasificación de Productos (4 tabs)

#### Tab 1: Productos Críticos
- Sin ventas en 60+ días Y stock > 0
- Ordenados por días sin movimiento (desc)
- Muestra: código, nombre, categoría, días sin mov, stock actual

#### Tab 2: Top Rotación
- Los 20 productos con mayor `rotacion_mensual` en el período
- Ordenados por unidades/mes (desc)
- Muestra: código, nombre, categoría, rotación, valor facturado

#### Tab 3: Sin Movimiento
- Productos activos sin ninguna venta en el período seleccionado
- Muestra: código, nombre, categoría, stock, valor en inventario (`stock * precio_costo`)
- Capital inmovilizado total mostrado en el header del tab

#### Tab 4: Mayor Crecimiento
- Productos con mayor incremento porcentual de ventas vs período anterior
- Requiere ventas en ambos períodos (no aplica a nuevos productos)
- Muestra: código, nombre, categoría, `tendencia_pct`, unidades período actual vs anterior

---

## 5. VISTA INDIVIDUAL — `/reportes/analitica_de_productos/{productoId}`

Ejemplo: `/reportes/analitica_de_productos/3936`

### 5.1 Header del Producto

Muestra información estática del producto:

| Elemento | Fuente |
|----------|--------|
| Imagen del producto | `producto.imagen` (si existe, si no: placeholder) |
| Código interno | `producto.codigo` |
| Nombre completo | `producto.nombre` |
| Marca | `marca.nombre` |
| Sub-categoría | `sub_categoria.nombre` |
| Unidad de medida | `unidad_medida.nombre` |
| **Badge de estado** | Calculado dinámicamente (ver sección 5.8) |
| Botón "Volver" | Navega a `/reportes/analitica_de_productos` |

### 5.2 KPIs Individuales (8 indicadores)

| # | Nombre | Cálculo | Interpretación |
|---|--------|---------|----------------|
| 1 | **Stock Actual** | `recibido_bodega.existencia_actual` | Unidades disponibles en bodega |
| 2 | **Rotación Mensual** | `SUM(vhp.cantidad) / meses_período` | Unidades vendidas por mes en promedio |
| 3 | **Días Sin Movimiento** | `DATEDIFF(NOW(), MAX(factura.fecha_emision))` | Días desde la última venta válida |
| 4 | **Cobertura (meses)** | `stock_actual / rotacion_mensual` | Meses que el stock actual cubre la demanda |
| 5 | **Promedio Mensual** | `SUM(ventas_12m) / 12` | Promedio de los últimos 12 meses siempre |
| 6 | **Tendencia** | `((ult_6m - ant_6m) / ant_6m) * 100` | % de crecimiento/decrecimiento reciente |
| 7 | **Días para Agotamiento** | `(stock_actual / rotacion_mensual) * 30` | Días estimados hasta stock = 0 |
| 8 | **Valor en Inventario** | `stock_actual * precio_costo_promedio` | Capital inmovilizado en este producto |

#### Cálculo detallado de Tendencia

$$\text{tendencia\%} = \frac{\sum_{\text{ult 6m}} \text{ventas} - \sum_{\text{ant 6m}} \text{ventas}}{\sum_{\text{ant 6m}} \text{ventas}} \times 100$$

Donde:
- **Últimos 6 meses:** Desde hace 6 meses hasta hoy
- **Anteriores 6 meses:** Desde hace 12 meses hasta hace 6 meses

### 5.3 Gráficas Individuales (3 gráficas)

#### Gráfica 1: Ventas de los Últimos 12 Meses (Área)
- **Tipo:** Area chart
- **X:** 12 meses (nombres cortos)
- **Y:** Unidades vendidas por mes
- **Datos:** `GROUP BY DATE_FORMAT(fecha, '%Y-%m')` últimos 12 meses
- **Línea adicional:** Promedio mensual (línea punteada horizontal)

#### Gráfica 2: Compras vs Ventas (Barras Agrupadas)
- **Tipo:** Bar chart agrupado (2 series)
- **X:** Últimos 12 meses
- **Y:** Unidades
- **Serie 1:** Compras recibidas (`recibido_bodega_detalle.cantidad`)
- **Serie 2:** Ventas facturadas (`venta_has_producto.cantidad`)
- **Permite ver:** Desequilibrios entre reposición y demanda

#### Gráfica 3: Movimientos en Kardex (Barras Apiladas)
- **Tipo:** Stacked bar chart
- **X:** Últimos 12 meses
- **Y:** Unidades
- **Capas:** Entradas (verde) / Salidas (rojo) / Stock disponible (azul)
- **Permite ver:** Flujo de inventario mes a mes

### 5.4 Análisis de Rotación

Sección con métricas detalladas de rotación:

| Métrica | Cálculo |
|---------|---------|
| **Índice de Rotación** | `ventas_anuales / stock_promedio` (veces/año) |
| **Días de Inventario (DSI)** | `365 / indice_rotacion` |
| **Velocidad de Ventas** | `rotacion_mensual / stock_actual * 100` (% del stock vendido por mes) |
| **Ciclo Reposición Estimado** | Basado en historial de compras: intervalo promedio entre compras |

**Clasificación de rotación:**

| Rango DSI | Clasificación | Color |
|-----------|---------------|-------|
| < 30 días | Alta Rotación | Verde |
| 30–90 días | Rotación Normal | Azul |
| 90–180 días | Baja Rotación | Naranja |
| > 180 días | Muy Baja Rotación | Rojo |

### 5.5 Kardex Reciente (Movimientos)

Tabla de los últimos 50 movimientos del producto, filtrable:

| Columna | Fuente |
|---------|--------|
| Fecha | `fecha_movimiento` |
| Tipo | Entrada / Salida / Ajuste / Devolución |
| Cantidad | `cantidad` (positivo = entrada, negativo = salida) |
| Stock Resultante | `existencia_despues` |
| Referencia | Número de factura o compra |
| Observación | Descripción del movimiento |

**Filtros del kardex:**
- Por tipo de movimiento (Todos / Entradas / Salidas)
- Por rango de fechas
- Búsqueda por referencia

### 5.6 Análisis Predictivo (4 predicciones)

El sistema genera 4 proyecciones basadas en el comportamiento histórico:

#### Predicción 1: Fecha Estimada de Agotamiento
$$\text{fecha\_agotamiento} = \text{hoy} + \left(\frac{\text{stock\_actual}}{\text{rotacion\_diaria}}\right) \text{ días}$$

Donde `rotacion_diaria = rotacion_mensual / 30`

#### Predicción 2: Cantidad Recomendada a Comprar
$$Q_{recomendado} = (\text{rotacion\_mensual} \times 3) - \text{stock\_actual}$$

Se recomienda reponer para cubrir 3 meses de demanda.

#### Predicción 3: Proyección de Ventas (próximos 3 meses)
- Promedio ponderado: últimos 3 meses tienen mayor peso
- Ajustado por `tendencia_pct` si la tendencia es consistente (+/- 2 meses consecutivos)

#### Predicción 4: Valor de Reposición Estimado
$$V_{reposicion} = Q_{recomendado} \times \text{precio\_costo\_promedio\_ult\_compra}$$

### 5.7 Alertas Inteligentes del Producto (hasta 6)

La vista individual genera alertas específicas para el producto analizado:

| Alerta | Condición | Prioridad |
|--------|-----------|-----------|
| ⚠️ Sin movimiento prolongado | `dias_sin_venta > 60` | Alta |
| 📉 Caída de ventas | `tendencia_pct <= -35%` | Alta |
| 🏭 Sobreinventario | `cobertura_meses > 6` | Media |
| ⏰ Próximo a agotarse | `cobertura_meses < 1` | Crítica |
| 📈 Tendencia positiva | `tendencia_pct >= 50%` | Informativa |
| 📦 Sin reposición reciente | Sin compras en 90+ días Y ventas activas | Media |

Cada alerta muestra:
- Ícono de prioridad
- Descripción del problema
- Valor actual del indicador
- Recomendación de acción

### 5.8 Badge de Estado del Producto (Header)

El badge se calcula con la siguiente lógica de prioridad:

```
SI cobertura_meses < 1     → "RIESGO AGOTAMIENTO" (badge rojo)
SINO SI dias_sin_venta > 60 → "SIN MOVIMIENTO"     (badge naranja)
SINO SI tendencia_pct <= -35% → "EN DECLIVE"        (badge amarillo)
SINO SI tendencia_pct >= 50%  → "EN CRECIMIENTO"    (badge verde)
SINO SI cobertura_meses > 6   → "SOBREINVENTARIO"   (badge azul)
SINO                          → "NORMAL"             (badge gris)
```

### 5.9 Comparativos Históricos

Tabla comparativa año a año:

| Período | Unidades Vendidas | Valor Facturado | Rotación Mensual | Var. Uds % | Var. Valor % |
|---------|-------------------|-----------------|------------------|------------|--------------|
| 2024 | X | Lps. X | X uds/mes | — | — |
| 2025 | X | Lps. X | X uds/mes | +/- % | +/- % |
| 2026 (parcial) | X | Lps. X | X uds/mes | +/- % | +/- % |

---

## 6. FÓRMULAS DE REFERENCIA RÁPIDA

| Métrica | Fórmula |
|---------|---------|
| Rotación mensual | `unidades_vendidas_período / meses_período` |
| Cobertura en meses | `stock_actual / rotacion_mensual` |
| Días para agotamiento | `(stock_actual / rotacion_mensual) * 30` |
| Tendencia % | `((ult_6m - ant_6m) / ant_6m) * 100` |
| Valor inventario | `stock_actual * precio_costo_promedio` |
| Índice rotación anual | `ventas_anuales / stock_promedio` |
| DSI (días inventario) | `365 / indice_rotacion` |
| Q recomendado compra | `(rotacion_mensual * 3) - stock_actual` |

---

## 7. CASOS DE USO TÍPICOS

### Caso 1: Identificar productos muertos (capital inmovilizado)
1. Ir a `/reportes/analitica_de_productos`
2. Seleccionar período de 6 meses
3. Tab "Sin Movimiento"
4. Ordenar por "Valor en Inventario" descendente
5. Los productos del top son capital inmovilizado prioridad de liquidación

### Caso 2: Planificar reposición de stock
1. Ver KPI "En Riesgo Agotamiento"
2. Tab "Críticos" (sin movimiento reciente) o alerta "Riesgo de Agotamiento"
3. Clic en producto específico
4. Ver "Cantidad Recomendada a Comprar" en Análisis Predictivo

### Caso 3: Analizar caída de ventas de un producto
1. En vista general, Centro de Alertas → "Caída de Ventas"
2. Clic en el producto
3. Ver Gráfica 1 (área 12 meses) para visualizar el patrón
4. Ver KPI Tendencia para el porcentaje exacto
5. Ver Kardex para identificar si hay devoluciones o problemas puntuales

### Caso 4: Evaluar oportunidad de compra masiva
1. Tab "Mayor Crecimiento" en vista general
2. Seleccionar producto con alta tendencia positiva
3. Ver Análisis Predictivo → Proyección 3 meses
4. Ver Cobertura actual: si < 2 meses y tendencia + → comprar más

---

## 8. INTEGRACIÓN CON OTROS MÓDULOS

Este módulo se conecta con el sistema de Alertas Inteligentes de Inventario:

- Las **mismas condiciones** de las alertas del centro (vista general) son las que se configuran como reglas en `alerta_rotacion_config`
- Cuando el Job `AlertasRotacionInventarioJob` corre, usa los **mismos productos** que aparecen en las alertas de la vista general
- La vista individual `/reportes/analitica_de_productos/{id}` y el reporte de alerta `/alertas/rotacion/{regla_id}/reporte` muestran datos complementarios del mismo producto
- Ver: `MISELANIOS/CONEXION_ALERTAS_ANALITICA.md` para el mapa de conexión detallado
