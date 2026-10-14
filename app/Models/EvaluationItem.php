<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['evaluation_id', 'criterion_item_id', 'score'])]
class EvaluationItem extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    /** @return BelongsTo<Evaluation, $this> */
    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    /** @return BelongsTo<CriterionItem, $this> */
    public function criterionItem(): BelongsTo
    {
        return $this->belongsTo(CriterionItem::class);
    }
}
