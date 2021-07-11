<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Data;

/**
 * @api
 */
class DeclaredDependency
{
    /** @var string|null */
    private $file;

    /** @var string|null */
    private $line;

    /** @var string|null */
    private $package;

    /** @var string|null */
    private $reason;

    /** @var string|null */
    private $reference;

    /** @var string|null */
    private $constraint;

    /** @var bool */
    private $required;

    public function __construct(
        ?string $file = null,
        ?string $line = null,
        ?string $reference = null,
        ?string $package = null,
        ?string $constraint = null,
        ?string $reason = null,
        bool $required = true
    ) {
        $this->file = $file;
        $this->line = $line;
        $this->reference = $reference;
        $this->package = $package;
        $this->constraint = $constraint;
        $this->reason = $reason;
        $this->required = $required;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function getLine(): ?string
    {
        return $this->line;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function getPackage(): ?string
    {
        return $this->package;
    }

    public function getConstraint(): ?string
    {
        return $this->constraint;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }
}
