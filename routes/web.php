<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Dev\DevUiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitemapController;
use App\Modules\Admin\Http\Controllers\AuditLogController;
use App\Modules\Admin\Http\Controllers\CryptoSellController as AdminCryptoSellController;
use App\Modules\Admin\Http\Controllers\EscrowController as AdminEscrowController;
use App\Modules\Admin\Http\Controllers\KycController as AdminKycController;
use App\Modules\Admin\Http\Controllers\ListingAdminController;
use App\Modules\Admin\Http\Controllers\SettingsController as AdminSettingsController;
use App\Modules\Admin\Http\Controllers\SupportTicketAdminController;
use App\Modules\Admin\Http\Controllers\WalletAdjustmentController;
use App\Modules\Admin\Http\Controllers\WalletFundingController as AdminWalletFundingController;
use App\Modules\Admin\Http\Controllers\WithdrawalAdminController;
use App\Modules\Marketplace\Http\Controllers\CheckoutController;
use App\Modules\Marketplace\Http\Controllers\ListingController;
use App\Modules\Marketplace\Http\Controllers\MarketplaceController;
use App\Modules\Marketplace\Http\Controllers\MessageController;
use App\Modules\Marketplace\Http\Controllers\NotificationController as UserNotificationController;
use App\Modules\Marketplace\Http\Controllers\ReviewController;
use App\Modules\Marketplace\Http\Controllers\WatchlistController;
use App\Modules\Support\Http\Controllers\SupportTicketController;
use App\Modules\Wallet\Http\Controllers\CryptoSellController;
use App\Modules\Wallet\Http\Controllers\DepositController;
use App\Modules\Wallet\Http\Controllers\HistoryController;
use App\Modules\Wallet\Http\Controllers\KycController;
use App\Modules\Wallet\Http\Controllers\WalletController;
use App\Modules\Wallet\Http\Controllers\WithdrawalController;
use App\Modules\Wallet\Services\CryptoPriceService;
use Illuminate\Support\Facades\Route;

if (app()->environment('local')) {
    Route::get('/dev/ui', [DevUiController::class, 'index'])->name('dev.ui');
}

Route::get('/', function (CryptoPriceService $prices) {
    return view('pages.home', ['cryptoPrices' => $prices->getPrices()]);
})->name('home');

Route::view('/about', 'pages.about')->name('about');
Route::view('/help', 'pages.help')->name('help');
Route::view('/terms', 'pages.terms')->name('terms');
Route::view('/privacy', 'pages.privacy')->name('privacy');

Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace');
Route::get('/marketplace/{slug}', [MarketplaceController::class, 'show'])->name('marketplace.show');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::view('/marketplace/web-services', 'pages.marketplace-web-services')->name('marketplace.web-services');
Route::view('/services', 'pages.services')->name('services');
Route::view('/exchange', 'pages.exchange')->name('exchange');
Route::view('/templates', 'pages.templates')->name('templates');
Route::view('/document-templates', 'pages.document-templates')->name('document-templates');
Route::view('/website-listings', 'pages.website-listings')->name('website-listings');
Route::view('/code', 'pages.code')->name('code');
Route::get('/support', fn () => redirect()->route('login'))->name('support');
Route::get('/u/{username}', function (string $username) {
    $user = \App\Models\User::where('username', $username)->firstOrFail();

    return view('pages.user-profile', ['user' => $user]);
})->name('user.profile');

Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('');
    Route::get('/kyc', [KycController::class, 'show'])->name('.kyc');
    Route::post('/kyc', [KycController::class, 'store'])->name('.kyc.store');
    Route::post('/wallet/create', [WalletController::class, 'create'])->name('.wallet.create');
    Route::get('/wallet', [DashboardController::class, 'wallet'])->name('.wallet');
    Route::middleware('has_wallet')->group(function () {
        Route::get('/deposit', [DepositController::class, 'index'])->name('.deposit.index');
        Route::get('/deposit/bank', [DepositController::class, 'createBank'])->name('.deposit.create-bank');
        Route::post('/deposit/bank', [DepositController::class, 'storeBank'])->name('.deposit.store-bank');
        Route::get('/crypto-sell', [CryptoSellController::class, 'index'])->name('.crypto-sell.index');
        Route::get('/crypto-sell/create', [CryptoSellController::class, 'create'])->name('.crypto-sell.create');
        Route::post('/crypto-sell', [CryptoSellController::class, 'store'])->name('.crypto-sell.store');
        Route::post('/crypto-sell/{cryptoSellRequest}/refresh', [CryptoSellController::class, 'refreshQuote'])->name('.crypto-sell.refresh');
        Route::get('/withdrawal', [WithdrawalController::class, 'index'])->name('.withdrawal.index');
        Route::get('/withdrawal/create', [WithdrawalController::class, 'create'])->name('.withdrawal.create');
        Route::post('/withdrawal', [WithdrawalController::class, 'store'])->name('.withdrawal.store');
        Route::get('/history', [HistoryController::class, 'index'])->name('.history');
        Route::post('/checkout/{listing}', [CheckoutController::class, 'store'])->name('.checkout.store');
    });
    Route::get('/exchange', [DashboardController::class, 'exchange'])->name('.exchange');
    Route::get('/social', [DashboardController::class, 'social'])->name('.social');
    Route::get('/documents', [DashboardController::class, 'documents'])->name('.documents');
    Route::get('/listings', [DashboardController::class, 'listings'])->name('.listings');
    Route::get('/listings/create', [ListingController::class, 'create'])->name('.listings.create');
    Route::post('/listings', [ListingController::class, 'store'])->name('.listings.store');
    Route::get('/listings/{listing}/edit', [ListingController::class, 'edit'])->name('.listings.edit');
    Route::put('/listings/{listing}', [ListingController::class, 'update'])->name('.listings.update');
    Route::post('/listings/{listing}/revision', [ListingController::class, 'storeRevision'])->name('.listings.revision');
    Route::post('/listings/{listing}/submit', [ListingController::class, 'submitForReview'])->name('.listings.submit');
    Route::get('/orders', [DashboardController::class, 'orders'])->name('.orders');
    Route::post('/orders/{order}/confirm', [CheckoutController::class, 'confirmDelivery'])->name('.orders.confirm');
    Route::post('/orders/{order}/review', [ReviewController::class, 'store'])->name('.orders.review');
    Route::get('/messages', [MessageController::class, 'index'])->name('.messages');
    Route::get('/messages/create', [MessageController::class, 'create'])->name('.messages.create');
    Route::post('/messages', [MessageController::class, 'store'])->name('.messages.store');
    Route::get('/messages/{message}', [MessageController::class, 'show'])->name('.messages.show');
    Route::post('/messages/{message}/reply', [MessageController::class, 'reply'])->name('.messages.reply');
    Route::get('/notifications', [UserNotificationController::class, 'index'])->name('.notifications');
    Route::post('/notifications/{notification}/read', [UserNotificationController::class, 'markRead'])->name('.notifications.read');
    Route::post('/notifications/read-all', [UserNotificationController::class, 'markAllRead'])->name('.notifications.read-all');
    Route::get('/watchlist', [WatchlistController::class, 'index'])->name('.watchlist');
    Route::post('/watchlist/{listing}', [WatchlistController::class, 'toggle'])->name('.watchlist.toggle');
    Route::get('/support', [SupportTicketController::class, 'index'])->name('.support.index');
    Route::get('/support/create', [SupportTicketController::class, 'create'])->name('.support.create');
    Route::post('/support', [SupportTicketController::class, 'store'])->name('.support.store');
    Route::get('/support/{ticket}', [SupportTicketController::class, 'show'])->name('.support.show');
    Route::post('/support/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('.support.reply');
});

Route::middleware(['auth', 'verified', 'role:admin', 'throttle:60,1'])->prefix('admin')->name('admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('');
    Route::get('/users', [UserManagementController::class, 'index'])->name('.users');
    Route::post('/users/{user}/suspend', [UserManagementController::class, 'suspend'])->name('.users.suspend');
    Route::post('/users/{user}/role', [UserManagementController::class, 'assignRole'])->name('.users.role');
    Route::get('/kyc', [AdminKycController::class, 'index'])->name('.kyc');
    Route::post('/kyc/{submission}/approve', [AdminKycController::class, 'approve'])->name('.kyc.approve');
    Route::post('/kyc/{submission}/reject', [AdminKycController::class, 'reject'])->name('.kyc.reject');
    Route::get('/fundings', [AdminWalletFundingController::class, 'index'])->name('.fundings');
    Route::post('/fundings/{funding}/approve', [AdminWalletFundingController::class, 'approve'])->name('.fundings.approve');
    Route::post('/fundings/{funding}/reject', [AdminWalletFundingController::class, 'reject'])->name('.fundings.reject');
    Route::post('/fundings/{funding}/reverse', [AdminWalletFundingController::class, 'reverse'])->name('.fundings.reverse');
    Route::get('/fundings/{funding}/proof', [AdminWalletFundingController::class, 'downloadProof'])->name('.fundings.proof');
    Route::get('/crypto-sells', [AdminCryptoSellController::class, 'index'])->name('.crypto-sells');
    Route::post('/crypto-sells/{cryptoSellRequest}/approve', [AdminCryptoSellController::class, 'approve'])->name('.crypto-sells.approve');
    Route::post('/crypto-sells/{cryptoSellRequest}/reject', [AdminCryptoSellController::class, 'reject'])->name('.crypto-sells.reject');
    Route::get('/withdrawals', [WithdrawalAdminController::class, 'index'])->name('.withdrawals');
    Route::post('/withdrawals/{withdrawal}/approve', [WithdrawalAdminController::class, 'approve'])->name('.withdrawals.approve');
    Route::post('/withdrawals/{withdrawal}/reject', [WithdrawalAdminController::class, 'reject'])->name('.withdrawals.reject');
    Route::get('/escrows', [AdminEscrowController::class, 'index'])->name('.escrows');
    Route::post('/escrows/{escrow}/release', [AdminEscrowController::class, 'release'])->name('.escrows.release');
    Route::post('/escrows/{escrow}/refund', [AdminEscrowController::class, 'refund'])->name('.escrows.refund');
    Route::get('/listings/pending', [ListingAdminController::class, 'pending'])->name('.listings.pending');
    Route::post('/listings/{listing}/approve', [ListingAdminController::class, 'approve'])->name('.listings.approve');
    Route::post('/listings/{listing}/reject', [ListingAdminController::class, 'reject'])->name('.listings.reject');
    Route::get('/transactions', [AdminDashboardController::class, 'transactions'])->name('.transactions');
    Route::get('/listings', [AdminDashboardController::class, 'listings'])->name('.listings');
    Route::get('/social', [AdminDashboardController::class, 'social'])->name('.social');
    Route::get('/tickets', [SupportTicketAdminController::class, 'index'])->name('.tickets');
    Route::get('/tickets/{ticket}', [SupportTicketAdminController::class, 'show'])->name('.tickets.show');
    Route::post('/tickets/{ticket}/reply', [SupportTicketAdminController::class, 'reply'])->name('.tickets.reply');
    Route::post('/tickets/{ticket}/status', [SupportTicketAdminController::class, 'updateStatus'])->name('.tickets.status');
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('.settings');
    Route::post('/settings', [AdminSettingsController::class, 'update'])->name('.settings.update');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('.audit-logs');
    Route::get('/wallet-adjustment', [WalletAdjustmentController::class, 'create'])->name('.wallet-adjustment');
    Route::post('/wallet-adjustment', [WalletAdjustmentController::class, 'store'])->name('.wallet-adjustment.store');
    Route::get('/analytics', [AdminDashboardController::class, 'analytics'])->name('.analytics');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
