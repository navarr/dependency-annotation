<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test\ScopeDeterminer;

use Navarr\Depends\Proxy\MimeDeterminer;
use Navarr\Depends\ScopeDeterminer\PhpFileDeterminer;
use PHPUnit\Framework\TestCase;

class PhpFileDeterminerTest extends TestCase
{
    public function testFileIsConsideredPhpBasedOnMimeType()
    {
        $pretendMimeTypes = [
            'text/x-php',
            'application/x-php',
            'application/x-httpd-php',
        ];

        foreach ($pretendMimeTypes as $pretendMimeType) {
            $mockMimeDeterminer = $this->createMock(MimeDeterminer::class);
            $mockMimeDeterminer->expects($this->once())
                ->method('getMimeType')
                ->willReturn($pretendMimeType);

            $determiner = new PhpFileDeterminer($mockMimeDeterminer);
            $this->assertTrue($determiner->isPhp(uniqid() . '.html'));
        }
    }

    public function testFileIsConsideredPhpBasedOnExtension()
    {
        $determiner = new PhpFileDeterminer(new MimeDeterminer());
        $pretendFileNames = [
            'hellow.php',
            'im-full-of-html.phtml',
            'im-so-old-why-even-bother.php3',
            'im-not-much-better.php4',
            'i-guess-im-okay-but-who-uses-this.php5',
            'seriously-why-are-you-using-this-format.php7',
            'are-we-not-past-the-20th-century.php8',
        ];

        foreach ($pretendFileNames as $pretendFileName) {
            $this->assertTrue(
                $determiner->isPhp($pretendFileName)
            );
        }
    }

    public function testNonPhpFileIsNotConsideredPhp()
    {
        $file = uniqid() . '.html';

        $mockMimeDeterminer = $this->createMock(MimeDeterminer::class);
        $mockMimeDeterminer->expects($this->once())
            ->method('getMimeType')
            ->with($file)
            ->willReturn('text/html');

        $determiner = new PhpFileDeterminer($mockMimeDeterminer);
        $this->assertFalse(
            $determiner->isPhp($file)
        );
    }
}
