<?php

declare(strict_types=1);

namespace Arrayy;

class ArrayyMeta {

    /**
     * @param string $obj
     *
     * @return $this
     */
    public function getMetaObject(string $obj) {
        static $STATIC_CACHE = array();

        $cacheKey = $obj;
        if (!empty($STATIC_CACHE[$cacheKey])) {
            return $STATIC_CACHE[$cacheKey];
        }

        $reflector = new \ReflectionClass($obj);
        $factory  = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
        $docblock = $factory->create($reflector->getDocComment());
        foreach ($docblock->getTagsByName('property') as $tag) {
          /* @var $tag \phpDocumentor\Reflection\DocBlock\Tags\Property */
          $PropertyName = $tag->getVariableName();
          $this->{$PropertyName} = $PropertyName;
        }

        $STATIC_CACHE[$cacheKey] = $this;

        return $this;
    }
}
