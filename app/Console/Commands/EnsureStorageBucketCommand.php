<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Ensure the configured S3-compatible bucket exists.
 *
 * Uses the standard S3 API through Laravel's filesystem layer, so it works
 * against any S3-compatible provider (RustFS locally, Cloudflare R2 in
 * production) without provider-specific code. Safe to run repeatedly.
 */
final class EnsureStorageBucketCommand extends Command
{
    protected $signature = 'app:storage:init';

    protected $description = 'Create the configured object-storage bucket if it does not exist';

    public function handle(): int
    {
        $disk = (string) config('filesystems.default');
        $bucket = (string) config("filesystems.disks.{$disk}.bucket");

        if ($disk !== 's3') {
            $this->components->warn(
                "Default disk is [{$disk}], not [s3]. The evidence store expects an S3-compatible disk."
            );
        }

        try {
            /** @var AwsS3V3Adapter $driver */
            $driver = Storage::disk($disk);
            $client = $driver->getClient();

            if ($client->doesBucketExist($bucket)) {
                $this->components->info("Bucket [{$bucket}] already exists.");

                return self::SUCCESS;
            }

            $client->createBucket(['Bucket' => $bucket]);

            $this->components->info("Bucket [{$bucket}] created.");
        } catch (Throwable $exception) {
            $this->components->error(
                "Could not initialise storage: {$exception->getMessage()}"
            );

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
