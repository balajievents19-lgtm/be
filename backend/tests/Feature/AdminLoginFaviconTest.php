<?php

namespace Tests\Feature;

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class AdminLoginFaviconTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    public function test_admin_login_uses_cms_favicon_url(): void
    {
        $this->seedSettings([
            'favicon' => 'settings/brand/01KZZK64KV96F26WKW0ZCTGZMD.png',
        ]);

        $favicon = Filament::getPanel('admin')->getFavicon();
        $this->assertProtectedDisplayUrl(
            $favicon,
            'settings/brand/01KZZK64KV96F26WKW0ZCTGZMD.png'
        );

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee((string) $favicon, false)
            ->assertDontSee('/storage/settings/brand/01KZZK64KV96F26WKW0ZCTGZMD.png', false);
    }
}
