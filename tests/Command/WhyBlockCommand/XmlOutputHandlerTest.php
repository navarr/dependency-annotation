<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

namespace Navarr\Depends\Test\Command\WhyBlockCommand;

use RuntimeException;
use JetBrains\PhpStorm\ArrayShape;
use Navarr\Depends\Command\WhyBlockCommand\XmlOutputHandler;
use Navarr\Depends\Data\DeclaredDependency;
use Navarr\Depends\Proxy\WriterInterface;
use Navarr\Depends\Test\Command\WhyBlockCommand\XmlOutputHandler\ContainerWriter;
use SimpleXMLElement;

class XmlOutputHandlerTest extends AbstractOutputHandlerTest
{
    protected function createHandler(array $args = []): XmlOutputHandler
    {
        if (!isset($args['writer'])) {
            $args['writer'] = $this->defaultWriterMock();
        }

        return $this->container->make(XmlOutputHandler::class, $args);
    }

    public function testExceptionWhenCanWriteReturnsFalse(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to output to stdin');

        $mock = $this->createMock(WriterInterface::class);
        $mock->expects($this->once())
            ->method('canWrite')
            ->willReturn(false);

        $handler = $this->createHandler(['writer' => $mock]);
        $handler->output([], '', '');
    }

    #[ArrayShape([
        'xml' => SimpleXMLElement::class,
        'dep1' => ['string','string','string','string'],
        'dep2' => ['string','string','string','string']
    ])]
    public function testRootElementOfResponseIsBlockReasonsAndHasExpectedAttributes(): array
    {
        $packageToSearchFor = uniqid();
        $versionToCompareTo = uniqid();

        $writer = new ContainerWriter();

        $reason1 = uniqid();
        $file1 = uniqid();
        $line1 = uniqid();
        $constraint1 = uniqid();
        $reason2 = uniqid();
        $file2 = uniqid();
        $line2 = uniqid();
        $constraint2 = uniqid();

        $dependency1 = new DeclaredDependency(
            file: $file1,
            line: $line1,
            constraint: $constraint1,
            reason: $reason1
        );

        $dependency2 = new DeclaredDependency(
            file: $file2,
            line: $line2,
            constraint: $constraint2,
            reason: $reason2
        );

        $handler = $this->createHandler(['writer' => $writer]);
        $handler->output([$dependency1, $dependency2], $packageToSearchFor, $versionToCompareTo);

        $xml = new SimpleXMLElement($writer->getContents());
        $this->assertEquals('block-reasons', $xml->getName());
        $this->assertEquals($packageToSearchFor, $xml['testedPackage']);
        $this->assertEquals($versionToCompareTo, $xml['packageVersion']);

        return [
            'xml' => $xml,
            'dep1' => [$file1, $line1, $constraint1, $reason1],
            'dep2' => [$file2, $line2, $constraint2, $reason2],
        ];
    }

    /**
     * @depends testRootElementOfResponseIsBlockReasonsAndHasExpectedAttributes
     */
    public function testReasonTagExistsForEachDependency(
        #[ArrayShape([
            'xml' => SimpleXMLElement::class,
            'dep1' => ['string', 'string', 'string', 'string'],
            'dep2' => ['string', 'string', 'string', 'string'],
        ])] array $result
    ): void {
        $xml = $result['xml'];

        $children = $xml->children();
        $this->assertCount(2, $children);
    }

    /**
     * @depends testRootElementOfResponseIsBlockReasonsAndHasExpectedAttributes
     */
    public function testDependency1ContentIsInReasons(
        #[ArrayShape([
            'xml' => SimpleXMLElement::class,
            'dep1' => ['string', 'string', 'string', 'string'],
            'dep2' => ['string', 'string', 'string', 'string'],
        ])] array $result
    ): void {
        $xml = $result['xml'];
        list($file, $line, $constraint, $reason) = $result['dep1'];

        $this->assertCount(3, $xml->reason->attributes());
        $this->assertEquals($reason, (string)$xml->reason[0]);
        $this->assertEquals($file, (string)$xml->reason[0]['file']);
        $this->assertEquals($line, (string)$xml->reason[0]['line']);
        $this->assertEquals($constraint, (string)$xml->reason[0]['constraint']);
    }

    /**
     * @depends testRootElementOfResponseIsBlockReasonsAndHasExpectedAttributes
     */
    public function testDependency2ContentIsInReasons(
        #[ArrayShape([
            'xml' => SimpleXMLElement::class,
            'dep1' => ['string', 'string', 'string', 'string'],
            'dep2' => ['string', 'string', 'string', 'string'],
        ])] array $result
    ): void {
        $xml = $result['xml'];
        list($file, $line, $constraint, $reason) = $result['dep2'];

        $this->assertCount(3, $xml->reason->attributes());
        $this->assertEquals($reason, (string)$xml->reason[1]);
        $this->assertEquals($file, (string)$xml->reason[1]['file']);
        $this->assertEquals($line, (string)$xml->reason[1]['line']);
        $this->assertEquals($constraint, (string)$xml->reason[1]['constraint']);
    }

    public function testAttributesAreExcludedWhenNotInDependency()
    {
        $dependency = new DeclaredDependency();
        $writer = new ContainerWriter();
        $handler = new XmlOutputHandler($writer);

        $handler->output([$dependency], '', '');

        $blankXml = <<<XML
<?xml version="1.0"?>
<block-reasons testedPackage="" packageVersion=""><reason/></block-reasons>

XML;

        $this->assertEquals($blankXml, $writer->getContents());
    }
}
