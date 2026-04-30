<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Throwable;
use Illuminate\Support\Str;

class CalculatorController extends Controller
{
    public function index()
    {
        $materials = collect();
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

            $materials = DB::connection('diller')
                ->table('bitrix24_catalog_products')
                ->select('bitrix_id', 'name', 'price_value', 'price_currency', 'path_parts', 'section_bitrix_id')
                ->where('active', true)
                ->whereNotNull('price_value')
                ->orderBy('name')
                ->limit(1000)
                ->get()
                ->map(function ($row) use ($sections) {
                    $rawName = trim((string) ($row->name ?? ''));
                    $displayName = $this->localizeName($rawName !== '' ? $rawName : 'Материал');
                    $sectionId = (int) ($row->section_bitrix_id ?? 0);

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
                        'price_currency' => (string) ($row->price_currency ?? 'KZT'),
                        'per_m2' => $this->detectConsumptionPerM2($displayName),
                        'path' => array_values($path),
                    ];
                })
                ->filter(fn (array $item) => $item['id'] > 0 && $item['price_value'] > 0)
                ->values();
        } catch (Throwable) {
            $materials = collect();
            $sectionPaths = collect();
        }

        if ($materials->isEmpty()) {
            $materials = collect([
                ['id' => 1, 'name' => 'Кирпич ручной формовки', 'price_value' => 400, 'price_currency' => 'KZT', 'per_m2' => 52, 'path' => ['Кирпичи', 'Коллекции', 'Antic']],
                ['id' => 2, 'name' => 'Плитка ручной формовки', 'price_value' => 320, 'price_currency' => 'KZT', 'per_m2' => 36, 'path' => ['Плитки', 'Коллекции', '1 коллекция Antic']],
                ['id' => 3, 'name' => 'Клинкерный кирпич', 'price_value' => 500, 'price_currency' => 'KZT', 'per_m2' => 48, 'path' => ['Кирпичи', 'Коллекции', 'Classic']],
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
            'calculatorMaterials' => $materials,
            'calculatorSections' => $sectionPaths,
        ]);
    }

    private function detectConsumptionPerM2(string $name): int
    {
        $needle = mb_strtolower($name);
        if (str_contains($needle, 'плитк')) {
            return 36;
        }
        if (str_contains($needle, 'клинкер')) {
            return 48;
        }

        return 52;
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

