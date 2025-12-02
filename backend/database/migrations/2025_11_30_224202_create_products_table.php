<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('nome');

            $table->decimal('preco_venda', 10, 2)->unsigned();

            $table->unsignedInteger('estoque_atual')->default(0);

            $table->decimal('custo_medio', 10, 2)
                  ->unsigned()
                  ->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
