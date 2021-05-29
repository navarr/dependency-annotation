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
use Navarr\Depends\Annotation\Dependency;
use Navarr\Depends\Data\DeclaredDependency;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
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

    private const INLINE_MATCH_PACKAGE = 2;
    private const INLINE_MATCH_VERSION = 3;
    private const INLINE_MATCH_REASON = 4;

    #[Dependency(
        'symfony/console',
        '^5',
        'Command\'s setName, addArgument and addOption methods as well as InputArgument\'s constants of REQUIRED and 
        VALUE_NONE',
    )]
    protected function configure(): void
    {
        $this->setName('why-block')
            ->addArgument('package', InputArgument::REQUIRED, 'Package to inspect')
            ->addArgument('version', InputArgument::REQUIRED, 'Version you want to update it to')
            ->addOption(
                self::ROOT_DEPS,
                ['r'],
                InputOption::VALUE_NONE,
                'Whether or not to search root dependencies for the @dependency annotation',
                null
            )
            ->addOption(
                self::ALL_DEPS,
                ['a'],
                InputOption::VALUE_NONE,
                'Whether or not to search all dependencies for the @dependency annotation',
                null
            );
    }

    #[Dependency('symfony/console', '^5', 'InputInterface::getOption and OutputInterface::writeln')]
    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        // TODO process dependency separation

        $packageToSearchFor = $input->getArgument('package');
        $versionToCompareTo = $input->getArgument('version');

        $classes = array_merge(
            get_declared_classes(),
            get_declared_interfaces(),
            get_declared_traits()
        );

        /** @var DeclaredDependency[][] $attributesForMerge */
        $attributesForMerge = [];

        /** @var ReflectionClassConstant[] $allConstants */
        $allConstants = [];

        /** @var ReflectionMethod[] $allMethods */
        $allMethods = [];

        /** @var ReflectionProperty[] $allProperties */
        $allProperties = [];

        /** @var ReflectionParameter[] $allMethodParameters */
        $allMethodParameters = [];

        foreach (get_defined_functions()['user'] as $function) {
            $reflection = new \ReflectionFunction($function);
            $attributes = $reflection->getAttributes(Dependency::class);
            $attributesForMerge[] = $this->processAttributes($attributes, reference: $function . '()');
        }

        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            $attributes = $reflection->getAttributes(Dependency::class);
            $attributesForMerge[] = $this->processAttributes($attributes, reference: $class);

            $allConstants[] = $reflection->getReflectionConstants();
            $allMethods[] = $reflection->getMethods();
            $allProperties[] = $reflection->getProperties();
        }

        foreach (array_merge(...$allConstants) as $constant) {
            $attributesForMerge[] = $this->processAttributes(
                $constant->getAttributes(Dependency::class),
                reference: $constant->getDeclaringClass()->getName() . '::' . $constant->getName(),
            );
        }

        foreach (array_merge(...$allMethods) as $method) {
            $attributesForMerge[] = $this->processAttributes(
                $method->getAttributes(Dependency::class),
                reference: $method->getDeclaringClass()->getName() . '::' . $method->getName() . '()',
            );
            $allMethodParameters[] = $method->getParameters();
        }

        foreach (array_merge(...$allMethodParameters) as $parameter) {
            $attributesForMerge[] = $this->processAttributes(
                $parameter->getAttributes(Dependency::class),
                reference: $parameter->getDeclaringClass()->getName()
                . '::' . $parameter->getDeclaringFunction()->getName()
                . '($' . $parameter->getName() . ')',
            );
        }

        foreach (array_merge(...$allProperties) as $property) {
            $attributesForMerge[] = $this->processAttributes(
                $property->getAttributes(Dependency::class),
                reference: $property->getDeclaringClass()->getName() . '::$' . $property->getName(),
            );
        }

        /** @var DeclaredDependency[] $attributes */
        $attributes = array_merge(...$attributesForMerge);

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
                $failingAttribute->getReference()
                . ': ' . $failingAttribute->getReason()
                . ' (' . $failingAttribute->getVersion() . ')'
            );
        }

        return 0;
    }

    /**
     * @param ReflectionAttribute[] $attributes
     * @return DeclaredDependency[]
     */
    private function processAttributes(
        array $attributes,
        ?string $reference = null,
        ?string $file = null,
        ?string $line = null,
    ): array {
        return array_map(
            static function (ReflectionAttribute $attribute) use ($reference, $file, $line) {
                /** @var Dependency $instance */
                $instance = $attribute->newInstance();
                return new DeclaredDependency(
                    $file,
                    $line,
                    $reference,
                    $instance->getPackage(),
                    $instance->getVersionConstraint(),
                    $instance->getReason(),
                );
            },
            $attributes
        );
    }

    protected function legacyExecute(InputInterface $input, OutputInterface $output): int
    {
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

        $found = false;
        foreach ($results as $file) {
            $contents = file_get_contents($file);
            if ($contents === false) {
                continue;
            }
            $matches = [];

            // Double slash comments
            preg_match_all(
                '#//\s+@(dependency|composerDependency)\s+([^:\s]+):(\S+)\s(.*)?(?=$)#im',
                $contents,
                $matches,
                PREG_OFFSET_CAPTURE
            );
            $found = $this->processMatches($matches, $input, $contents, $output, $file);

            // Slash asterisk comments.  We're cheating here and only using an asterisk as indicator.  False
            // positives possible.
            preg_match_all(
                '#\*\s+@(dependency|composerDependency)\s+([^:]+):(\S+) ?(.*)$#im',
                $contents,
                $matches,
                PREG_OFFSET_CAPTURE
            );
            $found = $this->processMatches($matches, $input, $contents, $output, $file) || $found;
        }

        if (!$found) {
            /** @var string $package */
            $package = $input->getArgument('package');
            $output->writeln('We found no documented reason for ' . $package . ' being blocked.');
        }

        return 0;
    }

    /**
     * Process any potential matches after a Regex Search for dependency annotations
     *
     * @param array<array> $matches Output of {@see preg_match_all} with PREG_OFFSET_CAPTURE flag set
     * @param InputInterface $input
     * @param string $contents Entire contents of a PHP file
     * @param OutputInterface $output
     * @param string $file Filename
     * @return bool Whether or not any matches were found in the file
     */
    protected function processMatches(
        array $matches,
        InputInterface $input,
        string $contents,
        OutputInterface $output,
        string $file
    ): bool {
        $found = false;

        $matchCount = count($matches[0]) ?? 0;
        for ($match = 0; $match < $matchCount; ++$match) {
            $package = strtolower($matches[static::INLINE_MATCH_PACKAGE][$match][0]);
            if ($package !== $input->getArgument('package')) {
                continue;
            }

            /** @var string $version */
            $version = $input->getArgument('version');

            // @dependency composer/semver:^1|^2|^3 We need the Semver::satisfies static method
            if (Semver::satisfies($version, $matches[static::INLINE_MATCH_VERSION][$match][0])) {
                continue;
            }

            $found = true;

            $pos = $matches[0][$match][1];
            $line = substr_count(mb_substr($contents, 0, $pos), "\n") + 1;

            $reason = trim($matches[static::INLINE_MATCH_REASON][$match][0]) ?? 'No reason provided';
            if (substr($reason, -2) === '*/') {
                $reason = trim(substr($reason, 0, -2));
            }

            $output->writeln(
                $file . ':' . $line . ' ' .
                $reason . ' ' .
                '(' . $matches[static::INLINE_MATCH_VERSION][$match][0] . ')'
            );
        }
        return $found;
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
                $results = static::getAllPhpFiles($realDir, $results);
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

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if ($path === false) {
                continue;
            }

            if (!is_dir($path) && substr($path, -4) === '.php') {
                $results[] = $path;
            } elseif (!in_array($value, ['.', '..'])) {
                $results = static::getAllPhpFiles($path, $results);
            }
        }

        return $results;
    }
}
