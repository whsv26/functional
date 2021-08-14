<?php

declare(strict_types=1);

namespace Tests\Runtime\Classes\LinkedList;

use Fp\Collections\NonEmptyLinkedList;
use Fp\Functional\Option\Option;
use PHPUnit\Framework\TestCase;

final class NonEmptyLinkedListTest extends TestCase
{
    public function testCollect(): void
    {
        $this->assertEquals(
            [1, 2, 3],
            NonEmptyLinkedList::collect([1, 2, 3])->toArray(),
        );

        $catch = Option::try(fn() => NonEmptyLinkedList::collect([]));
        $this->assertTrue($catch->isNone());
    }

    public function testCollectUnsafe(): void
    {
        $this->assertEquals(
            [1, 2, 3],
            NonEmptyLinkedList::collectUnsafe([1, 2, 3])->toArray(),
        );

        $catch = Option::try(fn() => NonEmptyLinkedList::collectUnsafe([]));
        $this->assertTrue($catch->isNone());
    }

    public function testCollectNonEmpty(): void
    {
        $this->assertEquals(
            [1, 2, 3],
            NonEmptyLinkedList::collectNonEmpty([1, 2, 3])->toArray(),
        );
    }
}