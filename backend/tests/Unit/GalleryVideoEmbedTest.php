<?php

namespace Tests\Unit;

use App\Support\Media\GalleryVideoEmbed;
use Tests\TestCase;

class GalleryVideoEmbedTest extends TestCase
{
    public function test_youtube_watch_and_shorts_and_youtu_be(): void
    {
        $watch = GalleryVideoEmbed::resolve('youtube', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        $this->assertTrue($watch['valid']);
        $this->assertSame('embed', $watch['mode']);
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $watch['embed_url']);
        $this->assertSame('dQw4w9WgXcQ', $watch['video_id']);
        $this->assertNull($watch['open_url']);
        $this->assertNull($watch['cta_label']);
        $this->assertSame('https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $watch['poster_url']);

        $short = GalleryVideoEmbed::resolve('youtube', 'https://www.youtube.com/shorts/dQw4w9WgXcQ');
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $short['embed_url']);

        $be = GalleryVideoEmbed::resolve('youtube', 'https://youtu.be/dQw4w9WgXcQ');
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $be['embed_url']);
    }

    public function test_instagram_reel_and_post_embed(): void
    {
        $reel = GalleryVideoEmbed::resolve('instagram', 'https://www.instagram.com/reel/AbC123xyz/');
        $this->assertTrue($reel['valid']);
        $this->assertSame('embed', $reel['mode']);
        $this->assertSame('https://www.instagram.com/reel/AbC123xyz/embed/', $reel['embed_url']);

        $post = GalleryVideoEmbed::resolve('instagram', 'https://www.instagram.com/p/AbC123xyz/');
        $this->assertSame('https://www.instagram.com/p/AbC123xyz/embed/', $post['embed_url']);
    }

    public function test_facebook_plugin_embed(): void
    {
        $url = 'https://www.facebook.com/watch/?v=123456789';
        $resolved = GalleryVideoEmbed::resolve('facebook', $url);
        $this->assertTrue($resolved['valid']);
        $this->assertSame('embed', $resolved['mode']);
        $this->assertStringStartsWith('https://www.facebook.com/plugins/video.php?href=', (string) $resolved['embed_url']);
        $this->assertStringContainsString(rawurlencode($url), (string) $resolved['embed_url']);
    }

    public function test_other_https_youtube_embeds_and_unknown_falls_back_to_link(): void
    {
        $yt = GalleryVideoEmbed::resolve('other', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        $this->assertSame('embed', $yt['mode']);
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $yt['embed_url']);
        $this->assertNull($yt['open_url']);

        $page = GalleryVideoEmbed::resolve('other', 'https://example.com/about');
        $this->assertTrue($page['valid']);
        $this->assertSame('link', $page['mode']);
        $this->assertNull($page['embed_url']);
        $this->assertSame('https://example.com/about', $page['open_url']);
    }

    public function test_rejects_javascript_and_source_mismatch(): void
    {
        $this->assertFalse(GalleryVideoEmbed::sourceMatches('youtube', 'https://www.youtube.com/channel/UCxxxxxxxx'));
        $this->assertFalse(GalleryVideoEmbed::sourceMatches('youtube', 'javascript:alert(1)'));
        $this->assertFalse(GalleryVideoEmbed::sourceMatches('instagram', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'));
        $this->assertFalse(GalleryVideoEmbed::resolve('other', 'http://example.com/video')['valid']);
    }
}
