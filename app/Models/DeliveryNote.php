<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_number',
        'weighbridge_ticket_id',
        'sales_order_id',
        'truck_id',
        'dispatched_qty_kg',
        'issued_at',
    ];

    protected $casts = [
        'dispatched_qty_kg' => 'float',
        'issued_at' => 'datetime',
    ];

    public function weighbridgeTicket(): BelongsTo
    {
        return $this->belongsTo(WeighbridgeTicket::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }
}
