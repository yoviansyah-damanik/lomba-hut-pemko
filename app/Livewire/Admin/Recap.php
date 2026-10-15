<?php

namespace App\Livewire\Admin;

use App\Models\Competition;
use App\Models\Criterion;
use App\Models\Registration;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;

class Recap extends Component
{
    public const SCHOOL_TYPES = ['SD', 'SMP', 'SMA', 'Umum'];

    #[Url]
    public string $school_type = '';

    #[Url]
    public string $search = '';

    #[Url]
    public bool $include_notes = false;

    public string $competitionId;

    public ?string $tieClusterKey = null;

    /** @var array<string, int> */
    public array $tiePositions = [];

    public ?string $detailRegistrationId = null;

    public bool $showBeritaAcaraModal = false;

    public string $beritaAcaraTempat = '';

    public string $beritaAcaraTanggal = '';

    public string $beritaAcaraKoordinator = '';

    // Modal penyesuaian nilai (tambah / kurang)
    public bool $showAdjustmentModal = false;

    public ?string $adjustingRegistrationId = null;

    public string $adjustingRegistrationName = '';

    public string $adjustingRegistrationNpp = '';

    public int $score_addition = 0;

    public int $score_deduction = 0;

    public string $adjustment_notes = '';

    public function mount(string $competitionId): void
    {
        $this->competitionId = $competitionId;
        $competition = $this->competition();
        $categories = $competition ? $competition->categories_list : self::SCHOOL_TYPES;

        if ($this->school_type !== '' && ! in_array($this->school_type, $categories, true)) {
            $this->school_type = '';
        }
    }

    public function printAllTypes(): void
    {
        $this->school_type = '';
        $this->dispatch('trigger-print');
    }

    /**
     * Peringkat 4-6 ditampilkan sebagai "Harapan 1-3", sesuai konvensi
     * penamaan juara pada lomba pramuka/sekolah.
     */
    public static function rankLabel(int $rank): string
    {
        if ($rank >= 4 && $rank <= 6) {
            return __('Harapan :n', ['n' => $rank - 3]);
        }

        return (string) $rank;
    }

    /** Peringkat 1-6 dalam format berita acara resmi: "JUARA I..III" lalu "HARAPAN I..III". */
    public static function beritaAcaraRankLabel(int $rank): string
    {
        $romans = [1 => 'I', 2 => 'II', 3 => 'III'];

        if ($rank >= 1 && $rank <= 3) {
            return 'JUARA '.$romans[$rank];
        }

        if ($rank >= 4 && $rank <= 6) {
            return 'HARAPAN '.$romans[$rank - 3];
        }

        return (string) $rank;
    }

    public function openBeritaAcara(): void
    {
        $competition = $this->competition();
        $categories = $competition ? $competition->categories_list : self::SCHOOL_TYPES;

        if (! in_array($this->school_type, $categories, true)) {
            return;
        }

        $this->beritaAcaraTanggal = now()->format('Y-m-d');
        $this->showBeritaAcaraModal = true;
    }

    public function closeBeritaAcara(): void
    {
        $this->showBeritaAcaraModal = false;
    }

    public function downloadBeritaAcara()
    {
        $competition = $this->competition();
        $categories = $competition ? $competition->categories_list : self::SCHOOL_TYPES;

        abort_unless($competition, 404);
        abort_unless(in_array($this->school_type, $categories, true), 422);

        $this->validate([
            'beritaAcaraTempat' => ['required', 'string', 'max:255'],
            'beritaAcaraTanggal' => ['required', 'date'],
            'beritaAcaraKoordinator' => ['required', 'string', 'max:255'],
        ], attributes: [
            'beritaAcaraTempat' => __('tempat'),
            'beritaAcaraTanggal' => __('tanggal'),
            'beritaAcaraKoordinator' => __('nama koordinator'),
        ]);

        $pdf = Pdf::loadView('pdf.berita-acara', [
            'competition' => $competition,
            'schoolType' => $this->school_type,
            'rows' => $this->beritaAcaraRows($competition, $this->school_type),
            'tempat' => $this->beritaAcaraTempat,
            'tanggal' => Carbon::parse($this->beritaAcaraTanggal),
            'koordinator' => $this->beritaAcaraKoordinator,
        ])->setPaper('a4', 'portrait');

        $this->stampFooter($pdf);

        $filename = collect([
            'berita_acara',
            Str::slug($competition->name, '_'),
            Str::slug($this->school_type, '_'),
            now()->format('Ymd_His'),
        ])->implode('_').'.pdf';

        $this->closeBeritaAcara();

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename
        );
    }

    private function beritaAcaraRows(Competition $competition, string $schoolType): Collection
    {
        $registrations = $competition->registrations()
            ->where('school_type', $schoolType)
            ->withSum('evaluations as raw_score', 'score')
            ->get()
            ->each(function (Registration $reg) {
                $reg->raw_score = (int) ($reg->raw_score ?? 0);
                $reg->final_score = $reg->calculateFinalScore($reg->raw_score);
                $reg->total_score = $reg->final_score;
            });

        $registrations = Registration::sortForRanking($registrations)->take(6)->values();

        return $registrations->map(fn (Registration $registration, int $index) => (object) [
            'no' => $index + 1,
            'npp' => $registration->npp,
            'rank_label' => self::beritaAcaraRankLabel($index + 1),
            'total_score' => $registration->final_score ?? 0,
        ]);
    }

    public function downloadPdf()
    {
        $competition = $this->competition();

        abort_unless($competition, 404);

        $pdf = Pdf::loadView('pdf.recap', [
            'competition' => $competition,
            'rows' => $this->rows($competition),
            'school_type' => $this->school_type,
            'include_notes' => $this->include_notes,
            'logo' => $this->logoDataUri(),
            'maskIdentity' => $competition->hide_identity,
        ])->setPaper('a4', 'landscape');

        $this->stampFooter($pdf);

        $filename = collect([
            Str::slug($competition->name, '_'),
            Str::slug($this->school_type ?: 'semua', '_'),
            now()->format('Ymd_His'),
        ])->implode('_').'.pdf';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename
        );
    }

    /**
     * Menulis footer (kiri: penyelenggara, kanan: waktu cetak + nomor halaman) langsung
     * ke canvas PDF. Nomor halaman total ({PAGE_COUNT}) hanya akurat lewat
     * Canvas::page_text(), karena counter(pages) via CSS tidak didukung dompdf
     * pada elemen fixed.
     */
    private function stampFooter(\Barryvdh\DomPDF\PDF $pdf): void
    {
        $pdf->render();

        $canvas = $pdf->getCanvas();
        $fontMetrics = $pdf->getFontMetrics();
        $font = $fontMetrics->getFont('DejaVu Sans');
        $color = [0.5, 0.5, 0.5];
        $margin = 24;
        $size = 7;

        $left = __(':owner &mdash; :app (:url)', [
            'owner' => 'Bidang Humas dan Informatika Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan',
            'app' => config('app.name'),
            'url' => config('app.url'),
        ]);
        $left = html_entity_decode($left, ENT_QUOTES);

        $printedAt = now()->translatedFormat('d M Y H:i');
        $totalPages = $canvas->get_page_count();

        $right = html_entity_decode(
            __('Dicetak :time &mdash; Halaman {PAGE_NUM} dari {PAGE_COUNT}', ['time' => $printedAt]),
            ENT_QUOTES
        );

        // {PAGE_NUM}/{PAGE_COUNT} baru disubstitusi dompdf saat output; lebar diukur
        // memakai skenario terlebar (nomor halaman = total halaman) agar teks tetap
        // rata kanan meski nanti digit nomor halaman berbeda-beda per halaman.
        $widestRight = html_entity_decode(
            __('Dicetak :time &mdash; Halaman :page dari :total', [
                'time' => $printedAt,
                'page' => $totalPages,
                'total' => $totalPages,
            ]),
            ENT_QUOTES
        );

        $pageWidth = $canvas->get_width();
        $pageHeight = $canvas->get_height();
        $bottomOffset = 20;
        $rightWidth = $fontMetrics->getTextWidth($widestRight, $font, $size);

        $canvas->page_text($margin, $pageHeight - $bottomOffset, $left, $font, $size, $color);
        $canvas->page_text($pageWidth - $margin - $rightWidth, $pageHeight - $bottomOffset, $right, $font, $size, $color);
    }

    public function openTieOrder(string $schoolType, int $totalScore): void
    {
        $this->tieClusterKey = $schoolType.'|'.$totalScore;

        $this->tiePositions = $this->clusterRegistrations($schoolType, $totalScore)
            ->mapWithKeys(fn (Registration $registration, int $index) => [
                $registration->id => $registration->tie_break_order ?? ($index + 1),
            ])
            ->all();
    }

    public function saveTieOrder(): void
    {
        [$schoolType, $totalScore] = $this->parseClusterKey();
        $registrations = $this->clusterRegistrations($schoolType, $totalScore);

        $positions = collect($this->tiePositions)
            ->only($registrations->pluck('id'))
            ->map(fn ($value) => (int) $value);

        $count = $registrations->count();

        if ($positions->contains(fn (int $value) => $value < 1 || $value > $count)) {
            $this->addError('tiePositions', __('Urutan harus antara 1 dan :count.', ['count' => $count]));

            return;
        }

        if ($positions->unique()->count() !== $positions->count()) {
            $this->addError('tiePositions', __('Urutan tidak boleh sama (duplikat) dalam satu kelompok.'));

            return;
        }

        foreach ($positions as $id => $position) {
            Registration::whereKey($id)->update(['tie_break_order' => $position]);
        }

        $this->closeTieOrder();
    }

    public function resetTieOrder(): void
    {
        [$schoolType, $totalScore] = $this->parseClusterKey();

        Registration::whereIn('id', $this->clusterRegistrations($schoolType, $totalScore)->pluck('id'))
            ->update(['tie_break_order' => null]);

        $this->closeTieOrder();
    }

    public function closeTieOrder(): void
    {
        $this->reset(['tieClusterKey', 'tiePositions']);
    }

    public function viewJudgeDetail(string $registrationId): void
    {
        $this->detailRegistrationId = $registrationId;
    }

    public function closeJudgeDetail(): void
    {
        $this->detailRegistrationId = null;
    }

    public function openAdjustment(string $registrationId): void
    {
        $registration = Registration::findOrFail($registrationId);
        $this->adjustingRegistrationId = $registration->id;
        $this->adjustingRegistrationName = $registration->displayName();
        $this->adjustingRegistrationNpp = $registration->npp;
        $this->score_addition = (int) ($registration->score_addition ?? 0);
        $this->score_deduction = (int) ($registration->score_deduction ?? 0);
        $this->adjustment_notes = (string) ($registration->adjustment_notes ?? '');
        $this->showAdjustmentModal = true;
    }

    public function closeAdjustment(): void
    {
        $this->showAdjustmentModal = false;
        $this->reset(['adjustingRegistrationId', 'adjustingRegistrationName', 'adjustingRegistrationNpp', 'score_addition', 'score_deduction', 'adjustment_notes']);
    }

    public function saveAdjustment(): void
    {
        $this->validate([
            'score_addition' => ['required', 'integer', 'min:0'],
            'score_deduction' => ['required', 'integer', 'min:0'],
            'adjustment_notes' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'score_addition' => __('penambahan nilai'),
            'score_deduction' => __('pengurangan nilai'),
            'adjustment_notes' => __('keterangan penyesuaian'),
        ]);

        if ($this->adjustingRegistrationId) {
            Registration::where('id', $this->adjustingRegistrationId)->update([
                'score_addition' => $this->score_addition,
                'score_deduction' => $this->score_deduction,
                'adjustment_notes' => $this->adjustment_notes ?: null,
            ]);
        }

        $this->closeAdjustment();
    }

    /**
     * Rincian juri per kriteria untuk satu peserta: siapa saja juri yang
     * ditugaskan, nilai yang sudah diberikan, dan catatannya (jika ada).
     */
    private function judgeDetail(Competition $competition): ?object
    {
        if (! $this->detailRegistrationId) {
            return null;
        }

        $registration = $competition->registrations()
            ->with(['participant', 'evaluations.items.criterionItem'])
            ->find($this->detailRegistrationId);

        if (! $registration) {
            return null;
        }

        $evaluationsByCriterionJudge = $registration->evaluations
            ->keyBy(fn ($evaluation) => $evaluation->criterion_id.'|'.$evaluation->user_id);

        $criteria = $competition->criteria
            ->filter(fn (Criterion $criterion) => in_array($registration->school_type, $criterion->school_types, true))
            ->sortBy('name')
            ->map(function (Criterion $criterion) use ($registration, $evaluationsByCriterionJudge) {
                $judges = $criterion->judgesFor($registration->school_type)
                    ->orderBy('name')
                    ->get()
                    ->map(function (User $judge) use ($criterion, $evaluationsByCriterionJudge) {
                        $evaluation = $evaluationsByCriterionJudge->get($criterion->id.'|'.$judge->id);

                        return (object) [
                            'judge' => $judge,
                            'score' => $evaluation->score ?? null,
                            'notes' => $evaluation->notes ?? null,
                            'submitted' => (bool) $evaluation,
                            'items' => $evaluation?->items ?? collect(),
                        ];
                    });

                return (object) [
                    'criterion' => $criterion,
                    'judges' => $judges,
                ];
            })
            ->values();

        return (object) [
            'registration' => $registration,
            'criteria' => $criteria,
        ];
    }

    /** @return array{0: string, 1: int} */
    private function parseClusterKey(): array
    {
        [$schoolType, $totalScore] = explode('|', $this->tieClusterKey, 2);

        return [$schoolType, (int) $totalScore];
    }

    private function clusterRegistrations(string $schoolType, int $totalScore): Collection
    {
        return $this->competition()->registrations()
            ->with('participant')
            ->where('school_type', $schoolType)
            ->withSum('evaluations as raw_score', 'score')
            ->get()
            ->each(function (Registration $reg) {
                $reg->raw_score = (int) ($reg->raw_score ?? 0);
                $reg->final_score = $reg->calculateFinalScore($reg->raw_score);
                $reg->total_score = $reg->final_score;
            })
            ->filter(fn (Registration $registration) => (int) ($registration->final_score ?? 0) === $totalScore)
            ->sortBy(fn (Registration $registration) => $registration->tie_break_order ?? PHP_INT_MAX)
            ->values();
    }

    public function render()
    {
        $competition = $this->competition();

        $tieCluster = $this->tieClusterKey
            ? $this->clusterRegistrations(...$this->parseClusterKey())
            : null;

        $categoryCounts = $competition
            ? $competition->registrations()
                ->selectRaw('school_type, count(*) as total')
                ->groupBy('school_type')
                ->pluck('total', 'school_type')
            : collect();

        $totalAllParticipants = $competition ? $competition->registrations()->count() : 0;

        return view('livewire.admin.recap', [
            'competition' => $competition,
            'rows' => $competition ? $this->rows($competition) : collect(),
            'tieCluster' => $tieCluster,
            'judgeDetail' => $competition ? $this->judgeDetail($competition) : null,
            'maskIdentity' => $competition && $competition->hide_identity,
            'categoryCounts' => $categoryCounts,
            'totalAllParticipants' => $totalAllParticipants,
        ]);
    }

    private function competition(): ?Competition
    {
        return Competition::with(['criteria.judges', 'criteria.items'])->find($this->competitionId);
    }

    private function logoDataUri(): ?string
    {
        $path = resource_path('images/logo.png');

        if (! is_file($path)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode(file_get_contents($path));
    }

    private function rows(Competition $competition): Collection
    {
        $registrations = $competition->registrations()
            ->with(['participant', 'evaluations.criterion', 'evaluations.judge', 'evaluations.items.criterionItem'])
            ->when($this->school_type, fn ($query) => $query->where('school_type', $this->school_type))
            ->when(filled($this->search), function ($query) {
                $query->where(function ($q) {
                    $q->where('npp', 'like', '%'.$this->search.'%')
                        ->orWhereHas('participant', fn ($pq) => $pq->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            ->withSum('evaluations as raw_score', 'score')
            ->get()
            ->each(function (Registration $reg) {
                $reg->raw_score = (int) ($reg->raw_score ?? 0);
                $reg->final_score = $reg->calculateFinalScore($reg->raw_score);
                $reg->total_score = $reg->final_score;
            });

        $registrations = Registration::sortForRanking($registrations);

        $clusterSizes = $registrations
            ->groupBy(fn (Registration $registration) => $registration->school_type.'|'.((int) ($registration->final_score ?? 0)))
            ->map->count();

        return $registrations->map(function (Registration $registration) use ($competition, $clusterSizes) {
            $evaluationsByCriterion = $registration->evaluations->groupBy('criterion_id');

            $criteriaScores = $competition->criteria->mapWithKeys(function ($criterion) use ($evaluationsByCriterion, $registration) {
                $applicable = in_array($registration->school_type, $criterion->school_types, true);
                $evaluations = $evaluationsByCriterion->get($criterion->id, collect());
                $expected = $applicable
                    ? $criterion->judges->where('pivot.school_type', $registration->school_type)->count()
                    : 0;

                $notes = $evaluations
                    ->filter(fn ($evaluation) => filled($evaluation->notes))
                    ->map(fn ($evaluation) => [
                        'judge' => $evaluation->judge->name,
                        'text' => $evaluation->notes,
                    ])
                    ->values();

                $subItems = collect();
                if ($criterion->hasItems() && $evaluations->isNotEmpty()) {
                    $subItems = $criterion->items->map(function ($item) use ($evaluations) {
                        $scoreSum = $evaluations->flatMap->items
                            ->where('criterion_item_id', $item->id)
                            ->sum('score');

                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'score' => $scoreSum,
                        ];
                    });
                }

                return [$criterion->id => [
                    'applicable' => $applicable,
                    'score' => $evaluations->sum('score'),
                    'submitted' => $evaluations->count(),
                    'expected' => $expected,
                    'notes' => $notes,
                    'has_items' => $criterion->hasItems(),
                    'sub_items' => $subItems,
                ]];
            });

            $applicableCriteria = $criteriaScores->filter(fn ($score) => $score['applicable']);
            $totalSubmitted = $applicableCriteria->sum('submitted');
            $totalExpected = $applicableCriteria->sum('expected');

            $isComplete = $applicableCriteria->isNotEmpty()
                && $totalExpected > 0
                && $applicableCriteria->every(fn ($score) => $score['expected'] > 0 && $score['submitted'] >= $score['expected']);

            $status = match (true) {
                $isComplete => 'complete',
                $totalSubmitted > 0 => 'partial',
                default => 'unassessed',
            };

            return (object) [
                'registration' => $registration,
                'npp' => $registration->npp,
                'criteria_scores' => $criteriaScores,
                'raw_score' => $registration->raw_score ?? 0,
                'score_addition' => $registration->score_addition ?? 0,
                'score_deduction' => $registration->score_deduction ?? 0,
                'adjustment_notes' => $registration->adjustment_notes,
                'final_score' => $registration->final_score ?? 0,
                'total_score' => $registration->final_score ?? 0,
                'is_complete' => $isComplete,
                'status' => $status,
                'submitted_count' => $totalSubmitted,
                'expected_count' => $totalExpected,
                'is_tied' => $clusterSizes->get($registration->school_type.'|'.((int) ($registration->final_score ?? 0)), 0) > 1,
            ];
        });
    }
}
