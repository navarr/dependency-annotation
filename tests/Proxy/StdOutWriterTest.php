<?php
/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

namespace Navarr\Depends\Test\Proxy;

use Navarr\Depends\Proxy\StdOutWriter;
use PHPUnit\Framework\TestCase;

class StdOutWriterTest extends TestCase
{
    public function testCanWriteReturnsFalseIfResourceIsFalse()
    {
        $writer = new StdOutWriter(false);
        $this->assertFalse($writer->canWrite());
    }

    public function testCanWriteReturnsTrueIfResourceCanBeWrittenTo()
    {
        $file = fopen('/tmp/example', 'w');
        if ($file === false) {
            $this->markTestSkipped('Could not open temporary file');
        }

        $writer = new StdOutWriter($file);
        $this->assertTrue($writer->canWrite());

        fclose($file);
    }

    public function testContentIsWritten()
    {
        $filename = '/tmp/' . uniqid();
        $file = fopen($filename, 'w');
        if ($file === false) {
            $this->markTestSkipped('Could not open temporary file');
        }

        $contents = uniqid();

        $writer = new StdOutWriter($file);
        $writer->write($contents);
        fclose($file);

        $this->assertEquals($contents, file_get_contents($filename));
    }

    public function testCsvContentIsWritten()
    {
        $filename = '/tmp/' . uniqid();
        $file = fopen($filename, 'w');
        if ($file === false) {
            $this->markTestSkipped('Could not open temporary file');
        }

        $contentA = uniqid();
        $contentB = uniqid();
        $contentC = uniqid();

        $contentArray = [$contentA, $contentB, $contentC];
        $expectedResult = "{$contentA},{$contentB},{$contentC}\n";

        $writer = new StdOutWriter($file);
        $writer->writeCsv($contentArray);
        fclose($file);

        $this->assertEquals($expectedResult, file_get_contents($filename));
    }
}
