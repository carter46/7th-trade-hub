<?php

namespace App\Console\Commands;

use App\Models\GatewayOperation;
use Illuminate\Console\Command;

class ReconcileGatewayWalletsCommand extends Command
{
    protected $signature = 'app:reconcile-gateway-wallets';

    protected $description = 'List pending or failed gateway wallet operations for manual reconciliation';

    public function handle(): int
    {
        $operations = GatewayOperation::query()
            ->whereIn('status', ['pending', 'failed'])
            ->orderByDesc('created_at')
            ->get();

        if ($operations->isEmpty()) {
            $this->info('No pending or failed gateway operations.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Key', 'Provider', 'Operation', 'Status', 'User', 'Wallet', 'Error'],
            $operations->map(fn ($op) => [
                $op->id,
                $op->idempotency_key,
                $op->provider,
                $op->operation,
                $op->status,
                $op->user_id,
                $op->wallet_id,
                $op->error_message,
            ])
        );

        $this->warn('Review gateway_operations and reconcile with provider dashboard before retrying.');

        return self::SUCCESS;
    }
}
