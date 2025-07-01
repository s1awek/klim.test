<?php

declare (strict_types=1);
namespace OmnibusProVendor\DI\Definition\Helper;

use OmnibusProVendor\DI\Definition\Definition;
/**
 * Helps defining container entries.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface DefinitionHelper
{
    /**
     * @param string $entryName Container entry name
     */
    public function getDefinition(string $entryName): Definition;
}
