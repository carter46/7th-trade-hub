<?php

namespace App\Modules\Wallet\Payments\Contracts;

interface PaymentRailInterface
{
    public function isConfigured(): bool;

    /**
     * @param  array{amount: float|string, paymentReference: string, customerName: string, customerEmail: string, redirectUrl: string, paymentDescription?: string}  $payload
     * @return array{checkoutUrl: string, transactionReference: string, paymentReference: string, amount: float|string}
     */
    public function initializeCheckout(array $payload): array;

    public function verifyTransaction(string $paymentReference): array;

    /**
     * @return array{accountName: string, accountNumber: string, bankCode: string}
     */
    public function resolveAccount(string $accountNumber, string $bankCode): array;

    /**
     * @return list<array{name: string, code: string}>
     */
    public function listBanks(): array;

    public function getMerchantWalletBalance(): float;

    /**
     * @param  array{amount: float|string, reference: string, bankCode: string, accountNumber: string, accountName: string, narration?: string, currency?: string, async?: bool}  $payload
     */
    public function initiateTransfer(array $payload): array;

    public function getTransferStatus(string $reference): array;
}
