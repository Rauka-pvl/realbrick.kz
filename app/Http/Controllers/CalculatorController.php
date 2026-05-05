<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Throwable;
use Illuminate\Support\Str;

class CalculatorController extends Controller
{
    public function index()
    {
        $wallMaterials = collect();
        $verticalCornerMaterials = collect();
        $horizontalCornerMaterials = collect();
        $sectionPaths = collect();
        $rootSectionId = (int) env('DILLER_ROOT_SECTION_ID', 22);

        try {
            $sections = DB::connection('diller')
                ->table('bitrix24_catalog_sections')
                ->select('bitrix_id', 'parent_bitrix_id', 'name', 'path_parts')
                ->where('excluded', false)
                ->get()
                ->map(function ($row) use ($rootSectionId) {
                    $parts = $this->decodePathParts((string) ($row->path_parts ?? ''), (string) ($row->name ?? ''));
                    $localizedParts = array_map(fn (string $part) => $this->localizeName($part), $parts);
                    $normalized = $this->normalizeSectionPath($localizedParts);
                
                    return [
                        'id' => (int) ($row->bitrix_id ?? 0),
                        'parent_id' => (int) ($row->parent_bitrix_id ?? 0),
                        'name' => $this->localizeName((string) ($row->name ?? '')),
                        'path' => $normalized,
                        'is_root' => (int) ($row->bitrix_id ?? 0) === $rootSectionId,
                    ];
                })
                ->keyBy('id');

            $sectionPaths = $sections
                ->map(fn (array $section) => array_values((array) ($section['path'] ?? [])))
                ->filter(fn (array $path) => $path !== [])
                ->values();

            $baseQuery = DB::connection('diller')
                ->table('bitrix24_catalog_products')
                ->select('bitrix_id', 'name', 'price_value', 'price_currency', 'path_parts', 'section_bitrix_id', 'units_per_sq_or_lm', 'pieces_per_pack', 'size', 'article')
                ->where('active', true)
                ->whereNotNull('price_value');

            $wallRows = (clone $baseQuery)
                ->whereNotNull('units_per_sq_or_lm')
                ->where('units_per_sq_or_lm', '!=', '')
                ->where('units_per_sq_or_lm', '!=', 'N')
                ->where(function ($q) {
                    $q->whereNull('article')
                        ->orWhere('article', '')
                        ->orWhere(function ($qq) {
                            $qq->where('article', 'not like', '98.11%')
                                ->where('article', 'not like', '98.12%');
                        });
                })
                ->orderBy('name')
                ->limit(1000)
                ->get()
                ->map(fn ($row) => $this->mapCalculatorProductRow($row, $sections))
                ->filter(fn (array $item) => $item['id'] > 0 && $item['price_value'] > 0)
                ->values();

            $verticalRows = (clone $baseQuery)
                ->where('article', 'like', '98.11%')
                ->orderBy('name')
                ->limit(1000)
                ->get()
                ->map(fn ($row) => $this->mapCalculatorProductRow($row, $sections))
                ->filter(fn (array $item) => $item['id'] > 0 && $item['price_value'] > 0)
                ->values();

            $horizontalRows = (clone $baseQuery)
                ->where('article', 'like', '98.12%')
                ->orderBy('name')
                ->limit(1000)
                ->get()
                ->map(fn ($row) => $this->mapCalculatorProductRow($row, $sections))
                ->filter(fn (array $item) => $item['id'] > 0 && $item['price_value'] > 0)
                ->values();

            $wallMaterials = $wallRows;
            $verticalCornerMaterials = $verticalRows;
            $horizontalCornerMaterials = $horizontalRows;
        } catch (Throwable) {
            $wallMaterials = collect();
            $verticalCornerMaterials = collect();
            $horizontalCornerMaterials = collect();
            $sectionPaths = collect();
        }

        if ($wallMaterials->isEmpty() && $verticalCornerMaterials->isEmpty() && $horizontalCornerMaterials->isEmpty()) {
            $wallMaterials = collect([
                ['id' => 1, 'name' => 'Кирпич ручной формовки', 'price_value' => 400, 'price_currency' => 'USD', 'per_m2' => 52, 'pack_size' => 1, 'article' => '11.01', 'corner_dims' => null, 'path' => ['Кирпичи', 'Коллекции', 'Antic']],
                ['id' => 2, 'name' => 'Плитка ручной формовки', 'price_value' => 320, 'price_currency' => 'USD', 'per_m2' => 36, 'pack_size' => 1, 'article' => '11.02', 'corner_dims' => null, 'path' => ['Плитки', 'Коллекции', '1 коллекция Antic']],
            ]);
            $verticalCornerMaterials = collect([
                ['id' => 101, 'name' => 'Угол вертикальный', 'price_value' => 18, 'price_currency' => 'USD', 'per_m2' => null, 'pack_size' => null, 'article' => '98.11.001', 'corner_dims' => ['height_m' => 0.44, 'width_m' => 0.05], 'path' => ['Углы', 'Вертикальные']],
            ]);
            $horizontalCornerMaterials = collect([
                ['id' => 201, 'name' => 'Угол горизонтальный', 'price_value' => 15, 'price_currency' => 'USD', 'per_m2' => null, 'pack_size' => null, 'article' => '98.12.001', 'corner_dims' => ['height_m' => 0.44, 'width_m' => 0.05], 'path' => ['Углы', 'Горизонтальные']],
            ]);
        }

        if ($sectionPaths->isEmpty()) {
            $sectionPaths = collect([
                ['Кирпичи'],
                ['Напольная программа'],
                ['Отливы и Колпаки-парапеты'],
                ['Подсистема ППС'],
                ['Плитки'],
                ['Фуга и Клей'],
                ['Черепица'],
            ]);
        }

        return view('real-brick.pages', [
            'page' => 'calculator',
            'calculatorMaterials' => $wallMaterials,
            'calculatorVerticalCornerMaterials' => $verticalCornerMaterials,
            'calculatorHorizontalCornerMaterials' => $horizontalCornerMaterials,
            'calculatorSections' => $sectionPaths,
        ]);
    }

    private function mapCalculatorProductRow(object $row, \Illuminate\Support\Collection $sections): array
    {
        $rawName = trim((string) ($row->name ?? ''));
        $displayName = $this->localizeName($rawName !== '' ? $rawName : 'Материал');
        $sectionId = (int) ($row->section_bitrix_id ?? 0);
        $perM2 = $this->extractConsumptionPerM2((string) ($row->units_per_sq_or_lm ?? ''));
        $packSize = $this->extractPackSize((string) ($row->pieces_per_pack ?? ''));
        $article = trim((string) ($row->article ?? ''));
        $cornerDimensions = $this->extractCornerDimensions((string) ($row->size ?? ''));

        $path = [];
        if ($sectionId > 0 && isset($sections[$sectionId])) {
            $path = (array) ($sections[$sectionId]['path'] ?? []);
        }

        if ($path === []) {
            $productParts = $this->decodePathParts((string) ($row->path_parts ?? ''), $rawName !== '' ? $rawName : 'Материал');
            $localizedProductParts = array_map(fn (string $part) => $this->localizeName($part), $productParts);
            $path = $this->normalizeSectionPath($localizedProductParts);
            if ($path !== []) {
                array_pop($path);
            }
        }

        if ($path === []) {
            $path = ['Материалы'];
        }

        return [
            'id' => (int) ($row->bitrix_id ?? 0),
            'name' => $displayName,
            'price_value' => (float) ($row->price_value ?? 0),
            'price_currency' => (string) ($row->price_currency ?? 'USD'),
            'per_m2' => $perM2,
            'pack_size' => $packSize,
            'article' => $article,
            'corner_dims' => $cornerDimensions,
            'path' => array_values($path),
        ];
    }

    private function extractConsumptionPerM2(string $rawValue): ?int
    {
        $rawValue = trim($rawValue);
        if ($rawValue === '') {
            return null;
        }

        // Bitrix property can be "1", "1,5", "1.5" or composite string.
        if (preg_match('/\d+(?:[.,]\d+)?/', $rawValue, $matches) !== 1) {
            return null;
        }

        $number = (float) str_replace(',', '.', $matches[0]);
        if ($number <= 0) {
            return null;
        }

        return (int) round($number);
    }

    private function extractPackSize(string $rawValue): ?int
    {
        $rawValue = trim($rawValue);
        if ($rawValue === '') {
            return null;
        }

        if (preg_match('/\d+(?:[.,]\d+)?/', $rawValue, $matches) !== 1) {
            return null;
        }

        $number = (float) str_replace(',', '.', $matches[0]);
        if ($number <= 0) {
            return null;
        }

        return (int) round($number);
    }

    private function extractCornerDimensions(string $rawValue): ?array
    {
        $rawValue = trim($rawValue);
        if ($rawValue === '') {
            return null;
        }

        $matchesCount = preg_match_all('/\d+(?:[.,]\d+)?/', $rawValue, $matches);
        if ($matchesCount === false || $matchesCount < 2) {
            return null;
        }

        $numbers = array_map(static fn (string $v) => (float) str_replace(',', '.', $v), $matches[0] ?? []);
        $heightMm = $numbers[0] ?? 0.0;
        $widthMm = $numbers[1] ?? 0.0;
        if ($heightMm <= 0 || $widthMm <= 0) {
            return null;
        }

        return [
            'height_m' => $heightMm / 1000,
            'width_m' => $widthMm / 1000,
        ];
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

    private function normalizeSectionPath(array $parts): array
    {
        $normalized = [];
        foreach ($parts as $part) {
            $clean = trim((string) $part);
            if ($clean === '') {
                continue;
            }
            if (Str::lower($clean) === 'каталог') {
                continue;
            }
            $normalized[] = $clean;
        }

        return array_values($normalized);
    }
}

