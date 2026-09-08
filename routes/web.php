<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCatalogController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\RoyaltyController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AdminMaintenanceController;
use App\Http\Controllers\AdminBulkReleaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Artist Setup (no artist middleware — redirects here if no artist profile)
    Route::get('/artist/setup', [ArtistController::class, 'setup'])->name('artist.setup');
    Route::post('/artist/setup', [ArtistController::class, 'updateSetup'])->name('artist.setup.update');

    // Artist routes — requires an existing artist profile
    Route::middleware('artist')->group(function () {
        Route::get('/artist/profile', [ArtistController::class, 'profile'])->name('artist.profile');
        Route::post('/artist/profile', [ArtistController::class, 'updateProfile'])->name('artist.profile.update');

        // ===== CATALOG / RELEASES (replaces Albums, Songs, Distribution) =====
        Route::get('/artist/catalog', [CatalogController::class, 'index'])->name('artist.catalog.index');
        Route::get('/artist/catalog/create', [CatalogController::class, 'create'])->name('artist.catalog.create');
        Route::post('/artist/catalog/step1', [CatalogController::class, 'storeStep1'])->name('artist.catalog.store-step1');

        // Step 2 — Tracks
        Route::get('/artist/catalog/{album}/step2', [CatalogController::class, 'step2'])->name('artist.catalog.step2');
        Route::post('/artist/catalog/{album}/step2', [CatalogController::class, 'storeStep2'])->name('artist.catalog.store-step2');

        // Step 3 — Pricing
        Route::get('/artist/catalog/{album}/step3', [CatalogController::class, 'step3'])->name('artist.catalog.step3');
        Route::post('/artist/catalog/{album}/step3', [CatalogController::class, 'storeStep3'])->name('artist.catalog.store-step3');

        // Step 4 — Stores
        Route::get('/artist/catalog/{album}/step4', [CatalogController::class, 'step4'])->name('artist.catalog.step4');
        Route::post('/artist/catalog/{album}/step4', [CatalogController::class, 'storeStep4'])->name('artist.catalog.store-step4');

        // AJAX audio file upload (with progress tracking)
        Route::post('/artist/catalog/upload-audio', [CatalogController::class, 'uploadAudio'])->name('artist.catalog.upload-audio');

        // CRUD
        Route::get('/artist/catalog/{album}', [CatalogController::class, 'show'])->name('artist.catalog.show');
        Route::get('/artist/catalog/{album}/edit', [CatalogController::class, 'edit'])->name('artist.catalog.edit');
        Route::put('/artist/catalog/{album}', [CatalogController::class, 'update'])->name('artist.catalog.update');
        Route::get('/artist/collaborations', [CatalogController::class, 'collaborations'])->name('artist.catalog.collaborations');
        Route::delete('/artist/catalog/{album}', [CatalogController::class, 'destroy'])->name('artist.catalog.destroy');

        // Legacy routes — keep for backward compatibility (redirect to catalog)
        Route::get('/artist/albums', [AlbumController::class, 'index'])->name('artist.albums');
        Route::get('/artist/albums/create', [AlbumController::class, 'create'])->name('artist.albums.create');
        Route::post('/artist/albums', [AlbumController::class, 'store'])->name('artist.albums.store');
        Route::get('/artist/albums/{album}', [AlbumController::class, 'show'])->name('artist.albums.show');
        Route::get('/artist/albums/{album}/edit', [AlbumController::class, 'edit'])->name('artist.albums.edit');
        Route::put('/artist/albums/{album}', [AlbumController::class, 'update'])->name('artist.albums.update');
        Route::delete('/artist/albums/{album}', [AlbumController::class, 'destroy'])->name('artist.albums.destroy');

        Route::get('/artist/songs', [SongController::class, 'index'])->name('artist.songs');
        Route::get('/artist/songs/create', [SongController::class, 'create'])->name('artist.songs.create');
        Route::post('/artist/songs', [SongController::class, 'store'])->name('artist.songs.store');
        Route::get('/artist/songs/{song}', [SongController::class, 'show'])->name('artist.songs.show');
        Route::get('/artist/songs/{song}/edit', [SongController::class, 'edit'])->name('artist.songs.edit');
        Route::put('/artist/songs/{song}', [SongController::class, 'update'])->name('artist.songs.update');
        Route::delete('/artist/songs/{song}', [SongController::class, 'destroy'])->name('artist.songs.destroy');

        Route::get('/artist/distributions', [DistributionController::class, 'index'])->name('artist.distributions');
        Route::get('/artist/distributions/create', [DistributionController::class, 'create'])->name('artist.distributions.create');
        Route::post('/artist/distributions', [DistributionController::class, 'store'])->name('artist.distributions.store');
        Route::get('/artist/distributions/{distribution}', [DistributionController::class, 'show'])->name('artist.distributions.show');

        // Artist - Royalties
        Route::get('/artist/royalties', [RoyaltyController::class, 'index'])->name('artist.royalties');
        Route::get('/artist/royalties/{royalty}', [RoyaltyController::class, 'show'])->name('artist.royalties.show');

        // Artist - Withdrawals
        Route::get('/artist/withdrawals', [WithdrawalController::class, 'index'])->name('artist.withdrawals');
        Route::get('/artist/withdrawals/create', [WithdrawalController::class, 'create'])->name('artist.withdrawals.create');
        Route::post('/artist/withdrawals', [WithdrawalController::class, 'store'])->name('artist.withdrawals.store');

        // Artist - Analytics
        Route::get('/artist/analytics', [AnalyticsController::class, 'index'])->name('artist.analytics');
    });

    // ===== ADMIN ROUTES =====
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::post('/maintenance/enable', [AdminMaintenanceController::class, 'enable'])->name('maintenance.enable');
        Route::post('/maintenance/disable', [AdminMaintenanceController::class, 'disable'])->name('maintenance.disable');

        // Artists
        Route::get('/artists', [AdminController::class, 'artists'])->name('artists');
        Route::get('/artists/create', [AdminController::class, 'createArtist'])->name('artists.create');
        Route::post('/artists', [AdminController::class, 'storeArtist'])->name('artists.store');
        Route::get('/artists/{artist}/edit', [AdminController::class, 'editArtist'])->name('artists.edit');
        Route::put('/artists/{artist}', [AdminController::class, 'updateArtist'])->name('artists.update');

        // Music Stores
        Route::get('/stores', [AdminController::class, 'stores'])->name('stores');
        Route::get('/stores/create', [AdminController::class, 'createStore'])->name('stores.create');
        Route::post('/stores', [AdminController::class, 'storeStore'])->name('stores.store');
        Route::get('/stores/{store}/edit', [AdminController::class, 'editStore'])->name('stores.edit');
        Route::put('/stores/{store}', [AdminController::class, 'updateStore'])->name('stores.update');

        // Royalties
        Route::get('/royalties', [AdminController::class, 'royalties'])->name('royalties');
        Route::post('/royalties', [AdminController::class, 'storeRoyalty'])->name('royalties.store');
        Route::get('/royalties/{royalty}/edit', [AdminController::class, 'editRoyalty'])->name('royalties.edit');
        Route::put('/royalties/{royalty}', [AdminController::class, 'updateRoyalty'])->name('royalties.update');

        // Payouts
        Route::get('/payouts', [AdminController::class, 'payouts'])->name('payouts');
        Route::get('/payouts/create', [AdminController::class, 'createPayout'])->name('payouts.create');
        Route::post('/payouts', [AdminController::class, 'storePayout'])->name('payouts.store');

        // Invoices
        Route::get('/invoices', [AdminController::class, 'invoices'])->name('invoices');
        Route::post('/invoices', [AdminController::class, 'storeInvoice'])->name('invoices.store');

        // Withdrawals
        Route::get('/withdrawals', [AdminController::class, 'withdrawals'])->name('withdrawals');
        Route::post('/withdrawals/{withdrawal}/approve', [AdminController::class, 'approveWithdrawal'])->name('withdrawals.approve');
        Route::post('/withdrawals/{withdrawal}/complete', [AdminController::class, 'completeWithdrawal'])->name('withdrawals.complete');
        Route::post('/withdrawals/{withdrawal}/reject', [AdminController::class, 'rejectWithdrawal'])->name('withdrawals.reject');

        // ===== ADMIN RELEASES (Catalog management) =====
        Route::get('/releases', [AdminController::class, 'releases'])->name('releases');
        Route::get('/releases/bulk-create', [AdminBulkReleaseController::class, 'create'])->name('releases.bulk-create');
        Route::post('/releases/bulk-fetch', [AdminBulkReleaseController::class, 'fetch'])->name('releases.bulk-fetch');
        Route::post('/releases/bulk-store', [AdminBulkReleaseController::class, 'store'])->name('releases.bulk-store');
        Route::get('/releases/{album}', [AdminController::class, 'showRelease'])->name('releases.show');
        Route::post('/releases/{album}/approve', [AdminController::class, 'approveRelease'])->name('releases.approve');
        Route::post('/releases/{album}/reject', [AdminController::class, 'rejectRelease'])->name('releases.reject');
        Route::post('/releases/{album}/update-isrc', [AdminController::class, 'updateReleaseIsrc'])->name('releases.update-isrc');

        // Admin Release Step Wizard (Create & Edit)
        Route::get('/releases/create/step-1', [AdminCatalogController::class, 'createStep1'])->name('releases.create-step1');
        Route::post('/releases/store-step1', [AdminCatalogController::class, 'storeStep1'])->name('releases.store-step1');
        Route::get('/releases/{album}/edit/step-1', [AdminCatalogController::class, 'editStep1'])->name('releases.edit-step1');
        Route::put('/releases/{album}/update-step1', [AdminCatalogController::class, 'updateStep1'])->name('releases.update-step1');

        Route::get('/releases/{album}/step-2', [AdminCatalogController::class, 'step2'])->name('releases.step2');
        Route::post('/releases/{album}/store-step-2', [AdminCatalogController::class, 'storeStep2'])->name('releases.store-step2');

        Route::get('/releases/{album}/step-3', [AdminCatalogController::class, 'step3'])->name('releases.step3');
        Route::post('/releases/{album}/store-step-3', [AdminCatalogController::class, 'storeStep3'])->name('releases.store-step3');

        Route::get('/releases/{album}/step-4', [AdminCatalogController::class, 'step4'])->name('releases.step4');
        Route::post('/releases/{album}/store-step-4', [AdminCatalogController::class, 'storeStep4'])->name('releases.store-step4');

        // AJAX audio file upload (with progress tracking)
        Route::post('/releases/upload-audio', [AdminCatalogController::class, 'uploadAudio'])->name('releases.upload-audio');

        // Albums — full CRUD
        Route::get('/albums', [AdminController::class, 'albums'])->name('albums');
        Route::get('/albums/create', [AdminController::class, 'createAlbum'])->name('albums.create');
        Route::post('/albums', [AdminController::class, 'storeAlbum'])->name('albums.store');
        Route::get('/albums/{album}/edit', [AdminController::class, 'editAlbum'])->name('albums.edit');
        Route::put('/albums/{album}', [AdminController::class, 'updateAlbum'])->name('albums.update');
        Route::delete('/albums/{album}', [AdminController::class, 'destroyAlbum'])->name('albums.destroy');
        Route::get('/songs', [AdminController::class, 'songs'])->name('songs');
        Route::get('/songs/create', [AdminController::class, 'createSong'])->name('songs.create');
        Route::post('/songs', [AdminController::class, 'storeSong'])->name('songs.store');
        Route::get('/songs/{song}/edit', [AdminController::class, 'editSong'])->name('songs.edit');
        Route::put('/songs/{song}', [AdminController::class, 'updateSong'])->name('songs.update');
        Route::delete('/songs/{song}', [AdminController::class, 'destroySong'])->name('songs.destroy');
        Route::get('/distributions', [AdminController::class, 'distributions'])->name('distributions');
        Route::post('/distributions', [AdminController::class, 'storeDistribution'])->name('distributions.store');

        // Analytics
        Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    });
});

require __DIR__.'/auth.php';
