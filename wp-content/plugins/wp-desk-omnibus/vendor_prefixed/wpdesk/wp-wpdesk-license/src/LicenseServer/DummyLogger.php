<?php

declare (strict_types=1);
namespace OmnibusProVendor\WPDesk\License\LicenseServer;

use OmnibusProVendor\Psr\Log\LoggerInterface;
use OmnibusProVendor\Psr\Log\LoggerTrait;
/**
 * Dummy implementation of LoggerInterface.
 */
class DummyLogger implements LoggerInterface
{
    use LoggerTrait;
    public function log($level, $message, array $context = [])
    {
        error_log('wpdesk.license ' . $level . ': ' . $message);
    }
}
