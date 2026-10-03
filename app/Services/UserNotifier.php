<?php

namespace App\Services;

use App\Models\Album;
use App\Models\Artist;
use App\Models\MasterAccount;
use App\Models\Royalty;
use App\Models\User;
use App\Models\Withdrawal;
use App\Notifications\UserActivityNotification;
use Illuminate\Support\Collection;

class UserNotifier
{
    public function activity(?User $user, string $title, string $message, ?string $url = null, string $actionText = 'Open TeleMusic', array $details = []): void
    {
        if (! $user || blank($user->email)) {
            return;
        }

        try {
            $user->notify(new UserActivityNotification($title, $message, $url, $actionText, $details));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public function login(User $user, ?string $ip, ?string $userAgent): void
    {
        $this->activity($user, 'New login to your account', 'Your TeleMusic account was signed in successfully.', route('dashboard'), 'Review Account', [
            'Time' => now()->format('Y-m-d H:i T'),
            'IP address' => $ip ?: 'Unknown',
            'Device' => $userAgent ? mb_strimwidth($userAgent, 0, 180, '…') : 'Unknown',
        ]);
    }

    public function releaseSubmitted(Album $album): void
    {
        $this->notifyAlbum($album, 'Release submitted', 'Your release was submitted to TeleMusic for review.', [
            'Release' => $album->title,
            'Status' => 'Submitted',
            'Release date' => $album->release_date?->format('Y-m-d') ?? 'Not set',
        ]);
    }

    public function releaseApproved(Album $album): void
    {
        $this->notifyAlbum($album, 'Release approved', 'Your release has been approved by TeleMusic.', [
            'Release' => $album->title,
            'Status' => 'Approved',
            'Release date' => $album->release_date?->format('Y-m-d') ?? 'Not set',
        ]);
    }

    public function releaseRejected(Album $album): void
    {
        $this->notifyAlbum($album, 'Release needs changes', 'Your release was rejected. Review the reason and update the release before submitting it again.', [
            'Release' => $album->title,
            'Status' => 'Rejected',
            'Reason' => $album->rejection_reason ?: 'No reason provided',
        ]);
    }

    public function releaseReleased(Album $album): void
    {
        $this->notifyAlbum($album, 'Your release is out now', 'The scheduled release date for your approved release has arrived.', [
            'Release' => $album->title,
            'Release date' => $album->release_date?->format('Y-m-d') ?? now()->format('Y-m-d'),
        ]);
    }

    public function withdrawalRequested(Withdrawal $withdrawal): void
    {
        $this->notifyArtist($withdrawal->artist, 'Withdrawal request received', 'We received your withdrawal request and it is waiting for Admin review.', [
            'Amount' => $this->money($withdrawal->amount, $withdrawal->currency),
            'Processing fee' => $this->money($withdrawal->fee, $withdrawal->currency),
            'You receive' => $this->money($withdrawal->total, $withdrawal->currency),
            'Status' => 'Pending',
        ], route('artist.withdrawals'));
    }

    public function withdrawalStatus(Withdrawal $withdrawal): void
    {
        $status = ucfirst($withdrawal->status);
        $message = match ($withdrawal->status) {
            'approved' => 'Your withdrawal was approved and is being prepared for payment.',
            'completed' => 'Your withdrawal payment has been completed.',
            'rejected' => 'Your withdrawal was rejected and the held amount was returned to your available balance.',
            default => 'The status of your withdrawal has changed.',
        };
        $details = [
            'Amount' => $this->money($withdrawal->amount, $withdrawal->currency),
            'You receive' => $this->money($withdrawal->total, $withdrawal->currency),
            'Status' => $status,
        ];
        if ($withdrawal->admin_notes) {
            $details['Admin note'] = $withdrawal->admin_notes;
        }
        $this->notifyArtist($withdrawal->artist, 'Withdrawal '.$status, $message, $details, route('artist.withdrawals'));
    }

    public function payoutCreated(Artist $artist, float $amount, float $fee, float $total, string $currency, ?string $reference = null): void
    {
        $details = [
            'Amount' => $this->money($amount, $currency),
            'Processing fee' => $this->money($fee, $currency),
            'Paid amount' => $this->money($total, $currency),
            'Status' => 'Paid',
        ];
        if ($reference) {
            $details['Reference'] = $reference;
        }
        $this->notifyArtist($artist, 'Payout created', 'Admin created and marked a payout as paid for your account.', $details, route('artist.withdrawals'));
    }

    public function royaltyCreated(Royalty $royalty): void
    {
        $royalty->loadMissing(['allocations', 'store', 'song', 'album']);
        foreach ($royalty->allocations->whereIn('beneficiary_type', ['artist', 'master_account']) as $allocation) {
            $user = $allocation->beneficiary_type === 'artist'
                ? Artist::with('user')->find($allocation->beneficiary_id)?->user
                : MasterAccount::with('owner')->find($allocation->beneficiary_id)?->owner;
            $this->activity($user, 'New royalty added', 'New royalty earnings were added to your TeleMusic account.', $this->userUrl($user, route('artist.royalties')), 'View Royalties', [
                'Your earnings' => $this->money($allocation->allocated_amount, $allocation->currency),
                'Gross royalty' => $this->money($royalty->amount, $royalty->currency),
                'Share type' => ucwords(str_replace('_', ' ', $allocation->share_type)),
                'Store' => $royalty->store?->name ?? 'N/A',
                'Release/track' => $royalty->song?->title ?? $royalty->album?->title ?? 'N/A',
                'Period' => str_pad((string) $royalty->month, 2, '0', STR_PAD_LEFT).'/'.$royalty->year,
            ]);
        }
    }

    public function royaltyBatchSummary(string $beneficiaryType, int $beneficiaryId, float $amount, int $entries, string $currency = 'USD'): void
    {
        $user = $beneficiaryType === 'artist'
            ? Artist::with('user')->find($beneficiaryId)?->user
            : MasterAccount::with('owner')->find($beneficiaryId)?->owner;
        $this->activity($user, 'New royalties added', 'New royalty earnings were added to your TeleMusic account.', $this->userUrl($user, route('artist.royalties')), 'View Royalties', [
            'Your earnings' => $this->money($amount, $currency),
            'Royalty entries' => $entries,
        ]);
    }

    public function splitChanged(Album $album, string $status, ?string $note = null): void
    {
        $album->loadMissing(['artist.user', 'collaboratingArtists.user']);
        $collaboratorTotal = $album->collaboratingArtists->sum(fn (Artist $artist) => (float) $artist->pivot->share_percentage);
        $details = [
            'Release' => $album->title,
            'Status' => ucfirst($status),
            'Primary artist share' => number_format(max(0, 100 - $collaboratorTotal), 2).'%',
            'Collaborators' => $album->collaboratingArtists->map(fn (Artist $artist) => $artist->artist_name.' — '.number_format((float) $artist->pivot->share_percentage, 2).'%')->join(', ') ?: 'None',
        ];
        if ($note) {
            $details['Admin note'] = $note;
        }
        foreach ($this->albumUsers($album) as $user) {
            $this->activity($user, 'Collaborator share '.strtolower($status), 'The collaborator revenue shares for “'.$album->title.'” were '.$status.'.', $this->userUrl($user, route('artist.catalog.show', $album)), 'View Release', $details);
        }
    }

    public function accountCreated(User $user, string $accountType): void
    {
        $title = $accountType === 'Master Account' ? 'Master Account created' : $accountType.' account created';
        $this->activity($user, $title, 'A TeleMusic '.$accountType.' account was created for you. For security, your password is not included in this email.', route('login'), 'Sign In', [
            'Account name' => $user->name,
            'Email' => $user->email,
            'Account type' => $accountType,
        ]);
    }

    private function notifyAlbum(Album $album, string $title, string $message, array $details): void
    {
        $album->loadMissing(['artist.user', 'collaboratingArtists.user']);
        foreach ($this->albumUsers($album) as $user) {
            $this->activity($user, $title, $message, $this->userUrl($user, route('artist.catalog.show', $album)), 'View Release', $details);
        }
    }

    private function notifyArtist(?Artist $artist, string $title, string $message, array $details, ?string $url = null): void
    {
        if (! $artist) {
            return;
        }
        foreach ($this->artistUsers($artist) as $user) {
            $this->activity($user, $title, $message, $this->userUrl($user, $url), 'Open TeleMusic', $details);
        }
    }

    private function albumUsers(Album $album): Collection
    {
        return collect([$album->artist])->merge($album->collaboratingArtists)->flatMap(fn (Artist $artist) => $this->artistUsers($artist))->unique('id')->values();
    }

    private function artistUsers(Artist $artist): Collection
    {
        $artist->loadMissing('user');
        $activeMasterOwners = $artist->masterAccounts()
            ->wherePivot('status', 'active')
            ->with('owner')
            ->get()
            ->pluck('owner');

        return collect([$artist->user])->merge($activeMasterOwners)->filter()->unique('id')->values();
    }

    private function money(mixed $amount, ?string $currency): string
    {
        return strtoupper($currency ?: 'USD').' '.number_format((float) $amount, 2);
    }

    private function userUrl(User $user, ?string $artistUrl): ?string
    {
        return $user->isArtist() ? $artistUrl : route('dashboard');
    }
}
