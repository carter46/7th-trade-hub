<?php

namespace App\Console\Commands;

use App\Enums\WalletHoldStatus;
use App\Models\User;
use App\Models\WalletHold;
use App\Modules\Marketplace\Services\NotificationService;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Console\Command;

class ExpireListingHoldsCommand extends Command
{
    protected $signature = 'wallet:expire-listing-holds';

    protected $description = 'Release expired listing collateral holds and notify sellers';

    public function handle(WalletService $wallets, NotificationService $notifications): int
    {
        $holds = WalletHold::query()
            ->where('reason_type', 'listing')
            ->where('status', WalletHoldStatus::Active->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($holds as $hold) {
            $wallets->releaseListingHold(
                (int) $hold->wallet_id,
                (int) $hold->reason_id,
                WalletHoldStatus::Expired
            );

            $wallet = $hold->wallet;
            $user = $wallet?->user;
            if ($user instanceof User) {
                $notifications->send(
                    $user,
                    'wallet',
                    __('Listing collateral released'),
                    __('A listing hold expired and ₦'.number_format((float) $hold->amount, 2).' is available again.'),
                    route('dashboard.wallet'),
                    ['database', 'mail']
                );
            }
        }

        $this->info('Expired '.$holds->count().' listing hold(s).');

        return self::SUCCESS;
    }
}
