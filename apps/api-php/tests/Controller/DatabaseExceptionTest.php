<?php

namespace App\Tests\Controller;

use App\Entity\Provider;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DatabaseExceptionTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private ?string $token = null;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        
        // Clean up test data
        $this->entityManager->createQuery('DELETE FROM App\Entity\Booking')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Provider')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Service')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        
        // Create and authenticate test user
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword($hasher->hashPassword($user, 'password123'));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        // Login to get token
        $this->client->request('POST', '/api/login_check', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['username' => 'test@example.com', 'password' => 'password123'])
        );
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->token = $response['token'];
    }

    protected function tearDown(): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\Booking')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Provider')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Service')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->close();
        parent::tearDown();
    }

    public function testDuplicateEmailRegistrationReturns400(): void
    {
        // First registration should succeed
        $this->client->request('POST', '/api/register', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'duplicate@example.com',
                'password' => 'password123'
            ])
        );

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

        // Second registration with same email should fail
        $this->client->request('POST', '/api/register', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'duplicate@example.com',
                'password' => 'different_password'
            ])
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        // Validator returns 'errors' array, or controller catches with 'error' string
        $this->assertTrue(
            isset($response['error']) || isset($response['errors']),
            'Response should contain either error or errors field'
        );
        
        $errorMessage = $response['error'] ?? implode(', ', $response['errors'] ?? []);
        $this->assertStringContainsString('already', strtolower($errorMessage));
    }

    public function testDoubleBookingRaceConditionReturns409(): void
    {
        // Create provider and service
        $provider = new Provider();
        $provider->setName('Test Provider');
        $provider->setWorkingHours(['Monday' => '09:00-17:00']);
        $this->entityManager->persist($provider);

        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(30);
        $this->entityManager->persist($service);

        $this->entityManager->flush();

        $futureDate = (new \DateTime('next Monday'))->setTime(10, 0, 0);

        // First booking should succeed
        $this->client->request('POST', '/api/bookings', [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            ],
            json_encode([
                'provider_id' => $provider->getId(),
                'service_id' => $service->getId(),
                'datetime' => $futureDate->format('Y-m-d H:i:s'),
            ])
        );

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

        // Second booking for same slot should fail with 409
        $this->client->request('POST', '/api/bookings', [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            ],
            json_encode([
                'provider_id' => $provider->getId(),
                'service_id' => $service->getId(),
                'datetime' => $futureDate->format('Y-m-d H:i:s'),
            ])
        );

        $this->assertEquals(409, $this->client->getResponse()->getStatusCode());
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertStringContainsString('already booked', strtolower($response['error']));
    }
}
