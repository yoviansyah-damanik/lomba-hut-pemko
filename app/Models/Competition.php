<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable(['name', 'categories', 'start_time', 'end_time', 'rank_open', 'hide_identity'])]
class Competition extends Model
{
    use HasUuids;

    public const DEFAULT_CATEGORIES = ['SD', 'SMP', 'SMA', 'Umum'];

    protected function casts(): array
    {
        return [
            'categories' => 'array',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'rank_open' => 'boolean',
            'hide_identity' => 'boolean',
        ];
    }

    /**
     * Dapatkan daftar kategori/jenjang kompetisi (default: SD, SMP, SMA, Umum).
     *
     * @return array<string>
     */
    public function getCategoriesListAttribute(): array
    {
        return !empty($this->categories) ? $this->categories : self::DEFAULT_CATEGORIES;
    }

    public function isActive(): bool
    {
        $now = Carbon::now();

        return $now->between($this->start_time, $this->end_time);
    }

    /** @return HasMany<Criterion, $this> */
    public function criteria(): HasMany
    {
        return $this->hasMany(Criterion::class);
    }

    /** @return HasMany<Registration, $this> */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
