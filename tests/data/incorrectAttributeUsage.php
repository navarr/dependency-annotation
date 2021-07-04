<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

use Navarr\Attribute\Dependency;

#[Dependency(reason: 'Reason')]
class TestClassC {
    public function test() {

    }
}
