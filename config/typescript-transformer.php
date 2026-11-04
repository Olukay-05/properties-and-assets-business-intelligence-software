<?php

declare(strict_types=1);

use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\Writers\TypeDefinitionWriter;

/*
|--------------------------------------------------------------------------
| TypeScript transformer (PHP -> TypeScript contract)
|--------------------------------------------------------------------------
|
| Every Data class that should be visible to the React/TypeScript layer is
| discovered under the configured paths and transformed into TypeScript
| definitions written to resources/js/types/generated. Hand-written duplicate
| TypeScript interfaces are forbidden (architecture decision AD-16).
|
| Regenerate with: php artisan types:transform
|
*/

return TypeScriptTransformerConfig::create()
    ->autoDiscoverTypes(
        app_path('Domain'),
        app_path('Data'),
    )
    ->transformers([
        DtoTransformer::class,
    ])
    ->writer(TypeDefinitionWriter::class)
    ->outputFile(resource_path('js/types/generated/types.d.ts'));
