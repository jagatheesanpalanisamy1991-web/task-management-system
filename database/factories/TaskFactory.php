<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Task;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::where('role','admin')->first() ?? User::factory()->create(['role' => 'admin']);
        return [
            'title' => fake()->unique()->sentence(3),
            'description' => fake()->sentence(),
            'status' => 'todo',
            'priority' => 'medium',
            'created_by' => $user->id,
            'assigned_to' => null,
            'assignment_pending' => true,
        ];
    }
}
