<?php

require_once __DIR__ . '/../src/Stringy.php';

class StringyExtend extends Stringy\Stringy
{
    public function __construct($str = '', string $encoding = null)
    {
        parent::__construct($str, $encoding);

        if (!$this->str) {
            $this->str = 'Töst';
        }
    }

    /**
     * @return string
     */
    public function fooBar()
    {
        return $this->str;
    }
}
