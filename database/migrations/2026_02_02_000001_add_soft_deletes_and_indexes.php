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
    Schema::table('players', function (Blueprint $table) {
      $table->softDeletes();
      $table->index('team_id');
    });

    Schema::table('teams', function (Blueprint $table) {
      $table->softDeletes();
      $table->index('team_match_id');
    });

    Schema::table('team_match', function (Blueprint $table) {
      $table->softDeletes();
    });

    Schema::table('team_classification', function (Blueprint $table) {
      $table->softDeletes();
      $table->index('team_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('players', function (Blueprint $table) {
      $table->dropIndex(['team_id']);
      $table->dropSoftDeletes();
    });

    Schema::table('teams', function (Blueprint $table) {
      $table->dropIndex(['team_match_id']);
      $table->dropSoftDeletes();
    });

    Schema::table('team_match', function (Blueprint $table) {
      $table->dropSoftDeletes();
    });

    Schema::table('team_classification', function (Blueprint $table) {
      $table->dropIndex(['team_id']);
      $table->dropSoftDeletes();
    });
  }
};
