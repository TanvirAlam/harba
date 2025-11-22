<?php

namespace App\Tests\Controller;

use App\Entity\Booking;
use App\Entity\Provider;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookingControllerSlotTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testAvailableSlotsGeneratesCorrectly(): void
    {
        // Create a test provider with working hours
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

        // Create a test service
        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(60); // 60 minutes
        $this->entityManager->persist($service);

        // Create a test user and authenticate
        $user = new User();
        $user->setEmail('test_' . uniqid() . '@example.com');
        $user->setPassword('hashed_password');
        $user->setRoles(['ROLE_USER']);
        $this->entityManager->persist($user);

        $this->entityManager->flush();

        // Create a booking to test filtering
        $booking = new Booking();
        $booking->setUser($user);
        $booking->setProvider($provider);
        $booking->setService($service);
        $booking->setDatetime(new \DateTime('next Monday 10:00'));
        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        // Authenticate the user
        $this->client->loginUser($user);

        // Request available slots
        $this->client->request('GET', '/api/bookings/available-slots', [
            'provider_id' => $provider->getId(),
            'service_id' => $service->getId(),
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        // Verify response is an array of slots
        $this->assertIsArray($response);
        $this->assertNotEmpty($response);

        // Verify slots are in correct format (Y-m-d H:i:s)
        foreach ($response as $slot) {
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $slot);
        }

        // Verify booked slot is not in available slots
        $bookedSlot = $booking->getDatetime()->format('Y-m-d H:i:s');
        $this->assertNotContains($bookedSlot, $response, 'Booked slot should not be available');

        // Clean up - hard delete the booking to avoid foreign key issues
        $bookingId = $booking->getId();
        $this->entityManager->getConnection()->executeStatement(
            'DELETE FROM booking WHERE id = ?',
            [$bookingId]
        );
        $this->entityManager->clear();
        
        // Now remove other entities
        $user = $this->entityManager->find(User::class, $user->getId());
        $service = $this->entityManager->find(Service::class, $service->getId());
        $provider = $this->entityManager->find(Provider::class, $provider->getId());
        
        $this->entityManager->remove($user);
        $this->entityManager->remove($service);
        $this->entityManager->remove($provider);
        $this->entityManager->flush();
    }

    public function testAvailableSlotsRespectsWorkingHours(): void
    {
        // Create a provider that only works on Monday from 10:00-12:00
        $provider = new Provider();
        $provider->setName('Limited Provider');
        $provider->setWorkingHours([
            'monday' => '10:00-12:00',
        ]);
        $this->entityManager->persist($provider);

        $service = new Service();
        $service->setName('Quick Service');
        $service->setDuration(30); // 30 minutes
        $this->entityManager->persist($service);

        $user = new User();
        $user->setEmail('test_' . uniqid() . '@example.com');
        $user->setPassword('hashed_password');
        $user->setRoles(['ROLE_USER']);
        $this->entityManager->persist($user);

        $this->entityManager->flush();

        $this->client->loginUser($user);

        $this->client->request('GET', '/api/bookings/available-slots', [
            'provider_id' => $provider->getId(),
            'service_id' => $service->getId(),
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        // Filter slots to only Mondays in the response
        $mondaySlots = array_filter($response, function ($slot) {
            $date = new \DateTime($slot);
            return $date->format('l') === 'Monday';
        });

        // Verify Monday slots are within working hours
        foreach ($mondaySlots as $slot) {
            $time = (new \DateTime($slot))->format('H:i');
            $this->assertGreaterThanOrEqual('10:00', $time);
            $this->assertLessThan('12:00', $time);
        }

        // Clean up
        $this->entityManager->remove($user);
        $this->entityManager->remove($service);
        $this->entityManager->remove($provider);
        $this->entityManager->flush();
    }
}
