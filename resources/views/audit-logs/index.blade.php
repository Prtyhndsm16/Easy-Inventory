<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="section-kicker">Security</p>
                <h2 class="section-title">Audit Logs</h2>
                <p class="section-subtitle">Track authentication attempts, account changes, product changes, and session fingerprints.</p>
            </div>
        </div>
    </x-slot>

    <div class="app-page">
        <div class="page-container space-y-6">
            <section class="panel">
                <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid gap-4 p-4 sm:p-6 lg:grid-cols-[1fr_220px_180px_auto]">
                    <div class="form-field">
                        <label for="search" class="block text-sm font-semibold text-gray-700">Search logs</label>
                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ $search }}"
                            placeholder="Email, event, route, or IP"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                    </div>

                    <div class="form-field">
                        <label for="event" class="block text-sm font-semibold text-gray-700">Event</label>
                        <select id="event" name="event" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">All events</option>
                            @foreach ($events as $eventOption)
                                <option value="{{ $eventOption }}" @selected($event === $eventOption)>{{ $eventOption }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="status" class="block text-sm font-semibold text-gray-700">Status</label>
                        <select id="status" name="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">All statuses</option>
                            @foreach ($statuses as $statusOption)
                                <option value="{{ $statusOption }}" @selected($status === $statusOption)>{{ ucfirst($statusOption) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn-primary">Apply</button>
                        @if ($search !== '' || $event !== '' || $status !== '')
                            <a href="{{ route('admin.audit-logs.index') }}" class="btn-muted">Reset</a>
                        @endif
                    </div>
                </form>
            </section>

            <section class="table-shell">
                <div class="panel-header">
                    <h3 class="text-lg font-semibold text-gray-950">Recorded Activity</h3>
                    <p class="section-subtitle">{{ $logs->total() }} log{{ $logs->total() === 1 ? '' : 's' }} found.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Actor</th>
                                <th>Event</th>
                                <th>Status</th>
                                <th>Route</th>
                                <th>IP / Session</th>
                                <th>Metadata</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                @php
                                    $metadata = $log->metadata ? json_encode($log->metadata, JSON_UNESCAPED_SLASHES) : null;
                                @endphp
                                <tr>
                                    <td class="whitespace-nowrap text-gray-600">
                                        {{ $log->created_at->format('M d, Y h:i A') }}
                                    </td>
                                    <td>
                                        <div class="font-semibold text-gray-950">{{ $log->user?->name ?? 'Guest / Unknown' }}</div>
                                        <div class="text-xs text-gray-500">{{ $log->email ?? 'No email' }}</div>
                                    </td>
                                    <td class="font-semibold text-gray-950">{{ $log->event }}</td>
                                    <td>
                                        <span class="badge {{ $log->status === 'success' ? 'bg-emerald-100 text-emerald-700' : ($log->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800') }}">
                                            {{ $log->status ? ucfirst(str_replace('_', ' ', $log->status)) : 'Recorded' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-sm text-gray-700">{{ $log->route_name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ $log->method ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <div class="text-sm text-gray-700">{{ $log->ip_address ?? 'N/A' }}</div>
                                        <div class="max-w-40 truncate text-xs text-gray-500">{{ $log->session_id_hash ?? 'No session' }}</div>
                                    </td>
                                    <td class="max-w-sm">
                                        @if ($metadata)
                                            <code class="block max-w-xs truncate rounded bg-gray-100 px-2 py-1 text-xs text-gray-700" title="{{ $metadata }}">
                                                {{ $metadata }}
                                            </code>
                                        @else
                                            <span class="text-sm text-gray-500">None</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center">
                                        <p class="font-semibold text-gray-900">No audit logs yet.</p>
                                        <p class="mt-1 text-sm text-gray-500">New login and activity events will appear here after migration.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($logs->hasPages())
                    <div class="border-t border-gray-100 px-6 py-4">
                        {{ $logs->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
