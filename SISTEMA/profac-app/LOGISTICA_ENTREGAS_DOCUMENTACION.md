# MÓDULO DE LOGÍSTICA DE ENTREGAS - DOCUMENTACIÓN

## 📋 RESUMEN EJECUTIVO

Se han creado 3 módulos para la gestión completa de entregas:

1. **Equipos de Entrega**: Gestión de equipos y asignación de porcentajes de comisión
2. **Distribución de Entregas**: Programación de entregas por equipo y fecha
3. **Confirmación de Entregas**: Registro de entregas, incidencias y evidencias

---

## 🗄️ ESTRUCTURA DE BASE DE DATOS

### Tablas Creadas

#### 1. `equipos_entrega`
Tabla principal de equipos de entrega.

**Campos principales:**
- `id`: ID único
- `nombre_equipo`: Nombre del equipo
- `descripcion`: Descripción opcional
- `estado_id`: 1=Activo, 2=Inactivo
- `users_id_creador`: Usuario que creó el equipo

#### 2. `equipos_entrega_miembros`
Miembros de los equipos con porcentajes de comisión.

**Campos principales:**
- `id`: ID único
- `equipo_entrega_id`: FK → equipos_entrega
- `user_id`: FK → users
- `porcentaje_comision`: Decimal(5,2) - 0 a 100%
- `estado_id`: 1=Activo, 2=Inactivo

**Restricciones:**
- Unique constraint: Un usuario solo puede estar una vez por equipo
- Check constraint: Porcentaje entre 0 y 100

#### 3. `distribuciones_entrega`
Programación de distribuciones de entrega.

**Campos principales:**
- `id`: ID único
- `equipo_entrega_id`: FK → equipos_entrega
- `fecha_programada`: Fecha de entrega
- `observaciones`: Notas generales
- `estado_id`: 1=Pendiente, 2=En proceso, 3=Completada, 4=Cancelada
- `users_id_creador`: Usuario que creó la distribución

#### 4. `distribuciones_entrega_facturas`
Facturas asignadas a cada distribución.

**Campos principales:**
- `id`: ID único
- `distribucion_entrega_id`: FK → distribuciones_entrega
- `factura_id`: FK → facturacion
- `orden_entrega`: Orden en la ruta (opcional)
- `estado_entrega`: ENUM('sin_entrega', 'parcial', 'entregado')
- `fecha_entrega_real`: Timestamp de entrega
- `observaciones`: Notas específicas

**Restricciones:**
- Unique constraint: Una factura solo en una distribución

#### 5. `entregas_productos`
Registro detallado de productos entregados e incidencias.

**Campos principales:**
- `id`: ID único
- `distribucion_factura_id`: FK → distribuciones_entrega_facturas
- `producto_id`: FK → producto
- `cantidad_facturada`: Cantidad en factura
- `cantidad_entregada`: Cantidad real entregada
- `entregado`: Boolean (1=Sí, 0=No)
- `tiene_incidencia`: Boolean
- `descripcion_incidencia`: Texto de la incidencia
- `tipo_incidencia`: VARCHAR(50) - faltante, dañado, rechazo, etc
- `user_id_registro`: Usuario que registró
- `fecha_registro`: Timestamp del registro

#### 6. `entregas_evidencias`
Evidencias fotográficas y documentales (opcional).

**Campos principales:**
- `id`: ID único
- `distribucion_factura_id`: FK → distribuciones_entrega_facturas
- `tipo_evidencia`: ENUM('foto_entrega', 'firma_cliente', 'incidencia', 'otro')
- `ruta_archivo`: Ruta del archivo
- `descripcion`: Descripción opcional
- `user_id_registro`: Usuario que subió

---

## 🔗 RELACIONES Y LLAVES FORÁNEAS

### Dependencias de Tablas Existentes

```sql
users → equipos_entrega (users_id_creador)
users → equipos_entrega_miembros (user_id)
users → distribuciones_entrega (users_id_creador)
users → entregas_productos (user_id_registro)
users → entregas_evidencias (user_id_registro)

facturacion → distribuciones_entrega_facturas (factura_id)
producto → entregas_productos (producto_id)
```

### Cascadas y Restricciones

**ON DELETE CASCADE:**
- `equipos_entrega` → `equipos_entrega_miembros`
- `distribuciones_entrega` → `distribuciones_entrega_facturas`
- `distribuciones_entrega_facturas` → `entregas_productos`
- `distribuciones_entrega_facturas` → `entregas_evidencias`

**ON DELETE RESTRICT:**
- `users` → `equipos_entrega` (no se puede eliminar usuario creador)
- `equipos_entrega` → `distribuciones_entrega` (no se puede eliminar equipo con distribuciones)
- `facturacion` → `distribuciones_entrega_facturas` (no se puede eliminar factura asignada)

**ON DELETE SET NULL:**
- `users` → `entregas_productos.user_id_registro` (se permite eliminar usuario)
- `users` → `entregas_evidencias.user_id_registro`

---

## ⚙️ TRIGGERS AUTOMÁTICOS

### Actualización de Estado de Factura

Se crearon 2 triggers que automáticamente actualizan el estado de entrega:

**trg_actualizar_estado_factura_after_producto:**
- Se ejecuta AFTER INSERT en `entregas_productos`
- Calcula automáticamente si la factura está: sin_entrega / parcial / entregado
- Actualiza `distribuciones_entrega_facturas.estado_entrega`

**trg_actualizar_estado_factura_after_update:**
- Se ejecuta AFTER UPDATE en `entregas_productos`
- Mismo comportamiento que el trigger anterior

**Lógica de Estados:**
```
productos_entregados = 0           → sin_entrega
productos_entregados = total       → entregado
0 < productos_entregados < total   → parcial
```

---

## 📦 MODELOS DE LARAVEL CREADOS

### 1. EquipoEntrega.php
**Ubicación:** `app/Models/Logistica/EquipoEntrega.php`

**Métodos principales:**
- `miembros()`: Relación con miembros
- `miembrosActivos()`: Solo miembros activos
- `distribuciones()`: Distribuciones asignadas
- `getTotalPorcentajesAttribute()`: Suma de porcentajes
- `tieneCupoParaPorcentaje($porcentaje)`: Validar disponibilidad

### 2. EquipoEntregaMiembro.php
**Ubicación:** `app/Models/Logistica/EquipoEntregaMiembro.php`

**Métodos principales:**
- `equipo()`: Relación con equipo
- `usuario()`: Relación con usuario
- `estaActivo()`: Verificar estado

### 3. DistribucionEntrega.php
**Ubicación:** `app/Models/Logistica/DistribucionEntrega.php`

**Métodos principales:**
- `equipo()`: Relación con equipo
- `facturas()`: Todas las facturas
- `facturasSinEntregar()`: Facturas pendientes
- `facturasParciales()`: Entregas parciales
- `facturasEntregadas()`: Entregas completadas
- `getProgresoAttribute()`: Porcentaje de avance

### 4. DistribucionEntregaFactura.php
**Ubicación:** `app/Models/Logistica/DistribucionEntregaFactura.php`

**Métodos principales:**
- `distribucion()`: Relación con distribución
- `factura()`: Relación con factura
- `productosEntregados()`: Productos de la factura
- `evidencias()`: Evidencias adjuntas
- `estaEntregada()`, `esParcial()`, `sinEntrega()`: Estados

### 5. EntregaProducto.php
**Ubicación:** `app/Models/Logistica/EntregaProducto.php`

**Métodos principales:**
- `producto()`: Relación con producto
- `marcarComoEntregado($cantidad, $userId)`: Registrar entrega
- `registrarIncidencia($tipo, $descripcion, $userId)`: Crear incidencia

### 6. EntregaEvidencia.php
**Ubicación:** `app/Models/Logistica/EntregaEvidencia.php`

**Métodos principales:**
- `distribucionFactura()`: Relación con factura
- `getUrlArchivoAttribute()`: URL completa del archivo

---

## 📝 INSTRUCCIONES DE INSTALACIÓN

### 1. Ejecutar Scripts SQL

```bash
# Conectar a MySQL
mysql -u tu_usuario -p nombre_base_datos

# Ejecutar el script
source /ruta/completa/logistica_entregas_schema.sql
```

O copiar y pegar el contenido del archivo en tu cliente MySQL favorito (phpMyAdmin, MySQL Workbench, etc.).

### 2. Verificar Creación de Tablas

```sql
SHOW TABLES LIKE '%entrega%';
SHOW TABLES LIKE '%distribucion%';
```

Deberías ver 6 tablas nuevas.

### 3. Verificar Triggers

```sql
SHOW TRIGGERS LIKE 'entregas_productos';
```

Deberías ver 2 triggers.

### 4. Verificar Relaciones

```sql
SELECT 
    TABLE_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'nombre_tu_base_datos'
AND REFERENCED_TABLE_NAME IS NOT NULL
AND TABLE_NAME LIKE '%entrega%';
```

---

## 🚀 PRÓXIMOS PASOS

### Para Completar el Módulo:

1. **Crear Controladores Livewire:**
   - `EquiposEntrega.php`
   - `DistribucionEntrega.php`
   - `ConfirmacionEntrega.php`

2. **Actualizar las Vistas Blade:**
   - `equipos-entrega.blade.php`
   - `distribucion-entrega.blade.php`
   - `confirmacion-entrega.php`

3. **Crear Rutas:**
   - Agregar rutas en `routes/web.php`

4. **Validaciones:**
   - Validar que suma de porcentajes no exceda 100%
   - Validar que factura no esté en otra distribución activa
   - Validar fechas de entrega

5. **Funcionalidades Adicionales:**
   - Generación de reportes
   - Cálculo de comisiones
   - Notificaciones push
   - Geolocalización (GPS)
   - Firma digital del cliente

---

## 📊 CONSULTAS ÚTILES

### Obtener equipos con sus miembros y porcentajes

```sql
SELECT 
    e.id,
    e.nombre_equipo,
    u.name AS miembro,
    m.porcentaje_comision,
    (SELECT SUM(porcentaje_comision) 
     FROM equipos_entrega_miembros 
     WHERE equipo_entrega_id = e.id AND estado_id = 1) AS total_porcentajes
FROM equipos_entrega e
INNER JOIN equipos_entrega_miembros m ON e.id = m.equipo_entrega_id
INNER JOIN users u ON m.user_id = u.id
WHERE e.estado_id = 1 AND m.estado_id = 1
ORDER BY e.id, m.porcentaje_comision DESC;
```

### Entregas del día con detalle de estado

```sql
SELECT 
    d.id AS distribucion_id,
    d.fecha_programada,
    e.nombre_equipo,
    f.id AS factura_id,
    df.estado_entrega,
    COUNT(ep.id) AS total_productos,
    SUM(ep.entregado) AS productos_entregados,
    SUM(ep.tiene_incidencia) AS productos_con_incidencia
FROM distribuciones_entrega d
INNER JOIN equipos_entrega e ON d.equipo_entrega_id = e.id
INNER JOIN distribuciones_entrega_facturas df ON d.id = df.distribucion_entrega_id
LEFT JOIN facturacion f ON df.factura_id = f.id
LEFT JOIN entregas_productos ep ON df.id = ep.distribucion_factura_id
WHERE d.fecha_programada = CURDATE()
GROUP BY d.id, f.id
ORDER BY e.nombre_equipo, df.orden_entrega;
```

### Incidencias del día

En **Confirmación de entregas**, el modal de incidencias muestra el código, la descripción y la unidad de medida del producto. La **cantidad a devolver** es obligatoria, mayor que cero y admite hasta dos decimales. La suma de las incidencias del producto no puede exceder su cantidad en esa entrega, incluidas las incidencias ya guardadas. Las incidencias anteriores al cambio conservan su cantidad como «No registrada».

**Agregar incidencia** la deja en la lista como pendiente de guardar; el registro se guarda al pulsar **Confirmar entrega**.

```sql
SELECT 
    ep.tipo_incidencia,
    ep.descripcion_incidencia,
    p.nombre AS producto,
    f.id AS factura_id,
    u.name AS registrado_por,
    ep.fecha_registro
FROM entregas_productos ep
INNER JOIN distribucion_entrega_facturas df ON ep.distribucion_factura_id = df.id
INNER JOIN distribuciones_entrega d ON df.distribucion_entrega_id = d.id
INNER JOIN producto p ON ep.producto_id = p.id
LEFT JOIN facturacion f ON df.factura_id = f.id
LEFT JOIN users u ON ep.user_id_registro = u.id
WHERE ep.tiene_incidencia = 1
AND d.fecha_programada = CURDATE()
ORDER BY ep.fecha_registro DESC;
```

---

## ⚠️ VALIDACIONES IMPORTANTES

### A Nivel de Base de Datos:
✅ Porcentajes entre 0 y 100 (CHECK constraint)
✅ Una factura solo en una distribución (UNIQUE constraint)
✅ Un producto solo una vez por factura (UNIQUE constraint)

### A Nivel de Aplicación (Por Implementar):
- [ ] Suma de porcentajes de equipo no exceda 100%
- [ ] Factura no esté en otra distribución activa
- [ ] Fecha programada no sea pasada al crear distribución
- [ ] Solo productos de la factura pueden registrarse
- [ ] Cantidad entregada no exceda cantidad facturada

---

## 🎯 CASOS DE USO

### 1. Crear Equipo de Entrega
```php
$equipo = EquipoEntrega::create([
    'nombre_equipo' => 'Equipo Norte',
    'descripcion' => 'Entregas zona norte',
    'estado_id' => 1,
    'users_id_creador' => auth()->id()
]);

// Agregar miembros
$equipo->miembros()->create([
    'user_id' => 5,
    'porcentaje_comision' => 60.00,
    'estado_id' => 1
]);

$equipo->miembros()->create([
    'user_id' => 8,
    'porcentaje_comision' => 40.00,
    'estado_id' => 1
]);
```

### 2. Programar Distribución
```php
$distribucion = DistribucionEntrega::create([
    'equipo_entrega_id' => 1,
    'fecha_programada' => '2025-12-08',
    'observaciones' => 'Ruta prioridad alta',
    'estado_id' => 1,
    'users_id_creador' => auth()->id()
]);

// Asignar facturas
$distribucion->facturas()->create([
    'factura_id' => 1001,
    'orden_entrega' => 1,
    'estado_entrega' => 'sin_entrega'
]);
```

### 3. Registrar Entrega
```php
// Obtener factura de distribución
$distFactura = DistribucionEntregaFactura::find(1);

// Crear registros de productos desde la factura
foreach ($factura->productos as $producto) {
    EntregaProducto::create([
        'distribucion_factura_id' => $distFactura->id,
        'producto_id' => $producto->id,
        'cantidad_facturada' => $producto->cantidad,
        'cantidad_entregada' => 0,
        'entregado' => 0
    ]);
}

// Marcar producto como entregado
$entregaProducto = EntregaProducto::find(1);
$entregaProducto->marcarComoEntregado(10, auth()->id());

// El trigger actualiza automáticamente el estado de la factura
```

---

## 📞 SOPORTE

Para dudas o problemas con la implementación, revisar:
1. Los comentarios en el código de los modelos
2. Los comentarios en el script SQL
3. Esta documentación

---

**Versión:** 1.0  
**Fecha:** 2025-12-07  
**Autor:** Sistema PROFAC
