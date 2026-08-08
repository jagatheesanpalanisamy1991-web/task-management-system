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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'manager', 'user'])->default('user')->after('email');
            $table->enum('department', ['finance', 'hr', 'it', 'operation'])->nullable()->after('role');
            $table->unsignedSmallInteger('years_experience')->default(0)->after('department');
            $table->string('location')->nullable()->after('years_experience');
            $table->index(['department', 'years_experience'], 'idx_users_rule_matching');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'department', 'years_experience', 'location']);
            $table->dropIndex('idx_users_rule_matching');

        });
    }
};
