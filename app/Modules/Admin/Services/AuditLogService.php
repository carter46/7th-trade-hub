<?php

namespace App\Modules\Admin\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditLogService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function log(
        ?int $adminId,
        string $action,
        ?Model $model = null,
        ?array $old = null,
        ?array $new = null,
        ?string $ip = null,
        array $context = [],
    ): AuditLog {
        $request = request();
        $userAgent = $context['user_agent'] ?? $request?->userAgent();
        $parsed = $this->parseUserAgent($userAgent);

        return AuditLog::create([
            'admin_id' => $adminId,
            'actor_id' => $context['actor_id'] ?? $adminId,
            'actor_type' => $context['actor_type'] ?? ($adminId ? 'admin' : null),
            'action' => $action,
            'module' => $context['module'] ?? $this->inferModule($action),
            'model_type' => $model ? $model::class : ($context['model_type'] ?? null),
            'model_id' => $model?->getKey() ?? ($context['model_id'] ?? null),
            'old_values' => $old,
            'new_values' => $new,
            'ip' => $ip ?? $request?->ip(),
            'user_agent' => $userAgent,
            'device' => $context['device'] ?? $parsed['device'],
            'browser' => $context['browser'] ?? $parsed['browser'],
            'country' => $context['country'] ?? null,
            'reason' => $context['reason'] ?? null,
            'correlation_id' => $context['correlation_id'] ?? $request?->header('X-Correlation-ID'),
            'request_id' => $context['request_id'] ?? $request?->header('X-Request-ID') ?? (string) Str::uuid(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function logFromRequest(Request $request, ?int $adminId, string $action, ?Model $model = null, ?array $old = null, ?array $new = null, array $context = []): AuditLog
    {
        return $this->log($adminId, $action, $model, $old, $new, $request->ip(), $context);
    }

    private function inferModule(string $action): ?string
    {
        $segment = explode('.', $action)[0] ?? null;

        return is_string($segment) && $segment !== '' ? $segment : null;
    }

    /**
     * @return array{device: string|null, browser: string|null}
     */
    private function parseUserAgent(?string $userAgent): array
    {
        if (! is_string($userAgent) || $userAgent === '') {
            return ['device' => null, 'browser' => null];
        }

        $device = str_contains($userAgent, 'Mobile') ? 'mobile' : 'desktop';
        $browser = 'unknown';
        foreach (['Edg', 'Chrome', 'Firefox', 'Safari', 'Opera'] as $name) {
            if (stripos($userAgent, $name) !== false) {
                $browser = strtolower($name === 'Edg' ? 'edge' : $name);
                break;
            }
        }

        return ['device' => $device, 'browser' => $browser];
    }
}
