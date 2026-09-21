<?php

namespace Tests\Unit;

use App\Support\PhoneNumber;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    public function test_exact_ten_digits_have_no_starting_digit_restriction(): void
    {
        $this->assertTrue(PhoneNumber::isExactTenDigits('0462577065'));
        $this->assertTrue(PhoneNumber::isExactTenDigits('9462577065'));
        $this->assertFalse(PhoneNumber::isExactTenDigits('+919462577065'));
        $this->assertFalse(PhoneNumber::isExactTenDigits('94625 77065'));
        $this->assertFalse(PhoneNumber::isExactTenDigits('94625-77065'));
        $this->assertFalse(PhoneNumber::isExactTenDigits('919462577065'));
    }

    public function test_local_ten_reads_legacy_country_prefix(): void
    {
        $this->assertSame('9462577065', PhoneNumber::localTen('9462577065'));
        $this->assertSame('9462577065', PhoneNumber::localTen('919462577065'));
        $this->assertSame('9462577065', PhoneNumber::localTen('+91-9462577065'));
    }
}
