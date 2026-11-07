<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class TeamResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'team_match_id' => $this->team_match_id,
            'players' => PlayerResource::collection($this->whenLoaded('players')),
            'team_match' => new TeamMatchResource($this->whenLoaded('teamMatch')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
