<?php

namespace App\Support;

/**
 * Картинки каталога Bitrix24: в БД — путь без домена и без сегмента вебхука;
 * для img — полный URL с токеном из DILLER_BITRIX24_REST_URL (как BITRIX24_CATALOG_URL в Diller).
 */
final class Bitrix24CatalogImageUrls
{
    /**
     * Локальный файл из storage/app/public: в БД может быть storage/cataglog/..., storage/app/public/... или cataglog/...
     */
    public static function publicAssetUrl(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $trim = str_replace('\\', '/', trim($raw));
        if ($trim === '') {
            return null;
        }

        // Fallback-only mode: skip local storage asset resolution.
        // Absolute URLs are still allowed and returned as-is.
        if ((bool) env('BITRIX24_IMAGES_FALLBACK_ONLY', false) === true) {
            return preg_match('#^https?://#i', $trim) ? $trim : null;
        }

        if (preg_match('#^https?://#i', $trim)) {
            return $trim;
        }

        $trim = ltrim($trim, '/');
        if (str_starts_with($trim, 'storage/app/public/')) {
            $trim = 'storage/'.substr($trim, strlen('storage/app/public/'));
        } elseif (str_starts_with($trim, 'app/public/')) {
            $trim = 'storage/'.substr($trim, strlen('app/public/'));
        } elseif (! str_starts_with($trim, 'storage/')
            && preg_match('#^(?:cataglog|catalog|bitrix-catalog|gallery|img|assets)/#i', $trim)) {
            $trim = 'storage/'.$trim;
        }

        if (str_starts_with($trim, 'storage/')) {
            return asset($trim);
        }

        return null;
    }

    /**
     * Путь для нормализации: "/catalog.product.download?..." (без /rest/ и без user/token).
     */
    public static function pathForStorage(?string $rel): ?string
    {
        if ($rel === null) {
            return null;
        }
        $rel = trim($rel);
        if ($rel === '') {
            return null;
        }
        if (str_starts_with($rel, 'http://') || str_starts_with($rel, 'https://')) {
            $path = (string) (parse_url($rel, PHP_URL_PATH) ?? '');
            $query = parse_url($rel, PHP_URL_QUERY);
            if ($path !== '' && str_contains($path, 'catalog.product.download')) {
                return '/catalog.product.download'.($query !== null && $query !== '' ? '?'.$query : '');
            }

            return $rel;
        }
        $normalized = '/'.ltrim($rel, '/');
        if (preg_match('#/catalog\.product\.download(\?.*)?$#', $normalized, $m)) {
            return '/catalog.product.download'.($m[1] ?? '');
        }
        if (str_starts_with($normalized, '/rest/')) {
            return '/'.substr($normalized, strlen('/rest/'));
        }

        return $normalized;
    }

    /**
     * Относительный путь Bitrix REST для скачивания/показа файла товара (без домена и вебхука).
     */
    public static function bitrixDownloadPath(int $productId, int $fileId, string $fieldName = 'property172'): string
    {
        return '/catalog.product.download?'.http_build_query([
            'fields' => [
                'fieldName' => $fieldName,
                'fileId' => $fileId,
                'productId' => $productId,
            ],
        ]);
    }

    /**
     * Полный URL для отображения (вебхук в пути).
     */
    public static function displayUrl(?string $storedPath, string $webhookBase): ?string
    {
        $storedPath = $storedPath !== null ? trim($storedPath) : '';
        if ($storedPath === '') {
            return null;
        }
        if (str_starts_with($storedPath, 'http://') || str_starts_with($storedPath, 'https://')) {
            return $storedPath;
        }
        $base = rtrim($webhookBase, '/');
        if ($base === '') {
            return $storedPath;
        }

        return $base.'/'.ltrim($storedPath, '/');
    }
}
