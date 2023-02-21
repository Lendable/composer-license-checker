<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\PHPUnit\Set\PHPUnitLevelSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonyLevelSetList;

return static function (RectorConfig $rector): void {
    $rector->parallel();
    $rector->paths([__DIR__.'/src', __DIR__.'/tests', __DIR__.'/bin', __DIR__.'/rector.php']);
    $rector->phpVersion(PhpVersion::PHP_81);
    $rector->phpstanConfig(__DIR__.'/phpstan-rector.neon');
    $rector->cacheDirectory(__DIR__.'/tmp/rector');
    $rector->sets([
        SetList::CODE_QUALITY,
        LevelSetList::UP_TO_PHP_81,
        PHPUnitLevelSetList::UP_TO_PHPUNIT_100,
        SymfonyLevelSetList::UP_TO_SYMFONY_54,
    ]);
};
