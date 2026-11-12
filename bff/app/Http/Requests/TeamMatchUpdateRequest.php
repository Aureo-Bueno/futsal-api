<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeamMatchUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'date_team_match' => 'required|date',
            'start_time' => 'required|string|max:20',
            'end_time' => 'required|string|max:20',
            'scoreboard' => 'required|integer|min:0',
        ];
    }
}
