<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    use HasFactory;

    public const STATUS_PROCESSED = 'processed';
    public const STATUS_PARTIALLY_SHIPPED = 'partially_shipped';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'so_number',
        'customer_name',
        'item_name',
        'ordered_qty_kg',
        'shipped_qty_kg',
        'status',
    ];

    protected $casts = [
        'ordered_qty_kg' => 'float',
        'shipped_qty_kg' => 'float',
    ];

    public function weighbridgeTickets(): HasMany
    {
        return $this->hasMany(WeighbridgeTicket::class);
    }

    public function deliveryNotes(): HasMany
    {
        return $this->hasMany(DeliveryNote::class);
    }

    public function getRemainingQtyKgAttribute(): float
    {
        return max(0, $this->ordered_qty_kg - $this->shipped_qty_kg);
    }
}
