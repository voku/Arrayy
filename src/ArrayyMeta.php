<?php

declare(strict_types=1);

namespace Arrayy;

class ArrayyMeta
{

    /** @noinspection MagicMethodsValidityInspection */

    /**
     * @param string $name
     *
     * @return string
     */
    public function __get($name)
    {
        return '';
    }

    /**
     * @param string $obj
     *
     * @return $this
     */
    public function getMetaObject(string $obj): self
    {
        static $STATIC_CACHE = [];

        $cacheKey = $obj;
        if (!empty($STATIC_CACHE[$cacheKey])) {
            return $STATIC_CACHE[$cacheKey];
        }

        $reflector = new \ReflectionClass($obj);
        $factory = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
        $docblock = $factory->create($reflector->getDocComment());
        foreach ($docblock->getTagsByName('property') as $tag) {
            /** @var \phpDocumentor\Reflection\DocBlock\Tags\Property $tag */
            $PropertyName = $tag->getVariableName();
            $this->{$PropertyName} = $PropertyName;
        }

        $STATIC_CACHE[$cacheKey] = $this;

        return $this;
    }
}
