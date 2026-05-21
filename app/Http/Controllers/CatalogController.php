<?php

namespace App\Http\Controllers;

use App\Support\CatalogSectionCover;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $lang = $this->detectLang($request);
        $rootSectionId = (int) env('DILLER_ROOT_SECTION_ID', 22);

        $sections = DB::connection('diller')
            ->table('bitrix24_catalog_sections')
            ->select(CatalogSectionCover::sectionSelectColumns())
            ->where('parent_bitrix_id', $rootSectionId)
            ->where('excluded', false)
            ->orderBy('name')
            ->get()
            ->map(function ($row) use ($lang) {
                $parts = $this->decodePathParts($row->path_parts, $row->name);

                return [
                    'id' => (int) $row->bitrix_id,
                    'name' => $this->localizeName((string) $row->name, $lang),
                    'slug' => $this->slugFromPathParts($parts),
                    'cover_url' => CatalogSectionCover::coverUrlForSection(
                        (int) $row->bitrix_id,
                        isset($row->image_url) ? (string) $row->image_url : null
                    ),
                ];
            })
            ->values();

        return view('real-brick.catalog.index', [
            'sections' => $sections,
            'lang' => $lang,
        ]);
    }

    public function collection(Request $request, string $pathSlug)
    {
        $lang = $this->detectLang($request);

        $currentSection = DB::connection('diller')
            ->table('bitrix24_catalog_sections')
            ->select('bitrix_id', 'name', 'path_parts')
            ->where('excluded', false)
            ->orderBy('name')
            ->get()
            ->first(function ($row) use ($pathSlug) {
                $parts = $this->decodePathParts($row->path_parts, $row->name);

                return $this->slugFromPathParts($parts) === $pathSlug;
            });

        abort_if(! $currentSection, 404);

        $sectionId = (int) $currentSection->bitrix_id;
        $sectionRawPath = $this->decodePathParts($currentSection->path_parts, $currentSection->name);
        $sectionPath = $this->localizePath($sectionRawPath, $lang);
        $collectionBreadcrumbs = $this->buildCollectionBreadcrumbs($sectionRawPath, $lang);

        $leftSections = DB::connection('diller')
            ->table('bitrix24_catalog_sections')
            ->select(CatalogSectionCover::sectionSelectColumns())
            ->where('parent_bitrix_id', $sectionId)
            ->where('excluded', false)
            ->orderBy('name')
            ->get()
            ->map(function ($row) use ($lang) {
                $parts = $this->decodePathParts($row->path_parts, $row->name);

                return [
                    'id' => (int) $row->bitrix_id,
                    'name' => $this->localizeName((string) $row->name, $lang),
                    'slug' => $this->slugFromPathParts($parts),
                    'cover_url' => CatalogSectionCover::coverUrlForSection(
                        (int) $row->bitrix_id,
                        isset($row->image_url) ? (string) $row->image_url : null
                    ),
                ];
            })
            ->values();

        $products = DB::connection('diller')
            ->table('bitrix24_catalog_products')
            ->select('bitrix_id', 'name', 'image_url', 'path_parts', 'price_value', 'price_currency')
            ->where('section_bitrix_id', $sectionId)
            ->where('active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($row) use ($lang) {
                $parts = $this->decodePathParts($row->path_parts, $row->name);

                return [
                    'id' => (int) $row->bitrix_id,
                    'name' => $this->localizeName((string) $row->name, $lang),
                    'slug' => $this->slugFromPathParts($parts),
                    'image_url' => CatalogSectionCover::resolveDisplayUrl(
                        isset($row->image_url) && $row->image_url !== '' ? (string) $row->image_url : null
                    ),
                    'price_value' => isset($row->price_value) ? (float) $row->price_value : null,
                    'price_currency' => isset($row->price_currency) ? (string) $row->price_currency : null,
                ];
            })
            ->values();

        return view('real-brick.catalog.collection', [
            'sectionName' => $this->localizeName((string) $currentSection->name, $lang),
            'sectionPath' => $sectionPath,
            'collectionBreadcrumbs' => $collectionBreadcrumbs,
            'leftSections' => $leftSections,
            'childSections' => $leftSections,
            'products' => $products,
            'lang' => $lang,
        ]);
    }

    public function product(Request $request, string $pathSlug)
    {
        $lang = $this->detectLang($request);

        $product = DB::connection('diller')
            ->table('bitrix24_catalog_products')
            ->select('bitrix_id', 'name', 'image_url', 'path_parts', 'section_bitrix_id', 'price_value', 'price_currency', 'size')
            ->where('active', true)
            ->orderBy('name')
            ->get()
            ->first(function ($row) use ($pathSlug) {
                $parts = $this->decodePathParts($row->path_parts, $row->name);

                return $this->slugFromPathParts($parts) === $pathSlug;
            });

        abort_if(! $product, 404);

        $sectionId = (int) ($product->section_bitrix_id ?? 0);
        $section = null;
        if ($sectionId > 0) {
            $section = DB::connection('diller')
                ->table('bitrix24_catalog_sections')
                ->select('name', 'path_parts')
                ->where('bitrix_id', $sectionId)
                ->first();
        }
        $productRawPath = $this->buildProductPathParts($product, $section);
        $pathParts = $this->localizePath($productRawPath, $lang);
        $productBreadcrumbs = $this->buildProductBreadcrumbs($productRawPath, $lang);

        $relatedProducts = collect();
        if ($sectionId > 0) {
            $relatedProducts = DB::connection('diller')
                ->table('bitrix24_catalog_products')
                ->select('bitrix_id', 'name', 'image_url', 'path_parts', 'price_value', 'price_currency')
                ->where('section_bitrix_id', $sectionId)
                ->where('active', true)
                ->orderBy('name')
                ->get()
                ->map(function ($row) use ($lang) {
                    $parts = $this->decodePathParts($row->path_parts, $row->name);

                    return [
                        'id' => (int) $row->bitrix_id,
                        'name' => $this->localizeName((string) $row->name, $lang),
                        'slug' => $this->slugFromPathParts($parts),
                        'image_url' => CatalogSectionCover::resolveDisplayUrl(
                            isset($row->image_url) && $row->image_url !== '' ? (string) $row->image_url : null
                        ),
                        'price_value' => isset($row->price_value) ? (float) $row->price_value : null,
                        'price_currency' => isset($row->price_currency) ? (string) $row->price_currency : null,
                    ];
                })
                ->values();
        }

        $productImage = CatalogSectionCover::resolveDisplayUrl(
            isset($product->image_url) && $product->image_url !== '' ? (string) $product->image_url : null
        );

        return view('real-brick.catalog.product', [
            'productName' => $this->localizeName((string) $product->name, $lang),
            'productBitrixId' => (int) $product->bitrix_id,
            'productImage' => $productImage,
            'productPriceValue' => isset($product->price_value) ? (float) $product->price_value : null,
            'productPriceCurrency' => isset($product->price_currency) ? (string) $product->price_currency : null,
            'productSize' => isset($product->size) ? trim((string) $product->size) : null,
            'pathParts' => $pathParts,
            'productBreadcrumbs' => $productBreadcrumbs,
            'relatedProducts' => $relatedProducts,
            'lang' => $lang,
        ]);
    }

    private function detectLang(Request $request): string
    {
        $lang = strtolower((string) $request->query('lang', 'ru'));

        return in_array($lang, ['ru', 'kz'], true) ? $lang : 'ru';
    }

    private function decodePathParts(?string $rawPathParts, string $fallbackName): array
    {
        $decoded = json_decode((string) $rawPathParts, true);
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

    private function splitLocalizedName(string $source, string $lang): string
    {
        $source = trim($source);
        if ($source === '') {
            return '';
        }

        $parts = array_map('trim', explode('/', $source, 2));
        if (count($parts) === 1) {
            return $parts[0];
        }

        $kz = $parts[0] !== '' ? $parts[0] : ($parts[1] !== '' ? $parts[1] : '');
        $ru = $parts[1] !== '' ? $parts[1] : ($parts[0] !== '' ? $parts[0] : '');

        return $lang === 'kz' ? $kz : $ru;
    }

    private function localizeName(string $rawName, string $lang): string
    {
        $fromRaw = $this->splitLocalizedName($rawName, $lang);

        return $fromRaw !== '' ? $fromRaw : $rawName;
    }

    private function localizePath(array $parts, string $lang): array
    {
        $out = [];
        foreach ($parts as $part) {
            $out[] = $this->splitLocalizedName((string) $part, $lang);
        }

        return $out;
    }

    private function buildProductPathParts(object $product, ?object $section): array
    {
        $productPath = $this->decodePathParts($product->path_parts ?? null, (string) ($product->name ?? ''));
        $sectionPath = $section ? $this->decodePathParts($section->path_parts ?? null, (string) ($section->name ?? '')) : ['Каталог'];
        $productName = (string) ($product->name ?? '');

        // Если в товаре путь уже полноценный (>=3 частей), используем его как есть.
        if (count($productPath) >= 3) {
            return $productPath;
        }

        // Иначе собираем путь из раздела + название товара.
        $base = $sectionPath;
        if ($productName !== '') {
            $last = end($base);
            if ((string) $last !== $productName) {
                $base[] = $productName;
            }
        }

        return $base;
    }

    private function buildCollectionBreadcrumbs(array $rawPathParts, string $lang): array
    {
        $crumbs = [];
        $count = count($rawPathParts);
        for ($i = 1; $i < $count; $i++) {
            $rawName = (string) $rawPathParts[$i];
            $name = $this->localizeName($rawName, $lang);
            $url = null;
            if ($i < $count - 1) {
                $slug = $this->slugFromPathParts(array_slice($rawPathParts, 0, $i + 1));
                $url = route('catalog.collection', ['slug' => $slug, 'lang' => $lang]);
            }
            $crumbs[] = ['name' => $name, 'url' => $url];
        }

        return $crumbs;
    }

    private function buildProductBreadcrumbs(array $rawPathParts, string $lang): array
    {
        $crumbs = [];
        $count = count($rawPathParts);
        for ($i = 1; $i < $count; $i++) {
            $rawName = (string) $rawPathParts[$i];
            $name = $this->localizeName($rawName, $lang);
            $url = null;
            // На товаре делаем кликабельными категория/подкатегория, последний элемент (сам товар) без ссылки.
            if ($i < $count - 1) {
                $slug = $this->slugFromPathParts(array_slice($rawPathParts, 0, $i + 1));
                $url = route('catalog.collection', ['slug' => $slug, 'lang' => $lang]);
            }
            $crumbs[] = ['name' => $name, 'url' => $url];
        }

        return $crumbs;
    }
}
