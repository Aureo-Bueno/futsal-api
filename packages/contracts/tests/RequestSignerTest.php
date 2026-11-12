<?php

declare(strict_types=1);

namespace Futsal\Contracts\Tests;

use Futsal\Contracts\Internal\RequestSigner;
use PHPUnit\Framework\TestCase;

final class RequestSignerTest extends TestCase
{
    public function test_sign_is_deterministic(): void
    {
        $signer = new RequestSigner('secret');

        $this->assertSame(
            hash_hmac('sha256', "POST\n/matches\n{\"a\":1}\n1700000000", 'secret'),
            $signer->sign('POST', '/matches', '{"a":1}', '1700000000'),
        );
    }
}
