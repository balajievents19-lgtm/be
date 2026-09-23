<?php

namespace Tests\Feature;

use App\Filament\Pages\About\ManageAboutCompany;
use App\Models\Setting;
use App\Models\User;
use App\Support\Rbac\AdminModules;
use Database\Seeders\RBACSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAboutImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RBACSeeder::class);
        config(['app.url' => 'https://www.balajiroyalevents.com']);
        config(['auth.admin_emails' => 'super.about@balaji.test']);
    }

    public function test_public_disk_urls_are_origin_relative(): void
    {
        $this->assertSame('/storage', config('filesystems.disks.public.url'));
        $this->assertSame('/storage/settings/about/photo.png', Storage::disk('public')->url('settings/about/photo.png'));
    }

    public function test_livewire_temporary_uploads_use_the_private_local_disk(): void
    {
        $this->assertSame('local', config('livewire.temporary_file_upload.disk'));
    }

    public function test_admin_livewire_urls_match_the_request_host_not_app_url(): void
    {
        $request = \Illuminate\Http\Request::create(
            'https://balajiroyalevents.com/livewire/upload-file',
            'POST'
        );

        $response = (new \App\Http\Middleware\AlignAdminAssetOrigin)->handle(
            $request,
            function () {
                $this->assertSame(
                    'https://balajiroyalevents.com/livewire/preview-file/demo',
                    url('/livewire/preview-file/demo')
                );

                return response('ok');
            }
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_about_image_upload_saves_and_is_readable_from_public_storage(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'email' => 'super.about@balaji.test',
        ]);
        $admin->assignRole(AdminModules::ROLE_SUPER_ADMIN);

        $file = UploadedFile::fake()->image('about.png', 120, 80);

        $this->actingAs($admin);

        Livewire::test(ManageAboutCompany::class)
            ->fillForm([
                'about_image' => [$file],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $path = Setting::query()->value('about_image');
        $this->assertIsString($path);
        $this->assertNotSame('', $path);
        $this->assertStringContainsString('settings/about', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertStringStartsWith('/storage/', Storage::disk('public')->url($path));
    }
}
