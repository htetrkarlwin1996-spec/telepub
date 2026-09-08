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
    public function __construct(private RoyaltyService $royaltyService) {}

    public function import(UploadedFile $file, array $defaults, User $admin): int
    {
        $hash = hash_file('sha256', $file->getRealPath());
        if (RoyaltyImport::where('file_hash', $hash)->exists()) {
            throw new RuntimeException('This exact CSV file has already been imported.');
        }

        [$headers, $rows] = $this->read($file);
        $isrcColumn = $this->column($headers, ['isrc', 'isrccode', 'trackisrc']);
        $amountColumn = $this->column($headers, ['amount', 'revenue', 'netrevenue', 'netamount', 'royalty', 'royalties', 'payable']);
        if ($isrcColumn === null || $amountColumn === null) {
            throw new RuntimeException('CSV must contain ISRC and Amount (or Net Revenue) columns.');
        }

        $songs = Song::with('album')->whereNotNull('isrc_code')->get()
            ->keyBy(fn (Song $song) => $this->normalizeIsrc($song->isrc_code));
        $stores = MusicStore::all();
        $storesById = $stores->keyBy('id');
        $storesByName = $stores->keyBy(fn (MusicStore $store) => $this->normalize($store->name));
        $storesBySlug = $stores->keyBy(fn (MusicStore $store) => $this->normalize($store->slug));

        return DB::transaction(function () use ($file, $hash, $rows, $headers, $isrcColumn, $amountColumn, $defaults, $admin, $songs, $storesById, $storesByName, $storesBySlug) {
            $import = RoyaltyImport::create([
                'file_name' => $file->getClientOriginalName(),
                'file_hash' => $hash,
                'entered_by' => $admin->id,
            ]);
            $count = 0;
            $errors = [];

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

                $storeValue = $this->value($row, ['store', 'platform', 'service', 'retailer', 'dsp']);
                $store = $storeValue
                    ? ($storesByName->get($this->normalize($storeValue)) ?? $storesBySlug->get($this->normalize($storeValue)))
                    : $storesById->get((int) $defaults['store_id']);
                if (! $store) {
                    $errors[] = "Row {$line}: music store '{$storeValue}' was not found.";

                    continue;
                }

                $amount = $this->decimal($row[$amountColumn] ?? '');
                $month = (int) ($this->value($row, ['month', 'reportmonth', 'salesmonth']) ?: $defaults['month']);
                $year = (int) ($this->value($row, ['year', 'reportyear', 'salesyear']) ?: $defaults['year']);
                $streams = $this->decimal($this->value($row, ['streams', 'streamcount', 'quantity', 'units']) ?: '0');
                if ($amount === null || $amount < 0 || $month < 1 || $month > 12 || $year < 2020) {
                    $errors[] = "Row {$line}: invalid amount, month, or year.";

                    continue;
                }

                $this->royaltyService->create([
                    'artist_id' => $song->artist_id,
                    'song_id' => $song->id,
                    'album_id' => $song->album_id,
                    'store_id' => $store->id,
                    'royalty_type' => $defaults['royalty_type'],
                    'month' => $month,
                    'year' => $year,
                    'amount' => $amount,
                    'currency' => strtoupper($this->value($row, ['currency', 'currencycode']) ?: $defaults['currency']),
                    'streams' => max(0, (int) ($streams ?? 0)),
                    'notes' => $this->value($row, ['notes', 'description']) ?: 'CSV import: '.$file->getClientOriginalName(),
                    'entered_by' => $admin->id,
                    'royalty_import_id' => $import->id,
                    'import_row' => $line,
                ]);
                $count++;
            }

            if ($errors) {
                throw new RuntimeException(implode(' ', array_slice($errors, 0, 10)).(count($errors) > 10 ? ' More rows also failed.' : ''));
            }
            if ($count === 0) {
                throw new RuntimeException('The CSV contains no importable rows.');
            }
            $import->update(['row_count' => $count]);

            return $count;
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
