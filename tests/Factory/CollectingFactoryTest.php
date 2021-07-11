<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test\Factory;

use Navarr\Depends\Factory\CollectingFactory;
use PhpParser\ErrorHandler\Collecting;

class CollectingFactoryTest extends AbstractFactoryTest
{
    protected function create(): CollectingFactory
    {
        return $this->container->get(CollectingFactory::class);
    }

    public function testFactoryProducesExpectedType()
    {
        $this->assertInstanceOf(Collecting::class, $this->create()->create());
    }
}
