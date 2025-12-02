<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $produtos = Product::select('id', 'nome', 'custo_medio', 'preco_venda', 'estoque_atual')
            ->orderBy('nome')
            ->get();

        return response()->json($produtos);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'nome'        => ['required', 'string', 'min:3'],
                'preco_venda' => ['required', 'numeric', 'min:0.01'],
            ],
            [
                'nome.required'        => 'O nome do produto é obrigatório.',
                'nome.min'             => 'O nome do produto deve ter pelo menos 3 caracteres.',
                'preco_venda.required' => 'O preço de venda é obrigatório.',
                'preco_venda.numeric'  => 'O preço de venda deve ser numérico.',
                'preco_venda.min'      => 'O preço de venda deve ser maior que zero.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $dados = $validator->validated();

        $produto = Product::create([
            'nome'          => $dados['nome'],
            'preco_venda'   => $dados['preco_venda'],
            'estoque_atual' => 0,
            'custo_medio'   => 0,
        ]);

        return response()->json($produto, 201);
    }

    public function update(Request $request, int $id)
    {
        $produto = Product::findOrFail($id);

        $dados = $request->validate([
            'nome' => ['required', 'string', 'min:3'],
            'preco_venda' => ['required', 'numeric', 'min:0.01'],
        ]);

        $produto->update($dados);

        return response()->json($produto);
    }
}
