<?php

namespace App\Services\Inventario;

use DomainException;
use Illuminate\Support\Collection;

class DistribuidorLotesAjuste
{
    public function distribuir(Collection $lotes, int $unidadesSolicitadas): array
    {
        if ($unidadesSolicitadas <= 0) {
            throw new DomainException('La cantidad de unidades debe ser mayor que cero.');
        }

        $distribucion = [];
        $unidadesPendientes = $unidadesSolicitadas;

        foreach ($lotes as $lote) {
            if ($unidadesPendientes <= 0) {
                break;
            }

            $cantidadDisponible = (int) $lote->cantidad_disponible;
            if ($cantidadDisponible <= 0) {
                continue;
            }

            $unidadesLote = min($cantidadDisponible, $unidadesPendientes);
            $distribucion[] = [
                'lote' => $lote,
                'unidades' => $unidadesLote,
            ];
            $unidadesPendientes -= $unidadesLote;
        }

        if ($unidadesPendientes > 0) {
            throw new DomainException('La existencia positiva disponible no es suficiente para realizar el ajuste.');
        }

        return $distribucion;
    }
}
