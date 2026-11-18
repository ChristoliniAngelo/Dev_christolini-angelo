<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pos Timbangan — Operasional Pengiriman</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900 antialiased min-h-screen">
    <!-- Top Bar -->
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="font-semibold text-base tracking-tight text-gray-900">Weighbridge Ops</span>
                <span class="text-gray-300">|</span>
                <span class="text-xs text-gray-500">Pos Timbangan &amp; Pengiriman</span>
            </div>
            <div class="text-xs text-gray-500">
                Aturan: SO <code>processed</code> tidak boleh di-cancel &middot; Muatan overload dilarang cetak Surat Jalan
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 space-y-6">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="bg-white border-l-4 border-emerald-600 border border-gray-200 text-gray-800 px-4 py-3 rounded text-sm">
                <span class="font-semibold text-emerald-700">Berhasil:</span> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-white border-l-4 border-red-600 border border-gray-200 text-gray-800 px-4 py-3 rounded text-sm">
                <span class="font-semibold text-red-700">Ditolak:</span> {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-white border-l-4 border-red-600 border border-gray-200 text-gray-800 px-4 py-3 rounded text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Panel Soal Tes -->
        <details open class="bg-white border border-gray-200 rounded-md">
            <summary class="px-5 py-3.5 border-b border-gray-200 bg-gray-50/70 text-sm font-semibold text-gray-800 cursor-pointer flex items-center justify-between select-none">
                <span>Instruksi &amp; Soal Tes (Waktu: 30 Menit)</span>
                <span class="text-xs font-normal text-gray-500">Tampilkan / Sembunyikan</span>
            </summary>
            <div class="p-5 space-y-4 text-sm text-gray-700 leading-relaxed">
                <div class="text-xs sm:text-sm text-gray-600 border-b border-gray-100 pb-3">
                    <strong class="text-gray-900">Studi Kasus:</strong>
                    Saat jam sibuk pengiriman, sebuah truk selesai muat dan naik ke timbangan untuk penimbangan akhir (<em>Gross Weight</em>).
                    Setelah dikurangi berat kosong (<em>Tare Weight</em>), <em>Net Weight</em> muatan melebihi batas maksimal (<strong>overload / ODOL</strong>).
                    Sopir mendesak surat jalan segera dicetak karena mengejar jadwal kapal, sementara aturan sistem melarang pembatalan dokumen <strong>Sales Order (SO)</strong> yang sudah <code>processed</code>.
                </div>

                <div>
                    <div class="font-semibold text-gray-900 mb-2">Daftar Tugas (5 Soal)</div>
                    <ol class="list-decimal list-inside space-y-2 text-xs sm:text-sm text-gray-700">
                        <li>
                            <strong class="text-gray-900">Penerbitan Surat Jalan:</strong>
                            Pastikan Surat Jalan hanya dapat diterbitkan untuk truk yang sudah lolos penimbangan dan aman dari batas muatan, serta pastikan proses penyimpanan datanya aman dari duplikasi saat jam sibuk.
                        </li>
                        <li>
                            <strong class="text-gray-900">Penimbangan Akhir &amp; Deteksi Overload:</strong>
                            Ketika truk ditimbang, sistem harus menghitung berat bersih muatan dan otomatis menahan tiket apabila muatan melebihi batas maksimal truk, atau meloloskannya jika masih dalam batas aman.
                        </li>
                        <li>
                            <strong class="text-gray-900">Penanganan Overload (Timbang Ulang):</strong>
                            Sediakan alur penyelesaian ketika truk terkena overload: setelah muatan dibongkar sebagian dan ditimbang ulang, pastikan berat terbarunya aman agar truk dapat melanjutkan proses cetak Surat Jalan tanpa membatalkan Sales Order.
                        </li>
                        <li>
                            <strong class="text-gray-900">Pengiriman Sisa Muatan (Ritase Lanjutan):</strong>
                            Ketika Sales Order belum terkirim seluruhnya, sistem harus dapat membuatkan tiket timbangan baru untuk armada berikutnya, dan menolak pembuatan tiket jika pesanan sudah terpenuhi seluruhnya.
                        </li>
                        <li>
                            <strong class="text-gray-900">Laporan Pos Timbangan (SQL Query di DBeaver):</strong>
                            Buat 1 query SQL di DBeaver untuk menghasilkan laporan seluruh aktivitas timbangan dengan output kolom: nomor tiket, nomor SO, nama customer, plat nomor truk, berat bersih (kg), status tiket, dan nomor surat jalan (tetap tampilkan tiket meskipun surat jalannya belum terbit).
                        </li>
                    </ol>
                </div>

                <div class="pt-3 border-t border-gray-100 text-xs text-gray-500 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        Verifikasi: <code class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-800">php artisan test --filter=WeighbridgeTest</code>
                    </div>
                    <div>
                        Pengumpulan: buat branch baru <code class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-800">dev_nama_kandidat</code> lalu push ke GitHub.
                    </div>
                </div>
            </div>
        </details>

        <!-- 1. Antrean Pos Timbangan -->
        <section class="bg-white border border-gray-200 rounded-md overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">Antrean Pos Timbangan</h2>
                <span class="text-xs text-gray-500">{{ $tickets->count() }} tiket</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2.5 font-medium">Tiket / SO</th>
                            <th class="px-4 py-2.5 font-medium">Armada &amp; Sopir</th>
                            <th class="px-4 py-2.5 font-medium text-right">Tare (kg)</th>
                            <th class="px-4 py-2.5 font-medium text-right">Gross / Net (kg)</th>
                            <th class="px-4 py-2.5 font-medium text-right">Maks. ODOL (kg)</th>
                            <th class="px-4 py-2.5 font-medium">Status</th>
                            <th class="px-4 py-2.5 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($tickets as $ticket)
                            <tr class="hover:bg-gray-50/70 align-top">
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-gray-900">{{ $ticket->ticket_number }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $ticket->salesOrder->so_number }} &middot; {{ $ticket->salesOrder->customer_name }}</div>
                                    <div class="text-xs text-gray-400">Order: {{ number_format($ticket->salesOrder->ordered_qty_kg) }} kg</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-gray-800">{{ $ticket->truck->plate_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $ticket->truck->driver_name }}</div>
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono tabular-nums text-gray-700">
                                    {{ number_format($ticket->tare_weight_kg) }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono tabular-nums">
                                    <div class="text-gray-700">{{ $ticket->gross_weight_kg ? number_format($ticket->gross_weight_kg) : '-' }}</div>
                                    <div class="text-xs text-gray-500">Net: {{ $ticket->net_weight_kg ? number_format($ticket->net_weight_kg) : '-' }}</div>
                                    @if ($ticket->is_overload)
                                        <div class="text-xs font-medium text-red-600 mt-0.5">
                                            +{{ number_format($ticket->overload_kg) }} kg overload
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono tabular-nums text-gray-700">
                                    {{ number_format($ticket->truck->max_net_weight_kg) }}
                                </td>
                                <td class="px-4 py-3.5">
                                    @php
                                        $badge = match ($ticket->status) {
                                            'pending_weighing' => 'bg-gray-100 text-gray-700 border-gray-200',
                                            'overload_hold' => 'bg-red-50 text-red-700 border-red-200',
                                            'approved_for_dispatch' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'dispatched' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            default => 'bg-gray-100 text-gray-700 border-gray-200',
                                        };
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded border text-xs font-medium {{ $badge }}">
                                        {{ $ticket->status }}
                                    </span>
                                    @if ($ticket->notes)
                                        <p class="text-xs text-gray-500 mt-1.5 max-w-xs">{{ $ticket->notes }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 space-y-2 min-w-[250px]">
                                    @if ($ticket->status === 'pending_weighing')
                                        <form action="{{ route('tickets.weigh', $ticket) }}" method="POST" class="flex gap-1.5">
                                            @csrf
                                            <input type="number" name="gross_weight_kg" step="0.01" required
                                                   placeholder="Gross (kg)"
                                                   class="w-32 rounded border border-gray-300 px-2.5 py-1 text-xs focus:outline-none focus:border-gray-500">
                                            <button type="submit"
                                                    class="bg-gray-900 hover:bg-gray-800 text-white px-3 py-1 rounded text-xs font-medium">
                                                Timbang Akhir
                                            </button>
                                        </form>
                                    @endif

                                    @if ($ticket->status === 'overload_hold')
                                        <form action="{{ route('tickets.resolve-overload', $ticket) }}" method="POST" class="space-y-1.5 bg-gray-50 p-2.5 rounded border border-gray-200">
                                            @csrf
                                            <div class="text-xs font-medium text-gray-700">Timbang Ulang (Bongkar Sebagian)</div>
                                            <input type="number" name="new_gross_weight_kg" step="0.01" required
                                                   placeholder="Gross baru (kg)"
                                                   class="w-full rounded border border-gray-300 px-2.5 py-1 text-xs bg-white focus:outline-none focus:border-gray-500">
                                            <input type="text" name="notes"
                                                   placeholder="Catatan (opsional)"
                                                   class="w-full rounded border border-gray-300 px-2.5 py-1 text-xs bg-white focus:outline-none focus:border-gray-500">
                                            <button type="submit"
                                                    class="w-full bg-amber-600 hover:bg-amber-700 text-white px-3 py-1 rounded text-xs font-medium">
                                                Simpan Timbang Ulang
                                            </button>
                                        </form>
                                    @endif

                                    @if ($ticket->status !== 'dispatched')
                                        <form action="{{ route('tickets.dispatch', $ticket) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    class="w-full bg-white hover:bg-gray-50 text-gray-800 border border-gray-300 px-3 py-1 rounded text-xs font-medium">
                                                Terbitkan Surat Jalan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-600 font-mono">
                                            {{ $ticket->deliveryNote?->delivery_number }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 2. Daftar Sales Order -->
        <section class="bg-white border border-gray-200 rounded-md overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">Daftar Sales Order (SO)</h2>
                <span class="text-xs text-gray-500">{{ $salesOrders->count() }} dokumen</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2.5 font-medium">No. SO</th>
                            <th class="px-4 py-2.5 font-medium">Customer &amp; Barang</th>
                            <th class="px-4 py-2.5 font-medium text-right">Ordered (kg)</th>
                            <th class="px-4 py-2.5 font-medium text-right">Shipped (kg)</th>
                            <th class="px-4 py-2.5 font-medium text-right">Sisa (kg)</th>
                            <th class="px-4 py-2.5 font-medium">Status</th>
                            <th class="px-4 py-2.5 font-medium">Ritase Lanjutan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($salesOrders as $so)
                            <tr class="hover:bg-gray-50/70">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $so->so_number }}</td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-800">{{ $so->customer_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $so->item_name }}</div>
                                </td>
                                <td class="px-4 py-3 text-right font-mono tabular-nums text-gray-700">{{ number_format($so->ordered_qty_kg) }}</td>
                                <td class="px-4 py-3 text-right font-mono tabular-nums text-gray-700">{{ number_format($so->shipped_qty_kg) }}</td>
                                <td class="px-4 py-3 text-right font-mono tabular-nums text-gray-700">{{ number_format($so->remaining_qty_kg) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-block px-2 py-0.5 rounded border border-gray-200 bg-gray-100 text-gray-700 text-xs font-medium">
                                        {{ $so->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <form action="{{ route('sales-orders.next-ticket', $so) }}" method="POST" class="flex items-center gap-1.5">
                                        @csrf
                                        <select name="truck_id" class="rounded border border-gray-300 px-2 py-1 text-xs bg-white focus:outline-none focus:border-gray-500">
                                            @foreach ($trucks as $truck)
                                                <option value="{{ $truck->id }}">
                                                    {{ $truck->plate_number }} (Max {{ number_format($truck->max_net_weight_kg) }} kg)
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit"
                                                class="bg-gray-900 hover:bg-gray-800 text-white px-3 py-1 rounded text-xs font-medium whitespace-nowrap">
                                            Buat Tiket Sisa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 3. Surat Jalan Terbit -->
        <section class="bg-white border border-gray-200 rounded-md overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">Surat Jalan Terbit (Delivery Notes)</h2>
                <span class="text-xs text-gray-500">{{ $deliveryNotes->count() }} dokumen</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2.5 font-medium">No. Surat Jalan</th>
                            <th class="px-4 py-2.5 font-medium">No. Tiket</th>
                            <th class="px-4 py-2.5 font-medium">No. SO</th>
                            <th class="px-4 py-2.5 font-medium">Truk</th>
                            <th class="px-4 py-2.5 font-medium text-right">Qty Dikirim (kg)</th>
                            <th class="px-4 py-2.5 font-medium">Waktu Terbit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($deliveryNotes as $dn)
                            <tr class="hover:bg-gray-50/70">
                                <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900">{{ $dn->delivery_number }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $dn->weighbridgeTicket->ticket_number }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $dn->salesOrder->so_number }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $dn->truck->plate_number }}</td>
                                <td class="px-4 py-3 text-right font-mono tabular-nums text-gray-700">{{ number_format($dn->dispatched_qty_kg) }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500 font-mono">{{ $dn->issued_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-xs text-gray-400">
                                    Belum ada Surat Jalan yang diterbitkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
