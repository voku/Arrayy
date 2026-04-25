<?php

declare(strict_types=1);

namespace Arrayy {
    use Arrayy\Collection\Collection;
    use Arrayy\TypeCheck\TypeCheckArray;
    use Arrayy\TypeCheck\TypeCheckInterface;

    if (!\function_exists('Arrayy\create')) {
        /**
         * Creates a Arrayy object.
         *
         * @param mixed $data
         *
         * @return Arrayy<int|string,mixed,array<int|string,mixed>>
         */
        function create($data): Arrayy
        {
            return Arrayy::create($data);
        }
    }

    if (!\function_exists('Arrayy\collection')) {
        /**
         * Creates a Collection object.
         *
         * @param string|TypeCheckArray|TypeCheckInterface[] $type
         * @param array<mixed>                               $data
         *
         * @return Collection
         *
         * @template T
         * @phpstan-param T $type
         * @phpstan-return Collection<array-key,T>
         */
        function collection($type, $data = []): Collection
        {
            /** @phpstan-var Collection<array-key,T> */
            return Collection::construct($type, $data);
        }
    }

    /**
     * @param array<mixed> $array
     * @param mixed        $fallback <p>This fallback will be used, if the array is empty.</p>
     *
     * @return mixed|null
     *
     * @template TLast
     * @template TLastFallback
     * @phpstan-param TLast[] $array
     * @phpstan-param TLastFallback $fallback
     * @phpstan-return TLast|TLastFallback
     */
    function array_last(array $array, $fallback = null)
    {
        $key_last = \array_key_last($array);
        if ($key_last === null) {
            return $fallback;
        }

        return $array[$key_last];
    }

    /**
     * @param array<mixed> $array
     * @param mixed        $fallback <p>This fallback will be used, if the array is empty.</p>
     *
     * @return mixed|null
     *
     * @template TFirst
     * @template TFirstFallback
     * @phpstan-param TFirst[] $array
     * @phpstan-param TFirstFallback $fallback
     * @phpstan-return TFirst|TFirstFallback
     */
    function array_first(array $array, $fallback = null)
    {
        $key_first = \array_key_first($array);
        if ($key_first === null) {
            return $fallback;
        }

        return $array[$key_first];
    }
}
