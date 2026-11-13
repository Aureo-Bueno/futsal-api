<?php

use App\Providers\AppServiceProvider;
use App\Providers\EventSourcingServiceProvider;
use Futsal\Observability\Laravel\ObservabilityServiceProvider;

return [
    AppServiceProvider::class,
    ObservabilityServiceProvider::class,
    EventSourcingServiceProvider::class,
];
