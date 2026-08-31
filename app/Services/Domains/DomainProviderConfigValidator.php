<?php

namespace App\Services\Domains;

use App\Models\DomainProvider;
use Illuminate\Validation\ValidationException;

class DomainProviderConfigValidator
{
    /**
     * @throws ValidationException
     */
    public function assertValidConfiguration(): void
    {
        $enabled = DomainProvider::query()->where('enabled', true)->get();

        if ($enabled->isEmpty()) {
            return;
        }

        $defaults = $enabled->where('is_default', true);
        if ($defaults->count() !== 1) {
            throw ValidationException::withMessages([
                'is_default' => 'Exactly one enabled provider must be marked as default.',
            ]);
        }

        $priorities = $enabled
            ->reject(fn (DomainProvider $p) => $p->is_default)
            ->pluck('fallback_priority')
            ->filter(fn ($p) => $p !== null);

        if ($priorities->count() !== $priorities->unique()->count()) {
            throw ValidationException::withMessages([
                'fallback_priority' => 'Fallback priorities must be unique among enabled non-default providers.',
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    public function validateSave(DomainProvider $provider, bool $enabled, bool $isDefault, ?int $fallbackPriority): void
    {
        if ($enabled && $isDefault && $fallbackPriority !== null) {
            throw ValidationException::withMessages([
                'fallback_priority' => 'Default provider cannot have a fallback priority.',
            ]);
        }

        if ($enabled && ! $isDefault && $fallbackPriority !== null) {
            $conflict = DomainProvider::query()
                ->where('enabled', true)
                ->where('is_default', false)
                ->where('id', '!=', $provider->id)
                ->where('fallback_priority', $fallbackPriority)
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'fallback_priority' => 'Fallback priority must be unique among enabled non-default providers.',
                ]);
            }
        }
    }
}
