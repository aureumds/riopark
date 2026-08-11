<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ParkingLot;
use App\Models\Plan;
use App\Models\TariffRule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoCompanySeeder extends Seeder
{
    public function run(): void
    {
        $plan = Plan::where('slug', 'basico')->first();

        $company = Company::firstOrCreate(
            ['slug' => 'demo-estacionamento'],
            [
                'name' => 'Demo Estacionamento',
                'phone' => '(21) 99999-0000',
                'payer_name' => 'João Demo',
                'plan_id' => $plan?->id,
                'primary_color' => '#1e40af',
                'accent_color' => '#f59e0b',
                'print_ticket_on_entry' => true,
                'print_ticket_on_exit' => true,
                'active' => true,
            ]
        );

        $lot = ParkingLot::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Pátio Central'],
            ['address' => 'Rua Demo, 100', 'capacity' => 50, 'active' => true]
        );

        TariffRule::where('company_id', $company->id)->update(['active' => false]);

        TariffRule::firstOrCreate(
            ['company_id' => $company->id, 'version' => 1],
            [
                'price_per_hour' => 5,
                'grace_minutes' => 15,
                'fraction_minutes' => 30,
                'fraction_price' => 3,
                'active' => true,
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Admin Demo',
                'password' => 'password',
                'company_id' => $company->id,
                'active' => true,
            ]
        );
        $admin->syncRoles(['company_admin']);

        $operator = User::firstOrCreate(
            ['email' => 'operador@demo.com'],
            [
                'name' => 'Operador Demo',
                'password' => 'password',
                'company_id' => $company->id,
                'parking_lot_id' => $lot->id,
                'active' => true,
            ]
        );
        $operator->syncRoles(['operator']);
    }
}
