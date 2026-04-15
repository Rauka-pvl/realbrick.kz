<?php

namespace App\Support;

/**
 * Картинки каталога Bitrix24: в БД — путь без домена и без сегмента вебхука;
 * для img — полный URL с токеном из DILLER_BITRIX24_REST_URL (как BITRIX24_CATALOG_URL в Diller).
 */
final class Bitrix24CatalogImageUrls
{
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
