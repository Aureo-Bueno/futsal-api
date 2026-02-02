<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlayersUpdateRequest extends FormRequest
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
      'name' => 'required|string',
      'jersey_number' => 'required|numeric',
      'team_id' => 'required|uuid|exists:teams,id',
    ];
  }
}
