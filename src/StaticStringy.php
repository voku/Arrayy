<?php

declare(strict_types=1);

namespace Stringy;

/**
 * Class StaticStringy
 *
 * INFO: "Method Parameter Information" via PhpStorm |
 * https://www.jetbrains.com/phpstorm/help/viewing-method-parameter-information.html
 *
 * @method static Stringy append(string $stringInput, string $stringAppend, string $encoding = null)
 * @method static Stringy appendPassword(string $stringInput, int $length)
 * @method static Stringy appendUniqueIdentifier(string $stringInput, string $extraPrefix = '')
 * @method static Stringy appendRandomString(string $stringInput, int $length, string $possibleChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
 * @method static Stringy at(string $stringInput, int $index, string $encoding = null)
 * @method static Stringy between(string $stringInput, string $start, string $end, int $offset = 0, string $encoding = null)
 * @method static Stringy camelize(string $stringInput, string $encoding = null)
 * @method static string[] chars(string $stringInput, string $encoding = null)
 * @method static Stringy collapseWhitespace(string $stringInput, string $encoding = null)
 * @method static bool   contains(string $stringInput, string $needle, bool $caseSensitive = true, string $encoding = null)
 * @method static bool   containsAll(string $stringInput, array $needle, bool $caseSensitive = true, string $encoding = null)
 * @method static bool   containsAny(string $stringInput, string $needle, bool $caseSensitive = true, string $encoding = null)
 * @method static int    count(string $stringInput, string $encoding = null)
 * @method static int    countSubstr(string $stringInput, string $substring, bool $caseSensitive = true, string $encoding = null)
 * @method static Stringy dasherize(string $stringInput, string $encoding = null)
 * @method static Stringy delimit(string $stringInput, string  $delimiter, string $encoding = null)
 * @method static bool   endsWith(string $stringInput, string $substring, bool $caseSensitive = true, string $encoding = null)
 * @method static Stringy ensureLeft(string $stringInput, string $substring, string $encoding = null)
 * @method static Stringy ensureRight(string $stringInput, string $substring, string $encoding = null)
 * @method static Stringy escape(string $stringInput, string $encoding = null)
 * @method static Stringy extractText(string $stringInput, string $search = '', int $length = null, string $ellipsis = '...')
 * @method static Stringy first(string $stringInput, int $n, string $encoding = null)
 * @method static bool   hasLowerCase(string $stringInput, string $encoding = null)
 * @method static bool   hasUpperCase(string $stringInput, string $encoding = null)
 * @method static Stringy htmlDecode(string $stringInput, int $flags = ENT_COMPAT, string $encoding = null)
 * @method static Stringy htmlEncode(string $stringInput, int $flags = ENT_COMPAT, string $encoding = null)
 * @method static Stringy humanize(string $stringInput, string $encoding = null)
 * @method static int|bool indexOf(string $stringInput, string $needle, int $offset = 0, string $encoding = null)
 * @method static int|bool indexOfLast(string $stringInput, string $needle, int $offset = 0, string $encoding = null)
 * @method static Stringy insert(string $stringInput, string $substring, int $index = 0, string $encoding = null)
 * @method static bool is(string $stringInput, string $pattern, string $encoding = null)
 * @method static bool isAlpha(string $stringInput, string $encoding = null)
 * @method static bool isAlphanumeric(string $stringInput, string $encoding = null)
 * @method static bool isBase64(string $stringInput, string $encoding = null)
 * @method static bool isBlank(string $stringInput, string $encoding = null)
 * @method static bool isHexadecimal(string $stringInput, string $encoding = null)
 * @method static bool isHtml(string $stringInput, string $encoding = null)
 * @method static bool isJson(string $stringInput, string $encoding = null)
 * @method static bool isLowerCase(string $stringInput, string $encoding = null)
 * @method static bool isSerialized(string $stringInput, string $encoding = null)
 * @method static bool isUpperCase(string $stringInput, string $encoding = null)
 * @method static Stringy last(string $stringInput, string $encoding = null)
 * @method static int    length(string $stringInput, string $encoding = null)
 * @method static string lineWrapAfterWord(string $stringInput, int $limit)
 * @method static Stringy[] lines(string $stringInput, string $encoding = null)
 * @method static Stringy longestCommonPrefix(string $stringInput, string $otherStr, string $encoding = null)
 * @method static Stringy longestCommonSuffix(string $stringInput, string $otherStr, string $encoding = null)
 * @method static Stringy longestCommonSubstring(string $stringInput, string $otherStr, string $encoding = null)
 * @method static Stringy lowerCaseFirst(string $stringInput, string $encoding = null)
 * @method static bool   offsetExists(string $stringInput, mixed $offset, string $encoding = null)
 * @method static string offsetGet(string $stringInput, mixed $offset, string $encoding = null)
 * @method static Stringy pad(string $stringInput, int $length, string $padStr = ' ', string $padType = 'right', string $encoding = null)
 * @method static Stringy padBoth(string $stringInput, int $length, string $padStr = ' ', string $encoding = null)
 * @method static Stringy padLeft(string $stringInput, int $length, string $padStr = ' ', string $encoding = null)
 * @method static Stringy padRight(string $stringInput, int $length, string $padStr = ' ', string $encoding = null)
 * @method static Stringy prepend(string $stringInput, string $string, string $encoding = null)
 * @method static Stringy regexReplace(string $stringInput, string $pattern, string $replacement, string $delimiter = '/')
 * @method static Stringy removeLeft(string $stringInput, string $substring, string $encoding = null)
 * @method static Stringy removeRight(string $stringInput, string $substring, string $encoding = null)
 * @method static Stringy removeHtml(string $stringInput, string $allowableTags = null, string $encoding = null)
 * @method static Stringy removeXss(string $stringInput, string $encoding = null)
 * @method static Stringy repeat(string $stringInput, int $multiplier, string $encoding = null)
 * @method static Stringy replace(string $stringInput, string $search, string $replacement, bool $caseSensitive, string $encoding = null)
 * @method static Stringy replaceAll(string $stringInput, array $search, string $replacement, bool $caseSensitive, string $encoding = null)
 * @method static Stringy reverse(string $stringInput, string $encoding = null)
 * @method static Stringy safeTruncate(string $stringInput, int $length, string $substring = '', string $encoding = null)
 * @method static Stringy shuffle(string $stringInput, string $encoding = null)
 * @method static Stringy shortenAfterWord(string $stringInput, int $length, string $strAddOn)
 * @method static Stringy slugify(string $stringInput, string $replacement = '-', string $language = 'de', boolean $strToLower = true)
 * @method static Stringy stripeCssMediaQueries(string $stringInput)
 * @method static Stringy stripeEmptyHtmlTags(string $stringInput)
 * @method static Stringy utf8ify(string $stringInput)
 * @method static Stringy snakeize(string $stringInput, string $encoding = null)
 * @method static bool   startsWith(string $stringInput, string $substring, bool $caseSensitive = true, string $encoding = null)
 * @method static Stringy slice(string $stringInput, int $start, int $end = null, string $encoding = null)
 * @method static Stringy[]  split(string $stringInput, string $pattern, int $limit = null, string $encoding = null)
 * @method static Stringy substr(string $stringInput, int $start, int $length = null, string $encoding = null)
 * @method static Stringy surround(string $stringInput, string $substring, string $encoding = null)
 * @method static Stringy swapCase(string $stringInput, string $encoding = null)
 * @method static Stringy tidy(string $stringInput, string $encoding = null)
 * @method static Stringy titleize(string $stringInput, string $encoding = null)
 * @method static Stringy toAscii(string $stringInput)
 * @method static Stringy toBoolean(string $stringInput, string $encoding = null)
 * @method static Stringy toString(string $stringInput)
 * @method static Stringy toLowerCase(string $stringInput, string $encoding = null)
 * @method static Stringy toSpaces(string $stringInput, int $tabLength = 4, string $encoding = null)
 * @method static Stringy toTabs(string $stringInput, int $tabLength = 4, string $encoding = null)
 * @method static Stringy toTitleCase(string $stringInput, string $encoding = null)
 * @method static Stringy toUpperCase(string $stringInput, string $encoding = null)
 * @method static Stringy trim(string $stringInput, string $chars = null, string $encoding = null)
 * @method static Stringy trimLeft(string $stringInput, string $chars = null, string $encoding = null)
 * @method static Stringy trimRight(string $stringInput, string $chars = null, string $encoding = null)
 * @method static Stringy truncate(string $stringInput, int $length, string $substring = '', string $encoding = null)
 * @method static Stringy underscored(string $stringInput, string $encoding = null)
 * @method static Stringy upperCamelize(string $stringInput, string $encoding = null)
 * @method static Stringy upperCaseFirst(string $stringInput, string $encoding = null)
 */
class StaticStringy
{
    /**
     * A mapping of method names to the numbers of arguments it accepts. Each
     * should be two more than the equivalent Stringy method. Necessary as
     * static methods place the optional $encoding as the last parameter.
     *
     * @var array
     */
    protected static $methodArgs = null;

    /**
     * Creates an instance of Stringy and invokes the given method with the
     * rest of the passed arguments. The optional encoding is expected to be
     * the last argument. For example, the following:
     * StaticStringy::slice('fòôbàř', 0, 3, 'UTF-8'); translates to
     * Stringy::create('fòôbàř', 'UTF-8')->slice(0, 3);
     * The result is not cast, so the return value may be of type Stringy,
     * integer, boolean, etc.
     *
     * @param string  $name
     * @param mixed[] $arguments
     *
     * @return Stringy
     */
    public static function __callStatic($name, array $arguments)
    {
        if (!static::$methodArgs) {
            $stringyClass = new \ReflectionClass(Stringy::class);
            $methods = $stringyClass->getMethods(\ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                $params = $method->getNumberOfParameters() + 2;
                static::$methodArgs[$method->name] = $params;
            }
        }

        if (!isset(static::$methodArgs[$name])) {
            throw new \BadMethodCallException($name . ' is not a valid method');
        }

        $numArgs = \count($arguments);
        $str = ($numArgs) ? $arguments[0] : '';

        if ($numArgs === static::$methodArgs[$name]) {
            $args = \array_slice($arguments, 1, -1);
            $encoding = $arguments[$numArgs - 1];
        } else {
            $args = \array_slice($arguments, 1);
            $encoding = null;
        }

        $stringy = Stringy::create($str, $encoding);

        return \call_user_func_array([$stringy, $name], $args);
    }
}
