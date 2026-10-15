<?php

namespace App\Livewire\Admin;

use App\Models\Competition;
use App\Models\Participant as ParticipantModel;
use App\Models\Registration;
use Livewire\Component;

class CompetitionParticipants extends Component
{
    public const SCHOOL_TYPES = ['SD', 'SMP', 'SMA', 'Umum'];

    public string $competitionId;

    public string $activeTab = 'slot';

    // Generate slot massal
    /** @var array<string, int> */
    public array $slotCounts = ['SD' => 0, 'SMP' => 0, 'SMA' => 0, 'Umum' => 0];

    public int $nppDigits = 3;

    public string $registeredFilter = '';

    // Tambah dari direktori Peserta
    public string $search = '';

    public string $school_type = '';

    /** @var array<string> */
    public array $selectedIds = [];

    /** @var array<string, string> */
    public array $npp = [];

    // Sinkronisasi identitas
    public ?string $syncingRegistrationId = null;

    public string $syncSearch = '';

    public string $newSchoolName = '';

    public function mount(string $competitionId): void
    {
        $this->competitionId = $competitionId;
        $competition = $this->competition();
        $this->slotCounts = array_fill_keys($competition->categories_list, 0);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function updatedSearch(): void
    {
        $this->selectedIds = [];
    }

    public function updatedSchoolType(): void
    {
        $this->selectedIds = [];
    }

    public function generateSlots(): void
    {
        $competition = $this->competition();
        $categories = $competition->categories_list;
        $generated = false;
        $digits = in_array($this->nppDigits, [2, 3], true) ? $this->nppDigits : 3;

        foreach ($categories as $type) {
            $count = (int) ($this->slotCounts[$type] ?? 0);

            if ($count <= 0) {
                continue;
            }

            $generated = true;
            $startSeq = $competition->registrations()->where('school_type', $type)->count() + 1;

            for ($i = 0; $i < $count; $i++) {
                $seq = $startSeq + $i;

                Registration::create([
                    'competition_id' => $competition->id,
                    'school_type' => $type,
                    'npp' => sprintf('%0'.$digits.'d', $seq),
                    'label' => __(':type Peserta :seq', ['type' => $type, 'seq' => $seq]),
                ]);
            }
        }

        if ($generated) {
            $this->slotCounts = array_fill_keys($categories, 0);
            $this->activeTab = 'registered';
        }
    }

    public function addSelected(): void
    {
        if (empty($this->selectedIds)) {
            return;
        }

        $competition = $this->competition();
        $existingNpp = $competition->registrations()->get(['school_type', 'npp'])
            ->groupBy('school_type')
            ->map(fn ($rows) => $rows->pluck('npp')->all());
        $usedInBatch = [];
        $added = [];

        foreach ($this->selectedIds as $participantId) {
            $npp = trim($this->npp[$participantId] ?? '');

            if ($npp === '') {
                $this->addError("npp.{$participantId}", __('NPP wajib diisi.'));

                continue;
            }

            $participant = ParticipantModel::find($participantId);

            if (! $participant) {
                continue;
            }

            $batchKey = "{$participant->school_type}:{$npp}";

            if (in_array($npp, $existingNpp[$participant->school_type] ?? [], true) || in_array($batchKey, $usedInBatch, true)) {
                $this->addError("npp.{$participantId}", __('NPP sudah digunakan pada lomba ini.'));

                continue;
            }

            $usedInBatch[] = $batchKey;
            $added[] = $participantId;

            Registration::create([
                'competition_id' => $competition->id,
                'participant_id' => $participant->id,
                'school_type' => $participant->school_type,
                'npp' => $npp,
            ]);
        }

        $this->selectedIds = array_values(array_diff($this->selectedIds, $added));
        $this->npp = array_diff_key($this->npp, array_flip($added));
    }

    public function updateNpp(string $registrationId, string $value): void
    {
        $value = trim($value);
        $competition = $this->competition();
        $registration = Registration::findOrFail($registrationId);

        if ($value === '') {
            $this->addError("assignedNpp.{$registrationId}", __('NPP wajib diisi.'));

            return;
        }

        $conflict = $competition->registrations()
            ->where('school_type', $registration->school_type)
            ->where('npp', $value)
            ->where('id', '!=', $registrationId)
            ->exists();

        if ($conflict) {
            $this->addError("assignedNpp.{$registrationId}", __('NPP sudah digunakan pada lomba ini.'));

            return;
        }

        Registration::where('id', $registrationId)->update(['npp' => $value]);
    }

    public function remove(Registration $registration): void
    {
        $registration->delete();
    }

    public function removeAll(): void
    {
        $this->competition()->registrations()->delete();
    }

    public function openSync(string $registrationId): void
    {
        $this->syncingRegistrationId = $registrationId;
        $this->syncSearch = '';
        $this->newSchoolName = '';
        $this->resetErrorBag('newSchoolName');
    }

    public function closeSync(): void
    {
        $this->syncingRegistrationId = null;
    }

    public function syncExisting(string $registrationId, string $participantId): void
    {
        $registration = Registration::findOrFail($registrationId);
        $participant = ParticipantModel::findOrFail($participantId);

        if ($participant->school_type !== $registration->school_type) {
            return;
        }

        $registration->update(['participant_id' => $participant->id]);
        $this->closeSync();
    }

    public function syncNew(string $registrationId): void
    {
        $name = trim($this->newSchoolName);

        if ($name === '') {
            $this->addError('newSchoolName', __('Nama sekolah wajib diisi.'));

            return;
        }

        $registration = Registration::findOrFail($registrationId);

        $participant = ParticipantModel::create([
            'school_name' => $name,
            'school_type' => $registration->school_type,
        ]);

        $registration->update(['participant_id' => $participant->id]);
        $this->closeSync();
    }

    public function unsync(string $registrationId): void
    {
        Registration::where('id', $registrationId)->update(['participant_id' => null]);
    }

    protected function competition(): Competition
    {
        return Competition::findOrFail($this->competitionId);
    }

    protected function linkedParticipantIds(): array
    {
        return $this->competition()->registrations()->whereNotNull('participant_id')->pluck('participant_id')->all();
    }

    protected function availableParticipantsQuery()
    {
        return ParticipantModel::query()
            ->whereNotIn('id', $this->linkedParticipantIds())
            ->when($this->school_type, fn ($query) => $query->where('school_type', $this->school_type))
            ->when($this->search, fn ($query) => $query->where('school_name', 'like', "%{$this->search}%"))
            ->orderBy('school_name');
    }

    protected function syncCandidatesQuery(Registration $registration)
    {
        return ParticipantModel::query()
            ->where('school_type', $registration->school_type)
            ->whereNotIn('id', $this->linkedParticipantIds())
            ->when($this->syncSearch, fn ($query) => $query->where('school_name', 'like', "%{$this->syncSearch}%"))
            ->orderBy('school_name')
            ->limit(20);
    }

    public function render()
    {
        $competition = $this->competition();
        $registrations = $competition->registrations()->with('participant')->orderBy('school_type')->orderBy('npp')->get();

        $syncingRegistration = $this->syncingRegistrationId
            ? $registrations->firstWhere('id', $this->syncingRegistrationId)
            : null;

        $filteredRegistrations = $this->registeredFilter
            ? $registrations->where('school_type', $this->registeredFilter)->values()
            : $registrations;

        return view('livewire.admin.competition-participants', [
            'competition' => $competition,
            'registrations' => $registrations,
            'filteredRegistrations' => $filteredRegistrations,
            'availableParticipants' => $this->availableParticipantsQuery()->limit(50)->get(),
            'syncingRegistration' => $syncingRegistration,
            'syncCandidates' => $syncingRegistration ? $this->syncCandidatesQuery($syncingRegistration)->get() : collect(),
        ]);
    }
}
