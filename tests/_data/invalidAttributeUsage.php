<?php
/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

use Navarr\Attribute\Dependency;

class TestClassB
{
    public function execute()
    {
        #[Dependency('example', '5', 'Bad Attribute')]
        return;
    }
}
