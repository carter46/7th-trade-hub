<?php

namespace App\Modules\Wallet\Services\Blockchain;

/**
 * Normalized inbound transfer from an explorer.
 *
 * @phpstan-type Transfer array{
 *   tx_hash: string,
 *   amount: float,
 *   block_height: ?int,
 *   confirmations: int,
 *   from_address: ?string,
 *   to_address: string,
 *   coin: string,
 *   network: string,
 *   token_contract: ?string,
 *   raw: array<string, mixed>
 * }
 */
interface ChainExplorerClient
{
    public function networkKey(): string;

    /**
     * @return list<Transfer>
     */
    public function fetchIncoming(string $address, string $coin, ?string $network = null): array;

    public function tipHeight(?string $network = null): ?int;

    public function healthCheck(): bool;
}
