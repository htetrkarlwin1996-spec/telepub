<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Analytics;
use App\Models\Artist;
use App\Models\Distribution;
use App\Models\Invoice;
use App\Models\MusicStore;
use App\Models\Payout;
use App\Models\Royalty;
use App\Models\Song;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\RoyaltyCsvImportService;
use App\Services\RoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalArtists = Artist::count();
        $totalAlbums = Album::count();
        $totalSongs = Song::count();
        $totalRoyalties = Royalty::sum('amount');
        $totalArtistShares = Royalty::with('artist')->get()->sum(
            fn (Royalty $royalty) => $royalty->artist?->getArtistShareAttribute($royalty->amount) ?? $royalty->amount
        );
        $totalTeleMusicFees = $totalRoyalties - $totalArtistShares;
        $totalPayouts = Payout::where('status', 'paid')->sum('amount');
        $pendingWithdrawals = Withdrawal::where('status', 'pending')->sum('amount');
        $recentArtists = Artist::with('user')->withCount('albums')->latest()->take(5)->get();
        $recentDistributions = Distribution::with('song', 'store')->latest()->take(5)->get();
        $recentWithdrawals = Withdrawal::with('artist')->where('status', 'pending')->latest()->take(5)->get();
        $storeStats = MusicStore::withCount('distributions')->get();

        return view('admin.dashboard', compact(
            'totalArtists', 'totalAlbums', 'totalSongs',
            'totalRoyalties', 'totalArtistShares', 'totalTeleMusicFees', 'totalPayouts', 'pendingWithdrawals',
            'recentArtists', 'recentDistributions', 'recentWithdrawals', 'storeStats'
        ));
    }

    // ===== USER / ARTIST MANAGEMENT =====
    public function artists()
    {
        $artists = Artist::with('user')->withCount(['albums', 'songs'])->withSum('royalties', 'amount')->latest()->paginate(20);

        return view('admin.artists.index', compact('artists'));
    }

    public function createArtist()
    {
        return view('admin.artists.create');
    }

    public function storeArtist(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'artist_name' => 'required|string|max:255',
            'genre' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'country' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'artist',
        ]);

        Artist::create([
            'user_id' => $user->id,
            'artist_name' => $validated['artist_name'],
            'genre' => $validated['genre'],
            'bio' => $validated['bio'],
            'country' => $validated['country'],
            'revenue_share_percentage' => $validated['revenue_share_percentage'] ?? 70.00,
        ]);

        return redirect()->route('admin.artists')->with('success', 'Artist created successfully.');
    }

    public function editArtist(Artist $artist)
    {
        return view('admin.artists.edit', compact('artist'));
    }

    public function updateArtist(Request $request, Artist $artist)
    {
        $validated = $request->validate([
            'artist_name' => 'required|string|max:255',
            'genre' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'payment_email' => 'nullable|email',
            'paypal_email' => 'nullable|email',
            'spotify_profile_url' => 'nullable|url|max:500',
            'apple_music_profile_url' => 'nullable|url|max:500',
            'youtube_profile_url' => 'nullable|url|max:500',
            'tidal_profile_url' => 'nullable|url|max:500',
            'revenue_share_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $artist->update($validated);

        return redirect()->route('admin.artists')->with('success', 'Artist updated successfully.');
    }

    // ===== MUSIC STORE MANAGEMENT =====
    public function stores()
    {
        $stores = MusicStore::withCount('distributions')->get();

        return view('admin.stores.index', compact('stores'));
    }

    public function createStore()
    {
        return view('admin.stores.create');
    }

    public function storeStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:music_stores',
            'description' => 'nullable|string',
            'url' => 'nullable|url',
        ]);

        MusicStore::create($validated);

        return redirect()->route('admin.stores')->with('success', 'Store created successfully.');
    }

    public function editStore(MusicStore $store)
    {
        return view('admin.stores.edit', compact('store'));
    }

    public function updateStore(Request $request, MusicStore $store)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:music_stores,slug,'.$store->id,
            'description' => 'nullable|string',
            'url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        $store->update($validated);

        return redirect()->route('admin.stores')->with('success', 'Store updated successfully.');
    }

    // ===== ROYALTY MANAGEMENT =====
    public function royalties()
    {
        $royalties = Royalty::with(['artist', 'store', 'song', 'album.collaboratingArtists'])->latest()->paginate(20);
        $stores = MusicStore::all();
        $artists = Artist::all();

        return view('admin.royalties.index', compact('royalties', 'stores', 'artists'));
    }

    public function storeRoyalty(Request $request, RoyaltyService $royaltyService)
    {
        $validated = $request->validate([
            'artist_id' => 'required|exists:artists,id',
            'store_id' => 'required|exists:music_stores,id',
            'royalty_type' => 'required|in:'.implode(',', array_keys(Royalty::TYPES)),
            'song_id' => 'nullable|exists:songs,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'streams' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['entered_by'] = auth()->id();

        $royaltyService->create($validated);

        return redirect()->route('admin.royalties')->with('success', 'Royalty entry added successfully.');
    }

    public function storeBulkRoyalty(Request $request, RoyaltyService $royaltyService)
    {
        $validated = $request->validate([
            'artist_id' => 'required|exists:artists,id',
            'royalty_type' => 'required|in:'.implode(',', array_keys(Royalty::TYPES)),
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:'.(date('Y') + 1),
            'currency' => 'required|string|size:3',
            'notes' => 'nullable|string|max:1000',
            'stores' => 'required|array',
            'stores.*.store_id' => 'required|distinct|exists:music_stores,id',
            'stores.*.amount' => 'nullable|numeric|min:0',
            'stores.*.streams' => 'nullable|integer|min:0',
        ]);

        $rows = collect($validated['stores'])->filter(
            fn ($row) => array_key_exists('amount', $row) && $row['amount'] !== null && $row['amount'] !== ''
        );
        if ($rows->isEmpty()) {
            return back()->withInput()->withErrors(['stores' => 'Enter an amount for at least one store.']);
        }

        DB::transaction(function () use ($rows, $validated, $royaltyService) {
            foreach ($rows as $row) {
                $royaltyService->create([
                    'artist_id' => $validated['artist_id'],
                    'store_id' => $row['store_id'],
                    'royalty_type' => $validated['royalty_type'],
                    'month' => $validated['month'],
                    'year' => $validated['year'],
                    'amount' => $row['amount'],
                    'currency' => strtoupper($validated['currency']),
                    'streams' => $row['streams'] ?? 0,
                    'notes' => $validated['notes'] ?? 'Bulk manual entry',
                    'entered_by' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('admin.royalties')->with('success', $rows->count().' store royalty entries added successfully.');
    }

    public function importRoyalties(Request $request, RoyaltyCsvImportService $importer)
    {
        $validated = $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:20480',
            'royalty_type' => 'nullable|in:'.implode(',', array_keys(Royalty::TYPES)),
        ]);
        $validated['royalty_type'] = $validated['royalty_type'] ?? 'royalties';

        try {
            $result = $importer->import($request->file('csv_file'), $validated, $request->user());
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors(['csv_file' => $exception->getMessage()]);
        }

        $redirect = redirect()->route('admin.royalties')
            ->with('success', "{$result['imported']} royalty rows imported successfully.");

        if ($result['already_imported'] > 0) {
            $redirect->with('info', "{$result['already_imported']} rows were already imported and were not duplicated.");
        }

        if ($result['skipped'] > 0) {
            $message = "{$result['skipped']} rows were skipped. ".implode(' ', $result['skip_messages']);
            $redirect->with('warning', $message);
        }

        return $redirect;
    }

    public function royaltyImportTemplate()
    {
        $csv = "ISRC,Amount,Streams,Store,Month,Year,Currency,Notes\nUSRC17607839,12.50,15000,Spotify,".date('n').','.date('Y').",USD,Monthly report\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="royalty-import-template.csv"',
        ]);
    }

    public function editRoyalty(Royalty $royalty)
    {
        $stores = MusicStore::all();
        $artists = Artist::all();

        return view('admin.royalties.edit', compact('royalty', 'stores', 'artists'));
    }

    public function updateRoyalty(Request $request, Royalty $royalty, RoyaltyService $royaltyService)
    {
        $validated = $request->validate([
            'artist_id' => 'required|exists:artists,id',
            'store_id' => 'required|exists:music_stores,id',
            'royalty_type' => 'required|in:'.implode(',', array_keys(Royalty::TYPES)),
            'song_id' => 'nullable|exists:songs,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'streams' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $royaltyService->update($royalty, $validated);

        return redirect()->route('admin.royalties')->with('success', 'Royalty updated successfully.');
    }

    // ===== PAYOUT MANAGEMENT =====
    public function payouts()
    {
        $payouts = Payout::with('artist')->latest()->paginate(20);

        return view('admin.payouts.index', compact('payouts'));
    }

    public function createPayout()
    {
        $artists = Artist::all();

        return view('admin.payouts.create', compact('artists'));
    }

    public function storePayout(Request $request)
    {
        $validated = $request->validate([
            'artist_id' => 'required|exists:artists,id',
            'amount' => 'required|numeric|min:0',
            'fee' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['total'] = $validated['amount'] - $validated['fee'];
        $validated['invoice_number'] = 'INV-'.strtoupper(uniqid());
        $validated['processed_by'] = auth()->id();
        $validated['status'] = 'paid';
        $validated['paid_at'] = now();

        Payout::create($validated);

        // Deduct from artist balance
        $artist = Artist::find($validated['artist_id']);
        $artist->decrement('available_balance', $validated['amount']);

        return redirect()->route('admin.payouts')->with('success', 'Payout processed successfully.');
    }

    // ===== INVOICE MANAGEMENT =====
    public function invoices()
    {
        $invoices = Invoice::with('artist')->latest()->paginate(20);
        $artists = Artist::all();

        return view('admin.invoices.index', compact('invoices', 'artists'));
    }

    public function storeInvoice(Request $request)
    {
        $validated = $request->validate([
            'artist_id' => 'required|exists:artists,id',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'type' => 'required|in:royalty,distribution_fee,other',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['invoice_number'] = 'INV-'.date('Ymd').'-'.strtoupper(uniqid());
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'sent';

        Invoice::create($validated);

        return redirect()->route('admin.invoices')->with('success', 'Invoice created and sent.');
    }

    // ===== WITHDRAWAL MANAGEMENT =====
    public function withdrawals()
    {
        $withdrawals = Withdrawal::with('artist', 'processedBy')->latest()->paginate(20);

        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function approveWithdrawal(Withdrawal $withdrawal)
    {
        $withdrawal->update([
            'status' => 'approved',
            'admin_notes' => request('admin_notes'),
            'processed_by' => auth()->id(),
        ]);

        return redirect()->route('admin.withdrawals')->with('success', 'Withdrawal approved.');
    }

    public function completeWithdrawal(Withdrawal $withdrawal)
    {
        $withdrawal->update([
            'status' => 'completed',
            'processed_at' => now(),
            'processed_by' => auth()->id(),
            'admin_notes' => request('admin_notes'),
        ]);

        // Create payout record
        Payout::create([
            'artist_id' => $withdrawal->artist_id,
            'invoice_number' => 'PAY-'.strtoupper(uniqid()),
            'amount' => $withdrawal->amount,
            'fee' => $withdrawal->fee,
            'total' => $withdrawal->total,
            'currency' => $withdrawal->currency,
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $withdrawal->payment_method,
            'processed_by' => auth()->id(),
        ]);

        return redirect()->route('admin.withdrawals')->with('success', 'Withdrawal completed and payout processed.');
    }

    public function rejectWithdrawal(Request $request, Withdrawal $withdrawal)
    {
        $withdrawal->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        // Return funds to artist
        $artist = Artist::find($withdrawal->artist_id);
        $artist->increment('available_balance', $withdrawal->total);

        return redirect()->route('admin.withdrawals')->with('success', 'Withdrawal rejected. Funds returned to artist.');
    }

    // ===== ALBUMS & SONGS MANAGEMENT =====
    public function albums()
    {
        $albums = Album::with('artist')->withCount('songs')->latest()->paginate(20);

        return view('admin.albums.index', compact('albums'));
    }

    public function createAlbum()
    {
        $artists = Artist::with('user')->orderBy('artist_name')->get();
        $genres = CatalogController::genres();

        return view('admin.albums.create', compact('artists', 'genres'));
    }

    public function storeAlbum(Request $request)
    {
        $validated = $request->validate([
            'artist_id' => 'required|exists:artists,id',
            'title' => 'required|string|max:255',
            'release_type' => 'required|in:single,ep,album',
            'genre' => 'required|string|max:100',
            'label' => 'nullable|string|max:255',
            'release_date' => 'required|date',
            'upc_code' => 'nullable|string|max:50',
            'copyright_holder' => 'nullable|string|max:255',
            'phonogram_right_holder' => 'nullable|string|max:255',
            'status' => 'sometimes|in:draft,submitted,approved,rejected',
        ]);

        $validated['slug'] = Str::slug($validated['title']).'-'.uniqid();
        $validated['status'] = $validated['status'] ?? 'draft';

        Album::create($validated);

        return redirect()->route('admin.albums')->with('success', 'Album created successfully.');
    }

    public function editAlbum(Album $album)
    {
        $artists = Artist::with('user')->orderBy('artist_name')->get();
        $genres = CatalogController::genres();

        return view('admin.albums.edit', compact('album', 'artists', 'genres'));
    }

    public function updateAlbum(Request $request, Album $album)
    {
        $validated = $request->validate([
            'artist_id' => 'required|exists:artists,id',
            'title' => 'required|string|max:255',
            'release_type' => 'required|in:single,ep,album',
            'genre' => 'required|string|max:100',
            'label' => 'nullable|string|max:255',
            'release_date' => 'required|date',
            'upc_code' => 'nullable|string|max:50',
            'copyright_holder' => 'nullable|string|max:255',
            'phonogram_right_holder' => 'nullable|string|max:255',
            'status' => 'required|in:draft,submitted,approved,rejected',
        ]);

        $album->update($validated);

        return redirect()->route('admin.albums')->with('success', 'Album updated successfully.');
    }

    public function destroyAlbum(Album $album)
    {
        // Delete associated distributions, royalties, analytics, songs
        $album->distributions()->delete();
        $album->royalties()->delete();
        Analytics::where('album_id', $album->id)->delete();
        $album->songs()->delete();
        $album->delete();

        return redirect()->route('admin.albums')->with('success', 'Album deleted successfully.');
    }

    public function songs()
    {
        $songs = Song::with('artist', 'album')->latest()->paginate(20);

        return view('admin.songs.index', compact('songs'));
    }

    public function createSong()
    {
        $albums = Album::with('artist')->orderBy('title')->get();

        return view('admin.songs.create', compact('albums'));
    }

    public function storeSong(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'album_id' => 'required|exists:albums,id',
            'track_number' => 'nullable|integer|min:1',
            'duration' => 'nullable|string|max:20',
            'isrc_code' => 'nullable|string|max:50',
            'explicit' => 'boolean',
            'genre' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:50',
            'composers' => 'nullable|string|max:500',
            'producers' => 'nullable|string|max:500',
            'featuring' => 'nullable|string|max:500',
            'lyrics' => 'nullable|string',
            'status' => 'nullable|in:draft,submitted,approved',
        ]);

        // Derive artist_id from the album
        $album = Album::findOrFail($validated['album_id']);
        $validated['artist_id'] = $album->artist_id;
        $validated['status'] = $validated['status'] ?? 'draft';

        if ($request->hasFile('audio_file')) {
            $validated['audio_file'] = $request->file('audio_file')->store('audio', 'public');
        }

        Song::create($validated);

        return redirect()->route('admin.songs')->with('success', 'Song created successfully.');
    }

    public function editSong(Song $song)
    {
        $albums = Album::with('artist')->orderBy('title')->get();

        return view('admin.songs.edit', compact('song', 'albums'));
    }

    public function updateSong(Request $request, Song $song)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'album_id' => 'required|exists:albums,id',
            'track_number' => 'nullable|integer|min:1',
            'duration' => 'nullable|string|max:20',
            'isrc_code' => 'nullable|string|max:50',
            'explicit' => 'boolean',
            'genre' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:50',
            'composers' => 'nullable|string|max:500',
            'producers' => 'nullable|string|max:500',
            'featuring' => 'nullable|string|max:500',
            'lyrics' => 'nullable|string',
            'status' => 'nullable|in:draft,submitted,approved',
        ]);

        if ($request->hasFile('audio_file')) {
            $validated['audio_file'] = $request->file('audio_file')->store('audio', 'public');
        }

        $song->update($validated);

        return redirect()->route('admin.songs')->with('success', 'Song updated successfully.');
    }

    public function destroySong(Song $song)
    {
        // Delete associated distributions and royalties
        $song->distributions()->delete();
        $song->royalties()->delete();
        $song->delete();

        return redirect()->route('admin.songs')->with('success', 'Song deleted successfully.');
    }

    public function distributions()
    {
        $distributions = Distribution::with('song', 'store', 'artist')->latest()->paginate(20);
        $stores = MusicStore::all();
        $songs = Song::with('artist')->where('status', 'approved')->get();

        return view('admin.distributions.index', compact('distributions', 'stores', 'songs'));
    }

    public function storeDistribution(Request $request)
    {
        $validated = $request->validate([
            'song_id' => 'required|exists:songs,id',
            'store_id' => 'required|exists:music_stores,id',
            'distribution_fee' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Derive artist_id and album_id from the song
        $song = Song::findOrFail($validated['song_id']);
        $validated['artist_id'] = $song->artist_id;
        $validated['album_id'] = $song->album_id;
        $validated['status'] = 'submitted';
        $validated['submitted_at'] = now();

        Distribution::create($validated);

        return redirect()->route('admin.distributions')->with('success', 'Distribution created and submitted.');
    }

    // ===== RELEASE / CATALOG MANAGEMENT =====
    public function releases()
    {
        $releases = Album::with('artist', 'songs')
            ->whereIn('status', ['submitted', 'approved', 'rejected'])
            ->latest()
            ->paginate(20);

        return view('admin.releases.index', compact('releases'));
    }

    public function showRelease(Album $album)
    {
        $album->load('artist.user', 'songs', 'distributions.store', 'collaboratingArtists');
        $stores = MusicStore::where('is_active', true)->get();

        return view('admin.releases.show', compact('album', 'stores'));
    }

    public function approveRelease(Request $request, Album $album)
    {
        $validated = $request->validate([
            'songs' => 'required|array',
            'songs.*.isrc_code' => 'nullable|string|max:50',
        ]);

        $album->update([
            'status' => 'approved',
            'approved_at' => now(),
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        // Update ISRC codes for songs
        foreach ($validated['songs'] as $songId => $data) {
            if (! empty($data['isrc_code'])) {
                Song::where('id', $songId)->update([
                    'isrc_code' => $data['isrc_code'],
                    'request_new_isrc' => false,
                    'status' => 'approved',
                ]);
            } else {
                Song::where('id', $songId)->update(['status' => 'approved']);
            }
        }

        // Approve all distributions
        $album->distributions()->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.releases.show', $album)
            ->with('success', 'Release approved successfully! ISRC codes have been saved.');
    }

    public function rejectRelease(Request $request, Album $album)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $album->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'approved_at' => null,
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // Reject all distributions
        $album->distributions()->update(['status' => 'rejected']);

        return redirect()->route('admin.releases.show', $album)
            ->with('success', 'Release rejected.');
    }

    public function updateReleaseIsrc(Request $request, Album $album)
    {
        $validated = $request->validate([
            'songs' => 'required|array',
            'songs.*.isrc_code' => 'nullable|string|max:50',
        ]);

        foreach ($validated['songs'] as $songId => $data) {
            if (! empty($data['isrc_code'])) {
                Song::where('id', $songId)->update([
                    'isrc_code' => $data['isrc_code'],
                    'request_new_isrc' => false,
                ]);
            }
        }

        return redirect()->route('admin.releases.show', $album)
            ->with('success', 'ISRC codes updated successfully.');
    }

    // ===== ANALYTICS =====
    public function analytics()
    {
        $monthlyStats = Analytics::selectRaw('month, year, SUM(streams) as total_streams, SUM(downloads) as total_downloads, SUM(revenue) as total_revenue')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        $storeAnalytics = Analytics::selectRaw('store_id, SUM(streams) as total_streams, SUM(revenue) as total_revenue')
            ->with('store')
            ->groupBy('store_id')
            ->get();

        return view('admin.analytics.index', compact('monthlyStats', 'storeAnalytics'));
    }
}
