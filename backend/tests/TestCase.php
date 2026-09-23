<?php

namespace Tests;

use App\Support\Media\ProtectedMedia;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function assertProtectedDisplayUrl(?string $url, string $relativePath): void
    {
        $this->assertIsString($url);
        $path = parse_url($url, PHP_URL_PATH);
        $this->assertIsString($path);
        $this->assertStringStartsWith(ProtectedMedia::URL_PREFIX, $path);
        $this->assertStringNotContainsString('/storage/', $path);
        $this->assertStringNotContainsString($relativePath, $url);
        $token = basename($path);
        $this->assertSame($relativePath, ProtectedMedia::pathFromToken($token));
    }
}
