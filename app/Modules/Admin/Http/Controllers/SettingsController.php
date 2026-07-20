<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Modules\Admin\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    public function __construct(private AuditLogService $audit) {}

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
}
