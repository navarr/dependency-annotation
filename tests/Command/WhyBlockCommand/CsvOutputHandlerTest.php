<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test\Command\WhyBlockCommand;

use Navarr\Depends\Command\WhyBlockCommand\CsvOutputHandler;
use Navarr\Depends\Data\DeclaredDependency;
use Navarr\Depends\Proxy\StdOutWriter;

class CsvOutputHandlerTest extends AbstractOutputHandlerTest
{
    protected function createHandler(array $args = []): CsvOutputHandler
    {
        if (!isset($args['includeHeader'])) {
            $args['includeHeader'] = false;
        }
        if (!isset($args['writer'])) {
            $args['writer'] = $this->defaultWriterMock();
        }
        return $this->container->make(CsvOutputHandler::class, $args);
    }

    public function testOutputHeaderIsNotWrittenWhenIncludeHeaderIsFalse(): void
    {
        $writer = $this->createMock(StdOutWriter::class);
        $writer->expects($this->never())
            ->method('writeCsv');

        $handler = $this->createHandler(['writer' => $writer, 'includeHeader' => false]);
        $handler->output([], '', '');
    }

    public function testHeaderIsWrittenByDefault(): void
    {
        $writer = $this->createMock(StdOutWriter::class);
        $writer->expects($this->atLeastOnce())
            ->method('writeCsv')
            ->with(['File', 'Line #', 'Constraint Specified', 'Reasoning']);

        $handler = $this->container->make(CsvOutputHandler::class, ['writer' => $writer]);
        $handler->output([], '', '');
    }

    public function testOutputHeaderIsWrittenWhenIncludeHeaderIsTrue(): void
    {
        $writer = $this->createMock(StdOutWriter::class);
        $writer->expects($this->atLeastOnce())
            ->method('writeCsv')
            ->with(['File', 'Line #', 'Constraint Specified', 'Reasoning']);

        $handler = $this->createHandler(['writer' => $writer, 'includeHeader' => true]);
        $handler->output([], '', '');
    }

    public function testCsvOutputIsInExpectedFormat(): void
    {
        $file = uniqid();
        $line = uniqid();
        $constraint = uniqid();
        $reason = uniqid();

        $writer = $this->createMock(StdOutWriter::class);
        $writer->expects($this->once())
            ->method('writeCsv')
            ->with([$file, $line, $constraint, $reason]);

        $dependency = new DeclaredDependency(
            file: $file,
            line: $line,
            constraint: $constraint,
            reason: $reason
        );

        $handler = $this->createHandler(['writer' => $writer, 'includeHeader' => false]);
        $handler->output([$dependency], '', '');
    }
}
