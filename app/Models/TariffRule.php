<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TariffRule extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'price_per_hour',
        'grace_minutes',
        'fraction_minutes',
        'fraction_price',
        'version',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'price_per_hour' => 'decimal:2',
            'fraction_price' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
