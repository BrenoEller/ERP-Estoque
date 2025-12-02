<?php

namespace App\Http\Controllers;

use App\Services\RegistrarCompraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;

class PurchaseController extends Controller
{
    public function index(): JsonResponse
    {
        $compras = Purchase::with(['itens.product:id,nome'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($compra) {
                return [
                    'id'           => $compra->id,
                    'fornecedor'   => $compra->fornecedor,
                    'total_compra' => (float) $compra->total_compra,
                    'created_at'   => $compra->created_at?->format('Y-m-d H:i:s'),
                    'itens'        => $compra->itens->map(function ($item) {
                        return [
                            'produto_id'     => $item->product_id,
                            'produto_nome'   => $item->product->nome ?? null,
                            'quantidade'     => (int) $item->quantidade,
                            'preco_unitario' => (float) $item->preco_unitario,
                            'total_cost'     => (float) $item->total_cost,
                        ];
                    })->toArray(),
                ];
            });

        return response()->json($compras);
    }

    public function store(Request $request, RegistrarCompraService $service): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'fornecedor'                => ['required', 'string', 'min:3'],
                'produtos'                  => ['required', 'array', 'min:1'],
                'produtos.*.id'             => ['required', 'integer', 'exists:products,id'],
                'produtos.*.quantidade'     => ['required', 'integer', 'min:1'],
                'produtos.*.preco_unitario' => ['required', 'numeric', 'min:0.01'],
            ],
            [
                'fornecedor.required'  => 'O fornecedor é obrigatório.',
                'produtos.required'    => 'Informe ao menos um produto.',
                'produtos.array'       => 'O campo produtos deve ser um array.',
                'produtos.*.id.exists' => 'Produto informado não existe.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $dados = $validator->validated();

        $compra = $service->executar($dados);

        return response()->json([
            'message' => 'Compra registrada com sucesso.',
            'compra'  => $compra,
        ], 201);
    }
}
