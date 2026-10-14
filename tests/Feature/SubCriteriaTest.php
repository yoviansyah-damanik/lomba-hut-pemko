<?php

namespace Tests\Feature;

use App\Livewire\Admin\Competition as CompetitionComponent;
use App\Livewire\Admin\Recap as AdminRecap;
use App\Livewire\Evaluation\CriterionForm;
use App\Livewire\Evaluation\Recap as EvaluationRecap;
use App\Models\Competition;
use App\Models\Criterion;
use App\Models\CriterionItem;
use App\Models\Evaluation;
use App\Models\EvaluationItem;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SubCriteriaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_criterion_with_sub_items(): void
    {
        $admin = User::factory()->admin()->create();
        $judge = User::factory()->create(['role' => 'judge']);

        $competition = Competition::create([
            'name' => 'Lomba Pidato',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
        ]);

        Livewire::actingAs($admin)
            ->test(CompetitionComponent::class)
            ->call('toggleCriteria', $competition->id)
            ->call('createCriterion')
            ->set('criterion_name', 'Materi & Penampilan')
            ->set('criterion_description', 'Penilaian rubrik beritem')
            ->set('has_sub_items', true)
            ->set('subItems', [
                ['id' => null, 'name' => 'Kerapian Busana', 'min_score' => 10, 'max_score' => 30],
                ['id' => null, 'name' => 'Artikulasi Vokal', 'min_score' => 20, 'max_score' => 40],
                ['id' => null, 'name' => 'Ketepatan Waktu', 'min_score' => 10, 'max_score' => 30],
            ])
            ->set('criterionSchoolTypes', ['SMA'])
            ->set('judgeIdsBySchoolType', [
                'SD' => [],
                'SMP' => [],
                'SMA' => [$judge->id],
                'Umum' => [],
            ])
            ->call('saveCriterion')
            ->assertHasNoErrors();

        $criterion = Criterion::where('name', 'Materi & Penampilan')->first();
        $this->assertNotNull($criterion);
        $this->assertSame(40, $criterion->min_score); // 10 + 20 + 10
        $this->assertSame(100, $criterion->max_score); // 30 + 40 + 30
        $this->assertCount(3, $criterion->items);
        $this->assertSame('Kerapian Busana', $criterion->items[0]->name);
        $this->assertSame('Artikulasi Vokal', $criterion->items[1]->name);
        $this->assertSame('Ketepatan Waktu', $criterion->items[2]->name);
    }

    public function test_judge_evaluates_with_sub_items_and_accumulates_total_score(): void
    {
        $judge = User::factory()->create(['role' => 'judge']);

        $competition = Competition::create([
            'name' => 'Lomba Baca Puisi',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
        ]);

        $criterion = Criterion::create([
            'competition_id' => $competition->id,
            'name' => 'Penjiwaan & Vokal',
            'school_types' => ['SMA'],
            'min_score' => 20,
            'max_score' => 100,
        ]);

        $criterion->judges()->attach($judge->id, ['school_type' => 'SMA']);

        $item1 = CriterionItem::create([
            'criterion_id' => $criterion->id,
            'name' => 'Ekspresi / Penghayatan',
            'min_score' => 10,
            'max_score' => 50,
            'order' => 0,
        ]);

        $item2 = CriterionItem::create([
            'criterion_id' => $criterion->id,
            'name' => 'Intonasi & Diksi',
            'min_score' => 10,
            'max_score' => 50,
            'order' => 1,
        ]);

        $participant = Participant::create([
            'school_name' => 'SMA Negeri 1',
            'school_type' => 'SMA',
        ]);

        $registration = Registration::create([
            'competition_id' => $competition->id,
            'participant_id' => $participant->id,
            'school_type' => 'SMA',
            'npp' => 'SMA-001',
        ]);

        Livewire::actingAs($judge)
            ->test(CriterionForm::class, [
                'registration' => $registration,
                'competitionId' => $competition->id,
            ])
            ->set("itemScores.{$criterion->id}.{$item1->id}", 45)
            ->set("itemScores.{$criterion->id}.{$item2->id}", 40)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('evaluation-saved');

        $evaluation = Evaluation::where('user_id', $judge->id)
            ->where('registration_id', $registration->id)
            ->where('criterion_id', $criterion->id)
            ->first();

        $this->assertNotNull($evaluation);
        $this->assertSame(85, $evaluation->score); // 45 + 40 = 85
        $this->assertCount(2, $evaluation->items);

        $evalItems = $evaluation->items->keyBy('criterion_item_id');
        $this->assertSame(45, $evalItems[$item1->id]->score);
        $this->assertSame(40, $evalItems[$item2->id]->score);
    }

    public function test_sub_item_validation_enforces_min_and_max_scores(): void
    {
        $judge = User::factory()->create(['role' => 'judge']);

        $competition = Competition::create([
            'name' => 'Lomba Debat',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
        ]);

        $criterion = Criterion::create([
            'competition_id' => $competition->id,
            'name' => 'Argumen',
            'school_types' => ['SMA'],
            'min_score' => 10,
            'max_score' => 50,
        ]);

        $criterion->judges()->attach($judge->id, ['school_type' => 'SMA']);

        $item = CriterionItem::create([
            'criterion_id' => $criterion->id,
            'name' => 'Kekuatan Dalil',
            'min_score' => 10,
            'max_score' => 50,
            'order' => 0,
        ]);

        $participant = Participant::create([
            'school_name' => 'SMA Negeri 2',
            'school_type' => 'SMA',
        ]);

        $registration = Registration::create([
            'competition_id' => $competition->id,
            'participant_id' => $participant->id,
            'school_type' => 'SMA',
            'npp' => 'SMA-002',
        ]);

        // Nilai melebihi batas maksimal (60 > 50)
        Livewire::actingAs($judge)
            ->test(CriterionForm::class, [
                'registration' => $registration,
                'competitionId' => $competition->id,
            ])
            ->set("itemScores.{$criterion->id}.{$item->id}", 60)
            ->call('save')
            ->assertHasErrors(["itemScores.{$criterion->id}.{$item->id}"]);
    }

    public function test_database_seeder_creates_criteria_with_sub_items(): void
    {
        $this->seed();

        $kerapian = Criterion::where('name', 'Kerapian')->first();
        $this->assertNotNull($kerapian);
        $this->assertTrue($kerapian->hasItems());
        $this->assertCount(3, $kerapian->items);
        $this->assertSame(100, $kerapian->max_score);

        $kekompakan = Criterion::where('name', 'Kekompakan')->first();
        $this->assertNotNull($kekompakan);
        $this->assertTrue($kekompakan->hasItems());
        $this->assertCount(3, $kekompakan->items);
        $this->assertSame(100, $kekompakan->max_score);

        $kreativitas = Criterion::where('name', 'Kreativitas')->first();
        $this->assertNotNull($kreativitas);
        $this->assertFalse($kreativitas->hasItems());
        $this->assertCount(0, $kreativitas->items);

        // Verifikasi Lomba Drumband / Marching Band
        $drumband = Competition::where('name', 'Lomba Drumband / Marching Band')->first();
        $this->assertNotNull($drumband);
        $this->assertCount(3, $drumband->criteria);

        $music = $drumband->criteria()->where('name', 'Music Performance')->first();
        $this->assertNotNull($music);
        $this->assertSame(100, $music->max_score);
        $this->assertCount(3, $music->items);
        $this->assertSame('Kualitas Suara Keseluruhan', $music->items[0]->name);
        $this->assertSame(40, $music->items[0]->max_score);
        $this->assertSame('Artikulasi dan Teknik & Dinamika', $music->items[1]->name);
        $this->assertSame(40, $music->items[1]->max_score);
        $this->assertSame('Balance and Blend', $music->items[2]->name);
        $this->assertSame(20, $music->items[2]->max_score);

        $visual = $drumband->criteria()->where('name', 'Visual Performance')->first();
        $this->assertNotNull($visual);
        $this->assertSame(100, $visual->max_score);
        $this->assertCount(3, $visual->items);
        $this->assertSame('Keseragaman dan Sinkronisasi Gerak', $visual->items[0]->name);
        $this->assertSame(40, $visual->items[0]->max_score);
        $this->assertSame('Kerapian Formasi & Interval', $visual->items[1]->name);
        $this->assertSame(40, $visual->items[1]->max_score);
        $this->assertSame('Control of Movement', $visual->items[2]->name);
        $this->assertSame(20, $visual->items[2]->max_score);

        $ge = $drumband->criteria()->where('name', 'Overall Impression (GE)')->first();
        $this->assertNotNull($ge);
        $this->assertSame(100, $ge->max_score);
        $this->assertCount(3, $ge->items);
        $this->assertSame('Effect dan Entertainment', $ge->items[0]->name);
        $this->assertSame(40, $ge->items[0]->max_score);
        $this->assertSame('Concept dan Coordination & Kreatifitas', $ge->items[1]->name);
        $this->assertSame(40, $ge->items[1]->max_score);
        $this->assertSame('Kesatuan dan Variasi', $ge->items[2]->name);
        $this->assertSame(20, $ge->items[2]->max_score);

        $this->assertSame(300, $music->max_score + $visual->max_score + $ge->max_score);
        $this->assertGreaterThan(0, $drumband->registrations()->count());
    }

    public function test_evaluation_recap_displays_sub_items_breakdown_and_total_accumulation(): void
    {
        $judge = User::factory()->create(['role' => 'judge']);

        $competition = Competition::create([
            'name' => 'Lomba Tartil',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
        ]);

        $criterion = Criterion::create([
            'competition_id' => $competition->id,
            'name' => 'Tajwid & Makhraj',
            'school_types' => ['SD'],
            'min_score' => 20,
            'max_score' => 100,
        ]);

        $criterion->judges()->attach($judge->id, ['school_type' => 'SD']);

        $item1 = CriterionItem::create([
            'criterion_id' => $criterion->id,
            'name' => 'Hukum Tajwid',
            'min_score' => 10,
            'max_score' => 50,
            'order' => 0,
        ]);

        $item2 = CriterionItem::create([
            'criterion_id' => $criterion->id,
            'name' => 'Fashahah',
            'min_score' => 10,
            'max_score' => 50,
            'order' => 1,
        ]);

        $participant = Participant::create([
            'school_name' => 'SDIT Al-Ihsan',
            'school_type' => 'SD',
        ]);

        $registration = Registration::create([
            'competition_id' => $competition->id,
            'participant_id' => $participant->id,
            'school_type' => 'SD',
            'npp' => 'SD-101',
        ]);

        $evaluation = Evaluation::create([
            'user_id' => $judge->id,
            'registration_id' => $registration->id,
            'criterion_id' => $criterion->id,
            'score' => 88, // 46 + 42
        ]);

        EvaluationItem::create([
            'evaluation_id' => $evaluation->id,
            'criterion_item_id' => $item1->id,
            'score' => 46,
        ]);

        EvaluationItem::create([
            'evaluation_id' => $evaluation->id,
            'criterion_item_id' => $item2->id,
            'score' => 42,
        ]);

        Livewire::actingAs($judge)
            ->test(EvaluationRecap::class)
            ->set('competitionId', (string) $competition->id)
            ->assertSee('Tajwid & Makhraj')
            ->assertSee('Hukum Tajwid')
            ->assertSee('46')
            ->assertSee('Fashahah')
            ->assertSee('42')
            ->assertSee('88');
    }

    public function test_berita_acara_and_pdf_recap_show_only_final_score_without_sub_items(): void
    {
        $admin = User::factory()->admin()->create();
        $judge = User::factory()->create(['role' => 'judge']);

        $competition = Competition::create([
            'name' => 'Lomba Cerdas Cermat',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
        ]);

        $criterion = Criterion::create([
            'competition_id' => $competition->id,
            'name' => 'Materi Inti',
            'school_types' => ['SMP'],
            'min_score' => 20,
            'max_score' => 100,
        ]);

        $criterion->judges()->attach($judge->id, ['school_type' => 'SMP']);

        $item1 = CriterionItem::create([
            'criterion_id' => $criterion->id,
            'name' => 'Babak Wajib',
            'min_score' => 10,
            'max_score' => 50,
            'order' => 0,
        ]);

        $participant = Participant::create([
            'school_name' => 'SMP Negeri 1',
            'school_type' => 'SMP',
        ]);

        $registration = Registration::create([
            'competition_id' => $competition->id,
            'participant_id' => $participant->id,
            'school_type' => 'SMP',
            'npp' => 'SMP-001',
        ]);

        $evaluation = Evaluation::create([
            'user_id' => $judge->id,
            'registration_id' => $registration->id,
            'criterion_id' => $criterion->id,
            'score' => 45,
        ]);

        EvaluationItem::create([
            'evaluation_id' => $evaluation->id,
            'criterion_item_id' => $item1->id,
            'score' => 45,
        ]);

        // Verifikasi pada cetak Berita Acara
        $beritaAcaraView = view('pdf.berita-acara', [
            'competition' => $competition,
            'schoolType' => 'SMP',
            'rows' => collect([(object) [
                'no' => 1,
                'npp' => 'SMP-001',
                'rank_label' => 'JUARA I',
                'total_score' => 45,
            ]]),
            'tempat' => 'Aula Pemko',
            'tanggal' => now(),
            'koordinator' => 'Koordinator Lomba',
            'logo' => null,
            'maskIdentity' => false,
        ])->render();

        // Hanya memuat nilai akhir dan label juara, tidak memuat nama sub-item
        $this->assertStringContainsString('JUARA I', $beritaAcaraView);
        $this->assertStringContainsString('45', $beritaAcaraView);
        $this->assertStringNotContainsString('Babak Wajib', $beritaAcaraView);

        // Verifikasi pada PDF Rekap
        $pdfRecapView = view('pdf.recap', [
            'competition' => $competition,
            'rows' => collect([(object) [
                'registration' => $registration,
                'npp' => 'SMP-001',
                'criteria_scores' => collect([
                    $criterion->id => [
                        'applicable' => true,
                        'score' => 45,
                        'expected' => 1,
                        'submitted' => 1,
                        'notes' => collect(),
                    ],
                ]),
                'raw_score' => 45,
                'score_addition' => 0,
                'score_deduction' => 0,
                'adjustment_notes' => null,
                'final_score' => 45,
            ]]),
            'school_type' => 'SMP',
            'include_notes' => false,
            'logo' => null,
            'maskIdentity' => false,
        ])->render();

        $this->assertStringContainsString('45', $pdfRecapView);
        $this->assertStringNotContainsString('Babak Wajib', $pdfRecapView);
    }

    public function test_admin_recap_displays_sub_items_breakdown_and_total_accumulation(): void
    {
        $admin = User::factory()->admin()->create();
        $judge = User::factory()->create(['role' => 'judge']);

        $competition = Competition::create([
            'name' => 'Lomba Nasyid',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
        ]);

        $criterion = Criterion::create([
            'competition_id' => $competition->id,
            'name' => 'Kekompakan & Harmonisasi',
            'school_types' => ['SMA'],
            'min_score' => 20,
            'max_score' => 100,
        ]);

        $criterion->judges()->attach($judge->id, ['school_type' => 'SMA']);

        $item1 = CriterionItem::create([
            'criterion_id' => $criterion->id,
            'name' => 'Harmonisasi Suara',
            'min_score' => 10,
            'max_score' => 50,
            'order' => 0,
        ]);

        $item2 = CriterionItem::create([
            'criterion_id' => $criterion->id,
            'name' => 'Koreografi',
            'min_score' => 10,
            'max_score' => 50,
            'order' => 1,
        ]);

        $participant = Participant::create([
            'school_name' => 'SMA Negeri 5',
            'school_type' => 'SMA',
        ]);

        $registration = Registration::create([
            'competition_id' => $competition->id,
            'participant_id' => $participant->id,
            'school_type' => 'SMA',
            'npp' => 'SMA-501',
        ]);

        $evaluation = Evaluation::create([
            'user_id' => $judge->id,
            'registration_id' => $registration->id,
            'criterion_id' => $criterion->id,
            'score' => 92, // 48 + 44
        ]);

        EvaluationItem::create([
            'evaluation_id' => $evaluation->id,
            'criterion_item_id' => $item1->id,
            'score' => 48,
        ]);

        EvaluationItem::create([
            'evaluation_id' => $evaluation->id,
            'criterion_item_id' => $item2->id,
            'score' => 44,
        ]);

        Livewire::actingAs($admin)
            ->test(AdminRecap::class, ['competitionId' => (string) $competition->id])
            ->assertSee('Kekompakan & Harmonisasi')
            ->assertSee('Harmonisasi Suara')
            ->assertSee('48')
            ->assertSee('Koreografi')
            ->assertSee('44')
            ->assertSee('92');
    }
}
