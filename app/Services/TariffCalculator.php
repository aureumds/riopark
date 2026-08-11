<?php

namespace App\Services;

use App\Models\TariffRule;
use Carbon\CarbonInterface;

class TariffCalculator
{
    public function calculate(TariffRule $tariff, CarbonInterface $entryAt, CarbonInterface $exitAt): float
    {
        $minutes = $entryAt->diffInMinutes($exitAt);

        if ($minutes <= $tariff->grace_minutes) {
            return 0.0;
        }

        $billableMinutes = $minutes - $tariff->grace_minutes;
        $fractionMinutes = max(1, $tariff->fraction_minutes);
        $fractions = (int) ceil($billableMinutes / $fractionMinutes);

        if ($tariff->fraction_price > 0) {
            return round($fractions * (float) $tariff->fraction_price, 2);
        }

        $hours = ceil($billableMinutes / 60);

        return round($hours * (float) $tariff->price_per_hour, 2);
    }
}
