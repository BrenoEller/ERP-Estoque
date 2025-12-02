<?php

namespace App\Http\Controllers;

use App\Services\RegistrarVendaService;
use App\Services\CancelarVendaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class SaleController extends Controller
{
    public function index(): JsonResponse
    {
        $vendas = Sale::with(['items.product:id,nome'])
            ->orderByDesc('sale_date')
            ->get()
            ->map(function ($venda) {
                return [
                    'id'          => $venda->id,
                    'cliente'     => $venda->cliente,
                    'status'      => $venda->status,
                    'sale_date'   => $venda->sale_date?->format('Y-m-d H:i:s'),
                    'total_venda' => (float) $venda->total_value,
                    'lucro_total' => (float) $venda->total_profit,
                    'itens'       => $venda->items->map(function ($item) {
                        return [
                            'id'            => $item->id,
                            'produto_id'    => $item->product_id,
                            'produto_nome'  => $item->product->nome ?? null,
                            'quantidade'    => (int) $item->quantidade,
                            'preco_unitario'=> (float) $item->preco_unitario,
                            'total_price'   => (float) $item->total_price,
                            'cost_at_sale'  => (float) $item->cost_at_sale,
                            'total_cost'    => (float) $item->total_cost,
                            'profit'        => (float) $item->profit,
                        ];
                    })->toArray(),
                ];
            });

        return response()->json($vendas);
    }
    public function store(Request $request, RegistrarVendaService $service)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'cliente'                 => ['required', 'string', 'min:3'],
                'produtos'                => ['required', 'array', 'min:1'],
                'produtos.*.id'           => ['required', 'integer', 'exists:products,id'],
                'produtos.*.quantidade'   => ['required', 'integer', 'min:1'],
                'produtos.*.preco_unitario' => ['required', 'numeric', 'min:0.01'],
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $venda = $service->executar($validator->validated());
        } catch (RuntimeException $e) {
            // Aqui entra, por exemplo, estoque insuficiente
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message'      => 'Venda registrada com sucesso.',
            'venda'        => $venda,
            'total_venda'  => $venda->total_value,
            'lucro_total'  => $venda->total_profit,
        ], 201);
    }

    public function cancelar(int $id, CancelarVendaService $service)
    {
        try {
            $venda = $service->executar($id);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Venda cancelada com sucesso.',
            'venda'   => $venda,
        ]);
    }
}
