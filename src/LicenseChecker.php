<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Exception\FailedProvidingPackages;
use Lendable\ComposerLicenseChecker\Exception\PackagesProviderNotLocated;
use Lendable\ComposerLicenseChecker\Output\Display;
use Lendable\ComposerLicenseChecker\Output\HumanReadableDisplay;
use Lendable\ComposerLicenseChecker\Output\JsonDisplay;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

final class LicenseChecker extends SingleCommandApplication
{
    public function __construct(private readonly PackagesProviderLocator $locator)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('Composer License Checker')
            ->setVersion('0.0.1')
            ->addOption(
                'allow-file',
                'a',
                InputOption::VALUE_REQUIRED,
                'Path to the allowed licenses configuration file',
                '.allowed-licenses.php',
            )->addOption(
                'path',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to project root, where composer.json lives',
                null,
            )->addOption(
                'provider-id',
                null,
                InputOption::VALUE_REQUIRED,
                \sprintf('Which packages data provider to use, one of: %s', \implode(', ', $ids = $this->locator->ids())),
                $ids[0] ?? null,
            )->addOption(
                'no-dev',
                null,
                InputOption::VALUE_NONE,
                'Disables dev dependencies check',
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'Format to display the results in ("json" or "human")',
                'human',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $display = $this->createDisplay($input, $output);
        $display->onStart();

        /** @var string $allowFile */
        $allowFile = $input->getOption('allow-file');
        if (!\is_file($allowFile) || !\is_readable($allowFile)) {
            $display->onFatalError(\sprintf('File "%s" could not be read.', $allowFile));

            return self::FAILURE;
        }

        $config = require $input->getOption('allow-file');
        if (!$config instanceof LicenseConfiguration) {
            $display->onFatalError(
                \sprintf(
                    'File "%s" must return an instance of %s.',
                    $allowFile,
                    LicenseConfiguration::class,
                )
            );

            return self::FAILURE;
        }

        /** @var string|null $path */
        $path = $input->getOption('path');
        if (\is_string($path) && !\is_dir($path)) {
            $display->onFatalError(\sprintf('The provided path "%s" does not exist.', $path));

            return self::FAILURE;
        }

        $path = $path === null ? \getcwd() : \realpath($path);
        if ($path === false) {
            $display->onFatalError('Could not resolve project path.');

            return self::FAILURE;
        }

        /** @var non-empty-string $providerId */
        $providerId = $input->getOption('provider-id');
        $noDev = $config->ignoreDev || $input->getOption('no-dev') === true;

        $display->onDetail(\sprintf('Checking project at: %s', $path));
        $display->onDetail(\sprintf('Using allow file: %s', \realpath($allowFile)));
        $display->onDetail(\sprintf('Using provider with id: %s', $providerId));
        $display->onDetail(\sprintf('With dev dependencies: %s', $noDev ? 'no' : 'yes'));

        try {
            $provider = $this->locator->locate($providerId);
        } catch (PackagesProviderNotLocated $e) {
            $display->onFatalError($e->getMessage());

            return self::FAILURE;
        }

        $violation = false;

        try {
            $packages = $provider->provide($path, $noDev);
        } catch (FailedProvidingPackages $e) {
            $message = $e->getMessage();
            if (null !== $cause = $e->getPrevious()) {
                $message .= ': '.$cause->getMessage();
            }

            $display->onFatalError($message);

            return self::FAILURE;
        }

        $display->onDetail(\sprintf('Packages found: %d', \count($packages)));

        foreach ($packages as $package) {
            if ($config->allowsPackage($package->name->toString())) {
                continue;
            }

            if ($package->licenses === []) {
                $violation = true;
                $display->onPackageWithNoLicenseNotExplicitlyAllowed($package->name->toString());

                continue;
            }

            foreach ($package->licenses as $license) {
                if ($config->allowsLicense($license)) {
                    continue;
                }

                $violation = true;
                $display->onPackageWithViolatingLicense($package->name->toString(), $license);
            }
        }

        if ($violation) {
            $display->onOverallFailure();

            return self::FAILURE;
        }

        $display->onOverallSuccess();

        return self::SUCCESS;
    }

    private function createDisplay(InputInterface $input, OutputInterface $output): Display
    {
        $format = $input->getOption('format');
        \assert(\is_string($format));

        return match ($format) {
            'human' => new HumanReadableDisplay($input, $output),
            'json' => new JsonDisplay($output),
            default => throw new \InvalidArgumentException(
                \sprintf(
                    'Format must be one of [human, json], "%s" is invalid.',
                    $format,
                )
            )
        };
    }
}
