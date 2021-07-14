<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test\Controller\Composer;

use Navarr\Depends\Controller\Composer\WhyBlockComposerCommand;
use Navarr\Depends\Test\Controller\AbstractWhyBlockCommandTest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

class WhyBlockComposerCommandTest extends AbstractWhyBlockCommandTest
{
    public function createCommand(): Command
    {
        return new WhyBlockComposerCommand();
    }
}
