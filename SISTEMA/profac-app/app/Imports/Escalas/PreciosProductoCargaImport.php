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
    // Cambiá esto si tu tabla no se llama 'productos'
    private const PRODUCTOS_TABLE = 'producto';

    protected string $tipoCategoria;
    protected int $tipoFiltro;
    protected int $valorFiltro;
    protected int $categoriaPrecioId;
    protected int $userId;
    protected ?int $defaultUnidadMedidaId;

    protected int $rowsRead = 0;
    protected int $rowsInserted = 0;
    protected int $rowsInactivated = 0;
    protected int $rowsSkipped = 0;

    protected array $missingHeaders = [];
    protected bool $headersValidated = false;

    // Para registrar por qué se omiten filas
    protected array $skippedReasons = [];

    public function __construct(string $tipoCategoria, int $tipoFiltro, int $valorFiltro, int $categoriaPrecioId, int $userId, ?int $defaultUnidadMedidaId = null)
    {
        $this->tipoCategoria         = $tipoCategoria;
        $this->tipoFiltro            = $tipoFiltro;
        $this->valorFiltro           = $valorFiltro;
        $this->categoriaPrecioId     = $categoriaPrecioId;
        $this->userId                = $userId;
        $this->defaultUnidadMedidaId = $defaultUnidadMedidaId;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) return;

        if (!$this->headersValidated) {
            $present = array_keys($rows->first()->toArray());
            // Requeridos mínimos del Excel
            $required = ['producto_id', 'categoria_precios_id', 'precio_base_venta'];
            $missing = array_diff($required, $present);
            $this->missingHeaders = array_values($missing);
            $this->headersValidated = true;

            if (!empty($this->missingHeaders)) {
                Log::warning('[Import precios] Faltan columnas requeridas', ['missing' => $this->missingHeaders]);
                return;
            }
        }

        $now = now();
        $batch = [];
        $groupForInactivate = [];

        // 1) Pre-cargar unidad_medida_compra_id desde productos para los product_id del chunk
        $productoIds = [];
        foreach ($rows as $row) {
            $pid = $this->asInt($row['producto_id'] ?? null);
            if ($pid) $productoIds[] = $pid;
        }
        $productoIds = array_values(array_unique($productoIds));

        $unidadMap = [];
        if (!empty($productoIds)) {
            $unidadMap = DB::table(self::PRODUCTOS_TABLE)
                ->whereIn('id', $productoIds)
                ->pluck('unidad_medida_compra_id', 'id')
                ->toArray();
        }

        foreach ($rows as $row) {
            $this->rowsRead++;

            $productoId   = $this->asInt($row['producto_id'] ?? null);
            $catPrecioId  = $this->asInt($row['categoria_precios_id'] ?? $this->categoriaPrecioId);

            if (!$productoId || !$catPrecioId) {
                $this->skip("Fila sin producto_id o categoria_precios_id");
                continue;
            }

            // Resolver unidad_medida_compra_id
            $unidadFromExcel = $this->asInt($row['unidad_medida_compra_id'] ?? null);
            if ($unidadFromExcel === 0) $unidadFromExcel = null; // evitar 0 inválido

            $unidadId = $unidadFromExcel
                ?? ($unidadMap[$productoId] ?? null)
                ?? $this->defaultUnidadMedidaId;

            if (!$this->isValidUnidad($unidadId)) {
                $this->skip("Sin unidad_medida_compra_id válido para producto_id={$productoId}");
                continue;
            }

            $tipoCategoriaId = $this->asInt($row['idtipocategoria'] ?? null);
            if (!$tipoCategoriaId) {
                $tipoCategoriaId = $this->tipoCategoria === 'manual' ? 2 : 1;
            }

            $batch[] = [
                'categoria_precios_id'     => $catPrecioId,
                'comentario'               => $this->asStr($row['comentario'] ?? null),
                'producto_id'              => $productoId,
                'estado_id'                => 1,
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
                'unidad_medida_compra_id'   => $unidadId,
                'costoproducto'            => $this->asFloat($row['costoproducto'] ?? null),
                'created_at'               => $now,
                'updated_at'               => $now,
            ];

            $groupForInactivate[$catPrecioId][] = $productoId;
        }

        if (empty($batch)) return;

        try {
            DB::transaction(function () use ($groupForInactivate, $batch, $now) {
                $totalInactivated = 0;

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

                DB::table('precios_producto_carga')->insert($batch);

                $this->rowsInactivated += $totalInactivated;
                $this->rowsInserted += count($batch);
            });
        } catch (QueryException $e) {
            Log::error('[Import precios] Error SQL', ['msg' => $e->getMessage()]);
            throw $e;
        }
    }

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

    protected function skip(string $reason): void
    {
        $this->rowsSkipped++;
        $this->skippedReasons[] = $reason;
    }

    protected function isValidUnidad($v): bool
    {
        if ($v === null) return false;
        $id = (int)$v;
        if ($id <= 0) return false;
        // Opcional: podríamos validar que existe, pero sería 1 query por fila.
        // Si querés validación dura, descomenta esta sección:
        // return DB::table('unidad_medida_venta')->where('id', $id)->exists();
        return true;
    }

    protected function asInt($v): ?int
    {
        if ($v === null || $v === '') return null;
        return (int)$v;
    }

    protected function asFloat($v): ?float
    {
        if ($v === null || $v === '') return null;
        if (is_string($v)) $v = str_replace(',', '.', $v);
        return is_numeric($v) ? (float)$v : null;
    }

    protected function asStr($v): ?string
    {
        if ($v === null) return null;
        $v = trim((string)$v);
        return $v === '' ? null : $v;
    }

    protected function numOrZero($v): float
    {
        $f = $this->asFloat($v);
        return $f === null ? 0.0 : $f;
    }
}
