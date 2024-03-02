<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Event\Dispatcher;
use Lendable\ComposerLicenseChecker\Event\FatalError;
use Lendable\ComposerLicenseChecker\Event\OutcomeFailure;
use Lendable\ComposerLicenseChecker\Event\OutcomeSuccess;
use Lendable\ComposerLicenseChecker\Event\PackageWithViolatingLicense;
use Lendable\ComposerLicenseChecker\Event\Started;
use Lendable\ComposerLicenseChecker\Event\TraceInformation;
use Lendable\ComposerLicenseChecker\Event\UnlicensedPackageNotExplicitlyAllowed;
use Lendable\ComposerLicenseChecker\Exception\FailedProvidingPackages;
use Lendable\ComposerLicenseChecker\Exception\PackagesProviderNotLocated;
use Lendable\ComposerLicenseChecker\Output\Display;
use Lendable\ComposerLicenseChecker\Output\DisplayOutputSubscriber;
use Lendable\ComposerLicenseChecker\Output\HumanReadableDisplay;
use Lendable\ComposerLicenseChecker\Output\JsonDisplay;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

final class LicenseChecker extends SingleCommandApplication
{
    public function __construct(
        private readonly PackagesProviderLocator $locator,
        private readonly Dispatcher $dispatcher = new Dispatcher(),
    ) {
        parent::__construct();

        $this->setDescription(
            'Checks licensing of dependencies against a set of rules '.
            'to ensure compliance with open source licenses and minimize legal risk.',
        );
    }

    protected function configure(): void
    {
        $this
            ->setName('Composer License Checker')
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
        try {
            $display = $this->createDisplay($input, $output);
        } catch (\InvalidArgumentException $exception) {
            $display = new HumanReadableDisplay($input, $output);
            $display->onStarted();
            /** @var non-empty-string $message */
            $message = $exception->getMessage();
            $display->onFatalError($message);

            return self::FAILURE;
        }

        $this->dispatcher->attach(new DisplayOutputSubscriber($display));

        $this->dispatcher->dispatch(new Started());

        /** @var string $allowFile */
        $allowFile = $input->getOption('allow-file');
        if (!\is_file($allowFile) || !\is_readable($allowFile)) {
            $this->dispatcher->dispatch(new FatalError(\sprintf('File "%s" could not be read.', $allowFile)));

            return self::FAILURE;
        }

        \ob_start();
        $config = require $input->getOption('allow-file');
        $outputBuffer = \ob_get_contents();
        \ob_end_clean();

        if (!$config instanceof LicenseConfiguration) {
            $this->dispatcher->dispatch(
                new FatalError(
                    \sprintf(
                        'File "%s" must return an instance of %s.',
                        $allowFile,
                        LicenseConfiguration::class,
                    ),
                ),
            );

            return self::FAILURE;
        }

        if ($outputBuffer !== '' && $outputBuffer !== false) {
            $this->dispatcher->dispatch(
                new FatalError(
                    \sprintf(
                        'File "%s" returned an instance of %s, but also output plain text due to content outside PHP tags.',
                        $allowFile,
                        LicenseConfiguration::class,
                    ),
                ),
            );

            return self::FAILURE;
        }

        /** @var string|null $path */
        $path = $input->getOption('path');
        if (\is_string($path) && !\is_dir($path)) {
            $this->dispatcher->dispatch(new FatalError(\sprintf('The provided path "%s" does not exist.', $path)));

            return self::FAILURE;
        }

        $path = $path === null ? \getcwd() : \realpath($path);
        if ($path === false) {
            $this->dispatcher->dispatch(new FatalError('Could not resolve project path.'));

            return self::FAILURE;
        }

        /** @var non-empty-string $providerId */
        $providerId = $input->getOption('provider-id');
        $noDev = $config->ignoreDev || $input->getOption('no-dev') === true;

        $this->dispatcher->dispatch(new TraceInformation(\sprintf('Checking project at: %s', $path)));
        $this->dispatcher->dispatch(new TraceInformation(\sprintf('Using allow file: %s', \realpath($allowFile))));
        $this->dispatcher->dispatch(new TraceInformation(\sprintf('Using provider with id: %s', $providerId)));
        $this->dispatcher->dispatch(new TraceInformation(\sprintf('With dev dependencies: %s', $noDev ? 'no' : 'yes')));

        try {
            $provider = $this->locator->locate($providerId);
        } catch (PackagesProviderNotLocated $e) {
            $this->dispatcher->dispatch(new FatalError($e->getMessage()));

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

            $this->dispatcher->dispatch(new FatalError($message));

            return self::FAILURE;
        }

        $this->dispatcher->dispatch(new TraceInformation(\sprintf('Packages found: %d', \count($packages))));

        foreach ($packages as $package) {
            if ($config->allowsPackage($package->name->toString())) {
                continue;
            }

            if ($package->isUnlicensed()) {
                $violation = true;
                $this->dispatcher->dispatch(new UnlicensedPackageNotExplicitlyAllowed($package));

                continue;
            }

            if ($config->allowsLicenseOfPackage($package)) {
                continue;
            }

            $violation = true;
            $this->dispatcher->dispatch(new PackageWithViolatingLicense($package));
        }

        if ($violation) {
            $this->dispatcher->dispatch(new OutcomeFailure());

            return self::FAILURE;
        }

        $this->dispatcher->dispatch(new OutcomeSuccess());

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
                ),
            ),
        };
    }
}
