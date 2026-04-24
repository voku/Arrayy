<?php

declare(strict_types=1);

namespace Arrayy;

#[\AllowDynamicProperties]
final class ArrayyMeta
{
    /** @noinspection MagicMethodsValidityInspection */

    /**
     * @param string $name
     *
     * @return string
     */
    public function __get($name): string
    {
        return '';
    }

    /**
     * @param string $className
     *
     * @return $this
     *
     * @phpstan-param class-string<\Arrayy\Arrayy<int|string,mixed>> $className
     */
    public function getMetaObject(string $className): self
    {
        static $STATIC_CACHE = [];

        $cacheKey = $className;
        if (!empty($STATIC_CACHE[$cacheKey])) {
            return $STATIC_CACHE[$cacheKey];
        }

        $reflector = new \ReflectionClass($className);
        /** @var Arrayy<int|string,mixed> $instance */
        $instance = $reflector->newInstanceWithoutConstructor();
        foreach ($instance->getPhpDocPropertiesFromClass() as $propertyName => $_) {
            $this->{$propertyName} = $propertyName;
        }

        $STATIC_CACHE[$cacheKey] = $this;

        return $this;
    }
}
