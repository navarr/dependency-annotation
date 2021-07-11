<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

if (!function_exists('preg_last_error_msg')) {
    function preg_last_error_msg(): string
    {
        return [
            PREG_NO_ERROR => 'No error',
            PREG_INTERNAL_ERROR => 'Internal Error',
            PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit exhausted',
            PREG_RECURSION_LIMIT_ERROR => 'Recursion limit exhausted',
            PREG_BAD_UTF8_ERROR => 'Malformed UTF-8 characters, possibly incorrectly encoded',
            PREG_BAD_UTF8_OFFSET_ERROR => 'The offset did not correspond to the beginning of a valid UTF-8 code point',
            PREG_JIT_STACKLIMIT_ERROR => 'JIT stack limit exhausted',
        ][preg_last_error()];
    }
}
