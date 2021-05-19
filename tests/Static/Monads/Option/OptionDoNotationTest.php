<?php

declare(strict_types=1);

namespace Tests\Static\Monads\Option;

use Fp\Functional\Option\Option;
use Fp\Functional\Unit;
use Tests\PhpBlockTestCase;

use function Fp\unit;

final class OptionDoNotationTest extends PhpBlockTestCase
{
    public function testUnitReturn(): void
    {
        $this->assertBlockType(
        /** @lang InjectablePHP */ '
                use Fp\Functional\Option\Option;
                use function Fp\unit;
                
                $result = Option::do(function () {
                    yield Option::of(false);
                    return unit();
                });
            ',
            strtr(
                'Option<Unit>',
                [
                    'Option' => Option::class,
                    'Unit' => Unit::class,
                ])
        );

        $this->assertBlockType(
        /** @lang InjectablePHP */ '
                use Fp\Functional\Option\Option;
                use function Fp\unit;
                
                $result = Option::do(function () {
                    yield Option::of(false);
                    
                    if (rand(0, 1) === 1) {
                        return 1;
                    }
                    
                    return unit();
                });
            ',
            strtr(
                'Option<1|Unit>',
                [
                    'Option' => Option::class,
                    'Unit' => Unit::class,
                ])
        );
    }
}