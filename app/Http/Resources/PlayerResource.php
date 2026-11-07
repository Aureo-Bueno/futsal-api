<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class PlayerResource extends JsonResource
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
            'jersey_number' => $this->jersey_number,
            'team_id' => $this->team_id,
            'team' => new TeamResource($this->whenLoaded('team')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
