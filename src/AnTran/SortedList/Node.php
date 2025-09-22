<?php

declare(strict_types=1);

namespace AnTran\SortedList;

/**
 * @template T of int|string
 */
final class Node
{
    public int|string $value;

    public ?Node $next = null;

    public function __construct(int|string $value)
    {
        $this->value = $value;
    }
}

