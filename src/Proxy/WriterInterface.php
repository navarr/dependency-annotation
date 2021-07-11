<?php
/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

namespace Navarr\Depends\Proxy;


interface WriterInterface
{
    public function canWrite(): bool;

    public function write(string $data): void;

    /**
     * @param mixed[]
     */
    public function writeCsv(array $data): void;
}
