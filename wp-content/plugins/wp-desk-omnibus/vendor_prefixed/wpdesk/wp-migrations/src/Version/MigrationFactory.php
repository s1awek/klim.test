<?php

namespace OmnibusProVendor\WPDesk\Migrations\Version;

use OmnibusProVendor\WPDesk\Migrations\AbstractMigration;
interface MigrationFactory
{
    /** @param class-string<AbstractMigration> $migration_class */
    public function create_version(string $migration_class): AbstractMigration;
}
