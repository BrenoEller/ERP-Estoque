<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'cliente',
        'sale_date',
        'total_value',
        'total_cost',
        'total_profit',
        'status',
        'canceled_at',
    ];

    protected $casts = [
        'sale_date'   => 'datetime',
        'canceled_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
