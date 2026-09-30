<?php

namespace Tests\Feature;

use App\Jobs\TransferReleaseAudioToSpaces;
use App\Models\User;
use App\Services\ChunkedAudioUpload;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SingleReleaseAudioUploadTest extends TestCase
{
    public function test_single_audio_upload_is_staged_and_queued_for_spaces(): void
    {
        Storage::fake('local');
        Queue::fake();
        config()->set('filesystems.release_audio_disk', 's3');
        config()->set('filesystems.release_audio_prefix', 'dashboardtelemusic');

        $request = Request::create('/upload', 'POST', ['action' => 'single'], [], [
            'audio_file' => UploadedFile::fake()->create('track.wav', 1024, 'audio/wav'),
        ]);
        $request->setUserResolver(fn () => new User(['id' => 5]));
        $request->user()->id = 5;

        $result = app(ChunkedAudioUpload::class)->handle($request);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['complete']);
        $this->assertTrue($result['queued']);
        $this->assertStringStartsWith('dashboardtelemusic/tracks/', $result['path']);
        Queue::assertPushed(TransferReleaseAudioToSpaces::class);
        $this->assertCount(1, Storage::disk('local')->allFiles('pending-release-audio'));
    }
}
