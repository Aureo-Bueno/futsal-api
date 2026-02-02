<?php

namespace Database\Seeders;

use App\Models\Players;
use App\Models\Team;
use Illuminate\Database\Seeder;

class PlayersSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $teams = Team::all();

    if ($teams->isEmpty()) {
      Players::factory()->count(10)->create();
      return;
    }

    foreach ($teams as $team) {
      Players::factory()->count(5)->create(['team_id' => $team->id]);
    }
  }
}
