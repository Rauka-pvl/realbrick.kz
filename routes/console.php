<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use App\Services\Bitrix24CatalogSyncService;

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

    $sections = \Illuminate\Support\Facades\DB::connection($connection)->table('bitrix24_catalog_sections')->count();
    $products = \Illuminate\Support\Facades\DB::connection($connection)->table('bitrix24_catalog_products')->count();
    $this->info("Готово. Разделов: {$sections}, товаров: {$products}");

    return self::SUCCESS;
})->purpose('Sync Bitrix24 catalog to local database');
