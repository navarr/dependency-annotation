<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\ScopeDeterminer;

use Composer\Composer;
use Composer\Package\Link;
use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;
use Navarr\Attribute\Dependency;
use RuntimeException;

// phpcs:ignore Generic.Files.LineLength.TooLong -- Attribute support pre PHP 8
#[Dependency('composer/composer', '^2', 'Repository Manager, Local Repository, and utilizing it to search for packages and retrieve their constraints')]
class ComposerScopeDeterminer implements ScopeDeterminerInterface
{
    public const SCOPE_PROJECT_ONLY = 1;
    public const SCOPE_ROOT_DEPENDENCIES = 2;
    public const SCOPE_ALL_DEPENDENCIES = 3;

    /** @var Composer */
    private $composer;

    /** @var PhpFileDeterminer */
    private $phpFileDeterminer;

    /** @var int */
    private $scope;

    public function __construct(
        Composer $composer,
        PhpFileDeterminer $phpFileDeterminer,
        #[ExpectedValues(valuesFromClass: ComposerScopeDeterminer::class)]
        int $scope = self::SCOPE_PROJECT_ONLY
    ) {
        $this->composer = $composer;
        $this->phpFileDeterminer = $phpFileDeterminer;
        $this->scope = $scope;
    }

    #[Pure]
    #[Dependency('composer/composer', '^2', 'Composer primary class getPackage')]
    #[Dependency('composer/composer', '^2', 'RootPackageInterface getAutoload and getRequires')]
    public function getFiles(): array
    {
        $package = $this->composer->getPackage();

        $autoload = $package->getAutoload();
        $files = $this->getAllFilesForAutoload('.', $autoload);

        /** @var Link[] $packages */
        $packages = [];
        if ($this->scope >= static::SCOPE_ROOT_DEPENDENCIES) {
            $packages = $package->getRequires();
        }

        if ($this->scope >= static::SCOPE_ALL_DEPENDENCIES) {
            for ($i = 0; $i < count($packages); ++$i) {
                $package = $packages[$i];
                $requirements = $this->getRequirementsForPackage($package);

                // I wonder if this is better or worse than recursive
                $packages = array_merge(
                    $packages,
                    array_diff($requirements, $packages)
                );
            }
        }

        foreach ($packages as $package) {
            $path = 'vendor' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $package->getTarget());

            $foundPackage = $this->composer->getRepositoryManager()
                ->getLocalRepository()
                ->findPackage($package->getTarget(), $package->getConstraint());

            if (!$foundPackage) {
                continue;
            }

            $this->getAllFilesForAutoload(
                $path,
                $foundPackage->getAutoload(),
                $files
            );
        }

        return array_unique($files);
    }

    /**
     * Get all packages required by a package
     *
     * @param Link $package
     * @return Link[]
     */
    private function getRequirementsForPackage(Link $package): array
    {
        $package = $this->composer->getRepositoryManager()
            ->getLocalRepository()
            ->findPackage($package->getTarget(), $package->getConstraint());

        return $package === null ? [] : $package->getRequires();
    }

    /**
     * Retrieve all PHP files out of the directories and files listed in the autoload directive
     *
     * @param string $basePath Base directory of the package who's autoload we're processing
     * @param array<array> $autoload Result of {@see PackageInterface::getAutoload()}
     * @param string[] $results Array of file paths to merge with
     * @return string[] File paths
     */
    #[Pure]
    private function getAllFilesForAutoload(
        string $basePath,
        array $autoload,
        array $results = []
    ): array {
        foreach ($autoload as $map) {
            foreach ($map as $dir) {
                $realDir = realpath($basePath . DIRECTORY_SEPARATOR . $dir);
                if ($realDir === false) {
                    continue;
                }
                if (is_file($realDir)) {
                    $results[] = $realDir;
                    continue;
                }
                if (is_dir($realDir)) {
                    $results = $this->getAllPhpFiles($realDir, $results);
                }
            }
        }
        return $results;
    }

    /**
     * Find all PHP files by recursively searching a directory
     *
     * @param string $dir Directory to search recursively
     * @param string[] $results Array of file paths to merge with
     * @return string[] File paths
     */
    private function getAllPhpFiles(string $dir, array $results = []): array
    {
        $files = scandir($dir);
        if ($files === false) {
            return $results;
        }

        foreach ($files as $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if ($path === false) {
                continue;
            }

            if (is_file($path) && $this->phpFileDeterminer->isPhp($path)) {
                $results[] = $path;
            } elseif (is_dir($path) && !in_array($value, ['.', '..'])) {
                $results = $this->getAllPhpFiles($path, $results);
            }
        }

        return $results;
    }
}
