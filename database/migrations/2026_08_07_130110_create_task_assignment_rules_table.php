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
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('rule_attribute');
            $table->string('rule_operator');
            $table->string('rule_value');
            
            $table->timestamps();
            $table->unique(
                ['task_id', 'rule_attribute'],
                'uq_task_rule_attribute'
            );
            $table->index('task_id', 'idx_task_rules_task_id');
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
