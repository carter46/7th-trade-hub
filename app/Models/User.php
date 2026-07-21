<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
        'country',
        'bio',
        'avatar',
        'terms_accepted_at',
        'profile_completed_at',
    ];

    protected $guarded = ['kyc_level', 'is_suspended'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_suspended' => 'boolean',
            'suspended_at' => 'datetime',
            'anonymized_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'profile_completed_at' => 'datetime',
        ];
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function kycSubmissions(): HasMany
    {
        return $this->hasMany(KycSubmission::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function suspend(?int $administratorId = null): bool
    {
        if ($this->is_suspended) {
            return true;
        }

        return $this->forceFill([
            'is_suspended' => true,
            'suspended_at' => now(),
            'suspended_by' => $administratorId,
        ])->save();
    }

    public function restoreAccess(): bool
    {
        return $this->forceFill([
            'is_suspended' => false,
            'suspended_at' => null,
            'suspended_by' => null,
        ])->save();
    }

    /**
     * Irreversibly scrub personal data while preserving financial and audit records.
     * Admins must never be anonymized.
     */
    public function anonymize(?int $administratorId = null): bool
    {
        if ($this->hasRole('admin') || $this->anonymized_at !== null) {
            return false;
        }

        return DB::transaction(function () use ($administratorId): bool {
            $id = $this->getKey();
            $tombstoneUsername = 'deleted_'.$id;

            $saved = $this->forceFill([
                'name' => 'Deleted User',
                'username' => $tombstoneUsername,
                'email' => "deleted+{$id}@invalid.local",
                'phone' => null,
                'country' => null,
                'bio' => null,
                'avatar' => null,
                'email_verified_at' => null,
                'remember_token' => null,
                // Plain string — hashed cast will hash once.
                'password' => Str::random(64),
                'is_suspended' => true,
                'suspended_at' => $this->suspended_at ?? now(),
                'suspended_by' => $administratorId ?? $this->suspended_by,
                'anonymized_at' => now(),
            ])->save();

            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $id)->delete();
            }

            if (Schema::hasTable('personal_access_tokens')) {
                DB::table('personal_access_tokens')
                    ->where('tokenable_type', self::class)
                    ->where('tokenable_id', $id)
                    ->delete();
            }

            return $saved;
        });
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->whereNull('read_at')->count();
    }

    public function hasApprovedKyc(int $level = 1): bool
    {
        return $this->kyc_level >= $level;
    }

    /**
     * Role-aware landing page after login/verification.
     */
    public function homeRoute(): string
    {
        return $this->hasRole('admin')
            ? route('admin', absolute: false)
            : route('dashboard', absolute: false);
    }
}
