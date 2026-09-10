<?php

namespace Tests\Feature\Helpers;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PriceHelperTest extends TestCase
{
    use DatabaseTransactions;

    public function test_numeric_price_with_no_currency_settings_returns_price_unchanged(): void
    {
        // testing DB has no currencies/settings rows: exchange rate falls back to 0
        $this->assertEquals(699, convertPriceToUserCurrency(699));
        $this->assertEquals(0, convertPriceToUserCurrency(0));
    }

    public function test_numeric_string_price_is_accepted(): void
    {
        $this->assertEquals(699, convertPriceToUserCurrency('699'));
    }

    public function test_default_currency_conversion_with_no_settings_returns_price(): void
    {
        $this->assertEquals(250, convertPriceToDefaultCurrency(250));
    }
}
