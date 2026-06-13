# Analisis Completo: Modulo Comisiones Conciliacion

## 1) Alcance del modulo

URL principal:
- /comisiones/conciliacion

Clase principal:
- App\Http\Livewire\Comisiones\Escalado\Conciliacion

Vista:
- resources/views/livewire/comisiones/escalado/conciliacion.blade.php

Este modulo administra el cierre contable de comisiones por periodo mensual. Su objetivo es:
- bloquear acreditacion de nuevas comisiones en meses cerrados,
- mantener snapshot historico auditable,
- permitir reapertura controlada con motivo,
- proveer tablero operativo de salud de reglas y deuda de conciliacion.

## 2) Rutas funcionales

- GET /comisiones/conciliacion
- GET /comisiones/conciliacion/validar-reglas
- GET /comisiones/conciliacion/periodos
- POST /comisiones/conciliacion/conciliar
- POST /comisiones/conciliacion/reabrir
- GET /comisiones/conciliacion/detalle
- GET /comisiones/conciliacion/auditoria-logs
- GET /comisiones/conciliacion/verificar-periodo

Dias de gracia:
- GET /comisiones/dias-gracia
- POST /comisiones/dias-gracia/guardar

## 3) Modelo de datos involucrado

Tablas principales:
- comision_periodo
- comision_periodo_log
- comision_empleado
- facturas_comision
- comision_escala
- comision_rol_config
- dias_gracia_comision
- comision_reversiones

La logica de conciliacion toma datos vivos de comision_empleado/facturas_comision y genera snapshots persistentes en comision_periodo y comision_periodo_log.

## 4) Health-check de reglas (validarReglas)

El endpoint /validar-reglas ejecuta chequeos de consistencia:

1. Existencia de escalas en comision_escala.
2. Existencia de configuracion por rol en comision_rol_config.
3. Existencia de dias_gracia_comision.
4. Comisiones en periodo actual.
5. Periodos historicos con comisiones sin conciliar.
6. Integridad facturas_comision vs factura (huerfanas).

Salida:
- checks[] con estado ok/warning/error,
- total_errores,
- total_warnings,
- estado_global.

Esto permite monitoreo preventivo antes de cerrar periodos.

## 5) Construccion de periodos (listarPeriodos)

## 5.1 Como se arma el universo de meses

- Incluye enero..diciembre del anio actual.
- Agrega meses historicos de anios anteriores que tengan comision_empleado activa.
- Ordena descendentemente.

## 5.2 Estado de cada periodo

Reglas:
- sin_abrir: mes futuro.
- conciliado: existe comision_periodo con estado conciliado.
- abierto: caso restante.

## 5.3 Fuente de totales

- Si periodo conciliado: usa snapshot de comision_periodo.
- Si periodo abierto/sin registro: usa totales live desde comision_empleado + facturas_comision.

## 5.4 KPIs del tablero

- total_abiertos
- total_conciliados
- total_sin_abrir
- monto_abierto
- monto_conciliado

## 6) Conciliar periodo (conciliarPeriodo)

Entrada:
- periodo
- observacion (opcional)

Validaciones:
- periodo requerido.
- no permite meses futuros.
- no permite conciliar dos veces un periodo ya conciliado.

Proceso transaccional:
1. Calcula snapshot live (_calcularSnapshot).
2. Upsert en comision_periodo con estado conciliado.
3. Guarda log de auditoria en comision_periodo_log con snapshot completo.
4. Commit.

Mensaje funcional:
- luego de conciliar, no se acreditaran nuevas comisiones en ese mes.

## 7) Reabrir periodo (reabrirPeriodo)

Entrada:
- periodo
- observacion obligatoria

Validaciones:
- periodo requerido.
- observacion requerida.
- solo reabre si estaba conciliado.

Proceso:
1. Calcula snapshot previo para auditoria.
2. Cambia estado a abierto.
3. Limpia usuario/fecha de conciliacion en comision_periodo.
4. Registra log de reapertura en comision_periodo_log.

Resultado:
- el mes vuelve a aceptar acreditaciones.

## 8) Snapshot de detalle (_calcularSnapshot)

Genera dos bloques:
- detalle_empleados
- detalle_facturas

Y totales:

$$
total\_comision = \sum comision\_acumulada\_{empleados\_mes}
$$

$$
cantidad\_empleados = \# usuarios\_distintos\_con\_comision\_positiva
$$

$$
cantidad\_facturas = \# facturas\_distintas\_comisionadas\_del\_mes
$$

Este snapshot se guarda en log para trazabilidad historica completa.

## 9) Verificacion de periodo de pago (verificarPeriodoPago)

Endpoint:
- GET /comisiones/conciliacion/verificar-periodo?fecha=YYYY-MM-DD

Funcion:
- Determina si el mes de esa fecha esta conciliado.
- Si esta conciliado, busca proximo mes abierto.

Uso real:
- Lo consume pagos.js antes de registrar abono.
- Si mes esta cerrado, informa desvio de acreditacion al proximo abierto.

Esto conecta conciliacion con CxC Pagos en tiempo real.

## 10) Dias de gracia y retencion

Endpoints:
- listarDiasGracia
- guardarDiasGracia

Configuracion por:
- rol
- tipo_factura (contado/credito)
- dias_gracia
- porcentaje_retencion
- descripcion

Esta configuracion es usada por el servicio AplicadorRetencionesMora en el flujo de pagos.

## 11) Auditoria de eventos (listarAuditoriaLogs)

Devuelve historial de:
- accion (conciliacion/reapertura)
- estado_anterior/nuevo
- snapshot totals
- snapshot detalle empleados/facturas (JSON)
- observacion
- usuario
- fecha

Permite filtro por anio.

Proposito:
- auditoria forense de cierres y reaperturas.

## 12) Relacion directa con acreditacion de comisiones

El bloqueo real ocurre en el servicio ProcesadorComisiones:
- antes de acreditar en comision_empleado, valida comision_periodo estado conciliado.
- si cerrado, no acredita.

Por eso conciliacion no solo reporta: gobierna la acreditacion efectiva.

## 13) UI y experiencia operativa

La vista conciliacion incluye:
- topbar con KPIs,
- tabla de periodos por anio/mes,
- acciones conciliar/reabrir/ver detalle,
- modal de conciliacion con resumen previo,
- modal de reapertura con motivo,
- modal de detalle con tabs empleados/facturas/logs,
- panel de dias de gracia por rol.

Adicional:
- exportaciones a Excel desde detalle (empleados/facturas/log).

## 14) Formulas del modulo

## 14.1 Identificacion de estado por periodo

$$
estado =
\begin{cases}
sin\_abrir & \text{si } periodo > mes\_actual \\
conciliado & \text{si existe snapshot conciliado} \\
abierto & \text{caso contrario}
\end{cases}
$$

## 14.2 Suma abierta y conciliada para KPI

$$
monto\_abierto = \sum total\_comision\_{periodos\_abiertos}
$$

$$
monto\_conciliado = \sum total\_comision\_{periodos\_conciliados}
$$

## 15) Riesgos funcionales que el modulo mitiga

- Evita acreditacion retroactiva en periodos ya cerrados.
- Evita cierres sin evidencia (snapshot + log).
- Evita reaperturas silenciosas (motivo obligatorio).
- Detecta deuda de conciliacion en periodos pasados.
- Detecta integridad rota de facturas comisionadas.

## 16) Resumen ejecutivo

/comisiones/conciliacion es el modulo de control contable del ciclo de comisiones mensual. Define el estado de cada mes, conserva snapshots auditables y bloquea/permite acreditaciones de forma trazable. Su integracion con Pagos y ProcesadorComisiones garantiza que el cierre mensual no sea solo visual, sino efectivo a nivel de datos y negocio.
