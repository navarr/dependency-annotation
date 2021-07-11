<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

namespace Navarr\Depends\ScopeDeterminer;

use JetBrains\PhpStorm\ArrayShape;

interface ScopeDeterminerInterface
{
    /**
     * @return string[] List of filepaths that should be processed in this scope
     */
    #[ArrayShape(['string'])]
    public function getFiles(): array;
}
