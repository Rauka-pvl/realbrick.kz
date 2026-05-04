<?php

use App\Services\Bitrix24CatalogProductPhotosDownloadService;
use App\Services\Bitrix24CatalogSyncService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bitrix:catalog-sync', function (Bitrix24CatalogSyncService $syncService) {
    $connection = (string) config('services.bitrix24.db_connection', 'diller');
    $restUrl = trim((string) config('services.bitrix24.rest_url', ''));

    if ($restUrl === '') {
        $this->error('BITRIX24_CATALOG_URL не задан. Укажите переменную в .env');

        return self::FAILURE;
    }

    $schema = Schema::connection($connection);
    if (! $schema->hasTable('bitrix24_catalog_sections') || ! $schema->hasTable('bitrix24_catalog_products')) {
        $this->error("Таблицы каталога отсутствуют в подключении '{$connection}'.");
        $this->line("Запустите: php artisan migrate --database={$connection}");

        return self::FAILURE;
    }

    $this->line("Синхронизация Bitrix24 -> {$connection}...");
    $ok = $syncService->sync();
    if (! $ok) {
        $this->error('Синхронизация завершилась с ошибкой. Проверьте laravel.log');

        return self::FAILURE;
    }

    $sections = DB::connection($connection)->table('bitrix24_catalog_sections')->count();
    $products = DB::connection($connection)->table('bitrix24_catalog_products')->count();
    $this->info("Готово. Разделов: {$sections}, товаров: {$products}");

    return self::SUCCESS;
})->purpose('Sync Bitrix24 catalog to local database');

Artisan::command('bitrix:catalog-download-photos {--section= : Корневой раздел Bitrix (по умолчанию BITRIX24_ROOT_SECTION_ID, обычно 22)} {--from= : Минимальный bitrix id товара (обрабатываются только id >= from)}', function (Bitrix24CatalogProductPhotosDownloadService $downloadService) {
    $connection = (string) config('services.bitrix24.db_connection', 'diller');
    $restUrl = trim((string) config('services.bitrix24.rest_url', ''));

    if ($restUrl === '') {
        $this->error('BITRIX24_CATALOG_URL не задан. Укажите переменную в .env');

        return self::FAILURE;
    }

    $schema = Schema::connection($connection);
    if (! $schema->hasTable('bitrix24_catalog_products')) {
        $this->error("Таблица bitrix24_catalog_products не найдена (подключение '{$connection}').");

        return self::FAILURE;
    }

    if (! $schema->hasColumn('bitrix24_catalog_products', 'image_url')) {
        $this->error('Нет колонки image_url. Выполните: php artisan migrate --database='.$connection);

        return self::FAILURE;
    }

    if (! File::exists(public_path('storage'))) {
        $this->warn('Симлинк public/storage отсутствует. Выполните: php artisan storage:link');
    }

    $rootSection = $this->option('section');
    $rootSectionId = ($rootSection !== null && $rootSection !== '')
        ? (int) $rootSection
        : null;

    $fromOpt = $this->option('from');
    $fromProductId = ($fromOpt !== null && $fromOpt !== '')
        ? (int) $fromOpt
        : null;

    return $downloadService->run($this, $rootSectionId, $fromProductId);
})->purpose('Download Bitrix24 product photos into storage/app/public/bitrix-catalog (tree under root section)');
