<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlayersStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'jersey_number' => 'required|integer|min:0|max:99',
            'team_id' => 'required|uuid|exists:teams,id',
        ];
    }
}
