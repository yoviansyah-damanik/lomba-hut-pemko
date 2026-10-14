<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'registration_id', 'criterion_id', 'score', 'notes'])]
class Evaluation extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<Registration, $this> */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /** @return BelongsTo<Criterion, $this> */
    public function criterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class);
    }

    /** @return HasMany<EvaluationItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(EvaluationItem::class);
    }
}
