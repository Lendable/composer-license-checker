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

Create a configuration file in your project root, `.allowed-licenses.php` (or you can use the the option `-a / --allow-file` to specify the location of the configuration).

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
./vendor/bin/composer-license-checker [--allow-file path/to/configuration_file.php]
```

It is suggested you build this into your CI pipeline to automate checking it.

## Licensing information providers

This tool can use two different sources for retrieving licensing information: using the `composer licenses` command and parsing the `installed.json` file created by Composer.

### Using the `installed.json` provider (*default*)
Specify `--provider-id=json`. 

The tool will parse the `installed.json` file created by Composer which has all the relevant information. This does not require Composer to be installed in the environment the tool is executed within. This file is internal to Composer however, so there is the potential that the schema may change in the future. If you experience issues, try using the `composer licenses` provider and report the issue.

### Using `composer licenses` provider
Specify `--provider-id=licenses`.

The `composer licenses` command provides a (potentially) more stable API for retrieving licensing information. This however requires the tool to execute `composer` so it must be installed in environment executed within. 
