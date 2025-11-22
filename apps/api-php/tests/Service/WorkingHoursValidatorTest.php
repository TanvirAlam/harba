<?php

namespace App\Tests\Service;

use App\Entity\Provider;
use App\Entity\Service;
use App\Service\WorkingHoursValidator;
use PHPUnit\Framework\TestCase;

class WorkingHoursValidatorTest extends TestCase
{
    private WorkingHoursValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new WorkingHoursValidator();
    }

    public function testIsWorkingDayReturnsTrueForConfiguredDay(): void
    {
        $provider = new Provider();
        $provider->setWorkingHours(['monday' => '09:00-17:00']);

        $monday = new \DateTime('next Monday');

        $this->assertTrue($this->validator->isWorkingDay($provider, $monday));
    }

    public function testIsWorkingDayReturnsFalseForNonWorkingDay(): void
    {
        $provider = new Provider();
        $provider->setWorkingHours(['monday' => '09:00-17:00']);

        $tuesday = new \DateTime('next Tuesday');

        $this->assertFalse($this->validator->isWorkingDay($provider, $tuesday));
    }

    public function testGetWorkingHoursForDayReturnsCorrectHours(): void
    {
        $provider = new Provider();
        $provider->setWorkingHours(['monday' => '09:00-17:00']);

        $monday = new \DateTime('next Monday');
        $hours = $this->validator->getWorkingHoursForDay($provider, $monday);

        $this->assertIsArray($hours);
        $this->assertEquals('09:00', $hours['start']);
        $this->assertEquals('17:00', $hours['end']);
    }

    public function testGetWorkingHoursForDayReturnsNullForInvalidFormat(): void
    {
        $provider = new Provider();
        $provider->setWorkingHours(['monday' => 'invalid-format']);

        $monday = new \DateTime('next Monday');
        $hours = $this->validator->getWorkingHoursForDay($provider, $monday);

        $this->assertNull($hours);
    }

    public function testIsWithinWorkingHoursReturnsTrueForValidTime(): void
    {
        $provider = new Provider();
        $provider->setWorkingHours(['monday' => '09:00-17:00']);

        $validTime = new \DateTime('next Monday 10:00');

        $this->assertTrue($this->validator->isWithinWorkingHours($provider, $validTime));
    }

    public function testIsWithinWorkingHoursReturnsFalseForTimeBeforeOpening(): void
    {
        $provider = new Provider();
        $provider->setWorkingHours(['monday' => '09:00-17:00']);

        $earlyTime = new \DateTime('next Monday 08:00');

        $this->assertFalse($this->validator->isWithinWorkingHours($provider, $earlyTime));
    }

    public function testIsWithinWorkingHoursReturnsFalseForTimeAfterClosing(): void
    {
        $provider = new Provider();
        $provider->setWorkingHours(['monday' => '09:00-17:00']);

        $lateTime = new \DateTime('next Monday 18:00');

        $this->assertFalse($this->validator->isWithinWorkingHours($provider, $lateTime));
    }

    public function testCanServiceFitInWorkingHoursReturnsTrueWhenFits(): void
    {
        $provider = new Provider();
        $provider->setWorkingHours(['monday' => '09:00-17:00']);

        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(60); // 1 hour

        $bookingTime = new \DateTime('next Monday 15:00');

        $this->assertTrue(
            $this->validator->canServiceFitInWorkingHours($provider, $service, $bookingTime)
        );
    }

    public function testCanServiceFitInWorkingHoursReturnsFalseWhenDoesNotFit(): void
    {
        $provider = new Provider();
        $provider->setWorkingHours(['monday' => '09:00-17:00']);

        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(120); // 2 hours

        $bookingTime = new \DateTime('next Monday 16:00'); // Would end at 18:00

        $this->assertFalse(
            $this->validator->canServiceFitInWorkingHours($provider, $service, $bookingTime)
        );
    }

    public function testGetValidationErrorReturnsNullForValidBooking(): void
    {
        $provider = new Provider();
        $provider->setWorkingHours(['monday' => '09:00-17:00']);

        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(60);

        $bookingTime = new \DateTime('next Monday 10:00');

        $error = $this->validator->getValidationError($provider, $service, $bookingTime);

        $this->assertNull($error);
    }

    public function testGetValidationErrorReturnsMessageForNonWorkingDay(): void
    {
        $provider = new Provider();
        $provider->setWorkingHours(['monday' => '09:00-17:00']);

        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(60);

        $tuesday = new \DateTime('next Tuesday 10:00');

        $error = $this->validator->getValidationError($provider, $service, $tuesday);

        $this->assertNotNull($error);
        $this->assertStringContainsString('does not work on', $error);
    }

    public function testGetValidationErrorReturnsMessageForOutsideWorkingHours(): void
    {
        $provider = new Provider();
        $provider->setWorkingHours(['monday' => '09:00-17:00']);

        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(30);

        $earlyTime = new \DateTime('next Monday 08:00');

        $error = $this->validator->getValidationError($provider, $service, $earlyTime);

        $this->assertNotNull($error);
        $this->assertStringContainsString('outside provider working hours', $error);
    }

    public function testGetValidationErrorReturnsMessageForServiceNotFitting(): void
    {
        $provider = new Provider();
        $provider->setWorkingHours(['monday' => '09:00-17:00']);

        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(120);

        $lateTime = new \DateTime('next Monday 16:00');

        $error = $this->validator->getValidationError($provider, $service, $lateTime);

        $this->assertNotNull($error);
        $this->assertStringContainsString('extends beyond provider closing time', $error);
    }
}
