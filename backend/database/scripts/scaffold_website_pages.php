<?php

$base = dirname(__DIR__, 2);

function write_file(string $path, string $contents): void
{
    $dir = dirname($path);
    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($path, $contents);
    echo 'Wrote '.str_replace($GLOBALS['base'].DIRECTORY_SEPARATOR, '', $path).PHP_EOL;
}

function setting_page(
    string $ns,
    string $class,
    string $cluster,
    string $label,
    int $sort,
    string $slug,
    string $fieldsCode,
    string $preview = '/',
    string $extraUses = ''
): string {
    return <<<PHP
<?php

namespace App\\Filament\\Pages\\{$ns};

use App\\Filament\\Clusters\\{$cluster};
use App\\Filament\\Pages\\EditWebsiteSettingPage;
{$extraUses}
class {$class} extends EditWebsiteSettingPage
{
    protected static bool \$isDiscovered = true;

    protected static ?string \$cluster = {$cluster}::class;

    protected static ?string \$navigationLabel = '{$label}';

    protected static ?string \$title = '{$label}';

    protected static ?int \$navigationSort = {$sort};

    protected static ?string \$slug = '{$slug}';

    protected function previewPath(): string
    {
        return '{$preview}';
    }

    protected function formFields(): array
    {
        return {$fieldsCode};
    }
}

PHP;
}

$pages = [
    // Brand
    ['Brand', 'ManageLogo', 'BrandCluster', 'Logo', 1, 'logo', "[\n            \\Filament\\Forms\\Components\\FileUpload::make('logo')->label('Logo')->image()->disk('public')->directory('settings/brand')->visibility('public')->imageEditor()->maxSize(2048)->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml']),\n            \\Filament\\Forms\\Components\\FileUpload::make('dark_logo')->label('Dark Logo')->image()->disk('public')->directory('settings/brand')->visibility('public')->imageEditor()->maxSize(2048),\n            \\Filament\\Forms\\Components\\FileUpload::make('footer_logo')->label('Footer Logo')->image()->disk('public')->directory('settings/brand')->visibility('public')->imageEditor()->maxSize(2048),\n        ]"],
    ['Brand', 'ManageFavicon', 'BrandCluster', 'Favicon', 2, 'favicon', "[\n            \\Filament\\Forms\\Components\\FileUpload::make('favicon')->label('Favicon')->image()->disk('public')->directory('settings/brand')->visibility('public')->maxSize(1024)->acceptedFileTypes(['image/png', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml', 'image/webp']),\n        ]"],
    ['Brand', 'ManageBrandName', 'BrandCluster', 'Brand Name', 3, 'brand-name', "[\n            \\Filament\\Forms\\Components\\TextInput::make('company_name')->label('Brand Name')->required()->maxLength(255),\n        ]"],
    ['Brand', 'ManageTagline', 'BrandCluster', 'Tagline', 4, 'tagline', "[\n            \\Filament\\Forms\\Components\\TextInput::make('company_tagline')->label('Tagline')->maxLength(255),\n        ]"],
    ['Brand', 'ManageCompanyInformation', 'BrandCluster', 'Company Information', 5, 'company-information', '\\App\\Filament\\Resources\\Settings\\Schemas\\SettingForm::aboutFields()', '/', "use App\\Filament\\Resources\\Settings\\Schemas\\SettingForm;\n"],
    ['Brand', 'ManageBrandContactDetails', 'BrandCluster', 'Contact Details', 6, 'contact-details', '\\App\\Filament\\Resources\\Settings\\Schemas\\SettingForm::contactFields()', '/', "use App\\Filament\\Resources\\Settings\\Schemas\\SettingForm;\n"],
    ['Brand', 'ManageBrandSocialMedia', 'BrandCluster', 'Social Media', 7, 'social-media', '\\App\\Filament\\Resources\\Settings\\Schemas\\SettingForm::socialFields()', '/', "use App\\Filament\\Resources\\Settings\\Schemas\\SettingForm;\n"],

    // Header
    ['Header', 'ManageTopBar', 'HeaderCluster', 'Top Bar', 1, 'top-bar', "[\n            \\Filament\\Forms\\Components\\Toggle::make('header_enabled')->label('Show Header')->default(true)->inline(false),\n            \\Filament\\Forms\\Components\\Toggle::make('top_bar_enabled')->label('Show Top Bar')->default(true)->inline(false),\n            \\Filament\\Forms\\Components\\TextInput::make('top_bar_text')->label('Top Bar Text')->maxLength(255)->columnSpanFull(),\n        ]"],
    ['Header', 'ManageHeaderCta', 'HeaderCluster', 'CTA Button', 3, 'cta-button', "[\n            \\Filament\\Forms\\Components\\TextInput::make('header_cta_label')->label('Button Label')->maxLength(100),\n            \\Filament\\Forms\\Components\\TextInput::make('header_cta_url')->label('Button Link')->maxLength(255),\n        ]"],
    ['Header', 'ManageStickyHeader', 'HeaderCluster', 'Sticky Header', 4, 'sticky-header', "[\n            \\Filament\\Forms\\Components\\Toggle::make('sticky_header_enabled')->label('Keep Header Visible While Scrolling')->default(true)->inline(false),\n        ]"],
    ['Header', 'ManageMobileHeader', 'HeaderCluster', 'Mobile Header', 5, 'mobile-header', "[\n            \\Filament\\Forms\\Components\\Toggle::make('mobile_header_enabled')->label('Show Mobile Header')->default(true)->inline(false),\n            \\Filament\\Forms\\Components\\Select::make('mobile_menu_style')->label('Mobile Menu Style')->options(['drawer' => 'Side Drawer', 'fullscreen' => 'Full Screen'])->default('drawer')->native(false),\n        ]"],

    // Home
    ['Home', 'ManageHeroSearch', 'HomeCluster', 'Hero Search', 2, 'hero-search', "[\n            \\Filament\\Forms\\Components\\Toggle::make('hero_search_enabled')->label('Show Search on Homepage')->default(true)->inline(false),\n            \\Filament\\Forms\\Components\\TextInput::make('hero_search_placeholder')->label('Search Placeholder')->maxLength(255),\n            \\Filament\\Forms\\Components\\TextInput::make('hero_search_button_label')->label('Search Button Label')->maxLength(100),\n        ]"],

    // About
    ['About', 'ManageAboutCompany', 'AboutCluster', 'Company', 1, 'company', '\\App\\Filament\\Resources\\Settings\\Schemas\\SettingForm::aboutFields()', '/about', "use App\\Filament\\Resources\\Settings\\Schemas\\SettingForm;\n"],
    ['About', 'ManageAboutVision', 'AboutCluster', 'Vision', 3, 'vision', "[\n            \\Filament\\Forms\\Components\\Textarea::make('about_vision')->label('Vision')->rows(6)->columnSpanFull(),\n        ]", '/about'],
    ['About', 'ManageAboutMission', 'AboutCluster', 'Mission', 4, 'mission', "[\n            \\Filament\\Forms\\Components\\Textarea::make('about_mission')->label('Mission')->rows(6)->columnSpanFull(),\n        ]", '/about'],
    ['About', 'ManageAboutJourney', 'AboutCluster', 'Journey', 5, 'journey', "[\n            \\Filament\\Forms\\Components\\Textarea::make('about_journey')->label('Our Journey')->rows(8)->columnSpanFull(),\n        ]", '/about'],

    // Contact
    ['Contact', 'ManageContactGoogleMap', 'ContactCluster', 'Google Map', 3, 'google-map', "[\n            \\Filament\\Forms\\Components\\Textarea::make('google_map_embed')->label('Google Map Embed Code')->rows(6)->helperText('Paste the Google Maps embed iframe code.')->columnSpanFull(),\n        ]", '/contact'],
    ['Contact', 'ManageContactBusinessHours', 'ContactCluster', 'Business Hours', 4, 'business-hours', '\\App\\Filament\\Resources\\Settings\\Schemas\\SettingForm::businessFields()', '/contact', "use App\\Filament\\Resources\\Settings\\Schemas\\SettingForm;\n"],
    ['Contact', 'ManageContactFormSettings', 'ContactCluster', 'Contact Form', 2, 'contact-form', "[\n            \\Filament\\Forms\\Components\\Placeholder::make('contact_form_help')->label('How enquiries work')->content('Website visitors submit the contact form on the Contact page. New messages appear under CRM → Contact Enquiries so your team can follow up.'),\n        ]", '/contact'],

    // Footer
    ['Footer', 'ManageFooterCopyright', 'FooterCluster', 'Copyright', 2, 'copyright', "[\n            \\Filament\\Forms\\Components\\Toggle::make('footer_enabled')->label('Show Footer')->default(true)->inline(false),\n            \\Filament\\Forms\\Components\\Textarea::make('footer_about')->label('Footer About Text')->rows(3),\n            \\Filament\\Forms\\Components\\TextInput::make('copyright_text')->label('Copyright Line')->maxLength(255),\n        ]"],
    ['Footer', 'ManageFooterNewsletter', 'FooterCluster', 'Newsletter', 3, 'newsletter', "[\n            \\Filament\\Forms\\Components\\Toggle::make('footer_newsletter_enabled')->label('Show Newsletter Signup')->default(true)->inline(false),\n            \\Filament\\Forms\\Components\\Placeholder::make('newsletter_help')->label('Subscribers')->content('People who subscribe are saved under CRM → Newsletter.'),\n        ]"],
    ['Footer', 'ManageFooterSocialLinks', 'FooterCluster', 'Social Links', 4, 'social-links', "[\n            \\Filament\\Forms\\Components\\Toggle::make('footer_social_enabled')->label('Show Social Icons in Footer')->default(true)->inline(false),\n            \\Filament\\Forms\\Components\\Placeholder::make('social_help')->label('Profile links')->content('Update Facebook, Instagram, YouTube and other profile links under Brand → Social Media.'),\n        ]"],
    ['Footer', 'ManageFooterMenu', 'FooterCluster', 'Footer Menu', 1, 'footer-menu', "[\n            \\Filament\\Forms\\Components\\Placeholder::make('footer_menu_help')->label('Footer links')->content('Manage footer links in Header → Navigation. Turn on “Show in Footer” for each link you want displayed here.'),\n        ]"],

    // SEO
    ['Seo', 'ManageHomepageSeo', 'SeoCluster', 'Homepage SEO', 1, 'homepage-seo', "[\n            \\Filament\\Forms\\Components\\TextInput::make('homepage_seo_title')->label('Homepage Title')->maxLength(255),\n            \\Filament\\Forms\\Components\\Textarea::make('homepage_seo_description')->label('Homepage Description')->rows(3),\n            \\Filament\\Forms\\Components\\Textarea::make('homepage_seo_keywords')->label('Homepage Keywords')->rows(2),\n        ]"],
    ['Seo', 'ManageGlobalSeo', 'SeoCluster', 'Global SEO', 2, 'global-seo', '\\App\\Filament\\Resources\\Settings\\Schemas\\SettingForm::seoFields()', '/', "use App\\Filament\\Resources\\Settings\\Schemas\\SettingForm;\n"],
    ['Seo', 'ManageSitemapSettings', 'SeoCluster', 'Sitemap', 3, 'sitemap', "[\n            \\Filament\\Forms\\Components\\Toggle::make('sitemap_enabled')->label('Publish Sitemap')->default(true)->inline(false),\n            \\Filament\\Forms\\Components\\Placeholder::make('sitemap_url')->label('Public address')->content(fn () => rtrim((string) (config('seo.site_url') ?: config('app.url')), '/').'/sitemap.xml'),\n        ]"],
    ['Seo', 'ManageRobotsSettings', 'SeoCluster', 'Robots', 4, 'robots', "[\n            \\Filament\\Forms\\Components\\TextInput::make('robots')->label('Default Robots Meta')->maxLength(255)->helperText('Example: index, follow'),\n            \\Filament\\Forms\\Components\\Textarea::make('robots_txt_extra')->label('Extra robots.txt Rules')->rows(6)->helperText('Optional lines appended to robots.txt'),\n        ]"],
];

foreach ($pages as $page) {
    [$ns, $class, $cluster, $label, $sort, $slug, $fields, $preview, $uses] = array_pad($page, 9, null);
    $preview ??= '/';
    $uses ??= '';
    $path = "{$base}/app/Filament/Pages/{$ns}/{$class}.php";
    write_file($path, setting_page($ns, $class, $cluster, $label, $sort, $slug, $fields, $preview, $uses));
}

// Instructional home featured pages
$featured = [
    ['ManageFeaturedServicesSection', 'Featured Services', 3, 'featured-services', 'Open Services → Services and turn on “Show on Homepage” for the services you want featured.'],
    ['ManageFeaturedGallerySection', 'Featured Gallery', 5, 'featured-gallery', 'Open Gallery → Images and turn on “Show on Homepage” for images you want on the homepage gallery.'],
    ['ManageFeaturedClientStoriesSection', 'Featured Client Stories', 6, 'featured-client-stories', 'Open Our Client Stories → Stories / Reviews and turn on “Show on Homepage” for entries you want featured.'],
    ['ManageFeaturedBlogsSection', 'Featured Blogs', 7, 'featured-blogs', 'Open Blog → Articles and turn on “Show on Homepage” for posts you want in Latest News.'],
];

foreach ($featured as [$class, $label, $sort, $slug, $help]) {
    $code = <<<PHP
<?php

namespace App\\Filament\\Pages\\Home;

use App\\Filament\\Clusters\\HomeCluster;
use App\\Filament\\Pages\\EditWebsiteSettingPage;
use Filament\\Forms\\Components\\Placeholder;

class {$class} extends EditWebsiteSettingPage
{
    protected static bool \$isDiscovered = true;

    protected static ?string \$cluster = HomeCluster::class;

    protected static ?string \$navigationLabel = '{$label}';

    protected static ?string \$title = '{$label}';

    protected static ?int \$navigationSort = {$sort};

    protected static ?string \$slug = '{$slug}';

    protected function formFields(): array
    {
        return [
            Placeholder::make('help')
                ->label('How to manage this section')
                ->content('{$help}'),
        ];
    }
}

PHP;
    write_file("{$base}/app/Filament/Pages/Home/{$class}.php", $code);
}

// Services pricing helper
write_file("{$base}/app/Filament/Pages/Services/ManageServicePricing.php", <<<'PHP'
<?php

namespace App\Filament\Pages\Services;

use App\Filament\Clusters\ServicesCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use Filament\Forms\Components\Placeholder;

class ManageServicePricing extends EditWebsiteSettingPage
{
    protected static bool $isDiscovered = true;

    protected static ?string $cluster = ServicesCluster::class;

    protected static ?string $navigationLabel = 'Pricing';

    protected static ?string $title = 'Pricing';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'pricing';

    protected function formFields(): array
    {
        return [
            Placeholder::make('help')
                ->label('How pricing works')
                ->content('Create and edit package prices under Services → Packages. Each package can show a price label such as “Starting ₹49,999”.'),
        ];
    }
}

PHP);

// Blog tags helper
write_file("{$base}/app/Filament/Pages/Blog/ManageBlogTags.php", <<<'PHP'
<?php

namespace App\Filament\Pages\Blog;

use App\Filament\Clusters\BlogCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use Filament\Forms\Components\Placeholder;

class ManageBlogTags extends EditWebsiteSettingPage
{
    protected static bool $isDiscovered = true;

    protected static ?string $cluster = BlogCluster::class;

    protected static ?string $navigationLabel = 'Tags';

    protected static ?string $title = 'Tags';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'tags';

    protected function formFields(): array
    {
        return [
            Placeholder::make('help')
                ->label('How tags work')
                ->content('Add tags while editing each article under Blog → Articles. Tags help visitors find related wedding and event stories.'),
        ];
    }
}

PHP);

// Client stories ratings helper
write_file("{$base}/app/Filament/Pages/ClientStories/ManageClientRatings.php", <<<'PHP'
<?php

namespace App\Filament\Pages\ClientStories;

use App\Filament\Clusters\ClientStoriesCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use Filament\Forms\Components\Placeholder;

class ManageClientRatings extends EditWebsiteSettingPage
{
    protected static bool $isDiscovered = true;

    protected static ?string $cluster = ClientStoriesCluster::class;

    protected static ?string $navigationLabel = 'Ratings';

    protected static ?string $title = 'Ratings';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'ratings';

    protected function formFields(): array
    {
        return [
            Placeholder::make('help')
                ->label('How ratings work')
                ->content('Set a star rating (1–5) when editing a review under Our Client Stories → Reviews. Ratings appear with Google-style client feedback.'),
        ];
    }
}

PHP);

echo "Pages scaffolded.\n";
