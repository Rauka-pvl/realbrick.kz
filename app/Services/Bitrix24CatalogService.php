<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Bitrix24CatalogService
{
    protected int $iblockId;

    /** Iblock для товаров (catalog.product.list); может отличаться от iblock_id разделов. */
    protected int $productIblockId;

    protected int $rootSectionId;

    /** Последняя ошибка API (для отображения при APP_DEBUG на хостинге). */
    protected ?string $lastError = null;

    /** Кэш разделов для построения путей (id => ['name'=>..., 'iblockSectionId'=>...]). */
    protected array $sectionCache = [];

    public function __construct()
    {
        $this->iblockId = (int) config('services.bitrix24.iblock_id', 14);
        $this->productIblockId = (int) config('services.bitrix24.product_iblock_id', 14);
        $this->rootSectionId = (int) config('services.bitrix24.root_section_id', 22);
        $url = config('services.bitrix24.rest_url');
        if (empty($url)) {
            Log::warning('Bitrix24: BITRIX24_CATALOG_URL не задан в .env');
        }
    }

    public function hasCachedCatalog(): bool
    {
        return Schema::hasTable('bitrix24_catalog_sections')
            && DB::table('bitrix24_catalog_sections')->exists();
    }

    /**
     * Список разделов каталога (подразделы по parent section id).
     */
    public function getSections(int $parentSectionId): array
    {
        $this->lastError = null;

        if ($this->hasCachedCatalog()) {
            return $this->getSectionsFromDb($parentSectionId);
        }

        return $this->getSectionsViaHttp($parentSectionId);
    }

    protected function getSectionsFromDb(int $parentSectionId): array
    {
        return DB::table('bitrix24_catalog_sections')
            ->where('parent_bitrix_id', $parentSectionId)
            ->orderBy('name')
            ->get()
            ->map(fn ($row) => ['id' => (int) $row->bitrix_id, 'name' => $row->name])
            ->all();
    }

    /**
     * Список разделов через HTTP.
     */
    protected function getSectionsViaHttp(int $parentSectionId): array
    {
        $baseUrl = rtrim((string) config('services.bitrix24.rest_url'), '/');
        if ($baseUrl === '') {
            return [];
        }
        $url = $baseUrl . '/catalog.section.list';
        $out = [];
        $start = 0;
        $pageSize = 50;
        do {
            $params = [
                'select' => ['id', 'name', 'iblockSectionId'],
                'filter' => [
                    'iblockId' => $this->iblockId,
                    'iblockSectionId' => $parentSectionId,
                ],
                'order' => ['name' => 'ASC'],
                'start' => $start,
            ];
            $response = Http::timeout(15)->get($url, $params);
            if (! $response->successful()) {
                if ($start === 0) {
                    $this->lastError = 'HTTP ' . $response->status();
                    Log::error('Bitrix24 catalog.section.list HTTP failed', [
                        'parentSectionId' => $parentSectionId,
                        'status' => $response->status(),
                    ]);
                }
                break;
            }
            $data = $response->json();
            $result = $data['result'] ?? [];
            if (! is_array($result)) {
                $result = (array) $result;
            }
            $items = $this->normalizeSectionsResult($result);
            foreach ($items as $s) {
                if ($parentSectionId === $this->rootSectionId && $this->isRootSectionExcluded($s['name'] ?? '')) {
                    continue;
                }
                $out[] = $s;
            }
            $start += $pageSize;
        } while (count($items) >= $pageSize);

        return $out;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Нормализация ответа catalog.section.list: result может быть массив с ключом sections или массив разделов.
     */
    protected function normalizeSectionsResult(array $result): array
    {
        $raw = isset($result['sections']) ? $result['sections'] : $result;
        $list = array_values(is_array($raw) ? $raw : (array) $raw);

        $out = [];
        foreach ($list as $s) {
            $arr = is_object($s) ? (array) $s : $s;
            $id = (int) ($arr['id'] ?? $arr['ID'] ?? 0);
            if ($id === 0) {
                continue;
            }
            $out[] = [
                'id' => $id,
                'name' => $arr['name'] ?? $arr['NAME'] ?? 'Без названия',
            ];
        }
        return $out;
    }

    /**
     * Список товаров раздела.
     */
    public function getProducts(int $sectionId): array
    {
        if ($this->hasCachedCatalog()) {
            return $this->getProductsFromDb($sectionId);
        }
        return $this->getProductsViaHttp($sectionId);
    }

    protected function getProductsFromDb(int $sectionId): array
    {
        return DB::table('bitrix24_catalog_products')
            ->where('section_bitrix_id', $sectionId)
            ->where('active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($row) => ['id' => (int) $row->bitrix_id, 'name' => $row->name])
            ->all();
    }

    /**
     * Товары через HTTP.
     */
    protected function getProductsViaHttp(int $sectionId): array
    {
        $baseUrl = rtrim((string) config('services.bitrix24.rest_url'), '/');
        if ($baseUrl === '') {
            return [];
        }
        $url = $baseUrl . '/catalog.product.list';
        $out = [];
        $start = 0;
        $pageSize = 50;
        do {
            $params = [
                'select' => ['id', 'iblockId', 'name'],
                'filter' => [
                    'iblockId' => $this->productIblockId,
                    'iblockSectionId' => $sectionId,
                ],
                'order' => ['name' => 'ASC'],
                'start' => $start,
            ];
            $response = Http::timeout(15)->get($url, $params);
            if (! $response->successful()) {
                if ($start === 0) {
                    Log::warning('Bitrix24 catalog.product.list HTTP failed', [
                        'sectionId' => $sectionId,
                        'status' => $response->status(),
                    ]);
                }
                break;
            }
            $data = $response->json();
            $result = $data['result'] ?? [];
            if (! is_array($result)) {
                $result = (array) $result;
            }
            $items = $this->normalizeProductsResult($result);
            foreach ($items as $p) {
                $out[] = $p;
            }
            $start += $pageSize;
        } while (count($items) >= $pageSize);

        if ($out !== []) {
            return $out;
        }

        // Пусто с фильтром по разделу — пробуем без раздела и фильтруем по iblockSectionId в PHP
        $all = $this->fetchAllProductsViaHttp();
        return array_values(array_filter($all, function ($p) use ($sectionId) {
            $sid = $p['iblockSectionId'] ?? $p['IBLOCK_SECTION_ID'] ?? null;
            return $sid !== null && (int) $sid === $sectionId;
        }));
    }

    /**
     * Все товары инфоблока по HTTP (для фильтрации по разделу в PHP).
     */
    protected function fetchAllProductsViaHttp(): array
    {
        $baseUrl = rtrim((string) config('services.bitrix24.rest_url'), '/');
        if ($baseUrl === '') {
            return [];
        }
        $url = $baseUrl . '/catalog.product.list';
        $out = [];
        $start = 0;
        $pageSize = 50;
        do {
            $params = [
                'select' => ['id', 'iblockId', 'name', 'iblockSectionId'],
                'filter' => ['iblockId' => $this->productIblockId],
                'order' => ['name' => 'ASC'],
                'start' => $start,
            ];
            $response = Http::timeout(15)->get($url, $params);
            if (! $response->successful()) {
                break;
            }
            $data = $response->json();
            $result = $data['result'] ?? [];
            if (! is_array($result)) {
                $result = (array) $result;
            }
            $list = isset($result['products']) ? array_values((array) $result['products']) : array_values($result);
            foreach ($list as $raw) {
                $arr = is_array($raw) ? $raw : (array) $raw;
                $id = $arr['id'] ?? $arr['ID'] ?? null;
                if ($id === null || $id === '') {
                    continue;
                }
                $out[] = [
                    'id' => (int) $id,
                    'name' => (string) ($arr['name'] ?? $arr['NAME'] ?? '—'),
                    'iblockSectionId' => isset($arr['iblockSectionId']) ? (int) $arr['iblockSectionId'] : (isset($arr['IBLOCK_SECTION_ID']) ? (int) $arr['IBLOCK_SECTION_ID'] : null),
                ];
            }
            $start += $pageSize;
        } while (count($list) >= $pageSize);

        return $out;
    }

    /**
     * Разбор ответа catalog.product.list: result может быть ['products' => [...]], объект с id-ключами, или массив элементов.
     */
    protected function normalizeProductsResult(array $result): array
    {
        $raw = isset($result['products']) ? $result['products'] : $result;
        $list = array_values(is_array($raw) ? $raw : (array) $raw);
        $out = [];
        foreach ($list as $p) {
            $arr = is_object($p) ? (array) $p : $p;
            if (! is_array($arr)) {
                continue;
            }
            $id = $arr['id'] ?? $arr['ID'] ?? null;
            if ($id === null || $id === '') {
                continue;
            }
            $name = $arr['name'] ?? $arr['NAME'] ?? '—';
            $out[] = [
                'id' => (int) $id,
                'name' => (string) $name,
            ];
        }
        return $out;
    }

    /**
     * Подразделы и товары одного раздела (для пошаговой подгрузки).
     */
    public function getSectionChildren(int $sectionId): array
    {
        $sections = $this->getSections($sectionId);
        $products = $this->getProducts($sectionId);
        $outSections = [];
        foreach ($sections as $s) {
            $id = (int) ($s['id'] ?? $s['ID'] ?? 0);
            if ($id === 0) {
                continue;
            }
            $outSections[] = ['id' => $id, 'name' => $s['name'] ?? $s['NAME'] ?? 'Без названия'];
        }
        $outProducts = [];
        foreach ($products as $p) {
            $outProducts[] = ['id' => $p['id'] ?? $p['ID'] ?? null, 'name' => $p['name'] ?? $p['NAME'] ?? '—'];
        }
        return ['sections' => $outSections, 'products' => $outProducts];
    }

    /**
     * Поиск товаров и разделов (из БД или Bitrix24 API).
     */
    public function searchProductsAndSections(string $query, int $productLimit = 25, int $sectionLimit = 10): array
    {
        $query = trim($query);
        if ($query === '') {
            return ['products' => [], 'sections' => []];
        }

        if ($this->hasCachedCatalog()) {
            return $this->searchFromDb($query, $productLimit, $sectionLimit);
        }

        return $this->searchProductsAndSectionsViaHttp($query, $productLimit, $sectionLimit);
    }

    protected function searchFromDb(string $query, int $productLimit, int $sectionLimit): array
    {
        $like = '%' . addcslashes($query, '%_\\') . '%';

        $products = DB::table('bitrix24_catalog_products')
            ->where('active', true)
            ->where('name', 'like', $like)
            ->orderBy('name')
            ->limit($productLimit)
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->bitrix_id,
                'name' => $row->name,
                'path' => json_decode($row->path_parts, true) ?: ['Каталог', $row->name],
            ])
            ->all();

        $sections = DB::table('bitrix24_catalog_sections')
            ->where('name', 'like', $like)
            ->orderBy('name')
            ->limit($sectionLimit)
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->bitrix_id,
                'name' => $row->name,
                'path' => json_decode($row->path_parts, true) ?: ['Каталог', $row->name],
            ])
            ->all();

        return ['products' => $products, 'sections' => $sections];
    }

    protected function searchProductsAndSectionsViaHttp(string $query, int $productLimit, int $sectionLimit): array
    {
        $baseUrl = rtrim((string) config('services.bitrix24.rest_url'), '/');
        if ($baseUrl === '') {
            return ['products' => [], 'sections' => []];
        }

        $productUrl = $baseUrl . '/catalog.product.list';
        $productParams = [
            'select' => ['id', 'iblockId', 'name', 'iblockSectionId'],
            'filter' => [
                'iblockId' => $this->productIblockId,
                'active' => 'Y',
                '%name' => $query,
            ],
            'order' => ['name' => 'ASC'],
            'start' => 0,
        ];

        $sectionUrl = $baseUrl . '/catalog.section.list';
        $sectionParams = [
            'select' => ['id', 'name', 'iblockSectionId'],
            'filter' => [
                'iblockId' => $this->iblockId,
                '%name' => $query,
            ],
            'order' => ['name' => 'ASC'],
            'start' => 0,
        ];

        $responses = Http::pool(fn ($pool) => [
            $pool->as('products')->timeout(15)->get($productUrl, $productParams),
            $pool->as('sections')->timeout(15)->get($sectionUrl, $sectionParams),
        ]);

        $products = $responses['products']->successful()
            ? $this->processProductSearchResult($responses['products']->json(), $productLimit)
            : [];
        $sections = $responses['sections']->successful()
            ? $this->processSectionSearchResult($responses['sections']->json(), $sectionLimit)
            : [];

        return ['products' => $products, 'sections' => $sections];
    }

    protected function processProductSearchResult(array $data, int $limit): array
    {
        $result = $data['result'] ?? [];
        if (! is_array($result)) {
            $result = (array) $result;
        }
        $items = $this->normalizeProductsWithSection($result);
        $out = [];
        foreach ($items as $p) {
            if (count($out) >= $limit) {
                break;
            }
            $sectionId = $p['iblockSectionId'] ?? null;
            if ($sectionId && $this->isSectionUnderExcludedBranch($sectionId)) {
                continue;
            }
            $path = $sectionId ? $this->getSectionPath($sectionId) : ['Каталог'];
            $path[] = $p['name'];
            $out[] = [
                'id' => $p['id'],
                'name' => $p['name'],
                'path' => $path,
            ];
        }
        return $out;
    }

    protected function processSectionSearchResult(array $data, int $limit): array
    {
        $result = $data['result'] ?? [];
        if (! is_array($result)) {
            $result = (array) $result;
        }
        $items = $this->normalizeSectionsResultWithParent($result);
        $out = [];
        foreach ($items as $s) {
            if (count($out) >= $limit) {
                break;
            }
            if ($this->isSectionUnderExcludedBranch($s['id'])) {
                continue;
            }
            $path = $this->getSectionPath($s['id']);
            $out[] = [
                'id' => $s['id'],
                'name' => $s['name'],
                'path' => $path,
            ];
        }
        return $out;
    }

    /**
     * Поиск товаров по названию (LIKE).
     */
    public function searchProducts(string $query, int $limit = 50): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $baseUrl = rtrim((string) config('services.bitrix24.rest_url'), '/');
        if ($baseUrl === '') {
            return [];
        }

        $url = $baseUrl . '/catalog.product.list';
        $params = [
            'select' => ['id', 'iblockId', 'name', 'iblockSectionId'],
            'filter' => [
                'iblockId' => $this->productIblockId,
                'active' => 'Y',
                '%name' => $query,
            ],
            'order' => ['name' => 'ASC'],
            'start' => 0,
        ];

        $response = Http::timeout(15)->get($url, $params);
        if (! $response->successful()) {
            Log::warning('Bitrix24 catalog.product.list search failed', [
                'query' => $query,
                'status' => $response->status(),
            ]);
            return [];
        }

        $data = $response->json();
        $result = $data['result'] ?? [];
        if (! is_array($result)) {
            $result = (array) $result;
        }
        $items = $this->normalizeProductsWithSection($result);
        $out = [];
        foreach ($items as $p) {
            if (count($out) >= $limit) {
                break;
            }
            $sectionId = $p['iblockSectionId'] ?? null;
            if ($sectionId && $this->isSectionUnderExcludedBranch($sectionId)) {
                continue;
            }
            $path = $sectionId ? $this->getSectionPath($sectionId) : ['Каталог'];
            $path[] = $p['name'];
            $out[] = [
                'id' => $p['id'],
                'name' => $p['name'],
                'path' => $path,
            ];
        }
        return $out;
    }

    /**
     * Проверка: название корневого раздела в исключённом списке?
     */
    protected function isRootSectionExcluded(string $sectionName): bool
    {
        $excluded = config('services.bitrix24.excluded_root_section_names', []);
        if (empty($excluded)) {
            return false;
        }
        $name = trim($sectionName);
        foreach ($excluded as $excl) {
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

    /**
     * Проверка: раздел принадлежит исключённой ветке (Модная одежда, Галерея Дизайна и т.п.)?
     */
    protected function isSectionUnderExcludedBranch(int $sectionId): bool
    {
        $currentId = $sectionId;
        $maxDepth = 10;
        $depth = 0;
        while ($currentId > 0 && $depth < $maxDepth) {
            $section = $this->fetchSectionById($currentId);
            if (! $section) {
                break;
            }
            $parentId = (int) ($section['iblockSectionId'] ?? 0);
            if ($parentId === 0 || $parentId === $this->rootSectionId) {
                return $this->isRootSectionExcluded($section['name'] ?? '');
            }
            $currentId = $parentId;
            $depth++;
        }
        return false;
    }

    /**
     * Получить путь раздела: [Каталог, Родитель, ..., Раздел].
     */
    protected function getSectionPath(int $sectionId): array
    {
        $path = [];
        $currentId = $sectionId;
        $maxDepth = 10;
        $depth = 0;

        while ($currentId > 0 && $depth < $maxDepth) {
            $section = $this->fetchSectionById($currentId);
            if (! $section) {
                break;
            }
            array_unshift($path, $section['name']);
            $parentId = (int) ($section['iblockSectionId'] ?? 0);
            if ($parentId === 0 || $parentId === $this->rootSectionId) {
                break;
            }
            $currentId = $parentId;
            $depth++;
        }
        if (empty($path)) {
            return ['Каталог'];
        }
        array_unshift($path, 'Каталог');
        return $path;
    }

    /**
     * Получить раздел по ID (с кэшированием в памяти и Redis/file на 1 час).
     */
    protected function fetchSectionById(int $sectionId): ?array
    {
        if (isset($this->sectionCache[$sectionId])) {
            return $this->sectionCache[$sectionId];
        }

        $cacheKey = 'bitrix24_section_' . $this->iblockId . '_' . $sectionId;
        $section = Cache::remember($cacheKey, 3600, function () use ($sectionId) {
            return $this->fetchSectionByIdFromApi($sectionId);
        });

        if ($section) {
            $this->sectionCache[$sectionId] = $section;
        }
        return $section;
    }

    protected function fetchSectionByIdFromApi(int $sectionId): ?array
    {
        $baseUrl = rtrim((string) config('services.bitrix24.rest_url'), '/');
        if ($baseUrl === '') {
            return null;
        }

        $url = $baseUrl . '/catalog.section.list';
        $params = [
            'select' => ['id', 'name', 'iblockSectionId'],
            'filter' => [
                'iblockId' => $this->iblockId,
                'id' => $sectionId,
            ],
            'start' => 0,
        ];

        $response = Http::timeout(10)->get($url, $params);
        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        $result = $data['result'] ?? [];
        if (! is_array($result)) {
            $result = (array) $result;
        }
        $items = $this->normalizeSectionsResultWithParent($result);
        return $items[0] ?? null;
    }

    /**
     * Нормализация разделов с iblockSectionId.
     */
    protected function normalizeSectionsResultWithParent(array $result): array
    {
        $raw = isset($result['sections']) ? $result['sections'] : $result;
        $list = array_values(is_array($raw) ? $raw : (array) $raw);
        $out = [];
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
            ];
        }
        return $out;
    }

    /**
     * Нормализация товаров с iblockSectionId (для поиска).
     */
    protected function normalizeProductsWithSection(array $result): array
    {
        $raw = isset($result['products']) ? $result['products'] : $result;
        $list = array_values(is_array($raw) ? $raw : (array) $raw);
        $out = [];
        foreach ($list as $p) {
            $arr = is_object($p) ? (array) $p : $p;
            if (! is_array($arr)) {
                continue;
            }
            $id = $arr['id'] ?? $arr['ID'] ?? null;
            if ($id === null || $id === '') {
                continue;
            }
            $out[] = [
                'id' => (int) $id,
                'name' => (string) ($arr['name'] ?? $arr['NAME'] ?? '—'),
                'iblockSectionId' => isset($arr['iblockSectionId']) ? (int) $arr['iblockSectionId'] : (isset($arr['IBLOCK_SECTION_ID']) ? (int) $arr['IBLOCK_SECTION_ID'] : null),
            ];
        }
        return $out;
    }

    /**
     * Поиск разделов по названию (LIKE).
     */
    public function searchSections(string $query, int $limit = 20): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $baseUrl = rtrim((string) config('services.bitrix24.rest_url'), '/');
        if ($baseUrl === '') {
            return [];
        }

        $url = $baseUrl . '/catalog.section.list';
        $params = [
            'select' => ['id', 'name', 'iblockSectionId'],
            'filter' => [
                'iblockId' => $this->iblockId,
                '%name' => $query,
            ],
            'order' => ['name' => 'ASC'],
            'start' => 0,
        ];

        $response = Http::timeout(15)->get($url, $params);
        if (! $response->successful()) {
            return [];
        }

        $data = $response->json();
        $result = $data['result'] ?? [];
        if (! is_array($result)) {
            $result = (array) $result;
        }
        $items = $this->normalizeSectionsResultWithParent($result);

        $out = [];
        foreach ($items as $s) {
            if (count($out) >= $limit) {
                break;
            }
            if ($this->isSectionUnderExcludedBranch($s['id'])) {
                continue;
            }
            $path = $this->getSectionPath($s['id']);
            $out[] = [
                'id' => $s['id'],
                'name' => $s['name'],
                'path' => $path,
            ];
        }
        return $out;
    }

    /**
     * Дерево каталога: секции и товары по корневому разделу.
     */
    public function buildTree(?int $rootSectionId = null): array
    {
        $rootSectionId = $rootSectionId ?? $this->rootSectionId;
        $sections = $this->getSections($rootSectionId);
        $tree = [];

        foreach ($sections as $section) {
            $id = (int) ($section['id'] ?? $section['ID'] ?? 0);
            if ($id === 0) {
                continue;
            }
            $name = $section['name'] ?? $section['NAME'] ?? 'Без названия';
            $tree[] = $this->buildNode($id, $name);
        }

        return $tree;
    }

    protected function buildNode(int $sectionId, string $name): array
    {
        $subSections = $this->getSections($sectionId);
        $products = $this->getProducts($sectionId);

        $children = [];
        foreach ($subSections as $section) {
            $id = (int) ($section['id'] ?? $section['ID'] ?? 0);
            if ($id === 0) {
                continue;
            }
            $childName = $section['name'] ?? $section['NAME'] ?? 'Без названия';
            $children[] = $this->buildNode($id, $childName);
        }

        $productList = [];
        foreach ($products as $product) {
            $productList[] = [
                'id' => $product['id'] ?? $product['ID'] ?? null,
                'name' => $product['name'] ?? $product['NAME'] ?? '—',
            ];
        }

        return [
            'id' => $sectionId,
            'name' => $name,
            'children' => $children,
            'products' => $productList,
        ];
    }
}
