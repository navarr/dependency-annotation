<?php
/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

namespace Navarr\Depends\Test\Model;

use Navarr\Depends\Model\NotifyOnIssueHandler;
use PHPUnit\Framework\TestCase;
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
}
