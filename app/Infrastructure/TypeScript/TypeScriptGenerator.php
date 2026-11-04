<?php

declare(strict_types=1);

namespace App\Infrastructure\TypeScript;

use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

/**
 * Runs the Spatie TypeScript transformer over the configured Data classes.
 *
 * The application's PHP Data objects are the single source of truth for the
 * PHP <-> TypeScript contract: the UI imports the generated definitions rather
 * than hand-writing duplicate interfaces.
 */
final class TypeScriptGenerator
{
    public function __construct(
        private readonly TypeScriptTransformerConfig $config,
    ) {}

    /**
     * Transform the configured classes and persist the output file.
     *
     * @return int Number of generated TypeScript definitions.
     */
    public function generate(): int
    {
        $collection = TypeScriptTransformer::create($this->config)->transform();

        return count($collection);
    }
}
