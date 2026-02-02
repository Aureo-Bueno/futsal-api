<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamClassificationResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'team_id' => $this->team_id,
      'points' => $this->points,
      'number_of_goals' => $this->number_of_goals,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}
