<?php
/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Annotation;

use \Attribute;

/**
 * Declares a dependency on another package
 */
#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
class Dependency
{
    public function __construct(
        private string $package,
        private ?string $versionConstraint = null,
        private ?string $reason = null
    ) {
    }

    public function getPackage(): string
    {
        return $this->package;
    }

    public function getVersionConstraint(): ?string
    {
        return $this->versionConstraint;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function hasVersionConstraint(): bool
    {
        return $this->versionConstraint !== null;
    }

    public function hasReason(): bool
    {
        return $this->reason !== null;
    }
}
