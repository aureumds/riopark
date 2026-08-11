<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::firstOrCreate(
            ['slug' => 'basico'],
            [
                'name' => 'Básico',
                'description' => 'Plano inicial com cobrança por ativação e mensalidade por maquininha.',
                'activation_fee' => 150.00,
                'monthly_per_machine' => 49.90,
                'active' => true,
            ]
        );

        Plan::firstOrCreate(
            ['slug' => 'profissional'],
            [
                'name' => 'Profissional',
                'description' => 'Para operações com múltiplas maquininhas.',
                'activation_fee' => 99.00,
                'monthly_per_machine' => 39.90,
                'active' => true,
            ]
        );
    }
}
