<?php

namespace Arrayy\tests;

/**
 * @property scalar $value
 *
 * @extends \Arrayy\Arrayy<array-key,mixed,array<array-key,mixed>>
 */
class DocBlockScalarData extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;
}
