# AGENTS.md — Copilot Agent Knowledge Base for voku/Arrayy

This file documents project conventions, architecture, and findings from agent interactions. It is intended to help AI coding agents work effectively in this repository.

---

## Project Overview

- **Package**: `voku/arrayy`
- **Description**: Array manipulation library for PHP
- **License**: MIT
- **Maintainer**: Lars Moelleken
- **PHP requirement**: `>=8.0.0`
- **Autoload namespace**: `Arrayy\` → `src/`
- **Helper function namespace**: `src/Create.php` (defines global `a()` helper)

---

## Directory Structure

```
src/
  Arrayy.php                          # Main class (~8000+ lines), all array methods live here
  ArrayyStrict.php                    # Strict-typed subclass of Arrayy
  ArrayyMeta.php                      # Meta/magic property helper (~67 lines)
  ArrayyIterator.php                  # Custom iterator
  ArrayyRewindableGenerator.php       # Generator-backed iteration
  ArrayyRewindableExtendedGenerator.php
  Create.php                          # Global helper functions (a(), etc.)
  StaticArrayy.php                    # Static façade using __callStatic
  Collection/                         # Collection base classes
  Type/                               # Typed collections (StringCollection, ArrayCollection, etc.)
  TypeCheck/                          # Type-checking utilities
  Mapper/                             # JSON mapper

tests/
  ArrayyTest.php                      # Main test file (~6500+ lines), all tests in one class

build/
  generate_docs.php                   # Regenerates README.md from src/Arrayy.php + build/docs/base.md
  composer.json                       # Requires voku/php-readme-helper for doc generation
  docs/base.md                        # Base template for README generation

examples/
  JsonResponseDataExample.php
```

---

## Running Tests

```bash
php vendor/bin/phpunit --no-coverage
```

- Tests use PHPUnit (supports `~6.0 || ~7.0 || ~9.0`)
- All tests are in a single class `Arrayy\tests\ArrayyTest` in `tests/ArrayyTest.php`
- Known pre-existing failures (unrelated to feature work): 2 errors (`array_sum` on strings) + 1 failure (sigma case)
- Run targeted tests with `--filter "testMethodName"` to speed up iteration

---

## Regenerating README.md

The README is **auto-generated** — do not edit it manually.

```bash
# Install build dependencies first (only needed once):
cd build && composer install && cd ..

# Also install main project dependencies (needed by generate_docs.php):
composer install --no-dev

# Generate README:
php build/generate_docs.php
```

This reads `src/Arrayy.php` docblocks and `build/docs/base.md`, then writes `README.md`.

---

## Findings from Task: README regeneration

- Rebuild the README with `php build/generate_docs.php`
- That script requires both:
  - `vendor/autoload.php`
  - `build/vendor/autoload.php`
- On this branch, rerunning the generator completed successfully and did not change `README.md`
- `AGENTS.md` is manually maintained and should be updated separately after README regeneration when task-specific context needs to be preserved

---

## Adding New Methods

All public methods in `src/Arrayy.php` follow this pattern:

1. **PHPDoc block** with:
   - Description
   - `EXAMPLE: <code>...</code>` block (used in README generation)
   - `@param` and `@return` tags
   - `@phpstan-param` / `@phpstan-return` for generics (`TKey`, `T`)
   - `@psalm-mutation-free` if the method is immutable

2. **Method signature** — use `self` return type for immutable methods, `$this` for mutable

3. **Implementation** — prefer `$this->getGenerator()` for lazy iteration

4. **Test** in `tests/ArrayyTest.php`:
   - Add a `{method}Provider()` data provider method
   - Add a `test{Method}()` test method with `@dataProvider`
   - Place tests alphabetically relative to similar methods

5. **Rebuild README** by running `php build/generate_docs.php`

---

## Key Patterns

### Immutable vs Mutable
- Immutable methods return `static` and create a new instance via `static::create()`
- Mutable methods return `$this` and modify `$this->array` directly

### Generator-backed iteration
- Use `$this->getGenerator()` instead of `$this->array` to support lazy/generator-backed arrays
- Call `$this->generatorToArray()` first if you need direct array access

### Template types
```php
/**
 * @template TKey of array-key
 * @template T
 */
class Arrayy ...
```
Use `@phpstan-param T $value` and `@phpstan-return TKey|false` etc. for PHPStan generics.

### StaticArrayy
`StaticArrayy` uses `__callStatic` to delegate all calls to an `Arrayy` instance — no changes needed there when adding instance methods.

---

## Findings from Task: `findKey()` (Issue #139)

**Problem**: No method existed to find the *key/index* of an array element by a predicate callable. The existing methods were:
- `find(\Closure $closure)` — returns the matching **value**, or `false`
- `searchIndex($value)` — returns the key for an exact **value** match
- `indexOf($value)` — alias for `searchIndex()`

**Solution**: Added `findKey(\Closure $closure)` in `src/Arrayy.php` (after `find()`):
- Accepts `\Closure` with signature `($value, $key): bool`
- Iterates via `$this->getGenerator()` for generator compatibility
- Returns the key of the first matching element, or `false`

**Usage examples**:
```php
// Find index of minimum value
$minIdx = a([3, 1, 4, 1, 5, 9])->findKey(fn($v) => $v === 1); // 1

// Find by key
a(['a' => 10, 'b' => 20])->findKey(fn($v, $k) => $k === 'b'); // 'b'

// Returns false when no match
a([1, 2, 3])->findKey(fn($v) => $v === 99); // false
```

**Test coverage added**:
- `findKeyProvider()` — 11 data-driven cases covering empty arrays, booleans, integers, floats, strings, string keys, and no-match
- `testFindKey()` — value-based matching via data provider
- `testFindKeyWithKeyParameter()` — verifies `$key` is correctly forwarded to the closure

---

## .gitattributes

Files excluded from Composer distribution (`export-ignore`):
- `/build`, `/examples`, `/tests`
- Config files: `.editorconfig`, `.scrutinizer.yml`, `.styleci.yml`, etc.
- `AGENTS.md` (this file)

---

## Dependencies

### Runtime
- `symfony/polyfill-mbstring` ~1.0
- `phpdocumentor/reflection-docblock` ~6.0

### Dev
- `phpunit/phpunit` ~6.0 || ~7.0 || ~9.0
- `phpstan/phpstan` ^2.0

### Build (doc generation only, in `build/`)
- `voku/php-readme-helper` ~0.6
