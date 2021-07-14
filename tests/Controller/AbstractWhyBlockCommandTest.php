<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test\Controller;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractWhyBlockCommandTest extends TestCase
{
    abstract function createCommand(): Command;

    /**
     * We don't use constants to test arguments so that when this test fails it fails b/c its a BC break
     */
    public function testCommonArgumentsAreAvailable(): Command
    {
        $command = $this->createCommand();
        $definition = $command->getDefinition();

        $this->assertTrue($definition->hasArgument('package'));
        $this->assertTrue($definition->hasArgument('version'));

        $this->assertTrue($definition->hasOption('format'));
        $this->assertTrue($definition->hasShortcut('f'));

        $this->assertTrue($definition->hasOption('fail-on-error'));
        $this->assertTrue($definition->hasShortcut('e'));

        $this->assertTrue($definition->hasOption('include-legacy-annotations'));
        $this->assertTrue($definition->hasShortcut('l'));

        return $command;
    }
}
