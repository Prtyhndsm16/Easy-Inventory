<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function index(Request $request): View
    {
        $search        = Str::limit(trim((string) $request->string('search')), 100, '');
        $dateFrom      = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo        = $request->input('date_to', now()->toDateString());
        $paymentMethod = (string) $request->string('payment_method');

        $query = Sale::query()->with('creator')->latest('sold_at');

        $query->whereBetween('sold_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('receipt_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        if ($paymentMethod !== '') {
            $query->where('payment_method', $paymentMethod);
        }

        $sales = $query->paginate(20)->withQueryString();

        $periodTotal = Sale::whereBetween('sold_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->when($paymentMethod !== '', fn ($q) => $q->where('payment_method', $paymentMethod))
            ->when($search !== '', fn ($q) => $q->where(function ($b) use ($search) {
                $b->where('receipt_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            }))
            ->sum('total_amount');

        return view('sales.index', [
            'sales'          => $sales,
            'search'         => $search,
            'dateFrom'       => $dateFrom,
            'dateTo'         => $dateTo,
            'paymentMethod'  => $paymentMethod,
            'periodTotal'    => (float) $periodTotal,
            'paymentMethods' => ['cash', 'gcash', 'card', 'other'],
        ]);
    }
}
