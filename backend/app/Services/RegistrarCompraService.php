<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;

class RegistrarCompraService
{
    public function executar(array $dados): Purchase
    {
        return DB::transaction(function () use ($dados) {
            $compra = Purchase::create([
                'fornecedor' => $dados['fornecedor'],
            ]);

            foreach ($dados['produtos'] as $item) {
                $produto    = Product::findOrFail($item['id']);
                $quantidade = $item['quantidade'];
                $precoUnit  = $item['preco_unitario'];
                $totalItem  = $quantidade * $precoUnit;

                $estoqueAnterior    = $produto->estoque_atual ?? 0;
                $custoMedioAnterior = $produto->custo_medio ?? 0;

                $novoEstoque = $estoqueAnterior + $quantidade;

                if ($novoEstoque === 0) {
                    $novoCustoMedio = 0;
                } else {
                    $novoCustoMedio = (
                        ($estoqueAnterior * $custoMedioAnterior) + $totalItem
                    ) / $novoEstoque;
                }

                $produto->estoque_atual = $novoEstoque;
                $produto->custo_medio   = $novoCustoMedio;
                $produto->save();

                PurchaseItem::create([
                    'purchase_id'    => $compra->id,
                    'product_id'     => $produto->id,
                    'quantidade'     => $quantidade,
                    'preco_unitario' => $precoUnit,
                    'total_cost'     => $totalItem,
                ]);
            }

            return $compra->load('itens.product');
        });
    }
}
