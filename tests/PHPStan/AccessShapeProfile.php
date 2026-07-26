<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

/**
 * @extends \Arrayy\Arrayy<'avatar'|'name',string,array{name: string, avatar?: string}>
 * @property-read string $name
 * @property-read string|null $avatar
 */
final class AccessShapeProfile extends \Arrayy\Arrayy
{
    /**
     * @param 'name' $offset
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function &offsetGet($offset)
    {
        $value = &parent::offsetGet($offset);
        if ($value === null) {
            throw new \OutOfBoundsException((string) $offset);
        }

        return $value;
    }
}
