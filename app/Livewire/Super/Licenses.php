<?php

namespace App\Livewire\Super;

use App\Models\Company;
use App\Models\Device;
use App\Services\LicenseService;
use InvalidArgumentException;
use Livewire\Component;

class Licenses extends Component
{
    public function markPaid(int $companyId): void
    {
        $company = Company::findOrFail($companyId);
        app(LicenseService::class)->markPaid($company);
        session()->flash('success', 'Mensalidade marcada como paga (+30 dias) para '.$company->name.'.');
    }

    public function issue(int $deviceId): void
    {
        $device = Device::with('company')->findOrFail($deviceId);

        try {
            app(LicenseService::class)->issueForDevice($device, auth()->user());
            session()->flash('success', 'Token liberado para '.$device->label.'. A máquina deve conectar à internet para baixar.');
        } catch (InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function revoke(int $deviceId): void
    {
        $device = Device::findOrFail($deviceId);
        $device->licenses()->whereNull('revoked_at')->update(['revoked_at' => now()]);
        session()->flash('success', 'Licença revogada. Vale na próxima conexão da máquina.');
    }

    public function toggleDevice(int $deviceId): void
    {
        $device = Device::findOrFail($deviceId);
        $device->update(['active' => ! $device->active]);
    }

    public function render()
    {
        $companies = Company::with(['plan', 'devices.parkingLot', 'devices.licenses' => function ($q) {
            $q->latest('issued_at');
        }])->orderBy('name')->get();

        return view('livewire.super.licenses', [
            'companies' => $companies,
        ])->layout('components.layouts.super', ['title' => 'Licenças', 'subtitle' => 'Mensalidade e tokens das máquinas']);
    }
}
