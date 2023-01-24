<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Output\Display;
use Lendable\ComposerLicenseChecker\Output\HumanReadableDisplay;
use Lendable\ComposerLicenseChecker\Output\JsonDisplay;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Process\Process;

final class LicenseChecker extends SingleCommandApplication
{
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

        $process = Process::fromShellCommandline('composer licenses --format=json');
        $process->run();
        if (!$process->isSuccessful()) {
            $display->onFatalError(
                \sprintf(
                    'Failed to run "composer licenses --format=json" (%d).',
                    $process->getExitCode(),
                )
            );

            return self::FAILURE;
        }

        $rawData = $process->getOutput();

        /** @var array{
         *       name: string,
         *       version: string,
         *       license: list<non-empty-string>,
         *       dependencies: array<non-empty-string, array{version: non-empty-string, license: list<non-empty-string>}>
         * }|false $data
         */
        $data = \json_decode($rawData, true, flags: \JSON_THROW_ON_ERROR);
        \assert(\is_array($data));

        $violation = false;

        foreach ($data['dependencies'] as $package => $packageData) {
            if ($config->allowsPackage($package)) {
                continue;
            }

            foreach ($packageData['license'] as $license) {
                if ($config->allowsLicense($license)) {
                    continue;
                }

                $violation = true;
                $display->onPackageWithViolatingLicense($package, $license);
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
