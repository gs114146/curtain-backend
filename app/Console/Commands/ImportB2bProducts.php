<?php

namespace App\Console\Commands;

use App\Models\Characteristic;
use App\Models\Color;
use App\Models\Concerns\HasTitleCaseName;
use App\Models\Design;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\ProductVariant;
use App\Models\Transparency;
use App\Models\Usage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportB2bProducts extends Command
{
    protected $signature = 'import:b2b-products
                            {--path= : Absolute or relative path to the B2B CSV file}
                            {--dry-run : Parse and report without writing to the database}
                            {--limit= : Import only the first N hangers (qualities)}
                            {--force : Recreate products that already exist by name}';

    protected $description = 'Import B2B curtain products + variants from CSV, matching hanger/variant images or using a placeholder';

    private const PLACEHOLDER = 'images/product-placeholder.svg';

    private const HANGER_ALIASES = [
        'CARIBBEAN' => 'CARIBEAN',
    ];

    /** @var array<int, int> external_id => local id */
    private array $colorMap = [];

    /** @var array<int, int> */
    private array $designMap = [];

    /** @var array<int, int> */
    private array $materialMap = [];

    /** @var array<int, int> */
    private array $usageMap = [];

    /** @var array<int, int> */
    private array $transparencyMap = [];

    /** @var array<int, int> */
    private array $characteristicMap = [];

    /** @var array<string, string> normalized => relative path under public/ */
    private array $hangerImageIndex = [];

    /** @var array<string, list<string>> sku => list of relative paths */
    private array $variantImageIndex = [];

    /** @var list<array<string, string>> */
    private array $missingImageRows = [];

    private int $productsCreated = 0;

    private int $productsSkipped = 0;

    private int $variantsCreated = 0;

    private int $hangerImagesMatched = 0;

    private int $hangerImagesPlaceholder = 0;

    private int $variantImagesMatched = 0;

    private int $variantImagesPlaceholder = 0;

    public function handle(): int
    {
        $path = $this->option('path') ?: base_path('B2B Characteristics - curtains.csv');

        if (!is_file($path)) {
            $this->error("CSV not found: {$path}");

            return self::FAILURE;
        }

        if (!is_file(public_path(self::PLACEHOLDER))) {
            $this->error('Placeholder missing: public/' . self::PLACEHOLDER);

            return self::FAILURE;
        }

        $this->info("Reading: {$path}");
        $this->loadMasterMaps();
        $this->buildHangerImageIndex();
        $this->buildVariantImageIndex();

        $groups = $this->readCsvGroupedByQuality($path);
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        if ($limit !== null && $limit > 0) {
            $groups = array_slice($groups, 0, $limit, true);
            $this->warn("Limiting import to {$limit} hanger(s).");
        }

        $this->info('Hangers to import: ' . count($groups));
        $this->info('Variant image SKUs indexed: ' . count($this->variantImageIndex));
        $this->info('Hanger images indexed: ' . count($this->hangerImageIndex));

        if ($this->option('dry-run')) {
            $this->simulate($groups);
            $this->writeMissingImagesReport();
            $this->printSummary();
            $this->warn('Dry run only — no database changes.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar(count($groups));
        $bar->start();

        foreach ($groups as $qualityName => $rows) {
            $this->importQuality($qualityName, $rows);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $reportPath = $this->writeMissingImagesReport();
        $this->printSummary();
        $this->info("Missing image report: {$reportPath}");

        return self::SUCCESS;
    }

    private function loadMasterMaps(): void
    {
        $this->colorMap = Color::query()->whereNotNull('external_id')->pluck('id', 'external_id')->all();
        $this->designMap = Design::query()->whereNotNull('external_id')->pluck('id', 'external_id')->all();
        $this->materialMap = Material::query()->whereNotNull('external_id')->pluck('id', 'external_id')->all();
        $this->usageMap = Usage::query()->whereNotNull('external_id')->pluck('id', 'external_id')->all();
        $this->transparencyMap = Transparency::query()->whereNotNull('external_id')->pluck('id', 'external_id')->all();
        $this->characteristicMap = Characteristic::query()->whereNotNull('external_id')->pluck('id', 'external_id')->all();

        if (empty($this->colorMap)) {
            $this->warn('No colors with external_id found. Run import:b2b-masters first.');
        }
    }

    private function buildHangerImageIndex(): void
    {
        $dir = public_path('AFRICA');
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) ?: [] as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                continue;
            }
            $stem = pathinfo($file, PATHINFO_FILENAME);
            $this->hangerImageIndex[$this->normalizeKey($stem)] = 'AFRICA/' . $file;
        }
    }

    private function buildVariantImageIndex(): void
    {
        $dir = public_path('photos/All Photos');
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) ?: [] as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                continue;
            }

            if (!preg_match('/^([^_]+)_/', $file, $m)) {
                continue;
            }

            $sku = $m[1];
            $this->variantImageIndex[$sku][] = 'photos/All Photos/' . $file;
        }
    }

    /**
     * @return array<string, list<array<string, string>>>
     */
    private function readCsvGroupedByQuality(string $path): array
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

        $header = array_map(fn ($col) => trim(ltrim((string) $col, "\xEF\xBB\xBF")), $header);
        $index = array_flip($header);

        $required = [
            'SKU', 'Quality name', 'Range of use', 'Design', 'Material', 'Composition',
            'Transparency', 'WidthDescr', 'Wingdings comment', 'Washing instructions',
            'Additional characteristics', 'SKU description', 'Color',
            'HorizontalDescr', 'VerticalDescr',
        ];
        foreach ($required as $col) {
            if (!array_key_exists($col, $index)) {
                fclose($handle);
                throw new \RuntimeException("Missing CSV column: {$col}");
            }
        }

        $groups = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === 1 && trim((string) $row[0]) === '') {
                continue;
            }

            $quality = trim((string) ($row[$index['Quality name']] ?? ''));
            $sku = trim((string) ($row[$index['SKU']] ?? ''));
            if ($quality === '' || $sku === '') {
                continue;
            }

            $groups[$quality][] = [
                'sku' => $sku,
                'quality' => $quality,
                'range_of_use' => (string) ($row[$index['Range of use']] ?? ''),
                'design' => (string) ($row[$index['Design']] ?? ''),
                'material' => (string) ($row[$index['Material']] ?? ''),
                'composition' => trim((string) ($row[$index['Composition']] ?? '')),
                'transparency' => (string) ($row[$index['Transparency']] ?? ''),
                'width_descr' => trim((string) ($row[$index['WidthDescr']] ?? '')),
                'wingdings_comment' => trim((string) ($row[$index['Wingdings comment']] ?? '')),
                'washing' => trim((string) ($row[$index['Washing instructions']] ?? '')),
                'additional_characteristics' => (string) ($row[$index['Additional characteristics']] ?? ''),
                'sku_description' => trim((string) ($row[$index['SKU description']] ?? '')),
                'color' => (string) ($row[$index['Color']] ?? ''),
                'horizontal_descr' => trim((string) ($row[$index['HorizontalDescr']] ?? '')),
                'vertical_descr' => trim((string) ($row[$index['VerticalDescr']] ?? '')),
            ];
        }

        fclose($handle);

        return $groups;
    }

    /**
     * @param  array<string, list<array<string, string>>>  $groups
     */
    private function simulate(array $groups): void
    {
        foreach ($groups as $qualityName => $rows) {
            $hangerPath = $this->resolveHangerImage($qualityName);
            if ($hangerPath === self::PLACEHOLDER) {
                $this->hangerImagesPlaceholder++;
                $this->missingImageRows[] = [
                    'type' => 'product',
                    'quality_name' => $qualityName,
                    'sku' => '',
                    'sku_description' => '',
                    'expected_path_hint' => 'AFRICA/' . strtoupper($qualityName) . '.jpg',
                    'reason' => 'not_found',
                ];
            } else {
                $this->hangerImagesMatched++;
            }

            $this->productsCreated++;

            foreach ($rows as $i => $row) {
                $paths = $this->resolveVariantImages($row['sku']);
                if (empty($paths)) {
                    $this->variantImagesPlaceholder++;
                    $this->missingImageRows[] = [
                        'type' => 'variant',
                        'quality_name' => $qualityName,
                        'sku' => $row['sku'],
                        'sku_description' => $row['sku_description'],
                        'expected_path_hint' => $row['sku'] . '_*',
                        'reason' => 'not_found',
                    ];
                } else {
                    $this->variantImagesMatched++;
                }
                $this->variantsCreated++;
            }
        }
    }

    /**
     * @param  list<array<string, string>>  $rows
     */
    private function importQuality(string $qualityName, array $rows): void
    {
        $name = HasTitleCaseName::toTitleCaseName($qualityName) ?? $qualityName;

        $existing = Product::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing && !$this->option('force')) {
            $this->productsSkipped++;

            return;
        }

        DB::transaction(function () use ($name, $qualityName, $rows, $existing) {
            if ($existing && $this->option('force')) {
                $existing->variants()->each(function (ProductVariant $variant) {
                    $variant->media()->delete();
                    $variant->delete();
                });
                $existing->media()->delete();
                $existing->types()->delete();
                $existing->designs()->detach();
                $existing->materials()->detach();
                $existing->characteristics()->detach();
                $existing->usages()->detach();
                $existing->delete();
            }

            $first = $rows[0];
            $hangerPath = $this->resolveHangerImage($qualityName);
            if ($hangerPath === self::PLACEHOLDER) {
                $this->hangerImagesPlaceholder++;
                $this->missingImageRows[] = [
                    'type' => 'product',
                    'quality_name' => $qualityName,
                    'sku' => '',
                    'sku_description' => '',
                    'expected_path_hint' => 'AFRICA/' . strtoupper($qualityName) . '.jpg',
                    'reason' => 'not_found',
                ];
            } else {
                $this->hangerImagesMatched++;
            }

            $directionComment = $this->normalizeDirectionComment($first['wingdings_comment']);
            $product = Product::create([
                'name' => $name,
                'image' => $hangerPath,
                'description' => null,
                'sku' => $this->createUniqueProductSku($name),
                'manual_product_id' => null,
                'price' => null,
                'sale_price' => null,
                'stock_quantity' => null,
                'dimensions' => $first['width_descr'] !== '' ? $first['width_descr'] : null,
                'weight' => 'N/A',
                'horizontal_repeat' => $this->nullableRepeat($first['horizontal_descr']),
                'vertical_repeat' => $this->nullableRepeat($first['vertical_descr']),
                'direction' => '⬍ ' . $directionComment,
                'extra_data' => $first['washing'] !== '' ? $first['washing'] : null,
                'compositions' => $first['composition'] !== '' ? $first['composition'] : null,
                'transparency_id' => $this->mapFirstId($first['transparency'], $this->transparencyMap),
                'usage_id' => $this->mapFirstId($first['range_of_use'], $this->usageMap),
                'type' => 'product_listing',
                'product_base' => 'curtain',
                'status' => 'active',
                'featured' => false,
                'in_stock' => true,
                'slug' => $this->createUniqueSlug($name),
            ]);

            $designIds = $this->mapIds($first['design'], $this->designMap);
            $materialIds = $this->mapIds($first['material'], $this->materialMap);
            $usageIds = $this->mapIds($first['range_of_use'], $this->usageMap);
            $characteristicIds = $this->mapIds($first['additional_characteristics'], $this->characteristicMap);

            if (!empty($designIds)) {
                $product->designs()->sync($designIds);
            }
            if (!empty($materialIds)) {
                $product->materials()->sync($materialIds);
            }
            if (!empty($usageIds)) {
                $product->usages()->sync($usageIds);
            }
            if (!empty($characteristicIds)) {
                $product->characteristics()->sync($characteristicIds);
            }

            $product->syncTypes(['product_listing', 'view_qualities']);

            // Product-level media from hanger image
            $this->createMedia($product->id, null, $hangerPath, $name, 1, true);

            foreach ($rows as $index => $row) {
                $this->importVariant($product, $row, $index);
            }

            $this->productsCreated++;
        });
    }

    /**
     * @param  array<string, string>  $row
     */
    private function importVariant(Product $product, array $row, int $index): void
    {
        $colorId = $this->mapFirstId($row['color'], $this->colorMap);
        if (!$colorId) {
            // Fallback: first imported B2B color, else any color
            $colorId = !empty($this->colorMap)
                ? (int) reset($this->colorMap)
                : (int) Color::query()->value('id');
        }

        if (!$colorId) {
            $this->warn("Skipping variant {$row['sku']} — no color available.");

            return;
        }

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'color_id' => $colorId,
            'article' => $row['sku'],
            'article_description' => $row['sku_description'] !== '' ? $row['sku_description'] : $row['sku'],
            'sku' => $this->createUniqueVariantSku($product->name, $colorId, $row['sku']),
            'price' => null,
            'sale_price' => null,
            'stock_quantity' => null,
            'status' => 'active',
            'in_stock' => true,
            'is_default' => $index === 0,
            'sort_order' => $index + 1,
        ]);

        $paths = $this->resolveVariantImages($row['sku']);
        if (empty($paths)) {
            $this->variantImagesPlaceholder++;
            $this->missingImageRows[] = [
                'type' => 'variant',
                'quality_name' => $row['quality'],
                'sku' => $row['sku'],
                'sku_description' => $row['sku_description'],
                'expected_path_hint' => $row['sku'] . '_*',
                'reason' => 'not_found',
            ];
            $this->createMedia($product->id, $variant->id, self::PLACEHOLDER, $row['sku_description'] ?: $row['sku'], 1, true);
        } else {
            $this->variantImagesMatched++;
            foreach ($paths as $i => $path) {
                $this->createMedia(
                    $product->id,
                    $variant->id,
                    $path,
                    $row['sku_description'] ?: $row['sku'],
                    $i + 1,
                    $i === 0
                );
            }
        }

        $this->variantsCreated++;
    }

    private function createMedia(
        int $productId,
        ?int $variantId,
        string $relativePath,
        string $alt,
        int $sortOrder,
        bool $isPrimary
    ): void {
        $full = public_path($relativePath);
        $mime = is_file($full) ? (mime_content_type($full) ?: null) : null;
        $size = is_file($full) ? filesize($full) : null;
        $ext = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

        ProductMedia::create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'file_path' => $relativePath,
            'file_name' => basename($relativePath),
            'thumbnail' => null,
            'file_type' => $ext !== '' ? $ext : 'image',
            'mime_type' => $mime,
            'file_size' => $size ?: null,
            'sort_order' => $sortOrder,
            'is_primary' => $isPrimary,
            'alt_text' => $alt,
            'status' => 'active',
        ]);
    }

    private function resolveHangerImage(string $qualityName): string
    {
        $key = $this->normalizeKey($qualityName);
        $key = self::HANGER_ALIASES[$key] ?? $key;

        return $this->hangerImageIndex[$key] ?? self::PLACEHOLDER;
    }

    /**
     * @return list<string>
     */
    private function resolveVariantImages(string $sku): array
    {
        return $this->variantImageIndex[$sku] ?? [];
    }

    private function normalizeKey(string $value): string
    {
        $value = Str::ascii($value);
        $value = strtoupper($value);

        return preg_replace('/[^A-Z0-9]+/', '', $value) ?: '';
    }

    private function normalizeDirectionComment(string $comment): string
    {
        $comment = trim($comment);
        if ($comment === '') {
            return 'woven railroaded';
        }

        $comment = str_ireplace('railraoded', 'railroaded', $comment);

        return $comment;
    }

    private function nullableRepeat(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || $value === '0') {
            return null;
        }

        return $value;
    }

    /**
     * @param  array<int, int>  $map
     * @return list<int>
     */
    private function mapIds(string $raw, array $map): array
    {
        $ids = [];
        foreach ($this->splitList($raw) as $externalId) {
            if (!ctype_digit($externalId)) {
                continue;
            }
            $ext = (int) $externalId;
            if (isset($map[$ext])) {
                $ids[] = (int) $map[$ext];
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param  array<int, int>  $map
     */
    private function mapFirstId(string $raw, array $map): ?int
    {
        $ids = $this->mapIds($raw, $map);

        return $ids[0] ?? null;
    }

    /**
     * @return list<string>
     */
    private function splitList(string $raw): array
    {
        if (trim($raw) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($part) => trim(ltrim($part, "\t")),
            explode(',', $raw)
        ), fn ($part) => $part !== ''));
    }

    private function createUniqueProductSku(string $name): string
    {
        $sku = preg_replace('/[^a-zA-Z0-9\s]/', '', $name) ?? '';
        $sku = strtoupper($sku);
        $sku = preg_replace('/\s+/', '-', $sku) ?? '';
        $sku = preg_replace('/-+/', '-', $sku) ?? '';
        $sku = trim($sku, '-');

        if (strlen($sku) > 20) {
            $sku = rtrim(substr($sku, 0, 20), '-');
        }

        $randomDigits = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $timestamp = date('Ymd');
        $sku = $sku . '-' . $randomDigits . '-' . $timestamp;

        $originalSku = $sku;
        $counter = 1;
        while (Product::where('sku', $sku)->exists()) {
            $sku = $originalSku . '-' . $counter;
            $counter++;
        }

        return $sku;
    }

    private function createUniqueVariantSku(string $productName, int $colorId, string $article): string
    {
        $color = Color::find($colorId);
        $colorName = $color ? $color->getRawOriginal('name') : 'UNKNOWN';

        $baseSku = preg_replace('/[^a-zA-Z0-9\s]/', '', $productName) ?? '';
        $baseSku = strtoupper($baseSku);
        $baseSku = preg_replace('/\s+/', '-', $baseSku) ?? '';
        $baseSku = preg_replace('/-+/', '-', $baseSku) ?? '';
        $baseSku = trim($baseSku, '-');

        $colorSku = preg_replace('/[^a-zA-Z0-9\s]/', '', (string) $colorName) ?? '';
        $colorSku = strtoupper($colorSku);
        $colorSku = preg_replace('/\s+/', '-', $colorSku) ?? '';
        $colorSku = preg_replace('/-+/', '-', $colorSku) ?? '';
        $colorSku = trim($colorSku, '-');

        $sku = $baseSku . '-' . $colorSku;
        if (strlen($sku) > 30) {
            $sku = rtrim(substr($sku, 0, 30), '-');
        }

        $randomDigits = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $timestamp = date('Ymd');
        $sku = $sku . '-' . $randomDigits . '-' . $timestamp;

        // Keep article in uniqueness salt when same product/color appears twice
        $originalSku = $sku;
        $counter = 1;
        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = $originalSku . '-' . $counter;
            $counter++;
        }

        return $sku;
    }

    private function createUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        if ($slug === '') {
            $slug = 'product-' . time();
        }

        $originalSlug = $slug;
        $counter = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function writeMissingImagesReport(): string
    {
        $dir = storage_path('app/import-reports');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'b2b-missing-images-' . date('Ymd-His') . '.csv';
        $fullPath = $dir . '/' . $filename;

        $handle = fopen($fullPath, 'w');
        fputcsv($handle, ['type', 'quality_name', 'sku', 'sku_description', 'expected_path_hint', 'reason']);
        foreach ($this->missingImageRows as $row) {
            fputcsv($handle, [
                $row['type'],
                $row['quality_name'],
                $row['sku'],
                $row['sku_description'],
                $row['expected_path_hint'],
                $row['reason'],
            ]);
        }
        fclose($handle);

        return $fullPath;
    }

    private function printSummary(): void
    {
        $this->table(
            ['Metric', 'Count'],
            [
                ['Products created', $this->productsCreated],
                ['Products skipped (already exist)', $this->productsSkipped],
                ['Variants created', $this->variantsCreated],
                ['Hanger images matched', $this->hangerImagesMatched],
                ['Hanger images placeholder', $this->hangerImagesPlaceholder],
                ['Variant images matched', $this->variantImagesMatched],
                ['Variant images placeholder', $this->variantImagesPlaceholder],
                ['Missing image report rows', count($this->missingImageRows)],
            ]
        );
    }
}
