<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\StockIn;
use App\Models\StockOut;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$dateFrom, $dateTo, $reportType, $reason] = $this->parseFilters($request);

        return view('reports.index', [
            'summary'     => $this->buildSummary($dateFrom, $dateTo),
            'stockOuts'   => $this->stockOutQuery($dateFrom, $dateTo, $reason)->latest('date')->latest('id')->get(),
            'stockIns'    => $this->stockInQuery($dateFrom, $dateTo)->latest('date')->latest('id')->get(),
            'sales'       => $this->salesQuery($dateFrom, $dateTo)->latest('sold_at')->get(),
            'lowStock'    => Product::where('stock', '<=', 10)->orderBy('stock')->get(),
            'allProducts' => Product::orderBy('product_name')->get(),
            'dateFrom'    => $dateFrom,
            'dateTo'      => $dateTo,
            'reportType'  => $reportType,
            'reason'      => $reason,
            'reasons'     => ['Damaged', 'Expired', 'Transferred'],
            'generatedAt' => now()->format('F d, Y h:i A'),
        ]);
    }

    public function download(Request $request): Response
    {
        [$dateFrom, $dateTo, $reportType, $reason] = $this->parseFilters($request);

        $data = [
            'summary'     => $this->buildSummary($dateFrom, $dateTo),
            'stockOuts'   => $this->stockOutQuery($dateFrom, $dateTo, $reason)->latest('date')->latest('id')->get(),
            'stockIns'    => $this->stockInQuery($dateFrom, $dateTo)->latest('date')->latest('id')->get(),
            'sales'       => $this->salesQuery($dateFrom, $dateTo)->latest('sold_at')->get(),
            'lowStock'    => Product::where('stock', '<=', 10)->orderBy('stock')->get(),
            'allProducts' => Product::orderBy('product_name')->get(),
            'dateFrom'    => $dateFrom,
            'dateTo'      => $dateTo,
            'reportType'  => $reportType,
            'reason'      => $reason,
            'generatedAt' => now()->format('F d, Y h:i A'),
            'appName'     => config('app.name'),
        ];

        $pdf = Pdf::loadView('reports.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'sans-serif')
            ->setOption('isHtml5ParserEnabled', true);

        $filename = 'report-' . $dateFrom . '-to-' . $dateTo . ($reason ? '-' . strtolower($reason) : '') . '.pdf';

        return $pdf->download($filename);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function parseFilters(Request $request): array
    {
        $dateFrom   = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo     = $request->input('date_to',   now()->toDateString());
        $reportType = $request->input('report_type', 'all');
        $reason     = (string) $request->input('reason', '');

        return [$dateFrom, $dateTo, $reportType, $reason];
    }

    private function buildSummary(string $dateFrom, string $dateTo): array
    {
        $totalProducts  = Product::count();
        $totalStock     = (int) Product::sum('stock');
        $lowStockCount  = Product::where('stock', '<=', 10)->count();

        $stockOutCount  = Schema::hasTable('stock_outs')
            ? StockOut::whereBetween('date', [$dateFrom, $dateTo])->count()
            : 0;
        $stockOutUnits  = Schema::hasTable('stock_outs')
            ? (int) StockOut::whereBetween('date', [$dateFrom, $dateTo])->sum('quantity')
            : 0;

        $salesCount     = Schema::hasTable('sales')
            ? Sale::whereBetween('sold_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->count()
            : 0;
        $salesTotal     = Schema::hasTable('sales')
            ? (float) Sale::whereBetween('sold_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->sum('total_amount')
            : 0.0;

        $stockInCount  = Schema::hasTable('stock_ins')
            ? StockIn::whereBetween('date', [$dateFrom, $dateTo])->count()
            : 0;
        $stockInUnits  = Schema::hasTable('stock_ins')
            ? (int) StockIn::whereBetween('date', [$dateFrom, $dateTo])->sum('quantity')
            : 0;

        return compact(
            'totalProducts', 'totalStock', 'lowStockCount',
            'stockOutCount', 'stockOutUnits',
            'stockInCount',  'stockInUnits',
            'salesCount', 'salesTotal'
        );
    }

    private function stockOutQuery(string $dateFrom, string $dateTo, string $reason = '')
    {
        if (! Schema::hasTable('stock_outs')) {
            return StockOut::query()->whereRaw('0=1');
        }

        $query = StockOut::with('recorder')->whereBetween('date', [$dateFrom, $dateTo]);

        if ($reason !== '') {
            $query->where('reason', $reason);
        }

        return $query;
    }

    private function salesQuery(string $dateFrom, string $dateTo)
    {
        if (! Schema::hasTable('sales')) {
            return Sale::query()->whereRaw('0=1');
        }

        return Sale::with('creator')
            ->whereBetween('sold_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
    }

    private function stockInQuery(string $dateFrom, string $dateTo)
    {
        if (! Schema::hasTable('stock_ins')) {
            return StockIn::query()->whereRaw('0=1');
        }

        return StockIn::with(['product', 'receiver'])
            ->whereBetween('date', [$dateFrom, $dateTo]);
    }
}

