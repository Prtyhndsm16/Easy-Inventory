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
            <a href="{{ route('admin.users.index') }}" class="btn-muted w-full sm:w-auto">
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

                    <div class="form-actions-end">
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
