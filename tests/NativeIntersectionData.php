<?php

namespace Arrayy\tests;

/**
 * @extends \Arrayy\Arrayy<array-key,mixed>
 */
class NativeIntersectionData extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;

    /**
     * @var \Countable&\Traversable<int, string>
     */
    protected \Countable&\Traversable $items;
}
