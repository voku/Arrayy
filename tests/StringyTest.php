<?php

require __DIR__ . '/../src/Stringy.php';

use Stringy\Stringy as S;
use voku\helper\UTF8;

/**
 * Class StringyTestCase
 */
class StringyTestCase extends PHPUnit_Framework_TestCase
{
  /**
   * @return array
   */
  public function appendProvider()
  {
    return array(
        array('foobar', 'foo', 'bar'),
        array('fòôbàř', 'fòô', 'bàř', 'UTF-8'),
    );
  }

  /**
   * Asserts that a variable is of a Stringy instance.
   *
   * @param mixed $actual
   */
  public function assertStringy($actual)
  {
    self::assertInstanceOf('Stringy\Stringy', $actual);
  }

  /**
   * @return array
   */
  public function atProvider()
  {
    return array(
        array('f', 'foo bar', 0),
        array('o', 'foo bar', 1),
        array('r', 'foo bar', 6),
        array('', 'foo bar', 7),
        array('f', 'fòô bàř', 0, 'UTF-8'),
        array('ò', 'fòô bàř', 1, 'UTF-8'),
        array('ř', 'fòô bàř', 6, 'UTF-8'),
        array('', 'fòô bàř', 7, 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function betweenProvider()
  {
    return array(
        array('', 'foo', '{', '}'),
        array('', '{foo', '{', '}'),
        array('foo', '{foo}', '{', '}'),
        array('{foo', '{{foo}', '{', '}'),
        array('', '{}foo}', '{', '}'),
        array('foo', '}{foo}', '{', '}'),
        array('foo', 'A description of {foo} goes here', '{', '}'),
        array('bar', '{foo} and {bar}', '{', '}', 1),
        array('', 'fòô', '{', '}', 0, 'UTF-8'),
        array('', '{fòô', '{', '}', 0, 'UTF-8'),
        array('fòô', '{fòô}', '{', '}', 0, 'UTF-8'),
        array('{fòô', '{{fòô}', '{', '}', 0, 'UTF-8'),
        array('', '{}fòô}', '{', '}', 0, 'UTF-8'),
        array('fòô', '}{fòô}', '{', '}', 0, 'UTF-8'),
        array('fòô', 'A description of {fòô} goes here', '{', '}', 0, 'UTF-8'),
        array('bàř', '{fòô} and {bàř}', '{', '}', 1, 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function camelizeProvider()
  {
    return array(
        array('camelCase', 'CamelCase'),
        array('camelCase', 'Camel-Case'),
        array('camelCase', 'camel case'),
        array('camelCase', 'camel -case'),
        array('camelCase', 'camel - case'),
        array('camelCase', 'camel_case'),
        array('camelCTest', 'camel c test'),
        array('stringWith1Number', 'string_with1number'),
        array('stringWith22Numbers', 'string-with-2-2 numbers'),
        array('dataRate', 'data_rate'),
        array('backgroundColor', 'background-color'),
        array('yesWeCan', 'yes_we_can'),
        array('mozSomething', '-moz-something'),
        array('carSpeed', '_car_speed_'),
        array('serveHTTP', 'ServeHTTP'),
        array('1Camel2Case', '1camel2case'),
        array('camelΣase', 'camel σase', 'UTF-8'),
        array('στανιλCase', 'Στανιλ case', 'UTF-8'),
        array('σamelCase', 'σamel  Case', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function capitalizePersonalNameProvider()
  {
    return array(
        array('Marcus Aurelius', 'marcus aurelius'),
        array('Torbjørn Færøvik', 'torbjørn færøvik'),
        array('Jaap de Hoop Scheffer', 'jaap de hoop scheffer'),
        array('K. Anders Ericsson', 'k. anders ericsson'),
        array('Per-Einar', 'per-einar'),
        array(
            'Line Break',
            'line
             break',
        ),
        array('ab', 'ab'),
        array('af', 'af'),
        array('al', 'al'),
        array('and', 'and'),
        array('ap', 'ap'),
        array('bint', 'bint'),
        array('binte', 'binte'),
        array('da', 'da'),
        array('de', 'de'),
        array('del', 'del'),
        array('den', 'den'),
        array('der', 'der'),
        array('di', 'di'),
        array('dit', 'dit'),
        array('ibn', 'ibn'),
        array('la', 'la'),
        array('mac', 'mac'),
        array('nic', 'nic'),
        array('of', 'of'),
        array('ter', 'ter'),
        array('the', 'the'),
        array('und', 'und'),
        array('van', 'van'),
        array('von', 'von'),
        array('y', 'y'),
        array('zu', 'zu'),
        array('Bashar al-Assad', 'bashar al-assad'),
        array("d'Name", "d'Name"),
        array('ffName', 'ffName'),
        array("l'Name", "l'Name"),
        array('macDuck', 'macDuck'),
        array('mcDuck', 'mcDuck'),
        array('nickMick', 'nickMick'),
    );
  }

  /**
   * @return array
   */
  public function charsProvider()
  {
    return array(
        array(array(), ''),
        array(array('T', 'e', 's', 't'), 'Test'),
        array(array('F', 'ò', 'ô', ' ', 'B', 'à', 'ř'), 'Fòô Bàř', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function collapseWhitespaceProvider()
  {
    return array(
        array('foo bar', '  foo   bar  '),
        array('test string', 'test string'),
        array('Ο συγγραφέας', '   Ο     συγγραφέας  '),
        array('123', ' 123 '),
        array('', ' ', 'UTF-8'), // no-break space (U+00A0)
        array('', '           ', 'UTF-8'), // spaces U+2000 to U+200A
        array('', ' ', 'UTF-8'), // narrow no-break space (U+202F)
        array('', ' ', 'UTF-8'), // medium mathematical space (U+205F)
        array('', '　', 'UTF-8'), // ideographic space (U+3000)
        array('1 2 3', '  1  2  3　　', 'UTF-8'),
        array('', ' '),
        array('', ''),
    );
  }

  /**
   * @return array
   */
  public function containsAllProvider()
  {
    // One needle
    $singleNeedle = array_map(
        function ($array) {
          $array[2] = array($array[2]);

          return $array;
        }, $this->containsProvider()
    );

    $provider = array(
      // One needle
      array(false, 'Str contains foo bar', array()),
      // Multiple needles
      array(true, 'Str contains foo bar', array('foo', 'bar')),
      array(true, '12398!@(*%!@# @!%#*&^%', array(' @!%#*', '&^%')),
      array(true, 'Ο συγγραφέας είπε', array('συγγρ', 'αφέας'), 'UTF-8'),
      array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('å´¥', '©'), true, 'UTF-8'),
      array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('å˚ ', '∆'), true, 'UTF-8'),
      array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('øœ', '¬'), true, 'UTF-8'),
      array(false, 'Str contains foo bar', array('Foo', 'bar')),
      array(false, 'Str contains foo bar', array('foobar', 'bar')),
      array(false, 'Str contains foo bar', array('foo bar ', 'bar')),
      array(false, 'Ο συγγραφέας είπε', array('  συγγραφέας ', '  συγγραφ '), true, 'UTF-8'),
      array(false, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array(' ßå˚', ' ß '), true, 'UTF-8'),
      array(true, 'Str contains foo bar', array('Foo bar', 'bar'), false),
      array(true, '12398!@(*%!@# @!%#*&^%', array(' @!%#*&^%', '*&^%'), false),
      array(true, 'Ο συγγραφέας είπε', array('ΣΥΓΓΡΑΦΈΑΣ', 'ΑΦΈΑ'), false, 'UTF-8'),
      array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('Å´¥©', '¥©'), false, 'UTF-8'),
      array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('Å˚ ∆', ' ∆'), false, 'UTF-8'),
      array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('ØŒ¬', 'Œ'), false, 'UTF-8'),
      array(false, 'Str contains foo bar', array('foobar', 'none'), false),
      array(false, 'Str contains foo bar', array('foo bar ', ' ba'), false),
      array(false, 'Ο συγγραφέας είπε', array('  συγγραφέας ', ' ραφέ '), false, 'UTF-8'),
      array(false, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array(' ßÅ˚', ' Å˚ '), false, 'UTF-8'),
    );

    return array_merge($singleNeedle, $provider);
  }

  /**
   * @return array
   */
  public function containsAnyProvider()
  {
    // One needle
    $singleNeedle = array_map(
        function ($array) {
          $array[2] = array($array[2]);

          return $array;
        }, $this->containsProvider()
    );

    $provider = array(
      // No needles
      array(false, 'Str contains foo bar', array()),
      // Multiple needles
      array(true, 'Str contains foo bar', array('foo', 'bar')),
      array(true, '12398!@(*%!@# @!%#*&^%', array(' @!%#*', '&^%')),
      array(true, 'Ο συγγραφέας είπε', array('συγγρ', 'αφέας'), 'UTF-8'),
      array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('å´¥', '©'), true, 'UTF-8'),
      array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('å˚ ', '∆'), true, 'UTF-8'),
      array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('øœ', '¬'), true, 'UTF-8'),
      array(false, 'Str contains foo bar', array('Foo', 'Bar')),
      array(false, 'Str contains foo bar', array('foobar', 'bar ')),
      array(false, 'Str contains foo bar', array('foo bar ', '  foo')),
      array(false, 'Ο συγγραφέας είπε', array('  συγγραφέας ', '  συγγραφ '), true, 'UTF-8'),
      array(false, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array(' ßå˚', ' ß '), true, 'UTF-8'),
      array(true, 'Str contains foo bar', array('Foo bar', 'bar'), false),
      array(true, '12398!@(*%!@# @!%#*&^%', array(' @!%#*&^%', '*&^%'), false),
      array(true, 'Ο συγγραφέας είπε', array('ΣΥΓΓΡΑΦΈΑΣ', 'ΑΦΈΑ'), false, 'UTF-8'),
      array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('Å´¥©', '¥©'), false, 'UTF-8'),
      array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('Å˚ ∆', ' ∆'), false, 'UTF-8'),
      array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array('ØŒ¬', 'Œ'), false, 'UTF-8'),
      array(false, 'Str contains foo bar', array('foobar', 'none'), false),
      array(false, 'Str contains foo bar', array('foo bar ', ' ba '), false),
      array(false, 'Ο συγγραφέας είπε', array('  συγγραφέας ', ' ραφέ '), false, 'UTF-8'),
      array(false, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', array(' ßÅ˚', ' Å˚ '), false, 'UTF-8'),
    );

    return array_merge($singleNeedle, $provider);
  }

  /**
   * @return array
   */
  public function containsProvider()
  {
    return array(
        array(true, 'Str contains foo bar', 'foo bar'),
        array(true, '12398!@(*%!@# @!%#*&^%', ' @!%#*&^%'),
        array(true, 'Ο συγγραφέας είπε', 'συγγραφέας', 'UTF-8'),
        array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'å´¥©', true, 'UTF-8'),
        array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'å˚ ∆', true, 'UTF-8'),
        array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'øœ¬', true, 'UTF-8'),
        array(false, 'Str contains foo bar', 'Foo bar'),
        array(false, 'Str contains foo bar', 'foobar'),
        array(false, 'Str contains foo bar', 'foo bar '),
        array(false, 'Ο συγγραφέας είπε', '  συγγραφέας ', true, 'UTF-8'),
        array(false, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', ' ßå˚', true, 'UTF-8'),
        array(true, 'Str contains foo bar', 'Foo bar', false),
        array(true, '12398!@(*%!@# @!%#*&^%', ' @!%#*&^%', false),
        array(true, 'Ο συγγραφέας είπε', 'ΣΥΓΓΡΑΦΈΑΣ', false, 'UTF-8'),
        array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'Å´¥©', false, 'UTF-8'),
        array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'Å˚ ∆', false, 'UTF-8'),
        array(true, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'ØŒ¬', false, 'UTF-8'),
        array(false, 'Str contains foo bar', 'foobar', false),
        array(false, 'Str contains foo bar', 'foo bar ', false),
        array(false, 'Ο συγγραφέας είπε', '  συγγραφέας ', false, 'UTF-8'),
        array(false, 'å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', ' ßÅ˚', false, 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function countSubstrProvider()
  {
    return array(
        array(false, '', 'foo'),
        array(0, 'foo', 'bar'),
        array(1, 'foo bar', 'foo'),
        array(2, 'foo bar', 'o'),
        array(false, '', 'fòô', 'UTF-8'),
        array(0, 'fòô', 'bàř', 'UTF-8'),
        array(1, 'fòô bàř', 'fòô', 'UTF-8'),
        array(2, 'fôòô bàř', 'ô', 'UTF-8'),
        array(0, 'fÔÒÔ bàř', 'ô', 'UTF-8'),
        array(0, 'foo', 'BAR', false),
        array(1, 'foo bar', 'FOo', false),
        array(2, 'foo bar', 'O', false),
        array(1, 'fòô bàř', 'fÒÔ', false, 'UTF-8'),
        array(2, 'fôòô bàř', 'Ô', false, 'UTF-8'),
        array(2, 'συγγραφέας', 'Σ', false, 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function dasherizeProvider()
  {
    return array(
        array('test-case', 'testCase'),
        array('test-case', 'Test-Case'),
        array('test-case', 'test case'),
        array('-test-case', '-test -case'),
        array('test-case', 'test - case'),
        array('test-case', 'test_case'),
        array('test-c-test', 'test c test'),
        array('test-d-case', 'TestDCase'),
        array('test-c-c-test', 'TestCCTest'),
        array('string-with1number', 'string_with1number'),
        array('string-with-2-2-numbers', 'String-with_2_2 numbers'),
        array('1test2case', '1test2case'),
        array('data-rate', 'dataRate'),
        array('car-speed', 'CarSpeed'),
        array('yes-we-can', 'yesWeCan'),
        array('background-color', 'backgroundColor'),
        array('dash-σase', 'dash Σase', 'UTF-8'),
        array('στανιλ-case', 'Στανιλ case', 'UTF-8'),
        array('σash-case', 'Σash  Case', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function delimitProvider()
  {
    return array(
        array('test*case', 'testCase', '*'),
        array('test&case', 'Test-Case', '&'),
        array('test#case', 'test case', '#'),
        array('test**case', 'test -case', '**'),
        array('~!~test~!~case', '-test - case', '~!~'),
        array('test*case', 'test_case', '*'),
        array('test%c%test', '  test c test', '%'),
        array('test+u+case', 'TestUCase', '+'),
        array('test=c=c=test', 'TestCCTest', '='),
        array('string#>with1number', 'string_with1number', '#>'),
        array('1test2case', '1test2case', '*'),
        array('test ύα σase', 'test Σase', ' ύα ', 'UTF-8',),
        array('στανιλαcase', 'Στανιλ case', 'α', 'UTF-8',),
        array('σashΘcase', 'Σash  Case', 'Θ', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function endsWithAnyProvider()
  {
    return array(
        array(true, 'foo bars', array('foo', 'o bars')),
        array(true, 'FOO bars', array('foo', 'o bars'), false),
        array(true, 'FOO bars', array('foo', 'o BARs'), false),
        array(true, 'FÒÔ bàřs', array('foo', 'ô bàřs'), false, 'UTF-8'),
        array(true, 'fòô bàřs', array('foo', 'ô BÀŘs'), false, 'UTF-8'),
        array(false, 'foo bar', array('foo')),
        array(false, 'foo bar', array('foo', 'foo bars')),
        array(false, 'FOO bar', array('foo', 'foo bars')),
        array(false, 'FOO bars', array('foo', 'foo BARS')),
        array(false, 'FÒÔ bàřs', array('fòô', 'fòô bàřs'), true, 'UTF-8'),
        array(false, 'fòô bàřs', array('fòô', 'fòô BÀŘS'), true, 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function endsWithProvider()
  {
    return array(
        array(true, 'foo bars', 'o bars'),
        array(true, 'FOO bars', 'o bars', false),
        array(true, 'FOO bars', 'o BARs', false),
        array(true, 'FÒÔ bàřs', 'ô bàřs', false, 'UTF-8'),
        array(true, 'fòô bàřs', 'ô BÀŘs', false, 'UTF-8'),
        array(false, 'foo bar', 'foo'),
        array(false, 'foo bar', 'foo bars'),
        array(false, 'FOO bar', 'foo bars'),
        array(false, 'FOO bars', 'foo BARS'),
        array(false, 'FÒÔ bàřs', 'fòô bàřs', true, 'UTF-8'),
        array(false, 'fòô bàřs', 'fòô BÀŘS', true, 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function ensureLeftProvider()
  {
    return array(
        array('foobar', 'foobar', 'f'),
        array('foobar', 'foobar', 'foo'),
        array('foo/foobar', 'foobar', 'foo/'),
        array('http://foobar', 'foobar', 'http://'),
        array('http://foobar', 'http://foobar', 'http://'),
        array('fòôbàř', 'fòôbàř', 'f', 'UTF-8'),
        array('fòôbàř', 'fòôbàř', 'fòô', 'UTF-8'),
        array('fòô/fòôbàř', 'fòôbàř', 'fòô/', 'UTF-8'),
        array('http://fòôbàř', 'fòôbàř', 'http://', 'UTF-8'),
        array('http://fòôbàř', 'http://fòôbàř', 'http://', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function ensureRightProvider()
  {
    return array(
        array('foobar', 'foobar', 'r'),
        array('foobar', 'foobar', 'bar'),
        array('foobar/bar', 'foobar', '/bar'),
        array('foobar.com/', 'foobar', '.com/'),
        array('foobar.com/', 'foobar.com/', '.com/'),
        array('fòôbàř', 'fòôbàř', 'ř', 'UTF-8'),
        array('fòôbàř', 'fòôbàř', 'bàř', 'UTF-8'),
        array('fòôbàř/bàř', 'fòôbàř', '/bàř', 'UTF-8'),
        array('fòôbàř.com/', 'fòôbàř', '.com/', 'UTF-8'),
        array('fòôbàř.com/', 'fòôbàř.com/', '.com/', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function escapeProvider()
  {
    return array(
        array('', ''),
        array('raboof &lt;3', 'raboof <3'),
        array('řàbôòf&lt;foo&lt;lall&gt;&gt;&gt;', 'řàbôòf<foo<lall>>>'),
        array('řàb &lt;ô&gt;òf', 'řàb <ô>òf'),
        array('&lt;∂∆ onerro=&quot;alert(xss)&quot;&gt; ˚åß', '<∂∆ onerro="alert(xss)"> ˚åß'),
        array('&#039;œ … &#039;’)', '\'œ … \'’)'),
    );
  }

  /**
   * @return array
   */
  public function firstProvider()
  {
    return array(
        array('', 'foo bar', -5),
        array('', 'foo bar', 0),
        array('f', 'foo bar', 1),
        array('foo', 'foo bar', 3),
        array('foo bar', 'foo bar', 7),
        array('foo bar', 'foo bar', 8),
        array('', 'fòô bàř', -5, 'UTF-8'),
        array('', 'fòô bàř', 0, 'UTF-8'),
        array('f', 'fòô bàř', 1, 'UTF-8'),
        array('fòô', 'fòô bàř', 3, 'UTF-8'),
        array('fòô bàř', 'fòô bàř', 7, 'UTF-8'),
        array('fòô bàř', 'fòô bàř', 8, 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function hasLowerCaseProvider()
  {
    return array(
        array(false, ''),
        array(true, 'foobar'),
        array(false, 'FOO BAR'),
        array(true, 'fOO BAR'),
        array(true, 'foO BAR'),
        array(true, 'FOO BAr'),
        array(true, 'Foobar'),
        array(false, 'FÒÔBÀŘ', 'UTF-8'),
        array(true, 'fòôbàř', 'UTF-8'),
        array(true, 'fòôbàř2', 'UTF-8'),
        array(true, 'Fòô bàř', 'UTF-8'),
        array(true, 'fòôbÀŘ', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function hasUpperCaseProvider()
  {
    return array(
        array(false, ''),
        array(true, 'FOOBAR'),
        array(false, 'foo bar'),
        array(true, 'Foo bar'),
        array(true, 'FOo bar'),
        array(true, 'foo baR'),
        array(true, 'fOOBAR'),
        array(false, 'fòôbàř', 'UTF-8'),
        array(true, 'FÒÔBÀŘ', 'UTF-8'),
        array(true, 'FÒÔBÀŘ2', 'UTF-8'),
        array(true, 'fÒÔ BÀŘ', 'UTF-8'),
        array(true, 'FÒÔBàř', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function htmlDecodeProvider()
  {
    return array(
        array('&', '&amp;'),
        array('"', '&quot;'),
        array("'", '&#039;', ENT_QUOTES),
        array('<', '&lt;'),
        array('>', '&gt;'),
    );
  }

  /**
   * @return array
   */
  public function htmlEncodeProvider()
  {
    return array(
        array('&amp;', '&'),
        array('&quot;', '"'),
        array('&#039;', "'", ENT_QUOTES),
        array('&lt;', '<'),
        array('&gt;', '>'),
    );
  }

  /**
   * @return array
   */
  public function humanizeProvider()
  {
    return array(
        array('Author', 'author_id'),
        array('Test user', ' _test_user_'),
        array('Συγγραφέας', ' συγγραφέας_id ', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function indexOfLastProvider()
  {
    return array(
        array(6, 'foo & bar', 'bar'),
        array(6, 'foo & bar', 'bar', 0),
        array(false, 'foo & bar', 'baz'),
        array(false, 'foo & bar', 'baz', 0),
        array(12, 'foo & bar & foo', 'foo', 0),
        array(0, 'foo & bar & foo', 'foo', -5),
        array(6, 'fòô & bàř', 'bàř', 0, 'UTF-8'),
        array(false, 'fòô & bàř', 'baz', 0, 'UTF-8'),
        array(12, 'fòô & bàř & fòô', 'fòô', 0, 'UTF-8'),
        array(0, 'fòô & bàř & fòô', 'fòô', -5, 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function indexOfProvider()
  {
    return array(
        array(6, 'foo & bar', 'bar'),
        array(6, 'foo & bar', 'bar', 0),
        array(false, 'foo & bar', 'baz'),
        array(false, 'foo & bar', 'baz', 0),
        array(0, 'foo & bar & foo', 'foo', 0),
        array(12, 'foo & bar & foo', 'foo', 5),
        array(6, 'fòô & bàř', 'bàř', 0, 'UTF-8'),
        array(false, 'fòô & bàř', 'baz', 0, 'UTF-8'),
        array(0, 'fòô & bàř & fòô', 'fòô', 0, 'UTF-8'),
        array(12, 'fòô & bàř & fòô', 'fòô', 5, 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function insertProvider()
  {
    return array(
        array('foo bar', 'oo bar', 'f', 0),
        array('foo bar', 'f bar', 'oo', 1),
        array('f bar', 'f bar', 'oo', 20),
        array('foo bar', 'foo ba', 'r', 6),
        array('fòôbàř', 'fòôbř', 'à', 4, 'UTF-8'),
        array('fòô bàř', 'òô bàř', 'f', 0, 'UTF-8'),
        array('fòô bàř', 'f bàř', 'òô', 1, 'UTF-8'),
        array('fòô bàř', 'fòô bà', 'ř', 6, 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function isAlphaProvider()
  {
    return array(
        array(true, ''),
        array(true, 'foobar'),
        array(false, 'foo bar'),
        array(false, 'foobar2'),
        array(true, 'fòôbàř', 'UTF-8'),
        array(false, 'fòô bàř', 'UTF-8'),
        array(false, 'fòôbàř2', 'UTF-8'),
        array(true, 'ҠѨњфгШ', 'UTF-8'),
        array(false, 'ҠѨњ¨ˆфгШ', 'UTF-8'),
        array(true, '丹尼爾', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function isAlphanumericProvider()
  {
    return array(
        array(true, ''),
        array(true, 'foobar1'),
        array(false, 'foo bar'),
        array(false, 'foobar2"'),
        array(false, "\nfoobar\n"),
        array(true, 'fòôbàř1', 'UTF-8'),
        array(false, 'fòô bàř', 'UTF-8'),
        array(false, 'fòôbàř2"', 'UTF-8'),
        array(true, 'ҠѨњфгШ', 'UTF-8'),
        array(false, 'ҠѨњ¨ˆфгШ', 'UTF-8'),
        array(true, '丹尼爾111', 'UTF-8'),
        array(true, 'دانيال1', 'UTF-8'),
        array(false, 'دانيال1 ', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function isBase64Provider()
  {
    return array(
        array(false, ' '),
        array(false, ''),
        array(true, base64_encode('FooBar')),
        array(true, base64_encode(' ')),
        array(true, base64_encode('FÒÔBÀŘ')),
        array(true, base64_encode('συγγραφέας')),
        array(false, 'Foobar'),
    );
  }

  /**
   * @return array
   */
  public function isBlankProvider()
  {
    return array(
        array(true, ''),
        array(true, ' '),
        array(true, "\n\t "),
        array(true, "\n\t  \v\f"),
        array(false, "\n\t a \v\f"),
        array(false, "\n\t ' \v\f"),
        array(false, "\n\t 2 \v\f"),
        array(true, '', 'UTF-8'),
        array(true, ' ', 'UTF-8'), // no-break space (U+00A0)
        array(true, '           ', 'UTF-8'), // spaces U+2000 to U+200A
        array(true, ' ', 'UTF-8'), // narrow no-break space (U+202F)
        array(true, ' ', 'UTF-8'), // medium mathematical space (U+205F)
        array(true, '　', 'UTF-8'), // ideographic space (U+3000)
        array(false, '　z', 'UTF-8'),
        array(false, '　1', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function isHexadecimalProvider()
  {
    return array(
        array(true, ''),
        array(true, 'abcdef'),
        array(true, 'ABCDEF'),
        array(true, '0123456789'),
        array(true, '0123456789AbCdEf'),
        array(false, '0123456789x'),
        array(false, 'ABCDEFx'),
        array(true, 'abcdef', 'UTF-8'),
        array(true, 'ABCDEF', 'UTF-8'),
        array(true, '0123456789', 'UTF-8'),
        array(true, '0123456789AbCdEf', 'UTF-8'),
        array(false, '0123456789x', 'UTF-8'),
        array(false, 'ABCDEFx', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function isJsonProvider()
  {
    return array(
        array(false, ''),
        array(false, '  '),
        array(true, 'null'),
        array(true, 'true'),
        array(true, 'false'),
        array(true, '[]'),
        array(true, '{}'),
        array(true, '123'),
        array(true, '{"foo": "bar"}'),
        array(false, '{"foo":"bar",}'),
        array(false, '{"foo"}'),
        array(true, '["foo"]'),
        array(false, '{"foo": "bar"]'),
        array(true, '123', 'UTF-8'),
        array(true, '{"fòô": "bàř"}', 'UTF-8'),
        array(false, '{"fòô":"bàř",}', 'UTF-8'),
        array(false, '{"fòô"}', 'UTF-8'),
        array(false, '["fòô": "bàř"]', 'UTF-8'),
        array(true, '["fòô"]', 'UTF-8'),
        array(false, '{"fòô": "bàř"]', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function isLowerCaseProvider()
  {
    return array(
        array(true, ''),
        array(true, 'foobar'),
        array(false, 'foo bar'),
        array(false, 'Foobar'),
        array(true, 'fòôbàř', 'UTF-8'),
        array(false, 'fòôbàř2', 'UTF-8'),
        array(false, 'fòô bàř', 'UTF-8'),
        array(false, 'fòôbÀŘ', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function isProvider()
  {
    return array(
        array(true, 'Gears\\String\\Str', 'Gears\\String\\Str'),
        array(true, 'Gears\\String\\Str', 'Gears\\*\\Str'),
        array(true, 'Gears\\String\\Str', 'Gears\\*\\*'),
        array(true, 'Gears\\String\\Str', '*\\*\\*'),
        array(true, 'Gears\\String\\Str', '*\\String\\*'),
        array(true, 'Gears\\String\\Str', '*\\*\\Str'),
        array(true, 'Gears\\String\\Str', '*\\Str'),
        array(true, 'Gears\\String\\Str', '*'),
        array(true, 'Gears\\String\\Str', '**'),
        array(true, 'Gears\\String\\Str', '****'),
        array(true, 'Gears\\String\\Str', '*Str'),
        array(false, 'Gears\\String\\Str', '*\\'),
        array(false, 'Gears\\String\\Str', 'Gears-*-*'),
    );
  }

  /**
   * @return array
   */
  public function isSerializedProvider()
  {
    return array(
        array(false, ''),
        array(true, 'a:1:{s:3:"foo";s:3:"bar";}'),
        array(false, 'a:1:{s:3:"foo";s:3:"bar"}'),
        array(true, serialize(array('foo' => 'bar'))),
        array(true, 'a:1:{s:5:"fòô";s:5:"bàř";}', 'UTF-8'),
        array(false, 'a:1:{s:5:"fòô";s:5:"bàř"}', 'UTF-8'),
        array(true, serialize(array('fòô' => 'bár')), 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function isUpperCaseProvider()
  {
    return array(
        array(true, ''),
        array(true, 'FOOBAR'),
        array(false, 'FOO BAR'),
        array(false, 'fOOBAR'),
        array(true, 'FÒÔBÀŘ', 'UTF-8'),
        array(false, 'FÒÔBÀŘ2', 'UTF-8'),
        array(false, 'FÒÔ BÀŘ', 'UTF-8'),
        array(false, 'FÒÔBàř', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function lastProvider()
  {
    return array(
        array('', 'foo bar', -5),
        array('', 'foo bar', 0),
        array('r', 'foo bar', 1),
        array('bar', 'foo bar', 3),
        array('foo bar', 'foo bar', 7),
        array('foo bar', 'foo bar', 8),
        array('', 'fòô bàř', -5, 'UTF-8'),
        array('', 'fòô bàř', 0, 'UTF-8'),
        array('ř', 'fòô bàř', 1, 'UTF-8'),
        array('bàř', 'fòô bàř', 3, 'UTF-8'),
        array('fòô bàř', 'fòô bàř', 7, 'UTF-8'),
        array('fòô bàř', 'fòô bàř', 8, 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function lengthProvider()
  {
    return array(
        array(11, '  foo bar  '),
        array(1, 'f'),
        array(0, ''),
        array(7, 'fòô bàř', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function linesProvider()
  {
    return array(
        array(array(), ''),
        array(array(''), "\r\n"),
        array(array('foo', 'bar'), "foo\nbar"),
        array(array('foo', 'bar'), "foo\rbar"),
        array(array('foo', 'bar'), "foo\r\nbar"),
        array(array('foo', '', 'bar'), "foo\r\n\r\nbar"),
        array(array('foo', 'bar', ''), "foo\r\nbar\r\n"),
        array(array('', 'foo', 'bar'), "\r\nfoo\r\nbar"),
        array(array('fòô', 'bàř'), "fòô\nbàř", 'UTF-8'),
        array(array('fòô', 'bàř'), "fòô\rbàř", 'UTF-8'),
        array(array('fòô', 'bàř'), "fòô\n\rbàř", 'UTF-8'),
        array(array('fòô', 'bàř'), "fòô\r\nbàř", 'UTF-8'),
        array(array('fòô', '', 'bàř'), "fòô\r\n\r\nbàř", 'UTF-8'),
        array(array('fòô', 'bàř', ''), "fòô\r\nbàř\r\n", 'UTF-8'),
        array(array('', 'fòô', 'bàř'), "\r\nfòô\r\nbàř", 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function longestCommonPrefixProvider()
  {
    return array(
        array('foo', 'foobar', 'foo bar'),
        array('foo bar', 'foo bar', 'foo bar'),
        array('f', 'foo bar', 'far boo'),
        array('', 'toy car', 'foo bar'),
        array('', 'foo bar', ''),
        array('fòô', 'fòôbar', 'fòô bar', 'UTF-8'),
        array('fòô bar', 'fòô bar', 'fòô bar', 'UTF-8'),
        array('fò', 'fòô bar', 'fòr bar', 'UTF-8'),
        array('', 'toy car', 'fòô bar', 'UTF-8'),
        array('', 'fòô bar', '', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function longestCommonSubstringProvider()
  {
    return array(
        array('foo', 'foobar', 'foo bar'),
        array('foo bar', 'foo bar', 'foo bar'),
        array('oo ', 'foo bar', 'boo far'),
        array('foo ba', 'foo bad', 'foo bar'),
        array('', 'foo bar', ''),
        array('fòô', 'fòôbàř', 'fòô bàř', 'UTF-8'),
        array('fòô bàř', 'fòô bàř', 'fòô bàř', 'UTF-8'),
        array(' bàř', 'fòô bàř', 'fòr bàř', 'UTF-8'),
        array(' ', 'toy car', 'fòô bàř', 'UTF-8'),
        array('', 'fòô bàř', '', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function longestCommonSuffixProvider()
  {
    return array(
        array('bar', 'foobar', 'foo bar'),
        array('foo bar', 'foo bar', 'foo bar'),
        array('ar', 'foo bar', 'boo far'),
        array('', 'foo bad', 'foo bar'),
        array('', 'foo bar', ''),
        array('bàř', 'fòôbàř', 'fòô bàř', 'UTF-8'),
        array('fòô bàř', 'fòô bàř', 'fòô bàř', 'UTF-8'),
        array(' bàř', 'fòô bàř', 'fòr bàř', 'UTF-8'),
        array('', 'toy car', 'fòô bàř', 'UTF-8'),
        array('', 'fòô bàř', '', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function lowerCaseFirstProvider()
  {
    return array(
        array('test', 'Test'),
        array('test', 'test'),
        array('1a', '1a'),
        array('σ test', 'Σ test', 'UTF-8'),
        array(' Σ test', ' Σ test', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function offsetExistsProvider()
  {
    return array(
        array(true, 0),
        array(true, 2),
        array(false, 3),
        array(true, -1),
        array(true, -3),
        array(false, -4),
    );
  }

  /**
   * @return array
   */
  public function padBothProvider()
  {
    return array(
        array('foo bar ', 'foo bar', 8),
        array(' foo bar ', 'foo bar', 9, ' '),
        array('fòô bàř ', 'fòô bàř', 8, ' ', 'UTF-8'),
        array(' fòô bàř ', 'fòô bàř', 9, ' ', 'UTF-8'),
        array('fòô bàř¬', 'fòô bàř', 8, '¬ø', 'UTF-8'),
        array('¬fòô bàř¬', 'fòô bàř', 9, '¬ø', 'UTF-8'),
        array('¬fòô bàř¬ø', 'fòô bàř', 10, '¬ø', 'UTF-8'),
        array('¬øfòô bàř¬ø', 'fòô bàř', 11, '¬ø', 'UTF-8'),
        array('¬fòô bàř¬ø', 'fòô bàř', 10, '¬øÿ', 'UTF-8'),
        array('¬øfòô bàř¬ø', 'fòô bàř', 11, '¬øÿ', 'UTF-8'),
        array('¬øfòô bàř¬øÿ', 'fòô bàř', 12, '¬øÿ', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function padLeftProvider()
  {
    return array(
        array('  foo bar', 'foo bar', 9),
        array('_*foo bar', 'foo bar', 9, '_*'),
        array('_*_foo bar', 'foo bar', 10, '_*'),
        array('  fòô bàř', 'fòô bàř', 9, ' ', 'UTF-8'),
        array('¬øfòô bàř', 'fòô bàř', 9, '¬ø', 'UTF-8'),
        array('¬ø¬fòô bàř', 'fòô bàř', 10, '¬ø', 'UTF-8'),
        array('¬ø¬øfòô bàř', 'fòô bàř', 11, '¬ø', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function padProvider()
  {
    return array(
      // length <= str
      array('foo bar', 'foo bar', -1),
      array('foo bar', 'foo bar', 7),
      array('fòô bàř', 'fòô bàř', 7, ' ', 'right', 'UTF-8'),

      // right
      array('foo bar  ', 'foo bar', 9),
      array('foo bar_*', 'foo bar', 9, '_*', 'right'),
      array('fòô bàř¬ø¬', 'fòô bàř', 10, '¬ø', 'right', 'UTF-8'),

      // left
      array('  foo bar', 'foo bar', 9, ' ', 'left'),
      array('_*foo bar', 'foo bar', 9, '_*', 'left'),
      array('¬ø¬fòô bàř', 'fòô bàř', 10, '¬ø', 'left', 'UTF-8'),

      // both
      array('foo bar ', 'foo bar', 8, ' ', 'both'),
      array('¬fòô bàř¬ø', 'fòô bàř', 10, '¬ø', 'both', 'UTF-8'),
      array('¬øfòô bàř¬øÿ', 'fòô bàř', 12, '¬øÿ', 'both', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function padRightProvider()
  {
    return array(
        array('foo bar  ', 'foo bar', 9),
        array('foo bar_*', 'foo bar', 9, '_*'),
        array('foo bar_*_', 'foo bar', 10, '_*'),
        array('fòô bàř  ', 'fòô bàř', 9, ' ', 'UTF-8'),
        array('fòô bàř¬ø', 'fòô bàř', 9, '¬ø', 'UTF-8'),
        array('fòô bàř¬ø¬', 'fòô bàř', 10, '¬ø', 'UTF-8'),
        array('fòô bàř¬ø¬ø', 'fòô bàř', 11, '¬ø', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function prependProvider()
  {
    return array(
        array('foobar', 'bar', 'foo'),
        array('fòôbàř', 'bàř', 'fòô', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function regexReplaceProvider()
  {
    return array(
        array('', '', '', ''),
        array('bar', 'foo', 'f[o]+', 'bar'),
        array('o bar', 'foo bar', 'f(o)o', '\1'),
        array('bar', 'foo bar', 'f[O]+\s', '', 'i'),
        array('foo', 'bar', '[[:alpha:]]{3}', 'foo'),
        array('', '', '', '', 'msr', 'UTF-8'),
        array('bàř', 'fòô ', 'f[òô]+\s', 'bàř', 'msr', 'UTF-8'),
        array('fòô', 'fò', '(ò)', '\\1ô', 'msr', 'UTF-8'),
        array('fòô', 'bàř', '[[:alpha:]]{3}', 'fòô', 'msr', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function removeHtmlBreakProvider()
  {
    return array(
        array('', ''),
        array('raboof <3', 'raboof <3', '<ä>'),
        array('řàbôòf <foo<lall>>>', 'řàbôòf<br/><foo<lall>>>', ' '),
        array(
            'řàb <ô>òf\', ô<br><br/>foo <a href="#">lall</a>',
            'řàb <ô>òf\', ô<br/>foo <a href="#">lall</a>',
            '<br><br/>',
        ),
        array('<∂∆ onerror="alert(xss)">˚åß', '<∂∆ onerror="alert(xss)">' . "\n" . '˚åß'),
        array('\'œ … \'’)', '\'œ … \'’)'),
    );
  }

  /**
   * @return array
   */
  public function removeHtmlProvider()
  {
    return array(
        array('', ''),
        array('raboof ', 'raboof <3', '<3>'),
        array('řàbôòf>', 'řàbôòf<foo<lall>>>', '<lall><lall/>'),
        array('řàb òf\', ô<br/>foo lall', 'řàb <ô>òf\', ô<br/>foo <a href="#">lall</a>', '<br><br/>'),
        array(' ˚åß', '<∂∆ onerror="alert(xss)"> ˚åß'),
        array('\'œ … \'’)', '\'œ … \'’)'),
    );
  }

  /**
   * @return array
   */
  public function removeLeftProvider()
  {
    return array(
        array('foo bar', 'foo bar', ''),
        array('oo bar', 'foo bar', 'f'),
        array('bar', 'foo bar', 'foo '),
        array('foo bar', 'foo bar', 'oo'),
        array('foo bar', 'foo bar', 'oo bar'),
        array('oo bar', 'foo bar', S::create('foo bar')->first(1), 'UTF-8'),
        array('oo bar', 'foo bar', S::create('foo bar')->at(0), 'UTF-8'),
        array('fòô bàř', 'fòô bàř', '', 'UTF-8'),
        array('òô bàř', 'fòô bàř', 'f', 'UTF-8'),
        array('bàř', 'fòô bàř', 'fòô ', 'UTF-8'),
        array('fòô bàř', 'fòô bàř', 'òô', 'UTF-8'),
        array('fòô bàř', 'fòô bàř', 'òô bàř', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function removeRightProvider()
  {
    return array(
        array('foo bar', 'foo bar', ''),
        array('foo ba', 'foo bar', 'r'),
        array('foo', 'foo bar', ' bar'),
        array('foo bar', 'foo bar', 'ba'),
        array('foo bar', 'foo bar', 'foo ba'),
        array('foo ba', 'foo bar', S::create('foo bar')->last(1), 'UTF-8'),
        array('foo ba', 'foo bar', S::create('foo bar')->at(6), 'UTF-8'),
        array('fòô bàř', 'fòô bàř', '', 'UTF-8'),
        array('fòô bà', 'fòô bàř', 'ř', 'UTF-8'),
        array('fòô', 'fòô bàř', ' bàř', 'UTF-8'),
        array('fòô bàř', 'fòô bàř', 'bà', 'UTF-8'),
        array('fòô bàř', 'fòô bàř', 'fòô bà', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function removeXssProvider()
  {
    return array(
        array('', ''),
        array(
            'Hello, i try to alert&#40;\'Hack\'&#41;; your site',
            'Hello, i try to <script>alert(\'Hack\');</script> your site',
        ),
        array(
            '<IMG >',
            '<IMG SRC=&#x6A&#x61&#x76&#x61&#x73&#x63&#x72&#x69&#x70&#x74&#x3A&#x61&#x6C&#x65&#x72&#x74&#x28&#x27&#x58&#x53&#x53&#x27&#x29>',
        ),
        array('', '<XSS STYLE="behavior: url(xss.htc);">'),
        array('<∂∆ > ˚åß', '<∂∆ onerror="alert(xss)"> ˚åß'),
        array('\'œ … <a href="#foo"> \'’)', '\'œ … <a href="#foo"> \'’)'),
    );
  }

  /**
   * @return array
   */
  public function repeatProvider()
  {
    return array(
        array('', 'foo', 0),
        array('foo', 'foo', 1),
        array('foofoo', 'foo', 2),
        array('foofoofoo', 'foo', 3),
        array('fòô', 'fòô', 1, 'UTF-8'),
        array('fòôfòô', 'fòô', 2, 'UTF-8'),
        array('fòôfòôfòô', 'fòô', 3, 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function replaceAllProvider()
  {
    return array(
        array('', '', array(), ''),
        array('', '', array(''), ''),
        array('foo', ' ', array(' ', ''), 'foo'),
        array('foo', '\s', array('\s', '\t'), 'foo'),
        array('foo bar', 'foo bar', array(''), ''),
        array('\1 bar', 'foo bar', array('f(o)o', 'foo'), '\1'),
        array('\1 \1', 'foo bar', array('foo', 'föö', 'bar'), '\1'),
        array('bar', 'foo bar', array('foo '), ''),
        array('far bar', 'foo bar', array('foo'), 'far'),
        array('bar bar', 'foo bar foo bar', array('foo ', ' foo'), ''),
        array('bar bar bar bar', 'foo bar foo bar', array('foo ', ' foo'), array('bar ', ' bar')),
        array('', '', array(''), '', 'UTF-8'),
        array('fòô', ' ', array(' ', '', '  '), 'fòô', 'UTF-8'),
        array('fòôòô', '\s', array('\s', 'f'), 'fòô', 'UTF-8'),
        array('fòô bàř', 'fòô bàř', array(''), '', 'UTF-8'),
        array('bàř', 'fòô bàř', array('fòô '), '', 'UTF-8'),
        array('far bàř', 'fòô bàř', array('fòô'), 'far', 'UTF-8'),
        array('bàř bàř', 'fòô bàř fòô bàř', array('fòô ', 'fòô'), '', 'UTF-8'),
        array('bàř bàř', 'fòô bàř fòô bàř', array('fòô '), ''),
        array('bàř bàř', 'fòô bàř fòô bàř', array('fòô '), ''),
        array('fòô bàř fòô bàř', 'fòô bàř fòô bàř', array('Fòô '), ''),
        array('fòô bàř fòô bàř', 'fòô bàř fòô bàř', array('fòÔ '), ''),
        array('fòô bàř bàř', 'fòô bàř [[fòô]] bàř', array('[[fòô]] ', '[]'), ''),
        array('', '', array(''), '', 'UTF-8', false),
        array('fòô', ' ', array(' ', '', '  '), 'fòô', 'UTF-8', false),
        array('fòôòô', '\s', array('\s', 'f'), 'fòô', 'UTF-8', false),
        array('fòô bàř', 'fòô bàř', array(''), '', 'UTF-8', false),
        array('bàř', 'fòô bàř', array('fòÔ '), '', 'UTF-8', false),
        array('bàř', 'fòô bàř', array('fòÔ '), array(''), 'UTF-8', false),
        array('far bàř', 'fòô bàř', array('Fòô'), 'far', 'UTF-8', false),
    );
  }

  /**
   * @return array
   */
  public function replaceBeginningProvider()
  {
    return array(
        array('', '', '', ''),
        array('foo', '', '', 'foo'),
        array('foo', '\s', '\s', 'foo'),
        array('foo bar', 'foo bar', '', ''),
        array('foo bar', 'foo bar', 'f(o)o', '\1'),
        array('\1 bar', 'foo bar', 'foo', '\1'),
        array('bar', 'foo bar', 'foo ', ''),
        array('far bar', 'foo bar', 'foo', 'far'),
        array('bar foo bar', 'foo bar foo bar', 'foo ', ''),
        array('', '', '', '', 'UTF-8'),
        array('fòô', '', '', 'fòô', 'UTF-8'),
        array('fòô', '\s', '\s', 'fòô', 'UTF-8'),
        array('fòô bàř', 'fòô bàř', '', '', 'UTF-8'),
        array('bàř', 'fòô bàř', 'fòô ', '', 'UTF-8'),
        array('far bàř', 'fòô bàř', 'fòô', 'far', 'UTF-8'),
        array('bàř fòô bàř', 'fòô bàř fòô bàř', 'fòô ', '', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function replaceEndingProvider()
  {
    return array(
        array('', '', '', ''),
        array('foo', '', '', 'foo'),
        array('foo', '\s', '\s', 'foo'),
        array('foo bar', 'foo bar', '', ''),
        array('foo bar', 'foo bar', 'f(o)o', '\1'),
        array('foo bar', 'foo bar', 'foo', '\1'),
        array('foo bar', 'foo bar', 'foo ', ''),
        array('foo lall', 'foo bar', 'bar', 'lall'),
        array('foo bar foo ', 'foo bar foo bar', 'bar', ''),
        array('', '', '', '', 'UTF-8'),
        array('fòô', '', '', 'fòô', 'UTF-8'),
        array('fòô', '\s', '\s', 'fòô', 'UTF-8'),
        array('fòô bàř', 'fòô bàř', '', '', 'UTF-8'),
        array('fòô', 'fòô bàř', ' bàř', '', 'UTF-8'),
        array('fòôfar', 'fòô bàř', ' bàř', 'far', 'UTF-8'),
        array('fòô bàř fòô', 'fòô bàř fòô bàř', ' bàř', '', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function replaceProvider()
  {
    return array(
        array('', '', '', ''),
        array('foo', ' ', ' ', 'foo'),
        array('foo', '\s', '\s', 'foo'),
        array('foo bar', 'foo bar', '', ''),
        array('foo bar', 'foo bar', 'f(o)o', '\1'),
        array('\1 bar', 'foo bar', 'foo', '\1'),
        array('bar', 'foo bar', 'foo ', ''),
        array('far bar', 'foo bar', 'foo', 'far'),
        array('bar bar', 'foo bar foo bar', 'foo ', ''),
        array('', '', '', '', 'UTF-8'),
        array('fòô', ' ', ' ', 'fòô', 'UTF-8'),
        array('fòô', '\s', '\s', 'fòô', 'UTF-8'),
        array('fòô bàř', 'fòô bàř', '', '', 'UTF-8'),
        array('bàř', 'fòô bàř', 'fòô ', '', 'UTF-8'),
        array('far bàř', 'fòô bàř', 'fòô', 'far', 'UTF-8'),
        array('bàř bàř', 'fòô bàř fòô bàř', 'fòô ', '', 'UTF-8'),
        array('bàř bàř', 'fòô bàř fòô bàř', 'fòô ', '',),
        array('bàř bàř', 'fòô bàř fòô bàř', 'fòô ', ''),
        array('fòô bàř fòô bàř', 'fòô bàř fòô bàř', 'Fòô ', ''),
        array('fòô bàř fòô bàř', 'fòô bàř fòô bàř', 'fòÔ ', ''),
        array('fòô bàř bàř', 'fòô bàř [[fòô]] bàř', '[[fòô]] ', ''),
        array('', '', '', '', 'UTF-8', false),
        array('òô', ' ', ' ', 'òô', 'UTF-8', false),
        array('fòô', '\s', '\s', 'fòô', 'UTF-8', false),
        array('fòô bàř', 'fòô bàř', '', '', 'UTF-8', false),
        array('bàř', 'fòô bàř', 'Fòô ', '', 'UTF-8', false),
        array('far bàř', 'fòô bàř', 'fòÔ', 'far', 'UTF-8', false),
        array('bàř bàř', 'fòô bàř fòô bàř', 'Fòô ', '', 'UTF-8', false),
    );
  }

  /**
   * @return array
   */
  public function reverseProvider()
  {
    return array(
        array('', ''),
        array('raboof', 'foobar'),
        array('řàbôòf', 'fòôbàř', 'UTF-8'),
        array('řàb ôòf', 'fòô bàř', 'UTF-8'),
        array('∂∆ ˚åß', 'ßå˚ ∆∂', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function safeTruncateProvider()
  {
    return array(
        array('Test foo bar', 'Test foo bar', 12),
        array('Test foo', 'Test foo bar', 11),
        array('Test foo', 'Test foo bar', 8),
        array('Test', 'Test foo bar', 7),
        array('Test', 'Test foo bar', 4),
        array('Test', 'Testfoobar', 4),
        array('Test foo bar', 'Test foo bar', 12, '...'),
        array('Test foo...', 'Test foo bar', 11, '...'),
        array('Test...', 'Test foo bar', 8, '...'),
        array('Test...', 'Test foo bar', 7, '...'),
        array('...', 'Test foo bar', 4, '...'),
        array('Test....', 'Test foo bar', 11, '....'),
        array('Test fòô bàř', 'Test fòô bàř', 12, '', 'UTF-8'),
        array('Test fòô', 'Test fòô bàř', 11, '', 'UTF-8'),
        array('Test fòô', 'Test fòô bàř', 8, '', 'UTF-8'),
        array('Test', 'Test fòô bàř', 7, '', 'UTF-8'),
        array('Test', 'Test fòô bàř', 4, '', 'UTF-8'),
        array('Test fòô bàř', 'Test fòô bàř', 12, 'ϰϰ', 'UTF-8'),
        array('Test fòôϰϰ', 'Test fòô bàř', 11, 'ϰϰ', 'UTF-8'),
        array('Testϰϰ', 'Test fòô bàř', 8, 'ϰϰ', 'UTF-8'),
        array('Testϰϰ', 'Test fòô bàř', 7, 'ϰϰ', 'UTF-8'),
        array('ϰϰ', 'Test fòô bàř', 4, 'ϰϰ', 'UTF-8'),
        array('What are your plans...', 'What are your plans today?', 22, '...'),
    );
  }

  /**
   * @return array
   */
  public function shortenAfterWordProvider()
  {
    return array(
        array('this...', 'this is a test', 5, '...'),
        array('this is...', 'this is öäü-foo test', 8, '...'),
        array('fòô', 'fòô bàř fòô', 6, ''),
        array('fòô bàř', 'fòô bàř fòô', 8, ''),
    );
  }

  /**
   * @return array
   */
  public function shuffleProvider()
  {
    return array(
        array('foo bar'),
        array('∂∆ ˚åß', 'UTF-8'),
        array('å´¥©¨ˆßå˚ ∆∂˙©å∑¥øœ¬', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function sliceProvider()
  {
    return array(
        array('foobar', 'foobar', 0),
        array('foobar', 'foobar', 0, null),
        array('foobar', 'foobar', 0, 6),
        array('fooba', 'foobar', 0, 5),
        array('', 'foobar', 3, 0),
        array('', 'foobar', 3, 2),
        array('ba', 'foobar', 3, 5),
        array('ba', 'foobar', 3, -1),
        array('fòôbàř', 'fòôbàř', 0, null, 'UTF-8'),
        array('fòôbàř', 'fòôbàř', 0, null),
        array('fòôbàř', 'fòôbàř', 0, 6, 'UTF-8'),
        array('fòôbà', 'fòôbàř', 0, 5, 'UTF-8'),
        array('', 'fòôbàř', 3, 0, 'UTF-8'),
        array('', 'fòôbàř', 3, 2, 'UTF-8'),
        array('bà', 'fòôbàř', 3, 5, 'UTF-8'),
        array('bà', 'fòôbàř', 3, -1, 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function slugifyProvider()
  {
    return array(
        array('foo-bar', ' foo  bar '),
        array('foo-bar', 'foo -.-"-...bar'),
        array('another-und-foo-bar', 'another..& foo -.-"-...bar'),
        array('foo-d-bar', " Foo d'Bar "),
        array('a-string-with-dashes', 'A string-with-dashes'),
        array('using-strings-like-foo-bar', 'Using strings like fòô bàř'),
        array('numbers-1234', 'numbers 1234'),
        array('perevirka-ryadka', 'перевірка рядка'),
        array('bukvar-s-bukvoj-y', 'букварь с буквой ы'),
        array('podehal-k-podezdu-moego-doma', 'подъехал к подъезду моего дома'),
        array('foo:bar:baz', 'Foo bar baz', ':'),
        array('a_string_with_underscores', 'A_string with_underscores', '_'),
        array('a_string_with_dashes', 'A string-with-dashes', '_'),
        array('a\string\with\dashes', 'A string-with-dashes', '\\'),
        array('an_odd_string', '--   An odd__   string-_', '_'),
    );
  }

  /**
   * @return array
   */
  public function snakeizeProvider()
  {
    return array(
        array('snake_case', 'SnakeCase'),
        array('snake_case', 'Snake-Case'),
        array('snake_case', 'snake case'),
        array('snake_case', 'snake -case'),
        array('snake_case', 'snake - case'),
        array('snake_case', 'snake_case'),
        array('camel_c_test', 'camel c test'),
        array('string_with_1_number', 'string_with 1 number'),
        array('string_with_1_number', 'string_with1number'),
        array('string_with_2_2_numbers', 'string-with-2-2 numbers'),
        array('data_rate', 'data_rate'),
        array('background_color', 'background-color'),
        array('yes_we_can', 'yes_we_can'),
        array('moz_something', '-moz-something'),
        array('car_speed', '_car_speed_'),
        array('serve_h_t_t_p', 'ServeHTTP'),
        array('1_camel_2_case', '1camel2case'),
        array('camel_σase', 'camel σase', 'UTF-8'),
        array('Στανιλ_case', 'Στανιλ case', 'UTF-8'),
        array('σamel_case', 'σamel  Case', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function splitProvider()
  {
    return array(
        array(array('foo,bar,baz'), 'foo,bar,baz', ''),
        array(array('foo,bar,baz'), 'foo,bar,baz', '-'),
        array(array('foo', 'bar', 'baz'), 'foo,bar,baz', ','),
        array(array('foo', 'bar', 'baz'), 'foo,bar,baz', ',', null),
        array(array('foo', 'bar', 'baz'), 'foo,bar,baz', ',', -1),
        array(array(), 'foo,bar,baz', ',', 0),
        array(array('foo'), 'foo,bar,baz', ',', 1),
        array(array('foo', 'bar'), 'foo,bar,baz', ',', 2),
        array(array('foo', 'bar', 'baz'), 'foo,bar,baz', ',', 3),
        array(array('foo', 'bar', 'baz'), 'foo,bar,baz', ',', 10),
        array(array('fòô,bàř,baz'), 'fòô,bàř,baz', '-', null, 'UTF-8'),
        array(array('fòô', 'bàř', 'baz'), 'fòô,bàř,baz', ',', null, 'UTF-8'),
        array(array('fòô', 'bàř', 'baz'), 'fòô,bàř,baz', ',', null, 'UTF-8'),
        array(array('fòô', 'bàř', 'baz'), 'fòô,bàř,baz', ',', -1, 'UTF-8'),
        array(array(), 'fòô,bàř,baz', ',', 0, 'UTF-8'),
        array(array('fòô'), 'fòô,bàř,baz', ',', 1, 'UTF-8'),
        array(array('fòô', 'bàř'), 'fòô,bàř,baz', ',', 2, 'UTF-8'),
        array(array('fòô', 'bàř', 'baz'), 'fòô,bàř,baz', ',', 3, 'UTF-8'),
        array(array('fòô', 'bàř', 'baz'), 'fòô,bàř,baz', ',', 10, 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function startsWithProvider()
  {
    return array(
        array(true, 'foo bars', 'foo bar'),
        array(true, 'FOO bars', 'foo bar', false),
        array(true, 'FOO bars', 'foo BAR', false),
        array(true, 'FÒÔ bàřs', 'fòô bàř', false, 'UTF-8'),
        array(true, 'fòô bàřs', 'fòô BÀŘ', false, 'UTF-8'),
        array(false, 'foo bar', 'bar'),
        array(false, 'foo bar', 'foo bars'),
        array(false, 'FOO bar', 'foo bars'),
        array(false, 'FOO bars', 'foo BAR'),
        array(false, 'FÒÔ bàřs', 'fòô bàř', true, 'UTF-8'),
        array(false, 'fòô bàřs', 'fòô BÀŘ', true, 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function startsWithProviderAny()
  {
    return array(
        array(true, 'foo bars', array('foo bar')),
        array(true, 'foo bars', array('foo', 'bar')),
        array(true, 'FOO bars', array('foo', 'bar'), false),
        array(true, 'FOO bars', array('foo', 'BAR'), false),
        array(true, 'FÒÔ bàřs', array('fòô', 'bàř'), false, 'UTF-8'),
        array(true, 'fòô bàřs', array('fòô BÀŘ'), false, 'UTF-8'),
        array(false, 'foo bar', array('bar')),
        array(false, 'foo bar', array('foo bars')),
        array(false, 'FOO bar', array('foo bars')),
        array(false, 'FOO bars', array('foo BAR')),
        array(false, 'FÒÔ bàřs', array('fòô bàř'), true, 'UTF-8'),
        array(false, 'fòô bàřs', array('fòô BÀŘ'), true, 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function stripWhitespaceProvider()
  {
    return array(
        array('foobar', '  foo   bar  '),
        array('teststring', 'test string'),
        array('Οσυγγραφέας', '   Ο     συγγραφέας  '),
        array('123', ' 123 '),
        array('', ' ', 'UTF-8'), // no-break space (U+00A0)
        array('', '           ', 'UTF-8'), // spaces U+2000 to U+200A
        array('', ' ', 'UTF-8'), // narrow no-break space (U+202F)
        array('', ' ', 'UTF-8'), // medium mathematical space (U+205F)
        array('', '　', 'UTF-8'), // ideographic space (U+3000)
        array('123', '  1  2  3　　', 'UTF-8'),
        array('', ' '),
        array('', ''),
    );
  }

  /**
   * @return array
   */
  public function substrProvider()
  {
    return array(
        array('foo bar', 'foo bar', 0),
        array('bar', 'foo bar', 4),
        array('bar', 'foo bar', 4, null),
        array('o b', 'foo bar', 2, 3),
        array('', 'foo bar', 4, 0),
        array('fòô bàř', 'fòô bàř', 0, null, 'UTF-8'),
        array('bàř', 'fòô bàř', 4, null, 'UTF-8'),
        array('ô b', 'fòô bàř', 2, 3, 'UTF-8'),
        array('', 'fòô bàř', 4, 0, 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function surroundProvider()
  {
    return array(
        array('__foobar__', 'foobar', '__'),
        array('test', 'test', ''),
        array('**', '', '*'),
        array('¬fòô bàř¬', 'fòô bàř', '¬'),
        array('ßå∆˚ test ßå∆˚', ' test ', 'ßå∆˚'),
    );
  }

  /**
   * @return array
   */
  public function swapCaseProvider()
  {
    return array(
        array('TESTcASE', 'testCase'),
        array('tEST-cASE', 'Test-Case'),
        array(' - σASH  cASE', ' - Σash  Case', 'UTF-8'),
        array('νΤΑΝΙΛ', 'Ντανιλ', 'UTF-8'),
    );
  }

  public function testAddPassword()
  {
    // init
    $disallowedChars = 'ф0Oo1l';
    $allowedChars = '2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ';

    $passwords = array();
    for ($i = 0; $i <= 100; $i++) {
      $stringy = S::create('');
      $passwords[] = $stringy->appendPassword(16);
    }

    // check for allowed chars
    $errors = array();
    foreach ($passwords as $password) {
      foreach (str_split($password) as $char) {
        if (strpos($allowedChars, $char) === false) {
          $errors[] = $char;
        }
      }
    }
    self::assertSame(0, count($errors));

    // check for disallowed chars
    $errors = array();
    foreach ($passwords as $password) {
      foreach (UTF8::str_split($password) as $char) {
        if (strpos($disallowedChars, $char) !== false) {
          $errors[] = $char;
        }
      }
    }
    self::assertSame(0, count($errors));

    // check the string length
    foreach ($passwords as $password) {
      self::assertSame(16, strlen($password));
    }
  }

  public function testAddRandomString()
  {
    $testArray = array(
        'öäü'       => array(10, 10),
        ''          => array(10, 0),
        'κόσμε-öäü' => array(10, 10),
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create('');
      $stringy = $stringy->appendRandomString($testResult[0], $testString);

      self::assertSame($testResult[1], $stringy->length(), 'tested: ' . $testString . ' | ' . $stringy->toString());
    }
  }

  public function testAddUniqueIdentifier()
  {
    $uniquIDs = array();
    for ($i = 0; $i <= 100; $i++) {
      $stringy = S::create('');
      $uniquIDs[] = (string)$stringy->appendUniqueIdentifier();
    }

    // detect duplicate values in the array
    foreach (array_count_values($uniquIDs) as $uniquID => $count) {
      self::assertSame(1, $count);
    }

    // check the string length
    foreach ($uniquIDs as $uniquID) {
      self::assertSame(32, strlen($uniquID));
    }
  }

  public function testAfterFirst()
  {
    $testArray = array(
        ''                         => '',
        '<h1>test</h1>'            => '',
        'foo<h1></h1>bar'          => 'ar',
        '<h1></h1> '               => '',
        '</b></b>'                 => '></b>',
        'öäü<strong>lall</strong>' => '',
        ' b<b></b>'                => '<b></b>',
        '<b><b>lall</b>'           => '><b>lall</b>',
        '</b>lall</b>'             => '>lall</b>',
        '[B][/B]'                  => '',
        '[b][/b]'                  => '][/b]',
        'κόσμbε ¡-öäü'             => 'ε ¡-öäü',
        'bκόσμbε'                  => 'κόσμbε',
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create($testString);
      self::assertSame($testResult, $stringy->afterFirst('b')->toString());
    }
  }

  public function testAfterFirstIgnoreCase()
  {
    $testArray = array(
        ''                         => '',
        '<h1>test</h1>'            => '',
        'foo<h1></h1>Bar'          => 'ar',
        'foo<h1></h1>bar'          => 'ar',
        '<h1></h1> '               => '',
        '</b></b>'                 => '></b>',
        'öäü<strong>lall</strong>' => '',
        ' b<b></b>'                => '<b></b>',
        '<b><b>lall</b>'           => '><b>lall</b>',
        '</b>lall</b>'             => '>lall</b>',
        '[B][/B]'                  => '][/B]',
        'κόσμbε ¡-öäü'             => 'ε ¡-öäü',
        'bκόσμbε'                  => 'κόσμbε',
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create($testString);
      self::assertSame($testResult, $stringy->afterFirstIgnoreCase('b')->toString());
    }
  }

  public function testAfterLasIgnoreCaset()
  {
    $testArray = array(
        ''                         => '',
        '<h1>test</h1>'            => '',
        'foo<h1></h1>bar'          => 'ar',
        'foo<h1></h1>Bar'          => 'ar',
        '<h1></h1> '               => '',
        '</b></b>'                 => '>',
        'öäü<strong>lall</strong>' => '',
        ' b<b></b>'                => '>',
        '<b><b>lall</b>'           => '>',
        '</b>lall</b>'             => '>',
        '[B][/B]'                  => ']',
        '[b][/b]'                  => ']',
        'κόσμbε ¡-öäü'             => 'ε ¡-öäü',
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create($testString);
      self::assertSame($testResult, $stringy->afterLastIgnoreCase('b')->toString());
    }
  }

  public function testAfterLast()
  {
    $testArray = array(
        ''                         => '',
        '<h1>test</h1>'            => '',
        'foo<h1></h1>bar'          => 'ar',
        '<h1></h1> '               => '',
        '</b></b>'                 => '>',
        'öäü<strong>lall</strong>' => '',
        ' b<b></b>'                => '>',
        '<b><b>lall</b>'           => '>',
        '</b>lall</b>'             => '>',
        '[b][/b]'                  => ']',
        '[B][/B]'                  => '',
        'κόσμbε ¡-öäü'             => 'ε ¡-öäü',
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create($testString);
      self::assertSame($testResult, $stringy->afterLast('b')->toString());
    }
  }

  /**
   * @dataProvider appendProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $string
   * @param null $encoding
   */
  public function testAppend($expected, $str, $string, $encoding = null)
  {
    $result = S::create($str, $encoding)->append($string);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
  }

  /**
   * @dataProvider atProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $index
   * @param null $encoding
   */
  public function testAt($expected, $str, $index, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->at($index);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  public function testBeforeFirst()
  {
    $testArray = array(
        ''                         => '',
        '<h1>test</h1>'            => '',
        'foo<h1></h1>bar'          => 'foo<h1></h1>',
        '<h1></h1> '               => '',
        '</b></b>'                 => '</',
        'öäü<strong>lall</strong>' => '',
        ' b<b></b>'                => ' ',
        '<b><b>lall</b>'           => '<',
        '</b>lall</b>'             => '</',
        '[b][/b]'                  => '[',
        '[B][/B]'                  => '',
        'κόσμbε ¡-öäü'             => 'κόσμ',
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create($testString);
      self::assertSame($testResult, $stringy->beforeFirst('b')->toString());
      self::assertSame($testResult, $stringy->substringOf('b', true)->toString());
    }
  }

  public function testBeforeFirstIgnoreCase()
  {
    $testArray = array(
        ''                         => '',
        '<h1>test</h1>'            => '',
        'foo<h1></h1>Bar'          => 'foo<h1></h1>',
        'foo<h1></h1>bar'          => 'foo<h1></h1>',
        '<h1></h1> '               => '',
        '</b></b>'                 => '</',
        'öäü<strong>lall</strong>' => '',
        ' b<b></b>'                => ' ',
        '<b><b>lall</b>'           => '<',
        '</b>lall</b>'             => '</',
        '[B][/B]'                  => '[',
        'κόσμbε ¡-öäü'             => 'κόσμ',
        'Bκόσμbε'                  => '',
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create($testString);
      self::assertSame($testResult, $stringy->beforeFirstIgnoreCase('b')->toString());
      self::assertSame($testResult, $stringy->substringOfIgnoreCase('b', true)->toString());
    }
  }

  public function testBeforeLast()
  {
    $testArray = array(
        ''                         => '',
        '<h1>test</h1>'            => '',
        'foo<h1></h1>bar'          => 'foo<h1></h1>',
        '<h1></h1> '               => '',
        '</b></b>'                 => '</b></',
        'öäü<strong>lall</strong>' => '',
        ' b<b></b>'                => ' b<b></',
        '<b><b>lall</b>'           => '<b><b>lall</',
        '</b>lall</b>'             => '</b>lall</',
        '[b][/b]'                  => '[b][/',
        '[B][/B]'                  => '',
        'κόσμbε ¡-öäü'             => 'κόσμ',
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create($testString);
      self::assertSame($testResult, $stringy->beforeLast('b')->toString());
      self::assertSame($testResult, $stringy->lastSubstringOf('b', true)->toString());
    }
  }

  public function testBeforeLastIgnoreCase()
  {
    $testArray = array(
        ''                         => '',
        '<h1>test</h1>'            => '',
        'foo<h1></h1>Bar'          => 'foo<h1></h1>',
        'foo<h1></h1>bar'          => 'foo<h1></h1>',
        '<h1></h1> '               => '',
        '</b></b>'                 => '</b></',
        'öäü<strong>lall</strong>' => '',
        ' b<b></b>'                => ' b<b></',
        '<b><b>lall</b>'           => '<b><b>lall</',
        '</b>lall</b>'             => '</b>lall</',
        '[B][/B]'                  => '[B][/',
        'κόσμbε ¡-öäü'             => 'κόσμ',
        'bκόσμbε'                  => 'bκόσμ',
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create($testString);
      self::assertSame($testResult, $stringy->beforeLastIgnoreCase('b')->toString());
    }
  }

  /**
   * @dataProvider betweenProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $start
   * @param      $end
   * @param null $offset
   * @param null $encoding
   */
  public function testBetween($expected, $str, $start, $end, $offset = null, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->between($start, $end, $offset);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider camelizeProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testCamelize($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->camelize();
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider capitalizePersonalNameProvider()
   *
   * @param string      $expected
   * @param string      $str
   * @param null|string $encoding
   */
  public function testCapitalizePersonalName($expected, $str, $encoding = null)
  {
    /** @var S $stringy */
    $stringy = S::create($str, $encoding);
    $result = $stringy->capitalizePersonalName();
    self::assertEquals($expected, $result);
    self::assertEquals($str, $stringy);
  }

  public function testChaining()
  {
    $stringy = S::create('Fòô     Bàř', 'UTF-8');
    self::assertStringy($stringy);
    $result = $stringy->collapseWhitespace()->swapCase()->upperCaseFirst();
    self::assertSame('FÒÔ bÀŘ', $result->toString());
  }

  /**
   * @dataProvider charsProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testChars($expected, $str, $encoding = null)
  {
    $result = S::create($str, $encoding)->chars();
    self::assertInternalType('array', $result);
    foreach ($result as $char) {
      self::assertInternalType('string', $char);
    }
    self::assertSame($expected, $result);
  }

  /**
   * @dataProvider collapseWhitespaceProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testCollapseWhitespace($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->collapseWhitespace();
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  public function testConstruct()
  {
    $stringy = new S('foo bar', 'UTF-8');
    self::assertStringy($stringy);
    self::assertSame('foo bar', (string)$stringy);
    self::assertSame('UTF-8', $stringy->getEncoding());
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testConstructWithArray()
  {
    /** @noinspection PhpExpressionResultUnusedInspection */
    (string)new S(array());
    static::fail('Expecting exception when the constructor is passed an array');
  }

  /**
   * @dataProvider containsProvider()
   *
   * @param      $expected
   * @param      $haystack
   * @param      $needle
   * @param bool $caseSensitive
   * @param null $encoding
   */
  public function testContains($expected, $haystack, $needle, $caseSensitive = true, $encoding = null)
  {
    $stringy = S::create($haystack, $encoding);
    $result = $stringy->contains($needle, $caseSensitive);
    self::assertInternalType('boolean', $result);
    self::assertSame($expected, $result);
    self::assertSame($haystack, $stringy->toString());
  }

  /**
   * @dataProvider containsAllProvider()
   *
   * @param      $expected
   * @param      $haystack
   * @param      $needles
   * @param bool $caseSensitive
   * @param null $encoding
   */
  public function testContainsAll($expected, $haystack, $needles, $caseSensitive = true, $encoding = null)
  {
    $stringy = S::create($haystack, $encoding);
    $result = $stringy->containsAll($needles, $caseSensitive);
    self::assertInternalType('boolean', $result);
    self::assertSame($expected, $result);
    self::assertSame($haystack, $stringy->toString());
  }

  public function testCount()
  {
    $stringy = S::create('Fòô', 'UTF-8');
    self::assertSame(3, $stringy->count());
    self::assertSame(3, count($stringy));
  }

  /**
   * @dataProvider countSubstrProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $substring
   * @param bool $caseSensitive
   * @param null $encoding
   */
  public function testCountSubstr($expected, $str, $substring, $caseSensitive = true, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->countSubstr($substring, $caseSensitive);
    self::assertSame($expected, $result, 'tested:' . $str);
    self::assertSame($str, $stringy->toString(), 'tested:' . $str);
  }

  public function testCreate()
  {
    $stringy = S::create('foo bar', 'UTF-8');
    self::assertStringy($stringy);
    self::assertSame('foo bar', (string)$stringy);
    self::assertSame('UTF-8', $stringy->getEncoding());
  }

  /**
   * @dataProvider dasherizeProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testDasherize($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->dasherize();
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider delimitProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $delimiter
   * @param null $encoding
   */
  public function testDelimit($expected, $str, $delimiter, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->delimit($delimiter);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  public function testEmptyConstruct()
  {
    $stringy = new S();
    self::assertStringy($stringy);
    self::assertSame('', (string)$stringy);
  }

  /**
   * @dataProvider endsWithProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $substring
   * @param bool $caseSensitive
   * @param null $encoding
   */
  public function testEndsWith($expected, $str, $substring, $caseSensitive = true, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->endsWith($substring, $caseSensitive);
    self::assertInternalType('boolean', $result);
    self::assertSame($expected, $result);
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider endsWithAnyProvider()
   *
   * @param string $expected
   * @param string $str
   * @param array  $substrings
   * @param bool   $caseSensitive
   * @param null   $encoding
   */
  public function testEndsWithAny($expected, $str, $substrings, $caseSensitive = true, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->endsWithAny($substrings, $caseSensitive);
    $this->assertInternalType('boolean', $result);
    $this->assertEquals($expected, $result);
    $this->assertEquals($str, $stringy);
  }

  /**
   * @dataProvider ensureLeftProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $substring
   * @param null $encoding
   */
  public function testEnsureLeft($expected, $str, $substring, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->ensureLeft($substring);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider ensureRightProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $substring
   * @param null $encoding
   */
  public function testEnsureRight($expected, $str, $substring, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->ensureRight($substring);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider escapeProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testEscape($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->escape();
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  public function testExtractText()
  {
    $testArray = array(
        ''                                                                                                                                          => '',
        '<h1>test</h1>'                                                                                                                             => '<h1>test</h1>',
        'test'                                                                                                                                      => 'test',
        'A PHP string manipulation library with multibyte support. Compatible with PHP 5.3+, PHP 7, and HHVM.'                                      => 'A PHP string manipulation library with multibyte support...',
        'A PHP string manipulation library with multibyte support. κόσμε-öäü κόσμε-öäü κόσμε-öäü foobar Compatible with PHP 5.3+, PHP 7, and HHVM.' => '...support. κόσμε-öäü κόσμε-öäü κόσμε-öäü foobar Compatible with PHP 5...',
        'A PHP string manipulation library with multibyte support. foobar Compatible with PHP 5.3+, PHP 7, and HHVM.'                               => '...with multibyte support. foobar Compatible with PHP 5...',
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create($testString);
      self::assertSame($testResult, (string)$stringy->extractText('foobar'), 'tested: ' . $testString);
    }

    // ----------------

    $testString = 'this is only a Fork of Stringy';
    $stringy = S::create($testString);
    self::assertSame('...a Fork of Stringy', (string)$stringy->extractText('Fork', 5), 'tested: ' . $testString);
  }

  /**
   * @dataProvider firstProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $n
   * @param null $encoding
   */
  public function testFirst($expected, $str, $n, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->first($n);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  public function testGetIterator()
  {
    $stringy = S::create('Fòô Bàř', 'UTF-8');

    $valResult = array();
    foreach ($stringy as $char) {
      $valResult[] = $char;
    }

    $keyValResult = array();
    foreach ($stringy as $pos => $char) {
      $keyValResult[$pos] = $char;
    }

    self::assertSame(array('F', 'ò', 'ô', ' ', 'B', 'à', 'ř'), $valResult);
    self::assertSame(array('F', 'ò', 'ô', ' ', 'B', 'à', 'ř'), $keyValResult);
  }

  /**
   * @dataProvider hasLowerCaseProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testHasLowerCase($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->hasLowerCase();
    self::assertInternalType('boolean', $result);
    self::assertSame($expected, $result);
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider hasUpperCaseProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testHasUpperCase($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->hasUpperCase();
    self::assertInternalType('boolean', $result);
    self::assertSame($expected, $result);
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider htmlDecodeProvider()
   *
   * @param      $expected
   * @param      $str
   * @param int  $flags
   * @param null $encoding
   */
  public function testHtmlDecode($expected, $str, $flags = ENT_COMPAT, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->htmlDecode($flags);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider htmlEncodeProvider()
   *
   * @param      $expected
   * @param      $str
   * @param int  $flags
   * @param null $encoding
   */
  public function testHtmlEncode($expected, $str, $flags = ENT_COMPAT, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->htmlEncode($flags);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider humanizeProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testHumanize($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->humanize();
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider indexOfProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $subStr
   * @param int  $offset
   * @param null $encoding
   */
  public function testIndexOf($expected, $str, $subStr, $offset = 0, $encoding = null)
  {
    $result = S::create($str, $encoding)->indexOf($subStr, $offset);
    self::assertSame($expected, $result);
  }

  /**
   * @dataProvider indexOfLastProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $subStr
   * @param int  $offset
   * @param null $encoding
   */
  public function testIndexOfLast($expected, $str, $subStr, $offset = 0, $encoding = null)
  {
    $result = S::create($str, $encoding)->indexOfLast($subStr, $offset);
    self::assertSame($expected, $result);
  }

  /**
   * @dataProvider insertProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $substring
   * @param      $index
   * @param null $encoding
   */
  public function testInsert($expected, $str, $substring, $index, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->insert($substring, $index);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider isProvider()
   *
   * @param      $expected
   * @param      $string
   * @param      $pattern
   * @param null $encoding
   */
  public function testIs($expected, $string, $pattern, $encoding = null)
  {
    $str = S::create($string, $encoding);
    $result = $str->is($pattern);
    $this->assertInternalType('boolean', $result);
    $this->assertEquals($expected, $result, 'tested: ' . $pattern);
    $this->assertEquals($string, $str);
  }

  /**
   * @dataProvider isAlphaProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testIsAlpha($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->isAlpha();
    self::assertInternalType('boolean', $result);
    self::assertSame($expected, $result);
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider isAlphanumericProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testIsAlphanumeric($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->isAlphanumeric();
    self::assertInternalType('boolean', $result);
    self::assertSame($expected, $result);
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider isBase64Provider()
   *
   * @param $expected
   * @param $str
   */
  public function testIsBase64($expected, $str)
  {
    $stringy = S::create($str);
    $result = $stringy->isBase64();
    self::assertInternalType('boolean', $result);
    self::assertSame($expected, $result);
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider isBlankProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testIsBlank($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->isBlank();
    self::assertInternalType('boolean', $result);
    self::assertSame($expected, $result);
    self::assertSame($str, $stringy->toString());
  }

  public function testIsEmail()
  {
    $testArray = array(
        ''             => false,
        'foo@bar'      => false,
        'foo@bar.foo'  => true,
        'foo@bar.foo ' => false,
        ' foo@bar.foo' => false,
        'lall'         => false,
        'κόσμbε@¡-öäü' => false,
        'lall.de'      => false,
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create($testString);
      self::assertSame($testResult, $stringy->isEmail());
    }

    // --- example domain check

    $stringy = S::create('test@test.com');
    self::assertSame(true, $stringy->isEmail());

    $stringy = S::create('test@test.com');
    self::assertSame(false, $stringy->isEmail(true));

    // --- tpyp domain check

    $stringy = S::create('test@aecor.de');
    self::assertSame(true, $stringy->isEmail());

    $stringy = S::create('test@aecor.de');
    self::assertSame(false, $stringy->isEmail(false, true));

  }

  /**
   * @dataProvider isHexadecimalProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testIsHexadecimal($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->isHexadecimal();
    self::assertInternalType('boolean', $result);
    self::assertSame($expected, $result);
    self::assertSame($str, $stringy->toString());
  }

  public function testIsHtml()
  {
    $testArray = array(
        ''                         => false,
        '<h1>test</h1>'            => true,
        'test'                     => false,
        '<b>lall</b>'              => true,
        'öäü<strong>lall</strong>' => true,
        ' <b>lall</b>'             => true,
        '<b><b>lall</b>'           => true,
        '</b>lall</b>'             => true,
        '[b]lall[b]'               => false,
        ' <test>κόσμε</test> '     => true,
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create($testString);
      self::assertSame($testResult, $stringy->isHtml(), 'tested: ' . $testString);
    }
  }

  /**
   * @dataProvider isJsonProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testIsJson($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->isJson();
    self::assertInternalType('boolean', $result);
    self::assertSame($expected, $result, 'tested:' . $str);
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider isLowerCaseProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testIsLowerCase($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->isLowerCase();
    self::assertInternalType('boolean', $result);
    self::assertSame($expected, $result);
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider isSerializedProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testIsSerialized($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->isSerialized();
    self::assertInternalType('boolean', $result);
    self::assertSame($expected, $result);
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider isUpperCaseProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testIsUpperCase($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->isUpperCase();
    self::assertInternalType('boolean', $result);
    self::assertSame($expected, $result);
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider lastProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $n
   * @param null $encoding
   */
  public function testLast($expected, $str, $n, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->last($n);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  public function testLastSubstringOf()
  {
    $testArray = array(
        ''                         => '',
        '<h1>test</h1>'            => '',
        'foo<h1></h1>bar'          => 'bar',
        '<h1></h1> '               => '',
        '</b></b>'                 => 'b>',
        'öäü<strong>lall</strong>' => '',
        ' b<b></b>'                => 'b>',
        '<b><b>lall</b>'           => 'b>',
        '</b>lall</b>'             => 'b>',
        '[b][/b]'                  => 'b]',
        '[B][/B]'                  => '',
        'κόσμbε ¡-öäü'             => 'bε ¡-öäü',
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create($testString);
      self::assertSame($testResult, $stringy->lastSubstringOf('b', false)->toString());
    }
  }

  public function testLastSubstringOfIgnoreCase()
  {
    $testArray = array(
        ''                         => '',
        '<h1>test</h1>'            => '',
        'foo<h1></h1>bar'          => 'bar',
        'foo<h1></h1>Bar'          => 'Bar',
        '<h1></h1> '               => '',
        '</b></b>'                 => 'b>',
        'öäü<strong>lall</strong>' => '',
        ' b<b></b>'                => 'b>',
        '<b><b>lall</b>'           => 'b>',
        '</b>lall</b>'             => 'b>',
        '[B][/B]'                  => 'B]',
        '[b][/b]'                  => 'b]',
        'κόσμbε ¡-öäü'             => 'bε ¡-öäü',
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create($testString);
      self::assertSame($testResult, $stringy->lastSubstringOfIgnoreCase('b', false)->toString());
    }
  }

  /**
   * @dataProvider lengthProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testLength($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->length();
    self::assertInternalType('int', $result);
    self::assertSame($expected, $result);
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider linesProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testLines($expected, $str, $encoding = null)
  {
    $result = S::create($str, $encoding)->lines();

    self::assertInternalType('array', $result);
    foreach ($result as $line) {
      self::assertStringy($line);
    }

    $counter = count($expected);
    /** @noinspection ForeachInvariantsInspection */
    for ($i = 0; $i < $counter; $i++) {
      self::assertSame($expected[$i], $result[$i]->toString());
    }
  }

  public function testLinewrap()
  {
    $testArray = array(
        ''                                                                                                      => "\n",
        ' '                                                                                                     => ' ' . "\n",
        'http:// moelleken.org'                                                                                 => 'http://' . "\n" . 'moelleken.org' . "\n",
        'http://test.de'                                                                                        => 'http://test.de' . "\n",
        'http://öäü.de'                                                                                         => 'http://öäü.de' . "\n",
        'http://menadwork.com'                                                                                  => 'http://menadwork.com' . "\n",
        'test.de'                                                                                               => 'test.de' . "\n",
        'test'                                                                                                  => 'test' . "\n",
        '0123456 789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789' => '0123456' . "\n" . '789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789' . "\n",
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create($testString);
      self::assertSame($testResult, $stringy->lineWrapAfterWord(10)->toString());
    }
  }

  /**
   * @dataProvider longestCommonPrefixProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $otherStr
   * @param null $encoding
   */
  public function testLongestCommonPrefix($expected, $str, $otherStr, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->longestCommonPrefix($otherStr);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider longestCommonSubstringProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $otherStr
   * @param null $encoding
   */
  public function testLongestCommonSubstring($expected, $str, $otherStr, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->longestCommonSubstring($otherStr);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider longestCommonSuffixProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $otherStr
   * @param null $encoding
   */
  public function testLongestCommonSuffix($expected, $str, $otherStr, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->longestCommonSuffix($otherStr);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider lowerCaseFirstProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testLowerCaseFirst($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->lowerCaseFirst();
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testMissingToString()
  {
    /** @noinspection PhpExpressionResultUnusedInspection */
    (string)new S(new stdClass());
    static::fail(
        'Expecting exception when the constructor is passed an ' .
        'object without a __toString method'
    );
  }

  /**
   * @dataProvider offsetExistsProvider()
   *
   * @param $expected
   * @param $offset
   */
  public function testOffsetExists($expected, $offset)
  {
    $stringy = S::create('fòô', 'UTF-8');
    self::assertSame($expected, $stringy->offsetExists($offset));
    self::assertSame($expected, isset($stringy[$offset]));
  }

  public function testOffsetGet()
  {
    $stringy = S::create('fòô', 'UTF-8');

    self::assertSame('f', $stringy->offsetGet(0));
    self::assertSame('ô', $stringy->offsetGet(2));

    self::assertSame('ô', $stringy[2]);
  }

  /**
   * @expectedException \OutOfBoundsException
   */
  public function testOffsetGetOutOfBounds()
  {
    $stringy = S::create('fòô', 'UTF-8');
    /** @noinspection PhpUnusedLocalVariableInspection */
    /** @noinspection OnlyWritesOnParameterInspection */
    $test = $stringy[3];
  }

  /**
   * @expectedException \Exception
   */
  public function testOffsetSet()
  {
    /** @noinspection OnlyWritesOnParameterInspection */
    $stringy = S::create('fòô', 'UTF-8');
    /** @noinspection OnlyWritesOnParameterInspection */
    $stringy[1] = 'invalid';
  }

  /**
   * @expectedException \Exception
   */
  public function testOffsetUnset()
  {
    $stringy = S::create('fòô', 'UTF-8');
    unset($stringy[1]);
  }

  /**
   * @dataProvider padProvider()
   *
   * @param        $expected
   * @param        $str
   * @param        $length
   * @param string $padStr
   * @param string $padType
   * @param null   $encoding
   */
  public function testPad($expected, $str, $length, $padStr = ' ', $padType = 'right', $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->pad($length, $padStr, $padType);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider padBothProvider()
   *
   * @param        $expected
   * @param        $str
   * @param        $length
   * @param string $padStr
   * @param null   $encoding
   */
  public function testPadBoth($expected, $str, $length, $padStr = ' ', $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->padBoth($length, $padStr);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testPadException()
  {
    $stringy = S::create('foo');
    $stringy->pad(5, 'foo', 'bar');
  }

  /**
   * @dataProvider padLeftProvider()
   *
   * @param        $expected
   * @param        $str
   * @param        $length
   * @param string $padStr
   * @param null   $encoding
   */
  public function testPadLeft($expected, $str, $length, $padStr = ' ', $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->padLeft($length, $padStr);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider padRightProvider()
   *
   * @param        $expected
   * @param        $str
   * @param        $length
   * @param string $padStr
   * @param null   $encoding
   */
  public function testPadRight($expected, $str, $length, $padStr = ' ', $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->padRight($length, $padStr);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider prependProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $string
   * @param null $encoding
   */
  public function testPrepend($expected, $str, $string, $encoding = null)
  {
    $result = S::create($str, $encoding)->prepend($string);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
  }

  /**
   * @dataProvider removeHtmlProvider()
   *
   * @param        $expected
   * @param        $str
   * @param string $allowableTags
   * @param null   $encoding
   */
  public function testRemoveHtml($expected, $str, $allowableTags = '', $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->removeHtml($allowableTags);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider removeHtmlBreakProvider()
   *
   * @param        $expected
   * @param        $str
   * @param string $replacement
   * @param null   $encoding
   */
  public function testRemoveHtmlBreak($expected, $str, $replacement = '', $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->removeHtmlBreak($replacement);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider removeLeftProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $substring
   * @param null $encoding
   */
  public function testRemoveLeft($expected, $str, $substring, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->removeLeft($substring);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider removeRightProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $substring
   * @param null $encoding
   */
  public function testRemoveRight($expected, $str, $substring, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->removeRight($substring);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider removeXssProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testRemoveXss($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->removeXss();
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider repeatProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $multiplier
   * @param null $encoding
   */
  public function testRepeat($expected, $str, $multiplier, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->repeat($multiplier);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider replaceProvider()
   *
   * @param string $expected
   * @param string $str
   * @param string $search
   * @param string $replacement
   * @param null   $encoding
   * @param bool   $caseSensitive
   */
  public function testReplace($expected, $str, $search, $replacement, $encoding = null, $caseSensitive = true)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->replace($search, $replacement, $caseSensitive);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider replaceAllProvider()
   *
   * @param string $expected
   * @param string $str
   * @param array  $search
   * @param string $replacement
   * @param null   $encoding
   * @param bool   $caseSensitive
   */
  public function testReplaceAll($expected, $str, $search, $replacement, $encoding = null, $caseSensitive = true)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->replaceAll($search, $replacement, $caseSensitive);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider replaceBeginningProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $search
   * @param      $replacement
   * @param null $encoding
   */
  public function testReplaceBeginning($expected, $str, $search, $replacement, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->replaceBeginning($search, $replacement);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider replaceEndingProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $search
   * @param      $replacement
   * @param null $encoding
   */
  public function testReplaceEnding($expected, $str, $search, $replacement, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->replaceEnding($search, $replacement);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider reverseProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testReverse($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->reverse();
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider safeTruncateProvider()
   *
   * @param        $expected
   * @param        $str
   * @param        $length
   * @param string $substring
   * @param null   $encoding
   */
  public function testSafeTruncate($expected, $str, $length, $substring = '', $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->safeTruncate($length, $substring);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString(), 'tested: ' . $str . ' | ' . $substring . ' (' . $length . ')');
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider shortenAfterWordProvider()
   *
   * @param        $expected
   * @param        $str
   * @param int    $length
   * @param string $strAddOn
   * @param null   $encoding
   */
  public function testShortenAfterWord($expected, $str, $length, $strAddOn = '...', $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->shortenAfterWord($length, $strAddOn);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider shuffleProvider()
   *
   * @param      $str
   * @param null $encoding
   */
  public function testShuffle($str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $encoding = $encoding ?: mb_internal_encoding();
    $result = $stringy->shuffle();

    self::assertStringy($result);
    self::assertSame($str, $stringy->toString());
    self::assertSame(
        mb_strlen($str, $encoding),
        mb_strlen($result, $encoding)
    );

    // We'll make sure that the chars are present after shuffle
    $length = mb_strlen($str, $encoding);
    for ($i = 0; $i < $length; $i++) {
      $char = mb_substr($str, $i, 1, $encoding);
      $countBefore = mb_substr_count($str, $char, $encoding);
      $countAfter = mb_substr_count($result, $char, $encoding);
      self::assertSame($countBefore, $countAfter);
    }
  }

  /**
   * @dataProvider sliceProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $start
   * @param null $end
   * @param null $encoding
   */
  public function testSlice($expected, $str, $start, $end = null, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->slice($start, $end);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider slugifyProvider()
   *
   * @param        $expected
   * @param        $str
   * @param string $replacement
   */
  public function testSlugify($expected, $str, $replacement = '-')
  {
    $stringy = S::create($str);
    $result = $stringy->slugify($replacement);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider snakeizeProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testSnakeize($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->snakeize();
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider splitProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $pattern
   * @param null $limit
   * @param null $encoding
   */
  public function testSplit($expected, $str, $pattern, $limit = null, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->split($pattern, $limit);

    self::assertInternalType('array', $result);
    foreach ($result as $string) {
      self::assertStringy($string);
    }

    $counter = count($expected);
    /** @noinspection ForeachInvariantsInspection */
    for ($i = 0; $i < $counter; $i++) {
      self::assertSame($expected[$i], $result[$i]->toString());
    }
  }

  /**
   * @dataProvider startsWithProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $substring
   * @param bool $caseSensitive
   * @param null $encoding
   */
  public function testStartsWith($expected, $str, $substring, $caseSensitive = true, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->startsWith($substring, $caseSensitive);
    self::assertInternalType('boolean', $result);
    self::assertSame($expected, $result);
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider startsWithProviderAny()
   *
   * @param      $expected
   * @param      $str
   * @param      $substring
   * @param bool $caseSensitive
   * @param null $encoding
   */
  public function testStartsWithAny($expected, $str, $substring, $caseSensitive = true, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->startsWithAny($substring, $caseSensitive);
    self::assertInternalType('boolean', $result);
    self::assertSame($expected, $result);
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider stripWhitespaceProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testStripWhitespace($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->stripWhitespace();
    $this->assertStringy($result);
    $this->assertEquals($expected, $result);
    $this->assertEquals($str, $stringy);
  }

  public function testStripeEmptyTags()
  {
    $testArray = array(
        ''                         => '',
        '<h1>test</h1>'            => '<h1>test</h1>',
        'foo<h1></h1>bar'          => 'foobar',
        '<h1></h1> '               => ' ',
        '</b></b>'                 => '</b></b>',
        'öäü<strong>lall</strong>' => 'öäü<strong>lall</strong>',
        ' b<b></b>'                => ' b',
        '<b><b>lall</b>'           => '<b><b>lall</b>',
        '</b>lall</b>'             => '</b>lall</b>',
        '[b][/b]'                  => '[b][/b]',
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create($testString);
      self::assertSame($testResult, $stringy->stripeEmptyHtmlTags()->toString());
    }
  }

  public function testStripeMediaQueries()
  {
    $testArray = array(
        'test lall '                                                                         => 'test lall ',
        ''                                                                                   => '',
        ' '                                                                                  => ' ',
        'test @media (min-width:660px){ .des-cla #mv-tiles{width:480px} } test '             => 'test  test ',
        'test @media only screen and (max-width: 950px) { .des-cla #mv-tiles{width:480px} }' => 'test ',
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create($testString);
      self::assertSame($testResult, $stringy->stripeCssMediaQueries()->toString());
    }
  }

  public function testSubStringOf()
  {
    $testArray = array(
        ''                         => '',
        '<h1>test</h1>'            => '',
        'foo<h1></h1>bar'          => 'bar',
        '<h1></h1> '               => '',
        '</b></b>'                 => 'b></b>',
        'öäü<strong>lall</strong>' => '',
        ' b<b></b>'                => 'b<b></b>',
        '<b><b>lall</b>'           => 'b><b>lall</b>',
        '</b>lall</b>'             => 'b>lall</b>',
        '[B][/B]'                  => '',
        '[b][/b]'                  => 'b][/b]',
        'κόσμbε ¡-öäü'             => 'bε ¡-öäü',
        'bκόσμbε'                  => 'bκόσμbε',
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create($testString);
      self::assertSame($testResult, $stringy->substringOf('b', false)->toString());
    }
  }

  /**
   * @dataProvider substrProvider()
   *
   * @param      $expected
   * @param      $str
   * @param      $start
   * @param null $length
   * @param null $encoding
   */
  public function testSubstr($expected, $str, $start, $length = null, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->substr($start, $length);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  public function testSubstringOfIgnoreCase()
  {
    $testArray = array(
        ''                         => '',
        '<h1>test</h1>'            => '',
        'foo<h1></h1>Bar'          => 'Bar',
        'foo<h1></h1>bar'          => 'bar',
        '<h1></h1> '               => '',
        '</b></b>'                 => 'b></b>',
        'öäü<strong>lall</strong>' => '',
        ' b<b></b>'                => 'b<b></b>',
        '<b><b>lall</b>'           => 'b><b>lall</b>',
        '</b>lall</b>'             => 'b>lall</b>',
        '[B][/B]'                  => 'B][/B]',
        'κόσμbε ¡-öäü'             => 'bε ¡-öäü',
        'bκόσμbε'                  => 'bκόσμbε',
    );

    foreach ($testArray as $testString => $testResult) {
      $stringy = S::create($testString);
      self::assertSame($testResult, $stringy->substringOfIgnoreCase('b', false)->toString());
    }
  }

  /**
   * @dataProvider surroundProvider()
   *
   * @param $expected
   * @param $str
   * @param $substring
   */
  public function testSurround($expected, $str, $substring)
  {
    $stringy = S::create($str);
    $result = $stringy->surround($substring);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider swapCaseProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testSwapCase($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->swapCase();
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider tidyProvider()
   *
   * @param $expected
   * @param $str
   */
  public function testTidy($expected, $str)
  {
    $stringy = S::create($str);
    $result = $stringy->tidy();
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider titleizeProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $ignore
   * @param null $encoding
   */
  public function testTitleize($expected, $str, $ignore = null, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->titleize($ignore);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider toAsciiProvider()
   *
   * @param $expected
   * @param $str
   */
  public function testToAscii($expected, $str)
  {
    $stringy = S::create($str);
    $result = $stringy->toAscii();

    self::assertStringy($result);
    self::assertSame($expected, $result->toString(), 'tested:' . $str);
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider toBooleanProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testToBoolean($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->toBoolean();
    self::assertInternalType('boolean', $result);
    self::assertSame($expected, $result);
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider toLowerCaseProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testToLowerCase($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->toLowerCase();
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider toSpacesProvider()
   *
   * @param     $expected
   * @param     $str
   * @param int $tabLength
   */
  public function testToSpaces($expected, $str, $tabLength = 4)
  {
    $stringy = S::create($str);
    $result = $stringy->toSpaces($tabLength);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider toStringProvider()
   *
   * @param $expected
   * @param $str
   */
  public function testToString($expected, $str)
  {
    $stringy = new S($str);
    self::assertSame($expected, (string)$stringy);
    self::assertSame($expected, $stringy->toString());
  }

  public function testToStringMethod()
  {
    $stringy = S::create('öäü - foo');
    $result = $stringy->toString();
    self::assertTrue(is_string($result));
    self::assertSame((string)$stringy, $result);
    self::assertSame('öäü - foo', $result);
  }

  /**
   * @dataProvider toTabsProvider()
   *
   * @param     $expected
   * @param     $str
   * @param int $tabLength
   */
  public function testToTabs($expected, $str, $tabLength = 4)
  {
    $stringy = S::create($str);
    $result = $stringy->toTabs($tabLength);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider toTitleCaseProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testToTitleCase($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->toTitleCase();
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider toUpperCaseProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testToUpperCase($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->toUpperCase();
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider trimProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $chars
   * @param null $encoding
   */
  public function testTrim($expected, $str, $chars = null, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->trim($chars);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider trimLeftProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $chars
   * @param null $encoding
   */
  public function testTrimLeft($expected, $str, $chars = null, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->trimLeft($chars);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider trimRightProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $chars
   * @param null $encoding
   */
  public function testTrimRight($expected, $str, $chars = null, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->trimRight($chars);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider truncateProvider()
   *
   * @param        $expected
   * @param        $str
   * @param        $length
   * @param string $substring
   * @param null   $encoding
   */
  public function testTruncate($expected, $str, $length, $substring = '', $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->truncate($length, $substring);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider underscoredProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testUnderscored($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->underscored();
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider upperCamelizeProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testUpperCamelize($expected, $str, $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->upperCamelize();
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @dataProvider upperCaseFirstProvider()
   *
   * @param      $expected
   * @param      $str
   * @param null $encoding
   */
  public function testUpperCaseFirst($expected, $str, $encoding = null)
  {
    $result = S::create($str, $encoding)->upperCaseFirst();
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
  }

  public function testUtf8ify()
  {
    $examples = array(
        ''                                     => array(''),
        // Valid UTF-8 + UTF-8 NO-BREAK SPACE
        "κόσμε\xc2\xa0"                        => array('κόσμε' . "\xc2\xa0" => 'κόσμε' . "\xc2\xa0"),
        // Valid UTF-8
        '中'                                    => array('中' => '中'),
        // Valid UTF-8 + ISO-Error
        'DÃ¼sseldorf'                          => array('Düsseldorf' => 'Düsseldorf'),
        // Valid UTF-8 + Invalid Chars
        "κόσμε\xa0\xa1-öäü"                    => array('κόσμε-öäü' => 'κόσμε-öäü'),
        // Valid ASCII
        'a'                                    => array('a' => 'a'),
        // Valid ASCII + Invalid Chars
        "a\xa0\xa1-öäü"                        => array('a-öäü' => 'a-öäü'),
        // Valid 2 Octet Sequence
        "\xc3\xb1"                             => array('ñ' => 'ñ'),
        // Invalid 2 Octet Sequence
        "\xc3\x28"                             => array('�(' => '('),
        // Invalid Sequence Identifier
        "\xa0\xa1"                             => array('��' => ''),
        // Valid 3 Octet Sequence
        "\xe2\x82\xa1"                         => array('₡' => '₡'),
        // Invalid 3 Octet Sequence (in 2nd Octet)
        "\xe2\x28\xa1"                         => array('�(�' => '('),
        // Invalid 3 Octet Sequence (in 3rd Octet)
        "\xe2\x82\x28"                         => array('�(' => '('),
        // Valid 4 Octet Sequence
        "\xf0\x90\x8c\xbc"                     => array('𐌼' => '𐌼'),
        // Invalid 4 Octet Sequence (in 2nd Octet)
        "\xf0\x28\x8c\xbc"                     => array('�(��' => '('),
        // Invalid 4 Octet Sequence (in 3rd Octet)
        "\xf0\x90\x28\xbc"                     => array('�(�' => '('),
        // Invalid 4 Octet Sequence (in 4th Octet)
        " \xf0\x28\x8c\x28"                    => array('�(�(' => ' (('),
        // Valid 5 Octet Sequence (but not Unicode!)
        "\xf8\xa1\xa1\xa1\xa1"                 => array('�' => ''),
        // Valid 6 Octet Sequence (but not Unicode!) + UTF-8 EN SPACE
        "\xfc\xa1\xa1\xa1\xa1\xa1\xe2\x80\x82" => array('�' => ' '),
        // test for database-insert
        '
        <h1>«DÃ¼sseldorf» &ndash; &lt;Köln&gt;</h1>
        <br /><br />
        <p>
          &nbsp;�&foo;❤&nbsp;
        </p>
        '                              => array(
            '' => '
        <h1>«Düsseldorf» &ndash; &lt;Köln&gt;</h1>
        <br /><br />
        <p>
          &nbsp;&foo;❤&nbsp;
        </p>
        ',
        ),
    );

    foreach ($examples as $testString => $testResults) {
      $stringy = S::create($testString);
      foreach ($testResults as $before => $after) {
        self::assertSame($after, $stringy->utf8ify()->toString());
      }
    }

    $examples = array(
      // Valid UTF-8
      'κόσμε'                    => array('κόσμε' => 'κόσμε'),
      '中'                        => array('中' => '中'),
      '«foobar»'                 => array('«foobar»' => '«foobar»'),
      // Valid UTF-8 + Invalied Chars
      "κόσμε\xa0\xa1-öäü"        => array('κόσμε-öäü' => 'κόσμε-öäü'),
      // Valid ASCII
      'a'                        => array('a' => 'a'),
      // Valid emoji (non-UTF-8)
      '😃'                       => array('😃' => '😃'),
      // Valid ASCII + Invalied Chars
      "a\xa0\xa1-öäü"            => array('a-öäü' => 'a-öäü'),
      // Valid 2 Octet Sequence
      "\xc3\xb1"                 => array('ñ' => 'ñ'),
      // Invalid 2 Octet Sequence
      "\xc3\x28"                 => array('�(' => '('),
      // Invalid Sequence Identifier
      "\xa0\xa1"                 => array('��' => ''),
      // Valid 3 Octet Sequence
      "\xe2\x82\xa1"             => array('₡' => '₡'),
      // Invalid 3 Octet Sequence (in 2nd Octet)
      "\xe2\x28\xa1"             => array('�(�' => '('),
      // Invalid 3 Octet Sequence (in 3rd Octet)
      "\xe2\x82\x28"             => array('�(' => '('),
      // Valid 4 Octet Sequence
      "\xf0\x90\x8c\xbc"         => array('𐌼' => '𐌼'),
      // Invalid 4 Octet Sequence (in 2nd Octet)
      "\xf0\x28\x8c\xbc"         => array('�(��' => '('),
      // Invalid 4 Octet Sequence (in 3rd Octet)
      "\xf0\x90\x28\xbc"         => array('�(�' => '('),
      // Invalid 4 Octet Sequence (in 4th Octet)
      "\xf0\x28\x8c\x28"         => array('�(�(' => '(('),
      // Valid 5 Octet Sequence (but not Unicode!)
      "\xf8\xa1\xa1\xa1\xa1"     => array('�' => ''),
      // Valid 6 Octet Sequence (but not Unicode!)
      "\xfc\xa1\xa1\xa1\xa1\xa1" => array('�' => ''),
    );

    $counter = 0;
    foreach ($examples as $testString => $testResults) {
      $stringy = S::create($testString);
      foreach ($testResults as $before => $after) {
        self::assertSame($after, $stringy->utf8ify()->toString(), $counter);
      }
      $counter++;
    }
  }

  /**
   * @dataProvider containsAnyProvider()
   *
   * @param      $expected
   * @param      $haystack
   * @param      $needles
   * @param bool $caseSensitive
   * @param null $encoding
   */
  public function testcontainsAny($expected, $haystack, $needles, $caseSensitive = true, $encoding = null)
  {
    $stringy = S::create($haystack, $encoding);
    $result = $stringy->containsAny($needles, $caseSensitive);
    self::assertInternalType('boolean', $result);
    self::assertSame($expected, $result);
    self::assertSame($haystack, $stringy->toString());
  }

  /**
   * @dataProvider regexReplaceProvider()
   *
   * @param        $expected
   * @param        $str
   * @param        $pattern
   * @param        $replacement
   * @param string $options
   * @param null   $encoding
   */
  public function testregexReplace($expected, $str, $pattern, $replacement, $options = 'msr', $encoding = null)
  {
    $stringy = S::create($str, $encoding);
    $result = $stringy->regexReplace($pattern, $replacement, $options);
    self::assertStringy($result);
    self::assertSame($expected, $result->toString());
    self::assertSame($str, $stringy->toString());
  }

  /**
   * @return array
   */
  public function tidyProvider()
  {
    return array(
        array('"I see..."', '“I see…”'),
        array("'This too'", '‘This too’'),
        array('test-dash', 'test—dash'),
        array('Ο συγγραφέας είπε...', 'Ο συγγραφέας είπε…'),
    );
  }

  /**
   * @return array
   */
  public function titleizeProvider()
  {
    $ignore = array('at', 'by', 'for', 'in', 'of', 'on', 'out', 'to', 'the');

    return array(
        array('Title Case', 'TITLE CASE'),
        array('Testing The Method', 'testing the method'),
        array('Testing the Method', 'testing the method', $ignore),
        array(
            'I Like to Watch Dvds at Home',
            'i like to watch DVDs at home',
            $ignore,
        ),
        array('Θα Ήθελα Να Φύγει', '  Θα ήθελα να φύγει  ', null, 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function toAsciiProvider()
  {
    return array(
        array('foo bar', 'fòô bàř'),
        array(' TEST ', ' ŤÉŚŢ '),
        array('ph = z = 3', 'φ = ź = 3'),
        array('perevirka', 'перевірка'),
        array('lysaia gora', 'лысая гора'),
        array('shchuka', 'щука'),
        array('Han Zi ', '漢字'),
        array('xin chao the gioi', 'xin chào thế giới'),
        array('XIN CHAO THE GIOI', 'XIN CHÀO THẾ GIỚI'),
        array('dam phat chet luon', 'đấm phát chết luôn'),
        array(' ', ' '), // no-break space (U+00A0)
        array('           ', '           '), // spaces U+2000 to U+200A
        array(' ', ' '), // narrow no-break space (U+202F)
        array(' ', ' '), // medium mathematical space (U+205F)
        array(' ', '　'), // ideographic space (U+3000)
        array('?', '𐍉'), // some uncommon, unsupported character (U+10349)
    );
  }

  /**
   * @return array
   */
  public function toBooleanProvider()
  {
    return array(
        array(true, 'true'),
        array(true, '1'),
        array(true, 'on'),
        array(true, 'ON'),
        array(true, 'yes'),
        array(true, '999'),
        array(false, 'false'),
        array(false, '0'),
        array(false, 'off'),
        array(false, 'OFF'),
        array(false, 'no'),
        array(false, '-999'),
        array(false, ''),
        array(false, ' '),
        array(false, '  ', 'UTF-8') // narrow no-break space (U+202F)
    );
  }

  /**
   * @return array
   */
  public function toLowerCaseProvider()
  {
    return array(
        array('foo bar', 'FOO BAR'),
        array(' foo_bar ', ' FOO_bar '),
        array('fòô bàř', 'FÒÔ BÀŘ', 'UTF-8'),
        array(' fòô_bàř ', ' FÒÔ_bàř ', 'UTF-8'),
        array('αυτοκίνητο', 'ΑΥΤΟΚΊΝΗΤΟ', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function toSpacesProvider()
  {
    return array(
        array('    foo    bar    ', '	foo	bar	'),
        array('     foo     bar     ', '	foo	bar	', 5),
        array('    foo  bar  ', '		foo	bar	', 2),
        array('foobar', '	foo	bar	', 0),
        array("    foo\n    bar", "	foo\n	bar"),
        array("    fòô\n    bàř", "	fòô\n	bàř"),
    );
  }

  /**
   * @return array
   */
  public function toStringProvider()
  {
    return array(
        array('', null),
        array('', false),
        array('1', true),
        array('-9', -9),
        array('1.18', 1.18),
        array(' string  ', ' string  '),
    );
  }

  /**
   * @return array
   */
  public function toTabsProvider()
  {
    return array(
        array('	foo	bar	', '    foo    bar    '),
        array('	foo	bar	', '     foo     bar     ', 5),
        array('		foo	bar	', '    foo  bar  ', 2),
        array("	foo\n	bar", "    foo\n    bar"),
        array("	fòô\n	bàř", "    fòô\n    bàř"),
    );
  }

  /**
   * @return array
   */
  public function toTitleCaseProvider()
  {
    return array(
        array('Foo Bar', 'foo bar'),
        array(' Foo_Bar ', ' foo_bar '),
        array('Fòô Bàř', 'fòô bàř', 'UTF-8'),
        array(' Fòô_Bàř ', ' fòô_bàř ', 'UTF-8'),
        array('Αυτοκίνητο Αυτοκίνητο', 'αυτοκίνητο αυτοκίνητο', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function toUpperCaseProvider()
  {
    return array(
        array('FOO BAR', 'foo bar'),
        array(' FOO_BAR ', ' FOO_bar '),
        array('FÒÔ BÀŘ', 'fòô bàř', 'UTF-8'),
        array(' FÒÔ_BÀŘ ', ' FÒÔ_bàř ', 'UTF-8'),
        array('ΑΥΤΟΚΊΝΗΤΟ', 'αυτοκίνητο', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function trimLeftProvider()
  {
    return array(
        array('foo   bar  ', '  foo   bar  '),
        array('foo bar', ' foo bar'),
        array('foo bar ', 'foo bar '),
        array("foo bar \n\t", "\n\t foo bar \n\t"),
        array('fòô   bàř  ', '  fòô   bàř  '),
        array('fòô bàř', ' fòô bàř'),
        array('fòô bàř ', 'fòô bàř '),
        array('foo bar', '--foo bar', '-'),
        array('fòô bàř', 'òòfòô bàř', 'ò', 'UTF-8'),
        array("fòô bàř \n\t", "\n\t fòô bàř \n\t", null, 'UTF-8'),
        array('fòô ', ' fòô ', null, 'UTF-8'), // narrow no-break space (U+202F)
        array('fòô  ', '  fòô  ', null, 'UTF-8'), // medium mathematical space (U+205F)
        array('fòô', '           fòô', null, 'UTF-8') // spaces U+2000 to U+200A
    );
  }

  /**
   * @return array
   */
  public function trimProvider()
  {
    return array(
        array('foo   bar', '  foo   bar  '),
        array('foo bar', ' foo bar'),
        array('foo bar', 'foo bar '),
        array('foo bar', "\n\t foo bar \n\t"),
        array('fòô   bàř', '  fòô   bàř  '),
        array('fòô bàř', ' fòô bàř'),
        array('fòô bàř', 'fòô bàř '),
        array(' foo bar ', "\n\t foo bar \n\t", "\n\t"),
        array('fòô bàř', "\n\t fòô bàř \n\t", null, 'UTF-8'),
        array('fòô', ' fòô ', null, 'UTF-8'), // narrow no-break space (U+202F)
        array('fòô', '  fòô  ', null, 'UTF-8'), // medium mathematical space (U+205F)
        array('fòô', '           fòô', null, 'UTF-8') // spaces U+2000 to U+200A
    );
  }

  /**
   * @return array
   */
  public function trimRightProvider()
  {
    return array(
        array('  foo   bar', '  foo   bar  '),
        array('foo bar', 'foo bar '),
        array(' foo bar', ' foo bar'),
        array("\n\t foo bar", "\n\t foo bar \n\t"),
        array('  fòô   bàř', '  fòô   bàř  '),
        array('fòô bàř', 'fòô bàř '),
        array(' fòô bàř', ' fòô bàř'),
        array('foo bar', 'foo bar--', '-'),
        array('fòô bàř', 'fòô bàřòò', 'ò', 'UTF-8'),
        array("\n\t fòô bàř", "\n\t fòô bàř \n\t", null, 'UTF-8'),
        array(' fòô', ' fòô ', null, 'UTF-8'), // narrow no-break space (U+202F)
        array('  fòô', '  fòô  ', null, 'UTF-8'), // medium mathematical space (U+205F)
        array('fòô', 'fòô           ', null, 'UTF-8') // spaces U+2000 to U+200A
    );
  }

  /**
   * @return array
   */
  public function truncateProvider()
  {
    return array(
        array('Test foo bar', 'Test foo bar', 12),
        array('Test foo ba', 'Test foo bar', 11),
        array('Test foo', 'Test foo bar', 8),
        array('Test fo', 'Test foo bar', 7),
        array('Test', 'Test foo bar', 4),
        array('Test foo bar', 'Test foo bar', 12, '...'),
        array('Test foo...', 'Test foo bar', 11, '...'),
        array('Test ...', 'Test foo bar', 8, '...'),
        array('Test...', 'Test foo bar', 7, '...'),
        array('T...', 'Test foo bar', 4, '...'),
        array('Test fo....', 'Test foo bar', 11, '....'),
        array('Test fòô bàř', 'Test fòô bàř', 12, '', 'UTF-8'),
        array('Test fòô bà', 'Test fòô bàř', 11, '', 'UTF-8'),
        array('Test fòô', 'Test fòô bàř', 8, '', 'UTF-8'),
        array('Test fò', 'Test fòô bàř', 7, '', 'UTF-8'),
        array('Test', 'Test fòô bàř', 4, '', 'UTF-8'),
        array('Test fòô bàř', 'Test fòô bàř', 12, 'ϰϰ', 'UTF-8'),
        array('Test fòô ϰϰ', 'Test fòô bàř', 11, 'ϰϰ', 'UTF-8'),
        array('Test fϰϰ', 'Test fòô bàř', 8, 'ϰϰ', 'UTF-8'),
        array('Test ϰϰ', 'Test fòô bàř', 7, 'ϰϰ', 'UTF-8'),
        array('Teϰϰ', 'Test fòô bàř', 4, 'ϰϰ', 'UTF-8'),
        array('What are your pl...', 'What are your plans today?', 19, '...'),
    );
  }

  /**
   * @return array
   */
  public function underscoredProvider()
  {
    return array(
        array('test_case', 'testCase'),
        array('test_case', 'Test-Case'),
        array('test_case', 'test case'),
        array('test_case', 'test -case'),
        array('_test_case', '-test - case'),
        array('test_case', 'test_case'),
        array('test_c_test', '  test c test'),
        array('test_u_case', 'TestUCase'),
        array('test_c_c_test', 'TestCCTest'),
        array('string_with1number', 'string_with1number'),
        array('string_with_2_2_numbers', 'String-with_2_2 numbers'),
        array('1test2case', '1test2case'),
        array('yes_we_can', 'yesWeCan'),
        array('test_σase', 'test Σase', 'UTF-8'),
        array('στανιλ_case', 'Στανιλ case', 'UTF-8'),
        array('σash_case', 'Σash  Case', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function upperCamelizeProvider()
  {
    return array(
        array('CamelCase', 'camelCase'),
        array('CamelCase', 'Camel-Case'),
        array('CamelCase', 'camel case'),
        array('CamelCase', 'camel -case'),
        array('CamelCase', 'camel - case'),
        array('CamelCase', 'camel_case'),
        array('CamelCTest', 'camel c test'),
        array('StringWith1Number', 'string_with1number'),
        array('StringWith22Numbers', 'string-with-2-2 numbers'),
        array('1Camel2Case', '1camel2case'),
        array('CamelΣase', 'camel σase', 'UTF-8'),
        array('ΣτανιλCase', 'στανιλ case', 'UTF-8'),
        array('ΣamelCase', 'Σamel  Case', 'UTF-8'),
    );
  }

  /**
   * @return array
   */
  public function upperCaseFirstProvider()
  {
    return array(
        array('Test', 'Test'),
        array('Test', 'test'),
        array('1a', '1a'),
        array('Σ test', 'σ test', 'UTF-8'),
        array(' σ test', ' σ test', 'UTF-8'),
    );
  }
}
