<?php

namespace Tests\Feature;

use App\Livewire\Admin\Competition as CompetitionComponent;
use App\Livewire\Evaluation\CriterionForm;
use App\Models\Competition;
use App\Models\Criterion;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CriterionScoreRangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_criterion_with_min_and_max_score(): void
    {
        $admin = User::factory()->admin()->create();
        $judge = User::factory()->create();
        $competition = Competition::create([
            'name' => 'Lomba Test',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
        ]);

        Livewire::actingAs($admin)
            ->test(CompetitionComponent::class)
            ->call('toggleCriteria', $competition->id)
            ->call('createCriterion')
            ->set('criterion_name', 'Tata Busana')
            ->set('criterion_description', 'Kerapian seragam')
            ->set('criterion_min_score', 10)
            ->set('criterion_max_score', 50)
            ->set('criterionSchoolTypes', ['SD', 'SMP'])
            ->set('judgeIdsBySchoolType', [
                'SD' => [$judge->id],
                'SMP' => [$judge->id],
                'SMA' => [],
            ])
            ->call('saveCriterion')
            ->assertHasNoErrors();

        $criterion = Criterion::where('name', 'Tata Busana')->first();
        $this->assertNotNull($criterion);
        $this->assertSame(10, $criterion->min_score);
        $this->assertSame(50, $criterion->max_score);
    }

    public function test_validation_fails_when_max_score_is_less_than_or_equal_to_min_score(): void
    {
        $admin = User::factory()->admin()->create();
        $judge = User::factory()->create();
        $competition = Competition::create([
            'name' => 'Lomba Test',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
        ]);

        Livewire::actingAs($admin)
            ->test(CompetitionComponent::class)
            ->call('toggleCriteria', $competition->id)
            ->call('createCriterion')
            ->set('criterion_name', 'Tata Busana')
            ->set('criterion_min_score', 50)
            ->set('criterion_max_score', 40)
            ->set('criterionSchoolTypes', ['SD'])
            ->set('judgeIdsBySchoolType', [
                'SD' => [$judge->id],
                'SMP' => [],
                'SMA' => [],
            ])
            ->call('saveCriterion')
            ->assertHasErrors(['criterion_max_score']);
    }

    public function test_judge_evaluation_enforces_criterion_min_and_max_score(): void
    {
        $judge = User::factory()->create();
        $competition = Competition::create([
            'name' => 'Lomba Test',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
        ]);

        $criterion = Criterion::create([
            'competition_id' => $competition->id,
            'name' => 'Ketertiban',
            'school_types' => ['SD'],
            'min_score' => 20,
            'max_score' => 80,
        ]);

        $criterion->judges()->attach($judge->id, ['school_type' => 'SD']);

        $registration = Registration::create([
            'competition_id' => $competition->id,
            'school_type' => 'SD',
            'npp' => 'SD-01',
            'label' => 'SD 01',
        ]);

        // Nilai di bawah minimal harus error
        Livewire::actingAs($judge)
            ->test(CriterionForm::class, ['registration' => $registration, 'competitionId' => $competition->id])
            ->set("scores.{$criterion->id}", 15)
            ->call('review')
            ->assertHasErrors(["scores.{$criterion->id}"]);

        // Nilai di atas maksimal harus error
        Livewire::actingAs($judge)
            ->test(CriterionForm::class, ['registration' => $registration, 'competitionId' => $competition->id])
            ->set("scores.{$criterion->id}", 90)
            ->call('review')
            ->assertHasErrors(["scores.{$criterion->id}"]);

        // Nilai valid dalam rentang
        Livewire::actingAs($judge)
            ->test(CriterionForm::class, ['registration' => $registration, 'competitionId' => $competition->id])
            ->set("scores.{$criterion->id}", 50)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('evaluations', [
            'user_id' => $judge->id,
            'registration_id' => $registration->id,
            'criterion_id' => $criterion->id,
            'score' => 50,
        ]);
    }

    public function test_admin_and_judge_can_handle_umum_school_type(): void
    {
        $admin = User::factory()->admin()->create();
        $judge = User::factory()->create();
        $competition = Competition::create([
            'name' => 'Lomba HUT Pemko',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
        ]);

        Livewire::actingAs($admin)
            ->test(CompetitionComponent::class)
            ->call('toggleCriteria', $competition->id)
            ->call('createCriterion')
            ->set('criterion_name', 'Atraksi Umum')
            ->set('criterion_description', 'Kreativitas regu umum')
            ->set('criterion_min_score', 0)
            ->set('criterion_max_score', 100)
            ->set('criterionSchoolTypes', ['Umum'])
            ->set('judgeIdsBySchoolType', [
                'SD' => [],
                'SMP' => [],
                'SMA' => [],
                'Umum' => [$judge->id],
            ])
            ->call('saveCriterion')
            ->assertHasNoErrors();

        $criterion = Criterion::where('name', 'Atraksi Umum')->first();
        $this->assertNotNull($criterion);
        $this->assertContains('Umum', $criterion->school_types);

        $registration = Registration::create([
            'competition_id' => $competition->id,
            'school_type' => 'Umum',
            'npp' => 'UM-01',
            'label' => 'Kecamatan Padangsidimpuan Utara',
        ]);

        Livewire::actingAs($judge)
            ->test(CriterionForm::class, ['registration' => $registration, 'competitionId' => $competition->id])
            ->set("scores.{$criterion->id}", 88)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('evaluations', [
            'user_id' => $judge->id,
            'registration_id' => $registration->id,
            'criterion_id' => $criterion->id,
            'score' => 88,
        ]);
    }
}
