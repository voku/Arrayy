<?php

declare(strict_types=1);

namespace Arrayy\Type;

use Arrayy\Arrayy;
use Arrayy\Collection\Collection;
use Arrayy\TypeCheck\TypeCheckArray;
use Arrayy\TypeCheck\TypeCheckCallback;
use Arrayy\TypeCheck\TypeCheckInterface;

/**
 * @extends Collection<array-key,numeric>
 */
final class NumericCollection extends Collection implements TypeInterface
{
    /**
     * @return TypeCheckArray<array-key, TypeCheckInterface>
     */
    public function getType()
    {
        /** @phpstan-var TypeCheckArray<array-key, TypeCheckInterface> $return */
        $return = TypeCheckArray::create(
            [
                Arrayy::ARRAYY_HELPER_TYPES_FOR_ALL_PROPERTIES => new TypeCheckCallback(
                    static function ($value) {
                        return \is_numeric($value);
                    }
                ),
            ]
        );

        return $return;
    }
}
