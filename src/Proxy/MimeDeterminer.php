<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Proxy;

use JetBrains\PhpStorm\Pure;
use Navarr\Attribute\Dependency;

class MimeDeterminer
{
    /**
     * @return false|string
     */
    #[Pure]
    #[Dependency('ext-fileinfo', required: false, reason: 'Usage of mime_content_type if available')]
    public function getMimeType(string $path)
    {
        if (function_exists('get_mime_type')) {
            return mime_content_type($path);
        } else {
            return false;
        }
    }
}
