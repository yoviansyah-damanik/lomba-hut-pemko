<?php

namespace App\Livewire\Dashboard;

use App\Models\Criterion;
use App\Models\Evaluation;
use App\Models\Registration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class JudgeProgress extends Component
{
    public function placeholder(): string
    {
        return view('livewire.dashboard.skeleton')->render();
    }

    /**
     * Jumlah penilaian yang dimasukkan juri ini per hari, 7 hari terakhir.
     *
     * @return array<string, int>
     */
    protected function evaluationTrend(string $judgeId): array
    {
        $since = Carbon::now()->subDays(6)->startOfDay();

        $counts = Evaluation::where('user_id', $judgeId)
            ->where('created_at', '>=', $since)
            ->get()
            ->groupBy(fn (Evaluation $evaluation) => $evaluation->created_at->format('Y-m-d'))
            ->map->count();

        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $trend[$date->translatedFormat('d M')] = $counts[$date->format('Y-m-d')] ?? 0;
        }

        return $trend;
    }

    public function render()
    {
        /** @var \App\Models\User $judge */
        $judge = Auth::user();

        // Pre-fetch count registrasi per competition_id dan school_type dalam 1 kueri agregasi
        $registrationCounts = Registration::selectRaw('competition_id, school_type, count(*) as total')
            ->groupBy('competition_id', 'school_type')
            ->get()
            ->keyBy(fn ($r) => $r->competition_id.'|'.$r->school_type);

        // Pre-fetch count evaluasi juri ini per criterion_id dan school_type dalam 1 kueri agregasi
        $evaluationCounts = Evaluation::join('registrations', 'evaluations.registration_id', '=', 'registrations.id')
            ->where('evaluations.user_id', $judge->id)
            ->selectRaw('evaluations.criterion_id, registrations.school_type, count(*) as total')
            ->groupBy('evaluations.criterion_id', 'registrations.school_type')
            ->get()
            ->keyBy(fn ($e) => $e->criterion_id.'|'.$e->school_type);

        $criteria = $judge->criteria()
            ->with('competition')
            ->get()
            ->map(function (Criterion $criterion) use ($registrationCounts, $evaluationCounts) {
                $schoolType = $criterion->pivot->school_type;
                $key = $criterion->competition_id.'|'.$schoolType;
                $evalKey = $criterion->id.'|'.$schoolType;

                $criterion->assignment_school_type = $schoolType;
                $criterion->registrations_count = $registrationCounts->get($key)?->total ?? 0;
                $criterion->evaluations_count = $evaluationCounts->get($evalKey)?->total ?? 0;

                return $criterion;
            });

        $totalAssigned = $criteria->sum('registrations_count');
        $totalCompleted = $criteria->sum('evaluations_count');
        $remainingCount = max(0, $totalAssigned - $totalCompleted);

        $activeCompetitions = $criteria->pluck('competition')
            ->filter(fn ($c) => $c && $c->isActive())
            ->unique('id')
            ->values();

        $recentEvaluations = Evaluation::where('user_id', $judge->id)
            ->with(['registration', 'criterion.competition'])
            ->latest('updated_at')
            ->limit(6)
            ->get();

        return view('livewire.dashboard.judge-progress', [
            'criteria' => $criteria,
            'totalAssigned' => $totalAssigned,
            'totalCompleted' => $totalCompleted,
            'remainingCount' => $remainingCount,
            'activeCompetitions' => $activeCompetitions,
            'trend' => $this->evaluationTrend($judge->id),
            'recentEvaluations' => $recentEvaluations,
        ]);
    }
}
