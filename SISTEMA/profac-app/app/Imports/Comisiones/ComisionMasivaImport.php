<?php

namespace App\Imports\Comisiones;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ComisionMasivaImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected int $userId;
    protected bool $previewMode;

    // Contadores reales (cuando previewMode=false)
    public int $insertados   = 0;
    public int $actualizados = 0;
    public int $omitidos     = 0;
    public array $errores    = [];

    // Contadores de preview (cuando previewMode=true)
    public int $previewExistentes = 0;
    public int $previewNuevos     = 0;
    public int $previewOmitidos   = 0;

    public function __construct(int $userId, bool $previewMode = false)
    {
        $this->userId      = $userId;
        $this->previewMode = $previewMode;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * Encabezados esperados (normalizados por WithHeadingRow):
     *   rol_id | cliente_categoria_id | categoria_precio_id | _comision_editar_aqui_
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // fila real en Excel (1 = encabezado)

            $rolId       = isset($row['rol_id'])              ? (int) $row['rol_id']              : null;
            $catCliId    = isset($row['cliente_categoria_id']) ? (int) $row['cliente_categoria_id'] : null;
            $catPrecioId = isset($row['categoria_precio_id'])  ? (int) $row['categoria_precio_id']  : null;

            // Buscar el % en la columna G — WithHeadingRow normaliza el encabezado
            $pct = $this->resolverPorcentaje($row);

            // Omitir filas sin IDs válidos (filas en blanco o corruptas)
            if (!$rolId || !$catCliId || !$catPrecioId) {
                $this->omitidos++;
                continue;
            }

            // Si el % está vacío o es 0, omitir (no crear ni actualizar con 0%)
            if ($pct === null || $pct <= 0) {
                $this->omitidos++;
                continue;
            }

            // Validar que los IDs referenciados existen
            if (!$this->existeRol($rolId) || !$this->existeCatCli($catCliId) || !$this->existeCatPrecio($catPrecioId)) {
                $this->errores[] = "Fila {$rowNum}: ID de rol, categoría cliente o categoría precio no existe.";
                $this->omitidos++;
                $this->previewOmitidos++;
                continue;
            }

            try {
                $existente = DB::table('comision_escala')
                    ->where('rol_id', $rolId)
                    ->where('cliente_categoria_escala_id', $catCliId)
                    ->where('categoria_precios_id', $catPrecioId)
                    ->first();

                if ($this->previewMode) {
                    // Solo contar, no escribir
                    if ($existente && $existente->estado_id == 1) {
                        $this->previewExistentes++;
                    } else {
                        $this->previewNuevos++;
                    }
                    continue;
                }

                if ($existente) {
                    // Actualizar (sea activo o inactivo — lo reactivamos con el nuevo %)
                    DB::table('comision_escala')
                        ->where('id', $existente->id)
                        ->update([
                            'porcentaje_comision'      => $pct,
                            'estado_id'                => 1,
                            'fechaultimamodificacion'  => now(),
                            'usermodifico'             => $this->userId,
                            'updated_at'               => now(),
                        ]);
                    $this->actualizados++;
                } else {
                    // Obtener nombre para el campo 'nombre' (requerido)
                    $rolNombre = DB::table('rol')->where('id', $rolId)->value('nombre') ?? 'Rol '.$rolId;
                    $catCliNombre = DB::table('cliente_categoria_escala')->where('id', $catCliId)->value('nombre_categoria') ?? '';
                    $catPreNombre = DB::table('categoria_precios')->where('id', $catPrecioId)->value('nombre') ?? '';

                    DB::table('comision_escala')->insert([
                        'nombre'                     => substr("{$rolNombre} - {$catCliNombre} - {$catPreNombre}", 0, 150),
                        'descripcion'                => 'Carga masiva',
                        'rol_id'                     => $rolId,
                        'cliente_categoria_escala_id'=> $catCliId,
                        'categoria_precios_id'       => $catPrecioId,
                        'porcentaje_comision'        => $pct,
                        'estado_id'                  => 1,
                        'users_registro'             => $this->userId,
                        'created_at'                 => now(),
                        'updated_at'                 => now(),
                    ]);
                    $this->insertados++;
                }
            } catch (\Exception $e) {
                $this->errores[] = "Fila {$rowNum}: " . $e->getMessage();
                $this->omitidos++;
            }
        }
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * WithHeadingRow normaliza encabezados: espacios→'_', mayúsculas→minúsculas,
     * caracteres especiales eliminados. La columna G es "% Comisión (editar aquí)"
     * que se normaliza a algo como: "_comision_editar_aqui_"
     * Buscamos por varias claves posibles.
     */
    protected function resolverPorcentaje(Collection $row): ?float
    {
        $posibles = [
            '_comision_editar_aqui_',
            'comision_editar_aqui_',
            '_comision_editar_aqui',
            'comision_editar_aqui',
            'porcentaje_comision',
            'comision',
            '_comision',
        ];

        foreach ($posibles as $key) {
            if (isset($row[$key]) && $row[$key] !== '' && $row[$key] !== null) {
                return (float) str_replace(',', '.', $row[$key]);
            }
        }

        // Fallback: buscar cualquier clave que contenga "comision" y tenga valor
        foreach ($row->keys() as $key) {
            if (str_contains((string) $key, 'comisi') && $row[$key] !== '' && $row[$key] !== null) {
                return (float) str_replace(',', '.', $row[$key]);
            }
        }

        return null;
    }

    protected function existeRol(int $id): bool
    {
        return DB::table('rol')->where('id', $id)->exists();
    }

    protected function existeCatCli(int $id): bool
    {
        return DB::table('cliente_categoria_escala')->where('id', $id)->where('estado_id', 1)->exists();
    }

    protected function existeCatPrecio(int $id): bool
    {
        return DB::table('categoria_precios')->where('id', $id)->where('estado_id', 1)->exists();
    }
}
