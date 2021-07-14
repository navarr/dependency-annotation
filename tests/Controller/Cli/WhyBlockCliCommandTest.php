<?php
/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

namespace Navarr\Depends\Test\Controller\Cli;

use Navarr\Depends\Controller\Cli\WhyBlockCliCommand;
use Navarr\Depends\Test\Controller\AbstractWhyBlockCommandTest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;

class WhyBlockCliCommandTest extends AbstractWhyBlockCommandTest
{
    public function createCommand(): Command
    {
        return new WhyBlockCliCommand();
    }

    public function testFormatNeedsNotBePassed(): void
    {
        $command = $this->createCommand();

        $path = realpath(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '_data', 'emptyFile.php']));

        $input = new ArgvInput(
            ['', 'project', 'constraint', $path],
            $command->getDefinition()
        );
        $output = $this->createMock(OutputInterface::class);

        $result = $command->run($input, $output);
        $this->assertEquals(0, $result);
    }
}
