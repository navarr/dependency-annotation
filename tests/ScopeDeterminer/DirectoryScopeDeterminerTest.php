<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test\ScopeDeterminer;

use Navarr\Depends\ScopeDeterminer\DirectoryScopeDeterminer;
use Navarr\Depends\ScopeDeterminer\PhpFileFinder;
use PHPUnit\Framework\TestCase;

class DirectoryScopeDeterminerTest extends TestCase
{
    public function testDirectoryIsGivenToPhpFileFinder(): void
    {
        $dir = uniqid();
        $finder = $this->createMock(PhpFileFinder::class);
        $finder->expects($this->once())
            ->method('findAll')
            ->with($dir)
            ->willReturn([]);

        $determiner = new DirectoryScopeDeterminer($finder, $dir);
        $determiner->getFiles();
    }

    public function testResultOfPhpFileFinderIsProvidedBack(): void
    {
        $results = [
            uniqid(),
            uniqid()
        ];

        $finder = $this->createMock(PhpFileFinder::class);
        $finder->method('findAll')
            ->willReturn($results);

        $determiner = new DirectoryScopeDeterminer($finder, uniqid());
        $this->assertEquals($results, $determiner->getFiles());
    }
}
