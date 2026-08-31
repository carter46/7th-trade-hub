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
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Modules\Admin\Http\Controllers\AuditLogController;
use App\Modules\Admin\Http\Controllers\CryptoSellController as AdminCryptoSellController;
use App\Modules\Admin\Http\Controllers\CryptoDepositWalletController;
use App\Modules\Admin\Http\Controllers\IncomingDepositController;
use App\Modules\Admin\Http\Controllers\OtcPricingController;
use App\Modules\Admin\Http\Controllers\EscrowController as AdminEscrowController;
use App\Modules\Admin\Http\Controllers\KycController as AdminKycController;
use App\Modules\Admin\Http\Controllers\ListingAdminController;
use App\Modules\Admin\Http\Controllers\SettingsController as AdminSettingsController;
use App\Modules\Admin\Http\Controllers\BlockchainMonitoringController;
use App\Modules\Admin\Http\Controllers\TrackingSettingsController;
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
use App\Modules\Wallet\Http\Controllers\BankAccountController;
use App\Modules\Wallet\Http\Controllers\CryptoSellController;
use App\Modules\Wallet\Http\Controllers\DepositController;
use App\Modules\Wallet\Http\Controllers\HistoryController;
use App\Modules\Wallet\Http\Controllers\KycController;
use App\Modules\Wallet\Http\Controllers\WalletController;
use App\Modules\Wallet\Http\Controllers\WithdrawalController;
use App\Http\Controllers\Webhooks\MonnifyWebhookController;
use App\Modules\Admin\Http\Controllers\ReconciliationController;
use App\Modules\Wallet\Services\CryptoPriceService;
use Illuminate\Support\Facades\Route;

if (app()->environment('local')) {
    Route::get('/dev/ui', [DevUiController::class, 'index'])->name('dev.ui');
}

Route::get('/', function (CryptoPriceService $prices, \App\Modules\Catalog\Services\CatalogBrowseService $browse, \App\Modules\Catalog\Services\CatalogContentResolver $catalogContent) {
    return view('pages.home', [
        'cryptoPrices' => $prices->getPrices(),
        'ecosystemItems' => $browse->homeEcosystemItems($catalogContent),
    ]);
})->name('home');

Route::post('/webhooks/monnify', MonnifyWebhookController::class)->name('webhooks.monnify');
Route::post('/webhooks/site-integrations/{integrationId}', \App\Http\Controllers\Webhooks\SiteIntegrationWebhookController::class)
    ->middleware('throttle:60,1')
    ->name('webhooks.site-integrations');

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
    $chat = app(\App\Services\Communications\LiveChat\LiveChatManager::class)->resolved();
    $contact = app(\App\Services\Communications\Contact\PlatformContactRepository::class)->all();
    $socials = app(\App\Services\Communications\Social\SocialLinkRepository::class)->enabled();

    return view('pages.contact', [
        'contact' => $contact,
        'socialLinks' => $socials,
        'liveChat' => $chat,
        'chatEnabled' => (bool) $chat['enabled'],
        'formattedAddress' => app(\App\Services\Communications\Contact\PlatformContactRepository::class)->formattedAddress(),
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

Route::middleware('marketplace.public')->group(function (): void {
    Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace');
    Route::get('/marketplace/suggestions', [MarketplaceController::class, 'suggestions'])
        ->middleware('throttle:60,1')
        ->name('marketplace.suggestions');
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
});
Route::redirect('/marketplace/web-services', '/services')->name('marketplace.web-services');
Route::get('/robots.txt', RobotsController::class)->name('robots');
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
Route::redirect('/document-templates', '/services/business-documents/receipt', 301)->name('document-templates');
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
        Route::get('/kyc', 'kyc')->name('.kyc');
        Route::get('/preferences', 'preferences')->name('.preferences');
        Route::get('/sessions', 'sessions')->name('.sessions');
        Route::delete('/sessions/{session}', 'revokeSession')->name('.sessions.destroy');
    });
    Route::get('/kyc', fn () => redirect()->route('dashboard.account.kyc'))->name('.kyc');
    Route::post('/kyc', [KycController::class, 'store'])->name('.kyc.store');
    Route::post('/wallet/create', [WalletController::class, 'create'])->name('.wallet.create');
    Route::get('/wallet', [DashboardController::class, 'wallet'])->name('.wallet');
    Route::middleware('has_wallet')->group(function () {
        Route::get('/deposit', [DepositController::class, 'index'])->name('.deposit.index');
        Route::get('/deposit/checkout', [DepositController::class, 'createCheckout'])->name('.deposit.create-checkout');
        Route::post('/deposit/checkout', [DepositController::class, 'storeCheckout'])
            ->middleware('throttle:10,1')
            ->name('.deposit.store-checkout');
        Route::get('/deposit/callback', [DepositController::class, 'callback'])->name('.deposit.callback');
        Route::get('/deposit/reserved', [DepositController::class, 'reservedAccount'])->name('.deposit.reserved');
        Route::get('/deposit/bank', [DepositController::class, 'createBank'])->name('.deposit.create-bank');
        Route::post('/deposit/bank', [DepositController::class, 'storeBank'])
            ->middleware('throttle:10,1')
            ->name('.deposit.store-bank');
        Route::get('/deposit/{funding}', [DepositController::class, 'show'])->name('.deposit.show');
        Route::get('/banks', [BankAccountController::class, 'index'])->name('.banks.index');
        Route::get('/banks/replace', [BankAccountController::class, 'replaceForm'])->name('.banks.replace');
        Route::post('/banks/replace/otp', [BankAccountController::class, 'sendOtp'])->middleware('throttle:5,10')->name('.banks.replace.otp');
        Route::post('/banks/replace/verify-otp', [BankAccountController::class, 'verifyOtp'])->middleware('throttle:10,10')->name('.banks.replace.verify-otp');
        Route::post('/banks/replace/resolve', [BankAccountController::class, 'resolve'])->middleware('throttle:10,1')->name('.banks.replace.resolve');
        Route::post('/banks/replace/confirm', [BankAccountController::class, 'confirm'])->middleware('throttle:5,1')->name('.banks.replace.confirm');
        Route::get('/crypto-sell', [CryptoSellController::class, 'index'])->name('.crypto-sell.index');
        Route::get('/crypto-sell/create', [CryptoSellController::class, 'create'])->name('.crypto-sell.create');
        Route::post('/crypto-sell', [CryptoSellController::class, 'store'])->name('.crypto-sell.store');
        Route::get('/crypto-sell/{cryptoSellRequest}', [CryptoSellController::class, 'show'])->name('.crypto-sell.show');
        Route::get('/crypto-sell/{cryptoSellRequest}/status', [CryptoSellController::class, 'status'])->name('.crypto-sell.status');
        Route::post('/crypto-sell/{cryptoSellRequest}/tx', [CryptoSellController::class, 'submitTx'])->name('.crypto-sell.tx');
        Route::post('/crypto-sell/{cryptoSellRequest}/cancel', [CryptoSellController::class, 'cancel'])->name('.crypto-sell.cancel');
        Route::post('/crypto-sell/{cryptoSellRequest}/refresh', [CryptoSellController::class, 'refreshQuote'])->name('.crypto-sell.refresh');
        Route::get('/withdrawal', [WithdrawalController::class, 'index'])->name('.withdrawal.index');
        Route::get('/withdrawal/create', [WithdrawalController::class, 'create'])->name('.withdrawal.create');
        Route::post('/withdrawal', [WithdrawalController::class, 'store'])->name('.withdrawal.store');
        Route::get('/withdrawal/{withdrawal}', [WithdrawalController::class, 'show'])->name('.withdrawal.show');
        Route::get('/history', [HistoryController::class, 'index'])->name('.history');
        Route::post('/checkout/{listing}', [CheckoutController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('.checkout.store');
    });
    Route::get('/exchange', [DashboardController::class, 'exchange'])->name('.exchange');
    Route::get('/social', [DashboardController::class, 'social'])->name('.social');
    Route::get('/documents', [DashboardController::class, 'documents'])->name('.documents');

    // Services (primary section; discover paths redirect)
    Route::get('/services', [\App\Http\Controllers\Dashboard\DiscoverServicesController::class, 'index'])->name('.services');
    Route::post('/services/domain-quote', [\App\Http\Controllers\Dashboard\DiscoverServicesController::class, 'domainQuote'])
        ->middleware(['verified', 'throttle:30,1'])
        ->name('.services.domain-quote');
    Route::get('/services/domain-tlds', [\App\Http\Controllers\Dashboard\DiscoverServicesController::class, 'domainTlds'])
        ->middleware(['verified'])
        ->name('.services.domain-tlds');
    Route::get('/services/product/{slug}/checkout', [\App\Http\Controllers\Dashboard\DiscoverServicesController::class, 'checkout'])->name('.services.checkout');
    Route::post('/services/product/{slug}/checkout', [\App\Http\Controllers\Dashboard\DiscoverServicesController::class, 'purchase'])
        ->middleware(['verified', 'throttle:10,1'])
        ->name('.services.purchase');
    Route::get('/services/product/{slug}/payment/callback', [\App\Http\Controllers\Dashboard\DiscoverServicesController::class, 'paymentCallback'])
        ->middleware(['verified', 'throttle:30,1'])
        ->name('.services.payment-callback');
    Route::get('/services/product/{slug}', [\App\Http\Controllers\Dashboard\DiscoverServicesController::class, 'product'])->name('.services.product');
    Route::get('/services/browse/{segment}', [\App\Http\Controllers\Dashboard\DiscoverServicesController::class, 'browse'])->name('.services.browse');
    Route::redirect('/discover/services', '/dashboard/services', 301)->name('.discover.services');
    Route::get('/service-orders', [DashboardController::class, 'serviceOrders'])->name('.service-orders');

    Route::get('/my-tools', [\App\Http\Controllers\Dashboard\MyToolsController::class, 'index'])->name('.my-tools');
    Route::get('/my-tools/domains', [\App\Http\Controllers\Dashboard\MyToolsController::class, 'domains'])->name('.my-tools.domains');
    Route::get('/my-tools/{tool}', [\App\Http\Controllers\Dashboard\MyToolsController::class, 'show'])->name('.my-tools.show');
    Route::post('/my-tools/{tool}/launch/admin', [\App\Http\Controllers\Dashboard\MyToolsController::class, 'launchAdmin'])
        ->middleware('throttle:10,1')
        ->name('.my-tools.launch-admin');
    Route::post('/my-tools/{tool}/credentials/password', [\App\Http\Controllers\Dashboard\MyToolsController::class, 'copyPassword'])
        ->middleware('throttle:10,1')
        ->name('.my-tools.password');

    Route::get('/my-domains', [\App\Http\Controllers\Dashboard\MyDomainsController::class, 'index'])->name('.my-domains');
    Route::get('/my-domains/{registration}', [\App\Http\Controllers\Dashboard\MyDomainsController::class, 'show'])->name('.my-domains.show');
    Route::put('/my-domains/{registration}/nameservers', [\App\Http\Controllers\Dashboard\MyDomainsController::class, 'updateNameservers'])
        ->middleware('throttle:10,1')
        ->name('.my-domains.nameservers.update');
    Route::post('/my-domains/{registration}/nameservers/defaults', [\App\Http\Controllers\Dashboard\MyDomainsController::class, 'applyDefaults'])
        ->middleware('throttle:10,1')
        ->name('.my-domains.nameservers.defaults');
    Route::post('/my-domains/{registration}/nameservers/sync', [\App\Http\Controllers\Dashboard\MyDomainsController::class, 'syncFromRegistrar'])
        ->middleware('throttle:10,1')
        ->name('.my-domains.nameservers.sync');

    Route::post('/services/product/{product:slug}/demo/{role}', \App\Http\Controllers\Dashboard\DemoLaunchController::class)
        ->whereIn('role', ['user', 'admin'])
        ->middleware('throttle:10,1')
        ->name('.services.demo-launch');

    // Marketplace (primary section; discover paths redirect)
    Route::get('/marketplace', [\App\Http\Controllers\Dashboard\DiscoverMarketplaceController::class, 'index'])->name('.marketplace');
    Route::get('/marketplace/{slug}/checkout', [\App\Http\Controllers\Dashboard\DiscoverMarketplaceController::class, 'checkout'])->name('.marketplace.checkout');
    Route::get('/marketplace/{slug}', [\App\Http\Controllers\Dashboard\DiscoverMarketplaceController::class, 'show'])->name('.marketplace.show');
    Route::redirect('/discover/marketplace', '/dashboard/marketplace', 301)->name('.discover.marketplace');
    Route::get('/discover/marketplace/{slug}/checkout', fn (string $slug) => redirect()->to(route('dashboard.marketplace.checkout', $slug), 301))->name('.discover.marketplace.checkout');
    Route::get('/discover/marketplace/{slug}', fn (string $slug) => redirect()->to(route('dashboard.marketplace.show', $slug), 301))->name('.discover.marketplace.show');

    Route::get('/listings', [DashboardController::class, 'listings'])->name('.listings');
    Route::get('/listings/create', [ListingController::class, 'create'])->name('.listings.create');
    Route::post('/listings', [ListingController::class, 'store'])->name('.listings.store');
    Route::get('/listings/{listing}/edit', [ListingController::class, 'edit'])->name('.listings.edit');
    Route::put('/listings/{listing}', [ListingController::class, 'update'])->name('.listings.update');
    Route::post('/listings/{listing}/revision', [ListingController::class, 'storeRevision'])->name('.listings.revision');
    Route::post('/listings/{listing}/submit', [ListingController::class, 'submitForReview'])->name('.listings.submit');
    Route::post('/listings/{listing}/archive', [ListingController::class, 'archive'])->name('.listings.archive');
    Route::post('/listings/{listing}/restore-archive', [ListingController::class, 'restoreArchive'])->name('.listings.restore-archive');
    Route::delete('/listings/{listing}', [ListingController::class, 'destroy'])->name('.listings.destroy');
    Route::get('/orders', [DashboardController::class, 'orders'])->name('.orders');
    Route::get('/sales', [DashboardController::class, 'sales'])->name('.sales');
    Route::post('/orders/{order}/confirm', [CheckoutController::class, 'confirmDelivery'])->name('.orders.confirm');
    Route::post('/orders/{order}/mark-delivered', [CheckoutController::class, 'markDelivered'])->name('.orders.mark-delivered');
    Route::post('/orders/{order}/dispute', [CheckoutController::class, 'openDispute'])->name('.orders.dispute');
    Route::post('/orders/{order}/review', [ReviewController::class, 'store'])->name('.orders.review');
    Route::get('/messages', [MessageController::class, 'index'])->name('.messages');
    Route::get('/messages/create', [MessageController::class, 'create'])->name('.messages.create');
    Route::post('/messages', [MessageController::class, 'store'])->name('.messages.store');
    Route::get('/messages/order/{order}', [MessageController::class, 'showOrder'])->name('.messages.order');
    Route::post('/messages/order/{order}/reply', [MessageController::class, 'replyOrder'])->name('.messages.order.reply');
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
    Route::get('/support/attachments/{attachment}', [SupportTicketController::class, 'downloadAttachment'])
        ->middleware('signed')
        ->name('.support.attachments.download');
    Route::get('/support/{ticket}', [SupportTicketController::class, 'show'])->name('.support.show');
    Route::post('/support/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('.support.reply');
});

Route::middleware(['auth', 'verified', 'role:admin|demo_finance|demo_compliance|demo_support|demo_moderator', 'throttle:60,1'])->prefix('admin')->name('admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('');
    Route::get('/overview/panel', [AdminDashboardController::class, 'overviewPanel'])->name('.overview.panel');
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
        Route::get('/users/{user}/tools', [UserManagementController::class, 'tools'])->name('.users.tools');
        Route::post('/users/{user}/tools/{tool}/setup', [UserManagementController::class, 'setupTool'])->name('.users.tools.setup');
        Route::post('/users/{user}/tools/{tool}/reconfigure', [UserManagementController::class, 'reconfigureTool'])->name('.users.tools.reconfigure');
        Route::post('/users/{user}/tools/{tool}/rotate', [UserManagementController::class, 'rotateToolCredentials'])->name('.users.tools.rotate');
        Route::post('/users/{user}/tools/{tool}/check', [UserManagementController::class, 'checkTool'])->name('.users.tools.check');
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

    // Personal user-notification inbox (distinct from admin system alerts at /admin/notifications).
    Route::get('/inbox', [UserNotificationController::class, 'index'])->name('.inbox');
    Route::post('/inbox/{notification}/read', [UserNotificationController::class, 'markRead'])->name('.inbox.read');
    Route::post('/inbox/read-all', [UserNotificationController::class, 'markAllRead'])->name('.inbox.read-all');

    Route::middleware('permission:compliance.manage')->group(function () {
        Route::get('/kyc', [AdminKycController::class, 'index'])->name('.kyc');
        Route::post('/kyc/requirement', [AdminKycController::class, 'updateRequirement'])->name('.kyc.requirement');
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
        Route::get('/crypto-sells/{cryptoSellRequest}', [AdminCryptoSellController::class, 'show'])->name('.crypto-sells.show');
        Route::post('/crypto-sells/{cryptoSellRequest}/approve', [AdminCryptoSellController::class, 'approve'])->name('.crypto-sells.approve');
        Route::post('/crypto-sells/{cryptoSellRequest}/reject', [AdminCryptoSellController::class, 'reject'])->name('.crypto-sells.reject');
        Route::get('/incoming-deposits', [IncomingDepositController::class, 'index'])->name('.incoming-deposits');
        Route::post('/incoming-deposits/{incomingCryptoTransaction}/ignore', [IncomingDepositController::class, 'ignore'])->name('.incoming-deposits.ignore');
        Route::post('/incoming-deposits/{incomingCryptoTransaction}/rematch', [IncomingDepositController::class, 'rematch'])->name('.incoming-deposits.rematch');
        Route::get('/crypto-wallets', [CryptoDepositWalletController::class, 'index'])->name('.crypto-wallets');
        Route::get('/crypto-wallets/treasury', [CryptoDepositWalletController::class, 'treasury'])->name('.crypto-wallets.treasury');
        Route::post('/crypto-wallets/treasury/refresh', [CryptoDepositWalletController::class, 'refreshTreasury'])->name('.crypto-wallets.treasury.refresh');
        Route::get('/crypto-wallets/create', [CryptoDepositWalletController::class, 'create'])->name('.crypto-wallets.create');
        Route::post('/crypto-wallets', [CryptoDepositWalletController::class, 'store'])->name('.crypto-wallets.store');
        Route::get('/crypto-wallets/{cryptoDepositWallet}/edit', [CryptoDepositWalletController::class, 'edit'])->name('.crypto-wallets.edit');
        Route::put('/crypto-wallets/{cryptoDepositWallet}', [CryptoDepositWalletController::class, 'update'])->name('.crypto-wallets.update');
        Route::delete('/crypto-wallets/{cryptoDepositWallet}', [CryptoDepositWalletController::class, 'destroy'])->name('.crypto-wallets.destroy');
        Route::get('/otc-pricing', [OtcPricingController::class, 'edit'])->name('.otc-pricing');
        Route::post('/otc-pricing', [OtcPricingController::class, 'update'])->name('.otc-pricing.update');
        Route::get('/withdrawals', [WithdrawalAdminController::class, 'index'])->name('.withdrawals');
        Route::post('/withdrawals/{withdrawal}/approve', [WithdrawalAdminController::class, 'approve'])->name('.withdrawals.approve');
        Route::post('/withdrawals/{withdrawal}/reject', [WithdrawalAdminController::class, 'reject'])->name('.withdrawals.reject');
        Route::post('/withdrawals/{withdrawal}/retry', [WithdrawalAdminController::class, 'retry'])->name('.withdrawals.retry');
        Route::get('/reconciliation', [ReconciliationController::class, 'index'])->name('.reconciliation');
        Route::post('/reconciliation/fundings/{funding}/fix', [ReconciliationController::class, 'fixFunding'])->name('.reconciliation.fix-funding');
        Route::post('/reconciliation/withdrawals/{withdrawal}/sync', [ReconciliationController::class, 'syncWithdrawal'])->name('.reconciliation.sync-withdrawal');
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
        Route::delete('/listings/trash', [\App\Modules\Admin\Http\Controllers\ListingAdminController::class, 'bulkDestroy'])->name('.listings.trash.destroy');
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
        Route::get('/platform-products/{platformProduct}/edit', [\App\Modules\Admin\Http\Controllers\PlatformProductAdminController::class, 'edit'])->name('.platform-products.edit');
        Route::put('/platform-products/{platformProduct}', [\App\Modules\Admin\Http\Controllers\PlatformProductAdminController::class, 'update'])->name('.platform-products.update');
        Route::post('/platform-products/{platformProduct}/toggle', [\App\Modules\Admin\Http\Controllers\PlatformProductAdminController::class, 'toggle'])->name('.platform-products.toggle');

        Route::get('/site-integrations', [\App\Http\Controllers\Admin\SiteIntegrationAdminController::class, 'index'])->name('.site-integrations');
        Route::get('/site-integrations/create', [\App\Http\Controllers\Admin\SiteIntegrationAdminController::class, 'create'])->name('.site-integrations.create');
        Route::post('/site-integrations', [\App\Http\Controllers\Admin\SiteIntegrationAdminController::class, 'store'])->name('.site-integrations.store');
        Route::get('/site-integrations/{siteIntegration}', [\App\Http\Controllers\Admin\SiteIntegrationAdminController::class, 'show'])->name('.site-integrations.show');
        Route::put('/site-integrations/{siteIntegration}', [\App\Http\Controllers\Admin\SiteIntegrationAdminController::class, 'update'])->name('.site-integrations.update');
        Route::post('/site-integrations/{siteIntegration}/rotate', [\App\Http\Controllers\Admin\SiteIntegrationAdminController::class, 'rotate'])->name('.site-integrations.rotate');
        Route::post('/site-integrations/{siteIntegration}/check', [\App\Http\Controllers\Admin\SiteIntegrationAdminController::class, 'check'])->name('.site-integrations.check');

        Route::get('/domain-providers', [\App\Http\Controllers\Admin\DomainProviderAdminController::class, 'index'])->name('.domain-providers');
        Route::get('/domain-providers/{domainProvider}/edit', [\App\Http\Controllers\Admin\DomainProviderAdminController::class, 'edit'])->name('.domain-providers.edit');
        Route::put('/domain-providers/{domainProvider}', [\App\Http\Controllers\Admin\DomainProviderAdminController::class, 'update'])->name('.domain-providers.update');
        Route::post('/domain-providers/{domainProvider}/test', [\App\Http\Controllers\Admin\DomainProviderAdminController::class, 'test'])->name('.domain-providers.test');

        Route::get('/service-categories', [\App\Modules\Admin\Http\Controllers\ServiceCategoryAdminController::class, 'index'])->name('.service-categories');
        Route::get('/service-categories/{serviceCategory}/edit', [\App\Modules\Admin\Http\Controllers\ServiceCategoryAdminController::class, 'edit'])->name('.service-categories.edit');
        Route::put('/service-categories/{serviceCategory}', [\App\Modules\Admin\Http\Controllers\ServiceCategoryAdminController::class, 'update'])->name('.service-categories.update');
        Route::post('/service-categories/{serviceCategory}/toggle', [\App\Modules\Admin\Http\Controllers\ServiceCategoryAdminController::class, 'toggle'])->name('.service-categories.toggle');
        Route::get('/services', [\App\Modules\Admin\Http\Controllers\ServiceAdminController::class, 'index'])->name('.services');
        Route::get('/services/{service}/edit', [\App\Modules\Admin\Http\Controllers\ServiceAdminController::class, 'edit'])->name('.services.edit');
        Route::put('/services/{service}', [\App\Modules\Admin\Http\Controllers\ServiceAdminController::class, 'update'])->name('.services.update');
        Route::post('/services/{service}/toggle', [\App\Modules\Admin\Http\Controllers\ServiceAdminController::class, 'toggle'])->name('.services.toggle');
        // Legacy platform-categories → service-categories
        Route::get('/platform-categories', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'platformCategories'])->name('.platform-categories');
        Route::get('/platform-categories/create', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'createPlatformCategory'])->name('.platform-categories.create');
        Route::post('/platform-categories', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'storePlatformCategory'])->name('.platform-categories.store');
        Route::get('/platform-categories/{platformCategory}/edit', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'editPlatformCategory'])->name('.platform-categories.edit');
        Route::put('/platform-categories/{platformCategory}', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'updatePlatformCategory'])->name('.platform-categories.update');
        Route::post('/platform-categories/{platformCategory}/toggle', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'togglePlatformCategory'])->name('.platform-categories.toggle');
        Route::get('/exchange-rates', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'exchangeRates'])->name('.exchange-rates');
        Route::get('/exchange-rates/coins', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'coinCatalog'])->name('.exchange-rates.coins');
        Route::get('/exchange-rates/coin-market', [\App\Modules\Admin\Http\Controllers\CatalogMetaAdminController::class, 'coinMarket'])->name('.exchange-rates.coin-market');
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
        Route::post('/settings/branding', [AdminSettingsController::class, 'updateBranding'])->name('.settings.branding');
        Route::post('/settings/contact', [AdminSettingsController::class, 'updateContact'])->name('.settings.contact');
        Route::post('/settings/social', [AdminSettingsController::class, 'updateSocial'])->name('.settings.social');
        Route::post('/settings/email', [AdminSettingsController::class, 'updateEmail'])->name('.settings.email');
        Route::post('/settings/test-mail', [AdminSettingsController::class, 'testMail'])->name('.settings.test-mail');
        Route::post('/settings/analytics', [AdminSettingsController::class, 'updateAnalytics'])->name('.settings.analytics');
        Route::post('/settings/analytics/test', [AdminSettingsController::class, 'testAnalyticsConnection'])->name('.settings.analytics.test');
        Route::get('/tracking', [TrackingSettingsController::class, 'index'])->name('.tracking');
        Route::post('/tracking/providers', [TrackingSettingsController::class, 'updateProviders'])->name('.tracking.providers');
        Route::post('/tracking/test', [TrackingSettingsController::class, 'testProvider'])->name('.tracking.test');
        Route::post('/tracking/scripts', [TrackingSettingsController::class, 'storeScript'])->name('.tracking.scripts.store');
        Route::put('/tracking/scripts/{script}', [TrackingSettingsController::class, 'updateScript'])->name('.tracking.scripts.update');
        Route::delete('/tracking/scripts/{script}', [TrackingSettingsController::class, 'destroyScript'])->name('.tracking.scripts.destroy');
        Route::post('/settings/google-identity', [AdminSettingsController::class, 'updateGoogleIdentity'])->name('.settings.google-identity');
        Route::post('/settings/google-identity/test', [AdminSettingsController::class, 'testGoogleIdentity'])->name('.settings.google-identity.test');
        Route::post('/settings/monnify', [AdminSettingsController::class, 'updateMonnify'])->name('.settings.monnify');
        Route::post('/settings/monnify/test', [AdminSettingsController::class, 'testMonnify'])->name('.settings.monnify.test');
        Route::get('/blockchain-monitoring', [BlockchainMonitoringController::class, 'index'])->name('.blockchain-monitoring');
        Route::post('/blockchain-monitoring', [BlockchainMonitoringController::class, 'update'])->name('.blockchain-monitoring.update');
        Route::post('/blockchain-monitoring/test', [BlockchainMonitoringController::class, 'test'])->name('.blockchain-monitoring.test');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('.audit-logs');
    });

    Route::middleware('permission:fees.manage')->group(function () {
        Route::get('/fees-limits', [\App\Modules\Admin\Http\Controllers\FeesLimitsController::class, 'index'])->name('.fees-limits');
        Route::post('/fees-limits', [\App\Modules\Admin\Http\Controllers\FeesLimitsController::class, 'update'])->name('.fees-limits.update');
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
