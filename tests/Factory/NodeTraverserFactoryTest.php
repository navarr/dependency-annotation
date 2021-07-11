<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test\Factory;

use Navarr\Depends\Factory\NodeTraverserFactory;
use PhpParser\NodeTraverser;

class NodeTraverserFactoryTest extends AbstractFactoryTest
{
    protected function create(): NodeTraverserFactory
    {
        return $this->container->get(NodeTraverserFactory::class);
    }

    public function testFactoryProducesExpectedType()
    {
        $this->assertInstanceOf(NodeTraverser::class, $this->create()->create());
    }
}
