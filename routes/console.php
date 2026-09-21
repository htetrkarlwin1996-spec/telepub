<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('catalog:import-shamwela {--check : Show the target account and import counts without writing}', function () {
    $email = 'shamwela2023@gmail.com';
    $user = DB::table('users')->whereRaw('LOWER(email) = ?', [$email])->first();
    if (! $user) {
        $this->error("User {$email} was not found. No releases were imported.");

        return 1;
    }

    $artist = DB::table('artists')->where('user_id', $user->id)->first();
    if (! $artist) {
        $this->error("User {$email} exists (ID {$user->id}, role {$user->role}) but has no artist profile. No releases were imported.");

        return 1;
    }

    $before = DB::table('albums')->where('artist_id', $artist->id)->count();
    $this->info("Target: {$email}; user #{$user->id}; artist #{$artist->id} ({$artist->artist_name}); current releases: {$before}.");
    if (! $this->option('check')) {
        (require database_path('migrations/2026_09_21_090000_import_shamwela_approved_catalog.php'))->up();
    }
    $albums = DB::table('albums')->where('artist_id', $artist->id);
    $imported = (clone $albums)->where('notes', 'like', 'SonoSuite source album %')->count();
    $tracks = DB::table('songs')->where('artist_id', $artist->id)->count();
    $covers = (clone $albums)->whereNotNull('cover_art')->count();
    $this->info("After check/import: {$imported} SonoSuite releases, {$tracks} total tracks, {$covers} covers, {$albums->count()} total releases.");
    if ($imported < 15 && ! $this->option('check')) {
        $this->error('Expected 15 imported releases. Check the account and migration errors.');

        return 1;
    }

    return 0;
})->purpose('Check or retry the Shamwela SonoSuite catalog import');
