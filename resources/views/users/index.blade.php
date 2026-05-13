<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-700">Admin Access</p>
                <h2 class="text-2xl font-semibold leading-tight text-gray-900">
                    User Management
                </h2>
            </div>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                Add User
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
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
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Users</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-950">{{ number_format($users->total()) }}</p>
                    <p class="mt-2 text-sm text-gray-500">All registered accounts in the system</p>
                </div>

                <div class="rounded-lg border border-blue-200 bg-blue-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-blue-700">Role Distribution</p>
                    <p class="mt-3 text-lg font-semibold text-blue-900">
                        {{ number_format($adminCount) }} admin / {{ number_format($staffCount) }} staff
                    </p>
                    <p class="mt-2 text-sm text-blue-700">Only admins can create new user accounts.</p>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-950">System Users</h3>
                    <p class="mt-1 text-sm text-gray-500">Create staff accounts here instead of public registration.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-normal text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Name</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Role</th>
                                <th class="px-6 py-3">Created</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($users as $user)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                        @if (auth()->id() === $user->id)
                                            <div class="text-xs text-emerald-700">Current account</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->role === 'admin' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">{{ $user->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user account?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center rounded-md border border-red-200 px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-50">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        No users found yet.
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
