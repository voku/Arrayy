<?php

declare(strict_types=1);

namespace Stringy;

use voku\helper\AntiXSS;
use voku\helper\EmailCheck;
use voku\helper\URLify;
use voku\helper\UTF8;

/**
 * Class Stringy
 */
class Stringy implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * An instance's string.
     *
     * @var string
     */
    protected $str;

    /**
     * The string's encoding, which should be one of the mbstring module's
     * supported encodings.
     *
     * @var string
     */
    protected $encoding;

    /**
     * @var UTF8
     */
    private $utf8;

    /**
     * Initializes a Stringy object and assigns both str and encoding properties
     * the supplied values. $str is cast to a string prior to assignment, and if
     * $encoding is not specified, it defaults to mb_internal_encoding(). Throws
     * an InvalidArgumentException if the first argument is an array or object
     * without a __toString method.
     *
     * @param mixed  $str      [optional] <p>Value to modify, after being cast to string. Default: ''</p>
     * @param string $encoding [optional] <p>The character encoding. Fallback: 'UTF-8'</p>
     *
     * @throws \InvalidArgumentException <p>if an array or object without a
     *                                   __toString method is passed as the first argument</p>
     */
    public function __construct($str = '', string $encoding = null)
    {
        if (\is_array($str)) {
            throw new \InvalidArgumentException(
                'Passed value cannot be an array'
            );
        }

        if (
            \is_object($str)
            &&
            !\method_exists($str, '__toString')
        ) {
            throw new \InvalidArgumentException(
                'Passed object must have a __toString method'
            );
        }

        $this->str = (string) $str;

        static $UTF8 = null;
        if ($UTF8 === null) {
            $UTF8 = new UTF8();
        }
        $this->utf8 = $UTF8;

        if ($encoding !== 'UTF-8') {
            $this->encoding = $this->utf8::normalize_encoding($encoding, 'UTF-8');
        } else {
            $this->encoding = $encoding;
        }
    }

    /**
     * Returns the value in $str.
     *
     * @return string <p>The current value of the $str property.</p>
     */
    public function __toString()
    {
        return (string) $this->str;
    }

    /**
     * Gets the substring after the first occurrence of a separator.
     * If no match is found returns new empty Stringy object.
     *
     * @param string $separator
     *
     * @return static
     */
    public function afterFirst(string $separator): self
    {
        return static::create(
            $this->utf8::str_substr_after_first_separator(
                $this->str,
                $separator,
                $this->encoding
            )
        );
    }

    /**
     * Gets the substring after the first occurrence of a separator.
     * If no match is found returns new empty Stringy object.
     *
     * @param string $separator
     *
     * @return static
     */
    public function afterFirstIgnoreCase(string $separator): self
    {
        return static::create(
            $this->utf8::str_isubstr_after_first_separator(
                $this->str,
                $separator,
                $this->encoding
            )
        );
    }

    /**
     * Gets the substring after the last occurrence of a separator.
     * If no match is found returns new empty Stringy object.
     *
     * @param string $separator
     *
     * @return static
     */
    public function afterLast(string $separator): self
    {
        return static::create(
            $this->utf8::str_substr_after_last_separator(
                $this->str,
                $separator,
                $this->encoding
            )
        );
    }

    /**
     * Gets the substring after the last occurrence of a separator.
     * If no match is found returns new empty Stringy object.
     *
     * @param string $separator
     *
     * @return static
     */
    public function afterLastIgnoreCase(string $separator): self
    {
        return static::create(
            $this->utf8::str_isubstr_after_last_separator(
                $this->str,
                $separator,
                $this->encoding
            )
        );
    }

    /**
     * Returns a new string with $string appended.
     *
     * @param string $string <p>The string to append.</p>
     *
     * @return static <p>Object with appended $string.</p>
     */
    public function append(string $string): self
    {
        return static::create($this->str . $string, $this->encoding);
    }

    /**
     * Append an password (limited to chars that are good readable).
     *
     * @param int $length <p>Length of the random string.</p>
     *
     * @return static <p>Object with appended password.</p>
     */
    public function appendPassword(int $length): self
    {
        return $this->appendRandomString(
            $length,
            '2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ!?_#'
        );
    }

    /**
     * Append an random string.
     *
     * @param int    $length        <p>Length of the random string.</p>
     * @param string $possibleChars [optional] <p>Characters string for the random selection.</p>
     *
     * @return static <p>Object with appended random string.</p>
     */
    public function appendRandomString(int $length, string $possibleChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'): self
    {
        $str = $this->utf8::get_random_string($length, $possibleChars);

        return $this->append($str);
    }

    /**
     * Append an unique identifier.
     *
     * @param int|string $entropyExtra [optional] <p>Extra entropy via a string or int value.</p>
     * @param bool       $md5          [optional] <p>Return the unique identifier as md5-hash? Default: true</p>
     *
     * @return static <p>Object with appended unique identifier as md5-hash.</p>
     */
    public function appendUniqueIdentifier($entropyExtra = '', bool $md5 = true): self
    {
        return $this->append(
            $this->utf8::get_unique_string($entropyExtra, $md5)
        );
    }

    /**
     * Returns the character at $index, with indexes starting at 0.
     *
     * @param int $index <p>Position of the character.</p>
     *
     * @return static <p>The character at $index.</p>
     */
    public function at(int $index): self
    {
        return static::create($this->utf8::char_at($this->str, $index), $this->encoding);
    }

    /**
     * Gets the substring before the first occurrence of a separator.
     * If no match is found returns new empty Stringy object.
     *
     * @param string $separator
     *
     * @return static
     */
    public function beforeFirst(string $separator): self
    {
        return static::create(
            $this->utf8::str_substr_before_first_separator(
                $this->str,
                $separator,
                $this->encoding
            )
        );
    }

    /**
     * Gets the substring before the first occurrence of a separator.
     * If no match is found returns new empty Stringy object.
     *
     * @param string $separator
     *
     * @return static
     */
    public function beforeFirstIgnoreCase(string $separator): self
    {
        return static::create(
            $this->utf8::str_isubstr_before_first_separator(
                $this->str,
                $separator,
                $this->encoding
            )
        );
    }

    /**
     * Gets the substring before the last occurrence of a separator.
     * If no match is found returns new empty Stringy object.
     *
     * @param string $separator
     *
     * @return static
     */
    public function beforeLast(string $separator): self
    {
        return static::create(
            $this->utf8::str_substr_before_last_separator(
                $this->str,
                $separator,
                $this->encoding
            )
        );
    }

    /**
     * Gets the substring before the last occurrence of a separator.
     * If no match is found returns new empty Stringy object.
     *
     * @param string $separator
     *
     * @return static
     */
    public function beforeLastIgnoreCase(string $separator): self
    {
        return static::create(
            $this->utf8::str_isubstr_before_last_separator(
                $this->str,
                $separator,
                $this->encoding
            )
        );
    }

    /**
     * Returns the substring between $start and $end, if found, or an empty
     * string. An optional offset may be supplied from which to begin the
     * search for the start string.
     *
     * @param string $start  <p>Delimiter marking the start of the substring.</p>
     * @param string $end    <p>Delimiter marking the end of the substring.</p>
     * @param int    $offset [optional] <p>Index from which to begin the search. Default: 0</p>
     *
     * @return static <p>Object whose $str is a substring between $start and $end.</p>
     */
    public function between(string $start, string $end, int $offset = null): self
    {
        /** @noinspection UnnecessaryCastingInspection */
        $str = $this->utf8::between(
            $this->str,
            $start,
            $end,
            (int) $offset,
            $this->encoding
        );

        return static::create($str, $this->encoding);
    }

    /**
     * Returns a camelCase version of the string. Trims surrounding spaces,
     * capitalizes letters following digits, spaces, dashes and underscores,
     * and removes spaces, dashes, as well as underscores.
     *
     * @return static <p>Object with $str in camelCase.</p>
     */
    public function camelize(): self
    {
        return static::create(
            $this->utf8::str_camelize($this->str, $this->encoding),
            $this->encoding
        );
    }

    /**
     * Returns the string with the first letter of each word capitalized,
     * except for when the word is a name which shouldn't be capitalized.
     *
     * @return static <p>Object with $str capitalized.</p>
     */
    public function capitalizePersonalName(): self
    {
        return static::create(
            $this->utf8::str_capitalize_name($this->str),
            $this->encoding
        );
    }

    /**
     * Returns an array consisting of the characters in the string.
     *
     * @return array <p>An array of string chars.</p>
     */
    public function chars(): array
    {
        return $this->utf8::str_split($this->str);
    }

    /**
     * Trims the string and replaces consecutive whitespace characters with a
     * single space. This includes tabs and newline characters, as well as
     * multibyte whitespace such as the thin space and ideographic space.
     *
     * @return static <p>Object with a trimmed $str and condensed whitespace.</p>
     */
    public function collapseWhitespace(): self
    {
        return static::create(
            $this->utf8::collapse_whitespace($this->str),
            $this->encoding
        );
    }

    /**
     * Returns true if the string contains $needle, false otherwise. By default
     * the comparison is case-sensitive, but can be made insensitive by setting
     * $caseSensitive to false.
     *
     * @param string $needle        <p>Substring to look for.</p>
     * @param bool   $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
     *
     * @return bool <p>Whether or not $str contains $needle.</p>
     */
    public function contains(string $needle, bool $caseSensitive = true): bool
    {
        return $this->utf8::str_contains(
            $this->str,
            $needle,
            $caseSensitive
        );
    }

    /**
     * Returns true if the string contains all $needles, false otherwise. By
     * default the comparison is case-sensitive, but can be made insensitive by
     * setting $caseSensitive to false.
     *
     * @param array $needles       <p>SubStrings to look for.</p>
     * @param bool  $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
     *
     * @return bool <p>Whether or not $str contains $needle.</p>
     */
    public function containsAll(array $needles, bool $caseSensitive = true): bool
    {
        return $this->utf8::str_contains_all(
            $this->str,
            $needles,
            $caseSensitive
        );
    }

    /**
     * Returns true if the string contains any $needles, false otherwise. By
     * default the comparison is case-sensitive, but can be made insensitive by
     * setting $caseSensitive to false.
     *
     * @param array $needles       <p>SubStrings to look for.</p>
     * @param bool  $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
     *
     * @return bool <p>Whether or not $str contains $needle.</p>
     */
    public function containsAny(array $needles, bool $caseSensitive = true): bool
    {
        return $this->utf8::str_contains_any(
            $this->str,
            $needles,
            $caseSensitive
        );
    }

    /**
     * Returns the length of the string, implementing the countable interface.
     *
     * @return int <p>The number of characters in the string, given the encoding.</p>
     */
    public function count(): int
    {
        return $this->length();
    }

    /**
     * Returns the number of occurrences of $substring in the given string.
     * By default, the comparison is case-sensitive, but can be made insensitive
     * by setting $caseSensitive to false.
     *
     * @param string $substring     <p>The substring to search for.</p>
     * @param bool   $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
     *
     * @return int
     */
    public function countSubstr(string $substring, bool $caseSensitive = true): int
    {
        return $this->utf8::substr_count_simple(
            $this->str,
            $substring,
            $caseSensitive,
            $this->encoding
        );
    }

    /**
     * Creates a Stringy object and assigns both str and encoding properties
     * the supplied values. $str is cast to a string prior to assignment, and if
     * $encoding is not specified, it defaults to mb_internal_encoding(). It
     * then returns the initialized object. Throws an InvalidArgumentException
     * if the first argument is an array or object without a __toString method.
     *
     * @param mixed  $str      [optional] <p>Value to modify, after being cast to string. Default: ''</p>
     * @param string $encoding [optional] <p>The character encoding. Fallback: 'UTF-8'</p>
     *
     * @throws \InvalidArgumentException <p>if an array or object without a
     *                                   __toString method is passed as the first argument</p>
     *
     * @return static <p>A Stringy object.</p>
     */
    public static function create($str = '', string $encoding = null): self
    {
        return new static($str, $encoding);
    }

    /**
     * Returns a lowercase and trimmed string separated by dashes. Dashes are
     * inserted before uppercase characters (with the exception of the first
     * character of the string), and in place of spaces as well as underscores.
     *
     * @return static <p>Object with a dasherized $str</p>
     */
    public function dasherize(): self
    {
        return static::create(
            $this->utf8::str_dasherize($this->str),
            $this->encoding
        );
    }

    /**
     * Returns a lowercase and trimmed string separated by the given delimiter.
     * Delimiters are inserted before uppercase characters (with the exception
     * of the first character of the string), and in place of spaces, dashes,
     * and underscores. Alpha delimiters are not converted to lowercase.
     *
     * @param string $delimiter <p>Sequence used to separate parts of the string.</p>
     *
     * @return static <p>Object with a delimited $str.</p>
     */
    public function delimit(string $delimiter): self
    {
        return static::create(
            $this->utf8::str_delimit($this->str, $delimiter),
            $this->encoding
        );
    }

    /**
     * Returns true if the string ends with $substring, false otherwise. By
     * default, the comparison is case-sensitive, but can be made insensitive
     * by setting $caseSensitive to false.
     *
     * @param string $substring     <p>The substring to look for.</p>
     * @param bool   $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
     *
     * @return bool <p>Whether or not $str ends with $substring.</p>
     */
    public function endsWith(string $substring, bool $caseSensitive = true): bool
    {
        if ($caseSensitive) {
            return $this->utf8::str_ends_with($this->str, $substring);
        }

        return $this->utf8::str_iends_with($this->str, $substring);
    }

    /**
     * Returns true if the string ends with any of $substrings, false otherwise.
     * By default, the comparison is case-sensitive, but can be made insensitive
     * by setting $caseSensitive to false.
     *
     * @param string[] $substrings    <p>Substrings to look for.</p>
     * @param bool     $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
     *
     * @return bool <p>Whether or not $str ends with $substring.</p>
     */
    public function endsWithAny(array $substrings, bool $caseSensitive = true): bool
    {
        if ($caseSensitive) {
            return $this->utf8::str_ends_with_any($this->str, $substrings);
        }

        return $this->utf8::str_iends_with_any($this->str, $substrings);
    }

    /**
     * Ensures that the string begins with $substring. If it doesn't, it's
     * prepended.
     *
     * @param string $substring <p>The substring to add if not present.</p>
     *
     * @return static <p>Object with its $str prefixed by the $substring.</p>
     */
    public function ensureLeft(string $substring): self
    {
        return static::create(
            $this->utf8::str_ensure_left($this->str, $substring),
            $this->encoding
        );
    }

    /**
     * Ensures that the string ends with $substring. If it doesn't, it's appended.
     *
     * @param string $substring <p>The substring to add if not present.</p>
     *
     * @return static <p>Object with its $str suffixed by the $substring.</p>
     */
    public function ensureRight(string $substring): self
    {
        return static::create(
            $this->utf8::str_ensure_right($this->str, $substring),
            $this->encoding
        );
    }

    /**
     * Create a escape html version of the string via "$this->utf8::htmlspecialchars()".
     *
     * @return static
     */
    public function escape(): self
    {
        return static::create(
            $this->utf8::htmlspecialchars(
                $this->str,
                \ENT_QUOTES | \ENT_SUBSTITUTE,
                $this->encoding
            ),
            $this->encoding
        );
    }

    /**
     * Create an extract from a sentence, so if the search-string was found, it try to centered in the output.
     *
     * @param string   $search
     * @param int|null $length                 [optional] <p>Default: null === text->length / 2</p>
     * @param string   $replacerForSkippedText [optional] <p>Default: …</p>
     *
     * @return static
     */
    public function extractText(string $search = '', int $length = null, string $replacerForSkippedText = '…'): self
    {
        return static::create(
            $this->utf8::extract_text(
                $this->str,
                $search,
                $length,
                $replacerForSkippedText,
                $this->encoding
            ),
            $this->encoding
        );
    }

    /**
     * Returns the first $n characters of the string.
     *
     * @param int $n <p>Number of characters to retrieve from the start.</p>
     *
     * @return static <p>Object with its $str being the first $n chars.</p>
     */
    public function first(int $n): self
    {
        return static::create(
            $this->utf8::first_char($this->str, $n, $this->encoding),
            $this->encoding
        );
    }

    /**
     * Returns the encoding used by the Stringy object.
     *
     * @return string <p>The current value of the $encoding property.</p>
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * Returns a new ArrayIterator, thus implementing the IteratorAggregate
     * interface. The ArrayIterator's constructor is passed an array of chars
     * in the multibyte string. This enables the use of foreach with instances
     * of Stringy\Stringy.
     *
     * @return \ArrayIterator <p>An iterator for the characters in the string.</p>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->chars());
    }

    /**
     * Returns true if the string contains a lower case char, false otherwise.
     *
     * @return bool <p>Whether or not the string contains a lower case character.</p>
     */
    public function hasLowerCase(): bool
    {
        return $this->utf8::has_lowercase($this->str);
    }

    /**
     * Returns true if the string contains an upper case char, false otherwise.
     *
     * @return bool <p>Whether or not the string contains an upper case character.</p>
     */
    public function hasUpperCase(): bool
    {
        return $this->utf8::has_uppercase($this->str);
    }

    /**
     * Convert all HTML entities to their applicable characters.
     *
     * @param int $flags [optional] <p>
     *                   A bitmask of one or more of the following flags, which specify how to handle quotes and
     *                   which document type to use. The default is ENT_COMPAT.
     *                   <table>
     *                   Available <i>flags</i> constants
     *                   <tr valign="top">
     *                   <td>Constant Name</td>
     *                   <td>Description</td>
     *                   </tr>
     *                   <tr valign="top">
     *                   <td><b>ENT_COMPAT</b></td>
     *                   <td>Will convert double-quotes and leave single-quotes alone.</td>
     *                   </tr>
     *                   <tr valign="top">
     *                   <td><b>ENT_QUOTES</b></td>
     *                   <td>Will convert both double and single quotes.</td>
     *                   </tr>
     *                   <tr valign="top">
     *                   <td><b>ENT_NOQUOTES</b></td>
     *                   <td>Will leave both double and single quotes unconverted.</td>
     *                   </tr>
     *                   <tr valign="top">
     *                   <td><b>ENT_HTML401</b></td>
     *                   <td>
     *                   Handle code as HTML 4.01.
     *                   </td>
     *                   </tr>
     *                   <tr valign="top">
     *                   <td><b>ENT_XML1</b></td>
     *                   <td>
     *                   Handle code as XML 1.
     *                   </td>
     *                   </tr>
     *                   <tr valign="top">
     *                   <td><b>ENT_XHTML</b></td>
     *                   <td>
     *                   Handle code as XHTML.
     *                   </td>
     *                   </tr>
     *                   <tr valign="top">
     *                   <td><b>ENT_HTML5</b></td>
     *                   <td>
     *                   Handle code as HTML 5.
     *                   </td>
     *                   </tr>
     *                   </table>
     *                   </p>
     *
     * @return static <p>Object with the resulting $str after being html decoded.</p>
     */
    public function htmlDecode(int $flags = \ENT_COMPAT): self
    {
        return static::create(
            $this->utf8::html_entity_decode(
                $this->str,
                $flags,
                $this->encoding
            ),
            $this->encoding
        );
    }

    /**
     * Convert all applicable characters to HTML entities.
     *
     * @param int $flags [optional] <p>
     *                   A bitmask of one or more of the following flags, which specify how to handle quotes and
     *                   which document type to use. The default is ENT_COMPAT.
     *                   <table>
     *                   Available <i>flags</i> constants
     *                   <tr valign="top">
     *                   <td>Constant Name</td>
     *                   <td>Description</td>
     *                   </tr>
     *                   <tr valign="top">
     *                   <td><b>ENT_COMPAT</b></td>
     *                   <td>Will convert double-quotes and leave single-quotes alone.</td>
     *                   </tr>
     *                   <tr valign="top">
     *                   <td><b>ENT_QUOTES</b></td>
     *                   <td>Will convert both double and single quotes.</td>
     *                   </tr>
     *                   <tr valign="top">
     *                   <td><b>ENT_NOQUOTES</b></td>
     *                   <td>Will leave both double and single quotes unconverted.</td>
     *                   </tr>
     *                   <tr valign="top">
     *                   <td><b>ENT_HTML401</b></td>
     *                   <td>
     *                   Handle code as HTML 4.01.
     *                   </td>
     *                   </tr>
     *                   <tr valign="top">
     *                   <td><b>ENT_XML1</b></td>
     *                   <td>
     *                   Handle code as XML 1.
     *                   </td>
     *                   </tr>
     *                   <tr valign="top">
     *                   <td><b>ENT_XHTML</b></td>
     *                   <td>
     *                   Handle code as XHTML.
     *                   </td>
     *                   </tr>
     *                   <tr valign="top">
     *                   <td><b>ENT_HTML5</b></td>
     *                   <td>
     *                   Handle code as HTML 5.
     *                   </td>
     *                   </tr>
     *                   </table>
     *                   </p>
     *
     * @return static <p>Object with the resulting $str after being html encoded.</p>
     */
    public function htmlEncode(int $flags = \ENT_COMPAT): self
    {
        return static::create(
            $this->utf8::htmlentities(
                $this->str,
                $flags,
                $this->encoding
            ),
            $this->encoding
        );
    }

    /**
     * Capitalizes the first word of the string, replaces underscores with
     * spaces, and strips '_id'.
     *
     * @return static <p>Object with a humanized $str.</p>
     */
    public function humanize(): self
    {
        return static::create(
            $this->utf8::str_humanize($this->str),
            $this->encoding
        );
    }

    /**
     * Returns the index of the first occurrence of $needle in the string,
     * and false if not found. Accepts an optional offset from which to begin
     * the search.
     *
     * @param string $needle <p>Substring to look for.</p>
     * @param int    $offset [optional] <p>Offset from which to search. Default: 0</p>
     *
     * @return false|int <p>The occurrence's <strong>index</strong> if found, otherwise <strong>false</strong>.</p>
     */
    public function indexOf(string $needle, int $offset = 0)
    {
        return $this->utf8::strpos(
            $this->str,
            $needle,
            $offset,
            $this->encoding
        );
    }

    /**
     * Returns the index of the first occurrence of $needle in the string,
     * and false if not found. Accepts an optional offset from which to begin
     * the search.
     *
     * @param string $needle <p>Substring to look for.</p>
     * @param int    $offset [optional] <p>Offset from which to search. Default: 0</p>
     *
     * @return false|int <p>The occurrence's <strong>index</strong> if found, otherwise <strong>false</strong>.</p>
     */
    public function indexOfIgnoreCase(string $needle, int $offset = 0)
    {
        return $this->utf8::stripos(
            $this->str,
            $needle,
            $offset,
            $this->encoding
        );
    }

    /**
     * Returns the index of the last occurrence of $needle in the string,
     * and false if not found. Accepts an optional offset from which to begin
     * the search. Offsets may be negative to count from the last character
     * in the string.
     *
     * @param string $needle <p>Substring to look for.</p>
     * @param int    $offset [optional] <p>Offset from which to search. Default: 0</p>
     *
     * @return false|int <p>The last occurrence's <strong>index</strong> if found, otherwise <strong>false</strong>.</p>
     */
    public function indexOfLast(string $needle, int $offset = 0)
    {
        return $this->utf8::strrpos(
            $this->str,
            $needle,
            $offset,
            $this->encoding
        );
    }

    /**
     * Returns the index of the last occurrence of $needle in the string,
     * and false if not found. Accepts an optional offset from which to begin
     * the search. Offsets may be negative to count from the last character
     * in the string.
     *
     * @param string $needle <p>Substring to look for.</p>
     * @param int    $offset [optional] <p>Offset from which to search. Default: 0</p>
     *
     * @return false|int <p>The last occurrence's <strong>index</strong> if found, otherwise <strong>false</strong>.</p>
     */
    public function indexOfLastIgnoreCase(string $needle, int $offset = 0)
    {
        return $this->utf8::strripos(
            $this->str,
            $needle,
            $offset,
            $this->encoding
        );
    }

    /**
     * Inserts $substring into the string at the $index provided.
     *
     * @param string $substring <p>String to be inserted.</p>
     * @param int    $index     <p>The index at which to insert the substring.</p>
     *
     * @return static <p>Object with the resulting $str after the insertion.</p>
     */
    public function insert(string $substring, int $index): self
    {
        return static::create(
            $this->utf8::str_insert(
                $this->str,
                $substring,
                $index,
                $this->encoding
            ),
            $this->encoding
        );
    }

    /**
     * Returns true if the string contains the $pattern, otherwise false.
     *
     * WARNING: Asterisks ("*") are translated into (".*") zero-or-more regular
     * expression wildcards.
     *
     * @credit Originally from Laravel, thanks Taylor.
     *
     * @param string $pattern <p>The string or pattern to match against.</p>
     *
     * @return bool <p>Whether or not we match the provided pattern.</p>
     */
    public function is(string $pattern): bool
    {
        if ($this->toString() === $pattern) {
            return true;
        }

        $quotedPattern = \preg_quote($pattern, '/');
        $replaceWildCards = \str_replace('\*', '.*', $quotedPattern);

        return $this->matchesPattern('^' . $replaceWildCards . '\z');
    }

    /**
     * Returns true if the string contains only alphabetic chars, false otherwise.
     *
     * @return bool <p>Whether or not $str contains only alphabetic chars.</p>
     */
    public function isAlpha(): bool
    {
        return $this->utf8::is_alpha($this->str);
    }

    /**
     * Returns true if the string contains only alphabetic and numeric chars, false otherwise.
     *
     * @return bool <p>Whether or not $str contains only alphanumeric chars.</p>
     */
    public function isAlphanumeric(): bool
    {
        return $this->utf8::is_alphanumeric($this->str);
    }

    /**
     * Returns true if the string is base64 encoded, false otherwise.
     *
     * @param bool $emptyStringIsValid
     *
     * @return bool <p>Whether or not $str is base64 encoded.</p>
     */
    public function isBase64($emptyStringIsValid = true): bool
    {
        return $this->utf8::is_base64($this->str, $emptyStringIsValid);
    }

    /**
     * Returns true if the string contains only whitespace chars, false otherwise.
     *
     * @return bool <p>Whether or not $str contains only whitespace characters.</p>
     */
    public function isBlank(): bool
    {
        return $this->utf8::is_blank($this->str);
    }

    /**
     * Returns true if the string contains a valid E-Mail address, false otherwise.
     *
     * @param bool $useExampleDomainCheck   [optional] <p>Default: false</p>
     * @param bool $useTypoInDomainCheck    [optional] <p>Default: false</p>
     * @param bool $useTemporaryDomainCheck [optional] <p>Default: false</p>
     * @param bool $useDnsCheck             [optional] <p>Default: false</p>
     *
     * @return bool <p>Whether or not $str contains a valid E-Mail address.</p>
     */
    public function isEmail(bool $useExampleDomainCheck = false, bool $useTypoInDomainCheck = false, bool $useTemporaryDomainCheck = false, bool $useDnsCheck = false): bool
    {
        return EmailCheck::isValid($this->str, $useExampleDomainCheck, $useTypoInDomainCheck, $useTemporaryDomainCheck, $useDnsCheck);
    }

    /**
     * Determine whether the string is considered to be empty.
     *
     * A variable is considered empty if it does not exist or if its value equals FALSE.
     * empty() does not generate a warning if the variable does not exist.
     *
     * @return bool <p>Whether or not $str is empty().</p>
     */
    public function isEmpty(): bool
    {
        return $this->utf8::is_empty($this->str);
    }

    /**
     * Returns true if the string contains only hexadecimal chars, false otherwise.
     *
     * @return bool <p>Whether or not $str contains only hexadecimal chars.</p>
     */
    public function isHexadecimal(): bool
    {
        return $this->utf8::is_hexadecimal($this->str);
    }

    /**
     * Returns true if the string contains HTML-Tags, false otherwise.
     *
     * @return bool <p>Whether or not $str contains HTML-Tags.</p>
     */
    public function isHtml(): bool
    {
        return $this->utf8::is_html($this->str);
    }

    /**
     * Returns true if the string is JSON, false otherwise. Unlike json_decode
     * in PHP 5.x, this method is consistent with PHP 7 and other JSON parsers,
     * in that an empty string is not considered valid JSON.
     *
     * @param bool $onlyArrayOrObjectResultsAreValid
     *
     * @return bool <p>Whether or not $str is JSON.</p>
     */
    public function isJson($onlyArrayOrObjectResultsAreValid = false): bool
    {
        return $this->utf8::is_json($this->str, $onlyArrayOrObjectResultsAreValid);
    }

    /**
     * Returns true if the string contains only lower case chars, false otherwise.
     *
     * @return bool <p>Whether or not $str contains only lower case characters.</p>
     */
    public function isLowerCase(): bool
    {
        return $this->utf8::is_lowercase($this->str);
    }

    /**
     * Returns true if the string is serialized, false otherwise.
     *
     * @return bool <p>Whether or not $str is serialized.</p>
     */
    public function isSerialized(): bool
    {
        return $this->utf8::is_serialized($this->str);
    }

    /**
     * Returns true if the string contains only lower case chars, false
     * otherwise.
     *
     * @return bool <p>Whether or not $str contains only lower case characters.</p>
     */
    public function isUpperCase(): bool
    {
        return $this->utf8::is_uppercase($this->str);
    }

    /**
     * Returns the last $n characters of the string.
     *
     * @param int $n <p>Number of characters to retrieve from the end.</p>
     *
     * @return static <p>Object with its $str being the last $n chars.</p>
     */
    public function last(int $n): self
    {
        return static::create(
            $this->utf8::str_last_char(
                $this->str,
                $n,
                $this->encoding
            ),
            $this->encoding
        );
    }

    /**
     * Gets the substring after (or before via "$beforeNeedle") the last occurrence of the "$needle".
     * If no match is found returns new empty Stringy object.
     *
     * @param string $needle       <p>The string to look for.</p>
     * @param bool   $beforeNeedle [optional] <p>Default: false</p>
     *
     * @return static
     */
    public function lastSubstringOf(string $needle, bool $beforeNeedle = false): self
    {
        return static::create(
            $this->utf8::str_substr_last($this->str, $needle, $beforeNeedle, $this->encoding),
            $this->encoding
        );
    }

    /**
     * Gets the substring after (or before via "$beforeNeedle") the last occurrence of the "$needle".
     * If no match is found returns new empty Stringy object.
     *
     * @param string $needle       <p>The string to look for.</p>
     * @param bool   $beforeNeedle [optional] <p>Default: false</p>
     *
     * @return static
     */
    public function lastSubstringOfIgnoreCase(string $needle, bool $beforeNeedle = false): self
    {
        return static::create(
            $this->utf8::str_isubstr_last($this->str, $needle, $beforeNeedle, $this->encoding),
            $this->encoding
        );
    }

    /**
     * Returns the length of the string.
     *
     * @return int <p>The number of characters in $str given the encoding.</p>
     */
    public function length(): int
    {
        return $this->utf8::strlen($this->str, $this->encoding);
    }

    /**
     * Line-Wrap the string after $limit, but also after the next word.
     *
     * @param int $limit
     *
     * @return static
     */
    public function lineWrapAfterWord(int $limit): self
    {
        return static::create(
            $this->utf8::wordwrap_per_line($this->str, $limit),
            $this->encoding
        );
    }

    /**
     * Splits on newlines and carriage returns, returning an array of Stringy
     * objects corresponding to the lines in the string.
     *
     * @return static[] <p>An array of Stringy objects.</p>
     */
    public function lines(): array
    {
        $array = $this->utf8::str_to_lines($this->str);
        foreach ($array as $i => &$value) {
            $value = static::create($value, $this->encoding);
        }

        return $array;
    }

    /**
     * Returns the longest common prefix between the string and $otherStr.
     *
     * @param string $otherStr <p>Second string for comparison.</p>
     *
     * @return static <p>Object with its $str being the longest common prefix.</p>
     */
    public function longestCommonPrefix(string $otherStr): self
    {
        return static::create(
            $this->utf8::str_longest_common_prefix(
                $this->str,
                $otherStr,
                $this->encoding
            ),
            $this->encoding
        );
    }

    /**
     * Returns the longest common substring between the string and $otherStr.
     * In the case of ties, it returns that which occurs first.
     *
     * @param string $otherStr <p>Second string for comparison.</p>
     *
     * @return static <p>Object with its $str being the longest common substring.</p>
     */
    public function longestCommonSubstring(string $otherStr): self
    {
        return static::create(
            $this->utf8::str_longest_common_substring(
                $this->str,
                $otherStr,
                $this->encoding
            ),
            $this->encoding
        );
    }

    /**
     * Returns the longest common suffix between the string and $otherStr.
     *
     * @param string $otherStr <p>Second string for comparison.</p>
     *
     * @return static <p>Object with its $str being the longest common suffix.</p>
     */
    public function longestCommonSuffix(string $otherStr): self
    {
        return static::create(
            $this->utf8::str_longest_common_suffix(
                $this->str,
                $otherStr,
                $this->encoding
            ),
            $this->encoding
        );
    }

    /**
     * Converts the first character of the string to lower case.
     *
     * @return static <p>Object with the first character of $str being lower case.</p>
     */
    public function lowerCaseFirst(): self
    {
        return static::create(
            $this->utf8::lcfirst($this->str, $this->encoding),
            $this->encoding
        );
    }

    /**
     * Returns whether or not a character exists at an index. Offsets may be
     * negative to count from the last character in the string. Implements
     * part of the ArrayAccess interface.
     *
     * @param int $offset <p>The index to check.</p>
     *
     * @return bool <p>Whether or not the index exists.</p>
     */
    public function offsetExists($offset): bool
    {
        return $this->utf8::str_offset_exists(
            $this->str,
            $offset,
            $this->encoding
        );
    }

    /**
     * Returns the character at the given index. Offsets may be negative to
     * count from the last character in the string. Implements part of the
     * ArrayAccess interface, and throws an OutOfBoundsException if the index
     * does not exist.
     *
     * @param int $offset <p>The <strong>index</strong> from which to retrieve the char.</p>
     *
     * @throws \OutOfBoundsException <p>If the positive or negative offset does not exist.</p>
     *
     * @return string <p>The character at the specified index.</p>
     */
    public function offsetGet($offset): string
    {
        return $this->utf8::str_offset_get($this->str, $offset, $this->encoding);
    }

    /**
     * Implements part of the ArrayAccess interface, but throws an exception
     * when called. This maintains the immutability of Stringy objects.
     *
     * @param int   $offset <p>The index of the character.</p>
     * @param mixed $value  <p>Value to set.</p>
     *
     * @throws \Exception <p>When called.</p>
     */
    public function offsetSet($offset, $value)
    {
        // Stringy is immutable, cannot directly set char
        /** @noinspection ThrowRawExceptionInspection */
        throw new \Exception('Stringy object is immutable, cannot modify char');
    }

    /**
     * Implements part of the ArrayAccess interface, but throws an exception
     * when called. This maintains the immutability of Stringy objects.
     *
     * @param int $offset <p>The index of the character.</p>
     *
     * @throws \Exception <p>When called.</p>
     */
    public function offsetUnset($offset)
    {
        // Don't allow directly modifying the string
        /** @noinspection ThrowRawExceptionInspection */
        throw new \Exception('Stringy object is immutable, cannot unset char');
    }

    /**
     * Pads the string to a given length with $padStr. If length is less than
     * or equal to the length of the string, no padding takes places. The
     * default string used for padding is a space, and the default type (one of
     * 'left', 'right', 'both') is 'right'. Throws an InvalidArgumentException
     * if $padType isn't one of those 3 values.
     *
     * @param int    $length  <p>Desired string length after padding.</p>
     * @param string $padStr  [optional] <p>String used to pad, defaults to space. Default: ' '</p>
     * @param string $padType [optional] <p>One of 'left', 'right', 'both'. Default: 'right'</p>
     *
     * @throws \InvalidArgumentException <p>If $padType isn't one of 'right', 'left' or 'both'.</p>
     *
     * @return static <p>Object with a padded $str.</p>
     */
    public function pad(int $length, string $padStr = ' ', string $padType = 'right'): self
    {
        return static::create(
            $this->utf8::str_pad(
                $this->str,
                $length,
                $padStr,
                $padType,
                $this->encoding
            )
        );
    }

    /**
     * Returns a new string of a given length such that both sides of the
     * string are padded. Alias for pad() with a $padType of 'both'.
     *
     * @param int    $length <p>Desired string length after padding.</p>
     * @param string $padStr [optional] <p>String used to pad, defaults to space. Default: ' '</p>
     *
     * @return static <p>String with padding applied.</p>
     */
    public function padBoth(int $length, string $padStr = ' '): self
    {
        return static::create(
            $this->utf8::str_pad_both(
                $this->str,
                $length,
                $padStr,
                $this->encoding
            )
        );
    }

    /**
     * Returns a new string of a given length such that the beginning of the
     * string is padded. Alias for pad() with a $padType of 'left'.
     *
     * @param int    $length <p>Desired string length after padding.</p>
     * @param string $padStr [optional] <p>String used to pad, defaults to space. Default: ' '</p>
     *
     * @return static <p>String with left padding.</p>
     */
    public function padLeft(int $length, string $padStr = ' '): self
    {
        return static::create(
            $this->utf8::str_pad_left(
                $this->str,
                $length,
                $padStr,
                $this->encoding
            )
        );
    }

    /**
     * Returns a new string of a given length such that the end of the string
     * is padded. Alias for pad() with a $padType of 'right'.
     *
     * @param int    $length <p>Desired string length after padding.</p>
     * @param string $padStr [optional] <p>String used to pad, defaults to space. Default: ' '</p>
     *
     * @return static <p>String with right padding.</p>
     */
    public function padRight(int $length, string $padStr = ' '): self
    {
        return static::create(
            $this->utf8::str_pad_right(
                $this->str,
                $length,
                $padStr,
                $this->encoding
            )
        );
    }

    /**
     * Returns a new string starting with $string.
     *
     * @param string $string <p>The string to append.</p>
     *
     * @return static <p>Object with appended $string.</p>
     */
    public function prepend(string $string): self
    {
        return static::create($string . $this->str, $this->encoding);
    }

    /**
     * Replaces all occurrences of $pattern in $str by $replacement.
     *
     * @param string $pattern     <p>The regular expression pattern.</p>
     * @param string $replacement <p>The string to replace with.</p>
     * @param string $options     [optional] <p>Matching conditions to be used.</p>
     * @param string $delimiter   [optional] <p>Delimiter the the regex. Default: '/'</p>
     *
     * @return static <p>Object with the result2ing $str after the replacements.</p>
     */
    public function regexReplace(string $pattern, string $replacement, string $options = '', string $delimiter = '/'): self
    {
        return static::create(
            $this->utf8::regex_replace(
                $this->str,
                $pattern,
                $replacement,
                $options,
                $delimiter
            ),
            $this->encoding
        );
    }

    /**
     * Remove html via "strip_tags()" from the string.
     *
     * @param string $allowableTags [optional] <p>You can use the optional second parameter to specify tags which should
     *                              not be stripped. Default: null
     *                              </p>
     *
     * @return static
     */
    public function removeHtml(string $allowableTags = null): self
    {
        return static::create(
            $this->utf8::remove_html($this->str, $allowableTags . ''),
            $this->encoding
        );
    }

    /**
     * Remove all breaks [<br> | \r\n | \r | \n | ...] from the string.
     *
     * @param string $replacement [optional] <p>Default is a empty string.</p>
     *
     * @return static
     */
    public function removeHtmlBreak(string $replacement = ''): self
    {
        return static::create(
            $this->utf8::remove_html_breaks($this->str, $replacement),
            $this->encoding
        );
    }

    /**
     * Returns a new string with the prefix $substring removed, if present.
     *
     * @param string $substring <p>The prefix to remove.</p>
     *
     * @return static <p>Object having a $str without the prefix $substring.</p>
     */
    public function removeLeft(string $substring): self
    {
        return static::create(
            $this->utf8::remove_left($this->str, $substring, $this->encoding),
            $this->encoding
        );
    }

    /**
     * Returns a new string with the suffix $substring removed, if present.
     *
     * @param string $substring <p>The suffix to remove.</p>
     *
     * @return static <p>Object having a $str without the suffix $substring.</p>
     */
    public function removeRight(string $substring): self
    {
        return static::create(
            $this->utf8::remove_right($this->str, $substring, $this->encoding),
            $this->encoding
        );
    }

    /**
     * Try to remove all XSS-attacks from the string.
     *
     * @return static
     */
    public function removeXss(): self
    {
        static $antiXss = null;

        if ($antiXss === null) {
            $antiXss = new AntiXSS();
        }

        $str = $antiXss->xss_clean($this->str);

        return static::create($str, $this->encoding);
    }

    /**
     * Returns a repeated string given a multiplier.
     *
     * @param int $multiplier <p>The number of times to repeat the string.</p>
     *
     * @return static <p>Object with a repeated str.</p>
     */
    public function repeat(int $multiplier): self
    {
        return static::create(
            \str_repeat($this->str, $multiplier),
            $this->encoding
        );
    }

    /**
     * Replaces all occurrences of $search in $str by $replacement.
     *
     * @param string $search        <p>The needle to search for.</p>
     * @param string $replacement   <p>The string to replace with.</p>
     * @param bool   $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
     *
     * @return static <p>Object with the resulting $str after the replacements.</p>
     */
    public function replace(string $search, string $replacement, bool $caseSensitive = true): self
    {
        if ($search === '' && $replacement === '') {
            return static::create($this->str, $this->encoding);
        }

        if ($this->str === '' && $search === '') {
            return static::create($replacement, $this->encoding);
        }

        if ($caseSensitive) {
            return static::create(
                $this->utf8::str_replace($search, $replacement, $this->str),
                $this->encoding
            );
        }

        return static::create(
            $this->utf8::str_ireplace($search, $replacement, $this->str),
            $this->encoding
        );
    }

    /**
     * Replaces all occurrences of $search in $str by $replacement.
     *
     * @param array        $search        <p>The elements to search for.</p>
     * @param array|string $replacement   <p>The string to replace with.</p>
     * @param bool         $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
     *
     * @return static <p>Object with the resulting $str after the replacements.</p>
     */
    public function replaceAll(array $search, $replacement, bool $caseSensitive = true): self
    {
        if ($caseSensitive) {
            return static::create(
                $this->utf8::str_replace($search, $replacement, $this->str),
                $this->encoding
            );
        }

        return static::create(
            $this->utf8::str_ireplace($search, $replacement, $this->str),
            $this->encoding
        );
    }

    /**
     * Replaces first occurrences of $search from the beginning of string with $replacement.
     *
     * @param string $search      <p>The string to search for.</p>
     * @param string $replacement <p>The replacement.</p>
     *
     * @return static <p>Object with the resulting $str after the replacements.</p>
     */
    public function replaceFirst(string $search, string $replacement): self
    {
        return static::create(
            $this->utf8::str_replace_first($search, $replacement, $this->str),
            $this->encoding
        );
    }

    /**
     * Replaces last occurrences of $search from the ending of string with $replacement.
     *
     * @param string $search      <p>The string to search for.</p>
     * @param string $replacement <p>The replacement.</p>
     *
     * @return static <p>Object with the resulting $str after the replacements.</p>
     */
    public function replaceLast(string $search, string $replacement): self
    {
        return static::create(
            $this->utf8::str_replace_last($search, $replacement, $this->str),
            $this->encoding
        );
    }

    /**
     * Replaces all occurrences of $search from the beginning of string with $replacement.
     *
     * @param string $search      <p>The string to search for.</p>
     * @param string $replacement <p>The replacement.</p>
     *
     * @return static <p>Object with the resulting $str after the replacements.</p>
     */
    public function replaceBeginning(string $search, string $replacement): self
    {
        return static::create(
            $this->utf8::str_replace_beginning($this->str, $search, $replacement),
            $this->encoding
        );
    }

    /**
     * Replaces all occurrences of $search from the ending of string with $replacement.
     *
     * @param string $search      <p>The string to search for.</p>
     * @param string $replacement <p>The replacement.</p>
     *
     * @return static <p>Object with the resulting $str after the replacements.</p>
     */
    public function replaceEnding(string $search, string $replacement): self
    {
        return static::create(
            $this->utf8::str_replace_ending($this->str, $search, $replacement),
            $this->encoding
        );
    }

    /**
     * Returns a reversed string. A multibyte version of strrev().
     *
     * @return static <p>Object with a reversed $str.</p>
     */
    public function reverse(): self
    {
        return static::create($this->utf8::strrev($this->str), $this->encoding);
    }

    /**
     * Truncates the string to a given length, while ensuring that it does not
     * split words. If $substring is provided, and truncating occurs, the
     * string is further truncated so that the substring may be appended without
     * exceeding the desired length.
     *
     * @param int    $length    <p>Desired length of the truncated string.</p>
     * @param string $substring [optional] <p>The substring to append if it can fit. Default: ''</p>
     * @param bool   $ignoreDoNotSplitWordsForOneWord
     *
     * @return static <p>Object with the resulting $str after truncating.</p>
     */
    public function safeTruncate(int $length, string $substring = '', bool $ignoreDoNotSplitWordsForOneWord = true): self
    {
        return static::create(
            $this->utf8::str_truncate_safe(
                $this->str,
                $length,
                $substring,
                $this->encoding,
                $ignoreDoNotSplitWordsForOneWord
            ),
            $this->encoding
        );
    }

    /**
     * Shorten the string after $length, but also after the next word.
     *
     * @param int    $length
     * @param string $strAddOn [optional] <p>Default: '…'</p>
     *
     * @return static
     */
    public function shortenAfterWord(int $length, string $strAddOn = '…'): self
    {
        return static::create(
            $this->utf8::str_limit_after_word($this->str, $length, $strAddOn),
            $this->encoding
        );
    }

    /**
     * A multibyte string shuffle function. It returns a string with its
     * characters in random order.
     *
     * @return static <p>Object with a shuffled $str.</p>
     */
    public function shuffle(): self
    {
        return static::create($this->utf8::str_shuffle($this->str), $this->encoding);
    }

    /**
     * Returns the substring beginning at $start, and up to, but not including
     * the index specified by $end. If $end is omitted, the function extracts
     * the remaining string. If $end is negative, it is computed from the end
     * of the string.
     *
     * @param int $start <p>Initial index from which to begin extraction.</p>
     * @param int $end   [optional] <p>Index at which to end extraction. Default: null</p>
     *
     * @return static <p>Object with its $str being the extracted substring.</p>
     */
    public function slice(int $start, int $end = null): self
    {
        return static::create(
            $this->utf8::str_slice($this->str, $start, $end, $this->encoding),
            $this->encoding
        );
    }

    /**
     * Converts the string into an URL slug. This includes replacing non-ASCII
     * characters with their closest ASCII equivalents, removing remaining
     * non-ASCII and non-alphanumeric characters, and replacing whitespace with
     * $replacement. The replacement defaults to a single dash, and the string
     * is also converted to lowercase. The language of the source string can
     * also be supplied for language-specific transliteration.
     *
     * @param string $replacement The string used to replace whitespace
     * @param string $language    Language of the source string
     *
     * @return static Object whose $str has been converted to an URL slug
     */
    public function slugify(string $replacement = '-', string $language = 'en'): self
    {
        $stringy = self::create($this->str);

        $split = \preg_split('/[-_]/', $language);
        $language = \strtolower($split[0]);
        $languageSpecific = [
            'de' => [['ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü'], ['ae', 'oe', 'ue', 'AE', 'OE', 'UE']],
            'bg' => [
                ['х', 'Х', 'щ', 'Щ', 'ъ', 'Ъ', 'ь', 'Ь'],
                ['h', 'H', 'sht', 'SHT', 'a', 'А', 'y', 'Y'],
            ],
        ];
        if (!empty($languageSpecific[$language])) {
            $stringy->str = \str_replace($languageSpecific[$language][0], $languageSpecific[$language][1], $stringy->str);
        }

        foreach ($this->charsArray() as $key => $value) {
            $stringy->str = \str_replace($value, $key, $stringy->str);
        }
        $stringy->str = \str_replace('@', $replacement, $stringy->str);

        $stringy->str = \preg_replace(
            '/[^a-zA-Z\d\s\-_' . \preg_quote($replacement, '/') . ']/u',
            '',
            $stringy->str
        );
        $stringy->str = \preg_replace("/^['\s']+|['\s']+\$/", '', \strtolower($stringy->str));
        $stringy->str = \preg_replace('/\B([A-Z])/', '/-\1/', $stringy->str);
        $stringy->str = \preg_replace('/[-_\s]+/', $replacement, $stringy->str);

        $l = \strlen($replacement);
        if (\strpos($stringy->str, $replacement) === 0) {
            $stringy->str = \substr($stringy->str, $l);
        }

        if (\substr($stringy->str, -$l) === $replacement) {
            $stringy->str = \substr($stringy->str, 0, \strlen($stringy->str) - $l);
        }

        return static::create($stringy->str, $this->encoding);
    }

    /**
     * Converts the string into an URL slug. This includes replacing non-ASCII
     * characters with their closest ASCII equivalents, removing remaining
     * non-ASCII and non-alphanumeric characters, and replacing whitespace with
     * $replacement. The replacement defaults to a single dash, and the string
     * is also converted to lowercase.
     *
     * @param string $replacement [optional] <p>The string used to replace whitespace. Default: '-'</p>
     * @param string $language    [optional] <p>The language for the url. Default: 'de'</p>
     * @param bool   $strToLower  [optional] <p>string to lower. Default: true</p>
     *
     * @return static <p>Object whose $str has been converted to an URL slug.</p>
     */
    public function urlify(string $replacement = '-', string $language = 'de', bool $strToLower = true): self
    {
        return static::create(
            URLify::slug($this->str, $language, $replacement, $strToLower),
            $this->encoding
        );
    }

    /**
     * Convert a string to e.g.: "snake_case"
     *
     * @return static <p>Object with $str in snake_case.</p>
     */
    public function snakeize(): self
    {
        return static::create(
            $this->utf8::str_snakeize($this->str, $this->encoding),
            $this->encoding
        );
    }

    /**
     * Splits the string with the provided regular expression, returning an
     * array of Stringy objects. An optional integer $limit will truncate the
     * results.
     *
     * @param string $pattern <p>The regex with which to split the string.</p>
     * @param int    $limit   [optional] <p>Maximum number of results to return. Default: -1 === no limit</p>
     *
     * @return static[] <p>An array of Stringy objects.</p>
     */
    public function split(string $pattern, int $limit = null): array
    {
        if ($limit === null) {
            $limit = -1;
        }

        $array = $this->utf8::str_split_pattern($this->str, $pattern, $limit);
        foreach ($array as $i => &$value) {
            $value = static::create($value, $this->encoding);
        }

        return $array;
    }

    /**
     * Returns true if the string begins with $substring, false otherwise. By
     * default, the comparison is case-sensitive, but can be made insensitive
     * by setting $caseSensitive to false.
     *
     * @param string $substring     <p>The substring to look for.</p>
     * @param bool   $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
     *
     * @return bool <p>Whether or not $str starts with $substring.</p>
     */
    public function startsWith(string $substring, bool $caseSensitive = true): bool
    {
        if ($caseSensitive) {
            return $this->utf8::str_starts_with($this->str, $substring);
        }

        return $this->utf8::str_istarts_with($this->str, $substring);
    }

    /**
     * Returns true if the string begins with any of $substrings, false otherwise.
     * By default the comparison is case-sensitive, but can be made insensitive by
     * setting $caseSensitive to false.
     *
     * @param array $substrings    <p>Substrings to look for.</p>
     * @param bool  $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
     *
     * @return bool <p>Whether or not $str starts with $substring.</p>
     */
    public function startsWithAny(array $substrings, bool $caseSensitive = true): bool
    {
        if ($caseSensitive) {
            return $this->utf8::str_starts_with_any($this->str, $substrings);
        }

        return $this->utf8::str_istarts_with_any($this->str, $substrings);
    }

    /**
     * Strip all whitespace characters. This includes tabs and newline characters,
     * as well as multibyte whitespace such as the thin space and ideographic space.
     *
     * @return static
     */
    public function stripWhitespace(): self
    {
        return static::create(
            $this->utf8::strip_whitespace($this->str),
            $this->encoding
        );
    }

    /**
     * Remove css media-queries.
     *
     * @return static
     */
    public function stripeCssMediaQueries(): self
    {
        return static::create(
            $this->utf8::css_stripe_media_queries($this->str),
            $this->encoding
        );
    }

    /**
     * Remove empty html-tag.
     *
     * e.g.: <tag></tag>
     *
     * @return static
     */
    public function stripeEmptyHtmlTags(): self
    {
        return static::create(
            $this->utf8::html_stripe_empty_tags($this->str),
            $this->encoding
        );
    }

    /**
     * Returns the substring beginning at $start with the specified $length.
     * It differs from the $this->utf8::substr() function in that providing a $length of
     * null will return the rest of the string, rather than an empty string.
     *
     * @param int $start  <p>Position of the first character to use.</p>
     * @param int $length [optional] <p>Maximum number of characters used. Default: null</p>
     *
     * @return static <p>Object with its $str being the substring.</p>
     */
    public function substr(int $start, int $length = null): self
    {
        return static::create(
            $this->utf8::substr(
                $this->str,
                $start,
                $length,
                $this->encoding
            ),
            $this->encoding
        );
    }

    /**
     * Gets the substring after (or before via "$beforeNeedle") the first occurrence of the "$needle".
     * If no match is found returns new empty Stringy object.
     *
     * @param string $needle       <p>The string to look for.</p>
     * @param bool   $beforeNeedle [optional] <p>Default: false</p>
     *
     * @return static
     */
    public function substringOf(string $needle, bool $beforeNeedle = false): self
    {
        return static::create(
            $this->utf8::str_substr_first($this->str, $needle, $beforeNeedle, $this->encoding),
            $this->encoding
        );
    }

    /**
     * Gets the substring after (or before via "$beforeNeedle") the first occurrence of the "$needle".
     * If no match is found returns new empty Stringy object.
     *
     * @param string $needle       <p>The string to look for.</p>
     * @param bool   $beforeNeedle [optional] <p>Default: false</p>
     *
     * @return static
     */
    public function substringOfIgnoreCase(string $needle, bool $beforeNeedle = false): self
    {
        return static::create(
            $this->utf8::str_isubstr_first($this->str, $needle, $beforeNeedle, $this->encoding),
            $this->encoding
        );
    }

    /**
     * Surrounds $str with the given substring.
     *
     * @param string $substring <p>The substring to add to both sides.</P>
     *
     * @return static <p>Object whose $str had the substring both prepended and appended.</p>
     */
    public function surround(string $substring): self
    {
        return static::create(
            $substring . $this->str . $substring,
            $this->encoding
        );
    }

    /**
     * Returns a case swapped version of the string.
     *
     * @return static <p>Object whose $str has each character's case swapped.</P>
     */
    public function swapCase(): self
    {
        return static::create(
            $this->utf8::swapCase($this->str, $this->encoding),
            $this->encoding
        );
    }

    /**
     * Returns a string with smart quotes, ellipsis characters, and dashes from
     * Windows-1252 (commonly used in Word documents) replaced by their ASCII
     * equivalents.
     *
     * @return static <p>Object whose $str has those characters removed.</p>
     */
    public function tidy(): self
    {
        return static::create(
            $this->utf8::normalize_msword($this->str),
            $this->encoding
        );
    }

    /**
     * Returns a trimmed string with the first letter of each word capitalized.
     * Also accepts an array, $ignore, allowing you to list words not to be
     * capitalized.
     *
     * @param array|null $ignore [optional] <p>An array of words not to capitalize or null. Default: null</p>
     *
     * @return static <p>Object with a titleized $str.</p>
     */
    public function titleize(array $ignore = null): self
    {
        return static::create(
            $this->utf8::str_titleize($this->str, $ignore, $this->encoding),
            $this->encoding
        );
    }

    /**
     * Returns a trimmed string in proper title case.
     *
     * Also accepts an array, $ignore, allowing you to list words not to be
     * capitalized.
     *
     * Adapted from John Gruber's script.
     *
     * @see https://gist.github.com/gruber/9f9e8650d68b13ce4d78
     *
     * @param array $ignore <p>An array of words not to capitalize.</p>
     *
     * @return static <p>Object with a titleized $str</p>
     */
    public function titleizeForHumans(array $ignore = []): self
    {
        return static::create(
            $this->utf8::str_titleize_for_humans($this->str, $ignore, $this->encoding),
            $this->encoding
        );
    }

    /**
     * Returns an ASCII version of the string. A set of non-ASCII characters are
     * replaced with their closest ASCII counterparts, and the rest are removed
     * unless instructed otherwise.
     *
     * @param bool $strict [optional] <p>Use "transliterator_transliterate()" from PHP-Intl | WARNING: bad performance |
     *                     Default: false</p>
     *
     * @return static <p>Object whose $str contains only ASCII characters.</p>
     */
    public function toTransliterate(bool $strict = false): self
    {
        return static::create(
            $this->utf8::to_ascii($this->str, '?', $strict),
            $this->encoding
        );
    }

    /**
     * Returns an ASCII version of the string. A set of non-ASCII characters are
     * replaced with their closest ASCII counterparts, and the rest are removed
     * by default. The language or locale of the source string can be supplied
     * for language-specific transliteration in any of the following formats:
     * en, en_GB, or en-GB. For example, passing "de" results in "äöü" mapping
     * to "aeoeue" rather than "aou" as in other languages.
     *
     * @param string $language          Language of the source string
     * @param bool   $removeUnsupported Whether or not to remove the
     *                                  unsupported characters
     *
     * @return static Object whose $str contains only ASCII characters
     */
    public function toAscii(string $language = 'en', bool $removeUnsupported = true)
    {
        // init
        $str = $this->str;

        $langSpecific = self::langSpecificCharsArray($language);
        if (!empty($langSpecific)) {
            $str = \str_replace($langSpecific[0], $langSpecific[1], $str);
        }

        foreach ($this->charsArray() as $key => $value) {
            $str = \str_replace($value, $key, $str);
        }

        if ($removeUnsupported) {
            $str = \preg_replace('/[^\x20-\x7E]/u', '', $str);
        }

        return static::create($str, $this->encoding);
    }

    /**
     * Returns a boolean representation of the given logical string value.
     * For example, 'true', '1', 'on' and 'yes' will return true. 'false', '0',
     * 'off', and 'no' will return false. In all instances, case is ignored.
     * For other numeric strings, their sign will determine the return value.
     * In addition, blank strings consisting of only whitespace will return
     * false. For all other strings, the return value is a result of a
     * boolean cast.
     *
     * @return bool <p>A boolean value for the string.</p>
     */
    public function toBoolean(): bool
    {
        return $this->utf8::to_boolean($this->str);
    }

    /**
     * Converts all characters in the string to lowercase.
     *
     * @param bool        $tryToKeepStringLength [optional] <p>true === try to keep the string length: e.g. ẞ -> ß</p>
     * @param string|null $lang                  [optional] <p>Set the language for special cases: az, el, lt, tr</p>
     *
     * @return static <p>Object with all characters of $str being lowercase.</p>
     */
    public function toLowerCase($tryToKeepStringLength = false, $lang = null): self
    {
        return static::create(
            $this->utf8::strtolower(
                $this->str, $this->encoding, false, $lang, $tryToKeepStringLength
            ),
            $this->encoding
        );
    }

    /**
     * Converts each tab in the string to some number of spaces, as defined by
     * $tabLength. By default, each tab is converted to 4 consecutive spaces.
     *
     * @param int $tabLength [optional] <p>Number of spaces to replace each tab with. Default: 4</p>
     *
     * @return static <p>Object whose $str has had tabs switched to spaces.</p>
     */
    public function toSpaces(int $tabLength = 4): self
    {
        if ($tabLength === 4) {
            $tab = '    ';
        } elseif ($tabLength === 2) {
            $tab = '  ';
        } else {
            $tab = \str_repeat(' ', $tabLength);
        }

        return static::create(
            \str_replace("\t", $tab, $this->str),
            $this->encoding
        );
    }

    /**
     * Return Stringy object as string, but you can also use (string) for automatically casting the object into a
     * string.
     *
     * @return string
     */
    public function toString(): string
    {
        return (string) $this->str;
    }

    /**
     * Converts each occurrence of some consecutive number of spaces, as
     * defined by $tabLength, to a tab. By default, each 4 consecutive spaces
     * are converted to a tab.
     *
     * @param int $tabLength [optional] <p>Number of spaces to replace with a tab. Default: 4</p>
     *
     * @return static <p>Object whose $str has had spaces switched to tabs.</p>
     */
    public function toTabs(int $tabLength = 4): self
    {
        if ($tabLength === 4) {
            $tab = '    ';
        } elseif ($tabLength === 2) {
            $tab = '  ';
        } else {
            $tab = \str_repeat(' ', $tabLength);
        }

        return static::create(
            \str_replace($tab, "\t", $this->str),
            $this->encoding
        );
    }

    /**
     * Converts the first character of each word in the string to uppercase
     * and all other chars to lowercase.
     *
     * @return static <p>Object with all characters of $str being title-cased.</p>
     */
    public function toTitleCase(): self
    {
        return static::create(
            $this->utf8::titlecase($this->str, $this->encoding),
            $this->encoding
        );
    }

    /**
     * Converts all characters in the string to uppercase.
     *
     * @param bool        $tryToKeepStringLength [optional] <p>true === try to keep the string length: e.g. ẞ -> ß</p>
     * @param string|null $lang                  [optional] <p>Set the language for special cases: az, el, lt, tr</p>
     *
     * @return static <p>Object with all characters of $str being uppercase.</p>
     */
    public function toUpperCase($tryToKeepStringLength = false, $lang = null): self
    {
        return static::create(
            $this->utf8::strtoupper($this->str, $this->encoding, false, $lang, $tryToKeepStringLength),
            $this->encoding
        );
    }

    /**
     * Returns a string with whitespace removed from the start and end of the
     * string. Supports the removal of unicode whitespace. Accepts an optional
     * string of characters to strip instead of the defaults.
     *
     * @param string $chars [optional] <p>String of characters to strip. Default: null</p>
     *
     * @return static <p>Object with a trimmed $str.</p>
     */
    public function trim(string $chars = null): self
    {
        return static::create(
            $this->utf8::trim($this->str, $chars),
            $this->encoding
        );
    }

    /**
     * Returns a string with whitespace removed from the start of the string.
     * Supports the removal of unicode whitespace. Accepts an optional
     * string of characters to strip instead of the defaults.
     *
     * @param string $chars [optional] <p>Optional string of characters to strip. Default: null</p>
     *
     * @return static <p>Object with a trimmed $str.</p>
     */
    public function trimLeft(string $chars = null): self
    {
        return static::create(
            $this->utf8::ltrim($this->str, $chars),
            $this->encoding
        );
    }

    /**
     * Returns a string with whitespace removed from the end of the string.
     * Supports the removal of unicode whitespace. Accepts an optional
     * string of characters to strip instead of the defaults.
     *
     * @param string $chars [optional] <p>Optional string of characters to strip. Default: null</p>
     *
     * @return static <p>Object with a trimmed $str.</p>
     */
    public function trimRight(string $chars = null): self
    {
        return static::create(
            $this->utf8::rtrim($this->str, $chars),
            $this->encoding
        );
    }

    /**
     * Truncates the string to a given length. If $substring is provided, and
     * truncating occurs, the string is further truncated so that the substring
     * may be appended without exceeding the desired length.
     *
     * @param int    $length    <p>Desired length of the truncated string.</p>
     * @param string $substring [optional] <p>The substring to append if it can fit. Default: ''</p>
     *
     * @return static <p>Object with the resulting $str after truncating.</p>
     */
    public function truncate(int $length, string $substring = ''): self
    {
        return static::create(
            $this->utf8::str_truncate($this->str, $length, $substring, $this->encoding),
            $this->encoding
        );
    }

    /**
     * Returns a lowercase and trimmed string separated by underscores.
     * Underscores are inserted before uppercase characters (with the exception
     * of the first character of the string), and in place of spaces as well as
     * dashes.
     *
     * @return static <p>Object with an underscored $str.</p>
     */
    public function underscored(): self
    {
        return $this->delimit('_');
    }

    /**
     * Returns an UpperCamelCase version of the supplied string. It trims
     * surrounding spaces, capitalizes letters following digits, spaces, dashes
     * and underscores, and removes spaces, dashes, underscores.
     *
     * @return static <p>Object with $str in UpperCamelCase.</p>
     */
    public function upperCamelize(): self
    {
        return static::create(
            $this->utf8::str_upper_camelize($this->str, $this->encoding),
            $this->encoding
        );
    }

    /**
     * Converts the first character of the supplied string to upper case.
     *
     * @return static <p>Object with the first character of $str being upper case.</p>
     */
    public function upperCaseFirst(): self
    {
        return static::create($this->utf8::ucfirst($this->str, $this->encoding), $this->encoding);
    }

    /**
     * Converts the string into an valid UTF-8 string.
     *
     * @return static
     */
    public function utf8ify(): self
    {
        return static::create($this->utf8::cleanup($this->str), $this->encoding);
    }

    /**
     * Returns the replacements for the toAscii() method.
     *
     * @return array an array of replacements
     */
    protected function charsArray(): array
    {
        static $charsArray;

        if (isset($charsArray)) {
            return $charsArray;
        }

        return $charsArray = [
            '0'    => ['°', '₀', '۰', '０'],
            '1'    => ['¹', '₁', '۱', '１'],
            '2'    => ['²', '₂', '۲', '２'],
            '3'    => ['³', '₃', '۳', '３'],
            '4'    => ['⁴', '₄', '۴', '٤', '４'],
            '5'    => ['⁵', '₅', '۵', '٥', '５'],
            '6'    => ['⁶', '₆', '۶', '٦', '６'],
            '7'    => ['⁷', '₇', '۷', '７'],
            '8'    => ['⁸', '₈', '۸', '８'],
            '9'    => ['⁹', '₉', '۹', '９'],
            'a'    => [
                'à',
                'á',
                'ả',
                'ã',
                'ạ',
                'ă',
                'ắ',
                'ằ',
                'ẳ',
                'ẵ',
                'ặ',
                'â',
                'ấ',
                'ầ',
                'ẩ',
                'ẫ',
                'ậ',
                'ā',
                'ą',
                'å',
                'α',
                'ά',
                'ἀ',
                'ἁ',
                'ἂ',
                'ἃ',
                'ἄ',
                'ἅ',
                'ἆ',
                'ἇ',
                'ᾀ',
                'ᾁ',
                'ᾂ',
                'ᾃ',
                'ᾄ',
                'ᾅ',
                'ᾆ',
                'ᾇ',
                'ὰ',
                'ά',
                'ᾰ',
                'ᾱ',
                'ᾲ',
                'ᾳ',
                'ᾴ',
                'ᾶ',
                'ᾷ',
                'а',
                'أ',
                'အ',
                'ာ',
                'ါ',
                'ǻ',
                'ǎ',
                'ª',
                'ა',
                'अ',
                'ا',
                'ａ',
                'ä',
            ],
            'b'    => ['б', 'β', 'ب', 'ဗ', 'ბ', 'ｂ'],
            'c'    => ['ç', 'ć', 'č', 'ĉ', 'ċ', 'ｃ'],
            'd'    => [
                'ď',
                'ð',
                'đ',
                'ƌ',
                'ȡ',
                'ɖ',
                'ɗ',
                'ᵭ',
                'ᶁ',
                'ᶑ',
                'д',
                'δ',
                'د',
                'ض',
                'ဍ',
                'ဒ',
                'დ',
                'ｄ',
            ],
            'e'    => [
                'é',
                'è',
                'ẻ',
                'ẽ',
                'ẹ',
                'ê',
                'ế',
                'ề',
                'ể',
                'ễ',
                'ệ',
                'ë',
                'ē',
                'ę',
                'ě',
                'ĕ',
                'ė',
                'ε',
                'έ',
                'ἐ',
                'ἑ',
                'ἒ',
                'ἓ',
                'ἔ',
                'ἕ',
                'ὲ',
                'έ',
                'е',
                'ё',
                'э',
                'є',
                'ə',
                'ဧ',
                'ေ',
                'ဲ',
                'ე',
                'ए',
                'إ',
                'ئ',
                'ｅ',
            ],
            'f'    => ['ф', 'φ', 'ف', 'ƒ', 'ფ', 'ｆ'],
            'g'    => [
                'ĝ',
                'ğ',
                'ġ',
                'ģ',
                'г',
                'ґ',
                'γ',
                'ဂ',
                'გ',
                'گ',
                'ｇ',
            ],
            'h'    => ['ĥ', 'ħ', 'η', 'ή', 'ح', 'ه', 'ဟ', 'ှ', 'ჰ', 'ｈ'],
            'i'    => [
                'í',
                'ì',
                'ỉ',
                'ĩ',
                'ị',
                'î',
                'ï',
                'ī',
                'ĭ',
                'į',
                'ı',
                'ι',
                'ί',
                'ϊ',
                'ΐ',
                'ἰ',
                'ἱ',
                'ἲ',
                'ἳ',
                'ἴ',
                'ἵ',
                'ἶ',
                'ἷ',
                'ὶ',
                'ί',
                'ῐ',
                'ῑ',
                'ῒ',
                'ΐ',
                'ῖ',
                'ῗ',
                'і',
                'ї',
                'и',
                'ဣ',
                'ိ',
                'ီ',
                'ည်',
                'ǐ',
                'ი',
                'इ',
                'ی',
                'ｉ',
            ],
            'j'    => ['ĵ', 'ј', 'Ј', 'ჯ', 'ج', 'ｊ'],
            'k'    => [
                'ķ',
                'ĸ',
                'к',
                'κ',
                'Ķ',
                'ق',
                'ك',
                'က',
                'კ',
                'ქ',
                'ک',
                'ｋ',
            ],
            'l'    => [
                'ł',
                'ľ',
                'ĺ',
                'ļ',
                'ŀ',
                'л',
                'λ',
                'ل',
                'လ',
                'ლ',
                'ｌ',
            ],
            'm'    => ['м', 'μ', 'م', 'မ', 'მ', 'ｍ'],
            'n'    => [
                'ñ',
                'ń',
                'ň',
                'ņ',
                'ŉ',
                'ŋ',
                'ν',
                'н',
                'ن',
                'န',
                'ნ',
                'ｎ',
            ],
            'o'    => [
                'ó',
                'ò',
                'ỏ',
                'õ',
                'ọ',
                'ô',
                'ố',
                'ồ',
                'ổ',
                'ỗ',
                'ộ',
                'ơ',
                'ớ',
                'ờ',
                'ở',
                'ỡ',
                'ợ',
                'ø',
                'ō',
                'ő',
                'ŏ',
                'ο',
                'ὀ',
                'ὁ',
                'ὂ',
                'ὃ',
                'ὄ',
                'ὅ',
                'ὸ',
                'ό',
                'о',
                'و',
                'θ',
                'ို',
                'ǒ',
                'ǿ',
                'º',
                'ო',
                'ओ',
                'ｏ',
                'ö',
            ],
            'p'    => ['п', 'π', 'ပ', 'პ', 'پ', 'ｐ'],
            'q'    => ['ყ', 'ｑ'],
            'r'    => ['ŕ', 'ř', 'ŗ', 'р', 'ρ', 'ر', 'რ', 'ｒ'],
            's'    => [
                'ś',
                'š',
                'ş',
                'с',
                'σ',
                'ș',
                'ς',
                'س',
                'ص',
                'စ',
                'ſ',
                'ს',
                'ｓ',
            ],
            't'    => [
                'ť',
                'ţ',
                'т',
                'τ',
                'ț',
                'ت',
                'ط',
                'ဋ',
                'တ',
                'ŧ',
                'თ',
                'ტ',
                'ｔ',
            ],
            'u'    => [
                'ú',
                'ù',
                'ủ',
                'ũ',
                'ụ',
                'ư',
                'ứ',
                'ừ',
                'ử',
                'ữ',
                'ự',
                'û',
                'ū',
                'ů',
                'ű',
                'ŭ',
                'ų',
                'µ',
                'у',
                'ဉ',
                'ု',
                'ူ',
                'ǔ',
                'ǖ',
                'ǘ',
                'ǚ',
                'ǜ',
                'უ',
                'उ',
                'ｕ',
                'ў',
                'ü',
            ],
            'v'    => ['в', 'ვ', 'ϐ', 'ｖ'],
            'w'    => ['ŵ', 'ω', 'ώ', 'ဝ', 'ွ', 'ｗ'],
            'x'    => ['χ', 'ξ', 'ｘ'],
            'y'    => [
                'ý',
                'ỳ',
                'ỷ',
                'ỹ',
                'ỵ',
                'ÿ',
                'ŷ',
                'й',
                'ы',
                'υ',
                'ϋ',
                'ύ',
                'ΰ',
                'ي',
                'ယ',
                'ｙ',
            ],
            'z'    => ['ź', 'ž', 'ż', 'з', 'ζ', 'ز', 'ဇ', 'ზ', 'ｚ'],
            'aa'   => ['ع', 'आ', 'آ'],
            'ae'   => ['æ', 'ǽ'],
            'ai'   => ['ऐ'],
            'ch'   => ['ч', 'ჩ', 'ჭ', 'چ'],
            'dj'   => ['ђ', 'đ'],
            'dz'   => ['џ', 'ძ'],
            'ei'   => ['ऍ'],
            'gh'   => ['غ', 'ღ'],
            'ii'   => ['ई'],
            'ij'   => ['ĳ'],
            'kh'   => ['х', 'خ', 'ხ'],
            'lj'   => ['љ'],
            'nj'   => ['њ'],
            'oe'   => ['œ', 'ؤ'],
            'oi'   => ['ऑ'],
            'oii'  => ['ऒ'],
            'ps'   => ['ψ'],
            'sh'   => ['ш', 'შ', 'ش'],
            'shch' => ['щ'],
            'ss'   => ['ß'],
            'sx'   => ['ŝ'],
            'th'   => ['þ', 'ϑ', 'ث', 'ذ', 'ظ'],
            'ts'   => ['ц', 'ც', 'წ'],
            'uu'   => ['ऊ'],
            'ya'   => ['я'],
            'yu'   => ['ю'],
            'zh'   => ['ж', 'ჟ', 'ژ'],
            '(c)'  => ['©'],
            'A'    => [
                'Á',
                'À',
                'Ả',
                'Ã',
                'Ạ',
                'Ă',
                'Ắ',
                'Ằ',
                'Ẳ',
                'Ẵ',
                'Ặ',
                'Â',
                'Ấ',
                'Ầ',
                'Ẩ',
                'Ẫ',
                'Ậ',
                'Å',
                'Ā',
                'Ą',
                'Α',
                'Ά',
                'Ἀ',
                'Ἁ',
                'Ἂ',
                'Ἃ',
                'Ἄ',
                'Ἅ',
                'Ἆ',
                'Ἇ',
                'ᾈ',
                'ᾉ',
                'ᾊ',
                'ᾋ',
                'ᾌ',
                'ᾍ',
                'ᾎ',
                'ᾏ',
                'Ᾰ',
                'Ᾱ',
                'Ὰ',
                'Ά',
                'ᾼ',
                'А',
                'Ǻ',
                'Ǎ',
                'Ａ',
                'Ä',
            ],
            'B'    => ['Б', 'Β', 'ब', 'Ｂ'],
            'C'    => ['Ç', 'Ć', 'Č', 'Ĉ', 'Ċ', 'Ｃ'],
            'D'    => [
                'Ď',
                'Ð',
                'Đ',
                'Ɖ',
                'Ɗ',
                'Ƌ',
                'ᴅ',
                'ᴆ',
                'Д',
                'Δ',
                'Ｄ',
            ],
            'E'    => [
                'É',
                'È',
                'Ẻ',
                'Ẽ',
                'Ẹ',
                'Ê',
                'Ế',
                'Ề',
                'Ể',
                'Ễ',
                'Ệ',
                'Ë',
                'Ē',
                'Ę',
                'Ě',
                'Ĕ',
                'Ė',
                'Ε',
                'Έ',
                'Ἐ',
                'Ἑ',
                'Ἒ',
                'Ἓ',
                'Ἔ',
                'Ἕ',
                'Έ',
                'Ὲ',
                'Е',
                'Ё',
                'Э',
                'Є',
                'Ə',
                'Ｅ',
            ],
            'F'    => ['Ф', 'Φ', 'Ｆ'],
            'G'    => ['Ğ', 'Ġ', 'Ģ', 'Г', 'Ґ', 'Γ', 'Ｇ'],
            'H'    => ['Η', 'Ή', 'Ħ', 'Ｈ'],
            'I'    => [
                'Í',
                'Ì',
                'Ỉ',
                'Ĩ',
                'Ị',
                'Î',
                'Ï',
                'Ī',
                'Ĭ',
                'Į',
                'İ',
                'Ι',
                'Ί',
                'Ϊ',
                'Ἰ',
                'Ἱ',
                'Ἳ',
                'Ἴ',
                'Ἵ',
                'Ἶ',
                'Ἷ',
                'Ῐ',
                'Ῑ',
                'Ὶ',
                'Ί',
                'И',
                'І',
                'Ї',
                'Ǐ',
                'ϒ',
                'Ｉ',
            ],
            'J'    => ['Ｊ'],
            'K'    => ['К', 'Κ', 'Ｋ'],
            'L'    => ['Ĺ', 'Ł', 'Л', 'Λ', 'Ļ', 'Ľ', 'Ŀ', 'ल', 'Ｌ'],
            'M'    => ['М', 'Μ', 'Ｍ'],
            'N'    => ['Ń', 'Ñ', 'Ň', 'Ņ', 'Ŋ', 'Н', 'Ν', 'Ｎ'],
            'O'    => [
                'Ó',
                'Ò',
                'Ỏ',
                'Õ',
                'Ọ',
                'Ô',
                'Ố',
                'Ồ',
                'Ổ',
                'Ỗ',
                'Ộ',
                'Ơ',
                'Ớ',
                'Ờ',
                'Ở',
                'Ỡ',
                'Ợ',
                'Ø',
                'Ō',
                'Ő',
                'Ŏ',
                'Ο',
                'Ό',
                'Ὀ',
                'Ὁ',
                'Ὂ',
                'Ὃ',
                'Ὄ',
                'Ὅ',
                'Ὸ',
                'Ό',
                'О',
                'Θ',
                'Ө',
                'Ǒ',
                'Ǿ',
                'Ｏ',
                'Ö',
            ],
            'P'    => ['П', 'Π', 'Ｐ'],
            'Q'    => ['Ｑ'],
            'R'    => ['Ř', 'Ŕ', 'Р', 'Ρ', 'Ŗ', 'Ｒ'],
            'S'    => ['Ş', 'Ŝ', 'Ș', 'Š', 'Ś', 'С', 'Σ', 'Ｓ'],
            'T'    => ['Ť', 'Ţ', 'Ŧ', 'Ț', 'Т', 'Τ', 'Ｔ'],
            'U'    => [
                'Ú',
                'Ù',
                'Ủ',
                'Ũ',
                'Ụ',
                'Ư',
                'Ứ',
                'Ừ',
                'Ử',
                'Ữ',
                'Ự',
                'Û',
                'Ū',
                'Ů',
                'Ű',
                'Ŭ',
                'Ų',
                'У',
                'Ǔ',
                'Ǖ',
                'Ǘ',
                'Ǚ',
                'Ǜ',
                'Ｕ',
                'Ў',
                'Ü',
            ],
            'V'    => ['В', 'Ｖ'],
            'W'    => ['Ω', 'Ώ', 'Ŵ', 'Ｗ'],
            'X'    => ['Χ', 'Ξ', 'Ｘ'],
            'Y'    => [
                'Ý',
                'Ỳ',
                'Ỷ',
                'Ỹ',
                'Ỵ',
                'Ÿ',
                'Ῠ',
                'Ῡ',
                'Ὺ',
                'Ύ',
                'Ы',
                'Й',
                'Υ',
                'Ϋ',
                'Ŷ',
                'Ｙ',
            ],
            'Z'    => ['Ź', 'Ž', 'Ż', 'З', 'Ζ', 'Ｚ'],
            'AE'   => ['Æ', 'Ǽ'],
            'Ch'   => ['Ч'],
            'Dj'   => ['Ђ'],
            'Dz'   => ['Џ'],
            'Gx'   => ['Ĝ'],
            'Hx'   => ['Ĥ'],
            'Ij'   => ['Ĳ'],
            'Jx'   => ['Ĵ'],
            'Kh'   => ['Х'],
            'Lj'   => ['Љ'],
            'Nj'   => ['Њ'],
            'Oe'   => ['Œ'],
            'Ps'   => ['Ψ'],
            'Sh'   => ['Ш'],
            'Shch' => ['Щ'],
            'Ss'   => ['ẞ'],
            'Th'   => ['Þ'],
            'Ts'   => ['Ц'],
            'Ya'   => ['Я'],
            'Yu'   => ['Ю'],
            'Zh'   => ['Ж'],
            ' '    => [
                "\xC2\xA0",
                "\xE2\x80\x80",
                "\xE2\x80\x81",
                "\xE2\x80\x82",
                "\xE2\x80\x83",
                "\xE2\x80\x84",
                "\xE2\x80\x85",
                "\xE2\x80\x86",
                "\xE2\x80\x87",
                "\xE2\x80\x88",
                "\xE2\x80\x89",
                "\xE2\x80\x8A",
                "\xE2\x80\xAF",
                "\xE2\x81\x9F",
                "\xE3\x80\x80",
                "\xEF\xBE\xA0",
            ],
        ];
    }

    /**
     * Returns true if $str matches the supplied pattern, false otherwise.
     *
     * @param string $pattern <p>Regex pattern to match against.</p>
     *
     * @return bool <p>Whether or not $str matches the pattern.</p>
     */
    protected function matchesPattern(string $pattern): bool
    {
        return $this->utf8::str_matches_pattern($this->str, $pattern);
    }

    /**
     * Returns language-specific replacements for the toAscii() method.
     * For example, German will map 'ä' to 'ae', while other languages
     * will simply return 'a'.
     *
     * @param string $language Language of the source string
     *
     * @return array an array of replacements
     */
    protected static function langSpecificCharsArray(string $language = 'en'): array
    {
        $split = \preg_split('/[-_]/', $language);
        $language = \strtolower($split[0]);
        static $charsArray = [];

        if (isset($charsArray[$language])) {
            return $charsArray[$language];
        }

        $languageSpecific = [
            'de' => [
                ['ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü'],
                ['ae', 'oe', 'ue', 'AE', 'OE', 'UE'],
            ],
            'bg' => [
                ['х', 'Х', 'щ', 'Щ', 'ъ', 'Ъ', 'ь', 'Ь'],
                ['h', 'H', 'sht', 'SHT', 'a', 'А', 'y', 'Y'],
            ],
        ];

        $charsArray[$language] = $languageSpecific[$language] ?? [];

        return $charsArray[$language];
    }
}
