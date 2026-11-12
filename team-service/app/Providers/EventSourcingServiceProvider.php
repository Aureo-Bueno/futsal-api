<?php

declare(strict_types=1);

namespace App\Providers;

use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;
use Futsal\Contracts\Topics;
use Futsal\EventSourcing\Outbox\OutboxRecorder;
use Futsal\EventSourcing\Outbox\OutboxRelay;
use Futsal\EventSourcing\Publisher\EventPublisher;
use Futsal\EventSourcing\Publisher\SnsPublisher;
use Futsal\EventSourcing\Repository\AggregateRepository;
use Futsal\EventSourcing\Store\EventStore;
use Futsal\EventSourcing\Store\PostgresEventStore;
use Illuminate\Support\ServiceProvider;

final class EventSourcingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EventStore::class, PostgresEventStore::class);
        $this->app->singleton(OutboxRecorder::class);

        $aws = [
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'endpoint' => env('AWS_ENDPOINT_URL'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID', 'test'),
                'secret' => env('AWS_SECRET_ACCESS_KEY', 'test'),
            ],
        ];

        $this->app->singleton(SnsClient::class, fn () => new SnsClient($aws));
        $this->app->singleton(SqsClient::class, fn () => new SqsClient($aws));
        $this->app->singleton(EventPublisher::class, fn ($app) => new SnsPublisher(
            $app->make(SnsClient::class),
            [
                Topics::TEAM_EVENTS => (string) env('TEAM_EVENTS_TOPIC_ARN', Topics::TEAM_EVENTS),
                Topics::MATCH_EVENTS => (string) env('MATCH_EVENTS_TOPIC_ARN', Topics::MATCH_EVENTS),
            ],
        ));
        $this->app->singleton(OutboxRelay::class);

        $this->app->singleton(AggregateRepository::class, fn ($app) => new AggregateRepository(
            $app->make(EventStore::class),
            $app->make(OutboxRecorder::class),
            config('app.name', 'team-service'),
        ));
    }
}
