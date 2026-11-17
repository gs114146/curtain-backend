<?php

namespace App\Console\Commands;

use App\Models\Characteristic;
use App\Models\Color;
use App\Models\Concerns\HasTitleCaseName;
use App\Models\Design;
use App\Models\Material;
use App\Models\Transparency;
use App\Models\Usage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportB2bMasters extends Command
{
    protected $signature = 'import:b2b-masters
                            {--path= : Absolute or relative path to the B2B CSV file}
                            {--dry-run : Parse and report without writing to the database}';

    protected $description = 'Import unique colors, designs, materials, transparencies, characteristics, and usages from the B2B curtains CSV (stores client IDs as internal external_id)';

    public function handle(): int
    {
        $path = $this->option('path') ?: base_path('B2B Characteristics - curtains.csv');

        if (!is_file($path)) {
            $this->error("CSV not found: {$path}");

            return self::FAILURE;
        }

        $this->info("Reading: {$path}");

        $masters = $this->extractMasters($path);

        $this->table(
            ['Entity', 'Unique IDs'],
            [
                ['colors', count($masters['colors'])],
                ['designs', count($masters['designs'])],
                ['materials', count($masters['materials'])],
                ['transparencies', count($masters['transparencies'])],
                ['characteristics', count($masters['characteristics'])],
                ['usages', count($masters['usages'])],
            ]
        );

        if ($this->option('dry-run')) {
            $this->warn('Dry run only — no database changes.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($masters) {
            $this->cleanupNameDuplicates();

            $this->upsertColors($masters['colors']);
            $this->upsertNamed(Design::class, $masters['designs'], 'designs');
            $this->upsertNamed(Material::class, $masters['materials'], 'materials');
            $this->upsertNamed(Transparency::class, $masters['transparencies'], 'transparencies');
            $this->upsertNamed(Characteristic::class, $masters['characteristics'], 'characteristics');
            $this->upsertNamed(Usage::class, $masters['usages'], 'usages');

            $this->titleCaseExistingNames();
        });

        $this->info('B2B master data imported successfully.');

        return self::SUCCESS;
    }

    /**
     * Merge accidental duplicates from earlier imports:
     * - same name (case-insensitive) with one seeded row (no external_id) + one imported row
     * - keep the older row, overwrite with imported values, delete the duplicate
     * - if multiple external_ids share a name (e.g. under clearance 20 & 116), keep those and drop seed-only dupes
     */
    private function cleanupNameDuplicates(): void
    {
        $models = [
            Color::class,
            Design::class,
            Material::class,
            Transparency::class,
            Characteristic::class,
            Usage::class,
        ];

        $removed = 0;

        foreach ($models as $modelClass) {
            $groups = $modelClass::query()->get()->groupBy(fn ($row) => mb_strtolower((string) $row->name));

            foreach ($groups as $rows) {
                if ($rows->count() < 2) {
                    continue;
                }

                $withExt = $rows->filter(fn ($row) => $row->external_id !== null)->values();
                $withoutExt = $rows->filter(fn ($row) => $row->external_id === null)->values();

                if ($withExt->isEmpty() || $withoutExt->isEmpty()) {
                    continue;
                }

                if ($withExt->count() > 1) {
                    // Intentional same-name different external_ids — remove seed-only copies.
                    foreach ($withoutExt as $seed) {
                        $seed->delete();
                        $removed++;
                    }
                    continue;
                }

                $keeper = $withoutExt->sortBy('id')->first();
                $duplicate = $withExt->first();

                $externalId = $duplicate->external_id;
                $importedName = $duplicate->name;
                $importedColorValue = $duplicate->color_value ?? null;

                // Free unique external_id before moving it onto the keeper row
                $duplicate->external_id = null;
                $duplicate->save();

                $keeper->external_id = $externalId;
                $keeper->name = $importedName;
                $keeper->status = 'active';
                if ($keeper instanceof Color) {
                    $keeper->color_value = $importedColorValue ?? $keeper->color_value;
                }
                $keeper->save();

                $duplicate->delete();
                $removed++;
            }
        }

        if ($removed > 0) {
            $this->line("cleanup: removed {$removed} duplicate row(s) and merged onto existing names");
        }
    }

    /**
     * @return array{
     *   colors: array<int, array{name: string, color_value: string}>,
     *   designs: array<int, string>,
     *   materials: array<int, string>,
     *   transparencies: array<int, string>,
     *   characteristics: array<int, string>,
     *   usages: array<int, string>
     * }
     */
    private function extractMasters(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Unable to open CSV: {$path}");
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            throw new \RuntimeException('CSV header is missing.');
        }

        // Normalize BOM / whitespace on header names
        $header = array_map(function ($col) {
            return trim(ltrim((string) $col, "\xEF\xBB\xBF"));
        }, $header);

        $index = array_flip($header);
        $required = [
            'Color', 'color name', 'Color 16',
            'Design', 'DesignDescr',
            'Material', 'MaterialDescr',
            'Range of use', 'RangeOfUseDescr',
            'Transparency', 'TransparencyDescr',
            'Additional characteristics', 'AddCharDescr',
        ];

        foreach ($required as $col) {
            if (!array_key_exists($col, $index)) {
                fclose($handle);
                throw new \RuntimeException("Missing CSV column: {$col}");
            }
        }

        $colorNameVotes = [];
        $colorHexVotes = [];
        $designVotes = [];
        $materialVotes = [];
        $usageVotes = [];
        $transparencyVotes = [];
        $characteristicVotes = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === 1 && trim((string) $row[0]) === '') {
                continue;
            }

            $this->votePairs(
                $this->cell($row, $index, 'Color'),
                $this->cell($row, $index, 'color name'),
                $colorNameVotes
            );
            $this->votePairs(
                $this->cell($row, $index, 'Color'),
                $this->cell($row, $index, 'Color 16'),
                $colorHexVotes
            );
            $this->votePairs(
                $this->cell($row, $index, 'Design'),
                $this->cell($row, $index, 'DesignDescr'),
                $designVotes
            );
            $this->votePairs(
                $this->cell($row, $index, 'Material'),
                $this->cell($row, $index, 'MaterialDescr'),
                $materialVotes
            );
            $this->votePairs(
                $this->cell($row, $index, 'Range of use'),
                $this->cell($row, $index, 'RangeOfUseDescr'),
                $usageVotes
            );
            $this->votePairs(
                $this->cell($row, $index, 'Transparency'),
                $this->cell($row, $index, 'TransparencyDescr'),
                $transparencyVotes
            );
            $this->votePairs(
                $this->cell($row, $index, 'Additional characteristics'),
                $this->cell($row, $index, 'AddCharDescr'),
                $characteristicVotes
            );
        }

        fclose($handle);

        $colors = [];
        foreach ($this->majorityMap($colorNameVotes) as $externalId => $name) {
            $hex = $this->majorityMap($colorHexVotes)[$externalId] ?? '#000000';
            $colors[$externalId] = [
                'name' => $name,
                'color_value' => $this->normalizeHex($hex),
            ];
        }

        return [
            'colors' => $colors,
            'designs' => $this->majorityMap($designVotes),
            'materials' => $this->majorityMap($materialVotes),
            'transparencies' => $this->majorityMap($transparencyVotes),
            'characteristics' => $this->majorityMap($characteristicVotes),
            'usages' => $this->majorityMap($usageVotes),
        ];
    }

    private function cell(array $row, array $index, string $column): string
    {
        $i = $index[$column];

        return isset($row[$i]) ? (string) $row[$i] : '';
    }

    /**
     * Zip client IDs with values and accumulate votes.
     * Uses positional pairing up to min(count); leftover IDs/names on mismatch rows are skipped.
     *
     * @param  array<int, array<string, int>>  $votes
     */
    private function votePairs(string $idsRaw, string $valuesRaw, array &$votes): void
    {
        $ids = $this->splitList($idsRaw);
        $values = $this->splitList($valuesRaw);

        if (empty($ids) || empty($values)) {
            return;
        }

        $pairCount = min(count($ids), count($values));

        for ($i = 0; $i < $pairCount; $i++) {
            $id = $ids[$i];
            if (!ctype_digit($id)) {
                continue;
            }

            $externalId = (int) $id;
            $value = $values[$i];
            if ($value === '') {
                continue;
            }

            if (!isset($votes[$externalId])) {
                $votes[$externalId] = [];
            }
            if (!isset($votes[$externalId][$value])) {
                $votes[$externalId][$value] = 0;
            }
            $votes[$externalId][$value]++;
        }
    }

    /**
     * @param  array<int, array<string, int>>  $votes
     * @return array<int, string>
     */
    private function majorityMap(array $votes): array
    {
        $map = [];
        ksort($votes, SORT_NUMERIC);

        foreach ($votes as $externalId => $options) {
            arsort($options);
            $map[(int) $externalId] = (string) array_key_first($options);
        }

        return $map;
    }

    /**
     * @return list<string>
     */
    private function splitList(string $raw): array
    {
        if (trim($raw) === '') {
            return [];
        }

        return array_values(array_filter(array_map(function ($part) {
            return trim(ltrim($part, "\t"));
        }, explode(',', $raw)), fn ($part) => $part !== ''));
    }

    private function normalizeHex(string $hex): string
    {
        $hex = strtolower(trim(ltrim($hex, "\t")));
        if ($hex === '') {
            return '#000000';
        }
        if ($hex[0] !== '#') {
            $hex = '#' . $hex;
        }

        return $hex;
    }

    /**
     * Ensure all master names (including non-CSV seed rows) are title-cased in DB.
     */
    private function titleCaseExistingNames(): void
    {
        $models = [
            Color::class,
            Design::class,
            Material::class,
            Transparency::class,
            Characteristic::class,
            Usage::class,
        ];

        foreach ($models as $modelClass) {
            $modelClass::query()->orderBy('id')->each(function ($row) {
                $titled = HasTitleCaseName::toTitleCaseName($row->getRawOriginal('name'));
                if ($titled !== null && $titled !== $row->getRawOriginal('name')) {
                    // Write raw to avoid double-transform noise; mutator also title-cases.
                    $row->name = $titled;
                    $row->save();
                }
            });
        }
    }

    /**
     * @param  array<int, array{name: string, color_value: string}>  $colors
     */
    private function upsertColors(array $colors): void
    {
        $created = 0;
        $updated = 0;
        $linked = 0;

        foreach ($colors as $externalId => $data) {
            $result = $this->upsertMaster(
                Color::class,
                (int) $externalId,
                HasTitleCaseName::toTitleCaseName($data['name']) ?? $data['name'],
                [
                    'name' => HasTitleCaseName::toTitleCaseName($data['name']) ?? $data['name'],
                    'color_value' => $data['color_value'],
                    'status' => 'active',
                ]
            );

            if ($result === 'created') {
                $created++;
            } elseif ($result === 'linked') {
                $linked++;
            } else {
                $updated++;
            }
        }

        $this->line("colors: {$created} created, {$linked} linked by name, {$updated} updated");
    }

    /**
     * @param  class-string  $modelClass
     * @param  array<int, string>  $items
     */
    private function upsertNamed(string $modelClass, array $items, string $label): void
    {
        $created = 0;
        $updated = 0;
        $linked = 0;

        foreach ($items as $externalId => $name) {
            $titled = HasTitleCaseName::toTitleCaseName($name) ?? $name;

            $result = $this->upsertMaster(
                $modelClass,
                (int) $externalId,
                $titled,
                [
                    'name' => $titled,
                    'status' => 'active',
                ]
            );

            if ($result === 'created') {
                $created++;
            } elseif ($result === 'linked') {
                $linked++;
            } else {
                $updated++;
            }
        }

        $this->line("{$label}: {$created} created, {$linked} linked by name, {$updated} updated");
    }

    /**
     * 1) Match by external_id → overwrite name/values
     * 2) Else match existing row by name (case-insensitive) with no external_id → overwrite + set external_id
     * 3) Else create
     *
     * @param  class-string  $modelClass
     * @param  array<string, mixed>  $attributes
     * @return 'created'|'linked'|'updated'
     */
    private function upsertMaster(string $modelClass, int $externalId, string $name, array $attributes): string
    {
        $attributes['external_id'] = $externalId;
        $attributes['name'] = $name;
        $attributes['status'] = $attributes['status'] ?? 'active';

        $existingByExternal = $modelClass::query()
            ->where('external_id', $externalId)
            ->first();

        if ($existingByExternal) {
            $existingByExternal->fill($attributes);
            $existingByExternal->save();

            return 'updated';
        }

        // Reuse existing seeded/admin row with same name and overwrite with CSV values
        $existingByName = $modelClass::query()
            ->whereNull('external_id')
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->orderBy('id')
            ->first();

        if ($existingByName) {
            $existingByName->fill($attributes);
            $existingByName->save();

            return 'linked';
        }

        $modelClass::query()->create($attributes);

        return 'created';
    }
}
