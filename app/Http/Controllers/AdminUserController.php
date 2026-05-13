<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminUserRequest;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::query()->where('role', 'staff')->latest()->paginate(10),
            'staffCount' => User::where('role', 'staff')->count(),
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'user' => new User(['role' => 'staff']),
        ]);
    }

    public function store(AdminUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        AuditLogger::record('user.created', 'success', [
            'target_user_id' => $user->id,
            'target_role' => $user->role,
        ], $user);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User account created successfully.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'managedUser' => $user,
        ]);
    }

    public function update(AdminUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->user()->is($user) && $validated['role'] !== 'admin') {
            return back()->withErrors([
                'role' => 'Your own account must remain an admin.',
            ])->withInput();
        }

        if ($user->isAdmin() && $validated['role'] !== 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors([
                'role' => 'At least one admin account must remain in the system.',
            ])->withInput();
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $changedFields = array_keys($user->getDirty());
        $user->save();

        AuditLogger::record('user.updated', 'success', [
            'target_user_id' => $user->id,
            'changed_fields' => $changedFields,
            'password_changed' => in_array('password', $changedFields, true),
        ], $user);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User account updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (request()->user()->is($user)) {
            return back()->withErrors([
                'delete' => 'You cannot delete your own account from user management.',
            ]);
        }

        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors([
                'delete' => 'At least one admin account must remain in the system.',
            ]);
        }

        AuditLogger::record('user.deleted', 'success', [
            'target_user_id' => $user->id,
            'target_email' => $user->email,
            'target_role' => $user->role,
        ], $user);

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User account deleted successfully.');
    }
}
