# GUION DE PRESENTACION - ANALITICA DE PRODUCTOS

Formato sugerido: 12 diapositivas  
Duracion estimada: 15 a 20 minutos

---

## Diapositiva 1 - Portada

Titulo: Analitica de Productos  
Subtitulo: Inteligencia de Inventario para decisiones de compra y venta  
Contexto: Sistema PROFAC

Mensaje clave:
- Esta solucion convierte datos operativos en decisiones concretas sobre inventario.

Guion del expositor:
- "Hoy presento el modulo de Analitica de Productos, una herramienta que nos permite ver en tiempo real la salud del inventario, detectar riesgos y priorizar acciones con base en datos." 

---

## Diapositiva 2 - Problema de negocio

Titulo: Que problema resolvemos

Puntos:
- Inventario evaluado en reportes separados.
- Reaccion tardia ante productos estancados o con alta demanda.
- Dificultad para priorizar que comprar, que liquidar y cuando actuar.

Mensaje clave:
- Sin vista integrada, se incrementa el riesgo de quiebre de stock y capital inmovilizado.

Guion del expositor:
- "El principal dolor era la dispersion de informacion: ventas por un lado, stock por otro y compras aparte. Eso retrasaba decisiones y elevaba costos." 

---

## Diapositiva 3 - Objetivo del modulo

Titulo: Objetivo estrategico

Puntos:
- Medir movimiento real del catalogo.
- Detectar inventario sin rotacion.
- Identificar crecimiento o caida de demanda.
- Priorizar acciones operativas y comerciales.

Mensaje clave:
- Pasamos de una lectura descriptiva a una gestion predictiva y accionable.

Guion del expositor:
- "El modulo no solo muestra datos, sino que orienta la accion: donde intervenir primero y con que criterio." 

---

## Diapositiva 4 - Ubicacion y alcance funcional

Titulo: Donde opera en el sistema

Puntos:
- Ruta principal: /reportes/analitica_de_productos
- Drill-down por producto: /reportes/analitica_de_productos/{productoId}
- Tecnologia: Livewire + Blade + consultas SQL en tiempo real

Mensaje clave:
- Es una vista ejecutiva con capacidad de bajar al detalle por producto.

Guion del expositor:
- "Desde una sola pantalla vemos todo el inventario y, con un clic, pasamos al analisis individual para actuar por producto." 

---

## Diapositiva 5 - Parametros y filtros

Titulo: Parametros de analisis

Puntos:
- Filtro por categoria.
- Filtro por marca.
- Rango de fechas (default: ultimos 90 dias).
- Pestanas de tabla: criticos, top_rotacion, sin_movimiento, mayor_crecimiento.

Mensaje clave:
- Cada filtro recalcula KPIs, alertas, graficas y tablas automaticamente.

Guion del expositor:
- "El analisis es dinamico: al cambiar filtros, todo se actualiza y permite comparar segmentos puntuales del negocio." 

---

## Diapositiva 6 - KPIs clave del tablero

Titulo: Indicadores ejecutivos

Puntos:
- Ventas del periodo.
- Productos activos.
- Productos sin movimiento.
- Rotacion mensual promedio.
- Facturas emitidas.
- Unidades vendidas.

Mensaje clave:
- Los KPIs entregan lectura financiera y comercial en segundos.

Guion del expositor:
- "Estos indicadores resumen el rendimiento del inventario desde dos perspectivas: valor economico y velocidad de salida." 

---

## Diapositiva 7 - Salud del inventario y alertas

Titulo: Sistema de priorizacion automatica

Puntos:
- Salud general: porcentaje de productos con movimiento.
- Alertas alta prioridad: sin movimiento.
- Alertas media prioridad: caida >= 35%.
- Alertas informativas: crecimiento >= 50%.

Mensaje clave:
- El sistema transforma analitica en prioridades de accion.

Guion del expositor:
- "No solo vemos el problema, tambien se ordena por urgencia: que atender hoy, que monitorear y donde hay oportunidad." 

---

## Diapositiva 8 - Graficas para interpretacion

Titulo: Evidencia visual

Puntos:
- Tendencia de ventas 6 meses.
- Rotacion por categoria (Top 8).
- Donut de estado: saludable, riesgo, estancado, sobreinventario.

Mensaje clave:
- Las graficas explican comportamiento, no solo volumen.

Guion del expositor:
- "Estas visualizaciones facilitan la exposicion gerencial: permiten explicar rapidamente causas, tendencias y focos de decision." 

---

## Diapositiva 9 - Tabla de accion por pestanas

Titulo: Del resumen a la ejecucion

Puntos:
- Criticos: mas vendidos (prioridad de abastecimiento).
- Top rotacion: mayor velocidad comercial.
- Sin movimiento: inventario inmovilizado.
- Mayor crecimiento: demanda acelerada.

Mensaje clave:
- La tabla convierte hallazgos en listado operativo accionable.

Guion del expositor:
- "Aqui aterrizamos la estrategia: producto por producto, con criterio para comprar, liquidar o reforzar disponibilidad." 

---

## Diapositiva 10 - Datos y tablas de origen

Titulo: Base de datos utilizada

Puntos:
- producto: maestro del articulo.
- sub_categoria y categoria_producto: estructura comercial.
- marca: segmentacion por fabricante.
- venta_has_producto: detalle de unidades vendidas.
- factura: fecha y valor monetario de ventas.

Mensaje clave:
- Se utilizan fuentes transaccionales reales, sin consolidaciones manuales.

Guion del expositor:
- "El valor del modulo depende de la trazabilidad de datos: cada KPI proviene de tablas operativas que ya usa el negocio diariamente." 

---

## Diapositiva 11 - Valor generado y resultados esperados

Titulo: Impacto en negocio

Puntos:
- Reduccion de capital inmovilizado.
- Menor riesgo de quiebre de stock.
- Mejor timing de compra y reabastecimiento.
- Decisiones sustentadas en evidencia.

Mensaje clave:
- El modulo reduce perdida por inaccion y mejora disponibilidad comercial.

Guion del expositor:
- "Esperamos una operacion mas eficiente: menos inventario muerto y mayor continuidad en productos de alta demanda." 

---

## Diapositiva 12 - Cierre

Titulo: Conclusiones y siguiente paso

Puntos:
- Ya contamos con tablero ejecutivo + detalle por producto.
- Priorizacion automatica lista para operacion diaria.
- Proximo paso: seguimiento semanal de indicadores y acciones tomadas.

Mensaje clave:
- El exito del modulo no es ver datos, es ejecutar decisiones oportunas.

Guion del expositor:
- "La herramienta ya esta lista para gestion continua. El siguiente paso es institucionalizar su uso semanal para asegurar impacto sostenido." 

---

## Anexo - Frases cortas para preguntas del publico

- "La salud del inventario se calcula con base en movimiento real, no en percepcion."  
- "Las alertas no son estaticas; se recalculan con los filtros de negocio."  
- "El enfoque combina lectura ejecutiva y detalle operativo en el mismo flujo."  
- "El objetivo final es tomar mejores decisiones, mas rapido y con menor riesgo."  
