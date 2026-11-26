<?php

namespace App\Services\PosIntegration;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryMirror
{
    public function forProduct(int $productId): Collection
    {
        if (! config('pos_integration.enabled')) {
            return collect();
        }

        return DB::table('pos_integration_mirror')
            ->where('snapshot->product_profac_id', $productId)
            ->orderBy('store_id')->orderBy('product_pos_id')
            ->get(['snapshot', 'updated_at'])
            ->map(fn ($row) => [
                'data' => json_decode($row->snapshot, true, 512, JSON_THROW_ON_ERROR),
                'updated_at' => $row->updated_at,
            ]);
    }
}
