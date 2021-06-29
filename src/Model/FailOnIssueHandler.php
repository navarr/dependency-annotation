<?php
/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Model;

use RuntimeException;

class FailOnIssueHandler implements IssueHandlerInterface
{
    public function execute(string $description): void
    {
        throw new RuntimeException($description);
    }
}
