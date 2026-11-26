# Integración POS: traslados e inventario espejo

La integración no sincroniza compras ni autoriza ventas en PROFAC del inventario espejo POS. Los traslados recibidos desde POS se almacenan pendientes sin afectar existencias; un usuario con permisos del historial y de `inventario/translado` debe distribuirlos y aprobarlos o rechazarlos. El rechazo no devuelve stock automáticamente al emisor. El comprobante no es factura.

Cuando POS rechaza un traslado de PROFAC, un usuario autorizado debe confirmar el retorno físico completo desde el historial. Solo entonces se restituye a los lotes originales y se envía `transfer.returned`; si faltan los lotes o cambian los registros originales, se bloquea toda la operación. Si PROFAC rechazó un traslado, el evento `transfer.returned` de POS solo actualiza su estado y auditoría, sin crear existencias en PROFAC.

## Preparación

En API Paperland, la pestaña **Guía local / VPS** contiene el paso a paso por entorno y un diagrama de conexión. La pestaña **Conexión y operación** conserva los controles existentes. Abrir la guía no ejecuta comandos ni modifica datos; la preparación de referencias, catálogo e inventario se realiza en POS.

Coordinar ambos extremos y respaldar antes de habilitar. Verificar en la base de PROFAC que `recibido_bodega.compra_id` acepta NULL y que sus demás columnas admiten las mismas inserciones que un ingreso normal. Las ubicaciones reales, productos, unidades y presentaciones deben estar configurados; la sección 433 queda reservada al flujo hacia POS. Verificar las restricciones locales antes de instalar: no se modifican tablas legadas en esta migración.

Instalar solamente la migración individual, desde `SISTEMA\profac-app`:

```powershell
php artisan migrate --path=database\migrations\2026_11_24_200000_create_pos_integration_tables.php
```

Instalar además, por separado, la migración `database\migrations\2026_11_24_210000_create_paperland_integration_settings.php`. Esta añade Configuración → API Paperland y concede el nuevo submenú únicamente al rol Administrador inicialmente, sin modificar permisos existentes. El singleton nace deshabilitado y sin clave.

Instalar individualmente después `database\migrations\2026_11_24_220000_create_paperland_connection_runtime.php`. Conserva el estado y la clave cifrada existentes en el perfil `Default/actual`; no inicia ningún servicio ni habilita una conexión previamente deshabilitada.

En Configuración → API Paperland, un usuario autorizado puede guardar varios perfiles, pero solo uno seleccionado. La clave se cifra con `Crypt` y nunca se muestra al abrir la página; dejarla vacía conserva la clave de ese perfil. Generar una clave es una acción explícita: cópiela al POS por un canal seguro antes de guardarla. No se modifica `.env`. Se requiere clave de al menos 32 caracteres y URL de origen HTTP(S) válida para habilitar. Para desarrollo local el POS puede estar en `http://127.0.0.1:8000`; fuera de desarrollo se requiere transporte seguro. No registrar ni compartir la clave o los cuerpos de eventos.

El endpoint anterior `/configuracion/api-paperland/guardar` ya no admite cambios después de instalar perfiles, para evitar saltarse la verificación de identidad. Si una pestaña abierta muestra ese aviso, recargue la página y use los controles de perfiles.

La prueba y activación consultan exclusivamente `GET /api/pos-integration/health` firmado, incluso cuando el POS está deshabilitado. La activación verifica el UUID de instancia POS, habilita la conexión y **no inicia** el worker. Tras vincular una instancia, una instancia diferente queda bloqueada si existen eventos, traslados o espejo; URLs distintas con el mismo UUID son válidas. Use bases de datos separadas para producción y pruebas. Detenga el worker, espere a que termine la ronda y resuelva las operaciones, bandejas y traslados pendientes antes de cambiar de perfil. Se bloquea eliminar el activo y editar URL, tienda o clave de un perfil activo habilitado; deshabilítelo primero. La preparación de catálogo e inventario se ejecuta en la pantalla API del POS; PROFAC se declara preparado.

Un perfil migrado puede conservar la configuración habilitada anterior, pero hasta verificar y activar su instancia no se admiten eventos, despachos a POS ni órdenes de ronda. Cambiar URL, tienda o clave de un perfil invalidará su verificación y exige activarlo de nuevo con una prueba firmada; la identidad vinculada anterior se conserva. Si existían pendientes de una integración anterior sin identidad vinculada, requieren revisión operativa antes de vincularla.

La configuración persistida prevalece sobre `PROFAC_POS_ENABLED`, `PROFAC_POS_SECRET` y `PROFAC_POS_PEER_URL`; las variables solo actúan como respaldo cuando la tabla o el registro aún no existen. Cada solicitud pertinente y cada ronda recargan los valores sin reinicio. Si falla la lectura o descodificación de la clave, la integración falla cerrada. Configure un cache compartido y permisos de escritura para el heartbeat de rondas. El estado en línea del coordinador depende de heartbeat y lease en base de datos, no de la orden de inicio.

### Pruebas locales Windows/Laragon

No necesita un VPS ni systemd. Abra una terminal en la carpeta de Profac y otra en la del POS, y mantenga ejecutándose `php artisan pos-integration:manage` en cada una. Si PHP no está en PATH, invoque su ejecutable mediante la ruta completa y el operador `&` de PowerShell. El supervisor mantiene el heartbeat y ejecuta las solicitudes de las vistas, incluso si cierra el navegador. Al cerrar los procesos o reiniciar Windows, levántelos nuevamente. Iniciar/Pausar controla el procesamiento, no crea el proceso supervisor. POS requiere completar su preparación antes de iniciar el worker.

### Servicio Linux

Descargue desde la pantalla el archivo `profac-paperland.service` protegido por el permiso de configuración. Ajuste `User=www-data` al usuario efectivo apropiado de la aplicación/PHP-FPM, `WorkingDirectory` y la ruta PHP CLI; por defecto se usa `/usr/bin/php` y puede configurarse `PROFAC_POS_PHP_CLI` en el entorno de despliegue. PHP CLI requiere la extensión `pcntl`, y el usuario requiere acceso a la aplicación, BD y cache. Instale una sola vez por SSH como administrador del servidor:

```sh
sudo install -m 0644 profac-paperland.service /etc/systemd/system/profac-paperland.service
sudo systemctl daemon-reload
sudo systemctl enable --now profac-paperland.service
sudo systemctl status profac-paperland.service
```

La aplicación no instala systemd ni invoca sudo. Después use Iniciar, Pausar y Procesar ronda en la pantalla: Pausar espera la ronda en curso, pero el coordinador permanece en línea. El lease de BD evita dos coordinadores simultáneos; se renueva cada dos segundos y vence tras quince. Un proceso hijo por ronda tiene límite de 1800 segundos. Al recibir SIGTERM, el coordinador detiene su proceso hijo identificado, marca fallida la ronda y libera el lease; `KillMode=mixed` limpia el grupo restante si vence el plazo. Tras una caída, la ronda interrumpida queda marcada como fallida y el operador puede solicitar otra. Las operaciones y su solicitante se conservan; solo se guarda un resultado genérico, nunca claves ni salida cruda. El comando `php artisan pos-integration:manage --once` ejecuta como máximo una ronda y termina; está diseñado para validaciones controladas. No ejecute `pos-integration:work` directamente una vez instalada la migración de perfiles: el coordinador lo ejecuta de forma exclusiva.

La outbox se inserta en la transacción del traslado; se reintentan fallos de red, HTTP 408, 429 y 5xx con espera creciente. Los demás HTTP 4xx quedan en estado `failed`, visibles en el historial; investigar y corregir antes de reencolarlos mediante intervención operativa controlada. Un bloqueo de envío caduca a los dos minutos. Las respuestas HTTP válidas incluyen `status` igual a `processed`, `duplicate` o `pending`. El buzón y el procesamiento son atómicos; los pendientes persistidos se recuperan con el trabajador.

## Transporte y datos

En Historial de Traslados, **Historial de Traslados** conserva las consultas por traslado y por producto. **Historial de Traslados de Paperland** separa **Traslados entrantes Paperland** y **Traslados enviados a Paperland**, sin cambiar las acciones de recepción o retorno. No incluye paneles de movimientos o colas; sus registros siguen almacenados y no se eliminan. Imprimir un enviado reutiliza el documento local original y el mismo PDF del historial de traslados; si falta ese documento, la impresión se bloquea explícitamente. Los entrantes abren un PDF tamaño carta con el membrete del comprobante normal, origen Paperland y destino Profac, estado en español, códigos de ambos sistemas, cantidades base, lotes y vencimientos. Conserva referencia/UUID y, si existe, la distribución recibida. Imprimir no aprueba la recepción, no crea compras ni modifica existencias.

En el detalle del catálogo (incluido el catálogo de apoyo), **Disponibilidad de Producto** contiene las pestañas **Inventario en bodegas** (tabla y total originales) e **Inventario en Paperland** (solo consulta). La segunda muestra exclusivamente el espejo del ID Profac consultado, con cinco columnas: código de producto Paperland, nombre, bodega, unidad de medida y cantidad. Cada presentación ocupa una fila y su cantidad corresponde a esa presentación, no al total de unidades base; se conservan las tiendas y productos segmentados. Sin presentaciones informadas, se muestra el saldo físico en unidades base o «Pendiente de regularizar» si es desconocido. Los IDs de unidades Paperland no se consultan en las unidades de Valencia. Esta tabla tiene búsqueda, paginación y exportación independientes: no se suma al total de bodegas, no modifica existencias, no habilita ventas ni interviene en reservas. Los datos corresponden al último evento recibido; recargar el detalle consulta el espejo nuevamente.

Los catálogos admiten `per_page` de 1 a 500, manteniendo 100 como valor predeterminado. POS usa páginas de 500 para la preparación masiva. Se mantiene el límite HTTP de la API; POS respeta `Retry-After` ante 429 y solicita la misma página hasta tres intentos, con firma actualizada. Si el límite persiste, la operación falla explícitamente y conserva las referencias verificadas.

Ambos peers usan `POST /api/pos-integration/events` y catálogos `GET /api/pos-integration/catalog/{entity}` (`units`, `brands`, `categories`, `subcategories`, `products`, `presentations`). Para cada petición se firma el cuerpo exacto con HMAC-SHA256 usando `timestamp + '.' + cuerpo`; en GET el cuerpo es vacío. Los encabezados son `X-Integration-Timestamp` (segundos Unix) y `X-Integration-Signature` (hex minúscula); tolerancia de reloj: cinco minutos. Catálogos devuelven `data` y `next_page`; `updated_since` incluye registros sin fecha de actualización y devuelve todo si la tabla no tiene `updated_at`.

Con la integración habilitada, seleccionar sección 433 identifica el envío PROFAC→POS: se descuenta solo el origen y no se crea lote local de destino. La mercancía permanece en tránsito hasta la recepción POS. Antes del commit se capturan exclusivamente los IDs de los logs persistidos `Translado de bodega` de esa operación, con `destino` NULL, sin reactivar históricos. `transfer.received` solo cambia el estado de integración y no altera existencias locales. Con integración deshabilitada permanece el comportamiento anterior. La cantidad proviene del log, no del saldo mutable de la ubicación. Cada lote conserva su vencimiento y cantidad base aunque no sea múltiplo de su presentación. Para lotes con compra, el costo base es `compra_has_producto.precio_unidad`; para lotes sin compra es el `producto.costo_promedio` explícito al despachar. Una compra sin costo, un costo ambiguo, una presentación inconsistente o un vencimiento inválido bloquea el traslado completo en lugar de inventar un costo.

Los lotes históricos ya existentes en la sección 433 no se modifican ni se reconcilian automáticamente. Antes de activar el espejo, revisar con el operador su correspondencia con los saldos POS; no sumar ambos como si fueran existencias independientes.

La recepción exige asignación exacta por línea, con presentación activa del producto y cadena bodega/segmento/sección válida; se crean lotes `recibido_bodega` con `compra_id` NULL solo al aprobar. El evento de recepción y el movimiento de auditoría se guardan en la misma transacción. Los campos y movimientos originales se conservan como snapshots de integración, incluso el costo de origen.

El inventario espejo solo acepta versiones más recientes por tienda/producto. `physical_base`, disponibilidad y reserva pueden ser NULL; si faltan unidades de presentación o de reserva se muestra «Unidades pendientes de regularizar», sin convertir valores desconocidos en cero. `known_physical_base` es únicamente un subtotal identificado y `unresolved_lots` cuenta los lotes sin resolver. Los `unit_id` en las presentaciones del espejo son IDs locales POS y no implican equivalencia con unidades PROFAC. Los movimientos legacy con factor, delta o saldos desconocidos se conservan sin transformar. Los movimientos se conservan incluso para versiones antiguas recibidas.

## Pruebas aisladas

```powershell
php vendor\bin\phpunit tests\Feature\PosIntegrationIsolatedTest.php --no-coverage
```

Esta clase crea sus tablas en SQLite `:memory:` y comprueba la conexión antes de crearlas. La prueba del proceso hijo clona esa base aislada a un archivo SQLite en el directorio de la aplicación y lo elimina al finalizar. El resto de pruebas del proyecto puede usar otra configuración; no lanzar suites generales sin comprobar su aislamiento.
