<?php

namespace Navarr\Depends\Model;

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
    #[Dependency('nikic/php-parser', '^4')]
    #[Dependency('navarr/attribute-dependency', '^1', 'Existence of Dependency attribute')]
    public function parse(string $file): array
    {
        $astParser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $nameResolver = new NameResolver(null, ['replaceNodes' => true]);
        $finder = new FindingVisitor(static function (Node $node) use ($nameResolver) {
            return $node instanceof Attribute
                && $node->name->toString() === Dependency::class;
            }
        );

        $traverser = new NodeTraverser();
        $traverser->addVisitor($nameResolver);
        $traverser->addVisitor($finder);

        $code = file_get_contents($file);
        $ast = $astParser->parse($code);
        $traverser->traverse($ast);

        $attributes = $finder->getFoundNodes();

        $argIndex = [
            0 => 'package',
            1 => 'versionConstraint',
            2 => 'reason'
        ];

        return array_map(
            static function (Attribute $node) use ($file, $argIndex): DeclaredDependency {
                $attributes = [];
                foreach ($node->args as $i => $arg) {
                    $name = $arg->name ?? $argIndex[$i];
                    if ($arg->value instanceof Node\Scalar\String_) {
                        $attributes[$name] = $arg->value->value;
                    }
                }
                return new DeclaredDependency(
                    $file,
                    $node->getLine(),
                    "{$file}:{$node->getLine()}",
                    $attributes['package'] ?? null,
                    $attributes['versionConstraint'] ?? null,
                    $attributes['reason'] ?? null
                );
            },
            $attributes
        );
    }
}
