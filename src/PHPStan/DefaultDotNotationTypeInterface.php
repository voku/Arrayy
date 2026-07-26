<?php

declare(strict_types=1);

namespace Arrayy\PHPStan;

/**
 * Marks a typed Arrayy subclass that keeps the default "." path separator.
 *
 * Implementations must not call changeSeparator() with another separator.
 *
 */
interface DefaultDotNotationTypeInterface
{
}
