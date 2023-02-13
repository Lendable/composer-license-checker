<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Exception\FailedProvidingPackages;
use Lendable\ComposerLicenseChecker\Exception\PackagesProviderNotLocated;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\SymfonyStyle;

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
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $style->title('Composer License Checker');

        /** @var string $allowFile */
        $allowFile = $input->getOption('allow-file');
        if (!\is_file($allowFile) || !\is_readable($allowFile)) {
            $style->error(\sprintf('File "%s" could not be read.', $allowFile));

            return self::FAILURE;
        }

        $config = require $input->getOption('allow-file');
        if (!$config instanceof LicenseConfiguration) {
            $style->error(\sprintf(
                'File "%s" must return an instance of %s.',
                $allowFile,
                LicenseConfiguration::class,
            ));

            return self::FAILURE;
        }

        /** @var string|null $path */
        $path = $input->getOption('path');
        if (\is_string($path) && !\is_dir($path)) {
            $style->error(\sprintf('The provided path "%s" does not exist.', $path));

            return self::FAILURE;
        }

        $path = $path === null ? \getcwd() : \realpath($path);
        if ($path === false) {
            $style->error('Could not resolve project path.');

            return self::FAILURE;
        }

        /** @var non-empty-string $providerId */
        $providerId = $input->getOption('provider-id');
        $noDev = $config->ignoreDev || $input->getOption('no-dev') === true;

        $style->writeln(\sprintf('Checking project at: %s', $path), OutputInterface::VERBOSITY_VERBOSE);
        $style->writeln(\sprintf('Using allow file: %s', \realpath($allowFile)), OutputInterface::VERBOSITY_VERBOSE);
        $style->writeln(\sprintf('Using provider with id: %s', $providerId), OutputInterface::VERBOSITY_VERBOSE);
        $style->writeln(\sprintf('With dev dependencies: %s', $noDev ? 'no' : 'yes'), OutputInterface::VERBOSITY_VERBOSE);

        try {
            $provider = $this->locator->locate($providerId);
        } catch (PackagesProviderNotLocated $e) {
            $style->error($e->getMessage());

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

            $style->error($message);

            return self::FAILURE;
        }

        $output->writeln(\sprintf('Packages found: %d', \count($packages)), OutputInterface::VERBOSITY_VERBOSE);

        foreach ($packages as $package) {
            if ($config->allowsPackage($package->name->toString())) {
                continue;
            }

            if ($package->licenses === []) {
                $violation = true;
                $style->error(
                    \sprintf(
                        'Dependency "%s" does not have a license and is not explicitly allowed.',
                        $package->name->toString(),
                    )
                );

                continue;
            }

            foreach ($package->licenses as $license) {
                if ($config->allowsLicense($license)) {
                    continue;
                }

                $violation = true;
                $style->error(
                    \sprintf(
                        'Dependency "%s" has license "%s" which is not in the allowed list.',
                        $package->name->toString(),
                        $license,
                    ),
                );
            }
        }

        if (!$violation) {
            $style->success('All dependencies have allowed licenses.');
        }

        return $violation ? self::FAILURE : self::SUCCESS;
    }
}
