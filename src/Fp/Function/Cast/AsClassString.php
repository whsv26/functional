<?php

declare(strict_types=1);

namespace Fp\Function\Cast;

use Fp\Functional\Option\Option;

/**
 * @psalm-return Option<class-string>
 */
function asClassString(string $potential): Option
{
    return Option::of(class_exists($potential) ? $potential : null);
}
