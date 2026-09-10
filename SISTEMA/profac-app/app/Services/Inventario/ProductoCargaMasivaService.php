<?php

namespace App\Services\Inventario;

use App\Models\ModelProducto;
use App\Models\ModelUnidadMedidaVenta;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class ProductoCargaMasivaService
{
    public const MAX_FILAS = 1000;

    public const CAMPOS_EDITABLES = [
        'codigo_barra', 'codigo_estatal', 'nombre', 'descripcion', 'isv',
        'marca_id', 'categoria_id', 'subcategoria_id', 'unidad_compra_id',
        'cantidad_compra', 'unidad_venta_id', 'cantidad_venta', 'precio_base',
        'costo_promedio', 'ultimo_costo_compra', 'tiempo_recuperacion_meses',
        'origen', 'seleccionado',
    ];

    public const HEADERS = [
        'codigo_barra',
        'codigo_estatal',
        'nombre',
        'descripcion',
        'isv',
        'marca',
        'categoria',
        'subcategoria',
        'unidad_compra',
        'cantidad_compra',
        'unidad_venta',
        'cantidad_venta',
        'precio_base',
        'costo_promedio',
        'ultimo_costo_compra',
        'tiempo_recuperacion_meses',
        'origen',
    ];

    public function catalogos(): array
    {
        return [
            'marcas' => DB::table('marca')->orderBy('nombre')->get(['id', 'nombre'])->all(),
            'categorias' => DB::table('categoria_producto')->orderBy('descripcion')->get(['id', 'descripcion'])->all(),
            'subcategorias' => DB::table('sub_categoria')->orderBy('descripcion')->get(['id', 'descripcion', 'categoria_producto_id'])->all(),
            'unidades' => DB::table('unidad_medida')->orderBy('nombre')->get(['id', 'nombre', 'simbolo'])->all(),
        ];
    }

    public function interpretar(UploadedFile $archivo): array
    {
        $libro = IOFactory::load($archivo->getRealPath());
        $hoja = $libro->getSheetByName('Productos') ?: $libro->getActiveSheet();
        $matriz = $hoja->toArray(null, true, true, false);

        if (count($matriz) < 2) {
            throw new RuntimeException('El archivo no contiene productos para previsualizar.');
        }

        $encabezados = array_map(fn ($valor) => $this->normalizarEncabezado((string) $valor), $matriz[0]);
        $faltantes = array_values(array_diff(self::HEADERS, $encabezados));
        if ($faltantes) {
            throw new RuntimeException('Faltan columnas obligatorias: ' . implode(', ', $faltantes) . '.');
        }

        $indices = array_flip($encabezados);
        $catalogos = $this->catalogos();
        $mapas = $this->mapasCatalogos($catalogos);
        $filas = [];

        foreach (array_slice($matriz, 1, null, true) as $indice => $valores) {
            if ($this->filaVacia($valores)) {
                continue;
            }
            if (count($filas) >= self::MAX_FILAS) {
                throw new RuntimeException('El archivo supera el máximo de ' . self::MAX_FILAS . ' productos por carga.');
            }

            $fila = [
                'uid' => (string) Str::uuid(),
                'fila_excel' => $indice + 1,
                'codigo_barra' => $this->texto($valores[$indices['codigo_barra']] ?? ''),
                'codigo_estatal' => $this->texto($valores[$indices['codigo_estatal']] ?? ''),
                'nombre' => $this->texto($valores[$indices['nombre']] ?? ''),
                'descripcion' => $this->texto($valores[$indices['descripcion']] ?? ''),
                'isv' => $this->normalizarIsv($valores[$indices['isv']] ?? null),
                'cantidad_compra' => $this->numero($valores[$indices['cantidad_compra']] ?? null),
                'cantidad_venta' => $this->numero($valores[$indices['cantidad_venta']] ?? null),
                'precio_base' => $this->numero($valores[$indices['precio_base']] ?? null),
                'costo_promedio' => $this->numero($valores[$indices['costo_promedio']] ?? null),
                'ultimo_costo_compra' => $this->numero($valores[$indices['ultimo_costo_compra']] ?? null),
                'tiempo_recuperacion_meses' => $this->numero($valores[$indices['tiempo_recuperacion_meses']] ?? null),
                'origen' => $this->texto($valores[$indices['origen']] ?? ''),
            ];

            $this->resolverCatalogo($fila, 'marca', $valores[$indices['marca']] ?? '', $mapas['marcas']);
            $this->resolverCatalogo($fila, 'categoria', $valores[$indices['categoria']] ?? '', $mapas['categorias']);
            $this->resolverSubcategoria($fila, $valores[$indices['subcategoria']] ?? '', $mapas['subcategorias']);
            $this->resolverCatalogo($fila, 'unidad_compra', $valores[$indices['unidad_compra']] ?? '', $mapas['unidades']);
            $this->resolverCatalogo($fila, 'unidad_venta', $valores[$indices['unidad_venta']] ?? '', $mapas['unidades']);
            $fila['seleccionado'] = true;
            $filas[] = $fila;
        }

        if (!$filas) {
            throw new RuntimeException('El archivo no contiene filas con información.');
        }

        return $this->validarFilas($filas);
    }

    public function validarFilas(array $filas): array
    {
        $catalogos = $this->catalogos();
        $ids = [
            'marcas' => collect($catalogos['marcas'])->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'categorias' => collect($catalogos['categorias'])->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'subcategorias' => collect($catalogos['subcategorias'])->keyBy(fn ($item) => (int) $item->id),
            'unidades' => collect($catalogos['unidades'])->pluck('id')->map(fn ($id) => (int) $id)->all(),
        ];

        $codigos = collect($filas)
            ->map(fn ($fila) => trim((string) ($fila['codigo_barra'] ?? '')))
            ->filter()
            ->countBy();

        return array_map(function (array $fila) use ($ids, $codigos) {
            $fila = $this->normalizarFila($fila);
            [$errores, $advertencias] = $this->validarFila($fila, $ids, $codigos);
            $fila['errores'] = $errores;
            $fila['advertencias'] = $advertencias;
            $fila['estado'] = $errores ? 'error' : ($advertencias ? 'advertencia' : 'listo');
            $fila['seleccionado'] = !$errores && filter_var($fila['seleccionado'] ?? true, FILTER_VALIDATE_BOOLEAN);

            return $fila;
        }, array_values($filas));
    }

    public function crearProducto(array $fila, int $usuarioId, int $importacionId, ?callable $despuesCrear = null): ModelProducto
    {
        $bloqueo = null;
        if ($fila['codigo_barra'] !== '') {
            $bloqueo = 'producto:' . substr(hash('sha256', $fila['codigo_barra']), 0, 55);
            $adquirido = DB::selectOne('SELECT GET_LOCK(?, 10) AS adquirido', [$bloqueo]);
            if ((int) ($adquirido->adquirido ?? 0) !== 1) {
                throw new RuntimeException('No se pudo reservar el código de barras para validar su creación.');
            }
        }

        try {
            return DB::transaction(function () use ($fila, $usuarioId, $importacionId, $despuesCrear) {
                if ($fila['codigo_barra'] !== '' && DB::table('producto')->where('codigo_barra', $fila['codigo_barra'])->exists()) {
                    throw new RuntimeException('El código de barras fue registrado por otro usuario durante la revisión.');
                }

                $producto = new ModelProducto();
            $producto->nombre = $fila['nombre'];
            $producto->descripcion = $fila['descripcion'];
            $producto->isv = $fila['isv'];
            $producto->codigo_barra = $fila['codigo_barra'] ?: null;
            $producto->codigo_estatal = $fila['codigo_estatal'] ?: null;
            $producto->precio_base = $fila['precio_base'];
            $producto->costo_promedio = $fila['costo_promedio'];
            $producto->ultimo_costo_compra = $fila['ultimo_costo_compra'];
            $producto->marca_id = $fila['marca_id'];
            $producto->sub_categoria_id = $fila['subcategoria_id'];
            $producto->unidad_medida_compra_id = $fila['unidad_compra_id'];
            $producto->unidadad_compra = $fila['cantidad_compra'];
            $producto->tiempo_recuperacion_meses = $fila['tiempo_recuperacion_meses'] ?: null;
            $producto->origen = $fila['origen'] ?: null;
            $producto->users_id = $usuarioId;
            $producto->estado_producto_id = 1;
            $producto->precio1 = round($fila['precio_base'] * 1.03, 2);
            $producto->precio2 = round($fila['precio_base'] * 1.06, 2);
            $producto->precio3 = round($fila['precio_base'] * 1.10, 2);
            $producto->precio4 = round($fila['precio_base'] * 1.30, 2);
            $producto->creado_masivamente = 1;
            $producto->producto_importacion_id = $importacionId;
            $producto->save();

            $unidadVenta = new ModelUnidadMedidaVenta();
            $unidadVenta->unidad_venta = $fila['cantidad_venta'];
            $unidadVenta->unidad_medida_id = $fila['unidad_venta_id'];
            $unidadVenta->producto_id = $producto->id;
            $unidadVenta->estado_id = 1;
            $unidadVenta->unidad_venta_defecto = 1;
            $unidadVenta->save();

            if ((int) $fila['unidad_compra_id'] !== (int) $fila['unidad_venta_id']) {
                $unidadCompra = new ModelUnidadMedidaVenta();
                $unidadCompra->unidad_venta = $fila['cantidad_compra'];
                $unidadCompra->unidad_medida_id = $fila['unidad_compra_id'];
                $unidadCompra->producto_id = $producto->id;
                $unidadCompra->estado_id = 1;
                $unidadCompra->unidad_venta_defecto = 0;
                $unidadCompra->save();
            }

                if ($despuesCrear) {
                    $despuesCrear($producto);
                }

                return $producto;
            });
        } finally {
            if ($bloqueo) {
                DB::selectOne('SELECT RELEASE_LOCK(?) AS liberado', [$bloqueo]);
            }
        }
    }

    private function validarFila(array $fila, array $ids, $codigos): array
    {
        $errores = [];
        $advertencias = [];

        if ($fila['nombre'] === '') {
            $errores[] = 'El nombre es obligatorio.';
        } elseif (mb_strlen($fila['nombre']) > 1000) {
            $errores[] = 'El nombre no puede exceder 1000 caracteres.';
        }
        if ($fila['descripcion'] === '') {
            $errores[] = 'La descripción es obligatoria.';
        } elseif (mb_strlen($fila['descripcion']) > 2000) {
            $errores[] = 'La descripción no puede exceder 2000 caracteres.';
        }
        if (!is_numeric($fila['isv']) || !in_array((float) $fila['isv'], [0.0, 15.0, 18.0], true)) {
            $errores[] = 'El ISV debe ser 0, 15 o 18.';
        }
        if (!in_array((int) $fila['marca_id'], $ids['marcas'], true)) {
            $errores[] = $this->catalogoInvalido('marca', $fila);
        }
        if (!in_array((int) $fila['categoria_id'], $ids['categorias'], true)) {
            $errores[] = $this->catalogoInvalido('categoría', $fila);
        }

        $subcategoria = $ids['subcategorias']->get((int) $fila['subcategoria_id']);
        if (!$subcategoria || (int) $subcategoria->categoria_producto_id !== (int) $fila['categoria_id']) {
            $errores[] = $this->catalogoInvalido('subcategoría', $fila);
        }
        if (!in_array((int) $fila['unidad_compra_id'], $ids['unidades'], true)) {
            $errores[] = $this->catalogoInvalido('unidad de compra', $fila);
        }
        if (!in_array((int) $fila['unidad_venta_id'], $ids['unidades'], true)) {
            $errores[] = $this->catalogoInvalido('unidad de venta', $fila);
        }

        foreach (['precio_base' => 'Precio base', 'costo_promedio' => 'Costo promedio', 'ultimo_costo_compra' => 'Último costo de compra'] as $campo => $etiqueta) {
            if (!is_numeric($fila[$campo]) || (float) $fila[$campo] < 0) {
                $errores[] = "$etiqueta debe ser un número mayor o igual a cero.";
            }
        }
        foreach (['cantidad_compra' => 'Cantidad de compra', 'cantidad_venta' => 'Cantidad de venta'] as $campo => $etiqueta) {
            if (!is_numeric($fila[$campo]) || (float) $fila[$campo] <= 0) {
                $errores[] = "$etiqueta debe ser mayor que cero.";
            }
        }

        if ($fila['tiempo_recuperacion_meses'] !== null && $fila['tiempo_recuperacion_meses'] !== '') {
            if (!is_numeric($fila['tiempo_recuperacion_meses']) || floor((float) $fila['tiempo_recuperacion_meses']) !== (float) $fila['tiempo_recuperacion_meses'] ||
                (int) $fila['tiempo_recuperacion_meses'] < 1 || (int) $fila['tiempo_recuperacion_meses'] > 65535) {
                $errores[] = 'El tiempo de recuperación debe ser un entero entre 1 y 65535 meses.';
            }
        }
        if (mb_strlen($fila['codigo_barra']) > 100) {
            $errores[] = 'El código de barras no puede exceder 100 caracteres.';
        }
        if (mb_strlen($fila['codigo_estatal']) > 45) {
            $errores[] = 'El código estatal no puede exceder 45 caracteres.';
        }
        if (mb_strlen($fila['origen']) > 200) {
            $errores[] = 'El origen no puede exceder 200 caracteres.';
        }

        if ($fila['codigo_barra'] === '') {
            $advertencias[] = 'El producto se creará sin código de barras.';
        } else {
            $existente = DB::table('producto')->where('codigo_barra', $fila['codigo_barra'])->first(['id', 'nombre']);
            if ($existente) {
                $errores[] = "Código de barras ya registrado en el producto #{$existente->id} {$existente->nombre}.";
            }
            if (($codigos[$fila['codigo_barra']] ?? 0) > 1) {
                $errores[] = 'El código de barras está repetido dentro del archivo.';
            }
        }

        return [array_values(array_unique($errores)), array_values(array_unique($advertencias))];
    }

    private function normalizarFila(array $fila): array
    {
        return [
            'uid' => (string) ($fila['uid'] ?? Str::uuid()),
            'fila_excel' => (int) ($fila['fila_excel'] ?? 0),
            'codigo_barra' => $this->texto($fila['codigo_barra'] ?? ''),
            'codigo_estatal' => $this->texto($fila['codigo_estatal'] ?? ''),
            'nombre' => $this->texto($fila['nombre'] ?? ''),
            'descripcion' => $this->texto($fila['descripcion'] ?? ''),
            'isv' => $this->normalizarIsv($fila['isv'] ?? null),
            'marca_id' => (int) ($fila['marca_id'] ?? 0),
            'marca_original' => $this->texto($fila['marca_original'] ?? ''),
            'categoria_id' => (int) ($fila['categoria_id'] ?? 0),
            'categoria_original' => $this->texto($fila['categoria_original'] ?? ''),
            'subcategoria_id' => (int) ($fila['subcategoria_id'] ?? 0),
            'subcategoria_original' => $this->texto($fila['subcategoria_original'] ?? ''),
            'unidad_compra_id' => (int) ($fila['unidad_compra_id'] ?? 0),
            'unidad_compra_original' => $this->texto($fila['unidad_compra_original'] ?? ''),
            'cantidad_compra' => $this->numero($fila['cantidad_compra'] ?? null),
            'unidad_venta_id' => (int) ($fila['unidad_venta_id'] ?? 0),
            'unidad_venta_original' => $this->texto($fila['unidad_venta_original'] ?? ''),
            'cantidad_venta' => $this->numero($fila['cantidad_venta'] ?? null),
            'precio_base' => $this->numero($fila['precio_base'] ?? null),
            'costo_promedio' => $this->numero($fila['costo_promedio'] ?? null),
            'ultimo_costo_compra' => $this->numero($fila['ultimo_costo_compra'] ?? null),
            'tiempo_recuperacion_meses' => $this->numero($fila['tiempo_recuperacion_meses'] ?? null),
            'origen' => $this->texto($fila['origen'] ?? ''),
            'seleccionado' => $fila['seleccionado'] ?? true,
        ];
    }

    private function mapasCatalogos(array $catalogos): array
    {
        $subcategorias = [];
        foreach ($catalogos['subcategorias'] as $subcategoria) {
            $subcategorias[(int) $subcategoria->categoria_producto_id . '|' . $this->claveCatalogo($subcategoria->descripcion)] = (int) $subcategoria->id;
        }

        return [
            'marcas' => $this->mapa($catalogos['marcas'], 'nombre'),
            'categorias' => $this->mapa($catalogos['categorias'], 'descripcion'),
            'subcategorias' => $subcategorias,
            'unidades' => $this->mapa($catalogos['unidades'], 'nombre'),
        ];
    }

    private function mapa(array $items, string $campo): array
    {
        $mapa = [];
        foreach ($items as $item) {
            $mapa[$this->claveCatalogo($item->{$campo})] = (int) $item->id;
        }

        return $mapa;
    }

    private function resolverCatalogo(array &$fila, string $campo, $valor, array $mapa): void
    {
        $texto = $this->texto($valor);
        $fila[$campo . '_id'] = $mapa[$this->claveCatalogo($texto)] ?? 0;
        $fila[$campo . '_original'] = $texto;
    }

    private function resolverSubcategoria(array &$fila, $valor, array $mapa): void
    {
        $texto = $this->texto($valor);
        $clave = (int) ($fila['categoria_id'] ?? 0) . '|' . $this->claveCatalogo($texto);
        $fila['subcategoria_id'] = $mapa[$clave] ?? 0;
        $fila['subcategoria_original'] = $texto;
    }

    private function catalogoInvalido(string $etiqueta, array $fila): string
    {
        $campo = str_replace(['categoría', 'subcategoría', ' de compra', ' de venta'], ['categoria', 'subcategoria', '_compra', '_venta'], $etiqueta);
        $original = $fila[$campo . '_original'] ?? '';

        return $original !== '' ? ucfirst($etiqueta) . " '$original' no existe o no es válida." : 'Debe seleccionar una ' . $etiqueta . ' válida.';
    }

    private function normalizarEncabezado(string $encabezado): string
    {
        $normalizado = Str::of(Str::ascii(trim($encabezado)))->lower()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->toString();
        $alias = [
            'codigo' => 'codigo_estatal',
            'codigo_de_barras' => 'codigo_barra',
            'sub_categoria' => 'subcategoria',
            'unidad_para_compra' => 'unidad_compra',
            'unidades_compra' => 'cantidad_compra',
            'unidad_para_venta' => 'unidad_venta',
            'unidades_venta' => 'cantidad_venta',
        ];

        return $alias[$normalizado] ?? $normalizado;
    }

    private function filaVacia(array $fila): bool
    {
        return collect($fila)->every(fn ($valor) => trim((string) $valor) === '');
    }

    private function claveCatalogo($valor): string
    {
        return mb_strtolower(trim(Str::ascii((string) $valor)));
    }

    private function texto($valor): string
    {
        return trim((string) ($valor ?? ''));
    }

    private function numero($valor)
    {
        if ($valor === null || trim((string) $valor) === '') {
            return null;
        }

        $normalizado = str_replace(',', '', trim((string) $valor));

        return is_numeric($normalizado) ? (float) $normalizado : $valor;
    }

    private function normalizarIsv($valor)
    {
        return $valor === null || trim((string) $valor) === '' ? 0.0 : $this->numero($valor);
    }
}