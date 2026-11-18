<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WeighbridgeTicket extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending_weighing';
    public const STATUS_OVERLOAD_HOLD = 'overload_hold';
    public const STATUS_APPROVED = 'approved_for_dispatch';
    public const STATUS_DISPATCHED = 'dispatched';

    protected $fillable = [
        'ticket_number',
        'sales_order_id',
        'truck_id',
        'tare_weight_kg',
        'gross_weight_kg',
        'net_weight_kg',
        'is_overload',
        'overload_kg',
        'status',
        'notes',
        'weighed_at',
    ];

    protected $casts = [
        'tare_weight_kg' => 'float',
        'gross_weight_kg' => 'float',
        'net_weight_kg' => 'float',
        'is_overload' => 'boolean',
        'overload_kg' => 'float',
        'weighed_at' => 'datetime',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    public function deliveryNote(): HasOne
    {
        return $this->hasOne(DeliveryNote::class);
    }
}
