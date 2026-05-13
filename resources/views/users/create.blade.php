<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="section-kicker">Admin Access</p>
                <h2 class="section-title">
                    Add User Account
                </h2>
                <p class="section-subtitle">Create staff or admin access for the inventory system.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn-muted">
                Back to Users
            </a>
        </div>
    </x-slot>

    <div class="app-page">
        <div class="page-container-narrow">
            <section class="panel">
                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
                    @csrf

                    <div class="panel-body">
                    @include('users.partials.form-fields', ['managedUser' => $user])
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-5 py-4 sm:px-6">
                        <a href="{{ route('admin.users.index') }}" class="btn-muted">
                            Cancel
                        </a>
                        <button type="submit" class="btn-primary">
                            Create User
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
