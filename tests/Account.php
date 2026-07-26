<?php

namespace Arrayy\tests;

/**
 * @internal
 */
class Account
{
    public function __construct(string $accountName)
    {
        $this->accountName = $accountName;
    }

    /**
     * @var string
     */
    public $accountName;
}
