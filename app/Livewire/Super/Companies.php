<?php

namespace App\Livewire\Super;

use App\Models\Company;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;

class Companies extends Component
{
    public string $name = '';

    public string $slug = '';

    public string $phone = '';

    public string $payer_name = '';

    public ?int $plan_id = null;

    public string $primary_color = '#1e40af';

    public string $accent_color = '#f59e0b';

    public string $admin_email = '';

    public string $admin_password = '';

    public ?int $editingId = null;

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:companies,slug,'.$this->editingId],
            'phone' => ['nullable', 'string', 'max:20'],
            'payer_name' => ['nullable', 'string', 'max:255'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'primary_color' => ['required', 'string'],
            'accent_color' => ['required', 'string'],
        ]);

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->slug ?: $this->name),
            'phone' => $this->phone ?: null,
            'payer_name' => $this->payer_name ?: null,
            'plan_id' => $this->plan_id,
            'primary_color' => $this->primary_color,
            'accent_color' => $this->accent_color,
        ];

        if ($this->editingId) {
            Company::findOrFail($this->editingId)->update($data);
        } else {
            $company = Company::create([...$data, 'active' => true]);

            if ($this->admin_email && $this->admin_password) {
                $admin = User::create([
                    'name' => $this->name.' Admin',
                    'email' => $this->admin_email,
                    'password' => Hash::make($this->admin_password),
                    'company_id' => $company->id,
                    'active' => true,
                ]);
                $admin->syncRoles(['company_admin']);
            }
        }

        $this->resetForm();
        session()->flash('success', 'Empresa salva com sucesso.');
    }

    public function edit(int $id): void
    {
        $company = Company::findOrFail($id);
        $this->editingId = $company->id;
        $this->name = $company->name;
        $this->slug = $company->slug;
        $this->phone = $company->phone ?? '';
        $this->payer_name = $company->payer_name ?? '';
        $this->plan_id = $company->plan_id;
        $this->primary_color = $company->primary_color;
        $this->accent_color = $company->accent_color;
    }

    public function toggleActive(int $id): void
    {
        $company = Company::findOrFail($id);
        $company->update(['active' => ! $company->active]);
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->slug = '';
        $this->phone = '';
        $this->payer_name = '';
        $this->plan_id = null;
        $this->primary_color = '#1e40af';
        $this->accent_color = '#f59e0b';
        $this->admin_email = '';
        $this->admin_password = '';
    }

    public function render()
    {
        return view('livewire.super.companies', [
            'companies' => Company::with('plan')->withCount('users')->orderBy('name')->get(),
            'plans' => Plan::where('active', true)->orderBy('name')->get(),
        ])->layout('layouts.super', ['title' => 'Empresas', 'subtitle' => 'Gerencie os clientes da plataforma']);
    }
}
