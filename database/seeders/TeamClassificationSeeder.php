<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\TeamClassification;
use Illuminate\Database\Seeder;

class TeamClassificationSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $teams = Team::all();

    if ($teams->isEmpty()) {
      TeamClassification::factory()->count(8)->create();
      return;
    }

    foreach ($teams as $team) {
      TeamClassification::factory()->create(['team_id' => $team->id]);
    }
  }
}
