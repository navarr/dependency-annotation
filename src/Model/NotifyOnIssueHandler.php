<?php
/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Model;

use Navarr\Attribute\Dependency;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NotifyOnIssueHandler implements IssueHandlerInterface
{
    /** @var OutputInterface */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    #[Dependency('symfony/console', '^5', 'OutputInterface->getErrorOutput and ->writeln')]
    #[Dependency('symfony/console', '^5', 'ConsoleOutputInterface\'s existence')]
    public function execute(string $description): void
    {
        $output = $this->output instanceof ConsoleOutputInterface ? $this->output->getErrorOutput() : $this->output;
        $output->writeln("<error>{$description}</error>");
    }
}
