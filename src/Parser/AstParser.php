<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Parser;

use Navarr\Depends\IssueHandler\IssueHandlerInterface;
use Navarr\Depends\Parser\ParserInterface;
use PhpParser\ErrorHandler\Collecting;
use Navarr\Attribute\Dependency;
use Navarr\Depends\Data\DeclaredDependency;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

class AstParser implements ParserInterface
{
    /** @var IssueHandlerInterface|null */
    private $issueHandler;

    #[Dependency('nikic/php-parser', '^4')]
    #[Dependency('navarr/attribute-dependency', '^1', 'Existence of Dependency attribute')]
    public function parse(
        string $contents
    ): array {
        $astParser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $nameResolver = new NameResolver();
        $finder = new FindingVisitor(
            static function (Node $node) {
                return $node instanceof Attribute
                    && $node->name->toString() === Dependency::class;
            }
        );

        $traverser = new NodeTraverser();
        $traverser->addVisitor($nameResolver);
        $traverser->addVisitor($finder);

        $errorCollector = new Collecting();

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
                        $attributes['reason'] ?? null
                    );
                },
                $attributes
            )
        );
    }

    public function setIssueHandler(IssueHandlerInterface $handler): void
    {
        $this->issueHandler = $handler;
    }

    private function handleIssue(string $description): void
    {
        if ($this->issueHandler !== null) {
            $this->issueHandler->execute($description);
        }
    }
}
