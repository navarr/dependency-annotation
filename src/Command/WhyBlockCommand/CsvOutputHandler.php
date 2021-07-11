<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Command\WhyBlockCommand;

use Navarr\Depends\Data\DeclaredDependency;
use Navarr\Depends\Proxy\WriterInterface;

class CsvOutputHandler implements OutputHandlerInterface
{
    /** @var bool */
    private $includeHeader;

    /** @var WriterInterface */
    private $writer;

    public function __construct(WriterInterface $writer, bool $includeHeader = true)
    {
        $this->includeHeader = $includeHeader;
        $this->writer = $writer;
    }

    /**
     * @param DeclaredDependency[] $dependencies
     * @param string $packageToSearchFor
     * @param string $versionToCompareTo
     * @return int
     */
    public function output(array $dependencies, string $packageToSearchFor, string $versionToCompareTo): int
    {
        if ($this->includeHeader) {
            $this->writer->writeCsv(['File', 'Line #', 'Constraint Specified', 'Reasoning']);
        }
        foreach ($dependencies as $dependency) {
            $this->writer->writeCsv(
                [
                    $dependency->getFile() ?: '',
                    $dependency->getLine() ?: '',
                    $dependency->getConstraint() ?: '',
                    $dependency->getReason() ?: '',
                ]
            );
        }
        return count($dependencies) < 1 ? 0 : 1;
    }
}
