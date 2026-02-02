<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMatchResource extends JsonResource
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
      'date_team_match' => $this->date_team_match,
      'start_time' => $this->start_time,
      'end_time' => $this->end_time,
      'scoreboard' => $this->scoreboard,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}
