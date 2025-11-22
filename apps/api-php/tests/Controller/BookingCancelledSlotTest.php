<?php

namespace App\Tests\Controller;

use App\Entity\Booking;
use App\Entity\Provider;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookingCancelledSlotTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testCancelledBookingsDoNotBlockSlots(): void
    {
        // Create provider, service, and users
        $provider = new Provider();
        $provider->setName('Test Provider');
        $provider->setWorkingHours([
            'monday' => '09:00-17:00',
            'tuesday' => '09:00-17:00',
            'wednesday' => '09:00-17:00',
            'thursday' => '09:00-17:00',
            'friday' => '09:00-17:00',
        ]);
        $this->entityManager->persist($provider);

        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(30);
        $this->entityManager->persist($service);

        $user1 = new User();
        $user1->setEmail('user1_' . uniqid() . '@example.com');
        $user1->setPassword('hashed_password');
        $user1->setRoles(['ROLE_USER']);
        $this->entityManager->persist($user1);

        $user2 = new User();
        $user2->setEmail('user2_' . uniqid() . '@example.com');
        $user2->setPassword('hashed_password');
        $user2->setRoles(['ROLE_USER']);
        $this->entityManager->persist($user2);

        $this->entityManager->flush();

        // Create a booking and cancel it
        $targetDatetime = new \DateTime('next Monday 10:00');
        $booking = new Booking();
        $booking->setUser($user1);
        $booking->setProvider($provider);
        $booking->setService($service);
        $booking->setDatetime($targetDatetime);
        $booking->setStatus('confirmed');
        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        // Cancel the booking
        $booking->setStatus('cancelled');
        $this->entityManager->flush();

        // Authenticate as second user
        $this->client->loginUser($user2);

        // Get available slots - cancelled slot should be available
        $this->client->request('GET', '/api/bookings/available-slots', [
            'provider_id' => $provider->getId(),
            'service_id' => $service->getId(),
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        // The cancelled slot should be available again
        $cancelledSlot = $targetDatetime->format('Y-m-d H:i:s');
        $this->assertContains($cancelledSlot, $response, 'Cancelled booking slot should be available');

        // User2 should be able to book the cancelled slot
        $this->client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'provider_id' => $provider->getId(),
            'service_id' => $service->getId(),
            'datetime' => $cancelledSlot,
        ]));

        $this->assertResponseStatusCodeSame(201, 'Should be able to book a cancelled slot');
        $bookingResponse = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Booking created', $bookingResponse['message']);

        // Clean up - hard delete bookings to avoid foreign key issues
        $this->entityManager->getConnection()->executeStatement(
            'DELETE FROM booking WHERE provider_id = ?',
            [$provider->getId()]
        );
        $this->entityManager->clear();

        // Remove other entities
        $user1 = $this->entityManager->find(User::class, $user1->getId());
        $user2 = $this->entityManager->find(User::class, $user2->getId());
        $service = $this->entityManager->find(Service::class, $service->getId());
        $provider = $this->entityManager->find(Provider::class, $provider->getId());

        $this->entityManager->remove($user1);
        $this->entityManager->remove($user2);
        $this->entityManager->remove($service);
        $this->entityManager->remove($provider);
        $this->entityManager->flush();
    }

    public function testConfirmedBookingsBlockSlots(): void
    {
        // Create provider, service, and users
        $provider = new Provider();
        $provider->setName('Test Provider 2');
        $provider->setWorkingHours([
            'monday' => '09:00-17:00',
            'tuesday' => '09:00-17:00',
            'wednesday' => '09:00-17:00',
            'thursday' => '09:00-17:00',
            'friday' => '09:00-17:00',
        ]);
        $this->entityManager->persist($provider);

        $service = new Service();
        $service->setName('Test Service 2');
        $service->setDuration(30);
        $this->entityManager->persist($service);

        $user1 = new User();
        $user1->setEmail('user1_' . uniqid() . '@example.com');
        $user1->setPassword('hashed_password');
        $user1->setRoles(['ROLE_USER']);
        $this->entityManager->persist($user1);

        $user2 = new User();
        $user2->setEmail('user2_' . uniqid() . '@example.com');
        $user2->setPassword('hashed_password');
        $user2->setRoles(['ROLE_USER']);
        $this->entityManager->persist($user2);

        $this->entityManager->flush();

        // Create a confirmed booking
        $targetDatetime = new \DateTime('next Monday 11:00');
        $booking = new Booking();
        $booking->setUser($user1);
        $booking->setProvider($provider);
        $booking->setService($service);
        $booking->setDatetime($targetDatetime);
        $booking->setStatus('confirmed');
        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        // Authenticate as second user
        $this->client->loginUser($user2);

        // Get available slots - confirmed slot should NOT be available
        $this->client->request('GET', '/api/bookings/available-slots', [
            'provider_id' => $provider->getId(),
            'service_id' => $service->getId(),
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        // The confirmed slot should NOT be available
        $bookedSlot = $targetDatetime->format('Y-m-d H:i:s');
        $this->assertNotContains($bookedSlot, $response, 'Confirmed booking slot should NOT be available');

        // User2 should NOT be able to book the confirmed slot
        $this->client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'provider_id' => $provider->getId(),
            'service_id' => $service->getId(),
            'datetime' => $bookedSlot,
        ]));

        $this->assertResponseStatusCodeSame(409, 'Should not be able to book a confirmed slot');
        $bookingResponse = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Slot already booked', $bookingResponse['error']);

        // Clean up - hard delete bookings
        $this->entityManager->getConnection()->executeStatement(
            'DELETE FROM booking WHERE provider_id = ?',
            [$provider->getId()]
        );
        $this->entityManager->clear();

        // Remove other entities
        $user1 = $this->entityManager->find(User::class, $user1->getId());
        $user2 = $this->entityManager->find(User::class, $user2->getId());
        $service = $this->entityManager->find(Service::class, $service->getId());
        $provider = $this->entityManager->find(Provider::class, $provider->getId());

        $this->entityManager->remove($user1);
        $this->entityManager->remove($user2);
        $this->entityManager->remove($service);
        $this->entityManager->remove($provider);
        $this->entityManager->flush();
    }
}
