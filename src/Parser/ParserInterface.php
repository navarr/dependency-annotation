<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

namespace Navarr\Depends\Parser;

use JetBrains\PhpStorm\ArrayShape;
use Navarr\Depends\Data\DeclaredDependency;

interface ParserInterface
{
    /**
     * @return DeclaredDependency[]
     */
    #[ArrayShape([DeclaredDependency::class])]
    public function parse(string $contents): array;
}
