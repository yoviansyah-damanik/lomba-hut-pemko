<?php

namespace App\Livewire\Admin;

use App\Models\Participant as ParticipantModel;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Participant extends Component
{
    use WithPagination;

    public const SCHOOL_TYPES = ['SD', 'SMP', 'SMA', 'Umum'];

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterSchoolType = '';

    public bool $showModal = false;

    public ?ParticipantModel $editing = null;

    #[Validate('required|string|max:255')]
    public string $school_name = '';

    #[Validate('required|in:SD,SMP,SMA,Umum')]
    public string $school_type = 'SD';

    // Modal riwayat lomba
    public bool $showHistoryModal = false;

    public ?string $viewingHistoryFor = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterSchoolType(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['editing', 'school_name']);
        $this->school_type = 'SD';
        $this->showModal = true;
    }

    public function edit(ParticipantModel $participant): void
    {
        $this->editing = $participant;
        $this->school_name = $participant->school_name;
        $this->school_type = $participant->school_type;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editing) {
            $this->editing->update($validated);
        } else {
            ParticipantModel::create($validated);
        }

        $this->showModal = false;
        $this->reset(['editing', 'school_name']);
        $this->school_type = 'SD';
    }

    public function delete(ParticipantModel $participant): void
    {
        $participant->delete();
    }

    public function viewHistory(string $participantId): void
    {
        $this->viewingHistoryFor = $participantId;
        $this->showHistoryModal = true;
    }

    public function closeHistory(): void
    {
        $this->showHistoryModal = false;
        $this->viewingHistoryFor = null;
    }

    public function render()
    {
        return view('livewire.admin.participant', [
            'participants' => ParticipantModel::query()
                ->when($this->search, fn ($query) => $query->where('school_name', 'like', "%{$this->search}%"))
                ->when($this->filterSchoolType, fn ($query) => $query->where('school_type', $this->filterSchoolType))
                ->latest()
                ->paginate(10),
        ]);
    }
}
