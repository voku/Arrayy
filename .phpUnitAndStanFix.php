<?php

namespace PHPStan\Testing {
    if (!function_exists('\\PHPStan\\Testing\\assertType')) {
        function assertType(string $expectedType, $value) { }
    }
}