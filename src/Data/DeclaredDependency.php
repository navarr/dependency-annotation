<?php

namespace Navarr\Depends\Data;

use Navarr\Depends\Annotation\Dependency;

class DeclaredDependency
{
    /**
     * @var string|null
     */
    private $file;

    /**
     * @var string|null
     */
    private $line;

    /**
     * @var string
     */
    private $package;

    /**
     * @var string|null
     */
    private $reason;

    /**
     * @var string|null
     */
    private $reference;

    /**
     * @var string|null
     */
    private $version;

    #[Dependency('php', '^8', 'Constructor property promotion')]
    public function __construct(
        ?string $file,
        ?string $line,
        ?string $reference,
        string $package,
        ?string $version,
        ?string $reason
    ) {
        $this->file = $file;
        $this->line = $line;
        $this->reference = $reference;
        $this->package = $package;
        $this->version = $version;
        $this->reason = $reason;
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

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }
}
