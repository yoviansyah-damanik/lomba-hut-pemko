<?php

namespace App\Livewire\Evaluation;

use App\Models\Criterion;
use App\Models\Evaluation;
use App\Models\EvaluationItem;
use App\Models\Registration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class CriterionForm extends Component
{
    public Registration $registration;

    public string $competitionId;

    /** @var array<string, int|null> */
    public array $scores = [];

    /** @var array<string, array<string, int|null>> */
    public array $itemScores = [];

    /** @var array<string, string> */
    public array $notes = [];

    public bool $showConfirm = false;

    /** @var Collection<int, Criterion>|null */
    private ?Collection $criteriaCache = null;

    public function mount(Registration $registration, string $competitionId): void
    {
        $this->registration = $registration;
        $this->competitionId = $competitionId;

        /** @var \App\Models\User $judge */
        $judge = Auth::user();

        $existing = Evaluation::where('user_id', $judge->id)
            ->where('registration_id', $registration->id)
            ->with('items')
            ->get()
            ->keyBy('criterion_id');

        foreach ($this->activeCriteria() as $criterion) {
            $eval = $existing[$criterion->id] ?? null;
            $this->scores[$criterion->id] = $eval->score ?? null;
            $this->notes[$criterion->id] = $eval->notes ?? '';

            if ($criterion->items->isNotEmpty()) {
                $existingItems = $eval ? $eval->items->keyBy('criterion_item_id') : collect();
                $this->itemScores[$criterion->id] = [];
                $sum = 0;
                $hasAny = false;

                foreach ($criterion->items as $item) {
                    $itemVal = $existingItems[$item->id]->score ?? null;
                    $this->itemScores[$criterion->id][$item->id] = $itemVal;
                    if ($itemVal !== null) {
                        $sum += (int) $itemVal;
                        $hasAny = true;
                    }
                }

                if ($hasAny) {
                    $this->scores[$criterion->id] = $sum;
                }
            }
        }
    }

    /**
     * Kriteria milik juri yang login, khusus untuk jenjang sekolah peserta ini,
     * pada lomba yang dipilih dan sedang berlangsung.
     *
     * @return \Illuminate\Support\Collection<int, Criterion>
     */
    public function activeCriteria(): Collection
    {
        if ($this->criteriaCache !== null) {
            return $this->criteriaCache;
        }

        /** @var \App\Models\User $judge */
        $judge = Auth::user();

        return $this->criteriaCache = $judge->criteria()
            ->wherePivot('school_type', $this->registration->school_type)
            ->where('competition_id', $this->competitionId)
            ->with(['competition', 'items'])
            ->get()
            ->filter(fn (Criterion $criterion) => $criterion->competition->isActive())
            ->sortBy('name')
            ->values();
    }

    public function updatedItemScores($value, $key): void
    {
        $parts = explode('.', (string) $key);
        if (! empty($parts[0])) {
            $this->recalculateCriterionScore($parts[0]);
        }
    }

    public function recalculateCriterionScore(string $criterionId): void
    {
        if (isset($this->itemScores[$criterionId])) {
            $sum = 0;
            $hasAny = false;
            foreach ($this->itemScores[$criterionId] as $val) {
                if ($val !== null && $val !== '') {
                    $sum += (int) $val;
                    $hasAny = true;
                }
            }
            $this->scores[$criterionId] = $hasAny ? $sum : null;
        }
    }

    public function incrementItemScore(string $criterionId, string $itemId): void
    {
        $criterion = $this->activeCriteria()->firstWhere('id', $criterionId);
        $item = $criterion?->items->firstWhere('id', $itemId);
        if (! $item) {
            return;
        }

        $min = $item->min_score;
        $max = $item->max_score;
        $current = $this->itemScores[$criterionId][$itemId] ?? ($min - 1);
        $this->itemScores[$criterionId][$itemId] = min($max, ((int) $current) + 1);
        $this->recalculateCriterionScore($criterionId);
    }

    public function decrementItemScore(string $criterionId, string $itemId): void
    {
        $criterion = $this->activeCriteria()->firstWhere('id', $criterionId);
        $item = $criterion?->items->firstWhere('id', $itemId);
        if (! $item) {
            return;
        }

        $min = $item->min_score;
        $max = $item->max_score;
        $current = $this->itemScores[$criterionId][$itemId] ?? ($min + 1);
        $this->itemScores[$criterionId][$itemId] = max($min, ((int) $current) - 1);
        $this->recalculateCriterionScore($criterionId);
    }

    /** @return array<string, array<string>> */
    protected function rules(): array
    {
        $rules = [];

        foreach ($this->activeCriteria() as $criterion) {
            if ($criterion->items->isNotEmpty()) {
                foreach ($criterion->items as $item) {
                    $rules["itemScores.{$criterion->id}.{$item->id}"] = [
                        'required',
                        'integer',
                        'min:'.$item->min_score,
                        'max:'.$item->max_score,
                    ];
                }
            } else {
                $min = $criterion->min_score ?? Criterion::DEFAULT_MIN_SCORE;
                $max = $criterion->max_score ?? Criterion::DEFAULT_MAX_SCORE;
                $rules["scores.{$criterion->id}"] = ['required', 'integer', 'min:'.$min, 'max:'.$max];
            }
            $rules["notes.{$criterion->id}"] = ['nullable', 'string'];
        }

        return $rules;
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        $attributes = [];

        foreach ($this->activeCriteria() as $criterion) {
            if ($criterion->items->isNotEmpty()) {
                foreach ($criterion->items as $item) {
                    $attributes["itemScores.{$criterion->id}.{$item->id}"] = __('nilai ":item"', ['item' => $item->name]);
                }
            } else {
                $attributes["scores.{$criterion->id}"] = __('nilai ":name"', ['name' => $criterion->name]);
            }
            $attributes["notes.{$criterion->id}"] = __('catatan ":name"', ['name' => $criterion->name]);
        }

        return $attributes;
    }

    public function incrementScore(string $criterionId): void
    {
        $criterion = $this->activeCriteria()->firstWhere('id', $criterionId);
        $min = $criterion?->min_score ?? Criterion::DEFAULT_MIN_SCORE;
        $max = $criterion?->max_score ?? Criterion::DEFAULT_MAX_SCORE;

        $current = $this->scores[$criterionId] ?? ($min - 1);
        $this->scores[$criterionId] = min($max, ((int) $current) + 1);
    }

    public function decrementScore(string $criterionId): void
    {
        $criterion = $this->activeCriteria()->firstWhere('id', $criterionId);
        $min = $criterion?->min_score ?? Criterion::DEFAULT_MIN_SCORE;
        $max = $criterion?->max_score ?? Criterion::DEFAULT_MAX_SCORE;

        $current = $this->scores[$criterionId] ?? ($min + 1);
        $this->scores[$criterionId] = max($min, ((int) $current) - 1);
    }

    public function review(): void
    {
        $this->validate($this->rules());
        $this->showConfirm = true;
    }

    public function cancelReview(): void
    {
        $this->showConfirm = false;
    }

    public function save(): void
    {
        $this->validate($this->rules());

        /** @var \App\Models\User $judge */
        $judge = Auth::user();

        $criteria = $this->activeCriteria();
        $now = now();
        $records = [];

        foreach ($criteria as $criterion) {
            $id = $criterion->id;
            if ($criterion->items->isNotEmpty()) {
                $this->recalculateCriterionScore($id);
            }

            $records[] = [
                'id' => (string) Str::uuid(),
                'user_id' => $judge->id,
                'registration_id' => $this->registration->id,
                'criterion_id' => $id,
                'score' => (int) $this->scores[$id],
                'notes' => filled($this->notes[$id] ?? null) ? $this->notes[$id] : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($records)) {
            DB::transaction(function () use ($records, $criteria, $judge, $now) {
                Evaluation::upsert(
                    $records,
                    ['user_id', 'registration_id', 'criterion_id'],
                    ['score', 'notes', 'updated_at']
                );

                // Simpan rincian nilai sub-item ke evaluation_items
                foreach ($criteria as $criterion) {
                    if ($criterion->items->isNotEmpty()) {
                        $evaluation = Evaluation::where('user_id', $judge->id)
                            ->where('registration_id', $this->registration->id)
                            ->where('criterion_id', $criterion->id)
                            ->first();

                        if ($evaluation) {
                            $itemRecords = [];
                            foreach ($criterion->items as $item) {
                                $scoreVal = (int) ($this->itemScores[$criterion->id][$item->id] ?? 0);
                                $itemRecords[] = [
                                    'id' => (string) Str::uuid(),
                                    'evaluation_id' => $evaluation->id,
                                    'criterion_item_id' => $item->id,
                                    'score' => $scoreVal,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];
                            }
                            EvaluationItem::upsert(
                                $itemRecords,
                                ['evaluation_id', 'criterion_item_id'],
                                ['score', 'updated_at']
                            );
                        }
                    }
                }
            });
        }

        $this->showConfirm = false;
        $this->dispatch('evaluation-saved');
    }

    public function render()
    {
        return view('livewire.evaluation.criterion-form', [
            'criteria' => $this->activeCriteria(),
        ]);
    }
}
