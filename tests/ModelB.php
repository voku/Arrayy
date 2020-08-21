<?php

namespace Arrayy\tests;

/**
 * @extends    \Arrayy\Arrayy<array-key,mixed>
 */
class ModelB extends \Arrayy\Arrayy implements ModelInterface
{
    public function getFoo(): string
    {
        return 'foo_B';
    }
}
