<?php

declare(strict_types=1);

foreach ([__DIR__.'/../team-service/vendor/autoload.php', __DIR__.'/../match-service/vendor/autoload.php', __DIR__.'/../packages/contracts/vendor/autoload.php'] as $autoload) {
    if (file_exists($autoload)) {
        require $autoload;
        break;
    }
}

[$script, $method, $path, $body, $timestamp] = $argv + [null, 'GET', '/', '', (string) time()];

$signer = new Futsal\Contracts\Internal\RequestSigner(getenv('INTERNAL_HMAC_SECRET') ?: 'local-secret');

echo $signer->sign($method, $path, $body, $timestamp), PHP_EOL;
echo $timestamp, PHP_EOL;
