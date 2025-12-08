# Modulo de Logistica de Entregas

Sistema completo de gestion de entregas con equipos, distribucion y confirmacion de productos entregados.

## Instalacion

### 1. Ejecutar el Schema SQL

```bash
# Conectar a MySQL
mysql -u root -p profac_app

# Ejecutar el script
source c:/laragon/www/Valencia/PROFAC/SISTEMA/profac-app/database/migrations/logistica_entregas_schema.sql
```

O desde phpMyAdmin/HeidiSQL:
1. Abrir la base de datos `profac_app`
2. Importar el archivo `database/migrations/logistica_entregas_schema.sql`

### 2. Verificar Tablas Creadas

```sql
SHOW TABLES LIKE 'equipos_entrega%';
SHOW TABLES LIKE 'distribuciones_entrega%';
SHOW TABLES LIKE 'entregas_%';
```

Debe mostrar 6 tablas:
- equipos_entrega
- equipos_entrega_miembros
- distribuciones_entrega
- distribuciones_entrega_facturas
- entregas_productos
- entregas_evidencias

### 3. Verificar Triggers

```sql
SHOW TRIGGERS WHERE `Table` = 'entregas_productos';
```

Debe mostrar 2 triggers:
- trg_actualizar_estado_factura_after_producto
- trg_actualizar_estado_factura_after_update

## Uso del Sistema

### Modulo 1: Equipos de Entrega

**Ruta:** `/logistica/equipos`

#### Crear Equipo
1. Clic en "Nuevo Equipo"
2. Llenar nombre y descripcion
3. Agregar miembros con su porcentaje de comision
4. **Importante:** La suma de porcentajes no puede exceder 100%
5. Guardar

#### Validaciones:
- Al menos 1 miembro requerido
- Porcentajes entre 0-100
- No usuarios duplicados
- Suma total <= 100%

#### Acciones:
- **Ver Miembros:** Ver lista completa con porcentajes
- **Desactivar:** Solo si no tiene distribuciones activas

---

### Modulo 2: Distribucion de Entregas

**Ruta:** `/logistica/distribuciones`

#### Crear Distribucion
1. Seleccionar equipo activo
2. Seleccionar fecha programada
3. Buscar facturas (por numero, cliente, telefono)
4. Agregar facturas una por una
5. Guardar distribucion

#### Estados de Distribucion:
1. **Pendiente** (amarillo) - Recien creada
2. **En Proceso** (azul) - Iniciada, lista para entregas
3. **Completada** (verde) - Todas las facturas entregadas
4. **Cancelada** (rojo) - Distribucion cancelada

#### Acciones:
- **Iniciar:** Cambiar de Pendiente a En Proceso
- **Ver Facturas:** Detalle de todas las facturas asignadas
- **Cancelar:** Solo en Pendiente o En Proceso
- **Confirmar Entregas:** Ir al modulo de confirmacion

#### Progreso:
Se muestra con badges de colores:
- Verde: Facturas entregadas completas
- Amarillo: Facturas parciales
- Rojo: Sin entrega
- Barra de progreso visual

---

### Modulo 3: Confirmacion de Entregas

**Ruta:** `/logistica/confirmacion`

#### Proceso de Confirmacion

1. **Seleccionar Fecha:** Muestra distribuciones en proceso para esa fecha

2. **Seleccionar Distribucion:** Ver facturas del equipo

3. **Por cada Factura:**
   - Ver cliente, direccion, telefono
   - Lista de productos facturados
   - Checkboxes para marcar entregados
   
4. **Marcar Productos:**
   - Individual: Check en cada producto
   - Masivo: "Marcar Todos" por factura
   - "Seleccionar Todos": Checkbox en header de tabla

5. **Registrar Incidencias:**
   - Clic en icono de advertencia
   - Tipos disponibles:
     * Producto danado
     * Cantidad incorrecta
     * Cliente rechazo
     * Direccion incorrecta
     * Otro
   - Descripcion detallada

6. **Guardar Confirmacion:**
   - Los triggers actualizan automaticamente el estado:
     * 0 productos = `sin_entrega`
     * Todos = `entregado`
     * Algunos = `parcial`

#### Evidencias (Opcional)
- Subir fotos de entrega
- Firma del cliente
- Fotos de incidencias
- Otros documentos

---

## Flujo de Trabajo Completo

```
1. CREAR EQUIPO
   |
   v
2. AGREGAR MIEMBROS (con % comision)
   |
   v
3. CREAR DISTRIBUCION
   |
   v
4. AGREGAR FACTURAS
   |
   v
5. INICIAR DISTRIBUCION
   |
   v
6. EQUIPO SALE A ENTREGAR
   |
   v
7. CONFIRMAR ENTREGAS
   |
   v
8. MARCAR PRODUCTOS ENTREGADOS
   |
   v
9. REGISTRAR INCIDENCIAS (si hay)
   |
   v
10. SISTEMA ACTUALIZA ESTADOS AUTOMATICAMENTE
```

## Reportes Disponibles

### Consultas SQL Utiles

#### Equipos con sus Miembros
```sql
SELECT 
    e.nombre_equipo,
    u.name AS miembro,
    m.porcentaje_comision
FROM equipos_entrega e
INNER JOIN equipos_entrega_miembros m ON e.id = m.equipo_entrega_id
INNER JOIN users u ON m.user_id = u.id
WHERE e.estado_id = 1
AND m.estado_id = 1;
```

#### Distribuciones del Dia
```sql
SELECT 
    d.id,
    e.nombre_equipo,
    COUNT(df.id) as total_facturas,
    SUM(CASE WHEN df.estado_entrega = 'entregado' THEN 1 ELSE 0 END) as entregadas
FROM distribuciones_entrega d
INNER JOIN equipos_entrega e ON d.equipo_entrega_id = e.id
LEFT JOIN distribuciones_entrega_facturas df ON d.id = df.distribucion_entrega_id
WHERE DATE(d.fecha_programada) = CURDATE()
AND d.estado_id = 2
GROUP BY d.id;
```

#### Incidencias del Mes
```sql
SELECT 
    d.fecha_programada,
    e.nombre_equipo,
    p.nombre AS producto,
    ep.tipo_incidencia,
    ep.descripcion_incidencia
FROM entregas_productos ep
INNER JOIN distribuciones_entrega_facturas df ON ep.distribucion_factura_id = df.id
INNER JOIN distribuciones_entrega d ON df.distribucion_entrega_id = d.id
INNER JOIN equipos_entrega e ON d.equipo_entrega_id = e.id
INNER JOIN producto p ON ep.producto_id = p.id
WHERE ep.tiene_incidencia = 1
AND MONTH(d.fecha_programada) = MONTH(CURDATE());
```

## Validaciones Importantes

### Equipos
- Porcentaje total <= 100%
- No usuarios duplicados
- No desactivar con distribuciones activas

### Distribuciones
- Facturas no duplicadas en distribuciones activas
- Solo equipos activos
- Estados secuenciales

### Confirmacion
- Triggers actualizan estado automaticamente
- Cantidad entregada <= cantidad facturada
- Productos solo pueden confirmarse una vez

## Archivos del Sistema

### Backend (Livewire Controllers)
- `app/Http/Livewire/Logistica/EquiposEntrega.php`
- `app/Http/Livewire/Logistica/DistribucionEntrega.php`
- `app/Http/Livewire/Logistica/ConfirmacionEntrega.php`

### Frontend (Blade Views)
- `resources/views/livewire/Logistica/equipos-entrega.blade.php`
- `resources/views/livewire/Logistica/distribucion-entrega.blade.php`
- `resources/views/livewire/Logistica/confirmacion-entrega.php`

### Modelos
- `app/Models/Logistica/EquipoEntrega.php`
- `app/Models/Logistica/EquipoEntregaMiembro.php`
- `app/Models/Logistica/DistribucionEntrega.php`
- `app/Models/Logistica/DistribucionEntregaFactura.php`
- `app/Models/Logistica/EntregaProducto.php`
- `app/Models/Logistica/EntregaEvidencia.php`

### Rutas
Todas definidas en `routes/web.php` bajo el grupo `/logistica/*`

## Soporte

Para mas detalles tecnicos ver:
- `LOGISTICA_ENTREGAS_DOCUMENTACION.md` - Documentacion completa
- `database/migrations/logistica_entregas_schema.sql` - Schema con comentarios

## Proximos Pasos Sugeridos

1. Agregar menu de navegacion en el sidebar
2. Dashboard con estadisticas del dia
3. Notificaciones push para equipos
4. Mapa de rutas de entrega
5. Calculo automatico de comisiones
6. Reportes PDF/Excel
7. Historial de entregas por cliente
8. Evaluacion de desempeno por equipo
