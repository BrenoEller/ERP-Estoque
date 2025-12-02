<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'fornecedor',
    ];

    public function itens(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function getTotalCompraAttribute(): float
    {
        return (float) $this->itens->sum('total_cost');
    }
}
