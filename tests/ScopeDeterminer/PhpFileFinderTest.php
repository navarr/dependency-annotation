<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test\ScopeDeterminer;

use DI\Container;
use Navarr\Depends\ScopeDeterminer\PhpFileFinder;
use PHPUnit\Framework\TestCase;

class PhpFileFinderTest extends TestCase
{
    public function testFindAll()
    {
        $directory = implode(
            DIRECTORY_SEPARATOR,
            [
                __DIR__,
                '..',
                '_data',
                'phpFileFinder',
            ]
        );

        $files = array_map(
            'realpath',
            [
                $directory . DIRECTORY_SEPARATOR . 'file1.php',
                $directory . DIRECTORY_SEPARATOR . 'recursion1/file4.php',
                $directory . DIRECTORY_SEPARATOR . 'recursion1/anotherFile.php',
                $directory . DIRECTORY_SEPARATOR . 'recursion1/recursion2/file3.php',
                $directory . DIRECTORY_SEPARATOR . 'recursion1/recursion2/recursion3/file2.php',
            ]
        );

        $container = new Container();
        $finder = $container->get(PhpFileFinder::class);
        $results = $finder->findAll($directory);

        $this->assertIsArray($results);
        $this->assertCount(5, $results);

        foreach ($files as $file) {
            $this->assertContains($file, $results);
        }
    }
}
