<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test\Factory;

use Navarr\Depends\Factory\FindingVisitorFactory;
use PhpParser\NodeVisitor\FindingVisitor;

class FindingVisitorFactoryTest extends AbstractFactoryTest
{
    protected function create(): FindingVisitorFactory
    {
        return $this->container->get(FindingVisitorFactory::class);
    }

    public function testEachCreateReturnsNewInstance()
    {
        $factory = $this->create();

        $args = [
            'filterCallback' => static function () {
            },
        ];

        $instance1 = $factory->create($args);
        $instance2 = $factory->create($args);

        $this->assertFalse($instance1 === $instance2);
    }

    public function testFactoryProducesExpectedType()
    {
        $this->assertInstanceOf(
            FindingVisitor::class,
            $this->create()->create(
                [
                    'filterCallback' => static function () {
                    },
                ]
            )
        );
    }
}
