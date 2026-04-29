<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AddressMapCoherenceChecker
{
    /**
     * @param  array<string, mixed>  $input  validated request subset with address + map_* keys
     */
    public function validate(array $input): ?string
    {
        [$lat, $lng] = $this->parseLatLng($input);

        $hasMap = $lat !== null && $lng !== null;
        $hasAddress = $this->hasSubstantiveAddress($input);

        if (! $hasMap && ! $hasAddress) {
            return 'Укажите адрес объекта (населённый пункт, улицу, дом или кадастровый номер) и поставьте точку на карте в том же месте.';
        }

        if (! $hasMap) {
            return 'Поставьте точку на карте — она должна соответствовать введённому адресу.';
        }

        if (! $hasAddress) {
            return 'Заполните адрес (населённый пункт, улицу, дом или кадастровый номер) — он должен совпадать с точкой на карте.';
        }

        $query = $this->buildGeocodeQuery($input);
        if ($query === '') {
            return 'Заполните адрес (населённый пункт, улицу, дом или кадастровый номер) — он должен совпадать с точкой на карте.';
        }

        $geocoded = $this->forwardGeocode($query);
        $maxM = (int) config('services.nominatim.max_distance_meters', 2500);

        if ($geocoded !== null) {
            $m = $this->haversineMeters($lat, $lng, $geocoded[0], $geocoded[1]);
            if ($m <= $maxM) {
                return null;
            }
        }

        $reverseLabel = $this->reverseGeocodeLabel($lat, $lng);
        if ($reverseLabel !== null && $this->formMatchesReverseLabel($input, $reverseLabel)) {
            return null;
        }

        if ($geocoded !== null) {
            $m = $this->haversineMeters($lat, $lng, $geocoded[0], $geocoded[1]);

            return 'Точка на карте не совпадает с введённым адресом (расхождение около '.round($m / 1000, 1).' км). Уточните поля адреса или выберите место через поиск на карте и поставьте метку заново.';
        }

        return 'Не удалось сопоставить адрес с картой. Проверьте написание адреса или найдите объект через поиск на карте и выберите точку из списка результатов.';
    }

    /**
     * @return array{0: ?float, 1: ?float}
     */
    private function parseLatLng(array $input): array
    {
        $lat = $input['map_lat'] ?? null;
        $lng = $input['map_lng'] ?? null;
        if ($lat === null || $lng === null || $lat === '' || $lng === '') {
            return [null, null];
        }

        return [(float) $lat, (float) $lng];
    }

    private function hasSubstantiveAddress(array $input): bool
    {
        foreach (['address_locality', 'address_street', 'address_house', 'address_cadastral'] as $key) {
            if (trim((string) ($input[$key] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    private function buildGeocodeQuery(array $input): string
    {
        $parts = array_filter([
            trim((string) ($input['address_country'] ?? '')),
            trim((string) ($input['address_locality'] ?? '')),
            trim((string) ($input['address_street'] ?? '')),
            trim((string) ($input['address_house'] ?? '')),
        ], fn (string $s) => $s !== '');

        $line = implode(', ', $parts);
        $cad = trim((string) ($input['address_cadastral'] ?? ''));
        if ($cad !== '') {
            $line = $line !== '' ? $line.', '.$cad : $cad;
        }

        return $line;
    }

    private function formMatchesReverseLabel(array $input, string $reverseLabel): bool
    {
        $form = mb_strtolower($this->buildGeocodeQuery($input));
        $rev = mb_strtolower($reverseLabel);
        if ($form === '' || $rev === '') {
            return false;
        }

        similar_text($form, $rev, $percent);

        return $percent >= 68.0;
    }

    /**
     * @return ?array{0: float, 1: float}
     */
    private function forwardGeocode(string $query): ?array
    {
        $ua = (string) config('services.nominatim.user_agent', 'RealBrick-Diller/1.0');

        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => $ua])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 1,
                ]);

            if (! $response->successful()) {
                return null;
            }

            $json = $response->json();
            if (! is_array($json) || $json === [] || ! isset($json[0]['lat'], $json[0]['lon'])) {
                return null;
            }

            return [(float) $json[0]['lat'], (float) $json[0]['lon']];
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    private function reverseGeocodeLabel(float $lat, float $lng): ?string
    {
        $ua = (string) config('services.nominatim.user_agent', 'RealBrick-Diller/1.0');

        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => $ua])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat' => $lat,
                    'lon' => $lng,
                    'format' => 'json',
                ]);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            if (! is_array($data)) {
                return null;
            }

            $name = trim((string) ($data['display_name'] ?? ''));

            return $name !== '' ? $name : null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    private function haversineMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371000.0;
        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $dphi = deg2rad($lat2 - $lat1);
        $dlambda = deg2rad($lon2 - $lon1);
        $a = sin($dphi / 2) ** 2 + cos($phi1) * cos($phi2) * sin($dlambda / 2) ** 2;

        return 2 * $earth * asin(min(1.0, sqrt($a)));
    }
}
