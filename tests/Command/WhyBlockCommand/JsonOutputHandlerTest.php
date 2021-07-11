<?php
/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

namespace Navarr\Depends\Test\Command\WhyBlockCommand;

use Navarr\Depends\Command\WhyBlockCommand\JsonOutputHandler;
use Navarr\Depends\Command\WhyBlockCommand\OutputHandlerInterface;
use Navarr\Depends\Data\DeclaredDependency;
use Navarr\Depends\Proxy\StdOutWriter;
use Navarr\Depends\Proxy\WriterInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class JsonOutputHandlerTest extends AbstractOutputHandlerTest
{
    protected function createHandler(array $args = []): JsonOutputHandler
    {
        if (!isset($args['writer'])) {
            $args['writer'] = $this->defaultWriterMock();
        }

        return $this->container->make(JsonOutputHandler::class, $args);
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

    public function expectedResultProvider(): array
    {
        $file = uniqid();
        $line = uniqid();
        $constraint = uniqid();
        $reason = uniqid();

        return [
            'No Results' => ['[]', []],
            'Only File' => [json_encode([['file' => $file]]), [new DeclaredDependency(file: $file)]],
            'Only Line' => [json_encode([['line' => $line]]), [new DeclaredDependency(line: $line)]],
            'Only Constraint' => [
                json_encode([['declaredConstraint' => $constraint]]),
                [new DeclaredDependency(constraint: $constraint)],
            ],
            'Only Reason' => [json_encode([['reason' => $reason]]), [new DeclaredDependency(reason: $reason)]],
            'All in order' => [
                json_encode(
                    [
                        [
                            'file' => $file,
                            'line' => $line,
                            'declaredConstraint' => $constraint,
                            'reason' => $reason,
                        ],
                    ]
                ),
                [new DeclaredDependency(file: $file, line: $line, constraint: $constraint, reason: $reason)],
            ],
        ];
    }

    /**
     * @param string $expectedResult
     * @param DeclaredDependency[] $dependencies
     * @dataProvider expectedResultProvider
     */
    public function testOutputFormatIsAsExpected(string $expectedResult, array $dependencies = []): void
    {
        $writer = $this->defaultWriterMock();
        $writer->expects($this->once())
            ->method('write')
            ->with($expectedResult);

        $handler = $this->createHandler(['writer' => $writer]);
        $handler->output($dependencies, '', '');
    }
}
