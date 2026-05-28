<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Alert</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f3f4f6; margin: 0; padding: 24px; color: #111827; }
        .card { background: #fff; border-radius: 12px; max-width: 600px; margin: 0 auto; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .header { background: #dc2626; padding: 28px 32px; }
        .header h1 { color: #fff; font-size: 20px; font-weight: 700; margin: 0; }
        .header p  { color: #fecaca; font-size: 13px; margin: 6px 0 0; }
        .body { padding: 28px 32px; }
        .body p { font-size: 14px; color: #374151; line-height: 1.6; margin: 0 0 16px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 12px; }
        th { text-align: left; padding: 8px 10px; font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .5px; border-bottom: 2px solid #e5e7eb; }
        td { padding: 8px 10px; border-bottom: 1px solid #f3f4f6; color: #374151; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 12px; font-weight: 700; }
        .badge-critical { background: #fee2e2; color: #b91c1c; }
        .badge-warning  { background: #fef3c7; color: #92400e; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 16px 32px; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>⚠️ Low Stock Alert</h1>
            <p>{{ $products->count() }} product(s) have dropped to {{ $threshold }} units or below.</p>
        </div>

        <div class="body">
            <p>Hello Admin,</p>
            <p>
                The following products are running low on stock and may need to be restocked soon.
                Please log in to the inventory system to record new deliveries via <strong>Stock In</strong>.
            </p>

            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th style="text-align:right">Stock Left</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td><strong>{{ $product->product_name }}</strong></td>
                            <td style="color:#6b7280">{{ $product->category ?? '—' }}</td>
                            <td style="color:#6b7280">{{ $product->supplier ?? '—' }}</td>
                            <td style="text-align:right">
                                <span class="badge {{ $product->stock <= 3 ? 'badge-critical' : 'badge-warning' }}">
                                    {{ $product->stock }} left
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="footer">
            {{ config('app.name') }} · Automated Low Stock Alert · {{ now()->format('F d, Y h:i A') }}
        </div>
    </div>
</body>
</html>
