<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ArtistController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReleaseController;
use App\Http\Controllers\Api\RoyaltyController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\TrackController;
use App\Http\Controllers\Api\WithdrawalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Sanity check: php artisan route:list --path=api
|
*/

// ===== PUBLIC AUTH ROUTES (rate limited) =====
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,60');
Route::post('/verify-otp', [AuthController::class, 'verifyRegistrationOtp'])->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,60');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,60');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,60');

// ===== AUTHENTICATED USER ROUTES =====
Route::middleware(['auth:sanctum', 'verified', 'throttle:120,60'])->group(function () {

    // --- Auth ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'updateUser']);

    // --- Artist Profile ---
    Route::prefix('artist')->group(function () {
        Route::get('/profile', [ArtistController::class, 'profile']);
        Route::put('/profile', [ArtistController::class, 'updateProfile'])->middleware('artist.manage');
        Route::post('/setup', [ArtistController::class, 'setup'])->middleware('artist.manage');
    });

    // --- Artist Releases / Catalog ---
    Route::prefix('artist/releases')->group(function () {
        Route::get('/', [ReleaseController::class, 'index']);
        Route::get('/collaborations', [ReleaseController::class, 'collaborations']);
        Route::post('/', [ReleaseController::class, 'store'])->middleware('artist.manage');
        Route::get('{album}', [ReleaseController::class, 'show']);
        Route::put('{album}', [ReleaseController::class, 'update'])->middleware('artist.manage');
        Route::delete('{album}', [ReleaseController::class, 'destroy'])->middleware('artist.manage');
        Route::post('{album}/submit', [ReleaseController::class, 'submit'])->middleware('artist.manage');

        // Tracks nested under releases
        Route::get('{album}/tracks', [TrackController::class, 'index']);
        Route::post('{album}/tracks', [TrackController::class, 'batchSave'])->middleware('artist.manage');

        // Pricing & Stores
        Route::put('{album}/pricing', [ReleaseController::class, 'updatePricing'])->middleware('artist.manage');
        Route::post('{album}/stores', [ReleaseController::class, 'selectStores'])->middleware('artist.manage');
    });

    // --- Tracks (standalone) ---
    Route::prefix('artist/tracks')->group(function () {
        Route::delete('{song}', [TrackController::class, 'destroy'])->middleware('artist.manage');
        Route::post('upload-audio', [TrackController::class, 'uploadAudio'])->middleware('artist.manage');
    });

    // --- Stores (public for authenticated) ---
    Route::get('/stores', [StoreController::class, 'index']);

    // --- Artist Royalties ---
    Route::prefix('artist/royalties')->group(function () {
        Route::get('/', [RoyaltyController::class, 'index']);
        Route::get('/summary', [RoyaltyController::class, 'summary']);
        Route::get('{royalty}', [RoyaltyController::class, 'show']);
    });

    // --- Artist Withdrawals ---
    Route::prefix('artist/withdrawals')->group(function () {
        Route::get('/', [WithdrawalController::class, 'index']);
        Route::post('/', [WithdrawalController::class, 'store'])->middleware('artist.manage');
    });

    // --- Artist Analytics ---
    Route::get('/artist/analytics', [AnalyticsController::class, 'index']);

    // ===== ADMIN ROUTES =====
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);

        // Artists
        Route::get('/artists', [AdminController::class, 'artists']);
        Route::post('/artists', [AdminController::class, 'storeArtist']);
        Route::put('/artists/{artist}', [AdminController::class, 'updateArtist']);

        // Releases
        Route::get('/releases', [AdminController::class, 'releases']);
        Route::get('/releases/{album}', [AdminController::class, 'showRelease']);
        Route::post('/releases/{album}/approve', [AdminController::class, 'approveRelease']);
        Route::post('/releases/{album}/reject', [AdminController::class, 'rejectRelease']);

        // Royalties
        Route::get('/royalties', [AdminController::class, 'royalties']);
        Route::post('/royalties', [AdminController::class, 'storeRoyalty']);
        Route::put('/royalties/{royalty}', [AdminController::class, 'updateRoyalty']);

        // Withdrawals
        Route::get('/withdrawals', [AdminController::class, 'withdrawals']);
        Route::post('/withdrawals/{withdrawal}/approve', [AdminController::class, 'approveWithdrawal']);
        Route::post('/withdrawals/{withdrawal}/complete', [AdminController::class, 'completeWithdrawal']);
        Route::post('/withdrawals/{withdrawal}/reject', [AdminController::class, 'rejectWithdrawal']);

        // Stores
        Route::get('/stores', [AdminController::class, 'stores']);
        Route::post('/stores', [AdminController::class, 'storeStore']);
        Route::put('/stores/{store}', [AdminController::class, 'updateStore']);
    });
});
