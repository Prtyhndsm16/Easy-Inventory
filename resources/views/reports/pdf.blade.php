<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Report — {{ $dateFrom }} to {{ $dateTo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            background: #fff;
            padding: 32px 36px;
        }

        /* ── Header ── */
        .report-header {
            border-bottom: 2px solid #059669;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }
        .report-header h1 {
            font-size: 20px;
            font-weight: 700;
            color: #059669;
            letter-spacing: -0.3px;
        }
        .report-header .app-name {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }
        .report-header .period {
            margin-top: 6px;
            font-size: 11px;
            color: #374151;
        }
        .report-header .generated {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 2px;
        }

        /* ── Section title ── */
        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
            margin-top: 22px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e5e7eb;
        }

        /* ── Summary cards ── */
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 10px 12px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            vertical-align: top;
        }
        .summary-card + .summary-card { margin-left: 8px; }
        .summary-card .label { font-size: 10px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; }
        .summary-card .value { font-size: 20px; font-weight: 700; color: #111827; margin-top: 2px; }
        .summary-card .note  { font-size: 9px; color: #9ca3af; margin-top: 2px; }
        .summary-card.alert  .value { color: #dc2626; }

        /* ── Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 4px;
        }
        thead tr {
            background: #f3f4f6;
        }
        th {
            text-align: left;
            padding: 6px 8px;
            font-weight: 700;
            color: #374151;
            border-bottom: 1px solid #d1d5db;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        td {
            padding: 5px 8px;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            vertical-align: top;
        }
        tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 9999px;
            font-size: 9px;
            font-weight: 700;
        }
        .badge-red    { background: #fee2e2; color: #b91c1c; }
        .badge-amber  { background: #fef3c7; color: #92400e; }
        .badge-blue   { background: #dbeafe; color: #1d4ed8; }
        .badge-green  { background: #d1fae5; color: #065f46; }
        .badge-gray   { background: #f3f4f6; color: #4b5563; }

        .font-bold { font-weight: 700; }
        .text-gray { color: #6b7280; }
        .text-small { font-size: 9px; color: #9ca3af; }
        .empty-row td { text-align: center; color: #9ca3af; padding: 12px; }

        .total-row {
            background: #f0fdf4;
            font-weight: 700;
        }
        .total-row td {
            border-top: 2px solid #d1d5db;
            color: #059669;
        }

        /* ── Footer ── */
        .report-footer {
            margin-top: 28px;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="report-header">
        <h1>Inventory &amp; Sales Report</h1>
        <div class="app-name">{{ $appName }}</div>
        <div class="period">Period: {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}</div>
        <div class="generated">Generated: {{ $generatedAt }}</div>
    </div>

    {{-- Summary --}}
    <div class="section-title">Summary</div>
    <table>
        <tr>
            <td width="25%" style="padding:0 6px 0 0; border:none; vertical-align:top;">
                <div style="background:#f0fdf4;border:1px solid #a7f3d0;border-radius:6px;padding:10px 12px;">
                    <div class="label">Total Products</div>
                    <div class="value">{{ number_format($summary['totalProducts']) }}</div>
                    <div class="note">{{ number_format($summary['totalStock']) }} total stock units</div>
                </div>
            </td>
            <td width="25%" style="padding:0 6px;border:none;vertical-align:top;">
                <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:6px;padding:10px 12px;">
                    <div class="label" style="color:#b91c1c;">Low Stock Items</div>
                    <div class="value" style="color:#dc2626;">{{ number_format($summary['lowStockCount']) }}</div>
                    <div class="note">At or below 10 units</div>
                </div>
            </td>
            <td width="25%" style="padding:0 6px;border:none;vertical-align:top;">
                <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:6px;padding:10px 12px;">
                    <div class="label" style="color:#92400e;">Stock Outs</div>
                    <div class="value" style="color:#d97706;">{{ number_format($summary['stockOutCount']) }}</div>
                    <div class="note">{{ number_format($summary['stockOutUnits']) }} units removed</div>
                </div>
            </td>
            <td width="25%" style="padding:0 0 0 6px;border:none;vertical-align:top;">
                <div style="background:#eff6ff;border:1px solid #93c5fd;border-radius:6px;padding:10px 12px;">
                    <div class="label" style="color:#1d4ed8;">Total Sales</div>
                    <div class="value" style="color:#1d4ed8;">{{ number_format($summary['salesCount']) }}</div>
                    <div class="note">₱{{ number_format($summary['salesTotal'], 2) }} revenue</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Low Stock --}}
    <div class="section-title">Low Stock Products (≤ 10 units)</div>
    @if ($lowStock->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Barcode</th>
                    <th class="text-right">Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lowStock as $i => $product)
                    <tr>
                        <td class="text-gray">{{ $i + 1 }}</td>
                        <td class="font-bold">{{ $product->product_name }}</td>
                        <td class="text-gray">{{ $product->category ?? '—' }}</td>
                        <td class="text-gray">{{ $product->supplier ?? '—' }}</td>
                        <td class="text-gray">{{ $product->barcode ?? '—' }}</td>
                        <td class="text-right"><span class="badge badge-red">{{ $product->stock }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size:10px;color:#9ca3af;padding:8px 0;">No low stock products found.</p>
    @endif

    {{-- Stock Outs --}}
    <div class="section-title">Stock In / Received ({{ $dateFrom }} to {{ $dateTo }})</div>
    @if ($stockIns->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Supplier</th>
                    <th>Reference No.</th>
                    <th>Received By</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($stockIns as $i => $item)
                    <tr>
                        <td class="text-gray">{{ $i + 1 }}</td>
                        <td class="text-gray">{{ $item->date->format('M d, Y') }}</td>
                        <td class="font-bold">{{ $item->product_name }}</td>
                        <td><span class="badge badge-green">+{{ $item->quantity }}</span></td>
                        <td class="text-gray">{{ $item->supplier ?? '—' }}</td>
                        <td class="text-gray">{{ $item->reference_no ?? '—' }}</td>
                        <td class="text-gray">{{ $item->receiver?->name ?? 'Unknown' }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" class="font-bold">Total Units Received</td>
                    <td class="font-bold">+{{ $stockIns->sum('quantity') }}</td>
                    <td colspan="3"></td>
                </tr>
            </tbody>
        </table>
    @else
        <p style="font-size:10px;color:#9ca3af;padding:8px 0;">No stock in records for this period.</p>
    @endif

    {{-- Stock Outs --}}
    <div class="section-title">Stock Out Records ({{ $dateFrom }} to {{ $dateTo }})</div>
    @if ($stockOuts->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Reason</th>
                    <th>Transferred To</th>
                    <th>Notes</th>
                    <th>Recorded By</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($stockOuts as $i => $item)
                    @php
                        $badgeClass = match($item->reason) {
                            'Damaged'     => 'badge-red',
                            'Expired'     => 'badge-amber',
                            'Transferred' => 'badge-blue',
                            default       => 'badge-gray',
                        };
                    @endphp
                    <tr>
                        <td class="text-gray">{{ $i + 1 }}</td>
                        <td class="text-gray">{{ $item->date->format('M d, Y') }}</td>
                        <td class="font-bold">{{ $item->product_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td><span class="badge {{ $badgeClass }}">{{ $item->reason }}</span></td>
                        <td>
                            @if ($item->reason === 'Transferred' && $item->transfer_destination)
                                <div class="font-bold">{{ $item->transfer_destination }}</div>
                                <div class="text-small">{{ $item->transfer_address }}</div>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-gray">{{ $item->notes ?? '—' }}</td>
                        <td class="text-gray">{{ $item->recorder?->name ?? 'Unknown' }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" class="font-bold">Total Units Removed</td>
                    <td class="font-bold">{{ $stockOuts->sum('quantity') }}</td>
                    <td colspan="4"></td>
                </tr>
            </tbody>
        </table>
    @else
        <p style="font-size:10px;color:#9ca3af;padding:8px 0;">No stock out records for this period.</p>
    @endif

    {{-- Sales --}}
    <div class="section-title">Sales Transactions ({{ $dateFrom }} to {{ $dateTo }})</div>
    @if ($sales->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date & Time</th>
                    <th>Receipt No.</th>
                    <th>Payment</th>
                    <th>Cashier</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sales as $i => $sale)
                    <tr>
                        <td class="text-gray">{{ $i + 1 }}</td>
                        <td class="text-gray">{{ optional($sale->sold_at)->format('M d, Y h:i A') }}</td>
                        <td class="font-bold">{{ $sale->receipt_number ?? $sale->reference_number }}</td>
                        <td><span class="badge badge-blue">{{ ucfirst($sale->payment_method ?? 'N/A') }}</span></td>
                        <td class="text-gray">{{ $sale->creator?->name ?? 'Unknown' }}</td>
                        <td class="text-right font-bold">₱{{ number_format((float) $sale->total_amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="5" class="font-bold">Total Revenue</td>
                    <td class="text-right font-bold">₱{{ number_format($summary['salesTotal'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <p style="font-size:10px;color:#9ca3af;padding:8px 0;">No sales transactions for this period.</p>
    @endif

    {{-- Inventory Snapshot --}}
    @if (!empty($allProducts) && $allProducts->isNotEmpty())
    <div class="section-title">Current Inventory Snapshot</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Barcode</th>
                <th>Supplier</th>
                <th class="text-right">Price</th>
                <th class="text-right">Stock</th>
                <th class="text-right">Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($allProducts as $i => $p)
                @php $val = (float) $p->price * (int) $p->stock; @endphp
                <tr>
                    <td class="text-gray">{{ $i + 1 }}</td>
                    <td class="font-bold">{{ $p->product_name }}</td>
                    <td class="text-gray">{{ $p->category ?? '—' }}</td>
                    <td class="text-gray">{{ $p->barcode ?? '—' }}</td>
                    <td class="text-gray">{{ $p->supplier ?? '—' }}</td>
                    <td class="text-right">₱{{ number_format((float)$p->price, 2) }}</td>
                    <td class="text-right">
                        <span class="badge {{ $p->stock <= 10 ? 'badge-red' : 'badge-green' }}">{{ $p->stock }}</span>
                    </td>
                    <td class="text-right">₱{{ number_format($val, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6" class="font-bold">Total Inventory Value</td>
                <td class="text-right font-bold">{{ $allProducts->sum('stock') }}</td>
                <td class="text-right font-bold">₱{{ number_format($allProducts->sum(fn($p) => $p->price * $p->stock), 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    {{-- Footer --}}
    <div class="report-footer">
        {{ $appName }} &bull; Inventory &amp; Sales Report &bull; Period: {{ $dateFrom }} to {{ $dateTo }} &bull; Generated {{ $generatedAt }}
    </div>

</body>
</html>
