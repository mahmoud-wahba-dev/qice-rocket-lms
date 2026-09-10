<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Pure unit tests for currency helpers.
 *
 * These cover the no-database early-return guards (PHP 8 strict arithmetic).
 * Numeric paths that hit the DB live in tests/Feature/Helpers/PriceHelperTest.php.
 */
class PriceHelperTest extends TestCase
{
    public function test_non_numeric_price_returns_zero_without_error(): void
    {
        $this->assertSame(0, convertPriceToUserCurrency(''));
        $this->assertSame(0, convertPriceToUserCurrency(null));
        $this->assertSame(0, convertPriceToUserCurrency('not-a-price'));
        $this->assertSame(0, convertPriceToUserCurrency([]));
    }

    public function test_non_numeric_price_to_default_currency_returns_zero(): void
    {
        $this->assertSame(0, convertPriceToDefaultCurrency(''));
        $this->assertSame(0, convertPriceToDefaultCurrency(null));
        $this->assertSame(0, convertPriceToDefaultCurrency('abc'));
    }
}
