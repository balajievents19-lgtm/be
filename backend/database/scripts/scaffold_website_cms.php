<?php

/**
 * One-shot scaffold for Website CMS Filament clusters/pages.
 * Run: php database/scripts/scaffold_website_cms.php
 */
$base = dirname(__DIR__, 2);

$clusters = [
    ['BrandCluster', 'Brand', 1, 'OutlinedSparkles', 'brand'],
    ['HeaderCluster', 'Header', 2, 'OutlinedBars3CenterLeft', 'header'],
    ['HomeCluster', 'Home', 3, 'OutlinedHome', 'home'],
    ['AboutCluster', 'About', 4, 'OutlinedInformationCircle', 'about'],
    ['ServicesCluster', 'Services', 5, 'OutlinedBriefcase', 'services'],
    ['GalleryCluster', 'Gallery', 6, 'OutlinedSquares2x2', 'gallery'],
    ['ClientStoriesCluster', 'Our Client Stories', 7, 'OutlinedChatBubbleLeftRight', 'client-stories'],
    ['BlogCluster', 'Blog', 8, 'OutlinedNewspaper', 'blog'],
    ['ContactCluster', 'Contact', 9, 'OutlinedPhone', 'contact'],
    ['FooterCluster', 'Footer', 10, 'OutlinedBars3BottomLeft', 'footer'],
    ['SeoCluster', 'SEO', 11, 'OutlinedMagnifyingGlassCircle', 'seo'],
];

foreach ($clusters as [$class, $label, $sort, $icon, $slug]) {
    $path = "{$base}/app/Filament/Clusters/{$class}.php";
    $code = <<<PHP
<?php

namespace App\Filament\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class {$class} extends Cluster
{
    protected static string|BackedEnum|null \$navigationIcon = Heroicon::{$icon};

    protected static string|UnitEnum|null \$navigationGroup = 'Website';

    protected static ?int \$navigationSort = {$sort};

    protected static ?string \$navigationLabel = '{$label}';

    protected static ?string \$slug = '{$slug}';
}

PHP;
    file_put_contents($path, $code);
    echo "Wrote {$class}\n";
}

echo "Clusters done.\n";
