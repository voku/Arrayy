<?php

declare(strict_types=1);

namespace Arrayy;

/**
 * Methods to manage strict arrays.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @template TKey of array-key
 * @template T
 * @extends  \Arrayy\Arrayy<TKey,T>
 */
class ArrayyStrict extends Arrayy implements \Arrayy\Type\TypeInterface
{
    /**
     * @var bool
     */
    protected $checkPropertyTypes = true;

    /**
     * @var bool
     */
    protected $checkPropertiesMismatch = false;

    /**
     * @var bool
     */
    protected $checkForMissingPropertiesInConstructor = true;

    /**
     * @var bool
     */
    protected $checkPropertiesMismatchInConstructor = false;
}
