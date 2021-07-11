<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test\Factory;

use DI\Container;
use PHPUnit\Framework\TestCase;

abstract class AbstractFactoryTest extends TestCase
{
    /** @var Container */
    protected $container;

    abstract protected function create();

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
    }

    public function testEachCreateReturnsNewInstance()
    {
        $factory = $this->create();

        $instance1 = $factory->create();
        $instance2 = $factory->create();

        $this->assertFalse($instance1 === $instance2);
    }

    abstract public function testFactoryProducesExpectedType();
}
