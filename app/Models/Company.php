<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'phone',
        'payer_name',
        'plan_id',
        'primary_color',
        'accent_color',
        'print_ticket_on_entry',
        'print_ticket_on_exit',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'print_ticket_on_entry' => 'boolean',
            'print_ticket_on_exit' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function parkingLots(): HasMany
    {
        return $this->hasMany(ParkingLot::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function tariffRules(): HasMany
    {
        return $this->hasMany(TariffRule::class);
    }

    public function dailyClosings(): HasMany
    {
        return $this->hasMany(DailyClosing::class);
    }

    public function activeTariff(): ?TariffRule
    {
        return $this->tariffRules()->where('active', true)->latest()->first();
    }
}
