<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

/**
 * @extends \Arrayy\Arrayy<'profile',AccessShapeProfile,array{profile: AccessShapeProfile}>
 * @property-read AccessShapeProfile $profile
 */
final class AccessShapeUser extends \Arrayy\Arrayy
{
    /**
     * @param 'profile' $offset
     * @return AccessShapeProfile
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
