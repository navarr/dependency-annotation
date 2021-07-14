<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test\Controller\Composer;

use Composer\Composer;
use Navarr\Depends\Controller\Composer\WhyBlockComposerCommand;
use Navarr\Depends\Test\Controller\AbstractWhyBlockCommandTest;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;

class WhyBlockComposerCommandTest extends AbstractWhyBlockCommandTest
{
    public function createCommand(): WhyBlockComposerCommand
    {
        return new WhyBlockComposerCommand();
    }

    public function testFormatNeedsNotBePassed()
    {
        $this->markTestSkipped('Not sure how to properly inject composer');

        $command = $this->createCommand();
        $composer = new Composer();
        $command->setComposer($composer);

        $input = new ArgvInput(
            ['', 'project', 'constraint'],
            $command->getDefinition()
        );
        $output = $this->createMock(OutputInterface::class);

        $result = $command->run($input, $output);
        $this->assertEquals(0, $result);
    }
}
