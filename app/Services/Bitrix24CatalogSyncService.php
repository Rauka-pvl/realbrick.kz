<?php

namespace App\Services;

use App\Support\Bitrix24CatalogImageUrls;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Bitrix24CatalogSyncService
{
    protected string $dbConnection;

    protected bool $verifySsl;

    protected bool $debugRaw;

    protected int $iblockId;

    protected int $productIblockId;

    protected int $rootSectionId;

    protected array $excludedRootNames;

    protected string $photoFieldName;

    public function __construct()
    {
        $this->dbConnection = (string) config('services.bitrix24.db_connection', 'diller');
        $this->verifySsl = (bool) config('services.bitrix24.verify_ssl', true);
        $this->debugRaw = (bool) env('BITRIX24_DEBUG_RAW', false);
        $this->iblockId = (int) config('services.bitrix24.iblock_id', 14);
        $this->productIblockId = (int) config('services.bitrix24.product_iblock_id', 14);
        $this->rootSectionId = (int) config('services.bitrix24.root_section_id', 22);
        $this->excludedRootNames = config('services.bitrix24.excluded_root_section_names', []);
        $this->photoFieldName = trim((string) config('services.bitrix24.photo_property_field', 'property172'));
    }

    public function sync(?callable $onProgress = null): bool
    {
        $progress = static function (string $message) use ($onProgress): void {
            if ($onProgress !== null) {
                $onProgress($message);
            }
        };

        $baseUrl = rtrim((string) config('services.bitrix24.rest_url'), '/');
        if ($baseUrl === '') {
            Log::error('Bitrix24CatalogSync: BITRIX24_CATALOG_URL не задан');

            return false;
        }

        if (! $this->catalogTablesExist()) {
            Log::error('Bitrix24CatalogSync: отсутствуют таблицы каталога', [
                'connection' => $this->dbConnection,
                'missing' => ['bitrix24_catalog_sections', 'bitrix24_catalog_products'],
            ]);

            return false;
        }

        $progress('Загрузка разделов из Bitrix24…');
        $sections = $this->fetchAllSections($baseUrl);

        if ($sections === null) {
            return false;
        }

        $sectionMap = [];
        foreach ($sections as $s) {
            $sectionMap[$s['id']] = $s;
        }

        $this->markExcludedSections($sections, $sectionMap);
        $allowedSectionIds = $this->allowedSectionBitrixIdsUnderRoot($sections, $sectionMap);
        foreach ($sections as &$s) {
            if (! isset($allowedSectionIds[$s['id']])) {
                $s['excluded'] = true;
            }
        }
        unset($s);

        $allowedCount = count($allowedSectionIds);
        $progress("Real Brick (раздел {$this->rootSectionId}): {$allowedCount} разделов в дереве");
        $progress('Загрузка товаров из Bitrix24…');
        $products = $this->fetchAllProducts($baseUrl, $allowedSectionIds, $progress);

        $priceMap = [];
        if (filter_var(env('BITRIX24_SYNC_FETCH_ALL_PRICES', false), FILTER_VALIDATE_BOOL)) {
            $progress('Загрузка цен из Bitrix24…');
            $priceMap = $this->fetchAllPrices($baseUrl) ?? [];
        }

        if ($products === null) {
            return false;
        }

        $this->buildSectionPaths($sections, $sectionMap);

        $productsToInsert = [];
        foreach ($products as $p) {
            if (($p['active'] ?? 'Y') !== 'Y') {
                continue;
            }
            $productId = (int) ($p['id'] ?? 0);
            if (($p['priceValue'] ?? null) === null && isset($priceMap[$productId])) {
                $p['priceValue'] = $priceMap[$productId]['priceValue'];
                $p['priceCurrency'] = $priceMap[$productId]['priceCurrency'];
            }
            $sectionId = $p['iblockSectionId'] ?? null;
            $section = $sectionId ? ($sectionMap[$sectionId] ?? null) : null;
            if ($section && ($section['excluded'] ?? false)) {
                continue;
            }
            $path = $section
                ? ($section['path_parts'] ?? ['Каталог'])
                : ['Каталог'];
            $path[] = $p['name'];
            $p['path'] = $path;
            $productsToInsert[] = $p;
        }
        unset($products, $priceMap);

        $db = DB::connection($this->dbConnection);
        $schema = Schema::connection($this->dbConnection);
        $hasImageCols = $schema->hasColumn('bitrix24_catalog_products', 'image_url');
        $hasPhotoPropertyRawCol = $schema->hasColumn('bitrix24_catalog_products', 'photo_property_raw');
        $hasArticleCol = $schema->hasColumn('bitrix24_catalog_products', 'article');

        $hasSectionImageCol = $schema->hasColumn('bitrix24_catalog_sections', 'image_url');
        $preservedSectionImages = [];
        if ($hasSectionImageCol) {
            foreach ($db->table('bitrix24_catalog_sections')->select(['bitrix_id', 'image_url'])->cursor() as $row) {
                $img = isset($row->image_url) ? trim((string) $row->image_url) : '';
                if ($img !== '') {
                    $preservedSectionImages[(int) $row->bitrix_id] = $img;
                }
            }
        }

        $preservedMedia = [];
        if ($hasImageCols) {
            foreach ($db->table('bitrix24_catalog_products')->select(['bitrix_id', 'image_url'])->cursor() as $row) {
                $img = isset($row->image_url) ? trim((string) $row->image_url) : '';
                if ($img !== '') {
                    $preservedMedia[(int) $row->bitrix_id] = $img;
                }
            }
        }

        $progress('Сохранение в БД '.$this->dbConnection.'…');

        $db->transaction(function () use ($sections, $productsToInsert, $db, $preservedMedia, $preservedSectionImages, $hasImageCols, $hasPhotoPropertyRawCol, $hasArticleCol, $hasSectionImageCol, $progress) {
            $db->table('bitrix24_catalog_products')->delete();
            $db->table('bitrix24_catalog_sections')->delete();

            $now = now();
            $sectionRows = [];
            foreach ($sections as $s) {
                if ($s['excluded'] ?? false) {
                    continue;
                }
                $sectionRow = [
                    'bitrix_id' => $s['id'],
                    'name' => $s['name'],
                    'parent_bitrix_id' => $s['iblockSectionId'] ?? 0,
                    'path_parts' => json_encode($s['path_parts'] ?? []),
                    'excluded' => $s['excluded'] ?? false,
                    'synced_at' => $now,
                ];
                if ($hasSectionImageCol) {
                    $sectionRow['image_url'] = $preservedSectionImages[(int) $s['id']] ?? null;
                }
                $sectionRows[] = $sectionRow;
            }
            foreach (array_chunk($sectionRows, 100) as $chunk) {
                $db->table('bitrix24_catalog_sections')->insert($chunk);
            }

            $productRows = [];
            foreach ($productsToInsert as $p) {
                $path = $p['path'] ?? ['Каталог', $p['name']];
                $row = [
                    'bitrix_id' => $p['id'],
                    'name' => $p['name'],
                    'section_bitrix_id' => $p['iblockSectionId'] ?? null,
                    'path_parts' => json_encode($path),
                    'active' => ($p['active'] ?? 'Y') === 'Y',
                    'price_value' => $p['priceValue'] ?? null,
                    'price_currency' => $p['priceCurrency'] ?? null,
                    'size' => $p['property50'] ?? null,
                    'pieces_per_pack' => $p['property130'] ?? null,
                    'units_per_sq_or_lm' => $p['property186'] ?? null,
                    'synced_at' => $now,
                ];
                if ($hasImageCols) {
                    $photoRaw = $this->decodePhotoPropertyRaw((string) ($p['property172Stored'] ?? ''));
                    $apiMedia = $this->mediaFromProperty172((int) $p['id'], $photoRaw);
                    $prevImage = $preservedMedia[(int) $p['id']] ?? null;
                    if ($apiMedia['image_url'] !== null) {
                        $row['image_url'] = $apiMedia['image_url'];
                    } else {
                        if ($this->preferBitrixFallbackUrls() && $this->isLocalStorageImagePath($prevImage)) {
                            $prevImage = null;
                        }
                        $row['image_url'] = $prevImage;
                    }
                    $row['gallery_json'] = null;
                }
                if ($hasPhotoPropertyRawCol) {
                    $row['photo_property_raw'] = $p['property172Stored'] ?? null;
                }
                if ($hasArticleCol) {
                    $row['article'] = $p['property164'] ?? null;
                }
                $productRows[] = $row;
            }
            $totalProducts = count($productRows);
            $savedProducts = 0;
            foreach (array_chunk($productRows, 100) as $chunk) {
                $db->table('bitrix24_catalog_products')->insert($chunk);
                $savedProducts += count($chunk);
                if ($totalProducts > 200) {
                    $progress("  товаров в БД: {$savedProducts} / {$totalProducts}");
                }
            }
        });

        $sectionsCount = count(array_filter($sections, fn ($s) => ! ($s['excluded'] ?? false)));
        Log::info('Bitrix24CatalogSync: синхронизировано', [
            'connection' => $this->dbConnection,
            'sections' => $sectionsCount,
            'products' => count($productsToInsert),
        ]);

        return true;
    }

    /**
     * Заполняет image_url / gallery_json из photo_property_raw (без повторного API sync).
     */
    public function backfillProductImageUrls(): int
    {
        $db = DB::connection($this->dbConnection);
        $schema = Schema::connection($this->dbConnection);
        if (! $schema->hasColumn('bitrix24_catalog_products', 'image_url')
            || ! $schema->hasColumn('bitrix24_catalog_products', 'photo_property_raw')) {
            return 0;
        }

        $updated = 0;
        $rows = $db->table('bitrix24_catalog_products')
            ->select('bitrix_id', 'photo_property_raw')
            ->whereNotNull('photo_property_raw')
            ->where('photo_property_raw', '!=', '')
            ->get();

        foreach ($rows as $row) {
            $productId = (int) $row->bitrix_id;
            $raw = $this->decodePhotoPropertyRaw((string) $row->photo_property_raw);
            $media = $this->mediaFromProperty172($productId, $raw);
            if ($media['image_url'] === null) {
                continue;
            }
            $db->table('bitrix24_catalog_products')
                ->where('bitrix_id', $productId)
                ->update([
                    'image_url' => $media['image_url'],
                    'gallery_json' => null,
                ]);
            $updated++;
        }

        return $updated;
    }

    protected function decodePhotoPropertyRaw(string $stored): mixed
    {
        $trim = trim($stored);
        if ($trim === '') {
            return null;
        }
        $decoded = json_decode($trim, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $trim;
    }

    protected function fetchAllSections(string $baseUrl): ?array
    {
        $url = $baseUrl.'/catalog.section.list';
        $out = [];
        $start = 0;
        $pageSize = 50;

        do {
            $response = Http::timeout(30)->withOptions(['verify' => $this->verifySsl])->get($url, [
                'select' => ['id', 'iblockId', 'name', 'iblockSectionId'],
                'filter' => ['iblockId' => $this->iblockId],
                'order' => ['name' => 'ASC'],
                'start' => $start,
            ]);

            if (! $response->successful()) {
                Log::error('Bitrix24CatalogSync: catalog.section.list failed', [
                    'start' => $start,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $data = $response->json();
            $result = $data['result'] ?? [];
            if (! is_array($result)) {
                $result = (array) $result;
            }
            $raw = isset($result['sections']) ? $result['sections'] : $result;
            $list = array_values(is_array($raw) ? $raw : (array) $raw);

            foreach ($list as $s) {
                $arr = is_object($s) ? (array) $s : $s;
                $id = (int) ($arr['id'] ?? $arr['ID'] ?? 0);
                if ($id === 0) {
                    continue;
                }
                $out[] = [
                    'id' => $id,
                    'name' => $arr['name'] ?? $arr['NAME'] ?? 'Без названия',
                    'iblockSectionId' => (int) ($arr['iblockSectionId'] ?? $arr['IBLOCK_SECTION_ID'] ?? 0),
                    'excluded' => false,
                    'path_parts' => null,
                ];
            }
            $start += $pageSize;
        } while (count($list) >= $pageSize);

        return $out;
    }

    protected function shouldFetchPhotoPropertyInSync(): bool
    {
        $explicit = env('BITRIX24_SYNC_FETCH_PHOTOS');
        if ($explicit !== null && $explicit !== '') {
            return filter_var($explicit, FILTER_VALIDATE_BOOL);
        }

        return (bool) env('BITRIX24_PHOTO_DOWNLOAD_ENABLED', false);
    }

    /**
     * Разделы в поддереве корня Real Brick (включая корень), без excluded-веток.
     *
     * @return array<int, true>
     */
    protected function allowedSectionBitrixIdsUnderRoot(array $sections, array $sectionMap): array
    {
        $childrenByParent = [];
        foreach ($sections as $s) {
            $pid = (int) ($s['iblockSectionId'] ?? 0);
            $cid = (int) $s['id'];
            $childrenByParent[$pid][] = $cid;
        }

        $allowed = [$this->rootSectionId => true];
        $stack = $childrenByParent[$this->rootSectionId] ?? [];
        while ($stack !== []) {
            $cid = (int) array_pop($stack);
            if (isset($allowed[$cid])) {
                continue;
            }
            $sec = $sectionMap[$cid] ?? null;
            if ($sec && ($sec['excluded'] ?? false)) {
                continue;
            }
            $allowed[$cid] = true;
            foreach ($childrenByParent[$cid] ?? [] as $next) {
                $stack[] = (int) $next;
            }
        }

        return $allowed;
    }

    /**
     * @param  array<int, true>  $allowedSectionIds
     */
    protected function fetchAllProducts(string $baseUrl, array $allowedSectionIds, ?callable $onProgress = null): ?array
    {
        $url = $baseUrl.'/catalog.product.list';
        $out = [];
        $seenProductIds = [];
        $pageSize = 50;
        $fetchPhotos = $this->shouldFetchPhotoPropertyInSync();
        $select = [
            'id',
            'iblockId',
            'name',
            'iblockSectionId',
            'active',
            'price',
            'currencyId',
            'property50',
            'property130',
            'property186',
            'property164',
        ];
        if ($fetchPhotos && $this->photoFieldName !== '') {
            $select[] = $this->photoFieldName;
        }

        $sectionIds = array_map('intval', array_keys($allowedSectionIds));
        sort($sectionIds);
        $totalSections = count($sectionIds);
        $sectionIndex = 0;

        foreach ($sectionIds as $sectionBitrixId) {
            $sectionIndex++;
            $start = 0;
            $pageCount = 0;

            do {
                $response = Http::timeout(30)->withOptions(['verify' => $this->verifySsl])->get($url, [
                    'select' => array_values(array_unique($select)),
                    'filter' => [
                        'iblockId' => $this->productIblockId,
                        'active' => 'Y',
                        'iblockSectionId' => $sectionBitrixId,
                    ],
                    'order' => ['name' => 'ASC'],
                    'start' => $start,
                ]);

                if (! $response->successful()) {
                    Log::error('Bitrix24CatalogSync: catalog.product.list failed', [
                        'sectionId' => $sectionBitrixId,
                        'start' => $start,
                        'status' => $response->status(),
                    ]);

                    return null;
                }

                $data = $response->json();
                $result = $data['result'] ?? [];
                if (! is_array($result)) {
                    $result = (array) $result;
                }
                $raw = isset($result['products']) ? $result['products'] : $result;
                $list = array_values(is_array($raw) ? $raw : (array) $raw);
                if ($this->debugRaw && $sectionIndex === 1 && $start === 0 && ! empty($list)) {
                    $firstRaw = is_object($list[0]) ? (array) $list[0] : (array) $list[0];
                    Log::info('Bitrix24CatalogSync: raw product sample', [
                        'endpoint' => 'catalog.product.list',
                        'keys' => array_keys($firstRaw),
                        'product' => $firstRaw,
                    ]);
                }

                foreach ($list as $p) {
                    $arr = is_object($p) ? (array) $p : $p;
                    $id = $arr['id'] ?? $arr['ID'] ?? null;
                    if ($id === null || $id === '') {
                        continue;
                    }
                    $productId = (int) $id;
                    if (isset($seenProductIds[$productId])) {
                        continue;
                    }
                    $seenProductIds[$productId] = true;
                    $rawPhoto = $fetchPhotos && $this->photoFieldName !== ''
                        ? ($arr[$this->photoFieldName] ?? $arr[mb_strtoupper($this->photoFieldName)] ?? null)
                        : null;
                    $out[] = [
                        'id' => $productId,
                        'name' => (string) ($arr['name'] ?? $arr['NAME'] ?? '—'),
                        'iblockSectionId' => isset($arr['iblockSectionId'])
                            ? (int) $arr['iblockSectionId']
                            : (isset($arr['IBLOCK_SECTION_ID']) ? (int) $arr['IBLOCK_SECTION_ID'] : null),
                        'active' => $arr['active'] ?? $arr['ACTIVE'] ?? 'Y',
                        'priceValue' => $this->extractProductPrice($arr),
                        'priceCurrency' => $this->extractProductCurrency($arr),
                        'property50' => $this->extractProductProperty($arr, 'property50'),
                        'property130' => $this->extractProductProperty($arr, 'property130'),
                        'property186' => $this->extractProductProperty($arr, 'property186'),
                        'property164' => $this->extractProductProperty($arr, 'property164'),
                        'property172Stored' => $this->serializeProperty172ForStorage($rawPhoto),
                    ];
                    unset($rawPhoto);
                }
                $pageCount = count($list);
                if ($onProgress !== null && $pageCount > 0) {
                    $onProgress("  раздел {$sectionIndex}/{$totalSections}, товаров: ".count($out));
                }
                unset($data, $result, $raw, $list, $response);
                $start += $pageSize;
            } while ($pageCount >= $pageSize);
        }

        return $out;
    }

    protected function fetchAllPrices(string $baseUrl): ?array
    {
        $url = $baseUrl.'/catalog.price.list';
        $out = [];
        $start = 0;
        $pageSize = 50;

        do {
            $response = Http::timeout(30)->withOptions(['verify' => $this->verifySsl])->get($url, [
                'select' => ['id', 'productId', 'price', 'currency'],
                'order' => ['id' => 'ASC'],
                'start' => $start,
            ]);

            if (! $response->successful()) {
                Log::warning('Bitrix24CatalogSync: catalog.price.list failed', [
                    'start' => $start,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $data = $response->json();
            $result = $data['result'] ?? [];
            if (! is_array($result)) {
                $result = (array) $result;
            }
            $raw = isset($result['prices']) ? $result['prices'] : $result;
            $list = array_values(is_array($raw) ? $raw : (array) $raw);
            if ($this->debugRaw && $start === 0 && ! empty($list)) {
                $firstRaw = is_object($list[0]) ? (array) $list[0] : (array) $list[0];
                Log::info('Bitrix24CatalogSync: raw price sample', [
                    'endpoint' => 'catalog.price.list',
                    'keys' => array_keys($firstRaw),
                    'price' => $firstRaw,
                ]);
            }

            foreach ($list as $priceRow) {
                $arr = is_object($priceRow) ? (array) $priceRow : $priceRow;
                $productId = (int) ($arr['productId'] ?? $arr['PRODUCT_ID'] ?? 0);
                if ($productId <= 0) {
                    continue;
                }
                $priceValue = $this->extractProductPrice($arr);
                if ($priceValue === null) {
                    continue;
                }
                $out[$productId] = [
                    'priceValue' => $priceValue,
                    'priceCurrency' => $this->extractProductCurrency($arr) ?? 'USD',
                ];
            }

            $start += $pageSize;
        } while (count($list) >= $pageSize);

        return $out;
    }

    protected function markExcludedSections(array &$sections, array $sectionMap): void
    {
        foreach ($sections as &$s) {
            $currentId = $s['id'];
            $depth = 0;
            while ($currentId > 0 && $depth < 15) {
                $sec = $sectionMap[$currentId] ?? null;
                if (! $sec) {
                    break;
                }
                $parentId = (int) ($sec['iblockSectionId'] ?? 0);
                if ($parentId === 0 || $parentId === $this->rootSectionId) {
                    if ($this->isRootSectionExcluded($sec['name'] ?? '')) {
                        $s['excluded'] = true;
                    }
                    break;
                }
                $currentId = $parentId;
                $depth++;
            }
        }
    }

    protected function isRootSectionExcluded(string $sectionName): bool
    {
        if (empty($this->excludedRootNames)) {
            return false;
        }
        $name = trim($sectionName);
        foreach ($this->excludedRootNames as $excl) {
            $excl = trim((string) $excl);
            if ($excl === '') {
                continue;
            }
            if ($name === $excl) {
                return true;
            }
            if ($excl !== 'Товары' && mb_strpos($name, $excl) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function buildSectionPaths(array &$sections, array $sectionMap): void
    {
        foreach ($sections as &$s) {
            if ($s['excluded'] ?? false) {
                $s['path_parts'] = [];

                continue;
            }
            $path = [];
            $currentId = $s['id'];
            $depth = 0;
            while ($currentId > 0 && $depth < 15) {
                $sec = $sectionMap[$currentId] ?? null;
                if (! $sec) {
                    break;
                }
                array_unshift($path, $sec['name']);
                $parentId = (int) ($sec['iblockSectionId'] ?? 0);
                if ($parentId === 0 || $parentId === $this->rootSectionId) {
                    break;
                }
                $currentId = $parentId;
                $depth++;
            }
            $s['path_parts'] = empty($path) ? ['Каталог'] : array_merge(['Каталог'], $path);
        }
    }

    protected function catalogTablesExist(): bool
    {
        $schema = Schema::connection($this->dbConnection);

        return $schema->hasTable('bitrix24_catalog_sections')
            && $schema->hasTable('bitrix24_catalog_products');
    }

    protected function extractProductPrice(array $arr): ?float
    {
        $candidates = [
            $arr['price'] ?? null,
            $arr['PRICE'] ?? null,
            $arr['priceValue'] ?? null,
            $arr['PRICE_VALUE'] ?? null,
            $arr['basePrice'] ?? null,
            $arr['BASE_PRICE'] ?? null,
        ];

        foreach ($candidates as $value) {
            if ($value === null || $value === '') {
                continue;
            }
            if (is_array($value)) {
                $nested = $value['price'] ?? $value['PRICE'] ?? $value['value'] ?? $value['VALUE'] ?? null;
                if ($nested === null || $nested === '') {
                    continue;
                }

                return (float) $nested;
            }

            return (float) $value;
        }

        return null;
    }

    protected function extractProductCurrency(array $arr): ?string
    {
        $candidates = [
            $arr['currencyId'] ?? null,
            $arr['CURRENCY_ID'] ?? null,
            $arr['currency'] ?? null,
            $arr['CURRENCY'] ?? null,
            $arr['priceCurrency'] ?? null,
            $arr['PRICE_CURRENCY'] ?? null,
        ];

        foreach ($candidates as $value) {
            if ($value === null || $value === '') {
                continue;
            }
            if (is_array($value)) {
                $nested = $value['currency'] ?? $value['CURRENCY'] ?? $value['currencyId'] ?? $value['CURRENCY_ID'] ?? null;
                if ($nested === null || $nested === '') {
                    continue;
                }

                return mb_strtoupper(trim((string) $nested));
            }

            return mb_strtoupper(trim((string) $value));
        }

        return null;
    }

    protected function extractProductProperty(array $arr, string $propertyKey): ?string
    {
        $candidates = [
            $arr[$propertyKey] ?? null,
            $arr[mb_strtoupper($propertyKey)] ?? null,
            $arr[ucfirst($propertyKey)] ?? null,
        ];

        foreach ($candidates as $value) {
            $normalized = $this->normalizeProductPropertyValue($value);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        return null;
    }

    protected function normalizeProductPropertyValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            if (array_key_exists('value', $value) || array_key_exists('VALUE', $value)) {
                $direct = $value['value'] ?? $value['VALUE'] ?? null;
                if ($direct === null || $direct === '') {
                    return null;
                }
                $direct = trim((string) $direct);

                return ($direct === '' || mb_strtoupper($direct) === 'N') ? null : $direct;
            }

            $flattened = [];
            foreach ($value as $item) {
                if (is_array($item)) {
                    $nested = $item['value'] ?? $item['VALUE'] ?? null;
                    if ($nested !== null && $nested !== '') {
                        $flattened[] = trim((string) $nested);
                    }

                    continue;
                }
                if ($item !== null && $item !== '') {
                    $flattened[] = trim((string) $item);
                }
            }
            $flattened = array_values(array_filter($flattened, fn (string $v) => $v !== '' && mb_strtoupper($v) !== 'N'));
            if ($flattened === []) {
                return null;
            }

            return implode(', ', array_unique($flattened));
        }

        $scalar = trim((string) $value);
        if ($scalar === '' || mb_strtoupper($scalar) === 'N') {
            return null;
        }

        return $scalar;
    }

    protected function serializeProperty172ForStorage(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if (is_array($raw)) {
            return json_encode($raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return trim((string) $raw);
    }

    /**
     * @return array{image_url: ?string, gallery_json: ?string}
     */
    protected function mediaFromProperty172(int $productId, mixed $rawPhoto): array
    {
        if ($productId <= 0 || $rawPhoto === null || $rawPhoto === '') {
            return ['image_url' => null, 'gallery_json' => null];
        }

        $photoService = app(Bitrix24CatalogProductPhotosDownloadService::class);
        $fileIds = $photoService->fileIdsFromPropertyValue($rawPhoto);
        if ($fileIds === []) {
            $pathFromUrl = $this->downloadPathFromPropertyValue($rawPhoto);
            if ($pathFromUrl === null) {
                return ['image_url' => null, 'gallery_json' => null];
            }

            return ['image_url' => $pathFromUrl, 'gallery_json' => null];
        }

        $paths = [];
        foreach ($fileIds as $fileId) {
            $paths[] = Bitrix24CatalogImageUrls::bitrixDownloadPath($productId, $fileId, $this->photoFieldName);
        }

        return [
            'image_url' => $paths[0],
            'gallery_json' => null,
        ];
    }

    protected function preferBitrixFallbackUrls(): bool
    {
        return (bool) env('BITRIX24_IMAGES_FALLBACK_ONLY', false);
    }

    protected function isLocalStorageImagePath(?string $path): bool
    {
        if ($path === null || trim($path) === '') {
            return false;
        }
        $trim = str_replace('\\', '/', trim($path));
        if (preg_match('#^https?://#i', $trim) && str_contains($trim, '/storage/')) {
            return true;
        }

        return str_contains($trim, 'storage/')
            || str_contains($trim, 'bitrix-catalog/');
    }

    protected function downloadPathFromPropertyValue(mixed $rawPhoto): ?string
    {
        $encoded = json_encode($rawPhoto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encoded === false || ! str_contains($encoded, 'catalog.product.download')) {
            return null;
        }
        if (preg_match('#/catalog\.product\.download\?[^"\s]+#', $encoded, $m) !== 1) {
            return null;
        }
        $path = urldecode(stripslashes($m[0]));

        return Bitrix24CatalogImageUrls::pathForStorage($path);
    }
}
