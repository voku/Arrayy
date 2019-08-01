<?php

declare(strict_types=1);

namespace {

    if (\PHP_VERSION_ID < 70300) {
        if (!\function_exists('is_countable')) {
            function is_countable($var)
            {
                /** @noinspection PhpComposerExtensionStubsInspection */
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
            function array_key_first(array $array)
            {
                foreach ($array as $key => $value) {
                    return $key;
                }

                return null;
            }
        }

        if (!\function_exists('array_key_last')) {
            function array_key_last(array $array)
            {
                \end($array);

                return \key($array);
            }
        }
    }

}

namespace Arrayy {

    use Arrayy\Collection\Collection;
    use Arrayy\Collection\CollectionInterface;

    if (!\function_exists('Arrayy\create')) {
        /**
         * Creates a Arrayy object.
         *
         * @param mixed $data
         *
         * @return Arrayy
         */
        function create($data): Arrayy
        {
            return new Arrayy($data);
        }

        /**
         * Creates a Collection object.
         *
         * @param string $type
         * @param mixed  $data
         *
         * @return CollectionInterface
         */
        function collection($type, $data = []): CollectionInterface
        {
            return new Collection($type, $data);
        }
    }

    /**
     * @param array $array
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
     * @param array $array
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
