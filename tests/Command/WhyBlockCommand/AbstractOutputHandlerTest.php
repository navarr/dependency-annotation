<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test\Command\WhyBlockCommand;

use DI\Container;
use Navarr\Depends\Command\WhyBlockCommand\OutputHandlerInterface;
use Navarr\Depends\Data\DeclaredDependency;
use Navarr\Depends\Proxy\StdOutWriter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractOutputHandlerTest extends TestCase
{
    /** @var Container */
    protected $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
    }

    /**
     * @param mixed[] $args Arguments to pass to constructor (may be named)
     * @return OutputHandlerInterface
     */
    abstract protected function createHandler(array $args = []): OutputHandlerInterface;

    /**
     * @return StdOutWriter&MockObject
     */
    protected function defaultWriterMock()
    {
        $writer = $this->createMock(StdOutWriter::class);
        $writer->method('canWrite')
            ->willReturn(true);

        return $writer;
    }

    public function testReturnValueIsZeroWhenThereAreNoDependencies(): void
    {
        $handler = $this->createHandler();
        $this->assertEquals(0, $handler->output([], '', ''));
    }

    public function testReturnValueIsOneWhenThereAreDependencies(): void
    {
        $handler = $this->createHandler();
        $this->assertEquals(1, $handler->output([new DeclaredDependency()], '', ''));
    }
}
