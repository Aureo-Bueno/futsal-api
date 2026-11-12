<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('home_team_id');
            $table->uuid('away_team_id');
            $table->string('status');
            $table->unsignedInteger('home_score')->default(0);
            $table->unsignedInteger('away_score')->default(0);
            $table->timestampTz('scheduled_at');
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('finished_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
        });

        Schema::create('match_goals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('match_id');
            $table->uuid('team_id');
            $table->uuid('player_id');
            $table->unsignedInteger('minute');
            $table->boolean('own_goal')->default(false);
            $table->timestamps();

            $table->foreign('match_id')->references('id')->on('matches');
            $table->index(['match_id', 'minute']);
        });

        Schema::create('known_teams', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->unsignedInteger('players_count')->default(0);
            $table->timestamps();
        });

        Schema::create('known_players', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('team_id');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['team_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('known_players');
        Schema::dropIfExists('known_teams');
        Schema::dropIfExists('match_goals');
        Schema::dropIfExists('matches');
    }
};
