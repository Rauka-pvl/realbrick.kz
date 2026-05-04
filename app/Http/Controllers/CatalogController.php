<?php

namespace App\Http\Controllers;

use App\Support\Bitrix24CatalogImageUrls;
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
            ->select('bitrix_id', 'name', 'path_parts')
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
                    'cover_url' => $this->getSectionCoverUrl((int) $row->bitrix_id),
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
            ->select('bitrix_id', 'name', 'path_parts')
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
                    'cover_url' => $this->getSectionCoverUrl((int) $row->bitrix_id),
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
                    'image_url' => $this->resolveProductImageDisplayUrl(
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
            ->select('bitrix_id', 'name', 'image_url', 'gallery_json', 'path_parts', 'section_bitrix_id', 'price_value', 'price_currency', 'property_50')
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
                        'image_url' => $this->resolveProductImageDisplayUrl(
                            isset($row->image_url) && $row->image_url !== '' ? (string) $row->image_url : null
                        ),
                        'price_value' => isset($row->price_value) ? (float) $row->price_value : null,
                        'price_currency' => isset($row->price_currency) ? (string) $row->price_currency : null,
                    ];
                })
                ->values();
        }

        $galleryPaths = [];
        if (isset($product->gallery_json) && $product->gallery_json !== null && $product->gallery_json !== '') {
            $decoded = json_decode((string) $product->gallery_json, true);
            if (is_array($decoded)) {
                foreach ($decoded as $path) {
                    if (is_string($path) && $path !== '') {
                        $galleryPaths[] = $path;
                    }
                }
            }
        }
        $galleryUrls = collect($galleryPaths)
            ->map(fn (string $p) => $this->resolveProductImageDisplayUrl($p))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $mainImage = $galleryUrls[0] ?? $this->resolveProductImageDisplayUrl(
            isset($product->image_url) && $product->image_url !== '' ? (string) $product->image_url : null
        );
        $productImages = $galleryUrls !== [] ? $galleryUrls : ($mainImage ? [$mainImage] : []);

        return view('real-brick.catalog.product', [
            'productName' => $this->localizeName((string) $product->name, $lang),
            'productBitrixId' => (int) $product->bitrix_id,
            'productImage' => $mainImage,
            'productImages' => $productImages,
            'productPriceValue' => isset($product->price_value) ? (float) $product->price_value : null,
            'productPriceCurrency' => isset($product->price_currency) ? (string) $product->price_currency : null,
            'productSize' => isset($product->property_50) ? trim((string) $product->property_50) : null,
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

    /**
     * Как в Diller Bitrix24CatalogService::resolveProductImageDisplayUrl.
     */
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

    private function getSectionCoverUrl(int $sectionId): ?string
    {
        $raw = DB::connection('diller')
            ->table('bitrix24_catalog_sections')
            ->where('bitrix_id', $sectionId)
            ->value('image_url');

        if ($raw === null || trim((string) $raw) === '') {
            $raw = DB::connection('diller')
                ->table('bitrix24_catalog_products')
                ->where('section_bitrix_id', $sectionId)
                ->where('active', true)
                ->orderBy('name')
                ->value('image_url');
        }

        return $this->resolveProductImageDisplayUrl($raw !== null && $raw !== '' ? (string) $raw : null);
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
