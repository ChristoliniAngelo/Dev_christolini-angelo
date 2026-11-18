<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weighbridge_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('truck_id')->constrained('trucks')->cascadeOnDelete();
            $table->decimal('tare_weight_kg', 15, 2);
            $table->decimal('gross_weight_kg', 15, 2)->nullable();
            $table->decimal('net_weight_kg', 15, 2)->nullable();
            $table->boolean('is_overload')->default(false);
            $table->decimal('overload_kg', 15, 2)->default(0);
            // Status: pending_weighing, overload_hold, approved_for_dispatch, dispatched
            $table->string('status')->default('pending_weighing');
            $table->text('notes')->nullable();
            $table->timestamp('weighed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weighbridge_tickets');
    }
};
