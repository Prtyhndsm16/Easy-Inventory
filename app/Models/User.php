<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'locked_at',
        'locked_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'locked_at' => 'datetime',
        ];
    }

    /**
     * Normalize stored roles so copied/newline-padded values still authorize correctly.
     *
     * @return Attribute<string, string>
     */
    protected function role(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): string => strtolower(trim((string) $value)),
            set: fn (?string $value): string => strtolower(trim((string) $value)),
        );
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    public function dashboardRouteName(): string
    {
        return $this->isAdmin() ? 'admin.dashboard' : 'staff.dashboard';
    }
}
