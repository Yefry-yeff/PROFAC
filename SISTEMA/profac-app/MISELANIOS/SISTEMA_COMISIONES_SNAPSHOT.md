# Sistema de Comisiones con Snapshot Dinámico de Miembros

## Problema a Resolver

Cuando un equipo de entrega realiza entregas, cada miembro debe recibir comisión según el porcentaje que tenía asignado **el día que se realizó la entrega**. Además, si se edita el equipo el mismo día antes de iniciar las entregas, esos cambios deben reflejarse en las comisiones.

### Ejemplo del Problema:
- **Lunes 8:00 AM**: Se crea distribución con Juan (40%), María (30%), Pedro (30%)
- **Lunes 9:00 AM**: Se edita equipo, Pedro cambia a 35% y María a 25%
  - ✅ El snapshot DEBE actualizarse porque aún no han iniciado entregas
- **Lunes 10:00 AM**: La distribución cambia a "En Proceso" (inician entregas)
- **Lunes 11:00 AM**: Se intenta quitar a Pedro del equipo
  - ❌ El snapshot NO debe cambiar porque ya iniciaron entregas
- **Martes**: Pedro sale del equipo. Nueva distribución: Juan (70%), María (30%)

**Comisiones a Pagar:**
- Del lunes: Juan 40%, María 25%, Pedro 35% (según último snapshot antes de iniciar)
- Del martes: Juan 70%, María 30% (Pedro ya no está)

## Solución Implementada

### 1. Tabla: `distribuciones_entrega_miembros`

Esta tabla guarda un **snapshot (foto instantánea)** de quiénes eran los miembros y sus porcentajes cuando se creó cada distribución.

```sql
CREATE TABLE `distribuciones_entrega_miembros` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `distribucion_entrega_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `porcentaje_comision` DECIMAL(5,2) NOT NULL,
  `monto_comision_calculado` DECIMAL(10,2) NULL,
  `pagado` TINYINT NOT NULL DEFAULT 0,
  `fecha_pago` DATETIME NULL,
  `users_id_quien_pago` BIGINT UNSIGNED NULL,
  PRIMARY KEY (`id`)
)
```

### 2. Trigger Automático

#### Trigger 1: Crear snapshot al crear distribución
Cuando se crea una distribución, automáticamente se copian los miembros activos del equipo:

```sql
CREATE TRIGGER `trg_snapshot_miembros_distribucion`
AFTER INSERT ON `distribuciones_entrega`
FOR EACH ROW
BEGIN
    INSERT INTO `distribuciones_entrega_miembros` 
        (`distribucion_entrega_id`, `user_id`, `porcentaje_comision`)
    SELECT NEW.id, eem.user_id, eem.porcentaje_comision
    FROM `equipos_entrega_miembros` eem
    WHERE eem.equipo_entrega_id = NEW.equipo_entrega_id
    AND eem.estado_id = 1;
END
```

#### Trigger 2: Actualizar snapshot cuando se agrega miembro (mismo día, estado PENDIENTE)
```sql
CREATE TRIGGER `trg_actualizar_snapshot_miembros_after_insert`
AFTER INSERT ON `equipos_entrega_miembros`
FOR EACH ROW
BEGIN
    -- Agregar a snapshots de distribuciones PENDIENTES del mismo día
    IF NEW.estado_id = 1 THEN
        INSERT INTO `distribuciones_entrega_miembros` 
            (`distribucion_entrega_id`, `user_id`, `porcentaje_comision`)
        SELECT de.id, NEW.user_id, NEW.porcentaje_comision
        FROM `distribuciones_entrega` de
        WHERE de.equipo_entrega_id = NEW.equipo_entrega_id
        AND de.estado_id = 1  -- Solo PENDIENTES
        AND DATE(de.fecha_programada) = CURDATE();  -- Solo día actual
    END IF;
END
```

#### Trigger 3: Actualizar snapshot cuando se edita miembro (mismo día, estado PENDIENTE)
```sql
CREATE TRIGGER `trg_actualizar_snapshot_miembros_after_update`
AFTER UPDATE ON `equipos_entrega_miembros`
FOR EACH ROW
BEGIN
    -- Actualizar porcentaje en snapshots PENDIENTES del día actual
    IF NEW.estado_id = 1 AND OLD.porcentaje_comision != NEW.porcentaje_comision THEN
        UPDATE `distribuciones_entrega_miembros` dem
        INNER JOIN `distribuciones_entrega` de ON dem.distribucion_entrega_id = de.id
        SET dem.porcentaje_comision = NEW.porcentaje_comision
        WHERE de.equipo_entrega_id = NEW.equipo_entrega_id
        AND de.estado_id = 1  -- Solo PENDIENTES
        AND DATE(de.fecha_programada) = CURDATE()
        AND dem.user_id = NEW.user_id;
    END IF;
    
    -- Remover de snapshots PENDIENTES si desactivan miembro
    IF OLD.estado_id = 1 AND NEW.estado_id != 1 THEN
        DELETE dem FROM `distribuciones_entrega_miembros` dem
        INNER JOIN `distribuciones_entrega` de ON dem.distribucion_entrega_id = de.id
        WHERE de.equipo_entrega_id = NEW.equipo_entrega_id
        AND de.estado_id = 1
        AND DATE(de.fecha_programada) = CURDATE()
        AND dem.user_id = NEW.user_id;
    END IF;
END
```

## Flujo de Trabajo

### 1. Crear Distribución
```php
// Al crear una distribución, el trigger automáticamente crea el snapshot
$distribucion = DistribucionEntrega::create([
    'equipo_entrega_id' => 1,
    'fecha_programada' => '2025-12-10',
    'users_id_creador' => auth()->id(),
]);

// Automáticamente se crean registros en distribuciones_entrega_miembros
// con los miembros actuales del equipo y sus porcentajes
```

### 2. Completar Distribución y Calcular Comisiones
```php
// Cuando la distribución se completa, calcular las comisiones
$totalDistribucion = $distribucion->facturas->sum('total');

foreach ($distribucion->miembrosSnapshot as $miembro) {
    $miembro->calcularComision($totalDistribucion);
}
```

### 3. Registrar Pago de Comisiones
```php
// Cuando se paga a un miembro
$miembro = DistribucionEntregaMiembro::find($id);
$miembro->marcarComoPagada(auth()->id());
```

### 4. Consultar Comisiones Pendientes
```php
// Comisiones pendientes de un usuario
$comisionesPendientes = DistribucionEntregaMiembro::where('user_id', $userId)
    ->pendientesPago()
    ->with('distribucion')
    ->get();

// Total pendiente de pagar a un usuario
$totalPendiente = $comisionesPendientes->sum('monto_comision_calculado');
```

## Ventajas del Sistema

1. **Snapshot Dinámico del Mismo Día**: Permite corregir errores antes de iniciar entregas
2. **Congelamiento Automático**: Una vez que inicia la distribución, el snapshot es inmutable
3. **Histórico Inmutable**: Las comisiones de días anteriores nunca cambian
4. **Auditoría Completa**: Se puede rastrear exactamente quién participó en cada entrega
5. **Flexibilidad Controlada**: El equipo puede cambiar sin afectar distribuciones activas
6. **Control de Pagos**: Se puede saber qué está pagado y qué está pendiente
7. **Reportes Precisos**: Fácil generar reportes de comisiones por período, usuario, equipo, etc.

## Reglas de Actualización del Snapshot

### ✅ El snapshot SE ACTUALIZA cuando:
- La distribución está en estado **PENDIENTE** (estado_id = 1)
- La fecha programada es el **día actual** (CURDATE())
- Se cumple alguna de estas condiciones:
  - Se agrega un nuevo miembro al equipo
  - Se cambia el porcentaje de un miembro existente
  - Se desactiva/remueve un miembro del equipo

### ❌ El snapshot NO se actualiza cuando:
- La distribución ya está en estado **EN PROCESO** (2), **COMPLETADA** (3) o **CANCELADA** (4)
- La fecha programada es de un día **anterior o posterior** al actual
- La distribución ya tiene comisiones calculadas (monto_comision_calculado != NULL)

## Casos de Uso

### Caso 1: Edición el mismo día antes de iniciar
```
08:00 - Crear distribución: Juan 40%, María 30%, Pedro 30%
        Snapshot: [Juan 40%, María 30%, Pedro 30%]
        
09:00 - Editar equipo: Pedro → 35%, María → 25%
        ✅ Snapshot actualizado: [Juan 40%, María 25%, Pedro 35%]
        (Porque está PENDIENTE y es mismo día)
        
10:00 - Iniciar distribución (cambiar a EN PROCESO)
        🔒 Snapshot congelado: [Juan 40%, María 25%, Pedro 35%]
        
11:00 - Intentar quitar a Pedro
        ❌ Snapshot NO cambia
        (Porque ya está EN PROCESO)
        
15:00 - Completar distribución y calcular comisiones
        💰 Comisiones: Juan 40%, María 25%, Pedro 35%
```

### Caso 2: Distribución programada para mañana
```
HOY 10:00 - Crear distribución para MAÑANA: Juan 40%, María 30%, Pedro 30%
            Snapshot: [Juan 40%, María 30%, Pedro 30%]
            
HOY 11:00 - Editar equipo: Pedro → 35%
            ❌ Snapshot NO cambia
            (Porque fecha_programada != CURDATE())
            
MAÑANA 08:00 - Editar equipo: María → 25%
               ✅ Snapshot actualizado: [Juan 40%, María 25%, Pedro 35%]
               (Porque ahora fecha_programada = CURDATE() y está PENDIENTE)
               
MAÑANA 09:00 - Iniciar distribución
               🔒 Snapshot congelado
```

### Caso 4: Reporte de comisiones
```sql
-- Comisiones de Juan en diciembre 2025
SELECT 
    d.fecha_programada,
    dem.porcentaje_comision,
    dem.monto_comision_calculado,
    dem.pagado,
    dem.fecha_pago
FROM distribuciones_entrega_miembros dem
INNER JOIN distribuciones_entrega d ON dem.distribucion_entrega_id = d.id
WHERE dem.user_id = [id_juan]
AND YEAR(d.fecha_programada) = 2025
AND MONTH(d.fecha_programada) = 12
ORDER BY d.fecha_programada;
```

## Modelo Laravel

```php
// Obtener miembros que participaron en una distribución
$distribucion->miembrosSnapshot;

// Calcular comisión
$miembro->calcularComision($totalDistribucion);

// Marcar como pagada
$miembro->marcarComoPagada(auth()->id());

// Consultas útiles
DistribucionEntregaMiembro::pendientesPago()->get();
DistribucionEntregaMiembro::porUsuario($userId)->get();
```

## Conclusión

Este sistema garantiza que cada miembro del equipo reciba exactamente la comisión que le corresponde según su participación en cada distribución específica, independientemente de cambios posteriores en la composición del equipo.
