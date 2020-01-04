<?php

declare(strict_types=1);

namespace {

    if (\PHP_VERSION_ID < 70300) {
        if (!\function_exists('is_countable')) {
            /**
             * @param mixed $var
             *
             * @return bool
             *
             * @noinspection PhpComposerExtensionStubsInspection
             */
            function is_countable($var)
            {
                return \is_array($var)
                       ||
                       $var instanceof Countable
                       ||
                       $var instanceof ResourceBundle
                       ||
                       $var instanceof SimpleXMLElement;
            }
        }

        if (!\function_exists('array_key_first')) {
            /**
             * @param array<mixed> $array
             *
             * @return int|string|null
             */
            function array_key_first(array $array)
            {
                foreach ($array as $key => $value) {
                    return $key;
                }

                return null;
            }
        }

        if (!\function_exists('array_key_last')) {
            /**
             * @param array<mixed> $array
             *
             * @return int|string|null
             */
            function array_key_last(array $array)
            {
                if (\count($array) === 0) {
                    return null;
                }

                return \array_keys(
                    \array_slice($array, -1, 1, true)
                )[0];
            }
        }
    }

}

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
         * @return Arrayy<int|string,mixed>
         */
        function create($data): Arrayy
        {
            return new Arrayy($data);
        }

        /**
         * Creates a Collection object.
         *
         * @param string|TypeCheckArray|TypeCheckInterface[] $type
         * @param array<mixed>                               $data
         *
         * @return Collection
         *
         * @template T
         * @psalm-param T $type
         * @psalm-return Collection<int|string,T>
         */
        function collection($type, $data = []): Collection
        {
            return Collection::construct($type, $data);
        }
    }

    /**
     * @param array<mixed> $array
     *
     * @return mixed|null
     */
    function array_last(array $array)
    {
        $key_last = \array_key_last($array);
        if ($key_last === null) {
            return null;
        }

        return $array[$key_last];
    }

    /**
     * @param array<mixed> $array
     *
     * @return mixed|null
     */
    function array_first(array $array)
    {
        $key_first = array_key_first($array);
        if ($key_first === null) {
            return null;
        }

        return $array[$key_first];
    }

}
