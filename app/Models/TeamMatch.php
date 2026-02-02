<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamMatch extends Model
{
  use HasFactory, HasUuids, SoftDeletes;
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'team_match';

  /**
   * The primary key associated with the table.
   *
   * @var string
   */
  protected $primaryKey = 'id';

  /**
   * The "type" of the primary key ID.
   *
   * @var string
   */
  protected $keyType = 'string';

  /**
   * Indicates if the IDs are auto-incrementing.
   *
   * @var bool
   */
  public $incrementing = false;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'id',
    'date_team_match',
    'start_time',
    'end_time',
    'scoreboard',
  ];

  /**
   * Get the teams for the team match.
   */
  public function teams(): HasMany
  {
    return $this->hasMany(Team::class, 'foreign_key');
  }
}
