<?php

namespace Arrayy\tests;

use function PHPUnit\Framework\assertSame;

/**
 * @internal
 */
final class JsonMapperTest extends \PHPUnit\Framework\TestCase
{
    public function testJsonMappingV1() {
        $data = ['accounts' => [new Account('Foo'), new Account('Bar')]];
        $json = json_encode($data);

        $found = false;

        GetAccountsResponse::createFromJsonMapper($json)
            ->accounts
            ->each(function (Account $a) use (&$found) {
                static::assertTrue($a->accountName === 'Foo' || $a->accountName === 'Bar');
                $found = true;
            });

        static::assertTrue($found);
    }
}
