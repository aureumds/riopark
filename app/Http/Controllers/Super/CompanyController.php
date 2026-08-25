<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function edit(Company $company): View
    {
        $admin = User::role('company_admin')->where('company_id', $company->id)->orderBy('id')->first();

        return view('super.companies.edit', [
            'company' => $company,
            'admin' => $admin,
            'plans' => Plan::where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $admin = User::role('company_admin')->where('company_id', $company->id)->orderBy('id')->first();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('companies', 'slug')->ignore($company->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'payer_name' => ['nullable', 'string', 'max:255'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'primary_color' => ['required', 'string', 'max:20'],
            'accent_color' => ['required', 'string', 'max:20'],
            'admin_email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin?->id)],
            'admin_password' => ['nullable', 'string', 'min:6'],
        ]);

        $company->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['slug'] ?: $data['name']),
            'phone' => $data['phone'] ?: null,
            'payer_name' => $data['payer_name'] ?: null,
            'plan_id' => $data['plan_id'] ?: null,
            'primary_color' => $data['primary_color'],
            'accent_color' => $data['accent_color'],
        ]);

        if ($admin) {
            $payload = [
                'name' => $data['name'].' Admin',
                'email' => $data['admin_email'] ?: $admin->email,
            ];
            if (! empty($data['admin_password'])) {
                $payload['password'] = $data['admin_password'];
            }
            $admin->update($payload);
        } elseif (! empty($data['admin_email']) && ! empty($data['admin_password'])) {
            $newAdmin = User::create([
                'name' => $data['name'].' Admin',
                'email' => $data['admin_email'],
                'password' => $data['admin_password'],
                'company_id' => $company->id,
                'active' => true,
            ]);
            $newAdmin->syncRoles(['company_admin']);
        }

        return redirect()->route('super.companies')->with('success', 'Empresa atualizada.');
    }
}
