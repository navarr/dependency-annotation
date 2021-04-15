<?php
/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Annotation;

use Attribute;

/**
 * A PHP8 style attribute
 *
 * Our goal here is to improve with PHP8 while maintaining backwards-compatibility with PHP7.
 * To do that, we can't use any PHP8 features EXCEPT for PHP8 Annotations.
 * We can still use those, because in older versions they'll be detected as comments, when single-line
 */
#[Attribute]
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
