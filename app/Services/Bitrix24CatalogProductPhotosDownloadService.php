<?php

namespace App\Services;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class Bitrix24CatalogProductPhotosDownloadService
{
    protected string $dbConnection;

    protected bool $verifySsl;

    protected int $productIblockId;

    protected string $photoFieldName;

    protected int $photoDownloadTimeout;

    protected int $photoDownloadRetries;

    protected int $photoDownloadRetrySleepMs;

    public function __construct()
    {
        $this->dbConnection = (string) config('services.bitrix24.db_connection', 'diller');
        $this->verifySsl = (bool) config('services.bitrix24.verify_ssl', true);
        $this->productIblockId = (int) config('services.bitrix24.product_iblock_id', 14);
        $this->photoFieldName = trim((string) config('services.bitrix24.photo_property_field', 'property172'));
        $this->photoDownloadTimeout = max(30, (int) config('services.bitrix24.photo_download_timeout', 300));
        $this->photoDownloadRetries = max(1, (int) config('services.bitrix24.photo_download_retries', 4));
        $this->photoDownloadRetrySleepMs = max(0, (int) config('services.bitrix24.photo_download_retry_sleep_ms', 3000));
    }

    public function run(Command $command, ?int $rootSectionId = null, ?int $fromProductId = null): int
    {
        $rootSectionId ??= (int) config('services.bitrix24.root_section_id', 22);
        if ($fromProductId !== null && $fromProductId <= 0) {
            $fromProductId = null;
        }

        $baseUrl = rtrim((string) config('services.bitrix24.rest_url'), '/');
        if ($baseUrl === '') {
            $command->error('BITRIX24_CATALOG_URL не задан в .env');

            return Command::FAILURE;
        }

        if (! Schema::connection($this->dbConnection)->hasTable('bitrix24_catalog_products')) {
            $command->error("Таблица bitrix24_catalog_products не найдена (подключение {$this->dbConnection}).");

            return Command::FAILURE;
        }

        $allowedSectionIds = $this->allowedSectionBitrixIdsUnderRoot($rootSectionId);
        $command->line('Только раздел Bitrix id '.$rootSectionId.' и подразделы (не excluded): '.count($allowedSectionIds).' разделов в дереве.');
        if (Schema::connection($this->dbConnection)->hasTable('bitrix24_catalog_sections')
            && DB::connection($this->dbConnection)->table('bitrix24_catalog_sections')->count() === 0) {
            $command->warn('Таблица bitrix24_catalog_sections пуста — выполните php artisan bitrix:catalog-sync, иначе фильтр по дереву разделов неполный.');
        }

        $disk = Storage::disk('public');
        $disk->makeDirectory('bitrix-catalog');

        $command->line('Загрузка фото из Bitrix24 (поле '.$this->photoFieldName.')…');
        $command->line("  Таймаут файла: {$this->photoDownloadTimeout}s, попыток: {$this->photoDownloadRetries}, пауза: {$this->photoDownloadRetrySleepMs}ms (BITRIX24_PHOTO_DOWNLOAD_*)");
        if ($fromProductId !== null && $fromProductId > 0) {
            $command->line("  С товара bitrix id >= {$fromProductId} (меньшие id пропускаются).");
        }

        $db = DB::connection($this->dbConnection);
        $ok = 0;
        $skipped = 0;
        $errors = 0;

        $notInCatalog = 0;
        $outsideSectionTree = 0;
        $skippedBelowFrom = 0;
        $pagesSkippedByFrom = 0;

        $processProduct = function (array $p) use (
            $command,
            $baseUrl,
            $disk,
            $db,
            $allowedSectionIds,
            $fromProductId,
            &$ok,
            &$skipped,
            &$errors,
            &$notInCatalog,
            &$outsideSectionTree,
            &$skippedBelowFrom
        ): void {
            $productId = (int) ($p['id'] ?? 0);
            if ($productId === 0) {
                return;
            }

            if ($fromProductId !== null && $fromProductId > 0 && $productId < $fromProductId) {
                $skippedBelowFrom++;

                return;
            }

            $sectionId = (int) ($p['iblockSectionId'] ?? $p['IBLOCK_SECTION_ID'] ?? 0);
            if ($sectionId === 0 || ! isset($allowedSectionIds[$sectionId])) {
                $outsideSectionTree++;

                return;
            }

            $raw = $p[$this->photoFieldName] ?? $p[mb_strtoupper($this->photoFieldName)] ?? null;
            $fileIds = $this->fileIdsFromPropertyValue($raw);
            if ($fileIds === []) {
                $skipped++;

                return;
            }

            if (! $db->table('bitrix24_catalog_products')->where('bitrix_id', $productId)->exists()) {
                $notInCatalog++;

                return;
            }

            $relativePaths = [];
            foreach ($fileIds as $fileId) {
                $rel = $this->downloadOneFile($baseUrl, $productId, $fileId, $disk);
                if ($rel === null) {
                    $errors++;
                    Log::warning('Bitrix24 photo download failed', [
                        'productId' => $productId,
                        'fileId' => $fileId,
                    ]);

                    continue;
                }
                $relativePaths[] = $rel;
            }

            if ($relativePaths === []) {
                return;
            }

            $serialized = $this->serializeRawProperty172($raw);
            $gallery = array_values(array_unique($relativePaths));
            $first = $gallery[0];
            $galleryJson = json_encode($gallery, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $db->table('bitrix24_catalog_products')
                ->where('bitrix_id', $productId)
                ->update([
                    'image_url' => $first,
                    'gallery_json' => $galleryJson,
                    'photo_property_raw' => $serialized,
                ]);

            $ok++;
            $command->line('  OK #'.$productId.' — файлов: '.count($gallery));
        };

        if ($this->streamProductsFromApi($baseUrl, $fromProductId, $processProduct, $pagesSkippedByFrom, $skippedBelowFrom) === null) {
            $command->error('Не удалось получить список товаров из Bitrix24.');

            return Command::FAILURE;
        }

        $command->info("Готово. Обновлено товаров: {$ok}, без фото в поле: {$skipped}, не в дереве раздела {$rootSectionId}: {$outsideSectionTree}, не в локальном каталоге: {$notInCatalog}, ошибок файла: {$errors}");
        if ($fromProductId !== null && $fromProductId > 0) {
            $command->line("Пропущено (id < {$fromProductId}): {$skippedBelowFrom}, страниц API пропущено целиком: {$pagesSkippedByFrom}.");
        }
        $command->line('Файлы: storage/app/public/bitrix-catalog/ (URL: /storage/bitrix-catalog/...)');

        return Command::SUCCESS;
    }

    /**
     * Постранично читает catalog.product.list (order id ASC) и вызывает $processProduct.
     * При --from: целиком пропускает страницы, где все id меньше порога (меньше запросов к диску/БД).
     *
     * @param  \Closure(array<string, mixed>):void  $processProduct
     * @return bool|null null при ошибке HTTP
     */
    protected function streamProductsFromApi(
        string $baseUrl,
        ?int $fromProductId,
        \Closure $processProduct,
        int &$pagesSkippedByFrom,
        int &$skippedBelowFrom
    ): ?bool {
        $url = $baseUrl.'/catalog.product.list';
        $start = 0;
        $pageSize = 50;
        $select = [
            'id',
            'iblockId',
            'name',
            'iblockSectionId',
            'active',
            $this->photoFieldName,
        ];

        do {
            $response = Http::timeout(60)->withOptions(['verify' => $this->verifySsl])->get($url, [
                'select' => $select,
                'filter' => ['iblockId' => $this->productIblockId],
                'order' => ['id' => 'ASC'],
                'start' => $start,
            ]);

            if (! $response->successful()) {
                Log::error('Bitrix24CatalogProductPhotos: catalog.product.list failed', [
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

            $idsOnPage = [];
            foreach ($list as $row) {
                $arr = is_object($row) ? (array) $row : $row;
                $pid = (int) ($arr['id'] ?? $arr['ID'] ?? 0);
                if ($pid > 0) {
                    $idsOnPage[] = $pid;
                }
            }

            if ($fromProductId !== null && $fromProductId > 0 && $idsOnPage !== [] && max($idsOnPage) < $fromProductId) {
                $pagesSkippedByFrom++;
                $skippedBelowFrom += count($list);
                $start += $pageSize;
                if (count($list) < $pageSize) {
                    break;
                }

                continue;
            }

            foreach ($list as $row) {
                $arr = is_object($row) ? (array) $row : $row;
                $processProduct($arr);
            }

            $start += $pageSize;
        } while (count($list) >= $pageSize);

        return true;
    }

    /**
     * Разделы с bitrix_id в поддереве корня (включая корень), только где excluded = false.
     *
     * @return array<int, true>
     */
    protected function allowedSectionBitrixIdsUnderRoot(int $rootSectionId): array
    {
        if (! Schema::connection($this->dbConnection)->hasTable('bitrix24_catalog_sections')) {
            return [$rootSectionId => true];
        }

        $rows = DB::connection($this->dbConnection)
            ->table('bitrix24_catalog_sections')
            ->where('excluded', false)
            ->get(['bitrix_id', 'parent_bitrix_id']);

        $childrenByParent = [];
        foreach ($rows as $r) {
            $pid = (int) $r->parent_bitrix_id;
            $cid = (int) $r->bitrix_id;
            $childrenByParent[$pid][] = $cid;
        }

        $allowed = [$rootSectionId => true];
        $stack = $childrenByParent[$rootSectionId] ?? [];
        while ($stack !== []) {
            $cid = (int) array_pop($stack);
            if (isset($allowed[$cid])) {
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
     * @return list<int>
     */
    public function fileIdsFromPropertyValue(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_numeric($value)) {
            $id = (int) $value;

            return $id > 0 ? [$id] : [];
        }

        if (is_string($value)) {
            $trim = trim($value);
            if ($trim === '') {
                return [];
            }
            $decoded = json_decode($trim, true);
            if (is_array($decoded)) {
                return $this->fileIdsFromPropertyValue($decoded);
            }

            return array_values(array_unique(array_filter(
                array_map('intval', preg_split('/\s*,\s*/', $trim) ?: [])
            )));
        }

        if (is_array($value)) {
            $ids = [];
            foreach ($value as $item) {
                if (is_numeric($item)) {
                    $n = (int) $item;
                    if ($n > 0) {
                        $ids[] = $n;
                    }
                } elseif (is_array($item)) {
                    foreach (['id', 'ID', 'fileId', 'FILE_ID'] as $k) {
                        if (isset($item[$k]) && is_numeric($item[$k])) {
                            $n = (int) $item[$k];
                            if ($n > 0) {
                                $ids[] = $n;
                            }
                            break;
                        }
                    }
                    $ids = array_merge($ids, $this->fileIdsFromPropertyValue($item));
                } elseif (is_object($item)) {
                    $ids = array_merge($ids, $this->fileIdsFromPropertyValue((array) $item));
                }
            }

            return array_values(array_unique(array_filter($ids, fn (int $i) => $i > 0)));
        }

        return [];
    }

    protected function serializeRawProperty172(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if (is_array($raw)) {
            return json_encode($raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return trim((string) $raw);
    }

    protected function downloadOneFile(string $baseUrl, int $productId, int $fileId, Filesystem $disk): ?string
    {
        $url = $baseUrl.'/catalog.product.download';
        $query = [
            'fields' => [
                'fieldName' => $this->photoFieldName,
                'fileId' => $fileId,
                'productId' => $productId,
            ],
        ];

        $lastError = null;

        for ($attempt = 1; $attempt <= $this->photoDownloadRetries; $attempt++) {
            try {
                $response = Http::timeout($this->photoDownloadTimeout)
                    ->withOptions(['verify' => $this->verifySsl])
                    ->get($url, $query);
            } catch (ConnectionException $e) {
                $lastError = $e->getMessage();
                Log::warning('Bitrix24 catalog.product.download connection error', [
                    'productId' => $productId,
                    'fileId' => $fileId,
                    'attempt' => $attempt,
                    'message' => $lastError,
                ]);
                if ($attempt < $this->photoDownloadRetries && $this->photoDownloadRetrySleepMs > 0) {
                    usleep($this->photoDownloadRetrySleepMs * 1000);
                }

                continue;
            }

            if (! $response->successful()) {
                if ($attempt < $this->photoDownloadRetries && $response->status() >= 500) {
                    usleep($this->photoDownloadRetrySleepMs * 1000);

                    continue;
                }

                return null;
            }

            $body = $response->body();
            if ($body === '' || str_starts_with(ltrim($body), '{')) {
                $json = json_decode($body, true);
                if (is_array($json) && isset($json['error'])) {
                    return null;
                }
            }

            $ext = $this->extensionFromContentType($response->header('Content-Type'));
            $relative = "bitrix-catalog/{$productId}/{$fileId}.{$ext}";
            $disk->put($relative, $body);

            return 'storage/'.$relative;
        }

        if ($lastError !== null) {
            Log::error('Bitrix24 catalog.product.download exhausted retries', [
                'productId' => $productId,
                'fileId' => $fileId,
                'message' => $lastError,
            ]);
        }

        return null;
    }

    protected function extensionFromContentType(?string $contentType): string
    {
        $ct = strtolower((string) $contentType);
        if (str_contains($ct, 'image/jpeg') || str_contains($ct, 'image/jpg')) {
            return 'jpg';
        }
        if (str_contains($ct, 'image/png')) {
            return 'png';
        }
        if (str_contains($ct, 'image/webp')) {
            return 'webp';
        }
        if (str_contains($ct, 'image/gif')) {
            return 'gif';
        }

        return 'bin';
    }
}
