<?php

namespace App\Http\Controllers;

use App\Support\Bitrix24CatalogImageUrls;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
        $rootSectionId = (int) env('DILLER_ROOT_SECTION_ID', 22);

        $collections = DB::connection('diller')
            ->table('bitrix24_catalog_sections')
            ->select('bitrix_id', 'name', 'path_parts')
            ->where('parent_bitrix_id', $rootSectionId)
            ->where('excluded', false)
            ->orderBy('name')
            ->get()
            ->map(function ($row) {
                $parts = $this->decodePathParts((string) ($row->path_parts ?? ''), (string) ($row->name ?? ''));

                return [
                    'id' => (int) ($row->bitrix_id ?? 0),
                    'name' => $this->localizeName((string) ($row->name ?? '')),
                    'slug' => $this->slugFromPathParts($parts),
                    'cover_url' => $this->getSectionCoverUrl((int) ($row->bitrix_id ?? 0)),
                ];
            })
            ->values();

        return view('real-brick.index', [
            'collections' => $collections,
        ]);
    }

    private function decodePathParts(string $rawPathParts, string $fallbackName): array
    {
        $decoded = json_decode($rawPathParts, true);
        if (! is_array($decoded) || $decoded === []) {
            return ['Каталог', $fallbackName];
        }

        $out = [];
        foreach ($decoded as $part) {
            $part = trim((string) $part);
            if ($part !== '') {
                $out[] = $part;
            }
        }

        return $out === [] ? ['Каталог', $fallbackName] : $out;
    }

    private function localizeName(string $source): string
    {
        $source = trim($source);
        if ($source === '') {
            return '';
        }

        $parts = array_map('trim', explode('/', $source, 2));
        if (count($parts) === 1) {
            return $parts[0];
        }

        return $parts[1] !== '' ? $parts[1] : $parts[0];
    }

    private function slugFromPathParts(array $parts): string
    {
        $normalized = [];
        foreach ($parts as $part) {
            $part = trim((string) $part);
            if ($part !== '' && mb_strtolower($part) !== 'каталог') {
                $normalized[] = $part;
            }
        }

        return Str::slug(implode(' ', $normalized));
    }

    private function getSectionCoverUrl(int $sectionId): ?string
    {
        $raw = DB::connection('diller')
            ->table('bitrix24_catalog_sections')
            ->where('bitrix_id', $sectionId)
            ->value('image_url');

        return $this->resolveProductImageDisplayUrl($raw !== null && $raw !== '' ? (string) $raw : null);
    }

    private function resolveProductImageDisplayUrl(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }
        $rawTrim = trim($raw);
        if (preg_match('#^storage/#', $rawTrim)) {
            return asset($rawTrim);
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
}

