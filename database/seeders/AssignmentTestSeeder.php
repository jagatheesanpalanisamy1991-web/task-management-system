<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AssignmentTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating 10,000 users...');

        $password = Hash::make('password');

        for ($batch = 1; $batch <= 10; $batch++) {
            $users = [];
            for ($i = 1; $i <= 1000; $i++) {
                $users[] = [
                    'name' => 'User ' . (($batch - 1) * 1000 + $i),
                    'email' => 'user' . (($batch - 1) * 1000 + $i) . '@example.com',
                    'password' => $password,
                    'role' => 'user',
                    'department' => fake()->randomElement(['finance', 'hr', 'it', 'operation']),
                    'years_experience' => fake()->numberBetween(1, 10),
                    'location' => fake()->randomElement(['Chennai', 'Coimbatore', 'Bangalore', 'Hyderabad']),
                ];
            }
            //User::insert($users);
            DB::table('users')->upsert(
                $users,
                ['email'],
                [
                    'name',
                    'department',
                    'years_experience',
                    'location',
                    'updated_at',
                ]
            );

        }

        $this->command->info('Users created.');

        $this->command->info('Creating 10,000 tasks...');

        Task::factory()
            ->count(10000)
            ->create();
        
        for($task_batch = 1; $task_batch <= 10; $task_batch++) {
            $tasks = [];
            for ($i = 1; $i <= 1000; $i++) {
                $tasks[] = [
                    'title' => 'Performance Task ' . (($task_batch - 1) * 1000 + $i).now(),
                    'description' => fake()->sentence(),
                    'status' => 'todo',
                    'priority' => 'medium',
                    'created_by' => User::where('role', 'admin')->first()->id,
                    'assigned_to' => null,
                    'assignment_pending' => true,
                ];
            }
            DB::table('tasks')->upsert(
                $tasks,
                ['title'],
                [
                    'description',
                    'status',
                    'priority',
                    'created_by',
                    'assignment_pending',
                    'updated_at',
                ]
            );
        }

        $this->command->info('Tasks created.');

        $this->command->info('Creating task assignment rules...');

        Task::chunkById(500, function ($tasks) {
            foreach ($tasks as $task) {
                $rules = [
                    [
                        'rule_attribute' => 'department',
                        'rule_operator' => '=',
                        'rule_value' => fake()->randomElement([
                            'finance',
                            'hr',
                            'it',
                            'operation',
                        ]),
                    ],
                    [
                        'rule_attribute' => 'minimum_experience',
                        'rule_operator' => '>=',
                        'rule_value' => fake()->numberBetween(1, 10),
                    ],
                    [
                        'rule_attribute' => 'location',
                        'rule_operator' => '=',
                        'rule_value' => fake()->randomElement([
                            'Chennai',
                            'Coimbatore',
                            'Bangalore',
                            'Hyderabad',
                        ]),
                    ],
                ];

                foreach ($rules as $rule) {
                    $task->taskRules()->create([
                        ...$rule,
                    ]);
                }
            }
        });

        $this->command->info('Task assignment rules created.');
        $this->command->info('Assignment test data completed.');
        
    }
}
