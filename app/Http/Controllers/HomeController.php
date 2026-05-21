<?php

namespace App\Http\Controllers;

use App\Support\CatalogSectionCover;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
        $rootSectionId = (int) env('DILLER_ROOT_SECTION_ID', 22);

        $collections = DB::connection('diller')
            ->table('bitrix24_catalog_sections')
            ->select(CatalogSectionCover::sectionSelectColumns())
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
                    'cover_url' => CatalogSectionCover::coverUrlForSection(
                        (int) ($row->bitrix_id ?? 0),
                        isset($row->image_url) ? (string) $row->image_url : null
                    ),
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

}

