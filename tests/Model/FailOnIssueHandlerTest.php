<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

namespace Navarr\Depends\Test\Model;

use Navarr\Depends\IssueHandler\FailOnIssueHandler;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FailOnIssueHandlerTest extends TestCase
{
    public function testExecuteThrowsException()
    {
        $this->expectException(RuntimeException::class);

        $handler = new FailOnIssueHandler();
        $handler->execute('');
    }

    public function testThrownExceptionContainsDescription()
    {
        $handler = new FailOnIssueHandler();

        $randomMessage = uniqid();
        $this->expectExceptionMessage($randomMessage);
        $handler->execute($randomMessage);
    }
}
