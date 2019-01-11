<?php

use Stringy\Stringy;

require_once __DIR__ . '/../src/Create.php';

/**
 * Class CreateTest
 *
 * @internal
 */
final class CreateTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $stringyObject = new Stringy();
        $stringy = $stringyObject::create('foo bar', 'UTF-8');
        static::assertInstanceOf('Stringy\Stringy', $stringy);
        static::assertSame('foo bar', (string) $stringy);
        static::assertSame('UTF-8', $stringy->getEncoding());
    }
}
