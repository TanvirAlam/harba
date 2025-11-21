<?php

namespace App\Tests\Controller;

use App\Entity\Provider;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookingDateTimeValidationTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testBookingWithInvalidDateTimeFormatReturns400(): void
    {
        // Create test user
        $user = new User();
        $user->setEmail('test_datetime_' . uniqid() . '@example.com');
        $user->setPassword('hashed_password');
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

        $this->entityManager->flush();

        // Authenticate the user
        $this->client->loginUser($user);

        // Attempt to book with invalid datetime format
        $this->client->request('POST', '/api/bookings', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'provider_id' => $provider->getId(),
            'service_id' => $service->getId(),
            'datetime' => 'not-a-valid-datetime', // Invalid format
        ]));

        // Should return 400 Bad Request
        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertStringContainsString('Invalid datetime format', $response['error']);

        // Clean up
        $this->entityManager->getConnection()->executeStatement(
            'DELETE FROM booking WHERE user_id = ?',
            [$user->getId()]
        );
        $this->entityManager->remove($user);
        $this->entityManager->remove($service);
        $this->entityManager->remove($provider);
        $this->entityManager->flush();
    }

    public function testBookingWithValidDateTimeSucceeds(): void
    {
        // Create test user
        $user = new User();
        $user->setEmail('test_datetime_valid_' . uniqid() . '@example.com');
        $user->setPassword('hashed_password');
        $user->setRoles(['ROLE_USER']);
        $this->entityManager->persist($user);

        // Create test provider
        $provider = new Provider();
        $provider->setName('Test Provider Valid');
        $provider->setWorkingHours(['Monday' => '09:00-17:00']);
        $this->entityManager->persist($provider);

        // Create test service
        $service = new Service();
        $service->setName('Test Service Valid');
        $service->setDuration(60);
        $this->entityManager->persist($service);

        $this->entityManager->flush();

        // Authenticate the user
        $this->client->loginUser($user);

        // Book with valid datetime format
        $validDateTime = (new \DateTime('+1 week Monday 10:00'))->format('Y-m-d H:i:s');
        
        $this->client->request('POST', '/api/bookings', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'provider_id' => $provider->getId(),
            'service_id' => $service->getId(),
            'datetime' => $validDateTime, // Valid format
        ]));

        // Should return 201 Created
        $this->assertResponseStatusCodeSame(201);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('Booking created', $response['message']);

        // Clean up
        $this->entityManager->getConnection()->executeStatement(
            'DELETE FROM booking WHERE user_id = ?',
            [$user->getId()]
        );
        $this->entityManager->remove($user);
        $this->entityManager->remove($service);
        $this->entityManager->remove($provider);
        $this->entityManager->flush();
    }

}
