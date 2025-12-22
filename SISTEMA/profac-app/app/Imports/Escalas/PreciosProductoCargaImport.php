<?php

namespace App\Imports\Escalas;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PreciosProductoCargaImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    // Nombre de la tabla donde se obtendrá la unidad de medida del producto.
    // Permite ajustar el nombre si cambia el esquema o prefijo de la BD.
    private const PRODUCTOS_TABLE = 'producto';

    // Parámetros recibidos desde el controlador (tipo de categoría, filtros, usuario, etc.)
    protected string $tipoCategoria;
    protected int $tipoFiltro;
    protected int $valorFiltro;
    protected int $categoriaPrecioId;
    protected int $userId;
    protected ?int $defaultUnidadMedidaId;
    protected bool $previewMode;  // Modo preview: solo validar, no insertar

    // Contadores y variables de control para estadísticas de proceso
    protected int $rowsRead = 0;
    protected int $rowsInserted = 0;
    protected int $rowsInactivated = 0;
    protected int $rowsSkipped = 0;
    protected int $rowsToProcess = 0;  // Contador para preview

    // Variables de control de encabezados
    protected array $missingHeaders = [];
    protected bool $headersValidated = false;

    // Registro de motivos por los cuales se omiten filas
    protected array $skippedReasons = [];

    // Registro detallado de productos procesados
    protected array $productosInsertados = [];
    protected array $productosInactivados = [];
    protected array $productosParaProcesar = [];  // Preview de productos a procesar
    protected array $productoInfoMap = [];  // Mapeo temporal de info de productos

    /**
     * Constructor: inicializa los parámetros requeridos para el proceso.
     * Se reciben directamente desde el controlador.
     */
    public function __construct(string $tipoCategoria, int $tipoFiltro, int $valorFiltro, int $categoriaPrecioId, int $userId, ?int $defaultUnidadMedidaId = null, bool $previewMode = false)
    {
        $this->tipoCategoria         = $tipoCategoria;
        $this->tipoFiltro            = $tipoFiltro;
        $this->valorFiltro           = $valorFiltro;
        $this->categoriaPrecioId     = $categoriaPrecioId;
        $this->userId                = $userId;
        $this->defaultUnidadMedidaId = $defaultUnidadMedidaId;
        $this->previewMode           = $previewMode;
    }

    /**
     * Define el tamaño de los bloques (chunks) a leer desde el archivo Excel.
     * Procesar en bloques evita desbordar memoria con archivos grandes.
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * Método principal que se ejecuta al leer cada bloque (chunk) del archivo Excel.
     * Aquí se realiza la carga, validación, estructuración e inserción masiva de los registros.
     */
    public function collection(Collection $rows)
    {
        // Si no existen filas en el chunk, termina sin procesar.
        if ($rows->isEmpty()) return;

        /**
         * Validación inicial de los encabezados del archivo.
         * Se ejecuta solo una vez, para verificar que las columnas obligatorias existan.
         */
        if (!$this->headersValidated) {
            $present = array_keys($rows->first()->toArray());
            $required = ['producto_id', 'categoria_precios_id', 'precio_base_venta'];
            $missing = array_diff($required, $present);
            $this->missingHeaders = array_values($missing);
            $this->headersValidated = true;

            // Si faltan encabezados esenciales, se registra un warning y se interrumpe el proceso.
            if (!empty($this->missingHeaders)) {
                Log::warning('[Import precios] Faltan columnas requeridas', ['missing' => $this->missingHeaders]);
                return;
            }
        }

        // Variables auxiliares
        $now = now();
        $batch = [];                // Contendrá los registros listos para insertar
        $groupForInactivate = [];   // Agrupa productos por categoría de precios para actualizar estado a inactivo antes de insertar los nuevos

        /**
         * Se obtiene la lista de IDs de productos presentes en el chunk actual
         * para precargar sus unidades de medida en un solo query (optimización de rendimiento).
         */
        $productoIds = [];
        foreach ($rows as $row) {
            $pid = $this->asInt($row['producto_id'] ?? null);
            if ($pid) $productoIds[] = $pid;
        }
        $productoIds = array_values(array_unique($productoIds));

        /**
         * Se obtiene el mapeo de producto → unidad_medida_compra_id
         * desde la tabla de productos. Esto evita consultas repetitivas dentro del loop.
         */
        $unidadMap = [];
        $productoInfoMap = []; // Mapeo de id => [codigo, nombre]
        if (!empty($productoIds)) {
            $unidadMap = DB::table(self::PRODUCTOS_TABLE)
                ->whereIn('id', $productoIds)
                ->pluck('unidad_medida_compra_id', 'id')
                ->toArray();

            // Obtener información completa de productos para usar en skip()
            // Ajustar nombres de columnas según la estructura real de la tabla
            $productosInfo = DB::table(self::PRODUCTOS_TABLE)
                ->select('id', 'nombre')  // Solo nombre, sin codigo por ahora
                ->whereIn('id', $productoIds)
                ->get();

            foreach ($productosInfo as $prod) {
                $productoInfoMap[$prod->id] = [
                    'codigo' => $prod->id,  // Usar ID como codigo si no existe la columna
                    'nombre' => $prod->nombre
                ];
            }
        }

        // Guardar el mapeo en la propiedad de clase para usar en skip()
        $this->productoInfoMap = $productoInfoMap;

        /**
         * Iteración fila por fila del Excel.
         * En cada vuelta se valida la información y se construye un array estructurado
         * con la información que será insertada en la tabla `precios_producto_carga`.
         */
/*        $actualizados = DB::table('precios_producto_carga')
        ->where('categoria_precios_id', $this->categoriaPrecioId)
        ->where('estado_id', 1)
        ->update(['estado_id' => 2]); */




        foreach ($rows as $row) {
            $this->rowsRead++; // Incrementa el contador de filas leídas

            // Se obtienen y validan los IDs principales requeridos.
            $productoId   = $this->asInt($row['producto_id'] ?? null);
            $catPrecioId  = $this->asInt($row['categoria_precios_id'] ?? $this->categoriaPrecioId);

            // Si faltan datos críticos, la fila se omite.
            if (!$productoId || !$catPrecioId) {
                $this->skip("Falta producto_id o categoria_precios_id", $row->toArray());
                continue;
            }

            /**
             * Se determina el ID de unidad de medida a usar:
             * 1. Se toma del Excel si viene informado.
             * 2. Si no, se obtiene del mapeo del producto.
             * 3. Si ambos faltan, se usa el valor por defecto recibido en el constructor.
             */
            $unidadFromExcel = $this->asInt($row['unidad_medida_compra_id'] ?? null);
            if ($unidadFromExcel === 0) $unidadFromExcel = null; // Evita valores inválidos (0)

            $unidadId = $unidadFromExcel
                ?? ($unidadMap[$productoId] ?? null)
                ?? $this->defaultUnidadMedidaId;

            // Si no se obtiene un ID de unidad válido, se omite la fila.
            if (!$this->isValidUnidad($unidadId)) {
                $this->skip("Sin unidad_medida_compra_id válido para producto_id={$productoId}", $row->toArray());
                continue;
            }

            /**
             * Se determina el tipo de categoría de precio (manual o escalable).
             * Si no se especifica en el archivo, se infiere desde el parámetro global.
             */
            $tipoCategoriaId = $this->asInt($row['idtipocategoria'] ?? null);
            if (!$tipoCategoriaId) {
                $tipoCategoriaId = $this->tipoCategoria === 'manual' ? 2 : 1;

            }

            $categoriaProductoId = $this->asInt($row['categoria_producto_id'] ?? null);
            $subcategoriaProductoId = $this->asInt($row['sub_categoria_id'] ?? null);
            $marca_idProductoId = $this->asInt($row['marca_id'] ?? null);

            // Validar filtros según el tipo seleccionado
            if ($this->tipoFiltro == 1) { // Filtro por Marca
                if (!$marca_idProductoId || $marca_idProductoId != $this->valorFiltro) {
                    $this->skip("El producto no pertenece a la marca seleccionada (Esperado: {$this->valorFiltro}, Encontrado: {$marca_idProductoId})", $row->toArray());
                    continue;
                }
            } elseif ($this->tipoFiltro == 2) { // Filtro por Categoría
                if (!$categoriaProductoId || $categoriaProductoId != $this->valorFiltro) {
                    $this->skip("El producto no pertenece a la categoría seleccionada (Esperado: {$this->valorFiltro}, Encontrado: {$categoriaProductoId})", $row->toArray());
                    continue;
                }
            }

            if (!$categoriaProductoId) {
                $this->skip("Falta categoria_producto_id para producto {$productoId}", $row->toArray());
                continue;
            }

            if (!$subcategoriaProductoId) {
                $this->skip("Falta sub_categoria_id para producto {$productoId}", $row->toArray());
                continue;
            }

            if (!$marca_idProductoId) {
                $this->skip("Falta marca_id para producto {$productoId}", $row->toArray());
                continue;
            }

            if ((int)$tipoCategoriaId === 1) {
                // 1) Traer porcentajes correctamente (un solo registro)
                $cat = DB::table('categoria_precios')
                    ->select('porc_precio_a','porc_precio_b','porc_precio_c','porc_precio_d')
                    ->where('id', $catPrecioId)
                    ->first();

                if (!$cat) {
                    $this->skip("Categoría de precios no existe: {$catPrecioId}", $row->toArray());
                    continue;
                }

                // 2) Tomar base desde el Excel (asegura encabezado correcto)
                $precioBase = $this->asFloat($row['precio_base_venta']);

                // Validar que tenga precio base
                if ($precioBase === null || $precioBase <= 0) {
                    $this->skip("Producto sin precio_base_venta válido", $row->toArray());
                    continue;
                }

                // 3) Calcular precios
                $precioA = $precioBase + (($cat->porc_precio_a/100)*$precioBase);
                $precioB = $precioBase + (($cat->porc_precio_b/100)*$precioBase);
                $precioC = $precioBase + (($cat->porc_precio_c/100)*$precioBase);
                $precioD = $precioBase + (($cat->porc_precio_d/100)*$precioBase);

                // 4) Usar los calculados en el batch (no los del Excel)
                $batch[] = [
                    'categoria_precios_id'   => $catPrecioId,
                    'comentario'             => $this->asStr($row['comentario'] ?? null),
                    'producto_id'            => $productoId,
                    'estado_id'              => 1,
                    'precio_a'               => $precioA,
                    'precio_b'               => $precioB,
                    'precio_c'               => $precioC,
                    'precio_d'               => $precioD,
                    'precio_base_venta'      => $precioBase,
                    'categoria_producto_id'    => $categoriaProductoId,
                    'sub_categoria_id'    => $subcategoriaProductoId,
                    'marca_id'    => $marca_idProductoId,
                    'tipo_categoria_precio_id'=> $tipoCategoriaId,
                    'users_id_creador'       => $this->userId,
                    'precio_compra_usd'      => $this->asFloat($row['precio_compra_usd'] ?? null),
                    'tipo_cambio_usd'        => $this->asFloat($row['tipo_cambio_usd'] ?? null),
                    'flete'                  => $this->asFloat($row['flete'] ?? null),
                    'arancel'                => $this->asFloat($row['arancel'] ?? null),
                    'porc_flete'                => $this->asFloat($row['porc_flete'] ?? null),
                    'porc_arancel'                => $this->asFloat($row['porc_arancel'] ?? null),
                    'costoproducto'                => $this->asFloat($row['costoproducto'] ?? null),
                    'unidad_medida_compra_id'   => $this->asFloat($row['unidad_medida_compra_id'] ?? null),
                    'precio_hnl'   => $this->asFloat($row['precio_hnl'] ?? null),
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ];
            }else{
                // Modo manual - validar que tenga precio base
                $precioBase = $this->asFloat($row['precio_base_venta']);

                if ($precioBase === null || $precioBase <= 0) {
                    $this->skip("Producto sin precio_base_venta válido (modo manual)", $row->toArray());
                    continue;
                }

                /**
                 * Se prepara la estructura de datos lista para insertar en la base.
                 * Los campos faltantes o vacíos se manejan de forma segura con valores por defecto o nulos.
                 */
                $batch[] = [
                    'categoria_precios_id'   => $catPrecioId,
                    'comentario'             => $this->asStr($row['comentario'] ?? null),
                    'producto_id'            => $productoId,
                    'estado_id'              => 1,
                    'precio_a'                 => $this->numOrZero($row['precio_a'] ?? null),
                    'precio_b'                 => $this->numOrZero($row['precio_b'] ?? null),
                    'precio_c'                 => $this->numOrZero($row['precio_c'] ?? null),
                    'precio_d'                 => $this->numOrZero($row['precio_d'] ?? null),
                    'precio_base_venta'      => $precioBase,
                    'categoria_producto_id'    => $categoriaProductoId,
                    'sub_categoria_id'    => $subcategoriaProductoId,
                    'marca_id'    => $marca_idProductoId,
                    'tipo_categoria_precio_id'=> $tipoCategoriaId,
                    'users_id_creador'       => $this->userId,
                    'precio_compra_usd'      => $this->asFloat($row['precio_compra_usd'] ?? null),
                    'tipo_cambio_usd'        => $this->asFloat($row['tipo_cambio_usd'] ?? null),
                    'flete'                  => $this->asFloat($row['flete'] ?? null),
                    'arancel'                => $this->asFloat($row['arancel'] ?? null),
                    'porc_flete'                => $this->asFloat($row['porc_flete'] ?? null),
                    'porc_arancel'                => $this->asFloat($row['porc_arancel'] ?? null),
                    'costoproducto'                => $this->asFloat($row['costoproducto'] ?? null),
                    'unidad_medida_compra_id'   => $this->asFloat($row['unidad_medida_compra_id'] ?? null),
                    'precio_hnl'   => $this->asFloat($row['precio_hnl'] ?? null),
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ];
            }
            
            // Se agrupan los productos por categoría para poder inactivar los registros anteriores antes de insertar los nuevos.
            // Esto se hace SIEMPRE, independientemente del modo (escalable o manual)
            $groupForInactivate[$catPrecioId][] = $productoId;
        }

        // Si no hay registros válidos en el bloque, termina aquí.
        if (empty($batch)) return;

        /**
         * MODO PREVIEW: Solo registrar productos para mostrar, NO insertar en BD
         */
        if ($this->previewMode) {
            // Obtener información detallada de los productos para preview
            $productoIds = array_column($batch, 'producto_id');
            $productos = DB::table('producto')
                ->whereIn('id', $productoIds)
                ->get()
                ->keyBy('id');

            // Guardar detalles de productos a procesar para mostrar en frontend
            foreach ($batch as $item) {
                $producto = $productos[$item['producto_id']] ?? null;
                $this->productosParaProcesar[] = [
                    'producto_id' => $item['producto_id'],
                    'codigo' => $producto->id ?? $item['producto_id'],
                    'descripcion' => $producto->nombre ?? 'Producto #' . $item['producto_id'],
                    'precio_base' => number_format($item['precio_base_venta'], 2),
                    'precio_a' => number_format($item['precio_a'], 2),
                    'precio_b' => number_format($item['precio_b'], 2),
                    'precio_c' => number_format($item['precio_c'], 2),
                    'precio_d' => number_format($item['precio_d'], 2),
                ];
            }

            $this->rowsToProcess += count($batch);
            return; // NO ejecutar la transacción en modo preview
        }

        /**
         * MODO FINAL: Se ejecuta la inserción dentro de una transacción:
         * 1. Se inactivan los registros antiguos del mismo producto y categoría.
         * 2. Se insertan los nuevos registros.
         * 3. Se actualizan los contadores globales de estadísticas.
         */
        try {
            DB::transaction(function () use ($groupForInactivate, $batch, $now) {
                $totalInactivated = 0;

                // Paso 1: inactivar registros antiguos
                foreach ($groupForInactivate as $catId => $prodIds) {
                    $affected = DB::table('precios_producto_carga')
                        ->where('categoria_precios_id', $catId)
                        ->whereIn('producto_id', array_unique($prodIds))
                        ->update([
                            'estado_id'   => 2,
                            'updated_at'  => $now,
                        ]);
                    $totalInactivated += (int)$affected;
                }

                // Paso 2: insertar los nuevos registros en bloque
                DB::table('precios_producto_carga')->insert($batch);

                // Paso 3: actualizar las métricas del proceso
                $this->rowsInactivated += $totalInactivated;
                $this->rowsInserted += count($batch);

                // Paso 4: Obtener información detallada de los productos insertados
                $productoIds = array_column($batch, 'producto_id');
                $productos = DB::table('producto')
                    ->whereIn('id', $productoIds)
                    ->get()
                    ->keyBy('id');

                // Guardar detalles de productos insertados para mostrar en frontend
                foreach ($batch as $item) {
                    $producto = $productos[$item['producto_id']] ?? null;
                    $this->productosInsertados[] = [
                        'producto_id' => $item['producto_id'],
                        'codigo' => $producto->id ?? $item['producto_id'],
                        'descripcion' => $producto->nombre ?? 'Producto #' . $item['producto_id'],
                        'precio_base' => number_format($item['precio_base_venta'], 2),
                        'precio_a' => number_format($item['precio_a'], 2),
                        'precio_b' => number_format($item['precio_b'], 2),
                        'precio_c' => number_format($item['precio_c'], 2),
                        'precio_d' => number_format($item['precio_d'], 2),
                    ];
                }
            });
        } catch (QueryException $e) {
            // En caso de error SQL, se registra el detalle y se relanza la excepción
            Log::error('[Import precios] Error SQL', ['msg' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Retorna estadísticas del proceso: filas leídas, insertadas, omitidas, etc.
     */
    public function getStats(): array
    {
        return [
            'rows_read'        => $this->rowsRead,
            'rows_inserted'    => $this->rowsInserted,
            'rows_inactivated' => $this->rowsInactivated,
            'rows_skipped'     => $this->rowsSkipped,
            'rows_to_process'  => $this->rowsToProcess,  // Para modo preview
            'missing_headers'  => $this->missingHeaders,
            'skipped_reasons'  => $this->skippedReasons,
            'productos_insertados' => $this->productosInsertados,
            'productos_inactivados' => $this->productosInactivados,
            'productos_para_procesar' => $this->productosParaProcesar,  // Preview
        ];
    }

    /**
     * Marca una fila como omitida, registrando el motivo con detalles.
     */
    protected function skip(string $reason, array $rowData = []): void
    {
        $this->rowsSkipped++;

        // Si tenemos datos de la fila, creamos un objeto con detalles
        if (!empty($rowData)) {
            $productoId = $rowData['producto_id'] ?? null;
            $codigo = 'N/A';
            $descripcion = 'N/A';

            // Usar el mapeo precargado en lugar de consultar cada vez
            if ($productoId && isset($this->productoInfoMap[$productoId])) {
                $codigo = $this->productoInfoMap[$productoId]['codigo'] ?? $productoId;
                $descripcion = $this->productoInfoMap[$productoId]['nombre'] ?? 'Producto #' . $productoId;
            } elseif ($productoId) {
                // Si no está en el mapeo, intentar consulta directa (fallback)
                try {
                    $producto = DB::table(self::PRODUCTOS_TABLE)
                        ->select('id', 'nombre')
                        ->where('id', $productoId)
                        ->first();

                    if ($producto) {
                        $codigo = $producto->id;
                        $descripcion = $producto->nombre ?? 'Producto #' . $productoId;
                    } else {
                        $codigo = $productoId;
                        $descripcion = 'Producto #' . $productoId;
                    }
                } catch (\Exception $e) {
                    $codigo = $productoId;
                    $descripcion = 'Producto #' . $productoId;
                }
            }

            $this->skippedReasons[] = [
                'fila' => $this->rowsRead + 1,  // +1 para no contar el encabezado
                'codigo' => $codigo,
                'descripcion' => $descripcion,  // Mismo campo que productos actualizables
                'producto_id' => $productoId,
                'motivo' => $reason
            ];
        } else {
            $this->skippedReasons[] = $reason;
        }
    }

    /**
     * Valida que el ID de unidad de medida sea numéricamente válido (>0).
     * (La validación de existencia en BD se deja opcional por eficiencia.)
     */
    protected function isValidUnidad($v): bool
    {
        if ($v === null) return false;
        $id = (int)$v;
        if ($id <= 0) return false;
        return true;
    }

    /**
     * Convierte valores a entero seguro, permitiendo null.
     */
    protected function asInt($v): ?int
    {
        if ($v === null || $v === '') return null;
        return (int)$v;
    }

    /**
     * Convierte valores numéricos (con o sin comas) a float.
     */
    protected function asFloat($v): ?float
    {
        if ($v === null || $v === '') return null;
        if (is_string($v)) $v = str_replace(',', '.', $v);
        return is_numeric($v) ? (float)$v : null;
    }

    /**
     * Limpia y normaliza valores de texto.
     */
    protected function asStr($v): ?string
    {
        if ($v === null) return null;
        $v = trim((string)$v);
        return $v === '' ? null : $v;
    }

    /**
     * Convierte valores numéricos a float y retorna 0.0 si no son válidos.
     * Se usa para columnas de precios que no pueden quedar nulas.
     */
    protected function numOrZero($v): float
    {
        $f = $this->asFloat($v);
        return $f === null ? 0.0 : $f;
    }
}
