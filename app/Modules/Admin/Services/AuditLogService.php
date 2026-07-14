<?php

namespace App\Modules\Admin\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    public function log(
        ?int $adminId,
        string $action,
        ?Model $model = null,
        ?array $old = null,
        ?array $new = null,
        ?string $ip = null
    ): AuditLog {
        return AuditLog::create([
            'admin_id' => $adminId,
            'action' => $action,
            'model_type' => $model ? $model::class : null,
            'model_id' => $model?->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'ip' => $ip,
        ]);
    }
}
