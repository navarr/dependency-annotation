<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Data;

class ReferenceAdder
{
    public function add(DeclaredDependency $dependency, string $file): DeclaredDependency
    {
        return new DeclaredDependency(
            $file,
            $dependency->getLine(),
            "{$file}:{$dependency->getLine()}",
            $dependency->getPackage(),
            $dependency->getConstraint(),
            $dependency->getReason()
        );
    }
}
