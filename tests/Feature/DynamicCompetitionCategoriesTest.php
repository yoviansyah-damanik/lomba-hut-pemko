<?php

namespace Tests\Feature;

use App\Livewire\Admin\Competition as CompetitionComponent;
use App\Livewire\Admin\CompetitionParticipants;
use App\Livewire\Admin\Recap;
use App\Livewire\Evaluation\ParticipantLookup;
use App\Livewire\LiveRank\Board;
use App\Models\Competition;
use App\Models\Criterion;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DynamicCompetitionCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_competition_with_marching_band_preset(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(CompetitionComponent::class)
            ->set('name', 'Lomba Drumband / Marching Band')
            ->set('start_date', now()->format('Y-m-d'))
            ->set('start_hour', '08')
            ->set('start_minute', '00')
            ->set('end_date', now()->addHours(5)->format('Y-m-d'))
            ->set('end_hour', '17')
            ->set('end_minute', '00')
            ->set('categoryPreset', 'marching_band')
            ->set('customCategories', ['Marching Band', 'Drum Band'])
            ->call('save')
            ->assertHasNoErrors();

        $competition = Competition::where('name', 'Lomba Drumband / Marching Band')->first();
        $this->assertNotNull($competition);
        $this->assertSame(['Marching Band', 'Drum Band'], $competition->categories_list);
    }

    public function test_admin_can_create_criterion_with_marching_band_categories_and_assign_judges(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $judge = User::factory()->create(['role' => 'judge', 'name' => 'Juri Musik']);

        $competition = Competition::create([
            'name' => 'Lomba Band Divisi',
            'categories' => ['Marching Band', 'Drum Band'],
            'start_time' => now(),
            'end_time' => now()->addHours(4),
        ]);

        Livewire::actingAs($admin)
            ->test(CompetitionComponent::class)
            ->set('expandedId', $competition->id)
            ->call('createCriterion')
            ->set('criterion_name', 'Music Performance')
            ->set('criterion_min_score', 0)
            ->set('criterion_max_score', 100)
            ->set('criterionSchoolTypes', ['Marching Band', 'Drum Band'])
            ->set('judgeIdsBySchoolType', [
                'Marching Band' => [$judge->id],
                'Drum Band' => [$judge->id],
            ])
            ->call('saveCriterion')
            ->assertHasNoErrors();

        $criterion = Criterion::where('competition_id', $competition->id)->first();
        $this->assertNotNull($criterion);
        $this->assertSame(['Marching Band', 'Drum Band'], $criterion->school_types);
        $this->assertCount(2, $criterion->judges);
    }

    public function test_admin_can_generate_participant_slots_for_marching_band_divisions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $competition = Competition::create([
            'name' => 'Lomba Drumband Test',
            'categories' => ['Marching Band', 'Drum Band'],
            'start_time' => now(),
            'end_time' => now()->addHours(4),
        ]);

        Livewire::actingAs($admin)
            ->test(CompetitionParticipants::class, ['competitionId' => $competition->id])
            ->set('slotCounts', [
                'Marching Band' => 3,
                'Drum Band' => 2,
            ])
            ->set('nppDigits', 2)
            ->call('generateSlots')
            ->assertHasNoErrors();

        $this->assertSame(3, $competition->registrations()->where('school_type', 'Marching Band')->count());
        $this->assertSame(2, $competition->registrations()->where('school_type', 'Drum Band')->count());
    }

    public function test_live_rank_board_displays_dynamic_categories_tabs(): void
    {
        $competition = Competition::create([
            'name' => 'Lomba Marching Band Live',
            'categories' => ['Marching Band', 'Drum Band'],
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(4),
            'rank_open' => true,
        ]);

        $participant = Participant::create([
            'school_name' => 'MB Gita Bahana',
            'school_type' => 'Marching Band',
        ]);

        Registration::create([
            'competition_id' => $competition->id,
            'participant_id' => $participant->id,
            'school_type' => 'Marching Band',
            'npp' => 'MB-01',
        ]);

        Livewire::test(Board::class, ['competitionId' => $competition->id])
            ->assertSee('Marching Band')
            ->assertSee('Drum Band')
            ->assertSet('school_type', 'Marching Band');
    }

    public function test_judge_participant_lookup_searches_by_competition_dynamic_categories(): void
    {
        $judge = User::factory()->create(['role' => 'judge']);
        $competition = Competition::create([
            'name' => 'Lomba Marching Band Penilaian',
            'categories' => ['Marching Band', 'Drum Band'],
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(4),
        ]);

        $criterion = Criterion::create([
            'competition_id' => $competition->id,
            'name' => 'Effect',
            'school_types' => ['Marching Band', 'Drum Band'],
            'min_score' => 0,
            'max_score' => 100,
        ]);

        $criterion->judges()->attach($judge->id, ['school_type' => 'Marching Band']);

        $participant = Participant::create([
            'school_name' => 'MB Surya Kencana',
            'school_type' => 'Marching Band',
        ]);

        $reg = Registration::create([
            'competition_id' => $competition->id,
            'participant_id' => $participant->id,
            'school_type' => 'Marching Band',
            'npp' => '01',
        ]);

        Livewire::actingAs($judge)
            ->test(ParticipantLookup::class)
            ->set('competitionId', $competition->id)
            ->set('school_type', 'Marching Band')
            ->set('npp', '01')
            ->call('search')
            ->assertHasNoErrors()
            ->assertSet('registration.id', $reg->id);
    }
}
