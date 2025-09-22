# 🔗 Sorted Linked List (PHP 8.3)

[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777bb4?logo=php)](https://www.php.net/)
[![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)](#running-code-coverage)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](#license)

A type-safe, always-sorted singly linked list for ints or strings with stable handling of duplicates.

- 🔒 Type-safe: fixed to int or string per list (no mixing)
- 📈 Always sorted (ascending) with stable duplicates
- 🧰 Ergonomic API: insertMany, removeAll, isEmpty, first, last, contains
- 🔁 Iterable and JSON-serializable
- 🧪 100% unit test coverage with Docker/Composer setup

## 🚀 Quick start

```bash
docker compose up --build
# or locally
composer install
```

## ✨ Features

- Stable ordering for duplicates on insert
- Bulk operations: `insertMany`, `removeAll`, `clear`, `isEmpty`
- Simple factories:
  - `forInts()`, `forStrings()`
  - `fromArray(...)` (type inferred; empty defaults to int)
  - `fromArrayOfInts(...)`, `fromArrayOfStrings(...)`
  - `fromIterableWithType(iterable, 'int'|'string')` (explicit type, great for generators and empty iterables)
- Implements `IteratorAggregate`, `Countable`, `JsonSerializable`

## 🆕 What's new

- Added stable duplicate handling during insert (equal values keep insertion order)
- New helpers: `insertMany`, `removeAll`, `isEmpty`
- Safer construction options for empty inputs and iterables

## 🧪 Running tests

You can run the unit tests with Docker (recommended) or locally.

### 🐳 With Docker (includes code coverage)
- Build the image
- Run the test suite (the compose file runs `composer test` inside the container)

Example commands:
- `docker compose build --no-cache app`
- `docker compose up --abort-on-container-exit`

### 💻 Locally
- Install dependencies: `composer install`
- If you don't have a coverage driver installed, use: `composer run test-no-coverage`
- If you have Xdebug or PCOV enabled, you can use: `composer test`

## 📊 Running code coverage

You can generate an HTML coverage report (output to `var/coverage`) using Docker or locally.

- With Docker (recommended):
  - `docker compose run --rm app bash -lc "composer install && composer coverage"`
  - Open `var/coverage/index.html` in your browser.

- Locally:
  - Ensure you have a coverage driver (PCOV or Xdebug) enabled.
  - `composer coverage`
  - Open `var/coverage/index.html`.

## 📚 Usage

### ⚡ Quick examples
```php
use AnTran\SortedList\SortedLinkedList;

// Integers
$ints = SortedLinkedList::forInts();
$ints->insertMany([3, 1, 2, 2]);
$ints->remove(1);            // removes first occurrence
$ints->last();               // 3
$ints->toArray();            // [2,2,3]

// Strings
$words = SortedLinkedList::fromArrayOfStrings(['b', 'a']);
$words->insert('c');
$words->toArray();           // ['a','b','c']
```

Other handy ops: removeAll(value), isEmpty(), clear(), contains(value), first(), last()

## 🧭 API at a glance

- Construction
  - `forInts()`, `forStrings()`
  - `fromArray(array $values)`
  - `fromArrayOfInts(array $values)`, `fromArrayOfStrings(array $values)`
  - `fromIterableWithType(iterable $values, 'int'|'string')`
- Core
  - `insert(int|string $value)`, `insertMany(iterable $values)`
  - `remove(int|string $value): bool`, `removeAll(int|string $value): int`
  - `clear()`, `isEmpty(): bool`, `count(): int`
  - `first(): int|string|null`, `last(): int|string|null`, `contains(int|string $value): bool`
  - `toArray(): list<int|string>`, `jsonSerialize(): list<int|string>`
  - `getIterator(): Traversable<int,int|string>`
- Exceptions
  - `InvalidArgumentException` on mixed/unsupported types or value/type mismatch

## ⚙️ Performance notes

- Singly linked list: inserts/removes/searches are O(n)
- Optimized for incremental sorted inserts and sequential reads; arrays may be faster for random access

