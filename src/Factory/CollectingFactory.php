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
use PhpParser\ErrorHandler\Collecting;
use RuntimeException;

#[Dependency('php-di/php-di', '^6', 'Container::make')]
#[Dependency('nikic/php-parser', '^4', 'Existence of Collecting error handler')]
class CollectingFactory
{
    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function create(): Collecting
    {
        $result = $this->container->make(Collecting::class);
        if (!$result instanceof Collecting) {
            throw new RuntimeException('Container did not return a Collecting');
        }
        return $result;
    }
}
