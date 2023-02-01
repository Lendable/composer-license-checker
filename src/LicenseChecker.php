<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

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
                'provider',
                null,
                InputOption::VALUE_REQUIRED,
                \sprintf(
                    'Which packages data provider to use, one of: %s',
                    \implode(
                        ', ',
                        \array_map(
                            static fn (string $v): string => \sprintf('"%s"', $v),
                            PackagesProviderType::values(),
                        )
                    )
                ),
                PackagesProviderType::COMPOSER_LICENSES->value,
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
            $style->error(
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
            $style->error(\sprintf('The provided path "%s" does not exist.', $path));

            return self::FAILURE;
        }

        /** @var non-empty-string $path */
        $path = (string) \realpath($path ?? \dirname(__DIR__));
        $providerInputValue = $input->getOption('provider');
        \assert(\is_string($providerInputValue));
        $providerType = PackagesProviderType::from($providerInputValue);

        $style->writeln(\sprintf('Checking project at: %s', $path), OutputInterface::VERBOSITY_VERBOSE);
        $style->writeln(\sprintf('Using allow file: %s', \realpath($allowFile)), OutputInterface::VERBOSITY_VERBOSE);
        $style->writeln(\sprintf('Using provider with id: %s', $providerType->value), OutputInterface::VERBOSITY_VERBOSE);

        try {
            $provider = $this->locator->locate($providerType);
        } catch (PackagesProviderNotLocated $e) {
            $style->error($e->getMessage());

            return self::FAILURE;
        }

        $violation = false;

        foreach ($provider->provide($path) as $package) {
            if ($config->allowsPackage($package->name->toString())) {
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
