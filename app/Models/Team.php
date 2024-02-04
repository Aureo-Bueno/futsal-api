<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
  use HasUuids;
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'teams';

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
    'name',
    'team_match_id',
  ];

  /**
   * Get the players for the teams.
   */
  public function players(): HasMany
  {
    return $this->hasMany(Players::class, 'foreign_key');
  }

  public function team_match(): BelongsTo
  {
    return $this->belongsTo(TeamMatch::class);
  }
}
