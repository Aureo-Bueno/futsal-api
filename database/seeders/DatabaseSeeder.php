<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Players;
use App\Models\Team;
use App\Models\TeamClassification;
use App\Models\TeamMatch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    if (!User::where('email', 'admin@admin.com')->exists()) {
      User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin@admin.com',
        'password' => Hash::make('123456'),
      ]);
    }

    if (User::count() < 5) {
      $this->call(UsersSeeder::class);
    }

    if (TeamMatch::count() === 0) {
      $this->call(TeamMatchSeeder::class);
    }

    if (Team::count() === 0) {
      $this->call(TeamSeeder::class);
    }

    if (Players::count() === 0) {
      $this->call(PlayersSeeder::class);
    }

    if (TeamClassification::count() === 0) {
      $this->call(TeamClassificationSeeder::class);
    }
  }
}
