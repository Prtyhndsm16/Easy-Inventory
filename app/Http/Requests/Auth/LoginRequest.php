<?php

namespace App\Http\Requests\Auth;

use App\Support\AuditLogger;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private const MAX_LOGIN_ATTEMPTS = 3;

    private const LOGIN_LOCK_SECONDS = 3600;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey(), self::LOGIN_LOCK_SECONDS);

            AuditLogger::record('auth.login', 'failed', [
                'attempts' => RateLimiter::attempts($this->throttleKey()),
            ], request: $this, email: $this->string('email')->toString());

            if (RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_LOGIN_ATTEMPTS)) {
                $this->throwLockoutValidationException();
            }

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = Auth::user();

        if ($user?->isLocked()) {
            Auth::guard('web')->logout();

            AuditLogger::record('auth.login', 'locked_account', [
                'locked_at' => optional($user->locked_at)->toDateTimeString(),
            ], request: $this, email: $this->string('email')->toString());

            throw ValidationException::withMessages([
                'email' => 'This account is locked. Please contact an admin.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_LOGIN_ATTEMPTS)) {
            return;
        }

        $this->throwLockoutValidationException();
    }

    /**
     * Reject a login attempt while the account is temporarily locked.
     *
     * @throws ValidationException
     */
    private function throwLockoutValidationException(): never
    {
        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        AuditLogger::record('auth.login', 'locked_out', [
            'available_in_seconds' => $seconds,
        ], request: $this, email: $this->string('email')->toString());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')));
    }
}
