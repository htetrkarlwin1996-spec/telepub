<?php

namespace Tests\Feature;

use App\Services\SpacesClientFactory;
use Tests\TestCase;

class SpacesClientFactoryTest extends TestCase
{
    public function test_spaces_client_uses_aws_signing_region_and_virtual_host_style(): void
    {
        config()->set('services.spaces', [
            'key' => 'test-key',
            'secret' => 'test-secret',
            'signing_region' => 'us-east-1',
            'bucket' => 'telemusicapp',
            'endpoint' => 'https://telemusicapp.sgp1.digitaloceanspaces.com/',
            'use_path_style_endpoint' => true,
        ]);

        $client = app(SpacesClientFactory::class)->make();

        $this->assertSame('us-east-1', $client->getRegion());
        $this->assertSame('https://sgp1.digitaloceanspaces.com', (string) $client->getEndpoint());
        $this->assertFalse($client->getConfig('use_path_style_endpoint'));
    }
}
