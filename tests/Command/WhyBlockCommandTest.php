<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test\Command;

use Composer\Semver\Semver;
use Navarr\Depends\Command\WhyBlockCommand;
use Navarr\Depends\Command\WhyBlockCommand\OutputHandlerInterface;
use Navarr\Depends\Data\DeclaredDependency;
use Navarr\Depends\DeclaredDependencyAggregator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WhyBlockCommandTest extends TestCase
{
    private const TEST_PACKAGE_UNUSED = 'navarr/unused-package';
    private const TEST_PACKAGE = 'navarr/example-package';

    /**
     * @param DeclaredDependency[] $dependencies
     * @return DeclaredDependencyAggregator|MockObject
     */
    private function createAggregatorMock(array $dependencies = [])
    {
        $aggregatorMock = $this->createMock(DeclaredDependencyAggregator::class);
        $aggregatorMock->expects($this->once())
            ->method('aggregate')
            ->willReturn($dependencies);

        return $aggregatorMock;
    }

    /**
     * @param string $package
     * @param string $version
     * @param DeclaredDependency[] $expectedResult Defaults to empty
     * @return OutputHandlerInterface&MockObject
     */
    private function createOutputHandlerMock(string $package, string $version, array $expectedResult = [])
    {
        $outputHandlerMock = $this->createMock(OutputHandlerInterface::class);
        $outputHandlerMock->expects($this->once())
            ->method('output')
            ->with($expectedResult, $package, $version);

        return $outputHandlerMock;
    }

    public function testNonTargetedPackagesAreNotReturned(): void
    {
        $constraint = '>=100';

        $dependency = new DeclaredDependency(
            package: static::TEST_PACKAGE_UNUSED,
            constraint: $constraint
        );

        $failingVersion = '5';

        $this->assertFalse(Semver::satisfies($failingVersion, $constraint));

        $aggregatorMock = $this->createAggregatorMock([$dependency]);
        $outputHandlerMock = $this->createOutputHandlerMock(static::TEST_PACKAGE, $failingVersion);

        $command = new WhyBlockCommand($aggregatorMock, $outputHandlerMock);
        $command->execute(static::TEST_PACKAGE, $failingVersion);
    }

    public function testSatisfactoryConstraintsAreNotReturned(): void
    {
        $constraint = '<100';

        $dependency = new DeclaredDependency(
            package: static::TEST_PACKAGE,
            constraint: $constraint
        );

        $testVersion = '5';

        $this->assertTrue(Semver::satisfies($testVersion, $constraint));

        $aggregatorMock = $this->createAggregatorMock([$dependency]);

        $outputHandler = $this->createOutputHandlerMock(static::TEST_PACKAGE, $testVersion);

        $command = new WhyBlockCommand($aggregatorMock, $outputHandler);
        $command->execute(static::TEST_PACKAGE, $testVersion);
    }

    public function testDependencyWithoutPackageIsNotReturned(): void
    {
        $constraint = '>=100';

        $dependency = new DeclaredDependency(
            constraint: $constraint
        );

        $failingVersion = '5';

        $this->assertFalse(Semver::satisfies($failingVersion, $constraint));

        $aggregatorMock = $this->createAggregatorMock([$dependency]);
        $outputHandlerMock = $this->createOutputHandlerMock(static::TEST_PACKAGE, $failingVersion);

        $command = new WhyBlockCommand($aggregatorMock, $outputHandlerMock);
        $command->execute(static::TEST_PACKAGE, $failingVersion);
    }

    public function testDependencyWithoutConstraintIsNotReturned(): void
    {
        $constraint = '>=100';

        $dependency = new DeclaredDependency(
            package: static::TEST_PACKAGE
        );

        $failingVersion = '5';

        $this->assertFalse(Semver::satisfies($failingVersion, $constraint));

        $aggregatorMock = $this->createAggregatorMock([$dependency]);
        $outputHandlerMock = $this->createOutputHandlerMock(static::TEST_PACKAGE, $failingVersion);

        $command = new WhyBlockCommand($aggregatorMock, $outputHandlerMock);
        $command->execute(static::TEST_PACKAGE, $failingVersion);
    }

    public function testDependenciesFailingConstraintsAreReturned(): void
    {
        $failingConstraint = '>=100';

        $dependency1 = new DeclaredDependency(
            package: static::TEST_PACKAGE,
            constraint: $failingConstraint,
            reason: uniqid()
        );

        $dependency2 = new DeclaredDependency(
            package: static::TEST_PACKAGE,
            constraint: $failingConstraint,
            reason: uniqid()
        );

        $failingVersion = '5';

        $this->assertFalse(Semver::satisfies($failingVersion, $failingConstraint));

        $aggregatorMock = $this->createAggregatorMock([$dependency1, $dependency2]);
        $outputHandlerMock = $this->createOutputHandlerMock(
            static::TEST_PACKAGE,
            $failingVersion,
            [$dependency1, $dependency2]
        );

        $command = new WhyBlockCommand($aggregatorMock, $outputHandlerMock);
        $command->execute(static::TEST_PACKAGE, $failingVersion);
    }

    public function testLowercaseVersionOfPackageNamesIsUsedForMatch(): void
    {
        $failingConstraint = '>=100';

        $dependency1 = new DeclaredDependency(
            package: 'navarr/example-PACKAGE',
            constraint: $failingConstraint,
            reason: uniqid()
        );

        $dependency2 = new DeclaredDependency(
            package: 'navarr/EXAMPLE-package',
            constraint: $failingConstraint,
            reason: uniqid()
        );

        $failingVersion = '5';

        $this->assertFalse(Semver::satisfies($failingVersion, $failingConstraint));

        $aggregatorMock = $this->createAggregatorMock([$dependency1, $dependency2]);
        $outputHandlerMock = $this->createOutputHandlerMock(
            'NAVARR/example-package',
            $failingVersion,
            [$dependency1, $dependency2]
        );

        $command = new WhyBlockCommand($aggregatorMock, $outputHandlerMock);
        $command->execute('NAVARR/example-package', $failingVersion);
    }

    public function testDependenciesPassingConstraintsAreNotReturned(): void
    {
        $constraint = '<100';

        $dependency1 = new DeclaredDependency(
            package: static::TEST_PACKAGE,
            constraint: $constraint,
            reason: uniqid()
        );

        $dependency2 = new DeclaredDependency(
            package: static::TEST_PACKAGE,
            constraint: $constraint,
            reason: uniqid()
        );

        $checkedVersion = '5';

        $this->assertTrue(Semver::satisfies($checkedVersion, $constraint));

        $aggregatorMock = $this->createAggregatorMock([$dependency1, $dependency2]);
        $outputHandlerMock = $this->createOutputHandlerMock(static::TEST_PACKAGE, $checkedVersion);

        $command = new WhyBlockCommand($aggregatorMock, $outputHandlerMock);
        $command->execute(static::TEST_PACKAGE, $checkedVersion);
    }
}
