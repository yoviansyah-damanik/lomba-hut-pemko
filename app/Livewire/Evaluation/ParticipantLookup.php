<?php

namespace App\Livewire\Evaluation;

use App\Models\Competition;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Session;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ParticipantLookup extends Component
{
    #[Validate('required|exists:competitions,id', as: 'lomba')]
    public string $competitionId = '';

    #[Validate('required|string', as: 'NPP')]
    public string $npp = '';

    #[Session]
    #[Validate('required|in:SD,SMP,SMA,Umum', as: 'jenis sekolah')]
    public string $school_type = 'SD';

    public ?Registration $registration = null;

    public bool $searched = false;

    public bool $justSaved = false;

    public function mount(): void
    {
        $competitions = $this->judgeCompetitions();

        if ($competitions->count() === 1) {
            $this->competitionId = $competitions->first()->id;
        }
    }

    /** @var \Illuminate\Support\Collection<int, Competition>|null */
    private ?\Illuminate\Support\Collection $competitionsCache = null;

    /** @return \Illuminate\Support\Collection<int, Competition> */
    public function judgeCompetitions()
    {
        if ($this->competitionsCache !== null) {
            return $this->competitionsCache;
        }

        /** @var \App\Models\User $judge */
        $judge = Auth::user();

        return $this->competitionsCache = $judge->criteria()
            ->with('competition')
            ->get()
            ->pluck('competition')
            ->filter(fn (?Competition $competition) => $competition && $competition->isActive())
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    public function search(): void
    {
        $this->validate();

        $this->registration = Registration::with(['participant', 'competition'])
            ->where('competition_id', $this->competitionId)
            ->where('npp', $this->npp)
            ->where('school_type', $this->school_type)
            ->first();

        $this->searched = true;
        $this->justSaved = false;
    }

    public function resetSearch(): void
    {
        $this->reset(['npp', 'registration', 'searched']);
    }

    #[On('evaluation-saved')]
    public function onEvaluationSaved(): void
    {
        $this->resetSearch();
        $this->justSaved = true;
    }

    public function render()
    {
        return view('livewire.evaluation.participant-lookup', [
            'competitions' => $this->judgeCompetitions(),
        ]);
    }
}
