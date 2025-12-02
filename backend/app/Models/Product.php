<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'nome',
        'preco_venda',
        'estoque_atual',
        'custo_medio',
    ];
}
