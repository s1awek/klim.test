<?php

declare (strict_types=1);
namespace OmnibusProVendor\WPDesk\Migrations;

interface Migrator
{
    public function migrate(): void;
}
