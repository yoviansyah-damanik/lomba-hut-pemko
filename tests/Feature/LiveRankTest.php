<?php

namespace Tests\Feature;

use App\Livewire\LiveRank\Board;
use App\Models\Competition;
use App\Models\Criterion;
use App\Models\Evaluation;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LiveRankTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_rank_displays_only_top_6_with_juara_and_harapan_labels(): void
    {
        $judge = User::factory()->create();

        $competition = Competition::create([
            'name' => 'Lomba Cerdas Cermat',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
            'rank_open' => true,
        ]);

        $criterion = Criterion::create([
            'competition_id' => $competition->id,
            'name' => 'Nilai Babak Final',
            'school_types' => ['SD'],
            'min_score' => 10,
            'max_score' => 100,
        ]);

        $criterion->judges()->attach($judge->id, ['school_type' => 'SD']);

        // Buat 8 peserta untuk jenjang SD
        for ($i = 1; $i <= 8; $i++) {
            $participant = Participant::create([
                'school_name' => "SD Peserta $i",
                'school_type' => 'SD',
            ]);

            $registration = Registration::create([
                'competition_id' => $competition->id,
                'participant_id' => $participant->id,
                'school_type' => 'SD',
                'npp' => sprintf('SD-%03d', $i),
            ]);

            // Beri nilai berbeda agar ranking terurut (100, 95, 90, ..., 65)
            Evaluation::create([
                'registration_id' => $registration->id,
                'criterion_id' => $criterion->id,
                'user_id' => $judge->id,
                'score' => 105 - ($i * 5),
            ]);
        }

        // Test Livewire Board
        Livewire::test(Board::class, ['competitionId' => $competition->id])
            ->set('school_type', 'SD')
            ->assertOk()
            // Harus memuat Juara 1 - 3
            ->assertSee('Juara 1')
            ->assertSee('Juara 2')
            ->assertSee('Juara 3')
            ->assertSee('SD-001')
            ->assertSee('SD-002')
            ->assertSee('SD-003')
            // Harus memuat Harapan 1 - 3
            ->assertSee('Harapan 1')
            ->assertSee('Harapan 2')
            ->assertSee('Harapan 3')
            ->assertSee('SD-004')
            ->assertSee('SD-005')
            ->assertSee('SD-006')
            // Peserta ke-7 dan ke-8 TIDAK BOLEH tampil di Live Rank
            ->assertDontSee('SD-007')
            ->assertDontSee('SD-008');
    }
}
