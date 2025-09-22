<?php

declare(strict_types=1);

namespace AnTran\SortedList;

/**
 * @template T of int|string
 */
final class Node
{
    /** @var T */
    public int|string $value;
    /** @var Node<T>|null $next */
    public ?Node $next = null;

    /**
     * @param T $value
     */
    public function __construct(int|string $value)
    {
        $this->value = $value;
    }
}
