<?php

namespace App\Livewire\Admin;

use App\Models\TariffRule;
use Livewire\Component;

class Tariff extends Component
{
    public float $price_per_hour = 5;

    public int $grace_minutes = 15;

    public int $fraction_minutes = 30;

    public float $fraction_price = 3;

    public function mount(): void
    {
        $tariff = auth()->user()->company?->activeTariff();

        if ($tariff) {
            $this->price_per_hour = (float) $tariff->price_per_hour;
            $this->grace_minutes = $tariff->grace_minutes;
            $this->fraction_minutes = $tariff->fraction_minutes;
            $this->fraction_price = (float) $tariff->fraction_price;
        }
    }

    public function save(): void
    {
        $this->validate([
            'price_per_hour' => ['required', 'numeric', 'min:0'],
            'grace_minutes' => ['required', 'integer', 'min:0'],
            'fraction_minutes' => ['required', 'integer', 'min:1'],
            'fraction_price' => ['required', 'numeric', 'min:0'],
        ]);

        $companyId = auth()->user()->company_id;

        TariffRule::where('company_id', $companyId)->update(['active' => false]);

        $lastVersion = TariffRule::where('company_id', $companyId)->max('version') ?? 0;

        TariffRule::create([
            'company_id' => $companyId,
            'price_per_hour' => $this->price_per_hour,
            'grace_minutes' => $this->grace_minutes,
            'fraction_minutes' => $this->fraction_minutes,
            'fraction_price' => $this->fraction_price,
            'version' => $lastVersion + 1,
            'active' => true,
        ]);

        session()->flash('success', 'Tarifa atualizada.');
    }

    public function render()
    {
        return view('livewire.admin.tariff')->layout('components.layouts.admin', ['title' => 'Tarifa', 'subtitle' => 'Configure as regras de cobrança']);
    }
}
