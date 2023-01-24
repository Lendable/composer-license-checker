<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

final class LicenseChecker extends SingleCommandApplication
{
    protected function configure(): void
    {
        $this
            ->setName('Composer License Checker')
            ->setVersion('0.0.1')
            ->addOption('allow-file', 'a', InputOption::VALUE_OPTIONAL, '', '.allowed-licenses.php')
        ;
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

        $process = Process::fromShellCommandline('composer licenses --format=json');
        $process->run();
        if (!$process->isSuccessful()) {
            $style->error(\sprintf('Failed to run "composer licenses --format=json" (%d).', $process->getExitCode()));

            return self::FAILURE;
        }

        $rawData = $process->getOutput();

        /** @var array{
         *       name: string,
         *       version: string,
         *       license: list{string},
         *       dependencies: array<string, array{version: string, license: list<string}>
         * }|false $data
         */
        $data = \json_decode($rawData, true, flags: JSON_THROW_ON_ERROR);
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
                $style->error(\sprintf('Dependency "%s" has license "%s" which is not in the allowed list.', $package, $license));
            }
        }

        if (!$violation) {
            $style->success('All dependencies have allowed licenses.');

        }

        return $violation ? self::FAILURE : self::SUCCESS;
    }
}
