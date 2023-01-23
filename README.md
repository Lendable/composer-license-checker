# Composer License Checker

**Unstable**

## TODO

- [ ] Composer plugin to auto-run licensing checks on install/updates. 
- [ ] Configuration improvements to allow licensing to differ per package name and package vendor.
- [ ] Tests, static analysis, general refactor of inlined `symfony/console` code to ease this. 

## Installation

```sh
composer require --dev lendable/composer-license-checker:dev-main
```

## Usage

Create a configuration file in your project root, `.allowed-licenses.php`.

```php
<?php

declare(strict_types=1);

use Lendable\ComposerLicenseChecker\LicenseConfigurationBuilder;

return (new LicenseConfigurationBuilder())
    ->addLicenses(
        'MIT',
        'BSD-2-Clause',
        'BSD-3-Clause',
        'Apache-2.0',
        // And other licenses you wish to allow.
    )
    ->addAllowedVendor('vendor_name') // Allow any license from a specific vendor, i.e. your own company.
    ->build();

```

```sh
./vendor/bin/composer-license-checker
```

It is suggested you build this into your CI pipeline to automate checking it.
