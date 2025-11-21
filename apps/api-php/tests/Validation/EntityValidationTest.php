<?php

namespace App\Tests\Validation;

use App\Entity\Booking;
use App\Entity\Provider;
use App\Entity\Service;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntityValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    // User Entity Validation Tests

    public function testUserWithValidDataPassesValidation(): void
    {
        $user = new User();
        $user->setEmail('valid@example.com');
        $user->setPassword('hashed_password_here');

        $violations = $this->validator->validate($user);
        $this->assertCount(0, $violations, 'Valid user should not have validation errors');
    }

    public function testUserWithInvalidEmailFailsValidation(): void
    {
        $user = new User();
        $user->setEmail('not-an-email');
        $user->setPassword('password123');

        $violations = $this->validator->validate($user);
        $this->assertGreaterThan(0, $violations->count(), 'Invalid email should cause validation error');
        
        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = $violation->getMessage();
        }
        
        $this->assertContains('Please provide a valid email address', $messages);
    }

    public function testUserWithEmptyEmailFailsValidation(): void
    {
        $user = new User();
        $user->setEmail('');
        $user->setPassword('password123');

        $violations = $this->validator->validate($user);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testUserWithEmptyPasswordFailsValidation(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('');

        $violations = $this->validator->validate($user);
        $this->assertGreaterThan(0, $violations->count());
        
        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = $violation->getMessage();
        }
        
        $this->assertContains('Password is required', $messages);
    }

    // Provider Entity Validation Tests

    public function testProviderWithValidDataPassesValidation(): void
    {
        $provider = new Provider();
        $provider->setName('Valid Provider Name');
        $provider->setWorkingHours(['Monday' => '09:00-17:00']);

        $violations = $this->validator->validate($provider);
        $this->assertCount(0, $violations, 'Valid provider should not have validation errors');
    }

    public function testProviderWithEmptyNameFailsValidation(): void
    {
        $provider = new Provider();
        $provider->setName('');
        $provider->setWorkingHours(['Monday' => '09:00-17:00']);

        $violations = $this->validator->validate($provider);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testProviderWithShortNameFailsValidation(): void
    {
        $provider = new Provider();
        $provider->setName('A'); // Only 1 character
        $provider->setWorkingHours(['Monday' => '09:00-17:00']);

        $violations = $this->validator->validate($provider);
        $this->assertGreaterThan(0, $violations->count());
    }

    // Service Entity Validation Tests

    public function testServiceWithValidDataPassesValidation(): void
    {
        $service = new Service();
        $service->setName('Haircut');
        $service->setDuration(60);

        $violations = $this->validator->validate($service);
        $this->assertCount(0, $violations, 'Valid service should not have validation errors');
    }

    public function testServiceWithEmptyNameFailsValidation(): void
    {
        $service = new Service();
        $service->setName('');
        $service->setDuration(60);

        $violations = $this->validator->validate($service);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testServiceWithZeroDurationFailsValidation(): void
    {
        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(0);

        $violations = $this->validator->validate($service);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testServiceWithNegativeDurationFailsValidation(): void
    {
        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(-30);

        $violations = $this->validator->validate($service);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testServiceWithExcessiveDurationFailsValidation(): void
    {
        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(500); // Over 480 minutes (8 hours)

        $violations = $this->validator->validate($service);
        $this->assertGreaterThan(0, $violations->count());
    }

    // Booking Entity Validation Tests

    public function testBookingWithValidDataPassesValidation(): void
    {
        $user = new User();
        $user->setEmail('booking@example.com');
        $user->setPassword('password');

        $provider = new Provider();
        $provider->setName('Test Provider');
        $provider->setWorkingHours(['Monday' => '09:00-17:00']);

        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(60);

        $booking = new Booking();
        $booking->setUser($user);
        $booking->setProvider($provider);
        $booking->setService($service);
        $booking->setDatetime(new \DateTime('+1 day'));

        $violations = $this->validator->validate($booking);
        $this->assertCount(0, $violations, 'Valid booking should not have validation errors');
    }

    public function testBookingWithPastDateFailsValidation(): void
    {
        $user = new User();
        $user->setEmail('booking@example.com');
        $user->setPassword('password');

        $provider = new Provider();
        $provider->setName('Test Provider');
        $provider->setWorkingHours(['Monday' => '09:00-17:00']);

        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(60);

        $booking = new Booking();
        $booking->setUser($user);
        $booking->setProvider($provider);
        $booking->setService($service);
        $booking->setDatetime(new \DateTime('-1 day')); // Past date

        $violations = $this->validator->validate($booking);
        $this->assertGreaterThan(0, $violations->count());
        
        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = $violation->getMessage();
        }
        
        $this->assertContains('Booking date must be today or in the future', $messages);
    }

    public function testBookingWithNullUserFailsValidation(): void
    {
        $provider = new Provider();
        $provider->setName('Test Provider');
        $provider->setWorkingHours(['Monday' => '09:00-17:00']);

        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(60);

        $booking = new Booking();
        // No user set
        $booking->setProvider($provider);
        $booking->setService($service);
        $booking->setDatetime(new \DateTime('+1 day'));

        $violations = $this->validator->validate($booking);
        $this->assertGreaterThan(0, $violations->count());
    }
}
