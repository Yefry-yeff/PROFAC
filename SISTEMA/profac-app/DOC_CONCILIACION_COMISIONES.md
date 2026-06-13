# MÓDULO: Comisiones / Conciliación
**URL:** `/comisiones/conciliacion`

---

## ¿Para qué sirve este módulo?

Es el **módulo de cierre contable mensual** del sistema de comisiones. Permite a administración:
- Bloquear un mes para que no se acrediten más comisiones en él.
- Guardar un snapshot inmutable (foto del estado real) del período antes de bloquearlo.
- Reabrir períodos conciliados cuando sea necesario, con motivo obligatorio y log de auditoría.
- Monitorear la salud operativa del sistema (health-check).
- Configurar los días de gracia y retención por mora para cada rol.
- Auditar todo el historial de conciliaciones y reaperturas.

**Efecto real:** cuando un período está marcado como **conciliado**, el servicio `ProcesadorComisiones` detecta esto y **no acredita nuevas comisiones** en `comision_empleado` para ese mes, aunque la factura cierre correctamente y genere `facturas_comision`.

---

## Archivos involucrados

| Capa | Archivo |
|------|---------|
| Backend (Livewire) | `app/Http/Livewire/Comisiones/Escalado/Conciliacion.php` |
| Vista | `resources/views/livewire/comisiones/escalado/conciliacion.blade.php` |
| Modelo Período | `app/Models/Comisiones/ModelComisionPeriodo.php` |
| Modelo Log | `app/Models/Comisiones/ModelComisionPeriodoLog.php` |
| Modelo Días Gracia | `app/Models/Comisiones/ModelDiasGraciaComision.php` |

---

## Tablas de base de datos

| Tabla | Propósito |
|-------|-----------|
| `comision_periodo` | Un registro por período mensual. Guarda estado y snapshot de totales al conciliar. |
| `comision_periodo_log` | Historial de acciones: cada conciliación y reapertura con snapshot completo en JSON. |
| `comision_empleado` | Fuente de datos en vivo. Contiene el acumulado mensual por usuario/rol. |
| `facturas_comision` | Detalle de facturas comisionadas. Se usa para construir el snapshot. |
| `dias_gracia_comision` | Configuración de días de gracia y % de retención por rol/tipo de factura. |
| `comision_escala` | Referenciada en el health-check para detectar si hay escalas configuradas. |
| `comision_rol_config` | Referenciada en el health-check para validar configuración de roles. |

---

## Todos los endpoints del módulo

| Método | URL | Función |
|--------|-----|---------|
| GET | `/comisiones/conciliacion` | Vista principal del módulo |
| GET | `/comisiones/conciliacion/validar-reglas` | Health-check: valida consistencia del sistema |
| GET | `/comisiones/conciliacion/periodos` | Lista todos los períodos con sus estados, totales y KPIs |
| POST | `/comisiones/conciliacion/conciliar` | Cierra un período: genera snapshot y lo bloquea |
| POST | `/comisiones/conciliacion/reabrir` | Reabre un período conciliado |
| GET | `/comisiones/conciliacion/detalle` | Detalle completo de un período: empleados, facturas, logs |
| GET | `/comisiones/conciliacion/auditoria-logs` | Historial de todas las conciliaciones y reaperturas |
| GET | `/comisiones/conciliacion/verificar-periodo` | Consulta si una fecha corresponde a un período conciliado |
| GET | `/comisiones/dias-gracia` | Lista todos los roles con su configuración de días de gracia |
| POST | `/comisiones/dias-gracia/guardar` | Guarda o actualiza días de gracia para un rol/tipo |

---

## Funcionalidades detalladas

### 1. Health-check del sistema — `validarReglas`

Antes de conciliar (o cuando el administrador lo solicita), el sistema ejecuta 6 verificaciones:

| # | Verificación | Estado posible |
|---|-------------|---------------|
| 1 | Existen escalas en `comision_escala` | OK si hay al menos 1; ERROR si ninguna |
| 2 | Existen registros en `comision_rol_config` | OK si hay; WARNING si ninguno |
| 3 | Existen `dias_gracia_comision` configurados | OK si hay; WARNING si ninguno (sin mora configurada) |
| 4 | Hay comisiones en el período actual | OK si hay; WARNING si el mes actual no tiene nada |
| 5 | Hay períodos históricos con comisiones sin conciliar | OK si todos conciliados; WARNING si hay deuda |
| 6 | Integridad de `facturas_comision` vs `factura` | OK si todas tienen padre; ERROR si hay huérfanas |

El resultado incluye `estado_global` = `ok`, `warning`, o `error`.

---

### 2. Listado de períodos — `listarPeriodos`

**Qué incluye:**
- Enero a diciembre del año actual.
- Todos los meses históricos de años anteriores que tengan comisiones en `comision_empleado`.
- Ordenados de más reciente a más antiguo.

**Estado de cada período:**

| Estado | Condición |
|--------|-----------|
| `sin_abrir` | El mes es futuro |
| `conciliado` | Existe registro en `comision_periodo` con estado conciliado |
| `abierto` | Cualquier otro caso (pasado sin conciliar o sin registro) |

**Fuente de totales:**
- Si el período está **conciliado**: usa el snapshot guardado en `comision_periodo` (dato inmutable).
- Si está **abierto**: calcula en vivo desde `comision_empleado` y `facturas_comision`.

**KPIs del tablero:**

| KPI | Cálculo |
|-----|---------|
| Períodos conciliados | COUNT de períodos con estado conciliado |
| Períodos abiertos | COUNT de períodos con estado abierto |
| Períodos sin abrir | COUNT de períodos futuros |
| Monto abierto | SUM de total_comision de períodos abiertos |
| Monto conciliado | SUM de total_comision de períodos conciliados |

La UI permite filtrar por año y por mes con botones de selección rápida.

---

### 3. Conciliar un período — `conciliarPeriodo`

**Validaciones previas:**
- El período es requerido.
- No se puede conciliar un mes futuro.
- No se puede conciliar un período ya conciliado.

**Proceso transaccional (en una sola transacción de BD):**
1. Calcula snapshot en tiempo real con `_calcularSnapshot()`.
2. Hace UPSERT en `comision_periodo`:
   - estado = conciliado
   - `total_comision`, `cantidad_empleados`, `cantidad_facturas`
   - `usuario_concilio`, `fecha_conciliacion`, `observacion_conciliacion`
3. Registra en `comision_periodo_log`:
   - acción = `conciliacion`
   - estado anterior = abierto, estado nuevo = conciliado
   - snapshot completo de empleados y facturas en JSON
   - observación, usuario, nombre del usuario, fecha

**Efecto inmediato:** a partir de ese momento, `ProcesadorComisiones` detecta el período conciliado y no acredita en `comision_empleado` para ese mes.

---

### 4. Snapshot de un período — `_calcularSnapshot`

El snapshot contiene:

| Campo | Contenido |
|-------|-----------|
| `total_comision` | Suma de `comision_acumulada` de todos los empleados en ese mes |
| `cantidad_empleados` | Número de usuarios distintos con comisión > 0 en el mes |
| `cantidad_facturas` | Número de facturas distintas comisionadas en el mes |
| `detalle_empleados` | JSON: `[{user_id, nombre, rol, comision_acumulada, cantidad_facturas}]` |
| `detalle_facturas` | JSON: `[{factura_id, monto_rol, rol_id, tipo_comision, fecha_cierre_factura}]` |

**Fórmulas del snapshot:**

$$
total\_comision = \sum comision\_acumulada_{(usuarios\ con\ comision > 0\ en\ el\ mes)}
$$

$$
cantidad\_empleados = COUNT(DISTINCT\ users\_comision)\ con\ comision > 0
$$

$$
cantidad\_facturas = COUNT(DISTINCT\ factura\_id)\ en\ facturas\_comision\ del\ mes
$$

---

### 5. Reabrir un período — `reabrirPeriodo`

**Validaciones:**
- El período es requerido.
- La **observación es obligatoria** (no se puede reabrir sin justificación).
- Solo funciona si el período estaba conciliado.

**Proceso:**
1. Calcula snapshot del estado actual (para dejarlo en el log como evidencia del estado antes de reabrir).
2. Cambia `comision_periodo.estado` a abierto.
3. Limpia `usuario_concilio`, `fecha_conciliacion`, `observacion_conciliacion`.
4. Registra en `comision_periodo_log`:
   - acción = `reapertura`
   - snapshot completo del momento de reapertura
   - observación, usuario, fecha

**Efecto:** el mes vuelve a aceptar acreditaciones desde `ProcesadorComisiones`.

---

### 6. Detalle de un período — `detallePeriodo`

Devuelve tres bloques:

**Empleados del período:**
```
usuario, rol, comision_acumulada, facturas, fecha_ult_modificacion
```

**Facturas del período:**
```
factura_id, correlativo, cliente, empleado, rol, tipo_comision, fecha_cierre, monto_rol
```
El mapeo de empleado usa el mismo CASE de `tipo_comision` del generador.

**Logs históricos del período:**
```
accion, estado_anterior, estado_nuevo, snapshot_total, empleados, facturas, observacion, usuario, fecha
```

La UI muestra esto en un modal con 3 pestañas y paginación interna. Incluye exportación a Excel de cada sección.

---

### 7. Auditoría completa — `listarAuditoriaLogs`

Lista **todos** los eventos de conciliación y reapertura del sistema, filtrable por año.

Por cada evento:
- Tipo de acción (conciliación / reapertura)
- Estados anterior y nuevo
- Snapshot: total comisión, empleados, facturas
- Snapshot detallado en JSON (empleados y facturas)
- Observación
- Usuario que ejecutó
- Fecha y hora exacta

---

### 8. Verificación de período desde Pagos — `verificarPeriodoPago`

**GET** `/comisiones/conciliacion/verificar-periodo?fecha=YYYY-MM-DD`

Este endpoint lo usa el módulo de Pagos (JS) antes de registrar un abono.

**Lógica:**
1. Extrae el mes/año de la fecha.
2. Busca en `comision_periodo` si ese período está conciliado.
3. Si está **abierto**: responde `conciliado: false` → flujo normal.
4. Si está **conciliado**: busca el próximo mes que no esté conciliado (máximo 24 meses hacia adelante).

**Respuesta si conciliado:**
```json
{
  "conciliado": true,
  "periodo": "2026-05-01",
  "periodo_label": "Mayo 2026",
  "proximo_abierto": "2026-06-01",
  "proximo_label": "Junio 2026"
}
```

El módulo de Pagos usa esta respuesta para:
- Advertir al usuario que el período está cerrado.
- Ofrecer continuar desviando la comisión al próximo mes abierto.
- Registrar los campos `periodo_comision_original` y `periodo_comision_asignado` en `abonos_creditos`.

---

### 9. Días de gracia y retención — `listarDiasGracia` / `guardarDiasGracia`

Muestra todos los roles del sistema con su configuración de días de gracia para contado y crédito.

**Por rol y tipo:**
- Días de gracia
- % de retención
- Nota interna
- Permite editar cada combinación con stepper numérico

**Regla de persistencia:** usa UPSERT por `(rol_id, tipo_factura)`. Si ya existe, actualiza; si no, crea.

---

## Reglas de negocio completas

| # | Regla |
|---|-------|
| R1 | No se puede conciliar un mes futuro. |
| R2 | No se puede conciliar dos veces el mismo período. |
| R3 | Para reabrir un período se requiere observación/motivo obligatorio. |
| R4 | Al conciliar, se guarda snapshot inmutable en `comision_periodo_log`. |
| R5 | Al reabrir, también se guarda snapshot del estado al momento de reabrir. |
| R6 | Un período conciliado bloquea `ProcesadorComisiones`: la comisión no se acredita en `comision_empleado`. |
| R7 | La `facturas_comision` sí se inserta incluso si el período está conciliado (queda como evidencia). |
| R8 | La verificación de período es consultada por Pagos en tiempo real antes de guardar cada abono. |
| R9 | Si el período del pago está conciliado, la comisión se desvía al próximo mes abierto. |
| R10 | El listado de períodos combina snapshots históricos (conciliados) con datos en vivo (abiertos). |
| R11 | Los días de gracia se aplican automáticamente en `AplicadorRetencionesMora` al cerrar factura. |
| R12 | El health-check detecta deuda de conciliación (períodos pasados con comisiones sin cerrar). |
| R13 | El health-check detecta integridad rota (facturas_comision sin factura padre). |

---

## Resumen de propósito

`/comisiones/conciliacion` es el módulo de **control temporal** del sistema de comisiones. Define cuándo un período deja de acumular comisiones, preserva evidencia histórica inalterable, permite correcciones controladas y protege la coherencia contable entre el módulo de pagos y los reportes de nómina.
