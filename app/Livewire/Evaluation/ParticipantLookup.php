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

    public string $school_type = '';

    public ?Registration $registration = null;

    public bool $searched = false;

    public bool $justSaved = false;

    public function mount(): void
    {
        $competitions = $this->judgeCompetitions();

        if ($competitions->count() === 1) {
            $this->competitionId = $competitions->first()->id;
        }

        $this->syncSchoolType();
    }

    public function updatedCompetitionId(): void
    {
        $this->syncSchoolType();
    }

    public function currentCategories(): array
    {
        if ($this->competitionId) {
            $competition = Competition::find($this->competitionId);
            if ($competition) {
                return $competition->categories_list;
            }
        }

        return ['SD', 'SMP', 'SMA', 'Umum'];
    }

    protected function syncSchoolType(): void
    {
        $categories = $this->currentCategories();
        if ($this->school_type === '' || ! in_array($this->school_type, $categories, true)) {
            $this->school_type = $categories[0] ?? 'SD';
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
        $categories = $this->currentCategories();

        $this->validate([
            'competitionId' => ['required', 'exists:competitions,id'],
            'npp' => ['required', 'string'],
            'school_type' => ['required', 'string', 'in:'.implode(',', $categories)],
        ], attributes: [
            'competitionId' => __('lomba'),
            'npp' => __('NPP'),
            'school_type' => __('kategori'),
        ]);

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
