# ANALITICA DE PRODUCTOS - GUIA EXPLICATIVA PARA EXPOSICION

Fecha: 2026-05-30  
Modulo principal: /reportes/analitica_de_productos

---

## 1. Objetivo del modulo

El modulo muestra, en una sola pantalla, el estado comercial del inventario para apoyar decisiones de compra, liquidacion y reabastecimiento.

Responde cuatro preguntas de negocio:
1. Cuanto del catalogo realmente se mueve (vende) en el periodo analizado.
2. Que productos estan detenidos y representan capital inmovilizado.
3. Que categorias y productos tienen mayor rotacion o mayor crecimiento.
4. Donde existen alertas tempranas (caidas de venta o riesgo operativo).

---

## 2. Ubicacion tecnica y arquitectura

Implementacion principal:
- Clase Livewire: app/Http/Livewire/Reportes/AnaliticaDeProductos.php
- Vista Blade: resources/views/livewire/reportes/analiticadeproductos.blade.php
- Ruta principal: /reportes/analitica_de_productos
- Ruta complementaria (drill-down por producto): /reportes/analitica_de_productos/{productoId}

Patron de trabajo:
1. El componente carga filtros y catalogos en mount().
2. Calcula metricas agregadas con calcularMetricas().
3. Genera alertas con generarAlertas().
4. Llena tabla dinamica con cargarTabla().
5. La vista renderiza KPIs, alertas, graficas y tabla por pestanas.

---

## 3. Parametros utilizados

### 3.1 Parametros de entrada (filtros del usuario)

- filtroCategoria: ID de categoria_producto. Vacio = todas.
- filtroMarca: ID de marca. Vacio = todas.
- filtroFechaInicio: fecha inicial en formato Y-m-d.
- filtroFechaFin: fecha final en formato Y-m-d.
- tablaTab: pestana activa de tabla. Valores:
  - criticos
  - top_rotacion
  - sin_movimiento
  - mayor_crecimiento

Valores por defecto:
- Fecha inicio: hoy - 90 dias.
- Fecha fin: hoy.
- Pestana inicial: criticos.

### 3.2 Parametros/logicas internas del motor analitico

- Catalogo analizado: solo productos activos (estado_producto_id = 1).
- Ventana de tendencia mensual global: ultimos 6 meses.
- Top categorias en grafica: maximo 8.
- Alertas maximas mostradas: 7.
- Alertas de alta prioridad: top 4 estancados por mayor precio_base.
- Alertas de media prioridad: top 3 caidas con umbral >= 35%.
- Alertas informativas: top 2 crecimientos con umbral >= 50%.
- Tablas dinamicas: maximo 20 registros por pestana.
- Distribucion "Riesgo" en donut: min(15, pctMovimiento * 0.15).

---

## 4. Flujo de funcionamiento (paso a paso)

1. Se determina el periodo de trabajo con filtroFechaInicio y filtroFechaFin.
2. Se calcula duracion del periodo (dias) para normalizar rotacion mensual.
3. Se obtienen IDs de productos con venta en el periodo desde venta_has_producto + factura.
4. Sobre el universo de productos activos filtrados (categoria/marca) se calcula:
   - total_activos
   - con_movimiento
   - estancados
5. Se calculan indicadores de negocio:
   - valor_ventas (sumatoria de factura.total)
   - total_unidades (sumatoria de vhp.cantidad)
   - total_facturas
   - rotacion_promedio
6. Se calcula variacion porcentual contra periodo anterior de igual duracion.
7. Se calcula salud_general en porcentaje y su mensaje cualitativo.
8. Se arman tres bloques visuales:
   - tendenciaVentas (linea)
   - rotacionCategorias (barra horizontal)
   - distribucionEstado (donut)
9. Se generan alertas de accion (alta, media, info).
10. Se carga tabla detalle segun pestana activa.

---

## 5. Logicas de calculo principales

## 5.1 KPIs

- total_activos = conteo de productos activos filtrados.
- con_movimiento = activos que aparecen en ventas del periodo.
- estancados = total_activos - con_movimiento.
- valor_ventas = suma de factura.total en periodo.
- pct_cambio = ((valor_ventas - valor_anterior) / valor_anterior) * 100.
- total_unidades = suma de venta_has_producto.cantidad en periodo.
- rotacion_promedio = total_unidades / total_activos / (dias/30).
- total_facturas = conteo de facturas del periodo.
- riesgo_agotamiento = productos con venta y tiempo_recuperacion_meses nulo.

## 5.2 Salud general

- saludGeneral = (con_movimiento / total_activos) * 100, truncado al rango 0..100.
- Texto de estado:
  - >= 80: Inventario saludable y en movimiento.
  - >= 60: Estable con areas de atencion.
  - >= 40: Requiere atencion en multiples categorias.
  - < 40: Estado critico.

## 5.3 Alertas inteligentes

A. Alta prioridad (Sin movimiento):
- Producto activo, sin venta en el periodo.
- Prioriza los de mayor precio_base por impacto economico.
- Accion sugerida: Liquidar.

B. Media prioridad (Caida de ventas):
- Divide el periodo en primera mitad y segunda mitad.
- Si caida >= 35%, genera alerta.
- Muestra los 3 casos mas severos.

C. Informativa (Tendencia positiva):
- Si crecimiento >= 50% entre mitades, marca oportunidad.
- Muestra 2 productos con mayor crecimiento.

## 5.4 Tabla dinamica por pestana

- criticos: mas vendidos del periodo (prioridad de abastecimiento).
- top_rotacion: mejor rotacion mensual normalizada.
- sin_movimiento: activos sin ventas (capital inmovilizado).
- mayor_crecimiento: productos con aceleracion de demanda.

---

## 6. Tablas de base de datos involucradas y su funcion

- producto:
  - Maestro del articulo (nombre, marca, subcategoria, precio_base, estado, tiempo_recuperacion).
  - Es la entidad central del modulo.

- sub_categoria:
  - Nivel intermedio de clasificacion.
  - Permite mapear cada producto a una categoria superior.

- categoria_producto:
  - Agrupa productos para analisis gerencial por linea de negocio.

- marca:
  - Permite comparar desempeno por fabricante/proveedor de marca.

- venta_has_producto:
  - Detalle de unidades vendidas por producto en cada factura.
  - Es la principal fuente de movimiento (volumen).

- factura:
  - Cabecera de la venta (fecha y total monetario).
  - Aporta tiempo de transaccion y valor economico.

---

## 7. Objetivo de cada tabla o dato mostrado (para exposicion)

KPIs superiores:
- Ventas en el periodo: medir resultado economico global.
- Productos activos: dimension real del catalogo evaluado.
- Sin movimiento: detectar inventario no rentable.
- Rotacion mensual promedio: medir velocidad comercial del portafolio.
- Facturas emitidas: medir actividad transaccional.
- Unidades vendidas: medir demanda en volumen.

Gauge de salud:
- Convertir multiples metricas en un unico indicador ejecutivo rapido.

Centro de alertas:
- Traducir analitica en accion operativa concreta (liquidar, revisar, reabastecer).

Grafica de tendencia de ventas:
- Mostrar direccion temporal del negocio (crece, cae o se estabiliza).

Grafica de rotacion por categoria:
- Priorizar categorias que mueven mas unidades.

Donut de estado de inventario:
- Visualizar mezcla del portafolio entre saludable, riesgo, estancado y sobreinventario.

Tabla detallada por pestanas:
- Bajar del nivel ejecutivo al nivel tactico por producto.
- Permitir pasar de la observacion al plan de accion.

---

## 8. Como se llego a este producto y por que

Este producto analitico nace de una necesidad operativa recurrente: el inventario se evaluaba con reportes aislados (ventas por un lado, stock por otro, compras aparte), lo que retrasaba decisiones y aumentaba quiebres o sobreinventario.

Se construyo una vista unificada con tres principios:
1. Lectura ejecutiva inmediata (KPIs + salud + alertas).
2. Evidencia visual para exposicion (3 graficas complementarias).
3. Capacidad de accion (tabla filtrable y enlace al analisis individual por producto).

Por que esta estructura:
- KPIs: resumen financiero y comercial en segundos.
- Alertas: priorizacion automatica para actuar primero en lo mas costoso.
- Graficas: contexto temporal y por categoria para explicar causas.
- Tabla por pestanas: conversion de la estrategia en tareas concretas por producto.

Resultado esperado del modulo:
- Menos capital inmovilizado.
- Mejor disponibilidad de productos de alta demanda.
- Menor reaccion tardia ante caidas de venta.
- Decisiones de compra con base en datos y no solo percepcion.
