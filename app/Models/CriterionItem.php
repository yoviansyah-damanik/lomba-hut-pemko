<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['criterion_id', 'name', 'min_score', 'max_score', 'order'])]
class CriterionItem extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'min_score' => 'integer',
            'max_score' => 'integer',
            'order' => 'integer',
        ];
    }

    /** @return BelongsTo<Criterion, $this> */
    public function criterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class);
    }

    /** @return HasMany<EvaluationItem, $this> */
    public function evaluationItems(): HasMany
    {
        return $this->hasMany(EvaluationItem::class);
    }
}
