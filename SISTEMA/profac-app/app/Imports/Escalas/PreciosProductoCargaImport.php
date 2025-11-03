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

    // Contadores y variables de control para estadísticas de proceso
    protected int $rowsRead = 0;
    protected int $rowsInserted = 0;
    protected int $rowsInactivated = 0;
    protected int $rowsSkipped = 0;

    // Variables de control de encabezados
    protected array $missingHeaders = [];
    protected bool $headersValidated = false;

    // Registro de motivos por los cuales se omiten filas
    protected array $skippedReasons = [];

    /**
     * Constructor: inicializa los parámetros requeridos para el proceso.
     * Se reciben directamente desde el controlador.
     */
    public function __construct(string $tipoCategoria, int $tipoFiltro, int $valorFiltro, int $categoriaPrecioId, int $userId, ?int $defaultUnidadMedidaId = null)
    {
        $this->tipoCategoria         = $tipoCategoria;
        $this->tipoFiltro            = $tipoFiltro;
        $this->valorFiltro           = $valorFiltro;
        $this->categoriaPrecioId     = $categoriaPrecioId;
        $this->userId                = $userId;
        $this->defaultUnidadMedidaId = $defaultUnidadMedidaId;
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
        if (!empty($productoIds)) {
            $unidadMap = DB::table(self::PRODUCTOS_TABLE)
                ->whereIn('id', $productoIds)
                ->pluck('unidad_medida_compra_id', 'id')
                ->toArray();
        }

        /**
         * Iteración fila por fila del Excel.
         * En cada vuelta se valida la información y se construye un array estructurado
         * con la información que será insertada en la tabla `precios_producto_carga`.
         */
        foreach ($rows as $row) {
            $this->rowsRead++; // Incrementa el contador de filas leídas

            // Se obtienen y validan los IDs principales requeridos.
            $productoId   = $this->asInt($row['producto_id'] ?? null);
            $catPrecioId  = $this->asInt($row['categoria_precios_id'] ?? $this->categoriaPrecioId);

            // Si faltan datos críticos, la fila se omite.
            if (!$productoId || !$catPrecioId) {
                $this->skip("Fila sin producto_id o categoria_precios_id");
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
                $this->skip("Sin unidad_medida_compra_id válido para producto_id={$productoId}");
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

            /**
             * Se prepara la estructura de datos lista para insertar en la base.
             * Los campos faltantes o vacíos se manejan de forma segura con valores por defecto o nulos.
             */
            $batch[] = [
                'categoria_precios_id'     => $catPrecioId,
                'comentario'               => $this->asStr($row['comentario'] ?? null),
                'producto_id'              => $productoId,
                'estado_id'                => 1, // siempre activo al crear
                'precio_a'                 => $this->numOrZero($row['precio_a'] ?? null),
                'precio_b'                 => $this->numOrZero($row['precio_b'] ?? null),
                'precio_c'                 => $this->numOrZero($row['precio_c'] ?? null),
                'precio_d'                 => $this->numOrZero($row['precio_d'] ?? null),
                'precio_base_venta'        => $this->asFloat($row['precio_base_venta'] ?? null),
                'tipo_categoria_precio_id' => $tipoCategoriaId,
                'users_id_creador'         => $this->userId,
                'precio_compra_usd'        => $this->asFloat($row['precio_compra_usd'] ?? null),
                'tipo_cambio_usd'          => $this->asFloat($row['tipo_cambio_usd'] ?? null),
                'precio_hnl'               => $this->asFloat($row['precio_hnl'] ?? null),
                'flete'                    => $this->asFloat($row['flete'] ?? null),
                'arancel'                  => $this->asFloat($row['arancel'] ?? null),
                'porc_flete'               => $this->asFloat($row['porc_flete'] ?? null),
                'porc_arancel'             => $this->asFloat($row['porc_arancel'] ?? null),
                'categoria_producto_id'    => $this->asInt($row['categoria_producto_id'] ?? null),
                'sub_categoria_id'         => $this->asInt($row['sub_categoria_id'] ?? null),
                'marca_id'                 => $this->asInt($row['marca_id'] ?? null),
                'unidad_medida_compra_id'  => $unidadId,
                'costoproducto'            => $this->asFloat($row['costoproducto'] ?? null),
                'created_at'               => $now,
                'updated_at'               => $now,
            ];

            // Se agrupan los productos por categoría para poder inactivar los registros anteriores antes de insertar los nuevos.
            $groupForInactivate[$catPrecioId][] = $productoId;
        }

        // Si no hay registros válidos en el bloque, termina aquí.
        if (empty($batch)) return;

        /**
         * Se ejecuta la inserción dentro de una transacción:
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
            'missing_headers'  => $this->missingHeaders,
            'skipped_reasons'  => $this->skippedReasons,
        ];
    }

    /**
     * Marca una fila como omitida, registrando el motivo.
     */
    protected function skip(string $reason): void
    {
        $this->rowsSkipped++;
        $this->skippedReasons[] = $reason;
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
