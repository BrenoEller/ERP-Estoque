<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->string('cliente');
            $table->timestamp('sale_date')->nullable();

            $table->decimal('total_value', 10, 2)->unsigned()->default(0);  // total da venda
            $table->decimal('total_cost', 10, 2)->unsigned()->default(0);   // soma dos custos
            $table->decimal('total_profit', 10, 2)->default(0);             // lucro

            $table->string('status')->default('ativo'); // ativo | cancelado
            $table->timestamp('canceled_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
