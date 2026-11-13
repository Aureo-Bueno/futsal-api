<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('team_classification');
        Schema::dropIfExists('team_match');
        Schema::dropIfExists('players');
        Schema::dropIfExists('teams');
    }

    public function down(): void
    {
        // Tabelas de domínio agora pertencem aos serviços; não recriar no BFF.
    }
};
