<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = Str::limit(trim((string) $request->string('search')), 100, '');
        $event = (string) $request->string('event');
        $status = (string) $request->string('status');

        $query = AuditLog::query()
            ->with('user')
            ->latest('created_at');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('email', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('route_name', 'like', "%{$search}%");
            });
        }

        if ($event !== '') {
            $query->where('event', $event);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        return view('audit-logs.index', [
            'logs' => $query->paginate(20)->withQueryString(),
            'events' => AuditLog::query()->distinct()->orderBy('event')->pluck('event'),
            'statuses' => AuditLog::query()->whereNotNull('status')->distinct()->orderBy('status')->pluck('status'),
            'search' => $search,
            'event' => $event,
            'status' => $status,
        ]);
    }
}
