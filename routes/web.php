<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\PaymentMethodController;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\User\AlbumController;
use App\Http\Controllers\User\TakedownRequestController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\AlbumController as AdminAlbumController;
use App\Http\Controllers\Admin\PayoutController as AdminPayoutController;
use App\Http\Controllers\Admin\TakedownController as AdminTakedownController;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        if (!auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'otp.verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::post('/profile/agreement/regenerate', [ProfileController::class, 'regenerateAgreement'])
        ->name('profile.agreement.regenerate');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::get('/payment-methods', [PaymentMethodController::class, 'index'])
        ->name('user.payment-methods.index');

    Route::post('/payment-methods', [PaymentMethodController::class, 'store'])
        ->name('user.payment-methods.store');

    Route::put('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update'])
        ->name('user.payment-methods.update');

    Route::delete('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'destroy'])
        ->name('user.payment-methods.destroy');

    Route::post('/payment-methods/{paymentMethod}/default', [PaymentMethodController::class, 'setDefault'])
        ->name('user.payment-methods.default');

    Route::get('/wallet', [WalletController::class, 'index'])
        ->name('user.wallet.index');

    Route::post('/wallet/payout-request', [WalletController::class, 'storePayoutRequest'])
        ->name('user.wallet.payout-request');

    Route::get('/albums', [AlbumController::class, 'index'])
        ->name('user.albums.index');

    Route::get('/albums/{album}', [AlbumController::class, 'show'])
        ->name('user.albums.show');

    Route::get('/takedown-requests', [TakedownRequestController::class, 'index'])
        ->name('user.takedowns.index');

    Route::post('/takedown-requests', [TakedownRequestController::class, 'store'])
        ->name('user.takedowns.store');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'otp.verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Admin Users
        |--------------------------------------------------------------------------
        */
        Route::get('/users', [AdminUserController::class, 'index'])
            ->name('users.index');

        Route::get('/users/{user}', [AdminUserController::class, 'show'])
            ->name('users.show');

        Route::patch('/users/{user}/wallet', [AdminUserController::class, 'updateWallet'])
            ->name('users.wallet.update');

        Route::patch('/users/{user}/status', [AdminUserController::class, 'updateStatus'])
            ->name('users.status.update');

        /*
        |--------------------------------------------------------------------------
        | Admin Albums
        |--------------------------------------------------------------------------
        */
        Route::get('/albums', [AdminAlbumController::class, 'index'])
            ->name('albums.index');

        Route::post('/albums', [AdminAlbumController::class, 'store'])
            ->name('albums.store');

        Route::get('/albums/{album}', [AdminAlbumController::class, 'show'])
            ->name('albums.show');

        Route::patch('/albums/{album}', [AdminAlbumController::class, 'update'])
            ->name('albums.update');

        Route::post('/albums/{album}/songs', [AdminAlbumController::class, 'addSong'])
            ->name('albums.songs.store');

        Route::patch('/songs/{song}', [AdminAlbumController::class, 'updateSong'])
            ->name('songs.update');

        Route::delete('/songs/{song}', [AdminAlbumController::class, 'destroySong'])
            ->name('songs.destroy');

        /*
        |--------------------------------------------------------------------------
        | Admin Payouts
        |--------------------------------------------------------------------------
        */
        Route::get('/payouts', [AdminPayoutController::class, 'index'])
            ->name('payouts.index');

        Route::patch('/payouts/{payout}/approve', [AdminPayoutController::class, 'approve'])
            ->name('payouts.approve');

        Route::patch('/payouts/{payout}/reject', [AdminPayoutController::class, 'reject'])
            ->name('payouts.reject');

        Route::patch('/payouts/{payout}/paid', [AdminPayoutController::class, 'markPaid'])
            ->name('payouts.paid');

        /*
        |--------------------------------------------------------------------------
        | Admin Take Downs
        |--------------------------------------------------------------------------
        */
        Route::get('/takedowns', [AdminTakedownController::class, 'index'])
            ->name('takedowns.index');

        Route::patch('/takedowns/{takedown}', [AdminTakedownController::class, 'update'])
            ->name('takedowns.update');
    });

require __DIR__.'/auth.php';