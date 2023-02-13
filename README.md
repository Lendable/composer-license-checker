# Composer License Checker

[![Latest Stable Version](https://poser.pugx.org/lendable/composer-license-checker/v/stable)](https://packagist.org/packages/lendable/composer-license-checker)
[![License](https://poser.pugx.org/lendable/composer-license-checker/license)](https://packagist.org/packages/lendable/composer-license-checker)
[![Continuous Integration](https://github.com/lendable/composer-license-checker/actions/workflows/ci.yml/badge.svg)](https://github.com/lendable/composer-license-checker/actions/workflows/ci.yml)

This library provides tooling to check licensing of dependencies against a set of rules to ensure compliance with open source licenses and minimize legal risk. It helps you to keep track of licenses of dependencies in use and make informed decisions on their usage.

## Installation

```sh
composer require --dev lendable/composer-license-checker
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
    ->addAllowedPackage('vendor_name/foo_bar') // Allow a specific package regardless licensing.
    ->build();

```

```sh
./vendor/bin/composer-license-checker
```

It is suggested you build this into your CI pipeline to automate checking it.
