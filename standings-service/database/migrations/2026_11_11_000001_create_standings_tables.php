<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_standings', function (Blueprint $table): void {
            $table->uuid('team_id')->primary();
            $table->unsignedInteger('played')->default(0);
            $table->unsignedInteger('won')->default(0);
            $table->unsignedInteger('drawn')->default(0);
            $table->unsignedInteger('lost')->default(0);
            $table->unsignedInteger('goals_for')->default(0);
            $table->unsignedInteger('goals_against')->default(0);
            $table->integer('goal_difference')->default(0);
            $table->unsignedInteger('points')->default(0);
            $table->timestamps();
        });

        Schema::create('match_results', function (Blueprint $table): void {
            $table->uuid('match_id')->primary();
            $table->uuid('home_team_id');
            $table->uuid('away_team_id');
            $table->unsignedInteger('home_score');
            $table->unsignedInteger('away_score');
            $table->timestampTz('processed_at')->useCurrent();
        });

        Schema::create('processed_events', function (Blueprint $table): void {
            $table->string('consumer');
            $table->uuid('event_id');
            $table->timestampTz('processed_at')->useCurrent();

            $table->primary(['consumer', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_events');
        Schema::dropIfExists('match_results');
        Schema::dropIfExists('team_standings');
    }
};
