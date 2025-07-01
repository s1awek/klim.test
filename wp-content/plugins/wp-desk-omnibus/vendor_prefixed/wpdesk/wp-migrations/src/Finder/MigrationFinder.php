<?php

declare (strict_types=1);
namespace OmnibusProVendor\WPDesk\Migrations\Finder;

use OmnibusProVendor\WPDesk\Migrations\AbstractMigration;
interface MigrationFinder
{
    /**
     * @param string $directory
     * @return class-string<AbstractMigration>[]
     */
    public function find_migrations(string $directory): array;
}
