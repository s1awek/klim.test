<?php

declare (strict_types=1);
namespace OmnibusProVendor\DI;

use OmnibusProVendor\Psr\Container\ContainerExceptionInterface;
/**
 * Exception for the Container.
 */
class DependencyException extends \Exception implements ContainerExceptionInterface
{
}
