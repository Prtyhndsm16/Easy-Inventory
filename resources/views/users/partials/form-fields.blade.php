<div class="space-y-8">
    <section>
        <div class="mb-5 border-b border-gray-100 pb-4">
            <h3 class="text-base font-semibold text-gray-950">Account Details</h3>
            <p class="mt-1 text-sm text-gray-500">Use an active email address and choose the correct access level.</p>
        </div>

        <div class="form-grid">
            <div class="form-field">
                <x-input-label for="name" :value="__('Full Name')" />
                <x-text-input
                    id="name"
                    name="name"
                    type="text"
                    class="block w-full"
                    :value="old('name', $managedUser->name)"
                    placeholder="Example: Juan Dela Cruz"
                    required
                    autofocus
                    autocomplete="name"
                />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div class="form-field">
                <x-input-label for="email" :value="__('Email Address')" />
                <x-text-input
                    id="email"
                    name="email"
                    type="email"
                    class="block w-full"
                    :value="old('email', $managedUser->email)"
                    placeholder="name@example.com"
                    required
                    autocomplete="email"
                />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <div class="form-field">
                <x-input-label for="role" :value="__('Role')" />
                <select id="role" name="role" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                    <option value="staff" @selected(old('role', $managedUser->role) === 'staff')>Staff</option>
                    <option value="admin" @selected(old('role', $managedUser->role) === 'admin')>Admin</option>
                </select>
                <p class="form-hint">Staff can view inventory. Admins can manage products and users.</p>
                <x-input-error class="mt-2" :messages="$errors->get('role')" />
            </div>

            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                <p class="font-semibold">Access reminder</p>
                <p class="mt-1">Create admin accounts only for users who should manage inventory records and staff access.</p>
            </div>
        </div>
    </section>

    <section>
        <div class="mb-5 border-b border-gray-100 pb-4">
            <h3 class="text-base font-semibold text-gray-950">Password</h3>
            <p class="mt-1 text-sm text-gray-500">
                {{ $managedUser->exists ? 'Leave the password fields blank to keep the current password.' : 'Set a strong temporary password and share it with the user through a secure channel.' }}
            </p>
        </div>

        <div class="form-grid">
            <div class="form-field">
                <x-input-label for="password" :value="$managedUser->exists ? __('New Password (optional)') : __('Password')" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="block w-full"
                    autocomplete="new-password"
                    :required="! $managedUser->exists"
                />
                <x-input-error class="mt-2" :messages="$errors->get('password')" />
            </div>

            <div class="form-field">
                <x-input-label for="password_confirmation" :value="$managedUser->exists ? __('Confirm New Password') : __('Confirm Password')" />
                <x-text-input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    class="block w-full"
                    autocomplete="new-password"
                    :required="! $managedUser->exists"
                />
            </div>
        </div>
    </section>
</div>
