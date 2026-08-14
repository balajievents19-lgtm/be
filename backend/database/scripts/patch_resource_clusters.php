<?php

/**
 * Patch existing Filament resources to Website clusters.
 * Run: php database/scripts/patch_resource_clusters.php
 */
$base = dirname(__DIR__, 2);

$patches = [
    'app/Filament/Resources/HeroSlides/HeroSlideResource.php' => [
        'use' => 'use App\\Filament\\Clusters\\HomeCluster;',
        'cluster' => 'HomeCluster',
        'sort' => 1,
        'label' => null,
    ],
    'app/Filament/Resources/NavigationItems/NavigationItemResource.php' => [
        'use' => 'use App\\Filament\\Clusters\\HeaderCluster;',
        'cluster' => 'HeaderCluster',
        'sort' => 2,
        'label' => 'Navigation',
    ],
    'app/Filament/Resources/Services/ServiceResource.php' => [
        'use' => 'use App\\Filament\\Clusters\\ServicesCluster;',
        'cluster' => 'ServicesCluster',
        'sort' => 2,
        'label' => null,
    ],
    'app/Filament/Resources/Faqs/FaqResource.php' => [
        'use' => 'use App\\Filament\\Clusters\\ServicesCluster;',
        'cluster' => 'ServicesCluster',
        'sort' => 5,
        'label' => null,
    ],
    'app/Filament/Resources/GalleryItems/GalleryItemResource.php' => [
        'use' => 'use App\\Filament\\Clusters\\GalleryCluster;',
        'cluster' => 'GalleryCluster',
        'sort' => 2,
        'label' => 'Images',
        'modelLabel' => 'Image',
        'plural' => 'Images',
    ],
    'app/Filament/Resources/GalleryCategories/GalleryCategoryResource.php' => [
        'use' => 'use App\\Filament\\Clusters\\GalleryCluster;',
        'cluster' => 'GalleryCluster',
        'sort' => 1,
        'label' => 'Albums',
        'modelLabel' => 'Album',
        'plural' => 'Albums',
        'showNav' => true,
    ],
    'app/Filament/Resources/Testimonials/TestimonialResource.php' => [
        'use' => 'use App\\Filament\\Clusters\\ClientStoriesCluster;',
        'cluster' => 'ClientStoriesCluster',
        'sort' => 1,
        'label' => 'Stories',
        'modelLabel' => 'Story',
        'plural' => 'Stories',
    ],
    'app/Filament/Resources/BlogPosts/BlogPostResource.php' => [
        'use' => 'use App\\Filament\\Clusters\\BlogCluster;',
        'cluster' => 'BlogCluster',
        'sort' => 2,
        'label' => 'Articles',
        'modelLabel' => 'Article',
        'plural' => 'Articles',
    ],
    'app/Filament/Resources/BlogCategories/BlogCategoryResource.php' => [
        'use' => 'use App\\Filament\\Clusters\\BlogCluster;',
        'cluster' => 'BlogCluster',
        'sort' => 1,
        'label' => 'Categories',
        'showNav' => true,
    ],
    'app/Filament/Resources/EventOverviews/EventOverviewResource.php' => [
        'use' => 'use App\\Filament\\Clusters\\HomeCluster;',
        'cluster' => 'HomeCluster',
        'sort' => 5,
        'label' => 'Events Overview',
        'showNav' => true,
    ],
];

foreach ($patches as $rel => $cfg) {
    $path = $base.'/'.$rel;
    $code = file_get_contents($path);

    if (! str_contains($code, $cfg['use'])) {
        $code = preg_replace('/^namespace .+?;\n\n/m', "$0{$cfg['use']}\n", $code, 1);
    }

    if (! str_contains($code, 'protected static ?string $cluster')) {
        $code = preg_replace(
            '/protected static \?string \$model = .+?;\n/',
            "$0\n    protected static ?string \$cluster = {$cfg['cluster']}::class;\n",
            $code,
            1
        );
    }

    $code = preg_replace(
        '/protected static string\|UnitEnum\|null \$navigationGroup = .*?;/',
        'protected static string|UnitEnum|null $navigationGroup = null;',
        $code
    );

    $code = preg_replace(
        '/protected static \?int \$navigationSort = \d+;/',
        'protected static ?int $navigationSort = '.$cfg['sort'].';',
        $code
    );

    if (! empty($cfg['label'])) {
        $code = preg_replace(
            '/protected static \?string \$navigationLabel = \'.*?\';/',
            "protected static ?string \$navigationLabel = '{$cfg['label']}';",
            $code
        );
    }

    if (! empty($cfg['modelLabel'])) {
        $code = preg_replace(
            '/protected static \?string \$modelLabel = \'.*?\';/',
            "protected static ?string \$modelLabel = '{$cfg['modelLabel']}';",
            $code
        );
    }

    if (! empty($cfg['plural'])) {
        $code = preg_replace(
            '/protected static \?string \$pluralModelLabel = \'.*?\';/',
            "protected static ?string \$pluralModelLabel = '{$cfg['plural']}';",
            $code
        );
    }

    if (! empty($cfg['showNav'])) {
        $code = preg_replace(
            '/protected static bool \$shouldRegisterNavigation = false;/',
            'protected static bool $shouldRegisterNavigation = true;',
            $code
        );
        if (! str_contains($code, 'shouldRegisterNavigation')) {
            $code = preg_replace(
                '/protected static \?int \$navigationSort = \d+;/',
                "$0\n\n    protected static bool \$shouldRegisterNavigation = true;",
                $code,
                1
            );
        }
    }

    file_put_contents($path, $code);
    echo "Patched {$rel}\n";
}

echo "Done.\n";
