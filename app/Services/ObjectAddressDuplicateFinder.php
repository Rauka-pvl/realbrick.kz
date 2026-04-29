<?php

namespace App\Services;

use App\Models\ProjectObject;

class ObjectAddressDuplicateFinder
{
    public const SIMILARITY_THRESHOLD_PERCENT = 86.0;

    /**
     * @param  array<string, mixed>  $addressFields
     */
    public function findConflict(
        int $currentDealerId,
        array $addressFields,
        ?int $excludeObjectId = null
    ): ?ProjectObject {
        $cadastral = $this->normalize($addressFields['address_cadastral'] ?? null);
        $locality = $this->normalize($addressFields['address_locality'] ?? null);
        $street = $this->normalize($addressFields['address_street'] ?? null);
        $house = $this->normalize($addressFields['address_house'] ?? null);

        if ($cadastral === '' && $locality === '' && $street === '' && $house === '') {
            return null;
        }

        $query = ProjectObject::query()
            ->where('dealer_id', '!=', $currentDealerId)
            ->whereNull('moderation_status')
            ->with(['dealer']);

        if ($excludeObjectId) {
            $query->where('id', '!=', $excludeObjectId);
        }

        $best = null;
        $bestScore = 0.0;

        foreach ($query->cursor() as $obj) {
            if ($this->isStrongMatch($cadastral, $locality, $street, $house, $obj)) {
                return $obj;
            }
            $score = $this->fuzzySimilarityPercent($locality, $street, $house, $obj);
            if ($score >= self::SIMILARITY_THRESHOLD_PERCENT && $score > $bestScore) {
                $bestScore = $score;
                $best = $obj;
            }
        }

        return $best;
    }

    public function normalize(?string $value): string
    {
        $value = trim((string) ($value ?? ''));
        $value = mb_strtolower($value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';

        return $value;
    }

    private function isStrongMatch(
        string $cadastral,
        string $locality,
        string $street,
        string $house,
        ProjectObject $obj
    ): bool {
        $oc = $this->normalize($obj->address_cadastral);
        if ($cadastral !== '' && $oc !== '' && $cadastral === $oc) {
            return true;
        }

        $ol = $this->normalize($obj->address_locality);
        $os = $this->normalize($obj->address_street);
        $oh = $this->normalize($obj->address_house);

        if ($locality !== '' && $street !== '' && $house !== ''
            && $locality === $ol && $street === $os && $house === $oh) {
            return true;
        }

        return false;
    }

    private function fuzzySimilarityPercent(
        string $locality,
        string $street,
        string $house,
        ProjectObject $obj
    ): float {
        $a = trim(implode(' ', array_filter([$locality, $street, $house])));
        $b = trim(implode(' ', array_filter([
            $this->normalize($obj->address_locality),
            $this->normalize($obj->address_street),
            $this->normalize($obj->address_house),
        ])));

        if ($a === '' || $b === '') {
            return 0.0;
        }

        similar_text($a, $b, $percent);

        return $percent;
    }
}
