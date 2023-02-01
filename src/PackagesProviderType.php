<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

enum PackagesProviderType: string
{
    case COMPOSER_LICENSES = 'composer-licenses';
    case INSTALLED_JSON = 'installed-json';

    /**
     * @return list<value-of<PackagesProviderType>>
     */
    public static function values(): array
    {
        return [
            self::COMPOSER_LICENSES->value,
            self::INSTALLED_JSON->value,
        ];
    }
}
