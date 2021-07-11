<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Command\WhyBlockCommand;

use Navarr\Depends\Data\DeclaredDependency;

class CsvOutputHandler implements OutputHandlerInterface
{
    /** @var bool */
    private $includeHeader;

    public function __construct(bool $includeHeader = true)
    {
        $this->includeHeader = $includeHeader;
    }

    /**
     * @param DeclaredDependency[] $dependencies
     * @param string $packageToSearchFor
     * @param string $versionToCompareTo
     * @return int
     */
    public function output(array $dependencies, string $packageToSearchFor, string $versionToCompareTo): int
    {
        $resource = STDIN;
        if ($this->includeHeader) {
            fputcsv($resource, ['File', 'Line #', 'Constraint Specified', 'Reasoning']);
        }
        foreach ($dependencies as $dependency) {
            fputcsv(
                $resource,
                [
                    $dependency->getFile(),
                    $dependency->getLine(),
                    $dependency->getConstraint(),
                    $dependency->getReason(),
                ]
            );
        }
        return count($dependencies) < 1 ? 0 : 1;
    }
}
