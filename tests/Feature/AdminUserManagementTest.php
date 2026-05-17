<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user_with_default_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Staff',
            'email' => 'newstaff@example.com',
            'role' => 'staff',
        ])->assertRedirect(route('admin.users.index', absolute: false));

        $user = User::query()->where('email', 'newstaff@example.com')->firstOrFail();

        $this->assertSame('staff', $user->role);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_create_user_form_does_not_require_password_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Default password')
            ->assertDontSee('name="password"', false);
    }

    public function test_admin_can_lock_and_unlock_user_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($admin)
            ->patch(route('admin.users.lock', $staff))
            ->assertSessionHasNoErrors();

        $this->assertTrue($staff->refresh()->isLocked());
        $this->assertSame($admin->id, $staff->locked_by);

        $this->actingAs($admin)
            ->patch(route('admin.users.unlock', $staff))
            ->assertSessionHasNoErrors();

        $this->assertFalse($staff->refresh()->isLocked());
        $this->assertNull($staff->locked_by);
    }

    public function test_locked_user_cannot_log_in(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'locked_at' => now(),
        ]);

        $this->post('/login', [
            'email' => $staff->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_locked_signed_in_user_is_logged_out_from_protected_pages(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'locked_at' => now(),
        ]);

        $this->actingAs($staff)
            ->get(route('staff.dashboard'))
            ->assertRedirect(route('login', absolute: false));

        $this->assertGuest();
    }
}
