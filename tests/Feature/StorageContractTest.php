<?php

/*
|--------------------------------------------------------------------------
| Object storage contract tests
|--------------------------------------------------------------------------
|
| These tests run against the real S3-compatible service configured by the
| AWS_* environment variables (RustFS locally, Cloudflare R2 in production).
| They verify actual access control, not merely bucket configuration:
|
|   - upload, retrieval, existence and deletion work
|   - a short-lived pre-signed URL grants read access
|   - the pre-signed URL stops working once it has expired
|   - an unauthenticated request cannot read a private object
|
| They are skipped when no S3-compatible endpoint is configured, so the suite
| still runs on machines without the storage service. Run `docker compose up -d`
| and `php artisan app:storage:init` first.
|
*/

beforeEach(function () {
    if ((string) config('filesystems.default') !== 's3') {
        $this->markTestSkipped('Default filesystem disk is not s3.');
    }

    $endpoint = (string) config('filesystems.disks.s3.endpoint');

    if ($endpoint === '') {
        $this->markTestSkipped('No S3-compatible endpoint configured.');
    }

    $this->disk = Illuminate\Support\Facades\Storage::disk('s3');
    $this->key = 'tests/' . uniqid('evidence-', true) . '.txt';
    $this->contents = 'evidence-' . bin2hex(random_bytes(8));
});

afterEach(function () {
    if (isset($this->disk, $this->key) && $this->disk->exists($this->key)) {
        $this->disk->delete($this->key);
    }
});

it('uploads and reads back an object', function () {
    $this->disk->put($this->key, $this->contents);

    expect($this->disk->exists($this->key))->toBeTrue()
        ->and($this->disk->get($this->key))->toBe($this->contents);
});

it('deletes an object', function () {
    $this->disk->put($this->key, $this->contents);

    $this->disk->delete($this->key);

    expect($this->disk->exists($this->key))->toBeFalse();
});

it('serves a private object through a pre-signed URL', function () {
    $this->disk->put($this->key, $this->contents);

    $url = $this->disk->temporaryUrl($this->key, now()->addMinutes(10));

    expect(file_get_contents($url))->toBe($this->contents);
});

it('stops serving a pre-signed URL once it has expired', function () {
    $this->disk->put($this->key, $this->contents);

    // Issue a URL whose validity window has already elapsed. The storage
    // service validates the signature timestamp itself, so the assertion holds
    // against the real server rather than a faked clock.
    $url = $this->disk->temporaryUrl($this->key, now()->subMinutes(5));

    $context = stream_context_create(['http' => ['ignore_errors' => true]]);

    $response = @file_get_contents($url, false, $context);
    $status = $http_response_header[0] ?? '';

    expect($response)->not->toBe($this->contents)
        ->and($status)->toMatch('/4\d\d/')
        ->and($status)->not->toMatch('/200/');
});

it('denies unauthenticated access to a private object', function () {
    $this->disk->put($this->key, $this->contents);

    $endpoint = rtrim((string) config('filesystems.disks.s3.endpoint'), '/');
    $bucket = (string) config('filesystems.disks.s3.bucket');

    $context = stream_context_create(['http' => ['ignore_errors' => true]]);
    $response = @file_get_contents("{$endpoint}/{$bucket}/{$this->key}", false, $context);
    $status = $http_response_header[0] ?? '';

    expect($response)->not->toBe($this->contents)
        ->and($status)->toMatch('/403/');
});
