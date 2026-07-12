<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::view('/about', 'pages.about')->name('about');
Route::view('/help', 'pages.help')->name('help');
Route::view('/terms', 'pages.terms')->name('terms');
Route::view('/privacy', 'pages.privacy')->name('privacy');

Route::view('/marketplace', 'pages.marketplace')->name('marketplace');
Route::view('/marketplace/web-services', 'pages.marketplace-web-services')->name('marketplace.web-services');
Route::view('/services', 'pages.services')->name('services');
Route::view('/exchange', 'pages.exchange')->name('exchange');
Route::view('/templates', 'pages.templates')->name('templates');
Route::view('/document-templates', 'pages.document-templates')->name('document-templates');
Route::view('/website-listings', 'pages.website-listings')->name('website-listings');
Route::view('/code', 'pages.code')->name('code');
Route::view('/support', 'pages.support')->name('support');
Route::get('/u/{username}', function (string $username) {
    return view('pages.user-profile', ['username' => $username]);
})->name('user.profile');

Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('');
    Route::get('/wallet', [DashboardController::class, 'wallet'])->name('.wallet');
    Route::get('/exchange', [DashboardController::class, 'exchange'])->name('.exchange');
    Route::get('/social', [DashboardController::class, 'social'])->name('.social');
    Route::get('/documents', [DashboardController::class, 'documents'])->name('.documents');
    Route::get('/listings', [DashboardController::class, 'listings'])->name('.listings');
    Route::get('/orders', [DashboardController::class, 'orders'])->name('.orders');
    Route::get('/messages', [DashboardController::class, 'messages'])->name('.messages');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('');
    Route::get('/users', [UserManagementController::class, 'index'])->name('.users');
    Route::get('/transactions', [AdminDashboardController::class, 'transactions'])->name('.transactions');
    Route::get('/listings', [AdminDashboardController::class, 'listings'])->name('.listings');
    Route::get('/social', [AdminDashboardController::class, 'social'])->name('.social');
    Route::get('/tickets', [AdminDashboardController::class, 'tickets'])->name('.tickets');
    Route::get('/analytics', [AdminDashboardController::class, 'analytics'])->name('.analytics');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
