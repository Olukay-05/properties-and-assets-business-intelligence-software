<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Sample Data object proving the PHP -> TypeScript contract pipeline.
 *
 * It is the template every later feature follows: a Spatie Data class in PHP
 * whose shape is generated into resources/js/types/generated so the React layer
 * consumes the same contract. Replace or delete once real feature Data objects
 * exist.
 */
#[TypeScript]
final class SampleData extends Data
{
    public function __construct(
        public string $name,
        public int $count,
        public ?string $description = null,
    ) {}
}
