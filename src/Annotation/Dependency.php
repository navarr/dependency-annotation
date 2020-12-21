<?php
/**
 * @copyright 2020 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Annotation;

/**
 * A PHP8 style attribute
 * 
 * Our goal here is to improve with PHP8 while maintaining backwards-compatibility with PHP7.
 * To do that, we can't use any PHP8 features EXCEPT for PHP8 Annotations.
 * We can still use those, because in older versions they'll be detected as comments, when single-line
 */
#[Attribute]
class Dependency {
    /** @var string */
    private $package;
    
    /** @var string|null */
    private $versionConstraint;
    
    /** @var string|null */
    private $reason;
    
    public function __construct(string $package, ?string $versionConstraint = null, ?string $reason = null)
    {
        $this->package = $package;
        $this->versionConstraint = $versionConstraint;
        $this->reason = $reason;
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
