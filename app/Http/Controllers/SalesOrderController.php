<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Services\WeighbridgeService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    public function createNextTicket(Request $request, SalesOrder $salesOrder, WeighbridgeService $service): RedirectResponse
    {
        $data = $request->validate([
            'truck_id' => ['required', 'exists:trucks,id'],
        ]);

        try {
            $t = $service->createNextTicket($salesOrder->id, (int) $data['truck_id']);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Tiket {$t->ticket_number} dibuat.");
    }
}
