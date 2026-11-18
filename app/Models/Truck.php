<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Truck extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number',
        'driver_name',
        'tare_weight_kg',
        'max_net_weight_kg',
    ];

    protected $casts = [
        'tare_weight_kg' => 'float',
        'max_net_weight_kg' => 'float',
    ];

    public function weighbridgeTickets(): HasMany
    {
        return $this->hasMany(WeighbridgeTicket::class);
    }

    public function createNextTicket(int $salesOrderId, int $truckId): WeighbridgeTicket
    {
        return DB::transaction(function () use ($salesOrderId, $truckId) {
            $so    = SalesOrder::lockForUpdate()->findOrFail($salesOrderId);
            $truck = Truck::findOrFail($truckId);

            if ($so->status === SalesOrder::STATUS_COMPLETED || $so->shipped_qty_kg >= $so->ordered_qty_kg) {
                throw new DomainException('SO sudah terpenuhi seluruhnya, tiket baru ditolak.');
            }

            // cek duplikat
            $active = WeighbridgeTicket::where('sales_order_id', $so->id)
                ->where('truck_id', $truck->id)
                ->whereIn('status', [
                    WeighbridgeTicket::STATUS_PENDING,
                    WeighbridgeTicket::STATUS_OVERLOAD_HOLD,
                    WeighbridgeTicket::STATUS_APPROVED,
                ])->exists();

            if ($active) {
                throw new DomainException('Truk ini masih punya tiket aktif untuk SO tersebut.');
            }

            return WeighbridgeTicket::create([
                'ticket_number'  => 'WB-' . now()->format('YmdHis') . '-' . $so->id . '-' . $truck->id,
                'sales_order_id' => $so->id,
                'truck_id'       => $truck->id,
                'tare_weight_kg' => $truck->tare_weight_kg,
                'status'         => WeighbridgeTicket::STATUS_PENDING,
                'notes'          => 'Ritase lanjutan, sisa SO ' . $so->remaining_qty_kg . ' kg.',
            ]);
        });
    }
}
