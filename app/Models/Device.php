<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'parking_lot_id',
        'device_uid',
        'label',
        'last_seen_at',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parkingLot(): BelongsTo
    {
        return $this->belongsTo(ParkingLot::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    public function currentLicense(): ?License
    {
        return $this->licenses()
            ->whereNull('revoked_at')
            ->latest('issued_at')
            ->first();
    }
}
