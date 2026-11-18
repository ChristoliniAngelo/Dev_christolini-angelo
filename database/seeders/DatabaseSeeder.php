<?php

namespace Database\Seeders;

use App\Models\SalesOrder;
use App\Models\Truck;
use App\Models\WeighbridgeTicket;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $truckOverload = Truck::create([
            'plate_number' => 'B 9123 XYZ',
            'driver_name' => 'Budi Santoso',
            'tare_weight_kg' => 5000,
            'max_net_weight_kg' => 10000, // Batas maksimal ODOL / standar ekspedisi: 10.000 kg
        ]);

        $truckNormal = Truck::create([
            'plate_number' => 'B 8841 ABC',
            'driver_name' => 'Andi Wijaya',
            'tare_weight_kg' => 4500,
            'max_net_weight_kg' => 8000,
        ]);

        // SO-2026-001: 12.000 kg (melebihi kapasitas truk B 9123 XYZ yang maksimal 10.000 kg)
        // Sudah diproses (status = processed) -> TIDAK BOLEH di-cancel!
        $so1 = SalesOrder::create([
            'so_number' => 'SO-2026-001',
            'customer_name' => 'PT Maju Jaya',
            'item_name' => 'Semen Portland 50kg',
            'ordered_qty_kg' => 12000,
            'shipped_qty_kg' => 0,
            'status' => SalesOrder::STATUS_PROCESSED,
        ]);

        // SO-2026-002: 7.500 kg (normal, di bawah batas 8.000 kg)
        $so2 = SalesOrder::create([
            'so_number' => 'SO-2026-002',
            'customer_name' => 'CV Karya Mandiri',
            'item_name' => 'Besi Beton 12mm',
            'ordered_qty_kg' => 7500,
            'shipped_qty_kg' => 0,
            'status' => SalesOrder::STATUS_PROCESSED,
        ]);

        WeighbridgeTicket::create([
            'ticket_number' => 'WB-2026-001',
            'sales_order_id' => $so1->id,
            'truck_id' => $truckOverload->id,
            'tare_weight_kg' => $truckOverload->tare_weight_kg,
            'status' => WeighbridgeTicket::STATUS_PENDING,
            'notes' => 'Sopir mengejar jadwal kapal, muatan penuh dari gudang A.',
        ]);

        WeighbridgeTicket::create([
            'ticket_number' => 'WB-2026-002',
            'sales_order_id' => $so2->id,
            'truck_id' => $truckNormal->id,
            'tare_weight_kg' => $truckNormal->tare_weight_kg,
            'status' => WeighbridgeTicket::STATUS_PENDING,
            'notes' => 'Pengiriman reguler.',
        ]);
    }
}
