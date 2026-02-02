<?php

namespace Database\Factories;

use App\Models\TeamMatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamMatch>
 */
class TeamMatchFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var class-string<TeamMatch>
   */
  protected $model = TeamMatch::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $date = $this->faker->dateTimeBetween('-1 month', '+1 month');

    return [
      'date_team_match' => $date->format('Y-m-d'),
      'start_time' => $this->faker->time('H:i'),
      'end_time' => $this->faker->time('H:i'),
      'scoreboard' => (string) $this->faker->numberBetween(0, 10),
    ];
  }
}
