<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\SalesOrder;
use App\Models\Truck;
use App\Models\WeighbridgeTicket;
use App\Services\WeighbridgeService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeighbridgeController extends Controller
{
    public function index(): View
    {
        $tickets = WeighbridgeTicket::with(['salesOrder', 'truck', 'deliveryNote'])
            ->orderBy('id')
            ->get();

        $salesOrders = SalesOrder::orderBy('id')->get();
        $trucks = Truck::orderBy('id')->get();
        $deliveryNotes = DeliveryNote::with(['salesOrder', 'truck', 'weighbridgeTicket'])
            ->latest('issued_at')
            ->get();

        return view('weighbridge.index', compact('tickets', 'salesOrders', 'trucks', 'deliveryNotes'));
    }

    public function weigh(Request $request, WeighbridgeTicket $ticket, WeighbridgeService $service): RedirectResponse
    {
        $data = $request->validate([
            'gross_weight_kg' => ['required', 'numeric', 'gt:' . $ticket->tare_weight_kg],
        ]);

        try {
            $t = $service->weighGross($ticket->id, (float) $data['gross_weight_kg']);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return $t->is_overload
            ? back()->with('error', "Overload {$t->overload_kg} kg. Tiket ditahan, lakukan timbang ulang.")
            : back()->with('success', "Net {$t->net_weight_kg} kg. Tiket lolos, siap terbit SJ.");
    }

    public function resolveOverload(Request $request, WeighbridgeTicket $ticket, WeighbridgeService $service): RedirectResponse
    {
        $data = $request->validate([
            'new_gross_weight_kg' => ['required', 'numeric', 'gt:' . $ticket->tare_weight_kg],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $t = $service->reweigh($ticket->id, (float) $data['new_gross_weight_kg'], $data['notes'] ?? null);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return $t->is_overload
            ? back()->with('error', "Masih overload {$t->overload_kg} kg. Kurangi muatan lalu timbang ulang.")
            : back()->with('success', "Net {$t->net_weight_kg} kg. Aman, tiket siap terbit SJ.");
    }

    public function dispatch(WeighbridgeTicket $ticket, WeighbridgeService $service): RedirectResponse
    {
        try {
            $dn = $service->issueDeliveryNote($ticket->id);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Surat jalan {$dn->delivery_number} terbit.");
    }
}
