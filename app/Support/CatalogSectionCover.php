<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Обложки разделов каталога: bitrix24_catalog_sections.image_url, иначе первый товар раздела.
 */
final class CatalogSectionCover
{
    /** @return list<string> */
    public static function sectionSelectColumns(): array
    {
        $columns = ['bitrix_id', 'name', 'path_parts'];
        $connection = (string) config('services.bitrix24.db_connection', 'diller');
        if (Schema::connection($connection)->hasColumn('bitrix24_catalog_sections', 'image_url')) {
            $columns[] = 'image_url';
        }

        return $columns;
    }

    public static function resolveDisplayUrl(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $rawTrim = trim($raw);
        $localUrl = Bitrix24CatalogImageUrls::publicAssetUrl($rawTrim);
        if ($localUrl !== null) {
            return $localUrl;
        }

        $webhook = rtrim((string) env('DILLER_BITRIX24_REST_URL', ''), '/');
        if ($webhook === '') {
            return preg_match('#^https?://#i', $rawTrim) ? $rawTrim : null;
        }

        $normalized = Bitrix24CatalogImageUrls::pathForStorage($rawTrim);
        if ($normalized === null || $normalized === '') {
            return null;
        }

        return Bitrix24CatalogImageUrls::displayUrl($normalized, $webhook);
    }

    public static function coverUrlForSection(int $sectionBitrixId, ?string $sectionImageUrl = null): ?string
    {
        if ($sectionBitrixId <= 0) {
            return null;
        }

        $sectionRaw = $sectionImageUrl !== null ? trim($sectionImageUrl) : '';
        if ($sectionRaw === '') {
            $connection = (string) config('services.bitrix24.db_connection', 'diller');
            if (Schema::connection($connection)->hasColumn('bitrix24_catalog_sections', 'image_url')) {
                $fromDb = DB::connection($connection)
                    ->table('bitrix24_catalog_sections')
                    ->where('bitrix_id', $sectionBitrixId)
                    ->value('image_url');
                $sectionRaw = $fromDb !== null ? trim((string) $fromDb) : '';
            }
        }

        if ($sectionRaw !== '') {
            $resolved = self::resolveDisplayUrl($sectionRaw);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        $connection = (string) config('services.bitrix24.db_connection', 'diller');
        $productRaw = DB::connection($connection)
            ->table('bitrix24_catalog_products')
            ->where('section_bitrix_id', $sectionBitrixId)
            ->where('active', true)
            ->whereNotNull('image_url')
            ->where('image_url', '!=', '')
            ->orderBy('name')
            ->value('image_url');

        return self::resolveDisplayUrl($productRaw !== null && $productRaw !== '' ? (string) $productRaw : null);
    }
}
