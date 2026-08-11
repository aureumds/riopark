<?php

namespace App\Services;

class PlateNormalizer
{
    public function normalize(string $plate): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $plate) ?? '');
    }

    public function format(string $plate): string
    {
        $normalized = $this->normalize($plate);

        if (strlen($normalized) === 7) {
            return substr($normalized, 0, 3).'-'.substr($normalized, 3);
        }

        return $normalized;
    }
}
