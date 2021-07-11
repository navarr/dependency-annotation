<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Proxy;

class StdOutWriter implements WriterInterface
{
    /** @var false|resource */
    private $resource;

    /**
     * @param false|resource $resource
     */
    public function __construct($resource = STDIN)
    {
        $this->resource = $resource;
    }

    public function canWrite(): bool
    {
        return $this->resource !== false;
    }

    public function write(string $data): void
    {
        if ($this->resource) {
            fputs($this->resource, $data);
        }
    }

    /**
     * @param string[] $data
     */
    public function writeCsv(array $data): void
    {
        if ($this->resource) {
            fputcsv($this->resource, $data);
        }
    }
}
