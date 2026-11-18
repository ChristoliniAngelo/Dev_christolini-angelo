<?php

namespace Tests\Feature;

use App\Models\DeliveryNote;
use App\Models\SalesOrder;
use App\Models\Truck;
use App\Models\WeighbridgeTicket;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeighbridgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_normal_weighing_and_dispatch_completes_sales_order(): void
    {
        $ticket = WeighbridgeTicket::where('ticket_number', 'WB-2026-002')->firstOrFail();

        // Tare 4500 + Net 7500 = Gross 12000 (Max net 8000 -> aman)
        $this->post(route('tickets.weigh', $ticket), [
            'gross_weight_kg' => 12000,
        ])->assertRedirect();

        $ticket->refresh();
        $this->assertEquals(7500, $ticket->net_weight_kg);
        $this->assertFalse($ticket->is_overload);
        $this->assertEquals(WeighbridgeTicket::STATUS_APPROVED, $ticket->status);

        $this->post(route('tickets.dispatch', $ticket))->assertRedirect();

        $ticket->refresh();
        $this->assertEquals(WeighbridgeTicket::STATUS_DISPATCHED, $ticket->status);
        $this->assertDatabaseHas('delivery_notes', [
            'weighbridge_ticket_id' => $ticket->id,
            'dispatched_qty_kg' => 7500,
        ]);

        $so = $ticket->salesOrder->fresh();
        $this->assertEquals(7500, $so->shipped_qty_kg);
        $this->assertEquals(SalesOrder::STATUS_COMPLETED, $so->status);
    }

    public function test_overload_weighing_puts_ticket_on_hold_and_blocks_dispatch(): void
    {
        $ticket = WeighbridgeTicket::where('ticket_number', 'WB-2026-001')->firstOrFail();

        // Tare 5000 + Net 12000 = Gross 17000 (Max net 10000 -> overload 2000 kg)
        $this->post(route('tickets.weigh', $ticket), [
            'gross_weight_kg' => 17000,
        ])->assertRedirect();

        $ticket->refresh();
        $this->assertEquals(12000, $ticket->net_weight_kg);
        $this->assertTrue($ticket->is_overload);
        $this->assertEquals(2000, $ticket->overload_kg);
        $this->assertEquals(WeighbridgeTicket::STATUS_OVERLOAD_HOLD, $ticket->status);

        // Mencoba cetak surat jalan saat overload harus ditolak
        $this->post(route('tickets.dispatch', $ticket))->assertRedirect();

        $ticket->refresh();
        $this->assertEquals(WeighbridgeTicket::STATUS_OVERLOAD_HOLD, $ticket->status);
        $this->assertEquals(0, DeliveryNote::where('weighbridge_ticket_id', $ticket->id)->count());
    }

    public function test_can_create_next_ticket_for_remaining_so_qty_and_rejects_completed_so(): void
    {
        $so = SalesOrder::where('so_number', 'SO-2026-001')->firstOrFail();
        $truck = Truck::where('plate_number', 'B 8841 ABC')->firstOrFail();

        // SO masih punya sisa muatan -> boleh buat tiket ritase berikutnya
        $this->post(route('sales-orders.next-ticket', $so), [
            'truck_id' => $truck->id,
        ])->assertRedirect();

        $this->assertEquals(2, WeighbridgeTicket::where('sales_order_id', $so->id)->count());

        // Jika SO sudah completed -> harus ditolak dengan pesan error
        $so->update([
            'shipped_qty_kg' => $so->ordered_qty_kg,
            'status' => SalesOrder::STATUS_COMPLETED,
        ]);

        $this->post(route('sales-orders.next-ticket', $so), [
            'truck_id' => $truck->id,
        ])->assertRedirect()->assertSessionHas('error');
    }

    public function test_resolve_overload_by_reweighing_allows_partial_dispatch_without_cancelling_so(): void
    {
        $ticket = WeighbridgeTicket::where('ticket_number', 'WB-2026-001')->firstOrFail();

        // Langkah 1: Penimbangan awal overload (17.000 kg gross -> 12.000 kg net)
        $this->post(route('tickets.weigh', $ticket), [
            'gross_weight_kg' => 17000,
        ]);

        // Langkah 2: Bongkar 2.000 kg, timbang ulang menjadi 15.000 kg gross -> 10.000 kg net (aman)
        $this->post(route('tickets.resolve-overload', $ticket), [
            'new_gross_weight_kg' => 15000,
            'notes' => 'Bongkar sebagian 2000 kg ke gudang sementara.',
        ])->assertRedirect();

        $ticket->refresh();
        $this->assertEquals(10000, $ticket->net_weight_kg);
        $this->assertFalse($ticket->is_overload);
        $this->assertEquals(0, $ticket->overload_kg);
        $this->assertEquals(WeighbridgeTicket::STATUS_APPROVED, $ticket->status);

        // Langkah 3: Terbitkan surat jalan untuk 10.000 kg
        $this->post(route('tickets.dispatch', $ticket))->assertRedirect();

        $ticket->refresh();
        $this->assertEquals(WeighbridgeTicket::STATUS_DISPATCHED, $ticket->status);

        $so = $ticket->salesOrder->fresh();
        $this->assertEquals(10000, $so->shipped_qty_kg);
        $this->assertEquals(2000, $so->remaining_qty_kg);
        $this->assertEquals(SalesOrder::STATUS_PARTIALLY_SHIPPED, $so->status);
    }
}
