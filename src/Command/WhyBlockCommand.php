<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Command;

use Composer\Semver\Semver;
use Navarr\Depends\Command\WhyBlockCommand\OutputHandlerInterface;
use Navarr\Depends\Data\DeclaredDependency;
use Navarr\Depends\DeclaredDependencyAggregator;

class WhyBlockCommand
{
    /** @var DeclaredDependencyAggregator */
    private $aggregator;

    /** @var OutputHandlerInterface */
    private $outputHandler;

    public function __construct(DeclaredDependencyAggregator $aggregator, OutputHandlerInterface $outputHandler)
    {
        $this->aggregator = $aggregator;
        $this->outputHandler = $outputHandler;
    }

    public function execute(string $packageToSearchFor, string $versionToCompareTo): int
    {
        $dependencies = $this->aggregator->aggregate();

        /** @var DeclaredDependency[] $failingDependencies Declarations of the provided package that don't match the
         * version requirement
         */
        $failingDependencies = array_filter(
            $dependencies,
            static function (DeclaredDependency $attribute) use ($packageToSearchFor, $versionToCompareTo) {
                if ($attribute->getPackage() === null || $attribute->getConstraint() === null) {
                    return false;
                }
                return strtolower($attribute->getPackage()) === strtolower($packageToSearchFor)
                    && !Semver::satisfies($versionToCompareTo, $attribute->getConstraint());
            }
        );

        return $this->outputHandler->output($failingDependencies, $packageToSearchFor, $versionToCompareTo);
    }
}
