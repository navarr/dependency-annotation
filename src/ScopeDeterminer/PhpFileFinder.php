<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\ScopeDeterminer;

class PhpFileFinder
{
    /** @var PhpFileDeterminer */
    private $phpFileDeterminer;

    public function __construct(PhpFileDeterminer $phpFileDeterminer)
    {
        $this->phpFileDeterminer = $phpFileDeterminer;
    }

    /**
     * Find all PHP files by recursively searching a directory
     *
     * @param string $dir Directory to search recursively
     * @param string[] $results Array of file paths to merge with
     * @return string[] File paths
     */
    public function findAll(string $dir, array $results = []): array
    {
        // Directories is ever expanding by the loop.  We do this instead of recursion b/c I have an unhealthy fear
        // of recursion limits
        $directories = [$dir];
        for ($i = 0; $i < count($directories); ++$i) {
            $files = scandir($directories[$i]);
            if ($files !== false) {
                foreach ($files as $value) {
                    $path = realpath($directories[$i] . DIRECTORY_SEPARATOR . $value);
                    if ($path === false) {
                        continue;
                    }

                    if (is_file($path) && $this->phpFileDeterminer->isPhp($path)) {
                        $results[] = $path;
                    } elseif (is_dir($path) && !in_array($value, ['.', '..'])) {
                        $directories[] = $path;
                    }
                }
            }
        }

        return $results;
    }
}
