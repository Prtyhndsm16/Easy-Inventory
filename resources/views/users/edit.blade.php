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
            <a href="{{ route('admin.users.index') }}" class="btn-muted">
                Back to Users
            </a>
        </div>
    </x-slot>

    <div class="app-page">
        <div class="page-container-narrow">
            <section class="panel">
                <form id="delete-user-form" method="POST" action="{{ route('admin.users.destroy', $managedUser) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>

                <form method="POST" action="{{ route('admin.users.update', $managedUser) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="panel-body">
                    @include('users.partials.form-fields', ['managedUser' => $managedUser])
                    </div>

                    <div class="flex flex-col gap-3 border-t border-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <button
                            type="submit"
                            form="delete-user-form"
                            onclick="return confirm('Delete this user account?');"
                            class="btn-danger"
                        >
                            Delete User
                        </button>

                        <div class="flex flex-wrap items-center gap-3">
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
