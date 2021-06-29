<?php
/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Model;

use Navarr\Depends\Data\DeclaredDependency;

interface ParserInterface
{
    /**
     * @param string $file
     * @return DeclaredDependency[]
     */
    public function parse(string $file): array;

    public function setIssueHandler(IssueHandlerInterface $handler): void;
}
