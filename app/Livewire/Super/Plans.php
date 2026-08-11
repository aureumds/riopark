<?php

namespace App\Livewire\Super;

use App\Models\Plan;
use Illuminate\Support\Str;
use Livewire\Component;

class Plans extends Component
{
    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public float $activation_fee = 0;

    public float $monthly_per_machine = 0;

    public ?int $editingId = null;

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:plans,slug,'.$this->editingId],
            'description' => ['nullable', 'string', 'max:1000'],
            'activation_fee' => ['required', 'numeric', 'min:0'],
            'monthly_per_machine' => ['required', 'numeric', 'min:0'],
        ]);

        if ($this->editingId) {
            Plan::findOrFail($this->editingId)->update([
                'name' => $this->name,
                'slug' => Str::slug($this->slug ?: $this->name),
                'description' => $this->description ?: null,
                'activation_fee' => $this->activation_fee,
                'monthly_per_machine' => $this->monthly_per_machine,
            ]);
        } else {
            Plan::create([
                'name' => $this->name,
                'slug' => Str::slug($this->slug ?: $this->name),
                'description' => $this->description ?: null,
                'activation_fee' => $this->activation_fee,
                'monthly_per_machine' => $this->monthly_per_machine,
                'active' => true,
            ]);
        }

        $this->resetForm();
        session()->flash('success', 'Plano salvo com sucesso.');
    }

    public function edit(int $id): void
    {
        $plan = Plan::findOrFail($id);
        $this->editingId = $plan->id;
        $this->name = $plan->name;
        $this->slug = $plan->slug;
        $this->description = $plan->description ?? '';
        $this->activation_fee = (float) $plan->activation_fee;
        $this->monthly_per_machine = (float) $plan->monthly_per_machine;
    }

    public function toggleActive(int $id): void
    {
        $plan = Plan::findOrFail($id);
        $plan->update(['active' => ! $plan->active]);
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->activation_fee = 0;
        $this->monthly_per_machine = 0;
    }

    public function render()
    {
        return view('livewire.super.plans', [
            'plans' => Plan::withCount('companies')->orderBy('name')->get(),
        ])->layout('layouts.super', ['title' => 'Planos', 'subtitle' => 'Configure os planos de cobrança']);
    }
}
