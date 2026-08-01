<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBankAccount extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'account_number' => 'encrypted',
            'verified_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function maskedAccountNumber(): string
    {
        $num = (string) $this->account_number;
        if (strlen($num) <= 4) {
            return str_repeat('*', strlen($num));
        }

        return str_repeat('*', max(0, strlen($num) - 4)).substr($num, -4);
    }
}
