<?php

namespace App\Services;

use App\Models\MusicStore;
use App\Models\RoyaltyImport;
use App\Models\Song;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RoyaltyCsvImportService
{
    public function import(UploadedFile $file, array $defaults, User $admin): array
    {
        $hash = hash_file('sha256', $file->getRealPath());
        $existingImport = RoyaltyImport::where('file_hash', $hash)->first();

        [$headers, $rows] = $this->read($file);
        $isrcColumn = $this->column($headers, ['isrc', 'isrccode', 'trackisrc']);
        $amountColumn = $this->column($headers, [
            'netrevenueeur', 'netrevenueusd', 'netrevenuegbp', 'netrevenue',
            'nettotalclientcurrency', 'nettotal', 'amount', 'revenue', 'netamount',
            'royalty', 'royalties', 'payable',
        ]);
        if ($isrcColumn === null || $amountColumn === null) {
            throw new RuntimeException('CSV must contain ISRC and Amount (or Net Revenue) columns.');
        }

        $songs = Song::with(['album', 'artist'])->whereNotNull('isrc_code')->get()
            ->keyBy(fn (Song $song) => $this->normalizeIsrc($song->isrc_code));
        $stores = MusicStore::all();
        $storesById = $stores->keyBy('id');
        $storesByName = $stores->keyBy(fn (MusicStore $store) => $this->normalize($store->name));
        $storesBySlug = $stores->keyBy(fn (MusicStore $store) => $this->normalize($store->slug));

        return DB::transaction(function () use ($file, $hash, $existingImport, $rows, $headers, $isrcColumn, $amountColumn, $defaults, $admin, $songs, $storesById, $storesByName, $storesBySlug) {
            $import = $existingImport ?? RoyaltyImport::create([
                'file_name' => $file->getClientOriginalName(),
                'file_hash' => $hash,
                'entered_by' => $admin->id,
            ]);
            $importedRows = $import->royalties()->whereNotNull('import_row')->get()->keyBy('import_row');
            $count = 0;
            $alreadyImported = 0;
            $errors = [];
            $inserts = [];
            $artistShares = [];
            $now = now();

            foreach ($rows as $index => $values) {
                $line = $index + 2;
                if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }
                $row = array_combine($headers, array_pad(array_slice($values, 0, count($headers)), count($headers), ''));
                $song = $songs->get($this->normalizeIsrc($row[$isrcColumn] ?? ''));
                if (! $song) {
                    $errors[] = "Row {$line}: song not found for ISRC ".($row[$isrcColumn] ?: '(empty)').'.';

                    continue;
                }

                $storeValue = $this->value($row, ['musicstore', 'store', 'channel', 'platform', 'service', 'retailer', 'dsp']);
                $store = $storeValue
                    ? $this->findStore($storeValue, $storesByName, $storesBySlug)
                    : $storesById->get((int) ($defaults['store_id'] ?? 0));
                if (! $store) {
                    $errors[] = "Row {$line}: music store '{$storeValue}' was not found.";

                    continue;
                }

                if ($existingRoyalty = $importedRows->get($line)) {
                    if ((int) $existingRoyalty->store_id !== (int) $store->id) {
                        $existingRoyalty->update(['store_id' => $store->id]);
                    }
                    $alreadyImported++;

                    continue;
                }

                $amount = $this->decimal($row[$amountColumn] ?? '');
                [$month, $year] = $this->period($row, $defaults);
                $streams = $this->decimal($this->value($row, ['streams', 'streamcount', 'quantity', 'units']) ?: '0');
                if ($amount === null || $month < 1 || $month > 12 || $year < 2020) {
                    $errors[] = "Row {$line}: invalid amount, month, or year.";

                    continue;
                }

                $currency = $this->currency($row, $amountColumn, $defaults);
                $inserts[] = [
                    'artist_id' => $song->artist_id,
                    'song_id' => $song->id,
                    'album_id' => $song->album_id,
                    'store_id' => $store->id,
                    'royalty_type' => $defaults['royalty_type'],
                    'month' => $month,
                    'year' => $year,
                    'amount' => $amount,
                    'currency' => $currency,
                    'streams' => max(0, (int) ($streams ?? 0)),
                    'notes' => $this->value($row, ['notes', 'description']) ?: 'CSV import: '.$file->getClientOriginalName(),
                    'entered_by' => $admin->id,
                    'royalty_import_id' => $import->id,
                    'import_row' => $line,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $artistShares[$song->artist_id] = ($artistShares[$song->artist_id] ?? 0)
                    + $song->artist->getArtistShareAttribute($amount);
                $count++;
            }

            if ($count === 0 && $alreadyImported === 0) {
                throw new RuntimeException('No rows could be imported. '.implode(' ', array_slice($errors, 0, 10)));
            }
            foreach (array_chunk($inserts, 1000) as $chunk) {
                DB::table('royalties')->insert($chunk);
            }
            foreach ($artistShares as $artistId => $share) {
                DB::table('artists')->where('id', $artistId)->update([
                    'total_earnings' => DB::raw('total_earnings + '.(float) $share),
                    'available_balance' => DB::raw('available_balance + '.(float) $share),
                    'updated_at' => $now,
                ]);
            }
            $import->update(['row_count' => $import->royalties()->count()]);

            return [
                'imported' => $count,
                'skipped' => count($errors),
                'skip_messages' => array_slice($errors, 0, 10),
                'already_imported' => $alreadyImported,
            ];
        });
    }

    private function read(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if (! $handle) {
            throw new RuntimeException('The CSV file could not be read.');
        }
        $first = fgets($handle);
        rewind($handle);
        $delimiter = substr_count($first ?: '', "\t") > substr_count($first ?: '', ',') ? "\t" : (substr_count($first ?: '', ';') > substr_count($first ?: '', ',') ? ';' : ',');
        $rawHeaders = fgetcsv($handle, 0, $delimiter);
        if (! is_array($rawHeaders)) {
            throw new RuntimeException('The CSV header row is missing.');
        }
        $headers = array_map(fn ($value) => $this->normalize((string) $value), $rawHeaders);
        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($rows) >= 50000) {
                throw new RuntimeException('A CSV import is limited to 50,000 rows.');
            }
            $rows[] = $row;
        }
        fclose($handle);

        return [$headers, $rows];
    }

    private function column(array $headers, array $aliases): ?string
    {
        foreach ($aliases as $alias) {
            if (in_array($alias, $headers, true)) {
                return $alias;
            }
        }

        return null;
    }

    private function value(array $row, array $aliases): ?string
    {
        $column = $this->column(array_keys($row), $aliases);

        return $column === null ? null : trim((string) ($row[$column] ?? ''));
    }

    private function period(array $row, array $defaults): array
    {
        $period = $this->value($row, ['month', 'period', 'reportmonth', 'salesmonth', 'startdate']);
        if ($period && preg_match('/^(\d{4})[-\/]([01]?\d)/', $period, $match)) {
            return [(int) $match[2], (int) $match[1]];
        }

        return [
            (int) ($period ?: ($defaults['month'] ?? 0)),
            (int) ($this->value($row, ['year', 'reportyear', 'salesyear']) ?: ($defaults['year'] ?? 0)),
        ];
    }

    private function currency(array $row, string $amountColumn, array $defaults): string
    {
        $currency = $this->value($row, ['currency', 'currencycode']);
        if (! $currency && preg_match('/(usd|eur|gbp|jpy)$/', $amountColumn, $match)) {
            $currency = $match[1];
        }

        return strtoupper($currency ?: ($defaults['currency'] ?? 'USD'));
    }

    private function findStore(string $value, $storesByName, $storesBySlug): ?MusicStore
    {
        $name = $this->normalize($value);
        $aliases = [
            'amazonprime' => 'amazon',
            'amazonprimemusic' => 'amazon',
            'meta' => 'instagramfacebookmeta',
            'facebook' => 'instagramfacebookmeta',
            'instagram' => 'instagramfacebookmeta',
            'tiktok' => 'tiktok',
            'tencent' => 'tencent',
            'youtubearttracks' => 'youtubearttracks',
            'youtubeaudiocontentid' => 'youtubeaudiocontentid',
            'youtubecontentid' => 'youtubeaudiocontentid',
        ];
        $name = $aliases[$name] ?? $name;

        return $storesByName->get($name) ?? $storesBySlug->get($name);
    }

    private function normalize(string $value): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '', preg_replace('/^\xEF\xBB\xBF/', '', trim($value))));
    }

    private function normalizeIsrc(string $value): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $value));
    }

    private function decimal(string $value): ?float
    {
        $negative = str_contains($value, '(') && str_contains($value, ')');
        $clean = preg_replace('/[^0-9.\-]/', '', str_replace(',', '', $value));
        if ($clean === '' || ! is_numeric($clean)) {
            return null;
        }

        return (float) $clean * ($negative ? -1 : 1);
    }
}
