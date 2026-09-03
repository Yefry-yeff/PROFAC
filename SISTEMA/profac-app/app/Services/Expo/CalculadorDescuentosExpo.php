<?php

namespace App\Services\Expo;

class CalculadorDescuentosExpo
{
    private const TOLERANCIA = 0.005;

    /**
     * @param array<int, array{marca_id:int, subtotal_bruto:float}> $lineas
    * @param array{version?:int, generales?:array, marcas?:array} $reglas
     */
    public function calcular(array $lineas, array $reglas): array
    {
        $porEscala = ($reglas['tipo'] ?? 'marca') === 'escala';
        if ($porEscala) {
            $lineas = array_map(function (array $linea) {
                $linea['marca_id'] = (int) ($linea['escala_id'] ?? 0);
                return $linea;
            }, $lineas);
            $reglas['marcas'] = array_map(function (array $regla) {
                $regla['marca_id'] = (int) ($regla['escala_id'] ?? 0);
                return $regla;
            }, $reglas['escalas'] ?? []);
        }

        $totalBruto = round(array_sum(array_column($lineas, 'subtotal_bruto')), 2);
        $subtotalesMarca = [];

        foreach ($lineas as $linea) {
            $marcaId = (int) $linea['marca_id'];
            $subtotalesMarca[$marcaId] = ($subtotalesMarca[$marcaId] ?? 0)
                + (float) $linea['subtotal_bruto'];
        }

        $version = (int) ($reglas['version'] ?? 4);
        $porcentajesForzados = $this->porcentajesForzados(
            array_keys($subtotalesMarca),
            $reglas['marcas'] ?? [],
            (string) ($reglas['descuento_modo'] ?? 'automatico'),
            (int) ($reglas['descuento_escalon'] ?? 0)
        );
        if ($porcentajesForzados !== null) {
            [$porcentajeGeneral, $porcentajesMarca] = $this->resolverGeneralConPorcentajesFijos(
                $lineas,
                $porcentajesForzados,
                $reglas['generales'] ?? []
            );
        } elseif ($version >= 4) {
            [$porcentajeGeneral, $porcentajesMarca] = $this->resolverEscalonesNetos(
                $lineas,
                array_keys($subtotalesMarca),
                $reglas
            );
        } else {
            $porcentajeGeneral = $this->porcentajeAlcanzado($totalBruto, $reglas['generales'] ?? []);
            $porcentajesMarca = [];
            foreach ($subtotalesMarca as $marcaId => $subtotal) {
                $reglasMarca = array_values(array_filter(
                    $reglas['marcas'] ?? [],
                    fn (array $regla) => (int) ($regla['marca_id'] ?? 0) === $marcaId
                ));
                $baseEscalonMarca = $version >= 3 ? $totalBruto : $subtotal;
                $porcentajesMarca[$marcaId] = $this->porcentajeAlcanzado($baseEscalonMarca, $reglasMarca);
            }
        }

        $descuentoMarca = 0.0;
        $descuentoGeneral = 0.0;
        $detalleMarcas = [];
        foreach ($lineas as $linea) {
            $marcaId = (int) $linea['marca_id'];
            $subtotal = (float) $linea['subtotal_bruto'];
            $porcentajeMarca = $porcentajesMarca[$marcaId] ?? 0.0;
            $marca = round($subtotal * $porcentajeMarca / 100, 2);
            $general = round(($subtotal - $marca) * $porcentajeGeneral / 100, 2);
            $descuentoMarca += $marca;
            $descuentoGeneral += $general;
            $detalleMarcas[$marcaId] ??= [
                'marca_id' => $marcaId,
                'subtotal_bruto' => 0.0,
                'porcentaje_marca' => $porcentajeMarca,
                'descuento_marca' => 0.0,
                'descuento_general' => 0.0,
                'descuento_ganado' => 0.0,
            ];
            $detalleMarcas[$marcaId]['subtotal_bruto'] += $subtotal;
            $detalleMarcas[$marcaId]['descuento_marca'] += $marca;
            $detalleMarcas[$marcaId]['descuento_general'] += $general;
            $detalleMarcas[$marcaId]['descuento_ganado'] += $marca + $general;
        }

        $detalleMarcas = array_map(function (array $detalle) {
            foreach (['subtotal_bruto', 'descuento_marca', 'descuento_general', 'descuento_ganado'] as $campo) {
                $detalle[$campo] = round($detalle[$campo], 2);
            }

            return $detalle;
        }, array_values($detalleMarcas));

        $resultado = [
            'total_bruto' => $totalBruto,
            'subtotal_neto' => round($totalBruto - $descuentoMarca - $descuentoGeneral, 2),
            'porcentaje_general' => $porcentajeGeneral,
            'porcentajes_marca' => $porcentajesMarca,
            'descuento_marca' => round($descuentoMarca, 2),
            'descuento_general' => round($descuentoGeneral, 2),
            'descuento_ganado' => round($descuentoMarca + $descuentoGeneral, 2),
            'detalle_marcas' => $detalleMarcas,
        ];

        if ($porEscala) {
            $resultado['porcentajes_escala'] = $resultado['porcentajes_marca'];
            $resultado['detalle_escalas'] = array_map(function (array $detalle) {
                $detalle['escala_id'] = $detalle['marca_id'];
                unset($detalle['marca_id']);
                return $detalle;
            }, $resultado['detalle_marcas']);
        }

        return $resultado;
    }

    /** @param array<int, array{venta_minima:mixed, porcentaje_descuento:mixed}> $reglas */
    public function porcentajeAlcanzado(float $subtotal, array $reglas): float
    {
        $alcanzada = null;
        foreach ($reglas as $regla) {
            $minimo = (float) ($regla['venta_minima'] ?? 0);
            if ($subtotal + self::TOLERANCIA >= $minimo
                && ($alcanzada === null || $minimo >= $alcanzada['minimo'])) {
                $alcanzada = [
                    'minimo' => $minimo,
                    'porcentaje' => (float) ($regla['porcentaje_descuento'] ?? 0),
                ];
            }
        }

        return $alcanzada['porcentaje'] ?? 0.0;
    }

    /** @return array<int, float>|null */
    private function porcentajesForzados(array $marcaIds, array $reglas, string $modo, int $escalon): ?array
    {
        if (!in_array($modo, ['escalon', 'maximo'], true)) {
            return null;
        }

        $porcentajes = [];
        foreach ($marcaIds as $marcaId) {
            $reglasMarca = array_values(array_filter(
                $reglas,
                fn (array $regla) => (int) ($regla['marca_id'] ?? 0) === $marcaId
            ));
            usort($reglasMarca, fn (array $primera, array $segunda) =>
                [(float) ($primera['venta_minima'] ?? 0), (int) ($primera['orden'] ?? 0)]
                <=> [(float) ($segunda['venta_minima'] ?? 0), (int) ($segunda['orden'] ?? 0)]
            );

            if (!$reglasMarca) {
                $porcentajes[$marcaId] = 0.0;
                continue;
            }

            $indice = $modo === 'maximo'
                ? count($reglasMarca) - 1
                : min(max($escalon, 1) - 1, count($reglasMarca) - 1);
            $porcentajes[$marcaId] = (float) ($reglasMarca[$indice]['porcentaje_descuento'] ?? 0);
        }

        return $porcentajes;
    }

    /** @return array{0:float, 1:array<int, float>} */
    private function resolverGeneralConPorcentajesFijos(
        array $lineas,
        array $porcentajesMarca,
        array $reglasGenerales
    ): array {
        $candidatos = array_map(
            fn (array $regla) => (float) ($regla['venta_minima'] ?? 0),
            $reglasGenerales
        );
        $candidatos[] = 0.0;
        $candidatos = array_values(array_unique($candidatos));
        rsort($candidatos, SORT_NUMERIC);

        foreach ($candidatos as $baseEscalon) {
            $porcentajeGeneral = $this->porcentajeAlcanzado($baseEscalon, $reglasGenerales);
            $subtotalNeto = 0.0;
            foreach ($lineas as $linea) {
                $subtotal = (float) $linea['subtotal_bruto'];
                $descuentoMarca = round($subtotal * ($porcentajesMarca[(int) $linea['marca_id']] ?? 0) / 100, 2);
                $descuentoGeneral = round(($subtotal - $descuentoMarca) * $porcentajeGeneral / 100, 2);
                $subtotalNeto += $subtotal - $descuentoMarca - $descuentoGeneral;
            }

            if ($subtotalNeto + self::TOLERANCIA >= $baseEscalon) {
                return [$porcentajeGeneral, $porcentajesMarca];
            }
        }

        return [0.0, $porcentajesMarca];
    }

    /**
     * @param array<int, array{marca_id:int, subtotal_bruto:float}> $lineas
     * @param array<int, int> $marcaIds
     * @return array{0:float, 1:array<int, float>}
     */
    private function resolverEscalonesNetos(array $lineas, array $marcaIds, array $reglas): array
    {
        $reglasGenerales = $reglas['generales'] ?? [];
        $reglasPorMarca = [];
        foreach ($marcaIds as $marcaId) {
            $reglasPorMarca[$marcaId] = array_values(array_filter(
                $reglas['marcas'] ?? [],
                fn (array $regla) => (int) ($regla['marca_id'] ?? 0) === $marcaId
            ));
        }

        $candidatos = [0.0];
        foreach ($reglasGenerales as $regla) {
            $candidatos[] = (float) ($regla['venta_minima'] ?? 0);
        }
        foreach ($reglasPorMarca as $reglasMarca) {
            foreach ($reglasMarca as $regla) {
                $candidatos[] = (float) ($regla['venta_minima'] ?? 0);
            }
        }
        $candidatos = array_values(array_unique($candidatos));
        rsort($candidatos, SORT_NUMERIC);

        foreach ($candidatos as $baseEscalon) {
            $porcentajeGeneral = $this->porcentajeAlcanzado($baseEscalon, $reglasGenerales);
            $porcentajesMarca = [];
            $subtotalNeto = 0.0;

            foreach ($marcaIds as $marcaId) {
                $porcentajesMarca[$marcaId] = $this->porcentajeAlcanzado(
                    $baseEscalon,
                    $reglasPorMarca[$marcaId]
                );
            }
            foreach ($lineas as $linea) {
                $subtotal = (float) $linea['subtotal_bruto'];
                $descuentoMarca = round($subtotal * ($porcentajesMarca[(int) $linea['marca_id']] ?? 0) / 100, 2);
                $descuentoGeneral = round(($subtotal - $descuentoMarca) * $porcentajeGeneral / 100, 2);
                $subtotalNeto += $subtotal - $descuentoMarca - $descuentoGeneral;
            }

            if ($subtotalNeto + self::TOLERANCIA >= $baseEscalon) {
                return [$porcentajeGeneral, $porcentajesMarca];
            }
        }

        return [0.0, array_fill_keys($marcaIds, 0.0)];
    }
}