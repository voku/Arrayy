<?php

namespace Arrayy\tests;

class ModelB extends \Arrayy\Arrayy implements ModelInterface
{
    public function getFoo(): string
    {
        return 'foo_B';
    }
}
