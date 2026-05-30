# Analysis: Saving Factura for "Sin Restricción Gobierno" Type Invoice

## Overview
This document traces the exact code flow for saving an invoice (factura) of type "sin restriccion gobierno" (unrestricted government) in the PROFAC system, including where the `factura`, `flujo`, and `historico_flujo` records are created and how `cliente_id` is assigned.

---

## 1. Main Method: `guardarVenta()` in FacturacionEstatal.php

**File:** [app/Http/Livewire/Ventas/FacturacionEstatal.php](app/Http/Livewire/Ventas/FacturacionEstatal.php)
**Method:** `guardarVenta(Request $request)`  
**Line Range:** [639-951](app/Http/Livewire/Ventas/FacturacionEstatal.php#L639-L951)

### Key Operation: Factura INSERT

```php
$factura = new ModelFactura;
$factura->numero_factura = $numeroVenta->numero;
$factura->cai = $numeroCAI;
$factura->numero_secuencia_cai = $numeroSecuencia;
$factura->nombre_cliente = $request->nombre_cliente_ventas;
$factura->rtn = $request->rtn_ventas;
$factura->sub_total = $request->subTotalGeneral;
$factura->sub_total_grabado = $request->subTotalGeneralGrabado;
$factura->sub_total_excento = $request->subTotalGeneralExcento;
$factura->isv = $request->isvGeneral;
$factura->total = $request->totalGeneral;
$factura->credito = $request->totalGeneral;
$factura->fecha_emision = $request->fecha_emision;
$factura->fecha_vencimiento = $request->fecha_vencimiento;
$factura->tipo_pago_id = $request->tipoPagoVenta;
$factura->dias_credito = $diasCredito;
$factura->cai_id = $cai->id;
$factura->estado_venta_id = 1;
$factura->cliente_id = $request->seleccionarCliente;  // ← CLIENTE_ID ASSIGNED HERE
$factura->vendedor = $request->vendedor;
$factura->monto_comision = $montoComision;
$factura->tipo_venta_id = 2; // estatal (government)
$factura->estado_factura_id = 1; // se presenta
$factura->users_id = Auth::user()->id;
$factura->comision_estado_pagado = 0;
$factura->pendiente_cobro = $request->totalGeneral;
$factura->estado_editar = 1;
$factura->numero_orden_compra_id = $request->ordenCompra;
$factura->comentario = $request->nota_comen;
$factura->porc_descuento = $request->porDescuento;
$factura->monto_descuento = $request->porDescuentoCalculado;
$factura->save();  // ← INSERT EXECUTED HERE
```

**Line 825:** `$factura->cliente_id = $request->seleccionarCliente;`  
**Line 848:** `$factura->save();`

### Key Features:
- **tipo_venta_id = 2** indicates this is an "estatal" (government) type invoice
- **cliente_id** is assigned from `$request->seleccionarCliente` (the selected client ID)
- **Database:** Inserts into `factura` table with all required fields

---

## 2. CAI (Factura Number) Management

**Line Range:** [712-738](app/Http/Livewire/Ventas/FacturacionEstatal.php#L712-L738)

The CAI (Código de Autorización de Impresión - Authorization Code for Printing) is managed:

```php
$cai = DB::SELECTONE("select
    id,
    numero_inicial,
    numero_final,
    cantidad_otorgada,
    numero_actual
    from cai
    where tipo_documento_fiscal_id = 1 and estado_id = 1");

$numeroSecuencia = $cai->numero_actual;
$arrayCai = explode('-', $cai->numero_final);
$cuartoSegmentoCAI = sprintf("%'.08d", $numeroSecuencia);
$numeroCAI = $arrayCai[0] . '-' . $arrayCai[1] . '-' . $arrayCai[2] . '-' . $cuartoSegmentoCAI;

// ... After factura save:
$caiUpdated = ModelCAI::find($cai->id);
$caiUpdated->numero_actual = $numeroSecuencia + 1;
$caiUpdated->cantidad_no_utilizada = $cai->cantidad_otorgada - $numeroSecuencia;
$caiUpdated->save();
```

---

## 3. Inventory and Product Records

**Line Range:** [876-918](app/Http/Livewire/Ventas/FacturacionEstatal.php#L876-L918)

Products are saved through `restarUnidadesInventario()` method:

```php
for ($i = 0; $i < count($arrayInputs); $i++) {
    // ... Extract product data from request
    $this->restarUnidadesInventario(
        $precios_producto_carga_id, 
        $idPrecioSeleccionado,
        $precioSeleccionado,
        $restaInventario, 
        $idProducto, 
        $idSeccion, 
        $factura->id,  // ← Factura ID passed here
        $idUnidadVenta, 
        $precio, 
        $cantidad, 
        $subTotal, 
        $isv, 
        $total, 
        $ivsProducto, 
        $unidad, 
        $arrayInputs[$i]
    );
}

// Insert product records:
ModelVentaProducto::insert($this->arrayProductos);
ModelLogTranslados::insert($this->arrayLogs);
```

**Method:** `restarUnidadesInventario()`  
**Line Range:** [955-1234](app/Http/Livewire/Ventas/FacturacionEstatal.php#L955-L1234)

This method:
1. Decreases inventory from `recibido_bodega` table
2. Inserts product records into `venta_has_producto` table with `factura_id`
3. Creates log entries in `log_translados` table

---

## 4. FLUJO and HISTORICO_FLUJO Records

**IMPORTANT:** The `guardarVenta()` method in FacturacionEstatal.php does **NOT** directly create flujo or historico_flujo records.

Instead, a separate method in **FacturacionCorporativa.php** handles this after the factura is created:

### Method: `confirmarFacturaFlujo()` in FacturacionCorporativa.php

**File:** [app/Http/Livewire/Ventas/FacturacionCorporativa.php](app/Http/Livewire/Ventas/FacturacionCorporativa.php)
**Method:** `confirmarFacturaFlujo(Request $request)`  
**Line Range:** [469-600+](app/Http/Livewire/Ventas/FacturacionCorporativa.php#L469)
**Route:** `POST /flujo/factura/confirmar`

### Flujo Creation Logic (Line 492-538):

```php
// If no flujo_id exists, search or create one
if (!$flujoId) {
    // Check if historico_flujo already has this factura
    $flujoExistente = DB::table('historico_flujo')
        ->where('tipo_tramite_id', 3)  // Factura type
        ->where('tramite_id', $facturaId)
        ->where('estado_id', '!=', 7)
        ->value('flujo_id');

    if ($flujoExistente) {
        $flujoId = (int) $flujoExistente;
    } else {
        // Check if pedido has existing flujo
        if ($pedidoIdReq) {
            $flujoPedido = DB::table('flujo')
                ->where('identificacion', (string) $pedidoIdReq)
                ->where('tipo_flujo_id', 1)
                ->value('id');
            if ($flujoPedido) {
                $flujoId = (int) $flujoPedido;
            }
        }

        // Create new flujo from factura data
        if (!$flujoId) {
            $facturaData = DB::table('factura')
                ->where('id', $facturaId)
                ->first(['nombre_cliente', 'rtn']);

            $flujoId = DB::table('flujo')->insertGetId([
                'tipo_flujo_id'   => 1,
                'identificacion'  => (string) $facturaId,
                'nombre'          => $facturaData->nombre_cliente,
                'cliente_rtn'     => $facturaData->rtn ?? null,
                'tipo_tramite_id' => 7,  // Flujo conjunto (Entrega + Cobro)
                'created_by'      => Auth::id(),
                'updated_by'      => Auth::id(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }
}
```

**IMPORTANT NOTE:** The `flujo` table does **NOT** have a direct `cliente_id` field. Instead:
- **`identificacion`** field stores the factura_id or pedido_id
- **`nombre`** field stores the client name
- **`cliente_rtn`** field stores the client's RTN
- **`cliente_id`** is maintained in the `factura` table and can be retrieved via joins

### Historico_Flujo Records (3 records created):

```php
// 1. Factura record (tipo_tramite_id = 3)
DB::table('historico_flujo')->insert([
    'flujo_id'        => $flujoId,
    'tipo_tramite_id' => 3,  // Factura
    'tramite_id'      => $facturaId,
    'estado_id'       => 1,  // Active
    'observaciones'   => 'Factura #' . $facturaId . ' registrada',
    'created_by'      => Auth::id(),
    'updated_by'      => Auth::id(),
    'created_at'      => now(),
    'updated_at'      => now(),
]);

// 2. Entrega record (tipo_tramite_id = 5)
DB::table('historico_flujo')->insert([
    'flujo_id'        => $flujoId,
    'tipo_tramite_id' => 5,  // Entrega (Delivery)
    'tramite_id'      => null,
    'estado_id'       => 5,  // Pending
    'observaciones'   => 'Entrega pendiente para factura #' . $facturaId,
    'created_by'      => Auth::id(),
    'updated_by'      => Auth::id(),
    'created_at'      => now(),
    'updated_at'      => now(),
]);

// 3. Cobro record (tipo_tramite_id = 6)
DB::table('historico_flujo')->insert([
    'flujo_id'        => $flujoId,
    'tipo_tramite_id' => 6,  // Cobro (Collection/Payment)
    'tramite_id'      => null,
    'estado_id'       => 5,  // Pending
    'observaciones'   => 'Cobro pendiente para factura #' . $facturaId,
    'created_by'      => Auth::id(),
    'updated_by'      => Auth::id(),
    'created_at'      => now(),
    'updated_at'      => now(),
]);
```

---

## 5. Flujo Table Structure (No cliente_id field)

**File:** [database/migrations/2026_04_15_204055_create_flujo_table.php](database/migrations/2026_04_15_204055_create_flujo_table.php)

```php
Schema::create('flujo', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tipo_flujo_id')->constrained('tipos_flujo');
    $table->string('identificacion');      // Contains factura_id or pedido_id
    $table->string('nombre');              // Client name
    $table->string('estado');
    $table->foreignId('estatus_id')->constrained('tipos_estatus');
    $table->unsignedBigInteger('created_by')->nullable();
    $table->unsignedBigInteger('updated_by')->nullable();
    $table->timestamps();
});
```

**Added Field Migration:** [database/migrations/2026_04_30_005715_add_cliente_rtn_to_flujo_table.php](database/migrations/2026_04_30_005715_add_cliente_rtn_to_flujo_table.php)

```php
$table->string('cliente_rtn', 30)->nullable()->after('nombre');
```

---

## 6. Complete Flow Sequence

1. **guardarVenta()** [FacturacionEstatal.php:639]
   - Validates request data
   - Creates factura record with `cliente_id = $request->seleccionarCliente`
   - Updates CAI counter
   - Saves product records with inventory deduction
   - Saves credit log (if credit payment)
   - Commits transaction
   - Returns JSON response with `factura->id`

2. **Frontend (JavaScript)** receives factura ID
   - Calls `confirmarFacturaFlujo()` endpoint with `factura_id`

3. **confirmarFacturaFlujo()** [FacturacionCorporativa.php:469]
   - Creates or finds flujo record
   - Inserts 3 historico_flujo records (Factura, Entrega, Cobro)
   - Updates flujo to "Flujo conjunto" state

---

## 7. Cliente ID Assignment Flow

```
REQUEST DATA
    ↓
$request->seleccionarCliente (client ID from form)
    ↓
$factura->cliente_id = $request->seleccionarCliente  [Line 825]
    ↓
$factura->save()  [Line 848]
    ↓
INSERT INTO factura (cliente_id, ...)
    ↓
FLUJO (no direct cliente_id, but linked via:)
    ├─ flujo.identificacion = factura.id
    └─ factura.cliente_id can be joined
```

---

## 8. Key Configuration

- **Tipo Venta ID:** 2 (Estatal/Government)
- **Estado Venta ID:** 1 (Active)
- **Estado Factura ID:** 1 (Se presenta/Presented)
- **Tipo Pago ID:** From request (1=Cash, 2=Credit)
- **Tipo Tramite ID for Flujo:** 7 (Flujo conjunto - Entrega + Cobro)

---

## 9. Related Methods

| Method | File | Purpose |
|--------|------|---------|
| `restarUnidadesInventario()` | FacturacionEstatal.php:955 | Inventory reduction & product save |
| `restarCreditoCliente()` | FacturacionEstatal.php:1240+ | Credit deduction (if payment_type=2) |
| `comprobarCreditoCliente()` | FacturacionEstatal.php:1165+ | Validate client credit availability |
| `comprobarFacturaVencida()` | FacturacionEstatal.php:1230+ | Check expired invoices |
| `confirmarFacturaFlujo()` | FacturacionCorporativa.php:469 | Create flujo & historico_flujo |

---

## Summary Table

| Element | Location | Line(s) | Key Detail |
|---------|----------|---------|-----------|
| **Factura INSERT** | FacturacionEstatal.php | 639-951 | `type_venta_id=2, cliente_id=$request->seleccionarCliente` |
| **cliente_id Assignment** | FacturacionEstatal.php | 825 | `$factura->cliente_id = $request->seleccionarCliente` |
| **Factura Save** | FacturacionEstatal.php | 848 | `$factura->save()` |
| **Flujo Creation** | FacturacionCorporativa.php | 469-538 | Creates flujo with `identificacion=factura_id` |
| **Historico_Flujo Insert** | FacturacionCorporativa.php | 540-600+ | 3 records: Factura, Entrega, Cobro |
| **Flujo Table** | migrations/2026_04_15_204055 | - | No `cliente_id` field; uses `identificacion` + joins to factura |
| **Historico_Flujo Table** | migrations/2026_04_15_204131 | - | Links to flujo via `flujo_id` |

