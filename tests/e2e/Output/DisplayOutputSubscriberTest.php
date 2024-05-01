<?php

declare(strict_types=1);

namespace Tests\E2E\Lendable\ComposerLicenseChecker\Output;

use Lendable\ComposerLicenseChecker\Event\Dispatcher;
use Lendable\ComposerLicenseChecker\Event\FatalError;
use Lendable\ComposerLicenseChecker\Event\OutcomeFailure;
use Lendable\ComposerLicenseChecker\Event\OutcomeSuccess;
use Lendable\ComposerLicenseChecker\Event\PackageWithViolatingLicense;
use Lendable\ComposerLicenseChecker\Event\Started;
use Lendable\ComposerLicenseChecker\Event\TraceInformation;
use Lendable\ComposerLicenseChecker\Event\UnlicensedPackageNotExplicitlyAllowed;
use Lendable\ComposerLicenseChecker\Licenses;
use Lendable\ComposerLicenseChecker\Output\Display;
use Lendable\ComposerLicenseChecker\Output\DisplayOutputSubscriber;
use Lendable\ComposerLicenseChecker\Package;
use Lendable\ComposerLicenseChecker\PackageName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(DisplayOutputSubscriber::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class DisplayOutputSubscriberTest extends TestCase
{
    private Display&MockObject $display;

    private Dispatcher $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->display = $this->createMock(Display::class);
        $this->dispatcher = new Dispatcher();
        $this->dispatcher->attach(new DisplayOutputSubscriber($this->display));
    }

    public function test_delegates_on_started(): void
    {
        $this->display
            ->expects(self::once())
            ->method('onStarted');

        $this->dispatcher->dispatch(new Started());
    }

    public function test_delegates_on_fatal_error(): void
    {
        $this->display
            ->expects(self::once())
            ->method('onFatalError')
            ->with('Foo Bar');

        $this->dispatcher->dispatch(new FatalError('Foo Bar'));
    }

    public function test_delegates_on_outcome_failure(): void
    {
        $this->display
            ->expects(self::once())
            ->method('onOutcomeFailure');

        $this->dispatcher->dispatch(new OutcomeFailure());
    }

    public function test_delegates_on_outcome_success(): void
    {
        $this->display
            ->expects(self::once())
            ->method('onOutcomeSuccess');

        $this->dispatcher->dispatch(new OutcomeSuccess());
    }

    public function test_delegates_on_package_with_violating_license(): void
    {
        $package = new Package(new PackageName('foo/bar'), new Licenses(['MIT']));

        $this->display
            ->expects(self::once())
            ->method('onPackageWithViolatingLicense')
            ->with($package);

        $this->dispatcher->dispatch(new PackageWithViolatingLicense($package));
    }

    public function test_delegates_on_unlicensed_package_not_explicitly_allowed(): void
    {
        $package = new Package(new PackageName('foo/bar'), new Licenses(['MIT']));

        $this->display
            ->expects(self::once())
            ->method('onUnlicensedPackageNotExplicitlyAllowed')
            ->with($package);

        $this->dispatcher->dispatch(new UnlicensedPackageNotExplicitlyAllowed($package));
    }

    public function test_delegates_on_trace_information(): void
    {
        $this->display
            ->expects(self::once())
            ->method('onTraceInformation')
            ->with('Bar Foo');

        $this->dispatcher->dispatch(new TraceInformation('Bar Foo'));
    }
}
