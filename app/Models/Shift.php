<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'local_uuid',
        'company_id',
        'parking_lot_id',
        'user_id',
        'opened_at',
        'closed_at',
        'opening_balance',
        'closing_balance',
        'sync_status',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parkingSessions(): HasMany
    {
        return $this->hasMany(ParkingSession::class);
    }

    public function isOpen(): bool
    {
        return $this->closed_at === null;
    }
}
