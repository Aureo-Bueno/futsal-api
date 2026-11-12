<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class TeamMatchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date_team_match' => $this->date_team_match,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'scoreboard' => $this->scoreboard,
            'teams' => TeamResource::collection($this->whenLoaded('teams')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
