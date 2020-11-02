<?php

declare(strict_types=1);

namespace Arrayy;

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
        $factory = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
        $docComment = $reflector->getDocComment();
        if ($docComment) {
            $docblock = $factory->create($docComment);
            /** @var \phpDocumentor\Reflection\DocBlock\Tags\Property $tag */
            foreach ($docblock->getTagsByName('property') as $tag) {
                $PropertyName = $tag->getVariableName();
                $this->{$PropertyName} = $PropertyName;
            }
        }

        /** @noinspection PhpAssignmentInConditionInspection */
        while ($reflector = $reflector->getParentClass()) {
            $docComment = $reflector->getDocComment();
            if ($docComment) {
                $docblock = $factory->create($docComment);
                /** @var \phpDocumentor\Reflection\DocBlock\Tags\Property $tag */
                foreach ($docblock->getTagsByName('property') as $tag) {
                    $PropertyName = $tag->getVariableName();
                    $this->{$PropertyName} = $PropertyName;
                }
            }
        }

        $STATIC_CACHE[$cacheKey] = $this;

        return $this;
    }
}
