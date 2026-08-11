<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParkingLot extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'address',
        'capacity',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function parkingSessions(): HasMany
    {
        return $this->hasMany(ParkingSession::class);
    }

    public function operators(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
