<?php

namespace App\Livewire\Admin;

use App\Models\ParkingLot;
use Livewire\Component;

class ParkingLots extends Component
{
    public string $name = '';

    public string $address = '';

    public int $capacity = 0;

    public ?int $editingId = null;

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'capacity' => ['required', 'integer', 'min:0'],
        ]);

        ParkingLot::updateOrCreate(
            ['id' => $this->editingId],
            [
                'company_id' => auth()->user()->company_id,
                'name' => $this->name,
                'address' => $this->address,
                'capacity' => $this->capacity,
                'active' => true,
            ]
        );

        $this->resetForm();
        session()->flash('success', 'Estacionamento salvo.');
    }

    public function edit(int $id): void
    {
        $lot = ParkingLot::findOrFail($id);
        $this->editingId = $lot->id;
        $this->name = $lot->name;
        $this->address = $lot->address ?? '';
        $this->capacity = $lot->capacity;
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->address = '';
        $this->capacity = 0;
    }

    public function render()
    {
        return view('livewire.admin.parking-lots', [
            'lots' => ParkingLot::orderBy('name')->get(),
        ])->layout('components.layouts.admin', ['title' => 'Estacionamentos', 'subtitle' => 'Gerencie os locais de estacionamento']);
    }
}
