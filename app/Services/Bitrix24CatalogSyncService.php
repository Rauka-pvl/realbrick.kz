<?php

namespace App\Services;

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

    public function __construct()
    {
        $this->dbConnection = (string) config('services.bitrix24.db_connection', 'diller');
        $this->verifySsl = (bool) config('services.bitrix24.verify_ssl', true);
        $this->debugRaw = (bool) env('BITRIX24_DEBUG_RAW', false);
        $this->iblockId = (int) config('services.bitrix24.iblock_id', 14);
        $this->productIblockId = (int) config('services.bitrix24.product_iblock_id', 14);
        $this->rootSectionId = (int) config('services.bitrix24.root_section_id', 22);
        $this->excludedRootNames = config('services.bitrix24.excluded_root_section_names', []);
    }

    public function sync(): bool
    {
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

        $sections = $this->fetchAllSections($baseUrl);
        $products = $this->fetchAllProducts($baseUrl);
        $priceMap = $this->fetchAllPrices($baseUrl) ?? [];

        if ($sections === null || $products === null) {
            return false;
        }

        $sectionMap = [];
        foreach ($sections as $s) {
            $sectionMap[$s['id']] = $s;
        }

        $this->markExcludedSections($sections, $sectionMap);
        $this->buildSectionPaths($sections, $sectionMap);

        $productsToInsert = [];
        $productPaths = [];
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
            $productsToInsert[] = $p;
            $productPaths[] = $path;
        }

        $db = DB::connection($this->dbConnection);
        $db->transaction(function () use ($sections, $productsToInsert, $productPaths, $db) {
            $db->table('bitrix24_catalog_products')->delete();
            $db->table('bitrix24_catalog_sections')->delete();

            $now = now();
            foreach ($sections as $s) {
                if ($s['excluded'] ?? false) {
                    continue;
                }
                $db->table('bitrix24_catalog_sections')->insert([
                    'bitrix_id' => $s['id'],
                    'name' => $s['name'],
                    'parent_bitrix_id' => $s['iblockSectionId'] ?? 0,
                    'path_parts' => json_encode($s['path_parts'] ?? []),
                    'excluded' => $s['excluded'] ?? false,
                    'synced_at' => $now,
                ]);
            }

            foreach ($productsToInsert as $i => $p) {
                $path = $productPaths[$i] ?? ['Каталог', $p['name']];
                $db->table('bitrix24_catalog_products')->insert([
                    'bitrix_id' => $p['id'],
                    'name' => $p['name'],
                    'section_bitrix_id' => $p['iblockSectionId'] ?? null,
                    'path_parts' => json_encode($path),
                    'active' => ($p['active'] ?? 'Y') === 'Y',
                    'price_value' => $p['priceValue'] ?? null,
                    'price_currency' => $p['priceCurrency'] ?? null,
                    'property_50' => $p['property50'] ?? null,
                    'property_130' => $p['property130'] ?? null,
                    'property_186' => $p['property186'] ?? null,
                    'synced_at' => $now,
                ]);
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

    protected function fetchAllSections(string $baseUrl): ?array
    {
        $url = $baseUrl . '/catalog.section.list';
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

    protected function fetchAllProducts(string $baseUrl): ?array
    {
        $url = $baseUrl . '/catalog.product.list';
        $out = [];
        $start = 0;
        $pageSize = 50;

        do {
            $response = Http::timeout(30)->withOptions(['verify' => $this->verifySsl])->get($url, [
                'select' => [
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
                ],
                'filter' => ['iblockId' => $this->productIblockId],
                'order' => ['name' => 'ASC'],
                'start' => $start,
            ]);

            if (! $response->successful()) {
                Log::error('Bitrix24CatalogSync: catalog.product.list failed', [
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
            if ($this->debugRaw && $start === 0 && ! empty($list)) {
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
                $out[] = [
                    'id' => (int) $id,
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
                ];
            }
            $start += $pageSize;
        } while (count($list) >= $pageSize);

        return $out;
    }

    protected function fetchAllPrices(string $baseUrl): ?array
    {
        $url = $baseUrl . '/catalog.price.list';
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
                    'priceCurrency' => $this->extractProductCurrency($arr) ?? 'KZT',
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
}
