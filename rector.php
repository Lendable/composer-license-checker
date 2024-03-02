<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withPaths([__DIR__.'/src', __DIR__.'/tests', __DIR__.'/bin', __DIR__.'/rector.php'])
    ->withPHPStanConfigs([__DIR__.'/phpstan-rector.neon'])
    ->withCache(__DIR__.'/tmp/rector', FileCacheStorage::class)
    ->withPreparedSets(codeQuality: true)
    ->withPhpSets(php82: true)
    ->withSets(
        [
            PHPUnitSetList::PHPUNIT_100,
            SymfonySetList::SYMFONY_54,
        ],
    )
    ->withSkip(
        [
            FlipTypeControlToUseExclusiveTypeRector::class,
        ],
    );
