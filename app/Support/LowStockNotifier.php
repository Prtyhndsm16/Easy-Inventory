<?php

namespace App\Support;

use App\Mail\LowStockAlert;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LowStockNotifier
{
    private const THRESHOLD     = 10;
    private const CACHE_TTL_MIN = 60;   // Minimum minutes between repeat emails per product

    /**
     * Check products that dropped to or below the threshold after a stock change,
     * and send a single alert email to all admin users.
     *
     * @param  array<int, array{id: int, name: string, before: int, after: int}>  $changes
     */
    public static function check(array $changes): void
    {
        try {
            $triggered = [];

            foreach ($changes as $change) {
                $before = $change['before'];
                $after  = $change['after'];

                // Only fire if we just crossed the threshold (not already below before)
                if ($before > self::THRESHOLD && $after <= self::THRESHOLD) {
                    $cacheKey = 'low_stock_notified_' . $change['id'];

                    if (! Cache::has($cacheKey)) {
                        $triggered[] = $change['id'];
                        Cache::put($cacheKey, true, now()->addMinutes(self::CACHE_TTL_MIN));
                    }
                }
            }

            if (empty($triggered)) {
                return;
            }

            $products = Product::whereIn('product_id', $triggered)
                ->orderBy('stock')
                ->get();

            if ($products->isEmpty()) {
                return;
            }

            $admins = User::where('role', 'admin')->pluck('email')->filter()->values();

            if ($admins->isEmpty()) {
                return;
            }

            foreach ($admins as $email) {
                Mail::to($email)->send(new LowStockAlert($products, self::THRESHOLD));
            }
        } catch (\Throwable $e) {
            // Never let a notification failure crash the user flow
            Log::warning('LowStockNotifier failed: ' . $e->getMessage());
        }
    }
}
