<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Factory;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Navarr\Attribute\Dependency;
use PhpParser\NodeVisitor\FindingVisitor;

#[Dependency('php-di/php-di', '^6', 'Container::make')]
#[Dependency('nikic/php-parser', '^4', 'Existence of FindingVisitor')]
class FindingVisitorFactory
{
    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param mixed[] $args Arguments for {@link FindingVisitor}'s constructor
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function create(array $args = []): FindingVisitor
    {
        return $this->container->make(FindingVisitor::class, $args);
    }
}
