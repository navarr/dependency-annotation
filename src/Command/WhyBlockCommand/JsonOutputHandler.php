<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Command\WhyBlockCommand;

use Navarr\Depends\Data\DeclaredDependency;
use Navarr\Depends\Proxy\WriterInterface;
use RuntimeException;

class JsonOutputHandler implements OutputHandlerInterface
{
    /** @var WriterInterface */
    private $writer;

    public function __construct(WriterInterface $writer)
    {
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
        if (!function_exists('json_encode')) {
            throw new RuntimeException('PHP JSON Extension must be installed to use JSON Output');
        }
        if (!$this->writer->canWrite()) {
            throw new RuntimeException('Unable to output to stdin');
        }
        $this->writer->write(
            json_encode(
                array_map(
                    static function (DeclaredDependency $dependency) {
                        $result = [];
                        if ($dependency->getFile() !== null) {
                            $result['file'] = $dependency->getFile();
                        }
                        if ($dependency->getLine() !== null) {
                            $result['line'] = $dependency->getLine();
                        }
                        if ($dependency->getConstraint() !== null) {
                            $result['declaredConstraint'] = $dependency->getConstraint();
                        }
                        if ($dependency->getReason() !== null) {
                            $result['reason'] = $dependency->getReason();
                        }
                        return $result;
                    },
                    $dependencies
                )
            ) ?: ''
        );
        return count($dependencies) < 1 ? 0 : 1;
    }
}
