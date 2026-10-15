<?php

namespace App\Livewire\Admin;

use App\Models\Competition as CompetitionModel;
use App\Models\Criterion;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Competition extends Component
{
    use WithPagination;

    public const MAX_DISPLAYED_JUDGES = 3;

    public const STATUSES = ['upcoming', 'active', 'ended'];

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterStatus = '';

    // Form lomba
    public bool $showModal = false;

    public ?CompetitionModel $editing = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|date_format:Y-m-d')]
    public string $start_date = '';

    #[Validate('required|in:00,01,02,03,04,05,06,07,08,09,10,11,12,13,14,15,16,17,18,19,20,21,22,23')]
    public string $start_hour = '00';

    #[Validate('required|digits:2')]
    public string $start_minute = '00';

    #[Validate('required|date_format:Y-m-d')]
    public string $end_date = '';

    #[Validate('required|in:00,01,02,03,04,05,06,07,08,09,10,11,12,13,14,15,16,17,18,19,20,21,22,23')]
    public string $end_hour = '00';

    #[Validate('required|digits:2')]
    public string $end_minute = '00';

    // Panel kriteria per lomba
    public ?string $expandedId = null;

    // Modal peserta per lomba
    public bool $showParticipantsModal = false;

    public ?string $managingParticipantsFor = null;

    // Kategori lomba
    public string $categoryPreset = 'school';

    /** @var array<string> */
    public array $customCategories = ['SD', 'SMP', 'SMA', 'Umum'];

    public string $newCategoryInput = '';

    // Form kriteria
    public bool $showCriterionModal = false;

    public ?Criterion $editingCriterion = null;

    #[Validate('required|string|max:255')]
    public string $criterion_name = '';

    public string $criterion_description = '';

    public int $criterion_min_score = 0;

    public int $criterion_max_score = 100;

    public bool $has_sub_items = false;

    /** @var array<int, array{id?: string|null, name: string, min_score: int, max_score: int}> */
    public array $subItems = [];

    public const SCHOOL_TYPES = ['SD', 'SMP', 'SMA', 'Umum'];

    public const PRESETS = [
        'school' => ['label' => 'Jenjang Sekolah (SD, SMP, SMA, Umum)', 'categories' => ['SD', 'SMP', 'SMA', 'Umum']],
        'marching_band' => ['label' => 'Divisi Band (Marching Band, Drum Band)', 'categories' => ['Marching Band', 'Drum Band']],
        'custom' => ['label' => 'Kustom (Tentukan sendiri)', 'categories' => []],
    ];

    /** @var array<string> */
    public array $criterionSchoolTypes = ['SD', 'SMP', 'SMA', 'Umum'];

    /** @var array<string, array<string>> */
    public array $judgeIdsBySchoolType = [];

    public function updatedHasSubItems(bool $value): void
    {
        if ($value && empty($this->subItems)) {
            $this->addSubItem();
        }
    }

    public function addSubItem(): void
    {
        $this->subItems[] = [
            'id' => null,
            'name' => '',
            'min_score' => 0,
            'max_score' => 25,
        ];
        $this->recalculateMinMaxFromSubItems();
    }

    public function removeSubItem(int $index): void
    {
        unset($this->subItems[$index]);
        $this->subItems = array_values($this->subItems);
        $this->recalculateMinMaxFromSubItems();
    }

    public function updatedSubItems(): void
    {
        $this->recalculateMinMaxFromSubItems();
    }

    public function recalculateMinMaxFromSubItems(): void
    {
        if ($this->has_sub_items && ! empty($this->subItems)) {
            $this->criterion_min_score = (int) collect($this->subItems)->sum('min_score');
            $this->criterion_max_score = (int) collect($this->subItems)->sum('max_score');
        }
    }

    // Modal daftar juri lengkap
    public bool $showJudgesModal = false;

    public string $judgesModalTitle = '';

    /** @var array<int, string> */
    public array $judgesModalNames = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryPreset(string $preset): void
    {
        if (isset(self::PRESETS[$preset]) && $preset !== 'custom') {
            $this->customCategories = self::PRESETS[$preset]['categories'];
        }
    }

    public function addCustomCategory(): void
    {
        $name = trim($this->newCategoryInput);
        if ($name !== '' && ! in_array($name, $this->customCategories, true)) {
            $this->customCategories[] = $name;
            $this->newCategoryInput = '';
        }
    }

    public function removeCustomCategory(int $index): void
    {
        unset($this->customCategories[$index]);
        $this->customCategories = array_values($this->customCategories);
    }

    protected function resetScheduleFields(): void
    {
        $this->reset(['editing', 'name', 'start_date', 'start_hour', 'start_minute', 'end_date', 'end_hour', 'end_minute', 'newCategoryInput']);
        $this->categoryPreset = 'school';
        $this->customCategories = self::PRESETS['school']['categories'];
        $this->start_hour = $this->end_hour = '00';
        $this->start_minute = $this->end_minute = '00';
    }

    public function create(): void
    {
        $this->resetScheduleFields();
        $this->showModal = true;
    }

    public function edit(CompetitionModel $competition): void
    {
        $this->editing = $competition;
        $this->name = $competition->name;
        $this->start_date = $competition->start_time->format('Y-m-d');
        $this->start_hour = $competition->start_time->format('H');
        $this->start_minute = $competition->start_time->format('i');
        $this->end_date = $competition->end_time->format('Y-m-d');
        $this->end_hour = $competition->end_time->format('H');
        $this->end_minute = $competition->end_time->format('i');

        $cats = $competition->categories_list;
        $this->customCategories = $cats;
        if ($cats === self::PRESETS['school']['categories']) {
            $this->categoryPreset = 'school';
        } elseif ($cats === self::PRESETS['marching_band']['categories']) {
            $this->categoryPreset = 'marching_band';
        } else {
            $this->categoryPreset = 'custom';
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        $hourRule = 'required|in:00,01,02,03,04,05,06,07,08,09,10,11,12,13,14,15,16,17,18,19,20,21,22,23';

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'start_hour' => $hourRule,
            'start_minute' => ['required', 'digits:2'],
            'end_date' => ['required', 'date_format:Y-m-d'],
            'end_hour' => $hourRule,
            'end_minute' => ['required', 'digits:2'],
            'customCategories' => ['required', 'array', 'min:1'],
            'customCategories.*' => ['required', 'string', 'max:50'],
        ], [
            'customCategories.min' => __('Pilih atau tentukan minimal satu jenjang/kategori lomba.'),
            'customCategories.*.required' => __('Nama kategori tidak boleh kosong.'),
        ]);

        $startTime = Carbon::createFromFormat('Y-m-d H:i', "{$validated['start_date']} {$validated['start_hour']}:{$validated['start_minute']}");
        $endTime = Carbon::createFromFormat('Y-m-d H:i', "{$validated['end_date']} {$validated['end_hour']}:{$validated['end_minute']}");

        if ($endTime->lessThanOrEqualTo($startTime)) {
            throw ValidationException::withMessages([
                'end_date' => __('Waktu selesai harus setelah waktu mulai.'),
            ]);
        }

        $categories = array_values(array_unique(array_filter(array_map('trim', $this->customCategories))));

        $payload = [
            'name' => $validated['name'],
            'categories' => $categories,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];

        if ($this->editing) {
            $oldCategories = $this->editing->categories_list;
            $this->editing->update($payload);

            // Jika daftar kategori berubah, bersihkan penugasan juri yang sudah tidak relevan
            if ($oldCategories !== $categories) {
                $criterionIds = $this->editing->criteria()->pluck('id');

                if ($criterionIds->isNotEmpty()) {
                    // Hapus penugasan juri pada kategori yang sudah tidak ada
                    DB::table('criterion_user')
                        ->whereIn('criterion_id', $criterionIds)
                        ->whereNotIn('school_type', $categories)
                        ->delete();

                    // Update school_types pada tiap kriteria agar hanya memuat kategori yang masih valid
                    foreach ($this->editing->criteria as $criterion) {
                        $updatedTypes = array_values(array_intersect($criterion->school_types ?? [], $categories));
                        // Jika seluruh kategori lama hilang, kosongkan atau set ke kategori baru
                        $criterion->update(['school_types' => $updatedTypes]);

                        // Jika kriteria tidak lagi memiliki juri untuk kategori yang tersisa dan kosong, bersihkan semua penugasan
                        if (empty($updatedTypes)) {
                            DB::table('criterion_user')->where('criterion_id', $criterion->id)->delete();
                        }
                    }
                }
            }
        } else {
            CompetitionModel::create($payload);
        }

        $this->showModal = false;
        $this->resetScheduleFields();
    }

    public function delete(CompetitionModel $competition): void
    {
        $competition->delete();
    }

    public function toggleRankOpen(CompetitionModel $competition): void
    {
        $competition->update(['rank_open' => ! $competition->rank_open]);
    }

    public function toggleHideIdentity(CompetitionModel $competition): void
    {
        $competition->update(['hide_identity' => ! $competition->hide_identity]);
    }

    public function toggleCriteria(string $competitionId): void
    {
        $this->expandedId = $this->expandedId === $competitionId ? null : $competitionId;
    }

    public function currentCompetitionCategories(): array
    {
        if ($this->expandedId) {
            $comp = CompetitionModel::find($this->expandedId);
            if ($comp) {
                return $comp->categories_list;
            }
        }

        return self::SCHOOL_TYPES;
    }

    public function manageParticipants(string $competitionId): void
    {
        $this->managingParticipantsFor = $competitionId;
        $this->showParticipantsModal = true;
    }

    public function closeParticipants(): void
    {
        $this->showParticipantsModal = false;
        $this->managingParticipantsFor = null;
    }

    public function createCriterion(): void
    {
        $this->reset(['editingCriterion', 'criterion_name', 'criterion_description', 'has_sub_items', 'subItems']);
        $this->criterion_min_score = 0;
        $this->criterion_max_score = 100;
        $cats = $this->currentCompetitionCategories();
        $this->criterionSchoolTypes = $cats;
        $this->judgeIdsBySchoolType = array_fill_keys($cats, []);
        $this->showCriterionModal = true;
    }

    public function editCriterion(Criterion $criterion): void
    {
        $this->editingCriterion = $criterion;
        $this->criterion_name = $criterion->name;
        $this->criterion_description = (string) $criterion->description;
        $this->criterion_min_score = $criterion->min_score ?? 0;
        $this->criterion_max_score = $criterion->max_score ?? 100;
        $this->criterionSchoolTypes = $criterion->school_types;

        $items = $criterion->items()->orderBy('order')->get();
        if ($items->isNotEmpty()) {
            $this->has_sub_items = true;
            $this->subItems = $items->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'min_score' => $item->min_score,
                'max_score' => $item->max_score,
            ])->all();
        } else {
            $this->has_sub_items = false;
            $this->subItems = [];
        }

        $cats = $this->currentCompetitionCategories();
        $this->judgeIdsBySchoolType = array_fill_keys($cats, []);
        foreach ($criterion->judges as $judge) {
            $this->judgeIdsBySchoolType[$judge->pivot->school_type][] = $judge->id;
        }

        $this->showCriterionModal = true;
    }

    public function saveCriterion(): void
    {
        $validCategories = $this->currentCompetitionCategories();
        $inCategoriesRule = 'in:'.implode(',', $validCategories);

        $rules = [
            'criterion_name' => ['required', 'string', 'max:255'],
            'criterion_description' => ['nullable', 'string'],
            'criterionSchoolTypes' => ['required', 'array', 'min:1'],
            'criterionSchoolTypes.*' => [$inCategoriesRule],
        ];

        if ($this->has_sub_items) {
            $rules['subItems'] = ['required', 'array', 'min:1'];
            $rules['subItems.*.name'] = ['required', 'string', 'max:255'];
            $rules['subItems.*.min_score'] = ['required', 'integer', 'min:0'];
            $rules['subItems.*.max_score'] = ['required', 'integer', 'gt:subItems.*.min_score'];
            $this->recalculateMinMaxFromSubItems();
        } else {
            $rules['criterion_min_score'] = ['required', 'integer', 'min:0'];
            $rules['criterion_max_score'] = ['required', 'integer', 'gt:criterion_min_score'];
        }

        $validated = $this->validate($rules, [
            'criterion_max_score.gt' => __('Nilai maksimal harus lebih besar dari nilai minimal (:min).', ['min' => $this->criterion_min_score]),
            'subItems.*.max_score.gt' => __('Nilai maksimal sub-item harus lebih besar dari nilai minimalnya.'),
            'subItems.min' => __('Tambahkan minimal satu sub-item jika mode rubrik diaktifkan.'),
        ], [
            'criterion_name' => __('nama kriteria'),
            'criterion_description' => __('deskripsi kriteria'),
            'criterion_min_score' => __('nilai minimal'),
            'criterion_max_score' => __('nilai maksimal'),
            'subItems.*.name' => __('nama sub-item'),
            'subItems.*.min_score' => __('nilai minimal sub-item'),
            'subItems.*.max_score' => __('nilai maksimal sub-item'),
        ]);

        foreach ($validated['criterionSchoolTypes'] as $type) {
            if (empty($this->judgeIdsBySchoolType[$type])) {
                $this->addError('judgeIdsBySchoolType.'.$type, __('Pilih minimal satu juri untuk jenjang :type.', ['type' => $type]));
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $criterion = $this->editingCriterion ?? new Criterion(['competition_id' => $this->expandedId]);
        $criterion->name = $validated['criterion_name'];
        $criterion->description = $validated['criterion_description'];
        $criterion->min_score = $this->has_sub_items ? (int) collect($this->subItems)->sum('min_score') : $validated['criterion_min_score'];
        $criterion->max_score = $this->has_sub_items ? (int) collect($this->subItems)->sum('max_score') : $validated['criterion_max_score'];
        $criterion->school_types = $validated['criterionSchoolTypes'];
        $criterion->save();

        // Sinkronisasi sub-items
        if ($this->has_sub_items) {
            $keepIds = [];
            foreach ($this->subItems as $index => $itemData) {
                if (! empty($itemData['id'])) {
                    $item = $criterion->items()->find($itemData['id']);
                    if ($item) {
                        $item->update([
                            'name' => $itemData['name'],
                            'min_score' => (int) $itemData['min_score'],
                            'max_score' => (int) $itemData['max_score'],
                            'order' => $index,
                        ]);
                        $keepIds[] = $item->id;
                        continue;
                    }
                }

                $newItem = $criterion->items()->create([
                    'name' => $itemData['name'],
                    'min_score' => (int) $itemData['min_score'],
                    'max_score' => (int) $itemData['max_score'],
                    'order' => $index,
                ]);
                $keepIds[] = $newItem->id;
            }

            $criterion->items()->whereNotIn('id', $keepIds)->delete();
        } else {
            $criterion->items()->delete();
        }

        $criterion->judges()->newPivotStatement()->where('criterion_id', $criterion->id)->delete();

        $rows = [];
        foreach ($validated['criterionSchoolTypes'] as $type) {
            foreach ($this->judgeIdsBySchoolType[$type] as $judgeId) {
                $rows[] = [
                    'criterion_id' => $criterion->id,
                    'user_id' => $judgeId,
                    'school_type' => $type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (! empty($rows)) {
            $criterion->judges()->newPivotStatement()->insert($rows);
        }

        $this->showCriterionModal = false;
        $this->reset(['editingCriterion', 'criterion_name', 'criterion_description', 'has_sub_items', 'subItems']);
        $this->criterion_min_score = 0;
        $this->criterion_max_score = 100;
        $this->criterionSchoolTypes = ['SD', 'SMP', 'SMA', 'Umum'];
        $this->judgeIdsBySchoolType = ['SD' => [], 'SMP' => [], 'SMA' => [], 'Umum' => []];
    }

    public function deleteCriterion(Criterion $criterion): void
    {
        $criterion->delete();
    }

    public function viewJudges(Criterion $criterion): void
    {
        $this->judgesModalTitle = $criterion->name;
        $this->judgesModalNames = $criterion->judges()->orderBy('school_type')->orderBy('name')->get()
            ->map(fn (User $judge) => "{$judge->name} ({$judge->pivot->school_type})")
            ->all();
        $this->showJudgesModal = true;
    }

    public function render()
    {
        return view('livewire.admin.competition', [
            'competitions' => CompetitionModel::withCount(['criteria', 'registrations'])
                ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
                ->when($this->filterStatus, fn ($query) => match ($this->filterStatus) {
                    'upcoming' => $query->where('start_time', '>', now()),
                    'active' => $query->where('start_time', '<=', now())->where('end_time', '>=', now()),
                    'ended' => $query->where('end_time', '<', now()),
                    default => $query,
                })
                ->latest('start_time')
                ->paginate(10),
            'expandedCriteria' => $this->expandedId
                ? Criterion::where('competition_id', $this->expandedId)
                    ->with(['judges' => fn ($query) => $query->orderBy('name'), 'items'])
                    ->orderBy('name')
                    ->get()
                : collect(),
            'judges' => User::where('role', 'judge')->orderBy('name')->get(),
        ]);
    }
}
