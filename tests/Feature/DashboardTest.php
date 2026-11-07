<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\AdminSummary;
use App\Livewire\Dashboard\JudgeProgress;
use App\Models\Competition;
use App\Models\Criterion;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard_and_admin_summary(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin Test',
        ]);

        $competition = Competition::create([
            'name' => 'Lomba Menyanyi',
            'start_time' => now()->subDay(),
            'end_time' => now()->addDay(),
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Admin Test');
        $response->assertSee('Panel Administrator');
        $response->assertSee('Kelola Lomba');

        // Lazy component initially renders placeholder skeleton
        Livewire::actingAs($admin)
            ->test(AdminSummary::class)
            ->assertOk()
            ->assertSee('animate-pulse');
    }

    public function test_judge_can_view_dashboard_and_judge_progress(): void
    {
        $judge = User::factory()->create([
            'role' => 'judge',
            'name' => 'Juri Test',
        ]);

        $competition = Competition::create([
            'name' => 'Lomba Tari',
            'start_time' => now()->subDay(),
            'end_time' => now()->addDay(),
        ]);

        $criterion = Criterion::create([
            'competition_id' => $competition->id,
            'name' => 'Koreografi',
            'school_types' => ['SMP'],
            'min_score' => 50,
            'max_score' => 100,
        ]);

        $criterion->judges()->attach($judge->id, ['school_type' => 'SMP']);

        $participant = Participant::create([
            'school_name' => 'SMP 1',
            'school_type' => 'SMP',
        ]);

        Registration::create([
            'competition_id' => $competition->id,
            'participant_id' => $participant->id,
            'school_type' => 'SMP',
            'npp' => 'SMP-001',
        ]);

        $response = $this->actingAs($judge)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Juri Test');
        $response->assertSee('Panel Dewan Juri');
        $response->assertSee('Mulai Menilai');

        // Lazy component initially renders placeholder skeleton
        Livewire::actingAs($judge)
            ->test(JudgeProgress::class)
            ->assertOk()
            ->assertSee('animate-pulse');
    }

    public function test_admin_summary_judge_progress_calculates_max_based_on_assigned_criterion_and_school_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin']);
        $judge = User::factory()->create(['role' => 'judge', 'name' => 'Juri 1']);

        // Buat kompetisi dengan 3 kriteria
        $competition = Competition::create([
            'name' => 'Lomba Pramuka',
            'start_time' => now()->subDay(),
            'end_time' => now()->addDay(),
        ]);

        $c1 = Criterion::create([
            'competition_id' => $competition->id,
            'name' => 'Kerapian',
            'school_types' => ['SD', 'SMP', 'SMA'],
            'min_score' => 0,
            'max_score' => 100,
        ]);

        $c2 = Criterion::create([
            'competition_id' => $competition->id,
            'name' => 'Kekompakan',
            'school_types' => ['SD', 'SMP', 'SMA'],
            'min_score' => 0,
            'max_score' => 100,
        ]);

        $c3 = Criterion::create([
            'competition_id' => $competition->id,
            'name' => 'Kreativitas',
            'school_types' => ['SD', 'SMP', 'SMA'],
            'min_score' => 0,
            'max_score' => 100,
        ]);

        // Juri hanya ditugaskan pada Kriteria 1 (untuk SD, SMP, SMA)
        $c1->judges()->attach($judge->id, ['school_type' => 'SD']);
        $c1->judges()->attach($judge->id, ['school_type' => 'SMP']);
        $c1->judges()->attach($judge->id, ['school_type' => 'SMA']);

        // Buat total 38 peserta (15 SD, 13 SMP, 10 SMA)
        for ($i = 1; $i <= 15; $i++) {
            $p = Participant::create(['school_name' => "SD $i", 'school_type' => 'SD']);
            Registration::create(['competition_id' => $competition->id, 'participant_id' => $p->id, 'school_type' => 'SD', 'npp' => "SD-$i"]);
        }
        for ($i = 1; $i <= 13; $i++) {
            $p = Participant::create(['school_name' => "SMP $i", 'school_type' => 'SMP']);
            Registration::create(['competition_id' => $competition->id, 'participant_id' => $p->id, 'school_type' => 'SMP', 'npp' => "SMP-$i"]);
        }
        for ($i = 1; $i <= 10; $i++) {
            $p = Participant::create(['school_name' => "SMA $i", 'school_type' => 'SMA']);
            Registration::create(['competition_id' => $competition->id, 'participant_id' => $p->id, 'school_type' => 'SMA', 'npp' => "SMA-$i"]);
        }

        // Render AdminSummary (bukan placeholder skeleton)
        $component = new AdminSummary();
        $view = $component->render();
        $judgeProgress = $view->getData()['judgeProgress'];

        $this->assertCount(1, $judgeProgress);
        $this->assertSame('Juri 1', $judgeProgress[0]['label']);
        // Juri hanya melayani 38 peserta untuk 1 kriteria, bukan 38 * 3 = 114
        $this->assertSame(38, $judgeProgress[0]['max']);
        $this->assertSame(0, $judgeProgress[0]['value']);
    }
}
