<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="section-kicker">Account Settings</p>
            <h2 class="section-title">
                {{ __('Profile') }}
            </h2>
            <p class="section-subtitle">Update your personal details, password, and account preferences.</p>
        </div>
    </x-slot>

    <div class="app-page">
        <div class="page-container space-y-6">
            <section class="panel">
                <div class="max-w-xl">
                    <div class="panel-body">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="max-w-xl">
                    <div class="panel-body">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="max-w-xl">
                    <div class="panel-body">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
