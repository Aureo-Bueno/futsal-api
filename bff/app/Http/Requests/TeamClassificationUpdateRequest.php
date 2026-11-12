<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeamClassificationUpdateRequest extends FormRequest
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
            'team_id' => 'required|uuid|exists:teams,id',
            'points' => 'required|integer|min:0',
            'number_of_goals' => 'required|integer|min:0',
        ];
    }
}
