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
    Schema::create('team_classification', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->uuid('team_id');
      $table->integer('points');
      $table->integer('number_of_goals');
      $table->timestamps();

      $table->foreign('team_id')->references('id')->on('teams');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('team_classification');
  }
};
