<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\IssueHandler;

interface IssueHandlerInterface
{
    /**
     * Handle an issue
     */
    public function execute(string $description): void;
}
