<?php

declare(strict_types=1);

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
        Schema::table('team_classification', function (Blueprint $table) {
            $table->index(['deleted_at', 'points', 'number_of_goals'], 'team_classification_standings_idx');
        });

        Schema::table('team_match', function (Blueprint $table) {
            $table->index(['deleted_at', 'date_team_match'], 'team_match_date_idx');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->index('deleted_at', 'teams_deleted_at_idx');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->index('deleted_at', 'players_deleted_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_classification', function (Blueprint $table) {
            $table->dropIndex('team_classification_standings_idx');
        });

        Schema::table('team_match', function (Blueprint $table) {
            $table->dropIndex('team_match_date_idx');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropIndex('teams_deleted_at_idx');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropIndex('players_deleted_at_idx');
        });
    }
};
