<?php
/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

namespace Navarr\Depends\Test\Controller\Cli;

use Navarr\Depends\Controller\Cli\WhyBlockCliCommand;
use Navarr\Depends\Test\Controller\AbstractWhyBlockCommandTest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

class WhyBlockCliCommandTest extends AbstractWhyBlockCommandTest
{
    public function createCommand(): Command
    {
        return new WhyBlockCliCommand();
    }
}
