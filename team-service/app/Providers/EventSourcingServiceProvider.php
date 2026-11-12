<?php

declare(strict_types=1);

namespace App\Providers;

use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;
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
        $this->app->singleton(EventPublisher::class, SnsPublisher::class);
        $this->app->singleton(OutboxRelay::class);

        $this->app->singleton(AggregateRepository::class, fn ($app) => new AggregateRepository(
            $app->make(EventStore::class),
            $app->make(OutboxRecorder::class),
            config('app.name', 'team-service'),
        ));
    }
}
