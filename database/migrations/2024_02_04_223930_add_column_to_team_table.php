<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::table('teams', function (Blueprint $table) {
      $table->uuid('team_match_id')->after('name');
    });
  }

  public function down()
  {
    Schema::table('teams', function (Blueprint $table) {
      $table->dropColumn('team_match_id');
    });
  }
};
