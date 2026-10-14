<?php

namespace App\Livewire\Evaluation;

use App\Models\Competition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

class Recap extends Component
{
    #[Url]
    public string $competitionId = '';

    #[Url]
    public string $school_type = '';

    /** @return \Illuminate\Support\Collection<int, Competition> */
    protected function judgeCompetitions(): Collection
    {
        return Auth::user()->criteria()
            ->with('competition')
            ->get()
            ->pluck('competition')
            ->filter()
            ->unique('id')
            ->sortByDesc(fn (Competition $competition) => $competition->start_time)
            ->values();
    }

    public function render()
    {
        /** @var \App\Models\User $judge */
        $judge = Auth::user();

        $competitions = $this->judgeCompetitions();

        if ($this->competitionId === '' || ! $competitions->contains('id', $this->competitionId)) {
            $this->competitionId = (string) optional($competitions->first())->id;
            $this->school_type = '';
        }

        $competition = $competitions->firstWhere('id', $this->competitionId);

        $criteria = collect();
        $availableSchoolTypes = collect();
        $rows = collect();

        if ($competition) {
            $judgeCriteria = $judge->criteria()
                ->with('items')
                ->where('competition_id', $competition->id)
                ->get();

            $allowedTypesByCriterion = $judgeCriteria
                ->groupBy('id')
                ->map(fn ($group) => $group->pluck('pivot.school_type')->unique()->all());

            $criteria = $judgeCriteria->unique('id')->sortBy('name')->values();

            $availableSchoolTypes = $judgeCriteria->pluck('pivot.school_type')->unique()->sort()->values();

            $schoolTypes = ($this->school_type !== '' && $availableSchoolTypes->contains($this->school_type))
                ? [$this->school_type]
                : $availableSchoolTypes->all();

            $registrations = $competition->registrations()
                ->with([
                    'participant',
                    'evaluations' => fn ($query) => $query->where('user_id', $judge->id)->with('items.criterionItem'),
                ])
                ->whereIn('school_type', $schoolTypes)
                ->orderBy('school_type')
                ->orderBy('npp')
                ->get();

            $rows = $registrations->map(function ($registration) use ($criteria, $allowedTypesByCriterion) {
                $evaluationsByCriterion = $registration->evaluations->keyBy('criterion_id');

                $criteriaScores = $criteria->mapWithKeys(function ($criterion) use ($registration, $evaluationsByCriterion, $allowedTypesByCriterion) {
                    $applicable = in_array($registration->school_type, $allowedTypesByCriterion->get($criterion->id, []), true);
                    /** @var \App\Models\Evaluation|null $evaluation */
                    $evaluation = $evaluationsByCriterion->get($criterion->id);

                    $subItems = collect();
                    if ($criterion->hasItems()) {
                        $evaluationItemsMap = $evaluation?->items?->keyBy('criterion_item_id') ?? collect();
                        $subItems = $criterion->items->map(function ($item) use ($evaluationItemsMap) {
                            $evalItem = $evaluationItemsMap->get($item->id);

                            return [
                                'id' => $item->id,
                                'name' => $item->name,
                                'score' => $evalItem?->score,
                            ];
                        });
                    }

                    return [$criterion->id => [
                        'applicable' => $applicable,
                        'filled' => $applicable && $evaluation !== null,
                        'score' => $evaluation->score ?? null,
                        'notes' => $evaluation->notes ?? null,
                        'has_items' => $criterion->hasItems(),
                        'sub_items' => $subItems,
                    ]];
                });

                $applicableScores = $criteriaScores->filter(fn ($score) => $score['applicable']);
                $totalFilled = $applicableScores->where('filled', true)->count();
                $totalCount = $applicableScores->count();
                $isComplete = $applicableScores->isNotEmpty() && $totalFilled === $totalCount;

                $status = match (true) {
                    $isComplete => 'complete',
                    $totalFilled > 0 => 'partial',
                    default => 'unassessed',
                };

                return (object) [
                    'registration' => $registration,
                    'criteria_scores' => $criteriaScores,
                    'total_score' => $applicableScores->sum('score'),
                    'is_complete' => $isComplete,
                    'status' => $status,
                    'filled_count' => $totalFilled,
                    'total_count' => $totalCount,
                ];
            });
        }

        return view('livewire.evaluation.recap', [
            'competitions' => $competitions,
            'competition' => $competition,
            'criteria' => $criteria,
            'availableSchoolTypes' => $availableSchoolTypes,
            'rows' => $rows,
            'maskIdentity' => $competition && $competition->hide_identity,
        ]);
    }
}
