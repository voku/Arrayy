<?php

namespace Arrayy\tests;

use Arrayy\Collection\AbstractCollection;

/**
 * @extends  AbstractCollection<array-key,Account>
 */
class AccountCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Account::class;
    }
}