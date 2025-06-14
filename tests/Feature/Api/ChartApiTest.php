<?php

namespace Tests\Feature\Api;

use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_returns_status_summary_chart_data(): void
    {
        Todo::factory()->create(['status' => 'pending']);
        Todo::factory()->create(['status' => 'pending']);
        Todo::factory()->create(['status' => 'completed']);

        $this->getJson('/api/chart?type=status')
            ->assertStatus(200)
            ->assertJson([
                'status_summary' => [
                    'pending' => 2,
                    'completed' => 1,
                    'open' => 0,
                    'in_progress' => 0,
                ],
            ]);
    }

    /**
     * @test
     */
    public function it_returns_priority_summary_chart_data(): void
    {
        Todo::factory()->create(['priority' => 'high']);
        Todo::factory()->create(['priority' => 'high']);
        Todo::factory()->create(['priority' => 'low']);

        $this->getJson('/api/chart?type=priority')
            ->assertStatus(200)
            ->assertJson([
                'priority_summary' => [
                    'high' => 2,
                    'low' => 1,
                    'medium' => 0,
                ],
            ]);
    }

    /**
     * @test
     */
    public function it_returns_assignee_summary_chart_data(): void
    {
        Todo::factory()->create([
            'assignee' => 'Alice',
            'status' => 'pending',
            'time_tracked' => 0
        ]);
        Todo::factory()->create([
            'assignee' => 'Alice',
            'status' => 'completed',
            'time_tracked' => 60
        ]);
        Todo::factory()->create([
            'assignee' => 'Bob',
            'status' => 'pending',
            'time_tracked' => 0
        ]);

        $this->getJson('/api/chart?type=assignee')
            ->assertStatus(200)
            ->assertJson([
                'assignee_summary' => [
                    'Alice' => [
                        'total_todos' => 2,
                        'total_pending_todos' => 1,
                        'total_timetracked_completed_todos' => 60,
                    ],
                    'Bob' => [
                        'total_todos' => 1,
                        'total_pending_todos' => 1,
                        'total_timetracked_completed_todos' => 0,
                    ],
                ]
            ]);
    }

    /**
     * @test
     */
    public function it_returns_a_validation_error_for_invalid_chart_type(): void
    {
        $this->getJson('/api/chart?type=invalid_type')
            ->assertStatus(422)
            ->assertJsonValidationErrors('type');
    }

    /** @test */
    public function assignee_summary_is_empty_when_no_todos_have_assignees(): void
    {
        Todo::factory()->count(5)->create(['assignee' => null]);

        $this->getJson('/api/chart?type=assignee')
            ->assertStatus(200)
            ->assertJson([
                'assignee_summary' => []
            ]);
    }
}
