<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::create('players', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('name');
      $table->integer('jersey_number');
      $table->uuid('team_id');
      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('players');
  }
};
