# PHPStan ignore blind-spot audit

This audit reviews the 504 identifier-scoped PHPStan ignore sites that existed at
the start of the audit. The sites were reviewed in batches of at most 50 so that
production generic limitations, deliberately-invalid tests, and genuine runtime
blind spots were not treated as one undifferentiated list.

## Review batches

| Batch | Original sites | Area | Confirmation and outcome |
|---:|---:|---|---|
| 1 | 1–50 | `Arrayy` construction and transformations | Confirmed most `argument.type` findings are caused by immutable methods deliberately changing keys or values while `create()` retains the receiver's templates. Found transform-return generic debt; retained locally for a separate API-typing change. |
| 2 | 51–100 | `Arrayy` mutation, replacement, sorting, slicing | Found that three `array_combine()` fallbacks were unreachable on PHP 8: mismatched inputs throw instead of returning `false`. Added explicit size guards and regression coverage. |
| 3 | 101–150 | Remaining source files and first providers | Fixed scalar dot-path traversal, invalid offset normalization, JSON mapper iterable shapes, callback nullability, mapper object-template bounds, dead type-check state, and scalar pseudo-type discovery. Provider findings were documentation gaps rather than runtime failures. |
| 4 | 151–200 | `ArrayyTest` providers and basic mutations | Confirmed provider arrays intentionally contain heterogeneous test tuples. Replaced suppressions with explicit `array<mixed>` PHPDoc rather than hiding missing iterable types. |
| 5 | 201–250 | `ArrayyTest` filtering, lookup, mapping, merging | Confirmed deliberate dynamic fixtures. `method.nonObject` sites identify generic precision debt, while runtime assertions validate the objects before use. No new runtime defect was reproduced. |
| 6 | 251–300 | `ArrayyTest` offsets, randomization, replacement | Confirmed invalid-offset and wrong-type cases are the behavior under test. Added mismatched replacement-size tests for the production defect found in batch 2. |
| 7 | 301–350 | `ArrayyTest` sorting, factories, serialization | Removed obsolete PHP `< 7.4` branches because the package requires PHP 8 or newer. Converted remaining iterable suppressions to typed PHPDoc. |
| 8 | 351–400 | `BasicArrayTest` | Confirmed most narrowed-type findings are redundant runtime assertions. Removed two no-op `assertTrue(true)` branches; retained useful runtime assertions even when PHPStan can prove them. |
| 9 | 401–450 | Typed collection and mapper tests | Confirmed `argument.type` is intentional in tests asserting runtime type rejection. Mapper invalid-target tests now state that rationale beside the ignore. Collection property findings remain generic-inference limitations backed by runtime instance assertions. |
| 10 | 451–500 | PHPStan fixtures and type-check coverage | Confirmed invalid fixtures and dynamic phpDocumentor tags are intentionally outside the analyzer's declared interfaces. Retained precise identifiers; runtime assertions cover every nullable factory result before its behavior is relied upon. |
| 11 | 501–504 | Invalid generic/type-check fixtures | Confirmed all four sites deliberately declare malformed PHPDoc or generic inheritance to exercise defensive parsing and rejection paths. |

## Classification rules

- **Fixed now:** a diagnostic exposed behavior that could throw, contradicted the
  documented contract, represented dead compatibility code, or could be expressed
  accurately without weakening analysis.
- **Expected negative test:** the line deliberately violates its declared type to
  verify a runtime `TypeError`, parser fallback, or PHPStan rejection.
- **Runtime assertion retained:** PHPStan can prove the assertion from PHPDoc, but
  the assertion still protects behavior when PHPDoc is wrong at runtime.
- **Generic precision debt:** the runtime library intentionally re-parameterizes a
  late-static collection in ways PHPStan cannot express consistently for fixed
  typed subclasses. These ignores remain local and identifier-specific so
  `reportUnmatchedIgnoredErrors` detects stale entries.

## Blind spots fixed during the audit

1. Dot-notation existence checks no longer call `array_key_exists()` on a scalar,
   and recursive traversal stops before descending through a scalar intermediate.
2. `where()` can extract fields from arrays and ordinary objects, matching its
   public behavior rather than requiring every item to be an `Arrayy` instance.
3. Replacement methods return an empty collection for mismatched key/value counts
   as documented, instead of allowing PHP 8's `array_combine()` to throw.
4. Float removal keys are normalized before array offset access.
5. JSON mapper inputs, caches, annotation maps, callback nullability, and object
   templates now have explicit types; object JSON input is converted to iterable
   public properties before traversal.
6. Obsolete PHP-version branches, a write-only property, no-op test assertions,
   and avoidable missing-iterable suppressions were removed.

The remaining ignores are therefore reviewable at their exact line and fall into
one of the expected categories above; there is no project-wide baseline.
