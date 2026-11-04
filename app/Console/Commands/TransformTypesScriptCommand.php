<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Infrastructure\TypeScript\TypeScriptGenerator;
use Illuminate\Console\Command;

/**
 * Regenerate the TypeScript definitions from the application's PHP Data classes.
 *
 * The generated file is a build artifact: it is produced here and checked by
 * CI, never edited by hand.
 */
final class TransformTypesScriptCommand extends Command
{
    protected $signature = 'types:transform';

    protected $description = 'Generate TypeScript definitions from the application Data classes';

    public function handle(TypeScriptGenerator $generator): int
    {
        $count = $generator->generate();

        $this->components->info(
            "Generated {$count} TypeScript definition(s) in resources/js/types/generated"
        );

        return self::SUCCESS;
    }
}
