<?php

namespace Database\Factories;

use App\Models\TeamClassification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamClassification>
 */
class TeamClassificationFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var class-string<TeamClassification>
   */
  protected $model = TeamClassification::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'team_id' => null,
      'points' => $this->faker->numberBetween(0, 30),
      'number_of_goals' => $this->faker->numberBetween(0, 50),
    ];
  }
}
