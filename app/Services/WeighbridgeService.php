<?php

namespace App\Services;

use App\Models\DeliveryNote;
use App\Models\SalesOrder;
use App\Models\WeighbridgeTicket;
use App\Models\Truck;
use DomainException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class WeighbridgeService
{
    public function issueDeliveryNote(int $ticketId): DeliveryNote
    {
        try {
            return DB::transaction(function () use ($ticketId) {
                $ticket = WeighbridgeTicket::lockForUpdate()->findOrFail($ticketId);

                if ($existing = DeliveryNote::where('weighbridge_ticket_id', $ticket->id)->first()) {
                    return $existing;
                }

                if ($ticket->is_overload || $ticket->status === WeighbridgeTicket::STATUS_OVERLOAD_HOLD) {
                    throw new DomainException('Tiket overload. Lakukan timbang ulang sampai muatan aman.');
                }

                if ($ticket->status !== WeighbridgeTicket::STATUS_APPROVED) {
                    throw new DomainException('Tiket belum lolos penimbangan.');
                }

                $so = SalesOrder::lockForUpdate()->findOrFail($ticket->sales_order_id);

                $dn = DeliveryNote::create([
                    'delivery_number'       => 'SJ-' . now()->format('Ymd') . '-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT),
                    'weighbridge_ticket_id' => $ticket->id,
                    'sales_order_id'        => $ticket->sales_order_id,
                    'truck_id'              => $ticket->truck_id,
                    'dispatched_qty_kg'     => $ticket->net_weight_kg,
                    'issued_at'             => now(),
                ]);

                $ticket->update(['status' => WeighbridgeTicket::STATUS_DISPATCHED]);

                $shipped = $so->shipped_qty_kg + $ticket->net_weight_kg;
                $so->update([
                    'shipped_qty_kg' => $shipped,
                    'status' => $shipped >= $so->ordered_qty_kg
                        ? SalesOrder::STATUS_COMPLETED
                        : SalesOrder::STATUS_PARTIALLY_SHIPPED,
                ]);

                return $dn;
            });
        } catch (UniqueConstraintViolationException $e) {
            return DeliveryNote::where('weighbridge_ticket_id', $ticketId)->firstOrFail();
        }
    }

    public function createNextTicket(int $salesOrderId, int $truckId): WeighbridgeTicket
    {
        return DB::transaction(function () use ($salesOrderId, $truckId) {
            $so    = SalesOrder::lockForUpdate()->findOrFail($salesOrderId);
            $truck = Truck::findOrFail($truckId);

            if ($so->status === SalesOrder::STATUS_COMPLETED || $so->shipped_qty_kg >= $so->ordered_qty_kg) {
                throw new DomainException('SO sudah terpenuhi seluruhnya, tiket baru ditolak.');
            }
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

    public function weighGross(int $ticketId, float $gross): WeighbridgeTicket
    {
        return DB::transaction(function () use ($ticketId, $gross) {
            $ticket = WeighbridgeTicket::with('truck')->lockForUpdate()->findOrFail($ticketId);

            if ($ticket->status !== WeighbridgeTicket::STATUS_PENDING) {
                throw new DomainException('Tiket sudah ditimbang.');
            }

            return $this->applyWeight($ticket, $gross);
        });
    }

    private function applyWeight(WeighbridgeTicket $ticket, float $gross): WeighbridgeTicket
    {
        $net = $gross - $ticket->tare_weight_kg;
        if ($net <= 0) {
            throw new DomainException('Gross harus lebih besar dari tare.');
        }

        $overload = max(0, $net - $ticket->truck->max_net_weight_kg);

        $ticket->update([
            'gross_weight_kg' => $gross,
            'net_weight_kg'   => $net,
            'is_overload'     => $overload > 0,
            'overload_kg'     => $overload,
            'status'          => $overload > 0
                ? WeighbridgeTicket::STATUS_OVERLOAD_HOLD
                : WeighbridgeTicket::STATUS_APPROVED,
            'weighed_at'      => now(),
        ]);

        return $ticket;
    }

    public function reweigh(int $ticketId, float $newGross, ?string $notes = null): WeighbridgeTicket
    {
        return DB::transaction(function () use ($ticketId, $newGross, $notes) {
            $ticket = WeighbridgeTicket::with('truck')->lockForUpdate()->findOrFail($ticketId);

            if ($ticket->status !== WeighbridgeTicket::STATUS_OVERLOAD_HOLD) {
                throw new DomainException('Hanya tiket overload_hold yang bisa ditimbang ulang.');
            }

            $oldGross = $ticket->gross_weight_kg;
            $ticket = $this->applyWeight($ticket, $newGross);

            $log = "Timbang ulang: gross {$oldGross} -> {$newGross} kg" . ($notes ? " ({$notes})" : '');
            $ticket->update([
                'notes' => trim(($ticket->notes ? $ticket->notes . "\n" : '') . $log),
            ]);

            return $ticket;
        });
    }
}
