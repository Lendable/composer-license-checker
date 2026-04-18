<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([__DIR__.'/src', __DIR__.'/tests', __DIR__.'/bin', __DIR__.'/rector.php'])
    ->withPHPStanConfigs([__DIR__.'/phpstan-rector.neon'])
    ->withCache(__DIR__.'/tmp/rector', FileCacheStorage::class)
    ->withPreparedSets(codeQuality: true)
    ->withComposerBased(phpunit: true, symfony: true)
    ->withPhpSets(php84: true)
    ->withSkip(
        [
            FlipTypeControlToUseExclusiveTypeRector::class,
        ],
    );
