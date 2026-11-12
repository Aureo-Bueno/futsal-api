<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_events', function (Blueprint $table): void {
            $table->bigIncrements('global_position');
            $table->uuid('id')->primary();
            $table->string('aggregate_type');
            $table->uuid('aggregate_id');
            $table->unsignedInteger('aggregate_version');
            $table->string('event_type');
            $table->unsignedInteger('event_version')->default(1);
            $table->jsonb('payload');
            $table->jsonb('metadata')->default('{}');
            $table->string('producer');
            $table->timestampTz('occurred_at');
            $table->timestampTz('recorded_at')->useCurrent();

            $table->unique(['aggregate_id', 'aggregate_version']);
            $table->index(['aggregate_type', 'aggregate_id', 'aggregate_version']);
        });

        Schema::create('outbox_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('event_id');
            $table->string('topic');
            $table->jsonb('payload');
            $table->jsonb('headers')->default('{}');
            $table->string('status')->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestampTz('available_at')->useCurrent();
            $table->timestampTz('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'available_at']);
        });

        Schema::create('processed_events', function (Blueprint $table): void {
            $table->string('consumer');
            $table->uuid('event_id');
            $table->timestampTz('processed_at')->useCurrent();

            $table->primary(['consumer', 'event_id']);
        });

        Schema::create('snapshots', function (Blueprint $table): void {
            $table->string('aggregate_type');
            $table->uuid('aggregate_id');
            $table->unsignedInteger('aggregate_version');
            $table->jsonb('state');
            $table->timestampTz('created_at')->useCurrent();

            $table->primary(['aggregate_type', 'aggregate_id', 'aggregate_version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snapshots');
        Schema::dropIfExists('processed_events');
        Schema::dropIfExists('outbox_events');
        Schema::dropIfExists('domain_events');
    }
};
