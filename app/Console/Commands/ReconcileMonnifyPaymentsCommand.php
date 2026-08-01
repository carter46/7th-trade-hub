<?php

namespace App\Console\Commands;

use App\Models\WalletFunding;
use App\Models\Withdrawal;
use App\Modules\Wallet\Payments\Contracts\PaymentRailInterface;
use App\Modules\Wallet\Services\DepositCheckoutService;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Console\Command;

class ReconcileMonnifyPaymentsCommand extends Command
{
    protected $signature = 'monnify:reconcile {--minutes=30 : Age threshold for stuck records}';

    protected $description = 'Poll Monnify for stuck fundings and withdrawals';

    public function handle(
        PaymentRailInterface $rail,
        DepositCheckoutService $deposits,
        WalletService $wallets,
    ): int {
        if (! $rail->isConfigured()) {
            $this->warn('Monnify not configured.');

            return self::SUCCESS;
        }

        $cutoff = now()->subMinutes((int) $this->option('minutes'));

        $fundings = WalletFunding::query()
            ->whereNotNull('provider_payment_reference')
            ->whereIn('status', ['pending', 'processing'])
            ->where('created_at', '<', $cutoff)
            ->limit(50)
            ->get();

        foreach ($fundings as $funding) {
            try {
                $deposits->completeFromReturn($funding->provider_payment_reference);
                $this->line('Funding '.$funding->reference.' checked');
            } catch (\Throwable $e) {
                $this->error('Funding '.$funding->id.': '.$e->getMessage());
            }
        }

        $withdrawals = Withdrawal::query()
            ->whereNotNull('provider_payout_reference')
            ->whereIn('status', ['processing', 'approved'])
            ->where('updated_at', '<', $cutoff)
            ->limit(50)
            ->get();

        foreach ($withdrawals as $withdrawal) {
            try {
                $status = $rail->getTransferStatus($withdrawal->provider_payout_reference);
                $st = strtoupper((string) ($status['status'] ?? ''));
                $withdrawal->update(['provider_status' => $st]);
                if (in_array($st, ['SUCCESS', 'COMPLETED'], true)) {
                    $wallets->completeWithdrawalPayout($withdrawal);
                } elseif (in_array($st, ['FAILED', 'EXPIRED'], true)) {
                    $wallets->failWithdrawalPayout($withdrawal, 'Reconcile: '.$st);
                }
                $this->line('Withdrawal '.$withdrawal->reference.' → '.$st);
            } catch (\Throwable $e) {
                $this->error('Withdrawal '.$withdrawal->id.': '.$e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
