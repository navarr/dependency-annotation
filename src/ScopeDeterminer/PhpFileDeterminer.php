<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\ScopeDeterminer;

use JetBrains\PhpStorm\Pure;
use Navarr\Attribute\Dependency;

class PhpFileDeterminer
{
    private const PHP_MIME_TYPES = [
        'text/x-php',
        'application/x-php',
        'application/x-httpd-php',
    ];

    private const PHP_FILE_EXTENSIONS = [
        'php',
        'phtml',
        'php3',
        'php4',
        'php5',
        // hah
        'php7',
        'php8',
    ];

    #[Pure]
    #[Dependency('ext-fileinfo', required: false, reason: 'Usage of mime_content_type if available')]
    public function isPhp(
        string $file
    ): bool {
        // There are so many approaches we could take here, but we're going with this one:

        if (function_exists('mime_content_type') && in_array(mime_content_type($file), static::PHP_MIME_TYPES)) {
            // Mime type is PHP.  That's good enough for me
            return true;
        }

        $parts = explode('.', $file);
        if (in_array(end($parts), static::PHP_FILE_EXTENSIONS)) {
            // Extension matches list - so it was probably intended to be PHP
            return true;
        }

        return false;
    }
}
