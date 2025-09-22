<?php

declare(strict_types=1);

namespace AnTran\SortedList\Tests\Unit;

use AnTran\SortedList\SortedLinkedList;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SortedLinkedListTest extends TestCase
{
    public function testInsertAndOrderWithInts(): void
    {
        $list = SortedLinkedList::forInts();
        $list->insert(3);
        $list->insert(1);
        $list->insert(2);
        $list->insert(2);

        self::assertSame([1,2,2,3], $list->toArray());
        self::assertSame(4, $list->count());
        self::assertSame(1, $list->first());
        self::assertSame(3, $list->last());
    }

    public function testInsertAndOrderWithStrings(): void
    {
        $list = SortedLinkedList::forStrings();
        $list->insert("banana");
        $list->insert("apple");
        $list->insert("cherry");

        self::assertSame(["apple","banana","cherry"], $list->toArray());
        self::assertTrue($list->contains("banana"));
        self::assertFalse($list->contains("durian"));
    }

    public function testInsertManyAndStability(): void
    {
        $list = SortedLinkedList::fromArray([2,2,3]);
        $list->insertMany([2,1,2]);
        self::assertSame([1,2,2,2,2,3], $list->toArray());
    }

    public function testRemove(): void
    {
        $list = SortedLinkedList::fromArray([3,1,2,2,5]);
        self::assertTrue($list->remove(2));
        self::assertSame([1,2,3,5], $list->toArray());
        self::assertTrue($list->remove(1));
        self::assertSame([2,3,5], $list->toArray());
        self::assertFalse($list->remove(100));
        self::assertSame([2,3,5], $list->toArray());
    }

    public function testRemoveAllRemovesAllOccurrencesAndUpdatesTail(): void
    {
        $list = SortedLinkedList::fromArray([1,2,2,2,3,2,4]);
        $removed = $list->removeAll(2);
        self::assertSame(4, $removed);
        self::assertSame([1,3,4], $list->toArray());
        self::assertSame(4, $list->last());

        $list2 = SortedLinkedList::fromArray([2,2]);
        self::assertSame(2, $list2->removeAll(2));
        self::assertTrue($list2->isEmpty());
        self::assertNull($list2->first());
        self::assertNull($list2->last());
    }

    public function testRemoveAllUpdatesTailWhenRemovingTailValues(): void
    {
        $list = SortedLinkedList::fromArray([1,2,3,3]);
        $removed = $list->removeAll(3);
        self::assertSame(2, $removed);
        self::assertSame([1,2], $list->toArray());
        self::assertSame(2, $list->last());
    }

    public function testFromArrayTypeEnforcement(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SortedLinkedList::fromArray([1, "bad"]);
    }

    public function testContainsAndIteration(): void
    {
        $list = SortedLinkedList::fromArray(["b","a","c"]);
        $seen = [];
        foreach ($list as $v) {
            $seen[] = $v;
        }
        self::assertSame(["a","b","c"], $seen);
        self::assertTrue($list->contains("a"));
        self::assertFalse($list->contains("z"));
    }

    public function testClearAndJsonAndIsEmpty(): void
    {
        $list = SortedLinkedList::fromArray([2,1]);
        self::assertFalse($list->isEmpty());
        self::assertSame([1,2], $list->toArray());
        $list->clear();
        self::assertTrue($list->isEmpty());
        self::assertSame(0, $list->count());
        self::assertSame([], $list->jsonSerialize());
    }

    public function testRejectWrongValueTypeOnInsert(): void
    {
        $list = SortedLinkedList::forInts();
        $this->expectException(InvalidArgumentException::class);
        $list->insert("oops");
    }

    public function testFirstAndLastOnEmptyList(): void
    {
        $list = SortedLinkedList::forInts();
        self::assertNull($list->first());
        self::assertNull($list->last());
    }

    public function testInsertAtTailUpdatesTail(): void
    {
        $list = SortedLinkedList::fromArray([1,2,3]);
        $list->insert(5); // insert greater than tail
        self::assertSame([1,2,3,5], $list->toArray());
        self::assertSame(5, $list->last());
    }

    public function testRemoveTailUpdatesTail(): void
    {
        $list = SortedLinkedList::fromArray([1,2,3]);
        self::assertTrue($list->remove(3));
        self::assertSame([1,2], $list->toArray());
        self::assertSame(2, $list->last());
    }

    public function testContainsRejectsWrongType(): void
    {
        $list = SortedLinkedList::forInts();
        $this->expectException(InvalidArgumentException::class);
        $list->contains("1");
    }

    public function testRemoveRejectsWrongType(): void
    {
        $list = SortedLinkedList::forInts();
        $this->expectException(InvalidArgumentException::class);
        $list->remove("1");
    }

    public function testFromArrayEmptyDefaultsToIntType(): void
    {
        $list = SortedLinkedList::fromArray([]);
        $list->insert(1);
        self::assertSame([1], $list->toArray());
        $this->expectException(InvalidArgumentException::class);
        $list->insert("a");
    }

    public function testFromArrayRejectsNonIntOrString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SortedLinkedList::fromArray([1.5]);
    }

    public function testRemoveFromEmptyReturnsFalse(): void
    {
        $list = SortedLinkedList::forInts();
        self::assertFalse($list->remove(1));
        self::assertNull($list->first());
        self::assertNull($list->last());
        self::assertSame([], $list->toArray());
    }

    public function testRemoveOnlyElementResetsHeadAndTail(): void
    {
        $list = SortedLinkedList::fromArray([42]);
        self::assertTrue($list->remove(42));
        self::assertSame(0, $list->count());
        self::assertNull($list->first());
        self::assertNull($list->last());
        self::assertSame([], $list->toArray());
    }

    public function testInsertEqualToHeadMaintainsStability(): void
    {
        $list = SortedLinkedList::fromArray([2,3]);
        $list->insert(2); // equal to current head — should be placed after existing equals
        self::assertSame([2,2,3], $list->toArray());
        self::assertSame(2, $list->first());
    }

    public function testFromArrayOfIntsAndStrings(): void
    {
        $ints = SortedLinkedList::fromArrayOfInts([3,1,2]);
        self::assertSame([1,2,3], $ints->toArray());

        $strings = SortedLinkedList::fromArrayOfStrings(["b","a","c"]);
        self::assertSame(["a","b","c"], $strings->toArray());
    }

    public function testFromIterableWithTypeRejectsInvalidDeclaredType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SortedLinkedList::fromIterableWithType([1,2,3], 'float');
    }

    public function testFromIterableWithTypeEmptyButTypedWorks(): void
    {
        $list = SortedLinkedList::fromIterableWithType([], 'string');
        $list->insert('a');
        self::assertSame(['a'], $list->toArray());

        $this->expectException(InvalidArgumentException::class);
        $list->insert(1); // wrong type after choosing 'string'
    }
}
