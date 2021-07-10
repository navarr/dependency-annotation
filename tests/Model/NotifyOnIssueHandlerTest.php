<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

namespace Navarr\Depends\Test\Model;

use Navarr\Depends\IssueHandler\NotifyOnIssueHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NotifyOnIssueHandlerTest extends TestCase
{
    public function testDescriptionIsPassedToWriteLn()
    {
        $description = uniqid();

        $outputMock = $this->createMock(OutputInterface::class);
        $outputMock->expects($this->once())
            ->method('writeln')
            ->with($this->equalTo("<error>{$description}</error>"));

        $handler = new NotifyOnIssueHandler($outputMock);
        $handler->execute($description);
    }

    public function testErrorOutputUsedIfExists()
    {
        $description = uniqid();

        $errorOutputMock = $this->createMock(OutputInterface::class);
        $errorOutputMock->expects($this->once())
            ->method('writeln')
            ->with($this->equalTo("<error>{$description}</error>"));

        $outputMock = $this->createMock(ConsoleOutputInterface::class);
        $outputMock->method('getErrorOutput')
            ->willReturn($errorOutputMock);

        $handler = new NotifyOnIssueHandler($outputMock);
        $handler->execute($description);
    }
}
