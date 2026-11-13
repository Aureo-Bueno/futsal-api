<?php

namespace App\Providers;

use Aws\Sqs\SqsClient;
use Futsal\Contracts\Internal\RequestSigner;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RequestSigner::class, fn () => new RequestSigner(
            (string) env('INTERNAL_HMAC_SECRET', 'local-secret'),
        ));

        $this->app->singleton(SqsClient::class, fn () => new SqsClient([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'endpoint' => env('AWS_ENDPOINT_URL'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID', 'test'),
                'secret' => env('AWS_SECRET_ACCESS_KEY', 'test'),
            ],
        ]));
    }

    public function boot(): void
    {
        //
    }
}
