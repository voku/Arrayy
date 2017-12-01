<?php

declare(strict_types=1);

namespace Stringy;

use voku\helper\AntiXSS;
use voku\helper\EmailCheck;
use voku\helper\URLify;
use voku\helper\UTF8;

/**
 * Class Stringy
 *
 * @package Stringy
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
   * Initializes a Stringy object and assigns both str and encoding properties
   * the supplied values. $str is cast to a string prior to assignment, and if
   * $encoding is not specified, it defaults to mb_internal_encoding(). Throws
   * an InvalidArgumentException if the first argument is an array or object
   * without a __toString method.
   *
   * @param mixed  $str      [optional] <p>Value to modify, after being cast to string. Default: ''</p>
   * @param string $encoding [optional] <p>The character encoding.</p>
   *
   * @throws \InvalidArgumentException <p>if an array or object without a
   *         __toString method is passed as the first argument</p>
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

    // init
    UTF8::checkForSupport();

    $this->str = (string)$str;

    if ($encoding) {
      $this->encoding = $encoding;
    } else {
      $this->encoding = \mb_internal_encoding();
    }
  }

  /**
   * Returns the value in $str.
   *
   * @return string <p>The current value of the $str property.</p>
   */
  public function __toString()
  {
    return (string)$this->str;
  }

  /**
   * Returns a new string with $string appended.
   *
   * @param string $string <p>The string to append.</p>
   *
   * @return static <p>Object with appended $string.</p>
   */
  public function append(string $string): Stringy
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
  public function appendPassword(int $length): Stringy
  {
    $possibleChars = '2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ';

    return $this->appendRandomString($length, $possibleChars);
  }

  /**
   * Append an unique identifier.
   *
   * @param string|int $entropyExtra [optional] <p>Extra entropy via a string or int value.</p>
   * @param bool       $md5          [optional] <p>Return the unique identifier as md5-hash? Default: true</p>
   *
   * @return static <p>Object with appended unique identifier as md5-hash.</p>
   */
  public function appendUniqueIdentifier($entropyExtra = '', bool $md5 = true): Stringy
  {
    $uniqueHelper = \mt_rand() .
                    \session_id() .
                    ($_SERVER['REMOTE_ADDR'] ?? '') .
                    ($_SERVER['SERVER_ADDR'] ?? '') .
                    $entropyExtra;

    $uniqueString = \uniqid($uniqueHelper, true);

    if ($md5) {
      $uniqueString = \md5($uniqueString . $uniqueHelper);
    }

    return $this->append($uniqueString);
  }

  /**
   * Append an random string.
   *
   * @param int    $length        <p>Length of the random string.</p>
   * @param string $possibleChars [optional] <p>Characters string for the random selection.</p>
   *
   * @return static <p>Object with appended random string.</p>
   */
  public function appendRandomString(int $length, string $possibleChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'): Stringy
  {
    // init
    $i = 0;
    $str = $this->str;
    $maxlength = UTF8::strlen($possibleChars, $this->encoding);

    if ($maxlength === 0) {
      return $this;
    }

    // add random chars
    while ($i < $length) {
      $char = UTF8::substr($possibleChars, random_int(0, $maxlength - 1), 1, $this->encoding);
      $str .= $char;
      $i++;
    }

    return $this->append($str);
  }

  /**
   * Creates a Stringy object and assigns both str and encoding properties
   * the supplied values. $str is cast to a string prior to assignment, and if
   * $encoding is not specified, it defaults to mb_internal_encoding(). It
   * then returns the initialized object. Throws an InvalidArgumentException
   * if the first argument is an array or object without a __toString method.
   *
   * @param  mixed  $str      [optional] <p>Value to modify, after being cast to string. Default: ''</p>
   * @param  string $encoding [optional] <p>The character encoding.</p>
   *
   * @return static <p>A Stringy object.</p>
   *
   * @throws \InvalidArgumentException <p>if an array or object without a
   *         __toString method is passed as the first argument</p>
   */
  public static function create($str = '', string $encoding = null): Stringy
  {
    return new static($str, $encoding);
  }

  /**
   * Returns the substring between $start and $end, if found, or an empty
   * string. An optional offset may be supplied from which to begin the
   * search for the start string.
   *
   * @param  string $start  <p>Delimiter marking the start of the substring.</p>
   * @param  string $end    <p>Delimiter marking the end of the substring.</p>
   * @param  int    $offset [optional] <p>Index from which to begin the search. Default: 0</p>
   *
   * @return static <p>Object whose $str is a substring between $start and $end.</p>
   */
  public function between(string $start, string $end, int $offset = 0): Stringy
  {
    $startIndex = $this->indexOf($start, $offset);
    if ($startIndex === false) {
      return static::create('', $this->encoding);
    }

    $substrIndex = $startIndex + UTF8::strlen($start, $this->encoding);
    $endIndex = $this->indexOf($end, $substrIndex);
    if ($endIndex === false) {
      return static::create('', $this->encoding);
    }

    return $this->substr($substrIndex, $endIndex - $substrIndex);
  }

  /**
   * Returns the index of the first occurrence of $needle in the string,
   * and false if not found. Accepts an optional offset from which to begin
   * the search.
   *
   * @param  string $needle <p>Substring to look for.</p>
   * @param  int    $offset [optional] <p>Offset from which to search. Default: 0</p>
   *
   * @return int|false <p>The occurrence's <strong>index</strong> if found, otherwise <strong>false</strong>.</p>
   */
  public function indexOf(string $needle, int $offset = 0)
  {
    return UTF8::strpos($this->str, $needle, $offset, $this->encoding);
  }

  /**
   * Returns the index of the first occurrence of $needle in the string,
   * and false if not found. Accepts an optional offset from which to begin
   * the search.
   *
   * @param  string $needle <p>Substring to look for.</p>
   * @param  int    $offset [optional] <p>Offset from which to search. Default: 0</p>
   *
   * @return int|false <p>The occurrence's <strong>index</strong> if found, otherwise <strong>false</strong>.</p>
   */
  public function indexOfIgnoreCase(string $needle, int $offset = 0)
  {
    return UTF8::stripos($this->str, $needle, $offset, $this->encoding);
  }

  /**
   * Returns the substring beginning at $start with the specified $length.
   * It differs from the UTF8::substr() function in that providing a $length of
   * null will return the rest of the string, rather than an empty string.
   *
   * @param int $start  <p>Position of the first character to use.</p>
   * @param int $length [optional] <p>Maximum number of characters used. Default: null</p>
   *
   * @return static <p>Object with its $str being the substring.</p>
   */
  public function substr(int $start, int $length = null): Stringy
  {
    if ($length === null) {
      $length = $this->length();
    }

    $str = UTF8::substr($this->str, $start, $length, $this->encoding);

    return static::create($str, $this->encoding);
  }

  /**
   * Returns the length of the string.
   *
   * @return int <p>The number of characters in $str given the encoding.</p>
   */
  public function length(): int
  {
    return UTF8::strlen($this->str, $this->encoding);
  }

  /**
   * Trims the string and replaces consecutive whitespace characters with a
   * single space. This includes tabs and newline characters, as well as
   * multibyte whitespace such as the thin space and ideographic space.
   *
   * @return static <p>Object with a trimmed $str and condensed whitespace.</p>
   */
  public function collapseWhitespace(): Stringy
  {
    return $this->regexReplace('[[:space:]]+', ' ')->trim();
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
  public function trim(string $chars = null): Stringy
  {
    if (!$chars) {
      $chars = '[:space:]';
    } else {
      $chars = \preg_quote($chars, '/');
    }

    return $this->regexReplace("^[$chars]+|[$chars]+\$", '');
  }

  /**
   * Replaces all occurrences of $pattern in $str by $replacement.
   *
   * @param  string $pattern     <p>The regular expression pattern.</p>
   * @param  string $replacement <p>The string to replace with.</p>
   * @param  string $options     [optional] <p>Matching conditions to be used.</p>
   * @param  string $delimiter   [optional] <p>Delimiter the the regex. Default: '/'</p>
   *
   * @return static <p>Object with the result2ing $str after the replacements.</p>
   */
  public function regexReplace(string $pattern, string $replacement, string $options = '', string $delimiter = '/'): Stringy
  {
    if ($options === 'msr') {
      $options = 'ms';
    }

    // fallback
    if (!$delimiter) {
      $delimiter = '/';
    }

    $str = (string)\preg_replace(
        $delimiter . $pattern . $delimiter . 'u' . $options,
        $replacement,
        $this->str
    );

    return static::create($str, $this->encoding);
  }

  /**
   * Returns true if the string contains all $needles, false otherwise. By
   * default the comparison is case-sensitive, but can be made insensitive by
   * setting $caseSensitive to false.
   *
   * @param  array $needles       <p>SubStrings to look for.</p>
   * @param  bool  $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
   *
   * @return bool  <p>Whether or not $str contains $needle.</p>
   */
  public function containsAll(array $needles, bool $caseSensitive = true): bool
  {
    /** @noinspection IsEmptyFunctionUsageInspection */
    if (empty($needles)) {
      return false;
    }

    foreach ($needles as $needle) {
      if (!$this->contains($needle, $caseSensitive)) {
        return false;
      }
    }

    return true;
  }

  /**
   * Returns true if the string contains $needle, false otherwise. By default
   * the comparison is case-sensitive, but can be made insensitive by setting
   * $caseSensitive to false.
   *
   * @param string $needle        <p>Substring to look for.</p>
   * @param bool   $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
   *
   * @return bool   <p>Whether or not $str contains $needle.</p>
   */
  public function contains(string $needle, bool $caseSensitive = true): bool
  {
    $encoding = $this->encoding;

    if ($caseSensitive) {
      return (UTF8::strpos($this->str, $needle, 0, $encoding) !== false);
    }

    return (UTF8::stripos($this->str, $needle, 0, $encoding) !== false);
  }

  /**
   * Returns true if the string contains any $needles, false otherwise. By
   * default the comparison is case-sensitive, but can be made insensitive by
   * setting $caseSensitive to false.
   *
   * @param  array $needles       <p>SubStrings to look for.</p>
   * @param  bool  $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
   *
   * @return bool <p>Whether or not $str contains $needle.</p>
   */
  public function containsAny(array $needles, bool $caseSensitive = true): bool
  {
    /** @noinspection IsEmptyFunctionUsageInspection */
    if (empty($needles)) {
      return false;
    }

    foreach ($needles as $needle) {
      if ($this->contains($needle, $caseSensitive)) {
        return true;
      }
    }

    return false;
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
   * @param  string $substring     <p>The substring to search for.</p>
   * @param  bool   $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
   *
   * @return int|false <p>This functions returns an integer or false if there isn't a string.</p>
   */
  public function countSubstr(string $substring, bool $caseSensitive = true)
  {
    if ($caseSensitive) {
      return UTF8::substr_count($this->str, $substring, 0, null, $this->encoding);
    }

    $str = UTF8::strtoupper($this->str, $this->encoding);
    $substring = UTF8::strtoupper($substring, $this->encoding);

    return UTF8::substr_count($str, $substring, 0, null, $this->encoding);
  }

  /**
   * Returns a lowercase and trimmed string separated by dashes. Dashes are
   * inserted before uppercase characters (with the exception of the first
   * character of the string), and in place of spaces as well as underscores.
   *
   * @return static <p>Object with a dasherized $str</p>
   */
  public function dasherize(): Stringy
  {
    return $this->delimit('-');
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
  public function delimit(string $delimiter): Stringy
  {
    $str = $this->trim();

    $str = (string)\preg_replace('/\B([A-Z])/u', '-\1', $str);

    $str = UTF8::strtolower($str, $this->encoding);

    $str = (string)\preg_replace('/[-_\s]+/u', $delimiter, $str);

    return static::create($str, $this->encoding);
  }

  /**
   * Ensures that the string begins with $substring. If it doesn't, it's
   * prepended.
   *
   * @param string $substring <p>The substring to add if not present.</p>
   *
   * @return static <p>Object with its $str prefixed by the $substring.</p>
   */
  public function ensureLeft(string $substring): Stringy
  {
    $stringy = static::create($this->str, $this->encoding);

    if (!$stringy->startsWith($substring)) {
      $stringy->str = $substring . $stringy->str;
    }

    return $stringy;
  }

  /**
   * Returns true if the string begins with $substring, false otherwise. By
   * default, the comparison is case-sensitive, but can be made insensitive
   * by setting $caseSensitive to false.
   *
   * @param  string $substring     <p>The substring to look for.</p>
   * @param  bool   $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
   *
   * @return bool   <p>Whether or not $str starts with $substring.</p>
   */
  public function startsWith(string $substring, bool $caseSensitive = true): bool
  {
    $str = $this->str;

    if (!$caseSensitive) {
      $substring = UTF8::strtolower($substring, $this->encoding);
      $str = UTF8::strtolower($this->str, $this->encoding);
    }

    return UTF8::strpos($str, $substring, 0, $this->encoding) === 0;
  }

  /**
   * Returns true if the string begins with any of $substrings, false otherwise.
   * By default the comparison is case-sensitive, but can be made insensitive by
   * setting $caseSensitive to false.
   *
   * @param  array $substrings    <p>Substrings to look for.</p>
   * @param  bool  $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
   *
   * @return bool  <p>Whether or not $str starts with $substring.</p>
   */
  public function startsWithAny(array $substrings, bool $caseSensitive = true): bool
  {
    if (empty($substrings)) {
      return false;
    }

    foreach ($substrings as $substring) {
      if ($this->startsWith($substring, $caseSensitive)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Ensures that the string ends with $substring. If it doesn't, it's appended.
   *
   * @param string $substring <p>The substring to add if not present.</p>
   *
   * @return static <p>Object with its $str suffixed by the $substring.</p>
   */
  public function ensureRight(string $substring): Stringy
  {
    $stringy = static::create($this->str, $this->encoding);

    if (!$stringy->endsWith($substring)) {
      $stringy->str .= $substring;
    }

    return $stringy;
  }

  /**
   * Returns true if the string ends with $substring, false otherwise. By
   * default, the comparison is case-sensitive, but can be made insensitive
   * by setting $caseSensitive to false.
   *
   * @param string $substring     <p>The substring to look for.</p>
   * @param bool   $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
   *
   * @return bool   <p>Whether or not $str ends with $substring.</p>
   */
  public function endsWith(string $substring, bool $caseSensitive = true): bool
  {
    $substringLength = UTF8::strlen($substring, $this->encoding);
    $strLength = $this->length();

    $endOfStr = UTF8::substr(
        $this->str,
        $strLength - $substringLength,
        $substringLength,
        $this->encoding
    );

    if (!$caseSensitive) {
      $substring = UTF8::strtolower($substring, $this->encoding);
      $endOfStr = UTF8::strtolower($endOfStr, $this->encoding);
    }

    return $substring === $endOfStr;
  }

  /**
   * Returns true if the string ends with any of $substrings, false otherwise.
   * By default, the comparison is case-sensitive, but can be made insensitive
   * by setting $caseSensitive to false.
   *
   * @param string[] $substrings    <p>Substrings to look for.</p>
   * @param bool     $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
   *
   * @return bool     <p>Whether or not $str ends with $substring.</p>
   */
  public function endsWithAny(array $substrings, bool $caseSensitive = true): bool
  {
    if (empty($substrings)) {
      return false;
    }

    foreach ($substrings as $substring) {
      if ($this->endsWith($substring, $caseSensitive)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Returns the first $n characters of the string.
   *
   * @param int $n <p>Number of characters to retrieve from the start.</p>
   *
   * @return static <p>Object with its $str being the first $n chars.</p>
   */
  public function first(int $n): Stringy
  {
    $stringy = static::create($this->str, $this->encoding);

    if ($n < 0) {
      $stringy->str = '';
    } else {
      return $stringy->substr(0, $n);
    }

    return $stringy;
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
   * Returns an array consisting of the characters in the string.
   *
   * @return array <p>An array of string chars.</p>
   */
  public function chars(): array
  {
    // init
    $chars = [];
    $l = $this->length();

    for ($i = 0; $i < $l; $i++) {
      $chars[] = $this->at($i)->str;
    }

    return $chars;
  }

  /**
   * Returns the character at $index, with indexes starting at 0.
   *
   * @param int $index <p>Position of the character.</p>
   *
   * @return static <p>The character at $index.</p>
   */
  public function at(int $index): Stringy
  {
    return $this->substr($index, 1);
  }

  /**
   * Returns true if the string contains a lower case char, false otherwise.
   *
   * @return bool <p>Whether or not the string contains a lower case character.</p>
   */
  public function hasLowerCase(): bool
  {
    return $this->matchesPattern('.*[[:lower:]]');
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
    if (\preg_match('/' . $pattern . '/u', $this->str)) {
      return true;
    }

    return false;
  }

  /**
   * Returns true if the string contains an upper case char, false otherwise.
   *
   * @return bool <p>Whether or not the string contains an upper case character.</p>
   */
  public function hasUpperCase(): bool
  {
    return $this->matchesPattern('.*[[:upper:]]');
  }

  /**
   * Convert all HTML entities to their applicable characters.
   *
   * @param int $flags       [optional] <p>
   *                         A bitmask of one or more of the following flags, which specify how to handle quotes and
   *                         which document type to use. The default is ENT_COMPAT.
   *                         <table>
   *                         Available <i>flags</i> constants
   *                         <tr valign="top">
   *                         <td>Constant Name</td>
   *                         <td>Description</td>
   *                         </tr>
   *                         <tr valign="top">
   *                         <td><b>ENT_COMPAT</b></td>
   *                         <td>Will convert double-quotes and leave single-quotes alone.</td>
   *                         </tr>
   *                         <tr valign="top">
   *                         <td><b>ENT_QUOTES</b></td>
   *                         <td>Will convert both double and single quotes.</td>
   *                         </tr>
   *                         <tr valign="top">
   *                         <td><b>ENT_NOQUOTES</b></td>
   *                         <td>Will leave both double and single quotes unconverted.</td>
   *                         </tr>
   *                         <tr valign="top">
   *                         <td><b>ENT_HTML401</b></td>
   *                         <td>
   *                         Handle code as HTML 4.01.
   *                         </td>
   *                         </tr>
   *                         <tr valign="top">
   *                         <td><b>ENT_XML1</b></td>
   *                         <td>
   *                         Handle code as XML 1.
   *                         </td>
   *                         </tr>
   *                         <tr valign="top">
   *                         <td><b>ENT_XHTML</b></td>
   *                         <td>
   *                         Handle code as XHTML.
   *                         </td>
   *                         </tr>
   *                         <tr valign="top">
   *                         <td><b>ENT_HTML5</b></td>
   *                         <td>
   *                         Handle code as HTML 5.
   *                         </td>
   *                         </tr>
   *                         </table>
   *                         </p>
   *
   * @return static <p>Object with the resulting $str after being html decoded.</p>
   */
  public function htmlDecode(int $flags = ENT_COMPAT): Stringy
  {
    $str = UTF8::html_entity_decode($this->str, $flags, $this->encoding);

    return static::create($str, $this->encoding);
  }

  /**
   * Convert all applicable characters to HTML entities.
   *
   * @param int $flags       [optional] <p>
   *                         A bitmask of one or more of the following flags, which specify how to handle quotes and
   *                         which document type to use. The default is ENT_COMPAT.
   *                         <table>
   *                         Available <i>flags</i> constants
   *                         <tr valign="top">
   *                         <td>Constant Name</td>
   *                         <td>Description</td>
   *                         </tr>
   *                         <tr valign="top">
   *                         <td><b>ENT_COMPAT</b></td>
   *                         <td>Will convert double-quotes and leave single-quotes alone.</td>
   *                         </tr>
   *                         <tr valign="top">
   *                         <td><b>ENT_QUOTES</b></td>
   *                         <td>Will convert both double and single quotes.</td>
   *                         </tr>
   *                         <tr valign="top">
   *                         <td><b>ENT_NOQUOTES</b></td>
   *                         <td>Will leave both double and single quotes unconverted.</td>
   *                         </tr>
   *                         <tr valign="top">
   *                         <td><b>ENT_HTML401</b></td>
   *                         <td>
   *                         Handle code as HTML 4.01.
   *                         </td>
   *                         </tr>
   *                         <tr valign="top">
   *                         <td><b>ENT_XML1</b></td>
   *                         <td>
   *                         Handle code as XML 1.
   *                         </td>
   *                         </tr>
   *                         <tr valign="top">
   *                         <td><b>ENT_XHTML</b></td>
   *                         <td>
   *                         Handle code as XHTML.
   *                         </td>
   *                         </tr>
   *                         <tr valign="top">
   *                         <td><b>ENT_HTML5</b></td>
   *                         <td>
   *                         Handle code as HTML 5.
   *                         </td>
   *                         </tr>
   *                         </table>
   *                         </p>
   *
   * @return static <p>Object with the resulting $str after being html encoded.</p>
   */
  public function htmlEncode(int $flags = ENT_COMPAT): Stringy
  {
    $str = UTF8::htmlentities($this->str, $flags, $this->encoding);

    return static::create($str, $this->encoding);
  }

  /**
   * Capitalizes the first word of the string, replaces underscores with
   * spaces, and strips '_id'.
   *
   * @return static <p>Object with a humanized $str.</p>
   */
  public function humanize(): Stringy
  {
    $str = UTF8::str_replace(['_id', '_'], ['', ' '], $this->str);

    return static::create($str, $this->encoding)->trim()->upperCaseFirst();
  }

  /**
   * Converts the first character of the supplied string to upper case.
   *
   * @return static <p>Object with the first character of $str being upper case.</p>
   */
  public function upperCaseFirst(): Stringy
  {
    $first = UTF8::substr($this->str, 0, 1, $this->encoding);
    $rest = UTF8::substr(
        $this->str,
        1,
        $this->length() - 1,
        $this->encoding
    );

    $str = UTF8::strtoupper($first, $this->encoding) . $rest;

    return static::create($str, $this->encoding);
  }

  /**
   * Returns the index of the last occurrence of $needle in the string,
   * and false if not found. Accepts an optional offset from which to begin
   * the search. Offsets may be negative to count from the last character
   * in the string.
   *
   * @param  string $needle <p>Substring to look for.</p>
   * @param  int    $offset [optional] <p>Offset from which to search. Default: 0</p>
   *
   * @return int|false <p>The last occurrence's <strong>index</strong> if found, otherwise <strong>false</strong>.</p>
   */
  public function indexOfLast(string $needle, int $offset = 0)
  {
    return UTF8::strrpos($this->str, $needle, $offset, $this->encoding);
  }

  /**
   * Returns the index of the last occurrence of $needle in the string,
   * and false if not found. Accepts an optional offset from which to begin
   * the search. Offsets may be negative to count from the last character
   * in the string.
   *
   * @param  string $needle <p>Substring to look for.</p>
   * @param  int    $offset [optional] <p>Offset from which to search. Default: 0</p>
   *
   * @return int|false <p>The last occurrence's <strong>index</strong> if found, otherwise <strong>false</strong>.</p>
   */
  public function indexOfLastIgnoreCase(string $needle, int $offset = 0)
  {
    return UTF8::strripos($this->str, $needle, $offset, $this->encoding);
  }

  /**
   * Inserts $substring into the string at the $index provided.
   *
   * @param  string $substring <p>String to be inserted.</p>
   * @param  int    $index     <p>The index at which to insert the substring.</p>
   *
   * @return static <p>Object with the resulting $str after the insertion.</p>
   */
  public function insert(string $substring, int $index): Stringy
  {
    $stringy = static::create($this->str, $this->encoding);
    if ($index > $stringy->length()) {
      return $stringy;
    }

    $start = UTF8::substr($stringy->str, 0, $index, $stringy->encoding);
    $end = UTF8::substr($stringy->str, $index, $stringy->length(), $stringy->encoding);

    $stringy->str = $start . $substring . $end;

    return $stringy;
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
    return $this->matchesPattern('^[[:alpha:]]*$');
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
    return empty($this->str);
  }

  /**
   * Returns true if the string contains only alphabetic and numeric chars, false otherwise.
   *
   * @return bool <p>Whether or not $str contains only alphanumeric chars.</p>
   */
  public function isAlphanumeric(): bool
  {
    return $this->matchesPattern('^[[:alnum:]]*$');
  }

  /**
   * Returns true if the string contains only whitespace chars, false otherwise.
   *
   * @return bool <p>Whether or not $str contains only whitespace characters.</p>
   */
  public function isBlank(): bool
  {
    return $this->matchesPattern('^[[:space:]]*$');
  }

  /**
   * Returns true if the string contains only hexadecimal chars, false otherwise.
   *
   * @return bool <p>Whether or not $str contains only hexadecimal chars.</p>
   */
  public function isHexadecimal(): bool
  {
    return $this->matchesPattern('^[[:xdigit:]]*$');
  }

  /**
   * Returns true if the string contains HTML-Tags, false otherwise.
   *
   * @return bool <p>Whether or not $str contains HTML-Tags.</p>
   */
  public function isHtml(): bool
  {
    return UTF8::is_html($this->str);
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
   * Returns true if the string is JSON, false otherwise. Unlike json_decode
   * in PHP 5.x, this method is consistent with PHP 7 and other JSON parsers,
   * in that an empty string is not considered valid JSON.
   *
   * @return bool <p>Whether or not $str is JSON.</p>
   */
  public function isJson(): bool
  {
    if (!isset($this->str[0])) {
      return false;
    }

    \json_decode($this->str);

    return \json_last_error() === JSON_ERROR_NONE;
  }

  /**
   * Returns true if the string contains only lower case chars, false otherwise.
   *
   * @return bool <p>Whether or not $str contains only lower case characters.</p>
   */
  public function isLowerCase(): bool
  {
    if ($this->matchesPattern('^[[:lower:]]*$')) {
      return true;
    }

    return false;
  }

  /**
   * Returns true if the string is serialized, false otherwise.
   *
   * @return bool <p>Whether or not $str is serialized.</p>
   */
  public function isSerialized(): bool
  {
    if (!isset($this->str[0])) {
      return false;
    }

    /** @noinspection PhpUsageOfSilenceOperatorInspection */
    /** @noinspection UnserializeExploitsInspection */
    return $this->str === 'b:0;'
           ||
           @\unserialize($this->str) !== false;
  }

  /**
   * Returns true if the string contains only lower case chars, false
   * otherwise.
   *
   * @return bool <p>Whether or not $str contains only lower case characters.</p>
   */
  public function isUpperCase(): bool
  {
    return $this->matchesPattern('^[[:upper:]]*$');
  }

  /**
   * Returns the last $n characters of the string.
   *
   * @param int $n <p>Number of characters to retrieve from the end.</p>
   *
   * @return static <p>Object with its $str being the last $n chars.</p>
   */
  public function last(int $n): Stringy
  {
    $stringy = static::create($this->str, $this->encoding);

    if ($n <= 0) {
      $stringy->str = '';
    } else {
      return $stringy->substr(-$n);
    }

    return $stringy;
  }

  /**
   * Splits on newlines and carriage returns, returning an array of Stringy
   * objects corresponding to the lines in the string.
   *
   * @return static[] <p>An array of Stringy objects.</p>
   */
  public function lines(): array
  {
    $array = \preg_split('/[\r\n]{1,2}/u', $this->str);
    /** @noinspection CallableInLoopTerminationConditionInspection */
    /** @noinspection ForeachInvariantsInspection */
    for ($i = 0; $i < \count($array); $i++) {
      $array[$i] = static::create($array[$i], $this->encoding);
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
  public function longestCommonPrefix(string $otherStr): Stringy
  {
    $encoding = $this->encoding;
    $maxLength = \min($this->length(), UTF8::strlen($otherStr, $encoding));

    $longestCommonPrefix = '';
    for ($i = 0; $i < $maxLength; $i++) {
      $char = UTF8::substr($this->str, $i, 1, $encoding);

      if ($char == UTF8::substr($otherStr, $i, 1, $encoding)) {
        $longestCommonPrefix .= $char;
      } else {
        break;
      }
    }

    return static::create($longestCommonPrefix, $encoding);
  }

  /**
   * Returns the longest common suffix between the string and $otherStr.
   *
   * @param string $otherStr <p>Second string for comparison.</p>
   *
   * @return static <p>Object with its $str being the longest common suffix.</p>
   */
  public function longestCommonSuffix(string $otherStr): Stringy
  {
    $encoding = $this->encoding;
    $maxLength = \min($this->length(), UTF8::strlen($otherStr, $encoding));

    $longestCommonSuffix = '';
    for ($i = 1; $i <= $maxLength; $i++) {
      $char = UTF8::substr($this->str, -$i, 1, $encoding);

      if ($char == UTF8::substr($otherStr, -$i, 1, $encoding)) {
        $longestCommonSuffix = $char . $longestCommonSuffix;
      } else {
        break;
      }
    }

    return static::create($longestCommonSuffix, $encoding);
  }

  /**
   * Returns the longest common substring between the string and $otherStr.
   * In the case of ties, it returns that which occurs first.
   *
   * @param string $otherStr <p>Second string for comparison.</p>
   *
   * @return static <p>Object with its $str being the longest common substring.</p>
   */
  public function longestCommonSubstring(string $otherStr): Stringy
  {
    // Uses dynamic programming to solve
    // http://en.wikipedia.org/wiki/Longest_common_substring_problem
    $encoding = $this->encoding;
    $stringy = static::create($this->str, $encoding);
    $strLength = $stringy->length();
    $otherLength = UTF8::strlen($otherStr, $encoding);

    // Return if either string is empty
    if ($strLength == 0 || $otherLength == 0) {
      $stringy->str = '';

      return $stringy;
    }

    $len = 0;
    $end = 0;
    $table = \array_fill(
        0,
        $strLength + 1,
        \array_fill(0, $otherLength + 1, 0)
    );

    for ($i = 1; $i <= $strLength; $i++) {
      for ($j = 1; $j <= $otherLength; $j++) {
        $strChar = UTF8::substr($stringy->str, $i - 1, 1, $encoding);
        $otherChar = UTF8::substr($otherStr, $j - 1, 1, $encoding);

        if ($strChar == $otherChar) {
          $table[$i][$j] = $table[$i - 1][$j - 1] + 1;
          if ($table[$i][$j] > $len) {
            $len = $table[$i][$j];
            $end = $i;
          }
        } else {
          $table[$i][$j] = 0;
        }
      }
    }

    $stringy->str = UTF8::substr($stringy->str, $end - $len, $len, $encoding);

    return $stringy;
  }

  /**
   * Returns whether or not a character exists at an index. Offsets may be
   * negative to count from the last character in the string. Implements
   * part of the ArrayAccess interface.
   *
   * @param int $offset <p>The index to check.</p>
   *
   * @return boolean <p>Whether or not the index exists.</p>
   */
  public function offsetExists($offset): bool
  {
    // init
    $length = $this->length();
    $offset = (int)$offset;

    if ($offset >= 0) {
      return ($length > $offset);
    }

    return ($length >= \abs($offset));
  }

  /**
   * Returns the character at the given index. Offsets may be negative to
   * count from the last character in the string. Implements part of the
   * ArrayAccess interface, and throws an OutOfBoundsException if the index
   * does not exist.
   *
   * @param int $offset <p>The <strong>index</strong> from which to retrieve the char.</p>
   *
   * @return string <p>The character at the specified index.</p>
   *
   * @throws \OutOfBoundsException <p>If the positive or negative offset does not exist.</p>
   */
  public function offsetGet($offset): string
  {
    // init
    $offset = (int)$offset;
    $length = $this->length();

    if (
        ($offset >= 0 && $length <= $offset)
        ||
        $length < \abs($offset)
    ) {
      throw new \OutOfBoundsException('No character exists at the index');
    }

    return UTF8::substr($this->str, $offset, 1, $this->encoding);
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
   * @return static <p>Object with a padded $str.</p>
   *
   * @throws \InvalidArgumentException <p>If $padType isn't one of 'right', 'left' or 'both'.</p>
   */
  public function pad(int $length, string $padStr = ' ', string $padType = 'right'): Stringy
  {
    if (!\in_array($padType, ['left', 'right', 'both'], true)) {
      throw new \InvalidArgumentException(
          'Pad expects $padType ' . "to be one of 'left', 'right' or 'both'"
      );
    }

    switch ($padType) {
      case 'left':
        return $this->padLeft($length, $padStr);
      case 'right':
        return $this->padRight($length, $padStr);
      default:
        return $this->padBoth($length, $padStr);
    }
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
  public function padLeft(int $length, string $padStr = ' '): Stringy
  {
    return $this->applyPadding($length - $this->length(), 0, $padStr);
  }

  /**
   * Adds the specified amount of left and right padding to the given string.
   * The default character used is a space.
   *
   * @param int    $left   [optional] <p>Length of left padding. Default: 0</p>
   * @param int    $right  [optional] <p>Length of right padding. Default: 0</p>
   * @param string $padStr [optional] <p>String used to pad. Default: ' '</p>
   *
   * @return static <p>String with padding applied.</p>
   */
  protected function applyPadding(int $left = 0, int $right = 0, string $padStr = ' '): Stringy
  {
    $stringy = static::create($this->str, $this->encoding);

    $length = UTF8::strlen($padStr, $stringy->encoding);

    $strLength = $stringy->length();
    $paddedLength = $strLength + $left + $right;

    if (!$length || $paddedLength <= $strLength) {
      return $stringy;
    }

    $leftPadding = UTF8::substr(
        UTF8::str_repeat(
            $padStr,
            (int)\ceil($left / $length)
        ),
        0,
        $left,
        $stringy->encoding
    );

    $rightPadding = UTF8::substr(
        UTF8::str_repeat(
            $padStr,
            (int)\ceil($right / $length)
        ),
        0,
        $right,
        $stringy->encoding
    );

    $stringy->str = $leftPadding . $stringy->str . $rightPadding;

    return $stringy;
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
  public function padRight(int $length, string $padStr = ' '): Stringy
  {
    return $this->applyPadding(0, $length - $this->length(), $padStr);
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
  public function padBoth(int $length, string $padStr = ' '): Stringy
  {
    $padding = $length - $this->length();

    return $this->applyPadding((int)\floor($padding / 2), (int)\ceil($padding / 2), $padStr);
  }

  /**
   * Returns a new string starting with $string.
   *
   * @param string $string <p>The string to append.</p>
   *
   * @return static <p>Object with appended $string.</p>
   */
  public function prepend(string $string): Stringy
  {
    return static::create($string . $this->str, $this->encoding);
  }

  /**
   * Returns a new string with the prefix $substring removed, if present.
   *
   * @param string $substring <p>The prefix to remove.</p>
   *
   * @return static <p>Object having a $str without the prefix $substring.</p>
   */
  public function removeLeft(string $substring): Stringy
  {
    $stringy = static::create($this->str, $this->encoding);

    if ($stringy->startsWith($substring)) {
      $substringLength = UTF8::strlen($substring, $stringy->encoding);

      return $stringy->substr($substringLength);
    }

    return $stringy;
  }

  /**
   * Returns a new string with the suffix $substring removed, if present.
   *
   * @param string $substring <p>The suffix to remove.</p>
   *
   * @return static <p>Object having a $str without the suffix $substring.</p>
   */
  public function removeRight(string $substring): Stringy
  {
    $stringy = static::create($this->str, $this->encoding);

    if ($stringy->endsWith($substring)) {
      $substringLength = UTF8::strlen($substring, $stringy->encoding);

      return $stringy->substr(0, $stringy->length() - $substringLength);
    }

    return $stringy;
  }

  /**
   * Returns a repeated string given a multiplier.
   *
   * @param int $multiplier <p>The number of times to repeat the string.</p>
   *
   * @return static <p>Object with a repeated str.</p>
   */
  public function repeat(int $multiplier): Stringy
  {
    $repeated = UTF8::str_repeat($this->str, $multiplier);

    return static::create($repeated, $this->encoding);
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
  public function replace(string $search, string $replacement, bool $caseSensitive = true): Stringy
  {
    if ($caseSensitive) {
      $return = UTF8::str_replace($search, $replacement, $this->str);
    } else {
      $return = UTF8::str_ireplace($search, $replacement, $this->str);
    }

    return static::create($return);
  }

  /**
   * Replaces all occurrences of $search in $str by $replacement.
   *
   * @param array        $search        <p>The elements to search for.</p>
   * @param string|array $replacement   <p>The string to replace with.</p>
   * @param bool         $caseSensitive [optional] <p>Whether or not to enforce case-sensitivity. Default: true</p>
   *
   * @return static <p>Object with the resulting $str after the replacements.</p>
   */
  public function replaceAll(array $search, $replacement, bool $caseSensitive = true): Stringy
  {
    if ($caseSensitive) {
      $return = UTF8::str_replace($search, $replacement, $this->str);
    } else {
      $return = UTF8::str_ireplace($search, $replacement, $this->str);
    }

    return static::create($return);
  }

  /**
   * Replaces all occurrences of $search from the beginning of string with $replacement.
   *
   * @param string $search      <p>The string to search for.</p>
   * @param string $replacement <p>The replacement.</p>
   *
   * @return static <p>Object with the resulting $str after the replacements.</p>
   */
  public function replaceBeginning(string $search, string $replacement): Stringy
  {
    $str = $this->regexReplace('^' . \preg_quote($search, '/'), UTF8::str_replace('\\', '\\\\', $replacement));

    return static::create($str, $this->encoding);
  }

  /**
   * Replaces all occurrences of $search from the ending of string with $replacement.
   *
   * @param string $search      <p>The string to search for.</p>
   * @param string $replacement <p>The replacement.</p>
   *
   * @return static <p>Object with the resulting $str after the replacements.</p>
   */
  public function replaceEnding(string $search, string $replacement): Stringy
  {
    $str = $this->regexReplace(\preg_quote($search, '/') . '$', UTF8::str_replace('\\', '\\\\', $replacement));

    return static::create($str, $this->encoding);
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
  public function substringOf(string $needle, bool $beforeNeedle = false): Stringy
  {
    if ('' === $needle) {
      return static::create();
    }

    if (false === $part = UTF8::strstr($this->str, $needle, $beforeNeedle, $this->encoding)) {
      return static::create();
    }

    return static::create($part);
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
  public function substringOfIgnoreCase(string $needle, bool $beforeNeedle = false): Stringy
  {
    if ('' === $needle) {
      return static::create();
    }

    if (false === $part = UTF8::stristr($this->str, $needle, $beforeNeedle, $this->encoding)) {
      return static::create();
    }

    return static::create($part);
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
  public function lastSubstringOf(string $needle, bool $beforeNeedle = false): Stringy
  {
    if ('' === $needle) {
      return static::create();
    }

    if (false === $part = UTF8::strrchr($this->str, $needle, $beforeNeedle, $this->encoding)) {
      return static::create();
    }

    return static::create($part);
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
  public function lastSubstringOfIgnoreCase(string $needle, bool $beforeNeedle = false): Stringy
  {
    if ('' === $needle) {
      return static::create();
    }

    if (false === $part = UTF8::strrichr($this->str, $needle, $beforeNeedle, $this->encoding)) {
      return static::create();
    }

    return static::create($part);
  }

  /**
   * Returns a reversed string. A multibyte version of strrev().
   *
   * @return static <p>Object with a reversed $str.</p>
   */
  public function reverse(): Stringy
  {
    $reversed = UTF8::strrev($this->str);

    return static::create($reversed, $this->encoding);
  }

  /**
   * Truncates the string to a given length, while ensuring that it does not
   * split words. If $substring is provided, and truncating occurs, the
   * string is further truncated so that the substring may be appended without
   * exceeding the desired length.
   *
   * @param int    $length    <p>Desired length of the truncated string.</p>
   * @param string $substring [optional] <p>The substring to append if it can fit. Default: ''</p>
   *
   * @return static <p>Object with the resulting $str after truncating.</p>
   */
  public function safeTruncate(int $length, string $substring = ''): Stringy
  {
    $stringy = static::create($this->str, $this->encoding);
    if ($length >= $stringy->length()) {
      return $stringy;
    }

    // need to further trim the string so we can append the substring
    $encoding = $stringy->encoding;
    $substringLength = UTF8::strlen($substring, $encoding);
    $length -= $substringLength;

    $truncated = UTF8::substr($stringy->str, 0, $length, $encoding);

    // if the last word was truncated
    $strPosSpace = UTF8::strpos($stringy->str, ' ', $length - 1, $encoding);
    if ($strPosSpace != $length) {
      // find pos of the last occurrence of a space, get up to that
      $lastPos = UTF8::strrpos($truncated, ' ', 0, $encoding);

      if ($lastPos !== false || $strPosSpace !== false) {
        $truncated = UTF8::substr($truncated, 0, (int)$lastPos, $encoding);
      }
    }

    $stringy->str = $truncated . $substring;

    return $stringy;
  }

  /**
   * A multibyte string shuffle function. It returns a string with its
   * characters in random order.
   *
   * @return static <p>Object with a shuffled $str.</p>
   */
  public function shuffle(): Stringy
  {
    $shuffledStr = UTF8::str_shuffle($this->str);

    return static::create($shuffledStr, $this->encoding);
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
  public function slugify(string $replacement = '-', string $language = 'de', bool $strToLower = true): Stringy
  {
    $slug = URLify::slug($this->str, $language, $replacement, $strToLower);

    return static::create($slug, $this->encoding);
  }

  /**
   * Remove css media-queries.
   *
   * @return static
   */
  public function stripeCssMediaQueries(): Stringy
  {
    $pattern = '#@media\\s+(?:only\\s)?(?:[\\s{\\(]|screen|all)\\s?[^{]+{.*}\\s*}\\s*#misU';

    return static::create(\preg_replace($pattern, '', $this->str));
  }

  /**
   * Strip all whitespace characters. This includes tabs and newline characters,
   * as well as multibyte whitespace such as the thin space and ideographic space.
   *
   * @return static
   */
  public function stripWhitespace(): Stringy
  {
    return static::create(UTF8::strip_whitespace($this->str));
  }

  /**
   * Remove empty html-tag.
   *
   * e.g.: <tag></tag>
   *
   * @return static
   */
  public function stripeEmptyHtmlTags(): Stringy
  {
    $pattern = "/<[^\/>]*>(([\s]?)*|)<\/[^>]*>/i";

    return static::create(\preg_replace($pattern, '', $this->str));
  }

  /**
   * Converts the string into an valid UTF-8 string.
   *
   * @return static
   */
  public function utf8ify(): Stringy
  {
    return static::create(UTF8::cleanup($this->str));
  }

  /**
   * Create a escape html version of the string via "UTF8::htmlspecialchars()".
   *
   * @return static
   */
  public function escape(): Stringy
  {
    $str = UTF8::htmlspecialchars(
        $this->str,
        ENT_QUOTES | ENT_SUBSTITUTE,
        $this->encoding
    );

    return static::create($str, $this->encoding);
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
  public function extractText(string $search = '', int $length = null, string $replacerForSkippedText = '…'): Stringy
  {
    // init
    $text = $this->str;

    if (empty($text)) {
      return static::create('', $this->encoding);
    }

    $trimChars = "\t\r\n -_()!~?=+/*\\,.:;\"'[]{}`&";

    if ($length === null) {
      $length = (int)\round($this->length() / 2, 0);
    }

    if (empty($search)) {

      $stringLength = UTF8::strlen($text, $this->encoding);

      if ($length > 0) {
        $end = ($length - 1) > $stringLength ? $stringLength : ($length - 1);
      } else {
        $end = 0;
      }

      $pos = \min(
          UTF8::strpos($text, ' ', $end, $this->encoding),
          UTF8::strpos($text, '.', $end, $this->encoding)
      );

      if ($pos) {
        return static::create(
            \rtrim(
                UTF8::substr($text, 0, $pos, $this->encoding),
                $trimChars
            ) . $replacerForSkippedText,
            $this->encoding
        );
      }

      return static::create($text, $this->encoding);
    }

    $wordPos = UTF8::stripos(
        $text,
        $search,
        0,
        $this->encoding
    );
    $halfSide = (int)($wordPos - $length / 2 + UTF8::strlen($search, $this->encoding) / 2);

    if ($halfSide > 0) {

      $halfText = UTF8::substr($text, 0, $halfSide, $this->encoding);
      $pos_start = \max(UTF8::strrpos($halfText, ' ', 0), UTF8::strrpos($halfText, '.', 0));

      if (!$pos_start) {
        $pos_start = 0;
      }

    } else {
      $pos_start = 0;
    }

    if ($wordPos && $halfSide > 0) {
      $l = $pos_start + $length - 1;
      $realLength = UTF8::strlen($text, $this->encoding);

      if ($l > $realLength) {
        $l = $realLength;
      }

      $pos_end = \min(
                     UTF8::strpos($text, ' ', $l, $this->encoding),
                     UTF8::strpos($text, '.', $l, $this->encoding)
                 ) - $pos_start;

      if (!$pos_end || $pos_end <= 0) {
        $extract = $replacerForSkippedText . \ltrim(
                UTF8::substr(
                    $text,
                    $pos_start,
                    UTF8::strlen($text),
                    $this->encoding
                ),
                $trimChars
            );
      } else {
        $extract = $replacerForSkippedText . \trim(
                UTF8::substr(
                    $text,
                    $pos_start,
                    $pos_end,
                    $this->encoding
                ),
                $trimChars
            ) . $replacerForSkippedText;
      }

    } else {

      $l = $length - 1;
      $trueLength = UTF8::strlen($text, $this->encoding);

      if ($l > $trueLength) {
        $l = $trueLength;
      }

      $pos_end = \min(
          UTF8::strpos($text, ' ', $l, $this->encoding),
          UTF8::strpos($text, '.', $l, $this->encoding)
      );

      if ($pos_end) {
        $extract = \rtrim(
                       UTF8::substr($text, 0, $pos_end, $this->encoding),
                       $trimChars
                   ) . $replacerForSkippedText;
      } else {
        $extract = $text;
      }
    }

    return static::create($extract, $this->encoding);
  }


  /**
   * Try to remove all XSS-attacks from the string.
   *
   * @return static
   */
  public function removeXss(): Stringy
  {
    static $antiXss = null;

    if ($antiXss === null) {
      $antiXss = new AntiXSS();
    }

    $str = $antiXss->xss_clean($this->str);

    return static::create($str, $this->encoding);
  }

  /**
   * Remove all breaks [<br> | \r\n | \r | \n | ...] from the string.
   *
   * @param string $replacement [optional] <p>Default is a empty string.</p>
   *
   * @return static
   */
  public function removeHtmlBreak(string $replacement = ''): Stringy
  {
    $str = (string)\preg_replace('#/\r\n|\r|\n|<br.*/?>#isU', $replacement, $this->str);

    return static::create($str, $this->encoding);
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
  public function removeHtml(string $allowableTags = null): Stringy
  {
    $str = \strip_tags($this->str, $allowableTags);

    return static::create($str, $this->encoding);
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
  public function slice(int $start, int $end = null): Stringy
  {
    if ($end === null) {
      $length = $this->length();
    } elseif ($end >= 0 && $end <= $start) {
      return static::create('', $this->encoding);
    } elseif ($end < 0) {
      $length = $this->length() + $end - $start;
    } else {
      $length = $end - $start;
    }

    $str = UTF8::substr($this->str, $start, $length, $this->encoding);

    return static::create($str, $this->encoding);
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
  public function split(string $pattern, int $limit = -1): array
  {
    if ($limit === 0) {
      return [];
    }

    // this->split errors when supplied an empty pattern in < PHP 5.4.13
    // and current versions of HHVM (3.8 and below)
    if ($pattern === '') {
      return [static::create($this->str, $this->encoding)];
    }

    // this->split returns the remaining unsplit string in the last index when
    // supplying a limit
    if ($limit > 0) {
      $limit += 1;
    } else {
      $limit = -1;
    }

    $array = \preg_split('/' . \preg_quote($pattern, '/') . '/u', $this->str, $limit);

    if ($limit > 0 && \count($array) === $limit) {
      \array_pop($array);
    }

    /** @noinspection CallableInLoopTerminationConditionInspection */
    /** @noinspection ForeachInvariantsInspection */
    for ($i = 0; $i < \count($array); $i++) {
      $array[$i] = static::create($array[$i], $this->encoding);
    }

    return $array;
  }

  /**
   * Surrounds $str with the given substring.
   *
   * @param string $substring <p>The substring to add to both sides.</P>
   *
   * @return static <p>Object whose $str had the substring both prepended and appended.</p>
   */
  public function surround(string $substring): Stringy
  {
    $str = \implode('', [$substring, $this->str, $substring]);

    return static::create($str, $this->encoding);
  }

  /**
   * Returns a case swapped version of the string.
   *
   * @return static <p>Object whose $str has each character's case swapped.</P>
   */
  public function swapCase(): Stringy
  {
    $stringy = static::create($this->str, $this->encoding);

    $stringy->str = UTF8::swapCase($stringy->str, $stringy->encoding);

    return $stringy;
  }

  /**
   * Returns a string with smart quotes, ellipsis characters, and dashes from
   * Windows-1252 (commonly used in Word documents) replaced by their ASCII
   * equivalents.
   *
   * @return static <p>Object whose $str has those characters removed.</p>
   */
  public function tidy(): Stringy
  {
    $str = UTF8::normalize_msword($this->str);

    return static::create($str, $this->encoding);
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
  public function titleize(array $ignore = null): Stringy
  {
    $stringy = static::create($this->trim(), $this->encoding);
    $encoding = $this->encoding;

    $stringy->str = (string)\preg_replace_callback(
        '/([\S]+)/u',
        function ($match) use ($encoding, $ignore) {
          if ($ignore && \in_array($match[0], $ignore, true)) {
            return $match[0];
          }

          $stringy = new static($match[0], $encoding);

          return (string)$stringy->toLowerCase()->upperCaseFirst();
        },
        $stringy->str
    );

    return $stringy;
  }

  /**
   * Converts all characters in the string to lowercase.
   *
   * @return static <p>Object with all characters of $str being lowercase.</p>
   */
  public function toLowerCase(): Stringy
  {
    $str = UTF8::strtolower($this->str, $this->encoding);

    return static::create($str, $this->encoding);
  }

  /**
   * Returns true if the string is base64 encoded, false otherwise.
   *
   * @return bool <p>Whether or not $str is base64 encoded.</p>
   */
  public function isBase64(): bool
  {
    return UTF8::is_base64($this->str);
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
  public function toAscii(bool $strict = false): Stringy
  {
    $str = UTF8::to_ascii($this->str, '?', $strict);

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
    $key = $this->toLowerCase()->str;
    $map = [
        'true'  => true,
        '1'     => true,
        'on'    => true,
        'yes'   => true,
        'false' => false,
        '0'     => false,
        'off'   => false,
        'no'    => false,
    ];

    if (\array_key_exists($key, $map)) {
      return $map[$key];
    }

    if (\is_numeric($this->str)) {
      return ((int)$this->str > 0);
    }

    return (bool)$this->regexReplace('[[:space:]]', '')->str;
  }

  /**
   * Return Stringy object as string, but you can also use (string) for automatically casting the object into a string.
   *
   * @return string
   */
  public function toString(): string
  {
    return (string)$this->str;
  }

  /**
   * Converts each tab in the string to some number of spaces, as defined by
   * $tabLength. By default, each tab is converted to 4 consecutive spaces.
   *
   * @param int $tabLength [optional] <p>Number of spaces to replace each tab with. Default: 4</p>
   *
   * @return static <p>Object whose $str has had tabs switched to spaces.</p>
   */
  public function toSpaces(int $tabLength = 4): Stringy
  {
    $spaces = UTF8::str_repeat(' ', $tabLength);
    $str = UTF8::str_replace("\t", $spaces, $this->str);

    return static::create($str, $this->encoding);
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
  public function toTabs(int $tabLength = 4): Stringy
  {
    $spaces = UTF8::str_repeat(' ', $tabLength);
    $str = UTF8::str_replace($spaces, "\t", $this->str);

    return static::create($str, $this->encoding);
  }

  /**
   * Converts the first character of each word in the string to uppercase.
   *
   * @return static  Object with all characters of $str being title-cased
   */
  public function toTitleCase(): Stringy
  {
    // "mb_convert_case()" used a polyfill from the "UTF8"-Class
    $str = \mb_convert_case($this->str, MB_CASE_TITLE, $this->encoding);

    return static::create($str, $this->encoding);
  }

  /**
   * Converts all characters in the string to uppercase.
   *
   * @return static  Object with all characters of $str being uppercase
   */
  public function toUpperCase(): Stringy
  {
    $str = UTF8::strtoupper($this->str, $this->encoding);

    return static::create($str, $this->encoding);
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
  public function trimLeft(string $chars = null): Stringy
  {
    if (!$chars) {
      $chars = '[:space:]';
    } else {
      $chars = \preg_quote($chars, '/');
    }

    return $this->regexReplace("^[$chars]+", '');
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
  public function trimRight(string $chars = null): Stringy
  {
    if (!$chars) {
      $chars = '[:space:]';
    } else {
      $chars = \preg_quote($chars, '/');
    }

    return $this->regexReplace("[$chars]+\$", '');
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
  public function truncate(int $length, string $substring = ''): Stringy
  {
    $stringy = static::create($this->str, $this->encoding);
    if ($length >= $stringy->length()) {
      return $stringy;
    }

    // Need to further trim the string so we can append the substring
    $substringLength = UTF8::strlen($substring, $stringy->encoding);
    $length -= $substringLength;

    $truncated = UTF8::substr($stringy->str, 0, $length, $stringy->encoding);
    $stringy->str = $truncated . $substring;

    return $stringy;
  }

  /**
   * Returns a lowercase and trimmed string separated by underscores.
   * Underscores are inserted before uppercase characters (with the exception
   * of the first character of the string), and in place of spaces as well as
   * dashes.
   *
   * @return static <p>Object with an underscored $str.</p>
   */
  public function underscored(): Stringy
  {
    return $this->delimit('_');
  }

  /**
   * Returns an UpperCamelCase version of the supplied string. It trims
   * surrounding spaces, capitalizes letters following digits, spaces, dashes
   * and underscores, and removes spaces, dashes, underscores.
   *
   * @return static  <p>Object with $str in UpperCamelCase.</p>
   */
  public function upperCamelize(): Stringy
  {
    return $this->camelize()->upperCaseFirst();
  }

  /**
   * Returns a camelCase version of the string. Trims surrounding spaces,
   * capitalizes letters following digits, spaces, dashes and underscores,
   * and removes spaces, dashes, as well as underscores.
   *
   * @return static <p>Object with $str in camelCase.</p>
   */
  public function camelize(): Stringy
  {
    $encoding = $this->encoding;
    $stringy = $this->trim()->lowerCaseFirst();
    $stringy->str = (string)\preg_replace('/^[-_]+/', '', $stringy->str);

    $stringy->str = (string)\preg_replace_callback(
        '/[-_\s]+(.)?/u',
        function ($match) use ($encoding) {
          if (isset($match[1])) {
            return UTF8::strtoupper($match[1], $encoding);
          }

          return '';
        },
        $stringy->str
    );

    $stringy->str = (string)\preg_replace_callback(
        '/[\d]+(.)?/u',
        function ($match) use ($encoding) {
          return UTF8::strtoupper($match[0], $encoding);
        },
        $stringy->str
    );

    return $stringy;
  }

  /**
   * Convert a string to e.g.: "snake_case"
   *
   * @return static <p>Object with $str in snake_case.</p>
   */
  public function snakeize(): Stringy
  {
    $str = $this->str;

    $encoding = $this->encoding;
    $str = UTF8::normalize_whitespace($str);
    $str = \str_replace('-', '_', $str);

    $str = (string)\preg_replace_callback(
        '/([\d|A-Z])/u',
        function ($matches) use ($encoding) {
          $match = $matches[1];
          $matchInt = (int)$match;

          if ("$matchInt" == $match) {
            return '_' . $match . '_';
          }

          return '_' . UTF8::strtolower($match, $encoding);
        },
        $str
    );

    $str = (string)\preg_replace(
        [

            '/\s+/',      // convert spaces to "_"
            '/^\s+|\s+$/',  // trim leading & trailing spaces
            '/_+/',         // remove double "_"
        ],
        [
            '_',
            '',
            '_',
        ],
        $str
    );

    $str = UTF8::trim($str, '_'); // trim leading & trailing "_"
    $str = UTF8::trim($str); // trim leading & trailing whitespace

    return static::create($str, $this->encoding);
  }

  /**
   * Converts the first character of the string to lower case.
   *
   * @return static <p>Object with the first character of $str being lower case.</p>
   */
  public function lowerCaseFirst(): Stringy
  {
    $first = UTF8::substr($this->str, 0, 1, $this->encoding);
    $rest = UTF8::substr($this->str, 1, $this->length() - 1, $this->encoding);

    $str = UTF8::strtolower($first, $this->encoding) . $rest;

    return static::create($str, $this->encoding);
  }

  /**
   * Shorten the string after $length, but also after the next word.
   *
   * @param int    $length
   * @param string $strAddOn [optional] <p>Default: '…'</p>
   *
   * @return static
   */
  public function shortenAfterWord(int $length, string $strAddOn = '…'): Stringy
  {
    $string = UTF8::str_limit_after_word($this->str, $length, $strAddOn);

    return static::create($string);
  }

  /**
   * Line-Wrap the string after $limit, but also after the next word.
   *
   * @param int $limit
   *
   * @return static
   */
  public function lineWrapAfterWord(int $limit): Stringy
  {
    $strings = (array)\preg_split('/\\r\\n|\\r|\\n/', $this->str);

    $string = '';
    foreach ($strings as $value) {
      $string .= wordwrap($value, $limit);
      $string .= "\n";
    }

    return static::create($string);
  }

  /**
   * Gets the substring after the first occurrence of a separator.
   * If no match is found returns new empty Stringy object.
   *
   * @param string $separator
   *
   * @return static
   */
  public function afterFirst(string $separator): Stringy
  {
    if ($separator === '') {
      return static::create();
    }

    if ($this->str === '') {
      return static::create();
    }

    if (($offset = $this->indexOf($separator)) === false) {
      return static::create();
    }

    return static::create(
        UTF8::substr(
            $this->str,
            $offset + UTF8::strlen($separator, $this->encoding),
            null,
            $this->encoding
        ),
        $this->encoding
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
  public function afterFirstIgnoreCase(string $separator): Stringy
  {
    if ($separator === '') {
      return static::create();
    }

    if ($this->str === '') {
      return static::create();
    }

    if (($offset = $this->indexOfIgnoreCase($separator)) === false) {
      return static::create();
    }

    return static::create(
        UTF8::substr(
            $this->str,
            $offset + UTF8::strlen($separator, $this->encoding),
            null,
            $this->encoding
        ),
        $this->encoding
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
  public function afterLastIgnoreCase(string $separator): Stringy
  {
    if ($separator === '') {
      return static::create();
    }

    if ($this->str === '') {
      return static::create();
    }

    $offset = $this->indexOfLastIgnoreCase($separator);
    if ($offset === false) {
      return static::create('', $this->encoding);
    }

    return static::create(
        UTF8::substr(
            $this->str,
            $offset + UTF8::strlen($separator, $this->encoding),
            null,
            $this->encoding
        ),
        $this->encoding
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
  public function afterLast(string $separator): Stringy
  {
    if ($separator === '') {
      return static::create();
    }

    if ($this->str === '') {
      return static::create();
    }

    $offset = $this->indexOfLast($separator);
    if ($offset === false) {
      return static::create('', $this->encoding);
    }

    return static::create(
        UTF8::substr(
            $this->str,
            $offset + UTF8::strlen($separator, $this->encoding),
            null,
            $this->encoding
        ),
        $this->encoding
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
  public function beforeFirst(string $separator): Stringy
  {
    if ($separator === '') {
      return static::create();
    }

    if ($this->str === '') {
      return static::create();
    }

    $offset = $this->indexOf($separator);
    if ($offset === false) {
      return static::create('', $this->encoding);
    }

    return static::create(
        UTF8::substr(
            $this->str,
            0,
            $offset,
            $this->encoding
        ),
        $this->encoding
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
  public function beforeFirstIgnoreCase(string $separator): Stringy
  {
    if ($separator === '') {
      return static::create();
    }

    if ($this->str === '') {
      return static::create();
    }

    $offset = $this->indexOfIgnoreCase($separator);
    if ($offset === false) {
      return static::create('', $this->encoding);
    }

    return static::create(
        UTF8::substr(
            $this->str,
            0,
            $offset,
            $this->encoding
        ),
        $this->encoding
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
  public function beforeLast(string $separator): Stringy
  {
    if ($separator === '') {
      return static::create();
    }

    if ($this->str === '') {
      return static::create();
    }

    $offset = $this->indexOfLast($separator);
    if ($offset === false) {
      return static::create('', $this->encoding);
    }

    return static::create(
        UTF8::substr(
            $this->str,
            0,
            $offset,
            $this->encoding
        ),
        $this->encoding
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
  public function beforeLastIgnoreCase(string $separator): Stringy
  {
    if ($separator === '') {
      return static::create();
    }

    if ($this->str === '') {
      return static::create();
    }

    $offset = $this->indexOfLastIgnoreCase($separator);
    if ($offset === false) {
      return static::create('', $this->encoding);
    }

    return static::create(
        UTF8::substr(
            $this->str,
            0,
            $offset,
            $this->encoding
        ),
        $this->encoding
    );
  }

  /**
   * Returns the string with the first letter of each word capitalized,
   * except for when the word is a name which shouldn't be capitalized.
   *
   * @return static <p>Object with $str capitalized.</p>
   */
  public function capitalizePersonalName(): Stringy
  {
    $stringy = $this->collapseWhitespace();
    $stringy->str = $this->capitalizePersonalNameByDelimiter($stringy->str, ' ')->toString();
    $stringy->str = $this->capitalizePersonalNameByDelimiter($stringy->str, '-')->toString();

    return static::create($stringy, $this->encoding);
  }

  /**
   * @param string $word
   *
   * @return static <p>Object with $str capitalized.</p>
   */
  protected function capitalizeWord(string $word): Stringy
  {
    $encoding = $this->encoding;

    $firstCharacter = UTF8::substr($word, 0, 1, $encoding);
    $restOfWord = UTF8::substr($word, 1, null, $encoding);
    $firstCharacterUppercased = UTF8::strtoupper($firstCharacter, $encoding);

    return static::create($firstCharacterUppercased . $restOfWord, $encoding);
  }

  /**
   * Personal names such as "Marcus Aurelius" are sometimes typed incorrectly using lowercase ("marcus aurelius").
   *
   * @param string $names
   * @param string $delimiter
   *
   * @return static
   */
  protected function capitalizePersonalNameByDelimiter(string $names, string $delimiter): Stringy
  {
    // init
    $namesArray = \explode($delimiter, $names);
    $encoding = $this->encoding;

    $specialCases = [
        'names'    => [
            'ab',
            'af',
            'al',
            'and',
            'ap',
            'bint',
            'binte',
            'da',
            'de',
            'del',
            'den',
            'der',
            'di',
            'dit',
            'ibn',
            'la',
            'mac',
            'nic',
            'of',
            'ter',
            'the',
            'und',
            'van',
            'von',
            'y',
            'zu',
        ],
        'prefixes' => [
            'al-',
            "d'",
            'ff',
            "l'",
            'mac',
            'mc',
            'nic',
        ],
    ];

    foreach ($namesArray as &$name) {
      if (\in_array($name, $specialCases['names'], true)) {
        continue;
      }

      $continue = false;

      if ($delimiter == '-') {
        foreach ($specialCases['names'] as $beginning) {
          if (UTF8::strpos($name, $beginning, 0, $encoding) === 0) {
            $continue = true;
          }
        }
      }

      foreach ($specialCases['prefixes'] as $beginning) {
        if (UTF8::strpos($name, $beginning, 0, $encoding) === 0) {
          $continue = true;
        }
      }

      if ($continue) {
        continue;
      }

      $name = $this->capitalizeWord($name);
    }

    return static::create(\implode($delimiter, $namesArray), $encoding);
  }
}
