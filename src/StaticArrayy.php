<?php

declare(strict_types=1);

namespace Arrayy;

/**
 * INFO: "Method Parameter Information" via PhpStorm |
 * https://www.jetbrains.com/phpstorm/help/viewing-method-parameter-information.html
 *
 * @deprecated please use e.g. "\Arrayy\create()"
 */
class StaticArrayy
{
    /**
     * A mapping of method names to the numbers of arguments it accepts. Each
     * should be two more than the equivalent Arrayy method. Necessary as
     * static methods place the optional $encoding as the last parameter.
     *
     * @var int[]|string[]
     */
    protected static $methodArgs;

    /**
     * Creates an instance of Arrayy and invokes the given method
     *
     * @param string  $name
     * @param mixed[] $arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $name, $arguments)
    {
        if (!static::$methodArgs) {
            $arrayyClass = new \ReflectionClass(Arrayy::class);
            $methods = $arrayyClass->getMethods(\ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                $params = $method->getNumberOfParameters() + 2;
                static::$methodArgs[$method->name] = $params;
            }
        }

        if (!isset(static::$methodArgs[$name])) {
            throw new \BadMethodCallException($name . ' is not a valid method');
        }

        $numArgs = \count($arguments);
        $array = $numArgs ? $arguments[0] : '';

        if ($numArgs === static::$methodArgs[$name]) {
            $args = \array_slice($arguments, 1, -1);
        } else {
            $args = \array_slice($arguments, 1);
        }

        $arrayy = Arrayy::create($array);

        return \call_user_func_array([$arrayy, $name], $args);
    }

    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// GENERATE /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Generate an array from a range.
     *
     * @param int      $base The base number
     * @param int|null $stop The stopping point
     * @param int      $step How many to increment of
     *
     * @return Arrayy<int,int>
     *
     * @psalm-suppress InvalidReturnStatement - why?
     * @psalm-suppress InvalidReturnType - why?
     */
    public static function range(int $base, int $stop = null, int $step = 1): Arrayy
    {
        if ($stop !== null) {
            $start = $base;
        } else {
            $start = 1;
            $stop = $base;
        }

        return Arrayy::create(\range($start, $stop, $step));
    }

    /**
     * Fill an array with $times times some $data.
     *
     * @param float|int|string|null $data
     * @param int                   $times
     *
     * @return Arrayy<int,float|int|string|null>
     *
     * @psalm-suppress InvalidReturnStatement - why?
     * @psalm-suppress InvalidReturnType - why?
     */
    public static function repeat($data, int $times): Arrayy
    {
        if ($times === 0 || empty($data)) {
            return Arrayy::create();
        }

        return Arrayy::create(\array_fill(0, $times, $data));
    }
}
