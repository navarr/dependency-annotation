<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Test\Command\WhyBlockCommand\XmlOutputHandler;

use Navarr\Depends\Proxy\WriterInterface;
use RuntimeException;

class ContainerWriter implements WriterInterface
{
    /** @var string */
    private $content = '';

    public function canWrite(): bool
    {
        return true;
    }

    public function write(string $data): void
    {
        $this->content .= $data;
    }

    /**
     * @param string[] $data
     */
    public function writeCsv(array $data): void
    {
        $resource = fopen('php://memory', 'r+');
        if ($resource === false) {
            throw new RuntimeException('could not open memory for read/write');
        }
        fputcsv($resource, $data);
        rewind($resource);
        $this->content .= stream_get_contents($resource);
        fclose($resource);
    }

    public function getContents(): string
    {
        return $this->content;
    }
}
