<?php

namespace Arrayy\tests;

/**
 * @internal
 */
final class JsonMapperTest extends \PHPUnit\Framework\TestCase
{
    public function testJsonMappingV1(): void
    {
        $data = ['accounts' => [new Account('Foo'), new Account('Bar')]];
        $json = json_encode($data);

        $found = false;

        GetAccountsResponse::createFromJsonMapper($json) // @phpstan-ignore-line argument.type (the runtime API intentionally accepts or transforms a value PHPStan cannot reconcile with the invariant template)
            ->accounts
            ->each(function (Account $a) use (&$found) {
                static::assertTrue($a->accountName === 'Foo' || $a->accountName === 'Bar');
                $found = true;
            });

        static::assertTrue($found);
    }
}
