<?php

namespace App\Services;

use Aws\S3\S3Client;
use RuntimeException;

class SpacesClientFactory
{
    public function make(): S3Client
    {
        $config = config('services.spaces');
        foreach (['key', 'secret', 'bucket', 'endpoint'] as $required) {
            if (blank($config[$required] ?? null)) {
                throw new RuntimeException('DigitalOcean Spaces configuration is missing: '.$required.'.');
            }
        }

        $endpoint = rtrim((string) $config['endpoint'], '/');
        $host = parse_url($endpoint, PHP_URL_HOST);
        $bucketPrefix = strtolower((string) $config['bucket']).'.';
        if (is_string($host) && str_starts_with(strtolower($host), $bucketPrefix)) {
            $endpoint = (parse_url($endpoint, PHP_URL_SCHEME) ?: 'https').'://'.substr($host, strlen($bucketPrefix));
        }

        return new S3Client([
            'version' => 'latest',
            // Spaces uses the endpoint for its physical datacenter. DigitalOcean's
            // AWS SDK guidance requires an AWS-compatible SigV4 signing region.
            'region' => $config['signing_region'] ?? 'us-east-1',
            'endpoint' => $endpoint,
            'use_path_style_endpoint' => false,
            'signature_version' => 'v4',
            'request_checksum_calculation' => 'when_required',
            'response_checksum_validation' => 'when_required',
            'credentials' => [
                'key' => $config['key'],
                'secret' => $config['secret'],
            ],
        ]);
    }
}
