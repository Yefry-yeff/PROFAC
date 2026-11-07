<?php

namespace App\Support\Comisiones;

use Illuminate\Support\Facades\DB;

class CategoriaComisionPorPrecio
{
    private const COLUMNAS_PRECIO = [
        'pb' => 'precio_base_venta',
        'p1' => 'precio_a',
        'p2' => 'precio_b',
        'p3' => 'precio_c',
        'p4' => 'precio_d',
    ];

    private array $candidatos;

    public function __construct(array $candidatos = [])
    {
        $this->candidatos = $candidatos;
    }

    public static function cargar(iterable $productoIds, iterable $clienteCategoriaEscalaIds): self
    {
        $productos = collect($productoIds)->map(fn($id) => (int) $id)->filter()->unique()->values()->all();
        $categoriasCliente = collect($clienteCategoriaEscalaIds)->map(fn($id) => (int) $id)->filter()->unique()->values()->all();

        if (empty($productos) || empty($categoriasCliente)) {
            return new self();
        }

        $filas = DB::table('precios_producto_carga as ppc')
            ->join('categoria_precios as cp', 'cp.id', '=', 'ppc.categoria_precios_id')
            ->whereIn('ppc.producto_id', $productos)
            ->whereIn('cp.cliente_categoria_escala_id', $categoriasCliente)
            ->where('cp.estado_id', 1)
            ->orderByRaw('CASE WHEN ppc.estado_id = 1 THEN 0 ELSE 1 END')
            ->orderByDesc('ppc.id')
            ->get([
                'ppc.id',
                'ppc.producto_id',
                'ppc.categoria_precios_id',
                'ppc.precio_base_venta',
                'ppc.precio_a',
                'ppc.precio_b',
                'ppc.precio_c',
                'ppc.precio_d',
                'cp.nombre as categoria_precio',
                'cp.cliente_categoria_escala_id',
            ]);

        return self::desdeFilas($filas);
    }

    public static function desdeFilas(iterable $filas): self
    {
        $candidatos = [];
        $categoriasVistas = [];

        foreach ($filas as $fila) {
            $productoId = (int) $fila->producto_id;
            $clienteCategoriaEscalaId = (int) $fila->cliente_categoria_escala_id;
            $categoriaPrecioId = (int) $fila->categoria_precios_id;
            $categoriaKey = $productoId . '|' . $clienteCategoriaEscalaId . '|' . $categoriaPrecioId;

            if (isset($categoriasVistas[$categoriaKey])) {
                continue;
            }

            $categoriasVistas[$categoriaKey] = true;
            $candidatos[$productoId . '|' . $clienteCategoriaEscalaId][] = [
                'categoria_precios_id' => $categoriaPrecioId,
                'categoria_precio' => (string) $fila->categoria_precio,
                'precio_base_venta' => self::decimalNullable($fila->precio_base_venta),
                'precio_a' => self::decimalNullable($fila->precio_a),
                'precio_b' => self::decimalNullable($fila->precio_b),
                'precio_c' => self::decimalNullable($fila->precio_c),
                'precio_d' => self::decimalNullable($fila->precio_d),
            ];
        }

        return new self($candidatos);
    }

    public function resolver(
        int $clienteCategoriaEscalaId,
        int $productoId,
        int $categoriaPrecioId,
        int $categoriaClientePrecioId,
        string $categoriaPrecio,
        string $precioSeleccionadoId,
        float $precioVendido
    ): array {
        $sinAjuste = [
            'categoria_precios_id' => $categoriaPrecioId,
            'categoria_precio' => $categoriaPrecio,
            'ajustada' => false,
        ];

        if ($clienteCategoriaEscalaId <= 0
            || $productoId <= 0
            || $categoriaClientePrecioId <= 0
            || $categoriaClientePrecioId === $clienteCategoriaEscalaId
            || $precioVendido <= 0) {
            return $sinAjuste;
        }

        $columna = self::COLUMNAS_PRECIO[$precioSeleccionadoId] ?? null;
        if (!$columna) {
            return $sinAjuste;
        }

        $candidatos = collect($this->candidatos[$productoId . '|' . $clienteCategoriaEscalaId] ?? [])
            ->filter(fn(array $candidato) => $candidato[$columna] !== null);

        $categoria = $candidatos
            ->filter(fn(array $candidato) => $candidato[$columna] <= $precioVendido + 0.0001)
            ->sort(function (array $a, array $b) use ($columna) {
                $porPrecio = $b[$columna] <=> $a[$columna];

                return $porPrecio !== 0
                    ? $porPrecio
                    : $a['categoria_precios_id'] <=> $b['categoria_precios_id'];
            })
            ->first();

        if (!$categoria) {
            $categoria = $candidatos
                ->sort(function (array $a, array $b) use ($columna) {
                    $porPrecio = $a[$columna] <=> $b[$columna];

                    return $porPrecio !== 0
                        ? $porPrecio
                        : $a['categoria_precios_id'] <=> $b['categoria_precios_id'];
                })
                ->first();
        }

        if (!$categoria) {
            return $sinAjuste;
        }

        return [
            'categoria_precios_id' => (int) $categoria['categoria_precios_id'],
            'categoria_precio' => (string) $categoria['categoria_precio'],
            'ajustada' => true,
        ];
    }

    public function categoriaIds(): array
    {
        return collect($this->candidatos)
            ->flatten(1)
            ->pluck('categoria_precios_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private static function decimalNullable($valor): ?float
    {
        return $valor === null ? null : (float) $valor;
    }
}
