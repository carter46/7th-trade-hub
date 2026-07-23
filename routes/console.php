<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    DB::table('email_verification_codes')->where('expires_at', '<', now())->delete();
})->daily()->name('prune-expired-otp-codes');

Schedule::command('app:expire-crypto-quotes')->hourly()->withoutOverlapping();
Schedule::command('app:prune-notifications')->weekly()->sundays()->at('03:00');
Schedule::command('app:warm-crypto-prices')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('cache:prune-stale-tags')->daily();

Schedule::command('analytics:rollup-kpis')->hourly()->withoutOverlapping();
Schedule::command('analytics:prune-activity')->daily()->at('04:00');
Schedule::command('analytics:sync-ga')->daily()->at('05:00');
Schedule::command('monitoring:heartbeat')->everyFiveMinutes()->withoutOverlapping();

// Uncomment when mysqldump is available on the server (e.g. via cPanel cron + SSH):
// Schedule::command('app:backup-database')->daily()->at('02:00');
