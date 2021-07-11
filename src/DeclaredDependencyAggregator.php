<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends;

use JetBrains\PhpStorm\Pure;
use Navarr\Depends\Data\DeclaredDependency;
use Navarr\Depends\Data\ReferenceAdder;
use Navarr\Depends\IssueHandler\IssueHandlerInterface;
use Navarr\Depends\Parser\ParserInterface;
use Navarr\Depends\ScopeDeterminer\ScopeDeterminerInterface;

class DeclaredDependencyAggregator
{
    /** @var IssueHandlerInterface|null */
    private $issueHandler;

    /** @var ParserInterface */
    private $parser;

    /** @var ReferenceAdder */
    private $referenceAdder;

    /** @var ScopeDeterminerInterface */
    private $scopeDeterminer;

    public function __construct(
        ParserInterface $parser,
        ScopeDeterminerInterface $scopeDeterminer,
        ReferenceAdder $referenceAdder,
        IssueHandlerInterface $issueHandler = null
    ) {
        $this->parser = $parser;
        $this->scopeDeterminer = $scopeDeterminer;
        $this->issueHandler = $issueHandler;
        $this->referenceAdder = $referenceAdder;
    }

    /**
     * @return DeclaredDependency[]
     */
    #[Pure]
    public function aggregate(): array
    {
        $dependencies = [[]];
        $files = $this->scopeDeterminer->getFiles();
        foreach ($files as $file) {
            $contents = @file_get_contents($file);
            if ($contents === false) {
                $this->handleIssue("Could not read from file '{$file}'");
                continue;
            }
            $dependencies[] = array_map(
                function (DeclaredDependency $dependency) use ($file) {
                    return $this->referenceAdder->add($dependency, $file);
                },
                $this->parser->parse($contents)
            );
        }
        return array_merge(...$dependencies);
    }

    private function handleIssue(string $description): void
    {
        if ($this->issueHandler) {
            $this->issueHandler->execute($description);
        }
    }
}
