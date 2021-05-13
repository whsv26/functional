<?php

declare(strict_types=1);

namespace Fp\Psalm\TypeCombiner\SumType;

use Fp\Psalm\TypeCombiner\TypeCombinerInterface;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TInt;

use function Fp\Cast\asList;
use function Fp\Collection\every;
use function Fp\Collection\partition;

/**
 * @implements TypeCombinerInterface<TInt>
 */
class IntTypeCombiner implements TypeCombinerInterface
{
    /**
     * @inheritdoc
     */
    public function supports(array $types): bool
    {
        return every($types, fn(Atomic $a) => $a instanceof TInt);
    }

    /**
     * @inheritdoc
     */
    public function combine(array $types): array
    {
        [$parents, $children] = partition(
            $types,
            fn(TInt $t) => $t::class === TInt::class
        );

        return asList(empty($parents) ? $children : [$parents[0]]);
    }
}
