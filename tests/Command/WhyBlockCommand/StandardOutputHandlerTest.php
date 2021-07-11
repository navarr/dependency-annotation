<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test\Command\WhyBlockCommand;

use Navarr\Depends\Command\WhyBlockCommand\StandardOutputHandler;
use Navarr\Depends\Data\DeclaredDependency;
use Symfony\Component\Console\Output\OutputInterface;

class StandardOutputHandlerTest extends AbstractOutputHandlerTest
{
    protected function createHandler(array $args = []): StandardOutputHandler
    {
        if (!isset($args['output'])) {
            $args['output'] = $this->createMock(OutputInterface::class);
        }
        return $this->container->make(StandardOutputHandler::class, $args);
    }

    public function testHumanReadableOutputWhenNoResults(): void
    {
        $packageToSearchFor = uniqid();
        $versionToCompareTo = uniqid();

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())
            ->method('writeln')
            ->with("We found no reason to block {$packageToSearchFor} v{$versionToCompareTo}");

        $handler = $this->createHandler(['output' => $output]);
        $handler->output([], $packageToSearchFor, $versionToCompareTo);
    }

    public function testGeneralOutputFormat(): void
    {
        $reference = uniqid();
        $constraint = uniqid();
        $reason = uniqid();

        $dependency = new DeclaredDependency(
            reference: $reference,
            constraint: $constraint,
            reason: $reason
        );

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())
            ->method('writeln')
            ->with("{$reference}: {$reason} ({$constraint})");

        $handler = $this->createHandler(['output' => $output]);
        $handler->output([$dependency], '', '');
    }

    public function testReferencelessOutputFormat(): void
    {
        $constraint = uniqid();
        $reason = uniqid();

        $dependency = new DeclaredDependency(
            constraint: $constraint,
            reason: $reason
        );

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())
            ->method('writeln')
            ->with("Unknown File: {$reason} ({$constraint})");

        $handler = $this->createHandler(['output' => $output]);
        $handler->output([$dependency], '', '');
    }

    public function testCurrentWorkingDirectoryIsRemovedFromReference(): void
    {
        $reference = uniqid();
        $constraint = uniqid();
        $reason = uniqid();

        $dependency = new DeclaredDependency(
            reference: getcwd() . '/' .$reference,
            constraint: $constraint,
            reason: $reason
        );

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())
            ->method('writeln')
            ->with("{$reference}: {$reason} ({$constraint})");

        $handler = $this->createHandler(['output' => $output]);
        $handler->output([$dependency], '', '');
    }
}
