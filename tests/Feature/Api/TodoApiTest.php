<?php

namespace Tests\Feature\Api;

use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class TodoApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_can_create_a_todo(): void
    {
        $todoData = [
            'title' => 'My First Test Todo',
            'assignee' => 'Test User',
            'due_date' => now()->addWeek()->format('Y-m-d'),
            'priority' => 'high',
        ];

        $this->postJson('/api/todos', $todoData)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'My First Test Todo']);

        $this->assertDatabaseHas('todos', [
            'title' => 'My First Test Todo',
            'assignee' => 'Test User',
        ]);
    }

    /**
     * @test
     */
    public function it_returns_a_validation_error_if_title_is_missing(): void
    {
        $this->postJson('/api/todos', ['due_date' => now()->format('Y-m-d')])
            ->assertStatus(422) // 422 Unprocessable Entity
            ->assertJsonValidationErrors('title');
    }

    /**
     * @test
     */
    public function it_can_list_paginated_todos(): void
    {
        // Create 20 todos using the factory
        Todo::factory()->count(20)->create();

        $this->getJson('/api/todos')
            ->assertStatus(200)
            ->assertJsonCount(15, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'assignee', 'status', 'priority']
                ],
                'links',
                'meta',
            ]);
    }

    /**
     * @test
     */
    public function it_can_filter_the_list_of_todos(): void
    {
        Todo::factory()->create(['status' => 'pending', 'priority' => 'high']);
        Todo::factory()->create(['status' => 'completed', 'priority' => 'high']);
        Todo::factory()->create(['status' => 'pending', 'priority' => 'low']);

        $this->getJson('/api/todos?status=pending&priority=high')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data') // Expect only one result
            ->assertJsonFragment(['status' => 'pending', 'priority' => 'high']);
    }

    /**
     * @test
     */
    public function it_can_trigger_an_excel_export(): void
    {
        \Carbon\Carbon::setTestNow(now());
        Excel::fake();
        Todo::factory()->create();
        $this->getJson('/api/todos/export')
            ->assertStatus(200);
        $expectedFilename = 'todos_report_' . now()->format('Y-m-d_H-i') . '.xlsx';
        Excel::assertDownloaded($expectedFilename, function ($export) {
            return true;
        });
        \Carbon\Carbon::setTestNow();
    }

    /**
     * @test
     */
    public function it_returns_a_validation_error_if_due_date_is_in_the_past(): void
    {
        $todoData = [
            'title' => 'A todo with a past due date',
            'due_date' => now()->subDay()->format('Y-m-d'),
        ];
        $response = $this->postJson('/api/todos', $todoData);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('due_date');
    }
}
