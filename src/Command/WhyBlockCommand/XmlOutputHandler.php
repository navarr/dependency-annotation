<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Command\WhyBlockCommand;

use Navarr\Depends\Data\DeclaredDependency;
use Navarr\Depends\Proxy\WriterInterface;
use RuntimeException;
use SimpleXMLElement;

class XmlOutputHandler implements OutputHandlerInterface
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
        if (!class_exists(SimpleXMLElement::class)) {
            throw new RuntimeException('PHP SimpleXML extension required to use XML Output');
        }
        if (!$this->writer->canWrite()) {
            throw new RuntimeException('Unable to output to stdin');
        }

        $results = new SimpleXMLElement('<block-reasons />');
        $results->addAttribute('testedPackage', $packageToSearchFor);
        $results->addAttribute('packageVersion', $versionToCompareTo);
        foreach ($dependencies as $dependency) {
            if ($dependency->getReason() !== null) {
                $reason = $results->addChild('reason', $dependency->getReason());
            } else {
                $reason = $results->addChild('reason');
            }
            if ($dependency->getFile() !== null) {
                $reason->addAttribute('file', $dependency->getFile());
            }
            if ($dependency->getLine() !== null) {
                $reason->addAttribute('line', $dependency->getLine());
            }
            if ($dependency->getConstraint() !== null) {
                $reason->addAttribute('constraint', $dependency->getConstraint());
            }
        }

        $this->writer->write($results->asXML() ?: '');

        return count($dependencies) < 1 ? 0 : 1;
    }
}
