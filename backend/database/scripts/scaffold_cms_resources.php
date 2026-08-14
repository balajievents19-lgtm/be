<?php

$base = dirname(__DIR__, 2);

function w(string $path, string $contents): void
{
    $dir = dirname($path);
    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($path, $contents);
    echo basename(dirname($path)).'/'.basename($path)."\n";
}

function policy(string $model): string
{
    $var = lcfirst($model);

    return <<<PHP
<?php

namespace App\\Policies;

use App\\Models\\{$model};
use App\\Models\\User;

class {$model}Policy
{
    public function viewAny(User \$user): bool
    {
        return true;
    }

    public function view(User \$user, {$model} \${$var}): bool
    {
        return true;
    }

    public function create(User \$user): bool
    {
        return true;
    }

    public function update(User \$user, {$model} \${$var}): bool
    {
        return true;
    }

    public function delete(User \$user, {$model} \${$var}): bool
    {
        return true;
    }
}

PHP;
}

$resources = [
    [
        'dir' => 'TeamMembers',
        'model' => 'TeamMember',
        'cluster' => 'AboutCluster',
        'label' => 'Team',
        'singular' => 'Team Member',
        'plural' => 'Team',
        'icon' => 'OutlinedUserGroup',
        'sort' => 2,
        'titleAttr' => 'name',
        'form' => <<<'PHP'
use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class TeamMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Team Member')->columnSpanFull()->tabs([
                Tab::make('General')->schema([
                    Section::make()->columns(2)->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('role')->label('Role / Title')->maxLength(255),
                        Textarea::make('bio')->rows(4)->columnSpanFull(),
                        TextInput::make('email')->email()->maxLength(255),
                        TextInput::make('phone')->maxLength(50),
                        TextInput::make('social_linkedin')->label('LinkedIn')->url()->maxLength(255),
                        TextInput::make('social_instagram')->label('Instagram')->url()->maxLength(255),
                    ]),
                ]),
                Tab::make('Media')->schema([
                    Section::make()->schema([
                        FileUpload::make('photo')->image()->disk('public')->directory('team')->visibility('public')->imageEditor()->maxSize(4096),
                    ]),
                ]),
                Tab::make('SEO')->schema([
                    Section::make()->schema([
                        TextInput::make('seo_title')->label('SEO Title')->maxLength(255),
                        Textarea::make('seo_description')->label('SEO Description')->rows(3),
                    ]),
                ]),
                Tab::make('Publish')->schema([
                    Section::make()->columns(2)->schema([
                        ...WebsitePublishFields::statusAndSort(),
                        Toggle::make('is_visible')->label('Visible')->default(true)->inline(false),
                        ...WebsitePublishFields::schedule(),
                    ]),
                    Section::make('History')->columns(2)->schema(WebsitePublishFields::audit())->collapsed(),
                ]),
            ]),
        ]);
    }
}
PHP,
        'table' => "TextColumn::make('name')->searchable()->sortable();\n                TextColumn::make('role')->label('Role');\n                IconColumn::make('status')->boolean();\n                TextColumn::make('sort_order')->label('Order')->sortable();",
    ],
    [
        'dir' => 'Statistics',
        'model' => 'Statistic',
        'cluster' => 'HomeCluster',
        'label' => 'Statistics',
        'singular' => 'Statistic',
        'plural' => 'Statistics',
        'icon' => 'OutlinedChartBar',
        'sort' => 4,
        'titleAttr' => 'label',
        'form' => <<<'PHP'
use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class StatisticForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Statistic')->columnSpanFull()->tabs([
                Tab::make('General')->schema([
                    Section::make()->columns(2)->schema([
                        TextInput::make('label')->required()->maxLength(255),
                        TextInput::make('value')->required()->maxLength(100),
                        TextInput::make('suffix')->maxLength(50)->helperText('Example: +, %'),
                        TextInput::make('icon')->maxLength(100),
                    ]),
                ]),
                Tab::make('Publish')->schema([
                    Section::make()->columns(2)->schema([
                        ...WebsitePublishFields::statusAndSort(withHomepage: true, homepageField: 'show_on_homepage'),
                        Toggle::make('is_visible')->label('Visible')->default(true)->inline(false),
                        ...WebsitePublishFields::schedule(),
                    ]),
                    Section::make('History')->columns(2)->schema(WebsitePublishFields::audit())->collapsed(),
                ]),
            ]),
        ]);
    }
}
PHP,
        'table' => "TextColumn::make('label')->searchable()->sortable();\n                TextColumn::make('value');\n                IconColumn::make('show_on_homepage')->label('Homepage')->boolean();\n                IconColumn::make('status')->boolean();",
    ],
    [
        'dir' => 'CtaSections',
        'model' => 'CtaSection',
        'cluster' => 'HomeCluster',
        'label' => 'CTA Sections',
        'singular' => 'CTA Section',
        'plural' => 'CTA Sections',
        'icon' => 'OutlinedMegaphone',
        'sort' => 8,
        'titleAttr' => 'title',
        'form' => <<<'PHP'
use App\Enums\CtaSectionKey;
use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class CtaSectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('CTA')->columnSpanFull()->tabs([
                Tab::make('General')->schema([
                    Section::make()->columns(2)->schema([
                        Select::make('key')->label('Section')->options(CtaSectionKey::options())->required()->native(false),
                        TextInput::make('title')->required()->maxLength(255),
                        TextInput::make('subtitle')->maxLength(255)->columnSpanFull(),
                        Textarea::make('body')->rows(3)->columnSpanFull(),
                        TextInput::make('button_text')->label('Button Label')->maxLength(100),
                        TextInput::make('button_url')->label('Button Link')->maxLength(255),
                        TextInput::make('secondary_button_text')->label('Second Button Label')->maxLength(100),
                        TextInput::make('secondary_button_url')->label('Second Button Link')->maxLength(255),
                    ]),
                ]),
                Tab::make('Media')->schema([
                    Section::make()->schema([
                        FileUpload::make('background_image')->label('Background Image')->image()->disk('public')->directory('cta')->visibility('public')->imageEditor()->maxSize(5120),
                    ]),
                ]),
                Tab::make('SEO')->schema([
                    Section::make()->schema([
                        TextInput::make('seo_title')->label('SEO Title')->maxLength(255),
                        Textarea::make('seo_description')->label('SEO Description')->rows(3),
                    ]),
                ]),
                Tab::make('Publish')->schema([
                    Section::make()->columns(2)->schema([
                        ...WebsitePublishFields::statusAndSort(withHomepage: true, homepageField: 'show_on_homepage'),
                        Toggle::make('is_visible')->label('Visible')->default(true)->inline(false),
                        ...WebsitePublishFields::schedule(),
                    ]),
                    Section::make('History')->columns(2)->schema(WebsitePublishFields::audit())->collapsed(),
                ]),
            ]),
        ]);
    }
}
PHP,
        'table' => "TextColumn::make('title')->searchable();\n                TextColumn::make('key')->badge();\n                IconColumn::make('status')->boolean();",
    ],
    [
        'dir' => 'OfficeLocations',
        'model' => 'OfficeLocation',
        'cluster' => 'ContactCluster',
        'label' => 'Office Locations',
        'singular' => 'Office Location',
        'plural' => 'Office Locations',
        'icon' => 'OutlinedMapPin',
        'sort' => 1,
        'titleAttr' => 'name',
        'form' => <<<'PHP'
use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class OfficeLocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Office')->columnSpanFull()->tabs([
                Tab::make('General')->schema([
                    Section::make()->columns(2)->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        Toggle::make('is_primary')->label('Primary Office')->default(false)->inline(false),
                        Textarea::make('address')->required()->rows(3)->columnSpanFull(),
                        TextInput::make('city')->maxLength(100),
                        TextInput::make('state')->maxLength(100),
                        TextInput::make('pincode')->label('PIN Code')->maxLength(20),
                        TextInput::make('phone')->maxLength(50),
                        TextInput::make('email')->email()->maxLength(255),
                        Textarea::make('map_embed')->label('Google Map Embed')->rows(4)->columnSpanFull(),
                        TextInput::make('latitude')->maxLength(50),
                        TextInput::make('longitude')->maxLength(50),
                    ]),
                ]),
                Tab::make('Publish')->schema([
                    Section::make()->columns(2)->schema([
                        ...WebsitePublishFields::statusAndSort(),
                        Toggle::make('is_visible')->label('Visible')->default(true)->inline(false),
                        ...WebsitePublishFields::schedule(),
                    ]),
                    Section::make('History')->columns(2)->schema(WebsitePublishFields::audit())->collapsed(),
                ]),
            ]),
        ]);
    }
}
PHP,
        'table' => "TextColumn::make('name')->searchable();\n                TextColumn::make('city');\n                IconColumn::make('is_primary')->label('Primary')->boolean();\n                IconColumn::make('status')->boolean();",
    ],
    [
        'dir' => 'ServiceCategories',
        'model' => 'ServiceCategory',
        'cluster' => 'ServicesCluster',
        'label' => 'Categories',
        'singular' => 'Category',
        'plural' => 'Categories',
        'icon' => 'OutlinedTag',
        'sort' => 1,
        'titleAttr' => 'name',
        'form' => <<<'PHP'
use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Category')->columnSpanFull()->tabs([
                Tab::make('General')->schema([
                    Section::make()->columns(2)->schema([
                        TextInput::make('name')->required()->maxLength(255)->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set, callable $get): void {
                                if (filled($get('slug'))) {
                                    return;
                                }
                                $set('slug', Str::slug((string) $state));
                            }),
                        TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true)->alphaDash(),
                        Textarea::make('description')->rows(3)->columnSpanFull(),
                        TextInput::make('icon')->maxLength(100),
                    ]),
                ]),
                Tab::make('Media')->schema([
                    Section::make()->columns(2)->schema([
                        FileUpload::make('image')->image()->disk('public')->directory('service-categories')->visibility('public')->imageEditor()->maxSize(4096),
                        FileUpload::make('opengraph_image')->label('Social Image')->image()->disk('public')->directory('service-categories/seo')->visibility('public')->maxSize(2048),
                    ]),
                ]),
                Tab::make('SEO')->schema([
                    Section::make()->schema([
                        TextInput::make('seo_title')->label('SEO Title')->maxLength(255),
                        Textarea::make('seo_description')->label('SEO Description')->rows(3),
                    ]),
                ]),
                Tab::make('Publish')->schema([
                    Section::make()->columns(2)->schema([
                        ...WebsitePublishFields::statusAndSort(),
                        Toggle::make('is_visible')->label('Visible')->default(true)->inline(false),
                        ...WebsitePublishFields::schedule(),
                    ]),
                    Section::make('History')->columns(2)->schema(WebsitePublishFields::audit())->collapsed(),
                ]),
            ]),
        ]);
    }
}
PHP,
        'table' => "TextColumn::make('name')->searchable()->sortable();\n                TextColumn::make('slug');\n                IconColumn::make('status')->boolean();",
    ],
    [
        'dir' => 'ServicePackages',
        'model' => 'ServicePackage',
        'cluster' => 'ServicesCluster',
        'label' => 'Packages',
        'singular' => 'Package',
        'plural' => 'Packages',
        'icon' => 'OutlinedCube',
        'sort' => 3,
        'titleAttr' => 'name',
        'form' => <<<'PHP'
use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServicePackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Package')->columnSpanFull()->tabs([
                Tab::make('General')->schema([
                    Section::make()->columns(2)->schema([
                        TextInput::make('name')->required()->maxLength(255)->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set, callable $get): void {
                                if (filled($get('slug'))) {
                                    return;
                                }
                                $set('slug', Str::slug((string) $state));
                            }),
                        TextInput::make('slug')->required()->unique(ignoreRecord: true)->alphaDash(),
                        Select::make('service_id')->label('Related Service')->relationship('service', 'name')->searchable()->preload()->native(false),
                        Select::make('service_category_id')->label('Category')->relationship('category', 'name')->searchable()->preload()->native(false),
                        TextInput::make('summary')->maxLength(255)->columnSpanFull(),
                        Textarea::make('description')->rows(4)->columnSpanFull(),
                        TextInput::make('price_label')->label('Price Label')->helperText('Example: Starting ₹49,999')->maxLength(100),
                        TextInput::make('price_amount')->label('Price Amount')->numeric(),
                        TextInput::make('currency')->default('INR')->maxLength(10),
                        TagsInput::make('features')->placeholder('Add feature')->columnSpanFull(),
                        Toggle::make('is_featured')->label('Featured Package')->inline(false),
                    ]),
                ]),
                Tab::make('Media')->schema([
                    Section::make()->schema([
                        FileUpload::make('image')->image()->disk('public')->directory('service-packages')->visibility('public')->imageEditor()->maxSize(4096),
                    ]),
                ]),
                Tab::make('SEO')->schema([
                    Section::make()->schema([
                        TextInput::make('seo_title')->label('SEO Title')->maxLength(255),
                        Textarea::make('seo_description')->label('SEO Description')->rows(3),
                    ]),
                ]),
                Tab::make('Publish')->schema([
                    Section::make()->columns(2)->schema([
                        ...WebsitePublishFields::statusAndSort(),
                        Toggle::make('is_visible')->label('Visible')->default(true)->inline(false),
                        ...WebsitePublishFields::schedule(),
                    ]),
                    Section::make('History')->columns(2)->schema(WebsitePublishFields::audit())->collapsed(),
                ]),
            ]),
        ]);
    }
}
PHP,
        'table' => "TextColumn::make('name')->searchable();\n                TextColumn::make('price_label')->label('Price');\n                IconColumn::make('is_featured')->label('Featured')->boolean();\n                IconColumn::make('status')->boolean();",
    ],
    [
        'dir' => 'Redirects',
        'model' => 'Redirect',
        'cluster' => 'SeoCluster',
        'label' => 'Redirects',
        'singular' => 'Redirect',
        'plural' => 'Redirects',
        'icon' => 'OutlinedArrowsRightLeft',
        'sort' => 5,
        'titleAttr' => 'from_path',
        'form' => <<<'PHP'
use App\Enums\RedirectStatusCode;
use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class RedirectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Redirect')->columnSpanFull()->tabs([
                Tab::make('General')->schema([
                    Section::make()->columns(2)->schema([
                        TextInput::make('from_path')->label('From Path')->required()->maxLength(255)->helperText('Example: /old-page'),
                        TextInput::make('to_url')->label('Send Visitors To')->required()->maxLength(500),
                        Select::make('status_code')->label('Redirect Type')->options(RedirectStatusCode::options())->default(301)->required()->native(false),
                        Textarea::make('notes')->label('Internal Notes')->rows(3)->columnSpanFull(),
                    ]),
                ]),
                Tab::make('Publish')->schema([
                    Section::make()->columns(2)->schema([
                        ...WebsitePublishFields::statusAndSort(),
                        Toggle::make('is_visible')->label('Visible')->default(true)->inline(false),
                        ...WebsitePublishFields::schedule(),
                    ]),
                    Section::make('History')->columns(2)->schema(WebsitePublishFields::audit())->collapsed(),
                ]),
            ]),
        ]);
    }
}
PHP,
        'table' => "TextColumn::make('from_path')->label('From')->searchable();\n                TextColumn::make('to_url')->label('To')->limit(40);\n                TextColumn::make('status_code')->label('Type');\n                IconColumn::make('status')->boolean();",
    ],
];

foreach ($resources as $r) {
    $ns = "App\\Filament\\Resources\\{$r['dir']}";
    $resourceClass = $r['model'].'Resource';
    if ($r['model'] === 'CtaSection') {
        $resourceClass = 'CtaSectionResource';
    }

    w("{$base}/app/Policies/{$r['model']}Policy.php", policy($r['model']));

    w("{$base}/app/Filament/Resources/{$r['dir']}/Schemas/{$r['model']}Form.php", "<?php\n\nnamespace {$ns}\\Schemas;\n\n{$r['form']}\n");

    $tableClass = $r['model'].'sTable';
    if ($r['model'] === 'ServiceCategory') {
        $tableClass = 'ServiceCategoriesTable';
    }
    if ($r['model'] === 'Statistic') {
        $tableClass = 'StatisticsTable';
    }

    w("{$base}/app/Filament/Resources/{$r['dir']}/Tables/{$tableClass}.php", <<<PHP
<?php

namespace {$ns}\\Tables;

use Filament\\Actions\\BulkActionGroup;
use Filament\\Actions\\DeleteBulkAction;
use Filament\\Actions\\EditAction;
use Filament\\Tables\\Columns\\IconColumn;
use Filament\\Tables\\Columns\\TextColumn;
use Filament\\Tables\\Table;

class {$tableClass}
{
    public static function configure(Table \$table): Table
    {
        return \$table
            ->columns([
                {$r['table']}
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

PHP);

    foreach (['List' => 'ListRecords', 'Create' => 'CreateRecord', 'Edit' => 'EditRecord'] as $prefix => $baseClass) {
        $pageClass = $prefix.$r['model'];
        if ($prefix === 'List') {
            $pageClass = 'List'.$r['dir'];
        }
        $extra = $prefix === 'Edit' ? "\n\n    protected function getHeaderActions(): array\n    {\n        return [\n            \\Filament\\Actions\\DeleteAction::make(),\n        ];\n    }" : '';
        $actions = $prefix === 'List' ? "\n\n    protected function getHeaderActions(): array\n    {\n        return [\n            \\Filament\\Actions\\CreateAction::make(),\n        ];\n    }" : '';

        w("{$base}/app/Filament/Resources/{$r['dir']}/Pages/{$pageClass}.php", <<<PHP
<?php

namespace {$ns}\\Pages;

use {$ns}\\{$resourceClass};
use Filament\\Resources\\Pages\\{$baseClass};

class {$pageClass} extends {$baseClass}
{
    protected static string \$resource = {$resourceClass}::class;{$actions}{$extra}
}

PHP);
    }

    $listPage = 'List'.$r['dir'];
    $createPage = 'Create'.$r['model'];
    $editPage = 'Edit'.$r['model'];

    w("{$base}/app/Filament/Resources/{$r['dir']}/{$resourceClass}.php", <<<PHP
<?php

namespace {$ns};

use App\\Filament\\Clusters\\{$r['cluster']};
use {$ns}\\Pages\\{$createPage};
use {$ns}\\Pages\\{$editPage};
use {$ns}\\Pages\\{$listPage};
use {$ns}\\Schemas\\{$r['model']}Form;
use {$ns}\\Tables\\{$tableClass};
use App\\Models\\{$r['model']};
use BackedEnum;
use Filament\\Resources\\Resource;
use Filament\\Schemas\\Schema;
use Filament\\Support\\Icons\\Heroicon;
use Filament\\Tables\\Table;
use UnitEnum;

class {$resourceClass} extends Resource
{
    protected static ?string \$model = {$r['model']}::class;

    protected static ?string \$cluster = {$r['cluster']}::class;

    protected static ?string \$navigationLabel = '{$r['label']}';

    protected static ?string \$modelLabel = '{$r['singular']}';

    protected static ?string \$pluralModelLabel = '{$r['plural']}';

    protected static string|UnitEnum|null \$navigationGroup = null;

    protected static ?int \$navigationSort = {$r['sort']};

    protected static string|BackedEnum|null \$navigationIcon = Heroicon::{$r['icon']};

    protected static ?string \$recordTitleAttribute = '{$r['titleAttr']}';

    public static function form(Schema \$schema): Schema
    {
        return {$r['model']}Form::configure(\$schema);
    }

    public static function table(Table \$table): Table
    {
        return {$tableClass}::configure(\$table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => {$listPage}::route('/'),
            'create' => {$createPage}::route('/create'),
            'edit' => {$editPage}::route('/{record}/edit'),
        ];
    }
}

PHP);
}

echo "Resources generated.\n";
