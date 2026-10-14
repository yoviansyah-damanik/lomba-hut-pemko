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
}
