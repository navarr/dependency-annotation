<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

namespace Navarr\Depends\Command\WhyBlockCommand;

use JetBrains\PhpStorm\ArrayShape;
use Navarr\Depends\Data\DeclaredDependency;

/**
 * @api
 */
interface OutputHandlerInterface
{
    /**
     * @param DeclaredDependency[] $dependencies
     */
    #[ArrayShape([DeclaredDependency::class])]
    public function output(array $dependencies, string $packageToSearchFor, string $versionToCompareTo): int;
}
