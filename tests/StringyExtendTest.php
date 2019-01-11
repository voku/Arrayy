<?php

require_once __DIR__ . '/../src/Stringy.php';
require_once __DIR__ . '/StringyExtend.php';

/**
 * Class StringyExtendTest
 *
 * @internal
 */
final class StringyExtendTest extends \PHPUnit\Framework\TestCase
{
    public function testFirst()
    {
        $stringyEx = new StringyExtend();

        $result = $stringyEx->first(2);
        static::assertInstanceOf('StringyExtend', $result);
        static::assertSame('Tö', $result->toString());
        static::assertSame('Töst', $stringyEx->fooBar());
    }

    public function testAfterFirst()
    {
        $testArray = [
            ''                         => 'Töst',
            '<h1>test</h1>'            => 'Töst',
            'foo<h1></h1>bar'          => 'ar',
            '<h1></h1> '               => 'Töst',
            '</b></b>'                 => '></b>',
            'öäü<strong>lall</strong>' => 'Töst',
            ' b<b></b>'                => '<b></b>',
            '<b><b>lall</b>'           => '><b>lall</b>',
            '</b>lall</b>'             => '>lall</b>',
            '[B][/B]'                  => 'Töst',
            '[b][/b]'                  => '][/b]',
            'κόσμbε ¡-öäü'             => 'ε ¡-öäü',
            'bκόσμbε'                  => 'κόσμbε',
        ];

        $stringyEx = new StringyExtend();

        foreach ($testArray as $testString => $testResult) {
            $stringy = $stringyEx::create($testString);
            static::assertSame($testResult, $stringy->afterFirst('b')->toString());
        }
    }
}
