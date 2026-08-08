<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_assignment_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();
            $table->enum('department', [
                'finance',
                'hr',
                'it',
                'operation'
            ])->nullable();
            $table->unsignedSmallInteger('minimum_experience')
                ->default(0);
            $table->string('location')
                ->nullable();
            $table->unsignedTinyInteger('maximum_active_tasks')
                ->default(5);
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->index(
                [
                    'department',
                    'location',
                    'minimum_experience',
                    'maximum_active_tasks'
                ],
                'idx_task_rule_engine'
            );
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_assignment_rules');
    }
};
