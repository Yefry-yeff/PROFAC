# Database Operations Summary: Factura & Flujo Records

## Overview
This document describes where factura (invoice) and flujo (flow/history) records are created and updated in the PROFAC system. The database operations are primarily performed in PHP Livewire components and controllers.

---

## 1. FACTURA (Invoice) Record Creation

### 1.1 Corporate Sales (`FacturacionCorporativa.php`)
**File:** [app/Http/Livewire/Ventas/FacturacionCorporativa.php](app/Http/Livewire/Ventas/FacturacionCorporativa.php)

**Method:** `guardarVenta(Request $request)` (Line 652)

**Routes:**
- `POST /ventas/corporativo/guardar` → `FacturacionCorporativa@guardarVenta`

**Key Operations:**
1. **Creates ModelFactura instance** (Lines 877-920):
   - Generates numero_factura, CAI, sequence number
   - Sets invoice details (client name, RTN, amounts, dates, etc.)
   - Sets payment terms and credit days
   - Assigns CAI and estado_factura_id
   - Calls `$factura->save()` to INSERT into `factura` table

2. **Inserts venta_has_producto records** (Line 1017):
   - `ModelVentaProducto::insert($this->arrayProductos)` for invoice line items

3. **Inserts log_translados records** (Line 1018):
   - `ModelLogTranslados::insert($this->arrayLogs)` for inventory tracking

**AJAX Call Location:**
- Called from facturacion-corporativa.blade.php form submission
- Returns response with factura ID and success status

---

### 1.2 State/Government Sales (`FacturacionEstatal.php`)
**File:** [app/Http/Livewire/Ventas/FacturacionEstatal.php](app/Http/Livewire/Ventas/FacturacionEstatal.php)

**Method:** `guardarVenta(Request $request)` (Line 639)

**Routes:**
- `POST /ventas/estatal/guardar` → `FacturacionEstatal@guardarVenta`

**Key Operations:**
- Similar to corporate sales but for government/state invoices (tipo_venta_id = 2)
- Uses same factura model insertion pattern

---

### 1.3 Exempted Sales (`VentasExoneradas.php`)
**File:** [app/Http/Livewire/VentasExoneradas/VentasExoneradas.php](app/Http/Livewire/VentasExoneradas/VentasExoneradas.php)

**Method:** `guardarVenta(Request $request)` (Line 89)

**Routes:**
- `POST /exonerado/venta/guardar` → `VentasExoneradas@guardarVenta`

**Key Operations:**
- Creates factura records for exempted/exonerated sales (tipo_venta_id = 3)
- `$factura->save()` to persist

---

### 1.4 Prefactura to Factura Conversion (`PrefacturaController.php`)
**File:** [app/Http/Livewire/Flujo/PrefacturaController.php](app/Http/Livewire/Flujo/PrefacturaController.php)

**Method:** `prefacturarDesdeOferta(Request $request)` (Line 261)

**Routes:**
- `POST /cotizacion/prefacturar-desde-oferta` → `PrefacturaController@prefacturarDesdeOferta`

**Key Operations:**
1. Creates prefactura record (Line 1119):
   - `DB::table('prefactura')->insertGetId([...])`
   
2. Inserts prefactura_has_producto records (Line 1173):
   - `DB::table('prefactura_has_producto')->insert($prefProds)`

3. **Registers in historico_flujo** (Line 1178):
   - tipo_tramite_id = 4 (Prefactura)
   - estado_id = 1 (Active)

4. Updates flujo status (Line 1192):
   - `DB::table('flujo')->update(['tipo_tramite_id' => 4])`

---

## 2. FLUJO (Flow) & HISTORICO_FLUJO (Flow History) Record Creation

### 2.1 Cotizaciones (Quotes) Flow Creation (`Cotizacion.php`)
**File:** [app/Http/Livewire/Cotizaciones/Cotizacion.php](app/Http/Livewire/Cotizaciones/Cotizacion.php)

**Key Lines:**
- Line 373, 388, 400, 419, 434, 446, 461, 473

**Operations:**
1. **Creates flujo record when needed** (Line 388):
   ```php
   $flujoNuevo = DB::table('flujo')->insertGetId([
       'tipo_flujo_id'   => 1,
       'identificacion'  => (string) $cotizacion->id,
       'nombre'          => $cotizacion->nombre_cliente,
       'cliente_rtn'     => $request->rtn_ventas,
       'tipo_tramite_id' => 2,  // 'Ofertas'
       'estado_id'       => 1,
       'created_by'      => Auth::id(),
       'created_at'      => now(),
   ])
   ```

2. **Inserts historico_flujo for quotations** (Line 373, 400, 419, 446, 473):
   - tipo_tramite_id = 2 (Ofertas/Quotes)
   - tramite_id = cotizacion.id
   - estado_id = 1 (Active)

3. **Marks winning offers** (Lines 628-642):
   - Updates historico_flujo observations to mark as "ganadora" (winner)

---

### 2.2 Modal Flujo Pedido (`ModalFlujoPedido.php`)
**File:** [app/Http/Livewire/Flujo/ModalFlujoPedido.php](app/Http/Livewire/Flujo/ModalFlujoPedido.php)

**Key Operations:**

1. **Insert historico_flujo for quotations** (Line 934):
   ```php
   DB::table('historico_flujo')->insert([
       'flujo_id'        => $flujoId,
       'tipo_tramite_id' => 2,  // Ofertas
       'tramite_id'      => $cotizacionId,
       'estado_id'       => 5,  // Pendiente
       'observaciones'   => 'En Revisión de Crédito. Oferta #' . $cotizacionId,
       'created_by'      => Auth::id(),
       'created_at'      => now(),
   ])
   ```

2. **Create prefactura and register flow** (Lines 1119, 1178):
   - Insert prefactura
   - Insert historico_flujo for prefactura (tipo_tramite_id = 4)
   - Update flujo tipo_tramite_id to 4

3. **Handle credit revision** (Lines 979):
   - Insert historico_flujo tipo_tramite_id = 10 (Revisión de Crédito)

---

### 2.3 Factura Confirmation Flow (`FacturacionCorporativa.php`)
**File:** [app/Http/Livewire/Ventas/FacturacionCorporativa.php](app/Http/Livewire/Ventas/FacturacionCorporativa.php)

**Method:** `confirmarFacturaFlujo(Request $request)` (Line 469)

**Routes:**
- `POST /flujo/factura/confirmar` → `FacturacionCorporativa@confirmarFacturaFlujo`

**Key Operations:**
1. **Creates or finds flujo for factura** (Lines 495-551):
   - Searches for existing historico_flujo entry for factura
   - Or searches for flujo from linked pedido
   - Or creates new flujo using factura data

2. **Inserts historico_flujo records**:
   - **Factura Record** (Line 571):
     - tipo_tramite_id = 3 (Factura)
     - tramite_id = factura.id
     - estado_id = 1 (Active)
   
   - **Entrega Record** (Line 593):
     - tipo_tramite_id = 5 (Entrega/Delivery)
     - tramite_id = NULL
     - estado_id = 5 (Pendiente)
   
   - **Cobro Record** (Line 631):
     - tipo_tramite_id = 6 (Cobro/Collection)
     - tramite_id = aplicacion_pagos.id
     - estado_id = 5 (Pendiente)

3. **Updates flujo** (Line 549):
   - tipo_tramite_id = 7 (Flujo conjunto - Entrega + Cobro)

---

### 2.4 Credit Review (`RevisionCreditos.php`)
**File:** [app/Http/Livewire/Flujo/RevisionCreditos.php](app/Http/Livewire/Flujo/RevisionCreditos.php)

**Key Operations:**
1. **Inserts cliente_credito** (Line 476):
   ```php
   DB::table('cliente_credito')->insert([...])
   ```

2. **Inserts historico_flujo** (Line 657):
   - tipo_tramite_id = 10 (Revisión de Crédito)
   - tracks credit approval/rejection

---

### 2.5 Inventory Review (`RevicionInventario.php`)
**File:** [app/Http/Livewire/Flujo/RevicionInventario.php](app/Http/Livewire/Flujo/RevicionInventario.php)

**Key Operations:**
1. **Creates prefactura** (Line 448):
   ```php
   $prefacturaId = DB::table('prefactura')->insertGetId([...])
   ```

2. **Inserts prefactura_has_producto** (Line 503)

3. **Inserts historico_flujo** (Line 519):
   - tipo_tramite_id = 9 (Revisión de Inventario)
   - estado_id = 1

---

## 3. AJAX Callers & View Files

### 3.1 Corporate Invoicing
**View:** [resources/views/livewire/ventas/facturacion-corporativa.blade.php](resources/views/livewire/ventas/facturacion-corporativa.blade.php)

**AJAX Calls:**
- Form submission to `/ventas/corporativo/guardar`
- Returns factura ID and success status
- Then calls `/flujo/factura/confirmar` to register flow history

**Example JavaScript:**
```javascript
axios.post('/ventas/corporativo/guardar', formDataObj)
  .then(response => {
    // Success - returns factura ID
  })
```

### 3.2 Modal Flow Pedido
**View:** [resources/views/livewire/flujo/modal-flujo-pedido.blade.php](resources/views/livewire/flujo/modal-flujo-pedido.blade.php)

**AJAX Calls:**
- Line 2158: `axios.post(e.detail.url, { tipo_pago: 1 })`
- Handles prefactura creation and flow registration

### 3.3 Exempted Sales
**View:** [resources/views/livewire/ventas-exoneradas/ventas-exoneradas.blade.php](resources/views/livewire/ventas-exoneradas/ventas-exoneradas.blade.php)

**AJAX Calls:**
- Line 1375: `axios.post('/exonerado/venta/guardar', formDataObj, options)`

---

## 4. Database Tables Affected

### factura table
- **INSERT operations:** `guardarVenta()` methods
- **UPDATE operations:** estado_factura_id, estado_venta_id, estado_editar
- **Fields populated:** numero_factura, cai, cliente_id, total, isv, etc.

### prefactura table
- **INSERT operations:** `prefacturarDesdeOferta()`, `RevicionInventario.php`
- **Fields:** cotizacion_id, flujo_id, cliente_id, total, etc.

### prefactura_has_producto table
- **INSERT operations:** Product line items for prefacturas

### flujo table
- **INSERT operations:** When creating new flow records
- **UPDATE operations:** Changing tipo_tramite_id as flow progresses
- **Fields:** tipo_flujo_id, identificacion, nombre, cliente_rtn, tipo_tramite_id, estado_id

### historico_flujo table
- **INSERT operations:** Recording each step in the flow
- **UPDATE operations:** Changing estado_id, observaciones as flow progresses
- **Fields:**
  - flujo_id (FK to flujo)
  - tipo_tramite_id (1=Pedido, 2=Ofertas, 3=Factura, 4=Prefactura, 5=Entrega, 6=Cobro, 7=Flujo conjunto, 9=Inventario, 10=Crédito)
  - tramite_id (FK to specific transaction, null for some types)
  - estado_id (1=Active, 5=Pending, 7=Cancelled)
  - observaciones (narrative field)

### venta_has_producto table
- **INSERT operations:** Invoice line items

### aplicacion_pagos table
- **References:** Used to link cobro records in historico_flujo

### cliente_credito table
- **INSERT operations:** Credit approval/limit records

---

## 5. Key Flow Patterns

### Pattern 1: Simple Sale to Invoice to Flow
1. `guardarVenta()` → INSERT factura + venta_has_producto
2. `confirmarFacturaFlujo()` → INSERT/UPDATE flujo + historico_flujo (3 records: factura, entrega, cobro)

### Pattern 2: Quote → Prefactura → Factura
1. `Cotizacion.php` → INSERT flujo + historico_flujo (type 2 - Ofertas)
2. Mark as winner → UPDATE historico_flujo observaciones
3. `ModalFlujoPedido.php` → INSERT prefactura + historico_flujo (type 4)
4. `confirmarFacturaFlujo()` → Final factura + flow registration

### Pattern 3: Credit Review Flow
1. `ModalFlujoPedido.php` → INSERT historico_flujo (type 10 - Revisión de Crédito)
2. `RevisionCreditos.php` → INSERT cliente_credito + UPDATE historico_flujo
3. Approve → INSERT historico_flujo (type 9 - Inventario)

---

## 6. Transactions & Error Handling

Most database operations wrap in transactions:
```php
DB::beginTransaction();
try {
    // Insert/Update operations
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
}
```

---

## 7. Authentication & Logging

All operations record:
- `created_by` / `updated_by` → Auth::id()
- `created_at` / `updated_at` → now()
- Observaciones field tracks state changes and reasons

---

## 8. Related Models

- **ModelFactura:** App\Models\Factura
- **ModelVentaProducto:** App\Models\VentaHasProducto
- **ModelLogTranslados:** App\Models\LogTranslado
- **ModelCAI:** App\Models\CAI
- **CreditoRevision:** App\Models\CreditoRevision

---

## Summary Table

| Operation | File | Method | Route | Database |
|-----------|------|--------|-------|----------|
| Create Corporate Invoice | FacturacionCorporativa.php | guardarVenta() | POST /ventas/corporativo/guardar | factura, venta_has_producto |
| Create Government Invoice | FacturacionEstatal.php | guardarVenta() | POST /ventas/estatal/guardar | factura, venta_has_producto |
| Create Exempted Invoice | VentasExoneradas.php | guardarVenta() | POST /exonerado/venta/guardar | factura, venta_has_producto |
| Confirm Invoice Flow | FacturacionCorporativa.php | confirmarFacturaFlujo() | POST /flujo/factura/confirmar | flujo, historico_flujo |
| Create Quote Flow | Cotizacion.php | save() | Livewire event | flujo, historico_flujo |
| Create Prefactura | ModalFlujoPedido.php | Methods | Livewire event | prefactura, prefactura_has_producto, historico_flujo |
| Create Prefactura from Quote | PrefacturaController.php | prefacturarDesdeOferta() | POST /cotizacion/prefacturar-desde-oferta | prefactura, prefactura_has_producto, historico_flujo |

