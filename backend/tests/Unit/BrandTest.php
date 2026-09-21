<?php

namespace Tests\Unit;

use App\Support\Brand;
use PHPUnit\Framework\TestCase;

class BrandTest extends TestCase
{
    public function test_name_normalizes_outdated_labels(): void
    {
        $this->assertSame('Balaji Royal Events', Brand::name(null));
        $this->assertSame('Balaji Royal Events', Brand::name(''));
        $this->assertSame('Balaji Royal Events', Brand::name('Balaji Events'));
        $this->assertSame('Balaji Royal Events', Brand::name('Balaji Event'));
        $this->assertSame('Balaji Royal Events', Brand::name('BALAJI EVENTS'));
        $this->assertSame('Balaji Royal Events', Brand::name('Balaji Royal Events'));
        $this->assertSame(
            'Contact Balaji Royal Events in Jhunjhunu',
            Brand::name('Contact Balaji Events in Jhunjhunu')
        );
    }

    public function test_rewrite_leaves_empty_optional_text_empty(): void
    {
        $this->assertNull(Brand::rewrite(null));
        $this->assertSame('', Brand::rewrite(''));
        $this->assertSame('Wedding planning in Rajasthan', Brand::rewrite('Wedding planning in Rajasthan'));
    }
}
