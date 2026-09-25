<?php

namespace App\Services\SiteIntegrations;

use App\Models\User;
use App\Models\UserTool;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\NotificationMessage;
use Illuminate\Support\Facades\Route;

class UserToolLifecycleNotifier
{
    public function __construct(
        private NotificationDispatcher $dispatcher,
    ) {}

    /**
     * Email + inbox when a purchased website is ready for the member (My Tools).
     * Same dedupe key for admin setup and first successful connection check — only one mail.
     */
    public function notifySetupComplete(UserTool $tool): void
    {
        $user = $this->owner($tool);
        if (! $user?->email) {
            return;
        }

        $productName = $this->productName($tool);
        $toolUrl = $this->toolUrl($tool);

        $this->dispatcher->notifyUser(
            $user,
            new NotificationMessage(
                type: 'tool.setup_complete',
                title: __('Your website setup is complete'),
                body: __(':product has been set up successfully. Open My Tools to view your website details and access.', [
                    'product' => $productName,
                ]),
                actionUrl: $toolUrl,
                meta: [
                    'user_tool_id' => $tool->id,
                    'action_label' => __('View tool'),
                ],
                emailSubject: __(':product — setup complete', ['product' => $productName]),
                dedupeKey: 'tool.setup_complete.'.$tool->id,
            ),
            ['database', 'mail']
        );
    }

    public function notifyNaturallyExpired(UserTool $tool): void
    {
        $user = $this->owner($tool);
        if (! $user) {
            return;
        }

        $productName = $this->productName($tool);
        $toolUrl = $this->toolUrl($tool);

        $this->dispatcher->notifyUser(
            $user,
            new NotificationMessage(
                type: 'tool.subscription_expired',
                title: __('Your subscription has expired'),
                body: __(':product has expired. Renew to restore access to your website and admin tools.', [
                    'product' => $productName,
                ]),
                actionUrl: $toolUrl,
                meta: [
                    'user_tool_id' => $tool->id,
                    'action_label' => __('View tool'),
                ],
                emailSubject: __(':product — subscription expired', ['product' => $productName]),
                dedupeKey: 'tool.subscription_expired.'.$tool->id.'.'.$tool->expires_at?->timestamp,
            ),
            ['database', 'mail']
        );
    }

    public function notifyExtendedAfterNaturalExpiry(UserTool $tool): void
    {
        $user = $this->owner($tool);
        if (! $user) {
            return;
        }

        $productName = $this->productName($tool);
        $toolUrl = $this->toolUrl($tool);
        $expiresLabel = $tool->expires_at?->timezone(config('app.timezone'))->format('M j, Y');

        $this->dispatcher->notifyUser(
            $user,
            new NotificationMessage(
                type: 'tool.subscription_extended',
                title: __('Your subscription was extended'),
                body: $expiresLabel
                    ? __(':product is active again until :date.', [
                        'product' => $productName,
                        'date' => $expiresLabel,
                    ])
                    : __(':product is active again.', ['product' => $productName]),
                actionUrl: $toolUrl,
                meta: [
                    'user_tool_id' => $tool->id,
                    'expires_at' => $tool->expires_at?->toIso8601String(),
                    'action_label' => __('View tool'),
                ],
                emailSubject: __(':product — subscription extended', ['product' => $productName]),
                dedupeKey: 'tool.subscription_extended.'.$tool->id.'.'.$tool->expires_at?->timestamp,
            ),
            ['database', 'mail']
        );
    }

    private function owner(UserTool $tool): ?User
    {
        $tool->loadMissing('user');

        return $tool->user;
    }

    private function productName(UserTool $tool): string
    {
        $tool->loadMissing('product');

        return $tool->product?->title ?? $tool->resolvedDisplayName();
    }

    private function toolUrl(UserTool $tool): ?string
    {
        return Route::has('dashboard.my-tools.show')
            ? route('dashboard.my-tools.show', $tool)
            : null;
    }
}
