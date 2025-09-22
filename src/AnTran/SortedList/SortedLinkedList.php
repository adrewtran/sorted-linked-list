<?php

declare(strict_types=1);

namespace AnTran\SortedList;

use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;
use InvalidArgumentException;

/**
 * A type-safe sorted singly linked list containing either ints or strings (not both).
 *
 * Guarantees ascending order insertion. Duplicate values are allowed and kept stable.
 *
 * @implements IteratorAggregate<int,int|string>
 */
final class SortedLinkedList implements IteratorAggregate, Countable, JsonSerializable
{
    private const TYPE_INT = 'int';
    private const TYPE_STRING = 'string';

    private ?Node $head = null;

    private ?Node $tail = null;
    private int $size = 0;

    private string $type; // 'int' or 'string'

    private function __construct(string $type)
    {
        if (!in_array($type, [self::TYPE_INT, self::TYPE_STRING], true)) {
            throw new InvalidArgumentException('Type must be "int" or "string".');
        }
        $this->type = $type;
    }

    public static function forInts(): self
    {
        return new self(self::TYPE_INT);
    }

    public static function forStrings(): self
    {
        return new self(self::TYPE_STRING);
    }

    /**
     * Create a list from an array, inferring the type (throws on mixed types).
     * Empty arrays default to int type.
     *
     * @param array<int|string> $values
     */
    public static function fromArray(array $values): self
    {
        $type = self::inferTypeOrThrow($values);
        $list = new self($type);
        foreach ($values as $v) {
            $list->insert($v);
        }
        return $list;
    }

    /**
     * Create a list from any iterable with an explicit type (safe for empty iterables).
     *
     * @param iterable<int|string> $values
     * @param 'int'|'string' $type
     */
    public static function fromIterableWithType(iterable $values, string $type): self
    {
        $list = new self($type);
        foreach ($values as $v) {
            $list->insert($v);
        }
        return $list;
    }

    /**
     * Convenience: build from an array of ints.
     * @param array<int> $values
     */
    public static function fromArrayOfInts(array $values): self
    {
        return self::fromIterableWithType($values, self::TYPE_INT);
    }

    /**
     * Convenience: build from an array of strings.
     * @param array<string> $values
     */
    public static function fromArrayOfStrings(array $values): self
    {
        return self::fromIterableWithType($values, self::TYPE_STRING);
    }

    /**
     * Insert a value while maintaining sorted order.
     * Stable for duplicates (new equal values come after existing equals).
     * @param int|string $value
     */
    public function insert(int|string $value): void
    {
        $this->assertType($value);

        $new = new Node($value);

        if ($this->head === null) {
            // empty list
            $this->head = $this->tail = $new;
            $this->size = 1;
            return;
        }

        // insert at head only if strictly smaller, to keep duplicates stable
        if ($this->compare($value, $this->head->value) < 0) {
            $new->next = $this->head;
            $this->head = $new;
            $this->size++;
            return;
        }

        // general insertion: walk past all values <= new (stable)
        $prev = $this->head;
        $curr = $this->head->next;
        while ($curr !== null && $this->compare($value, $curr->value) >= 0) {
            $prev = $curr;
            $curr = $curr->next;
        }
        // insert between prev and curr
        $new->next = $curr;
        $prev->next = $new;
        if ($curr === null) {
            $this->tail = $new;
        }
        $this->size++;
    }

    /**
     * Insert many values.
     * @param iterable<int|string> $values
     */
    public function insertMany(iterable $values): void
    {
        foreach ($values as $v) {
            $this->insert($v);
        }
    }

    /**
     * Remove the first occurrence of the given value.
     * @param int|string $value
     * @return bool true if removed
     */
    public function remove(int|string $value): bool
    {
        $this->assertType($value);
        if ($this->head === null) {
            return false;
        }
        if ($this->head->value === $value) {
            $this->head = $this->head->next;
            if ($this->head === null) {
                $this->tail = null;
            }
            $this->size--;
            return true;
        }

        $prev = $this->head;
        $curr = $this->head->next;
        while ($curr !== null && $curr->value !== $value) {
            $prev = $curr;
            $curr = $curr->next;
        }
        if ($curr === null) {
            return false;
        }
        $prev->next = $curr->next;
        if ($curr === $this->tail) {
            $this->tail = $prev;
        }
        $this->size--;
        return true;
    }

    /**
     * Remove all occurrences of the given value.
     * @param int|string $value
     * @return int number of removed items
     */
    public function removeAll(int|string $value): int
    {
        $this->assertType($value);
        $removed = 0;

        // remove from head while matches
        while ($this->head !== null && $this->head->value === $value) {
            $this->head = $this->head->next;
            $this->size--;
            $removed++;
        }
        if ($this->head === null) {
            $this->tail = null;
            return $removed;
        }

        $prev = $this->head;
        $curr = $this->head->next;
        while ($curr !== null) {
            if ($curr->value === $value) {
                $prev->next = $curr->next;
                if ($curr === $this->tail) {
                    $this->tail = $prev;
                }
                $this->size--;
                $removed++;
                $curr = $prev->next;
            } else {
                $prev = $curr;
                $curr = $curr->next;
            }
        }

        return $removed;
    }

    public function clear(): void
    {
        $this->head = $this->tail = null;
        $this->size = 0;
    }

    public function isEmpty(): bool
    {
        return $this->size === 0;
    }

    public function first(): int|string|null
    {
        return $this->head?->value;
    }

    public function last(): int|string|null
    {
        return $this->tail?->value;
    }

    public function contains(int|string $value): bool
    {
        $this->assertType($value);
        foreach ($this as $v) {
            if ($v === $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return list<int|string>
     */
    public function toArray(): array
    {
        $out = [];
        foreach ($this as $v) {
            $out[] = $v;
        }
        return $out;
    }

    public function count(): int
    {
        return $this->size;
    }

    /**
     * @return Traversable<int,int|string>
     */
    public function getIterator(): Traversable
    {
        $curr = $this->head;
        while ($curr !== null) {
            yield $curr->value;
            $curr = $curr->next;
        }
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * @param list<int|string> $values
     * @return 'int'|'string'
     */
    private static function inferTypeOrThrow(array $values): string
    {
        $type = null;
        foreach ($values as $v) {
            $current = is_int($v) ? self::TYPE_INT : (is_string($v) ? self::TYPE_STRING : null);
            if ($current === null) {
                throw new InvalidArgumentException('Only int or string values are supported.');
            }
            if ($type === null) {
                $type = $current;
            } elseif ($type !== $current) {
                throw new InvalidArgumentException('Mixed types detected. Use only ints or only strings.');
            }
        }
        return $type ?? self::TYPE_INT; // default if empty
    }

    private function assertType(int|string $value): void
    {
        $isOk = ($this->type === self::TYPE_INT && is_int($value))
            || ($this->type === self::TYPE_STRING && is_string($value));
        if (!$isOk) {
            throw new InvalidArgumentException('Value type does not match list type: ' . $this->type);
        }
    }

    /**
     * @param int|string $a
     * @param int|string $b
     */
    private function compare(int|string $a, int|string $b): int
    {
        if ($this->type === self::TYPE_INT) {
            return $a <=> $b;
        }

        return strcmp($a, $b);
    }
}

