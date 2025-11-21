<?php

namespace App\Tests\Entity;

use App\Entity\Booking;
use App\Entity\Provider;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BookingSoftDeleteTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testBookingSoftDelete(): void
    {
        // Create test user
        $user = new User();
        $user->setEmail('test_softdelete_' . uniqid() . '@example.com');
        $user->setPassword('hashedpassword');
        $user->setRoles(['ROLE_USER']);
        $this->entityManager->persist($user);

        // Create test provider
        $provider = new Provider();
        $provider->setName('Test Provider');
        $provider->setWorkingHours(['Monday' => '09:00-17:00']);
        $this->entityManager->persist($provider);

        // Create test service
        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(60);
        $this->entityManager->persist($service);

        // Create booking
        $booking = new Booking();
        $booking->setUser($user);
        $booking->setProvider($provider);
        $booking->setService($service);
        $booking->setDatetime(new \DateTime('+1 day'));
        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        $bookingId = $booking->getId();
        $this->assertNotNull($bookingId);

        // Verify booking exists
        $foundBooking = $this->entityManager->getRepository(Booking::class)->find($bookingId);
        $this->assertNotNull($foundBooking);
        $this->assertNull($foundBooking->getDeletedAt(), 'Booking should not be soft deleted initially');

        // Soft delete the booking
        $this->entityManager->remove($booking);
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Fetch the booking again - it should still exist but have deletedAt set
        $softDeletedBooking = $this->entityManager->getRepository(Booking::class)->find($bookingId);

        // Verify the booking still exists in database
        $this->assertNotNull($softDeletedBooking, 'Booking should still exist in database after soft delete');

        // Verify deletedAt is set (this is the key indicator of soft delete)
        $this->assertNotNull($softDeletedBooking->getDeletedAt(), 'Booking should have deletedAt timestamp after soft delete');
        $this->assertInstanceOf(\DateTimeInterface::class, $softDeletedBooking->getDeletedAt(), 'deletedAt should be a DateTime object');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
