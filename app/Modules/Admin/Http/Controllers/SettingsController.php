<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsProvider;
use App\Models\SystemSetting;
use App\Modules\Admin\Services\AuditLogService;
use App\Services\Analytics\Providers\GoogleAnalyticsProvider;
use App\Services\Analytics\Providers\MicrosoftClarityProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
        private GoogleAnalyticsProvider $googleAnalytics,
        private MicrosoftClarityProvider $clarity,
    ) {}

    public function index(): View
    {
        $mailConfig = config('mail');
        $defaultMailer = $mailConfig['default'] ?? 'smtp';
        $mailer = $mailConfig['mailers'][$defaultMailer] ?? [];

        return view('dashboard.admin.settings', [
            'platformFeePercent' => SystemSetting::get('platform_fee_percent', '2.5'),
            'withdrawalMinAmount' => SystemSetting::get('withdrawal_min_amount', '100'),
            'withdrawalMaxAmount' => SystemSetting::get('withdrawal_max_amount', '1000000'),
            'depositMinAmount' => SystemSetting::get('deposit_min_amount', '100'),
            'liveChatProvider' => SystemSetting::get('live_chat_provider', 'none'),
            'smartsuppKey' => SystemSetting::get('smartsupp_key', ''),
            'jivoWidgetId' => SystemSetting::get('jivo_widget_id', ''),
            'contactPhone' => SystemSetting::get('contact_phone', ''),
            'contactEmail' => SystemSetting::get('contact_email', ''),
            'contactEmailAlt' => SystemSetting::get('contact_email_alt', ''),
            'analyticsGoogle' => AnalyticsProvider::forProvider(AnalyticsProvider::PROVIDER_GOOGLE_ANALYTICS),
            'analyticsClarity' => AnalyticsProvider::forProvider(AnalyticsProvider::PROVIDER_MICROSOFT_CLARITY),
            'mailStatus' => [
                'mailer' => $defaultMailer,
                'host' => $mailer['host'] ?? config('mail.mailers.smtp.host'),
                'port' => $mailer['port'] ?? config('mail.mailers.smtp.port'),
                'encryption' => $mailer['encryption'] ?? config('mail.mailers.smtp.encryption'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
                'username_set' => filled($mailer['username'] ?? config('mail.mailers.smtp.username')),
                'password_set' => filled($mailer['password'] ?? config('mail.mailers.smtp.password')),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'platform_fee_percent' => ['required', 'numeric', 'min:0', 'max:50'],
            'withdrawal_min_amount' => ['required', 'numeric', 'min:1'],
            'withdrawal_max_amount' => ['required', 'numeric', 'min:1', 'gte:withdrawal_min_amount'],
            'deposit_min_amount' => ['required', 'numeric', 'min:1'],
            'live_chat_provider' => ['required', 'in:none,smartsupp,jivo'],
            'smartsupp_key' => ['nullable', 'string', 'max:255'],
            'jivo_widget_id' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_email_alt' => ['nullable', 'email', 'max:255'],
        ]);

        if ($validated['live_chat_provider'] === 'smartsupp' && blank($validated['smartsupp_key'] ?? null)) {
            return back()->withInput()->withErrors([
                'smartsupp_key' => 'Smartsupp key is required when Smartsupp is selected.',
            ]);
        }
        if ($validated['live_chat_provider'] === 'jivo' && blank($validated['jivo_widget_id'] ?? null)) {
            return back()->withInput()->withErrors([
                'jivo_widget_id' => 'Jivo widget ID is required when Jivo is selected.',
            ]);
        }

        $keys = [
            'platform_fee_percent',
            'withdrawal_min_amount',
            'withdrawal_max_amount',
            'deposit_min_amount',
            'live_chat_provider',
            'smartsupp_key',
            'jivo_widget_id',
            'contact_phone',
            'contact_email',
            'contact_email_alt',
        ];

        $old = [];
        foreach ($keys as $key) {
            $old[$key] = SystemSetting::get($key);
        }

        foreach ($keys as $key) {
            SystemSetting::set($key, (string) ($validated[$key] ?? ''));
        }

        $this->audit->log(auth()->id(), 'settings.updated', null, $old, $validated, $request->ip());

        return back()->with('status', __('Platform settings saved.'));
    }

    public function testMail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
            'test_subject' => ['nullable', 'string', 'max:150'],
        ]);

        $to = $validated['test_email'];
        $subject = $validated['test_subject'] ?: '7th Trade Hub — test email';

        try {
            Mail::raw(
                "This is a test email from 7th Trade Hub Admin Settings.\n\nIf you received this, your mail configuration is working.\n\nSent at: ".now()->toDateTimeString(),
                function ($message) use ($to, $subject) {
                    $message->to($to)->subject($subject);
                }
            );

            $this->audit->log(
                auth()->id(),
                'settings.mail_test',
                null,
                null,
                ['recipient' => $to, 'ok' => true],
                $request->ip()
            );

            return back()->with('status', __('Test email sent to :email.', ['email' => $to]));
        } catch (Throwable $e) {
            $this->audit->log(
                auth()->id(),
                'settings.mail_test',
                null,
                null,
                ['recipient' => $to, 'ok' => false, 'error' => $e->getMessage()],
                $request->ip()
            );

            return back()->withInput()->withErrors([
                'test_email' => 'Mail send failed: '.$e->getMessage(),
            ]);
        }
    }

    public function updateAnalytics(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'google_enabled' => ['nullable', 'boolean'],
            'google_measurement_id' => ['nullable', 'string', 'max:32'],
            'google_property_id' => ['nullable', 'string', 'max:32'],
            'clarity_enabled' => ['nullable', 'boolean'],
            'clarity_project_id' => ['nullable', 'string', 'max:64'],
        ]);

        $google = AnalyticsProvider::forProvider(AnalyticsProvider::PROVIDER_GOOGLE_ANALYTICS);
        $clarity = AnalyticsProvider::forProvider(AnalyticsProvider::PROVIDER_MICROSOFT_CLARITY);

        $googleEnabled = $request->boolean('google_enabled');
        $clarityEnabled = $request->boolean('clarity_enabled');

        if ($googleEnabled && blank($validated['google_measurement_id'] ?? null)) {
            return back()->withInput()->withErrors([
                'google_measurement_id' => 'Measurement ID is required when Google Analytics is enabled.',
            ]);
        }

        if ($clarityEnabled && blank($validated['clarity_project_id'] ?? null)) {
            return back()->withInput()->withErrors([
                'clarity_project_id' => 'Project ID is required when Microsoft Clarity is enabled.',
            ]);
        }

        $google->fill([
            'enabled' => $googleEnabled,
            'status' => $googleEnabled ? 'configured' : 'idle',
        ]);
        $google->mergeCredentials([
            'measurement_id' => trim((string) ($validated['google_measurement_id'] ?? '')),
            'property_id' => trim((string) ($validated['google_property_id'] ?? '')),
        ]);
        $google->save();

        $clarity->fill([
            'enabled' => $clarityEnabled,
            'status' => $clarityEnabled ? 'configured' : 'idle',
        ]);
        $clarity->mergeCredentials([
            'project_id' => trim((string) ($validated['clarity_project_id'] ?? '')),
        ]);
        $clarity->save();

        $this->audit->log(
            auth()->id(),
            'settings.analytics.updated',
            null,
            null,
            [
                'google_enabled' => $googleEnabled,
                'clarity_enabled' => $clarityEnabled,
            ],
            $request->ip()
        );

        return back()->with('status', __('Analytics settings saved.'));
    }

    public function testAnalyticsConnection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'in:google_analytics,microsoft_clarity'],
            'google_measurement_id' => ['nullable', 'string', 'max:32'],
            'google_property_id' => ['nullable', 'string', 'max:32'],
            'clarity_project_id' => ['nullable', 'string', 'max:64'],
        ]);

        if ($validated['provider'] === AnalyticsProvider::PROVIDER_GOOGLE_ANALYTICS) {
            $measurementId = trim((string) ($validated['google_measurement_id'] ?? ''));
            if ($measurementId === '') {
                $measurementId = (string) (AnalyticsProvider::forProvider(AnalyticsProvider::PROVIDER_GOOGLE_ANALYTICS)
                    ->credential('measurement_id') ?? '');
            }

            $result = $this->googleAnalytics->connectionTestFromInput([
                'measurement_id' => $measurementId,
                'property_id' => trim((string) ($validated['google_property_id'] ?? '')),
            ]);
        } else {
            $projectId = trim((string) ($validated['clarity_project_id'] ?? ''));
            if ($projectId === '') {
                $projectId = (string) (AnalyticsProvider::forProvider(AnalyticsProvider::PROVIDER_MICROSOFT_CLARITY)
                    ->credential('project_id') ?? '');
            }

            $result = $this->clarity->connectionTestFromInput([
                'project_id' => $projectId,
            ]);
        }

        $this->audit->log(
            auth()->id(),
            'settings.analytics.connection_test',
            null,
            null,
            ['provider' => $validated['provider'], 'ok' => $result['ok']],
            $request->ip()
        );

        if (! $result['ok']) {
            return back()->withInput()->withErrors([
                'analytics_connection' => $result['message'],
            ]);
        }

        return back()->with('status', $result['message']);
    }
}
