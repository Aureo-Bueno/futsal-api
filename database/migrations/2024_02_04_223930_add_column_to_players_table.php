<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::table('players', function (Blueprint $table) {
      $table->foreign('team_id')->references('id')->on('teams');
    });
  }

  public function down()
  {
    Schema::table('players', function (Blueprint $table) {
      $table->dropColumn('team_id');
    });
  }
};
