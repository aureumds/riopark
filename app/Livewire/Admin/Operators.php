<?php

namespace App\Livewire\Admin;

use App\Models\ParkingLot;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Operators extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public ?int $parking_lot_id = null;

    public ?int $editingId = null;

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$this->editingId],
            'parking_lot_id' => ['required', 'exists:parking_lots,id'],
        ];

        if (! $this->editingId) {
            $rules['password'] = ['required', 'string', 'min:6'];
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'company_id' => auth()->user()->company_id,
            'parking_lot_id' => $this->parking_lot_id,
            'active' => true,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        $user = User::updateOrCreate(['id' => $this->editingId], $data);
        $user->syncRoles(['operator']);

        $this->resetForm();
        session()->flash('success', 'Operador salvo.');
    }

    public function edit(int $id): void
    {
        $user = User::role('operator')->findOrFail($id);
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->parking_lot_id = $user->parking_lot_id;
        $this->password = '';
    }

    public function toggleActive(int $id): void
    {
        $user = User::role('operator')->findOrFail($id);
        $user->update(['active' => ! $user->active]);
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->parking_lot_id = null;
    }

    public function render()
    {
        return view('livewire.admin.operators', [
            'operators' => User::role('operator')->with('parkingLot')->orderBy('name')->get(),
            'lots' => ParkingLot::orderBy('name')->get(),
        ])->layout('components.layouts.admin', ['title' => 'Operadores', 'subtitle' => 'Gerencie os operadores do estacionamento']);
    }
}
