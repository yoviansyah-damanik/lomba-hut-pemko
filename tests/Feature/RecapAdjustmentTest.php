<?php

namespace Tests\Feature;

use App\Livewire\Admin\Recap;
use App\Models\Competition;
use App\Models\Criterion;
use App\Models\Evaluation;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RecapAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_adjust_registration_score_addition_and_deduction(): void
    {
        $admin = User::factory()->admin()->create();
        $judge = User::factory()->create();

        $competition = Competition::create([
            'name' => 'Lomba Cerdas Cermat',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
        ]);

        $criterion = Criterion::create([
            'competition_id' => $competition->id,
            'name' => 'Ketepatan Menjawab',
            'school_types' => ['SD'],
            'min_score' => 50,
            'max_score' => 100,
        ]);

        $criterion->judges()->attach($judge->id, ['school_type' => 'SD']);

        $participantA = Participant::create(['school_name' => 'SD Negeri 1', 'school_type' => 'SD']);
        $participantB = Participant::create(['school_name' => 'SD Negeri 2', 'school_type' => 'SD']);

        $regA = Registration::create([
            'competition_id' => $competition->id,
            'participant_id' => $participantA->id,
            'school_type' => 'SD',
            'npp' => 'SD-01',
        ]);

        $regB = Registration::create([
            'competition_id' => $competition->id,
            'participant_id' => $participantB->id,
            'school_type' => 'SD',
            'npp' => 'SD-02',
        ]);

        // Juri memberikan nilai: Reg A = 80, Reg B = 85
        Evaluation::create([
            'user_id' => $judge->id,
            'registration_id' => $regA->id,
            'criterion_id' => $criterion->id,
            'score' => 80,
        ]);

        Evaluation::create([
            'user_id' => $judge->id,
            'registration_id' => $regB->id,
            'criterion_id' => $criterion->id,
            'score' => 85,
        ]);

        // Berikan Reg A bonus 10 sehingga nilai akhirnya 90 (mengalahkan Reg B yang bernilai 85)
        Livewire::actingAs($admin)
            ->test(Recap::class, ['competitionId' => $competition->id])
            ->call('openAdjustment', $regA->id)
            ->set('score_addition', 10)
            ->set('score_deduction', 0)
            ->set('adjustment_notes', 'Bonus kedisiplinan')
            ->call('saveAdjustment')
            ->assertHasNoErrors();

        $regA->refresh();
        $this->assertEquals(10, $regA->score_addition);
        $this->assertEquals(0, $regA->score_deduction);
        $this->assertEquals('Bonus kedisiplinan', $regA->adjustment_notes);
        $this->assertEquals(90, $regA->calculateFinalScore(80));

        // Verifikasi ranking di Recap: Reg A menjadi peringkat 1 dengan nilai akhir 90
        $testRecap = Livewire::actingAs($admin)
            ->test(Recap::class, ['competitionId' => $competition->id]);

        $rows = $testRecap->viewData('rows');
        $this->assertEquals($regA->id, $rows->first()->registration->id);
        $this->assertEquals(90, $rows->first()->final_score);
        $this->assertEquals(80, $rows->first()->raw_score);
        $this->assertEquals(10, $rows->first()->score_addition);
    }

    public function test_berita_acara_displays_final_score_without_breakdown(): void
    {
        $admin = User::factory()->admin()->create();
        $judge = User::factory()->create();

        $competition = Competition::create([
            'name' => 'Lomba Cerdas Cermat',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
        ]);

        $criterion = Criterion::create([
            'competition_id' => $competition->id,
            'name' => 'Ketepatan Menjawab',
            'school_types' => ['SD'],
            'min_score' => 10,
            'max_score' => 100,
        ]);

        $criterion->judges()->attach($judge->id, ['school_type' => 'SD']);

        $participant = Participant::create(['school_name' => 'SD Teladan', 'school_type' => 'SD']);
        $reg = Registration::create([
            'competition_id' => $competition->id,
            'participant_id' => $participant->id,
            'school_type' => 'SD',
            'npp' => 'SD-99',
            'score_addition' => 5,
            'score_deduction' => 2,
        ]);

        Evaluation::create([
            'user_id' => $judge->id,
            'registration_id' => $reg->id,
            'criterion_id' => $criterion->id,
            'score' => 80,
        ]);

        // Unduh berita acara
        $response = Livewire::actingAs($admin)
            ->test(Recap::class, ['competitionId' => $competition->id])
            ->set('school_type', 'SD')
            ->set('beritaAcaraTempat', 'Jakarta')
            ->set('beritaAcaraTanggal', now()->format('Y-m-d'))
            ->set('beritaAcaraKoordinator', 'Budi Santoso')
            ->call('downloadBeritaAcara');

        $response->assertFileDownloaded();

        // Render view langsung untuk memeriksa konten HTML berita acara
        $recapComponent = new Recap();
        $recapComponent->competitionId = $competition->id;
        $recapComponent->school_type = 'SD';

        $method = new \ReflectionMethod(Recap::class, 'beritaAcaraRows');
        $method->setAccessible(true);
        $rows = $method->invoke($recapComponent, $competition, 'SD');

        $html = view('pdf.berita-acara', [
            'competition' => $competition,
            'schoolType' => 'SD',
            'rows' => $rows,
            'tempat' => 'Jakarta',
            'tanggal' => now(),
            'koordinator' => 'Budi Santoso',
        ])->render();

        // Harus menampilkan Nilai Akhir (83.00), tanpa breakdown murni/tambah/kurang
        $this->assertStringContainsString('Nilai Akhir', $html);
        $this->assertStringContainsString('83.00', $html);
        $this->assertStringNotContainsString('Nilai Murni', $html);
        $this->assertStringNotContainsString('Bonus', $html);
        $this->assertStringNotContainsString('Penambahan', $html);
        $this->assertStringNotContainsString('Pengurangan', $html);
    }

    public function test_pdf_recap_displays_only_final_score_column(): void
    {
        $admin = User::factory()->admin()->create();
        $judge = User::factory()->create();

        $competition = Competition::create([
            'name' => 'Lomba Cerdas Cermat',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
        ]);

        $criterion = Criterion::create([
            'competition_id' => $competition->id,
            'name' => 'Ketepatan Menjawab',
            'school_types' => ['SD'],
            'min_score' => 10,
            'max_score' => 100,
        ]);

        $criterion->judges()->attach($judge->id, ['school_type' => 'SD']);

        $participant = Participant::create(['school_name' => 'SD Teladan', 'school_type' => 'SD']);
        $reg = Registration::create([
            'competition_id' => $competition->id,
            'participant_id' => $participant->id,
            'school_type' => 'SD',
            'npp' => 'SD-99',
            'score_addition' => 5,
            'score_deduction' => 2,
        ]);

        Evaluation::create([
            'user_id' => $judge->id,
            'registration_id' => $reg->id,
            'criterion_id' => $criterion->id,
            'score' => 80,
        ]);

        $recapComponent = new Recap();
        $recapComponent->competitionId = $competition->id;
        $recapComponent->school_type = 'SD';

        $method = new \ReflectionMethod(Recap::class, 'rows');
        $method->setAccessible(true);
        $rows = $method->invoke($recapComponent, $competition);

        $html = view('pdf.recap', [
            'competition' => $competition,
            'rows' => $rows,
            'school_type' => 'SD',
            'include_notes' => false,
            'logo' => null,
            'maskIdentity' => false,
        ])->render();

        // Memastikan kolom kriteria dan Akhir ada, sedangkan Murni, (+), (-) tidak ada
        $this->assertStringContainsString('Ketepatan Menjawab', $html);
        $this->assertStringContainsString('Akhir', $html);
        $this->assertStringContainsString('83', $html);
        $this->assertStringNotContainsString('>Murni<', $html);
        $this->assertStringNotContainsString('>(+)<', $html);
        $this->assertStringNotContainsString('>(-)<', $html);
    }

    public function test_recap_displays_accurate_status_badges_for_all_states(): void
    {
        $admin = User::factory()->admin()->create();
        $judge1 = User::factory()->create();
        $judge2 = User::factory()->create();

        $competition = Competition::create([
            'name' => 'Lomba Status Test',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
        ]);

        $criterion = Criterion::create([
            'competition_id' => $competition->id,
            'name' => 'Kerapian',
            'school_types' => ['SD'],
            'min_score' => 10,
            'max_score' => 100,
        ]);

        $criterion->judges()->attach($judge1->id, ['school_type' => 'SD']);
        $criterion->judges()->attach($judge2->id, ['school_type' => 'SD']);

        $participantA = Participant::create(['school_name' => 'Peserta Lengkap', 'school_type' => 'SD']);
        $participantB = Participant::create(['school_name' => 'Peserta Sebagian', 'school_type' => 'SD']);
        $participantC = Participant::create(['school_name' => 'Peserta Kosong', 'school_type' => 'SD']);

        $regA = Registration::create([
            'competition_id' => $competition->id,
            'participant_id' => $participantA->id,
            'school_type' => 'SD',
            'npp' => 'SD-A',
        ]);
        $regB = Registration::create([
            'competition_id' => $competition->id,
            'participant_id' => $participantB->id,
            'school_type' => 'SD',
            'npp' => 'SD-B',
        ]);
        $regC = Registration::create([
            'competition_id' => $competition->id,
            'participant_id' => $participantC->id,
            'school_type' => 'SD',
            'npp' => 'SD-C',
        ]);

        // Reg A dinilai oleh judge1 dan judge2 -> lengkap
        Evaluation::create(['user_id' => $judge1->id, 'registration_id' => $regA->id, 'criterion_id' => $criterion->id, 'score' => 80]);
        Evaluation::create(['user_id' => $judge2->id, 'registration_id' => $regA->id, 'criterion_id' => $criterion->id, 'score' => 85]);

        // Reg B dinilai hanya oleh judge1 -> belum lengkap (partial)
        Evaluation::create(['user_id' => $judge1->id, 'registration_id' => $regB->id, 'criterion_id' => $criterion->id, 'score' => 75]);

        // Reg C belum dinilai sama sekali -> belum dinilai (unassessed)

        $test = Livewire::actingAs($admin)
            ->test(Recap::class, ['competitionId' => $competition->id]);

        $rows = $test->viewData('rows')->keyBy(fn ($r) => $r->registration->id);

        $this->assertSame('complete', $rows[$regA->id]->status);
        $this->assertTrue($rows[$regA->id]->is_complete);

        $this->assertSame('partial', $rows[$regB->id]->status);
        $this->assertFalse($rows[$regB->id]->is_complete);

        $this->assertSame('unassessed', $rows[$regC->id]->status);
        $this->assertFalse($rows[$regC->id]->is_complete);

        // Verifikasi badge di HTML yang di-render
        $test->assertSee('Lengkap')
            ->assertSee('Belum lengkap')
            ->assertSee('Belum dinilai');
    }
}
