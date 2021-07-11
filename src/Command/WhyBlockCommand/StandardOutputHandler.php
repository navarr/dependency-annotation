<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Command\WhyBlockCommand;

use Navarr\Depends\Data\DeclaredDependency;
use Symfony\Component\Console\Output\OutputInterface;

class StandardOutputHandler implements OutputHandlerInterface
{
    /** @var OutputInterface */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param DeclaredDependency[] $dependencies
     * @param string $packageToSearchFor
     * @param string $versionToCompareTo
     * @return int
     */
    public function output(array $dependencies, string $packageToSearchFor, string $versionToCompareTo): int
    {
        if (count($dependencies) < 1) {
            $this->output->writeln("We found no reason to block {$packageToSearchFor} v{$versionToCompareTo}");
            return 0;
        }

        foreach ($dependencies as $dependency) {
            $this->output->writeln(
                ($dependency->getReference() !== null
                    ? str_replace(getcwd() . '/', '', $dependency->getReference())
                    : 'Unknown File')
                . ': ' . $dependency->getReason()
                . ' (' . $dependency->getConstraint() . ')'
            );
        }
        return 1;
    }
}
