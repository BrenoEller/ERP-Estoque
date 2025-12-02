<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                ->constrained('sales')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products');

            $table->unsignedInteger('quantidade');

            $table->decimal('preco_unitario', 10, 2)->unsigned(); // preço que vendeu
            $table->decimal('total_price', 10, 2)->unsigned();    // receita (qtd * preco_unitario)

            $table->decimal('cost_at_sale', 10, 2)->unsigned();   // custo médio unitário na hora
            $table->decimal('total_cost', 10, 2)->unsigned();     // custo total daquela linha
            $table->decimal('profit', 10, 2);                     // lucro dessa linha

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
