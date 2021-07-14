<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\ScopeDeterminer;

class DirectoryScopeDeterminer implements ScopeDeterminerInterface
{
    /** @var string */
    private $directory;

    /** @var PhpFileFinder */
    private $phpfileFinder;

    public function __construct(PhpFileFinder $phpfileFinder, string $directory = '.')
    {
        $this->directory = $directory;
        $this->phpfileFinder = $phpfileFinder;
    }

    public function getFiles(): array
    {
        return $this->phpfileFinder->findAll($this->directory);
    }
}
