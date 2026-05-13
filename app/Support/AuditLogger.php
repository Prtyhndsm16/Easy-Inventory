<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AuditLogger
{
    /**
     * Store a non-blocking audit record. Logging failures must not break the user flow.
     *
     * @param  array<string, mixed>  $metadata
     */
    public static function record(
        string $event,
        ?string $status = null,
        array $metadata = [],
        ?Model $auditable = null,
        ?User $actor = null,
        ?Request $request = null,
        ?string $email = null,
    ): void {
        try {
            if (! Schema::hasTable('audit_logs')) {
                return;
            }

            $request ??= request();
            $actor ??= $request->user();
            $sessionId = $request->hasSession() ? $request->session()->getId() : null;

            AuditLog::create([
                'user_id' => $actor?->getKey(),
                'email' => $email ?? $actor?->email,
                'event' => $event,
                'status' => $status,
                'auditable_type' => $auditable?->getMorphClass(),
                'auditable_id' => $auditable?->getKey(),
                'route_name' => $request->route()?->getName(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id_hash' => $sessionId ? hash('sha256', $sessionId) : null,
                'metadata' => self::sanitizeMetadata($metadata),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Audit log write failed.', [
                'event' => $event,
                'status' => $status,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    private static function sanitizeMetadata(array $metadata): array
    {
        $blockedKeys = ['password', 'password_confirmation', 'current_password', 'token', '_token'];

        foreach ($blockedKeys as $key) {
            if (array_key_exists($key, $metadata)) {
                $metadata[$key] = '[redacted]';
            }
        }

        return $metadata;
    }
}
