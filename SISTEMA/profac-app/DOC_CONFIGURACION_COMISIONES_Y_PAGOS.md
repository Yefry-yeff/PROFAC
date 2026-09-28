# MÓDULO: Comisiones / Configuración
**URL:** `/comisiones/configuracion`  
**Conexión directa con:** `/cuentas_por_cobrar/pagos`

---

## ¿Para qué sirve este módulo?

Este módulo es el **núcleo de gobierno** del sistema de comisiones. Define exactamente **cuánto** gana cada rol por vender un producto de determinada categoría de precio a un cliente de determinada categoría de escala. Sin configuración, el módulo de pagos no puede calcular nada.

No registra pagos. No acredita comisiones. Solo define reglas y controles que otros módulos ejecutan.

---

## Archivos involucrados

| Capa | Archivo |
|------|---------|
| Backend (Livewire) | `app/Http/Livewire/Comisiones/Escalado/Configuracion.php` |
| Vista | `resources/views/livewire/comisiones/escalado/configuracion.blade.php` |
| JS | `public/js/js_proyecto/comisiones/Escalado/gestionComision.js` |
| Export Excel | `app/Exports/Comisiones/PlantillaComisionMasivaExport.php` |
| Import Excel | `app/Imports/Comisiones/ComisionMasivaImport.php` |

---

## Tablas de base de datos que gobierna

### `comision_escala` — Tabla principal de reglas

Cada fila es una regla activa o inactiva. Su combinación única es:

```
rol_id + cliente_categoria_escala_id + categoria_precios_id
```

| Campo | Descripción |
|-------|-------------|
| `rol_id` | Rol que recibirá la comisión |
| `cliente_categoria_escala_id` | Categoría de cliente (mayorista, minorista, etc.) |
| `categoria_precios_id` | Lista de precios del producto vendido |
| `porcentaje_comision` | Porcentaje a aplicar sobre el precio de venta |
| `estado_id` | 1 = activo, 2 = inactivo |
| `users_registro` | Quién creó el registro |
| `usermodifico` | Quién editó el porcentaje |
| `fechaultimamodificacion` | Fecha del último cambio |

**Regla de negocio:** si existe una fila activa para la combinación `rol + catCliente + catPrecio`, ese rol comisiona los productos de esa categoría de precio cuando se vende a ese tipo de cliente.

---

### `comision_rol_config` — Interruptor de cálculo por rol

| Campo | Descripción |
|-------|-------------|
| `rol_id` | Rol del sistema |
| `calcular` | 1 = el rol participa en el cálculo al cerrar factura; 0 = excluido |
| `updated_by` | Quién cambió el estado |

**Regla de negocio:** si `calcular = 0`, el rol queda excluido del cálculo automático aunque tenga escalas activas configuradas. Este control opera en tiempo real: si se apaga un rol, la próxima factura que cierre ya no genera comisión para ese rol.

---

### `dias_gracia_comision` — Retención por mora

| Campo | Descripción |
|-------|-------------|
| `rol_id` | Rol al que aplica la retención |
| `tipo_factura` | `contado` o `credito` |
| `dias_gracia` | Días desde referencia antes de penalizar |
| `porcentaje_retencion` | Porcentaje a retener del subtotal de la factura |
| `descripcion` | Nota interna |

Este catálogo se edita desde la pestaña **Días de Gracia** dentro de Configuración, y es consumido por `AplicadorRetencionesMora` cuando se cierra una factura en el módulo de Pagos.

---

## Todos los endpoints del módulo

### Navegación y catálogos
| Método | URL | Función |
|--------|-----|---------|
| GET | `/comisiones/configuracion` | Carga la vista principal |
| GET | `/comisiones/configuracion/rol` | Lista de roles para selector |
| GET | `/comisiones/configuracion/categorias-precio` | Categorías de precio activas por categoría de cliente (con % ya guardado si existe) |
| GET | `/comisiones/configuracion/categorias-cliente-activas` | Categorías de cliente activas |
| GET | `/comisiones/configuracion/cat-precio-para-filtro` | Categorías de precio filtradas por catCliente (para carga selectiva) |
| GET | `/comisiones/configuracion/roles-para-filtro` | Todos los roles para filtro |

### CRUD de parámetros
| Método | URL | Función |
|--------|-----|---------|
| POST | `/guardar/parametro/comision` | Crea uno o varios registros de comision_escala |
| GET | `/listar/parametros/comision` | Listado DataTable de todos los parámetros |
| GET | `/parametro-comision/{id}` | Datos de un registro puntual para editar |
| POST | `/actualizar/parametro/comision/{id}` | Modifica el porcentaje de un registro existente |
| POST | `/desactivar/parametro-comision/{id}` | Desactiva (estado 2) sin borrar |

### KPIs y resumen analítico
| Método | URL | Función |
|--------|-----|---------|
| GET | `/comisiones/configuracion/stats` | Tarjetas: activos, roles, cat. cliente, promedio % |
| GET | `/comisiones/configuracion/resumen-por-rol` | Tabla agrupada rol × catCliente con mín/prom/máx de % |

### Control de cálculo por rol
| Método | URL | Función |
|--------|-----|---------|
| GET | `/comisiones/configuracion/roles-calculo` | Lista todos los roles con su estado calcular y si tienen escala |
| POST | `/comisiones/configuracion/roles-calculo/toggle` | Activa/desactiva cálculo para un rol (flip 1↔0) |

### Carga masiva (Excel)
| Método | URL | Función |
|--------|-----|---------|
| GET | `/comisiones/configuracion/plantilla-masiva` | Descarga Excel con todas las combinaciones activas y % actuales |
| POST | `/comisiones/configuracion/carga-masiva` | Procesa el Excel y aplica insertos/actualizaciones |
| GET | `/comisiones/configuracion/plantilla-filtrada` | Descarga plantilla filtrada por rol/catCliente/catPrecio |
| POST | `/comisiones/configuracion/preview-carga-filtrada` | Simula la carga sin guardar: devuelve existentes/nuevos/omitidos |
| POST | `/comisiones/configuracion/procesar-carga-filtrada` | Procesa la carga selectiva real |

---

## Funcionalidades detalladas

### 1. Crear parámetro de comisión

**Flujo:**
1. Usuario elige rol + categoría de cliente.
2. El sistema carga dinámicamente todas las categorías de precio activas para esa categoría de cliente, prellenando los % ya configurados si existen.
3. Usuario llena los porcentajes que desea activar (puede dejar en blanco las que no apliquen).
4. Se envía un array de filas `[{categoria_precios_id, porcentaje}]`.

**Reglas del servidor:**
- Requiere rol, categoría cliente y al menos una fila con porcentaje > 0.
- Si ya existe una combinación activa idéntica → la **omite** sin error.
- Solo inserta las filas nuevas no duplicadas.
- Retorna cuántos registros fueron insertados.

---

### 2. Editar porcentaje

Edita **solo** el campo `porcentaje_comision` del registro seleccionado. No cambia rol ni categorías. Registra usuario y fecha de modificación.

---

### 3. Desactivar parámetro

Cambia `estado_id` a 2 (inactivo). **No borra el registro.** Esto garantiza trazabilidad histórica. Los registros inactivos no participan en el cálculo de comisiones.

---

### 4. Control de cálculo por rol (toggle)

Permite encender o apagar el cálculo de comisiones para un rol completo sin tocar sus porcentajes configurados.

**Reglas:**
- Si `calcular = 0` → el rol no genera comisión aunque tenga escalas activas.
- El toggle funciona con UPSERT (crea el registro si no existe).
- Efecto inmediato: la próxima factura que cierre ya respeta el nuevo estado.

La tabla muestra:
- Estado actual (activo/inactivo).
- Si tiene al menos una escala configurada.
- Quién hizo el último cambio.

---

### 5. KPIs del tablero de configuración

La vista muestra 4 indicadores en tiempo real:

| KPI | Fórmula |
|-----|---------|
| Parámetros activos | `COUNT(*) WHERE estado_id=1` en comision_escala |
| Roles activos | `COUNT(DISTINCT rol_id) WHERE estado_id=1` |
| Categorías cliente cubiertas | `COUNT(DISTINCT cliente_categoria_escala_id) WHERE estado_id=1` |
| Promedio % comisión | `AVG(porcentaje_comision) WHERE estado_id=1` |

---

### 6. Resumen por rol

Agrupa todos los registros activos por `rol × categoría cliente` y devuelve:
- Cantidad de configuraciones
- % mínimo, promedio y máximo

Sirve para detectar dispersión o inconsistencias de porcentajes por rol.

---

### 7. Plantilla masiva Excel (Export)

**Genera combinaciones:** `rol × cliente_categoria_escala × categoria_precios` (solo activos).

**Columnas de la plantilla:**

| Col | Campo | Editable |
|-----|-------|----------|
| A | rol_id | No (protegida) |
| B | Nombre del rol | No |
| C | cliente_categoria_id | No |
| D | Nombre categoría cliente | No |
| E | categoria_precio_id | No |
| F | Nombre categoría precio | No |
| G | **% Comisión** | **Sí** |

La hoja está protegida con contraseña (`profac2026`). Solo la columna G es editable. Si ya existe un % configurado, aparece prellenado para edición rápida.

---

### 8. Import masivo (Carga Excel)

**Reglas de procesamiento por fila:**
- Si `porcentaje ≤ 0` o vacío → **omite**.
- Si algún ID (rol, catCliente, catPrecio) no existe en BD → **omite y registra error**.
- Si la combinación **ya existe activa** → **actualiza** el porcentaje y reactiva si estaba inactivo.
- Si **no existe** → **inserta** nuevo registro.

**Contadores de resultado:**
- `insertados` — nuevos registros creados
- `actualizados` — registros editados o reactivados
- `omitidos` — filas ignoradas
- `errores` — errores con número de fila y motivo

La carga selectiva permite filtrar primero por rol/catCliente/catPrecio para trabajar solo el subconjunto deseado.

---

### 9. Días de gracia y retención por mora

Configurable por **rol + tipo de factura (contado/crédito)**:

| Campo | Descripción |
|-------|-------------|
| Días de gracia | Días desde la referencia antes de penalizar |
| % retención | Porcentaje del **subtotal de la factura** a retener |

**Referencia de fecha según tipo:**
- Contado → `fecha_emision`
- Crédito → `fecha_vencimiento`

Estos datos los consume `AplicadorRetencionesMora` automáticamente al cerrar cada factura. Ver fórmulas en la sección de conexión con Pagos.

---

## CONEXIÓN COMPLETA CON /cuentas_por_cobrar/pagos

### Cuándo se activa la conexión

La conexión se dispara automáticamente cuando:
1. **Un abono deja saldo en 0** en `aplicacion_pagos` (cierre automático).
2. **El usuario cierra manualmente** la factura desde el módulo de Pagos.

En ambos casos se ejecuta la cadena de servicios:

```
GeneradorFacturasComision
    → AplicadorRetencionesMora
        → ProcesadorComisiones
```

---

### Flujo técnico completo

```
PAGOS: Abono registrado
    ↓
SP sp_aplicacion_pagos actualiza saldo
    ↓
¿Saldo = 0?
    NO → Fin, sin comisiones
    SÍ → GeneradorFacturasComision::generar($facturaId, $apId, $fechaPago)
         ↓
         Lee factura.users_id (facturador) y factura.vendedor
         ↓
         Construye 4 posibles targets:
           · Tipo 1: facturador → ROL_FACTURADOR_ID fijo (3)
           · Tipo 2: facturador → su rol real (si difiere del 3 y del 2)
           · Tipo 3: vendedor  → ROL_VENDEDOR_ID fijo (2)
           · Tipo 4: gestor de entrega → ROL_GESTOR_ENTREGA_ID fijo (16)
         ↓
         Deduplica por rol_id (un rol recibe comisión solo una vez por factura)
         ↓
         Filtra roles con comision_rol_config.calcular = 0
         ↓
         Filtra roles sin escala activa en comision_escala
         ↓
         Por cada target restante:
           · Busca porcentaje en comision_escala por rol + categoria_precios_id del producto
           · Calcula comision por línea
           · Inserta en facturas_comision y producto_comision
         ↓
         AplicadorRetencionesMora::aplicar()
         ↓
         Evalúa mora por tipo de factura
         ↓
         ProcesadorComisiones::procesar()
         ↓
         Verifica si el mes está conciliado en comision_periodo
         ↓
         Si abierto: acredita en comision_empleado
         Si conciliado: no acredita (el registro facturas_comision queda como evidencia)
```

---

### Fórmulas reales de cálculo

**Comisión por línea de producto:**

$$
C_{linea} = precio\_unidad \times cantidad \times \frac{porcentaje\_comision}{100}
$$

**Total comisión del rol en la factura (monto_rol):**

$$
monto\_rol = \sum_{i=1}^{n} C_{linea_i}
$$

**Acumulado mensual del empleado:**

$$
comision\_acumulada_{mes} = \sum monto\_rol_{facturas\_del\_mes}
$$

---

### Retención por mora — Contado

Se aplica cuando: `dias_transcurridos > dias_gracia`

Referencia: `fecha_emision`

**Resultado:** comisión total a cero (pérdida completa).

$$
comision\_final = 0
$$

---

### Retención por mora — Crédito

Referencia: `fecha_vencimiento`

$$
periodos\_vencidos = \left\lfloor \frac{dias\_transcurridos}{dias\_gracia} \right\rfloor
$$

$$
retencion\_total = periodos\_vencidos \times subtotal\_factura \times \frac{\%\_retencion}{100}
$$

$$
comision\_final = \max(0,\ monto\_rol - retencion\_total)
$$

Se genera **un registro de log por período vencido** en `retencion_mora_log` para auditoría completa.

---

### Preview de comisiones antes de guardar el pago

Antes de que el usuario confirme el abono, el sistema consulta:

**GET** `/pagos/preview-comisiones`

Evalúa (sin modificar datos):
1. ¿La factura ya tiene comisiones activas? → informa que no generará nuevas.
2. ¿El monto del abono cierra el saldo? → si no, no habrá comisión.
3. ¿Qué roles participarían? → lista con capacidad, empleado, rol y si tiene escala configurada.
4. ¿Algún rol tiene `calcular = 0`? → lo excluye del preview.

El usuario ve exactamente qué va a suceder antes de confirmar.

---

### Verificación de período conciliado (integración con Conciliación)

Antes de guardar el abono, el JS consulta:

**GET** `/comisiones/conciliacion/verificar-periodo?fecha=YYYY-MM-DD`

Respuestas posibles:
- **Período abierto:** flujo normal, comisión se acredita en el mes del pago.
- **Período conciliado:** se advierte al usuario y se registra en `abonos_creditos` los campos:
  - `periodo_comision_original` — el mes que estaba cerrado
  - `periodo_comision_asignado` — el próximo mes abierto

La comisión se acredita automáticamente en el mes asignado, no en el original.

---

### Anulación de abono — Reversión de comisiones

Cuando se anula un pago desde el módulo de Pagos:

1. El abono se marca como anulado (`estado_abono = 0`).
2. Se revierte el saldo en `aplicacion_pagos`.
3. Si la factura estaba cerrada → **se reabre**.
4. Si el cliente tiene crédito inicial → se descuenta el abono del crédito disponible.
5. **Por cada `facturas_comision` activa de esa factura:**
   - Se descuenta `monto_rol` de `comision_empleado.comision_acumulada` (nunca baja de 0).
   - Se marca `facturas_comision.estado_id = 2` (inactivo).
   - Se marca `producto_comision.estado_id = 2` (inactivo).
6. Se registra en `comision_reversiones` con JSON completo de comisiones revertidas, motivo, usuario y si la factura fue reabierta.
7. Se genera log en `ModelComisionReversionLog`.

**Garante de integridad:** el mapeo de usuario por `tipo_comision` se respeta también en la reversión.

---

## Reglas de negocio completas — Resumen ejecutivo

| # | Regla |
|---|-------|
| R1 | No se puede duplicar una combinación `rol + catCliente + catPrecio` activa. |
| R2 | Un rol con `calcular = 0` no genera comisión aunque tenga escala configurada. |
| R3 | Un rol sin escala activa no genera comisión aunque `calcular = 1`. |
| R4 | Un mismo rol recibe comisión solo una vez por factura (deduplicación por rol_id). |
| R5 | Tipos 1 y 2 se excluyen mutuamente si el rol real del facturador ya está cubierto por tipo 3. |
| R6 | Si la factura ya tiene comisiones activas, el generador no crea nuevas (idempotencia). |
| R7 | Si el período está conciliado, `ProcesadorComisiones` no acredita en `comision_empleado`. |
| R8 | La comisión de contado fuera de gracia se lleva a cero (100% de pérdida). |
| R9 | La comisión de crédito se penaliza proporcionalmente por períodos completos de gracia vencidos. |
| R10 | La retención no se aplica dos veces a la misma `facturas_comision` (guardia de idempotencia en `retencion_mora_log`). |
| R11 | Al anular un abono, la comisión del empleado nunca baja de cero (uso de `GREATEST(0, ...)`). |
| R12 | Desactivar un parámetro no borra el registro; preserva historial. |
| R13 | La carga masiva actualiza y reactiva registros inactivos si el porcentaje es válido. |
| R14 | La carga masiva omite filas con porcentaje vacío o ≤ 0. |
| R15 | La verificación de período conciliado desvía la acreditación al próximo mes abierto. |

---

## Resumen de propósito

`/comisiones/configuracion` define:
- **Quién** puede comisionar (rol con `calcular = 1` y escala activa).
- **Cuánto** comisiona (porcentaje por combinación rol / catCliente / catPrecio).
- **En qué condiciones pierde** parte o toda la comisión (días de gracia y retención).

`/cuentas_por_cobrar/pagos` ejecuta esas reglas automáticamente en cada cierre de factura, generando evidencia en `facturas_comision`, `producto_comision` y acreditando en `comision_empleado`.
