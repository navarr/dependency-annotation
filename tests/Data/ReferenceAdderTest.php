<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test\Data;

use Navarr\Depends\Data\DeclaredDependency;
use Navarr\Depends\Data\ReferenceAdder;
use PHPUnit\Framework\TestCase;

class ReferenceAdderTest extends TestCase
{
    public function testAdd()
    {
        $file = uniqid();
        $line = uniqid();
        $package = uniqid();
        $constraint = uniqid();
        $reason = uniqid();

        $dependency = new DeclaredDependency(
            line: $line,
            package: $package,
            constraint: $constraint,
            reason: $reason
        );

        $this->assertEquals($package, $dependency->getPackage());
        $this->assertEquals($constraint, $dependency->getConstraint());
        $this->assertEquals($reason, $dependency->getReason());
        $this->assertEquals($line, $dependency->getLine());
        $this->assertNull($dependency->getFile());
        $this->assertNull($dependency->getReference());

        $referenceAdder = new ReferenceAdder();
        $dependency2 = $referenceAdder->add(
            $dependency,
            $file
        );

        // Ensure original hasn't changed
        $this->assertEquals($package, $dependency->getPackage());
        $this->assertEquals($constraint, $dependency->getConstraint());
        $this->assertEquals($reason, $dependency->getReason());
        $this->assertEquals($line, $dependency->getLine());
        $this->assertNull($dependency->getFile());
        $this->assertNull($dependency->getReference());

        // Check new
        $this->assertEquals($package, $dependency2->getPackage());
        $this->assertEquals($constraint, $dependency2->getConstraint());
        $this->assertEquals($reason, $dependency2->getReason());
        $this->assertEquals($line, $dependency2->getLine());
        $this->assertEquals($file, $dependency2->getFile());
        $this->assertEquals("{$file}:{$line}", $dependency2->getReference());
    }
}
