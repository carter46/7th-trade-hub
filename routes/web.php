<?php

use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminSearchController;
use App\Http\Controllers\Admin\AdministratorController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\MediaLibraryController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Account\AccountController;
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
        'searchIndex' => \App\Support\HelpContent::searchIndex(),
    ]);
})->name('help');
Route::get('/help/{slug}', function (string $slug) {
    $article = \App\Support\HelpContent::find($slug);
    abort_unless($article, 404);

    return view('pages.help-article', [
        'article' => $article,
        'slug' => $slug,
    ]);
})->where('slug', '[a-z0-9\-]+')->name('help.article');
Route::get('/contact', function () {
    $provider = strtolower(trim((string) \App\Models\SystemSetting::get('live_chat_provider', 'none')));
    $smartsuppKey = trim((string) \App\Models\SystemSetting::get('smartsupp_key', ''));
    $jivoId = trim((string) \App\Models\SystemSetting::get('jivo_widget_id', ''));
    $chatEnabled = ($provider === 'smartsupp' && $smartsuppKey !== '')
        || ($provider === 'jivo' && $jivoId !== '');

    return view('pages.contact', [
        'contactPhone' => \App\Models\SystemSetting::get('contact_phone', ''),
        'contactEmail' => \App\Models\SystemSetting::get('contact_email', ''),
        'contactEmailAlt' => \App\Models\SystemSetting::get('contact_email_alt', ''),
        'liveChatProvider' => $provider,
        'chatEnabled' => $chatEnabled,
    ]);
})->name('contact');
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
Route::get('/marketplace/{slug}/checkout', [MarketplaceController::class, 'checkout'])
    ->middleware('auth')
    ->where('slug', '[A-Za-z0-9\-_]+')
    ->name('marketplace.checkout');
Route::get('/marketplace/{category}/{product}', [MarketplaceController::class, 'pair'])
    ->where('category', '[a-z0-9\-_]+')
    ->where('product', '[a-z0-9\-_]+')
    ->name('marketplace.product');
Route::get('/marketplace/{segment}', [MarketplaceController::class, 'segment'])
    ->where('segment', '[A-Za-z0-9\-_]+')
    ->name('marketplace.show');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/services', [\App\Modules\Catalog\Http\Controllers\ServiceController::class, 'index'])->name('services');
// Nested product: /services/{category}/{service}/{product}
Route::get('/services/{category}/{service}/{productSlug}', [\App\Modules\Catalog\Http\Controllers\ServiceController::class, 'nestedShow'])
    ->where('category', '[a-z0-9\-_]+')
    ->where('service', '[a-z0-9\-_]+')
    ->where('productSlug', '[A-Za-z0-9\-_]+')
    ->name('services.nested.show');
// Two segments: category+service listing OR legacy type+product (same URI; pair() disambiguates).
// Register type name first so inbound binds {category}/{service}; show name kept for product URL generation.
Route::get('/services/{category}/{service}', [\App\Modules\Catalog\Http\Controllers\ServiceController::class, 'pair'])
    ->where('category', '[a-z0-9\-_]+')
    ->where('service', '[A-Za-z0-9\-_]+')
    ->name('services.type');
Route::get('/services/{type}/{productSlug}', [\App\Modules\Catalog\Http\Controllers\ServiceController::class, 'pair'])
    ->where('type', '[a-z0-9\-_]+')
    ->where('productSlug', '[A-Za-z0-9\-_]+')
    ->name('services.show');
// One segment: group slug, type key (301 → nested), or legacy product slug (301)
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
    Route::prefix('account')->name('.account')->controller(AccountController::class)->group(function () {
        Route::get('/profile', 'profile')->name('.profile');
        Route::patch('/profile', 'updateProfile')->name('.profile.update');
        Route::get('/security', 'security')->name('.security');
        Route::delete('/security', 'destroy')->name('.destroy');
        Route::get('/notifications', 'notifications')->name('.notifications');
        Route::get('/preferences', 'preferences')->name('.preferences');
        Route::get('/sessions', 'sessions')->name('.sessions');
        Route::delete('/sessions/{session}', 'revokeSession')->name('.sessions.destroy');
    });
    Route::get('/kyc', [KycController::class, 'show'])->name('.kyc');
    Route::post('/kyc', [KycController::class, 'store'])->name('.kyc.store');
    Route::post('/wallet/create', [WalletController::class, 'create'])->name('.wallet.create');
    Route::get('/wallet', [DashboardController::class, 'wallet'])->name('.wallet');
    Route::middleware('has_wallet')->group(function () {
        Route::get('/deposit', [DepositController::class, 'index'])->name('.deposit.index');
        Route::get('/deposit/bank', [DepositController::class, 'createBank'])->name('.deposit.create-bank');
        Route::post('/deposit/bank', [DepositController::class, 'storeBank'])
            ->middleware('throttle:10,1')
            ->name('.deposit.store-bank');
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
    Route::get('/discover/marketplace', [\App\Http\Controllers\Dashboard\DiscoverMarketplaceController::class, 'index'])->name('.discover.marketplace');
    Route::get('/discover/marketplace/{slug}', [\App\Http\Controllers\Dashboard\DiscoverMarketplaceController::class, 'show'])->name('.discover.marketplace.show');
    Route::get('/discover/services', [\App\Http\Controllers\Dashboard\DiscoverServicesController::class, 'index'])->name('.discover.services');
    Route::get('/listings', [DashboardController::class, 'listings'])->name('.listings');
    Route::get('/listings/create', [ListingController::class, 'create'])->name('.listings.create');
    Route::post('/listings', [ListingController::class, 'store'])->name('.listings.store');
    Route::get('/listings/{listing}/edit', [ListingController::class, 'edit'])->name('.listings.edit');
    Route::put('/listings/{listing}', [ListingController::class, 'update'])->name('.listings.update');
    Route::post('/listings/{listing}/revision', [ListingController::class, 'storeRevision'])->name('.listings.revision');
    Route::post('/listings/{listing}/submit', [ListingController::class, 'submitForReview'])->name('.listings.submit');
    Route::post('/listings/{listing}/archive', [ListingController::class, 'archive'])->name('.listings.archive');
    Route::post('/listings/{listing}/restore-archive', [ListingController::class, 'restoreArchive'])->name('.listings.restore-archive');
    Route::get('/orders', [DashboardController::class, 'orders'])->name('.orders');
    Route::get('/sales', [DashboardController::class, 'sales'])->name('.sales');
    Route::post('/orders/{order}/confirm', [CheckoutController::class, 'confirmDelivery'])->name('.orders.confirm');
    Route::post('/orders/{order}/mark-delivered', [CheckoutController::class, 'markDelivered'])->name('.orders.mark-delivered');
    Route::post('/orders/{order}/dispute', [CheckoutController::class, 'openDispute'])->name('.orders.dispute');
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

Route::middleware(['auth', 'verified', 'role:admin|demo_finance|demo_compliance|demo_support|demo_moderator', 'throttle:60,1'])->prefix('admin')->name('admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('');
    Route::get('/search', AdminSearchController::class)->name('.search');
    Route::prefix('account')->name('.account')->controller(AccountController::class)->group(function () {
        Route::get('/profile', 'profile')->name('.profile');
        Route::patch('/profile', 'updateProfile')->name('.profile.update');
        Route::get('/security', 'security')->name('.security');
        Route::get('/notifications', 'notifications')->name('.notifications');
        Route::get('/preferences', 'preferences')->name('.preferences');
        Route::get('/sessions', 'sessions')->name('.sessions');
        Route::delete('/sessions/{session}', 'revokeSession')->name('.sessions.destroy');
    });
    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('.users');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('.users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('.users.store');
        Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('.users.show');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('.users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('.users.update');
        Route::get('/users/{user}/wallet', [UserManagementController::class, 'wallet'])->name('.users.wallet');
        Route::get('/users/{user}/transactions', [UserManagementController::class, 'transactions'])->name('.users.transactions');
        Route::get('/users/{user}/orders', [UserManagementController::class, 'orders'])->name('.users.orders');
        Route::get('/users/{user}/listings', [UserManagementController::class, 'listings'])->name('.users.listings');
        Route::get('/users/{user}/escrows', [UserManagementController::class, 'escrows'])->name('.users.escrows');
        Route::get('/users/{user}/tickets', [UserManagementController::class, 'tickets'])->name('.users.tickets');
        Route::get('/users/{user}/activity', [UserManagementController::class, 'activity'])->name('.users.activity');
        Route::get('/users/{user}/security', [UserManagementController::class, 'security'])->name('.users.security');
        Route::post('/users/{user}/suspend', [UserManagementController::class, 'suspend'])->name('.users.suspend');
        Route::post('/users/{user}/restore', [UserManagementController::class, 'restore'])->name('.users.restore');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('.users.destroy');
        Route::post('/users/{user}/role', [UserManagementController::class, 'assignRole'])->name('.users.role');
        Route::post('/users/{user}/password-reset', [UserManagementController::class, 'sendPasswordReset'])->name('.users.password-reset');
        Route::post('/users/{user}/verify-email', [UserManagementController::class, 'verifyEmail'])->name('.users.verify-email');
        Route::post('/users/{user}/unverify-email', [UserManagementController::class, 'unverifyEmail'])->name('.users.unverify-email');
        Route::post('/users/{user}/provision-wallet', [UserManagementController::class, 'provisionWallet'])->name('.users.provision-wallet');
        Route::post('/users/{user}/impersonate', [ImpersonationController::class, 'start'])->name('.users.impersonate');
    });

    Route::middleware('permission:admins.manage')->group(function () {
        Route::get('/administrators', [AdministratorController::class, 'index'])->name('.administrators');
        Route::get('/administrators/create', [AdministratorController::class, 'create'])->name('.administrators.create');
        Route::post('/administrators', [AdministratorController::class, 'store'])->name('.administrators.store');
        Route::get('/administrators/{administrator}/edit', [AdministratorController::class, 'edit'])->name('.administrators.edit');
        Route::put('/administrators/{administrator}', [AdministratorController::class, 'update'])->name('.administrators.update');
        Route::post('/administrators/{administrator}/suspend', [AdministratorController::class, 'suspend'])->name('.administrators.suspend');
        Route::post('/administrators/{administrator}/restore', [AdministratorController::class, 'restore'])->name('.administrators.restore');
    });

    Route::get('/notifications', [UserNotificationController::class, 'index'])->name('.inbox');
    Route::post('/notifications/{notification}/read', [UserNotificationController::class, 'markRead'])->name('.inbox.read');
    Route::post('/notifications/read-all', [UserNotificationController::class, 'markAllRead'])->name('.inbox.read-all');

    Route::middleware('permission:compliance.manage')->group(function () {
        Route::get('/kyc', [AdminKycController::class, 'index'])->name('.kyc');
        Route::post('/kyc/{submission}/approve', [AdminKycController::class, 'approve'])->name('.kyc.approve');
        Route::post('/kyc/{submission}/reject', [AdminKycController::class, 'reject'])->name('.kyc.reject');
        Route::post('/kyc/{submission}/return-pending', [AdminKycController::class, 'returnToPending'])->name('.kyc.return-pending');
        Route::post('/kyc/{submission}/override', [AdminKycController::class, 'override'])->name('.kyc.override');
    });

    Route::middleware('permission:finance.manage')->group(function () {
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
        Route::get('/transactions', [AdminDashboardController::class, 'transactions'])->name('.transactions');
        Route::get('/wallet-adjustment', [WalletAdjustmentController::class, 'create'])->name('.wallet-adjustment');
        Route::post('/wallet-adjustment', [WalletAdjustmentController::class, 'store'])->name('.wallet-adjustment.store');
    });

    Route::middleware('permission:catalog.manage')->group(function () {
        Route::get('/listings', [\App\Modules\Admin\Http\Controllers\ListingAdminController::class, 'index'])->name('.listings');
        Route::get('/listings/pending', fn () => redirect()->route('admin.listings', ['status' => 'pending'], 301))->name('.listings.pending');
        Route::get('/listings/{listing}', [\App\Modules\Admin\Http\Controllers\ListingAdminController::class, 'show'])->withTrashed()->name('.listings.show');
        Route::post('/listings/{listing}/approve', [\App\Modules\Admin\Http\Controllers\ListingAdminController::class, 'approve'])->withTrashed()->name('.listings.approve');
        Route::post('/listings/{listing}/reject', [\App\Modules\Admin\Http\Controllers\ListingAdminController::class, 'reject'])->withTrashed()->name('.listings.reject');
        Route::post('/listings/{listing}/suspend', [\App\Modules\Admin\Http\Controllers\ListingAdminController::class, 'suspend'])->withTrashed()->name('.listings.suspend');
        Route::post('/listings/{listing}/restore', [\App\Modules\Admin\Http\Controllers\ListingAdminController::class, 'restore'])->withTrashed()->name('.listings.restore');
        Route::post('/listings/{listing}/feature', [\App\Modules\Admin\Http\Controllers\ListingAdminController::class, 'toggleFeature'])->withTrashed()->name('.listings.feature');
        Route::post('/listings/{listing}/duplicate', [\App\Modules\Admin\Http\Controllers\ListingAdminController::class, 'duplicate'])->withTrashed()->name('.listings.duplicate');
        Route::delete('/listings/{listing}', [\App\Modules\Admin\Http\Controllers\ListingAdminController::class, 'destroy'])->withTrashed()->name('.listings.destroy');

        Route::get('/marketplace-categories', [\App\Modules\Admin\Http\Controllers\MarketplaceCategoryAdminController::class, 'index'])->name('.marketplace-categories');
        Route::get('/marketplace-categories/create', [\App\Modules\Admin\Http\Controllers\MarketplaceCategoryAdminController::class, 'create'])->name('.marketplace-categories.create');
        Route::post('/marketplace-categories', [\App\Modules\Admin\Http\Controllers\MarketplaceCategoryAdminController::class, 'store'])->name('.marketplace-categories.store');
        Route::get('/marketplace-categories/{category}/edit', [\App\Modules\Admin\Http\Controllers\MarketplaceCategoryAdminController::class, 'edit'])->name('.marketplace-categories.edit');
        Route::put('/marketplace-categories/{category}', [\App\Modules\Admin\Http\Controllers\MarketplaceCategoryAdminController::class, 'update'])->name('.marketplace-categories.update');
        Route::post('/marketplace-categories/{category}/toggle', [\App\Modules\Admin\Http\Controllers\MarketplaceCategoryAdminController::class, 'toggle'])->name('.marketplace-categories.toggle');
        Route::delete('/marketplace-categories/{category}', [\App\Modules\Admin\Http\Controllers\MarketplaceCategoryAdminController::class, 'destroy'])->name('.marketplace-categories.destroy');

        Route::get('/marketplace-products', [\App\Modules\Admin\Http\Controllers\MarketplaceProductAdminController::class, 'index'])->name('.marketplace-products');
        Route::get('/marketplace-products/create', [\App\Modules\Admin\Http\Controllers\MarketplaceProductAdminController::class, 'create'])->name('.marketplace-products.create');
        Route::post('/marketplace-products', [\App\Modules\Admin\Http\Controllers\MarketplaceProductAdminController::class, 'store'])->name('.marketplace-products.store');
        Route::get('/marketplace-products/{marketplaceProduct}/edit', [\App\Modules\Admin\Http\Controllers\MarketplaceProductAdminController::class, 'edit'])->name('.marketplace-products.edit');
        Route::put('/marketplace-products/{marketplaceProduct}', [\App\Modules\Admin\Http\Controllers\MarketplaceProductAdminController::class, 'update'])->name('.marketplace-products.update');
        Route::post('/marketplace-products/{marketplaceProduct}/toggle', [\App\Modules\Admin\Http\Controllers\MarketplaceProductAdminController::class, 'toggle'])->name('.marketplace-products.toggle');
        Route::delete('/marketplace-products/{marketplaceProduct}', [\App\Modules\Admin\Http\Controllers\MarketplaceProductAdminController::class, 'destroy'])->name('.marketplace-products.destroy');

        Route::get('/platform-products', [\App\Modules\Admin\Http\Controllers\PlatformProductAdminController::class, 'index'])->name('.platform-products');
        Route::get('/platform-products/create', [\App\Modules\Admin\Http\Controllers\PlatformProductAdminController::class, 'create'])->name('.platform-products.create');
        Route::post('/platform-products', [\App\Modules\Admin\Http\Controllers\PlatformProductAdminController::class, 'store'])->name('.platform-products.store');
        Route::get('/platform-products/{platformProduct}/edit', [\App\Modules\Admin\Http\Controllers\PlatformProductAdminController::class, 'edit'])->name('.platform-products.edit');
        Route::put('/platform-products/{platformProduct}', [\App\Modules\Admin\Http\Controllers\PlatformProductAdminController::class, 'update'])->name('.platform-products.update');
        Route::delete('/platform-products/{platformProduct}', [\App\Modules\Admin\Http\Controllers\PlatformProductAdminController::class, 'destroy'])->name('.platform-products.destroy');
        Route::get('/service-categories', [\App\Modules\Admin\Http\Controllers\ServiceCategoryAdminController::class, 'index'])->name('.service-categories');
        Route::get('/service-categories/create', [\App\Modules\Admin\Http\Controllers\ServiceCategoryAdminController::class, 'create'])->name('.service-categories.create');
        Route::post('/service-categories', [\App\Modules\Admin\Http\Controllers\ServiceCategoryAdminController::class, 'store'])->name('.service-categories.store');
        Route::get('/service-categories/{serviceCategory}/edit', [\App\Modules\Admin\Http\Controllers\ServiceCategoryAdminController::class, 'edit'])->name('.service-categories.edit');
        Route::put('/service-categories/{serviceCategory}', [\App\Modules\Admin\Http\Controllers\ServiceCategoryAdminController::class, 'update'])->name('.service-categories.update');
        Route::post('/service-categories/{serviceCategory}/toggle', [\App\Modules\Admin\Http\Controllers\ServiceCategoryAdminController::class, 'toggle'])->name('.service-categories.toggle');
        Route::delete('/service-categories/{serviceCategory}', [\App\Modules\Admin\Http\Controllers\ServiceCategoryAdminController::class, 'destroy'])->name('.service-categories.destroy');
        Route::get('/services', [\App\Modules\Admin\Http\Controllers\ServiceAdminController::class, 'index'])->name('.services');
        Route::get('/services/create', [\App\Modules\Admin\Http\Controllers\ServiceAdminController::class, 'create'])->name('.services.create');
        Route::post('/services', [\App\Modules\Admin\Http\Controllers\ServiceAdminController::class, 'store'])->name('.services.store');
        Route::get('/services/{service}/edit', [\App\Modules\Admin\Http\Controllers\ServiceAdminController::class, 'edit'])->name('.services.edit');
        Route::put('/services/{service}', [\App\Modules\Admin\Http\Controllers\ServiceAdminController::class, 'update'])->name('.services.update');
        Route::post('/services/{service}/toggle', [\App\Modules\Admin\Http\Controllers\ServiceAdminController::class, 'toggle'])->name('.services.toggle');
        Route::delete('/services/{service}', [\App\Modules\Admin\Http\Controllers\ServiceAdminController::class, 'destroy'])->name('.services.destroy');
        // Legacy platform-categories → service-categories
        Route::get('/platform-categories', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'platformCategories'])->name('.platform-categories');
        Route::get('/platform-categories/create', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'createPlatformCategory'])->name('.platform-categories.create');
        Route::post('/platform-categories', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'storePlatformCategory'])->name('.platform-categories.store');
        Route::get('/platform-categories/{platformCategory}/edit', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'editPlatformCategory'])->name('.platform-categories.edit');
        Route::put('/platform-categories/{platformCategory}', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'updatePlatformCategory'])->name('.platform-categories.update');
        Route::post('/platform-categories/{platformCategory}/toggle', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'togglePlatformCategory'])->name('.platform-categories.toggle');
        Route::get('/exchange-rates', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'exchangeRates'])->name('.exchange-rates');
        Route::get('/exchange-rates/create', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'createExchangeRate'])->name('.exchange-rates.create');
        Route::post('/exchange-rates', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'storeExchangeRate'])->name('.exchange-rates.store');
        Route::get('/exchange-rates/{exchangeRate}/edit', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'editExchangeRate'])->name('.exchange-rates.edit');
        Route::put('/exchange-rates/{exchangeRate}', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'updateExchangeRate'])->name('.exchange-rates.update');
        Route::delete('/exchange-rates/{exchangeRate}', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'destroyExchangeRate'])->name('.exchange-rates.destroy');
    });

    Route::middleware('permission:support.manage')->group(function () {
        Route::get('/tickets', [SupportTicketAdminController::class, 'index'])->name('.tickets');
        Route::get('/tickets/create', [SupportTicketAdminController::class, 'create'])->name('.tickets.create');
        Route::post('/tickets', [SupportTicketAdminController::class, 'store'])->name('.tickets.store');
        Route::get('/tickets/{ticket}', [SupportTicketAdminController::class, 'show'])->name('.tickets.show');
        Route::post('/tickets/{ticket}/reply', [SupportTicketAdminController::class, 'reply'])->name('.tickets.reply');
        Route::post('/tickets/{ticket}/status', [SupportTicketAdminController::class, 'updateStatus'])->name('.tickets.status');
        Route::post('/tickets/{ticket}/assign', [SupportTicketAdminController::class, 'assign'])->name('.tickets.assign');
    });

    Route::middleware('permission:system.manage|catalog.manage')->group(function () {
        Route::get('/media/json', [MediaLibraryController::class, 'jsonIndex'])->name('.media.json');
        Route::post('/media', [MediaLibraryController::class, 'store'])->name('.media.store');
        Route::get('/media/{mediaAsset}/usages', [MediaLibraryController::class, 'usages'])->name('.media.usages');
    });

    Route::middleware('permission:system.manage')->group(function () {
        Route::get('/monitoring', [MonitoringController::class, 'index'])->name('.monitoring');
        Route::get('/media', [MediaLibraryController::class, 'index'])->name('.media');
        Route::patch('/media/{mediaAsset}', [MediaLibraryController::class, 'update'])->name('.media.update');
        Route::delete('/media/bulk', [MediaLibraryController::class, 'bulkDestroy'])->name('.media.bulk-destroy');
        Route::delete('/media/{mediaAsset}', [MediaLibraryController::class, 'destroy'])->name('.media.destroy');
        Route::post('/media/{mediaAsset}/replace', [MediaLibraryController::class, 'replace'])->name('.media.replace');
        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('.settings');
        Route::post('/settings', [AdminSettingsController::class, 'update'])->name('.settings.update');
        Route::post('/settings/test-mail', [AdminSettingsController::class, 'testMail'])->name('.settings.test-mail');
        Route::post('/settings/analytics', [AdminSettingsController::class, 'updateAnalytics'])->name('.settings.analytics');
        Route::post('/settings/analytics/test', [AdminSettingsController::class, 'testAnalyticsConnection'])->name('.settings.analytics.test');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('.audit-logs');
    });

    Route::middleware('permission:analytics.view|system.manage|finance.manage|support.manage|compliance.manage|catalog.manage')->group(function () {
        Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('.notifications');
        Route::post('/notifications/read-all', [AdminNotificationController::class, 'markAllRead'])->name('.notifications.read-all');
        Route::post('/notifications/{notification}/read', [AdminNotificationController::class, 'markRead'])->name('.notifications.read');
    });

    Route::middleware('permission:analytics.view|finance.manage|catalog.manage|support.manage|compliance.manage|users.manage')->group(function () {
        Route::get('/analytics', [AdminDashboardController::class, 'analytics'])->name('.analytics');
    });

    Route::get('/social', [AdminDashboardController::class, 'social'])->name('.social');
});

Route::middleware('auth')->group(function () {
    Route::post('/impersonation/leave', [ImpersonationController::class, 'leave'])->name('impersonation.leave');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/theme-preference', [\App\Http\Controllers\ThemePreferenceController::class, 'update'])
        ->middleware('throttle:30,1')
        ->name('theme.preference');
});

require __DIR__.'/auth.php';
