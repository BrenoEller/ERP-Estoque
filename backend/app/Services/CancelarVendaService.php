<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CancelarVendaService
{
    public function executar(int $saleId): Sale
    {
        return DB::transaction(function () use ($saleId) {
            $sale = Sale::with('items')->findOrFail($saleId);

            if ($sale->status === 'cancelado') {
                throw new RuntimeException('Essa venda já foi cancelada.');
            }

            // Devolve estoque
            foreach ($sale->items as $item) {
                $produto = Product::findOrFail($item->product_id);

                $produto->update([
                    'estoque_atual' => $produto->estoque_atual + $item->quantidade,
                ]);
            }

            $sale->update([
                'status'      => 'cancelado',
                'canceled_at' => now(),
            ]);

            return $sale->fresh('items.product');
        });
    }
}
