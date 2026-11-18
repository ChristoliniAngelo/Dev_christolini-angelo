<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('so_number')->unique();
            $table->string('customer_name');
            $table->string('item_name');
            $table->decimal('ordered_qty_kg', 15, 2);
            $table->decimal('shipped_qty_kg', 15, 2)->default(0);
            // Status: processed, partially_shipped, completed (tidak boleh cancelled setelah processed)
            $table->string('status')->default('processed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
