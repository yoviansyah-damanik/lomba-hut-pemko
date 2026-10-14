<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['competition_id', 'name', 'description', 'school_types', 'min_score', 'max_score'])]
class Criterion extends Model
{
    use HasUuids;

    public const DEFAULT_MIN_SCORE = 0;

    public const DEFAULT_MAX_SCORE = 100;

    public const MIN_SCORE = 0;

    public const MAX_SCORE = 100;

    protected function casts(): array
    {
        return [
            'school_types' => 'array',
            'min_score' => 'integer',
            'max_score' => 'integer',
        ];
    }

    /** @return BelongsTo<Competition, $this> */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function judges(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('school_type');
    }

    /** @return BelongsToMany<User, $this> */
    public function judgesFor(string $schoolType): BelongsToMany
    {
        return $this->judges()->wherePivot('school_type', $schoolType);
    }

    /** @return HasMany<Evaluation, $this> */
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    /** @return HasMany<CriterionItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(CriterionItem::class)->orderBy('order')->orderBy('created_at');
    }

    public function hasItems(): bool
    {
        return $this->relationLoaded('items')
            ? $this->items->isNotEmpty()
            : $this->items()->exists();
    }
}
