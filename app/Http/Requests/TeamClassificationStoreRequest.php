<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeamClassificationStoreRequest extends FormRequest
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
      'points' => 'required|numeric',
      'number_of_goals' => 'required|numeric',
    ];
  }
}
