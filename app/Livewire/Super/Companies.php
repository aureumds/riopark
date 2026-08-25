<?php

namespace App\Livewire\Super;

use App\Models\Company;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Companies extends Component
{
    public string $name = '';

    public string $slug = '';

    public string $phone = '';

    public string $payer_name = '';

    public mixed $plan_id = null;

    public string $primary_color = '#1e40af';

    public string $accent_color = '#f59e0b';

    public string $admin_email = '';

    public string $admin_password = '';

    public function updatedName(string $value): void
    {
        $this->slug = Str::slug($value);
    }

    public function save(): void
    {
        $this->plan_id = $this->plan_id === '' ? null : $this->plan_id;

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:companies,slug'],
            'phone' => ['nullable', 'string', 'max:20'],
            'payer_name' => ['nullable', 'string', 'max:255'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'primary_color' => ['required', 'string'],
            'accent_color' => ['required', 'string'],
            'admin_email' => [
                Rule::requiredIf(filled($this->admin_password)),
                'nullable',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'admin_password' => [
                Rule::requiredIf(filled($this->admin_email)),
                'nullable',
                'string',
                'min:6',
            ],
        ]);

        $company = Company::create([
            'name' => $this->name,
            'slug' => Str::slug($this->slug ?: $this->name),
            'phone' => $this->phone ?: null,
            'payer_name' => $this->payer_name ?: null,
            'plan_id' => $this->plan_id ?: null,
            'primary_color' => $this->primary_color,
            'accent_color' => $this->accent_color,
            'active' => true,
            'subscription_status' => 'trial',
        ]);

        if ($this->admin_email && $this->admin_password) {
            $admin = User::create([
                'name' => $this->name.' Admin',
                'email' => $this->admin_email,
                'password' => $this->admin_password,
                'company_id' => $company->id,
                'active' => true,
            ]);
            $admin->syncRoles(['company_admin']);
        }

        session()->flash('success', 'Empresa cadastrada.');

        $this->reset('name', 'slug', 'phone', 'payer_name', 'plan_id', 'admin_email', 'admin_password');
        $this->primary_color = '#1e40af';
        $this->accent_color = '#f59e0b';
    }

    public function toggleActive(int $id): void
    {
        $company = Company::findOrFail($id);
        $company->update(['active' => ! $company->active]);
        session()->flash('success', $company->active ? 'Empresa ativada.' : 'Empresa desativada.');
    }

    public function delete(int $id): void
    {
        $company = Company::findOrFail($id);
        $name = $company->name;
        $company->delete();
        session()->flash('success', "Empresa \"{$name}\" excluída.");
    }

    public function render()
    {
        return view('livewire.super.companies', [
            'companies' => Company::with('plan')->withCount('users')->orderBy('name')->get(),
            'plans' => Plan::where('active', true)->orderBy('name')->get(),
        ])->layout('components.layouts.super', ['title' => 'Empresas', 'subtitle' => 'Cadastre e gerencie os clientes']);
    }
}
