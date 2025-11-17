<?php

namespace App\Imports\Escalas;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

use App\Models\ModelCliente;
use App\Models\Escalas\clienteCategoriaEscalaLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\{
    ToCollection, WithHeadingRow, WithValidation, WithChunkReading, SkipsOnFailure, SkipsFailures
};
use Illuminate\Support\Collection;

class ClientesCategoriaMasivaImport implements ToCollection, WithHeadingRow, WithValidation, WithChunkReading, SkipsOnFailure
{
    use SkipsFailures;

    protected array $errores = [];
    protected int $actualizados = 0;
    protected int $saltados = 0;

   public function collection(Collection $rows)
{
    foreach ($rows as $rawRow) {

        // 1) Normaliza LLAVES y VALORES
        $norm = [];
        foreach ($rawRow as $k => $v) {
            $k = is_string($k) ? trim($k) : $k;
            // normaliza: minúsculas, espacios->_, quita acentos y ñ
            $k = mb_strtolower($k, 'UTF-8');
            $k = str_replace(
                [' ', '-', 'á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ'],
                ['_', '_','a','e','i','o','u','a','e','i','o','u','n','N'],
                $k
            );
            $norm[$k] = is_string($v) ? trim($v) : $v;
        }
        $row = collect($norm);

        // 2) Lee ID (obligatorio)
        $idCliente = $row->get('id');
/*         $activados = \App\Models\ModelCliente::lockForUpdate()->find((int)$idCliente);
            if (!$cliente) {
                $this->errores[] = "Cliente ID {$idCliente} no existe.";
                \DB::rollBack();
                continue;
            }
 */
        // 3) Lee nueva categoría desde varios alias
        $nuevaCat = $row->get('cliente_categoria_escala_id');
        if ($nuevaCat === null || $nuevaCat === '') $nuevaCat = $row->get('nueva_categoria_id');
        if ($nuevaCat === null || $nuevaCat === '') $nuevaCat = $row->get('nueva_categoria');

        // 4) Validaciones mínimas
        if ($idCliente === null || $idCliente === '' || $nuevaCat === null || $nuevaCat === '') {
            $this->saltados++;
            continue;
        }
        if (!is_numeric((string)$idCliente) || !is_numeric((string)$nuevaCat)) {
            $this->errores[] = "Fila ID '{$idCliente}': valores no numéricos.";
            continue;
        }

        \DB::beginTransaction();
        try {
            $cliente = \App\Models\ModelCliente::lockForUpdate()->find((int)$idCliente);
            if (!$cliente) {
                $this->errores[] = "Cliente ID {$idCliente} no existe.";
                \DB::rollBack();
                continue;
            }

            $old = (int)($cliente->cliente_categoria_escala_id ?? 0);
            $new = (int)$nuevaCat;

            if ($old === $new) {
                $this->saltados++;
                \DB::commit();
                continue;
            }

            // 5) Actualiza cliente
            $cliente->cliente_categoria_escala_id = $new;
            $cliente->save();

            // 6) Log
            DB::table('cliente_categoria_escala_logs')->insert([
                'cliente_id'        => $cliente->id,
                'antigua_categoria' => $old ?: null,
                'nueva_categoria'   => $new,
                'comentario'        => 'Actualización masiva por Excel',
                'users_id'          => Auth::id() ?? 1,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            \DB::commit();
            $this->actualizados++;
        } catch (\Throwable $e) {
            \DB::rollBack();
            $this->errores[] = "Cliente ID {$idCliente}: {$e->getMessage()}";
        }
    }
}



    public function rules(): array
    {
        return [
            '*.id' => ['required'],
            // Permitimos vacío y lo tratamos como "no actualizar"
            // '*.cliente_categoria_escala_id' => ['nullable','integer','min:1'],
        ];
    }

    public function chunkSize(): int { return 1000; }

    public function resumen(): array
    {
        return [
            'actualizados' => $this->actualizados,
            'saltados'     => $this->saltados,
            'errores'      => $this->errores,
        ];
    }
}

// Helper mínimo para chequear columnas sin usar DB::getDoctrine… (compatible cPanel)
if (!function_exists('Schema')) {
    function Schema() { return new class {
        public function hasColumn($table, $column){
            try {
                $cols = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM {$table}");
                foreach ($cols as $c) if ($c->Field === $column) return true;
            } catch (\Throwable $e) {}
            return false;
        }
    }; }
}
