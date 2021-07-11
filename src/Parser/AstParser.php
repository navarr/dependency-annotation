<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Parser;

use Navarr\Attribute\Dependency;
use Navarr\Depends\Data\DeclaredDependency;
use Navarr\Depends\Factory\CollectingFactory;
use Navarr\Depends\Factory\FindingVisitorFactory;
use Navarr\Depends\Factory\NodeTraverserFactory;
use Navarr\Depends\IssueHandler\IssueHandlerInterface;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

class AstParser implements ParserInterface
{
    /** @var CollectingFactory */
    private $errorCollectorFactory;

    /** @var FindingVisitorFactory */
    private $findingVisitorFactory;

    /** @var IssueHandlerInterface|null */
    private $issueHandler;

    /** @var NameResolver */
    private $nameResolver;

    /** @var NodeTraverserFactory */
    private $nodeTraverserFactory;

    /** @var ParserFactory */
    private $parserFactory;

    public function __construct(
        ParserFactory $parserFactory,
        NameResolver $nameResolver,
        NodeTraverserFactory $nodeTraverserFactory,
        CollectingFactory $errorCollectorFactory,
        FindingVisitorFactory $findingVisitorFactory,
        IssueHandlerInterface $issueHandler = null
    ) {
        $this->parserFactory = $parserFactory;
        $this->nameResolver = $nameResolver;
        $this->nodeTraverserFactory = $nodeTraverserFactory;
        $this->errorCollectorFactory = $errorCollectorFactory;
        $this->findingVisitorFactory = $findingVisitorFactory;
        $this->issueHandler = $issueHandler;
    }

    #[Dependency('nikic/php-parser', '^4')]
    #[Dependency('navarr/attribute-dependency', '^1', 'Existence of Dependency attribute')]
    public function parse(
        string $contents
    ): array {
        $astParser = $this->parserFactory->create(ParserFactory::PREFER_PHP7);
        $nameResolver = $this->nameResolver;
        $finder = $this->findingVisitorFactory->create(
            [
                'filterCallback' => static function (Node $node) {
                    return $node instanceof Attribute
                        && $node->name->toString() === Dependency::class;
                },
            ]
        );

        $traverser = $this->nodeTraverserFactory->create();
        $traverser->addVisitor($nameResolver);
        $traverser->addVisitor($finder);

        $errorCollector = $this->errorCollectorFactory->create();

        $ast = $astParser->parse($contents, $errorCollector);
        if ($ast === null || $errorCollector->hasErrors()) {
            $description = "Could not parse contents:" . PHP_EOL . ' - '
                . implode(PHP_EOL . ' - ', $errorCollector->getErrors());
            $this->handleIssue($description);
            return [];
        }

        $traverser->traverse($ast);

        $attributes = $finder->getFoundNodes();

        $argIndex = [
            0 => 'package',
            1 => 'versionConstraint',
            2 => 'reason',
            3 => 'required',
        ];

        return array_filter(
            array_map(
                static function (Attribute $node) use ($argIndex): ?DeclaredDependency {
                    $attributes = [];
                    foreach ($node->args as $i => $arg) {
                        $name = $arg->name->name ?? $argIndex[$i];
                        if (!is_string($name)) {
                            return null;
                        }
                        if ($arg->value instanceof Node\Scalar\String_) {
                            $attributes[$name] = $arg->value->value;
                        }
                    }
                    if (!isset($attributes['package'])) {
                        return null;
                    }
                    return new DeclaredDependency(
                        null,
                        (string)$node->getLine(),
                        null,
                        $attributes['package'],
                        $attributes['versionConstraint'] ?? null,
                        $attributes['reason'] ?? null,
                        isset($attributes['required']) && (bool)$attributes['required']
                    );
                },
                $attributes
            )
        );
    }

    private function handleIssue(string $description): void
    {
        if ($this->issueHandler !== null) {
            $this->issueHandler->execute($description);
        }
    }
}
