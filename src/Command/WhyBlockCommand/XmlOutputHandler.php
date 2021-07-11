<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Command\WhyBlockCommand;

use Navarr\Depends\Data\DeclaredDependency;
use RuntimeException;
use SimpleXMLElement;

class XmlOutputHandler implements OutputHandlerInterface
{
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
        $resource = STDIN;
        if ($resource === false) {
            throw new RuntimeException('Unable to output to stdin');
        }

        $results = new SimpleXMLElement('<block-reasons />');
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

        fputs($resource, $results->asXML() ?: '');

        return count($dependencies) < 1 ? 0 : 1;
    }
}
