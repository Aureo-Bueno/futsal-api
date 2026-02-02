<?php

namespace Database\Seeders;

use App\Models\TeamMatch;
use Illuminate\Database\Seeder;

class TeamMatchSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    TeamMatch::factory()->count(5)->create();
  }
}
