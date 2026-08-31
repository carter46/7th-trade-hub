<?php

namespace App\Services\Domains;

use App\Models\DomainProvider;
use App\Modules\Admin\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

class DomainAuditLogger
{
    public function __construct(
        private AuditLogService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function log(string $action, ?Model $model = null, array $context = [], ?int $actorId = null): void
    {
        $this->audit->log(
            adminId: null,
            action: $action,
            model: $model,
            context: array_merge([
                'module' => 'domains',
                'actor_id' => $actorId ?? auth()->id(),
                'actor_type' => auth()->check() ? (auth()->user()?->hasRole('admin') ? 'admin' : 'user') : 'system',
            ], $this->sanitize($context)),
        );
    }

    public function providerConfigChanged(DomainProvider $provider, array $old, array $new, ?int $adminId): void
    {
        $this->audit->log(
            adminId: $adminId,
            action: 'domains.provider.updated',
            model: $provider,
            old: $this->sanitize($old),
            new: $this->sanitize($new),
            context: ['module' => 'domains'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function sanitize(array $context): array
    {
        $redact = ['api_token', 'api_key', 'password', 'credentials', 'token', 'secret'];

        foreach ($context as $key => $value) {
            if (in_array($key, $redact, true)) {
                $context[$key] = '[redacted]';
            } elseif (is_array($value)) {
                $context[$key] = $this->sanitize($value);
            }
        }

        return $context;
    }
}
