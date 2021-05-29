<?php

namespace Navarr\Depends\Data;

use Navarr\Depends\Annotation\Dependency;

class DeclaredDependency
{
    #[Dependency('php', '^8', 'Constructor property promotion')]
    public function __construct(
        private ?string $file,
        private ?string $line,
        private ?string $reference,
        private string $package,
        private ?string $version,
        private ?string $reason
    ) {
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
