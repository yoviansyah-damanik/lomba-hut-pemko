<?php

namespace App\Livewire\LiveRank;

use App\Models\Competition;
use App\Models\Registration;
use Livewire\Attributes\Url;
use Livewire\Component;

class Board extends Component
{
    public const SCHOOL_TYPES = ['SD', 'SMP', 'SMA', 'Umum'];

    #[Url]
    public string $school_type = 'SD';

    public string $competitionId;

    public function mount(string $competitionId): void
    {
        $this->competitionId = $competitionId;

        if (! in_array($this->school_type, self::SCHOOL_TYPES, true)) {
            $this->school_type = 'SD';
        }
    }

    public function render()
    {
        $competition = Competition::with('criteria.judges')
            ->where('id', $this->competitionId)
            ->where('rank_open', true)
            ->first();

        $registrations = collect();

        if ($competition) {
            $registrations = $competition->registrations()
                ->with('participant')
                ->withSum('evaluations as raw_score', 'score')
                ->withCount('evaluations as evaluations_count')
                ->when($this->school_type, fn ($query) => $query->where('school_type', $this->school_type))
                ->get()
                ->each(function (Registration $reg) {
                    $reg->raw_score = (int) ($reg->raw_score ?? 0);
                    $reg->final_score = $reg->calculateFinalScore($reg->raw_score);
                    $reg->total_score = $reg->final_score;
                });

            $registrations = Registration::sortForRanking($registrations)
                ->map(function ($registration) use ($competition) {
                    $expected = $competition->criteria
                        ->filter(fn ($criterion) => in_array($registration->school_type, $criterion->school_types, true))
                        ->sum(fn ($criterion) => $criterion->judges->where('pivot.school_type', $registration->school_type)->count());

                    $registration->is_complete = $expected > 0 && $registration->evaluations_count >= $expected;

                    return $registration;
                })
                ->take(6)
                ->values();
        }

        $schoolTypeCounts = [];
        $totalParticipants = 0;

        if ($competition) {
            $counts = Registration::where('competition_id', $competition->id)
                ->selectRaw('school_type, count(*) as total')
                ->groupBy('school_type')
                ->pluck('total', 'school_type');

            foreach (self::SCHOOL_TYPES as $type) {
                $schoolTypeCounts[$type] = (int) ($counts[$type] ?? 0);
            }
            $totalParticipants = array_sum($schoolTypeCounts);
        }

        return view('livewire.live-rank.board', [
            'competition' => $competition,
            'participants' => $registrations,
            'maskIdentity' => $competition && $competition->hide_identity,
            'schoolTypeCounts' => $schoolTypeCounts,
            'totalParticipants' => $totalParticipants,
        ]);
    }

    /**
     * Label peringkat: Juara 1-3 dan Harapan 1-3.
     */
    public static function rankLabel(int $rank): string
    {
        return match ($rank) {
            1 => 'Juara 1',
            2 => 'Juara 2',
            3 => 'Juara 3',
            4 => 'Harapan 1',
            5 => 'Harapan 2',
            6 => 'Harapan 3',
            default => (string) $rank,
        };
    }
}
