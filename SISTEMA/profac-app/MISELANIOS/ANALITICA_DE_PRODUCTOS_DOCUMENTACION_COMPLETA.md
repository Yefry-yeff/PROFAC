# DOCUMENTACIÓN COMPLETA — MÓDULO ANALÍTICA DE PRODUCTOS
**Sistema PROFAC — Plataforma de Gestión Comercial**  
Fecha de elaboración: Mayo 2026

---

## ÍNDICE

1. [Visión General del Módulo](#1-visión-general-del-módulo)
2. [Sección 1 — Vista General del Inventario (`/reportes/analitica_de_productos`)](#2-sección-1--vista-general-del-inventario)
3. [Sección 2 — Análisis Individual de Producto (`/reportes/analitica_de_productos/{id}`)](#3-sección-2--análisis-individual-de-producto)
4. [Origen de los Datos — Tablas de la Base de Datos](#4-origen-de-los-datos--tablas-de-la-base-de-datos)
5. [Fórmulas y Cálculos Detallados](#5-fórmulas-y-cálculos-detallados)
6. [Interpretación de Indicadores](#6-interpretación-de-indicadores)
7. [Propósito Estratégico del Módulo](#7-propósito-estratégico-del-módulo)

---

## 1. VISIÓN GENERAL DEL MÓDULO

El módulo **Analítica de Productos** es una herramienta de inteligencia de inventario diseñada para que los responsables de compras, gerencia y logística puedan:

- Detectar productos que se están agotando antes de que impacten las ventas.
- Identificar productos estancados que inmovilizan capital sin generar retorno.
- Comparar el comportamiento de ventas a lo largo del tiempo.
- Predecir cuándo y cuánto comprar de cada producto.
- Tomar decisiones basadas en datos reales de movimiento, no en estimaciones.

El módulo se compone de **dos pantallas complementarias**:

| Pantalla | URL | Propósito |
|---|---|---|
| Vista General del Inventario | `/reportes/analitica_de_productos` | Panorámica de todo el portafolio activo |
| Análisis Individual de Producto | `/reportes/analitica_de_productos/{productoId}` | Radiografía completa de un solo producto |

Ambas operan sobre datos **en tiempo real** consultando directamente la base de datos del sistema, sin pre-cálculos ni tablas intermedias.

---

## 2. SECCIÓN 1 — VISTA GENERAL DEL INVENTARIO
**URL:** `/reportes/analitica_de_productos`

### 2.1 Objetivo y Justificación

Esta pantalla responde a la pregunta: **¿Cómo está funcionando el inventario en su conjunto?**

Un gerente o jefe de compras necesita ver de un solo vistazo qué parte del catálogo está activo, qué parte está dormida, cuánto se vendió, y dónde están los riesgos más grandes. Sin esta vista agregada, detectar problemas requeriría revisar cientos de productos uno a uno.

La pantalla permite filtrar por categoría, marca y rango de fechas para segmentar el análisis a una línea de producto específica.

---

### 2.2 Filtros Disponibles

| Filtro | Descripción |
|---|---|
| **Categoría** | Filtra todos los cálculos a una categoría de producto específica |
| **Marca** | Filtra todos los cálculos a una marca específica |
| **Fecha inicio / Fecha fin** | Define el período de análisis (por defecto: últimos 90 días) |

Al cambiar cualquier filtro, **todos los KPIs, gráficas, alertas y tablas se recalculan automáticamente** en tiempo real.

---

### 2.3 KPIs Principales (Indicadores Clave de Rendimiento)

#### KPI 1 — Total de Productos Activos
**¿Qué mide?** La cantidad total de productos que están en estado activo (`estado_producto_id = 1`) dentro del catálogo, aplicando los filtros de categoría/marca seleccionados.

**¿Para qué sirve?** Establecer el universo de análisis. Es la base sobre la que se calcula el porcentaje de movimiento y la salud del inventario.

**Fuente:** Tabla `producto` cruzada con `sub_categoria`, `categoria_producto` y `marca`.

---

#### KPI 2 — Productos en Movimiento
**¿Qué mide?** Cuántos de los productos activos tuvieron al menos una venta dentro del período seleccionado.

**¿Para qué sirve?** Saber qué porcentaje del catálogo está "vivo" comercialmente. Si solo el 40% de los productos se venden, el 60% restante está inmovilizando capital.

**Fuente:** Tablas `venta_has_producto` y `factura` filtradas por el rango de fechas.

---

#### KPI 3 — Productos Estancados
**¿Qué mide?** La diferencia entre productos activos y productos con movimiento.

**Fórmula:** `Estancados = Total Activos − Productos con Movimiento`

**¿Para qué sirve?** Cuantificar el problema de inventario muerto. Un producto estancado no genera ventas pero sí ocupa espacio físico y valor contable.

---

#### KPI 4 — Valor Facturado en el Período
**¿Qué mide?** La suma total de todas las facturas emitidas dentro del rango de fechas seleccionado, en Lempiras.

**¿Para qué sirve?** Dar contexto de volumen de negocio al período analizado. Permite comparar si el inventario soporta el ritmo de ventas actual.

**Fuente:** Tabla `factura`, campo `total`, filtrado por `created_at`.

---

#### KPI 5 — Cambio vs. Período Anterior (%)
**¿Qué mide?** La variación porcentual del valor facturado comparando el período actual con el período anterior de igual duración.

**Fórmula:**
```
pct_cambio = ((valor_actual - valor_anterior) / valor_anterior) × 100
```
El período anterior se calcula automáticamente con la misma cantidad de días que el período seleccionado, retrocediendo desde la fecha de inicio.

**Ejemplo:** Si se analizan los últimos 30 días y se facturó L 200,000, pero en los 30 días anteriores se facturó L 180,000 → cambio = +11.1%

**¿Para qué sirve?** Detectar si el negocio está creciendo, estable o en declive respecto a su propio historial.

---

#### KPI 6 — Rotación Promedio Mensual por Producto
**¿Qué mide?** En promedio, cuántas unidades vende cada producto activo por mes dentro del período analizado.

**Fórmula:**
```
rotacion_promedio = (total_unidades_vendidas / total_activos) / (dias_periodo / 30)
```

**¿Para qué sirve?** Establecer un benchmark de rotación para el portafolio. Si el promedio es 50 unidades/mes y un producto vende 2, claramente está por debajo del estándar de la empresa.

---

#### KPI 7 — Total de Facturas Emitidas
**¿Qué mide?** El número de transacciones de venta (facturas) realizadas en el período.

**¿Para qué sirve?** Contexto operacional. Junto al valor facturado, permite calcular el ticket promedio y entender la densidad de la actividad comercial.

---

#### KPI 8 — Productos en Riesgo de Agotamiento
**¿Qué mide?** Cuántos productos tienen ventas registradas (están activos comercialmente) pero **no tienen definido un tiempo de recuperación** (`tiempo_recuperacion_meses` es NULL), lo que impide calcular cuándo deben reabastecerse.

**¿Para qué sirve?** Identificar un hueco en la configuración del catálogo que impide la planificación de compras. Un producto sin tiempo de recuperación definido no puede disparar alertas de reabastecimiento oportunas.

---

### 2.4 Índice de Salud General del Inventario

**¿Qué es?** Un score del 0 al 100 que resume qué tan saludable está el inventario en general.

**Fórmula:**
```
salud_general = (productos_con_movimiento / total_activos) × 100
(máximo 100, mínimo 0)
```

**Interpretación:**

| Rango | Texto | Significado |
|---|---|---|
| 80–100 | "Inventario saludable y en movimiento" | La gran mayoría del catálogo se está vendiendo |
| 60–79 | "Estable con áreas de atención" | Mayoría activa, pero hay segmentos con problemas |
| 40–59 | "Requiere atención en múltiples categorías" | Menos de la mitad del catálogo genera ventas |
| 0–39 | "Estado crítico — acción inmediata requerida" | El inventario está mayoritariamente detenido |

---

### 2.5 Gráfica — Tendencia de Ventas (Últimos 6 Meses)

**¿Qué muestra?** Una gráfica de barras con el valor total facturado y el número de facturas por cada uno de los últimos 6 meses calendario.

**Fuente:** Tabla `factura` agrupada por mes (`DATE_FORMAT(created_at, '%Y-%m')`).

**¿Para qué sirve?** Visualizar si las ventas generales tienen una tendencia ascendente, descendente o estacional a lo largo del tiempo. Permite detectar meses bajos recurrentes.

---

### 2.6 Gráfica — Rotación por Categoría (Top 8)

**¿Qué muestra?** Las 8 categorías de producto con mayor volumen de unidades vendidas en el período analizado.

**Fuente:** Tablas `venta_has_producto`, `factura`, `producto`, `sub_categoria`, `categoria_producto`, agrupadas por categoría.

**¿Para qué sirve?** Identificar qué líneas de producto mueven más volumen. Ayuda a priorizar qué categorías necesitan mayor atención en reabastecimiento y cuáles pueden reducirse.

---

### 2.7 Distribución del Estado del Inventario (Gráfica Donut)

**¿Qué muestra?** Una estimación visual de la composición del inventario en cuatro estados:

| Estado | Cálculo | Descripción |
|---|---|---|
| **Saludable** | 85% de los productos con movimiento | Productos activos con ventas estables |
| **Riesgo** | 15% de los productos con movimiento | Productos activos pero con señales de alerta (sin tiempo recuperación u otras) |
| **Estancado** | Productos sin ventas / total activos | Productos que no se vendieron en el período |
| **Sobreinventario** | Complemento hasta 100% | Estimado de productos con exceso de stock relativo |

**Importante:** Esta distribución es una **estimación analítica**, no un conteo exacto. Su propósito es dar una visión proporcional del estado del inventario, no un número preciso de productos en cada categoría.

---

### 2.8 Centro de Alertas

**¿Qué es?** Un listado automático de hasta 7 alertas priorizadas generadas por el sistema, basadas en el comportamiento real de los datos.

**Tipos de alertas generadas:**

#### Alertas de Alta Prioridad — "Sin movimiento"
- Se generan para los 4 productos sin ninguna venta en el período, ordenados por mayor precio base (mayor impacto económico primero).
- **Acción sugerida:** Liquidar
- **Criterio:** Producto activo, sin aparición en `venta_has_producto` durante el período analizado.

#### Alertas de Media Prioridad — "Caída de ventas"
- Se generan para productos que vendieron significativamente más en la primera mitad del período que en la segunda.
- **Umbral de activación:** Caída ≥ 35% entre ambas mitades del período.
- **Método:** Se dividen las ventas del período en dos ventanas iguales y se compara el porcentaje de caída. Se muestran los 3 con mayor caída.
- **Acción sugerida:** Revisar precio, disponibilidad o campaña de venta.

#### Alertas Informativas — "Tendencia positiva"
- Se generan para los 2 productos con mayor crecimiento entre la primera y segunda mitad del período.
- **Umbral de activación:** Crecimiento ≥ 50%.
- **Acción sugerida:** Considerar reabastecimiento anticipado para no perder ventas.

---

### 2.9 Tablas de Productos

La sección inferior muestra tablas navegables con 4 pestañas:

#### Pestaña "Críticos" (por defecto)
Los 20 productos con mayor volumen de ventas en el período. Son los productos más importantes del catálogo y los primeros que deben monitorearse para asegurar disponibilidad continua.

**Columnas:** ID, Nombre, Categoría, Unidades Vendidas, Última Venta, Precio Base, Rotación Mensual.

#### Pestaña "Top Rotación"
Igual que "Críticos" pero ordenado por rotación mensual normalizada por el número de días del período:
```
rotacion_mensual = total_vendido / (dias_periodo / 30)
```

#### Pestaña "Sin Movimiento"
Los 20 productos activos sin ninguna venta en el período, ordenados por mayor precio base (los que representan mayor capital inmovilizado).

#### Pestaña "Mayor Crecimiento"
Los 20 productos con mayor porcentaje de crecimiento entre la primera y segunda mitad del período. Se calcula para cada producto que tuvo ventas en ambas mitades:
```
pct_crecimiento = ((ventas_2da_mitad - ventas_1ra_mitad) / ventas_1ra_mitad) × 100
```
Solo se incluyen productos con crecimiento positivo y que tuvieron ventas en la primera mitad (para evitar divisiones por cero).

---

## 3. SECCIÓN 2 — ANÁLISIS INDIVIDUAL DE PRODUCTO
**URL:** `/reportes/analitica_de_productos/{productoId}`

### 3.1 Objetivo y Justificación

Esta pantalla responde a la pregunta: **¿Cómo está funcionando este producto específico, y qué debería hacerse con él?**

Cuando la primera pantalla identifica un producto problemático o destacado, esta segunda pantalla permite ingresar a él y obtener un análisis profundo: su historial de 12 meses, su comportamiento comparativo con el año anterior, cuándo se agotará, cuánto conviene comprar, y qué alertas específicas está generando.

Está diseñada para ser utilizada directamente desde las alertas de la Vista General, haciendo clic en cualquier producto.

---

### 3.2 Cabecera / Header del Producto

**¿Qué muestra?**
- Imagen del producto (si tiene cargada en `img_producto`)
- Nombre completo
- Código de barras
- Marca, categoría y subcategoría como etiquetas visuales
- **Badge de estado** dinámico (ver sección 3.10)
- Stock disponible total
- Proveedor principal (el que más veces ha abastecido el producto históricamente)
- Fecha de última venta
- Fecha de última compra
- Precio base
- Distribución de stock por bodega y sección
- Botones de acceso rápido: Comprar, Transferir, Ajustar inventario, Ver kardex, Exportar análisis

---

### 3.3 KPIs Inteligentes (8 indicadores)

Todos calculados automáticamente al cargar la página, usando los últimos 30 y 90 días como ventanas de análisis.

#### KPI 1 — Stock Disponible
**¿Qué mide?** La suma de todas las unidades en `recibido_bodega.cantidad_disponible` para este producto, en todas las bodegas y secciones.

**¿Por qué importa?** Es el punto de partida de todos los demás cálculos. Si el stock es cero, la mayoría de los análisis predictivos no aplica.

---

#### KPI 2 — Rotación Mensual
**¿Qué mide?** Las unidades vendidas en los últimos 30 días exactos. Representa la demanda actual del producto.

**Fórmula:** `ventas_ultimos_30_dias` (suma directa de `vhp.cantidad` para facturas en los últimos 30 días)

**¿Por qué importa?** Expresa en términos concretos "qué tan rápido se mueve este producto". Una rotación mensual de 500 significa que el producto necesita al menos 500 unidades en bodega cada mes para no romper stock.

---

#### KPI 3 — Días Sin Movimiento
**¿Qué mide?** Los días transcurridos desde la última venta registrada hasta hoy.

**Fórmula:** `dias_sin_movimiento = fecha_hoy − fecha_ultima_venta`

**Semáforo de colores:**
- Verde: 0–7 días (producto activo)
- Amarillo: 8–30 días (atención)
- Rojo: +31 días (producto posiblemente estancado)

**¿Por qué importa?** Un producto sin ventas por 45 días en una tienda activa es una señal de alerta: puede estar descatalogado, con precio incorrecto, sin visibilidad en tienda, o agotado sin reposición.

---

#### KPI 4 — Cobertura Estimada (meses)
**¿Qué mide?** Cuántos meses de demanda puede cubrir el stock actual, basándose en el promedio mensual de los últimos 90 días.

**Fórmula:**
```
cobertura_meses = stock_actual / promedio_mensual_90_dias
```
donde:
```
promedio_mensual_90_dias = ventas_90_dias / 3
```

**¿Por qué importa?** Una cobertura de 0.5 meses significa que en 15 días el producto se agotará si no se compra. Una cobertura de 8 meses podría indicar sobreinventario.

---

#### KPI 5 — Promedio Mensual (90 días)
**¿Qué mide?** Las unidades vendidas por mes en promedio, calculado sobre los últimos 90 días.

**Fórmula:** `promedio_mensual = ventas_90_dias / 3`

**¿Por qué se usa 90 días y no 30?** Un solo mes puede tener estacionalidad o eventos excepcionales. Usar 3 meses suaviza esas variaciones y da una cifra más representativa de la demanda real.

---

#### KPI 6 — Tendencia de Ventas (%)
**¿Qué mide?** El cambio porcentual en ventas comparando los últimos 30 días contra los 30 días anteriores (periodo 30–60 días atrás).

**Fórmula:**
```
tendencia_pct = ((ventas_0-30 - ventas_30-60) / ventas_30-60) × 100
```

**Casos especiales:**
- Si no hubo ventas en el período anterior pero sí en el actual → tendencia = +100%
- Si no hubo ventas en ningún período → tendencia = 0%

**Interpretación:**
- +20% o más: el producto está creciendo activamente
- Entre −20% y +20%: comportamiento estable
- −35% o menos: caída preocupante que activa una alerta

---

#### KPI 7 — Predicción de Agotamiento (días)
**¿Qué mide?** Los días estimados hasta que el stock llegue a cero, manteniendo el ritmo de ventas actual.

**Fórmula:**
```
ritmo_diario = ventas_30_dias / 30
dias_agotamiento = stock_actual / ritmo_diario
```

**¿Por qué importa?** Permite anticipar cuándo debe hacerse la siguiente compra. Si el tiempo de recuperación del proveedor es de 15 días y el producto se agota en 10 días, hay un déficit de 5 días que causará quiebre de stock.

**Nota:** Si el ritmo diario es 0 (sin ventas recientes), este indicador no aplica.

---

#### KPI 8 — Valor en Inventario (L)
**¿Qué mide?** El valor monetario total del stock existente, calculado al costo promedio del producto.

**Fórmula:**
```
valor_inventario = stock_actual × costo_promedio
```
(Si no hay costo promedio disponible, se usa el precio base como aproximación)

**¿Por qué importa?** Traduce el problema de inventario a términos financieros. Un producto con 5,000 unidades estancadas y costo de L 50 tiene L 250,000 de capital inmovilizado.

---

### 3.4 Sección de Tendencia Histórica (3 Gráficas)

#### Gráfica 1 — Ventas Mensuales (Últimos 12 Meses)
**Tipo:** Área suavizada  
**¿Qué muestra?** Las unidades vendidas mes a mes durante los últimos 12 meses calendario.

**Fuente:** `venta_has_producto` + `factura`, agrupado por `DATE_FORMAT(created_at, '%Y-%m')`.

**¿Para qué sirve?** Ver de un vistazo si el producto tiene estacionalidad, si está creciendo o decreciendo, y comparar meses específicos. Es el gráfico más importante para entender el patrón histórico de demanda.

---

#### Gráfica 2 — Compras vs. Ventas (Últimos 12 Meses)
**Tipo:** Barras agrupadas  
**¿Qué muestra?** Lado a lado, por cada mes, las unidades compradas al proveedor vs. las unidades vendidas a clientes.

**Fuente ventas:** `venta_has_producto` + `factura`  
**Fuente compras:** `compra_has_producto` + `compra`

**¿Para qué sirve?**
- Si las barras de compra son siempre mayores a las de venta → se está comprando más de lo que se vende → posible sobreinventario.
- Si las barras de venta superan a las de compra en varios meses seguidos → el producto está dependiendo del stock acumulado → riesgo de quiebre.
- Si hay meses con compra pero sin venta → se hicieron compras en meses de baja demanda → posible error de planificación.

---

#### Gráfica 3 — Entradas y Salidas de Bodega (Kardex, 12 Meses)
**Tipo:** Barras apiladas  
**¿Qué muestra?** Por cada mes, la cantidad total de unidades que entraron a bodega (signo `(+)` en descripción del cardex) y las que salieron (signo `(-)`).

**Fuente:** Tabla `cardex`, campo `descripcion` para detectar signo, campo `cantidad` para el volumen.

**¿Para qué sirve?** Esta gráfica incluye **todos los movimientos**: ventas, compras, ajustes, traslados, devoluciones y notas de crédito. Permite detectar si hay movimientos inusuales (por ejemplo, muchos ajustes negativos que sugieren merma o robo sistemático).

---

### 3.5 Análisis de Rotación

**¿Qué es?** Una sección dedicada a evaluar la velocidad a la que se mueve el producto en términos anuales, con una clasificación cualitativa.

#### Indicadores calculados:

| Indicador | Fórmula | Significado |
|---|---|---|
| **Índice de rotación** | `ventas_12m / stock_actual` | Cuántas veces "se renovó" el inventario en el año. Índice ≥ 12 es excelente (rota cada mes o más frecuente). |
| **Promedio mensual (12m)** | `ventas_12m / 12` | Media de ventas por mes sobre el año completo. Más estable que el de 90 días. |
| **Meses con ventas** | Conteo de meses del año con qty > 0 | Indica si el producto vende todo el año o solo en temporadas. |
| **Días entre ventas** | Promedio de días entre transacciones en los últimos 90 días | Un producto que se vende cada 1.5 días es muy frecuente; uno que se vende cada 30 días es esporádico. |
| **Mes con mayor movimiento** | Mes del año con más unidades vendidas | Identifica el pico de temporada. |
| **Mes con menor movimiento** | Mes del año con menos unidades vendidas | Identifica la temporada baja. |

#### Clasificación de Rotación:

| Promedio Mensual | Clasificación | Color |
|---|---|---|
| ≥ 100 unidades/mes | Alta rotación 🔥 | Verde |
| 30–99 unidades/mes | Rotación media 📦 | Azul |
| 5–29 unidades/mes | Baja rotación ⚠️ | Amarillo |
| < 5 unidades/mes | Producto muerto 🛑 | Gris |

El "gauge" visual (medidor circular) refleja el score de clasificación: 90 puntos para alta rotación, 65 para media, 35 para baja, 10 para muerto.

#### Distribución de Stock por Bodega
Barras horizontales proporcionales que muestran cuántas unidades hay en cada bodega y sección, facilitando decisiones de transferencia si un almacén tiene exceso y otro déficit.

---

### 3.6 Movimientos Recientes (Kardex Filtrado)

**¿Qué es?** Una tabla con los últimos 50 movimientos del producto en el kardex, con filtros interactivos por tipo y rango de fechas.

**Fuente:** Tabla `cardex` filtrada por `id_producto`, ordenada de más reciente a más antiguo.

#### Tipos de movimiento identificados automáticamente:

| Tipo | Criterio de detección | Color |
|---|---|---|
| **Venta** | Campo `id_factura` presente, sin "Anulada" en descripción | Rojo |
| **Compra** | Campo `detalleCompra` presente | Verde |
| **Devolución** | Campo `id_factura` presente + "Anulada" en descripción, o `nota_credito` presente | Púrpura |
| **Nota de Crédito** | Campo `nota_credito` presente | Púrpura |
| **Ajuste (+/-)** | Campo `ajuste` presente | Naranja |
| **Traslado** | Campo `comprobante` presente | Azul |

**Reconstrucción del stock:** La tabla muestra el stock al momento de cada movimiento, calculado hacia atrás desde el stock actual:
- Para entradas pasadas: se resta la cantidad al stock actual acumulado
- Para salidas pasadas: se suma la cantidad al stock actual acumulado

Esto permite ver el historial de nivel de inventario transacción por transacción.

#### Número de documento generado:
- Ventas → `FAC-{numero_factura}`
- Notas de crédito → `NC-{numero_nota}`
- Traslados → `COMP-{numero_comprobante}`
- Ajustes → `AJ-{ajuste_cod}`
- Otros → `#{id_cardex}`

---

### 3.7 Análisis Predictivo

**¿Qué es?** Cuatro indicadores de proyección calculados con base en el comportamiento reciente del producto, para apoyar decisiones de compra.

#### Predicción 1 — Días Hasta Agotamiento
**Fórmula:**
```
ritmo_diario = ventas_30_dias / 30
dias_agotamiento = stock_actual / ritmo_diario
```

**Niveles de riesgo:**
- ≤ 7 días → Crítico (compra urgente)
- 8–15 días → Alto (programar compra pronto)
- 16–30 días → Medio (stock suficiente por menos de un mes)
- Stock > 6 meses de demanda → Sobreinventario
- Cualquier otro → Normal

---

#### Predicción 2 — Cantidad Recomendada a Comprar
**¿Qué calcula?** Las unidades adicionales que se deberían comprar para cubrir la demanda del período de recuperación más un mes de colchón.

**Fórmula:**
```
stock_recomendado = promedio_mensual × (tiempo_recuperacion_meses + 1)
cantidad_a_comprar = max(0, stock_recomendado − stock_actual)
```

El `tiempo_recuperacion_meses` es un campo configurable en el catálogo del producto. Representa cuántos meses tarda el proveedor en entregar desde que se realiza la orden de compra.

**Ejemplo práctico:** Si el producto se vende en promedio 200 unidades/mes, el tiempo de recuperación es 2 meses, y hay 150 unidades en stock:
```
stock_recomendado = 200 × (2 + 1) = 600 unidades
cantidad_a_comprar = 600 − 150 = 450 unidades
```

---

#### Predicción 3 — Riesgo de Sobreinventario
**Criterio:** Se activa cuando los meses de cobertura superan 6 meses.
```
meses_cubiertos = stock_actual / promedio_mensual
sobreinventario = true  si  meses_cubiertos > 6
```

**¿Por qué 6 meses?** Seis meses de inventario acumulado generalmente implica que se compró en exceso, que la demanda cayó, o que el producto está en declive. En ese escenario, el capital inmovilizado ya es significativo y conviene evaluar promociones o transferencias.

---

#### Predicción 4 — Proyección del Próximo Mes
**¿Qué calcula?** Las unidades que probablemente se venderán el próximo mes, ajustadas por la tendencia actual.

**Fórmula:**
```
proyeccion = promedio_mensual × (1 + tendencia_pct / 100)
```

**Ejemplo:** Promedio 300 unidades/mes, tendencia actual +20% → proyección = 360 unidades.

---

### 3.8 Alertas Inteligentes del Producto

**¿Qué es?** Un sistema de hasta 6 alertas automáticas específicas para el producto, ordenadas por prioridad de atención. Cada alerta incluye descripción del problema y acción sugerida.

#### Alertas posibles (en orden de prioridad):

| Alerta | Criterio | Acción Sugerida |
|---|---|---|
| **Stock crítico** | `dias_agotamiento ≤ 7` | Realizar compra de emergencia |
| **Sin stock** | `stock_actual ≤ 0` | Realizar compra o transferencia inmediata |
| **Sin movimiento** | `dias_sin_mov > 30` con stock disponible | Revisar precio y disponibilidad en tienda |
| **Caída brusca de ventas** | `tendencia_pct ≤ −35%` | Analizar causa: precio, competencia o disponibilidad |
| **Sobreinventario** | `meses_cubiertos > 6` | Evaluar promoción o transferencia a otra sucursal |
| **Crecimiento acelerado** | `tendencia_pct ≥ +50%` | Programar compra preventiva |
| **Ajustes frecuentes** | ≥ 3 ajustes en últimos 30 días | Auditar inventario físico y verificar conteos |

El límite de 6 alertas evita ruido visual; se muestran las más urgentes primero.

---

### 3.9 Comparativos Históricos

**¿Qué es?** Una sección que compara el desempeño del producto en diferentes horizontes de tiempo para detectar tendencias de largo plazo.

#### Comparativo Anual
**¿Qué muestra?** Total de unidades vendidas en el año actual vs. el año anterior, con el porcentaje de variación.

```
pct_anual = ((ventas_año_actual - ventas_año_anterior) / ventas_año_anterior) × 100
```

Un resultado positivo indica que el producto está creciendo año a año; uno negativo indica declive.

---

#### Comparativo Mensual
**¿Qué muestra?** Unidades vendidas en el mes actual vs. el mismo mes del período anterior (mes anterior calendario), con porcentaje de variación.

```
pct_mensual = ((ventas_mes_actual - ventas_mes_anterior) / ventas_mes_anterior) × 100
```

---

#### Estacionalidad (Barras por Mes)
**¿Qué muestra?** El promedio histórico de ventas para cada mes del año, calculado sobre los últimos 24 meses disponibles.

**Fuente:** Consulta SQL que agrupa por `MONTH(created_at)` y `YEAR(created_at)`, promedia las cantidades por mes del año.

**¿Para qué sirve?** Identificar los meses de temporada alta y baja del producto. Si en octubre y noviembre el promedio es el doble que en otros meses, significa que se debe pre-comprar en septiembre.

---

#### Gráfica Comparativa Anual (Líneas)
**¿Qué muestra?** Dos líneas — año actual y año anterior — mostrando las ventas mes a mes para todos los 12 meses del año.

**¿Para qué sirve?** Ver si el año actual está por encima o por debajo del anterior en cada mes. Permite detectar si el rezago es estacional o si hay un problema estructural de demanda.

---

### 3.10 Badge de Estado del Producto

**¿Qué es?** Un indicador visual en la cabecera que clasifica el estado general del producto en una de 7 categorías, calculado automáticamente.

**Lógica de asignación (en orden de prioridad):**

| Condición | Badge | Color | Emoji |
|---|---|---|---|
| `stock_actual ≤ 0` | Sin stock | Rojo | 🛑 |
| `dias_sin_mov > 60` | Estancado | Gris | 🛑 |
| `tendencia_pct ≤ −35%` | En caída | Rojo | 📉 |
| `tendencia_pct ≥ +30%` | Crecimiento | Verde | 📈 |
| `rotacion_mensual ≥ 50 uds/mes` | Alta rotación | Naranja | 🔥 |
| `dias_agotamiento ≤ 15 días` | Riesgo | Amarillo | ⚠️ |
| Ninguna de las anteriores | Normal | Azul | 📦 |

---

## 4. ORIGEN DE LOS DATOS — TABLAS DE LA BASE DE DATOS

| Tabla | Uso en el módulo |
|---|---|
| `producto` | Catálogo base: nombre, precio, costo, tiempo recuperación |
| `sub_categoria` | Subcategoría del producto |
| `categoria_producto` | Categoría del producto |
| `marca` | Marca del producto |
| `estado_producto` | Estado activo/inactivo del producto |
| `img_producto` | URL de la imagen del producto |
| `recibido_bodega` | Stock disponible por bodega/sección |
| `seccion` | Sección dentro de la bodega |
| `segmento` | Segmento dentro de la sección |
| `bodega` | Nombre de la bodega |
| `factura` | Facturas de venta: fecha, total |
| `venta_has_producto` | Detalle de venta: producto, cantidad, monto por factura |
| `compra` | Órdenes de compra al proveedor |
| `compra_has_producto` | Detalle de compra: producto, cantidad, precio por orden |
| `proveedores` | Datos de proveedores |
| `cardex` | Registro de todos los movimientos de inventario |
| `ajuste` | Ajustes manuales de inventario (solo para conteo en alertas) |

---

## 5. FÓRMULAS Y CÁLCULOS DETALLADOS

### Rotación Mensual del Producto
```
rotacion_mensual = SUM(venta_has_producto.cantidad)
                   WHERE factura.created_at BETWEEN hace_30_dias AND hoy
                   AND venta_has_producto.producto_id = {id}
```

### Tendencia Porcentual
```
V1 = ventas de los últimos 30 días
V2 = ventas de los días 30 a 60 atrás

tendencia = ((V1 - V2) / V2) × 100  [si V2 > 0]
tendencia = +100  [si V2 = 0 y V1 > 0]
tendencia = 0     [si ambos = 0]
```

### Índice de Rotación Anual
```
indice = ventas_12_meses / stock_actual_en_bodega
```
Valores > 12 indican que el inventario rota más de una vez al mes en promedio.

### Días Hasta Agotamiento
```
ritmo_diario = ventas_ultimos_30 / 30
dias = stock_actual / ritmo_diario
```

### Cantidad Recomendada a Comprar
```
stock_objetivo = promedio_mensual_90d × (tiempo_recuperacion_meses + 1)
a_comprar = max(0, stock_objetivo - stock_actual)
```

### Salud General del Inventario (Sección 1)
```
salud = (productos_con_al_menos_una_venta_en_periodo / total_activos) × 100
```

### Proyección Próximo Mes
```
proyeccion = promedio_mensual_90d × (1 + tendencia_pct / 100)
```

---

## 6. INTERPRETACIÓN DE INDICADORES

### ¿Cuándo un producto necesita atención urgente?
Se deben considerar acciones inmediatas cuando se cumple al menos una de estas condiciones:
1. `dias_agotamiento ≤ 7` (stock para menos de una semana)
2. `stock_actual ≤ 0` (sin stock)
3. `tendencia_pct ≤ −50%` (caída muy brusca de ventas)

### ¿Cuándo un producto tiene sobreinventario?
Cuando `meses_cubiertos > 6`. En ese caso, el capital invertido en ese producto no está generando retorno y podría usarse en productos de mayor demanda.

### ¿Cuándo un producto está "muerto"?
Cuando `promedio_mensual < 5` unidades/mes durante los últimos 12 meses. Esto indica que la demanda es prácticamente inexistente y el producto debería ser evaluado para descatalogación, liquidación o transferencia.

### ¿Cómo leer el índice de rotación?
- **Índice = 1:** El inventario se renovó 1 vez en el año (rota cada 12 meses)
- **Índice = 6:** Se renovó 6 veces (rota aproximadamente cada 2 meses)
- **Índice = 12:** Se renovó 12 veces (rota una vez al mes — muy buena rotación)
- **Índice = 24+:** Rota más de 2 veces al mes — producto de altísima demanda, control de stock estricto

---

## 7. PROPÓSITO ESTRATÉGICO DEL MÓDULO

### Problema que resuelve
Sin este módulo, detectar que un producto específico se va a agotar en 8 días requeriría que alguien:
1. Abriera el listado de todos los productos.
2. Buscara el producto manualmente.
3. Revisara el kardex para ver cuánto queda.
4. Calculara en papel cuándo se agotaría.
5. Comparara con el historial de compras anteriores.
6. Decidiera cuánto pedir.

Con este módulo, toda esa información está consolidada en una sola pantalla, calculada automáticamente, con alertas que señalan los productos más urgentes primero.

### Casos de uso principales

| Rol | Uso del módulo |
|---|---|
| **Jefe de Compras** | Revisa la Vista General cada mañana. Entra a los productos en riesgo de agotamiento y usa la "cantidad recomendada a comprar" para generar órdenes de compra. |
| **Gerente Comercial** | Revisa el índice de salud general y la tendencia de ventas para entender el ritmo del negocio. Detecta categorías con caída para tomar decisiones de campaña. |
| **Encargado de Bodega** | Consulta el análisis individual de productos para verificar stock por ubicación y detectar productos con ajustes frecuentes que podrían tener problemas de conteo. |
| **Analista de Inventario** | Usa los comparativos históricos y la estacionalidad para planificar compras de temporada con anticipación. |

### Valor diferencial
A diferencia de un reporte estático, este módulo es **reactivo y contextual**: cambia en tiempo real al aplicar filtros, muestra alertas solo cuando las condiciones las justifican, y conecta la información macro (estado del inventario) con el micro (comportamiento de un producto individual), creando un flujo de análisis coherente de lo general a lo específico.

---

*Documento generado en base al análisis del código fuente de los componentes:*  
- `App\Http\Livewire\Reportes\AnaliticaDeProductos`  
- `App\Http\Livewire\Reportes\AnalisisProductoIndividual`  

*Base de datos: `profac_app` (MySQL via Laragon)*  
*Framework: Laravel 8/9 + Livewire + ApexCharts*
