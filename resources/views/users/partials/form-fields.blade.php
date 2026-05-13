<div class="grid gap-6 md:grid-cols-2">
    <div>
        <x-input-label for="name" :value="__('Full Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $managedUser->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="email" :value="__('Email Address')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $managedUser->email)" required />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />
    </div>

    <div>
        <x-input-label for="role" :value="__('Role')" />
        <select id="role" name="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
            <option value="staff" @selected(old('role', $managedUser->role) === 'staff')>Staff</option>
            <option value="admin" @selected(old('role', $managedUser->role) === 'admin')>Admin</option>
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('role')" />
    </div>

    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
        <p class="font-medium text-gray-900">Account access</p>
        <p class="mt-1">Staff users get read-only inventory access. Admin users can manage products, users, and system setup.</p>
    </div>

    @if ($managedUser->exists)
        <div>
            <x-input-label for="password" :value="__('New Password (optional)')" />
            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" />
            <x-input-error class="mt-2" :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
        </div>
    @else
        <div class="md:col-span-2 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <p class="font-medium">Default password</p>
            <p class="mt-1">Newly created users will receive the default password <span class="font-semibold">password</span> and can change it later from their account settings.</p>
        </div>
    @endif
</div>
