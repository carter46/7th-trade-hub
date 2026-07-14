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

// Uncomment when mysqldump is available on the server (e.g. via cPanel cron + SSH):
// Schedule::command('app:backup-database')->daily()->at('02:00');
