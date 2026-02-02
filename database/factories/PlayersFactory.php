<?php

namespace Database\Factories;

use App\Models\Players;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Players>
 */
class PlayersFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var class-string<Players>
   */
  protected $model = Players::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'name' => $this->faker->name(),
      'jersey_number' => $this->faker->unique()->numberBetween(1, 99),
      'team_id' => null,
    ];
  }
}
