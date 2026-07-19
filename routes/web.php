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
Route::get('/help', function () {
    return view('pages.help', [
        'categories' => config('help.categories', []),
        'faqs' => config('help.faqs', []),
    ]);
})->name('help');
Route::get('/legal', function (\Illuminate\Http\Request $request) {
    $doc = $request->string('doc')->toString() ?: 'terms';
    $documents = config('legal.documents', []);
    if (! isset($documents[$doc])) {
        $doc = 'terms';
    }

    return view('pages.legal', [
        'activeDoc' => $doc,
        'document' => $documents[$doc] ?? [],
    ]);
})->name('legal');
Route::redirect('/terms', '/legal?doc=terms')->name('terms');
Route::redirect('/privacy', '/legal?doc=privacy')->name('privacy');

Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace');
Route::get('/marketplace/suggestions', [MarketplaceController::class, 'suggestions'])
    ->middleware('throttle:60,1')
    ->name('marketplace.suggestions');
Route::redirect('/marketplace/web-services', '/services')->name('marketplace.web-services');
Route::get('/marketplace/{slug}', [MarketplaceController::class, 'show'])->name('marketplace.show');
Route::get('/marketplace/{slug}/checkout', [MarketplaceController::class, 'checkout'])
    ->middleware('auth')
    ->name('marketplace.checkout');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/services', [\App\Modules\Catalog\Http\Controllers\ServiceController::class, 'index'])->name('services');
Route::get('/services/{type}/{productSlug}', [\App\Modules\Catalog\Http\Controllers\ServiceController::class, 'show'])
    ->where('type', '[a-z0-9_]+')
    ->where('productSlug', '[A-Za-z0-9\-_]+')
    ->name('services.show');
// One segment: group slug, type key, or legacy product slug (301)
Route::get('/services/{segment}', [\App\Modules\Catalog\Http\Controllers\ServiceController::class, 'segment'])
    ->name('services.segment');
Route::get('/exchange', \App\Modules\Catalog\Http\Controllers\ExchangePageController::class)->name('exchange');
Route::get('/templates', [\App\Modules\Catalog\Http\Controllers\TemplateController::class, 'index'])->name('templates');
Route::get('/templates/{slug}', [\App\Modules\Catalog\Http\Controllers\TemplateController::class, 'show'])->name('templates.show');
Route::redirect('/document-templates', '/templates')->name('document-templates');
Route::get('/website-listings', [\App\Modules\Catalog\Http\Controllers\WebsiteListingController::class, 'index'])->name('website-listings');
Route::get('/website-listings/{slug}', [\App\Modules\Catalog\Http\Controllers\WebsiteListingController::class, 'show'])->name('website-listings.show');
Route::get('/checkout/platform/{slug}', [\App\Modules\Catalog\Http\Controllers\PlatformCheckoutController::class, 'show'])
    ->middleware('auth')
    ->name('checkout.platform.show');
Route::post('/checkout/platform/{slug}', [\App\Modules\Catalog\Http\Controllers\PlatformCheckoutController::class, 'store'])
    ->middleware(['auth', 'verified', 'has_wallet', 'throttle:10,1'])
    ->name('checkout.platform.store');
Route::post('/favorites/toggle', [\App\Modules\Catalog\Http\Controllers\FavoriteController::class, 'toggle'])
    ->middleware(['auth', 'verified', 'throttle:30,1'])
    ->name('favorites.toggle');
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
        Route::post('/checkout/{listing}', [CheckoutController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('.checkout.store');
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
    Route::get('/platform-products', [\App\Modules\Admin\Http\Controllers\PlatformProductAdminController::class, 'index'])->name('.platform-products');
    Route::get('/platform-products/create', [\App\Modules\Admin\Http\Controllers\PlatformProductAdminController::class, 'create'])->name('.platform-products.create');
    Route::post('/platform-products', [\App\Modules\Admin\Http\Controllers\PlatformProductAdminController::class, 'store'])->name('.platform-products.store');
    Route::get('/platform-products/{platformProduct}/edit', [\App\Modules\Admin\Http\Controllers\PlatformProductAdminController::class, 'edit'])->name('.platform-products.edit');
    Route::put('/platform-products/{platformProduct}', [\App\Modules\Admin\Http\Controllers\PlatformProductAdminController::class, 'update'])->name('.platform-products.update');
    Route::delete('/platform-products/{platformProduct}', [\App\Modules\Admin\Http\Controllers\PlatformProductAdminController::class, 'destroy'])->name('.platform-products.destroy');
    Route::get('/marketplace-categories', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'marketplaceCategories'])->name('.marketplace-categories');
    Route::post('/marketplace-categories', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'storeMarketplaceCategory'])->name('.marketplace-categories.store');
    Route::post('/marketplace-categories/{category}/toggle', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'toggleMarketplaceCategory'])->name('.marketplace-categories.toggle');
    Route::get('/platform-categories', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'platformCategories'])->name('.platform-categories');
    Route::post('/platform-categories', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'storePlatformCategory'])->name('.platform-categories.store');
    Route::put('/platform-categories/{platformCategory}', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'updatePlatformCategory'])->name('.platform-categories.update');
    Route::post('/platform-categories/{platformCategory}/toggle', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'togglePlatformCategory'])->name('.platform-categories.toggle');
    Route::get('/catalog-pages', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'catalogPages'])->name('.catalog-pages');
    Route::post('/catalog-pages', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'upsertCatalogPage'])->name('.catalog-pages.upsert');
    Route::get('/exchange-rates', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'exchangeRates'])->name('.exchange-rates');
    Route::post('/exchange-rates', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'storeExchangeRate'])->name('.exchange-rates.store');
    Route::delete('/exchange-rates/{exchangeRate}', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'destroyExchangeRate'])->name('.exchange-rates.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
