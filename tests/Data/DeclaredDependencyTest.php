<?php
/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test\Data;

use Navarr\Depends\Data\DeclaredDependency;
use PHPUnit\Framework\TestCase;

class DeclaredDependencyTest extends TestCase
{
    public function testNonDefaultValues()
    {
        $file = uniqid();
        $line = uniqid();
        $reference = uniqid();
        $package = uniqid();
        $version = uniqid();
        $reason = uniqid();

        $dependency = new DeclaredDependency(
            $file,
            $line,
            $reference,
            $package,
            $version,
            $reason,
            false
        );

        $this->assertEquals($file, $dependency->getFile());
        $this->assertEquals($line, $dependency->getLine());
        $this->assertEquals($reference, $dependency->getReference());
        $this->assertEquals($package, $dependency->getPackage());
        $this->assertEquals($version, $dependency->getConstraint());
        $this->assertEquals($reason, $dependency->getReason());
        $this->assertFalse($dependency->isRequired());
    }

    public function testDefaultValues()
    {
        $dependency = new DeclaredDependency();

        $this->assertNull($dependency->getFile());
        $this->assertNull($dependency->getLine());
        $this->assertNull($dependency->getReference());
        $this->assertNull($dependency->getPackage());
        $this->assertNull($dependency->getConstraint());
        $this->assertNull($dependency->getReason());
        $this->assertTrue($dependency->isRequired());
    }

    public function testRequiredAttributeReturnsProvidedValue()
    {
        $dependency = new DeclaredDependency(required: true);
        $this->assertTrue($dependency->isRequired());

        $dependency = new DeclaredDependency(required: false);
        $this->assertFalse($dependency->isRequired());
    }
}
