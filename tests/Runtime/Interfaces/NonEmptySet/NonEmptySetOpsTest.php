<?php

declare(strict_types=1);

namespace Tests\Runtime\Interfaces\NonEmptySet;

use Fp\Collections\HashSet;
use Fp\Collections\NonEmptyHashMap;
use Fp\Collections\NonEmptyHashSet;
use Fp\Collections\NonEmptySet;
use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Fp\Functional\Separated\Separated;
use Generator;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Bar;
use Tests\Mock\Foo;

final class NonEmptySetOpsTest extends TestCase
{
    public function testContains(): void
    {
        /** @psalm-var NonEmptyHashSet<int> $hs */
        $hs = NonEmptyHashSet::collectNonEmpty([1, 2, 2]);

        $this->assertTrue($hs->contains(1));
        $this->assertTrue($hs->contains(2));
        $this->assertFalse($hs->contains(3));

        $this->assertTrue($hs(1));
        $this->assertTrue($hs(2));
        $this->assertFalse($hs(3));
    }

    public function testUpdatedAndRemoved(): void
    {
        /** @psalm-var NonEmptyHashSet<int> $hs */
        $hs = NonEmptyHashSet::collectNonEmpty([1, 2, 2])->updated(3)->removed(1);

        $this->assertEquals([2, 3], $hs->toList());
    }

    public function testEvery(): void
    {
        $hs = NonEmptyHashSet::collectNonEmpty([0, 1, 2, 3, 4, 5]);

        $this->assertTrue($hs->every(fn($i) => $i >= 0));
        $this->assertFalse($hs->every(fn($i) => $i > 0));
    }

    public function testEveryOf(): void
    {
        $this->assertTrue(NonEmptyHashSet::collectNonEmpty([new Foo(1), new Foo(2)])->everyOf(Foo::class));
        $this->assertFalse(NonEmptyHashSet::collectNonEmpty([new Foo(1), new Bar(2)])->everyOf(Foo::class));
    }

    public function provideTestTraverseData(): Generator
    {
        yield NonEmptyHashSet::class => [
            NonEmptyHashSet::collectNonEmpty([1, 2, 3]),
            NonEmptyHashSet::collectNonEmpty([0, 1, 2]),
        ];
    }

    /**
     * @param NonEmptySet<int> $set1
     * @param NonEmptySet<int> $set2
     *
     * @dataProvider provideTestTraverseData
     */
    public function testTraverseOption(NonEmptySet $set1, NonEmptySet $set2): void
    {
        $this->assertEquals(
            Option::some($set1),
            $set1->traverseOption(fn($x) => $x >= 1 ? Option::some($x) : Option::none()),
        );
        $this->assertEquals(
            Option::none(),
            $set2->traverseOption(fn($x) => $x >= 1 ? Option::some($x) : Option::none()),
        );
        $this->assertEquals(
            Option::some($set1),
            $set1->map(fn($x) => $x >= 1 ? Option::some($x) : Option::none())->sequenceOption(),
        );
        $this->assertEquals(
            Option::none(),
            $set2->map(fn($x) => $x >= 1 ? Option::some($x) : Option::none())->sequenceOption(),
        );
    }

    /**
     * @param NonEmptySet<int> $set1
     * @param NonEmptySet<int> $set2
     *
     * @dataProvider provideTestTraverseData
     */
    public function testTraverseEither(NonEmptySet $set1, NonEmptySet $set2): void
    {
        $this->assertEquals(
            Either::right($set1),
            $set1->traverseEither(fn($x) => $x >= 1 ? Either::right($x) : Either::left('err')),
        );
        $this->assertEquals(
            Either::left('err'),
            $set2->traverseEither(fn($x) => $x >= 1 ? Either::right($x) : Either::left('err')),
        );
        $this->assertEquals(
            Either::right($set1),
            $set1->map(fn($x) => $x >= 1 ? Either::right($x) : Either::left('err'))->sequenceEither(),
        );
        $this->assertEquals(
            Either::left('err'),
            $set2->map(fn($x) => $x >= 1 ? Either::right($x) : Either::left('err'))->sequenceEither(),
        );
    }

    public function testPartition(): void
    {
        $this->assertEquals(
            Separated::create(
                HashSet::collect([3, 4, 5]),
                HashSet::collect([0, 1, 2]),
            ),
            NonEmptyHashSet::collectNonEmpty([0, 1, 2, 3, 4, 5])->partition(fn($i) => $i < 3),
        );
    }

    public function testPartitionMap(): void
    {
        $this->assertEquals(
            Separated::create(
                HashSet::collect(['L: 5']),
                HashSet::collect(['R: 0', 'R: 1', 'R: 2', 'R: 3', 'R: 4']),
            ),
            NonEmptyHashSet::collectNonEmpty([0, 1, 2, 3, 4, 5])
                ->partitionMap(fn($i) => $i >= 5 ? Either::left("L: {$i}") : Either::right("R: {$i}")),
        );
    }

    public function testExists(): void
    {
        /** @psalm-var NonEmptyHashSet<object|scalar> $hs */
        $hs = NonEmptyHashSet::collectNonEmpty([new Foo(1), 1, 1, new Foo(1)]);

        $this->assertTrue($hs->exists(fn($i) => $i === 1));
        $this->assertFalse($hs->exists(fn($i) => $i === 2));
    }

    public function testExistsOf(): void
    {
        $hs = NonEmptyHashSet::collectNonEmpty([new Foo(1), 1, 1, new Foo(1)]);

        $this->assertTrue($hs->existsOf(Foo::class));
        $this->assertFalse($hs->existsOf(Bar::class));
    }

    public function testGroupBy(): void
    {
        $this->assertEquals(
            NonEmptyHashMap::collectNonEmpty([
                'odd' => NonEmptyHashSet::collectNonEmpty([3, 1]),
                'even' => NonEmptyHashSet::collectNonEmpty([2]),
            ]),
            NonEmptyHashSet::collectNonEmpty([1, 1, 2, 2, 3, 3])
                ->groupBy(fn($i) => 0 === $i % 2 ? 'even' : 'odd'),
        );
    }

    public function testFilter(): void
    {
        $hs = NonEmptyHashSet::collectNonEmpty([new Foo(1), 1, 1, new Foo(1)]);
        $this->assertEquals([1], $hs->filter(fn($i) => $i === 1)->toList());
        $this->assertEquals([1], NonEmptyHashSet::collectNonEmpty([1, null])->filterNotNull()->toList());
    }

    public function testFilterOf(): void
    {
        $hs = NonEmptyHashSet::collectNonEmpty([new Foo(1), 1, 2, new Foo(1)]);
        $this->assertCount(1, $hs->filterOf(Foo::class));
    }

    public function testFilterMap(): void
    {
        $this->assertEquals(
            [1, 2],
            NonEmptyHashSet::collectNonEmpty(['zero', '1', '2'])
                ->filterMap(fn($e) => is_numeric($e) ? Option::some((int) $e) : Option::none())
                ->toList()
        );
    }

    public function testFirstsAndLasts(): void
    {
        $hs = NonEmptyHashSet::collectNonEmpty(['1', 2, '3']);
        $this->assertEquals('1', $hs->first(fn($i) => is_string($i))->get());
        $this->assertEquals('1', $hs->firstElement());

        $hs = NonEmptyHashSet::collectNonEmpty(['1', 2, '3']);
        $this->assertEquals('3', $hs->last(fn($i) => is_string($i))->get());
        $this->assertEquals('3', $hs->lastElement());

        $hs = NonEmptyHashSet::collectNonEmpty([$f1 = new Foo(1), 2, new Foo(2)]);
        $this->assertEquals($f1, $hs->firstOf(Foo::class)->get());
    }

    public function testFold(): void
    {
        $list = NonEmptyHashSet::collectNonEmpty(['1', '2', '3']);

        $this->assertEquals(
            '0123',
            $list->fold('0')(fn(string $acc, $e) => $acc . $e)
        );
    }

    public function testMap(): void
    {
        $this->assertEquals(
            ['2', '3', '4'],
            NonEmptyHashSet::collectNonEmpty([1, 2, 2, 3])->map(fn($e) => (string) ($e + 1))->toList()
        );
    }

    public function testMapN(): void
    {
        $tuples = [
            [1, true, true],
            [2, true, false],
            [3, false, false],
        ];

        $this->assertEquals(
            NonEmptyHashSet::collectNonEmpty([
                new Foo(1, true, true),
                new Foo(2, true, false),
                new Foo(3, false, false),
            ]),
            NonEmptyHashSet::collectNonEmpty($tuples)->mapN(Foo::create(...)),
        );
    }

    public function testFlatten(): void
    {
        $this->assertEquals(
            HashSet::collect([]),
            NonEmptyHashSet::collectNonEmpty([
                HashSet::collect([]),
                HashSet::collect([]),
                HashSet::collect([]),
            ])->flatten(),
        );
        $this->assertEquals(
            HashSet::collect([1, 2, 3, 4]),
            NonEmptyHashSet::collectNonEmpty([
                HashSet::collect([1, 1, 2]),
                HashSet::collect([2, 2, 3]),
                HashSet::collect([3, 3, 4]),
            ])->flatten(),
        );
    }

    public function testFlatMap(): void
    {
        $this->assertEquals(
            [1, 2, 3, 4, 5, 6],
            NonEmptyHashSet::collectNonEmpty([2, 5])
                ->flatMap(fn($e) => [$e - 1, $e, $e + 1])
                ->toList()
        );
    }

    public function testTap(): void
    {
        $this->assertEquals(
            [2, 3],
            NonEmptyHashSet::collectNonEmpty([new Foo(1), new Foo(2)])
                ->tap(fn(Foo $foo) => $foo->a = $foo->a + 1)
                ->map(fn(Foo $foo) => $foo->a)
                ->toList()
        );
    }

    public function testSubsetOf(): void
    {
        $set = NonEmptyHashSet::collectNonEmpty([1, 2]);

        $this->assertFalse($set->subsetOf(HashSet::collect([])));
        $this->assertTrue($set->subsetOf($set));
        $this->assertTrue($set->subsetOf(NonEmptyHashSet::collectNonEmpty([1, 2, 3])));
        $this->assertFalse(NonEmptyHashSet::collectNonEmpty([1, 2, 3])->subsetOf($set));
    }

    public function testHead(): void
    {
        $this->assertEquals(
            1,
            NonEmptyHashSet::collectNonEmpty([1, 2, 3])->head()
        );
    }

    public function testTail(): void
    {
        $this->assertEquals(
            [2, 3],
            NonEmptyHashSet::collectNonEmpty([1, 2, 3])->tail()->toList()
        );
    }

    public function testInit(): void
    {
        $this->assertEquals(
            [1, 2],
            NonEmptyHashSet::collectNonEmpty([1, 2, 3])->init()->toList()
        );
    }

    public function testIntersectAndDiff(): void
    {
        $this->assertEquals(
            [2, 3],
            NonEmptyHashSet::collectNonEmpty([1, 2, 3])
                ->intersect(HashSet::collect([2, 3]))
                ->toList()
        );

        $this->assertEquals(
            [1],
            NonEmptyHashSet::collectNonEmpty([1, 2, 3])
                ->diff(HashSet::collect([2, 3]))
                ->toList()
        );
    }

    public function testReindex(): void
    {
        $this->assertEquals(
            NonEmptyHashMap::collectPairsNonEmpty([
                ['key-1', 1],
                ['key-2', 2],
            ]),
            NonEmptyHashSet::collectNonEmpty([1, 2, 2])
                ->reindex(fn($value) => "key-{$value}"),
        );
    }
}
