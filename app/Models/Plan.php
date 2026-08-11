<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'activation_fee',
        'monthly_per_machine',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'activation_fee' => 'decimal:2',
            'monthly_per_machine' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }
}
