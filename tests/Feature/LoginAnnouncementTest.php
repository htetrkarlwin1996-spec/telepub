<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Artist;
use App\Models\User;
use App\Services\LoginAnnouncement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LoginAnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_announcement_is_shown_only_once_per_login(): void
    {
        $this->saveAnnouncement([
            'enabled' => true,
            'title' => 'New YouTube Premiere',
            'body' => 'Watch our latest announcement.',
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'button_text' => 'Watch Now',
            'button_url' => 'https://youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
        $user = User::factory()->create(['role' => 'artist', 'password' => 'password']);
        Artist::create(['user_id' => $user->id, 'artist_name' => 'Announcement Artist']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('id="login-announcement"', false)
            ->assertSeeText('New YouTube Premiere')
            ->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false);

        $this->get(route('dashboard'))->assertOk()->assertDontSee('id="login-announcement"', false);

        $this->post(route('logout'))->assertRedirect('/');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->get(route('dashboard'))->assertOk()->assertSee('id="login-announcement"', false);
    }

    public function test_admin_can_save_image_text_and_youtube_announcement(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.settings.login-announcement'), [
            'enabled' => '1',
            'title' => 'Important News',
            'body' => 'Please read this announcement.',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'image' => UploadedFile::fake()->image('announcement.jpg', 1200, 630),
            'button_text' => 'Read More',
            'button_url' => 'https://telemusic.io/news',
        ])->assertRedirect()->assertSessionHas('success');

        $settings = json_decode(AppSetting::where('key', 'login_announcement')->value('value'), true);
        $this->assertTrue($settings['enabled']);
        $this->assertSame('Important News', $settings['title']);
        $this->assertSame('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $settings['youtube_url']);
        Storage::disk('public')->assertExists($settings['image_path']);
    }

    public function test_non_youtube_video_url_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->from(route('admin.settings'))->put(route('admin.settings.login-announcement'), [
            'enabled' => '1',
            'title' => 'Invalid video',
            'youtube_url' => 'https://example.com/video',
        ])->assertRedirect(route('admin.settings'))->assertSessionHasErrors('youtube_url');
    }

    private function saveAnnouncement(array $settings): void
    {
        AppSetting::updateOrCreate(['key' => 'login_announcement'], ['value' => json_encode($settings)]);
        app(LoginAnnouncement::class)->clearCache();
    }
}
