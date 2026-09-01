<?php

namespace App\Services\Notifications;

final class AdminNotificationChannels
{
    /** @var list<string> */
    public const FINANCE = ['database', 'mail'];

    /** @var list<string> */
    public const SUPPORT = ['database', 'mail'];

    /** @var list<string> */
    public const SALES = ['database', 'mail'];

    /** @var list<string> */
    public const GENERAL = ['database', 'mail'];

    /** @var list<string> */
    public const SECURITY = ['database', 'mail'];
}
