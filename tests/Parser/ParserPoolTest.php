<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test\Parser;

use Navarr\Depends\Parser\ParserInterface;
use Navarr\Depends\Parser\ParserPool;
use PHPUnit\Framework\TestCase;
use TypeError;

class ParserPoolTest extends TestCase
{
    public function testPoolErrorsOnNonCompliantParsers()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('All parsers must implement Navarr\Depends\Parser\ParserInterface');

        new ParserPool(['test']);
    }

    public function testAllParsersAreUsed()
    {
        $contents = uniqid();

        $parser1Result = uniqid();
        $parser1 = $this->createMock(ParserInterface::class);
        $parser1->expects($this->once())
            ->method('parse')
            ->with($contents)
            ->willReturn([$parser1Result]);

        $parser2Result = uniqid();
        $parser2 = $this->createMock(ParserInterface::class);
        $parser2->expects($this->once())
            ->method('parse')
            ->with($contents)
            ->willReturn([$parser2Result]);

        $pool = new ParserPool([$parser1, $parser2]);
        $result = $pool->parse($contents);

        $this->assertIsArray($result);
        $this->assertContains($parser1Result, $result);
        $this->assertContains($parser2Result, $result);
    }

    public function testHandlesNoResults()
    {
        $pool = new ParserPool([]);
        $result = $pool->parse('');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
