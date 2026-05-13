<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="section-kicker">Admin Access</p>
                <h2 class="section-title">
                    User Management
                </h2>
                <p class="section-subtitle">Manage staff accounts for the inventory system.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn-primary w-full sm:w-auto">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Add Staff
            </a>
        </div>
    </x-slot>

    <div class="app-page">
        <div class="page-container space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->has('delete'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ $errors->first('delete') }}
                </div>
            @endif

            <section class="grid gap-4 sm:grid-cols-2">
                <div class="stat-card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="stat-label">Total Users</p>
                            <p class="stat-value">{{ number_format($users->total()) }}</p>
                        </div>
                        <span class="stat-icon bg-emerald-100 text-emerald-700">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M7.5 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM13 17a5.5 5.5 0 0 0-11 0M14 8v5M16.5 10.5h-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </div>
                    <p class="stat-note">Staff accounts in the system</p>
                </div>

                <div class="stat-card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="stat-label">Staff Accounts</p>
                            <p class="mt-3 text-2xl font-semibold text-gray-950">
                                {{ number_format($staffCount) }} active staff
                            </p>
                        </div>
                        <span class="stat-icon bg-blue-100 text-blue-700">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M4 6h12M4 10h12M4 14h7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </div>
                    <p class="stat-note">Only staff users are shown in this list</p>
                </div>
            </section>

            <section class="table-shell">
                <div class="panel-header">
                    <h3 class="text-lg font-semibold text-gray-950">System Users</h3>
                    <p class="section-subtitle">Create staff accounts here instead of public registration.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>
                                        <div class="font-semibold text-gray-950">{{ $user->name }}</div>
                                        @if (auth()->id() === $user->id)
                                            <div class="text-xs font-medium text-emerald-700">Current account</div>
                                        @endif
                                    </td>
                                    <td class="text-gray-600">{{ $user->email }}</td>
                                    <td>
                                        <span class="badge bg-blue-100 text-blue-700">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="text-gray-600">{{ $user->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user account?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-50">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center">
                                        <p class="font-semibold text-gray-900">No staff users found.</p>
                                        <p class="mt-1 text-sm text-gray-500">Create a staff account to start managing access.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <div class="border-t border-gray-100 px-6 py-4">
                        {{ $users->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
