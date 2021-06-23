<?php
/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Command;

use Composer\Command\BaseCommand;
use Composer\Composer;
use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Composer\Semver\Semver;
use Navarr\Attribute\Dependency;
use Navarr\Depends\Data\DeclaredDependency;
use Navarr\Depends\Model\AstParser;
use Navarr\Depends\Model\LegacyParser;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[Dependency('composer/composer', '^1|^2', 'Extends BaseCommand')]
class WhyBlockCommand extends BaseCommand
{
    private const ALL_DEPS = 'include-all-dependencies';
    private const ROOT_DEPS = 'include-root-dependencies';
    private const LEGACY_ANNOTATION = 'include-legacy-annotations';

    #[Dependency('symfony/console', '^5', 'Command\'s setName, addArgument and addOption methods as well as InputArgument\'s constants of REQUIRED and VALUE_NONE',)]
    protected function configure(): void
    {
        $this->setName('why-block')
            ->addArgument('package', InputArgument::REQUIRED, 'Package to inspect')
            ->addArgument('version', InputArgument::REQUIRED, 'Version you want to update it to')
            ->addOption(
                self::LEGACY_ANNOTATION,
                ['l'],
                InputOption::VALUE_NONE,
                'Include old @dependency/@composerDependency annotations in search'
            )
            ->addOption(
                self::ROOT_DEPS,
                ['r'],
                InputOption::VALUE_NONE,
                'Search root dependencies for the @dependency annotation'
            )
            ->addOption(
                self::ALL_DEPS,
                ['a'],
                InputOption::VALUE_NONE,
                'Search all dependencies for the @dependency annotation'
            );
    }

    #[Dependency('symfony/console', '^5', 'InputInterface::getOption and OutputInterface::writeln')]
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $packageToSearchFor = $input->getArgument('package');
        $versionToCompareTo = $input->getArgument('version');

        /** @var Composer $composer required indicates it can never be null. */
        $composer = $this->getComposer(true);

        // Always check the base files
        $results = static::getAllFilesForAutoload('.', $composer->getPackage()->getAutoload());

        $packages = [];
        // If we're checking dependencies, grab all packages
        if ($input->getOption(self::ROOT_DEPS) || $input->getOption(self::ALL_DEPS)) {
            $packages = $composer->getRepositoryManager()->getLocalRepository()->getPackages();
        }

        // If we're only checking root dependencies, determine them and filter down `$packages`
        if ($input->getOption(self::ROOT_DEPS)) {
            $requires = array_map(
                static function (Link $link) {
                    return $link->getTarget();
                },
                $composer->getPackage()->getRequires()
            );
            $packages = array_filter(
                $packages,
                static function (PackageInterface $package) use ($requires) {
                    return in_array($package->getName(), $requires, true);
                }
            );
        }

        // Find all files for the packages
        foreach ($packages as $package) {
            $path = 'vendor' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $package->getName());
            $results = static::getAllFilesForAutoload($path, $package->getAutoload(), $results);
        }

        $parsers = [];
        $parsers[] = new AstParser();
        if ($input->getOption(self::LEGACY_ANNOTATION)) {
            $parsers[] = new LegacyParser();
        }

        $attributes = [[]];
        foreach ($results as $file) {
            foreach ($parsers as $parser) {
                $attributes[] = $parser->parse($file);
            }
        }
        $attributes = array_merge(...$attributes);

        /** @var DeclaredDependency[] $failingAttributes Declarations of the provided package that don't match the
         * version requirement
         */
        $failingAttributes = array_filter(
            $attributes,
            static function (DeclaredDependency $attribute) use ($packageToSearchFor, $versionToCompareTo) {
                return strtolower($attribute->getPackage()) === strtolower($packageToSearchFor)
                    && !Semver::satisfies($versionToCompareTo, $attribute->getVersion());
            }
        );

        foreach ($failingAttributes as $failingAttribute) {
            $output->writeln(
                str_replace(getcwd().'/', '', $failingAttribute->getReference())
                . ': ' . $failingAttribute->getReason()
                . ' (' . $failingAttribute->getVersion() . ')'
            );
        }

        if (count($failingAttributes) < 1) {
            $package = $input->getArgument('package');
            $version = $input->getArgument('version');
            $output->writeln("We found no documented reason for {$package} v{$version} being blocked.");
        }

        return 0;
    }

    /**
     * Retrieve all PHP files out of the directories and files listed in the autoload directive
     *
     * @param string $basePath Base directory of the package who's autoload we're processing
     * @param array<array> $autoload Result of {@see PackageInterface::getAutoload()}
     * @param string[] $results Array of file paths to merge with
     * @return string[] File paths
     */
    private static function getAllFilesForAutoload(string $basePath, array $autoload, array $results = []): array
    {
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
                    $results = static::getAllPhpFiles($realDir, $results);
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
    private static function getAllPhpFiles(string $dir, array $results = []): array
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

            if (!is_dir($path) && substr($path, -4) === '.php') {
                $results[] = $path;
            } elseif (is_dir($path) && !in_array($value, ['.', '..'])) {
                $results = static::getAllPhpFiles($path, $results);
            }
        }

        return $results;
    }
}
