<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\TeamMatch;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $matches = TeamMatch::all();

    if ($matches->isEmpty()) {
      Team::factory()->count(8)->create();
      return;
    }

    Team::factory()
      ->count(8)
      ->state(fn() => ['team_match_id' => $matches->random()->id])
      ->create();
  }
}
