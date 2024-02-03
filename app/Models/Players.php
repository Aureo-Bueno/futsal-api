<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Players extends Model
{
  use HasUuids;
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'players';

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
    'jersey_number',
  ];
}
