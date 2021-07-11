<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test;

use Navarr\Depends\Data\DeclaredDependency;
use Navarr\Depends\Data\ReferenceAdder;
use Navarr\Depends\DeclaredDependencyAggregator;
use Navarr\Depends\IssueHandler\FailOnIssueHandler;
use Navarr\Depends\Parser\ParserInterface;
use Navarr\Depends\ScopeDeterminer\ScopeDeterminerInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class DeclaredDependencyAggregatorTest extends TestCase
{
    public function testAllFilesProvidedByScopeDeterminerAreSentToParser(): void
    {
        $files = [
            __DIR__ . '/_data/attributeUsage.php',
            __DIR__ . '/_data/emptyFile.php',
            __DIR__ . '/_data/incorrectAttributeUsage.php',
        ];

        $scopeDeterminerMock = $this->createMock(ScopeDeterminerInterface::class);
        $scopeDeterminerMock->expects($this->once())
            ->method('getFiles')
            ->willReturn($files);

        $parserMock = $this->createMock(ParserInterface::class);
        $parserMock->expects($this->exactly(3))
            ->method('parse')
            ->withConsecutive(
            // How can we update this so that we test each one was put through, but not necessarily consecutively?
                [file_get_contents($files[0])],
                [file_get_contents($files[1])],
                [file_get_contents($files[2])]
            );

        $aggregator = new DeclaredDependencyAggregator(
            $parserMock,
            $scopeDeterminerMock,
            new ReferenceAdder(),
            new FailOnIssueHandler()
        );

        $aggregator->aggregate();
    }

    public function testIssueHandlerAlertedWhenFileCannotBeOpened(): void
    {
        $file = __DIR__ . '/_data/nonExistantFile';
        $files = [$file];

        $scopeDeterminerMock = $this->createMock(ScopeDeterminerInterface::class);
        $scopeDeterminerMock->expects($this->once())
            ->method('getFiles')
            ->willReturn($files);

        $parserMock = $this->createMock(ParserInterface::class);
        $parserMock->method('parse')
            ->willReturn([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Could not read from file '{$file}'");

        $aggregator = new DeclaredDependencyAggregator(
            $parserMock,
            $scopeDeterminerMock,
            new ReferenceAdder(),
            new FailOnIssueHandler()
        );

        $aggregator->aggregate();
    }

    public function testParserNotCalledWhenFileCannotBeOpened(): void
    {
        $file = __DIR__ . '/_data/nonExistantFile';
        $files = [$file];

        $scopeDeterminerMock = $this->createMock(ScopeDeterminerInterface::class);
        $scopeDeterminerMock->expects($this->once())
            ->method('getFiles')
            ->willReturn($files);

        $parserMock = $this->createMock(ParserInterface::class);
        $parserMock->expects($this->never())
            ->method('parse');

        $aggregator = new DeclaredDependencyAggregator(
            $parserMock,
            $scopeDeterminerMock,
            new ReferenceAdder(),
            null
        );

        $aggregator->aggregate();
    }

    public function testFileAndReferenceAreAddedToDeclaredDependency(): void
    {
        $file = __DIR__ . '/_data/attributeUsage.php';
        $files = [$file];

        $scopeDeterminerMock = $this->createMock(ScopeDeterminerInterface::class);
        $scopeDeterminerMock->expects($this->once())
            ->method('getFiles')
            ->willReturn($files);

        $line = '5';

        $dependency = new DeclaredDependency(line: $line);
        $this->assertNull($dependency->getFile());
        $this->assertNull($dependency->getReference());

        $parserMock = $this->createMock(ParserInterface::class);
        $parserMock->expects($this->once())
            ->method('parse')
            ->willReturn([$dependency]);

        $aggregator = new DeclaredDependencyAggregator(
            $parserMock,
            $scopeDeterminerMock,
            new ReferenceAdder(),
            null
        );

        $results = $aggregator->aggregate();
        $this->assertCount(1, $results);

        $newDependency = $results[0];

        $this->assertEquals($line, $newDependency->getLine());
        $this->assertEquals($file, $newDependency->getFile());
        $this->assertEquals("{$file}:{$line}", $newDependency->getReference());
    }
}
