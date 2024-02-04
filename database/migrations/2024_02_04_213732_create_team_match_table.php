<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('team_match', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->date('date_team_match');
      $table->string('start_time');
      $table->string('end_time');
      $table->string('scoreboard');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('team_match');
  }
};
