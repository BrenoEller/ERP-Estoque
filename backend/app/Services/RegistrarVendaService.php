<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RegistrarVendaService
{
    public function executar(array $dados): Sale
    {
        return DB::transaction(function () use ($dados) {
            $sale = Sale::create([
                'cliente' => $dados['cliente'],
                'sale_date' => now(),
                'total_value' => 0,
                'total_cost' => 0,
                'total_profit'=> 0,
                'status' => 'ativo',
            ]);

            $totalVenda = 0;
            $totalCusto = 0;
            $totalLucro = 0;

            foreach ($dados['produtos'] as $item) {
                $produto = Product::findOrFail($item['id']);
                $quantidade = $item['quantidade'];
                $precoVenda = $item['preco_unitario'];

                if ($produto->estoque_atual < $quantidade) {
                    throw new RuntimeException("Estoque insuficiente para o produto {$produto->nome}.");
                }

                $novoEstoque = $produto->estoque_atual - $quantidade;
                $produto->update([
                    'estoque_atual' => $novoEstoque,
                ]);

                $custoUnitario = $produto->custo_medio;   
                $totalLinha = $quantidade * $precoVenda;          
                $custoLinha = $quantidade * $custoUnitario;        
                $lucroLinha = $totalLinha - $custoLinha;

                $totalVenda += $totalLinha;
                $totalCusto += $custoLinha;
                $totalLucro += $lucroLinha;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $produto->id,
                    'quantidade' => $quantidade,
                    'preco_unitario' => $precoVenda,
                    'total_price' => $totalLinha,
                    'cost_at_sale' => $custoUnitario,
                    'total_cost' => $custoLinha,
                    'profit' => $lucroLinha,
                ]);
            }

            $sale->update([
                'total_value' => $totalVenda,
                'total_cost' => $totalCusto,
                'total_profit' => $totalLucro,
            ]);

            return $sale->load('items.product');
        });
    }
}
