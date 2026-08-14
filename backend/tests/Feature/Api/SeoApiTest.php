<?php

namespace Tests\Feature\Api;

use App\Filament\Clusters\SeoCluster;
use App\Filament\Pages\Seo\ManageGlobalSeo;
use App\Filament\Pages\Seo\ManageHomepageSeo;
use App\Models\User;
use App\Support\Rbac\AdminModules;
use Database\Seeders\RBACSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class SeoApiTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->seedSettings([
            'meta_title' => 'Balaji Events SEO',
            'meta_description' => 'SEO description',
            'canonical_url' => 'https://frontend.test',
            'company_name' => 'Balaji Events',
            'company_description' => 'Wedding and event management.',
            'opengraph_image' => 'settings/seo/og.jpg',
        ]);
        config(['seo.site_url' => 'https://frontend.test']);
    }

    public function test_seo_show_returns_global_payload(): void
    {
        $this->getJson('/api/seo')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'meta' => [
                        'title',
                        'description',
                        'canonical',
                        'open_graph' => ['title', 'description', 'url', 'type', 'image'],
                        'twitter' => ['card', 'title', 'description', 'image'],
                    ],
                    'schema',
                ],
            ])
            ->assertJsonPath('data.meta.title', 'Balaji Events SEO')
            ->assertJsonPath('data.meta.description', 'SEO description')
            ->assertJsonPath('data.meta.canonical', 'https://frontend.test')
            ->assertJsonPath('data.meta.open_graph.url', 'https://frontend.test');
    }

    public function test_seo_resolve_home_meta_canonical_og_and_faq_schema(): void
    {
        $this->seedSettings([
            'homepage_seo_title' => 'Homepage SEO Title',
            'homepage_seo_description' => 'Homepage SEO Description',
            'homepage_seo_keywords' => 'wedding, events',
            'canonical_url' => 'https://frontend.test',
            'meta_title' => 'Balaji Events SEO',
            'meta_description' => 'SEO description',
        ]);
        $this->createFaq();

        $response = $this->getJson('/api/seo/resolve?type=home')
            ->assertOk()
            ->assertJsonPath('data.meta.title', 'Homepage SEO Title')
            ->assertJsonPath('data.meta.description', 'Homepage SEO Description')
            ->assertJsonPath('data.meta.keywords', 'wedding, events')
            ->assertJsonPath('data.meta.canonical', 'https://frontend.test')
            ->assertJsonPath('data.meta.open_graph.title', 'Homepage SEO Title')
            ->assertJsonPath('data.meta.open_graph.description', 'Homepage SEO Description')
            ->assertJsonPath('data.meta.open_graph.url', 'https://frontend.test')
            ->assertJsonPath('data.meta.open_graph.type', 'website');

        $schemaTypes = collect($response->json('data.schema'))->pluck('@type')->all();
        $this->assertContains('FAQPage', $schemaTypes);
        $this->assertContains('Organization', $schemaTypes);
        $this->assertContains('WebSite', $schemaTypes);
        $this->assertContains('BreadcrumbList', $schemaTypes);
    }

    public function test_seo_resolve_about_services_packages_contact(): void
    {
        $this->getJson('/api/seo/resolve?type=about')
            ->assertOk()
            ->assertJsonPath('data.meta.canonical', 'https://frontend.test/about')
            ->assertJsonPath('data.meta.open_graph.url', 'https://frontend.test/about');

        $this->getJson('/api/seo/resolve?type=services')
            ->assertOk()
            ->assertJsonPath('data.meta.canonical', 'https://frontend.test/services')
            ->assertJsonPath('data.meta.title', 'Services | Balaji Events');

        $this->getJson('/api/seo/resolve?type=packages')
            ->assertOk()
            ->assertJsonPath('data.meta.canonical', 'https://frontend.test/packages')
            ->assertJsonPath('data.meta.title', 'Packages | Balaji Events');

        $this->getJson('/api/seo/resolve?type=contact')
            ->assertOk()
            ->assertJsonPath('data.meta.canonical', 'https://frontend.test/contact')
            ->assertJsonPath('data.meta.title', 'Contact Us | Balaji Events');
    }

    public function test_seo_resolve_service_detail(): void
    {
        $this->createService(['seo_title' => 'Service SEO Title', 'seo_description' => 'Service SEO Description']);

        $this->getJson('/api/seo/resolve?type=service&slug=wedding-planning')
            ->assertOk()
            ->assertJsonPath('data.meta.title', 'Service SEO Title')
            ->assertJsonPath('data.meta.description', 'Service SEO Description')
            ->assertJsonPath('data.meta.canonical', 'https://frontend.test/services/wedding-planning')
            ->assertJsonPath('data.meta.open_graph.url', 'https://frontend.test/services/wedding-planning');

        $schemaTypes = collect(
            $this->getJson('/api/seo/resolve?type=service&slug=wedding-planning')->json('data.schema')
        )->pluck('@type')->all();
        $this->assertContains('Service', $schemaTypes);
        $this->assertContains('BreadcrumbList', $schemaTypes);
    }

    public function test_seo_resolve_gallery_and_gallery_category(): void
    {
        $item = $this->createGalleryItem();
        $category = $item->category;

        $this->getJson('/api/seo/resolve?type=gallery')
            ->assertOk()
            ->assertJsonPath('data.meta.canonical', 'https://frontend.test/gallery')
            ->assertJsonPath('data.meta.title', 'Gallery | Balaji Events');

        $this->getJson('/api/seo/resolve?type=gallery-category&slug='.$category->slug)
            ->assertOk()
            ->assertJsonPath('data.meta.canonical', 'https://frontend.test/gallery/'.$category->slug)
            ->assertJsonPath('data.meta.title', $category->name.' Gallery | Balaji Events');
    }

    public function test_seo_resolve_blog_index_and_detail(): void
    {
        $this->createBlogPost(['seo_title' => 'Post SEO Title', 'seo_description' => 'Post SEO Description']);

        $this->getJson('/api/seo/resolve?type=blog-index')
            ->assertOk()
            ->assertJsonPath('data.meta.canonical', 'https://frontend.test/blog');

        $response = $this->getJson('/api/seo/resolve?type=blog&slug=season-highlights')
            ->assertOk()
            ->assertJsonPath('data.meta.title', 'Post SEO Title')
            ->assertJsonPath('data.meta.description', 'Post SEO Description')
            ->assertJsonPath('data.meta.canonical', 'https://frontend.test/blog/season-highlights')
            ->assertJsonPath('data.meta.open_graph.type', 'article');

        $schemaTypes = collect($response->json('data.schema'))->pluck('@type')->all();
        $this->assertContains('BlogPosting', $schemaTypes);
    }

    public function test_seo_resolve_faq_index_and_detail_keep_faqpage_schema(): void
    {
        $this->createFaq();

        $index = $this->getJson('/api/seo/resolve?type=faq')
            ->assertOk()
            ->assertJsonPath('data.meta.canonical', 'https://frontend.test/faq');

        $this->assertContains('FAQPage', collect($index->json('data.schema'))->pluck('@type')->all());

        $detail = $this->getJson('/api/seo/resolve?type=faq-detail&slug=how-do-i-book')
            ->assertOk()
            ->assertJsonPath('data.meta.canonical', 'https://frontend.test/faq/how-do-i-book');

        $this->assertContains('FAQPage', collect($detail->json('data.schema'))->pluck('@type')->all());
    }

    public function test_seo_resolve_rejects_unknown_type(): void
    {
        $this->getJson('/api/seo/resolve?type=unknown')
            ->assertStatus(422);
    }

    public function test_sitemap_includes_public_routes_and_excludes_admin(): void
    {
        $item = $this->createGalleryItem();
        $this->createService();
        $this->createBlogPost();
        $this->createFaq();

        $xml = $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('https://frontend.test', false)
            ->assertSee('/about', false)
            ->assertSee('/services', false)
            ->assertSee('/services/wedding-planning', false)
            ->assertSee('/packages', false)
            ->assertSee('/gallery', false)
            ->assertSee('/gallery/'.$item->category->slug, false)
            ->assertSee('/blog', false)
            ->assertSee('/blog/season-highlights', false)
            ->assertSee('/faq', false)
            ->assertSee('/faq/how-do-i-book', false)
            ->assertSee('/contact', false)
            ->getContent();

        $this->assertStringNotContainsString('/admin', $xml);
        $this->assertStringNotContainsString('/gallery/reception-hall', $xml);
    }

    public function test_robots_allows_public_and_disallows_admin(): void
    {
        $body = $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('User-agent: *', false)
            ->assertSee('Allow: /', false)
            ->assertSee('Disallow: /admin', false)
            ->assertSee('Sitemap:', false)
            ->assertSee('/sitemap.xml', false)
            ->getContent();

        $this->assertStringNotContainsString('Disallow: /about', $body);
        $this->assertStringNotContainsString('Disallow: /services', $body);
        $this->assertStringNotContainsString('Disallow: /gallery', $body);
        $this->assertStringNotContainsString('Disallow: /blog', $body);
        $this->assertStringNotContainsString('Disallow: /faq', $body);
        $this->assertStringNotContainsString('Disallow: /contact', $body);
        $this->assertStringNotContainsString('Disallow: /packages', $body);
    }

    public function test_admin_seo_authorization_uses_existing_rbac(): void
    {
        $this->seed(RBACSeeder::class);

        $super = User::factory()->create(['email' => 'seo.super@balaji.test']);
        $super->assignRole(AdminModules::ROLE_SUPER_ADMIN);

        $content = User::factory()->create(['email' => 'seo.content@balaji.test']);
        $content->assignRole(AdminModules::ROLE_CONTENT_MANAGER);

        $lead = User::factory()->create(['email' => 'seo.lead@balaji.test']);
        $lead->assignRole(AdminModules::ROLE_LEAD_MANAGER);

        $this->actingAs($super);
        $this->assertTrue($super->can('seo.view'));
        $this->assertTrue(SeoCluster::canAccess());
        $this->assertTrue(ManageGlobalSeo::canAccess());
        $this->assertTrue(ManageHomepageSeo::canAccess());

        $this->actingAs($content);
        $this->assertTrue($content->can('seo.view'));
        $this->assertTrue(SeoCluster::canAccess());
        $this->assertTrue(ManageGlobalSeo::canAccess());

        $this->actingAs($lead);
        $this->assertFalse($lead->can('seo.view'));
        $this->assertFalse(SeoCluster::canAccess());
        $this->assertFalse(ManageGlobalSeo::canAccess());
    }

    public function test_local_business_schema_does_not_invent_price_range(): void
    {
        $schema = collect($this->getJson('/api/seo')->json('data.schema'))
            ->firstWhere('@type', 'LocalBusiness');

        $this->assertIsArray($schema);
        $this->assertArrayNotHasKey('priceRange', $schema);
    }

    public function test_site_url_config_preferred_over_app_url_for_canonical_og_and_sitemap(): void
    {
        config([
            'app.url' => 'http://api.test',
            'seo.site_url' => 'http://public.test:3000',
        ]);

        $this->seedSettings([
            'canonical_url' => null,
            'meta_title' => 'Balaji Events SEO',
            'meta_description' => 'SEO description',
        ]);

        Cache::flush();

        $this->getJson('/api/seo/resolve?type=home')
            ->assertOk()
            ->assertJsonPath('data.meta.canonical', 'http://public.test:3000')
            ->assertJsonPath('data.meta.open_graph.url', 'http://public.test:3000');

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringContainsString('<loc>http://public.test:3000</loc>', $xml);
        $this->assertStringContainsString('<loc>http://public.test:3000/about</loc>', $xml);
        $this->assertStringNotContainsString('<loc>http://api.test', $xml);
    }
}
