<?php

declare(strict_types=1);

namespace Youvision\ThinkPayment\Exception;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

class ContainerNotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
}