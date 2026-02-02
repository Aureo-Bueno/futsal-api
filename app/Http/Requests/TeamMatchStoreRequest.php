<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeamMatchStoreRequest extends FormRequest
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
      'start_time' => 'required|string',
      'end_time' => 'required|string',
      'scoreboard' => 'required|numeric',
    ];
  }
}
