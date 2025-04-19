<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->decimal('total_price', 10, 2);
                $table->decimal('discount', 10, 2)->nullable();
                $table->decimal('final_price', 10, 2);
                $table->decimal('amount_paid', 10, 2);
                $table->decimal('change', 10, 2);
                $table->integer('points_used');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();

            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
