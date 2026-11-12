<?php

declare(strict_types=1);

namespace Futsal\EventSourcing\Exceptions;

use RuntimeException;

final class ConcurrencyException extends RuntimeException {}
