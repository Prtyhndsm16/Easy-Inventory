<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="section-kicker">Admin Access</p>
                <h2 class="section-title">
                    Edit User Account
                </h2>
                <p class="section-subtitle">Update access details or reset a user's password.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn-muted w-full sm:w-auto">
                Back to Users
            </a>
        </div>
    </x-slot>

    <div class="app-page">
        <div class="page-container-narrow space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->has('lock'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ $errors->first('lock') }}
                </div>
            @endif

            @if ($errors->has('delete'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ $errors->first('delete') }}
                </div>
            @endif

            <section class="panel">
                <form id="delete-user-form" method="POST" action="{{ route('admin.users.destroy', $managedUser) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>

                <form
                    id="lock-user-form"
                    method="POST"
                    action="{{ $managedUser->isLocked() ? route('admin.users.unlock', $managedUser) : route('admin.users.lock', $managedUser) }}"
                    class="hidden"
                >
                    @csrf
                    @method('PATCH')
                </form>

                <form method="POST" action="{{ route('admin.users.update', $managedUser) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="panel-body">
                    @include('users.partials.form-fields', ['managedUser' => $managedUser])
                    </div>

                    <div class="form-actions-between">
                        <div class="form-action-group">
                            <button
                                type="submit"
                                form="lock-user-form"
                                onclick="return confirm('{{ $managedUser->isLocked() ? 'Unlock this user account?' : 'Lock this user account? Locked users cannot log in.' }}');"
                                class="{{ $managedUser->isLocked() ? 'btn-muted' : 'btn-danger' }}"
                            >
                                {{ $managedUser->isLocked() ? 'Unlock Account' : 'Lock Account' }}
                            </button>

                            <button
                                type="submit"
                                form="delete-user-form"
                                onclick="return confirm('Delete this user account?');"
                                class="btn-danger"
                            >
                                Delete User
                            </button>
                        </div>

                        <div class="form-action-group">
                            <a href="{{ route('admin.users.index') }}" class="btn-muted">
                                Cancel
                            </a>
                            <button type="submit" class="btn-primary">
                                Update User
                            </button>
                        </div>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
