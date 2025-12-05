<?php

declare(strict_types=1);

namespace Tests\Unit\Lendable\ComposerLicenseChecker\Output;

use Lendable\ComposerLicenseChecker\Licenses;
use Lendable\ComposerLicenseChecker\Output\JsonDisplay;
use Lendable\ComposerLicenseChecker\Package;
use Lendable\ComposerLicenseChecker\PackageName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

#[CoversClass(JsonDisplay::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class JsonDisplayTest extends TestCase
{
    private BufferedOutput $bufferedOutput;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->bufferedOutput = new BufferedOutput();
    }

    public function test_on_fatal_error_displays_error(): void
    {
        $display = new JsonDisplay($this->bufferedOutput);

        $display->onFatalError('Fatal Error');

        self::assertJsonStringEqualsJsonString(
            '{"result": "error", "message": "Fatal Error"}',
            $this->bufferedOutput->fetch(),
        );
    }

    public function test_package_with_violating_license_is_in_display(): void
    {
        $display = new JsonDisplay($this->bufferedOutput);

        $display->onPackageWithViolatingLicense(new Package(new PackageName('foo/bar'), new Licenses(['MIT'])));
        $display->onOutcomeFailure();

        self::assertJsonStringEqualsJsonString(
            '{"result": "failure", "violations": {"MIT": ["foo/bar"]}}',
            $this->bufferedOutput->fetch(),
        );

        $display->onStarted();
        $display->onPackageWithViolatingLicense(new Package(new PackageName('bar/baz'), new Licenses(['MIT'])));
        $display->onOutcomeFailure();

        self::assertJsonStringEqualsJsonString(
            '{"result": "failure", "violations": {"MIT": ["bar/baz"]}}',
            $this->bufferedOutput->fetch(),
        );
    }

    public function test_unlicensed_package_not_explicitly_allowed_is_in_display(): void
    {
        $display = new JsonDisplay($this->bufferedOutput);
        $display->onUnlicensedPackageNotExplicitlyAllowed(new Package(new PackageName('foo/bar'), new Licenses([])));
        $display->onOutcomeFailure();

        self::assertJsonStringEqualsJsonString(
            '{"result": "failure", "violations": {"UNLICENSED": ["foo/bar"]}}',
            $this->bufferedOutput->fetch(),
        );

        $display->onStarted();
        $display->onUnlicensedPackageNotExplicitlyAllowed(new Package(new PackageName('bar/baz'), new Licenses([])));
        $display->onOutcomeFailure();

        self::assertJsonStringEqualsJsonString(
            '{"result": "failure", "violations": {"UNLICENSED": ["bar/baz"]}}',
            $this->bufferedOutput->fetch(),
        );
    }

    public function test_outcome_failure_displays_failure_result(): void
    {
        $display = new JsonDisplay($this->bufferedOutput);

        $display->onPackageWithViolatingLicense(new Package(new PackageName('foo/bar'), new Licenses(['MIT'])));
        $display->onOutcomeFailure();

        self::assertJsonStringEqualsJsonString(
            '{"result": "failure", "violations": {"MIT": ["foo/bar"]}}',
            $this->bufferedOutput->fetch(),
        );

        $display = new JsonDisplay($traceOutput = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE));

        $display->onPackageWithViolatingLicense(new Package(new PackageName('foo/bar'), new Licenses(['MIT'])));
        $display->onTraceInformation('Trace Info');
        $display->onOutcomeFailure();

        self::assertJsonStringEqualsJsonString(
            '{"result": "failure", "violations": {"MIT": ["foo/bar"]}, "trace": ["Trace Info"]}',
            $traceOutput->fetch(),
        );
    }

    public function test_outcome_success_displays_success_result(): void
    {
        $display = new JsonDisplay($this->bufferedOutput);

        $display->onOutcomeSuccess();

        self::assertJsonStringEqualsJsonString('{"result": "success"}', $this->bufferedOutput->fetch());

        $display = new JsonDisplay($traceOutput = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE));

        $display->onTraceInformation('Trace Info');
        $display->onOutcomeSuccess();

        self::assertJsonStringEqualsJsonString('{"result": "success", "trace": ["Trace Info"]}', $traceOutput->fetch());
    }
}
